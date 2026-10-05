<?php

declare(strict_types=1);

namespace Tests\Feature\IT;

use App\Exports\JobOrdersExport;
use App\Models\BusDetail;
use App\Models\CctvConcern;
use App\Models\ItInventoryItem;
use App\Models\JobOrder;
use App\Models\User;
use App\Repositories\Contracts\Fleet\BusDetailRepositoryInterface;
use App\Repositories\Contracts\IT\CctvConcernRepositoryInterface;
use App\Repositories\Contracts\IT\ItInventoryItemRepositoryInterface;
use App\Repositories\Contracts\IT\JobOrderRepositoryInterface;
use App\Repositories\Contracts\Security\UserRepositoryInterface;
use App\Repositories\Fleet\BusDetailRepository;
use App\Repositories\IT\CctvConcernRepository;
use App\Repositories\IT\ItInventoryItemRepository;
use App\Repositories\IT\JobOrderRepository;
use App\Repositories\Security\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * IT Support on the layered structure (Controller → Service → Repository → Model):
 * bindings, removed routes, and the write actions not covered by ItPagesTest.
 */
final class ItLayeredStructureTest extends TestCase
{
    use RefreshDatabase;

    private const PERMISSIONS = [
        'tickets.view', 'tickets.create', 'tickets.update', 'tickets.delete', 'tickets.export', 'tickets.approve',
        'cctv.view', 'cctv.create', 'cctv.update', 'cctv.delete', 'cctv.export',
        'it-inventory.view', 'it-inventory.create', 'it-inventory.update', 'it-inventory.delete',
    ];

    private User $head;

    private BusDetail $bus;

