<?php

declare(strict_types=1);

namespace Tests\Feature\Payroll;

use App\Enums\WorkdayType;
use App\Models\EmployeeBiometric;
use App\Models\EmployeePlottingSchedule;
use App\Models\MirasolBiometricsLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The Biometrics Sync preview (`mirasol-logs.index`) is what HR checks before running payroll.
 * Its late/undertime and overnight-shift handling must match what DailyAttendanceSummaryService
 * will actually compute, so the preview isn't misleading.
 */
final class BiometricsSyncPreviewAccuracyTest extends TestCase
{
    use RefreshDatabase;

    private EmployeeBiometric $employee;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('payroll.attendance.late_grace_minutes', 15);
        config()->set('payroll.attendance.late_deduction_block_minutes', 30);
        config()->set('payroll.attendance.undertime_grace_minutes', 5);
        config()->set('payroll.attendance.undertime_deduction_block_minutes', 30);

        $this->employee = EmployeeBiometric::query()->create([
            'source_key' => 'main:9101', 'source_crosschex_account' => 'main', 'source_employee_no' => '9101',
            'source_employee_name' => 'Preview Checker', 'display_name' => 'Preview Checker',
            'employment_status' => 'active', 'is_payroll_active' => true, 'group_name' => 1,
        ]);
    }

    public function test_late_minutes_use_the_same_grace_cliff_and_block_rounding_as_payroll(): void
    {
        $this->setSchedule(WorkdayType::EightHours, '08:00', '17:00');

        // 20 minutes late against an 08:00 start with 15-min grace.
        // Payroll rule: raw (20) > grace (15) -> round the FULL raw value up to the next 30-min block -> 30.
        $this->punch('2026-09-14 08:20:00');
        $this->punch('2026-09-14 17:00:00');

        $this->client()->get(route('mirasol-logs.index', ['q' => '9101', 'cutoff_month' => 9, 'cutoff_year' => 2026, 'cutoff_type' => '11_25']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('rows.data.3.date_label', 'September 14, 2026 (Monday)')
                ->where('rows.data.3.late_label', '00:30')
                ->where('rows.data.3.attendance_note', 'Late'));
    }

    public function test_overnight_shift_pairs_the_next_mornings_checkout_with_the_start_day(): void
    {
        $this->setSchedule(WorkdayType::StraightEightHours, '22:00', '06:00');

        // Clocks in 10:00 PM on the 14th, clocks out 6:00 AM on the 15th.
        $this->punch('2026-09-14 22:00:00');
        $this->punch('2026-09-15 06:00:00');

        $this->client()->get(route('mirasol-logs.index', ['q' => '9101', 'cutoff_month' => 9, 'cutoff_year' => 2026, 'cutoff_type' => '11_25']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('rows.data.3.date_label', 'September 14, 2026 (Monday)')
                ->where('rows.data.3.actual_time_in', '10:00 PM')
                ->where('rows.data.3.actual_time_out', '06:00 AM')
                ->where('rows.data.3.worked_hours_label', '08:00')
                ->where('rows.data.3.attendance_note', 'On Time')
                // The 6:00 AM punch belongs to the 14th's overnight shift, not a fresh lone punch on the 15th.
                ->where('rows.data.4.date_label', 'September 15, 2026 (Tuesday)')
                ->where('rows.data.4.attendance_note', 'Absent'));
    }

    private function setSchedule(WorkdayType $type, string $timeIn, string $timeOut): void
    {
        EmployeePlottingSchedule::query()->create([
            'employee_biometric_id' => $this->employee->id, 'biometric_employee_id' => null,
            'employee_no' => '9101', 'employee_name' => 'Preview Checker', 'work_date' => null,
            'shift_name' => 'Regular Shift', 'workday_type' => $type->value,
            'paid_work_minutes' => $type->paidMinutes(), 'lunch_break_minutes' => $type->lunchMinutes(),
            'time_in' => $timeIn, 'time_out' => $timeOut, 'grace_minutes' => 15, 'status' => 'scheduled',
            'day_offs' => [],
        ]);
    }

    private function punch(string $dateTime): void
    {
        MirasolBiometricsLog::query()->create([
            'crosschex_account' => 'main', 'crosschex_id' => sha1($dateTime), 'employee_no' => '9101',
            'employee_name' => 'Preview Checker', 'check_time' => $dateTime, 'device_sn' => '0770100024370009',
        ]);
    }

    private function client(): static
    {
        Permission::findOrCreate('mirasol-logs.view', 'web');
        $user = User::factory()->create(['account_status' => 'active', 'must_change_password' => false]);
        $role = Role::findOrCreate('Biometrics Preview Tester', 'web');
        $role->syncPermissions(['mirasol-logs.view']);
        $user->assignRole($role);

        return $this->actingAs($user)
            ->withSession(['_token' => 'preview-test', 'unlocked' => true, 'last_activity_time' => now()->timestamp])
            ->withHeader('X-CSRF-TOKEN', 'preview-test');
    }
}
