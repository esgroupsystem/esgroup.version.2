<?php

namespace App\Services\Payroll;

class GovernmentDeductionService
{
    public function compute(array $data): array
    {
        $monthlyBasic = round((float) ($data['monthly_basic'] ?? 0), 2);
        $sssMonthlyBasic = round((float) ($data['sss_monthly_basic'] ?? $monthlyBasic), 2);
        $philHealthMonthlyBasic = round((float) ($data['philhealth_monthly_basic'] ?? $monthlyBasic), 2);
        $pagibigMonthlyBasic = round((float) ($data['pagibig_monthly_basic'] ?? $monthlyBasic), 2);

        $sss = $this->computeSss($sssMonthlyBasic);
        $philhealth = $this->computePhilHealth($philHealthMonthlyBasic);
        $pagibig = $this->computePagIbig($pagibigMonthlyBasic);

        return $this->total([
            'sss_employee' => $sss['employee'],
            'sss_employer' => $sss['employer'],
            'philhealth_employee' => $philhealth['employee'],
            'philhealth_employer' => $philhealth['employer'],
            'pagibig_employee' => $pagibig['employee'],
            'pagibig_employer' => $pagibig['employer'],
            'withholding_tax' => 0.00,
            'taxable_cutoff_compensation' => round((float) ($data['taxable_cutoff_compensation'] ?? 0), 2),
        ]);
    }

    public function applyDeductionSchedule(
        array $monthlyGovernment,
        string $cutoffType,
        array $profileSchedules = [],
        ?float $taxableCutoffCompensation = null
    ): array {
        $scheduleConfig = config('payroll.government_deduction_schedule', []);

        $schedules = [
            'sss' => $profileSchedules['sss']
                ?? $scheduleConfig['sss']
                ?? 'first_cutoff',

            'philhealth' => $profileSchedules['philhealth']
                ?? $scheduleConfig['philhealth']
                ?? 'second_cutoff',

            'pagibig' => $profileSchedules['pagibig']
                ?? $scheduleConfig['pagibig']
                ?? 'second_cutoff',
        ];

        $government = [
            'sss_employee' => $this->scheduledMonthlyContribution(
                (float) ($monthlyGovernment['sss_employee'] ?? 0),
                $schedules['sss'],
                $cutoffType
            ),
            'sss_employer' => $this->scheduledMonthlyContribution(
                (float) ($monthlyGovernment['sss_employer'] ?? 0),
                $schedules['sss'],
                $cutoffType
            ),

            'philhealth_employee' => $this->scheduledMonthlyContribution(
                (float) ($monthlyGovernment['philhealth_employee'] ?? 0),
                $schedules['philhealth'],
                $cutoffType
            ),
            'philhealth_employer' => $this->scheduledMonthlyContribution(
                (float) ($monthlyGovernment['philhealth_employer'] ?? 0),
                $schedules['philhealth'],
                $cutoffType
            ),

            'pagibig_employee' => $this->scheduledMonthlyContribution(
                (float) ($monthlyGovernment['pagibig_employee'] ?? 0),
                $schedules['pagibig'],
                $cutoffType
            ),
            'pagibig_employer' => $this->scheduledMonthlyContribution(
                (float) ($monthlyGovernment['pagibig_employer'] ?? 0),
                $schedules['pagibig'],
                $cutoffType
            ),

            'withholding_tax' => 0.00,
        ];

        $taxableCompensation = round(
            (float) ($taxableCutoffCompensation ?? $monthlyGovernment['taxable_cutoff_compensation'] ?? 0),
            2
        );

        $government['schedule_meta'] = [
            'sss' => [
                'schedule' => $this->normalizeSchedule($schedules['sss']),
                'monthly_employee_share' => round((float) ($monthlyGovernment['sss_employee'] ?? 0), 2),
                'deducted_employee_share' => $government['sss_employee'],
            ],
            'philhealth' => [
                'schedule' => $this->normalizeSchedule($schedules['philhealth']),
                'monthly_employee_share' => round((float) ($monthlyGovernment['philhealth_employee'] ?? 0), 2),
                'deducted_employee_share' => $government['philhealth_employee'],
            ],
            'pagibig' => [
                'schedule' => $this->normalizeSchedule($schedules['pagibig']),
                'monthly_employee_share' => round((float) ($monthlyGovernment['pagibig_employee'] ?? 0), 2),
                'deducted_employee_share' => $government['pagibig_employee'],
            ],
            'withholding_tax' => [
                'schedule' => 'none',
                'taxable_before_government' => $taxableCompensation,
                'deducted_tax' => 0.00,
                'remarks' => 'Disabled by company policy. Tax is not deducted in payroll.',
            ],
        ];

        foreach (['sss_basis', 'philhealth_basis', 'pagibig_basis'] as $key) {
            if (array_key_exists($key, $monthlyGovernment)) {
                $government[$key] = $monthlyGovernment[$key];
            }
        }

        return $this->total($government);
    }

