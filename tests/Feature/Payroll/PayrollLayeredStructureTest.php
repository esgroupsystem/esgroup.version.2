<?php

declare(strict_types=1);

namespace Tests\Feature\Payroll;

use App\Models\DailyAttendanceSummary;
use App\Models\EmployeeBiometric;
use App\Models\Payroll;
use App\Models\PayrollAttendanceAdjustment;
use App\Models\PayrollItem;
use App\Models\User;
use App\Repositories\Contracts\Payroll\AttendanceAdjustmentRepositoryInterface;
use App\Repositories\Contracts\Payroll\AttendanceSummaryRepositoryInterface;
use App\Repositories\Contracts\Payroll\BenefitRecordRepositoryInterface;
use App\Repositories\Contracts\Payroll\BenefitSettlementRepositoryInterface;
use App\Repositories\Contracts\Payroll\PayrollAuditLogRepositoryInterface;
use App\Repositories\Contracts\Payroll\PayrollRepositoryInterface;
use App\Repositories\Payroll\AttendanceAdjustmentRepository;
use App\Repositories\Payroll\AttendanceSummaryRepository;
use App\Repositories\Payroll\BenefitRecordRepository;
use App\Repositories\Payroll\BenefitSettlementRepository;
use App\Repositories\Payroll\PayrollAuditLogRepository;
use App\Repositories\Payroll\PayrollRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Payroll on the layered structure: bindings, the payroll-group boundary (now enforced on every
 * payroll action, not only hidden in the UI), the one-query summary stats, adjustment
 * approve / reject, and finalize being refused while OT is pending.
 */
final class PayrollLayeredStructureTest extends TestCase
{
    use RefreshDatabase;

    private const PERMISSIONS = [
        'payroll.view', 'payroll.create', 'payroll.delete', 'payroll.finalize', 'payroll.export',
        'payroll-benefit-settlements.manage', 'attendance-summary.view', 'attendance-summary.create', 'attendance-summary.export',
        'payroll-attendance-adjustments.view', 'payroll-attendance-adjustments.create', 'payroll-attendance-adjustments.update', 'payroll-attendance-adjustments.delete',
    ];

    public function test_repository_interfaces_resolve_to_their_eloquent_classes(): void
    {
        $this->assertInstanceOf(PayrollRepository::class, app(PayrollRepositoryInterface::class));
        $this->assertInstanceOf(AttendanceAdjustmentRepository::class, app(AttendanceAdjustmentRepositoryInterface::class));
        $this->assertInstanceOf(AttendanceSummaryRepository::class, app(AttendanceSummaryRepositoryInterface::class));
        $this->assertInstanceOf(PayrollAuditLogRepository::class, app(PayrollAuditLogRepositoryInterface::class));
        $this->assertInstanceOf(BenefitSettlementRepository::class, app(BenefitSettlementRepositoryInterface::class));
        $this->assertInstanceOf(BenefitRecordRepository::class, app(BenefitRecordRepositoryInterface::class));
    }

