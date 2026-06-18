<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\PayrollAttendanceAdjustmentRequest;
use App\Models\MirasolBiometricsLog;
use App\Models\PayrollAttendanceAdjustment;
use App\Services\Payroll\BiometricsProofService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PayrollAttendanceAdjustmentController extends Controller
{
    public function __construct(
        private readonly BiometricsProofService $biometricsProofService
    ) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->search);
        $type = $request->type;
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        $query = PayrollAttendanceAdjustment::query()
            ->with('encoder')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('employee_name', 'like', "%{$search}%")
                        ->orWhere('employee_no', 'like', "%{$search}%")
                        ->orWhere('biometric_employee_id', 'like', "%{$search}%")
                        ->orWhere('adjustment_type', 'like', "%{$search}%")
                        ->orWhere('reason', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%");
                });
            })
            ->when($type, fn ($query) => $query->where('adjustment_type', $type))
            ->when($dateFrom, function ($query) use ($dateFrom) {
                $query->whereDate(DB::raw('COALESCE(date_from, work_date)'), '>=', $dateFrom);
            })
            ->when($dateTo, function ($query) use ($dateTo) {
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
                ])
                ->count(),
        ];

        $adjustments = $query
            ->orderByRaw('COALESCE(date_from, work_date) DESC')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('payroll.attendance_adjustments.index', [
            'adjustments' => $adjustments,
            'stats' => $stats,
            'types' => PayrollAttendanceAdjustment::TYPES,
            'search' => $search,
            'type' => $type,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public function create(): View
    {
        return view('payroll.attendance_adjustments.create', [
            'people' => $this->getBiometricsPeople(),
            'types' => PayrollAttendanceAdjustment::TYPES,
        ]);
    }

    public function store(PayrollAttendanceAdjustmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if ($this->hasDuplicateAdjustment($validated)) {
            return back()
                ->withInput()
                ->withErrors([
                    'work_date' => 'An adjustment already exists for this employee within the selected date.',
                ]);
        }

        $payload = $this->buildPayload($validated, $request);

        if ($validated['adjustment_type'] === PayrollAttendanceAdjustment::TYPE_OFFSET) {
            $proof = $this->biometricsProofService->findOffsetProof(
                $validated['biometric_employee_id'],
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

        PayrollAttendanceAdjustment::create($payload);

        return redirect()
            ->route('payroll-attendance-adjustments.index')
            ->with('success', 'Payroll attendance adjustment saved successfully.');
    }

    public function edit(PayrollAttendanceAdjustment $payrollAttendanceAdjustment): View
    {
        return view('payroll.attendance_adjustments.edit', [
            'payrollAttendanceAdjustment' => $payrollAttendanceAdjustment,
            'people' => $this->getBiometricsPeople(),
            'types' => PayrollAttendanceAdjustment::TYPES,
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
                    'work_date' => 'Another adjustment already exists for this employee within the selected date.',
                ]);
        }

        $payload = $this->buildPayload($validated, $request);

        if ($validated['adjustment_type'] === PayrollAttendanceAdjustment::TYPE_OFFSET) {
            $proof = $this->biometricsProofService->findOffsetProof(
                $validated['biometric_employee_id'],
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

        return redirect()
            ->route('payroll-attendance-adjustments.index')
            ->with('success', 'Payroll attendance adjustment updated successfully.');
    }

    public function destroy(PayrollAttendanceAdjustment $payrollAttendanceAdjustment): RedirectResponse
    {
        $payrollAttendanceAdjustment->delete();

        return redirect()
            ->route('payroll-attendance-adjustments.index')
            ->with('success', 'Payroll attendance adjustment deleted successfully.');
    }

    public function offsetProof(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'biometric_employee_id' => ['required', 'string'],
            'employee_no' => ['nullable', 'string'],
            'employee_name' => ['required', 'string'],
            'offset_source_date' => ['required', 'date'],
        ]);

        $proof = $this->biometricsProofService->findOffsetProof(
            $validated['biometric_employee_id'],
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

        $requiresManualTime = in_array($type, [
            PayrollAttendanceAdjustment::TYPE_CHANGE_SCHEDULE,
            PayrollAttendanceAdjustment::TYPE_OFFICIAL_BUSINESS,
        ], true);

        return [
            'biometric_employee_id' => $validated['biometric_employee_id'],
            'employee_no' => $validated['employee_no'] ?? null,
            'employee_name' => $validated['employee_name'],

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

            'is_paid' => $request->boolean('is_paid'),
            'ignore_late' => $request->boolean('ignore_late'),
            'ignore_undertime' => $request->boolean('ignore_undertime'),

            'reason' => $validated['reason'] ?? null,
            'remarks' => $validated['remarks'] ?? null,

            'encoded_by' => auth()->id(),
            'encoded_at' => now(),
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
            default => 'adjustment',
        };
    }

    private function getBiometricsPeople()
    {
        $logs = MirasolBiometricsLog::query()
            ->select([
                'id',
                'employee_id',
                'employee_no',
                'crosschex_id',
                'employee_name',
                'crosschex_account_name',
                'crosschex_account',
                'check_time',
            ])
            ->where(function ($query) {
                $query->whereNotNull('employee_name')
                    ->orWhereNotNull('crosschex_account_name')
                    ->orWhereNotNull('crosschex_account')
                    ->orWhereNotNull('employee_no')
                    ->orWhereNotNull('employee_id');
            })
            ->orderByDesc('check_time')
            ->get();

        return $logs
            ->map(function (MirasolBiometricsLog $log) {
                $employeeName = $this->firstFilledValue([
                    $log->employee_name,
                    $log->crosschex_account_name,
                    $log->crosschex_account,
                ]);

                $employeeNo = $this->firstFilledValue([
                    $log->employee_no,
                    $log->employee_id,
                ]);

                if (empty($employeeName)) {
                    return null;
                }

                return (object) [
                    'identity_key' => $this->makeEmployeeDropdownKey($employeeNo, $employeeName),
                    'biometric_employee_id' => $employeeNo ?: $employeeName,
                    'employee_no' => $employeeNo,
                    'employee_name' => $employeeName,
                    'last_check_time' => $log->check_time,
                    'total_logs' => 1,
                ];
            })
            ->filter()
            ->groupBy('identity_key')
            ->map(function ($group) {
                $latest = $group
                    ->sortByDesc(function ($person) {
                        return $person->last_check_time?->timestamp ?? 0;
                    })
                    ->first();

                $latest->total_logs = $group->count();

                return $latest;
            })
            ->sortBy('employee_name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    private function hasDuplicateAdjustment(array $validated, ?int $ignoreId = null): bool
    {
        $dateFrom = $validated['date_from'] ?? $validated['work_date'];
        $dateTo = $validated['date_to'] ?? $validated['work_date'];

        $query = PayrollAttendanceAdjustment::query();

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $query->where(function ($q) use ($validated) {
            $q->where('biometric_employee_id', $validated['biometric_employee_id']);

            if (! empty($validated['employee_no'])) {
                $q->orWhere('employee_no', $validated['employee_no']);
            }

            $q->orWhere('employee_name', $validated['employee_name']);
        });

        $query->where(function ($q) use ($dateFrom, $dateTo) {
            $q->whereRaw('COALESCE(date_from, work_date) <= ?', [$dateTo])
                ->whereRaw('COALESCE(date_to, work_date) >= ?', [$dateFrom]);
        });

        return $query->exists();
    }

    private function findOffsetProof(
        string $biometricEmployeeId,
        ?string $employeeNo,
        string $employeeName,
        string $offsetSourceDate
    ): ?array {
        $logs = MirasolBiometricsLog::query()
            ->whereDate('check_time', $offsetSourceDate)
            ->where(function ($query) use ($biometricEmployeeId, $employeeNo, $employeeName) {
                if (! empty($employeeNo)) {
                    $query->where('employee_no', $employeeNo)
                        ->orWhere('employee_id', $employeeNo);
                } else {
                    $query->where('employee_no', $biometricEmployeeId)
                        ->orWhere('employee_id', $biometricEmployeeId)
                        ->orWhere('employee_name', $employeeName)
                        ->orWhere('crosschex_account_name', $employeeName)
                        ->orWhere('crosschex_account', $employeeName);
                }
            })
            ->orderBy('check_time')
            ->get();

        if ($logs->isEmpty()) {
            return null;
        }

        $times = $logs
            ->pluck('check_time')
            ->filter()
            ->map(fn ($value) => Carbon::parse($value))
            ->sort()
            ->values();

        if ($times->isEmpty()) {
            return null;
        }

        $firstLog = $logs->first();

        return [
            'date' => Carbon::parse($offsetSourceDate)->format('Y-m-d'),
            'employee_name' => $firstLog->employee_name
                ?: $firstLog->crosschex_account_name
                ?: $firstLog->crosschex_account
                ?: $employeeName,
            'employee_no' => $firstLog->employee_no ?: $firstLog->employee_id,
            'biometric_employee_id' => $firstLog->employee_no ?: $firstLog->employee_id ?: $biometricEmployeeId,
            'time_in' => $times->first()->format('H:i'),
            'time_out' => $times->last()->format('H:i'),
            'count' => $logs->count(),
            'logs' => $logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'employee_id' => $log->employee_id,
                    'employee_no' => $log->employee_no,
                    'employee_name' => $log->employee_name
                        ?: $log->crosschex_account_name
                        ?: $log->crosschex_account,
                    'check_time' => optional($log->check_time)->format('Y-m-d H:i:s'),
                    'state' => $log->state,
                    'device_name' => $log->device_name,
                ];
            })->values()->toArray(),
        ];
    }

    private function biometricDateTimeColumn(): string
    {
        $table = (new MirasolBiometricsLog)->getTable();

        foreach ([
            'log_datetime',
            'attendance_datetime',
            'punch_time',
            'scan_time',
            'recorded_at',
            'datetime',
            'date_time',
            'created_at',
        ] as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return 'created_at';
    }

    private function firstFilledValue(array $values): ?string
    {
        foreach ($values as $value) {
            if ($value === null) {
                continue;
            }

            $value = trim((string) $value);

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function makeEmployeeDropdownKey(?string $employeeNo, string $employeeName): string
    {
        if (! empty($employeeNo)) {
            return 'employee-no:'.$this->normalizeEmployeeKey($employeeNo);
        }

        return 'employee-name:'.$this->normalizeEmployeeKey($employeeName);
    }

    private function normalizeEmployeeKey(string $value): string
    {
        return strtolower(trim(preg_replace('/\s+/', ' ', $value)));
    }
}