    protected function scheduledMonthlyContribution(float $monthlyAmount, ?string $schedule, string $cutoffType): float
    {
        $schedule = $this->normalizeSchedule($schedule);

        return match ($schedule) {
            'none' => 0.00,
            'first' => $cutoffType === 'first' ? round($monthlyAmount, 2) : 0.00,
            'second' => $cutoffType === 'second' ? round($monthlyAmount, 2) : 0.00,
            'every' => round($monthlyAmount / 2, 2),
            default => round($monthlyAmount, 2),
        };
    }

    protected function normalizeSchedule(?string $schedule): string
    {
        $schedule = strtolower(trim((string) $schedule));
        $schedule = str_replace([' ', '-', '/'], '_', $schedule);
        $schedule = preg_replace('/_+/', '_', $schedule);

        return match ($schedule) {
            'none',
            'no',
            'no_deduction',
            'not_applicable',
            'n_a',
            'na' => 'none',

            'first',
            '1st',
            'first_cutoff',
            '1st_cutoff',
            'first_cutoff_only',
            '1st_cutoff_only' => 'first',

            'second',
            '2nd',
            'second_cutoff',
            '2nd_cutoff',
            'second_cutoff_only',
            '2nd_cutoff_only' => 'second',

            'every',
            'every_cutoff',
            'per_cutoff',
            'each_cutoff',
            'both',
            'monthly',
            'all' => 'every',

            default => 'every',
        };
    }

    protected function total(array $government): array
    {
        foreach ([
            'sss_employee',
            'sss_employer',
            'philhealth_employee',
            'philhealth_employer',
            'pagibig_employee',
            'pagibig_employer',
        ] as $key) {
            $government[$key] = round((float) ($government[$key] ?? 0), 2);
        }

        $government['withholding_tax'] = 0.00;

        $government['total_employee_government_deductions'] = round(
            $government['sss_employee']
            + $government['philhealth_employee']
            + $government['pagibig_employee'],
            2
        );

        $government['total_employer_government_contributions'] = round(
            $government['sss_employer']
            + $government['philhealth_employer']
            + $government['pagibig_employer'],
            2
        );

        return $government;
    }

    protected function computeSss(float $monthlyBasic): array
    {
        $compensation = round($monthlyBasic, 2);

        if ($compensation <= 0) {
            return ['employee' => 0.00, 'employer' => 0.00];
        }

        if ($compensation <= 5249.99) {
            $totalMsc = 5000;
        } elseif ($compensation >= 34750) {
            $totalMsc = 35000;
        } else {
            $step = (int) floor(($compensation - 5250) / 500) + 1;
            $totalMsc = 5000 + ($step * 500);
        }

        $employee = $totalMsc * 0.05;
        $employer = $totalMsc * 0.10;
        $ec = $totalMsc <= 14500 ? 10 : 30;

        return [
            'employee' => round($employee, 2),
            'employer' => round($employer + $ec, 2),
        ];
    }

    protected function computePhilHealth(float $monthlyBasic): array
    {
        if ($monthlyBasic <= 0) {
            return ['employee' => 0.00, 'employer' => 0.00];
        }

        $salaryBase = min(max($monthlyBasic, 10000), 100000);
        $monthlyPremium = $salaryBase * 0.05;

        return [
            'employee' => round($monthlyPremium / 2, 2),
            'employer' => round($monthlyPremium / 2, 2),
        ];
    }

    protected function computePagIbig(float $monthlyBasic): array
    {
        if ($monthlyBasic <= 0) {
            return ['employee' => 0.00, 'employer' => 0.00];
        }

        $fundSalary = min($monthlyBasic, 10000);
        $employeeRate = $monthlyBasic <= 1500 ? 0.01 : 0.02;
        $employerRate = 0.02;

        return [
            'employee' => round($fundSalary * $employeeRate, 2),
            'employer' => round($fundSalary * $employerRate, 2),
        ];
    }
}
