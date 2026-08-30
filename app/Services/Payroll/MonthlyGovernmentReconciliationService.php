<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\Models\Payroll;
use App\Models\PayrollItem;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class MonthlyGovernmentReconciliationService
{
    public function __construct(
        private readonly MonthlyGovernmentContributionService $monthlyContributionService,
        private readonly GovernmentDeductionSettlementService $settlementService,
        private readonly PaymentLogService $paymentLogService,
    ) {}

    /**
     * Reconcile the BUSINESS 2ND CUTOFF (11-25, legacy key `first`) against the
     * finalized BUSINESS 1ST CUTOFF (26-10, legacy key `second`).
     *
     * The exact statutory monthly liability is calculated first. Employee cash
     * collection for the closing cutoff is then settled independently so a
     * resigned/no-pay employee cannot be pushed to a negative net pay while the
     * Benefits Records ledger still retains the complete monthly liability.
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
                    'Cannot reconcile the 2nd cutoff. The 1st cutoff (26-10) for %s has not been generated.',
                    $closingPayroll->contribution_label
                ),
            ]);
        }

        if ($openingPayroll->status !== 'finalized') {
            throw ValidationException::withMessages([
                'payroll' => sprintf(
                    'Cannot reconcile the 2nd cutoff. Finalize payroll %s (%s) first so the complete monthly government contribution can be computed.',
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
                (float) ($openingItem->gross_pay ?? 0),
                (float) $closingItem->gross_pay,
                (float) ($closingItem->monthly_rate ?: ($openingItem->monthly_rate ?? 0))
            );

            $old = $this->cashSnapshot($closingItem);

            // Exact monthly liability less whatever was already posted/withheld
            // in the opening cutoff. Signed negative values are valid true-up
            // credits when the opening cutoff over-withheld a program.
            $statutoryCurrent = [
                'sss_employee' => $this->delta(
                    (float) $calculation['sss_employee'],
                    (float) ($openingItem->sss_employee ?? 0)
                ),
                'sss_employer' => $this->delta(
                    (float) $calculation['sss_employer'],
                    (float) ($openingItem->sss_employer ?? 0)
                ),
                'sss_ec' => $this->delta(
                    (float) $calculation['sss_ec'],
                    (float) ($openingItem->sss_ec ?? 0)
                ),
                'philhealth_employee' => $this->delta(
                    (float) $calculation['philhealth_employee'],
                    (float) ($openingItem->philhealth_employee ?? 0)
                ),
                'philhealth_employer' => $this->delta(
                    (float) $calculation['philhealth_employer'],
                    (float) ($openingItem->philhealth_employer ?? 0)
                ),
                'pagibig_employee' => $this->delta(
                    (float) $calculation['pagibig_employee'],
                    (float) ($openingItem->pagibig_employee ?? 0)
                ),
                'pagibig_employer' => $this->delta(
                    (float) $calculation['pagibig_employer'],
                    (float) ($openingItem->pagibig_employer ?? 0)
                ),
                'withholding_tax' => round((float) ($closingItem->withholding_tax ?? 0), 2),
            ];

            $statutoryCurrent['total_employee_government_deductions'] = round(
                $statutoryCurrent['sss_employee']
                + $statutoryCurrent['philhealth_employee']
                + $statutoryCurrent['pagibig_employee']
                + $statutoryCurrent['withholding_tax'],
                2
            );

            $statutoryCurrent['total_employer_government_contributions'] = round(
                $statutoryCurrent['sss_employer']
                + $statutoryCurrent['sss_ec']
                + $statutoryCurrent['philhealth_employer']
                + $statutoryCurrent['pagibig_employer'],
                2
            );

            $settlement = $closingItem->benefitSettlement()->first();

            $governmentForSettlement = array_merge($calculation, $statutoryCurrent);
            $settled = $this->settlementService->apply(
                $governmentForSettlement,
                (float) $closingItem->gross_pay,
                (float) $closingItem->other_deductions,
                $settlement
            );

            $new = [
                'sss_employee' => round((float) ($settled['sss_employee'] ?? 0), 2),
                'sss_employer' => round((float) $statutoryCurrent['sss_employer'], 2),
                'sss_ec' => round((float) $statutoryCurrent['sss_ec'], 2),
                'philhealth_employee' => round((float) ($settled['philhealth_employee'] ?? 0), 2),
                'philhealth_employer' => round((float) $statutoryCurrent['philhealth_employer'], 2),
                'pagibig_employee' => round((float) ($settled['pagibig_employee'] ?? 0), 2),
                'pagibig_employer' => round((float) $statutoryCurrent['pagibig_employer'], 2),
                'total_employee_government_deductions' => round((float) ($settled['total_employee_government_deductions'] ?? 0), 2),
                'total_employer_government_contributions' => round((float) $statutoryCurrent['total_employer_government_contributions'], 2),
                'net_pay' => round((float) data_get(
                    $settled,
                    'settlement_meta.net_pay_after_settlement',
                    (float) $closingItem->gross_pay
                        - (float) $closingItem->other_deductions
                        - (float) ($settled['total_employee_government_deductions'] ?? 0)
                ), 2),
            ];

            $collection = $this->monthlyCollectionSummary(
                $calculation,
                $openingItem,
                $new
            );

            $meta = is_array($closingItem->meta) ? $closingItem->meta : [];

            $governmentAfterSettlement = $calculation;
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
                $governmentAfterSettlement[$field] = $new[$field];
            }

            $settlementMeta = (array) ($settled['settlement_meta'] ?? []);
            $settlementMeta = array_merge($settlementMeta, [
                'reconciled_reason' => $reason,
                'sss_employee_statutory_due' => round((float) ($calculation['sss_employee'] ?? 0), 2),
                'philhealth_employee_statutory_due' => round((float) ($calculation['philhealth_employee'] ?? 0), 2),
                'pagibig_employee_statutory_due' => round((float) ($calculation['pagibig_employee'] ?? 0), 2),
                'opening_employee_share_collected' => $collection['opening'],
                'closing_employee_cash_effect' => $collection['closing'],
                'monthly_employee_share_collected' => $collection['collected'],
                'employee_share_unrecovered_by_program' => $collection['unrecovered'],
                'employee_share_unrecovered' => $collection['unrecovered_total'],
                'settlement_status' => $collection['status'],
                'manual_reimbursement_caps' => [
                    'sss' => $this->reimbursementCap(
                        (float) ($openingItem->sss_employee ?? 0),
                        (float) ($calculation['sss_employee'] ?? 0)
                    ),
                    'philhealth' => $this->reimbursementCap(
                        (float) ($openingItem->philhealth_employee ?? 0),
                        (float) ($calculation['philhealth_employee'] ?? 0)
                    ),
                    'pagibig' => $this->reimbursementCap(
                        (float) ($openingItem->pagibig_employee ?? 0),
                        (float) ($calculation['pagibig_employee'] ?? 0)
                    ),
                ],
            ]);

            $meta['government_raw_before_schedule'] = $calculation;
            $meta['government_after_profile_schedule'] = $governmentAfterSettlement;
            $meta['government_settlement'] = $settlementMeta;
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
                'statutory_current_cutoff_before_settlement' => $statutoryCurrent,
                'new_values' => $new,
            ];

            $closingItem->fill($new);
            $closingItem->meta = $meta;
            $closingItem->save();

            // Keep the detailed payment/deduction ledger synchronized with the
            // reconciled item. This is especially important when Auto Cap,
            // Employer Advance, or a reimbursement changes the employee cash
            // effect after the original draft payroll item was generated.
            $this->paymentLogService->logPayrollItem(
                $closingPayroll,
                $closingItem,
                (array) data_get($meta, 'salary_deductions', []),
                auth()->id() ?: ($closingPayroll->generated_by ? (int) $closingPayroll->generated_by : null)
            );

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
                'settlement_mode' => $settlementMeta['mode'] ?? 'auto_cap',
                'employee_share_unrecovered' => $collection['unrecovered_total'],
                'old' => $old,
                'statutory' => $statutoryCurrent,
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

    private function cashSnapshot(PayrollItem $item): array
    {
        return [
            'sss_employee' => round((float) $item->sss_employee, 2),
            'sss_employer' => round((float) $item->sss_employer, 2),
            'sss_ec' => round((float) $item->sss_ec, 2),
            'philhealth_employee' => round((float) $item->philhealth_employee, 2),
            'philhealth_employer' => round((float) $item->philhealth_employer, 2),
            'pagibig_employee' => round((float) $item->pagibig_employee, 2),
            'pagibig_employer' => round((float) $item->pagibig_employer, 2),
            'total_employee_government_deductions' => round((float) $item->total_employee_government_deductions, 2),
            'total_employer_government_contributions' => round((float) $item->total_employer_government_contributions, 2),
            'net_pay' => round((float) $item->net_pay, 2),
        ];
    }

    private function monthlyCollectionSummary(
        array $calculation,
        ?PayrollItem $openingItem,
        array $closingCash
    ): array {
        $map = [
            'sss' => 'sss_employee',
            'philhealth' => 'philhealth_employee',
            'pagibig' => 'pagibig_employee',
        ];

        $opening = [];
        $closing = [];
        $collected = [];
        $unrecovered = [];

        foreach ($map as $program => $field) {
            $liability = round(max(0, (float) ($calculation[$field] ?? 0)), 2);
            $openingValue = round((float) ($openingItem->{$field} ?? 0), 2);
            $closingValue = round((float) ($closingCash[$field] ?? 0), 2);
            $fundedByEmployee = round($openingValue + $closingValue, 2);
            $collectedValue = round(max(0, min($liability, $fundedByEmployee)), 2);

            $opening[$program] = $openingValue;
            $closing[$program] = $closingValue;
            $collected[$program] = $collectedValue;
            $unrecovered[$program] = round(max(0, $liability - $collectedValue), 2);
        }

        $unrecoveredTotal = round(array_sum($unrecovered), 2);

        return [
            'opening' => $opening,
            'closing' => $closing,
            'collected' => $collected,
            'unrecovered' => $unrecovered,
            'unrecovered_total' => $unrecoveredTotal,
            'status' => $unrecoveredTotal > 0.009 ? 'employer_advanced_or_unrecovered' : 'complete',
        ];
    }

    private function reimbursementCap(float $openingCollected, float $monthlyLiability): float
    {
        return round(min(
            max(0, $openingCollected),
            max(0, $monthlyLiability)
        ), 2);
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
