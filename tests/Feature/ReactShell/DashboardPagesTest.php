<?php

declare(strict_types=1);

namespace Tests\Feature\ReactShell;

use App\Models\Bus;
use App\Models\BusDetail;
use App\Models\BusForSaleRecord;
use App\Models\Category;
use App\Models\Department;
use App\Models\DieselStock;
use App\Models\DriverLeave;
use App\Models\Employee;
use App\Models\JobOrder;
use App\Models\Location;
use App\Models\OdometerSubmission;
use App\Models\Position;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class DashboardPagesTest extends TestCase
{
    use RefreshDatabase;

    private const PERMISSIONS = [
        'chairman.view', 'hr-dashboard.view', 'dashboard.it', 'items.view',
        'odometer.view', 'odometer.create', 'odometer.edit', 'odometer.delete', 'odometer.update',
        'fleet.view', 'fleet.manage.create.view', 'fleet.manage.store', 'fleet.manage.edit', 'fleet.manage.update',
    ];

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::PERMISSIONS as $name) {
            Permission::findOrCreate($name, 'web');
        }
        $role = Role::findOrCreate('Dashboard Tester', 'web');
        $role->syncPermissions(self::PERMISSIONS);

        $this->user = User::factory()->create(['account_status' => 'active', 'must_change_password' => false, 'full_name' => 'Dash Tester']);
        $this->user->assignRole($role);
    }

    public function test_all_data_and_hr_dashboards(): void
    {
        $operations = Department::query()->create(['name' => 'Operations']);
        $position = Position::query()->create(['department_id' => $operations->id, 'title' => 'Driver']);
        $driver = $this->employee('Dario Driver', $position);
        $this->employee('Rita Resigned', $position, 'Resigned');
        DriverLeave::query()->create([
            'employee_id' => $driver->id, 'leave_type' => 'Sick Leave', 'start_date' => now()->subDay(), 'end_date' => now()->addDay(),
            'days' => 3, 'status' => 'approved', 'offense_level' => 2,
        ]);

        $this->client()->get(route('chairman.hr-data.index', ['year' => now()->year]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboards/hr-data/index')
                ->where('totals.employees', 2)
                ->where('totals.active', 1)
                ->where('departments.0.name', 'Operations')
                ->where('departments.0.total', 2)
                ->where('leaveReports.1.label', 'Driver Leave')
                ->where('leaveReports.1.recent.0.employee', 'Dario Driver'));

        $this->client()->get(route('hr.dashboard', ['q' => 'Dario']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboards/hr/index')
                ->where('kpis.total', 2)
                ->where('kpis.on_leave', 1)
                ->where('kpis.for_action', 1)
                ->where('employees.total', 1)
                ->where('employees.data.0.name', 'Dario Driver')
                ->where('offences.0.level', '2nd')
                ->where('employeesByDepartment.0', ['label' => 'Operations', 'value' => 2])
                ->where('leavesByType.0', ['label' => 'Sick Leave', 'value' => 1]));

        // The chart endpoints were routed but had no controller methods.
        $this->client()->getJson(route('hr.dashboard.chart.employees_by_dept'))->assertOk()->assertJsonPath('0.label', 'Operations');
        $this->client()->getJson(route('hr.dashboard.chart.leaves_by_type'))->assertOk()->assertJsonPath('0.value', 1);
    }

    public function test_it_dashboard(): void
    {
        $bus = BusDetail::query()->create(['garage' => 'Mirasol', 'name' => 'ES Transport', 'body_number' => 'B-12', 'plate_number' => 'ABC-12']);
        foreach (['Pending', 'Approval', 'In Progress', 'Completed'] as $status) {
            JobOrder::query()->create(['bus_detail_id' => $bus->id, 'created_by' => $this->user->id, 'job_name' => 'Ticket', 'job_type' => 'ACCIDENT', 'job_status' => $status, 'job_creator' => 'Dash Tester']);
        }

        $this->client()->get(route('dashboard.itindex'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboards/it/index')
                ->where('stats', ['new' => 4, 'pending' => 2, 'progress' => 1, 'completed' => 1])
                ->where('weekly.created.5', 4)
                ->has('unresolved', 3)
                ->where('categories.0', ['label' => 'ACCIDENT', 'value' => 4]));
    }

    public function test_maintenance_stock_dashboard_pages_stay_lists(): void
    {
        $main = Location::query()->where('code', 'MAIN')->sole();
        $category = Category::query()->create(['name' => 'Filters']);
        foreach (range(1, 12) as $i) {
            $product = Product::query()->create(['category_id' => $category->id, 'product_name' => sprintf('Filter %02d', $i), 'unit' => 'pc']);
            ProductStock::query()->updateOrCreate(['product_id' => $product->id, 'location_id' => $main->id], ['qty' => $i]);
        }

        $this->client()->get(route('items.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboards/maintenance-stock/index')
                ->where('totals.items', 12)
                ->where('totals.low', 5)
                ->where('main.total', 12)
                ->where('transfer.total', 12));

        // Page 2 of a forPage() collection keeps its keys; it must still arrive as a JSON list.
        $this->client()->get(route('items.dashboard', ['main_page' => 2, 'tab' => 'main']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.tab', 'main')
                ->has('main.data', 2)
                ->where('main.data.0.qty', 2));
    }

    public function test_odometer_monitoring_reads_and_writes(): void
    {
        $bus = BusDetail::query()->create(['garage' => 'Mirasol', 'name' => 'ES Transport', 'body_number' => 'B-7', 'plate_number' => 'XYZ-7']);
        $today = now()->toDateString();

        $this->client()->post(route('odometer.manual.store'), [
            'bus_detail_id' => $bus->id, 'date_bus_deployed' => $today, 'date' => $today, 'time' => '08:00',
            'driver_name' => 'Juan', 'new_odometer' => 1000, 'diesel_consumption' => 0,
        ])->assertSessionHas('success');
        $this->client()->post(route('odometer.manual.store'), [
            'bus_detail_id' => $bus->id, 'date_bus_deployed' => '2026-01-05', 'date' => $today, 'time' => '17:00',
            'driver_name' => 'Juan', 'new_odometer' => 1200, 'diesel_consumption' => 40, 'also_deduct_diesel_stock' => 1,
        ])->assertSessionHas('success');

        // Duplicate time and a missing driver are rejected.
        $this->client()->post(route('odometer.manual.store'), [
            'bus_detail_id' => $bus->id, 'date' => $today, 'time' => '17:00', 'driver_name' => 'Juan', 'new_odometer' => 1300,
        ])->assertSessionHas('error');
        $this->client()->post(route('odometer.manual.store'), [
            'bus_detail_id' => $bus->id, 'date' => $today, 'time' => '18:00', 'new_odometer' => 1300,
        ])->assertSessionHasErrors('driver_name');

        $this->client()->post(route('odometer.diesel-stock.store'), ['date' => $today, 'type' => 'in', 'liters' => 500, 'unit_cost' => 60])->assertSessionHas('success');

        $this->client()->get(route('odometer.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboards/odometer/index')
                ->where('summary.current_stock', 460)
                ->where('summary.total_km', 200)
                ->where('summary.average_km_per_liter', 5)
                ->where('records.data.1.total_km_run', 200)
                ->where('records.data.1.date_bus_deployed', '2026-01-05')
                ->where('records.data.1.time', '17:00')
                ->where('charts.perBus.0', ['label' => 'B-7 ES Transport', 'value' => 200])
                ->has('movements', 2)
                ->where('can.create', true));

        $second = OdometerSubmission::query()->where('new_odometer', 1200)->sole();

        // Editing keeps date_bus_deployed when the form sends it back.
        $this->client()->patch(route('odometer.update', $second), [
            'date_bus_deployed' => '2026-01-05', 'date' => $today, 'time' => '17:00', 'driver_name' => 'Juan', 'new_odometer' => 1250, 'diesel_consumption' => 50,
        ])->assertSessionHasNoErrors();
        $this->assertStringStartsWith('2026-01-05', (string) $second->refresh()->date_bus_deployed);
        $this->assertEquals(50, DieselStock::query()->where('reference_no', 'ODO-'.$second->id)->value('liters'));

        $this->client()->delete(route('odometer.destroy', $second))->assertSessionHas('success');
        $this->assertDatabaseMissing('diesel_stocks', ['reference_no' => 'ODO-'.$second->id]);

        $this->client()->get(route('odometer.export', ['export_type' => 'csv']))->assertOk();
    }

    public function test_fleet_bus_analytics_create_and_update(): void
    {
        $this->client()->post(route('fleet.buses.store'), [
            'bus_no' => 'fl-101', 'plate_no' => 'abc 101', 'company' => 'es transport', 'garage' => 'mirasol',
            'operational_status' => Bus::STATUS_ACTIVE, 'sale_status' => Bus::SALE_NOT_FOR_SALE,
        ])->assertRedirect(route('fleet.buses.index'));
        $bus = Bus::query()->where('bus_no', 'FL-101')->sole();
        BusForSaleRecord::query()->create(['bus_no' => 'FL-202', 'company' => 'ES TRANSPORT', 'garage' => 'MIRASOL', 'status' => 'running_condition']);

        $this->client()->get(route('fleet.buses.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboards/fleet/index')
                ->where('totals.total_units', 1)
                ->where('companySummary.0.name', 'ES TRANSPORT')
                ->where('forSaleSummary.total_for_sale', 1)
                ->where('folders.0.label', 'MIRASOL')
                ->where('folders.0.groups.0.rows.0.bus_no', 'FL-101')
                ->where('folders.0.groups.0.rows.0.for_sale', false)
                ->where('folders.1.type', 'for_sale')
                ->where('folders.1.groups.0.rows.0.status_label', 'Running Condition')
                ->where('can.edit', true));

        $this->client()->get(route('fleet.buses.edit', ['bus' => $bus->id, 'garage' => 'MIRASOL']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboards/fleet/form')
                ->where('bus.plate_no', 'ABC 101')
                ->where('urls.back', route('fleet.buses.index', ['garage' => 'MIRASOL'])));

        $this->client()->put(route('fleet.buses.update', ['bus' => $bus->id, 'garage' => 'MIRASOL']), [
            'bus_no' => 'FL-101', 'operational_status' => Bus::STATUS_MECHANICAL_BREAKDOWN, 'sale_status' => Bus::SALE_NOT_FOR_SALE,
        ])->assertRedirect(route('fleet.buses.index', ['garage' => 'MIRASOL']));
        $this->assertSame(Bus::STATUS_MECHANICAL_BREAKDOWN, $bus->refresh()->operational_status);

        $this->client()->get(route('fleet.buses.create'))->assertOk()->assertInertia(fn (Assert $page) => $page->component('dashboards/fleet/form')->where('bus', null));
    }

    private function employee(string $name, Position $position, string $status = 'Active'): Employee
    {
        return Employee::query()->create([
            'employee_id' => 'EMP-'.fake()->unique()->numerify('####'),
            'full_name' => $name,
            'company' => 'Jell Transport',
            'garage' => 'Mirasol',
            'status' => $status,
            'department_id' => $position->department_id,
            'position_id' => $position->id,
        ]);
    }

    private function client(): static
    {
        return $this->actingAs($this->user)
            ->withSession(['_token' => 'dash-test', 'unlocked' => true, 'last_activity_time' => now()->timestamp])
            ->withHeader('X-CSRF-TOKEN', 'dash-test');
    }
}
