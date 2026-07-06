<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\PayrollAttendanceAdjustmentRequest;
use App\Models\EmployeeBiometric;
use App\Models\PayrollAttendanceAdjustment;
use App\Services\Biometrics\EmployeeBiometricIdentityService;
use App\Services\Payroll\BiometricsProofService;
use App\Services\Payroll\DailyAttendanceSummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PayrollAttendanceAdjustmentController extends Controller
{
    public function __construct(
        private readonly BiometricsProofService $biometricsProofService,
        private readonly DailyAttendanceSummaryService $dailyAttendanceSummaryService,
        private readonly EmployeeBiometricIdentityService $identityService
    ) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->search);
        $type = $request->type;
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $groupName = trim((string) $request->group_name);

        $query = PayrollAttendanceAdjustment::query()
            ->with(['encoder', 'employeeBiometric'])
            ->when($search, function ($query) use ($search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('employee_name', 'like', "%{$search}%")
                        ->orWhere('employee_no', 'like', "%{$search}%")
                        ->orWhere('biometric_employee_id', 'like', "%{$search}%")
                        ->orWhere('adjustment_type', 'like', "%{$search}%")
                        ->orWhere('reason', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%")
                        ->orWhereHas('employeeBiometric', function ($employeeQuery) use ($search): void {
                            $employeeQuery
                                ->where('display_name', 'like', "%{$search}%")
                                ->orWhere('display_employee_no', 'like', "%{$search}%")
                                ->orWhere('group_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($type, fn ($query) => $query->where('adjustment_type', $type))
            ->when($groupName !== '', fn ($query) => $query->whereHas('employeeBiometric', fn ($employeeQuery) => $employeeQuery->where('group_name', $groupName)))
            ->when($dateFrom, function ($query) use ($dateFrom): void {
                $query->whereDate(DB::raw('COALESCE(date_from, work_date)'), '>=', $dateFrom);
            })
            ->when($dateTo, function ($query) use ($dateTo): void {
                $query->whereDate(DB::raw('COALESCE(date_to, work_date)'), '<=', $dateTo);
            });

        $stats = [
            'total' => (clone $query)->count(),
            'leaves' => (clone $query)
                ->whereIn('adjustment_type', [
                    PayrollAttendanceAdjustment::TYPE_SICK_LEAVE,
                    PayrollAttendanceAdjustment::TYPE_MEDICAL_LEAVE,
                ])
                ->count(),
            'offsets' => (clone $query)
                ->where('adjustment_type', PayrollAttendanceAdjustment::TYPE_OFFSET)
                ->count(),
            'manual_time' => (clone $query)
                ->whereIn('adjustment_type', [
                    PayrollAttendanceAdjustment::TYPE_CHANGE_SCHEDULE,
                    PayrollAttendanceAdjustment::TYPE_OFFICIAL_BUSINESS,
                    PayrollAttendanceAdjustment::TYPE_HOLIDAY_WORK,
                    PayrollAttendanceAdjustment::TYPE_OVERTIME,
                ])
                ->count(),
            'disasters' => (clone $query)
                ->where('adjustment_type', PayrollAttendanceAdjustment::TYPE_TYPHOON_DISASTER)
                ->count(),
        ];

        $adjustments = $query
            ->orderByRaw('COALESCE(date_from, work_date) DESC')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $groups = $this->groups();

        return view('payroll.attendance_adjustments.index', compact(
            'adjustments',
            'stats',
            'search',
            'type',
            'dateFrom',
            'dateTo',
            'groupName',
            'groups'
        ))->with('types', PayrollAttendanceAdjustment::TYPES);
    }

    public function create(): View
    {
        return view('payroll.attendance_adjustments.create', [
            'people' => $this->getBiometricsPeople(),
            'types' => PayrollAttendanceAdjustment::TYPES,
            'groups' => $this->groups(),
        ]);
    }

    public function store(PayrollAttendanceAdjustmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if ($this->hasDuplicateAdjustment($validated)) {
            return back()
                ->withInput()
                ->withErrors([
                    'work_date' => $this->isGlobalDisasterType($validated)
                        ? 'A Typhoon / Disaster adjustment already exists for this work date.'
                        : 'An adjustment already exists for this employee within the selected date.',
                ]);
        }

        $payload = $this->buildPayload($validated, $request);

        if ($validated['adjustment_type'] === PayrollAttendanceAdjustment::TYPE_OFFSET) {
            $proof = $this->biometricsProofService->findOffsetProof(
                (int) $validated['employee_biometric_id'],
                $validated['biometric_employee_id'] ?? null,
                $validated['employee_no'] ?? null,
                $validated['employee_name'],
                $validated['offset_source_date']
            );

            if (! $proof) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'offset_source_date' => 'No biometric logs found for the selected employee and offset proof date.',
                    ]);
            }

            $payload = array_merge($payload, [
                'offset_source_time_in' => $proof['time_in'],
                'offset_source_time_out' => $proof['time_out'],
                'offset_source_logs' => $proof['logs'],
            ]);
        }

        $adjustment = PayrollAttendanceAdjustment::create($payload);

        $this->rebuildAffectedSummary($adjustment);

        return redirect()
            ->route('payroll-attendance-adjustments.index')
            ->with('success', $this->successMessage($adjustment, 'saved'));
    }

    public function edit(PayrollAttendanceAdjustment $payrollAttendanceAdjustment): View
    {
        return view('payroll.attendance_adjustments.edit', [
            'payrollAttendanceAdjustment' => $payrollAttendanceAdjustment->load('employeeBiometric'),
            'people' => $this->getBiometricsPeople(),
            'types' => PayrollAttendanceAdjustment::TYPES,
            'groups' => $this->groups(),
        ]);
    }

    public function update(
        PayrollAttendanceAdjustmentRequest $request,
        PayrollAttendanceAdjustment $payrollAttendanceAdjustment
    ): RedirectResponse {
        $validated = $request->validated();

        if ($this->hasDuplicateAdjustment($validated, $payrollAttendanceAdjustment->id)) {
            return back()
                ->withInput()
                ->withErrors([
                    'work_date' => $this->isGlobalDisasterType($validated)
                        ? 'Another Typhoon / Disaster adjustment already exists for this work date.'
                        : 'Another adjustment already exists for this employee within the selected date.',
                ]);
        }

        $oldWorkDate = $payrollAttendanceAdjustment->work_date?->toDateString();
        $payload = $this->buildPayload($validated, $request);

        if ($validated['adjustment_type'] === PayrollAttendanceAdjustment::TYPE_OFFSET) {
            $proof = $this->biometricsProofService->findOffsetProof(
                (int) $validated['employee_biometric_id'],
                $validated['biometric_employee_id'] ?? null,
                $validated['employee_no'] ?? null,
                $validated['employee_name'],
                $validated['offset_source_date']
            );

            if (! $proof) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'offset_source_date' => 'No biometric logs found for the selected employee and offset proof date.',
                    ]);
            }

            $payload = array_merge($payload, [
                'offset_source_time_in' => $proof['time_in'],
                'offset_source_time_out' => $proof['time_out'],
                'offset_source_logs' => $proof['logs'],
            ]);
        }

        $payrollAttendanceAdjustment->update($payload);
        $payrollAttendanceAdjustment->refresh();

        $this->rebuildAffectedSummary($payrollAttendanceAdjustment, $oldWorkDate);

        return redirect()
            ->route('payroll-attendance-adjustments.index')
            ->with('success', $this->successMessage($payrollAttendanceAdjustment, 'updated'));
    }

    public function destroy(PayrollAttendanceAdjustment $payrollAttendanceAdjustment): RedirectResponse
    {
        $oldWorkDate = $payrollAttendanceAdjustment->work_date?->toDateString();

        $payrollAttendanceAdjustment->delete();

        if ($oldWorkDate) {
            $this->dailyAttendanceSummaryService->buildForDate($oldWorkDate);
        }

        return redirect()
            ->route('payroll-attendance-adjustments.index')
            ->with('success', 'Payroll attendance adjustment deleted successfully.');
    }

    public function offsetProof(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_biometric_id' => ['required', 'integer', 'exists:employee_biometrics,id'],
            'biometric_employee_id' => ['nullable', 'string'],
            'employee_no' => ['nullable', 'string'],
            'employee_name' => ['required', 'string'],
            'offset_source_date' => ['required', 'date'],
        ]);

        $proof = $this->biometricsProofService->findOffsetProof(
            (int) $validated['employee_biometric_id'],
            $validated['biometric_employee_id'] ?? null,
            $validated['employee_no'] ?? null,
            $validated['employee_name'],
            $validated['offset_source_date']
        );

        if (! $proof) {
            return response()->json([
                'found' => false,
                'message' => 'No biometrics logs found for this employee on the selected proof date.',
            ], 404);
        }

        return response()->json([
            'found' => true,
            'message' => 'Biometric proof found.',
            'proof' => $proof,
        ]);
    }

    private function buildPayload(array $validated, Request $request): array
    {
        $type = $validated['adjustment_type'];
        $isLeave = in_array($type, [
            PayrollAttendanceAdjustment::TYPE_SICK_LEAVE,
            PayrollAttendanceAdjustment::TYPE_MEDICAL_LEAVE,
        ], true);

        $isGlobalDisaster = $type === PayrollAttendanceAdjustment::TYPE_TYPHOON_DISASTER;

        $requiresManualTime = in_array($type, [
            PayrollAttendanceAdjustment::TYPE_CHANGE_SCHEDULE,
            PayrollAttendanceAdjustment::TYPE_OFFICIAL_BUSINESS,
            PayrollAttendanceAdjustment::TYPE_HOLIDAY_WORK,
            PayrollAttendanceAdjustment::TYPE_OVERTIME,
        ], true);

        $snapshot = [
            'employee_biometric_id' => null,
            'biometric_employee_id' => PayrollAttendanceAdjustment::GLOBAL_DISASTER_BIOMETRIC_ID,
            'employee_no' => null,
            'employee_name' => PayrollAttendanceAdjustment::GLOBAL_DISASTER_EMPLOYEE_NAME,
            'crosschex_id' => null,
        ];

        if (! $isGlobalDisaster) {
            $employee = EmployeeBiometric::query()
                ->payrollActive()
                ->findOrFail((int) $validated['employee_biometric_id']);

            $snapshot = $this->identityService->snapshot($employee);
        }

        return [
            'employee_biometric_id' => $snapshot['employee_biometric_id'],
            'biometric_employee_id' => $snapshot['biometric_employee_id'],
            'employee_no' => $snapshot['employee_no'],
            'employee_name' => $snapshot['employee_name'],
            'crosschex_id' => $snapshot['crosschex_id'],

            'work_date' => $isLeave ? $validated['date_from'] : $validated['work_date'],
            'date_from' => $isLeave ? $validated['date_from'] : null,
            'date_to' => $isLeave ? $validated['date_to'] : null,

            'adjustment_type' => $type,
            'adjusted_time_in' => $requiresManualTime ? ($validated['adjusted_time_in'] ?? null) : null,
            'adjusted_time_out' => $requiresManualTime ? ($validated['adjusted_time_out'] ?? null) : null,
            'adjusted_day_type' => $this->dayTypeFor($type),

            'offset_source_date' => $type === PayrollAttendanceAdjustment::TYPE_OFFSET
                ? $validated['offset_source_date']
                : null,

            'offset_source_time_in' => null,
            'offset_source_time_out' => null,
            'offset_source_logs' => null,

            'is_paid' => $isGlobalDisaster ? true : $request->boolean('is_paid'),
            'ignore_late' => $isGlobalDisaster ? true : $request->boolean('ignore_late'),
            'ignore_undertime' => $isGlobalDisaster ? true : $request->boolean('ignore_undertime'),

            'reason' => $validated['reason'] ?? null,
            'remarks' => $validated['remarks'] ?? null,

            'encoded_by' => auth()->id(),
            'encoded_at' => now('Asia/Manila'),
        ];
    }

    private function dayTypeFor(string $type): string
    {
        return match ($type) {
            PayrollAttendanceAdjustment::TYPE_SICK_LEAVE => 'sick_leave',
            PayrollAttendanceAdjustment::TYPE_MEDICAL_LEAVE => 'medical_leave',
            PayrollAttendanceAdjustment::TYPE_CHANGE_SCHEDULE => 'change_schedule',
            PayrollAttendanceAdjustment::TYPE_OFFSET => 'offset',
            PayrollAttendanceAdjustment::TYPE_OFFICIAL_BUSINESS => 'official_business',
            PayrollAttendanceAdjustment::TYPE_HOLIDAY_WORK => 'holiday_work',
            PayrollAttendanceAdjustment::TYPE_OVERTIME => 'overtime',
            PayrollAttendanceAdjustment::TYPE_TYPHOON_DISASTER => 'typhoon_disaster',
            default => 'adjustment',
        };
    }

    private function getBiometricsPeople()
    {
        return EmployeeBiometric::query()
            ->payrollActive()
            ->orderBy('group_name')
            ->orderByRaw("COALESCE(NULLIF(display_name, ''), NULLIF(source_employee_name, ''), NULLIF(source_crosschex_account_name, '')) ASC")
            ->get()
            ->map(function (EmployeeBiometric $employee) {
                $snapshot = $this->identityService->snapshot($employee);

                return (object) [
                    'employee_biometric_id' => $employee->id,
                    'biometric_employee_id' => $snapshot['biometric_employee_id'],
                    'employee_no' => $snapshot['employee_no'],
                    'employee_name' => $snapshot['employee_name'],
                    'crosschex_id' => $snapshot['crosschex_id'],
                    'group_name' => $employee->group_name,
                    'last_check_time' => $employee->last_check_time,
                    'total_logs' => $employee->total_logs,
                ];
            })
            ->values();
    }

    private function hasDuplicateAdjustment(array $validated, ?int $ignoreId = null): bool
    {
        $query = PayrollAttendanceAdjustment::query();

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($this->isGlobalDisasterType($validated)) {
            return $query
                ->where('adjustment_type', PayrollAttendanceAdjustment::TYPE_TYPHOON_DISASTER)
                ->whereDate('work_date', $validated['work_date'])
                ->exists();
        }

        $dateFrom = $validated['date_from'] ?? $validated['work_date'];
        $dateTo = $validated['date_to'] ?? $validated['work_date'];

        $query->where('employee_biometric_id', (int) $validated['employee_biometric_id']);

        $query->where(function ($q) use ($dateFrom, $dateTo): void {
            $q->whereRaw('COALESCE(date_from, work_date) <= ?', [$dateTo])
                ->whereRaw('COALESCE(date_to, work_date) >= ?', [$dateFrom]);
        });

        return $query->exists();
    }

    private function rebuildAffectedSummary(PayrollAttendanceAdjustment $adjustment, ?string $oldWorkDate = null): void
    {
        $newWorkDate = $adjustment->work_date?->toDateString();

        if ($oldWorkDate && $oldWorkDate !== $newWorkDate) {
            $this->dailyAttendanceSummaryService->buildForDate($oldWorkDate);
        }

        if ($newWorkDate) {
            $this->dailyAttendanceSummaryService->buildForDate($newWorkDate);
        }
    }

    private function successMessage(PayrollAttendanceAdjustment $adjustment, string $action): string
    {
        if ($adjustment->adjustment_type === PayrollAttendanceAdjustment::TYPE_TYPHOON_DISASTER) {
            return 'Typhoon / Disaster adjustment '.$action.'. All active biometric employees with time-in on the selected date will be paid as whole day after summary rebuild.';
        }

        return 'Payroll attendance adjustment '.$action.' successfully.';
    }

    private function isGlobalDisasterType(array $validated): bool
    {
        return ($validated['adjustment_type'] ?? null) === PayrollAttendanceAdjustment::TYPE_TYPHOON_DISASTER;
    }

    private function groups()
    {
        return EmployeeBiometric::query()
            ->payrollActive()
            ->whereNotNull('group_name')
            ->where('group_name', '!=', '')
            ->distinct()
            ->orderBy('group_name')
            ->pluck('group_name');
    }
}
