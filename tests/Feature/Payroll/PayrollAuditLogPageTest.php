<?php

declare(strict_types=1);

namespace Tests\Feature\Payroll;

use App\Models\PayrollAuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class PayrollAuditLogPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_logs_render_with_filters_and_details(): void
    {
        $permissions = ['payroll-audit-logs.view', 'payroll.all-access'];
        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $user = User::factory()->create(['account_status' => 'active', 'must_change_password' => false]);
        $role = Role::findOrCreate('Payroll Auditor', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        PayrollAuditLog::query()->create([
            'user_id' => $user->id,
            'garage_group' => 1,
            'module' => 'salary_adjustment',
            'action' => 'created',
            'description' => 'Added salary adjustment',
            'request_id' => 'req-123',
            'new_values' => ['amount' => -1000],
            'created_at' => now(),
        ]);
        PayrollAuditLog::query()->create([
            'module' => 'payroll',
            'action' => 'deleted',
            'description' => 'Deleted payroll',
            'created_at' => now(),
        ]);

        $client = $this->actingAs($user)
            ->withSession(['unlocked' => true, 'last_activity_time' => now()->timestamp]);

        $client->get(route('payroll-audit-logs.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('payroll/audit-logs/index')
                ->has('logs.data', 2)
                ->where('modules.salary_adjustment', 'Salary Adjustment')
                ->where('actions.deleted', 'Deleted')
                ->where('logs.data.0.user_name', 'System / Console')
                ->where('logs.data.1.action_label', 'Created')
                ->where('logs.data.1.new_values', "{\n    \"amount\": -1000\n}")
                ->where('logs.data.1.old_values', null));

        $client->get(route('payroll-audit-logs.index', ['module' => 'salary_adjustment', 'search' => 'req-123']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('logs.data', 1)
                ->where('filters.module', 'salary_adjustment')
                ->where('filters.search', 'req-123'));
    }
}
