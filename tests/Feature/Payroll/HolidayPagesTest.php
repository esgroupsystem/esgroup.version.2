<?php

declare(strict_types=1);

namespace Tests\Feature\Payroll;

use App\Models\Holiday;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class HolidayPagesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $permissions = ['holidays.view', 'holidays.create', 'holidays.update', 'holidays.delete'];
        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $this->user = User::factory()->create(['account_status' => 'active', 'must_change_password' => false]);
        $role = Role::findOrCreate('Holiday Editor', 'web');
        $role->syncPermissions($permissions);
        $this->user->assignRole($role);
    }

    private function client(): static
    {
        return $this->actingAs($this->user)
            ->withSession(['_token' => 'holiday-test', 'unlocked' => true, 'last_activity_time' => now()->timestamp])
            ->withHeader('X-CSRF-TOKEN', 'holiday-test');
    }

    public function test_holiday_create_edit_and_calendar(): void
    {
        $this->client()->get(route('holidays.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('payroll/holidays/form')
                ->where('values.holiday_type', 'regular')
                ->where('values.override_multipliers', false));

        // Standard multipliers are applied by type even if other numbers are posted.
        $this->client()->post(route('holidays.store'), [
            'name' => 'Rizal Day', 'holiday_type' => 'regular',
            'actual_date' => '2026-12-30', 'observed_date' => '2026-12-30',
            'override_multipliers' => false, 'not_worked_multiplier' => '9', 'worked_multiplier' => '9',
            'is_moved' => false, 'is_active' => true,
        ])->assertSessionHasNoErrors();

        $holiday = Holiday::query()->sole();
        $this->assertEquals(2.0, (float) $holiday->worked_multiplier);

        $this->client()->get(route('holidays.index', ['year' => 2026, 'month' => 12]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('payroll/holidays/index')
                ->where('holidays.data.0.name', 'Rizal Day')
                ->where('calendar.2026-12-30.0.type', 'regular'));

        $edit = $this->client()->get(route('holidays.edit', $holiday));
        $edit->assertInertia(fn (Assert $page) => $page->where('values.worked_multiplier', '2.00'));

        $values = $edit->viewData('page')['props']['values'];
        $values['override_multipliers'] = true;
        $values['worked_multiplier'] = '2.50';
        $this->client()->put(route('holidays.update', $holiday), $values)->assertSessionHasNoErrors();

        $this->assertEquals(2.5, (float) $holiday->fresh()->worked_multiplier);
        $this->client()->get(route('holidays.edit', $holiday))
            ->assertInertia(fn (Assert $page) => $page->where('values.override_multipliers', true));
    }

    public function test_holiday_table_filters_by_type_and_deletes(): void
    {
        $regular = Holiday::create([
            'name' => 'Rizal Day', 'holiday_type' => 'regular', 'actual_date' => '2026-12-30', 'observed_date' => '2026-12-30',
            'not_worked_multiplier' => 1, 'worked_multiplier' => 2, 'is_moved' => false, 'is_active' => true,
        ]);
        Holiday::create([
            'name' => 'Christmas Eve', 'holiday_type' => 'special', 'actual_date' => '2026-12-24', 'observed_date' => '2026-12-24',
            'not_worked_multiplier' => 0, 'worked_multiplier' => 1.3, 'is_moved' => false, 'is_active' => true,
        ]);

        $this->client()->get(route('holidays.index', ['year' => 2026, 'month' => 12, 'type' => 'special']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('payroll/holidays/index')
                ->where('filters.type', 'special')
                ->has('holidays.data', 1)
                ->where('holidays.data.0.name', 'Christmas Eve')
                // The month grid still shows every holiday.
                ->has('calendar.2026-12-30'));

        // Unknown types are ignored instead of hiding everything.
        $this->client()->get(route('holidays.index', ['year' => 2026, 'month' => 12, 'type' => 'bogus']))
            ->assertInertia(fn (Assert $page) => $page->where('filters.type', '')->has('holidays.data', 2));

        $this->client()->delete(route('holidays.destroy', $regular))
            ->assertRedirect(route('holidays.index'))
            ->assertSessionHas('success');
        $this->assertSame(['Christmas Eve'], Holiday::query()->pluck('name')->all());
    }
}
