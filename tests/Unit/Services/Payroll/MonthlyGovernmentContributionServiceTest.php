<?php

namespace Tests\Unit\Services\Payroll;

use App\Services\Payroll\MonthlyGovernmentContributionService;
use Tests\TestCase;

class MonthlyGovernmentContributionServiceTest extends TestCase
{
    public function test_july_example_uses_both_cutoffs_and_returns_exact_sss_mpf_breakdown(): void
    {
        $result = app(MonthlyGovernmentContributionService::class)->compute(
            11784.92,
            9117.80,
            22000.00
        );

        $this->assertSame(11784.92, $result['business_first_cutoff_gross']);
        $this->assertSame(9117.80, $result['business_second_cutoff_gross']);
        $this->assertSame(20902.72, $result['monthly_cycle_gross']);

        $this->assertSame(20902.72, $result['sss_basis']);
        $this->assertSame(21000.00, $result['sss_msc']);
        $this->assertSame(20000.00, $result['sss_regular_ss_msc']);
        $this->assertSame(1000.00, $result['sss_mpf_msc']);

        $this->assertSame(1000.00, $result['sss_employee_regular_ss']);
        $this->assertSame(50.00, $result['sss_employee_mpf']);
        $this->assertSame(1050.00, $result['sss_employee']);

        $this->assertSame(2000.00, $result['sss_employer_regular_ss']);
        $this->assertSame(100.00, $result['sss_employer_mpf']);
        $this->assertSame(2100.00, $result['sss_employer']);
        $this->assertSame(30.00, $result['sss_ec']);
        $this->assertSame(3180.00, $result['sss_total_contribution']);

        $this->assertSame(550.00, $result['philhealth_employee']);
        $this->assertSame(550.00, $result['philhealth_employer']);
        $this->assertSame(1100.00, $result['philhealth_total']);

        $this->assertSame(200.00, $result['pagibig_employee']);
        $this->assertSame(200.00, $result['pagibig_employer']);
        $this->assertSame(400.00, $result['pagibig_total']);

        $this->assertSame(1800.00, round(
            $result['sss_employee']
            + $result['philhealth_employee']
            + $result['pagibig_employee'],
            2
        ));

        $this->assertSame(2880.00, round(
            $result['sss_employer']
            + $result['sss_ec']
            + $result['philhealth_employer']
            + $result['pagibig_employer'],
            2
        ));
    }
}
