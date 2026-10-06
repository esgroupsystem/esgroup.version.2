<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * The Developer role: every permission (old and new), hidden from Roles and Users,
 * never assignable from the app, given only with `php artisan security:make-developer`.
 */
final class DeveloperRoleTest extends TestCase
{
    use RefreshDatabase;

    private const PERMISSIONS = ['users.view', 'users.create', 'users.update', 'roles.view', 'roles.create', 'roles.update', 'roles.delete'];

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('Developer', 'web');
        foreach (self::PERMISSIONS as $name) {
            Permission::findOrCreate($name, 'web');
        }
        Role::findOrCreate('Clerk', 'web')->syncPermissions(['users.view']);
    }

    public function test_every_new_permission_goes_to_the_developer_role_and_developers_pass_every_check(): void
    {
        $developer = $this->makeUser('dev', 'Developer');
        $role = Role::findByName('Developer', 'web');

        // Created after the role exists (route sync, migration, seeder): added automatically.
        $this->assertTrue($role->hasPermissionTo('users.view'));
        Permission::findOrCreate('brand-new.module', 'web');
        $this->assertTrue($role->refresh()->hasPermissionTo('brand-new.module'));

        // Even a permission missing from the stored role still passes (Gate::before).
        $role->revokePermissionTo('brand-new.module');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->assertTrue($developer->fresh()->can('brand-new.module'));
        $this->assertTrue($developer->fresh()->can('permission.that.does.not.exist'));
        $this->assertFalse($this->makeUser('clerk', 'Clerk')->can('brand-new.module'));

        // Sync permissions refills the role to 100%.
        $this->as($developer)->post(route('roles.sync-permissions'))->assertSessionHas('success');
        $this->assertSame(Permission::query()->count(), $role->refresh()->permissions()->count());
    }

    public function test_the_developer_role_is_hidden_locked_and_cannot_be_copied(): void
    {
        $developer = $this->makeUser('dev', 'Developer');
        $role = Role::findByName('Developer', 'web');
        $before = $role->permissions()->count();

        $this->as($developer)->get(route('roles.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('roles', fn ($roles) => collect($roles)->pluck('name')->all() === ['Clerk'])
            ->where('stats.roles', 1));

        // Editing it is refused and changes nothing (the name is reserved, and any other change is refused too).
        $this->as($developer)->put(route('roles.update', $role->id), ['name' => 'Developer', 'permissions' => []])->assertSessionHasErrors('name');
        $this->as($developer)->put(route('roles.update', $role->id), ['name' => 'Dev Team', 'permissions' => []])->assertSessionHas('error');
        $this->assertSame('Developer', $role->refresh()->name);
        $this->assertSame($before, $role->refresh()->permissions()->count());

        // No second role may use the name, in any letter case.
        $this->as($developer)->post(route('roles.store'), ['name' => 'developer', 'permissions' => ['users.view']])->assertSessionHasErrors('name');
        $this->as($developer)->put(route('roles.update', Role::findByName('Clerk', 'web')->id), ['name' => ' DEVELOPER ', 'permissions' => []])->assertSessionHasErrors('name');
        $this->assertSame(1, Role::query()->whereRaw('LOWER(name) = ?', ['developer'])->count());
    }

    public function test_the_users_page_never_offers_or_assigns_the_developer_role(): void
    {
        $developer = $this->makeUser('dev', 'Developer');
        $otherDeveloper = $this->makeUser('dev2', 'Developer');
        $clerk = $this->makeUser('clerk', 'Clerk');

        $this->as($developer)->get(route('authentication.users.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('roles', ['Clerk']));

        // Creating a Developer from the app is refused, even by a Developer.
        $this->as($developer)->post(route('authentication.users.store'), [
            'full_name' => 'New Dev', 'username' => 'newdev', 'email' => 'newdev@example.com', 'role' => 'Developer',
        ])->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['username' => 'newdev']);

        // Promoting an existing user is refused.
        $this->as($developer)->post(route('authentication.users.update', $clerk->id), [
            'full_name' => 'Clerk', 'username' => 'clerk', 'email' => 'clerk@example.com', 'role' => 'Developer', 'account_status' => 'active',
        ])->assertSessionHasErrors('role');
        $this->assertFalse($clerk->refresh()->isDeveloper());

        // A Developer account keeps its role: saving its profile works, changing the role does not.
        $this->as($developer)->post(route('authentication.users.update', $otherDeveloper->id), [
            'full_name' => 'Second Dev', 'username' => 'dev2', 'email' => 'dev2@example.com', 'role' => 'Developer', 'account_status' => 'active',
        ])->assertSessionHasNoErrors();
        $this->assertSame('Second Dev', $otherDeveloper->refresh()->full_name);
        $this->as($developer)->post(route('authentication.users.update', $otherDeveloper->id), [
            'full_name' => 'Second Dev', 'username' => 'dev2', 'email' => 'dev2@example.com', 'role' => 'Clerk', 'account_status' => 'active',
        ])->assertSessionHasErrors('role');
        $this->assertTrue($otherDeveloper->refresh()->isDeveloper());
    }

    public function test_the_command_line_is_the_only_way_to_make_a_developer(): void
    {
        $clerk = $this->makeUser('clerk', 'Clerk');

        $this->artisan('security:make-developer', ['username' => 'clerk'])
            ->expectsConfirmation('Give "clerk" the Developer role? Developers have every permission.', 'yes')
            ->expectsOutput('clerk is now a Developer.')
            ->assertSuccessful();
        $this->assertTrue($clerk->refresh()->isDeveloper());
        $this->assertSame('Developer', $clerk->role);

        $this->artisan('security:make-developer', ['username' => 'nobody'])
            ->expectsConfirmation('Give "nobody" the Developer role? Developers have every permission.', 'yes')
            ->assertFailed();

        $other = $this->makeUser('other', 'Clerk');
        $this->artisan('security:make-developer', ['username' => 'other'])
            ->expectsConfirmation('Give "other" the Developer role? Developers have every permission.', 'no')
            ->assertSuccessful();
        $this->assertFalse($other->refresh()->isDeveloper());
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
            ->withSession(['_token' => 'dev-test', 'unlocked' => true, 'last_activity_time' => now()->timestamp])
            ->withHeader('X-CSRF-TOKEN', 'dev-test');
    }
}
