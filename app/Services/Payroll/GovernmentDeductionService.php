<?php

namespace App\Services\Payroll;

class GovernmentDeductionService
{
    public function compute(array $data): array
    {
        $monthlyBasic = (float) ($data['monthly_basic'] ?? 0);
        $taxableCutoffCompensation = (float) ($data['taxable_cutoff_compensation'] ?? 0);

        $sss = $this->computeSss($monthlyBasic);
        $philhealth = $this->computePhilHealth($monthlyBasic);
        $pagibig = $this->computePagIbig($monthlyBasic);

        $withholdingTax = $this->computeSemiMonthlyWithholdingTax(
            max(
                0,
                $taxableCutoffCompensation
                - $sss['employee']
                - $philhealth['employee']
                - $pagibig['employee']
            )
        );

        $totalEmployeeGovernmentDeductions = round(
            $sss['employee']
            + $philhealth['employee']
            + $pagibig['employee']
            + $withholdingTax,
            2
        );

        $totalEmployerGovernmentContributions = round(
            $sss['employer']
            + $philhealth['employer']
            + $pagibig['employer'],
            2
        );

        return [
            'sss_employee' => $sss['employee'],
            'sss_employer' => $sss['employer'],
            'philhealth_employee' => $philhealth['employee'],
            'philhealth_employer' => $philhealth['employer'],
            'pagibig_employee' => $pagibig['employee'],
            'pagibig_employer' => $pagibig['employer'],
            'withholding_tax' => $withholdingTax,
            'total_employee_government_deductions' => $totalEmployeeGovernmentDeductions,
            'total_employer_government_contributions' => $totalEmployerGovernmentContributions,
        ];
    }

    protected function computeSss(float $monthlyBasic): array
    {
        $compensation = round($monthlyBasic, 2);

        if ($compensation <= 0) {
            return ['employee' => 0, 'employer' => 0];
        }

        // Effective Jan 2025 table logic
        // Total MSC: min 5,000, max 35,000; regular SSC capped at 20,000;
        // MPF applies above 20,000; EC is 10 or 30.
        if ($compensation <= 5249.99) {
            $totalMsc = 5000;
        } elseif ($compensation >= 34750) {
            $totalMsc = 35000;
        } else {
            $step = (int) floor(($compensation - 5250) / 500) + 1;
            $totalMsc = 5000 + ($step * 500);
        }

        $regularMsc = min($totalMsc, 20000);
        $mpfMsc = max(0, $totalMsc - 20000);
        $ec = $compensation < 14750 ? 10 : 30;

        $employeeRegular = $regularMsc * 0.05;
        $employerRegular = $regularMsc * 0.10;

        $employeeMpf = $mpfMsc * 0.025;
        $employerMpf = $mpfMsc * 0.05;

        return [
            'employee' => round($employeeRegular + $employeeMpf, 2),
            'employer' => round($employerRegular + $employerMpf + $ec, 2),
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

        // Configurable default:
        // Employee: 1% if <= 1500, else 2%
        // Employer: 2%
        // Max fund salary used here: 10,000
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
        if ($semiMonthlyTaxableCompensation <= 0) {
            return 0;
        }

        $monthlyTaxable = $semiMonthlyTaxableCompensation * 2;
        $monthlyTax = $this->computeMonthlyWithholdingTax($monthlyTaxable);

        return round($monthlyTax / 2, 2);
    }

    protected function computeMonthlyWithholdingTax(float $monthlyTaxable): float
    {
        if ($monthlyTaxable <= 20833) {
            return 0;
        }

        if ($monthlyTaxable <= 33333) {
            return ($monthlyTaxable - 20833) * 0.15;
        }

        if ($monthlyTaxable <= 66667) {
            return 1875 + (($monthlyTaxable - 33333) * 0.20);
        }

        if ($monthlyTaxable <= 166667) {
            return 8541.80 + (($monthlyTaxable - 66667) * 0.25);
        }

        if ($monthlyTaxable <= 666667) {
            return 33541.80 + (($monthlyTaxable - 166667) * 0.30);
        }

        return 183541.80 + (($monthlyTaxable - 666667) * 0.35);
    }
}