    protected function setUp(): void
    {
        parent::setUp();

        // Deleting a job order removes its attachment folder; never touch the real disk.
        Storage::fake('local');

        foreach (self::PERMISSIONS as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $this->head = $this->userWithRole('IT Head', self::PERMISSIONS);
        $this->bus = BusDetail::query()->create(['garage' => 'Mirasol', 'name' => 'Test Bus', 'body_number' => 'BUS-001', 'plate_number' => 'ABC-1234']);
    }

    public function test_repository_interfaces_resolve_to_their_eloquent_classes(): void
    {
        $this->assertInstanceOf(JobOrderRepository::class, app(JobOrderRepositoryInterface::class));
        $this->assertInstanceOf(CctvConcernRepository::class, app(CctvConcernRepositoryInterface::class));
        $this->assertInstanceOf(ItInventoryItemRepository::class, app(ItInventoryItemRepositoryInterface::class));
        $this->assertInstanceOf(BusDetailRepository::class, app(BusDetailRepositoryInterface::class));
        $this->assertInstanceOf(UserRepository::class, app(UserRepositoryInterface::class));
    }

    public function test_unused_cctv_routes_are_removed(): void
    {
        // `cctv-parts` was a resource route that let anyone with cctv.view create, edit and delete concerns.
        foreach (['cctv-parts.index', 'cctv-parts.store', 'cctv-parts.update', 'cctv-parts.destroy', 'concern.cctv.accept', 'concern.cctv.done', 'concern.cctv.addnote', 'concern.cctv.addfile'] as $name) {
            $this->assertFalse(Route::has($name), "{$name} should not exist.");
        }

        $viewer = $this->userWithRole('CCTV Viewer', ['cctv.view']);
        $this->as($viewer)->post('/cctv-parts', ['bus_no' => $this->bus->id, 'issue_type' => 'Camera', 'problem_details' => 'x', 'status' => 'Open'])->assertNotFound();
        $this->assertSame(0, CctvConcern::query()->count());
    }

    public function test_bus_dashboard_lists_buses_with_active_concerns_first_across_pages(): void
    {
        foreach (range(2, 25) as $i) {
            BusDetail::query()->create(['garage' => 'Mirasol', 'name' => 'Bus', 'body_number' => sprintf('BUS-%03d', $i), 'plate_number' => "P-{$i}"]);
        }
        $last = BusDetail::query()->where('body_number', 'BUS-025')->sole();
        CctvConcern::query()->create(['jo_no' => 'JO-2026-00001', 'bus_no' => $last->id, 'issue_type' => 'DVR', 'problem_details' => 'No recording', 'status' => 'Open']);
        CctvConcern::query()->create(['jo_no' => 'JO-2026-00002', 'bus_no' => $last->id, 'issue_type' => 'Camera', 'problem_details' => 'Old', 'status' => 'Fixed']);

        // BUS-025 sorts last by body number (page 2), but its open concern puts it first on page 1.
        $this->as($this->head)->get(route('concern.bus-status'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('it/cctv/bus-status')
                ->where('buses.total', 25)
                ->where('buses.data.0.body_number', 'BUS-025')
                ->where('buses.data.0.total', 1)
                ->where('buses.data.0.summary.DVR', 1)
                ->where('buses.data.0.summary.CCTV', 0)
                ->where('buses.data.1.body_number', 'BUS-001')
                ->where('columns', ['CCTV', 'DVR', 'Monitor', 'Power Supply', 'Other']));

        $this->as($this->head)->get(route('concern.bus-status.show', 'BUS-025'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('bus.display_name', 'BUS-025 - P-25 - Bus - Mirasol')
                ->where('totalIssues', 1)
                ->where('completedCount', 1)
                ->has('activeJobOrders.data', 1)
                ->has('timeline', 2));

        $this->as($this->head)->get(route('concern.bus-status.show', 'NO-SUCH-BUS'))->assertNotFound();
    }

    public function test_job_order_approval_workflow_and_delete(): void
    {
        $officer = $this->userWithRole('IT Officer', ['tickets.view', 'tickets.approve', 'tickets.delete', 'tickets.update']);
        $job = $this->jobOrder('Approval');

        $this->as($this->head)->get(route('tickets.joborder.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('tickets.data.0.status', 'approval')
                ->where('tickets.data.0.actions.approve', true)
                ->where('tickets.data.0.actions.delete', false));

        // Only the IT Head (or Developer) may approve, even with the permission.
        $this->as($officer)->post(route('tickets.approve', $job->id))->assertForbidden();
        $this->as($officer)->delete(route('tickets.joborder.delete', $job->id))->assertForbidden();

        $this->as($this->head)->post(route('tickets.approve', $job->id))
            ->assertRedirect(route('tickets.joborder.index'))
            ->assertSessionHas('success', 'Job order approved successfully.');
        $job->refresh();
        $this->assertSame(['Pending', 'Approved', $this->head->id], [$job->job_status, $job->approval_status, $job->approved_by]);

        $this->as($this->head)->post(route('tickets.approve', $job->id))
            ->assertSessionHas('warning', 'This job order is not waiting for approval. Current status: Pending / Approved');

        $this->as($officer)->post(route('tickets.joborder.accept', $job->id))->assertRedirect();
        $this->assertSame(['In Progress', $officer->full_name], [$job->refresh()->job_status, $job->job_assign_person]);

        // An In Progress job order is not deleted.
        $this->as($this->head)->delete(route('tickets.joborder.delete', $job->id))->assertRedirect();
        $this->assertDatabaseHas('job_orders', ['id' => $job->id]);

        $this->as($officer)->post(route('tickets.joborder.done', $job->id))->assertRedirect();
        $this->assertSame('Completed', $job->refresh()->job_status);
        $this->assertDatabaseHas('job_order_logs', ['joborder_id' => $job->id, 'action' => 'completed']);

        $rejected = $this->jobOrder('Approval');
        $this->as($this->head)->post(route('tickets.disapprove', $rejected->id))
            ->assertSessionHas('error', 'Job order disapproved.');
        $this->assertSame(['Disapproved', 'Disapproved'], [$rejected->refresh()->job_status, $rejected->approval_status]);

        $this->as($this->head)->delete(route('tickets.joborder.delete', $rejected->id))->assertRedirect();
        $this->assertDatabaseMissing('job_orders', ['id' => $rejected->id]);
        $this->assertDatabaseMissing('job_order_logs', ['joborder_id' => $rejected->id]);
    }

    public function test_job_order_files_and_exports(): void
    {
        $job = $this->jobOrder('Pending');

        $this->as($this->head)->post(route('tickets.joborder.addfile', $job->id), ['files' => [UploadedFile::fake()->create('report.pdf', 20, 'application/pdf')]])
            ->assertSessionHasNoErrors();
        $file = $job->files()->sole();
        Storage::disk('local')->assertExists($file->file_path);

        $this->as($this->head)->get(route('tickets.joborder.file.download', [$job->id, $file->id]))
            ->assertOk()
            ->assertDownload('report.pdf');

        Storage::disk('local')->delete($file->file_path);
        $this->as($this->head)->get(route('tickets.joborder.file.download', [$job->id, $file->id]))->assertNotFound();

        Excel::fake();
        $this->as($this->head)->get(route('tickets.export', 'excel'))->assertOk();
        Excel::assertDownloaded('job_orders.xlsx', fn (JobOrdersExport $export): bool => $export->collection()->pluck('id')->all() === [$job->id]);
        $this->as($this->head)->get(route('tickets.export', 'pdf'))->assertOk()->assertDownload('job_orders.pdf');
        $this->as($this->head)->get(route('tickets.export', 'word'))->assertSessionHas('error', 'Invalid export type selected.');
    }

    public function test_cctv_csv_export_and_delete_returns_parts_to_stock(): void
    {
        $item = ItInventoryItem::query()->create(['item_name' => 'BNC Connector', 'unit' => 'pcs', 'stock_qty' => 10, 'minimum_stock' => 1, 'is_active' => true]);

        $this->as($this->head)->post(route('concern.cctv.store'), [
            'bus_no' => $this->bus->id,
            'issue_type' => 'Wiring',
            'problem_details' => 'Loose cable',
            'status' => 'Open',
            'items' => [['it_inventory_item_id' => $item->id, 'qty_used' => 4, 'remarks' => '']],
        ])->assertSessionHasNoErrors();
        $concern = CctvConcern::query()->sole();
        $this->assertSame('JO-'.now()->year.'-00001', $concern->jo_no);
        $this->assertSame('BNC Connector x4', $concern->cctv_part);
        $this->assertSame(6, $item->refresh()->stock_qty);

        $csv = $this->as($this->head)->get(route('concern.export', ['type' => 'csv', 'status' => 'Open']))
            ->assertOk()
            ->assertDownload('cctv-job-orders-open-'.now()->format('Y-m-d').'.csv')
            ->streamedContent();
        $this->assertStringContainsString('"JO No","Bus Details"', $csv);
        $this->assertStringContainsString('BUS-001 - ABC-1234 - Test Bus', $csv);
        $this->assertStringContainsString('BNC Connector x4 pcs', $csv);

        $this->as($this->head)->get(route('concern.export', 'xml'))->assertNotFound();

        // Asking for more than the stock fails without changing anything.
        $this->as($this->head)->put(route('concern.cctv.update', $concern->id), [
            'status' => 'In Progress',
            'items' => [['it_inventory_item_id' => $item->id, 'qty_used' => 50]],
        ])->assertSessionHasErrors('error');
        $this->assertSame(6, $item->refresh()->stock_qty);
        $this->assertSame('Open', $concern->refresh()->status);

        $this->as($this->head)->delete(route('concern.cctv.destroy', $concern->id))
            ->assertRedirect(route('concern.cctv.index'))
            ->assertSessionHas('success', 'Job Order deleted.');
        $this->assertSame(10, $item->refresh()->stock_qty);
        $this->assertDatabaseCount('cctv_concern_items', 0);
    }

    public function test_it_inventory_create_and_delete(): void
    {
        $this->as($this->head)->post(route('it-inventory.store'), ['item_name' => 'HDMI Cable', 'unit' => 'pcs', 'stock_qty' => 3])
            ->assertRedirect(route('it-inventory.index'))
            ->assertSessionHas('success', 'IT inventory item added successfully.');
        $item = ItInventoryItem::query()->sole();
        $this->assertSame([0, false], [$item->minimum_stock, $item->is_active]);

        $this->as($this->head)->get(route('it-inventory.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('items.data.0.category', 'Uncategorized')
                ->where('items.data.0.destroy_url', route('it-inventory.destroy', $item->id)));

        $this->as($this->head)->delete(route('it-inventory.destroy', $item->id))->assertSessionHas('success', 'Deleted successfully.');
        $this->assertDatabaseCount('it_inventory_items', 0);
    }

    /** @param list<string> $permissions */
    private function userWithRole(string $roleName, array $permissions): User
    {
        $role = Role::findOrCreate($roleName, 'web');
        $role->syncPermissions($permissions);
        $user = User::factory()->create(['account_status' => 'active', 'must_change_password' => false, 'full_name' => "{$roleName} User"]);
        $user->assignRole($role);

        return $user;
    }

    private function jobOrder(string $status): JobOrder
    {
        return JobOrder::query()->create([
            'bus_detail_id' => $this->bus->id,
            'created_by' => $this->head->id,
            'job_name' => 'Job Order',
            'job_type' => 'ACCIDENT',
            'job_status' => $status,
            'approval_status' => $status === 'Approval' ? 'Approval' : 'Approved',
            'job_creator' => 'Tester',
            'job_date_filled' => now(),
        ]);
    }

    private function as(User $user): static
    {
        return $this->actingAs($user)
            ->withSession(['_token' => 'it-test', 'unlocked' => true, 'last_activity_time' => now()->timestamp])
            ->withHeader('X-CSRF-TOKEN', 'it-test');
    }
}
