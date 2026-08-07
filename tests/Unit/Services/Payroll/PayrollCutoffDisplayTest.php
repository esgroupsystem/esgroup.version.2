<?php

namespace Tests\Unit\Services\Payroll;

use App\Services\Payroll\PayrollPeriodService;
use Tests\TestCase;

class PayrollCutoffDisplayTest extends TestCase
{
    public function test_legacy_second_key_is_displayed_as_business_first_cutoff_26_to_10(): void
    {
        $service = app(PayrollPeriodService::class);

        [$startDate, $endDate] = $service->resolveCutoffRange(7, 2026, 'second');

        $this->assertSame('2026-07-26', $startDate->toDateString());
        $this->assertSame('2026-08-10', $endDate->toDateString());
        $this->assertSame('1st Cutoff (26-10)', config('payroll.cutoff_display.second.full'));
    }

    public function test_legacy_first_key_is_displayed_as_business_second_cutoff_11_to_25(): void
    {
        $service = app(PayrollPeriodService::class);

        [$startDate, $endDate] = $service->resolveCutoffRange(7, 2026, 'first');

        $this->assertSame('2026-07-11', $startDate->toDateString());
        $this->assertSame('2026-07-25', $endDate->toDateString());
        $this->assertSame('2nd Cutoff (11-25)', config('payroll.cutoff_display.first.full'));
    }
}
