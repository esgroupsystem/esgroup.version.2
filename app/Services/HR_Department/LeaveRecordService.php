<?php

declare(strict_types=1);

namespace App\Services\HR_Department;

use App\Enums\HrPositionType;
use App\Enums\LeaveStatus;
use App\Models\ConductorLeave;
use App\Models\DriverLeave;
use App\Models\Employee;
use App\Models\EmployeeLeave;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class LeaveRecordService
{
    /**
     * @param  array{employee_id:int|string,leave_type:string,start_date:string,end_date:string,reason?:string|null}  $data
     */
    public function createDriverLeave(array $data): DriverLeave
    {
        /** @var DriverLeave $leave */
        $leave = $this->create(DriverLeave::class, $data, HrPositionType::Driver->value);

        return $leave;
    }

    /** @param array{employee_id:int|string,leave_type:string,start_date:string,end_date:string,reason?:string|null} $data */
    public function createConductorLeave(array $data): ConductorLeave
    {
        /** @var ConductorLeave $leave */
        $leave = $this->create(ConductorLeave::class, $data, HrPositionType::Conductor->value);

        return $leave;
    }

    /** @param array{employee_id:int|string,leave_type:string,start_date:string,end_date:string,reason?:string|null} $data */
    public function createEmployeeLeave(array $data): EmployeeLeave
    {
        /** @var EmployeeLeave $leave */
        $leave = $this->create(EmployeeLeave::class, $data, null);

        return $leave;
    }

    /** @param array{employee_id:int|string,leave_type:string,start_date:string,end_date:string,reason?:string|null} $data */
    public function updateDriverLeave(DriverLeave $leave, array $data): void
    {
        $this->update($leave, $data, HrPositionType::Driver->value);
    }

    /** @param array{employee_id:int|string,leave_type:string,start_date:string,end_date:string,reason?:string|null} $data */
    public function updateConductorLeave(ConductorLeave $leave, array $data): void
    {
        $this->update($leave, $data, HrPositionType::Conductor->value);
    }

    /** @param array{employee_id:int|string,leave_type:string,start_date:string,end_date:string,reason?:string|null} $data */
    public function updateEmployeeLeave(EmployeeLeave $leave, array $data): void
    {
        $this->update($leave, $data, null);
    }

    /**
     * @param  class-string<DriverLeave|ConductorLeave|EmployeeLeave>  $leaveClass
     * @param  array{employee_id:int|string,leave_type:string,start_date:string,end_date:string,reason?:string|null}  $data
     */
    private function create(string $leaveClass, array $data, ?string $requiredPosition): Model
    {
        return DB::transaction(function () use ($leaveClass, $data, $requiredPosition): Model {
            $employee = $this->lockEligibleEmployee((int) $data['employee_id'], $requiredPosition);
            $days = CarbonImmutable::parse($data['start_date'])->diffInDays(CarbonImmutable::parse($data['end_date'])) + 1;

            /** @var DriverLeave|ConductorLeave|EmployeeLeave $leave */
            $leave = $leaveClass::query()->create([
                'employee_id' => $employee->id,
                'leave_type' => $data['leave_type'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'days' => $days,
                'reason' => $data['reason'] ?? null,
                'offense_level' => 0,
                'status' => LeaveStatus::Active->value,
            ]);

            $employee->update(['status' => 'On Leave']);

            return $leave;
        });
    }

    /** @param array{employee_id:int|string,leave_type:string,start_date:string,end_date:string,reason?:string|null} $data */
    private function update(DriverLeave|ConductorLeave|EmployeeLeave $leave, array $data, ?string $requiredPosition): void
    {
        DB::transaction(function () use ($leave, $data, $requiredPosition): void {
            $record = $leave::query()->lockForUpdate()->findOrFail($leave->getKey());
            $oldEmployeeId = (int) $record->employee_id;
            $newEmployee = $this->lockEligibleEmployee((int) $data['employee_id'], $requiredPosition, $oldEmployeeId);
            $days = CarbonImmutable::parse($data['start_date'])->diffInDays(CarbonImmutable::parse($data['end_date'])) + 1;

            $record->update([
                'employee_id' => $newEmployee->id,
                'leave_type' => $data['leave_type'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'days' => $days,
                'reason' => $data['reason'] ?? null,
            ]);

            if ($oldEmployeeId !== (int) $newEmployee->id) {
                Employee::query()->whereKey($oldEmployeeId)->lockForUpdate()->first()?->update(['status' => 'Active']);
                $newEmployee->update([
                    'status' => strtolower((string) $record->status) === 'inactive' ? 'Inactive' : 'On Leave',
                ]);
            }
        });
    }

    private function lockEligibleEmployee(int $employeeId, ?string $requiredPosition, ?int $currentEmployeeId = null): Employee
    {
        $employee = Employee::query()->with('position')->lockForUpdate()->findOrFail($employeeId);

        if ($requiredPosition !== null && $employee->position?->title !== $requiredPosition) {
            throw new DomainException("The selected employee is not assigned to the {$requiredPosition} position.");
        }

        if ($requiredPosition === null && in_array($employee->position?->title, [HrPositionType::Driver->value, HrPositionType::Conductor->value], true)) {
            throw new DomainException('Driver and Conductor employees must use their dedicated leave modules.');
        }

        $allowedStatuses = ['Active', 'Active(Re-Entry)', 'On Leave'];
        if ($employee->id !== $currentEmployeeId && ! in_array((string) $employee->status, $allowedStatuses, true)) {
            throw new DomainException('The selected employee is not currently eligible for leave assignment.');
        }

        return $employee;
    }
}
