<?php

declare(strict_types=1);

namespace Tests\Feature\Scheduling;

use App\Enums\WorkdayType;
use App\Models\Employee;
use App\Models\EmployeeBiometric;
use App\Models\EmployeePlottingSchedule;
use App\Models\Holiday;
use App\Models\PayrollEmployeeSalary;
use App\Models\User;
use App\Repositories\Biometrics\BiometricCompanyRepository;
use App\Repositories\Biometrics\EmployeeBiometricRepository;
use App\Repositories\Contracts\Biometrics\BiometricCompanyRepositoryInterface;
use App\Repositories\Contracts\Biometrics\EmployeeBiometricRepositoryInterface;
use App\Repositories\Contracts\Scheduling\EmployeeSalaryRepositoryInterface;
use App\Repositories\Contracts\Scheduling\HolidayRepositoryInterface;
use App\Repositories\Contracts\Scheduling\PlottingScheduleRepositoryInterface;
use App\Repositories\Scheduling\EmployeeSalaryRepository;
use App\Repositories\Scheduling\HolidayRepository;
use App\Repositories\Scheduling\PlottingScheduleRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Scheduling & Rates on the layered structure: bindings, the Work Schedule filter fix,
 * the removed holiday "show" route, and the write paths of all four pages.
 */
final class SchedulingLayeredStructureTest extends TestCase
{
    use RefreshDatabase;

