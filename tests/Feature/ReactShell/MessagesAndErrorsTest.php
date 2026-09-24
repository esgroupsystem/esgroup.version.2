<?php

declare(strict_types=1);

namespace Tests\Feature\ReactShell;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Bus;
use App\Models\BusForSaleRecord;
use App\Models\User;
use App\Support\HttpStatusMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class MessagesAndErrorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_listed_status_renders_the_react_error_page(): void
    {
        config(['app.debug' => false]);
        \Illuminate\Support\Facades\Route::middleware('web')->get('/_status/{code}', fn (string $code) => abort((int) $code));
        $this->assertDirectoryDoesNotExist(resource_path('views/errors'));

        foreach (array_keys(HttpStatusMessage::MESSAGES) as $code) {
            $status = HttpStatusMessage::for($code);

            $this->get("/_status/{$code}")
                ->assertStatus($code)
                ->assertInertia(fn (Assert $page) => $page
                    ->component('errors/show')
                    ->where('status.code', $code)
                    ->where('status.title', $status['title'])
                    ->where('primary.href', fn ($href) => is_string($href) && $href !== ''));
        }

        // A deliberate abort message is shown; framework messages never are.
        \Illuminate\Support\Facades\Route::middleware('web')->get('/_forbidden', fn () => abort(403, 'Payroll is locked for this cutoff.'));
        $this->get('/_forbidden')->assertInertia(fn (Assert $page) => $page->where('detail', 'Payroll is locked for this cutoff.'));
    }

    public function test_missing_page_renders_the_new_404_page(): void
    {
        $this->asUnlocked($this->makeUser([]))
            ->get('/this-page-does-not-exist')
            ->assertNotFound()
            ->assertSee('Not Found')
            ->assertSee('The page or record you are looking for does not exist.')
            ->assertDontSee('No query results', false);
    }

    public function test_inertia_errors_keep_their_status_so_the_client_can_toast_them(): void
    {
        $user = $this->makeUser(['dashboard.view']);
        $version = (string) app(HandleInertiaRequests::class)->version(request());

        // Forbidden React page: a 403, not a 409 full-page reload of the same URL.
        $this->asUnlocked($user)
            ->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => $version])
            ->get(route('fleet.for-sale-units.index'))
            ->assertForbidden();
    }

    public function test_login_errors_are_toasts_not_inline_alerts(): void
    {
        // Real flow: a failed security check redirects back with an error.
        \Illuminate\Support\Facades\Http::fake(['challenges.cloudflare.com/*' => \Illuminate\Support\Facades\Http::response(['success' => false])]);

        $this->from(route('login'))
            ->followingRedirects()
            ->post(route('login.post'), ['username' => 'nobody', 'password' => 'wrong-password', 'cf-turnstile-response' => 'x'])
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('auth/login')
                // Validation errors become toasts on the client (lib/notify.ts).
                ->where('errors.turnstile', 'Security verification failed. Please try again.')
                ->where('old.username', 'nobody')
                ->where('old.remember', false)
                ->has('turnstileSiteKey'));

        $this->assertDirectoryDoesNotExist(resource_path('views/landing'));
    }

    public function test_change_password_page_is_react(): void
    {
        $this->asUnlocked($this->makeUser([]))
            ->get(route('auth.change.password.form'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('auth/change-password')
                ->where('requiresCurrent', true));
    }

    public function test_for_sale_units_are_react_and_keep_their_crud(): void
    {
        $user = $this->makeUser(['fleet.view', 'fleet.manage.view', 'fleet.manage.create', 'fleet.manage.edit', 'fleet.manage.update', 'fleet.manage.delete']);
        $bus = Bus::query()->create(['bus_no' => 'FS-1', 'plate_no' => 'FSP-1', 'company' => 'ES TRANSPORT', 'garage' => 'MIRASOL']);

        $this->asUnlocked($user)->get(route('fleet.for-sale-units.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboards/fleet/for-sale-form')
                ->where('record', null)
                ->where('buses.0.bus_no', 'FS-1'));

        $this->asUnlocked($user)->post(route('fleet.for-sale-units.store'), [
            'bus_id' => $bus->id, 'bus_no' => 'FS-1', 'plate_no' => 'FSP-1', 'company' => 'ES TRANSPORT', 'garage' => 'MIRASOL',
            'status' => Bus::STATUS_MECHANICAL_BREAKDOWN, 'breakdown_start_date' => now()->subDays(3)->toDateString(),
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $record = BusForSaleRecord::query()->sole();
        $this->asUnlocked($user)->get(route('fleet.for-sale-units.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboards/fleet/for-sale-index')
                ->where('records.data.0.bus_no', 'FS-1')
                ->where('records.data.0.days', 3)
                ->where('summary.mechanical_breakdown', 1)
                ->where('can.delete', true));

        $this->asUnlocked($user)->get(route('fleet.for-sale-units.edit', $record))
            ->assertInertia(fn (Assert $page) => $page->component('dashboards/fleet/for-sale-form')->where('record.bus_id', (string) $bus->id));

        $this->asUnlocked($user)->put(route('fleet.for-sale-units.update', $record), [
            'bus_id' => $bus->id, 'bus_no' => 'FS-1', 'status' => Bus::STATUS_ACTIVE, 'remarks' => 'Ready for viewing',
        ])->assertSessionHasNoErrors();
        $this->assertSame('Ready for viewing', $record->refresh()->remarks);

        $this->asUnlocked($user)->delete(route('fleet.for-sale-units.destroy', $record))->assertSessionHas('success');
        $this->assertDatabaseMissing('bus_for_sale_records', ['id' => $record->id]);
    }

    public function test_deleted_legacy_modules_are_gone(): void
    {
        $this->asUnlocked($this->makeUser([]))->get('/purchase/index')->assertNotFound();
        $this->asUnlocked($this->makeUser([], 'other'))->get('/request/index')->assertNotFound();
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('received.index'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('dashboard.analytics'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('tickets.cctv.index'));
    }

    private function asUnlocked(User $user): static
    {
        return $this->actingAs($user)->withSession([
            'unlocked' => true,
            'last_activity_time' => now()->timestamp,
        ]);
    }

    /** @param list<string> $permissions */
    private function makeUser(array $permissions, string $username = 'toaster'): User
    {
        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $user = User::factory()->create([
            'username' => $username,
            'email' => $username.'@example.com',
            'password' => Hash::make('Password123!Password'),
            'account_status' => 'active',
            'must_change_password' => false,
        ]);

        $role = Role::findOrCreate('Toast Tester '.$username, 'web');
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        return $user;
    }
}
