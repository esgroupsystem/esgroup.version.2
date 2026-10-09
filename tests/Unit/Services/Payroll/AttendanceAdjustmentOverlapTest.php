<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Payroll;

use App\Models\EmployeeBiometric;
use App\Models\PayrollAttendanceAdjustment;
use App\Repositories\Contracts\Payroll\AttendanceAdjustmentRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** A rejected adjustment has no effect on payroll and must not block filing a new one. */
final class AttendanceAdjustmentOverlapTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_rejected_adjustment_does_not_block_a_new_filing_for_the_same_date(): void
    {
        $employee = EmployeeBiometric::query()->create([
            'source_key' => 'main:7003', 'source_crosschex_account' => 'main', 'source_employee_no' => '7003',
            'source_employee_name' => 'Retry Worker', 'display_name' => 'Retry Worker',
            'employment_status' => 'active', 'is_payroll_active' => true, 'group_name' => 1,
        ]);

        PayrollAttendanceAdjustment::query()->create([
            'employee_biometric_id' => $employee->id, 'employee_name' => 'Retry Worker',
            'work_date' => '2026-10-10', 'adjustment_type' => PayrollAttendanceAdjustment::TYPE_OVERTIME,
            'status' => PayrollAttendanceAdjustment::STATUS_REJECTED,
            'adjusted_time_in' => '18:00', 'adjusted_time_out' => '20:00', 'reason' => 'Rush order',
            'rejection_reason' => 'No OT form attached',
        ]);

        $repository = app(AttendanceAdjustmentRepositoryInterface::class);

        $this->assertFalse(
            $repository->overlapping([PayrollAttendanceAdjustment::TYPE_OVERTIME], $employee->id, '2026-10-10', '2026-10-10', null),
            'A rejected adjustment must not block a brand-new filing for the same date.'
        );
    }

    public function test_a_pending_or_approved_adjustment_still_blocks_a_duplicate_filing(): void
    {
        $employee = EmployeeBiometric::query()->create([
            'source_key' => 'main:7004', 'source_crosschex_account' => 'main', 'source_employee_no' => '7004',
            'source_employee_name' => 'Pending Worker', 'display_name' => 'Pending Worker',
            'employment_status' => 'active', 'is_payroll_active' => true, 'group_name' => 1,
        ]);

        PayrollAttendanceAdjustment::query()->create([
            'employee_biometric_id' => $employee->id, 'employee_name' => 'Pending Worker',
            'work_date' => '2026-10-10', 'adjustment_type' => PayrollAttendanceAdjustment::TYPE_OVERTIME,
            'status' => PayrollAttendanceAdjustment::STATUS_PENDING,
            'adjusted_time_in' => '18:00', 'adjusted_time_out' => '20:00', 'reason' => 'Rush order',
        ]);

        $repository = app(AttendanceAdjustmentRepositoryInterface::class);

        $this->assertTrue(
            $repository->overlapping([PayrollAttendanceAdjustment::TYPE_OVERTIME], $employee->id, '2026-10-10', '2026-10-10', null),
            'A still-pending adjustment must keep blocking a duplicate filing.'
        );
    }
}
