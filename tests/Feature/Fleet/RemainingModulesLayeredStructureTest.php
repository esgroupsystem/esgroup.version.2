<?php

declare(strict_types=1);

namespace Tests\Feature\Fleet;

use App\Models\Bus;
use App\Models\BusDetail;
use App\Models\BusForSaleRecord;
use App\Models\DieselStock;
use App\Models\EmployeeBiometric;
use App\Models\EmployeePlottingSchedule;
use App\Models\MirasolBiometricsLog;
use App\Models\OdometerSubmission;
use App\Models\User;
use App\Providers\RepositoryServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * General (dashboards), Fleet (Odometer Monitoring, Bus Analytics) and Biometrics (Sync, Manual)
 * on the layered structure, plus the bugs fixed while moving them.
 */
final class RemainingModulesLayeredStructureTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('Developer', 'web');
        $this->user = User::factory()->create(['account_status' => 'active', 'must_change_password' => false]);
        $this->user->assignRole('Developer');
    }

    public function test_every_repository_binding_resolves_and_old_controllers_are_gone(): void
    {
        foreach ((new RepositoryServiceProvider(app()))->singletons as $interface => $class) {
            $this->assertInstanceOf($class, app($interface));
        }

        foreach ([
            'App\Http\Controllers\DashboardController',
            'App\Http\Controllers\Chairman\HrDataController',
            'App\Http\Controllers\HR_Department\HRDashboardController',
            'App\Http\Controllers\HR_Department\MirasolBiometricsLogController',
            'App\Http\Controllers\Payroll\ManualBiometricsEncodingController',
            'App\Http\Controllers\Maintenance\OdometerReportController',
            'App\Services\Reports\HrDataReportService',
        ] as $class) {
            $this->assertFalse(class_exists($class), "{$class} should be gone");
        }
    }

    public function test_odometer_page_survives_bad_dates_and_export_without_encoder(): void
    {
        // A bad month or date in the URL used to fail with a 500.
        $this->client()->get(route('odometer.index', ['month' => 'abc']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('filters.month', now()->format('Y-m')));
        $this->client()->get(route('odometer.index', ['filter_type' => 'day', 'date' => '2026-13-45']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('filters.date', now()->toDateString()));

        // A diesel movement with no encoder (e.g. from the mobile app) used to crash the export.
        DieselStock::query()->create(['date' => now()->toDateString(), 'type' => 'in', 'liters' => 100, 'encoded_by' => null]);
        $this->client()->get(route('odometer.export', ['export_type' => 'xls']))->assertOk()->assertSee('System');
    }

    public function test_moving_a_reading_compares_it_with_its_real_neighbours(): void
    {
        $bus = BusDetail::query()->create(['garage' => 'Mirasol', 'name' => 'ES', 'body_number' => 'B-9', 'plate_number' => 'P-9']);
        $reading = fn (string $date, int $km): OdometerSubmission => OdometerSubmission::query()->create([
            'user_id' => $this->user->id, 'bus_detail_id' => $bus->id, 'date' => $date, 'time' => '08:00:00', 'driver_name' => 'Juan', 'new_odometer' => $km, 'diesel_consumption' => 0,
        ]);
        $reading('2026-09-01', 1000);
        $moved = $reading('2026-09-03', 2000);
        $reading('2026-09-05', 3000);

        // Moving the 3rd-of-month reading to the 4th at 1,900 km sits between 1,000 and 3,000.
        // It used to be compared with its own old value (2,000) and refused.
        $this->client()->patch(route('odometer.update', $moved), [
            'date' => '2026-09-04', 'time' => '08:00', 'driver_name' => 'Juan', 'new_odometer' => 1900,
        ])->assertSessionHasNoErrors();
        $this->assertSame(1900, (int) $moved->refresh()->new_odometer);

        $this->client()->patch(route('odometer.update', $moved), [
            'date' => '2026-09-04', 'time' => '08:00', 'driver_name' => 'Juan', 'new_odometer' => 3500,
        ])->assertSessionHasErrors('new_odometer');
    }

    public function test_bus_analytics_totals_and_folders(): void
    {
        Bus::query()->create(['bus_no' => '1', 'company' => 'JELL', 'garage' => 'MIRASOL', 'operational_status' => Bus::STATUS_ACTIVE, 'sale_status' => Bus::SALE_NOT_FOR_SALE]);
        Bus::query()->create(['bus_no' => '2', 'company' => 'JELL', 'garage' => 'MIRASOL', 'operational_status' => Bus::STATUS_ACCIDENT_RELATED_BREAKDOWN, 'sale_status' => Bus::SALE_NOT_FOR_SALE]);
        $forSale = Bus::query()->create(['bus_no' => '3', 'company' => 'JELL', 'garage' => 'BALINTAWAK', 'operational_status' => Bus::STATUS_ACTIVE, 'sale_status' => Bus::SALE_FOR_SALE]);
        BusForSaleRecord::query()->create(['bus_id' => $forSale->id, 'bus_no' => '3', 'company' => 'JELL', 'garage' => 'BALINTAWAK', 'status' => Bus::STATUS_ACTIVE, 'breakdown_start_date' => now()->subDays(4)->toDateString()]);
        // Older rows were not upper-cased; they belong to the same garage tab.
        Bus::query()->create(['bus_no' => '4', 'company' => 'Jell', 'garage' => 'Mirasol', 'operational_status' => Bus::STATUS_ACTIVE, 'sale_status' => Bus::SALE_NOT_FOR_SALE]);

        $this->client()->get(route('fleet.buses.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('totals.total_units', 4)
                ->where('totals.for_sale', 1)
                ->where('totals.active', 2)
                ->where('totals.accident_related', 1)
                ->where('garageSummary.0.name', 'BALINTAWAK')
                ->where('garageSummary.0.for_sale', 1)
                ->where('forSaleSummary.running_condition_total', 1)
                ->has('folders', 3)
                ->where('folders.0.label', 'MIRASOL')
                ->where('folders.0.count', 3)
                ->has('folders.0.groups', 1)
                ->where('folders.1.groups.0.rows.0.for_sale', true)
                ->where('folders.2.type', 'for_sale')
                ->where('folders.2.groups.0.rows.0.days', 4)
                ->where('filteredCount', 4));

        $this->client()->get(route('fleet.buses.index', ['sale_status' => Bus::SALE_FOR_SALE]))
            ->assertInertia(fn (Assert $page) => $page->where('filteredCount', 1));
    }

    public function test_dashboards_and_biometrics_pages_render(): void
    {
        $this->client()->get(route('dashboard.index'))->assertOk()->assertInertia(fn (Assert $page) => $page->component('dashboard/index')->has('greeting'));
        $this->client()->get(route('chairman.hr-data.index'))->assertOk()->assertInertia(fn (Assert $page) => $page->component('dashboards/hr-data/index')->has('leaveReports', 3));
        $this->client()->get(route('hr.dashboard'))->assertOk()->assertInertia(fn (Assert $page) => $page->component('dashboards/hr/index'));
        $this->client()->get(route('mirasol-logs.index'))->assertOk()->assertInertia(fn (Assert $page) => $page->component('biometrics/sync/index'));
        $this->client()->get(route('manual-biometrics.index'))->assertOk()->assertInertia(fn (Assert $page) => $page->component('payroll/manual-biometrics/index')->where('selectedEmployee', null));
    }

    public function test_biometrics_sync_merges_people_from_logs_and_schedules(): void
    {
        MirasolBiometricsLog::query()->create([
            'crosschex_account' => 'main', 'crosschex_id' => sha1('a'), 'employee_no' => '4713020',
            'employee_name' => 'Log Person', 'check_time' => '2026-09-14 08:00:00', 'device_sn' => 'DEV-1',
        ]);
        EmployeePlottingSchedule::query()->create([
            'employee_no' => '4713021', 'employee_name' => 'Schedule Person', 'biometric_employee_id' => '4713021',
            'shift_name' => 'Regular Shift', 'time_in' => '08:00', 'time_out' => '17:00', 'status' => 'scheduled',
        ]);

        // Merging the two lists used to fail with a 500 once both had people.
        $this->client()->get(route('mirasol-logs.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('people', fn ($people) => collect($people)->pluck('value')->intersect(['Log Person', 'Schedule Person'])->count() === 2));
        $this->client()->get(route('mirasol-logs.index', ['q' => 'Person', 'cutoff_month' => 9, 'cutoff_year' => 2026, 'cutoff_type' => '11_25']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('rows.total', 30));
    }

    public function test_manual_biometrics_writes_punches_once(): void
    {
        $employee = EmployeeBiometric::query()->create([
            'source_key' => 'main:4713010', 'source_crosschex_account' => 'main', 'source_employee_no' => '4713010',
            'source_employee_name' => 'Wfh Worker', 'display_name' => 'Wfh Worker', 'employment_status' => 'active',
            'is_payroll_active' => true, 'group_name' => 1,
        ]);
        $save = fn () => $this->client()->post(route('manual-biometrics.store'), [
            'cutoff_month' => 10, 'cutoff_year' => 2026, 'cutoff_type' => 'first', 'employee_biometric_id' => $employee->id,
            'rows' => [['work_date' => '2026-10-12', 'time_in' => '22:00', 'time_out' => '06:00', 'remarks' => 'Night WFH']],
        ]);

        $save()->assertSessionHasNoErrors()->assertSessionHas('success', fn (string $message): bool => str_contains($message, 'Punches written: 2'));
        // Overnight: the Time Out is saved on the next morning.
        $this->assertDatabaseHas('mirasol_biometrics_logs', ['employee_no' => '4713010', 'state' => 'Check Out', 'check_time' => '2026-10-13 06:00:00', 'device_sn' => 'WFH-MANUAL']);
        // Saving the same grid again changes nothing.
        $save()->assertSessionHas('success', fn (string $message): bool => str_contains($message, 'Punches written: 0'));

        $this->client()->get(route('manual-biometrics.index', ['cutoff_month' => 10, 'cutoff_year' => 2026, 'cutoff_type' => 'first', 'employee_biometric_id' => $employee->id]))
            ->assertInertia(fn (Assert $page) => $page->where('cutoffRows.1.time_out', '06:00')->has('recentLogs', 2));
        $this->client()->getJson(route('manual-biometrics.search-employees', ['q' => 'Wfh']))
            ->assertOk()
            ->assertJsonPath('0.employee_biometric_id', $employee->id);
    }

    private function client(): static
    {
        return $this->actingAs($this->user)
            ->withSession(['_token' => 'rest-test', 'unlocked' => true, 'last_activity_time' => now()->timestamp])
            ->withHeader('X-CSRF-TOKEN', 'rest-test');
    }
}
