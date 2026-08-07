<?php

namespace Tests\Unit\Services\Payroll;

use App\Services\Payroll\DailyAttendanceSummaryService;
use App\Models\PayrollItem;
use App\Services\Payroll\PayrollComputationService;
use App\Services\Payroll\PayrollPayslipService;
use Carbon\Carbon;
use ReflectionMethod;
use Tests\TestCase;

class AttendancePayrollRulesTest extends TestCase
{
    private DailyAttendanceSummaryService $dailySummaryService;

    private PayrollComputationService $payrollComputationService;

    private PayrollPayslipService $payrollPayslipService;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('payroll.attendance.paid_minutes_per_day', 480);
        config()->set('payroll.attendance.unpaid_break_minutes', 60);
        config()->set('payroll.attendance.unpaid_break_start', '12:00');
        config()->set('payroll.attendance.unpaid_break_end', '13:00');

        $this->dailySummaryService = app(DailyAttendanceSummaryService::class);
        $this->payrollComputationService = app(PayrollComputationService::class);
        $this->payrollPayslipService = app(PayrollPayslipService::class);
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


    public function test_nine_hour_workday_half_day_is_four_and_one_half_paid_hours(): void
    {
        [$payableDays, $payableHours] = $this->invokeProtected(
            $this->dailySummaryService,
            'payUnitsAfterDeductions',
            [270, 540]
        );

        $this->assertSame(0.5, $payableDays);
        $this->assertSame(4.5, $payableHours);
    }

    public function test_multiple_weekly_days_off_are_recognized(): void
    {
        $saturday = $this->invokeProtected(
            $this->dailySummaryService,
            'scheduleIndicatesRestDay',
            ['scheduled', ['Saturday', 'Sunday'], Carbon::parse('2026-07-11', 'Asia/Manila')]
        );

        $sundayFromLegacyString = $this->invokeProtected(
            $this->dailySummaryService,
            'scheduleIndicatesRestDay',
            ['scheduled', 'Saturday,Sunday', Carbon::parse('2026-07-12', 'Asia/Manila')]
        );

        $monday = $this->invokeProtected(
            $this->dailySummaryService,
            'scheduleIndicatesRestDay',
            ['scheduled', ['Saturday', 'Sunday'], Carbon::parse('2026-07-13', 'Asia/Manila')]
        );

        $this->assertTrue($saturday);
        $this->assertTrue($sundayFromLegacyString);
        $this->assertFalse($monday);
    }

    public function test_payroll_day_equivalent_uses_each_rows_paid_minutes(): void
    {
        $rows = collect([
            (object) [
                'worked_minutes' => 480,
                'payable_days' => 1.0,
                'meta' => ['paid_minutes_per_day' => 480],
            ],
            (object) [
                'worked_minutes' => 270,
                'payable_days' => 0.5,
                'meta' => ['paid_minutes_per_day' => 540],
            ],
        ]);

        $workedDays = $this->invokeProtected(
            $this->payrollComputationService,
            'totalWorkedDayEquivalent',
            [$rows]
        );

        $payableDays = $this->invokeProtected(
            $this->payrollComputationService,
            'totalPayableDayEquivalent',
            [$rows]
        );

        $this->assertSame(1.5, $workedDays);
        $this->assertSame(1.5, $payableDays);
    }

    public function test_payslip_day_equivalent_uses_nine_hour_schedule_metadata(): void
    {
        $row = (object) [
            'payable_hours' => 4.5,
            'meta' => ['paid_minutes_per_day' => 540],
        ];

        $payUnits = $this->invokeProtected(
            $this->payrollPayslipService,
            'payUnits',
            [$row]
        );

        $item = new PayrollItem([
            'total_absent_days' => 0,
            'total_late_minutes' => 0,
            'total_undertime_minutes' => 60,
            'meta' => ['paid_minutes_per_day' => 540],
        ]);

        $regularDays = $this->invokeProtected(
            $this->payrollPayslipService,
            'regularDaysWorked',
            [$item, collect(), collect()]
        );

        $this->assertSame(0.5, $payUnits);
        $this->assertSame(14.89, $regularDays);
    }

    public function test_flexible_shift_has_no_late_fallback_without_fixed_clock_in(): void
    {
        $row = (object) [
            'attendance_status' => 'present',
            'shift_name' => 'Flexible Shift',
            'late_minutes' => null,
            'scheduled_time_in' => null,
            'actual_time_in' => '2026-07-29 07:48:00',
            'work_date' => '2026-07-29',
            'meta' => [
                'schedule_mode' => 'flexible',
                'paid_minutes_per_day' => 540,
                'scheduled_clock_minutes' => 600,
                'clock_worked_minutes' => 602,
            ],
        ];

        $lateMinutes = $this->invokeProtected(
            $this->payrollComputationService,
            'payrollLateMinutesForRow',
            [$row]
        );

        $this->assertSame(0, $lateMinutes);
    }

    public function test_flexible_shift_undertime_fallback_uses_required_clock_minutes(): void
    {
        $row = (object) [
            'attendance_status' => 'undertime',
            'shift_name' => 'Flexible Shift',
            'undertime_minutes' => null,
            'work_date' => '2026-07-29',
            'meta' => [
                'schedule_mode' => 'flexible',
                'paid_minutes_per_day' => 540,
                'scheduled_clock_minutes' => 600,
                'clock_worked_minutes' => 540,
            ],
        ];

        $undertimeMinutes = $this->invokeProtected(
            $this->payrollComputationService,
            'payrollUndertimeMinutesForRow',
            [$row]
        );

        $this->assertSame(60, $undertimeMinutes);
    }

    private function invokeProtected(object $object, string $method, array $arguments = []): mixed
    {
        $reflection = new ReflectionMethod($object, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($object, $arguments);
    }
}
