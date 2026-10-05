<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payroll;

use App\Exports\PayrollItemsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\GeneratePayrollRequest;
use App\Http\Resources\Payroll\PayrollItemDetailResource;
use App\Http\Resources\Payroll\PayrollItemRowResource;
use App\Http\Resources\Payroll\PayrollRowResource;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Services\Payroll\PayrollFinalizationService;
use App\Services\Payroll\PayrollService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

/**
 * Payroll → Payroll: list, generate, review, recompute one employee, finalize, delete and export.
 * PayrollPolicy also limits every payroll to the user's payroll groups.
 */
final class PayrollController extends Controller
{
    public function __construct(
        private readonly PayrollService $payrolls,
        private readonly PayrollFinalizationService $finalization,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Payroll::class);

        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'status' => trim((string) $request->input('status', '')),
            'cutoff_type' => trim((string) $request->input('cutoff_type', '')),
            'garage_group' => trim((string) $request->input('garage_group', '')),
        ];
        $user = $request->user();

        return Inertia::render('payroll/payrolls/index', [
            'payrolls' => $this->payrolls->paginate($filters['search'], $filters['status'], $filters['cutoff_type'], $filters['garage_group'])
                ->through(fn (Payroll $payroll): array => PayrollRowResource::make($payroll)->resolve($request)),
            'filters' => $filters,
            'payrollGroups' => $this->payrolls->groupOptions(),
            'cutoffTypes' => $this->cutoffTypes(),
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

    public function create(): Response
    {
        $this->authorize('create', Payroll::class);
        $thisYear = (int) now('Asia/Manila')->year;

        return Inertia::render('payroll/payrolls/create', [
            'defaults' => $this->payrolls->defaultCutoff(),
            'payrollGroups' => $this->payrolls->groupOptions(),
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

        $payroll = $this->payrolls->generate($request->validated(), $request->boolean('rebuild_summary', true), $this->userId($request));

        return redirect()->route('payroll.show', $payroll)->with('success', 'Payroll generated successfully. Please review before finalizing.');
    }

    public function show(Request $request, Payroll $payroll): Response
    {
        $this->authorize('view', $payroll);

        $payroll = $this->payrolls->forShow($payroll);
        $totals = $this->payrolls->totals($payroll);
        $user = $request->user();

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
                'eligible_roster' => (int) data_get($payroll->meta, 'roster_audit.eligible_employee_count', $totals['employees']),
                'missing_summary_employees' => (int) data_get($payroll->meta, 'roster_audit.employees_without_summary_rows', 0),
                'settlement_carry_forward' => (int) data_get($payroll->meta, 'roster_audit.closing_settlement_carry_forward_count', 0),
            ],
            'totals' => $totals,
            'items' => $payroll->items->map(fn (PayrollItem $item): array => (new PayrollItemRowResource($item, $payroll))->resolve($request))->values(),
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

    public function showItem(Request $request, Payroll $payroll, PayrollItem $item): Response
    {
        $this->authorize('view', $payroll);

        ['item' => $item, 'summaries' => $summaries] = $this->payrolls->itemDetail($payroll, $item);

        return Inertia::render('payroll/items/show', (new PayrollItemDetailResource($item, $payroll, $summaries))->resolve($request));
    }

    /** Recomputes one employee's item (e.g. right after filing an adjustment); nobody else is touched. */
    public function recomputeItem(Request $request, Payroll $payroll, PayrollItem $item): RedirectResponse
    {
        $this->authorize('update', $payroll);

        $this->payrolls->recomputeItem($payroll, $item, $this->userId($request));

        return redirect()
            ->route('payroll.items.show', [$payroll, $item])
            ->with('success', sprintf(
                '%s\'s payroll computation was recomputed with the latest attendance and adjustment data. Other employees in this payroll were not affected.',
                $item->payroll_display_name,
            ));
    }

    public function finalize(Request $request, Payroll $payroll): RedirectResponse
    {
        $this->authorize('finalize', $payroll);

        try {
            $message = $this->finalization->finalize($payroll, $this->userId($request));
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        } catch (Throwable $exception) {
            Log::error('Payroll finalization / Benefits Records posting failed.', [
                'payroll_id' => $payroll->id,
                'payroll_number' => $payroll->payroll_number,
                'user_id' => $this->userId($request),
                'exception' => $exception,
            ]);

            return back()->withErrors(['payroll' => 'Payroll finalization failed and was rolled back. No Benefits Records were posted. Please review the application log and try again.']);
        }

        return back()->with('success', $message ?? 'Payroll is already finalized.');
    }

    public function destroy(Payroll $payroll): RedirectResponse
    {
        $this->authorize('delete', $payroll);

        if (! $this->payrolls->delete($payroll)) {
            return back()->withErrors(['payroll' => 'Finalized payroll cannot be deleted.']);
        }

        // The canonical URL avoids stale route caches sending the browser to /payroll/v2.
        return redirect('/payroll')->with('success', 'Draft payroll deleted successfully.');
    }

    public function exportExcel(Payroll $payroll): BinaryFileResponse
    {
        $this->authorize('export', $payroll);

        return Excel::download(new PayrollItemsExport($this->payrolls->forExport($payroll)), $payroll->payroll_number.'.xlsx');
    }

    public function exportPdf(Payroll $payroll): HttpResponse
    {
        $this->authorize('export', $payroll);

        return Pdf::loadView('payroll.payrolls.payslip-pdf', $this->payrolls->payslipData($payroll))
            ->setPaper('a4', 'portrait')
            ->stream($payroll->payroll_number.'-payslips.pdf');
    }

    /** @return array<string, string> */
    private function cutoffTypes(): array
    {
        return [
            'second' => config('payroll.cutoff_display.second.full', '1st Cutoff (26-10)'),
            'first' => config('payroll.cutoff_display.first.full', '2nd Cutoff (11-25)'),
        ];
    }

    private function userId(Request $request): ?int
    {
        $id = $request->user()?->getKey();

        return $id === null ? null : (int) $id;
    }
}
