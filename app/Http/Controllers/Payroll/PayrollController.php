<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payroll;

use App\Exports\PayrollItemsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\GeneratePayrollRequest;
use App\Models\DailyAttendanceSummary;
use App\Models\Payroll;
use App\Models\PayrollAttendanceAdjustment;
use App\Models\PayrollItem;
use App\Services\Payroll\BenefitContributionPostingService;
use App\Services\Payroll\DailyAttendanceSummaryService;
use App\Services\Payroll\MonthlyGovernmentReconciliationService;
use App\Services\Payroll\PayrollComputationService;
use App\Services\Payroll\PayrollPayslipService;
use App\Services\Payroll\PayrollPeriodService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class PayrollController extends Controller
{
    public function __construct(
        protected PayrollPeriodService $periodService,
        protected PayrollComputationService $payrollComputationService,
        protected PayrollPayslipService $payrollPayslipService,
        protected BenefitContributionPostingService $benefitContributionPostingService,
        protected MonthlyGovernmentReconciliationService $monthlyGovernmentReconciliationService,
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Payroll::class);

        $search = trim((string) $request->search);
        $status = trim((string) $request->status);
        $cutoffType = trim((string) $request->cutoff_type);
        $group = trim((string) $request->garage_group);

        $payrolls = Payroll::query()
            ->with(['generator', 'finalizer'])
            ->withCount('items')
            ->when($search, function ($query) use ($search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('payroll_number', 'like', "%{$search}%")
                        ->orWhere('cutoff_type', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%");
                });
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($cutoffType, fn ($query) => $query->where('cutoff_type', $cutoffType))
            ->when($group !== '', fn ($query) => $query->where('garage_group', $group))
            // Actual period dates keep both historical records and the new
            // cycle-month cutoff convention in the correct chronological order.
            ->orderByDesc('period_end')
            ->orderByDesc('period_start')
            ->orderByRaw("CASE WHEN cutoff_type = 'first' THEN 2 WHEN cutoff_type = 'second' THEN 1 ELSE 0 END DESC")
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $user = $request->user();

        return Inertia::render('payroll/payrolls/index', [
            'payrolls' => $payrolls->through(fn (Payroll $payroll): array => [
                'id' => $payroll->id,
                'payroll_number' => $payroll->payroll_number,
                'cutoff_label' => $payroll->cutoff_label,
                'period_start' => $payroll->period_start?->format('M d, Y'),
                'period_end' => $payroll->period_end?->format('M d, Y'),
                'contribution_label' => $payroll->contribution_label,
                'items_count' => (int) ($payroll->items_count ?? 0),
                'group_label' => $payroll->garage_group_label,
                'is_finalized' => $payroll->status === 'finalized',
                'generator_name' => $payroll->generator->full_name ?? $payroll->generator->name ?? 'N/A',
                'generated_at' => $payroll->generated_at?->timezone('Asia/Manila')->format('M d, Y h:i A'),
                'urls' => [
                    'show' => route('payroll.show', $payroll),
                    'destroy' => route('payroll.destroy', $payroll),
                ],
            ]),
            'filters' => ['search' => $search, 'status' => $status, 'cutoff_type' => $cutoffType, 'garage_group' => $group],
            'payrollGroups' => ['1' => 'Mirasol / Balintawak Payroll', '2' => 'Gonzales Payroll'],
            'cutoffTypes' => [
                'second' => config('payroll.cutoff_display.second.full', '1st Cutoff (26-10)'),
                'first' => config('payroll.cutoff_display.first.full', '2nd Cutoff (11-25)'),
            ],
            'can' => [
                'create' => $user->can('payroll.create'),
                'delete' => $user->can('payroll.delete'),
            ],
            'urls' => [
                'index' => route('payroll.index'),
                'create' => route('payroll.create'),
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('create', Payroll::class);

        [
            $defaultCutoffMonth,
            $defaultCutoffYear,
            $defaultCutoffType
        ] = $this->periodService->getDefaultCutoff();

        $allowedGroups = session('payroll_allowed_groups');

        $payrollGroups = collect([
            1 => 'Mirasol / Balintawak Payroll',
            2 => 'Gonzales Payroll',
        ]);

        if ($allowedGroups !== 'all') {

            $payrollGroups = $payrollGroups->only(
                $allowedGroups ?? []
            );

        }

        $thisYear = (int) now('Asia/Manila')->year;

        return Inertia::render('payroll/payrolls/create', [
            'defaults' => [
                'cutoff_month' => (int) $defaultCutoffMonth,
                'cutoff_year' => (int) $defaultCutoffYear,
                'cutoff_type' => (string) $defaultCutoffType,
            ],
            'payrollGroups' => $payrollGroups->mapWithKeys(fn (string $label, int $value): array => [(string) $value => $label]),
            'years' => range($thisYear + 1, 2020),
            'urls' => [
                'index' => route('payroll.index'),
                'store' => route('payroll.store'),
            ],
        ]);
    }

    public function store(GeneratePayrollRequest $request): RedirectResponse
    {
        $this->authorize('create', Payroll::class);

        $validated = $request->validated();

        [$startDate, $endDate] = $this->periodService->resolveCutoffRange(
            (int) $validated['cutoff_month'],
            (int) $validated['cutoff_year'],
            (string) $validated['cutoff_type']
        );

        if ($request->boolean('rebuild_summary', true) && class_exists(DailyAttendanceSummaryService::class)) {
            app(DailyAttendanceSummaryService::class)->buildForPeriod($startDate, $endDate);
        }

        try {
            $payroll = $this->payrollComputationService->generate(
                $validated,
                auth()->id()
            );

            // The business 2nd cutoff is immediately reconciled in Draft so HR
            // sees the exact monthly statutory true-up and the default Auto Cap
            // protection before reviewing/finalizing. This prevents a resigned
            // or no-pay employee from appearing with a negative net pay merely
            // because the monthly employee share falls on the closing cutoff.
            if ((string) $payroll->cutoff_type === 'first') {
                $this->monthlyGovernmentReconciliationService->reconcileClosingCutoff(
                    $payroll,
                    false,
                    'payroll_generation_preview'
                );
                $payroll->refresh();
            }

            return redirect()
                ->route('payroll.show', $payroll)
                ->with('success', 'Payroll generated successfully. Please review before finalizing.');
        } catch (Throwable $exception) {
            Log::error('Payroll generation failed', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            throw $exception;
        }
    }

    public function show(Payroll $payroll)
    {
        $this->authorize('view', $payroll);

        $payroll->load(['items.employeeBiometric.company', 'items.paymentLogs', 'generator', 'finalizer']);

        $payroll->setRelation(
            'items',
            $payroll->items
                ->sortBy(function (PayrollItem $item): string {
                    $employee = $item->employeeBiometric;
                    $inactive = $employee
                        && ($employee->employment_status === 'inactive' || $employee->is_payroll_active === false);

                    return ($inactive ? '1' : '0').'|'.strtolower($item->payroll_display_name);
                })
                ->values()
        );

        $totals = $this->totals($payroll);
        $items = $payroll->items->map(fn (PayrollItem $item): array => $this->payrollItemRow($payroll, $item));
        $user = request()->user();

        return Inertia::render('payroll/payrolls/show', [
            'payroll' => [
                'id' => $payroll->id,
                'payroll_number' => $payroll->payroll_number,
                'status' => $payroll->status,
                'cutoff_label' => $payroll->cutoff_label,
                'contribution_label' => $payroll->contribution_label,
                'period_start' => $payroll->period_start?->format('M d, Y'),
                'period_end' => $payroll->period_end?->format('M d, Y'),
                'group_label' => $payroll->garage_group_label,
                'eligible_roster' => (int) data_get($payroll->meta, 'roster_audit.eligible_employee_count', $items->count()),
                'missing_summary_employees' => (int) data_get($payroll->meta, 'roster_audit.employees_without_summary_rows', 0),
                'settlement_carry_forward' => (int) data_get($payroll->meta, 'roster_audit.closing_settlement_carry_forward_count', 0),
            ],
            'totals' => [
                'employees' => (int) data_get($totals, 'employees', $items->count()),
                'regular_pay' => (float) data_get($totals, 'regular_pay', $payroll->items->sum('regular_pay')),
                'holiday_pay' => (float) data_get($totals, 'holiday_pay', $payroll->items->sum('holiday_pay')),
                'rest_day_pay' => (float) data_get($totals, 'rest_day_pay', $payroll->items->sum('rest_day_pay')),
                'overtime_pay' => (float) data_get($totals, 'overtime_pay', $payroll->items->sum('overtime_pay')),
                'night_differential_pay' => (float) data_get($totals, 'night_differential_pay', $payroll->items->sum('night_differential_pay')),
                'leave_pay' => (float) data_get($totals, 'leave_pay', $payroll->items->sum('leave_pay')),
                'other_additions' => (float) data_get($totals, 'other_additions', $payroll->items->sum('other_additions')),
                'gross_pay' => (float) data_get($totals, 'gross_pay', $payroll->items->sum('gross_pay')),
                'government' => (float) data_get($totals, 'total_employee_government_deductions', $payroll->items->sum('total_employee_government_deductions')),
                'other_deductions' => (float) data_get($totals, 'other_deductions', $payroll->items->sum('other_deductions')),
                'net_pay' => (float) data_get($totals, 'net_pay', $payroll->items->sum('net_pay')),
                'payable_days' => (float) $payroll->items->sum('total_payable_days'),
                'payable_hours' => (float) $payroll->items->sum('total_payable_hours'),
            ],
            'items' => $items->values(),
            'can' => [
                'finalize' => $payroll->status !== 'finalized' && $user->can('payroll.finalize'),
                'export' => $user->can('payroll.export'),
            ],
            'urls' => [
                'index' => route('payroll.index'),
                'finalize' => route('payroll.finalize', $payroll),
                'excel' => route('payroll.export.excel', $payroll),
                'pdf' => route('payroll.export.pdf', $payroll),
            ],
        ]);
    }

    /**
     * One employee row of the payroll breakdown, including the audit badges
     * that flag rows needing review (same rules as the former Blade page).
     */
    private function payrollItemRow(Payroll $payroll, PayrollItem $item): array
    {
        $additions = (float) ($item->holiday_pay ?? 0) + (float) ($item->rest_day_pay ?? 0)
            + (float) ($item->overtime_pay ?? 0) + (float) ($item->night_differential_pay ?? 0)
            + (float) ($item->leave_pay ?? 0) + (float) ($item->other_additions ?? 0);
        $government = (float) ($item->total_employee_government_deductions ?? 0);
        $otherDeductions = (float) ($item->other_deductions ?? 0);
        $regular = (float) ($item->regular_pay ?? 0);
        $gross = (float) ($item->gross_pay ?? 0);
        $net = (float) ($item->net_pay ?? 0);
        $payableDays = (float) ($item->total_payable_days ?? 0);
        $payableHours = (float) ($item->total_payable_hours ?? 0);
        $missingSummaryDays = (int) data_get($item->meta, 'attendance_summary_coverage.missing_days', 0);
        $settlementOnly = (bool) data_get($item->meta, 'closing_benefit_settlement_only', false);
        $tags = collect(data_get($item->meta, 'adjustment_tags', []));
        $paidTags = $tags->filter(fn ($tag): bool => (bool) data_get($tag, 'paid_this_cutoff', false));

        $badges = [];
        if ($settlementOnly) {
            $badges[] = ['label' => 'Benefit Settlement Only', 'tone' => 'info'];
        } elseif ((bool) data_get($item->meta, 'safe_zero_pay', false)) {
            $badges[] = ['label' => 'No Summary', 'tone' => 'danger'];
        } elseif ($missingSummaryDays > 0) {
            $badges[] = ['label' => 'Summary Gap '.$missingSummaryDays.'d', 'tone' => 'warning'];
        }

        if (! $settlementOnly) {
            if ($regular <= 0) {
                $badges[] = ['label' => 'No Regular', 'tone' => 'danger'];
            }
            if ($gross <= 0) {
                $badges[] = ['label' => 'No Gross', 'tone' => 'danger'];
            }
            if ($net <= 0) {
                $badges[] = ['label' => 'No Net', 'tone' => 'danger'];
            }
            if ($payableDays <= 0 && $payableHours <= 0) {
                $badges[] = ['label' => 'No Payable', 'tone' => 'warning'];
            }
        } elseif ($net < -0.009) {
            $badges[] = ['label' => 'Negative Settlement', 'tone' => 'danger'];
        }

        if ($gross > 0 && ($government + $otherDeductions) > $gross * 0.6) {
            $badges[] = ['label' => 'High Deduct.', 'tone' => 'warning'];
        }
        if ($additions > 0) {
            $badges[] = ['label' => 'Additions', 'tone' => 'info'];
        }
        if ($paidTags->isNotEmpty()) {
            $badges[] = ['label' => 'ADJ Paid '.$paidTags->count(), 'tone' => 'primary'];
        } elseif ($tags->isNotEmpty()) {
            $badges[] = ['label' => 'ADJ '.$tags->count(), 'tone' => 'info'];
        }

        $tones = array_column($badges, 'tone');

        return [
            'id' => $item->id,
            'name' => $item->payroll_display_name,
            'employee_no' => $item->employee_no,
            'settlement_only' => $settlementOnly,
            'tags' => $tags->take(3)->map(fn ($tag): array => [
                'label' => (string) data_get($tag, 'label', 'Adjustment'),
                'amount' => (float) data_get($tag, 'amount', 0),
            ])->values(),
            'payable_days' => $payableDays,
            'regular' => $regular,
            'additions' => $additions,
            'government' => $government,
            'other_deductions' => $otherDeductions,
            'gross' => $gross,
            'net' => $net,
            'badges' => $badges === [] ? [['label' => 'OK', 'tone' => 'success']] : $badges,
            'severity' => in_array('danger', $tones, true) ? 'danger' : (in_array('warning', $tones, true) ? 'warning' : 'ok'),
            'url' => route('payroll.items.show', [$payroll, $item]),
        ];
    }

    public function showItem(Payroll $payroll, PayrollItem $item)
    {
        $this->authorize('view', $payroll);

        abort_if((int) $item->payroll_id !== (int) $payroll->id, 404);

        $item->load(['employeeBiometric.company', 'paymentLogs', 'benefitSettlement']);

        $summaries = DailyAttendanceSummary::query()
            ->with(['employeeBiometric', 'plottingSchedule'])
            ->whereBetween('work_date', [
                $this->dateString($payroll->period_start),
                $this->dateString($payroll->period_end),
            ])
            ->when(
                ! empty($item->employee_biometric_id),
                fn ($query) => $query->where('employee_biometric_id', (int) $item->employee_biometric_id),
                function ($query) use ($item): void {
                    $query->where(function ($query) use ($item): void {
                        if (! empty($item->biometric_employee_id)) {
                            $query->orWhere('biometric_employee_id', $item->biometric_employee_id);
                        }

                        if (! empty($item->employee_no)) {
                            $query->orWhere('employee_no', $item->employee_no);
                        }

                        if (! empty($item->employee_name)) {
                            $query->orWhere('employee_name', $item->employee_name);
                        }
                    });
                }
            )
            ->orderBy('work_date')
            ->get();

        return Inertia::render('payroll/items/show', app(\App\Support\Payroll\PayrollItemPresenter::class)
            ->present($payroll, $item, $summaries, request()->user()));
    }

    /**
     * Recompute a single employee's PayrollItem in place (e.g. right after
     * filing an adjustment for them from the item detail page), without
     * touching any other employee's item in the same draft payroll.
     */
    public function recomputeItem(Payroll $payroll, PayrollItem $item): RedirectResponse
    {
        $this->authorize('create', Payroll::class);

        abort_if((int) $item->payroll_id !== (int) $payroll->id, 404);

        try {
            $this->payrollComputationService->recomputeItem($payroll, $item, auth()->id());
        } catch (Throwable $exception) {
            Log::error('Payroll item recompute failed', [
                'payroll_id' => $payroll->id,
                'item_id' => $item->id,
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            throw $exception;
        }

        return redirect()
            ->route('payroll.items.show', [$payroll, $item])
            ->with('success', sprintf(
                '%s\'s payroll computation was recomputed with the latest attendance and adjustment data. Other employees in this payroll were not affected.',
                $item->payroll_display_name
            ));
    }

    public function finalize(Payroll $payroll): RedirectResponse
    {
        $this->authorize('finalize', $payroll);

        if ($payroll->status === 'finalized') {
            return back()->with('success', 'Payroll is already finalized.');
        }

        $payroll->loadMissing('items');

        $incompleteSummaryItems = $payroll->items->filter(function (PayrollItem $item): bool {
            return (bool) data_get($item->meta, 'safe_zero_pay', false)
                || (int) data_get($item->meta, 'attendance_summary_coverage.missing_days', 0) > 0;
        });

        if ($incompleteSummaryItems->isNotEmpty()) {
            return back()->withErrors([
                'payroll' => sprintf(
                    'Cannot finalize payroll. %d employee(s) have missing Attendance Summary coverage. Rebuild the cutoff and regenerate this draft payroll first.',
                    $incompleteSummaryItems->count()
                ),
            ]);
        }

        $legacyDeferredOffsetItems = $payroll->items->filter(function (PayrollItem $item): bool {
            return collect(data_get($item->meta, 'manual_adjustments.details', []))
                ->contains(function ($detail): bool {
                    if (! is_array($detail)) {
                        return false;
                    }

                    return (string) ($detail['type'] ?? '') === PayrollAttendanceAdjustment::TYPE_OFFSET
                        && (
                            (string) ($detail['effect'] ?? '') === 'Deferred offset payment'
                            || (bool) ($detail['paid_this_cutoff'] ?? false)
                        );
                });
        });

        if ($legacyDeferredOffsetItems->isNotEmpty()) {
            return back()->withErrors([
                'payroll' => sprintf(
                    'Cannot finalize payroll. %d employee item(s) still contain the old deferred-cash Offset computation. Delete/regenerate this draft payroll under the new normal Offset policy first.',
                    $legacyDeferredOffsetItems->count()
                ),
            ]);
        }

        $payrollEmployeeIds = $payroll->items
            ->pluck('employee_biometric_id')
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        $pendingApprovalCount = PayrollAttendanceAdjustment::query()
            ->pending()
            ->whereIn('adjustment_type', [
                PayrollAttendanceAdjustment::TYPE_OVERTIME,
                PayrollAttendanceAdjustment::TYPE_OFFSET,
            ])
            ->whereBetween('work_date', [
                $this->dateString($payroll->period_start),
                $this->dateString($payroll->period_end),
            ])
            ->whereIn('employee_biometric_id', $payrollEmployeeIds)
            ->count();

        if ($pendingApprovalCount > 0) {
            return back()->withErrors([
                'payroll' => sprintf(
                    'Cannot finalize payroll. %d OT/Offset adjustment(s) in this cutoff are still pending Head Manager approval/rejection. Resolve them, rebuild Attendance Summary when Offset is involved, then regenerate the draft payroll.',
                    $pendingApprovalCount
                ),
            ]);
        }

        /*
         * A payroll draft is a financial snapshot. If an approved attendance
         * adjustment was created/approved/edited after the draft was generated,
         * the current item amounts can be stale (most importantly OT approval).
         * Block finalization until the draft is regenerated from the latest
         * approved adjustment state.
         */
        if ($payroll->generated_at) {
            $approvedAdjustmentChanges = PayrollAttendanceAdjustment::query()
                ->approved()
                ->where('updated_at', '>', $payroll->generated_at)
                ->where(function ($query) use ($payroll): void {
                    $query->whereNull('paid_payroll_id')
                        ->orWhere('paid_payroll_id', '!=', $payroll->id);
                })
                ->where(function ($query) use ($payroll): void {
                    $start = $this->dateString($payroll->period_start);
                    $end = $this->dateString($payroll->period_end);

                    $query
                        ->whereBetween('work_date', [$start, $end])
                        ->orWhere(function ($rangeQuery) use ($start, $end): void {
                            $rangeQuery
                                ->whereNotNull('date_from')
                                ->whereRaw('COALESCE(date_from, work_date) <= ?', [$end])
                                ->whereRaw('COALESCE(date_to, work_date) >= ?', [$start]);
                        })
                        ->orWhereBetween('payroll_effective_date', [$start, $end]);
                })
                ->where(function ($query) use ($payrollEmployeeIds): void {
                    $query
                        ->whereIn('employee_biometric_id', $payrollEmployeeIds)
                        ->orWhereIn('adjustment_type', PayrollAttendanceAdjustment::TYPHOON_DISASTER_TYPES);
                })
                ->count();

            if ($approvedAdjustmentChanges > 0) {
                return back()->withErrors([
                    'payroll' => sprintf(
                        'Cannot finalize payroll. %d approved adjustment(s) changed after this draft was generated. Regenerate the draft so OT, offset, leave, holiday-work, and attendance effects are recalculated before finalization.',
                        $approvedAdjustmentChanges
                    ),
                ]);
            }
        }

        $postedBenefitRecords = 0;

        try {
            DB::transaction(function () use ($payroll, &$postedBenefitRecords): void {
                $lockedPayroll = Payroll::query()
                    ->lockForUpdate()
                    ->findOrFail($payroll->id);

                if ($lockedPayroll->status === 'finalized') {
                    return;
                }

                $finalizedAt = now('Asia/Manila');
                $userId = auth()->id();

                // The business 2nd cutoff (11-25 / legacy key `first`) closes
                // the contribution month. Reconcile it against the already
                // finalized business 1st cutoff (26-10) before locking payroll.
                // This guarantees that SSS/MPF uses the WHOLE monthly gross.
                $this->monthlyGovernmentReconciliationService->reconcileClosingCutoff(
                    $lockedPayroll,
                    false,
                    'payroll_finalize'
                );

                $lockedPayroll->update([
                    'status' => 'finalized',
                    'finalized_by' => $userId,
                    'finalized_at' => $finalizedAt,
                ]);

                // The official monthly Benefits Records ledger is part of the
                // same atomic transaction. The opening 26-10 cutoff returns 0
                // records because the contribution month is not complete yet.
                $postedBenefitRecords = $this->benefitContributionPostingService->postForPayroll(
                    $lockedPayroll->fresh(),
                    $userId,
                    $finalizedAt
                );
            }, 3);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        } catch (Throwable $exception) {
            Log::error('Payroll finalization / Benefits Records posting failed.', [
                'payroll_id' => $payroll->id,
                'payroll_number' => $payroll->payroll_number,
                'user_id' => auth()->id(),
                'exception' => $exception,
            ]);

            return back()->withErrors([
                'payroll' => 'Payroll finalization failed and was rolled back. No Benefits Records were posted. Please review the application log and try again.',
            ]);
        }

        $successMessage = (string) $payroll->cutoff_type === 'second'
            ? 'Payroll finalized successfully. This is the 1st cutoff (26-10); monthly Benefits Records will be posted after the 2nd cutoff (11-25) is finalized.'
            : sprintf(
                'Payroll finalized successfully. Exact monthly government contributions were reconciled from both cutoffs and %d Benefits Record(s) were posted.',
                $postedBenefitRecords
            );

        return back()->with('success', $successMessage);
    }

    public function destroy(Payroll $payroll): RedirectResponse
    {
        $this->authorize('delete', $payroll);

        if ($payroll->status === 'finalized') {
            return back()->withErrors([
                'payroll' => 'Finalized payroll cannot be deleted.',
            ]);
        }

        $payroll->delete();

        // Use the canonical payroll URL directly after deletion. This avoids
        // stale/legacy named-route caches sending the browser to /payroll/v2.
        return redirect('/payroll')
            ->with('success', 'Draft payroll deleted successfully.');
    }

    public function exportExcel(Payroll $payroll): BinaryFileResponse
    {
        $this->authorize('export', $payroll);

        return Excel::download(
            new PayrollItemsExport($payroll->load('items.employeeBiometric')),
            $payroll->payroll_number.'.xlsx'
        );
    }

    public function exportPdf(Payroll $payroll)
    {
        $this->authorize('export', $payroll);

        $data = $this->payrollPayslipService->build($payroll);

        $pdf = Pdf::loadView('payroll.payrolls.payslip-pdf', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->stream($payroll->payroll_number.'-payslips.pdf');
    }

    private function dateString(mixed $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->toDateString();
        }

        return Carbon::parse((string) $value, 'Asia/Manila')->toDateString();
    }

    protected function totals(Payroll $payroll): array
    {
        $items = $payroll->items;

        return [
            'employees' => $items->count(),

            'regular_pay' => round((float) $items->sum('regular_pay'), 2),
            'gross_pay' => round((float) $items->sum('gross_pay'), 2),
            'holiday_pay' => round((float) $items->sum('holiday_pay'), 2),
            'rest_day_pay' => round((float) $items->sum('rest_day_pay'), 2),
            'overtime_pay' => round((float) $items->sum('overtime_pay'), 2),
            'night_differential_pay' => round((float) $items->sum('night_differential_pay'), 2),
            'leave_pay' => round((float) $items->sum('leave_pay'), 2),

            'late_deduction' => round((float) $items->sum('late_deduction'), 2),
            'undertime_deduction' => round((float) $items->sum('undertime_deduction'), 2),
            'absence_deduction' => round((float) $items->sum('absence_deduction'), 2),

            'other_additions' => round((float) $items->sum('other_additions'), 2),
            'other_deductions' => round((float) $items->sum('other_deductions'), 2),

            'sss_employee' => round((float) $items->sum('sss_employee'), 2),
            'philhealth_employee' => round((float) $items->sum('philhealth_employee'), 2),
            'pagibig_employee' => round((float) $items->sum('pagibig_employee'), 2),
            'withholding_tax' => round((float) $items->sum('withholding_tax'), 2),

            'total_employee_government_deductions' => round(
                (float) $items->sum('total_employee_government_deductions'),
                2
            ),

            'total_employer_government_contributions' => round(
                (float) $items->sum('total_employer_government_contributions'),
                2
            ),

            'net_pay' => round((float) $items->sum('net_pay'), 2),
        ];
    }
}
