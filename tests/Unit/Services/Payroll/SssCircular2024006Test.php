<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Payroll;

use App\Models\Payroll;
use App\Models\PayrollEmployeeSalary;
use App\Services\Payroll\GovernmentDeductionService;
use App\Services\Payroll\MonthlyGovernmentContributionService;
use App\Services\Payroll\PayrollComputationService;
use App\Services\Payroll\PayrollDeductionService;
use App\Services\Payroll\SssContributionService;
use Tests\TestCase;

/**
 * SSS Circular No. 2024-006 (Business Employers and Employees, effective January 2025)
 * and the way payroll turns it into cutoff deductions.
 */
class SssCircular2024006Test extends TestCase
{
    /** Every row of the circular, checked at both ends of its range of compensation. */
    public function test_every_row_of_the_circular_matches(): void
    {
        $service = app(SssContributionService::class);
        $rows = 0;

        for ($msc = 5000; $msc <= 35000; $msc += 500) {
            $rows++;
            $regular = min($msc, 20000);
            $mpf = $msc - $regular;
            $ec = $msc <= 14500 ? 10.0 : 30.0;

            $expected = [
                'msc' => (float) $msc,
                'employer_regular_ss' => $regular * 0.10,
                'employer_mpf' => $mpf * 0.10,
                'ec' => $ec,
                'employer_total_with_ec' => $regular * 0.10 + $mpf * 0.10 + $ec,
                'employee_regular_ss' => $regular * 0.05,
                'employee_mpf' => $mpf * 0.05,
                'employee' => $msc * 0.05,
                'total_contribution' => $msc * 0.15 + $ec,
            ];

            $from = match ($msc) {
                5000 => 0.01,
                default => $msc - 250,
            };
            $to = match ($msc) {
                5000 => 5249.99,
                35000 => 1_000_000.00,
                default => $msc + 249.99,
            };

            foreach ([$from, $to] as $pay) {
                $result = $service->compute($pay);

                foreach ($expected as $key => $value) {
                    $this->assertEqualsWithDelta($value, $result[$key], 0.001, "Pay {$pay}: {$key}");
                }
            }
        }

        $this->assertSame(61, $rows);
    }

    /** Spot checks typed straight from the circular's printed table. */
    public function test_printed_rows(): void
    {
        $service = app(SssContributionService::class);

        // BELOW 5,250 → 500 / 10 / 510 · 250 · 760
        $row = $service->compute(5000);
        $this->assertSame([500.0, 10.0, 510.0, 250.0, 760.0], [$row['employer'], $row['ec'], $row['employer_total_with_ec'], $row['employee'], $row['total_contribution']]);

        // 14,750 - 15,249.99 → EC jumps to 30: 1,500 / 30 / 1,530 · 750 · 2,280
        $row = $service->compute(14750);
        $this->assertSame([1500.0, 30.0, 1530.0, 750.0, 2280.0], [$row['employer'], $row['ec'], $row['employer_total_with_ec'], $row['employee'], $row['total_contribution']]);

        // 20,250 - 20,749.99 → MPF starts: ER 2,000 + 50, EE 1,000 + 25, total 3,105
        $row = $service->compute(20749.99);
        $this->assertSame([2000.0, 50.0, 2080.0, 1000.0, 25.0, 1025.0, 3105.0], [
            $row['employer_regular_ss'], $row['employer_mpf'], $row['employer_total_with_ec'],
            $row['employee_regular_ss'], $row['employee_mpf'], $row['employee'], $row['total_contribution'],
        ]);

        // 34,750 - Over → 2,000 + 1,500 + 30 = 3,530 · 1,000 + 750 = 1,750 · 5,280
        $row = $service->compute(80000);
        $this->assertSame([3530.0, 1750.0, 5280.0], [$row['employer_total_with_ec'], $row['employee'], $row['total_contribution']]);
    }

