<?php

namespace Tests\Unit\Services\Payroll;

use App\Models\BenefitContributionRecord;
use App\Models\PayrollItem;
use App\Services\Payroll\BenefitRecordsService;
use App\Services\Payroll\PayrollComputationService;
use ReflectionMethod;
use Tests\TestCase;

class BenefitRecordsSettlementTest extends TestCase
{
    public function test_benefits_totals_separate_statutory_due_from_actual_payroll_collection(): void
    {
        $records = collect([
            new BenefitContributionRecord([
                'sss_employee_total' => 500,
                'sss_employee_collected' => 200,
                'sss_employer_total' => 1000,
                'sss_total_contribution' => 1500,
                'philhealth_employee' => 250,
                'philhealth_employee_collected' => 0,
                'philhealth_employer' => 250,
                'philhealth_total' => 500,
                'pagibig_employee' => 200,
                'pagibig_employee_collected' => 0,
                'pagibig_employer' => 200,
                'pagibig_total' => 400,
                'employee_total' => 950,
                'employer_total' => 1450,
                'grand_total' => 2400,
                'employee_share_unrecovered' => 750,
            ]),
        ]);

        $service = app(BenefitRecordsService::class);
        $method = new ReflectionMethod($service, 'aggregateTotals');
        $method->setAccessible(true);
        $totals = $method->invoke($service, $records);

        $this->assertSame(950.00, $totals['employee_total']);
        $this->assertSame(200.00, $totals['employee_collected_total']);
        $this->assertSame(750.00, $totals['employee_share_unrecovered']);
        $this->assertSame(1450.00, $totals['employer_total']);
    }

    public function test_closing_settlement_carry_forward_matches_employee_by_biometric_identity(): void
    {
        $service = app(PayrollComputationService::class);
        $method = new ReflectionMethod($service, 'payrollItemIdentityKey');
        $method->setAccessible(true);

        $opening = new PayrollItem([
            'employee_biometric_id' => 123,
            'employee_no' => 'EMP-001',
            'employee_name' => 'Opening Name',
        ]);
        $closing = new PayrollItem([
            'employee_biometric_id' => 123,
            'employee_no' => 'EMP-CHANGED',
            'employee_name' => 'Updated Name',
        ]);

        $this->assertSame(
            $method->invoke($service, $opening),
            $method->invoke($service, $closing)
        );
    }
}
