<?php

declare(strict_types=1);

namespace Tests\Feature\Maintenance;

use App\Models\Bus;
use App\Models\BusDetail;
use App\Models\Category;
use App\Models\JobOrderMaintenance;
use App\Models\Location;
use App\Models\PartsOut;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Receiving;
use App\Models\StockTransfer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class StockPagesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Location $main;

    private Location $balintawak;

    protected function setUp(): void
    {
        parent::setUp();

        $permissions = [];
        foreach ([
            'category' => ['view', 'create', 'update', 'delete'],
            'items' => ['view', 'create', 'update', 'delete'],
            'allbus' => ['view', 'create', 'edit', 'delete'],
            'buses' => ['view'],
            'parts-out' => ['view', 'create', 'rollback'],
            'receivings' => ['view', 'create', 'rollback'],
            'stock-transfers' => ['view', 'create', 'rollback'],
            'job-orders' => ['view', 'create', 'update-status', 'update-number'],
        ] as $module => $abilities) {
            foreach ($abilities as $ability) {
                $permissions[] = Permission::findOrCreate("{$module}.{$ability}", 'web')->name;
            }
        }

        $role = Role::findOrCreate('Stock Tester', 'web');
        $role->syncPermissions($permissions);
        $this->user = User::factory()->create(['account_status' => 'active', 'must_change_password' => false]);
        $this->user->assignRole($role);

        // The locations migration seeds both garages.
        $this->main = Location::query()->where('code', 'MAIN')->sole();
        $this->balintawak = Location::query()->where('code', 'BALINTAWAK')->sole();
    }

    public function test_categories_and_products(): void
    {
        $this->client()->post(route('category.store'), ['name' => 'Brakes'])->assertSessionHasNoErrors();
        $category = Category::query()->sole();
        $this->client()->post(route('category.update', $category->id), ['name' => 'Brake Parts'])->assertSessionHasNoErrors();
        $this->assertSame('Brake Parts', $category->refresh()->name);

        $this->client()->post(route('items.store'), ['category_id' => $category->id, 'product_name' => 'Brake Lining', 'unit' => 'set', 'part_number' => 'BL-1'])->assertSessionHasNoErrors();

        $this->client()->get(route('category.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('products/categories/index')->where('categories.0.name', 'Brake Parts')->where('categories.0.products_count', 1));

        $this->client()->get(route('items.index', ['search' => 'Lining']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('products/items/index')
                ->where('items.data.0.product_name', 'Brake Lining')
                ->where('items.data.0.category', 'Brake Parts')
                ->where('stock.data.0.stock_qty', 0));

        // The delete route is now a real DELETE instead of a GET link.
        $this->client()->get('/category/status/'.$category->id)->assertStatus(405);
        $this->client()->delete(route('category.destroy', $category->id))->assertSessionHasNoErrors();
        $this->assertSame(0, Category::query()->count());
    }

    public function test_bus_list_and_vehicle_history(): void
    {
        $this->client()->post(route('allbus.store'), ['garage' => 'Mirasol', 'name' => 'ES Transport', 'body_number' => 'B-100', 'plate_number' => 'ABC-100'])->assertRedirect(route('allbus.index'));
        $bus = BusDetail::query()->sole();

        $this->client()->get(route('allbus.index', ['search' => 'B-100']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('fleet/all-bus/index')->where('buses.data.0.plate_number', 'ABC-100'));
        $this->client()->get(route('allbus.edit', $bus))
            ->assertInertia(fn (Assert $page) => $page->component('fleet/all-bus/form')->where('values.body_number', 'B-100')->where('garages.0', 'Mirasol'));
        $this->client()->put(route('allbus.update', $bus), ['garage' => 'Gonzales', 'name' => 'ES Transport', 'body_number' => 'B-100', 'plate_number' => 'ABC-100'])->assertSessionHasNoErrors();

        $this->client()->get(route('buses.index'))->assertOk()->assertInertia(fn (Assert $page) => $page->component('fleet/vehicle-history/index')->where('buses.data.0.garage', 'Gonzales'));
        $this->client()->get(route('buses.show', $bus))->assertOk()->assertInertia(fn (Assert $page) => $page->component('fleet/vehicle-history/show')->where('summary.transactions', 0));
    }

    public function test_receiving_parts_out_and_transfer_move_stock(): void
    {
        Storage::fake('local');
        $category = Category::query()->create(['name' => 'Filters']);
        $product = Product::query()->create(['category_id' => $category->id, 'product_name' => 'Oil Filter', 'unit' => 'pc']);
        $bus = BusDetail::query()->create(['garage' => 'Mirasol', 'name' => 'Unit', 'body_number' => 'B-7', 'plate_number' => 'XYZ-7']);
        $stock = fn (Location $location): int => (int) ProductStock::query()->where('product_id', $product->id)->where('location_id', $location->id)->value('qty');

        // Receiving adds 10 to Main.
        $this->client()->get(route('receivings.create'))->assertInertia(fn (Assert $page) => $page->component('inventory/receivings/create')->has('locations', 2));
        $this->client()->post(route('receivings.store'), [
            'location_id' => $this->main->id, 'delivered_by' => 'Supplier Co', 'delivery_date' => '2026-09-20',
            'product_id' => [$product->id], 'qty_delivered' => [10], 'proof_image' => UploadedFile::fake()->image('dr.jpg'),
        ])->assertRedirect(route('receivings.index'))->assertSessionHasNoErrors();
        $this->assertSame(10, $stock($this->main));
        $receiving = Receiving::query()->sole();

        $this->client()->getJson(route('receivings.search-products', ['search' => 'Oil']))->assertJsonPath('0.name', 'Oil Filter');
        $this->client()->get(route('receivings.show', $receiving->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('inventory/receivings/show')
                ->where('record.items.0.delivered', 10)
                ->where('record.items.0.rollback_limit', 10)
                ->where('record.proof_url', fn ($url) => str_contains((string) $url, '/proof')));

        // Parts out uses 3 from Main.
        $this->client()->getJson(route('parts-out.search-products', ['search' => 'Oil', 'location_id' => $this->main->id]))->assertJsonPath('0.stock', 10);
        $this->client()->post(route('parts-out.store'), [
            'vehicle_id' => $bus->id, 'location_id' => $this->main->id, 'mechanic_name' => 'Mang Jose', 'issued_date' => '2026-09-21',
            'product_id' => [$product->id], 'qty_used' => [3], 'item_remarks' => ['Replaced'],
        ])->assertSessionHasNoErrors();
        $this->assertSame(7, $stock($this->main));
        $partsOut = PartsOut::query()->sole();

        $this->client()->get(route('parts-out.index'))->assertInertia(fn (Assert $page) => $page->component('inventory/parts-out/index')->where('records.data.0.items_count', 1)->where('records.data.0.status.key', 'posted'));
        $this->client()->get(route('parts-out.show', $partsOut))->assertInertia(fn (Assert $page) => $page
            ->component('inventory/parts-out/show')
            ->where('record.items.0.stock_before', 10)
            ->where('record.items.0.stock_after', 7)
            ->where('can.rollback', true));
        $this->client()->get(route('buses.show', $bus))->assertInertia(fn (Assert $page) => $page->where('summary.parts_used', 3)->where('records.data.0.items.0.name', 'Oil Filter'));

        // Transfer 4 from Main to Balintawak, then roll it back.
        $this->client()->getJson(route('stock-transfers.search-products', ['q' => 'Oil', 'from_location_id' => $this->main->id]))->assertJsonPath('0.stock', 7);
        $this->client()->post(route('stock-transfers.store'), [
            'from_location_id' => $this->main->id, 'to_location_id' => $this->balintawak->id, 'transfer_date' => '2026-09-22',
            'product_id' => [$product->id], 'qty' => [4],
        ])->assertSessionHasNoErrors();
        $this->assertSame([3, 4], [$stock($this->main), $stock($this->balintawak)]);
        $transfer = StockTransfer::query()->sole();

        $this->client()->get(route('stock-transfers.index'))->assertInertia(fn (Assert $page) => $page->component('inventory/stock-transfers/index')->where('records.data.0.items_count', 1));
        $this->client()->get(route('stock-transfers.show', $transfer))->assertInertia(fn (Assert $page) => $page
            ->component('inventory/stock-transfers/show')
            ->where('record.items.0.category', 'Filters')
            ->where('can.rollback', true));

        $this->client()->post(route('stock-transfers.rollback', $transfer), ['rollback_reason' => 'Wrong garage'])->assertSessionHasNoErrors();
        $this->assertSame([7, 0], [$stock($this->main), $stock($this->balintawak)]);

        $this->client()->patch(route('parts-out.rollback', $partsOut), ['rollback_reason' => 'Encoding error'])
            ->assertRedirect(route('parts-out.index'))
            ->assertSessionHasNoErrors();
        $this->assertSame(10, $stock($this->main));

        $item = $receiving->items()->sole();
        $this->client()->post(route('receivings.rollback', [$receiving->id, $item->id]), ['rollback_qty' => 2])->assertSessionHasNoErrors();
        $this->assertSame(8, $stock($this->main));
    }

    public function test_maintenance_job_orders(): void
    {
        $bus = Bus::query()->create(['bus_no' => 'JO-BUS-1', 'plate_no' => 'JOB-111', 'company' => 'ES Transport', 'garage' => 'Mirasol']);

        $this->client()->get(route('maintenance.job-orders.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('maintenance/job-orders/create')->where('buses.0.bus_no', 'JO-BUS-1')->has('repairTypes', 5));

        $this->client()->post(route('maintenance.job-orders.store'), [
            'job_order_no' => 'JO-2026-0001', 'bus_id' => $bus->id, 'full_name' => 'Dispatcher', 'description_of_work' => 'Engine overheating check', 'odometer_reading' => 120000,
        ])->assertSessionHasNoErrors();
        $jobOrder = JobOrderMaintenance::query()->sole();

        $this->client()->get(route('maintenance.job-orders.index', ['status' => 'standby']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('maintenance/job-orders/index')
                ->where('jobOrders.data.0.job_order_no', 'JO-2026-0001')
                ->where('jobOrders.data.0.odometer', '120,000 km')
                ->where('statusCards', fn ($cards) => collect($cards)->firstWhere('value', 'standby')['count'] === 1));

        // Operational requires mechanics and repair types.
        $this->client()->patch(route('maintenance.job-orders.update-status', $jobOrder), ['status' => 'operational', 'mechanic_names' => [], 'repair_types' => []])
            ->assertSessionHasErrors(['mechanic_names', 'repair_types']);
        $this->client()->patch(route('maintenance.job-orders.update-status', $jobOrder), [
            'status' => 'operational', 'mechanic_names' => ['Mang Jose'], 'repair_types' => ['mechanical'], 'remarks' => 'Fixed radiator',
        ])->assertSessionHasNoErrors();

        $this->client()->patch(route('maintenance.job-orders.update-number', $jobOrder), ['job_order_no' => 'JO-2026-0009'])->assertSessionHasNoErrors();

        $this->client()->get(route('maintenance.job-orders.show', $jobOrder))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('maintenance/job-orders/show')
                ->where('jobOrder.job_order_no', 'JO-2026-0009')
                ->where('jobOrder.status.value', 'operational')
                ->where('jobOrder.downtime_running', false)
                ->where('jobOrder.mechanics.0', 'Mang Jose')
                ->where('jobOrder.repair_types.0.label', 'Mechanical')
                ->where('jobOrder.histories', fn ($histories) => collect($histories)->isNotEmpty()));

        $this->client()->get(route('maintenance.job-orders.edit-status', $jobOrder))->assertInertia(fn (Assert $page) => $page->component('maintenance/job-orders/edit-status')->where('values.status', 'operational')->where('values.repair_types.0', 'mechanical'));
        $this->client()->get(route('maintenance.job-orders.edit-number', $jobOrder))->assertInertia(fn (Assert $page) => $page->component('maintenance/job-orders/edit-number')->where('jobOrder.job_order_no', 'JO-2026-0009'));
    }

    private function client(): static
    {
        return $this->actingAs($this->user)
            ->withSession(['_token' => 'stock-test', 'unlocked' => true, 'last_activity_time' => now()->timestamp])
            ->withHeader('X-CSRF-TOKEN', 'stock-test');
    }
}
