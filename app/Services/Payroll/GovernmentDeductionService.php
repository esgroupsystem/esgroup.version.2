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
        $semiMonthlyTaxableCompensation = round((float) ($data['taxable_cutoff_compensation'] ?? 0), 2);

        $sss = $this->computeSss($sssMonthlyBasic);
        $philhealth = $this->computePhilHealth($philHealthMonthlyBasic);
        $pagibig = $this->computePagIbig($pagibigMonthlyBasic);

        $withholdingTax = $this->computeSemiMonthlyWithholdingTax(
            max(
                0,
                $semiMonthlyTaxableCompensation
                - $sss['employee']
                - $philhealth['employee']
                - $pagibig['employee']
            )
        );

        return $this->total([
            'sss_employee' => $sss['employee'],
            'sss_employer' => $sss['employer'],
            'philhealth_employee' => $philhealth['employee'],
            'philhealth_employer' => $philhealth['employer'],
            'pagibig_employee' => $pagibig['employee'],
            'pagibig_employer' => $pagibig['employer'],
            'withholding_tax' => $withholdingTax,
        ]);
    }

    public function applyDeductionSchedule(array $government, string $cutoffType): array
    {
        $schedule = config('payroll.government_deduction_schedule', []);

        foreach (['sss', 'philhealth', 'pagibig', 'withholding_tax'] as $key) {
            $deductOn = (string) ($schedule[$key] ?? 'first');

            if (! $this->shouldDeductOnCutoff($deductOn, $cutoffType)) {
                if ($key === 'withholding_tax') {
                    $government['withholding_tax'] = 0;
                    continue;
                }

                $government[$key . '_employee'] = 0;
                $government[$key . '_employer'] = 0;
            }
        }

        return $this->total($government);
    }

    protected function shouldDeductOnCutoff(string $schedule, string $cutoffType): bool
    {
        return match ($schedule) {
            'both' => true,
            'none' => false,
            'second' => $cutoffType === 'second',
            default => $cutoffType === 'first',
        };
    }

    protected function total(array $government): array
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
            + (float) ($government['philhealth_employer'] ?? 0)
            + (float) ($government['pagibig_employer'] ?? 0),
            2
        );

        return $government;
    }

    protected function computeSss(float $monthlyBasic): array
    {
        $compensation = round($monthlyBasic, 2);

        if ($compensation <= 0) {
            return ['employee' => 0, 'employer' => 0];
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
            return ['employee' => 0, 'employer' => 0];
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
            return ['employee' => 0, 'employer' => 0];
        }

        $fundSalary = min($monthlyBasic, 10000);
        $employeeRate = $monthlyBasic <= 1500 ? 0.01 : 0.02;
        $employerRate = 0.02;

        return [
            'employee' => round($fundSalary * $employeeRate, 2),
            'employer' => round($fundSalary * $employerRate, 2),
        ];
    }

    protected function computeSemiMonthlyWithholdingTax(float $semiMonthlyTaxableCompensation): float
    {
        if ($semiMonthlyTaxableCompensation <= 10417) {
            return 0;
        }

        if ($semiMonthlyTaxableCompensation <= 16666) {
            return round(($semiMonthlyTaxableCompensation - 10417) * 0.15, 2);
        }

        if ($semiMonthlyTaxableCompensation <= 33332) {
            return round(937.50 + (($semiMonthlyTaxableCompensation - 16667) * 0.20), 2);
        }

        if ($semiMonthlyTaxableCompensation <= 83332) {
            return round(4270.70 + (($semiMonthlyTaxableCompensation - 33333) * 0.25), 2);
        }

        if ($semiMonthlyTaxableCompensation <= 333332) {
            return round(16770.70 + (($semiMonthlyTaxableCompensation - 83333) * 0.30), 2);
        }

        return round(91770.70 + (($semiMonthlyTaxableCompensation - 333333) * 0.35), 2);
    }
}
