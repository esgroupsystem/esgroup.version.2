<?php

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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
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
        $search = trim((string) $request->search);
        $status = trim((string) $request->status);
        $cutoffType = trim((string) $request->cutoff_type);

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
            ->orderByDesc('cutoff_year')
            ->orderByDesc('cutoff_month')
            ->orderByRaw("CASE WHEN cutoff_type = 'first' THEN 2 WHEN cutoff_type = 'second' THEN 1 ELSE 0 END DESC")
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('payroll.payrolls.index', compact(
            'payrolls',
            'search',
            'status',
            'cutoffType'
        ));
    }

    public function create()
    {
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

        return view('payroll.payrolls.create', compact(
            'defaultCutoffMonth',
            'defaultCutoffYear',
            'defaultCutoffType',
            'payrollGroups'
        ));
    }

    public function store(GeneratePayrollRequest $request): RedirectResponse
    {
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

            return redirect()
                ->route('payroll.show', $payroll)
                ->with('success', 'Payroll generated successfully. Please review before finalizing.');
        } catch (\Throwable $exception) {
            Log::error('Payroll generation failed', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            throw $exception;
        }
    }

    public function show(Payroll $payroll)
    {
        $payroll->load(['items.employeeBiometric.company', 'items.paymentLogs', 'generator', 'finalizer']);

        $totals = $this->totals($payroll);

        return view('payroll.payrolls.show', compact('payroll', 'totals'));
    }

    public function showItem(Payroll $payroll, PayrollItem $item)
    {
        abort_if((int) $item->payroll_id !== (int) $payroll->id, 404);

        $item->load(['employeeBiometric.company', 'paymentLogs']);

        $summaries = DailyAttendanceSummary::query()
            ->with(['employeeBiometric', 'plottingSchedule'])
            ->whereBetween('work_date', [
                $payroll->period_start->toDateString(),
                $payroll->period_end->toDateString(),
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

        return view('payroll.items.show', compact('payroll', 'item', 'summaries'));
    }

    public function finalize(Payroll $payroll): RedirectResponse
    {
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
                $payroll->period_start->toDateString(),
                $payroll->period_end->toDateString(),
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
                    $start = $payroll->period_start->toDateString();
                    $end = $payroll->period_end->toDateString();

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
                        ->orWhere('adjustment_type', PayrollAttendanceAdjustment::TYPE_TYPHOON_DISASTER);
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
        if ($payroll->status === 'finalized') {
            return back()->withErrors([
                'payroll' => 'Finalized payroll cannot be deleted.',
            ]);
        }

        $payroll->delete();

        return redirect()
            ->route('payroll.index')
            ->with('success', 'Draft payroll deleted successfully.');
    }

    public function exportExcel(Payroll $payroll): BinaryFileResponse
    {
        return Excel::download(
            new PayrollItemsExport($payroll->load('items.employeeBiometric')),
            $payroll->payroll_number.'.xlsx'
        );
    }

    public function exportPdf(Payroll $payroll)
    {
        $data = $this->payrollPayslipService->build($payroll);

        $pdf = Pdf::loadView('payroll.payrolls.payslip-pdf', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->stream($payroll->payroll_number.'-payslips.pdf');
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
