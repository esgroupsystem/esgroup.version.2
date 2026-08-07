<?php

namespace App\Services\Payroll;

use RuntimeException;

class SssContributionService
{
    public function compute(float $monthlyCompensation): array
    {
        $rules = config('sss.business_employee', []);

        $this->validateRules($rules);

        $compensation = $this->money($monthlyCompensation);

        if ($compensation <= 0) {
            return $this->emptyResult($rules);
        }

        $msc = $this->monthlySalaryCredit($compensation, $rules);
        $regularSsMsc = min($msc, (float) $rules['regular_ss_msc_cap']);
        $mpfMsc = max(0.00, $msc - $regularSsMsc);

        $employeeRegularSs = $this->money($regularSsMsc * (float) $rules['employee_rate']);
        $employeeMpf = $this->money($mpfMsc * (float) $rules['employee_rate']);
        $employerRegularSs = $this->money($regularSsMsc * (float) $rules['employer_rate']);
        $employerMpf = $this->money($mpfMsc * (float) $rules['employer_rate']);

        $employeeTotal = $this->money($employeeRegularSs + $employeeMpf);
        $employerSocialSecurityTotal = $this->money($employerRegularSs + $employerMpf);
        $ec = $msc <= (float) $rules['ec_low_msc_maximum']
            ? $this->money($rules['ec_low_amount'])
            : $this->money($rules['ec_high_amount']);

        [$rangeMinimum, $rangeMaximum] = $this->compensationRange($msc, $rules);

        return [
            'circular_number' => (string) $rules['circular_number'],
            'effective_from' => (string) $rules['effective_from'],
            'compensation' => $compensation,
            'range_minimum' => $rangeMinimum,
            'range_maximum' => $rangeMaximum,
            'msc' => $this->money($msc),
            'regular_ss_msc' => $this->money($regularSsMsc),
            'mpf_msc' => $this->money($mpfMsc),
            'employee_regular_ss' => $employeeRegularSs,
            'employee_mpf' => $employeeMpf,
            'employee' => $employeeTotal,
            'employer_regular_ss' => $employerRegularSs,
            'employer_mpf' => $employerMpf,
            'employer' => $employerSocialSecurityTotal,
            'ec' => $ec,
            'employer_total_with_ec' => $this->money($employerSocialSecurityTotal + $ec),
            'total_contribution' => $this->money($employeeTotal + $employerSocialSecurityTotal + $ec),
        ];
    }

    private function monthlySalaryCredit(float $compensation, array $rules): float
    {
        $minimumMsc = (float) $rules['minimum_msc'];
        $maximumMsc = (float) $rules['maximum_msc'];
        $increment = (float) $rules['msc_increment'];
        $firstMiddleRange = (float) $rules['first_middle_range'];
        $maximumRangeStart = (float) $rules['maximum_range_start'];

        if ($compensation < $firstMiddleRange) {
            return $minimumMsc;
        }

        if ($compensation >= $maximumRangeStart) {
            return $maximumMsc;
        }

        $step = (int) floor(($compensation - $firstMiddleRange) / $increment) + 1;

        return min($maximumMsc, $minimumMsc + ($step * $increment));
    }

    private function compensationRange(float $msc, array $rules): array
    {
        $minimumMsc = (float) $rules['minimum_msc'];
        $maximumMsc = (float) $rules['maximum_msc'];
        $firstMiddleRange = (float) $rules['first_middle_range'];
        $maximumRangeStart = (float) $rules['maximum_range_start'];

        if ($msc <= $minimumMsc) {
            return [0.00, $this->money($firstMiddleRange - 0.01)];
        }

        if ($msc >= $maximumMsc) {
            return [$this->money($maximumRangeStart), null];
        }

        return [
            $this->money($msc - 250.00),
            $this->money($msc + 249.99),
        ];
    }

    private function emptyResult(array $rules): array
    {
        return [
            'circular_number' => (string) ($rules['circular_number'] ?? '2024-006'),
            'effective_from' => (string) ($rules['effective_from'] ?? '2025-01-01'),
            'compensation' => 0.00,
            'range_minimum' => 0.00,
            'range_maximum' => null,
            'msc' => 0.00,
            'regular_ss_msc' => 0.00,
            'mpf_msc' => 0.00,
            'employee_regular_ss' => 0.00,
            'employee_mpf' => 0.00,
            'employee' => 0.00,
            'employer_regular_ss' => 0.00,
            'employer_mpf' => 0.00,
            'employer' => 0.00,
            'ec' => 0.00,
            'employer_total_with_ec' => 0.00,
            'total_contribution' => 0.00,
        ];
    }

    private function validateRules(array $rules): void
    {
        $required = [
            'circular_number',
            'effective_from',
            'employee_rate',
            'employer_rate',
            'minimum_msc',
            'maximum_msc',
            'msc_increment',
            'first_middle_range',
            'maximum_range_start',
            'regular_ss_msc_cap',
            'ec_low_amount',
            'ec_high_amount',
            'ec_low_msc_maximum',
        ];

        foreach ($required as $key) {
            if (! array_key_exists($key, $rules)) {
                throw new RuntimeException("Missing SSS configuration key: {$key}");
            }
        }
    }

    private function money(mixed $value): float
    {
        return round(max(0, (float) $value), 2);
    }
}
