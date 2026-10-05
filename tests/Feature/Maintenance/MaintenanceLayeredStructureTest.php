<?php

declare(strict_types=1);

namespace Tests\Feature\Maintenance;

use App\Models\Bus;
use App\Models\BusDetail;
use App\Models\Category;
use App\Models\Location;
use App\Models\PartsOut;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Receiving;
use App\Models\User;
use App\Providers\RepositoryServiceProvider;
use App\Repositories\Contracts\Fleet\BusRepositoryInterface;
use App\Repositories\Contracts\Maintenance\JobOrderMaintenanceRepositoryInterface;
use App\Repositories\Contracts\Maintenance\StockRepositoryInterface;
use App\Repositories\Contracts\Security\RoleRepositoryInterface;
use App\Repositories\Fleet\BusRepository;
use App\Repositories\Maintenance\JobOrderMaintenanceRepository;
use App\Repositories\Maintenance\StockRepository;
use App\Repositories\Security\RoleRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Maintenance, Inventory, Products and Security on the layered structure: repository bindings,
 * the removed dead routes, business refusals as field errors, exports, and the two security fixes
 * (random temporary password, no self-deactivation).
 */
final class MaintenanceLayeredStructureTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Location $main;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $permissions = [];
        foreach ([
            'category' => ['view', 'create', 'update', 'delete'],
            'items' => ['view', 'create', 'update', 'delete'],
            'allbus' => ['view', 'create', 'edit', 'delete'],
            'parts-out' => ['view', 'create', 'rollback'],
            'receivings' => ['view', 'create', 'rollback'],
            'stock-transfers' => ['view', 'create', 'rollback'],
            'job-orders' => ['view', 'create', 'update-status', 'update-number'],
            'users' => ['view', 'create', 'update'],
            'roles' => ['view', 'create', 'update', 'delete'],
        ] as $module => $abilities) {
            foreach ($abilities as $ability) {
                $permissions[] = Permission::findOrCreate("{$module}.{$ability}", 'web')->name;
            }
        }

        Role::findOrCreate('Admin', 'web')->syncPermissions($permissions);
        Role::findOrCreate('Developer', 'web');
        $this->user = $this->makeUser('stockadmin', 'Admin');
        $this->main = Location::query()->where('code', 'MAIN')->sole();
    }

    public function test_new_repositories_are_bound(): void
    {
        $this->assertInstanceOf(StockRepository::class, app(StockRepositoryInterface::class));
        $this->assertInstanceOf(JobOrderMaintenanceRepository::class, app(JobOrderMaintenanceRepositoryInterface::class));
        $this->assertInstanceOf(BusRepository::class, app(BusRepositoryInterface::class));
        $this->assertInstanceOf(RoleRepository::class, app(RoleRepositoryInterface::class));

        foreach ((new RepositoryServiceProvider(app()))->singletons as $interface => $class) {
            $this->assertInstanceOf($class, app($interface));
        }
    }

    public function test_dead_routes_without_a_controller_method_are_gone(): void
    {
        foreach ([
            'authentication.users.create', 'authentication.users.edit', 'allbus.show', 'category.edit', 'items.edit',
            'parts-out.edit', 'parts-out.update', 'parts-out.cancel', 'parts-out.print', 'buses.maintenance-history',
        ] as $name) {
            $this->assertFalse(Route::has($name), "{$name} should be removed");
        }
    }

    public function test_stock_refusals_come_back_as_field_errors_and_change_nothing(): void
    {
        $product = $this->stockedProduct(2);

        $this->client()->from(route('parts-out.create'))->post(route('parts-out.store'), [
            'location_id' => $this->main->id, 'mechanic_name' => 'Ben', 'issued_date' => '2026-10-05',
            'product_id' => [$product->id], 'qty_used' => [5],
        ])->assertRedirect(route('parts-out.create'))->assertSessionHasErrors(['product_id' => 'Insufficient stock for Brake Pad at '.$this->main->name.'. Available: 2, Requested: 5.']);
        $this->assertSame(0, PartsOut::query()->count());

        $other = Location::query()->whereKeyNot($this->main->id)->firstOrFail();
        $this->client()->from(route('stock-transfers.create'))->post(route('stock-transfers.store'), [
            'from_location_id' => $this->main->id, 'to_location_id' => $other->id, 'transfer_date' => '2026-10-05',
            'product_id' => [$product->id], 'qty' => [9],
        ])->assertSessionHasErrors('product_id');
        $this->assertSame(2, $this->qty($product, $this->main));

        // Rolling back more than the stockroom still holds is refused.
        $this->client()->post(route('receivings.store'), [
            'location_id' => $this->main->id, 'delivered_by' => 'Supplier', 'delivery_date' => '2026-10-05',
            'product_id' => [$product->id], 'qty_delivered' => [3],
        ])->assertSessionHasNoErrors();
        $receiving = Receiving::query()->sole();
        $item = $receiving->items()->sole();
        $this->client()->post(route('receivings.rollback', [$receiving->id, $item->id]), ['rollback_qty' => 4])->assertSessionHasErrors('rollback_qty');
        $this->assertSame(5, $this->qty($product, $this->main));
    }

    public function test_receiving_proof_is_private_and_served_inline(): void
    {
        $product = $this->stockedProduct(0);

        $this->client()->post(route('receivings.store'), [
            'location_id' => $this->main->id, 'delivered_by' => 'Supplier', 'delivery_date' => '2026-10-05',
            'product_id' => [$product->id], 'qty_delivered' => [1],
            'proof_image' => UploadedFile::fake()->image('proof.jpg'),
        ])->assertSessionHasNoErrors();
        $receiving = Receiving::query()->sole();
        Storage::disk('local')->assertExists($receiving->proof_image);

        $this->client()->get(route('receivings.show', $receiving->id))
            ->assertInertia(fn (Assert $page) => $page->where('record.proof_url', route('receivings.proof', $receiving)));
        $this->client()->get(route('receivings.proof', $receiving))->assertOk()->assertHeader('Content-Disposition', 'inline; filename="'.basename($receiving->proof_image).'"');
    }

    public function test_products_dashboard_and_delete(): void
    {
        $product = $this->stockedProduct(12);

        $this->client()->get(route('items.dashboard', ['location' => 'needs_transfer']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('dashboards/maintenance-stock/index')
                ->where('totals.main', 12)
                ->where('main.data.0.qty', 12)
                ->where('transfer.data.0.suggestion', 'Available in Main but zero in Balintawak'));
        $this->client()->get(route('items.dashboard', ['location' => 'bogus']))->assertSessionHas('flash_notification');

        $this->client()->delete(route('items.destroy', $product->id))->assertSessionHas('success', 'Item deleted successfully');
        $this->assertModelMissing($product);
        $this->assertSame(0, ProductStock::query()->where('product_id', $product->id)->count());
    }

    public function test_bus_list_delete_and_job_order_exports(): void
    {
        $vehicle = BusDetail::query()->create(['garage' => 'Mirasol', 'name' => 'ES', 'body_number' => 'B-1', 'plate_number' => 'P-1']);
        $this->client()->post(route('allbus.store'), ['garage' => 'Mirasol', 'name' => 'ES', 'body_number' => 'B-1', 'plate_number' => 'P-2'])->assertSessionHasErrors('body_number');
        $this->client()->delete(route('allbus.destroy', $vehicle))->assertRedirect(route('allbus.index'));
        $this->assertModelMissing($vehicle);

        $bus = Bus::query()->create(['bus_no' => '101', 'plate_no' => 'ABC-101', 'company' => 'Jell', 'garage' => 'Mirasol']);
        $this->client()->post(route('maintenance.job-orders.store'), ['bus_id' => $bus->id, 'description_of_work' => 'Change brake pads', 'odometer_reading' => 1000])->assertSessionHasNoErrors();
        $jobOrder = $bus->jobOrderMaintenances()->sole();
        $this->assertMatchesRegularExpression('/^JO-\d{4}-00001$/', $jobOrder->job_order_no);

        $this->client()->get(route('maintenance.job-orders.index'))
            ->assertInertia(fn (Assert $page) => $page->where('statusCards.0.value', 'standby')->where('statusCards.0.count', 1)->missing('statusCards.0.badge_class'));

        $csv = $this->client()->get(route('maintenance.job-orders.export', ['export_type' => 'csv']));
        $csv->assertOk();
        $this->assertStringContainsString($jobOrder->job_order_no, $csv->streamedContent());
        $this->client()->get(route('maintenance.job-orders.export', ['export_type' => 'xls', 'status' => 'standby']))
            ->assertOk()->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')->assertSee('Change brake pads');
        $single = $this->client()->get(route('maintenance.job-orders.export-single', ['jobOrderMaintenance' => $jobOrder, 'export_type' => 'csv']));
        $this->assertStringContainsString('Job order created', $single->streamedContent());
    }

    public function test_temporary_password_is_random_and_works(): void
    {
        $clerk = $this->makeUser('clerk1', 'Admin');

        $this->client()->post(route('authentication.users.reset.password', $clerk->id))->assertRedirect(route('authentication.users.index'));
        $first = (string) session('temporary_password');
        $this->client()->post(route('authentication.users.reset.password', $clerk->id));
        $second = (string) session('temporary_password');

        $this->assertSame(12, strlen($first));
        $this->assertNotSame($first, $second);
        $this->assertTrue(Hash::check($second, (string) $clerk->refresh()->password));
        $this->assertTrue((bool) $clerk->must_change_password);
    }

    public function test_a_user_cannot_deactivate_their_own_account(): void
    {
        $this->client()->post(route('authentication.users.status', $this->user->id))->assertSessionHasErrors('account_status');
        $this->client()->post(route('authentication.users.update', $this->user->id), [
            'full_name' => 'Stock Admin', 'username' => 'stockadmin', 'email' => 'stockadmin@example.com', 'role' => 'Admin', 'account_status' => 'deactivated',
        ])->assertSessionHasErrors('account_status');
        $this->assertSame('active', $this->user->refresh()->account_status);

        // Others can still be deactivated.
        $other = $this->makeUser('other1', 'Admin');
        $this->client()->post(route('authentication.users.status', $other->id))->assertSessionHasNoErrors();
        $this->assertSame('deactivated', $other->refresh()->account_status);
    }

    public function test_roles_page_and_developer_only_changes(): void
    {
        $this->client()->get(route('roles.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('authentication/roles/index')->where('can.create', false)->where('roles', fn ($roles) => ! collect($roles)->contains('name', 'Developer')));
        $this->client()->post(route('roles.store'), ['name' => 'Auditor'])->assertForbidden();

        $developer = $this->makeUser('dev1', 'Developer');
        $this->actingAsUnlocked($developer)->post(route('roles.store'), ['name' => 'Auditor', 'permissions' => ['items.view']])->assertSessionHasNoErrors();
        $auditor = Role::findByName('Auditor', 'web');
        $this->assertTrue($auditor->hasPermissionTo('items.view'));

        $this->actingAsUnlocked($developer)->delete(route('roles.destroy', Role::findByName('Admin', 'web')))->assertSessionHas('error', 'Role is still assigned to users.');
        $this->actingAsUnlocked($developer)->delete(route('roles.destroy', $auditor))->assertSessionHas('success', 'Role deleted successfully.');
        $this->assertModelMissing($auditor);
    }

    private function stockedProduct(int $quantity): Product
    {
        $category = Category::query()->create(['name' => 'Brakes']);
        $product = Product::query()->create(['category_id' => $category->id, 'product_name' => 'Brake Pad']);
        ProductStock::query()->where('product_id', $product->id)->where('location_id', $this->main->id)->update(['qty' => $quantity]);
        $product->update(['stock_qty' => $quantity]);

        return $product;
    }

    private function qty(Product $product, Location $location): int
    {
        return (int) ProductStock::query()->where('product_id', $product->id)->where('location_id', $location->id)->value('qty');
    }

    private function makeUser(string $username, string $role): User
    {
        $user = User::factory()->create([
            'username' => $username,
            'email' => $username.'@example.com',
            'role' => $role,
            'account_status' => 'active',
            'must_change_password' => false,
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function client(): static
    {
        return $this->actingAsUnlocked($this->user);
    }

    private function actingAsUnlocked(User $user): static
    {
        return $this->actingAs($user)
            ->withSession(['_token' => 'maint-test', 'unlocked' => true, 'last_activity_time' => now()->timestamp])
            ->withHeader('X-CSRF-TOKEN', 'maint-test');
    }
}
