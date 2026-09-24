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

final class DashboardInertiaTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_react_page_with_shared_shell_data(): void
    {
        $user = $this->makeUser(['dashboard.view', 'payroll.view']);

        $this->asUnlocked($user)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard/index')
                ->has('greeting')
                ->has('today')
                ->where('auth.user.name', 'Juan Dela Cruz')
                ->where('auth.user.must_change_password', false)
                ->where('navigation.0.label', 'General')
                // Dashboard is a single link now, not an expandable parent.
                ->has('navigation.0.items', 1)
                ->where('navigation.0.items.0.title', 'Dashboard')
                ->missing('navigation.0.items.0.items')
                ->where('navigation.0.items.0.inertia', true)
                ->where('navigation.0.items.0.isActive', true)
            );
    }

    public function test_menu_only_contains_permitted_links(): void
    {
        $user = $this->makeUser(['dashboard.view', 'payroll.view', 'mirasol-logs.view', 'users.view', 'dashboard.it']);

        $navigation = $this->asUnlocked($user)
            ->get(route('dashboard.index'))
            ->viewData('page')['props']['navigation'];

        $titles = collect($navigation)
            ->flatMap(fn (array $group): array => $group['items'])
            ->flatMap(fn (array $item): array => $item['items'] ?? [$item])
            ->pluck('title')
            ->all();

        $this->assertContains('Dashboard', $titles);
        $this->assertContains('Payroll', $titles);
        $this->assertContains('Users', $titles);
        $this->assertNotContains('Parts Issuance', $titles);
        $this->assertNotContains('Roles', $titles);
        $this->assertNotContains('Adjustment', $titles);

        $payroll = collect($navigation)->firstWhere('label', 'Payroll');
        $this->assertTrue($payroll['items'][0]['inertia'], 'Migrated pages are client-side visits.');

        $biometrics = collect($navigation)->firstWhere('label', 'Biometrics');
        $this->assertTrue($biometrics['items'][0]['inertia'], 'Biometrics Sync is migrated.');

        $authentication = collect($navigation)->firstWhere('label', 'Security');
        $this->assertTrue($authentication['items'][0]['inertia'], 'Users is migrated.');

        // The old per-department dashboards are no longer in the menu.
        $this->assertNotContains('All Data', $titles);
        $this->assertNotContains('IT Department', $titles);
    }

    public function test_inertia_visit_to_a_blade_page_becomes_a_full_page_load(): void
    {
        // Any plain HTML page (a print view, ...). Send the real asset version so the
        // 409 comes from the Blade conversion, not from a version mismatch.
        \Illuminate\Support\Facades\Route::middleware('web')->get('/_plain-html', fn () => response('<html><body>Print</body></html>'));
        $version = (string) app(\App\Http\Middleware\HandleInertiaRequests::class)->version(request());

        $this->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => $version])
            ->get('/_plain-html')
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', url('/_plain-html'));
    }

    public function test_developer_always_has_every_permission(): void
    {
        $developer = $this->makeUser([]);
        $developer->syncRoles([Role::findOrCreate('Developer', 'web')]);
        // A permission created after the role was synced.
        Permission::findOrCreate('payroll-plotting.view', 'web');

        $this->assertTrue($developer->fresh()->can('payroll-plotting.view'));
        $this->assertTrue($developer->fresh()->can('some-future.permission'));

        $this->asUnlocked($developer->fresh())
            ->get(route('payroll-plotting.index'))
            ->assertOk();

        $navigation = $this->asUnlocked($developer->fresh())
            ->get(route('dashboard.index'))
            ->viewData('page')['props']['navigation'];
        $this->assertContains('Security', array_column($navigation, 'label'));
    }

    public function test_non_developer_still_needs_the_permission(): void
    {
        Permission::findOrCreate('payroll-plotting.view', 'web');
        $user = $this->makeUser(['dashboard.view']);

        $this->assertFalse($user->can('payroll-plotting.view'));
        $this->asUnlocked($user)->get(route('payroll-plotting.index'))->assertForbidden();
    }

    public function test_locked_session_shows_the_lock_screen_instead_of_the_page(): void
    {
        $user = $this->makeUser(['dashboard.view']);

        // No page data at all while locked: the React lock screen is rendered in place.
        $this->actingAs($user)
            ->get(route('dashboard.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('auth/lock')
                ->where('user.name', 'Juan Dela Cruz')
                ->missing('greeting'))
            ->assertSessionHas('lock_intended', route('dashboard.index'));
    }

    private function asUnlocked(User $user): static
    {
        return $this->actingAs($user)->withSession([
            'unlocked' => true,
            'last_activity_time' => now()->timestamp,
        ]);
    }

    /** @param list<string> $permissions */
    private function makeUser(array $permissions): User
    {
        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $user = User::factory()->create([
            'full_name' => 'Juan Dela Cruz',
            'username' => 'juan',
            'email' => 'juan@example.com',
            'password' => Hash::make('Password123!Password'),
            'role' => 'Admin',
            'account_status' => 'active',
            'must_change_password' => false,
        ]);

        $role = Role::findOrCreate('Shell Tester', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        return $user;
    }
}
