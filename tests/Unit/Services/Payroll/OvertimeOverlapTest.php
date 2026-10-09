<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Payroll;

use App\Models\EmployeeBiometric;
use App\Models\PayrollAttendanceAdjustment;
use App\Services\Payroll\OvertimeCheckService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Overnight OT filings (e.g. 10:00 PM-2:00 AM) are stored with work_date = the start day.
 * The overlap check must still catch a second filing on the *next* calendar day whose
 * window actually overlaps in real time, not just filings sharing the same work_date.
 */
final class OvertimeOverlapTest extends TestCase
{
    use RefreshDatabase;

    public function test_overnight_ot_overlap_is_caught_across_the_midnight_boundary(): void
    {
        $employee = EmployeeBiometric::query()->create([
            'source_key' => 'main:7001', 'source_crosschex_account' => 'main', 'source_employee_no' => '7001',
            'source_employee_name' => 'Overnight Worker', 'display_name' => 'Overnight Worker',
            'employment_status' => 'active', 'is_payroll_active' => true, 'group_name' => 1,
        ]);

        // Existing approved OT: Oct 10, 10:00 PM - 2:00 AM (work_date is the start day, Oct 10).
        PayrollAttendanceAdjustment::query()->create([
            'employee_biometric_id' => $employee->id, 'employee_name' => 'Overnight Worker',
            'work_date' => '2026-10-10', 'adjustment_type' => PayrollAttendanceAdjustment::TYPE_OVERTIME,
            'status' => PayrollAttendanceAdjustment::STATUS_APPROVED,
            'adjusted_time_in' => '22:00', 'adjusted_time_out' => '02:00', 'reason' => 'Rush order',
        ]);

        // New filing: Oct 11, 1:00 AM - 5:00 AM. Overlaps the existing OT by 1:00-2:00 AM,
        // but its own work_date is Oct 11, one day after the existing filing's work_date.
        $result = app(OvertimeCheckService::class)->check(
            employeeBiometricId: $employee->id,
            biometricEmployeeId: null,
            employeeNo: '7001',
            employeeName: 'Overnight Worker',
            workDate: '2026-10-11',
            timeIn: '01:00',
            timeOut: '05:00',
        );

        $this->assertFalse($result['ok']);
        $this->assertTrue(
            collect($result['errors'])->contains(fn (string $error): bool => str_contains($error, 'overlaps another')),
            'Expected an overlap error; got: '.json_encode($result['errors'])
        );
    }

    public function test_adjacent_day_filings_that_do_not_actually_overlap_are_not_flagged(): void
    {
        $employee = EmployeeBiometric::query()->create([
            'source_key' => 'main:7002', 'source_crosschex_account' => 'main', 'source_employee_no' => '7002',
            'source_employee_name' => 'Day Worker', 'display_name' => 'Day Worker',
            'employment_status' => 'active', 'is_payroll_active' => true, 'group_name' => 1,
        ]);

        // Existing OT: Oct 10, 6:00 PM - 8:00 PM (same-day, finishes well before midnight).
        PayrollAttendanceAdjustment::query()->create([
            'employee_biometric_id' => $employee->id, 'employee_name' => 'Day Worker',
            'work_date' => '2026-10-10', 'adjustment_type' => PayrollAttendanceAdjustment::TYPE_OVERTIME,
            'status' => PayrollAttendanceAdjustment::STATUS_APPROVED,
            'adjusted_time_in' => '18:00', 'adjusted_time_out' => '20:00', 'reason' => 'Rush order',
        ]);

        // New filing: Oct 11, 6:00 AM - 8:00 AM. Adjacent calendar day, no real overlap.
        $result = app(OvertimeCheckService::class)->check(
            employeeBiometricId: $employee->id,
            biometricEmployeeId: null,
            employeeNo: '7002',
            employeeName: 'Day Worker',
            workDate: '2026-10-11',
            timeIn: '06:00',
            timeOut: '08:00',
        );

        $this->assertFalse(
            collect($result['errors'])->contains(fn (string $error): bool => str_contains($error, 'overlaps another')),
            'Did not expect an overlap error; got: '.json_encode($result['errors'])
        );
    }
}
