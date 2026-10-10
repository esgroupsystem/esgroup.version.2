<?php

declare(strict_types=1);

namespace Tests\Feature\Payroll;

use App\Models\Holiday;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\PayrollRule;
use App\Models\PayrollSettingVersion;
use App\Repositories\Contracts\Payroll\PayrollDataResetRepositoryInterface;
use App\Repositories\Payroll\PayrollDataResetRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** `php artisan payroll:reset-data` removes payroll test data, finalized runs included, and keeps the setup. */
final class ResetPayrollDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_binding_resolves(): void
    {
        $this->assertInstanceOf(PayrollDataResetRepository::class, app(PayrollDataResetRepositoryInterface::class));
    }

    public function test_dry_run_and_cancel_delete_nothing(): void
    {
        $this->seedPayrollData();

        $this->artisan('payroll:reset-data', ['--dry-run' => true])->assertSuccessful();
        $this->artisan('payroll:reset-data')
            ->expectsQuestion('This cannot be undone. Make a database backup first. Type DELETE to continue', 'no')
            ->assertSuccessful();

        $this->assertSame(1, DB::table('payrolls')->count());
        $this->assertSame(1, DB::table('benefit_contribution_records')->count());
    }

    public function test_removes_finalized_payroll_and_everything_it_saved_but_keeps_the_setup(): void
    {
        $data = $this->seedPayrollData();
        $settingsBefore = PayrollSettingVersion::query()->count();

        $this->artisan('payroll:reset-data')
            ->expectsQuestion('This cannot be undone. Make a database backup first. Type DELETE to continue', 'DELETE')
            ->assertSuccessful();

        foreach (['payrolls', 'payroll_items', 'benefit_contribution_records', 'payroll_benefit_settlements', 'payment_logs'] as $table) {
            $this->assertSame(0, DB::table($table)->count(), $table);
        }
        $this->assertSame(0, DB::table('payroll_audit_logs')->whereIn('module', ['payroll', 'benefits'])->count());

        // Kept, and released from the deleted payroll.
        $adjustment = DB::table('payroll_attendance_adjustments')->find($data['adjustment_id']);
        $this->assertNotNull($adjustment);
        $this->assertNull($adjustment->paid_payroll_id);
        $this->assertNull($adjustment->paid_payroll_item_id);
        $this->assertSame(1, DB::table('daily_attendance_summaries')->count());
        $this->assertSame(1, DB::table('payroll_audit_logs')->where('module', 'settings')->count());

        // Setup untouched.
        $this->assertSame($settingsBefore, PayrollSettingVersion::query()->count());
        $this->assertSame(1, PayrollRule::query()->count());
        $this->assertSame(1, Holiday::query()->count());
        $this->assertTrue(Storage::disk('local')->exists('payroll/ot-forms/ot.pdf'));
    }

    public function test_optional_scopes_remove_adjustments_files_and_summaries(): void
    {
        $this->seedPayrollData();

        $this->artisan('payroll:reset-data', ['--adjustments' => true, '--summaries' => true])
            ->expectsQuestion('This cannot be undone. Make a database backup first. Type DELETE to continue', 'DELETE')
            ->assertSuccessful();

        $this->assertSame(0, DB::table('payrolls')->count());
        $this->assertSame(0, DB::table('payroll_attendance_adjustments')->count());
        $this->assertSame(0, DB::table('daily_attendance_summaries')->count());
        $this->assertFalse(Storage::disk('local')->exists('payroll/ot-forms/ot.pdf'));
        $this->assertSame(1, DB::table('payroll_audit_logs')->where('module', 'settings')->count());
    }

    /** @return array{adjustment_id: int} */
    private function seedPayrollData(): array
    {
        $now = now();

        $payroll = Payroll::query()->create([
            'payroll_number' => 'PR-TEST-1', 'cutoff_month' => 10, 'cutoff_year' => 2026, 'cutoff_type' => 'first', 'garage_group' => '1',
            'period_start' => '2026-10-11', 'period_end' => '2026-10-25', 'status' => 'finalized', 'generated_at' => $now,
        ]);
        $item = PayrollItem::query()->create(['payroll_id' => $payroll->id, 'employee_name' => 'Test Person', 'gross_pay' => 10000, 'net_pay' => 9000]);

        DB::table('benefit_contribution_records')->insert([
            'payroll_id' => $payroll->id, 'payroll_item_id' => $item->id, 'monthly_key' => 'k1',
            'contribution_month' => 10, 'contribution_year' => 2026, 'period_start' => '2026-09-26', 'period_end' => '2026-10-25',
            'payroll_number' => 'PR-TEST-1', 'employee_name' => 'Test Person', 'posted_at' => $now, 'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('payroll_benefit_settlements')->insert(['payroll_id' => $payroll->id, 'payroll_item_id' => $item->id, 'created_at' => $now, 'updated_at' => $now]);
        DB::table('payment_logs')->insert([
            'payroll_id' => $payroll->id, 'payroll_item_id' => $item->id, 'log_type' => 'deduction', 'source_type' => 'sss_loan', 'source_id' => 1,
            'amount' => 500, 'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('payroll_audit_logs')->insert([
            ['module' => 'payroll', 'action' => 'finalized', 'payroll_id' => $payroll->id, 'created_at' => $now],
            ['module' => 'settings', 'action' => 'updated', 'payroll_id' => null, 'created_at' => $now],
        ]);

        Storage::disk('local')->put('payroll/ot-forms/ot.pdf', 'pdf');
        $adjustmentId = DB::table('payroll_attendance_adjustments')->insertGetId([
            'employee_name' => 'Test Person', 'adjustment_type' => 'change_time', 'work_date' => '2026-10-12', 'status' => 'approved',
            'paid_payroll_id' => $payroll->id, 'paid_payroll_item_id' => $item->id, 'attachment_path' => 'payroll/ot-forms/ot.pdf',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('daily_attendance_summaries')->insert(['employee_name' => 'Test Person', 'work_date' => '2026-10-12', 'created_at' => $now, 'updated_at' => $now]);

        PayrollRule::query()->create(['name' => 'Rule', 'code' => 'rule_one', 'kind' => 'earning', 'method' => 'fixed', 'amount' => 1]);
        Holiday::query()->create([
            'name' => 'Test Day', 'holiday_type' => 'regular', 'actual_date' => '2026-12-25', 'observed_date' => '2026-12-25',
            'not_worked_multiplier' => 1, 'worked_multiplier' => 2, 'is_moved' => false, 'is_active' => true,
        ]);

        return ['adjustment_id' => $adjustmentId];
    }
}
