<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\UpdatePayrollBenefitSettlementRequest;
use App\Models\Payroll;
use App\Models\PayrollBenefitSettlement;
use App\Models\PayrollItem;
use App\Services\Payroll\MonthlyGovernmentContributionService;
use App\Services\Payroll\MonthlyGovernmentReconciliationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayrollBenefitSettlementController extends Controller
{
    public function __construct(
        private readonly MonthlyGovernmentReconciliationService $reconciliationService,
        private readonly MonthlyGovernmentContributionService $monthlyContributionService
    ) {}

    public function store(
        UpdatePayrollBenefitSettlementRequest $request,
        Payroll $payroll,
        PayrollItem $item
    ): RedirectResponse {
        abort_if((int) $item->payroll_id !== (int) $payroll->id, 404);
        $this->authorizePayrollGroup($payroll);

        if ($payroll->status !== 'draft') {
            throw ValidationException::withMessages([
                'payroll' => 'Benefit settlement can only be changed while payroll is still in Draft status.',
            ]);
        }

        if ((string) $payroll->cutoff_type !== 'first') {
            throw ValidationException::withMessages([
                'payroll' => 'Benefit settlement actions are available on the business 2nd cutoff (11-25), where the monthly government contribution is reconciled.',
            ]);
        }

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $payroll, $item): void {
            $openingItem = $this->openingItemFor($payroll, $item);

            $monthlyLiability = $this->monthlyContributionService->compute(
                (float) ($openingItem?->gross_pay ?? 0),
                (float) $item->gross_pay,
                (float) ($item->monthly_rate ?: ($openingItem?->monthly_rate ?? 0))
            );

            $this->validateReimbursement(
                'sss_employee_reimbursement',
                (float) $validated['sss_employee_reimbursement'],
                (float) ($openingItem?->sss_employee ?? 0),
                (float) ($monthlyLiability['sss_employee'] ?? 0),
                'SSS'
            );

            $this->validateReimbursement(
                'philhealth_employee_reimbursement',
                (float) $validated['philhealth_employee_reimbursement'],
                (float) ($openingItem?->philhealth_employee ?? 0),
                (float) ($monthlyLiability['philhealth_employee'] ?? 0),
                'PhilHealth'
            );

            $this->validateReimbursement(
                'pagibig_employee_reimbursement',
                (float) $validated['pagibig_employee_reimbursement'],
                (float) ($openingItem?->pagibig_employee ?? 0),
                (float) ($monthlyLiability['pagibig_employee'] ?? 0),
                'Pag-IBIG'
            );

            $settlement = PayrollBenefitSettlement::query()->firstOrNew([
                'payroll_item_id' => $item->id,
            ]);

            if (! $settlement->exists) {
                $settlement->created_by = auth()->id();
            }

            $settlement->fill([
                'payroll_id' => $payroll->id,
                'employee_biometric_id' => $item->employee_biometric_id,
                'mode' => $validated['mode'],
                'sss_employee_reimbursement' => round((float) $validated['sss_employee_reimbursement'], 2),
                'philhealth_employee_reimbursement' => round((float) $validated['philhealth_employee_reimbursement'], 2),
                'pagibig_employee_reimbursement' => round((float) $validated['pagibig_employee_reimbursement'], 2),
                'reason' => $validated['reason'],
                'updated_by' => auth()->id(),
                'meta' => [
                    'opening_payroll_item_id' => $openingItem?->id,
                    'opening_collected' => [
                        'sss' => round((float) ($openingItem?->sss_employee ?? 0), 2),
                        'philhealth' => round((float) ($openingItem?->philhealth_employee ?? 0), 2),
                        'pagibig' => round((float) ($openingItem?->pagibig_employee ?? 0), 2),
                    ],
                    'monthly_statutory_employee_share' => [
                        'sss' => round((float) ($monthlyLiability['sss_employee'] ?? 0), 2),
                        'philhealth' => round((float) ($monthlyLiability['philhealth_employee'] ?? 0), 2),
                        'pagibig' => round((float) ($monthlyLiability['pagibig_employee'] ?? 0), 2),
                    ],
                ],
            ]);
            $settlement->save();

            // Re-run the exact closing-cutoff true-up immediately so the screen
            // shows the effect before HR finalizes payroll.
            $payroll->unsetRelation('items');
            $this->reconciliationService->reconcileClosingCutoff(
                $payroll,
                false,
                'benefit_settlement_updated'
            );
        });

        return redirect()
            ->route('payroll.items.show', [$payroll, $item])
            ->with('success', 'Government benefit settlement updated and payroll item recalculated.');
    }

    private function authorizePayrollGroup(Payroll $payroll): void
    {
        $allowedGroups = session('payroll_allowed_groups');

        if ($allowedGroups === 'all') {
            return;
        }

        $allowed = collect($allowedGroups ?? [])
            ->map(fn ($group): int => (int) $group)
            ->all();

        abort_unless(in_array((int) $payroll->garage_group, $allowed, true), 403);
    }

    private function openingItemFor(Payroll $closingPayroll, PayrollItem $closingItem): ?PayrollItem
    {
        $openingPayroll = Payroll::query()
            ->where('contribution_month', (int) $closingPayroll->contribution_month)
            ->where('contribution_year', (int) $closingPayroll->contribution_year)
            ->where('garage_group', (string) $closingPayroll->garage_group)
            ->where('cutoff_type', 'second')
            ->where('status', 'finalized')
            ->latest('id')
            ->first();

        if (! $openingPayroll) {
            return null;
        }

        return PayrollItem::query()
            ->where('payroll_id', $openingPayroll->id)
            ->where(function ($query) use ($closingItem): void {
                if ($closingItem->employee_biometric_id) {
                    $query->where('employee_biometric_id', $closingItem->employee_biometric_id);

                    return;
                }

                if ($closingItem->employee_id) {
                    $query->where('employee_id', $closingItem->employee_id);

                    return;
                }

                $query->where('employee_no', $closingItem->employee_no);
            })
            ->first();
    }

    private function validateReimbursement(
        string $field,
        float $requested,
        float $openingCollected,
        float $monthlyLiability,
        string $label
    ): void {
        $openingCollected = max(0, round($openingCollected, 2));
        $monthlyLiability = max(0, round($monthlyLiability, 2));

        /*
         * If the finalized opening cutoff already withheld more than the exact
         * monthly liability, the reconciliation service automatically creates a
         * negative true-up credit. Manual reimbursement must only cover the
         * remaining opening deduction; otherwise the employee could be refunded
         * more than was actually withheld.
         */
        $automaticTrueUpCredit = max(0, round($openingCollected - $monthlyLiability, 2));
        $maximum = max(0, round($openingCollected - $automaticTrueUpCredit, 2));

        if (round($requested, 2) > $maximum) {
            throw ValidationException::withMessages([
                $field => sprintf(
                    '%s reimbursement cannot exceed PHP %s. Any opening-cutoff over-withholding is already refunded automatically by the monthly true-up.',
                    $label,
                    number_format($maximum, 2)
                ),
            ]);
        }
    }
}