    private const PERMISSIONS = [
        'biometrics.view', 'biometrics.edit', 'biometrics.update', 'biometrics.sync',
        'payroll-plotting.view', 'payroll-plotting.update',
        'employee-salaries.view', 'employee-salaries.create', 'employee-salaries.update', 'employee-salaries.delete',
        'holidays.view', 'holidays.create', 'holidays.update', 'holidays.delete',
    ];

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->userWith([...self::PERMISSIONS, 'payroll.all-access']);
    }

    public function test_repository_interfaces_resolve_to_their_eloquent_classes(): void
    {
        $this->assertInstanceOf(EmployeeBiometricRepository::class, app(EmployeeBiometricRepositoryInterface::class));
        $this->assertInstanceOf(BiometricCompanyRepository::class, app(BiometricCompanyRepositoryInterface::class));
        $this->assertInstanceOf(PlottingScheduleRepository::class, app(PlottingScheduleRepositoryInterface::class));
        $this->assertInstanceOf(EmployeeSalaryRepository::class, app(EmployeeSalaryRepositoryInterface::class));
        $this->assertInstanceOf(HolidayRepository::class, app(HolidayRepositoryInterface::class));
    }

    public function test_work_schedule_filters_run_across_all_pages(): void
    {
        foreach (range(1, 30) as $i) {
            $this->person(sprintf('AAA Person %02d', $i), (string) (1000 + $i));
        }
        // Sorted last by name, so on page 2 without a filter.
        $rest = $this->person('ZZZ Restday', '2000');
        EmployeePlottingSchedule::query()->create([
            'employee_biometric_id' => $rest->id, 'employee_name' => 'ZZZ Restday', 'work_date' => null, 'status' => 'rest_day', 'shift_name' => 'Flexible Shift',
            'workday_type' => WorkdayType::EightHours->value, 'grace_minutes' => 10, 'day_offs' => ['Sunday'],
        ]);

        $this->client()->get(route('payroll-plotting.index', ['status' => 'rest_day']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('payroll/plotting/index')
                ->where('employees.total', 1)
                ->where('employees.data.0.name', 'Restday, ZZZ')
                ->where('employees.data.0.schedule.grace_minutes', 10)
                ->where('employees.data.0.schedule.is_saved', true)
                ->where('stats.rest_day', 1));

        // People with no saved schedule count as "scheduled" and "Regular Shift".
        $this->client()->get(route('payroll-plotting.index', ['status' => 'scheduled']))
            ->assertInertia(fn (Assert $page) => $page->where('employees.total', 30)->has('employees.data', 25));
        $this->client()->get(route('payroll-plotting.index', ['shift' => 'Flexible Shift']))
            ->assertInertia(fn (Assert $page) => $page->where('employees.total', 1));
        $this->client()->get(route('payroll-plotting.index', ['shift' => 'Regular Shift', 'page' => 2]))
            ->assertInertia(fn (Assert $page) => $page->where('employees.total', 30)->has('employees.data', 5));
    }

    public function test_work_schedule_save_replaces_the_permanent_row(): void
    {
        $person = $this->person('Sam Schedule', '3001');
        $row = [
            'employee_biometric_id' => $person->id, 'status' => 'scheduled', 'shift_name' => 'Regular Shift',
            'workday_type' => WorkdayType::NineHours->value, 'time_in' => '07:00', 'time_out' => '17:00',
            'grace_minutes' => 5, 'day_offs' => ['Sunday', 'Monday', 'Sunday'], 'remarks' => '',
        ];

        foreach ([1, 2] as $run) {
            $this->client()->post(route('payroll-plotting.save'), ['schedule' => [$row], 'search' => 'Sam'])
                ->assertRedirect(route('payroll-plotting.index', ['search' => 'Sam']));
        }

        $schedule = EmployeePlottingSchedule::query()->where('employee_biometric_id', $person->id)->sole();
        $this->assertSame(['Monday', 'Sunday'], $schedule->resolvedDayOffs());
        $this->assertSame('Monday,Sunday', $schedule->day_off);
        $this->assertSame(WorkdayType::NineHours, $schedule->resolvedWorkdayType());

        // "inactive" takes the person off payroll and clears the clock times.
        $this->client()->post(route('payroll-plotting.save'), ['schedule' => [['status' => 'inactive', 'time_in' => null, 'time_out' => null] + $row]])->assertSessionHasNoErrors();
        $this->assertNull(EmployeePlottingSchedule::query()->where('employee_biometric_id', $person->id)->sole()->time_in);
        $this->assertFalse((bool) $person->refresh()->is_payroll_active);
    }

    public function test_employee_rates_create_uses_schedule_hours_and_respects_payroll_groups(): void
    {
        $mirasol = $this->person('Mia Mirasol', '4001', group: 1);
        $gonzales = $this->person('Gil Gonzales', '4002', group: 2);
        EmployeePlottingSchedule::query()->create([
            'employee_biometric_id' => $mirasol->id, 'employee_name' => 'Mia Mirasol', 'work_date' => null, 'status' => 'scheduled', 'shift_name' => 'Regular Shift',
            'workday_type' => WorkdayType::NineHours->value, 'paid_work_minutes' => 540, 'lunch_break_minutes' => 60, 'time_in' => '07:00', 'time_out' => '17:00',
        ]);

        $form = $this->client()->get(route('payroll-employee-salaries.create'))->assertOk();
        $values = $form->viewData('page')['props']['values'];
        $this->assertSame('daily', $values['rate_type']);

        $this->client()->post(route('payroll-employee-salaries.store'), [
            ...$values,
            'employee_biometric_id' => $mirasol->id, 'employee_name' => 'Mia Mirasol', 'basic_salary' => '900',
            'cash_advance_payment_amount' => '100', 'other_loan_payment_amount' => '50',
            'other_deductions' => [
                ['name' => '', 'total_amount' => '500', 'payment_amount' => '25', 'deduction_schedule' => 'every_cutoff'],
                ['name' => '', 'total_amount' => '0', 'payment_amount' => '0', 'deduction_schedule' => 'none'],
            ],
        ])->assertRedirect(route('payroll-employee-salaries.index'))->assertSessionHas('success', 'Salary record created successfully.');

        $salary = PayrollEmployeeSalary::query()->with('otherDeductions')->sole();
        $this->assertEqualsWithDelta(100.0, (float) $salary->ot_rate_per_hour, 0.01, '900 / 9 paid hours from the Work Schedule.');
        $this->assertEquals(100.0, (float) $salary->vale);
        $this->assertEquals(75.0, (float) $salary->other_loans, 'Other loan + other deductions per cutoff.');
        $this->assertSame(['Other Deduction'], $salary->otherDeductions->pluck('name')->all());

        // One rate per person.
        $this->client()->post(route('payroll-employee-salaries.store'), [...$values, 'employee_biometric_id' => $mirasol->id, 'employee_name' => 'Again', 'basic_salary' => '1'])
            ->assertSessionHasErrors('employee_biometric_id');

        $this->client()->post(route('payroll-employee-salaries.store'), [...$values, 'employee_biometric_id' => $gonzales->id, 'employee_name' => 'Gil Gonzales', 'basic_salary' => '700'])
            ->assertSessionHasNoErrors();
        $this->client()->get(route('payroll-employee-salaries.index'))->assertInertia(fn (Assert $page) => $page->has('salaries.data', 2));

        // A Gonzales-only payroll user sees only Gonzales people.
        $gonzalesUser = $this->userWith([...self::PERMISSIONS, 'payroll.gonzales'], 'Gonzales Payroll');
        $this->client($gonzalesUser)->get(route('payroll-employee-salaries.index'))
            ->assertInertia(fn (Assert $page) => $page->has('salaries.data', 1)->where('salaries.data.0.name', fn ($name) => str_contains((string) $name, 'Gil')));
        $this->client($gonzalesUser)->get(route('payroll-employee-salaries.create'))
            ->assertInertia(fn (Assert $page) => $page->has('people', 1)->where('people.0.employee_biometric_id', $gonzales->id));

        $this->client()->delete(route('payroll-employee-salaries.destroy', $salary))->assertSessionHas('success', 'Salary record deleted successfully.');
        $this->assertDatabaseMissing('payroll_employee_salaries', ['id' => $salary->id]);
    }

    public function test_salary_sync_runs_one_at_a_time(): void
    {
        $this->person('Sync Person', '5001');
        $lock = Cache::lock('payroll:employee-salaries:sync-from-biometrics', 300);
        $this->assertTrue($lock->get());

        $this->client()->post(route('payroll-employee-salaries.sync'))->assertSessionHas('warning', 'A biometric salary sync is already running.');
        $this->assertSame(0, PayrollEmployeeSalary::query()->count());

        $lock->release();
        $this->client()->post(route('payroll-employee-salaries.sync'))->assertSessionHas('success', 'Biometrics sync completed. 1 added, 0 updated, 0 skipped.');
        $this->client()->post(route('payroll-employee-salaries.sync'))->assertSessionHas('success', 'Biometrics sync completed. 0 added, 1 updated, 0 skipped.');
    }

    public function test_biometric_employee_update_moves_the_hr_link_and_inactive_leaves_payroll(): void
    {
        $person = $this->person('Lenberd Ilaw', '6001');
        $first = Employee::query()->create(['employee_id' => 'EMP-0001', 'full_name' => 'Old Link', 'company' => 'Jell Transport', 'garage' => 'Mirasol', 'employee_biometric_id' => $person->id]);
        $second = Employee::query()->create(['employee_id' => 'EMP-0002', 'full_name' => 'Ilaw Lenberd', 'company' => 'Jell Transport', 'garage' => 'Mirasol']);

        $this->client()->get(route('biometrics.employees.edit', $person))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('values.hr_employee_id', (string) $first->id)
                ->where('hrEmployees.0.label', 'Ilaw Lenberd')
                ->where('hrEmployees.0.hint', fn ($hint) => str_starts_with((string) $hint, 'Name match'))
                ->where('groupOptions', ['1' => 'Mirasol / Balintawak Payroll', '2' => 'Gonzales Payroll']));

        $this->client()->put(route('biometrics.employees.update', $person), [
            'employment_status' => 'inactive', 'is_payroll_active' => true, 'group_name' => '2',
            'display_name' => 'ILAW, LENBERD', 'hr_employee_id' => $second->id,
        ])->assertRedirect(route('biometrics.employees.index'))->assertSessionHasNoErrors();

        $person->refresh();
        $this->assertSame(['inactive', false], [$person->employment_status, (bool) $person->is_payroll_active]);
        $this->assertNotNull($person->inactive_at);
        $this->assertNull($first->refresh()->employee_biometric_id, 'The old HR employee is unlinked.');
        $this->assertSame($person->id, (int) $second->refresh()->employee_biometric_id);

        $this->client()->get(route('biometrics.employees.index', ['employment_status' => 'inactive']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('employees.data.0.hr_employee.name', 'Ilaw Lenberd')
                ->where('employees.data.0.group_label', 'Gonzales Payroll')
                ->where('counts.inactive', 1));
    }

    public function test_holiday_multipliers_and_removed_show_route(): void
    {
        $this->client()->post(route('holidays.store'), [
            'name' => 'Custom Day', 'actual_date' => '2026-11-02', 'observed_date' => '2026-11-02', 'holiday_type' => 'special',
            'override_multipliers' => true, 'not_worked_multiplier' => '0.555', 'worked_multiplier' => '1.5',
        ])->assertRedirect(route('holidays.index'));
        $holiday = Holiday::query()->sole();
        $this->assertSame([0.56, 1.5, true, false], [(float) $holiday->not_worked_multiplier, (float) $holiday->worked_multiplier, (bool) $holiday->is_active, (bool) $holiday->is_moved]);

        $this->client()->get(route('holidays.edit', $holiday))
            ->assertInertia(fn (Assert $page) => $page->where('values.override_multipliers', true)->where('values.not_worked_multiplier', '0.56'));

        // Without the override, the type's standard multipliers apply.
        $this->client()->put(route('holidays.update', $holiday), [
            'name' => 'Custom Day', 'actual_date' => '2026-11-02', 'observed_date' => '2026-11-03', 'holiday_type' => 'regular', 'is_moved' => true,
        ])->assertSessionHasNoErrors();
        $holiday->refresh();
        $this->assertSame([1.0, 2.0, true], [(float) $holiday->not_worked_multiplier, (float) $holiday->worked_multiplier, (bool) $holiday->is_moved]);

        $this->client()->get(route('holidays.index', ['year' => 2026, 'month' => 11]))
            ->assertInertia(fn (Assert $page) => $page->where('calendar.2026-11-03.0.moved_from', 'Nov 02'));

        // "show" had no method behind it (a 500); it is gone now.
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('holidays.show'));
        $this->client()->get('/holidays/'.$holiday->id)->assertStatus(405);
    }

    private function person(string $name, string $number, int $group = 1): EmployeeBiometric
    {
        return EmployeeBiometric::query()->create([
            'source_key' => "main:{$number}", 'source_crosschex_account' => 'main', 'source_employee_no' => $number,
            'source_employee_name' => $name, 'display_name' => $name, 'display_employee_no' => $number,
            'employment_status' => 'active', 'is_payroll_active' => true, 'group_name' => $group,
        ]);
    }

    /** @param list<string> $permissions */
    private function userWith(array $permissions, string $roleName = 'Scheduling Tester'): User
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        $role = Role::findOrCreate($roleName, 'web');
        $role->syncPermissions($permissions);
        $user = User::factory()->create(['account_status' => 'active', 'must_change_password' => false]);
        $user->assignRole($role);

        return $user;
    }

    private function client(?User $user = null): static
    {
        return $this->actingAs($user ?? $this->user)
            ->withSession(['_token' => 'scheduling-test', 'unlocked' => true, 'last_activity_time' => now()->timestamp])
            ->withHeader('X-CSRF-TOKEN', 'scheduling-test');
    }
}
