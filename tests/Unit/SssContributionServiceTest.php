<?php

namespace Tests\Unit;

use App\Services\Payroll\SssContributionService;
use Tests\TestCase;

class SssContributionServiceTest extends TestCase
{
    public function test_employee_share_is_1050_for_20902_72_monthly_compensation(): void
    {
        $result = app(SssContributionService::class)->compute(20902.72);

        $this->assertSame(21000.00, $result['msc']);
        $this->assertSame(20000.00, $result['regular_ss_msc']);
        $this->assertSame(1000.00, $result['mpf_msc']);
        $this->assertSame(1000.00, $result['employee_regular_ss']);
        $this->assertSame(50.00, $result['employee_mpf']);
        $this->assertSame(1050.00, $result['employee']);
        $this->assertSame(2100.00, $result['employer']);
        $this->assertSame(30.00, $result['ec']);
        $this->assertSame(2130.00, $result['employer_total_with_ec']);
        $this->assertSame(3180.00, $result['total_contribution']);
        $this->assertSame(20750.00, $result['range_minimum']);
        $this->assertSame(21249.99, $result['range_maximum']);
    }

    public function test_legacy_14902_72_basis_explains_the_old_750_deduction(): void
    {
        $result = app(SssContributionService::class)->compute(14902.72);

        $this->assertSame(15000.00, $result['msc']);
        $this->assertSame(750.00, $result['employee']);
    }
}
