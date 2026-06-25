<?php

namespace App\Services\Payroll;

use App\Models\PayrollEmployeeSalary;
use Carbon\Carbon;

class PayrollDeductionService
{
    public const SCHEDULE_NONE = 'none';

    public const SCHEDULE_FIRST = 'first_cutoff';

    public const SCHEDULE_SECOND = 'second_cutoff';

    public const SCHEDULE_EVERY = 'every_cutoff';

    private const WORKING_DAYS_PER_MONTH = 22;

    private const HOURS_PER_DAY = 9;

    private const MINUTES_PER_HOUR = 60;

    public function computeRates(float $basicSalary, string $rateType): array
    {
        if ($basicSalary <= 0) {
            return [
                'daily_rate' => 0,
                'hourly_rate' => 0,
                'per_minute_rate' => 0,
            ];
        }

        $dailyRate = $rateType === 'monthly'
            ? $basicSalary / self::WORKING_DAYS_PER_MONTH
            : $basicSalary;

        $hourlyRate = $dailyRate / self::HOURS_PER_DAY;
        $perMinuteRate = $hourlyRate / self::MINUTES_PER_HOUR;

        return [
            'daily_rate' => round($dailyRate, 2),
            'hourly_rate' => round($hourlyRate, 2),
            'per_minute_rate' => round($perMinuteRate, 4),
        ];
    }

    public function monthlyBasicSalary(PayrollEmployeeSalary $salary): float
    {
        $basicSalary = (float) $salary->basic_salary;

        if ($basicSalary <= 0) {
            return 0;
        }

        return $salary->rate_type === 'monthly'
            ? round($basicSalary, 2)
            : round($basicSalary * self::WORKING_DAYS_PER_MONTH, 2);
    }

    public function salaryPreview(PayrollEmployeeSalary $salary): array
    {
        $monthlyBasic = $this->monthlyBasicSalary($salary);

        $monthlyGovernment = [
            'sss' => $this->computeSssEmployeeShare($monthlyBasic),
            'pagibig' => $this->computePagibigEmployeeShare($monthlyBasic),
            'philhealth' => $this->computePhilhealthEmployeeShare($monthlyBasic),
        ];

        $firstGovernment = [
            'sss' => $this->monthlyToCutoffAmount($monthlyGovernment['sss'], $salary->sss_contribution_cutoff, 'first'),
            'pagibig' => $this->monthlyToCutoffAmount($monthlyGovernment['pagibig'], $salary->pagibig_contribution_cutoff, 'first'),
            'philhealth' => $this->monthlyToCutoffAmount($monthlyGovernment['philhealth'], $salary->philhealth_contribution_cutoff, 'first'),
        ];

        $secondGovernment = [
            'sss' => $this->monthlyToCutoffAmount($monthlyGovernment['sss'], $salary->sss_contribution_cutoff, 'second'),
            'pagibig' => $this->monthlyToCutoffAmount($monthlyGovernment['pagibig'], $salary->pagibig_contribution_cutoff, 'second'),
            'philhealth' => $this->monthlyToCutoffAmount($monthlyGovernment['philhealth'], $salary->philhealth_contribution_cutoff, 'second'),
        ];

        $firstAllowance = [
            'regular' => $this->monthlyToCutoffAmount((float) $salary->allowance, $salary->allowance_release_schedule, 'first'),
            'sim_load' => $this->monthlyToCutoffAmount((float) $salary->sim_load_allowance, $salary->sim_load_release_schedule, 'first'),
        ];

        $secondAllowance = [
            'regular' => $this->monthlyToCutoffAmount((float) $salary->allowance, $salary->allowance_release_schedule, 'second'),
            'sim_load' => $this->monthlyToCutoffAmount((float) $salary->sim_load_allowance, $salary->sim_load_release_schedule, 'second'),
        ];

        $firstLoans = $this->loanCutoffAmounts($salary, 'first');
        $secondLoans = $this->loanCutoffAmounts($salary, 'second');

        $firstOtherDeductions = $this->otherDeductionCutoffAmounts($salary, 'first');
        $secondOtherDeductions = $this->otherDeductionCutoffAmounts($salary, 'second');

        $firstGrossPreview = round(($monthlyBasic / 2) + array_sum($firstAllowance), 2);
        $secondGrossPreview = round(($monthlyBasic / 2) + array_sum($secondAllowance), 2);

        $firstDeductions = round(
            array_sum($firstGovernment)
            + array_sum($firstLoans)
            + $firstOtherDeductions['total'],
            2
        );

        $secondDeductions = round(
            array_sum($secondGovernment)
            + array_sum($secondLoans)
            + $secondOtherDeductions['total'],
            2
        );

        return [
            'monthly_basic' => round($monthlyBasic, 2),
            'monthly_government' => $monthlyGovernment,

            'first' => [
                'gross_preview' => $firstGrossPreview,
                'government' => $firstGovernment,
                'allowance' => $firstAllowance,
                'loans' => $firstLoans,
                'total_deductions' => $firstDeductions,
                'other_deductions' => $firstOtherDeductions,
                'net_preview' => round($firstGrossPreview - $firstDeductions, 2),
            ],

            'second' => [
                'gross_preview' => $secondGrossPreview,
                'government' => $secondGovernment,
                'allowance' => $secondAllowance,
                'loans' => $secondLoans,
                'total_deductions' => $secondDeductions,
                'other_deductions' => $secondOtherDeductions,
                'net_preview' => round($secondGrossPreview - $secondDeductions, 2),
            ],

            'last_payment' => [
                'sss_loan' => $this->estimatedLastPaymentDate(
                    $salary->sss_loan_start_date,
                    (float) $salary->sss_loan_total_amount,
                    (float) $salary->sss_loan_payment_amount,
                    $salary->sss_loan_deduction_schedule
                ),
                'pagibig_loan' => $this->estimatedLastPaymentDate(
                    $salary->pagibig_loan_start_date,
                    (float) $salary->pagibig_loan_total_amount,
                    (float) $salary->pagibig_loan_payment_amount,
                    $salary->pagibig_loan_deduction_schedule
                ),
                'philhealth_loan' => $this->estimatedLastPaymentDate(
                    $salary->philhealth_loan_start_date,
                    (float) $salary->philhealth_loan_total_amount,
                    (float) $salary->philhealth_loan_payment_amount,
                    $salary->philhealth_loan_deduction_schedule
                ),
                'cash_advance' => $this->estimatedLastPaymentDate(
                    $salary->cash_advance_start_date,
                    (float) $salary->cash_advance_total_amount,
                    (float) $salary->cash_advance_payment_amount,
                    $salary->cash_advance_deduction_schedule
                ),
                'other_loan' => $this->estimatedLastPaymentDate(
                    $salary->other_loan_start_date,
                    (float) $salary->other_loan_total_amount,
                    (float) $salary->other_loan_payment_amount,
                    $salary->other_loan_deduction_schedule
                ),
                'other_deductions' => $this->otherDeductionLastPayments($salary),
            ],
        ];
    }

