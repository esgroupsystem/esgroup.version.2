<?php

declare(strict_types=1);

namespace Tests\Feature\HRDepartment;

use App\Models\Claim;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeLeave;
use App\Models\HrOffense;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class HrPagesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $permissions = [];
        foreach (['employees', 'departments', 'violations', 'claims', 'employee-leave', 'driver-leave', 'conductor-leave'] as $module) {
            foreach (['view', 'create', 'update', 'delete'] as $ability) {
                $permissions[] = Permission::findOrCreate("{$module}.{$ability}", 'web')->name;
            }
        }

        $role = Role::findOrCreate('HR Tester', 'web');
        $role->syncPermissions($permissions);
        $this->user = User::factory()->create(['account_status' => 'active', 'must_change_password' => false, 'full_name' => 'Helen HR']);
        $this->user->assignRole($role);
    }

    public function test_offenses_and_departments(): void
    {
        $this->client()->post(route('violation.offenses.store'), [
            'section' => 'SEC-1', 'offense_description' => 'Late arrival', 'offense_type' => 'A', 'offense_gravity' => 'LIGHT',
        ])->assertSessionHasNoErrors();

        $this->client()->get(route('violation.offenses.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('hr/offenses/index')
                ->where('offenses.data.0.section', 'SEC-1')
                ->where('can.create', true)
                ->has('gravities', 7));

        $this->client()->post(route('employees.departments.store'), ['name' => 'Operations'])->assertSessionHasNoErrors();
        $department = Department::query()->sole();
        $this->client()->post(route('employees.departments.position.store'), ['department_id' => $department->id, 'title' => 'Dispatcher'])->assertSessionHasNoErrors();

        $this->client()->get(route('employees.departments.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('hr/departments/index')
                ->where('departments.0.name', 'Operations')
                ->where('departments.0.positions.0.title', 'Dispatcher'));
    }

    public function test_claims_page_filters_and_crud(): void
    {
        $employee = $this->employee('Ana Cruz');

        $this->client()->post(route('claims.store'), [
            'employee_id' => $employee->id, 'claim_type' => 'MATERNITY', 'status' => 'Ongoing',
            'date_of_notification' => '2026-09-01', 'date_filed' => '2026-09-05', 'amount' => '15000',
        ])->assertRedirect(route('claims.index'))->assertSessionHasNoErrors();

        $claim = Claim::query()->sole();

        $this->client()->get(route('claims.index', ['status' => 'Ongoing', 'date_field' => 'date_filed', 'date_from' => '2026-09-01']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('hr/claims/index')
                ->has('claims.data', 1)
                ->where('claims.data.0.employee', 'Ana Cruz')
                ->where('claims.data.0.date_filed', '2026-09-05')
                ->where('statusCounts.Ongoing', 1)
                ->where('filters.date_from', '2026-09-01'));

        $this->client()->put(route('claims.update', $claim), [
            'employee_id' => $employee->id, 'claim_type' => 'MATERNITY', 'status' => 'Approved', 'date_filed' => '2026-09-05', 'approval_date' => '2026-09-10',
        ])->assertSessionHasNoErrors();
        $this->assertSame('Approved', $claim->refresh()->status);
    }

    public function test_all_three_leave_modules(): void
    {
        $operations = Department::query()->create(['name' => 'Operations']);
        $modules = [
            'employee-leave.employee' => ['kind' => 'employee', 'position' => 'Clerk'],
            'driver-leave.driver' => ['kind' => 'driver', 'position' => 'Driver'],
            'conductor-leave.conductor' => ['kind' => 'conductor', 'position' => 'Conductor'],
        ];

        foreach ($modules as $route => $module) {
            $position = Position::query()->create(['department_id' => $operations->id, 'title' => $module['position']]);
            $employee = $this->employee(ucfirst($module['kind']).' Person', $position);

            $this->client()->get(route("{$route}.create"))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->component('hr/leaves/form')
                    ->where('kind.key', $module['kind'])
                    ->where('employees', fn ($employees) => collect($employees)->contains('value', (string) $employee->id)));

            $this->client()->post(route("{$route}.store"), [
                'employee_id' => $employee->id, 'leave_type' => 'Medical Leave', 'start_date' => now()->subDays(12)->toDateString(), 'end_date' => now()->subDays(10)->toDateString(),
            ])->assertRedirect(route("{$route}.index"));

            $this->client()->get(route("{$route}.index"))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->component('hr/leaves/index')
                    ->where('kind.key', $module['kind'])
                    ->has('leaves.data', 1)
                    ->where('leaves.data.0.after_leave', true)
                    ->where('leaves.data.0.remaining.label', 'Warning for 2nd Notice')
                    ->where('leaves.data.0.employee.name', ucfirst($module['kind']).' Person'));
        }

        $leave = EmployeeLeave::query()->sole();

        $this->client()->get(route('employee-leave.employee.edit', $leave))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('hr/leaves/form')->where('leave.id', $leave->id)->where('values.leave_type', 'Medical Leave'));

        Storage::fake('local');
        $this->client()->post(route('employee-leave.employee.action', $leave), [
            'action_type' => 'first', 'note' => 'Sent by courier', 'proof_image' => UploadedFile::fake()->image('proof.jpg'),
        ])->assertRedirect(route('employee-leave.employee.index'));
        $this->assertNotNull($leave->refresh()->first_notice_sent_at);

        $this->client()->get(route('employee-leave.employee.index'))
            ->assertInertia(fn (Assert $page) => $page->where('leaves.data.0.notices.first.proof_url', fn ($url) => str_contains((string) $url, '/proof/first')));
    }

    public function test_offense_table_filters(): void
    {
        HrOffense::query()->create(['section' => 'SEC-1', 'offense_description' => 'Late arrival', 'offense_type' => 'A', 'offense_gravity' => 'LIGHT']);
        HrOffense::query()->create(['section' => 'SEC-2', 'offense_description' => 'Theft of company property', 'offense_type' => 'D', 'offense_gravity' => 'GRAVE']);

        $this->client()->get(route('violation.offenses.index', ['gravity' => 'GRAVE']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('hr/offenses/index')
                ->where('filters.gravity', 'GRAVE')
                ->has('offenses.data', 1)
                ->where('offenses.data.0.section', 'SEC-2'));

        $this->client()->get(route('violation.offenses.index', ['search' => 'late', 'type' => 'A']))
            ->assertInertia(fn (Assert $page) => $page->has('offenses.data', 1)->where('offenses.data.0.section', 'SEC-1'));

        // Unknown values are ignored instead of emptying the table.
        $this->client()->get(route('violation.offenses.index', ['gravity' => 'bogus', 'type' => 'Z']))
            ->assertInertia(fn (Assert $page) => $page->where('filters.gravity', '')->where('filters.type', '')->has('offenses.data', 2));
    }

    public function test_leave_table_filters_keep_counts_unfiltered(): void
    {
        $clerk = Position::query()->create(['department_id' => Department::query()->create(['name' => 'Admin'])->id, 'title' => 'Clerk']);
        $mirasol = $this->employee('Mira Clerk', $clerk);
        $mirasol->update(['garage' => 'Mirasol']);
        $gonzales = $this->employee('Gonz Clerk', $clerk);
        $gonzales->update(['garage' => 'Gonzales']);

        EmployeeLeave::query()->create(['employee_id' => $mirasol->id, 'leave_type' => 'Medical Leave', 'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(), 'status' => 'Active']);
        EmployeeLeave::query()->create(['employee_id' => $gonzales->id, 'leave_type' => 'Vacation Leave', 'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(), 'status' => 'Cancelled']);

        $this->client()->get(route('employee-leave.employee.index', ['status' => 'active']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('hr/leaves/index')
                ->where('filters.status', 'active')
                ->has('leaves.data', 1)
                ->where('leaves.data.0.employee.name', 'Mira Clerk')
                // The count cards still describe every record.
                ->where('counts.total', 2)
                ->where('counts.cancelled', 1)
                ->has('leaveTypes')
                ->has('garages'));

        $this->client()->get(route('employee-leave.employee.index', ['garage' => 'Gonzales']))
            ->assertInertia(fn (Assert $page) => $page->has('leaves.data', 1)->where('leaves.data.0.employee.name', 'Gonz Clerk'));

        $this->client()->get(route('employee-leave.employee.index', ['leave_type' => 'Medical Leave', 'status' => 'cancelled']))
            ->assertInertia(fn (Assert $page) => $page->has('leaves.data', 0));
    }

    public function test_profile_of_employee_on_leave_can_be_saved(): void
    {
        // LeaveRecordService sets "On Leave"; the profile form used to reject it,
        // so no employee on leave could be edited.
        $employee = $this->employee('Leah Leave');
        $employee->update(['status' => 'On Leave']);

        $this->client()->get(route('employees.staff.show', $employee->id))
            ->assertInertia(fn (Assert $page) => $page
                ->where('profileValues.status', 'On Leave')
                ->where('options.statuses', fn ($statuses) => collect($statuses)->contains('On Leave')));

        $this->client()->put(route('employees.update', $employee->id), [
            'full_name' => 'Leah Leave', 'status' => 'On Leave', 'company' => 'Jell Transport', 'garage' => 'Mirasol', 'address_2' => 'Unit 5',
        ])->assertSessionHasNoErrors()->assertRedirect(route('employees.staff.show', $employee->id));

        $this->assertSame('Unit 5', $employee->refresh()->address_2);
        $this->assertSame('On Leave', $employee->status);
    }

    public function test_employee_list_and_201_profile(): void
    {
        Storage::fake('local');
        $department = Department::query()->create(['name' => 'Admin']);
        $position = Position::query()->create(['department_id' => $department->id, 'title' => 'Clerk']);
        $offense = HrOffense::query()->create(['section' => 'SEC-9', 'offense_description' => 'Absent without leave', 'offense_type' => 'B', 'offense_gravity' => 'GRAVE']);

        $this->client()->post(route('employees.staff.store'), [
            'full_name' => 'Maria Santos', 'department_id' => $department->id, 'position_id' => $position->id,
            'company' => 'Jell Transport', 'garage' => 'Mirasol', 'phone_number' => '09171234567',
        ])->assertRedirect(route('employees.staff.index'));
        $employee = Employee::query()->sole();

        $this->client()->get(route('employees.staff.index', ['search' => 'Maria']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('hr/employees/index')
                ->where('employees.data.0.name', 'Maria Santos')
                ->where('employees.data.0.position', 'Clerk')
                ->where('departments.0.positions.0.title', 'Clerk'));

        $this->client()->post(route('employees.update', $employee), [
            '_method' => 'PUT', 'employee_id_permanent' => '123456', 'full_name' => 'Maria Santos', 'status' => 'Active', 'company' => 'Jell Transport',
            'garage' => 'Gonzales', 'department_id' => $department->id, 'position_id' => $position->id, 'date_hired' => '2024-01-15',
            'remove_profile_picture' => '0', 'profile_picture_cropped' => '',
        ])->assertRedirect(route('employees.staff.show', $employee));
        $this->assertSame('Gonzales', $employee->refresh()->garage);

        $this->client()->post(route('employees.assets.update', $employee), [
            'sss_number' => '34-1234567-8', 'contract' => UploadedFile::fake()->create('contract.pdf', 50, 'application/pdf'),
        ])->assertRedirect(route('employees.staff.show', $employee));
        $asset = $employee->refresh()->asset;
        $this->assertSame('34-1234567-8', $asset->sss_number);
        $this->assertNotNull($asset->contract, 'The 201 contract upload is stored.');
        Storage::disk('local')->assertExists($asset->contract);

        $this->client()->put(route('employees.status-details.update', $employee), ['type_of_status' => 'Resigned', 'last_pay_status' => 'Released'])->assertSessionHasNoErrors();

        $this->client()->post(route('employees.staff.history.store', $employee), [
            'title' => 'Violations', 'ir_number' => 'IR-2026-0001', 'offense_id' => [$offense->id], 'description' => ['Absent without leave'],
            'disciplinary_action' => ['Suspension'], 'suspension_start_date' => '2026-09-01',
        ])->assertSessionHasNoErrors();

        $this->client()->get(route('employees.staff.show', $employee))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('hr/employees/show')
                ->where('urls.show', fn ($url) => str_contains((string) $url, '/employees/employee/'))
                ->where('employee.employee_id_permanent', '123456')
                ->where('employee.qr_svg', fn ($svg) => str_contains((string) $svg, '<svg'))
                ->where('assets.numbers.0.value', '34-1234567-8')
                ->where('assets.files.2.url', fn ($url) => str_contains((string) $url, 'asset-file/contract'))
                ->where('statusDetails.type_of_status', 'Resigned')
                ->where('irGroups.0.ir_number', 'IR-2026-0001')
                ->where('irGroups.0.values.offense_id.0', (string) $offense->id)
                ->where('irStats.suspension', 1)
                ->where('offenses.0.label', 'SEC-9')
                ->where('logs.total', fn ($total) => $total >= 3));
    }

    private function employee(string $name, ?Position $position = null): Employee
    {
        return Employee::query()->create([
            'employee_id' => 'EMP-'.fake()->unique()->numerify('####'),
            'full_name' => $name,
            'company' => 'Jell Transport',
            'garage' => 'Mirasol',
            'status' => 'Active',
            'department_id' => $position?->department_id,
            'position_id' => $position?->id,
        ]);
    }

    private function client(): static
    {
        return $this->actingAs($this->user)
            ->withSession(['_token' => 'hr-test', 'unlocked' => true, 'last_activity_time' => now()->timestamp])
            ->withHeader('X-CSRF-TOKEN', 'hr-test');
    }
}
