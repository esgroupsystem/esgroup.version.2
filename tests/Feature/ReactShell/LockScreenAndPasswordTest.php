<?php

declare(strict_types=1);

namespace Tests\Feature\ReactShell;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

final class LockScreenAndPasswordTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('dashboard.view', 'web');
        Permission::findOrCreate('employees.view', 'web');
        $this->user = User::factory()->create([
            'full_name' => 'Lara Lock',
            'username' => 'lara',
            'password' => Hash::make('jell#2026'),
            'account_status' => 'active',
            'must_change_password' => false,
        ]);
        $this->user->givePermissionTo(['dashboard.view', 'employees.view']);
    }

    public function test_lock_hides_every_page_and_unlock_returns_to_it(): void
    {
        $unlocked = ['_token' => 'lock-test', 'unlocked' => true, 'last_activity_time' => now()->timestamp];

        // "Lock screen" from the user menu while on the employee list.
        $this->actingAs($this->user)->withSession($unlocked)->withHeader('X-CSRF-TOKEN', 'lock-test')
            ->from(route('employees.staff.index'))
            ->post(route('lockscreen.lock'))
            ->assertRedirect(route('lockscreen.show'));

        $this->assertFalse(session('unlocked'));
        $this->assertSame(route('employees.staff.index'), session('lock_intended'));

        // Pages render the lock screen, with no page data.
        $this->get(route('employees.staff.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('auth/lock')
                ->where('user.name', 'Lara Lock')
                ->where('urls.unlock', route('lockscreen.unlock'))
                ->missing('employees'));

        // Writes are refused; JSON calls get 423.
        $this->withHeader('X-CSRF-TOKEN', 'lock-test')
            ->post(route('employees.departments.store'), ['name' => 'Sneaky'])
            ->assertRedirect(route('lockscreen.show'));
        $this->assertDatabaseMissing('departments', ['name' => 'Sneaky']);
        $this->getJson(route('employees.staff.index'))->assertStatus(423);

        // Wrong password: stays locked with an error.
        $this->withHeader('X-CSRF-TOKEN', 'lock-test')
            ->from(route('employees.staff.index'))
            ->post(route('lockscreen.unlock'), ['password' => 'nope'])
            ->assertSessionHasErrors('password');
        $this->assertFalse(session('unlocked'));

        // Right password: back to the employee list.
        $this->withHeader('X-CSRF-TOKEN', 'lock-test')
            ->post(route('lockscreen.unlock'), ['password' => 'jell#2026'])
            ->assertRedirect(route('employees.staff.index'));
        $this->assertTrue(session('unlocked'));
        $this->get(route('employees.staff.index'))->assertInertia(fn (Assert $page) => $page->component('hr/employees/index'));
    }

    public function test_unlock_never_redirects_off_site_and_is_rate_limited(): void
    {
        $session = ['_token' => 'lock-test', 'unlocked' => false, 'lock_intended' => 'https://evil.example.com/phish'];

        $this->actingAs($this->user)->withSession($session)->withHeader('X-CSRF-TOKEN', 'lock-test')
            ->post(route('lockscreen.unlock'), ['password' => 'jell#2026'])
            ->assertRedirect(route('dashboard.index'));

        RateLimiter::clear('unlock:user:'.$this->user->id.':ip:127.0.0.1');
        $this->withSession(['unlocked' => false]);
        foreach (range(1, 5) as $attempt) {
            $this->withHeader('X-CSRF-TOKEN', 'lock-test')->post(route('lockscreen.unlock'), ['password' => 'wrong'.$attempt]);
        }
        $this->withHeader('X-CSRF-TOKEN', 'lock-test')
            ->post(route('lockscreen.unlock'), ['password' => 'jell#2026'])
            ->assertSessionHasErrors('password');
        $this->assertStringStartsWith('Too many unlock attempts.', session('errors')->first('password'));
        $this->assertFalse(session('unlocked'));
    }

    public function test_lockscreen_route_renders_react(): void
    {
        $this->actingAs($this->user)
            ->get(route('lockscreen.show'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('auth/lock'));
        $this->assertFalse(session('unlocked'));

        $this->get(route('login'))->assertInertia(fn (Assert $page) => $page->component('auth/login')->where('seconds', 0));
    }

    public function test_new_password_needs_seven_characters_a_number_and_a_symbol(): void
    {
        $client = fn () => $this->actingAs($this->user)
            ->withSession(['_token' => 'pw-test', 'unlocked' => true, 'last_activity_time' => now()->timestamp])
            ->withHeader('X-CSRF-TOKEN', 'pw-test');

        foreach (['ab#12' => 'too short', 'abcdefg#' => 'no number', 'abcdefg1' => 'no symbol'] as $password => $reason) {
            $client()->post(route('auth.change.password.update'), [
                'current_password' => 'jell#2026',
                'password' => $password,
                'password_confirmation' => $password,
            ])->assertSessionHasErrors('password', "Rejected because: {$reason}");
        }

        // 7 characters with a number and a symbol, no upper case needed.
        $client()->post(route('auth.change.password.update'), [
            'current_password' => 'jell#2026',
            'password' => 'bus#247',
            'password_confirmation' => 'bus#247',
        ])->assertSessionHasNoErrors()->assertRedirect(route('dashboard.index'));

        $this->assertTrue(Hash::check('bus#247', $this->user->refresh()->password));
    }
}