    /**
     * "Split across both cutoffs": the 26-10 cutoff used to take half the SSS of HALF a month's pay
     * (₱20,000/month → ₱250, then ₱750 on 11-25). It now estimates the full month.
     */
    public function test_opening_cutoff_estimates_the_whole_month(): void
    {
        $service = app(PayrollComputationService::class);
        $method = new \ReflectionMethod($service, 'monthlyCycleGovernmentBasis');
        $payroll = (new Payroll)->forceFill(['cutoff_type' => 'second']);

        // ₱10,000 earned + ₱500 allowance (half of ₱1,000/month) on 26-10.
        $basis = $method->invoke($service, $payroll, (object) [], 10500.00, 500.00, 1000.00);

        $this->assertSame(21000.00, $basis['amount']);
        $this->assertTrue($basis['estimated']);

        $split = app(GovernmentDeductionService::class)->applyDeductionSchedule(
            app(GovernmentDeductionService::class)->compute(['monthly_basic' => $basis['amount'], 'sss_monthly_basic' => $basis['amount']]),
            'second',
            ['sss' => 'every_cutoff', 'philhealth' => 'none', 'pagibig' => 'none'],
        );

        // MSC 21,000 → ₱1,050 a month → ₱525 on this cutoff.
        $this->assertSame(525.00, $split['sss_employee']);
    }

    /** An allowance released only on 26-10 is counted once, not doubled. */
    public function test_opening_cutoff_does_not_double_an_allowance_paid_only_on_it(): void
    {
        $service = app(PayrollComputationService::class);
        $method = new \ReflectionMethod($service, 'monthlyCycleGovernmentBasis');
        $payroll = (new Payroll)->forceFill(['cutoff_type' => 'second']);

        $basis = $method->invoke($service, $payroll, (object) [], 11000.00, 1000.00, 1000.00);

        $this->assertSame(21000.00, $basis['amount']);
    }

    /** Finalizing follows Payroll Settings "Computed from", like the drafts do. */
    public function test_month_end_follows_the_computed_from_setting(): void
    {
        $service = app(MonthlyGovernmentContributionService::class);

        // Default: actual gross of both cutoffs.
        $this->assertSame(21000.00, $service->compute(10500, 10500, 20000)['sss_msc']);

        config(['payroll.government_basis.sss' => 'fixed_monthly_basic']);
        $this->assertSame(20000.00, $service->compute(10500, 10500, 20000)['sss_msc']);
        $this->assertSame(1000.00, $service->compute(10500, 10500, 20000)['sss_employee']);

        config(['payroll.government_basis.sss' => 'none']);
        $this->assertSame(0.00, $service->compute(10500, 10500, 20000)['sss_employee']);
    }

    /** The Employee Rates preview uses the same pay as payroll (basic + allowances), not basic only. */
    public function test_rate_preview_includes_allowances_in_the_sss_basis(): void
    {
        $salary = (new PayrollEmployeeSalary)->forceFill([
            'rate_type' => 'monthly',
            'basic_salary' => 20000,
            'allowance' => 800,
            'sim_load_allowance' => 200,
            'allowance_release_schedule' => 'every_cutoff',
            'sim_load_release_schedule' => 'every_cutoff',
            'sss_contribution_cutoff' => 'first_cutoff',
            'pagibig_contribution_cutoff' => 'first_cutoff',
            'philhealth_contribution_cutoff' => 'first_cutoff',
        ]);

        foreach (PayrollEmployeeSalary::LOAN_PREFIXES as $prefix) {
            $salary->forceFill(["{$prefix}_deduction_schedule" => 'none']);
        }

        $preview = app(PayrollDeductionService::class)->salaryPreview($salary);

        // ₱21,000 → MSC 21,000 → ₱1,050 (it was ₱1,000 from basic only).
        $this->assertSame(1050.00, $preview['monthly_government']['sss']);
        // PhilHealth / Pag-IBIG stay on the fixed basic salary.
        $this->assertSame(500.00, $preview['monthly_government']['philhealth']);
        $this->assertSame(200.00, $preview['monthly_government']['pagibig']);
    }
}
