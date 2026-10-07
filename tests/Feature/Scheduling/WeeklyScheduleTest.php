<?php

declare(strict_types=1);

namespace Tests\Feature\Scheduling;

use App\Enums\WorkdayType;
use App\Models\DailyAttendanceSummary;
use App\Models\EmployeeBiometric;
use App\Models\EmployeePlottingSchedule;
use App\Models\MirasolBiometricsLog;
use App\Models\User;
use App\Repositories\Contracts\Scheduling\PlottingScheduleRepositoryInterface;
use App\Services\Payroll\DailyAttendanceSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Work Schedule "different time per day": e.g. Monday 09:00-18:00, Tuesday 06:00-15:00.
 * Mon Oct 5 / Tue Oct 6, 2026 are used below.
 */
final class WeeklyScheduleTest extends TestCase
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
            'source_key' => 'main:8801', 'source_crosschex_account' => 'main', 'source_employee_no' => '8801',
            'source_employee_name' => 'Lenberd Ilaw', 'display_name' => 'Lenberd Ilaw', 'display_employee_no' => '8801',
            'employment_status' => 'active', 'is_payroll_active' => true, 'group_name' => 1,
        ]);
    }

    public function test_a_per_day_schedule_is_saved_and_shown(): void
    {
        $this->save([
            'Monday' => ['time_in' => '09:00', 'time_out' => '18:00', 'workday_type' => 'eight_hours'],
            'Tuesday' => ['time_in' => '06:00', 'time_out' => '15:00', 'workday_type' => 'eight_hours'],
            'Wednesday' => ['time_in' => '07:00', 'time_out' => '17:00', 'workday_type' => 'nine_hours'],
            // A day off: dropped on save.
            'Sunday' => ['time_in' => '08:00', 'time_out' => '17:00', 'workday_type' => 'eight_hours'],
        ])->assertSessionHasNoErrors();

        $schedule = EmployeePlottingSchedule::query()->where('employee_biometric_id', $this->employee->id)->whereNull('work_date')->sole();
        $this->assertSame(['Monday', 'Tuesday', 'Wednesday'], array_keys($schedule->weeklyTimes()));
        $this->assertSame(['time_in' => '06:00', 'time_out' => '15:00', 'workday_type' => 'eight_hours'], $schedule->weeklyTimes()['Tuesday']);

        // Each date gets its own times; days without an entry keep the fixed times.
        $this->assertSame('09:00:00', $schedule->forDate('2026-10-05')->time_in);
        $tuesday = $schedule->forDate('2026-10-06');
        $this->assertSame(['06:00:00', '15:00:00'], [$tuesday->time_in, $tuesday->time_out]);
        $this->assertSame(540, $schedule->forDate('2026-10-07')->paidWorkMinutes(), 'Wednesday is 9 hours.');
        $this->assertSame('08:00', substr((string) $schedule->forDate('2026-10-08')->time_in, 0, 5), 'Thursday uses the fixed time.');
        $this->assertSame('08:00', substr((string) $schedule->time_in, 0, 5), 'The stored row itself is unchanged.');

        $this->assertSame('06:00:00', app(PlottingScheduleRepositoryInterface::class)->scheduleOn($this->employee->id, '2026-10-06')?->time_in);

        $this->client()->get(route('payroll-plotting.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('employees.data.0.schedule.weekly_times.Tuesday.time_in', '06:00'));
        $this->client()->get(route('biometrics.employees.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('employees.data.0.schedule.label', 'Different time per day')
            ->where('employees.data.0.schedule.hours', fn ($hours) => str_starts_with((string) $hours, 'Mon 9:00–6:00 PM, Tue 6:00–3:00 PM')));

        // Saving without per-day times makes it the same every day again.
        $this->save(null)->assertSessionHasNoErrors();
        $this->assertFalse(EmployeePlottingSchedule::query()->where('employee_biometric_id', $this->employee->id)->whereNull('work_date')->sole()->hasWeeklyTimes());
    }

    public function test_each_day_must_match_its_work_hours(): void
    {
        $this->save([
            'Monday' => ['time_in' => '09:00', 'time_out' => '18:00', 'workday_type' => 'eight_hours'],
            'Tuesday' => ['time_in' => '06:00', 'time_out' => '14:00', 'workday_type' => 'eight_hours'],
        ])->assertSessionHasErrors('schedule.0.weekly_times.Tuesday.time_out');

        $this->save(['Funday' => ['time_in' => '06:00', 'time_out' => '15:00', 'workday_type' => 'eight_hours']])
            ->assertSessionHasErrors('schedule.0.weekly_times');
        $this->assertSame(0, EmployeePlottingSchedule::query()->count());
    }

    public function test_attendance_summary_uses_the_time_of_each_day(): void
    {
        $this->save([
            'Monday' => ['time_in' => '09:00', 'time_out' => '18:00', 'workday_type' => 'eight_hours'],
            'Tuesday' => ['time_in' => '06:00', 'time_out' => '15:00', 'workday_type' => 'eight_hours'],
        ])->assertSessionHasNoErrors();

        // Monday: in at 09:00 = on time (would be 60 min late on an 08:00 schedule).
        $this->punch('2026-10-05 09:00:00');
        $this->punch('2026-10-05 18:00:00');
        // Tuesday: in at 06:40 = 40 min late against 06:00 (15 min grace, 30-min blocks → 60).
        $this->punch('2026-10-06 06:40:00');
        $this->punch('2026-10-06 15:00:00');

        $monday = $this->buildDay('2026-10-05');
        $tuesday = $this->buildDay('2026-10-06');

        $this->assertSame(['09:00', '18:00'], [substr((string) $monday->scheduled_time_in, 0, 5), substr((string) $monday->scheduled_time_out, 0, 5)]);
        $this->assertSame(0, (int) $monday->late_minutes);
        $this->assertSame(['06:00', '15:00'], [substr((string) $tuesday->scheduled_time_in, 0, 5), substr((string) $tuesday->scheduled_time_out, 0, 5)]);
        $this->assertGreaterThan(0, (int) $tuesday->late_minutes);
    }

    /** @param  array<string, array<string, string>>|null  $weekly */
    private function save(?array $weekly): \Illuminate\Testing\TestResponse
    {
        return $this->client()->post(route('payroll-plotting.save'), ['schedule' => [[
            'employee_biometric_id' => $this->employee->id, 'status' => 'scheduled', 'shift_name' => 'Regular Shift',
            'workday_type' => WorkdayType::EightHours->value, 'time_in' => '08:00', 'time_out' => '17:00',
            'grace_minutes' => 15, 'day_offs' => ['Sunday'], 'remarks' => '', 'weekly_times' => $weekly,
        ]]]);
    }

    private function punch(string $dateTime): void
    {
        MirasolBiometricsLog::query()->create([
            'crosschex_account' => 'main', 'crosschex_id' => sha1($dateTime), 'employee_no' => '8801',
            'employee_name' => 'Lenberd Ilaw', 'check_time' => $dateTime, 'device_sn' => '0770100024370009',
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
            ->withSession(['_token' => 'weekly-test', 'unlocked' => true, 'last_activity_time' => now()->timestamp])
            ->withHeader('X-CSRF-TOKEN', 'weekly-test');
    }
}
