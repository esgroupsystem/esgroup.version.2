<?php

namespace App\Services\Payroll;

use App\Models\PayrollBenefitSettlement;
use Illuminate\Validation\ValidationException;

class GovernmentDeductionSettlementService
{
    /**
     * Apply the PAYROLL-COLLECTION policy to already-computed statutory shares.
     *
     * Architecture:
     * - The exact statutory monthly liability is calculated elsewhere and is
     *   preserved in Benefits Records.
     * - This service controls only the employee-share CASH effect in the current
     *   payroll: collection, employer advance, and approved reimbursement.
     * - Employer shares and SSS EC are never reduced by this layer.
     * - Reimbursements are applied AFTER positive current-cutoff collection is
     *   capped/advanced so a reimbursement is a real payroll credit, not merely
     *   a cancellation of another current deduction.
     */
    public function apply(
        array $government,
        float $grossPay,
        float $otherDeductions,
        ?PayrollBenefitSettlement $settlement = null
    ): array {
        $mode = $settlement?->mode ?: PayrollBenefitSettlement::MODE_AUTO_CAP;

        if (! in_array($mode, PayrollBenefitSettlement::MODES, true)) {
            $mode = PayrollBenefitSettlement::MODE_AUTO_CAP;
        }

        $statutoryBeforeSettlement = [];
        foreach ($this->employeeFields() as $field) {
            $statutoryBeforeSettlement[$field] = round((float) ($government[$field] ?? 0), 2);
        }

        $reimbursements = [
            'sss_employee' => round(max(0, (float) ($settlement?->sss_employee_reimbursement ?? 0)), 2),
            'philhealth_employee' => round(max(0, (float) ($settlement?->philhealth_employee_reimbursement ?? 0)), 2),
            'pagibig_employee' => round(max(0, (float) ($settlement?->pagibig_employee_reimbursement ?? 0)), 2),
        ];

        // First determine how much of the positive current-cutoff statutory due
        // can be withheld from this payroll. Existing negative true-up credits
        // remain intact in every mode.
        if ($mode === PayrollBenefitSettlement::MODE_EMPLOYER_ADVANCE) {
            foreach ($this->employeeFields() as $field) {
                if ((float) ($government[$field] ?? 0) > 0) {
                    $government[$field] = 0.00;
                }
            }
        } elseif ($mode === PayrollBenefitSettlement::MODE_AUTO_CAP) {
            $government = $this->capPositiveEmployeeDeductions(
                $government,
                $grossPay,
                $otherDeductions
            );
        }

        // Then apply HR-approved reimbursement as an actual payroll credit.
        // Example: Employer Advance + PHP 250 PhilHealth reimbursement results
        // in -250.00 current-payroll PhilHealth cash effect, not zero.
        foreach ($reimbursements as $field => $amount) {
            if ($amount > 0) {
                $government[$field] = round((float) ($government[$field] ?? 0) - $amount, 2);
            }
        }

        $government = $this->refreshTotals($government);
        $netPay = round(
            $grossPay
            - $otherDeductions
            - (float) $government['total_employee_government_deductions'],
            2
        );

        if ($mode === PayrollBenefitSettlement::MODE_COLLECT_FULL && $netPay < 0) {
            throw ValidationException::withMessages([
                'mode' => sprintf(
                    'Full government collection would make this employee net pay negative by PHP %s. Choose Auto Cap or Employer Advance instead.',
                    number_format(abs($netPay), 2)
                ),
            ]);
        }

        if ($netPay > -0.01 && $netPay < 0) {
            $netPay = 0.00;
        }

        $applied = [];
        $positiveCollected = [];
        foreach ($this->employeeFields() as $field) {
            $applied[$field] = round((float) ($government[$field] ?? 0), 2);
            $positiveCollected[$field] = round(max(0, $applied[$field]), 2);
        }

        $government['settlement_meta'] = [
            'mode' => $mode,
            'settlement_id' => $settlement?->id,
            'reason' => $settlement?->reason,
            'gross_available_before_government' => round(max(0, $grossPay - $otherDeductions), 2),
            'statutory_current_cutoff_before_settlement' => $statutoryBeforeSettlement,
            'employee_reimbursements' => $reimbursements,
            'payroll_collection_after_settlement' => $applied,
            'positive_employee_share_collected_this_cutoff' => $positiveCollected,
            'net_pay_after_settlement' => $netPay,
            'statutory_liability_preserved' => true,
        ];

        return $government;
    }

    private function capPositiveEmployeeDeductions(
        array $government,
        float $grossPay,
        float $otherDeductions
    ): array {
        $available = round(max(0, $grossPay - $otherDeductions), 2);

        // Existing negative statutory true-up values are credits and therefore
        // increase cash available before positive withholding is capped.
        foreach ($this->employeeFields() as $field) {
            $value = round((float) ($government[$field] ?? 0), 2);

            if ($value < 0) {
                $available = round($available + abs($value), 2);
            }
        }

        foreach ($this->employeeFields() as $field) {
            $value = round((float) ($government[$field] ?? 0), 2);

            if ($value <= 0) {
                continue;
            }

            $deduct = min($value, $available);
            $government[$field] = round($deduct, 2);
            $available = round(max(0, $available - $deduct), 2);
        }

        return $government;
    }

    private function refreshTotals(array $government): array
    {
        $government['total_employee_government_deductions'] = round(
            (float) ($government['sss_employee'] ?? 0)
            + (float) ($government['philhealth_employee'] ?? 0)
            + (float) ($government['pagibig_employee'] ?? 0)
            + (float) ($government['withholding_tax'] ?? 0),
            2
        );

        $government['total_employer_government_contributions'] = round(
            (float) ($government['sss_employer'] ?? 0)
            + (float) ($government['sss_ec'] ?? 0)
            + (float) ($government['philhealth_employer'] ?? 0)
            + (float) ($government['pagibig_employer'] ?? 0),
            2
        );

        return $government;
    }

    private function employeeFields(): array
    {
        return [
            'sss_employee',
            'philhealth_employee',
            'pagibig_employee',
        ];
    }
}
