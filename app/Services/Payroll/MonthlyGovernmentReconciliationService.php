<?php

namespace App\Services\Payroll;

use App\Models\Payroll;
use App\Models\PayrollItem;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class MonthlyGovernmentReconciliationService
{
    public function __construct(
        private readonly MonthlyGovernmentContributionService $monthlyContributionService
    ) {}

    /**
     * Reconcile the BUSINESS 2ND CUTOFF (11-25, legacy key `first`) so the
     * employee's total deductions across both cutoffs equal the exact monthly
     * SSS / PhilHealth / Pag-IBIG liability.
     *
     * The opening BUSINESS 1ST CUTOFF (26-10, legacy key `second`) must already
     * be finalized. This removes the possibility that SSS is finalized from only
     * half of the contribution month.
     */
    public function reconcileClosingCutoff(
        Payroll $closingPayroll,
        bool $allowFinalized = false,
        string $reason = 'payroll_finalize'
    ): array {
        if ((string) $closingPayroll->cutoff_type !== 'first') {
            return [
                'updated_items' => 0,
                'changes' => [],
                'opening_payroll_id' => null,
            ];
        }

        if ($closingPayroll->status === 'finalized' && ! $allowFinalized) {
            throw ValidationException::withMessages([
                'payroll' => 'The closing cutoff is already finalized and cannot be reconciled through the normal finalize workflow.',
            ]);
        }

        $openingPayroll = Payroll::query()
            ->where('contribution_month', (int) $closingPayroll->contribution_month)
            ->where('contribution_year', (int) $closingPayroll->contribution_year)
            ->where('garage_group', (string) $closingPayroll->garage_group)
            ->where('cutoff_type', 'second')
            ->latest('id')
            ->first();

        if (! $openingPayroll) {
            throw ValidationException::withMessages([
                'payroll' => sprintf(
                    'Cannot finalize the 2nd cutoff. The 1st cutoff (26-10) for %s has not been generated. SSS must use the complete monthly compensation from both cutoffs.',
                    $closingPayroll->contribution_label
                ),
            ]);
        }

        if ($openingPayroll->status !== 'finalized') {
            throw ValidationException::withMessages([
                'payroll' => sprintf(
                    'Cannot finalize the 2nd cutoff. Finalize payroll %s (%s) first. The 1st and 2nd cutoffs must be locked before the exact monthly SSS/MPF contribution is posted.',
                    $openingPayroll->payroll_number,
                    $openingPayroll->cutoff_label
                ),
            ]);
        }

        $openingPayroll->loadMissing('items');
        $closingPayroll->loadMissing('items');

        $openingItems = $openingPayroll->items;
        $changes = [];
        $updated = 0;

        foreach ($closingPayroll->items as $closingItem) {
            $openingItem = $this->matchingItem($openingItems, $closingItem);

            $calculation = $this->monthlyContributionService->compute(
                (float) ($openingItem?->gross_pay ?? 0),
                (float) $closingItem->gross_pay,
                (float) ($closingItem->monthly_rate ?: ($openingItem?->monthly_rate ?? 0))
            );

            $old = [
                'sss_employee' => round((float) $closingItem->sss_employee, 2),
                'sss_employer' => round((float) $closingItem->sss_employer, 2),
                'sss_ec' => round((float) $closingItem->sss_ec, 2),
                'philhealth_employee' => round((float) $closingItem->philhealth_employee, 2),
                'philhealth_employer' => round((float) $closingItem->philhealth_employer, 2),
                'pagibig_employee' => round((float) $closingItem->pagibig_employee, 2),
                'pagibig_employer' => round((float) $closingItem->pagibig_employer, 2),
                'total_employee_government_deductions' => round((float) $closingItem->total_employee_government_deductions, 2),
                'total_employer_government_contributions' => round((float) $closingItem->total_employer_government_contributions, 2),
                'net_pay' => round((float) $closingItem->net_pay, 2),
            ];

            /*
             * Monthly true-up rule:
             * exact monthly liability - amount already deducted/contributed in
             * the business 1st cutoff = amount to place in business 2nd cutoff.
             *
             * A signed delta is intentional. If an earlier cutoff over-withheld
             * a benefit, a negative closing-cutoff amount becomes a transparent
             * payroll credit instead of silently leaving the monthly ledger wrong.
             */
            $new = [
                'sss_employee' => $this->delta(
                    (float) $calculation['sss_employee'],
                    (float) ($openingItem?->sss_employee ?? 0)
                ),
                'sss_employer' => $this->delta(
                    (float) $calculation['sss_employer'],
                    (float) ($openingItem?->sss_employer ?? 0)
                ),
                'sss_ec' => $this->delta(
                    (float) $calculation['sss_ec'],
                    (float) ($openingItem?->sss_ec ?? 0)
                ),
                'philhealth_employee' => $this->delta(
                    (float) $calculation['philhealth_employee'],
                    (float) ($openingItem?->philhealth_employee ?? 0)
                ),
                'philhealth_employer' => $this->delta(
                    (float) $calculation['philhealth_employer'],
                    (float) ($openingItem?->philhealth_employer ?? 0)
                ),
                'pagibig_employee' => $this->delta(
                    (float) $calculation['pagibig_employee'],
                    (float) ($openingItem?->pagibig_employee ?? 0)
                ),
                'pagibig_employer' => $this->delta(
                    (float) $calculation['pagibig_employer'],
                    (float) ($openingItem?->pagibig_employer ?? 0)
                ),
            ];

            $new['total_employee_government_deductions'] = round(
                $new['sss_employee']
                + $new['philhealth_employee']
                + $new['pagibig_employee'],
                2
            );

            $new['total_employer_government_contributions'] = round(
                $new['sss_employer']
                + $new['sss_ec']
                + $new['philhealth_employer']
                + $new['pagibig_employer'],
                2
            );

            $new['net_pay'] = round(
                (float) $closingItem->gross_pay
                - (float) $closingItem->other_deductions
                - $new['total_employee_government_deductions'],
                2
            );

            $meta = is_array($closingItem->meta) ? $closingItem->meta : [];

            $governmentAfterTrueUp = $calculation;
            foreach ([
                'sss_employee',
                'sss_employer',
                'sss_ec',
                'philhealth_employee',
                'philhealth_employer',
                'pagibig_employee',
                'pagibig_employer',
                'total_employee_government_deductions',
                'total_employer_government_contributions',
            ] as $field) {
                $governmentAfterTrueUp[$field] = $new[$field];
            }

            $meta['government_raw_before_schedule'] = $calculation;
            $meta['government_after_profile_schedule'] = $governmentAfterTrueUp;
            $meta['government_monthly_cycle_basis'] = [
                'amount' => round((float) $calculation['monthly_cycle_gross'], 2),
                'current_cutoff_basis' => round((float) $calculation['business_second_cutoff_gross'], 2),
                'previous_cutoff_basis' => round((float) $calculation['business_first_cutoff_gross'], 2),
                'previous_second_payroll_item_id' => $openingItem?->id,
                'basis_source' => 'finalized_business_first_gross_plus_business_second_gross',
                'cycle_rule' => 'business_1st_26_10_plus_business_2nd_11_25',
                'warning' => null,
            ];
            $meta['government_monthly_reconciliation'] = [
                'reason' => $reason,
                'reconciled_at' => now('Asia/Manila')->toIso8601String(),
                'opening_payroll_id' => $openingPayroll->id,
                'opening_payroll_number' => $openingPayroll->payroll_number,
                'opening_payroll_item_id' => $openingItem?->id,
                'closing_payroll_id' => $closingPayroll->id,
                'business_first_cutoff_gross' => round((float) $calculation['business_first_cutoff_gross'], 2),
                'business_second_cutoff_gross' => round((float) $calculation['business_second_cutoff_gross'], 2),
                'monthly_cycle_gross' => round((float) $calculation['monthly_cycle_gross'], 2),
                'sss_msc' => round((float) $calculation['sss_msc'], 2),
                'sss_regular_ss_msc' => round((float) $calculation['sss_regular_ss_msc'], 2),
                'sss_mpf_msc' => round((float) $calculation['sss_mpf_msc'], 2),
                'old_values' => $old,
                'new_values' => $new,
            ];

            $closingItem->fill($new);
            $closingItem->meta = $meta;
            $closingItem->save();

            $updated++;
            $changes[] = [
                'employee_biometric_id' => $closingItem->employee_biometric_id,
                'employee_no' => $closingItem->employee_no,
                'employee_name' => $closingItem->employee_name,
                'opening_payroll_item_id' => $openingItem?->id,
                'business_first_gross' => round((float) $calculation['business_first_cutoff_gross'], 2),
                'business_second_gross' => round((float) $calculation['business_second_cutoff_gross'], 2),
                'monthly_gross' => round((float) $calculation['monthly_cycle_gross'], 2),
                'sss_msc' => round((float) $calculation['sss_msc'], 2),
                'sss_mpf_msc' => round((float) $calculation['sss_mpf_msc'], 2),
                'old' => $old,
                'new' => $new,
            ];
        }

        return [
            'updated_items' => $updated,
            'changes' => $changes,
            'opening_payroll_id' => $openingPayroll->id,
            'opening_payroll_number' => $openingPayroll->payroll_number,
        ];
    }

    private function matchingItem(Collection $openingItems, PayrollItem $closingItem): ?PayrollItem
    {
        if ($closingItem->employee_biometric_id) {
            $match = $openingItems->firstWhere('employee_biometric_id', $closingItem->employee_biometric_id);
            if ($match) {
                return $match;
            }
        }

        if ($closingItem->employee_id) {
            $match = $openingItems->firstWhere('employee_id', $closingItem->employee_id);
            if ($match) {
                return $match;
            }
        }

        if ($closingItem->employee_no) {
            $match = $openingItems->firstWhere('employee_no', $closingItem->employee_no);
            if ($match) {
                return $match;
            }
        }

        if ($closingItem->biometric_employee_id) {
            return $openingItems->firstWhere('biometric_employee_id', $closingItem->biometric_employee_id);
        }

        return null;
    }

    private function delta(float $monthlyLiability, float $alreadyPosted): float
    {
        return round($monthlyLiability - $alreadyPosted, 2);
    }
}
