<?php

declare(strict_types=1);

namespace Tests\Feature\ITDepartment;

use App\Models\BusDetail;
use App\Models\CctvConcern;
use App\Models\ItInventoryItem;
use App\Models\JobOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class ItPagesTest extends TestCase
{
    use RefreshDatabase;

    private const PERMISSIONS = [
        'tickets.view', 'tickets.create', 'tickets.update', 'tickets.delete', 'tickets.export', 'tickets.approve',
        'cctv.view', 'cctv.create', 'cctv.update', 'cctv.delete', 'cctv.export',
        'it-inventory.view', 'it-inventory.create', 'it-inventory.update', 'it-inventory.delete',
    ];

    private User $user;

    private BusDetail $bus;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::PERMISSIONS as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $role = Role::findOrCreate('IT Head', 'web');
        $role->syncPermissions(self::PERMISSIONS);
        $this->user = User::factory()->create(['account_status' => 'active', 'must_change_password' => false, 'full_name' => 'Ian Tech']);
        $this->user->assignRole($role);

        $this->bus = BusDetail::query()->create(['garage' => 'Mirasol', 'name' => 'Test Bus', 'body_number' => 'BUS-001', 'plate_number' => 'ABC-1234']);
    }

    public function test_job_order_list_create_view_update_and_workflow(): void
    {
        Storage::fake('local');

        $this->client()->get(route('tickets.createjoborder.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('it/job-orders/create')
                ->where('buses.0.label', 'BUS-001 — ABC-1234')
                ->where('issueTypes.0', 'ACCIDENT'));

        $this->client()->post(route('tickets.storejoborder.post'), [
            'bus_detail_id' => $this->bus->id,
            'job_type' => 'CUTTING FARE',
            'job_datestart' => '23/09/26',
            'job_time_start' => '08:00',
            'job_time_end' => '09:30',
            'direction' => 'North Bound',
            'job_sitNumber' => 12,
            'files' => [UploadedFile::fake()->image('ticket.jpg')],
        ])->assertRedirect(route('tickets.joborder.index'))->assertSessionHasNoErrors();

        $job = JobOrder::query()->sole();

        $this->client()->get(route('tickets.joborder.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('it/job-orders/index')
                ->where('filters.tab', 'pending')
                ->has('tickets.data', 1)
                ->where('tickets.data.0.bus', 'Test Bus - BUS-001')
                ->where('tickets.data.0.issue', 'CUTTING FARE'));

        $this->client()->get(route('tickets.joborder.view', $job->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('it/job-orders/show')
                ->where('job.number', str_pad((string) $job->id, 5, '0', STR_PAD_LEFT))
                ->where('values.job_time_start', '08:00')
                ->has('files', 1)
                ->where('files.0.extension', 'jpg')
                ->where('can.update', true));

        $this->client()->put(route('tickets.joborder.update', $job->id), [
            'job_type' => 'ACCIDENT',
            'job_datestart' => '2026-09-24',
            'job_time_start' => '10:00',
            'job_time_end' => '11:00',
            'direction' => 'South Bound',
            'job_sitNumber' => '5',
            'job_remarks' => 'Updated from React',
            'driver_name' => 'Juan',
            'conductor_name' => 'Pedro',
        ])->assertSessionHasNoErrors();
        $this->assertSame('ACCIDENT', $job->refresh()->job_type);
        $this->assertSame('Juan', $job->driver_name);

        $this->client()->post(route('tickets.joborder.addnote', $job->id), ['reason' => 'Other', 'details' => 'Checked'])->assertSessionHasNoErrors();
        $this->client()->post(route('tickets.joborder.addfile', $job->id), ['files' => [UploadedFile::fake()->create('report.pdf', 20, 'application/pdf')]])->assertSessionHasNoErrors();

        $this->client()->get(route('tickets.joborder.view', $job->id))
            ->assertInertia(fn (Assert $page) => $page
                ->has('notes', 1)
                ->has('files', 2)
                ->where('logs', fn ($logs) => collect($logs)->isNotEmpty()));
    }

    public function test_job_order_accepts_several_seats_from_the_seat_map(): void
    {
        $payload = [
            'bus_detail_id' => $this->bus->id,
            'job_type' => 'ACCIDENT',
            'job_datestart' => '23/09/26',
            'job_time_start' => '08:00',
            'job_time_end' => '09:30',
            'direction' => 'North Bound',
        ];

        // The seat map sends a list; it is stored sorted without duplicates.
        $this->client()->post(route('tickets.storejoborder.post'), $payload + ['job_sitNumber' => '52,13, 12,13'])->assertSessionHasNoErrors();
        $job = JobOrder::query()->sole();
        $this->assertSame('12, 13, 52', $job->job_sitNumber);

        $this->client()->get(route('tickets.joborder.index'))
            ->assertInertia(fn (Assert $page) => $page->where('tickets.data.0.seat', '12, 13, 52'));

        // Out-of-range and non-numeric seats are rejected.
        $this->client()->post(route('tickets.storejoborder.post'), $payload + ['job_sitNumber' => '12, 61'])->assertSessionHasErrors('job_sitNumber');
        $this->client()->post(route('tickets.storejoborder.post'), $payload + ['job_sitNumber' => 'window'])->assertSessionHasErrors('job_sitNumber');

        // Editing keeps working with one seat or several.
        $this->client()->put(route('tickets.joborder.update', $job->id), ['job_sitNumber' => '7'])->assertSessionHasNoErrors();
        $this->assertSame('7', $job->refresh()->job_sitNumber);
        $this->client()->put(route('tickets.joborder.update', $job->id), ['job_sitNumber' => '1, 48'])->assertSessionHasNoErrors();
        $this->assertSame('1, 48', $job->refresh()->job_sitNumber);
    }

    public function test_cctv_concerns_and_bus_dashboard(): void
    {
        $item = ItInventoryItem::query()->create(['item_name' => 'CCTV Camera', 'unit' => 'pcs', 'stock_qty' => 10, 'minimum_stock' => 1, 'is_active' => true]);

        $this->client()->post(route('concern.cctv.store'), [
            'bus_no' => $this->bus->id,
            'issue_type' => 'Camera',
            'problem_details' => 'No video',
            'action_taken' => 'Checked wiring',
            'status' => 'Open',
            'items' => [['it_inventory_item_id' => $item->id, 'qty_used' => 2, 'remarks' => '']],
        ])->assertRedirect(route('concern.cctv.index'))->assertSessionHasNoErrors();

        $concern = CctvConcern::query()->sole();
        $this->assertSame('Checked wiring', $concern->action_taken, 'Action taken on create is saved.');
        $this->assertSame(8, $item->refresh()->stock_qty);

        $this->client()->get(route('concern.cctv.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('it/cctv/index')
                ->where('concerns.data.0.jo_no', $concern->jo_no)
                ->where('concerns.data.0.items.0.qty_used', '2')
                ->where('stats.open', 1)
                ->where('inventoryItems.0.label', 'CCTV Camera'));

        $this->client()->get(route('concern.cctv.view', $concern->id))
            ->assertRedirect(route('concern.cctv.index', ['q' => $concern->jo_no, 'open' => $concern->id]));

        $this->client()->put(route('concern.cctv.update', $concern->id), [
            'status' => 'Fixed',
            'action_taken' => 'Replaced camera',
            'assigned_to' => '',
            'items' => [['it_inventory_item_id' => $item->id, 'qty_used' => 3, 'remarks' => 'swap']],
        ])->assertSessionHasNoErrors();
        $this->assertSame('Fixed', $concern->refresh()->status);
        $this->assertSame(7, $item->refresh()->stock_qty, 'Old items are restored before the new ones are deducted.');

        $this->client()->get(route('concern.bus-status'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('it/cctv/bus-status')
                ->where('buses.data.0.body_number', 'BUS-001')
                ->where('buses.data.0.total', 0));

        $this->client()->get(route('concern.bus-status.show', 'BUS-001'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('it/cctv/bus-status-show')
                ->where('completedCount', 1)
                ->where('completedJobOrders.data.0.parts.0', 'CCTV Camera x3')
                ->where('partsSummary.0.qty', 3));
    }

    public function test_it_inventory_pages(): void
    {
        $item = ItInventoryItem::query()->create(['item_name' => 'Patch Cord', 'category' => 'Network', 'unit' => 'pcs', 'stock_qty' => 1, 'minimum_stock' => 2, 'is_active' => true]);

        $this->client()->get(route('it-inventory.index', ['category' => 'Network']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('it/inventory/index')
                ->has('items.data', 1)
                ->where('categories.0', 'Network')
                ->where('can.delete', true));

        $this->client()->get(route('it-inventory.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('it/inventory/form')->where('values.unit', 'pcs')->where('item', null));

        $this->client()->get(route('it-inventory.edit', $item->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('values.item_name', 'Patch Cord')->where('values.is_active', true));

        $this->client()->put(route('it-inventory.update', $item->id), [
            'item_name' => 'Patch Cord 2m', 'category' => 'Network', 'unit' => 'pcs', 'brand' => '', 'model' => '', 'part_number' => '',
            'location' => 'IT Room', 'stock_qty' => '9', 'minimum_stock' => '2', 'is_active' => false, 'description' => '',
        ])->assertRedirect(route('it-inventory.index'))->assertSessionHasNoErrors();

        $item->refresh();
        $this->assertSame('Patch Cord 2m', $item->item_name);
        $this->assertFalse((bool) $item->is_active);
    }

    private function client(): static
    {
        return $this->actingAs($this->user)
            ->withSession(['_token' => 'it-test', 'unlocked' => true, 'last_activity_time' => now()->timestamp])
            ->withHeader('X-CSRF-TOKEN', 'it-test');
    }
}
