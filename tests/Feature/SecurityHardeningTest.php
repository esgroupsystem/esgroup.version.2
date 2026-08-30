<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_endpoint_is_removed(): void
    {
        $response = $this->post('/register', [
            'full_name' => 'Attacker',
            'username' => 'attacker',
            'email' => 'attacker@example.com',
            'password' => 'Password123!Password',
            'password_confirmation' => 'Password123!Password',
        ]);

        $response->assertNotFound();
    }

    public function test_non_developer_cannot_assign_developer_role(): void
    {
        $this->seedRoles();
        $admin = $this->makeUser('admin', ['users.create']);
        $this->actingAs($admin);

        $response = $this->asSecurityTestClient()->post(route('authentication.users.store'), [
            'full_name' => 'Escalation Attempt',
            'username' => 'escalation',
            'email' => 'escalation@example.com',
            'role' => 'Developer',
            'location_id' => null,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', ['username' => 'escalation']);
    }

    public function test_non_developer_cannot_modify_role_definitions(): void
    {
        $this->seedRoles();
        $admin = $this->makeUser('roleadmin', ['roles.create']);
        $this->actingAs($admin)->withSession(['unlocked' => true]);

        $response = $this->asSecurityTestClient()->post(route('roles.store'), [
            'name' => 'Escalated Role',
            'permissions' => ['payroll.view'],
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('roles', ['name' => 'Escalated Role']);
    }

    public function test_login_throttles_same_username(): void
    {
        $this->seedRoles();
        $this->makeUser('normal', ['users.view'], 'normal@example.com', 'StrongPassword123!');
        RateLimiter::clear('login:user:'.sha1('normal'));
        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => true], 200),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->asSecurityTestClient()->post(route('login.post'), [
                'username' => 'normal',
                'password' => 'wrong-password',
                'cf-turnstile-response' => 'test-token',
            ]);
        }

        $response = $this->asSecurityTestClient()->post(route('login.post'), [
            'username' => 'normal',
            'password' => 'wrong-password',
            'cf-turnstile-response' => 'test-token',
        ]);

        $response->assertSessionHas('throttle');
        $this->assertTrue(RateLimiter::tooManyAttempts('login:user:'.sha1('normal'), 5));
    }

    public function test_change_password_requires_current_password_after_initial_forced_change(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('normal', ['users.view'], 'normal@example.com', 'OldPassword123!');
        $user->forceFill(['must_change_password' => false])->save();
        $this->actingAs($user);

        $response = $this->asSecurityTestClient()->post(route('auth.change.password.update'), [
            'password' => 'NewSecurePassword123!',
            'password_confirmation' => 'NewSecurePassword123!',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('OldPassword123!', $user->refresh()->password));
    }

    public function test_employee_attachment_is_stored_on_private_disk_and_download_is_authorized(): void
    {
        $this->seedRoles();
        $user = $this->makeUser('hr', ['employees.view', 'employees.update']);
        $employee = Employee::create([
            'employee_id' => 'TEST-'.fake()->unique()->numerify('######'),
            'full_name' => 'Security Test Employee',
            'status' => 'Active',
        ]);
        $this->actingAs($user)->withSession(['unlocked' => true]);

        Storage::fake('local');

        $file = \Illuminate\Http\UploadedFile::fake()->create('identity.pdf', 100, 'application/pdf');

        $this->asSecurityTestClient()->post(route('employees.staff.attachments.store', $employee), [
            'attachment' => $file,
        ])->assertRedirect();

        $attachment = $employee->attachments()->latest('id')->firstOrFail();
        Storage::disk('local')->assertExists($attachment->file_path);
        $this->assertStringNotContainsString('/storage/', $attachment->file_path);

        $download = $this->get(route('employees.staff.attachments.download', [
            'employee' => $employee,
            'attachment' => $attachment->id,
        ]));
        $download->assertOk();

        $viewerWithoutEmployeePermission = $this->makeUser('limited', [], 'limited@example.com');
        $this->actingAs($viewerWithoutEmployeePermission)->withSession(['unlocked' => true]);
        $this->get(route('employees.staff.attachments.download', [
            'employee' => $employee,
            'attachment' => $attachment->id,
        ]))->assertForbidden();
    }

    private function asSecurityTestClient(): static
    {
        return $this
            ->withSession([
                '_token' => 'security-test-csrf',
                'unlocked' => true,
                'last_activity_time' => now()->timestamp,
            ])
            ->withHeader('X-CSRF-TOKEN', 'security-test-csrf');
    }

    private function seedRoles(): void
    {
        $permissions = [
            'users.create',
            'users.view',
            'employees.view',
            'employees.update',
            'roles.create',
            'payroll.view',
        ];

        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        Role::findOrCreate('Admin', 'web')->syncPermissions($permissions);
        Role::findOrCreate('Developer', 'web')->syncPermissions(Permission::all());
    }

    /** @param array<int, string> $permissions */
    private function makeUser(
        string $username,
        array $permissions,
        string $email = 'user@example.com',
        string $password = 'Password123!Password'
    ): User {
        $user = User::factory()->create([
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'Admin',
            'account_status' => 'active',
            'must_change_password' => false,
        ]);

        $role = Role::findOrCreate('Admin', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        return $user;
    }
}
