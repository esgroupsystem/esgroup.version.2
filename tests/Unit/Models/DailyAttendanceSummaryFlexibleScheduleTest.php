<?php

namespace Tests\Unit\Models;

use App\Models\DailyAttendanceSummary;
use Tests\TestCase;

class DailyAttendanceSummaryFlexibleScheduleTest extends TestCase
{
    public function test_flexible_shift_is_a_valid_configured_schedule_without_fixed_times(): void
    {
        $row = new DailyAttendanceSummary([
            'shift_name' => 'Flexible Shift',
            'schedule_status' => 'scheduled',
            'attendance_status' => 'present',
            'scheduled_time_in' => null,
            'scheduled_time_out' => null,
            'meta' => [
                'paid_minutes_per_day' => 540,
                'scheduled_clock_minutes' => 600,
            ],
        ]);

        $this->assertTrue($row->isFlexibleShift());
        $this->assertTrue($row->hasConfiguredSchedule());
        $this->assertFalse($row->requiresFixedScheduleTimes());
        $this->assertSame(540, $row->paidMinutesPerDay());
        $this->assertSame(600, $row->scheduledClockMinutes());
    }

    public function test_regular_shift_without_plotted_times_is_not_a_complete_schedule(): void
    {
        $row = new DailyAttendanceSummary([
            'shift_name' => 'Regular Shift',
            'schedule_status' => 'scheduled',
            'attendance_status' => 'present',
            'scheduled_time_in' => null,
            'scheduled_time_out' => null,
        ]);

        $this->assertFalse($row->isFlexibleShift());
        $this->assertFalse($row->hasConfiguredSchedule());
    }

    public function test_explicit_no_schedule_row_is_never_treated_as_configured(): void
    {
        $row = new DailyAttendanceSummary([
            'shift_name' => 'No Schedule',
            'schedule_status' => 'no_schedule',
            'attendance_status' => 'no_schedule',
        ]);

        $this->assertFalse($row->hasConfiguredSchedule());
    }

    public function test_legacy_flexible_summary_is_detected_from_meta_even_when_shift_name_is_missing(): void
    {
        $row = new DailyAttendanceSummary([
            'shift_name' => 'No Schedule',
            'schedule_status' => 'no_schedule',
            'attendance_status' => 'present',
            'scheduled_time_in' => null,
            'scheduled_time_out' => null,
            'meta' => [
                'schedule_mode' => 'flexible',
                'has_configured_schedule' => true,
                'paid_minutes_per_day' => 540,
                'scheduled_clock_minutes' => 600,
            ],
        ]);

        $this->assertTrue($row->isFlexibleShift());
        $this->assertTrue($row->hasConfiguredSchedule());
        $this->assertFalse($row->requiresFixedScheduleTimes());
    }

    public function test_legacy_flexible_summary_is_detected_from_existing_remarks(): void
    {
        $row = new DailyAttendanceSummary([
            'shift_name' => null,
            'schedule_status' => 'no_schedule',
            'attendance_status' => 'present',
            'scheduled_time_in' => null,
            'scheduled_time_out' => null,
            'remarks' => 'Biometrics logs found: 2. Flexible shift completed 10 clock hours / 9 paid hours.',
        ]);

        $this->assertTrue($row->isFlexibleShift());
        $this->assertTrue($row->hasConfiguredSchedule());
        $this->assertFalse($row->requiresFixedScheduleTimes());
    }
}