    public function computeSssEmployeeShare(float $monthlySalary): float
    {
        if ($monthlySalary <= 0) {
            return 0;
        }

        $msc = $this->sssMonthlySalaryCredit($monthlySalary);

        return round($msc * 0.05, 2);
    }

    public function computePagibigEmployeeShare(float $monthlySalary): float
    {
        if ($monthlySalary <= 0) {
            return 0;
        }

        $baseSalary = min($monthlySalary, 10000);
        $rate = $baseSalary <= 1500 ? 0.01 : 0.02;

        return round($baseSalary * $rate, 2);
    }

    public function computePhilhealthEmployeeShare(float $monthlySalary): float
    {
        if ($monthlySalary <= 0) {
            return 0;
        }

        $baseSalary = min(max($monthlySalary, 10000), 100000);
        $totalMonthlyPremium = $baseSalary * 0.05;

        return round($totalMonthlyPremium / 2, 2);
    }

    public function monthlyToCutoffAmount(float $monthlyAmount, string $schedule, string $cutoff): float
    {
        if ($monthlyAmount <= 0 || $schedule === self::SCHEDULE_NONE) {
            return 0;
        }

        if ($schedule === self::SCHEDULE_EVERY) {
            return round($monthlyAmount / 2, 2);
        }

        if ($schedule === self::SCHEDULE_FIRST && $cutoff === 'first') {
            return round($monthlyAmount, 2);
        }

        if ($schedule === self::SCHEDULE_SECOND && $cutoff === 'second') {
            return round($monthlyAmount, 2);
        }

        return 0;
    }

