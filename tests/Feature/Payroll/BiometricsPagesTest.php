<?php

declare(strict_types=1);

namespace Tests\Feature\Payroll;

use App\Models\BiometricCompany;
use App\Models\EmployeeBiometric;
use App\Models\MirasolBiometricsLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class BiometricsPagesTest extends TestCase
{
    use RefreshDatabase;

    private EmployeeBiometric $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->employee = EmployeeBiometric::query()->create([
            'source_key' => 'main:4713002',
            'source_crosschex_account' => 'main',
            'source_employee_no' => '4713002',
            'source_employee_name' => 'Divina Bartolo',
            'display_name' => 'Divina Bartolo',
            'employment_status' => 'active',
            'is_payroll_active' => true,
            'group_name' => 1,
        ]);
    }

    public function test_biometrics_sync_page_searches_cutoff_attendance(): void
    {
        MirasolBiometricsLog::query()->create([
            'crosschex_account' => 'main',
            'crosschex_id' => sha1('in'),
            'employee_no' => '4713002',
            'employee_name' => 'Divina Bartolo',
            'check_time' => '2026-09-14 08:02:00',
            'device_sn' => 'DEV-1',
        ]);
        MirasolBiometricsLog::query()->create([
            'crosschex_account' => 'main',
            'crosschex_id' => sha1('out'),
            'employee_no' => '4713002',
            'employee_name' => 'Divina Bartolo',
            'check_time' => '2026-09-14 17:05:00',
            'device_sn' => 'DEV-1',
        ]);

        $client = $this->client(['mirasol-logs.view']);

        $client->get(route('mirasol-logs.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('biometrics/sync/index')
                ->where('isSearch', false)
                ->has('rows.data', 0)
                ->where('can.sync', false)
                ->where('people.0.value', 'Divina Bartolo'));

        $client->get(route('mirasol-logs.index', ['q' => '4713002', 'cutoff_month' => 9, 'cutoff_year' => 2026, 'cutoff_type' => '11_25']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('isSearch', true)
                ->where('rows.total', 15)
                ->where('rows.data.3.date_label', 'September 14, 2026 (Monday)')
                ->where('rows.data.3.actual_time_in', '08:02 AM')
                ->where('rows.data.3.actual_time_out', '05:05 PM')
                ->where('rows.data.3.attendance_note', 'No plotted schedule found.')
                ->where('rows.data.3.worked_hours_label', '09:03'));
    }

    public function test_biometric_employee_list_edit_and_update(): void
    {
        $client = $this->client(['biometrics.view', 'biometrics.edit', 'biometrics.update']);

        $client->get(route('biometrics.employees.index', ['search' => 'Bartolo']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('biometrics/employees/index')
                ->has('employees.data', 1)
                ->where('employees.data.0.group_label', 'Mirasol / Balintawak Payroll')
                ->where('employees.data.0.payroll_included', true)
                ->where('counts.total', 1)
                ->where('can.edit', true)
                ->where('can.sync', false));

        $client->get(route('biometrics.employees.edit', $this->employee))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('biometrics/employees/edit')
                ->where('values.group_name', '1')
                ->where('values.is_payroll_active', true)
                ->where('employee.source_employee_no', '4713002'));

        $company = BiometricCompany::query()->create(['name' => 'Jell Transport']);

        $client->put(route('biometrics.employees.update', $this->employee), [
            'biometric_company_id' => (string) $company->id,
            'group_name' => '2',
            'employment_status' => 'active',
            'is_payroll_active' => false,
            'display_employee_no' => 'EMP-1',
            'display_name' => 'Divina Bartolo',
            'remarks' => 'Moved to Gonzales',
        ])->assertRedirect(route('biometrics.employees.index'))->assertSessionHasNoErrors();

        $this->employee->refresh();
        $this->assertSame(2, (int) $this->employee->group_name);
        $this->assertFalse((bool) $this->employee->is_payroll_active);
        $this->assertSame($company->id, (int) $this->employee->biometric_company_id);
        $this->assertSame('EMP-1', $this->employee->display_employee_no);
    }

    public function test_company_tag_can_be_added(): void
    {
        $this->client(['biometrics.view', 'biometrics.create'])
            ->from(route('biometrics.employees.index'))
            ->post(route('biometrics.companies.store'), ['name' => 'Jell Transport', 'remarks' => 'Main'])
            ->assertRedirect(route('biometrics.employees.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('biometric_companies', ['name' => 'Jell Transport']);
    }

    /** @param list<string> $permissions */
    private function client(array $permissions): static
    {
        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $user = User::factory()->create(['account_status' => 'active', 'must_change_password' => false]);
        $role = Role::findOrCreate('Biometrics Tester', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        return $this->actingAs($user)
            ->withSession(['_token' => 'bio-test', 'unlocked' => true, 'last_activity_time' => now()->timestamp])
            ->withHeader('X-CSRF-TOKEN', 'bio-test');
    }
}
