<?php

namespace Tests\Unit\Services\Payroll;

use App\Services\Payroll\DailyAttendanceSummaryService;
use App\Services\Payroll\PayrollComputationService;
use Carbon\Carbon;
use ReflectionMethod;
use Tests\TestCase;

class AttendancePayrollRulesTest extends TestCase
{
    private DailyAttendanceSummaryService $dailySummaryService;

    private PayrollComputationService $payrollComputationService;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('payroll.attendance.paid_minutes_per_day', 480);
        config()->set('payroll.attendance.unpaid_break_minutes', 60);
        config()->set('payroll.attendance.unpaid_break_start', '12:00');
        config()->set('payroll.attendance.unpaid_break_end', '13:00');

        $this->dailySummaryService = app(DailyAttendanceSummaryService::class);
        $this->payrollComputationService = app(PayrollComputationService::class);
    }

    public function test_full_day_worked_minutes_exclude_one_hour_lunch(): void
    {
        $workDate = Carbon::parse('2026-07-06', 'Asia/Manila');
        $actualIn = Carbon::parse('2026-07-06 07:00:00', 'Asia/Manila');
        $actualOut = Carbon::parse('2026-07-06 16:00:00', 'Asia/Manila');

        $breakMinutes = $this->invokeProtected(
            $this->dailySummaryService,
            'unpaidBreakOverlapMinutes',
            [$workDate, $actualIn, $actualOut]
        );

        $workedMinutes = $actualIn->diffInMinutes($actualOut) - $breakMinutes;

        $this->assertSame(60, $breakMinutes);
        $this->assertSame(480.0, $workedMinutes);
        $this->assertSame(8.0, $workedMinutes / 60);
    }

    public function test_morning_half_day_does_not_remove_lunch_that_was_not_crossed(): void
    {
        $workDate = Carbon::parse('2026-07-06', 'Asia/Manila');
        $actualIn = Carbon::parse('2026-07-06 07:00:00', 'Asia/Manila');
        $actualOut = Carbon::parse('2026-07-06 11:00:00', 'Asia/Manila');

        $breakMinutes = $this->invokeProtected(
            $this->dailySummaryService,
            'unpaidBreakOverlapMinutes',
            [$workDate, $actualIn, $actualOut]
        );

        $this->assertSame(0, $breakMinutes);
        $this->assertSame(240.0, $actualIn->diffInMinutes($actualOut));
    }

    public function test_automatic_half_day_is_four_paid_hours_and_four_hours_undertime(): void
    {
        [$payableDays, $payableHours] = $this->invokeProtected(
            $this->dailySummaryService,
            'payUnitsAfterDeductions',
            [240]
        );

        $this->assertSame(0.5, $payableDays);
        $this->assertSame(4.0, $payableHours);

        $timeInOnly = $this->invokeProtected(
            $this->dailySummaryService,
            'isAutomaticHalfDay',
            [Carbon::parse('2026-07-06 07:00:00', 'Asia/Manila'), null]
        );

        $timeOutOnly = $this->invokeProtected(
            $this->dailySummaryService,
            'isAutomaticHalfDay',
            [null, Carbon::parse('2026-07-06 16:00:00', 'Asia/Manila')]
        );

        $this->assertTrue($timeInOnly);
        $this->assertTrue($timeOutOnly);
    }

    public function test_payroll_uses_summary_late_and_undertime_minutes_without_recomputing(): void
    {
        $row = (object) [
            'attendance_status' => 'late_undertime',
            'late_minutes' => 30,
            'undertime_minutes' => 240,
            'payable_hours' => 3.50,
            'ignore_late' => false,
            'ignore_undertime' => false,
        ];

        $lateMinutes = $this->invokeProtected(
            $this->payrollComputationService,
            'payrollLateMinutesForRow',
            [$row]
        );

        $undertimeMinutes = $this->invokeProtected(
            $this->payrollComputationService,
            'payrollUndertimeMinutesForRow',
            [$row]
        );

        $payableHours = $this->invokeProtected(
            $this->payrollComputationService,
            'payableHours',
            [$row]
        );

        $this->assertSame(30, $lateMinutes);
        $this->assertSame(240, $undertimeMinutes);
        $this->assertSame(3.5, $payableHours);
    }

    public function test_thirty_minute_late_deduction_uses_only_thirty_minutes(): void
    {
        $minuteRate = (22000 * 12 / 365) / 8 / 60;

        $result = $this->invokeProtected(
            $this->payrollComputationService,
            'computeAttendanceDeductions',
            [
                collect(),
                [
                    'minute_rate' => $minuteRate,
                    'late_deduction_per_minute' => $minuteRate,
                    'undertime_deduction_per_minute' => $minuteRate,
                    'absent_deduction_per_day' => 22000 * 12 / 365,
                ],
                30,
                240,
                0.0,
            ]
        );

        $this->assertSame(30, $result['late_minutes']);
        $this->assertSame(240, $result['undertime_minutes']);
        $this->assertSame(45.21, $result['late_deduction']);
        $this->assertSame(361.64, $result['undertime_deduction']);
    }

    private function invokeProtected(object $object, string $method, array $arguments = []): mixed
    {
        $reflection = new ReflectionMethod($object, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($object, $arguments);
    }
}
