<?php

declare(strict_types=1);

namespace Tests\Feature\Scheduling;

use App\Enums\WorkdayType;
use App\Models\DailyAttendanceSummary;
use App\Models\EmployeeBiometric;
use App\Models\EmployeePlottingSchedule;
use App\Models\MirasolBiometricsLog;
use App\Models\User;
use App\Services\Payroll\DailyAttendanceSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Flexible Shift sub-modes: Anytime (legacy, no clock window), Condition (clock-in window,
 * then complete the required clock hours) and Custom (fixed time in/out, Regular Shift rules).
 */
final class FlexibleShiftModesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private EmployeeBiometric $employee;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['payroll-plotting.view', 'payroll-plotting.update', 'biometrics.view'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        $role = Role::findOrCreate('Scheduler', 'web');
        $role->syncPermissions(['payroll-plotting.view', 'payroll-plotting.update', 'biometrics.view']);
        $this->user = User::factory()->create(['account_status' => 'active', 'must_change_password' => false]);
        $this->user->assignRole($role);

        $this->employee = EmployeeBiometric::query()->create([
            'source_key' => 'main:8802', 'source_crosschex_account' => 'main', 'source_employee_no' => '8802',
            'source_employee_name' => 'Flexi Worker', 'display_name' => 'Flexi Worker', 'display_employee_no' => '8802',
            'employment_status' => 'active', 'is_payroll_active' => true, 'group_name' => 1,
        ]);
    }

    public function test_anytime_mode_keeps_no_fixed_times(): void
    {
        $this->save(['flexible_mode' => 'anytime', 'time_in' => '08:00', 'time_out' => '17:00'])
            ->assertSessionHasNoErrors();

        $schedule = EmployeePlottingSchedule::query()->where('employee_biometric_id', $this->employee->id)->sole();
        $this->assertSame('anytime', $schedule->flexible_mode);
        $this->assertNull($schedule->time_in);
        $this->assertNull($schedule->time_out);
    }

    public function test_condition_mode_requires_a_clock_in_window(): void
    {
        $this->save(['flexible_mode' => 'condition', 'time_in' => '', 'time_out' => ''])
            ->assertSessionHasErrors('schedule.0.time_in');
        $this->assertSame(0, EmployeePlottingSchedule::query()->count());

        $this->save(['flexible_mode' => 'condition', 'time_in' => '06:00', 'time_out' => '06:00'])
            ->assertSessionHasErrors('schedule.0.time_out');
        $this->assertSame(0, EmployeePlottingSchedule::query()->count());

        $this->save(['flexible_mode' => 'condition', 'time_in' => '06:00', 'time_out' => '10:00'])
            ->assertSessionHasNoErrors();

        $schedule = EmployeePlottingSchedule::query()->where('employee_biometric_id', $this->employee->id)->sole();
        $this->assertSame('condition', $schedule->flexible_mode);
        $this->assertSame('06:00:00', $schedule->time_in);
        $this->assertSame('10:00:00', $schedule->time_out);
    }

    public function test_condition_mode_is_on_time_inside_the_window_and_present_once_hours_are_completed(): void
    {
        $this->save(['flexible_mode' => 'condition', 'time_in' => '06:00', 'time_out' => '10:00'])
            ->assertSessionHasNoErrors();

        // Clocked in at 07:00, inside the 06:00-10:00 window. 9 clock hours later = 16:00.
        $this->punch('2026-10-05 07:00:00');
        $this->punch('2026-10-05 16:00:00');

        $summary = $this->buildDay('2026-10-05');

        $this->assertSame(0, (int) $summary->late_minutes);
        $this->assertSame(0, (int) $summary->undertime_minutes);
        $this->assertSame('present', $summary->attendance_status);
        $this->assertSame(1.0, (float) $summary->payable_days);
    }

    public function test_condition_mode_is_late_when_clocking_in_after_the_window(): void
    {
        $this->save(['flexible_mode' => 'condition', 'time_in' => '06:00', 'time_out' => '10:00'])
            ->assertSessionHasNoErrors();

        // Clocked in at 10:30, 30 minutes after the window end (10:00), past the 15-min grace.
        $this->punch('2026-10-05 10:30:00');
        $this->punch('2026-10-05 19:30:00');

        $summary = $this->buildDay('2026-10-05');

        $this->assertGreaterThan(0, (int) $summary->late_minutes);
        $this->assertSame(0, (int) $summary->undertime_minutes, 'The full 9 clock hours were still completed.');
        $this->assertSame('late', $summary->attendance_status);
        $this->assertLessThan(1.0, (float) $summary->payable_days);
    }

    public function test_condition_mode_is_undertime_when_the_required_hours_are_not_completed(): void
    {
        $this->save(['flexible_mode' => 'condition', 'time_in' => '06:00', 'time_out' => '10:00'])
            ->assertSessionHasNoErrors();

        // On time in, but leaves early: only 5 clock hours worked instead of 9.
        $this->punch('2026-10-05 07:00:00');
        $this->punch('2026-10-05 12:00:00');

        $summary = $this->buildDay('2026-10-05');

        $this->assertSame(0, (int) $summary->late_minutes);
        $this->assertGreaterThan(0, (int) $summary->undertime_minutes);
        $this->assertSame('undertime', $summary->attendance_status);
    }

    public function test_custom_mode_requires_an_exact_clock_hour_span(): void
    {
        $this->save(['flexible_mode' => 'custom', 'time_in' => '', 'time_out' => ''])
            ->assertSessionHasErrors('schedule.0.time_in');

        $this->save(['flexible_mode' => 'custom', 'time_in' => '08:00', 'time_out' => '14:00'])
            ->assertSessionHasErrors('schedule.0.time_out');
        $this->assertSame(0, EmployeePlottingSchedule::query()->count());

        $this->save(['flexible_mode' => 'custom', 'time_in' => '08:00', 'time_out' => '17:00'])
            ->assertSessionHasNoErrors();

        $schedule = EmployeePlottingSchedule::query()->where('employee_biometric_id', $this->employee->id)->sole();
        $this->assertSame('custom', $schedule->flexible_mode);
        $this->assertSame('08:00:00', $schedule->time_in);
        $this->assertSame('17:00:00', $schedule->time_out);
    }

    public function test_custom_mode_computes_late_and_undertime_like_regular_shift(): void
    {
        $this->save(['flexible_mode' => 'custom', 'time_in' => '08:00', 'time_out' => '17:00'])
            ->assertSessionHasNoErrors();

        // In at 08:40 (40 min late against 08:00 + 15 min grace) and leaves on time.
        $this->punch('2026-10-05 08:40:00');
        $this->punch('2026-10-05 17:00:00');

        $summary = $this->buildDay('2026-10-05');

        $this->assertSame(['08:00', '17:00'], [substr((string) $summary->scheduled_time_in, 0, 5), substr((string) $summary->scheduled_time_out, 0, 5)]);
        $this->assertGreaterThan(0, (int) $summary->late_minutes);
        $this->assertTrue($summary->requiresFixedScheduleTimes());
    }

    /** @param  array{flexible_mode: string, time_in: string, time_out: string}  $overrides */
    private function save(array $overrides): \Illuminate\Testing\TestResponse
    {
        return $this->client()->post(route('payroll-plotting.save'), ['schedule' => [array_merge([
            'employee_biometric_id' => $this->employee->id, 'status' => 'scheduled', 'shift_name' => 'Flexible Shift',
            'workday_type' => WorkdayType::EightHours->value, 'grace_minutes' => 15, 'day_offs' => [], 'remarks' => '',
        ], $overrides)]]);
    }

    private function punch(string $dateTime): void
    {
        MirasolBiometricsLog::query()->create([
            'crosschex_account' => 'main', 'crosschex_id' => sha1($dateTime), 'employee_no' => '8802',
            'employee_name' => 'Flexi Worker', 'check_time' => $dateTime, 'device_sn' => '0770100024370009',
        ]);
    }

    private function buildDay(string $date): DailyAttendanceSummary
    {
        app(DailyAttendanceSummaryService::class)->buildForDate($date);

        return DailyAttendanceSummary::query()->where('employee_biometric_id', $this->employee->id)->whereDate('work_date', $date)->sole();
    }

    private function client(): static
    {
        return $this->actingAs($this->user)
            ->withSession(['_token' => 'flexible-test', 'unlocked' => true, 'last_activity_time' => now()->timestamp])
            ->withHeader('X-CSRF-TOKEN', 'flexible-test');
    }
}
