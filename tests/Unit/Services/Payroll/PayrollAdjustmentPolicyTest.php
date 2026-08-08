<?php

namespace Tests\Unit\Services\Payroll;

use App\Models\Holiday;
use App\Models\PayrollAttendanceAdjustment;
use App\Services\Payroll\PayrollPremiumService;
use Tests\TestCase;

class PayrollAdjustmentPolicyTest extends TestCase
{
    public function test_holiday_defaults_are_automatic_200_percent_regular_and_130_percent_special(): void
    {
        $this->assertSame([
            'not_worked_multiplier' => 1.00,
            'worked_multiplier' => 2.00,
        ], Holiday::standardMultipliers(Holiday::TYPE_REGULAR));

        $this->assertSame([
            'not_worked_multiplier' => 0.00,
            'worked_multiplier' => 1.30,
        ], Holiday::standardMultipliers(Holiday::TYPE_SPECIAL));
    }

    public function test_overtime_requires_manager_approval_and_is_not_generic_paid_attendance(): void
    {
        $rules = PayrollAttendanceAdjustment::rulesFor(PayrollAttendanceAdjustment::TYPE_OVERTIME);

        $this->assertTrue($rules['approval_required']);
        $this->assertSame('overtime', $rules['manual_time_mode']);
        $this->assertFalse($rules['default_paid']);
    }

    public function test_offset_is_company_compensatory_leave_and_preserves_overtime_entitlement(): void
    {
        $rules = PayrollAttendanceAdjustment::rulesFor(PayrollAttendanceAdjustment::TYPE_OFFSET);

        $this->assertFalse($rules['defer_to_next_payroll']);
        $this->assertTrue($rules['uses_time_credit']);
        $this->assertTrue($rules['approval_required']);
        $this->assertFalse($rules['default_paid']);
        $this->assertFalse($rules['default_ignore_late']);
        $this->assertFalse($rules['default_ignore_undertime']);
        $this->assertTrue($rules['preserves_overtime_entitlement']);
    }

    public function test_offset_credit_uses_only_time_beyond_required_clock_span(): void
    {
        $service = app(PayrollPremiumService::class);

        // 8 paid hours + 1 hour lunch = 9 required clock hours.
        $this->assertSame(60, $service->offsetCreditMinutes(
            '2026-08-01',
            '08:00',
            '18:00',
            540
        ));

        // Exactly the required span creates no transferable credit.
        $this->assertSame(0, $service->offsetCreditMinutes(
            '2026-08-01',
            '08:00',
            '17:00',
            540
        ));

        // 9 paid hours + 1 hour lunch = 10 required clock hours.
        $this->assertSame(30, $service->offsetCreditMinutes(
            '2026-08-01',
            '08:00',
            '18:30',
            600
        ));
    }

    public function test_adjustment_types_have_explicit_behavior_rules(): void
    {
        foreach (array_keys(PayrollAttendanceAdjustment::TYPES) as $type) {
            $rules = PayrollAttendanceAdjustment::rulesFor($type);

            $this->assertArrayHasKey('date_mode', $rules, $type);
            $this->assertArrayHasKey('manual_time_mode', $rules, $type);
            $this->assertArrayHasKey('default_paid', $rules, $type);
            $this->assertArrayHasKey('default_ignore_late', $rules, $type);
            $this->assertArrayHasKey('default_ignore_undertime', $rules, $type);
            $this->assertArrayHasKey('approval_required', $rules, $type);
        }
    }
}
