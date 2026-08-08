<?php

namespace Tests\Unit\Services\Payroll;

use App\Services\Payroll\PayrollPremiumService;
use Carbon\Carbon;
use Tests\TestCase;

class PayrollPremiumServiceTest extends TestCase
{
    private PayrollPremiumService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('payroll.premiums.standard_daily_hours', 8);
        config()->set('payroll.premiums.overtime_multiplier', 1.25);
        config()->set('payroll.premiums.premium_day_overtime_multiplier', 1.30);
        config()->set('payroll.premiums.night_differential_percent', 0.10);

        $this->service = app(PayrollPremiumService::class);
    }

    public function test_ordinary_overtime_uses_daily_rate_divided_by_8_times_125_percent(): void
    {
        // PHP 800 / 8 = PHP 100/hour; PHP 100 x 125% = PHP 125/hour.
        $this->assertSame(125.0, $this->service->overtimeHourlyRate(800, 1.0));
    }

    public function test_premium_day_overtime_uses_statutory_130_percent_on_applicable_day_rate(): void
    {
        // Special/rest day: 100 x 130% day rate x 130% OT = PHP 169/hour.
        $this->assertSame(169.0, $this->service->overtimeHourlyRate(800, 1.30));

        // Regular holiday: 100 x 200% day rate x 130% OT = PHP 260/hour.
        $this->assertSame(260.0, $this->service->overtimeHourlyRate(800, 2.00));
    }

    public function test_night_differential_uses_daily_rate_divided_by_8_times_10_percent(): void
    {
        // PHP 800 / 8 = PHP 100/hour; ND = PHP 10/hour on ordinary day.
        $this->assertSame(10.0, $this->service->nightDifferentialHourlyRate(800, 1.0));
        $this->assertSame(13.0, $this->service->nightDifferentialHourlyRate(800, 1.30));
        $this->assertSame(20.0, $this->service->nightDifferentialHourlyRate(800, 2.00));
    }

    public function test_night_differential_on_approved_overtime_uses_ten_percent_of_ot_hourly_rate(): void
    {
        // Ordinary OT = PHP 125/hour, so the OT-night differential premium is PHP 12.50/hour.
        $this->assertSame(12.5, $this->service->nightDifferentialOnOvertimeHourlyRate(125));

        // Regular-holiday OT = PHP 260/hour, so OT-night differential is PHP 26/hour.
        $this->assertSame(26.0, $this->service->nightDifferentialOnOvertimeHourlyRate(260));
    }

    public function test_night_differential_overlap_can_be_limited_to_an_approved_ot_interval(): void
    {
        $actualIn = Carbon::parse('2026-08-07 21:00:00', 'Asia/Manila');
        $actualOut = Carbon::parse('2026-08-08 01:00:00', 'Asia/Manila');
        $ot = $this->service->interval('2026-08-07', '23:00', '01:00');

        $this->assertNotNull($ot);
        $this->assertSame(120, $this->service->nightDifferentialOverlapMinutes(
            $actualIn,
            $actualOut,
            $ot['start'],
            $ot['end']
        ));
    }

    public function test_night_differential_detects_any_actual_work_overlap_from_10pm_to_6am(): void
    {
        $in = Carbon::parse('2026-08-07 21:00:00', 'Asia/Manila');
        $out = Carbon::parse('2026-08-08 07:00:00', 'Asia/Manila');

        $this->assertSame(480, $this->service->nightDifferentialMinutes($in, $out));

        $in = Carbon::parse('2026-08-07 23:00:00', 'Asia/Manila');
        $out = Carbon::parse('2026-08-08 02:00:00', 'Asia/Manila');

        $this->assertSame(180, $this->service->nightDifferentialMinutes($in, $out));
    }

    public function test_daytime_work_has_zero_night_differential(): void
    {
        $in = Carbon::parse('2026-08-07 08:00:00', 'Asia/Manila');
        $out = Carbon::parse('2026-08-07 17:00:00', 'Asia/Manila');

        $this->assertSame(0, $this->service->nightDifferentialMinutes($in, $out));
    }

    public function test_manual_overtime_interval_supports_overnight_approval(): void
    {
        $interval = $this->service->interval('2026-08-07', '23:00', '02:00');

        $this->assertNotNull($interval);
        $this->assertSame(180, $interval['minutes']);
        $this->assertSame('2026-08-07 23:00:00', $interval['start']->toDateTimeString());
        $this->assertSame('2026-08-08 02:00:00', $interval['end']->toDateTimeString());
    }
}
