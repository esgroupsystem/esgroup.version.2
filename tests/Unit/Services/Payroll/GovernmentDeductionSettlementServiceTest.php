<?php

namespace Tests\Unit\Services\Payroll;

use App\Models\PayrollBenefitSettlement;
use App\Services\Payroll\GovernmentDeductionSettlementService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class GovernmentDeductionSettlementServiceTest extends TestCase
{
    private GovernmentDeductionSettlementService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(GovernmentDeductionSettlementService::class);
    }

    public function test_auto_cap_prevents_negative_pay_and_keeps_statutory_due_in_metadata(): void
    {
        $government = $this->governmentPayload();

        $result = $this->service->apply(
            government: $government,
            grossPay: 300.00,
            otherDeductions: 100.00,
        );

        $this->assertSame(200.00, $result['sss_employee']);
        $this->assertSame(0.00, $result['philhealth_employee']);
        $this->assertSame(0.00, $result['pagibig_employee']);
        $this->assertSame(200.00, $result['total_employee_government_deductions']);
        $this->assertSame(0.00, $result['settlement_meta']['net_pay_after_settlement']);

        $this->assertSame(500.00, $result['settlement_meta']['statutory_current_cutoff_before_settlement']['sss_employee']);
        $this->assertSame(250.00, $result['settlement_meta']['statutory_current_cutoff_before_settlement']['philhealth_employee']);
        $this->assertSame(200.00, $result['settlement_meta']['statutory_current_cutoff_before_settlement']['pagibig_employee']);
        $this->assertTrue($result['settlement_meta']['statutory_liability_preserved']);
    }

    public function test_employer_advance_can_refund_prior_deduction_without_erasing_monthly_liability(): void
    {
        $settlement = new PayrollBenefitSettlement([
            'mode' => PayrollBenefitSettlement::MODE_EMPLOYER_ADVANCE,
            'sss_employee_reimbursement' => 0,
            'philhealth_employee_reimbursement' => 250,
            'pagibig_employee_reimbursement' => 200,
            'reason' => 'Resigned before closing cutoff; refund prior employee collections in final pay.',
        ]);

        $result = $this->service->apply(
            government: $this->governmentPayload(),
            grossPay: 0.00,
            otherDeductions: 0.00,
            settlement: $settlement,
        );

        $this->assertSame(0.00, $result['sss_employee']);
        $this->assertSame(-250.00, $result['philhealth_employee']);
        $this->assertSame(-200.00, $result['pagibig_employee']);
        $this->assertSame(-450.00, $result['total_employee_government_deductions']);
        $this->assertSame(450.00, $result['settlement_meta']['net_pay_after_settlement']);
        $this->assertSame(500.00, $result['settlement_meta']['statutory_current_cutoff_before_settlement']['sss_employee']);
        $this->assertSame(250.00, $result['settlement_meta']['employee_reimbursements']['philhealth_employee']);
    }

    public function test_collect_full_rejects_a_negative_net_pay(): void
    {
        $settlement = new PayrollBenefitSettlement([
            'mode' => PayrollBenefitSettlement::MODE_COLLECT_FULL,
            'reason' => 'Test full collection.',
        ]);

        $this->expectException(ValidationException::class);

        $this->service->apply(
            government: $this->governmentPayload(),
            grossPay: 100.00,
            otherDeductions: 0.00,
            settlement: $settlement,
        );
    }

    private function governmentPayload(): array
    {
        return [
            'sss_employee' => 500.00,
            'philhealth_employee' => 250.00,
            'pagibig_employee' => 200.00,
            'withholding_tax' => 0.00,
            'sss_employer' => 1000.00,
            'sss_ec' => 30.00,
            'philhealth_employer' => 250.00,
            'pagibig_employer' => 200.00,
            'total_employee_government_deductions' => 950.00,
            'total_employer_government_contributions' => 1480.00,
        ];
    }
}
