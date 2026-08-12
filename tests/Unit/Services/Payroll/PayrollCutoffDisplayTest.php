<?php

namespace Tests\Unit\Services\Payroll;

use App\Services\Payroll\PayrollPeriodService;
use Tests\TestCase;

class PayrollCutoffDisplayTest extends TestCase
{
    public function test_selecting_july_maps_business_first_cutoff_to_june_26_through_july_10(): void
    {
        $service = app(PayrollPeriodService::class);

        [$startDate, $endDate] = $service->resolveCutoffRange(7, 2026, 'second');

        $this->assertSame('2026-06-26', $startDate->toDateString());
        $this->assertSame('2026-07-10', $endDate->toDateString());
        $this->assertSame('1st Cutoff (26-10)', config('payroll.cutoff_display.second.full'));
    }

    public function test_selecting_july_maps_business_second_cutoff_to_july_11_through_july_25(): void
    {
        $service = app(PayrollPeriodService::class);

        [$startDate, $endDate] = $service->resolveCutoffRange(7, 2026, 'first');

        $this->assertSame('2026-07-11', $startDate->toDateString());
        $this->assertSame('2026-07-25', $endDate->toDateString());
        $this->assertSame('2nd Cutoff (11-25)', config('payroll.cutoff_display.first.full'));
    }

    public function test_selecting_august_maps_business_first_cutoff_to_july_26_through_august_10(): void
    {
        $service = app(PayrollPeriodService::class);

        [$startDate, $endDate] = $service->resolveCutoffRange(8, 2026, 'second');

        $this->assertSame('2026-07-26', $startDate->toDateString());
        $this->assertSame('2026-08-10', $endDate->toDateString());
    }

    public function test_dates_resolve_back_to_the_same_cycle_month_used_by_the_ui(): void
    {
        $service = app(PayrollPeriodService::class);

        $julyOpening = $service->cutoffContainingDate('2026-07-05');
        $augustOpening = $service->cutoffContainingDate('2026-07-26');

        $this->assertSame(7, $julyOpening['month']);
        $this->assertSame(2026, $julyOpening['year']);
        $this->assertSame('second', $julyOpening['type']);
        $this->assertSame('2026-06-26', $julyOpening['start']->toDateString());
        $this->assertSame('2026-07-10', $julyOpening['end']->toDateString());

        $this->assertSame(8, $augustOpening['month']);
        $this->assertSame(2026, $augustOpening['year']);
        $this->assertSame('second', $augustOpening['type']);
        $this->assertSame('2026-07-26', $augustOpening['start']->toDateString());
        $this->assertSame('2026-08-10', $augustOpening['end']->toDateString());
    }
}
