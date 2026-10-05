<?php

declare(strict_types=1);

namespace Tests\Feature\ITDepartment;

use App\Models\BusDetail;
use App\Models\CctvConcern;
use App\Models\ItInventoryItem;
use App\Models\JobOrder;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class ItArchitectureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->seedPermissions();
    }

    public function test_it_inventory_store_uses_form_request_and_service(): void
    {
        $user = $this->makeUser(['it-inventory.create']);

        $response = $this->actingAs($user)
            ->withSession(['unlocked' => true])
            ->post(route('it-inventory.store'), [
                'item_name' => 'CAT6 Cable',
                'category' => 'Network',
                'unit' => 'box',
                'stock_qty' => 5,
                'minimum_stock' => 2,
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('it-inventory.index'));
        $this->assertDatabaseHas('it_inventory_items', [
            'item_name' => 'CAT6 Cable',
            'stock_qty' => 5,
            'minimum_stock' => 2,
            'is_active' => 1,
        ]);
    }

    public function test_it_inventory_rejects_negative_stock(): void
    {
        $user = $this->makeUser(['it-inventory.create']);

        $response = $this->actingAs($user)
            ->withSession(['unlocked' => true])
            ->post(route('it-inventory.store'), [
                'item_name' => 'Invalid Item',
                'unit' => 'pcs',
                'stock_qty' => -1,
            ]);

        $response->assertSessionHasErrors('stock_qty');
        $this->assertDatabaseMissing('it_inventory_items', ['item_name' => 'Invalid Item']);
    }

    public function test_cctv_concern_creation_decrements_inventory_atomically(): void
    {
        $user = $this->makeUser(['cctv.create']);
        $bus = $this->makeBus();
        $item = ItInventoryItem::query()->create([
            'item_name' => 'CCTV Camera',
            'unit' => 'pcs',
            'stock_qty' => 10,
            'minimum_stock' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['unlocked' => true])
            ->post(route('concern.cctv.store'), [
                'bus_no' => $bus->id,
                'issue_type' => 'Camera',
                'problem_details' => 'No video signal',
                'status' => 'Open',
                'items' => [[
                    'it_inventory_item_id' => $item->id,
                    'qty_used' => 2,
                    'remarks' => 'Replacement',
                ]],
            ]);

        $response->assertRedirect(route('concern.cctv.index'));
        $this->assertSame(8, $item->refresh()->stock_qty);
        $this->assertDatabaseHas('cctv_job_orders', [
            'bus_no' => (string) $bus->id,
            'status' => 'Open',
            'created_by' => $user->id,
        ]);
        $this->assertDatabaseHas('cctv_concern_items', [
            'it_inventory_item_id' => $item->id,
            'qty_used' => 2,
        ]);
    }

    public function test_cctv_concern_rejects_unknown_status(): void
    {
        $user = $this->makeUser(['cctv.create']);
        $bus = $this->makeBus();

        $response = $this->actingAs($user)
            ->withSession(['unlocked' => true])
            ->post(route('concern.cctv.store'), [
                'bus_no' => $bus->id,
                'issue_type' => 'Camera',
                'problem_details' => 'No signal',
                'status' => 'Unknown',
            ]);

        $response->assertSessionHasErrors('status');
        $this->assertSame(0, CctvConcern::query()->count());
    }

    public function test_it_job_order_accept_transition_is_handled_by_service(): void
    {
        $user = $this->makeUser(['tickets.update']);
        $bus = $this->makeBus();
        $job = JobOrder::query()->create([
            'bus_detail_id' => $bus->id,
            'created_by' => $user->id,
            'job_name' => 'Ticketing Issue',
            'job_status' => 'Pending',
            'job_creator' => $user->full_name,
        ]);

        $this->actingAs($user)
            ->withSession(['unlocked' => true])
            ->post(route('tickets.joborder.accept', $job))
            ->assertRedirect();

        $this->assertSame('In Progress', $job->refresh()->job_status);
        $this->assertDatabaseHas('job_order_logs', [
            'joborder_id' => $job->id,
            'user_id' => $user->id,
            'action' => 'accepted task',
        ]);
    }

    private function seedPermissions(): void
    {
        foreach (['it-inventory.create', 'cctv.create', 'tickets.update'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        Role::findOrCreate('Admin', 'web');
    }

    /** @param list<string> $permissions */
    private function makeUser(array $permissions): User
    {
        $role = Role::findOrCreate('Admin', 'web');
        $role->syncPermissions($permissions);

        $user = User::factory()->create([
            'username' => 'it-architecture-'.fake()->unique()->numerify('######'),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('Password123!Password'),
            'role' => 'Admin',
            'account_status' => 'active',
            'must_change_password' => false,
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function makeBus(): BusDetail
    {
        return BusDetail::query()->create([
            'garage' => 'Mirasol',
            'name' => 'Test Bus',
            'body_number' => 'BUS-001',
            'plate_number' => 'ABC-1234',
        ]);
    }
}
