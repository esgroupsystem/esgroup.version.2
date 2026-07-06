<?php

namespace App\Http\Controllers\Payroll;

use App\Exports\PayrollItemsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\GeneratePayrollRequest;
use App\Models\DailyAttendanceSummary;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Services\Payroll\DailyAttendanceSummaryService;
use App\Services\Payroll\PayrollComputationService;
use App\Services\Payroll\PayrollPayslipService;
use App\Services\Payroll\PayrollPeriodService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PayrollController extends Controller
{
    public function __construct(
        protected PayrollPeriodService $periodService,
        protected PayrollComputationService $payrollComputationService,
        protected PayrollPayslipService $payrollPayslipService,
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
        [$defaultCutoffMonth, $defaultCutoffYear, $defaultCutoffType] = $this->periodService->getDefaultCutoff();

        return view('payroll.payrolls.create', compact(
            'defaultCutoffMonth',
            'defaultCutoffYear',
            'defaultCutoffType'
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
        $payroll->load(['items.employeeBiometric', 'items.paymentLogs', 'generator', 'finalizer']);

        $totals = $this->totals($payroll);

        return view('payroll.payrolls.show', compact('payroll', 'totals'));
    }

    public function showItem(Payroll $payroll, PayrollItem $item)
    {
        abort_if((int) $item->payroll_id !== (int) $payroll->id, 404);

        $item->load(['employeeBiometric', 'paymentLogs']);

        $summaries = DailyAttendanceSummary::query()
            ->with('employeeBiometric')
            ->whereBetween('work_date', [
                $payroll->period_start->toDateString(),
                $payroll->period_end->toDateString(),
            ])
            ->where(function ($query) use ($item): void {
                if (! empty($item->employee_biometric_id)) {
                    $query->orWhere('employee_biometric_id', (int) $item->employee_biometric_id);
                }

                if (! empty($item->biometric_employee_id)) {
                    $query->orWhere('biometric_employee_id', $item->biometric_employee_id);
                }

                if (! empty($item->employee_no)) {
                    $query->orWhere('employee_no', $item->employee_no);
                }

                if (! empty($item->employee_name)) {
                    $query->orWhere('employee_name', $item->employee_name);
                }
            })
            ->orderBy('work_date')
            ->get();

        return view('payroll.items.show', compact('payroll', 'item', 'summaries'));
    }

    public function finalize(Payroll $payroll): RedirectResponse
    {
        if ($payroll->status === 'finalized') {
            return back()->with('success', 'Payroll is already finalized.');
        }

        DB::transaction(function () use ($payroll): void {
            $payroll->update([
                'status' => 'finalized',
                'finalized_by' => auth()->id(),
                'finalized_at' => now('Asia/Manila'),
            ]);
        });

        return back()->with('success', 'Payroll finalized successfully.');
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
