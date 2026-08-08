<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\PayrollAttendanceAdjustmentRequest;
use App\Models\DailyAttendanceSummary;
use App\Models\EmployeeBiometric;
use App\Models\EmployeePlottingSchedule;
use App\Models\PayrollAttendanceAdjustment;
use App\Services\Biometrics\EmployeeBiometricIdentityService;
use App\Services\Payroll\BiometricsProofService;
use App\Services\Payroll\DailyAttendanceSummaryService;
use App\Services\Payroll\PayrollPremiumService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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
        private readonly EmployeeBiometricIdentityService $identityService,
        private readonly PayrollPremiumService $premiumService,
    ) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->search);
        $type = $request->type;
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $groupName = trim((string) $request->group_name);

        $query = PayrollAttendanceAdjustment::query()
            ->with(['encoder', 'employeeBiometric', 'approver', 'rejector', 'paidPayroll'])
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
            'leaves' => (clone $query)->whereIn('adjustment_type', [
                PayrollAttendanceAdjustment::TYPE_SICK_LEAVE,
                PayrollAttendanceAdjustment::TYPE_MEDICAL_LEAVE,
            ])->count(),
            'offsets' => (clone $query)->where('adjustment_type', PayrollAttendanceAdjustment::TYPE_OFFSET)->count(),
            'manual_time' => (clone $query)->whereIn('adjustment_type', [
                PayrollAttendanceAdjustment::TYPE_CHANGE_SCHEDULE,
                PayrollAttendanceAdjustment::TYPE_OFFICIAL_BUSINESS,
                PayrollAttendanceAdjustment::TYPE_HOLIDAY_WORK,
                PayrollAttendanceAdjustment::TYPE_OVERTIME,
            ])->count(),
            'disasters' => (clone $query)->where('adjustment_type', PayrollAttendanceAdjustment::TYPE_TYPHOON_DISASTER)->count(),
            'pending' => (clone $query)->where('status', PayrollAttendanceAdjustment::STATUS_PENDING)->count(),
        ];

        $adjustments = $query
            ->orderByRaw('COALESCE(date_from, work_date) DESC')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('payroll.attendance_adjustments.index', [
            'adjustments' => $adjustments,
            'stats' => $stats,
            'search' => $search,
            'type' => $type,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'groupName' => $groupName,
            'groups' => $this->groups(),
            'types' => PayrollAttendanceAdjustment::TYPES,
        ]);
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
            return back()->withInput()->withErrors([
                'work_date' => $this->isGlobalDisasterType($validated)
                    ? 'A Typhoon / Disaster adjustment already exists for this work date.'
                    : 'The same adjustment type already exists for this employee within the selected date range.',
            ]);
        }

        $payload = $this->buildPayload($validated, $request);

        if ($validated['adjustment_type'] === PayrollAttendanceAdjustment::TYPE_OFFSET) {
            $offset = $this->buildOffsetProofPayload($validated);

            if ($offset instanceof RedirectResponse) {
                return $offset;
            }

            $payload = array_merge($payload, $offset);
        }

        $adjustment = DB::transaction(fn () => PayrollAttendanceAdjustment::create($payload));
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
        if ($payrollAttendanceAdjustment->paid_payroll_id) {
            return back()->withErrors([
                'adjustment_type' => 'This adjustment is already linked to a generated payroll and can no longer be edited. Delete/regenerate the affected draft payroll first if a correction is required.',
            ]);
        }

        $validated = $request->validated();

        if ($this->hasDuplicateAdjustment($validated, $payrollAttendanceAdjustment->id)) {
            return back()->withInput()->withErrors([
                'work_date' => $this->isGlobalDisasterType($validated)
                    ? 'Another Typhoon / Disaster adjustment already exists for this work date.'
                    : 'The same adjustment type already exists for this employee within the selected date range.',
            ]);
        }

        $oldRange = $this->adjustmentDateRange($payrollAttendanceAdjustment);
        $payload = $this->buildPayload($validated, $request, $payrollAttendanceAdjustment);

        if ($validated['adjustment_type'] === PayrollAttendanceAdjustment::TYPE_OFFSET) {
            $offset = $this->buildOffsetProofPayload($validated, $payrollAttendanceAdjustment->id);

            if ($offset instanceof RedirectResponse) {
                return $offset;
            }

            $payload = array_merge($payload, $offset, [
                'paid_payroll_id' => null,
                'paid_payroll_item_id' => null,
            ]);
        }

        DB::transaction(fn () => $payrollAttendanceAdjustment->update($payload));
        $payrollAttendanceAdjustment->refresh();
        $this->rebuildAffectedSummary($payrollAttendanceAdjustment, $oldRange);

        return redirect()
            ->route('payroll-attendance-adjustments.index')
            ->with('success', $this->successMessage($payrollAttendanceAdjustment, 'updated'));
    }

    public function approve(PayrollAttendanceAdjustment $payrollAttendanceAdjustment): RedirectResponse
    {
        if (! $payrollAttendanceAdjustment->isApprovalRequired()) {
            return back()->with('success', 'This adjustment type does not require separate manager approval.');
        }

        if ($payrollAttendanceAdjustment->paid_payroll_id) {
            return back()->withErrors(['approval' => 'This adjustment is already linked to a payroll and cannot be re-approved.']);
        }

        if ($payrollAttendanceAdjustment->adjustment_type === PayrollAttendanceAdjustment::TYPE_OFFSET) {
            $validationMessage = $this->offsetApprovalValidationMessage($payrollAttendanceAdjustment);

            if ($validationMessage) {
                return back()->withErrors(['approval' => $validationMessage]);
            }
        }

        $payrollAttendanceAdjustment->update([
            'status' => PayrollAttendanceAdjustment::STATUS_APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now('Asia/Manila'),
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);

        if ($payrollAttendanceAdjustment->adjustment_type !== PayrollAttendanceAdjustment::TYPE_OVERTIME) {
            $this->rebuildAffectedSummary($payrollAttendanceAdjustment);
        }

        return back()->with('success', $payrollAttendanceAdjustment->adjustment_type === PayrollAttendanceAdjustment::TYPE_OVERTIME
            ? 'Overtime adjustment approved. It will now be included when the affected draft payroll is generated/regenerated.'
            : 'Offset adjustment approved. The compensatory-time credit has been applied to the target attendance date. Regenerate any affected draft payroll.');
    }

    public function reject(Request $request, PayrollAttendanceAdjustment $payrollAttendanceAdjustment): RedirectResponse
    {
        if (! $payrollAttendanceAdjustment->isApprovalRequired()) {
            return back()->withErrors([
                'approval' => 'Only adjustment types that require manager approval can be rejected through this workflow.',
            ]);
        }

        if ($payrollAttendanceAdjustment->paid_payroll_id) {
            return back()->withErrors(['approval' => 'This adjustment is already linked to a payroll and cannot be rejected.']);
        }

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $reason = trim((string) ($validated['rejection_reason'] ?? 'Rejected by Head Manager / authorized approver.'));

        $payrollAttendanceAdjustment->update([
            'status' => PayrollAttendanceAdjustment::STATUS_REJECTED,
            'rejected_by' => auth()->id(),
            'rejected_at' => now('Asia/Manila'),
            'rejection_reason' => $reason,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        if ($payrollAttendanceAdjustment->adjustment_type !== PayrollAttendanceAdjustment::TYPE_OVERTIME) {
            $this->rebuildAffectedSummary($payrollAttendanceAdjustment);
        }

        return back()->with('success', $payrollAttendanceAdjustment->adjustment_type === PayrollAttendanceAdjustment::TYPE_OVERTIME
            ? 'Overtime adjustment rejected. It will not be paid.'
            : 'Offset adjustment rejected. No compensatory-time credit will be applied.');
    }

    public function destroy(PayrollAttendanceAdjustment $payrollAttendanceAdjustment): RedirectResponse
    {
        if ($payrollAttendanceAdjustment->paid_payroll_id) {
            return back()->withErrors([
                'adjustment' => 'This adjustment is already linked to a generated payroll and cannot be deleted until the affected draft payroll is deleted/regenerated.',
            ]);
        }

        $oldRange = $this->adjustmentDateRange($payrollAttendanceAdjustment);
        $payrollAttendanceAdjustment->delete();
        $this->rebuildDates($oldRange);

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
            'work_date' => ['required', 'date', 'after:offset_source_date'],
            'offset_hours' => ['required', 'numeric', 'min:0.01', 'max:24'],
            'adjustment_id' => ['nullable', 'integer', 'exists:payroll_attendance_adjustments,id'],
        ]);

        $employeeBiometricId = (int) $validated['employee_biometric_id'];
        $ignoreAdjustmentId = isset($validated['adjustment_id'])
            ? (int) $validated['adjustment_id']
            : null;
        $targetDate = Carbon::parse($validated['work_date'], 'Asia/Manila');
        $targetSchedule = $this->scheduleForEmployeeDate(
            $employeeBiometricId,
            $targetDate->toDateString()
        );

        if (! $targetSchedule) {
            return response()->json([
                'found' => false,
                'message' => 'Target date has no plotted schedule. Plot the employee schedule first.',
            ], 422);
        }

        if ($targetSchedule->isDayOffOn($targetDate)) {
            return response()->json([
                'found' => false,
                'message' => "Target date is the employee's weekly day off. Select a scheduled working date.",
            ], 422);
        }

        $proof = $this->biometricsProofService->findOffsetProof(
            $employeeBiometricId,
            $validated['biometric_employee_id'] ?? null,
            $validated['employee_no'] ?? null,
            $validated['employee_name'],
            $validated['offset_source_date']
        );

        if (! $proof) {
            return response()->json([
                'found' => false,
                'message' => 'No biometrics logs found for this employee on the selected Offset source date.',
            ], 404);
        }

        $availableMinutes = $this->resolveOffsetApprovedMinutes(
            $employeeBiometricId,
            $validated['offset_source_date'],
            $proof['time_in'] ?? null,
            $proof['time_out'] ?? null,
            $ignoreAdjustmentId
        );
        $requestedMinutes = max(1, (int) round(((float) $validated['offset_hours']) * 60));
        $targetCapacityMinutes = $this->offsetTargetCapacityMinutes(
            $employeeBiometricId,
            $targetDate->toDateString(),
            $ignoreAdjustmentId
        );

        $proof['available_minutes'] = $availableMinutes;
        $proof['available_hours'] = round($availableMinutes / 60, 2);
        $proof['approved_minutes'] = $availableMinutes; // backwards-compatible UI key
        $proof['approved_hours'] = $proof['available_hours'];
        $proof['requested_minutes'] = $requestedMinutes;
        $proof['requested_hours'] = round($requestedMinutes / 60, 2);
        $proof['target_capacity_minutes'] = $targetCapacityMinutes;
        $proof['target_capacity_hours'] = $targetCapacityMinutes === null
            ? null
            : round($targetCapacityMinutes / 60, 2);
        $proof['target_date'] = $targetDate->toDateString();

        if ($availableMinutes <= 0) {
            return response()->json([
                'found' => false,
                'message' => 'Biometrics were found, but there is no unused excess time available. Minutes already reserved by another Offset request are excluded. Approved OT remains independently payable.',
                'proof' => $proof,
            ], 422);
        }

        if ($requestedMinutes > $availableMinutes) {
            return response()->json([
                'found' => false,
                'message' => sprintf(
                    'Requested %.2f hour(s), but only %.2f unused excess hour(s) are available on the source date.',
                    $requestedMinutes / 60,
                    $availableMinutes / 60
                ),
                'proof' => $proof,
            ], 422);
        }

        if ($targetCapacityMinutes !== null && $targetCapacityMinutes <= 0) {
            return response()->json([
                'found' => false,
                'message' => 'The target date already has a complete payable day with no attendance shortage to cover.',
                'proof' => $proof,
            ], 422);
        }

        if ($targetCapacityMinutes !== null && $requestedMinutes > $targetCapacityMinutes) {
            return response()->json([
                'found' => false,
                'message' => sprintf(
                    'Target date currently needs only %.2f hour(s) of Offset credit.',
                    $targetCapacityMinutes / 60
                ),
                'proof' => $proof,
            ], 422);
        }

        return response()->json([
            'found' => true,
            'message' => 'Offset is valid for review. Source excess, requested hours, and target attendance capacity are within the allowed limits.',
            'proof' => $proof,
        ]);
    }

    private function buildPayload(
        array $validated,
        Request $request,
        ?PayrollAttendanceAdjustment $existing = null
    ): array {
        $type = $validated['adjustment_type'];
        $rules = PayrollAttendanceAdjustment::rulesFor($type);
        $isLeave = ($rules['date_mode'] ?? 'single') === 'range';
        $isGlobalDisaster = $type === PayrollAttendanceAdjustment::TYPE_TYPHOON_DISASTER;
        $manualMode = (string) ($rules['manual_time_mode'] ?? 'none');
        $requiresManualTime = in_array($manualMode, ['schedule', 'actual', 'overtime'], true);

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

        $workDate = $isLeave ? $validated['date_from'] : $validated['work_date'];
        $isApprovalRequired = (bool) ($rules['approval_required'] ?? false);
        $status = $isApprovalRequired
            ? PayrollAttendanceAdjustment::STATUS_PENDING
            : PayrollAttendanceAdjustment::STATUS_APPROVED;

        // Editing an already-approved OT keeps approval only when its critical OT fields did not change.
        if ($existing && $isApprovalRequired && $existing->status === PayrollAttendanceAdjustment::STATUS_APPROVED) {
            $requestedOffsetMinutes = $type === PayrollAttendanceAdjustment::TYPE_OFFSET
                ? max(1, (int) round(((float) ($validated['offset_hours'] ?? 0)) * 60))
                : null;

            $criticalChanged = (string) $existing->adjustment_type !== (string) $type
                || (int) ($existing->employee_biometric_id ?? 0) !== (int) ($snapshot['employee_biometric_id'] ?? 0)
                || (string) $existing->work_date?->toDateString() !== (string) $workDate
                || (string) $existing->adjusted_time_in !== (string) ($validated['adjusted_time_in'] ?? '')
                || (string) $existing->adjusted_time_out !== (string) ($validated['adjusted_time_out'] ?? '')
                || ($type === PayrollAttendanceAdjustment::TYPE_OFFSET
                    && (
                        (string) $existing->offset_source_date?->toDateString() !== (string) ($validated['offset_source_date'] ?? '')
                        || (int) ($existing->approved_minutes ?? 0) !== (int) $requestedOffsetMinutes
                    ));

            $status = $criticalChanged ? PayrollAttendanceAdjustment::STATUS_PENDING : PayrollAttendanceAdjustment::STATUS_APPROVED;
        }

        $isPaid = (bool) ($rules['default_paid'] ?? false);
        $ignoreLate = (bool) ($rules['default_ignore_late'] ?? false);
        $ignoreUndertime = (bool) ($rules['default_ignore_undertime'] ?? false);

        // Leave pay can be explicitly switched off (e.g. unpaid/unsupported leave) without changing the type.
        if ($isLeave) {
            $isPaid = $request->boolean('is_paid', $isPaid);
        }

        /*
         * Offset is a company compensatory-leave credit: it is NOT a cash addition and is
         * NOT deferred to another payroll. Approved source excess minutes are
         * applied to attendance shortage on the target work date.
         */
        $deferToNextPayroll = false;

        if ($type === PayrollAttendanceAdjustment::TYPE_OFFSET) {
            $isPaid = false;
            $ignoreLate = false;
            $ignoreUndertime = false;
        }

        return [
            'employee_biometric_id' => $snapshot['employee_biometric_id'],
            'biometric_employee_id' => $snapshot['biometric_employee_id'],
            'employee_no' => $snapshot['employee_no'],
            'employee_name' => $snapshot['employee_name'],
            'crosschex_id' => $snapshot['crosschex_id'],
            'work_date' => $workDate,
            'date_from' => $isLeave ? $validated['date_from'] : null,
            'date_to' => $isLeave ? $validated['date_to'] : null,
            'adjustment_type' => $type,
            'adjusted_time_in' => $requiresManualTime ? ($validated['adjusted_time_in'] ?? null) : null,
            'adjusted_time_out' => $requiresManualTime ? ($validated['adjusted_time_out'] ?? null) : null,
            'adjusted_day_type' => $this->dayTypeFor($type),
            'offset_source_date' => $type === PayrollAttendanceAdjustment::TYPE_OFFSET ? $validated['offset_source_date'] : null,
            'offset_source_time_in' => null,
            'offset_source_time_out' => null,
            'offset_source_logs' => null,
            'approved_minutes' => null,
            'defer_to_next_payroll' => $deferToNextPayroll,
            'payroll_effective_date' => null,
            'is_paid' => $isPaid,
            'ignore_late' => $ignoreLate,
            'ignore_undertime' => $ignoreUndertime,
            'status' => $status,
            'approved_by' => $status === PayrollAttendanceAdjustment::STATUS_APPROVED ? (auth()->id() ?: $existing?->approved_by) : null,
            'approved_at' => $status === PayrollAttendanceAdjustment::STATUS_APPROVED ? ($existing?->approved_at ?: now('Asia/Manila')) : null,
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
            'reason' => $validated['reason'],
            'remarks' => $validated['remarks'] ?? null,
            'encoded_by' => $existing?->encoded_by ?: auth()->id(),
            'encoded_at' => $existing?->encoded_at ?: now('Asia/Manila'),
        ];
    }

    private function buildOffsetProofPayload(array $validated, ?int $ignoreAdjustmentId = null): array|RedirectResponse
    {
        $employeeBiometricId = (int) $validated['employee_biometric_id'];
        $targetDate = Carbon::parse($validated['work_date'], 'Asia/Manila');
        $targetSchedule = $this->scheduleForEmployeeDate(
            $employeeBiometricId,
            $targetDate->toDateString()
        );

        if (! $targetSchedule) {
            return back()->withInput()->withErrors([
                'work_date' => 'Offset target date has no plotted work schedule. Plot the employee schedule first before applying compensatory time.',
            ]);
        }

        if ($targetSchedule->isDayOffOn($targetDate)) {
            return back()->withInput()->withErrors([
                'work_date' => "Offset cannot be targeted to the employee's weekly day off. Select a scheduled working date with an attendance shortage.",
            ]);
        }

        $proof = $this->biometricsProofService->findOffsetProof(
            $employeeBiometricId,
            $validated['biometric_employee_id'] ?? null,
            $validated['employee_no'] ?? null,
            $validated['employee_name'],
            $validated['offset_source_date']
        );

        if (! $proof) {
            return back()->withInput()->withErrors([
                'offset_source_date' => 'No biometric logs found for the selected employee on the Offset source date.',
            ]);
        }

        $availableMinutes = $this->resolveOffsetApprovedMinutes(
            $employeeBiometricId,
            $validated['offset_source_date'],
            $proof['time_in'] ?? null,
            $proof['time_out'] ?? null,
            $ignoreAdjustmentId
        );

        if ($availableMinutes <= 0) {
            return back()->withInput()->withErrors([
                'offset_source_date' => 'Biometric proof exists, but there is no unused excess work time available for Offset. Only time beyond the required shift can be transferred, and minutes already allocated to another Offset request are excluded. Approved OT remains payable separately.',
            ]);
        }

        $requestedMinutes = max(1, (int) round(((float) $validated['offset_hours']) * 60));

        if ($requestedMinutes > $availableMinutes) {
            return back()->withInput()->withErrors([
                'offset_hours' => sprintf(
                    'Requested Offset is %.2f hour(s), but only %.2f unused excess hour(s) are available from the selected source date.',
                    $requestedMinutes / 60,
                    $availableMinutes / 60
                ),
            ]);
        }

        $targetCapacityMinutes = $this->offsetTargetCapacityMinutes(
            $employeeBiometricId,
            $targetDate->toDateString(),
            $ignoreAdjustmentId
        );

        if ($targetCapacityMinutes !== null && $targetCapacityMinutes <= 0) {
            return back()->withInput()->withErrors([
                'work_date' => 'The selected Offset target date already has a complete payable day with no attendance shortage to cover.',
            ]);
        }

        if ($targetCapacityMinutes !== null && $requestedMinutes > $targetCapacityMinutes) {
            return back()->withInput()->withErrors([
                'offset_hours' => sprintf(
                    'The target date currently needs only %.2f hour(s) of Offset credit. Reduce the requested hours to avoid over-allocation.',
                    $targetCapacityMinutes / 60
                ),
            ]);
        }

        return [
            'offset_source_time_in' => $proof['time_in'],
            'offset_source_time_out' => $proof['time_out'],
            'offset_source_logs' => $proof['logs'],
            'approved_minutes' => $requestedMinutes,
        ];
    }

    private function resolveOffsetApprovedMinutes(
        int $employeeBiometricId,
        string $proofDate,
        ?string $timeIn,
        ?string $timeOut,
        ?int $ignoreAdjustmentId = null
    ): int {
        $summary = DailyAttendanceSummary::query()
            ->where('employee_biometric_id', $employeeBiometricId)
            ->whereDate('work_date', $proofDate)
            ->first();

        $requiredClockMinutes = $this->requiredClockMinutesForEmployee($employeeBiometricId, $proofDate);

        $sourceExcessMinutes = $summary
            ? max(0, (int) ($summary->overtime_minutes ?? 0))
            : ($requiredClockMinutes > 0
                ? $this->premiumService->offsetCreditMinutes(
                    $proofDate,
                    $timeIn,
                    $timeOut,
                    $requiredClockMinutes
                )
                : 0);

        if ($sourceExcessMinutes <= 0) {
            return 0;
        }

        /*
         * Offset is a company compensatory-leave benefit. It never cancels or
         * replaces statutory overtime pay. The same source excess can still be
         * separately paid as approved OT, but it cannot be allocated to more
         * than one Offset request.
         */
        $allocatedOffsetMinutes = PayrollAttendanceAdjustment::query()
            ->where('employee_biometric_id', $employeeBiometricId)
            ->where('adjustment_type', PayrollAttendanceAdjustment::TYPE_OFFSET)
            ->whereDate('offset_source_date', $proofDate)
            ->where('status', '!=', PayrollAttendanceAdjustment::STATUS_REJECTED)
            ->when($ignoreAdjustmentId, fn ($query) => $query->whereKeyNot($ignoreAdjustmentId))
            ->sum('approved_minutes');

        return max(
            0,
            $sourceExcessMinutes
                - max(0, (int) $allocatedOffsetMinutes)
        );
    }

    private function offsetApprovalValidationMessage(
        PayrollAttendanceAdjustment $offsetAdjustment
    ): ?string {
        $employeeBiometricId = (int) ($offsetAdjustment->employee_biometric_id ?? 0);
        $sourceDate = $offsetAdjustment->offset_source_date?->toDateString();
        $targetDate = $offsetAdjustment->work_date?->toDateString();
        $requestedMinutes = max(0, (int) ($offsetAdjustment->approved_minutes ?? 0));

        if ($employeeBiometricId <= 0 || ! $sourceDate || ! $targetDate) {
            return 'Offset cannot be approved because the employee, source date, or target date is incomplete. Edit and revalidate the Offset request first.';
        }

        if ($requestedMinutes <= 0) {
            return 'Offset cannot be approved because no compensatory hours are stored. Edit the request, enter the hours to transfer, and run Check Available Offset Credit again.';
        }

        if (Carbon::parse($sourceDate, 'Asia/Manila')->greaterThanOrEqualTo(Carbon::parse($targetDate, 'Asia/Manila'))) {
            return 'Offset source date must be earlier than the target attendance date.';
        }

        $targetSchedule = $this->scheduleForEmployeeDate($employeeBiometricId, $targetDate);

        if (! $targetSchedule) {
            return 'Offset target date has no plotted work schedule. Plot the employee schedule first, then edit/revalidate the Offset request.';
        }

        if ($targetSchedule->isDayOffOn(Carbon::parse($targetDate, 'Asia/Manila'))) {
            return "Offset target date is the employee's weekly day off. Select a scheduled working date with an attendance shortage.";
        }

        $proof = $this->biometricsProofService->findOffsetProof(
            $employeeBiometricId,
            $offsetAdjustment->biometric_employee_id,
            $offsetAdjustment->employee_no,
            $offsetAdjustment->employee_name,
            $sourceDate
        );

        if (! $proof) {
            return 'Offset cannot be approved because biometrics proof is no longer available on the source date. Edit the request and select a valid source date.';
        }

        $availableMinutes = $this->resolveOffsetApprovedMinutes(
            $employeeBiometricId,
            $sourceDate,
            $proof['time_in'] ?? null,
            $proof['time_out'] ?? null,
            (int) $offsetAdjustment->id
        );

        if ($requestedMinutes > $availableMinutes) {
            return sprintf(
                'Offset cannot be approved. Requested credit is %.2f hour(s), but only %.2f unused company Offset hour(s) remain on the source date. Edit/revalidate the request first.',
                $requestedMinutes / 60,
                $availableMinutes / 60
            );
        }

        $targetCapacityMinutes = $this->offsetTargetCapacityMinutes(
            $employeeBiometricId,
            $targetDate,
            (int) $offsetAdjustment->id
        );

        if ($targetCapacityMinutes !== null && $targetCapacityMinutes <= 0) {
            return 'Offset cannot be approved because the target date has no attendance shortage to cover.';
        }

        if ($targetCapacityMinutes !== null && $requestedMinutes > $targetCapacityMinutes) {
            return sprintf(
                'Offset cannot be approved. The target date currently needs only %.2f hour(s) of attendance credit. Edit the Offset hours before approval.',
                $targetCapacityMinutes / 60
            );
        }

        return null;
    }

    private function offsetTargetCapacityMinutes(
        int $employeeBiometricId,
        string $targetDate,
        ?int $ignoreAdjustmentId = null
    ): ?int {
        $summary = DailyAttendanceSummary::query()
            ->where('employee_biometric_id', $employeeBiometricId)
            ->whereDate('work_date', $targetDate)
            ->first();

        if (! $summary) {
            // Future/not-yet-built target: source credit is validated now.
            // Actual consumption is capped by the attendance shortage later.
            return null;
        }

        $paidMinutesPerDay = max(
            1,
            (int) data_get(
                $summary->meta,
                'paid_minutes_per_day',
                $this->paidMinutesForEmployee($employeeBiometricId, $targetDate)
            )
        );
        $currentPayableMinutes = min(
            $paidMinutesPerDay,
            max(0, (int) round(((float) ($summary->payable_hours ?? 0)) * 60))
        );
        $currentShortage = max(0, $paidMinutesPerDay - $currentPayableMinutes);

        $ownAppliedMinutes = 0;
        if (
            $ignoreAdjustmentId
            && (int) data_get($summary->meta, 'offset_adjustment_id', 0) === $ignoreAdjustmentId
        ) {
            $ownAppliedMinutes = max(0, (int) data_get($summary->meta, 'offset_applied_minutes', 0));
        }

        return min($paidMinutesPerDay, $currentShortage + $ownAppliedMinutes);
    }

    private function requiredClockMinutesForEmployee(int $employeeBiometricId, string $date): int
    {
        return $this->scheduleForEmployeeDate($employeeBiometricId, $date)?->requiredClockMinutes() ?? 0;
    }

    private function paidMinutesForEmployee(int $employeeBiometricId, string $date): int
    {
        return $this->scheduleForEmployeeDate($employeeBiometricId, $date)?->paidWorkMinutes() ?? 480;
    }

    private function lunchMinutesForEmployee(int $employeeBiometricId, string $date): int
    {
        return $this->scheduleForEmployeeDate($employeeBiometricId, $date)?->lunchBreakMinutes() ?? 60;
    }

    private function scheduleForEmployeeDate(int $employeeBiometricId, string $date): ?EmployeePlottingSchedule
    {
        $exact = EmployeePlottingSchedule::query()
            ->where('employee_biometric_id', $employeeBiometricId)
            ->whereDate('work_date', $date)
            ->latest('updated_at')
            ->latest('id')
            ->first();

        if ($exact) {
            return $exact;
        }

        return EmployeePlottingSchedule::query()
            ->where('employee_biometric_id', $employeeBiometricId)
            ->whereNull('work_date')
            ->latest('updated_at')
            ->latest('id')
            ->first();
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
            PayrollAttendanceAdjustment::TYPE_OVERTIME => 'overtime_approved_interval',
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
        $query = PayrollAttendanceAdjustment::query()
            ->where('adjustment_type', $validated['adjustment_type']);

        if ($ignoreId) {
            $query->whereKeyNot($ignoreId);
        }

        if ($this->isGlobalDisasterType($validated)) {
            return $query->whereDate('work_date', $validated['work_date'])->exists();
        }

        $dateFrom = $validated['date_from'] ?? $validated['work_date'];
        $dateTo = $validated['date_to'] ?? $validated['work_date'];

        return $query
            ->where('employee_biometric_id', (int) $validated['employee_biometric_id'])
            ->where(function ($q) use ($dateFrom, $dateTo): void {
                $q->whereRaw('COALESCE(date_from, work_date) <= ?', [$dateTo])
                    ->whereRaw('COALESCE(date_to, work_date) >= ?', [$dateFrom]);
            })
            ->exists();
    }

    private function rebuildAffectedSummary(
        PayrollAttendanceAdjustment $adjustment,
        ?array $oldRange = null
    ): void {
        if ($oldRange) {
            $this->rebuildDates($oldRange);
        }

        // OT is payroll authorization only; it does not alter attendance time.
        if ($adjustment->adjustment_type === PayrollAttendanceAdjustment::TYPE_OVERTIME) {
            return;
        }

        $this->rebuildDates($this->adjustmentDateRange($adjustment));
    }

    private function adjustmentDateRange(PayrollAttendanceAdjustment $adjustment): array
    {
        $from = $adjustment->date_from?->toDateString()
            ?? $adjustment->work_date?->toDateString();
        $to = $adjustment->date_to?->toDateString() ?? $from;

        return array_values(array_filter([$from, $to]));
    }

    private function rebuildDates(array $range): void
    {
        if ($range === []) {
            return;
        }

        $from = Carbon::parse($range[0], 'Asia/Manila');
        $to = Carbon::parse($range[1] ?? $range[0], 'Asia/Manila');

        foreach (CarbonPeriod::create($from, $to) as $date) {
            $this->dailyAttendanceSummaryService->buildForDate($date->toDateString());
        }
    }

    private function successMessage(PayrollAttendanceAdjustment $adjustment, string $action): string
    {
        return match ($adjustment->adjustment_type) {
            PayrollAttendanceAdjustment::TYPE_TYPHOON_DISASTER => 'Typhoon / Disaster adjustment '.$action.'. Active biometric employees with time-in on the selected date will be paid a whole day after summary rebuild.',
            PayrollAttendanceAdjustment::TYPE_OFFSET => $adjustment->status === PayrollAttendanceAdjustment::STATUS_PENDING
                ? 'Offset '.$action.' and is PENDING approval. The source excess time will not affect payroll until approved.'
                : 'Offset '.$action.'. Approved compensatory minutes will cover attendance shortage on the target date. No separate cash Offset payment is created.',
            PayrollAttendanceAdjustment::TYPE_OVERTIME => $adjustment->status === PayrollAttendanceAdjustment::STATUS_PENDING
                ? 'Overtime adjustment '.$action.' and is PENDING Head Manager approval. Payroll will not pay this OT until it is approved.'
                : 'Overtime adjustment '.$action.' successfully.',
            default => 'Payroll attendance adjustment '.$action.' successfully.',
        };
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
