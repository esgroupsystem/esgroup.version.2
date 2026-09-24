<?php

declare(strict_types=1);

namespace Tests\Feature\ReactShell;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class AuthenticationPagesTest extends TestCase
{
    use RefreshDatabase;

    private const PERMISSIONS = ['users.view', 'users.create', 'users.update', 'roles.view', 'roles.create', 'roles.update', 'roles.delete', 'payroll.view'];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::PERMISSIONS as $name) {
            Permission::findOrCreate($name, 'web');
        }
        Role::findOrCreate('Developer', 'web');
        Role::findOrCreate('Admin', 'web')->syncPermissions(self::PERMISSIONS);
        Role::findOrCreate('Clerk', 'web')->syncPermissions(['payroll.view']);
    }

    public function test_admin_manages_users_but_never_sees_or_touches_the_developer(): void
    {
        $admin = $this->makeUser('admin1', 'Admin');
        $developer = $this->makeUser('dev1', 'Developer');

        $this->as($admin)->get(route('authentication.users.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('authentication/users/index')
                ->where('users.data', fn ($users) => ! collect($users)->contains('username', 'dev1'))
                ->where('roles', fn ($roles) => ! collect($roles)->contains('Developer'))
                ->where('temporaryPassword', null));

        // Create: the temporary password is shown once, on the very next page.
        $this->as($admin)->post(route('authentication.users.store'), [
            'full_name' => 'Carla Clerk', 'username' => 'carla', 'email' => 'carla@example.com', 'role' => 'Clerk',
        ])->assertRedirect(route('authentication.users.index'));
        $carla = User::query()->where('username', 'carla')->sole();

        $this->as($admin)->get(route('authentication.users.index'))
            ->assertInertia(fn (Assert $page) => $page->where('temporaryPassword.username', 'carla')->where('temporaryPassword.password', 'cc123456'));
        $this->as($admin)->get(route('authentication.users.index'))
            ->assertInertia(fn (Assert $page) => $page->where('temporaryPassword', null));

        $this->as($admin)->post(route('authentication.users.update', $carla->id), [
            'full_name' => 'Carla Clerk', 'username' => 'carla', 'email' => 'carla@example.com', 'role' => 'Admin', 'account_status' => 'active',
        ])->assertSessionHasNoErrors();
        $this->assertTrue($carla->refresh()->hasRole('Admin'));

        // Status toggle is POST only now.
        $this->as($admin)->get('/authentication/users/status/'.$carla->id)->assertStatus(405);
        $this->as($admin)->post(route('authentication.users.status', $carla->id))->assertRedirect(route('authentication.users.index'));
        $this->assertSame('deactivated', $carla->refresh()->account_status);

        $this->as($admin)->post(route('authentication.users.reset.password', $carla->id))->assertRedirect(route('authentication.users.index'));
        $this->assertTrue((bool) $carla->refresh()->must_change_password);

        // The Developer account stays out of reach.
        $this->as($admin)->post(route('authentication.users.status', $developer->id))->assertForbidden();
        $this->as($admin)->post(route('authentication.users.reset.password', $developer->id))->assertForbidden();
        $this->assertSame('active', $developer->refresh()->account_status);
    }

    public function test_roles_are_view_only_for_non_developers(): void
    {
        $admin = $this->makeUser('admin2', 'Admin');

        $this->as($admin)->get(route('roles.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('authentication/roles/index')
                ->where('roles', fn ($roles) => ! collect($roles)->contains('name', 'Developer'))
                ->where('can.create', false)
                ->where('can.update', false)
                ->where('can.delete', false));

        $this->as($admin)->post(route('roles.store'), ['name' => 'Sneaky', 'permissions' => ['users.update']])->assertForbidden();
        $this->assertDatabaseMissing('roles', ['name' => 'Sneaky']);
    }

    public function test_developer_manages_roles(): void
    {
        $developer = $this->makeUser('dev2', 'Developer');

        $this->as($developer)->get(route('roles.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('can.create', true)
                ->where('roles', fn ($roles) => collect($roles)->contains('name', 'Developer'))
                ->where('permissionGroups', fn ($groups) => collect($groups)->contains('module', 'Users')));

        $this->as($developer)->post(route('roles.store'), ['name' => 'Auditor', 'permissions' => ['payroll.view', 'users.view']])->assertSessionHasNoErrors();
        $auditor = Role::findByName('Auditor', 'web');
        $this->assertSame(['payroll.view', 'users.view'], $auditor->permissions->pluck('name')->sort()->values()->all());

        // A duplicate name now returns a field error instead of a generic failure.
        $this->as($developer)->post(route('roles.store'), ['name' => 'Auditor'])->assertSessionHasErrors('name');

        $this->as($developer)->put(route('roles.update', $auditor->id), ['name' => 'Payroll Auditor', 'permissions' => ['payroll.view']])->assertSessionHasNoErrors();
        $this->assertSame(['payroll.view'], $auditor->refresh()->permissions->pluck('name')->all());

        $this->as($developer)->delete(route('roles.destroy', Role::findByName('Developer', 'web')->id))->assertSessionHas('error');
        $this->as($developer)->delete(route('roles.destroy', $auditor->id))->assertSessionHas('success');
        $this->assertDatabaseMissing('roles', ['name' => 'Payroll Auditor']);
    }

    public function test_developer_syncs_route_permissions_and_drops_stale_ones_from_roles(): void
    {
        $developer = $this->makeUser('dev3', 'Developer');
        Permission::findOrCreate('stale.thing', 'web');
        Role::findByName('Clerk', 'web')->givePermissionTo('stale.thing');

        $this->as($developer)->post(route('roles.sync-permissions'))->assertSessionHas('success');

        $this->assertDatabaseMissing('permissions', ['name' => 'stale.thing']);
        $this->assertDatabaseHas('permissions', ['name' => 'users.view']);
        $this->assertTrue(Role::findByName('Clerk', 'web')->hasPermissionTo('payroll.view'));
    }

    private function makeUser(string $username, string $role): User
    {
        $user = User::factory()->create([
            'username' => $username,
            'email' => $username.'@example.com',
            'password' => Hash::make('Password123!Password'),
            'role' => $role,
            'account_status' => 'active',
            'must_change_password' => false,
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function as(User $user): static
    {
        return $this->actingAs($user)
            ->withSession(['_token' => 'auth-test', 'unlocked' => true, 'last_activity_time' => now()->timestamp])
            ->withHeader('X-CSRF-TOKEN', 'auth-test');
    }
}
