<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\PayrollAttendanceAdjustmentRequest;
use App\Models\DailyAttendanceSummary;
use App\Models\EmployeeBiometric;
use App\Models\EmployeePlottingSchedule;
use App\Models\Payroll;
use App\Models\PayrollAttendanceAdjustment;
use App\Models\PayrollItem;
use App\Services\Biometrics\EmployeeBiometricIdentityService;
use App\Services\Payroll\BiometricsProofService;
use App\Services\Payroll\DailyAttendanceSummaryService;
use App\Services\Payroll\PayrollComputationService;
use App\Services\Payroll\PayrollPremiumService;
use App\Support\Payroll\OvertimeCheck;
use App\Support\PayrollEmployeeNameFormatter;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTimeInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollAttendanceAdjustmentController extends Controller
{
    public function __construct(
        private readonly BiometricsProofService $biometricsProofService,
        private readonly DailyAttendanceSummaryService $dailyAttendanceSummaryService,
        private readonly EmployeeBiometricIdentityService $identityService,
        private readonly PayrollPremiumService $premiumService,
        private readonly PayrollComputationService $payrollComputationService,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', PayrollAttendanceAdjustment::class);

        $search = trim((string) $request->search);
        $type = $request->type;
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $groupName = trim((string) $request->group_name);
        $status = trim((string) $request->status);

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

        // Status cards (For approval / Approved / Rejected) count every status under the other filters.
        $statusCounts = (clone $query)
            ->reorder()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $query->when(
            in_array($status, [PayrollAttendanceAdjustment::STATUS_PENDING, PayrollAttendanceAdjustment::STATUS_APPROVED, PayrollAttendanceAdjustment::STATUS_REJECTED], true),
            fn ($query) => $query->where('status', $status)
        );

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
            'disasters' => (clone $query)->whereIn('adjustment_type', PayrollAttendanceAdjustment::TYPHOON_DISASTER_TYPES)->count(),
            'pending' => (clone $query)->where('status', PayrollAttendanceAdjustment::STATUS_PENDING)->count(),
            'status_pending' => (int) ($statusCounts[PayrollAttendanceAdjustment::STATUS_PENDING] ?? 0),
            'status_approved' => (int) ($statusCounts[PayrollAttendanceAdjustment::STATUS_APPROVED] ?? 0),
            'status_rejected' => (int) ($statusCounts[PayrollAttendanceAdjustment::STATUS_REJECTED] ?? 0),
        ];

        $adjustments = $query
            ->orderByRaw('COALESCE(date_from, work_date) DESC')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $user = $request->user();

        return Inertia::render('payroll/adjustments/index', [
            'adjustments' => $adjustments->through(
                fn (PayrollAttendanceAdjustment $item): array => $this->adjustmentListRow($item)
            ),
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'type' => (string) ($type ?? ''),
                'date_from' => (string) ($dateFrom ?? ''),
                'date_to' => (string) ($dateTo ?? ''),
                'group_name' => $groupName,
                'status' => $status,
            ],
            'groups' => ['1' => 'Mirasol / Balintawak Payroll', '2' => 'Gonzales Payroll'],
            'types' => PayrollAttendanceAdjustment::TYPES,
            'can' => [
                'create' => $user->can('create', PayrollAttendanceAdjustment::class),
                'update' => $user->can('payroll-attendance-adjustments.update'),
                'delete' => $user->can('payroll-attendance-adjustments.delete'),
                'approve' => $user->can('payroll.finalize'),
            ],
            'urls' => [
                'index' => route('payroll-attendance-adjustments.index'),
                'create' => route('payroll-attendance-adjustments.create'),
            ],
        ]);
    }

    /**
     * One row of the adjustment list, with every label the Blade table showed.
     */
    private function adjustmentListRow(PayrollAttendanceAdjustment $item): array
    {
        $type = (string) $item->adjustment_type;
        $isOffset = $type === PayrollAttendanceAdjustment::TYPE_OFFSET;
        $isOvertime = $type === PayrollAttendanceAdjustment::TYPE_OVERTIME;
        $isSalaryAdjustment = $type === PayrollAttendanceAdjustment::TYPE_CASH_ADJUSTMENT;
        $approvalNoun = $isOvertime ? 'OT' : ($isSalaryAdjustment ? 'Salary Adjustment' : 'Offset');

        $effect = match (true) {
            $isOffset => 'Comp Time Credit',
            $isOvertime => $item->status === PayrollAttendanceAdjustment::STATUS_APPROVED ? 'OT Pay Authorized' : 'No OT Pay Yet',
            $type === PayrollAttendanceAdjustment::TYPE_HOLIDAY_WORK => 'Holiday Premium',
            (bool) $item->is_paid => 'Paid Attendance',
            default => 'Attendance Rule Only',
        };

        return [
            'id' => $item->id,
            'is_disaster' => $item->isGlobalDisasterAdjustment(),
            'disaster_hours' => PayrollAttendanceAdjustment::typhoonDisasterRequiredHours($type),
            'employee' => [
                'name' => $item->payroll_display_name,
                'employee_no' => $item->employee_no,
                'employee_biometric_id' => $item->employee_biometric_id,
                'biometric_employee_id' => $item->biometric_employee_id,
            ],
            'type' => $type,
            'type_label' => $item->type_label,
            'status' => $item->status ?: PayrollAttendanceAdjustment::STATUS_APPROVED,
            'period_label' => $item->period_label,
            'day_type_label' => $item->adjusted_day_type ? Str::headline($item->adjusted_day_type) : 'Standard day',
            'adjusted_time_label' => $item->adjusted_time_label,
            'offset' => $isOffset ? [
                'proof_label' => $item->offset_proof_label,
                'approved_hours' => $item->approved_minutes ? round($item->approved_minutes / 60, 2) : null,
                'paid_payroll_number' => $item->paidPayroll?->payroll_number,
            ] : null,
            'effect' => $effect,
            'effect_positive' => ! ($isOvertime && $item->status !== PayrollAttendanceAdjustment::STATUS_APPROVED),
            'ignore_late' => (bool) $item->ignore_late,
            'ignore_undertime' => (bool) $item->ignore_undertime,
            'encoder_name' => $item->encoder?->name,
            // Who clicked Approve / Reject, and when.
            'decision' => match (true) {
                $item->status === PayrollAttendanceAdjustment::STATUS_APPROVED && $item->approved_by => [
                    'by' => $item->approver?->full_name ?: ($item->approver?->name ?: 'Unknown user'),
                    'at' => $item->approved_at?->timezone('Asia/Manila')->format('M d, Y h:i A'),
                ],
                $item->status === PayrollAttendanceAdjustment::STATUS_REJECTED && $item->rejected_by => [
                    'by' => $item->rejector?->full_name ?: ($item->rejector?->name ?: 'Unknown user'),
                    'at' => $item->rejected_at?->timezone('Asia/Manila')->format('M d, Y h:i A'),
                    'reason' => $item->rejection_reason,
                ],
                default => null,
            },
            'attachment' => $item->attachment_path ? [
                'name' => (string) $item->attachment_name,
                'url' => route('payroll-attendance-adjustments.attachment', $item),
            ] : null,
            'encoded_at' => $item->encoded_at?->timezone('Asia/Manila')->format('M d, Y h:i A'),
            'can_decide' => $item->isApprovalRequired() && $item->status === PayrollAttendanceAdjustment::STATUS_PENDING,
            'approve_title' => "Approve {$approvalNoun}",
            'reject_title' => "Reject {$approvalNoun}",
            'approve_confirm' => match (true) {
                $isOvertime => 'Approve this overtime adjustment for payroll payment?',
                $isSalaryAdjustment => 'Approve this Salary Adjustment of '.$item->adjusted_time_label.'?',
                default => 'Approve this Offset credit and apply it to the target attendance date?',
            },
            'reject_confirm' => match (true) {
                $isOvertime => 'Reject this overtime adjustment? It will not be paid.',
                $isSalaryAdjustment => 'Reject this Salary Adjustment? It will not be applied to payroll.',
                default => 'Reject this Offset request? No compensatory credit will be applied.',
            },
            'urls' => [
                'edit' => route('payroll-attendance-adjustments.edit', $item),
                'destroy' => route('payroll-attendance-adjustments.destroy', $item),
                'approve' => route('payroll-attendance-adjustments.approve', $item),
                'reject' => route('payroll-attendance-adjustments.reject', $item),
            ],
        ];
    }

    public function create(): Response
    {
        $this->authorize('create', PayrollAttendanceAdjustment::class);

        return $this->renderForm(null);
    }

    /**
     * Shared React form for create and edit. The same store/update endpoints,
     * validation and payroll rules are used as before.
     */
    private function renderForm(?PayrollAttendanceAdjustment $adjustment): Response
    {
        $time = fn (?string $value): string => $value ? substr($value, 0, 5) : '';

        return Inertia::render('payroll/adjustments/form', [
            'adjustment' => $adjustment ? [
                'id' => $adjustment->id,
                'employee_biometric_id' => $adjustment->employee_biometric_id,
                'adjustment_type' => (string) $adjustment->adjustment_type,
                'work_date' => $adjustment->work_date?->toDateString() ?? '',
                'date_from' => $adjustment->date_from?->toDateString() ?? '',
                'date_to' => $adjustment->date_to?->toDateString() ?? '',
                'adjusted_time_in' => $time($adjustment->adjusted_time_in),
                'adjusted_time_out' => $time($adjustment->adjusted_time_out),
                'offset_sources' => collect($adjustment->resolvedOffsetSources())
                    ->map(fn (array $source): array => [
                        'date' => $source['date'],
                        'hours' => number_format($source['minutes'] / 60, 2, '.', ''),
                    ])
                    ->values()
                    ->all(),
                'amount' => $adjustment->amount !== null ? (string) $adjustment->amount : '',
                'is_paid' => (bool) $adjustment->is_paid,
                'ignore_late' => (bool) $adjustment->ignore_late,
                'ignore_undertime' => (bool) $adjustment->ignore_undertime,
                'reason' => (string) ($adjustment->reason ?? ''),
                'remarks' => (string) ($adjustment->remarks ?? ''),
                'status' => $adjustment->status,
                'is_locked' => (bool) $adjustment->paid_payroll_id,
                'attachment' => $adjustment->attachment_path ? [
                    'name' => (string) $adjustment->attachment_name,
                    'url' => route('payroll-attendance-adjustments.attachment', $adjustment),
                ] : null,
            ] : null,
            'people' => $this->getBiometricsPeople()
                ->map(fn (object $person): array => [
                    'employee_biometric_id' => (int) $person->employee_biometric_id,
                    'biometric_employee_id' => $person->biometric_employee_id,
                    'employee_no' => $person->employee_no,
                    'employee_name' => $person->employee_name,
                    'display_name' => PayrollEmployeeNameFormatter::display($person->employee_name),
                    'crosschex_id' => $person->crosschex_id,
                    'group_name' => $person->group_name !== null ? (string) $person->group_name : null,
                ])
                ->values(),
            'types' => PayrollAttendanceAdjustment::TYPES,
            'typeRules' => collect(PayrollAttendanceAdjustment::TYPES)
                ->keys()
                ->mapWithKeys(fn (string $type): array => [$type => PayrollAttendanceAdjustment::rulesFor($type)]),
            'urls' => [
                'index' => route('payroll-attendance-adjustments.index'),
                'submit' => $adjustment
                    ? route('payroll-attendance-adjustments.update', $adjustment)
                    : route('payroll-attendance-adjustments.store'),
                'offsetProof' => route('payroll-attendance-adjustments.offset-proof'),
                'overtimeCheck' => route('payroll-attendance-adjustments.overtime-check'),
            ],
        ]);
    }

    public function store(PayrollAttendanceAdjustmentRequest $request): RedirectResponse
    {
        $this->authorize('create', PayrollAttendanceAdjustment::class);

        $validated = $request->validated();

        if ($this->hasDuplicateAdjustment($validated)) {
            return back()->withInput()->withErrors([
                'work_date' => $this->isGlobalDisasterType($validated)
                    ? 'A Typhoon / Disaster adjustment already exists for this work date.'
                    : 'The same adjustment type already exists for this employee within the selected date range.',
            ]);
        }

        $recomputePayrollItemId = (int) $request->input('recompute_payroll_item_id', 0);

        /*
         * The "File Adjustment" modal on a payroll item page locks the date
         * pickers to that draft's cutoff via HTML min/max, but that is only
         * a UI hint. Enforce it here too so a submitted date cannot land
         * outside the payroll the user was actually looking at.
         */
        if ($recomputePayrollItemId > 0) {
            $dateRangeError = $this->validateDateWithinPayrollPeriod($validated, $recomputePayrollItemId);

            if ($dateRangeError) {
                return back()->withInput()->withErrors(['work_date' => $dateRangeError]);
            }
        }

        $payload = $this->buildPayload($validated, $request) + $this->storeOtForm($request);

        if ($validated['adjustment_type'] === PayrollAttendanceAdjustment::TYPE_OFFSET) {
            $offset = $this->buildOffsetProofPayload($validated);

            if ($offset instanceof RedirectResponse) {
                return $offset;
            }

            $payload = array_merge($payload, $offset);
        }

        $adjustment = DB::transaction(fn () => PayrollAttendanceAdjustment::create($payload));
        $this->rebuildAffectedSummary($adjustment);

        $message = $this->successMessage($adjustment, 'saved');

        /*
         * "File adjustment" modal on the payroll item detail page submits
         * here with this hidden field set, so the user can save an
         * adjustment for that one employee and see it reflected immediately
         * without regenerating the whole draft payroll. Only that single
         * PayrollItem is recomputed; every other employee's item is left
         * untouched.
         */
        $recomputeItem = $this->recomputeLinkedPayrollItem($recomputePayrollItemId, $adjustment);

        if ($recomputeItem) {
            [$item, $recomputeError] = $recomputeItem;

            $message .= $recomputeError
                ? ' However, automatic payroll recompute failed: '.$recomputeError.' You can retry from the payroll item page.'
                : ' '.$item->payroll_display_name.'\'s payroll computation was automatically recomputed. Other employees in this payroll were not affected.';

            return redirect()
                ->route('payroll.items.show', [$item->payroll_id, $item->id])
                ->with('success', $message);
        }

        return redirect()
            ->route('payroll-attendance-adjustments.index')
            ->with('success', $message);
    }

    private function validateDateWithinPayrollPeriod(array $validated, int $payrollItemId): ?string
    {
        $item = PayrollItem::with('payroll')->find($payrollItemId);

        if (! $item || ! $item->payroll) {
            return null;
        }

        $periodStart = $this->dateString($item->payroll->period_start);
        $periodEnd = $this->dateString($item->payroll->period_end);

        if (! $periodStart || ! $periodEnd) {
            return null;
        }

        $rules = PayrollAttendanceAdjustment::rulesFor((string) ($validated['adjustment_type'] ?? ''));
        $isLeave = ($rules['date_mode'] ?? 'single') === 'range';

        $dateFrom = $isLeave ? ($validated['date_from'] ?? null) : ($validated['work_date'] ?? null);
        $dateTo = $isLeave ? ($validated['date_to'] ?? null) : $dateFrom;

        if (! $dateFrom || ! $dateTo) {
            return null;
        }

        if ($dateFrom <= $periodEnd && $dateTo >= $periodStart) {
            return null;
        }

        return sprintf(
            'The selected date must fall within this payroll\'s cutoff (%s - %s).',
            Carbon::parse($periodStart)->format('M d, Y'),
            Carbon::parse($periodEnd)->format('M d, Y')
        );
    }

    /**
     * @return array{0: PayrollItem, 1: string|null}|null Null when the item
     *                                                    id is absent/invalid/not authorized for recompute (e.g. a normal,
     *                                                    non-modal adjustment submission). Otherwise the item and, if the
     *                                                    recompute itself failed, the error message.
     */
    private function recomputeLinkedPayrollItem(int $payrollItemId, PayrollAttendanceAdjustment $adjustment): ?array
    {
        if ($payrollItemId <= 0) {
            return null;
        }

        $item = PayrollItem::with('payroll')->find($payrollItemId);

        if (
            ! $item
            || ! $item->payroll
            || (int) $item->employee_biometric_id !== (int) $adjustment->employee_biometric_id
            || ! auth()->user()?->can('create', Payroll::class)
        ) {
            return null;
        }

        try {
            $this->payrollComputationService->recomputeItem($item->payroll, $item, auth()->id());

            return [$item, null];
        } catch (\Throwable $exception) {
            return [$item, $exception->getMessage()];
        }
    }

    public function edit(PayrollAttendanceAdjustment $payrollAttendanceAdjustment): Response
    {
        $this->authorize('update', $payrollAttendanceAdjustment);

        return $this->renderForm($payrollAttendanceAdjustment);
    }

    public function update(
        PayrollAttendanceAdjustmentRequest $request,
        PayrollAttendanceAdjustment $payrollAttendanceAdjustment
    ): RedirectResponse {
        $this->authorize('update', $payrollAttendanceAdjustment);

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
        $payload = $this->buildPayload($validated, $request, $payrollAttendanceAdjustment) + $this->storeOtForm($request, $payrollAttendanceAdjustment);

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
        $this->authorize('approve', $payrollAttendanceAdjustment);

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
        $this->authorize('reject', $payrollAttendanceAdjustment);

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
        $this->authorize('delete', $payrollAttendanceAdjustment);

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

    /** Live OT checker for the form (same rules as the save). */
    public function overtimeCheck(Request $request, OvertimeCheck $check): JsonResponse
    {
        $this->authorize('offsetProof', PayrollAttendanceAdjustment::class);

        $validated = $request->validate([
            'employee_biometric_id' => ['required', 'integer', 'exists:employee_biometrics,id'],
            'biometric_employee_id' => ['nullable', 'string'],
            'employee_no' => ['nullable', 'string'],
            'employee_name' => ['required', 'string'],
            'work_date' => ['required', 'date'],
            'adjusted_time_in' => ['required', 'date_format:H:i'],
            'adjusted_time_out' => ['required', 'date_format:H:i', 'different:adjusted_time_in'],
            'adjustment_id' => ['nullable', 'integer'],
        ]);

        return response()->json($check->check(
            (int) $validated['employee_biometric_id'],
            $validated['biometric_employee_id'] ?? null,
            $validated['employee_no'] ?? null,
            $validated['employee_name'],
            $validated['work_date'],
            $validated['adjusted_time_in'],
            $validated['adjusted_time_out'],
            isset($validated['adjustment_id']) ? (int) $validated['adjustment_id'] : null,
        ));
    }

    /** The uploaded approved OT form (private file, shown inline). */
    public function attachment(PayrollAttendanceAdjustment $payrollAttendanceAdjustment): StreamedResponse
    {
        $this->authorize('viewAny', PayrollAttendanceAdjustment::class);

        $path = (string) $payrollAttendanceAdjustment->attachment_path;
        abort_if($path === '' || ! Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, $payrollAttendanceAdjustment->attachment_name ?: basename($path), [
            'Content-Type' => $payrollAttendanceAdjustment->attachment_mime ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /** Saves a newly uploaded OT form (replacing the previous one). */
    private function storeOtForm(Request $request, ?PayrollAttendanceAdjustment $existing = null): array
    {
        $file = $request->file('ot_form');

        if (! $file) {
            return [];
        }

        $path = $file->store('payroll/ot-forms', 'local');

        if ($existing?->attachment_path && $existing->attachment_path !== $path) {
            Storage::disk('local')->delete($existing->attachment_path);
        }

        return [
            'attachment_path' => $path,
            'attachment_name' => mb_substr($file->getClientOriginalName(), 0, 255),
            'attachment_mime' => $file->getMimeType(),
            'attachment_size' => (int) $file->getSize(),
        ];
    }

    public function offsetProof(Request $request): JsonResponse
    {
        $this->authorize('offsetProof', PayrollAttendanceAdjustment::class);

        $validated = $request->validate([
            'employee_biometric_id' => ['required', 'integer', 'exists:employee_biometrics,id'],
            'biometric_employee_id' => ['nullable', 'string'],
            'employee_no' => ['nullable', 'string'],
            'employee_name' => ['required', 'string'],
            'work_date' => ['required', 'date'],
            // Multi-date Offset. The legacy single-date fields are still
            // accepted so older pages keep working.
            'offset_sources' => ['nullable', 'array', 'max:31'],
            'offset_sources.*.date' => ['required', 'date', 'before:work_date', 'distinct'],
            'offset_sources.*.hours' => ['required', 'numeric', 'min:0.01', 'max:24'],
            'offset_source_date' => ['required_without:offset_sources', 'nullable', 'date', 'before:work_date'],
            'offset_hours' => ['required_without:offset_sources', 'nullable', 'numeric', 'min:0.01', 'max:24'],
            'adjustment_id' => ['nullable', 'integer', 'exists:payroll_attendance_adjustments,id'],
        ]);

        $sources = ! empty($validated['offset_sources'])
            ? $validated['offset_sources']
            : [['date' => $validated['offset_source_date'], 'hours' => $validated['offset_hours']]];

        $result = $this->evaluateOffsetSources(
            (int) $validated['employee_biometric_id'],
            $validated['biometric_employee_id'] ?? null,
            $validated['employee_no'] ?? null,
            $validated['employee_name'],
            $validated['work_date'],
            $sources,
            isset($validated['adjustment_id']) ? (int) $validated['adjustment_id'] : null
        );

        $firstProof = $result['sources'][0]['proof'] ?? null;
        $proof = is_array($firstProof) ? $firstProof : [];
        $proof['requested_minutes'] = $result['requested_minutes'];
        $proof['requested_hours'] = round($result['requested_minutes'] / 60, 2);
        $proof['available_minutes'] = $result['available_minutes'];
        $proof['available_hours'] = round($result['available_minutes'] / 60, 2);
        $proof['approved_minutes'] = $result['available_minutes']; // backwards-compatible UI key
        $proof['approved_hours'] = $proof['available_hours'];
        $proof['target_capacity_minutes'] = $result['target_capacity_minutes'];
        $proof['target_capacity_hours'] = $result['target_capacity_minutes'] === null
            ? null
            : round($result['target_capacity_minutes'] / 60, 2);
        $proof['target_date'] = Carbon::parse($validated['work_date'], 'Asia/Manila')->toDateString();

        $payload = [
            'found' => $result['error'] === null,
            'message' => $result['error']['message']
                ?? (count($result['sources']) > 1
                    ? sprintf(
                        'Offset is valid for review. %d source dates provide %.2f hour(s) of credit for the target date.',
                        count($result['sources']),
                        $result['requested_minutes'] / 60
                    )
                    : 'Offset is valid for review. Source excess, requested hours, and target attendance capacity are within the allowed limits.'),
            'proof' => $proof,
            'sources' => array_map(fn (array $source): array => [
                'date' => $source['date'],
                'requested_minutes' => $source['requested_minutes'],
                'requested_hours' => round($source['requested_minutes'] / 60, 2),
                'available_minutes' => $source['available_minutes'],
                'available_hours' => round($source['available_minutes'] / 60, 2),
                'time_in' => $source['proof']['time_in'] ?? null,
                'time_out' => $source['proof']['time_out'] ?? null,
                'has_proof' => $source['proof'] !== null,
                'error' => $source['error'],
            ], $result['sources']),
        ];

        if ($result['error'] === null) {
            return response()->json($payload);
        }

        return response()->json($payload, $result['error']['status'] ?? 422);
    }

    private function buildPayload(
        array $validated,
        Request $request,
        ?PayrollAttendanceAdjustment $existing = null
    ): array {
        $type = $validated['adjustment_type'];
        $rules = PayrollAttendanceAdjustment::rulesFor($type);
        $isLeave = ($rules['date_mode'] ?? 'single') === 'range';
        $isGlobalDisaster = PayrollAttendanceAdjustment::isTyphoonDisasterType($type);
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
                || (string) $this->dateString($existing->work_date) !== (string) $workDate
                || (string) $existing->adjusted_time_in !== (string) ($validated['adjusted_time_in'] ?? '')
                || (string) $existing->adjusted_time_out !== (string) ($validated['adjusted_time_out'] ?? '')
                || ($type === PayrollAttendanceAdjustment::TYPE_OFFSET
                    && (
                        (string) $this->dateString($existing->offset_source_date) !== (string) ($validated['offset_source_date'] ?? '')
                        || (int) ($existing->approved_minutes ?? 0) !== (int) $requestedOffsetMinutes
                        || $this->offsetSourceSignature($existing->resolvedOffsetSources())
                            !== $this->offsetSourceSignature($validated['offset_sources'] ?? [])
                    ))
                || ($type === PayrollAttendanceAdjustment::TYPE_CASH_ADJUSTMENT
                    && round((float) ($existing->amount ?? 0), 2) !== round((float) ($validated['amount'] ?? 0), 2));

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
            'offset_sources' => null,
            'approved_minutes' => null,
            'amount' => $type === PayrollAttendanceAdjustment::TYPE_CASH_ADJUSTMENT
                ? round((float) ($validated['amount'] ?? 0), 2)
                : null,
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

    /**
     * Order-independent "date=minutes" list used to detect edited sources.
     */
    private function offsetSourceSignature(array $sources): string
    {
        return collect($sources)
            ->filter(fn (mixed $row): bool => is_array($row) && filled($row['date'] ?? null))
            ->map(fn (array $row): string => Carbon::parse($row['date'], 'Asia/Manila')->toDateString().'='.(
                array_key_exists('minutes', $row)
                    ? (int) $row['minutes']
                    : (int) round(((float) ($row['hours'] ?? 0)) * 60)
            ))
            ->sort()
            ->implode(',');
    }

    private function buildOffsetProofPayload(array $validated, ?int $ignoreAdjustmentId = null): array|RedirectResponse
    {
        $result = $this->evaluateOffsetSources(
            (int) $validated['employee_biometric_id'],
            $validated['biometric_employee_id'] ?? null,
            $validated['employee_no'] ?? null,
            $validated['employee_name'],
            $validated['work_date'],
            $validated['offset_sources'] ?? [],
            $ignoreAdjustmentId
        );

        if ($result['error'] !== null) {
            return back()->withInput()->withErrors([
                $result['error']['field'] => $result['error']['message'],
            ]);
        }

        $sources = $result['sources'];
        $earliest = $sources[0];

        return [
            'offset_source_date' => $earliest['date'],
            'offset_source_time_in' => $earliest['proof']['time_in'] ?? null,
            'offset_source_time_out' => $earliest['proof']['time_out'] ?? null,
            'offset_source_logs' => $earliest['proof']['logs'] ?? null,
            'offset_sources' => array_map(fn (array $source): array => [
                'date' => $source['date'],
                'minutes' => $source['requested_minutes'],
                'time_in' => $source['proof']['time_in'] ?? null,
                'time_out' => $source['proof']['time_out'] ?? null,
            ], $sources),
            'approved_minutes' => $result['requested_minutes'],
        ];
    }

    /**
     * Validates an Offset that pools excess time from one or more earlier
     * source dates into a single target date. Every source date needs
     * biometric proof and enough unallocated excess time, and the pooled
     * total may not exceed the target date's attendance shortage.
     *
     * @param  array<int, array{date: string, hours?: mixed, minutes?: mixed}>  $sourceRows
     * @return array{
     *     error: array{field: string, message: string, status?: int}|null,
     *     sources: array<int, array{date: string, requested_minutes: int, available_minutes: int, proof: ?array, error: ?string}>,
     *     requested_minutes: int,
     *     available_minutes: int,
     *     target_capacity_minutes: ?int
     * }
     */
    private function evaluateOffsetSources(
        int $employeeBiometricId,
        ?string $biometricEmployeeId,
        ?string $employeeNo,
        string $employeeName,
        string $targetDate,
        array $sourceRows,
        ?int $ignoreAdjustmentId = null
    ): array {
        $result = [
            'error' => null,
            'sources' => [],
            'requested_minutes' => 0,
            'available_minutes' => 0,
            'target_capacity_minutes' => null,
        ];

        $fail = function (string $field, string $message, int $status = 422) use (&$result): void {
            $result['error'] ??= ['field' => $field, 'message' => $message, 'status' => $status];
        };

        $target = Carbon::parse($targetDate, 'Asia/Manila')->startOfDay();
        $targetSchedule = $this->scheduleForEmployeeDate($employeeBiometricId, $target->toDateString());

        if (! $targetSchedule) {
            $fail('work_date', 'Offset target date has no plotted work schedule. Plot the employee schedule first before applying compensatory time.');

            return $result;
        }

        if ($targetSchedule->isDayOffOn($target)) {
            $fail('work_date', "Offset cannot be targeted to the employee's weekly day off. Select a scheduled working date with an attendance shortage.");

            return $result;
        }

        $rows = collect($sourceRows)
            ->filter(fn (mixed $row): bool => is_array($row) && filled($row['date'] ?? null))
            ->map(fn (array $row): array => [
                'date' => Carbon::parse($row['date'], 'Asia/Manila')->toDateString(),
                'minutes' => array_key_exists('minutes', $row)
                    ? max(0, (int) $row['minutes'])
                    : max(0, (int) round(((float) ($row['hours'] ?? 0)) * 60)),
            ])
            ->sortBy('date')
            ->values();

        if ($rows->isEmpty()) {
            $fail('offset_sources', 'Please add at least one earlier source date containing excess work time for this Offset.');

            return $result;
        }

        if ($rows->pluck('date')->duplicates()->isNotEmpty()) {
            $fail('offset_sources', 'The same Offset source date is listed more than once.');

            return $result;
        }

        foreach ($rows as $row) {
            $sourceDate = $row['date'];
            $label = Carbon::parse($sourceDate, 'Asia/Manila')->format('M d, Y');
            $requestedMinutes = $row['minutes'];
            $entry = [
                'date' => $sourceDate,
                'requested_minutes' => $requestedMinutes,
                'available_minutes' => 0,
                'proof' => null,
                'error' => null,
            ];

            if ($requestedMinutes <= 0) {
                $entry['error'] = "Enter the hours to transfer from {$label}.";
                $fail('offset_sources', $entry['error']);
            } elseif (Carbon::parse($sourceDate, 'Asia/Manila')->greaterThanOrEqualTo($target)) {
                $entry['error'] = "Offset source date {$label} must be earlier than the target attendance date.";
                $fail('offset_sources', $entry['error']);
            } else {
                $proof = $this->biometricsProofService->findOffsetProof(
                    $employeeBiometricId,
                    $biometricEmployeeId,
                    $employeeNo,
                    $employeeName,
                    $sourceDate
                );

                $entry['proof'] = $proof;

                if (! $proof) {
                    $entry['error'] = "No biometric logs found for the selected employee on {$label}.";
                    $fail('offset_sources', $entry['error'], 404);
                } else {
                    $availableMinutes = $this->resolveOffsetApprovedMinutes(
                        $employeeBiometricId,
                        $sourceDate,
                        $proof['time_in'] ?? null,
                        $proof['time_out'] ?? null,
                        $ignoreAdjustmentId
                    );

                    $entry['available_minutes'] = $availableMinutes;

                    if ($availableMinutes <= 0) {
                        $entry['error'] = "{$label} has biometric proof but no unused excess work time. Only time beyond the required shift can be transferred, and minutes already allocated to another Offset request are excluded.";
                        $fail('offset_sources', $entry['error']);
                    } elseif ($requestedMinutes > $availableMinutes) {
                        $entry['error'] = sprintf(
                            'Requested %.2f hour(s) from %s, but only %.2f unused excess hour(s) are available on that date.',
                            $requestedMinutes / 60,
                            $label,
                            $availableMinutes / 60
                        );
                        $fail('offset_sources', $entry['error']);
                    }
                }
            }

            $result['sources'][] = $entry;
            $result['requested_minutes'] += $requestedMinutes;
            $result['available_minutes'] += $entry['available_minutes'];
        }

        $targetCapacityMinutes = $this->offsetTargetCapacityMinutes(
            $employeeBiometricId,
            $target->toDateString(),
            $ignoreAdjustmentId
        );
        $result['target_capacity_minutes'] = $targetCapacityMinutes;

        if ($targetCapacityMinutes !== null && $targetCapacityMinutes <= 0) {
            $fail('work_date', 'The selected Offset target date already has a complete payable day with no attendance shortage to cover.');
        } elseif ($targetCapacityMinutes !== null && $result['requested_minutes'] > $targetCapacityMinutes) {
            $fail('offset_sources', sprintf(
                'The target date needs only %.2f hour(s) of Offset credit, but %.2f hour(s) were requested in total. Reduce the hours to avoid over-allocation.',
                $targetCapacityMinutes / 60,
                $result['requested_minutes'] / 60
            ));
        }

        return $result;
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
        return max(
            0,
            $sourceExcessMinutes
                - $this->allocatedOffsetMinutes($employeeBiometricId, $proofDate, $ignoreAdjustmentId)
        );
    }

    /**
     * Minutes of one source date already reserved by other (non-rejected)
     * Offset requests, including multi-date requests that list this date.
     */
    private function allocatedOffsetMinutes(
        int $employeeBiometricId,
        string $proofDate,
        ?int $ignoreAdjustmentId = null
    ): int {
        return (int) PayrollAttendanceAdjustment::query()
            ->where('employee_biometric_id', $employeeBiometricId)
            ->where('adjustment_type', PayrollAttendanceAdjustment::TYPE_OFFSET)
            ->where('status', '!=', PayrollAttendanceAdjustment::STATUS_REJECTED)
            // offset_source_date holds the earliest source; the target date is
            // always later than every source.
            ->whereDate('offset_source_date', '<=', $proofDate)
            ->whereDate('work_date', '>', $proofDate)
            ->when($ignoreAdjustmentId, fn ($query) => $query->whereKeyNot($ignoreAdjustmentId))
            ->get()
            ->sum(fn (PayrollAttendanceAdjustment $offset): int => (int) collect($offset->resolvedOffsetSources())
                ->where('date', $proofDate)
                ->sum('minutes'));
    }

    private function offsetApprovalValidationMessage(
        PayrollAttendanceAdjustment $offsetAdjustment
    ): ?string {
        $employeeBiometricId = (int) ($offsetAdjustment->employee_biometric_id ?? 0);
        $targetDate = $this->dateString($offsetAdjustment->work_date);
        $sources = $offsetAdjustment->resolvedOffsetSources();

        if ($employeeBiometricId <= 0 || $sources === [] || ! $targetDate) {
            return 'Offset cannot be approved because the employee, source date, or target date is incomplete. Edit and revalidate the Offset request first.';
        }

        if ((int) ($offsetAdjustment->approved_minutes ?? 0) <= 0 || collect($sources)->sum('minutes') <= 0) {
            return 'Offset cannot be approved because no compensatory hours are stored. Edit the request, enter the hours to transfer, and run Check Available Offset Credit again.';
        }

        $result = $this->evaluateOffsetSources(
            $employeeBiometricId,
            $offsetAdjustment->biometric_employee_id,
            $offsetAdjustment->employee_no,
            (string) $offsetAdjustment->employee_name,
            $targetDate,
            $sources,
            (int) $offsetAdjustment->id
        );

        return $result['error'] === null
            ? null
            : 'Offset cannot be approved. '.$result['error']['message'].' Edit/revalidate the request first.';
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
        if (PayrollAttendanceAdjustment::isTyphoonDisasterType($type)) {
            return 'typhoon_disaster';
        }

        return match ($type) {
            PayrollAttendanceAdjustment::TYPE_SICK_LEAVE => 'sick_leave',
            PayrollAttendanceAdjustment::TYPE_MEDICAL_LEAVE => 'medical_leave',
            PayrollAttendanceAdjustment::TYPE_CHANGE_SCHEDULE => 'change_schedule',
            PayrollAttendanceAdjustment::TYPE_OFFSET => 'offset',
            PayrollAttendanceAdjustment::TYPE_OFFICIAL_BUSINESS => 'official_business',
            PayrollAttendanceAdjustment::TYPE_HOLIDAY_WORK => 'holiday_work',
            PayrollAttendanceAdjustment::TYPE_OVERTIME => 'overtime_approved_interval',
            PayrollAttendanceAdjustment::TYPE_CASH_ADJUSTMENT => 'cash_adjustment',
            default => 'adjustment',
        };
    }

    private function getBiometricsPeople()
    {
        return EmployeeBiometric::query()
            ->payrollActive()
            ->payrollDirectoryOrder()
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

        if ($this->isGlobalDisasterType($validated)) {
            $query->whereIn('adjustment_type', PayrollAttendanceAdjustment::TYPHOON_DISASTER_TYPES);
        } else {
            $query->where('adjustment_type', $validated['adjustment_type']);
        }

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

        // OT is payroll authorization only, and Salary Adjustment is a plain
        // signed amount; neither one alters attendance time.
        if (in_array($adjustment->adjustment_type, [
            PayrollAttendanceAdjustment::TYPE_OVERTIME,
            PayrollAttendanceAdjustment::TYPE_CASH_ADJUSTMENT,
        ], true)) {
            return;
        }

        $this->rebuildDates($this->adjustmentDateRange($adjustment));
    }

    private function adjustmentDateRange(PayrollAttendanceAdjustment $adjustment): array
    {
        $from = $this->dateString($adjustment->date_from)
            ?? $this->dateString($adjustment->work_date);
        $to = $this->dateString($adjustment->date_to) ?? $from;

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

    private function dateString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->toDateString();
        }

        $date = trim((string) $value);

        return $date === ''
            ? null
            : Carbon::parse($date, 'Asia/Manila')->toDateString();
    }

    private function successMessage(PayrollAttendanceAdjustment $adjustment, string $action): string
    {
        if ($adjustment->isGlobalDisasterAdjustment()) {
            $requiredHours = PayrollAttendanceAdjustment::typhoonDisasterRequiredHours($adjustment->adjustment_type) ?? 3;

            return sprintf(
                'Typhoon / Disaster %dhrs adjustment %s. Employees with a valid biometric time-in/time-out pair and at least %d completed paid work hour(s) are paid a full day; employees below the threshold remain on normal attendance computation.',
                $requiredHours,
                $action,
                $requiredHours
            );
        }

        return match ($adjustment->adjustment_type) {
            PayrollAttendanceAdjustment::TYPE_OFFSET => $adjustment->status === PayrollAttendanceAdjustment::STATUS_PENDING
                ? 'Offset '.$action.' and is PENDING approval. The source excess time will not affect payroll until approved.'
                : 'Offset '.$action.'. Approved compensatory minutes will cover attendance shortage on the target date. No separate cash Offset payment is created.',
            PayrollAttendanceAdjustment::TYPE_OVERTIME => $adjustment->status === PayrollAttendanceAdjustment::STATUS_PENDING
                ? 'Overtime adjustment '.$action.' and is PENDING Head Manager approval. Payroll will not pay this OT until it is approved.'
                : 'Overtime adjustment '.$action.' successfully.',
            PayrollAttendanceAdjustment::TYPE_CASH_ADJUSTMENT => sprintf(
                'Salary Adjustment of %s %s. It will be applied to this employee\'s pay for the cutoff containing %s.',
                $adjustment->adjusted_time_label,
                $action,
                optional($adjustment->work_date)->format('M d, Y') ?? 'the selected date'
            ),
            default => 'Payroll attendance adjustment '.$action.' successfully.',
        };
    }

    private function isGlobalDisasterType(array $validated): bool
    {
        return PayrollAttendanceAdjustment::isTyphoonDisasterType($validated['adjustment_type'] ?? null);
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
