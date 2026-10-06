<?php

declare(strict_types=1);

namespace Tests\Feature\Scheduling;

use App\Enums\WorkdayType;
use App\Models\EmployeeBiometric;
use App\Models\EmployeePlottingSchedule;
use App\Models\PayrollEmployeeSalary;
use App\Models\User;
use App\Support\Navigation\MainNavigation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Scheduling & Rates → Employees: one profile per person with Details, Work schedule and
 * Rates tabs. Saves use the existing routes; `return_profile` brings them back to the profile.
 */
final class EmployeeProfileTest extends TestCase
{
    use RefreshDatabase;

    private const ALL = [
        'biometrics.view', 'biometrics.edit', 'biometrics.update',
        'payroll-plotting.view', 'payroll-plotting.update',
        'employee-salaries.view', 'employee-salaries.create', 'employee-salaries.update',
    ];

    public function test_the_profile_shows_every_tab_and_what_is_missing(): void
    {
        $person = $this->person('Ana Santos', '7001');
        $user = $this->userWith([...self::ALL, 'payroll.all-access']);

        $this->client($user)->get(route('biometrics.employees.show', $person))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('biometrics/employees/show')
            ->where('employee.display_name', 'Santos, Ana')
            ->where('details.values.display_name', 'Ana Santos')
            ->where('schedule.row.schedule.is_saved', false)
            ->where('schedule.canUpdate', true)
            ->where('rates.mode', 'create')
            ->where('rates.form.values.employee_biometric_id', $person->id)
            ->where('rates.form.people.0.employee_biometric_id', $person->id)
            ->where('checklist', fn ($items) => collect($items)->where('done', false)->pluck('label')->values()->all() === ['Company tag', 'Linked HR employee', 'Work schedule', 'Employee rate']));

        // List rows carry the summary and the profile link.
        $this->client($user)->get(route('biometrics.employees.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('biometrics/employees/index')
            ->where('employees.data.0.show_url', route('biometrics.employees.show', $person))
            ->where('employees.data.0.schedule', null)
            ->where('employees.data.0.rate', ['visible' => true, 'label' => null])
            ->where('can.bulkSchedule', true));
    }

    public function test_each_tab_saves_through_its_own_route_and_returns_to_the_profile(): void
    {
        $person = $this->person('Ana Santos', '7001');
        $other = $this->person('Ben Cruz', '7002');
        EmployeePlottingSchedule::query()->create([
            'employee_biometric_id' => $other->id, 'employee_name' => 'Ben Cruz', 'work_date' => null, 'status' => 'scheduled', 'shift_name' => 'Regular Shift',
            'workday_type' => WorkdayType::EightHours->value, 'time_in' => '06:00', 'time_out' => '15:00', 'grace_minutes' => 5, 'day_offs' => ['Sunday'],
        ]);
        $user = $this->userWith([...self::ALL, 'payroll.all-access']);
        $profile = route('biometrics.employees.show', $person);

        // Work schedule: only this person changes.
        $this->client($user)->post(route('payroll-plotting.save'), [
            'return_profile' => $person->id,
            'schedule' => [[
                'employee_biometric_id' => $person->id, 'status' => 'scheduled', 'shift_name' => 'Regular Shift', 'workday_type' => 'nine_hours',
                'time_in' => '07:00', 'time_out' => '17:00', 'grace_minutes' => 10, 'day_offs' => ['Saturday', 'Sunday'], 'remarks' => '',
            ]],
        ])->assertRedirect($profile)->assertSessionHasNoErrors();

        $schedule = EmployeePlottingSchedule::query()->where('employee_biometric_id', $person->id)->whereNull('work_date')->sole();
        $this->assertSame([WorkdayType::NineHours, 10, ['Saturday', 'Sunday']], [$schedule->resolvedWorkdayType(), (int) $schedule->grace_minutes, $schedule->resolvedDayOffs()]);
        $this->assertSame('06:00', substr((string) EmployeePlottingSchedule::query()->where('employee_biometric_id', $other->id)->sole()->time_in, 0, 5));

        // Rates: create from the profile form values, then update.
        $values = $this->client($user)->get($profile)->viewData('page')['props']['rates']['form']['values'];
        $this->client($user)->post(route('payroll-employee-salaries.store'), [...$values, 'basic_salary' => '810', 'return_profile' => $person->id])
            ->assertRedirect($profile)->assertSessionHas('success', 'Salary record created successfully.');
        $salary = PayrollEmployeeSalary::query()->where('employee_biometric_id', $person->id)->sole();
        $this->assertEqualsWithDelta(90.0, (float) $salary->ot_rate_per_hour, 0.01, '810 / 9 paid hours from the schedule saved above.');

        $this->client($user)->get($profile)->assertInertia(fn (Assert $page) => $page
            ->where('rates.mode', 'edit')
            ->where('rates.form.salary.id', $salary->id)
            ->where('schedule.row.schedule.is_saved', true));

        $values = $this->client($user)->get($profile)->viewData('page')['props']['rates']['form']['values'];
        $this->client($user)->put(route('payroll-employee-salaries.update', $salary), [...$values, 'basic_salary' => '900', 'return_profile' => $person->id])
            ->assertRedirect($profile);
        $this->assertEquals(900.0, (float) $salary->refresh()->basic_salary);

        // Details.
        $this->client($user)->put(route('biometrics.employees.update', $person), [
            'employment_status' => 'active', 'is_payroll_active' => true, 'group_name' => '1', 'display_name' => 'ANA SANTOS', 'return_profile' => $person->id,
        ])->assertRedirect($profile)->assertSessionHasNoErrors();
        $this->assertSame('ANA SANTOS', $person->refresh()->display_name);

        // Without return_profile the old pages redirect as before.
        $this->client($user)->put(route('biometrics.employees.update', $person), [
            'employment_status' => 'active', 'is_payroll_active' => true, 'group_name' => '1', 'display_name' => 'ANA SANTOS',
        ])->assertRedirect(route('biometrics.employees.index'));
        $this->client($user)->put(route('payroll-employee-salaries.update', $salary), $values)->assertRedirect(route('payroll-employee-salaries.index'));
        // A return_profile of another person is ignored.
        $this->client($user)->put(route('payroll-employee-salaries.update', $salary), [...$values, 'return_profile' => $other->id])
            ->assertRedirect(route('payroll-employee-salaries.index'));

        $this->client($user)->get(route('biometrics.employees.index', ['search' => '7001']))->assertInertia(fn (Assert $page) => $page
            ->has('employees.data', 1)
            ->where('employees.data.0.schedule.label', WorkdayType::NineHours->shortLabel())
            ->where('employees.data.0.schedule.hours', '7:00 AM – 5:00 PM')
            // The redirect checks above re-sent the earlier form values (810).
            ->where('employees.data.0.rate.label', '₱810.00 / day'));
    }

