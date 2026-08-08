<?php

namespace App\Services\Payroll;

class MonthlyGovernmentContributionService
{
    public function __construct(
        private readonly GovernmentDeductionService $governmentDeductionService
    ) {}

    /**
     * Compute the statutory monthly government contribution from the complete
     * company payroll cycle:
     *
     * Business 1st cutoff  = 26th of previous month through 10th
     * Business 2nd cutoff  = 11th through 25th
     * Contribution month   = month containing the 10th and 25th dates
     *
     * SSS is based on the total actual gross remuneration from BOTH cutoffs.
     * PhilHealth and Pag-IBIG retain the project's configured fixed-monthly-
     * basic basis, because these programs must not be reduced by attendance
     * losses in the same way as payroll net pay.
     */
    public function compute(
        float $businessFirstCutoffGross,
        float $businessSecondCutoffGross,
        float $fixedMonthlyBasicSalary
    ): array {
        $firstGross = $this->money($businessFirstCutoffGross);
        $secondGross = $this->money($businessSecondCutoffGross);
        $monthlyGross = $this->money($firstGross + $secondGross);
        $monthlyBasic = $this->money($fixedMonthlyBasicSalary);

        $government = $this->governmentDeductionService->compute([
            'monthly_basic' => $monthlyGross,
            'sss_monthly_basic' => $monthlyGross,
            'philhealth_monthly_basic' => $monthlyBasic,
            'pagibig_monthly_basic' => $monthlyBasic,
            'taxable_cutoff_compensation' => $monthlyGross,
        ]);

        $government['business_first_cutoff_gross'] = $firstGross;
        $government['business_second_cutoff_gross'] = $secondGross;
        $government['monthly_cycle_gross'] = $monthlyGross;
        $government['fixed_monthly_basic_salary'] = $monthlyBasic;

        return $government;
    }

    private function money(mixed $value): float
    {
        return round(max(0, (float) $value), 2);
    }
}
