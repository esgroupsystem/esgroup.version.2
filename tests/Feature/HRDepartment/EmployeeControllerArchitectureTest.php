<?php

declare(strict_types=1);

namespace Tests\Feature\HRDepartment;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class EmployeeControllerArchitectureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_guest_cannot_create_employee(): void
    {
        $this->post(route('employees.staff.store'), $this->validEmployeePayload())
            ->assertRedirect(route('login'));
    }

    public function test_user_without_create_permission_cannot_create_employee(): void
    {
        $this->seedEmployeePermissions();
        $user = $this->makeUser([]);
        $this->actingAs($user)
            ->withSession(['unlocked' => true])
            ->post(route('employees.staff.store'), $this->validEmployeePayload())
            ->assertForbidden();

        $this->assertDatabaseMissing('employees', ['full_name' => 'Architecture Test Employee']);
    }

    public function test_authorized_user_can_create_employee_through_form_request_and_service(): void
    {
        $this->seedEmployeePermissions();
        $user = $this->makeUser(['employees.create']);

        $response = $this->actingAs($user)
            ->withSession(['unlocked' => true])
            ->post(route('employees.staff.store'), $this->validEmployeePayload());

        $response->assertRedirect(route('employees.staff.index'));
        $this->assertDatabaseHas('employees', [
            'full_name' => 'Architecture Test Employee',
            'company' => 'ES Transport',
            'garage' => 'Mirasol',
        ]);
        $this->assertDatabaseHas('employee_logs', [
            'action' => 'created',
            'user_id' => $user->id,
        ]);
    }

    public function test_invalid_employee_status_is_rejected_on_update(): void
    {
        $this->seedEmployeePermissions();
        $user = $this->makeUser(['employees.update']);
        $employee = \App\Models\Employee::query()->create([
            'employee_id' => 'EMP-TEST-001',
            'full_name' => 'Existing Employee',
            'company' => 'ES Transport',
            'garage' => 'Mirasol',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($user)
            ->withSession(['unlocked' => true])
            ->put(route('employees.update', $employee), [
                'full_name' => 'Existing Employee',
                'status' => 'Made Up Status',
                'company' => 'ES Transport',
                'garage' => 'Mirasol',
            ]);

        $response->assertSessionHasErrors('status');
        $this->assertSame('Active', $employee->refresh()->status);
    }

    /** @return array<string, string> */
    private function validEmployeePayload(): array
    {
        return [
            'full_name' => 'Architecture Test Employee',
            'company' => 'ES Transport',
            'garage' => 'Mirasol',
        ];
    }

    private function seedEmployeePermissions(): void
    {
        foreach (['employees.create', 'employees.update'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        Role::findOrCreate('Admin', 'web');
    }

    /** @param list<string> $permissions */
    private function makeUser(array $permissions): User
    {
        $role = Role::findOrCreate('Admin', 'web');
        $role->syncPermissions($permissions);

        $user = User::factory()->create([
            'username' => 'employee-architecture-'.fake()->unique()->numerify('######'),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('Password123!Password'),
            'role' => 'Admin',
            'account_status' => 'active',
            'must_change_password' => false,
        ]);
        $user->assignRole($role);

        return $user;
    }
}
