<?php

namespace App\Services\Payroll;

use App\Models\PaymentLog;
use App\Models\Payroll;
use App\Models\PayrollItem;

class PaymentLogService
{
    public function logPayrollItem(Payroll $payroll, PayrollItem $item, array $salaryDeductions, ?int $userId = null): void
    {
        $this->deleteExistingDraftLogs($payroll, $item);
        $this->logGovernment($payroll, $item, $userId);
        $this->logSalaryDeductions($payroll, $item, $salaryDeductions, $userId);
    }

    protected function deleteExistingDraftLogs(Payroll $payroll, PayrollItem $item): void
    {
        PaymentLog::query()
            ->where('payroll_id', $payroll->id)
            ->where('payroll_item_id', $item->id)
            ->delete();
    }

    protected function logGovernment(Payroll $payroll, PayrollItem $item, ?int $userId): void
    {
        $governmentRows = [
            'sss' => [
                'name' => 'SSS Contribution',
                'employee' => (float) $item->sss_employee,
                'employer' => (float) $item->sss_employer,
                'basis' => data_get($item->meta, 'government_raw_before_schedule.sss_basis'),
            ],
            'philhealth' => [
                'name' => 'PhilHealth Contribution',
                'employee' => (float) $item->philhealth_employee,
                'employer' => (float) $item->philhealth_employer,
                'basis' => data_get($item->meta, 'government_raw_before_schedule.philhealth_basis'),
            ],
            'pagibig' => [
                'name' => 'Pag-IBIG Contribution',
                'employee' => (float) $item->pagibig_employee,
                'employer' => (float) $item->pagibig_employer,
                'basis' => data_get($item->meta, 'government_raw_before_schedule.pagibig_basis'),
            ],
            'withholding_tax' => [
                'name' => 'Withholding Tax',
                'employee' => (float) $item->withholding_tax,
                'employer' => 0.00,
                'basis' => $item->taxable_compensation,
            ],
        ];

        foreach ($governmentRows as $sourceType => $row) {
            $employeeShare = round((float) $row['employee'], 2);
            $employerShare = round((float) $row['employer'], 2);
            $total = round($employeeShare + $employerShare, 2);

            if ($total <= 0) {
                continue;
            }

            $this->createLog($payroll, $item, [
                'log_type' => 'government',
                'source_type' => $sourceType,
                'source_name' => $row['name'],
                'amount' => $employeeShare,
                'employee_share' => $employeeShare,
                'employer_share' => $employerShare,
                'deduction_schedule' => $this->governmentScheduleFor($item, $sourceType),
                'remarks' => 'Government deduction/contribution posted from payroll generation.',
                'meta' => [
                    'basis' => round((float) ($row['basis'] ?? 0), 2),
                    'schedule_audit' => data_get($item->meta, 'government_schedule.'.$sourceType),
                ],
            ], $userId);
        }
    }

    protected function logSalaryDeductions(Payroll $payroll, PayrollItem $item, array $salaryDeductions, ?int $userId): void
    {
        foreach ($salaryDeductions as $deduction) {
            $amount = round((float) ($deduction['amount'] ?? 0), 2);

            if ($amount <= 0) {
                continue;
            }

            $this->createLog($payroll, $item, [
                'log_type' => 'deduction',
                'source_type' => (string) ($deduction['source_type'] ?? 'other_deduction'),
                'source_id' => $deduction['source_id'] ?? null,
                'source_name' => (string) ($deduction['name'] ?? 'Deduction'),
                'amount' => $amount,
                'employee_share' => $amount,
                'employer_share' => 0.00,
                'balance_before' => $deduction['balance_before'] ?? null,
                'balance_after' => $deduction['balance_after'] ?? null,
                'deduction_schedule' => $this->normalizeScheduleValue($deduction['deduction_schedule'] ?? null),
                'remarks' => $deduction['remarks'] ?? null,
                'meta' => $deduction,
            ], $userId);
        }
    }

    protected function createLog(Payroll $payroll, PayrollItem $item, array $payload, ?int $userId): PaymentLog
    {
        return PaymentLog::create([
            'payroll_id' => $payroll->id,
            'payroll_item_id' => $item->id,
            'employee_id' => $item->employee_id,
            'payroll_employee_salary_id' => $item->payroll_employee_salary_id,
            'biometric_employee_id' => $item->biometric_employee_id,
            'employee_no' => $item->employee_no,
            'employee_name' => $item->employee_name,

            'log_type' => (string) $payload['log_type'],
            'source_type' => (string) $payload['source_type'],
            'source_id' => $payload['source_id'] ?? null,
            'source_name' => $payload['source_name'] ?? null,

            /*
             | IMPORTANT:
             | payment_logs.deduction_schedule is a string column.
             | Never pass the full government schedule audit array here.
             */
            'deduction_schedule' => $this->normalizeScheduleValue($payload['deduction_schedule'] ?? null),

            'cutoff_month' => $payroll->cutoff_month,
            'cutoff_year' => $payroll->cutoff_year,
            'cutoff_type' => $payroll->cutoff_type,
            'contribution_month' => $payroll->contribution_month,
            'contribution_year' => $payroll->contribution_year,
            'period_start' => $payroll->period_start,
            'period_end' => $payroll->period_end,

            'amount' => round((float) ($payload['amount'] ?? 0), 2),
            'employee_share' => round((float) ($payload['employee_share'] ?? 0), 2),
            'employer_share' => round((float) ($payload['employer_share'] ?? 0), 2),

            'balance_before' => $payload['balance_before'] ?? null,
            'balance_after' => $payload['balance_after'] ?? null,
            'payment_no' => $payload['payment_no'] ?? null,
            'remaining_payments' => $payload['remaining_payments'] ?? null,
            'reference' => $payload['reference'] ?? $payroll->payroll_number,
            'remarks' => $payload['remarks'] ?? null,
            'posted_at' => now('Asia/Manila'),
            'created_by' => $userId,
            'meta' => $payload['meta'] ?? [],
        ]);
    }

    protected function governmentScheduleFor(PayrollItem $item, string $sourceType): string
    {
        $schedule = data_get($item->meta, 'government_schedule.'.$sourceType);

        if (is_array($schedule)) {
            return $this->normalizeScheduleValue($schedule['schedule'] ?? null);
        }

        if ($schedule !== null && trim((string) $schedule) !== '') {
            return $this->normalizeScheduleValue($schedule);
        }

        return $this->normalizeScheduleValue(
            config('payroll.government_deduction_schedule.'.$sourceType, 'per_cutoff')
        );
    }

    protected function normalizeScheduleValue(mixed $schedule): string
    {
        if (is_array($schedule)) {
            $schedule = $schedule['schedule']
                ?? $schedule['deduction_schedule']
                ?? $schedule['value']
                ?? null;
        }

        $schedule = strtolower(trim((string) ($schedule ?? 'per_cutoff')));

        return match ($schedule) {
            'first', '1st', 'first_cutoff', '1st_cutoff', '1st cutoff' => 'first',
            'second', '2nd', 'second_cutoff', '2nd_cutoff', '2nd cutoff' => 'second',
            'every_cutoff', 'per_cutoff', 'each_cutoff', 'both', 'every cutoff' => 'per_cutoff',
            'none', 'no_deduction', 'not_applicable', 'n/a', 'no deduction / not applicable' => 'no_deduction',
            default => $schedule,
        };
    }
}
