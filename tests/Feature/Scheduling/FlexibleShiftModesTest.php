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
 * Flexible Shift sub-modes: Anytime (legacy, no fixed time) and Custom (one or more exact
 * shift-time options, e.g. 8:00 AM-5:00 PM or 9:00 AM-6:00 PM; the actual time in picks the
 * closest option, then late/undertime are computed the same way as Regular Shift).
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
        $this->save('anytime', [])->assertSessionHasNoErrors();

        $schedule = EmployeePlottingSchedule::query()->where('employee_biometric_id', $this->employee->id)->sole();
        $this->assertSame('anytime', $schedule->flexible_mode);
        $this->assertNull($schedule->time_in);
        $this->assertNull($schedule->time_out);
        $this->assertSame([], $schedule->resolvedShiftOptions());
    }

    public function test_custom_mode_requires_at_least_one_option(): void
    {
        $this->save('custom', [])->assertSessionHasErrors('schedule.0.flexible_shift_options');
        $this->assertSame(0, EmployeePlottingSchedule::query()->count());
    }

    public function test_custom_mode_requires_each_option_to_span_the_exact_clock_hours(): void
    {
        $this->save('custom', [['time_in' => '08:00', 'time_out' => '14:00']])
            ->assertSessionHasErrors('schedule.0.flexible_shift_options.0.time_out');
        $this->assertSame(0, EmployeePlottingSchedule::query()->count());
    }

    public function test_custom_mode_saves_multiple_options_and_mirrors_the_first_one(): void
    {
        $this->save('custom', [
            ['time_in' => '08:00', 'time_out' => '17:00'],
            ['time_in' => '09:00', 'time_out' => '18:00'],
        ])->assertSessionHasNoErrors();

        $schedule = EmployeePlottingSchedule::query()->where('employee_biometric_id', $this->employee->id)->sole();
        $this->assertSame('custom', $schedule->flexible_mode);
        $this->assertSame('08:00:00', $schedule->time_in, 'The first option mirrors into the single time_in column.');
        $this->assertSame('17:00:00', $schedule->time_out);
        $this->assertSame(
            [['time_in' => '08:00', 'time_out' => '17:00'], ['time_in' => '09:00', 'time_out' => '18:00']],
            $schedule->resolvedShiftOptions()
        );
    }

    public function test_custom_mode_detects_which_option_the_employee_clocked_into(): void
    {
        $this->save('custom', [
            ['time_in' => '08:00', 'time_out' => '17:00'],
            ['time_in' => '09:00', 'time_out' => '18:00'],
        ])->assertSessionHasNoErrors();

        // Clocks in near 9:00 (the second option), on time, leaves on time.
        $this->punch('2026-10-05 09:05:00');
        $this->punch('2026-10-05 18:00:00');

        $summary = $this->buildDay('2026-10-05');

        $this->assertSame(['09:00', '18:00'], [substr((string) $summary->scheduled_time_in, 0, 5), substr((string) $summary->scheduled_time_out, 0, 5)]);
        $this->assertSame(0, (int) $summary->late_minutes, '5 minutes is within the 15-min grace.');
        $this->assertSame(0, (int) $summary->undertime_minutes);
        $this->assertSame('present', $summary->attendance_status);
        $this->assertTrue($summary->requiresFixedScheduleTimes());
    }

    public function test_custom_mode_is_late_against_the_matched_option(): void
    {
        $this->save('custom', [
            ['time_in' => '08:00', 'time_out' => '17:00'],
            ['time_in' => '09:00', 'time_out' => '18:00'],
        ])->assertSessionHasNoErrors();

        // Closer to the 8:00 option (25 min away) than the 9:00 one (35 min away), and late against it.
        $this->punch('2026-10-05 08:25:00');
        $this->punch('2026-10-05 17:00:00');

        $summary = $this->buildDay('2026-10-05');

        $this->assertSame(['08:00', '17:00'], [substr((string) $summary->scheduled_time_in, 0, 5), substr((string) $summary->scheduled_time_out, 0, 5)]);
        $this->assertGreaterThan(0, (int) $summary->late_minutes);
    }

    /** @param  list<array{time_in: string, time_out: string}>  $options */
    private function save(string $flexibleMode, array $options): \Illuminate\Testing\TestResponse
    {
        return $this->client()->post(route('payroll-plotting.save'), ['schedule' => [[
            'employee_biometric_id' => $this->employee->id, 'status' => 'scheduled', 'shift_name' => 'Flexible Shift',
            'flexible_mode' => $flexibleMode, 'flexible_shift_options' => $options,
            'workday_type' => WorkdayType::EightHours->value, 'grace_minutes' => 15, 'day_offs' => [], 'remarks' => '',
        ]]]);
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