    public function test_payroll_group_boundary_is_enforced_on_every_payroll_action(): void
    {
        $mirasol = $this->payroll('1', 'PR-MIR-1', 'draft');
        $gonzales = $this->payroll('2', 'PR-GON-1', 'draft');
        $item = PayrollItem::query()->create(['payroll_id' => $mirasol->id, 'employee_name' => 'Mia', 'employee_no' => '1']);
        $gonzalesUser = $this->userWith([...self::PERMISSIONS, 'payroll.gonzales'], 'Gonzales Payroll');

        // The list and the group options only show the user's own groups.
        $this->as($gonzalesUser)->get(route('payroll.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('payrolls.total', 1)
                ->where('payrolls.data.0.payroll_number', 'PR-GON-1')
                ->where('payrollGroups', ['2' => 'Gonzales Payroll']));

        // Before, every one of these worked for another group's payroll by id.
        $this->as($gonzalesUser)->get(route('payroll.show', $mirasol))->assertForbidden();
        $this->as($gonzalesUser)->get(route('payroll.items.show', [$mirasol, $item]))->assertForbidden();
        $this->as($gonzalesUser)->get(route('payroll.export.excel', $mirasol))->assertForbidden();
        $this->as($gonzalesUser)->post(route('payroll.finalize', $mirasol))->assertForbidden();
        $this->as($gonzalesUser)->post(route('payroll.items.recompute', [$mirasol, $item]))->assertForbidden();
        $this->as($gonzalesUser)->delete(route('payroll.destroy', $mirasol))->assertForbidden();
        $this->as($gonzalesUser)->post(route('payroll.items.benefit-settlement.store', [$mirasol, $item]), [
            'mode' => 'auto_cap', 'sss_employee_reimbursement' => 0, 'philhealth_employee_reimbursement' => 0, 'pagibig_employee_reimbursement' => 0, 'reason' => 'Separated employee settlement',
        ])->assertForbidden();
        $this->assertDatabaseHas('payrolls', ['id' => $mirasol->id]);

        // Generating for another group is refused; for its own group the request passes validation.
        $this->as($gonzalesUser)->post(route('payroll.store'), ['cutoff_month' => 10, 'cutoff_year' => 2026, 'cutoff_type' => 'second', 'garage_group' => 1, 'rebuild_summary' => false])
            ->assertSessionHasErrors(['garage_group' => 'You are not allowed to generate payroll for the selected payroll group.']);

        $this->as($gonzalesUser)->get(route('payroll.show', $gonzales))->assertOk();
        $this->as($gonzalesUser)->delete(route('payroll.destroy', $gonzales))->assertRedirect('/payroll');
        $this->assertSoftDeleted('payrolls', ['id' => $gonzales->id]);
    }

    public function test_summary_stats_come_from_one_query_with_the_same_numbers(): void
    {
        $user = $this->userWith([...self::PERMISSIONS, 'payroll.all-access']);
        $person = $this->person('4001');
        $day = Carbon::create(2026, 10, 12);
        $rows = [
            ['attendance_status' => 'present', 'payable_days' => 1, 'payable_hours' => 8, 'worked_minutes' => 480, 'late_minutes' => 0],
            ['attendance_status' => 'late', 'payable_days' => 1, 'payable_hours' => 7.5, 'worked_minutes' => 450, 'late_minutes' => 30],
            ['attendance_status' => 'absent', 'payable_days' => 0, 'payable_hours' => 0, 'worked_minutes' => 0, 'late_minutes' => 0],
            ['attendance_status' => 'present', 'payable_days' => 1, 'payable_hours' => 8, 'worked_minutes' => 480, 'late_minutes' => 0, 'is_leave' => true],
            ['attendance_status' => 'holiday_worked', 'payable_days' => 2, 'payable_hours' => 8, 'worked_minutes' => 480, 'late_minutes' => 5, 'is_holiday' => true, 'holiday_type' => 'regular'],
        ];
        foreach ($rows as $index => $row) {
            DailyAttendanceSummary::query()->create($row + [
                'employee_biometric_id' => $person->id, 'employee_name' => 'Pat Person', 'employee_no' => '4001',
                'work_date' => $day->copy()->addDays($index)->toDateString(), 'shift_name' => 'Regular Shift',
            ]);
        }

        $this->as($user)->get(route('attendance-summary.index', ['cutoff_month' => 10, 'cutoff_year' => 2026, 'cutoff_type' => 'first']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('stats.total', 5)
                ->where('stats.present', 2)
                ->where('stats.late', 1)
                ->where('stats.absent', 1)
                ->where('stats.needs_review', 1)
                ->where('stats.leave', 1)
                ->where('stats.holiday', 1)
                ->where('stats.holiday_paid', 1)
                ->where('stats.regular_holiday_worked', 1)
                ->where('stats.special_holiday_worked', 0)
                ->where('stats.regular_shift', 5)
                ->where('stats.total_late_minutes', 35)
                ->where('stats.total_payable_days', fn ($value) => (float) $value === 5.0)
                ->where('stats.total_payable_hours', fn ($value) => (float) $value === 31.5)
                ->where('stats.eligible_employees', 1)
                ->where('stats.missing_summary_employees', 0));

        // The status filter narrows the cards too.
        $this->as($user)->get(route('attendance-summary.index', ['cutoff_month' => 10, 'cutoff_year' => 2026, 'cutoff_type' => 'first', 'status' => 'needs_review']))
            ->assertInertia(fn (Assert $page) => $page->where('stats.total', 1)->has('summaries.data', 1));
    }

    public function test_adjustment_approve_reject_and_finalize_refused_while_ot_pending(): void
    {
        $user = $this->userWith([...self::PERMISSIONS, 'payroll.all-access']);
        $person = $this->person('5001');
        $payroll = $this->payroll('1', 'PR-OT-1', 'draft');
        PayrollItem::query()->create(['payroll_id' => $payroll->id, 'employee_biometric_id' => $person->id, 'employee_name' => 'Ot Person', 'employee_no' => '5001']);
        $ot = PayrollAttendanceAdjustment::query()->create([
            'employee_biometric_id' => $person->id, 'employee_name' => 'Ot Person', 'work_date' => '2026-10-15',
            'adjustment_type' => PayrollAttendanceAdjustment::TYPE_OVERTIME, 'status' => PayrollAttendanceAdjustment::STATUS_PENDING,
            'adjusted_time_in' => '17:00', 'adjusted_time_out' => '19:00', 'reason' => 'Rush',
        ]);

        $this->as($user)->post(route('payroll.finalize', $payroll))
            ->assertSessionHasErrors(['payroll' => 'Cannot finalize payroll. 1 OT/Offset adjustment(s) in this cutoff are still pending Head Manager approval/rejection. Resolve them, rebuild Attendance Summary when Offset is involved, then regenerate the draft payroll.']);
        $this->assertSame('draft', $payroll->refresh()->status);

        $this->as($user)->post(route('payroll-attendance-adjustments.reject', $ot), ['rejection_reason' => 'No OT form'])
            ->assertSessionHas('success', 'Overtime adjustment rejected. It will not be paid.');
        $ot->refresh();
        $this->assertSame([PayrollAttendanceAdjustment::STATUS_REJECTED, 'No OT form', $user->id], [$ot->status, $ot->rejection_reason, (int) $ot->rejected_by]);

        $this->as($user)->post(route('payroll-attendance-adjustments.approve', $ot))
            ->assertSessionHas('success', 'Overtime adjustment approved. It will now be included when the affected draft payroll is generated/regenerated.');
        $ot->refresh();
        $this->assertSame([PayrollAttendanceAdjustment::STATUS_APPROVED, null], [$ot->status, $ot->rejection_reason]);

        // A paid adjustment can no longer be rejected or deleted.
        $ot->update(['paid_payroll_id' => $payroll->id]);
        $this->as($user)->post(route('payroll-attendance-adjustments.reject', $ot))->assertSessionHasErrors(['approval' => 'This adjustment is already linked to a payroll and cannot be rejected.']);
        $this->as($user)->delete(route('payroll-attendance-adjustments.destroy', $ot))->assertSessionHasErrors('adjustment');
        $this->assertDatabaseHas('payroll_attendance_adjustments', ['id' => $ot->id]);
    }

    private function payroll(string $group, string $number, string $status): Payroll
    {
        return Payroll::query()->create([
            'payroll_number' => $number, 'cutoff_month' => 10, 'cutoff_year' => 2026, 'cutoff_type' => 'first', 'garage_group' => $group,
            'period_start' => '2026-10-11', 'period_end' => '2026-10-25', 'status' => $status, 'generated_at' => now()->addHour(),
        ]);
    }

    private function person(string $number): EmployeeBiometric
    {
        return EmployeeBiometric::query()->create([
            'source_key' => "main:{$number}", 'source_crosschex_account' => 'main', 'source_employee_no' => $number,
            'source_employee_name' => "Person {$number}", 'display_name' => "Person {$number}", 'display_employee_no' => $number,
            'employment_status' => 'active', 'is_payroll_active' => true, 'group_name' => 1,
        ]);
    }

    /** @param list<string> $permissions */
    private function userWith(array $permissions, string $roleName = 'Payroll Tester'): User
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        $role = Role::findOrCreate($roleName, 'web');
        $role->syncPermissions($permissions);
        $user = User::factory()->create(['account_status' => 'active', 'must_change_password' => false]);
        $user->assignRole($role);

        return $user;
    }

    private function as(User $user): static
    {
        return $this->actingAs($user)
            ->withSession(['_token' => 'payroll-layer', 'unlocked' => true, 'last_activity_time' => now()->timestamp])
            ->withHeader('X-CSRF-TOKEN', 'payroll-layer');
    }
}
