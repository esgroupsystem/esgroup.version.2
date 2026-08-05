<?php

namespace Tests\Unit\Services\Payroll;

use App\Services\Payroll\GovernmentDeductionService;
use Tests\TestCase;

class GovernmentDeductionServiceTest extends TestCase
{
    private GovernmentDeductionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(GovernmentDeductionService::class);
    }

    public function test_pagibig_is_fixed_at_two_hundred_when_monthly_salary_is_ten_thousand_or_higher(): void
    {
        foreach ([10000, 15000, 35000, 100000] as $monthlySalary) {
            $result = $this->service->compute([
                'monthly_basic' => $monthlySalary,
                'pagibig_monthly_basic' => $monthlySalary,
            ]);

            $this->assertSame(200.00, $result['pagibig_employee']);
            $this->assertSame(200.00, $result['pagibig_employer']);
            $this->assertSame(10000.00, $result['pagibig_fund_salary']);
        }
    }

    public function test_pagibig_uses_one_percent_employee_share_at_one_thousand_five_hundred_or_below(): void
    {
        $result = $this->service->compute([
            'monthly_basic' => 1500,
            'pagibig_monthly_basic' => 1500,
        ]);

        $this->assertSame(15.00, $result['pagibig_employee']);
        $this->assertSame(30.00, $result['pagibig_employer']);
    }

    public function test_philhealth_uses_five_percent_split_equally_with_floor_and_ceiling(): void
    {
        $minimum = $this->service->compute([
            'monthly_basic' => 5000,
            'philhealth_monthly_basic' => 5000,
        ]);

        $maximum = $this->service->compute([
            'monthly_basic' => 150000,
            'philhealth_monthly_basic' => 150000,
        ]);

        $this->assertSame(250.00, $minimum['philhealth_employee']);
        $this->assertSame(250.00, $minimum['philhealth_employer']);
        $this->assertSame(10000.00, $minimum['philhealth_salary_base']);

        $this->assertSame(2500.00, $maximum['philhealth_employee']);
        $this->assertSame(2500.00, $maximum['philhealth_employer']);
        $this->assertSame(100000.00, $maximum['philhealth_salary_base']);
    }

    public function test_sss_uses_five_percent_employee_and_ten_percent_employer_with_msc_cap(): void
    {
        $result = $this->service->compute([
            'monthly_basic' => 50000,
            'sss_monthly_basic' => 50000,
        ]);

        $this->assertSame(35000.00, $result['sss_msc']);
        $this->assertSame(1750.00, $result['sss_employee']);
        $this->assertSame(3500.00, $result['sss_employer']);
        $this->assertSame(30.00, $result['sss_ec']);
    }

    public function test_first_cutoff_schedule_deducts_full_monthly_amount_only_once(): void
    {
        $monthly = $this->service->compute([
            'monthly_basic' => 10000,
            'sss_monthly_basic' => 10000,
            'philhealth_monthly_basic' => 10000,
            'pagibig_monthly_basic' => 10000,
        ]);

        $first = $this->service->applyDeductionSchedule(
            $monthly,
            'first',
            [
                'sss' => 'first_cutoff',
                'philhealth' => 'first_cutoff',
                'pagibig' => 'first_cutoff',
            ]
        );

        $second = $this->service->applyDeductionSchedule(
            $monthly,
            'second',
            [
                'sss' => 'first_cutoff',
                'philhealth' => 'first_cutoff',
                'pagibig' => 'first_cutoff',
            ]
        );

        $this->assertSame(500.00, $first['sss_employee']);
        $this->assertSame(250.00, $first['philhealth_employee']);
        $this->assertSame(200.00, $first['pagibig_employee']);
        $this->assertSame(0.00, $second['total_employee_government_deductions']);
    }

    public function test_every_cutoff_schedule_splits_monthly_amount_in_half(): void
    {
        $monthly = $this->service->compute([
            'monthly_basic' => 10000,
            'pagibig_monthly_basic' => 10000,
        ]);

        $first = $this->service->applyDeductionSchedule(
            $monthly,
            'first',
            [
                'sss' => 'none',
                'philhealth' => 'none',
                'pagibig' => 'every_cutoff',
            ]
        );

        $second = $this->service->applyDeductionSchedule(
            $monthly,
            'second',
            [
                'sss' => 'none',
                'philhealth' => 'none',
                'pagibig' => 'every_cutoff',
            ]
        );

        $this->assertSame(100.00, $first['pagibig_employee']);
        $this->assertSame(100.00, $second['pagibig_employee']);
    }
}