    public function fixedDeductionForCutoff(float $paymentAmount, string $schedule, string $cutoff): float
    {
        if ($paymentAmount <= 0 || $schedule === self::SCHEDULE_NONE) {
            return 0;
        }

        if ($schedule === self::SCHEDULE_EVERY) {
            return round($paymentAmount, 2);
        }

        if ($schedule === self::SCHEDULE_FIRST && $cutoff === 'first') {
            return round($paymentAmount, 2);
        }

        if ($schedule === self::SCHEDULE_SECOND && $cutoff === 'second') {
            return round($paymentAmount, 2);
        }

        return 0;
    }

    public function estimatedLastPaymentDate(
        mixed $startDate,
        float $totalAmount,
        float $paymentAmount,
        string $schedule
    ): ?string {
        if ($totalAmount <= 0 || $paymentAmount <= 0 || $schedule === self::SCHEDULE_NONE) {
            return null;
        }

        $paymentCount = (int) ceil($totalAmount / $paymentAmount);

        $cursor = $startDate
            ? Carbon::parse($startDate)->startOfDay()->subDay()
            : now()->startOfDay()->subDay();

        for ($i = 0; $i < $paymentCount; $i++) {
            $cursor = $this->nextCutoffDate($cursor, $schedule);
        }

        return $cursor->format('M d, Y');
    }

    private function sssMonthlySalaryCredit(float $monthlySalary): float
    {
        if ($monthlySalary < 5250) {
            return 5000;
        }

        if ($monthlySalary >= 34750) {
            return 35000;
        }

        return round($monthlySalary / 500) * 500;
    }

    private function loanCutoffAmounts(PayrollEmployeeSalary $salary, string $cutoff): array
    {
        return [
            'sss_loan' => $this->fixedDeductionForCutoff(
                (float) $salary->sss_loan_payment_amount,
                $salary->sss_loan_deduction_schedule,
                $cutoff
            ),
            'pagibig_loan' => $this->fixedDeductionForCutoff(
                (float) $salary->pagibig_loan_payment_amount,
                $salary->pagibig_loan_deduction_schedule,
                $cutoff
            ),
            'philhealth_loan' => $this->fixedDeductionForCutoff(
                (float) $salary->philhealth_loan_payment_amount,
                $salary->philhealth_loan_deduction_schedule,
                $cutoff
            ),
            'cash_advance' => $this->fixedDeductionForCutoff(
                (float) $salary->cash_advance_payment_amount,
                $salary->cash_advance_deduction_schedule,
                $cutoff
            ),
            'other_loan' => $this->fixedDeductionForCutoff(
                (float) $salary->other_loan_payment_amount,
                $salary->other_loan_deduction_schedule,
                $cutoff
            ),
        ];
    }

    private function otherDeductionCutoffAmounts(PayrollEmployeeSalary $salary, string $cutoff): array
    {
        $salary->loadMissing('otherDeductions');

        $items = [];
        $total = 0;

        foreach ($salary->otherDeductions as $deduction) {
            if (! $deduction->is_active) {
                continue;
            }

            $amount = $this->fixedDeductionForCutoff(
                (float) $deduction->payment_amount,
                $deduction->deduction_schedule,
                $cutoff
            );

            $items[$deduction->id] = [
                'name' => $deduction->name,
                'amount' => $amount,
            ];

            $total += $amount;
        }

        return [
            'items' => $items,
            'total' => round($total, 2),
        ];
    }

    private function otherDeductionLastPayments(PayrollEmployeeSalary $salary): array
    {
        $salary->loadMissing('otherDeductions');

        $items = [];

        foreach ($salary->otherDeductions as $deduction) {
            $items[$deduction->id] = $this->estimatedLastPaymentDate(
                $deduction->start_date,
                (float) $deduction->total_amount,
                (float) $deduction->payment_amount,
                $deduction->deduction_schedule
            );
        }

        return $items;
    }

    private function nextCutoffDate(Carbon $afterDate, string $schedule): Carbon
    {
        $allowedDays = match ($schedule) {
            self::SCHEDULE_FIRST => [25],
            self::SCHEDULE_SECOND => [11],
            default => [11, 25],
        };

        $candidates = [];

        for ($monthOffset = 0; $monthOffset <= 3; $monthOffset++) {
            foreach ($allowedDays as $day) {
                $candidate = $afterDate->copy()
                    ->firstOfMonth()
                    ->addMonthsNoOverflow($monthOffset)
                    ->day($day)
                    ->startOfDay();

                if ($candidate->greaterThan($afterDate)) {
                    $candidates[] = $candidate;
                }
            }
        }

        usort($candidates, fn (Carbon $a, Carbon $b): int => $a->timestamp <=> $b->timestamp);

        return $candidates[0];
    }
}
