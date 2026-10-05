<?php

declare(strict_types=1);

namespace Tests\Feature\HRDepartment;

use App\Models\Department;
use App\Models\DriverLeave;
use App\Models\Employee;
use App\Models\Position;
use App\Services\HR_Department\DepartmentService;
use App\Services\HR_Department\LeaveRecordService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HrReferenceAndLeaveArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_department_service_creates_department_and_position(): void
    {
        $service = app(DepartmentService::class);
        $department = $service->createDepartment('Operations');
        $position = $service->createPosition($department->id, 'Dispatcher');

        self::assertSame('Operations', $department->name);
        self::assertSame('Dispatcher', $position->title);
        self::assertTrue($department->fresh()?->positions->contains($position) ?? false);
    }

    public function test_driver_leave_service_creates_leave_and_marks_employee_on_leave(): void
    {
        [$employee] = $this->makeEmployee('Driver');

        $leave = app(LeaveRecordService::class)->createDriverLeave([
            'employee_id' => $employee->id,
            'leave_type' => 'Vacation',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-03',
            'reason' => 'Approved vacation',
        ]);

        self::assertInstanceOf(DriverLeave::class, $leave);
        self::assertSame(3, $leave->days);
        self::assertSame('Active', $leave->status);
        self::assertSame('On Leave', $employee->refresh()->status);
    }

    public function test_driver_leave_service_rejects_employee_from_wrong_position(): void
    {
        [$employee] = $this->makeEmployee('Conductor');

        $this->expectException(DomainException::class);
        app(LeaveRecordService::class)->createDriverLeave([
            'employee_id' => $employee->id,
            'leave_type' => 'Vacation',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-01',
            'reason' => null,
        ]);
    }

    public function test_general_employee_leave_rejects_driver_and_conductor_positions(): void
    {
        [$employee] = $this->makeEmployee('Driver');

        $this->expectException(DomainException::class);
        app(LeaveRecordService::class)->createEmployeeLeave([
            'employee_id' => $employee->id,
            'leave_type' => 'Vacation',
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-01',
            'reason' => null,
        ]);
    }

    /** @return array{Employee, Department, Position} */
    private function makeEmployee(string $positionTitle): array
    {
        $department = Department::query()->create(['name' => 'HR '.$positionTitle]);
        $position = Position::query()->create([
            'department_id' => $department->id,
            'title' => $positionTitle,
        ]);
        $employee = Employee::query()->create([
            'employee_id' => 'EMP-'.strtoupper(substr($positionTitle, 0, 3)).'-'.uniqid(),
            'full_name' => 'Test '.$positionTitle,
            'department_id' => $department->id,
            'position_id' => $position->id,
            'status' => 'Active',
        ]);

        return [$employee, $department, $position];
    }
}