    public function test_tabs_follow_permissions_and_payroll_groups(): void
    {
        $mirasol = $this->person('Mia Mirasol', '7101', group: 1);
        $gonzales = $this->person('Gil Gonzales', '7102', group: 2);

        // Rates-only user: can open the Employees page and see the Rates tab, nothing else.
        $ratesOnly = $this->userWith(['employee-salaries.view', 'employee-salaries.update', 'payroll.mirasol'], 'Rates only');
        $this->client($ratesOnly)->get(route('biometrics.employees.index'))->assertOk();
        $this->client($ratesOnly)->get(route('biometrics.employees.show', $mirasol))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('details', null)
            ->where('schedule', null)
            ->where('rates.mode', 'unavailable')
            ->where('rates.message', 'No rate yet. You do not have permission to add one.'));

        // Another payroll group: the rate stays hidden.
        $this->client($ratesOnly)->get(route('biometrics.employees.show', $gonzales))->assertInertia(fn (Assert $page) => $page
            ->where('rates.mode', 'unavailable')
            ->where('checklist', fn ($items) => ! collect($items)->contains('label', 'Employee rate')));
        $this->client($ratesOnly)->get(route('biometrics.employees.index', ['group_name' => '2']))->assertInertia(fn (Assert $page) => $page
            ->where('employees.data.0.rate', ['visible' => false, 'label' => null]));

        // Schedule viewer without update: read-only schedule, no rates tab.
        $viewer = $this->userWith(['payroll-plotting.view'], 'Schedule viewer');
        $this->client($viewer)->get(route('biometrics.employees.show', $mirasol))->assertInertia(fn (Assert $page) => $page
            ->where('schedule.canUpdate', false)
            ->where('rates', null));

        // No permission at all.
        $nobody = $this->userWith(['holidays.view'], 'Nobody');
        $this->client($nobody)->get(route('biometrics.employees.show', $mirasol))->assertForbidden();
        $this->client($nobody)->get(route('biometrics.employees.index'))->assertForbidden();
    }

    public function test_sidebar_shows_one_employees_link_for_any_of_the_three_permissions(): void
    {
        $ratesOnly = $this->userWith(['employee-salaries.view'], 'Rates only');
        $request = Request::create(route('payroll-employee-salaries.index'));
        $request->setRouteResolver(fn () => app('router')->getRoutes()->match($request));

        $groups = collect(MainNavigation::for($ratesOnly, $request));
        $scheduling = $groups->firstWhere('label', 'Scheduling & Rates');
        $titles = collect($scheduling['items'])->pluck('title')->all();

        $this->assertSame(['Employees'], $titles);
        $this->assertTrue($scheduling['items'][0]['isActive'], 'Employees stays highlighted on the Employee Rates page.');
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
    private function userWith(array $permissions, string $roleName = 'Profile Tester'): User
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

    private function client(User $user): static
    {
        return $this->actingAs($user)
            ->withSession(['_token' => 'profile-test', 'unlocked' => true, 'last_activity_time' => now()->timestamp])
            ->withHeader('X-CSRF-TOKEN', 'profile-test');
    }
}
