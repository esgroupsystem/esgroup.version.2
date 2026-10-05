<?php

declare(strict_types=1);

namespace App\Services\HR;

use App\Enums\HrPositionType;
use App\Enums\LeaveKind;
use App\Enums\LeaveStatus;
use App\Models\Employee;
use App\Models\LeaveRecord;
use App\Repositories\Contracts\HR\EmployeeRepositoryInterface;
use App\Repositories\Contracts\HR\LeaveRepositoryInterface;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Leaves → Admin / Driver / Conductor: list counts, eligible employees, and creating or
 * editing a leave (which moves the employee to On Leave and back).
 */
final class LeaveService
{
    /** Employee statuses that can be given a leave. */
    private const ELIGIBLE_STATUSES = ['Active', 'Active(Re-Entry)'];

    public function __construct(
        private readonly LeaveRepositoryInterface $leaves,
        private readonly EmployeeRepositoryInterface $employees,
    ) {}

    /** @return LengthAwarePaginator<int, LeaveRecord> */
    public function paginate(LeaveKind $kind, string $search, string $status, string $leaveType, string $garage): LengthAwarePaginator
    {
        return $this->leaves->paginate($kind, $search, $status, $leaveType, $garage);
    }

    /**
     * Count cards and the per-garage summary, over every record matching the search
     * (the status / type / garage filters do not change them).
     *
     * @return array{counts: array<string, int>, garageSummary: Collection<int, array<string, int|string>>}
     */
    public function summary(LeaveKind $kind, string $search): array
    {
        $all = $this->leaves->allMatching($kind, $search);
        $counts = ['active' => 0, 'first' => 0, 'second' => 0, 'inactive' => 0, 'termination' => 0, 'completed' => 0, 'cancelled' => 0, 'total' => $all->count()];

        foreach ($all as $leave) {
            $level = (int) ($leave->offense_level ?? 0);
            $status = $this->status($leave);

            if (in_array($status, ['inactive', 'completed', 'cancelled'], true)) {
                $counts[$status]++;
            }
            if ($level === 1) {
                $counts['first']++;
            } elseif ($level === 2) {
                $counts['second']++;
            } elseif ($level >= 3 || $status === 'terminated') {
                $counts['termination']++;
            } elseif (! in_array($status, ['cancelled', 'terminated', 'completed', 'inactive'], true)) {
                $counts['active']++;
            }
        }

        $garageSummary = $all
            ->groupBy(fn (LeaveRecord $leave): string => $leave->employee?->garage ?: 'No Garage Assigned')
            ->map(fn (Collection $items, int|string $garage): array => [
                'garage' => (string) $garage,
                'total' => $items->count(),
                'active' => $items->filter(fn (LeaveRecord $leave): bool => in_array($this->status($leave), ['', 'active', 'on_leave'], true))->count(),
                'first_notice' => $items->where('offense_level', 1)->count(),
                'second_notice' => $items->where('offense_level', 2)->count(),
                'inactive' => $items->filter(fn (LeaveRecord $leave): bool => $this->status($leave) === 'inactive')->count(),
                'terminated' => $items->filter(fn (LeaveRecord $leave): bool => $this->status($leave) === 'terminated')->count(),
            ])
            ->sortBy('garage')
            ->values();

        return ['counts' => $counts, 'garageSummary' => $garageSummary];
    }

    /**
     * Employees offered in the form: active, holding the kind's position (admin: any other
     * position), plus the employee already on the record being edited.
     *
     * @return Collection<int, Employee>
     */
    public function candidates(LeaveKind $kind, ?LeaveRecord $current = null): Collection
    {
        return $this->employees->leaveCandidates(
            $kind->requiredPosition()?->value,
            [HrPositionType::Driver->value, HrPositionType::Conductor->value],
            self::ELIGIBLE_STATUSES,
            $current?->employee_id !== null ? (int) $current->employee_id : null,
        );
    }

    /** @param list<string> $with */
    public function find(LeaveKind $kind, int $id, array $with = []): LeaveRecord
    {
        return $this->leaves->findOrFail($kind, $id, $with);
    }

    /** Absolute path of a notice proof ("first", "second", "final"); 404 when missing. */
    public function proofPath(LeaveKind $kind, int $id, string $type): string
    {
        $path = $this->find($kind, $id)->proofPath($type);
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->path($path);
    }

    /**
     * @param  array{employee_id: int|string, leave_type: string, start_date: string, end_date: string, reason?: string|null}  $data
     *
     * @throws DomainException when the employee may not take this kind of leave
     */
    public function create(LeaveKind $kind, array $data): LeaveRecord
    {
        return DB::transaction(function () use ($kind, $data): LeaveRecord {
            $employee = $this->lockEligibleEmployee($kind, (int) $data['employee_id']);

            $leave = $this->leaves->create($kind, [
                'employee_id' => $employee->id,
                ...$this->leaveFields($data),
                'offense_level' => 0,
                'status' => LeaveStatus::Active->value,
            ]);

            $this->employees->update($employee, ['status' => 'On Leave']);

            return $leave;
        });
    }

    /**
     * Saves the leave; when the employee changes, the old one returns to Active.
     *
     * @param  array{employee_id: int|string, leave_type: string, start_date: string, end_date: string, reason?: string|null}  $data
     *
     * @throws DomainException when the employee may not take this kind of leave
     */
    public function update(LeaveKind $kind, LeaveRecord $leave, array $data): void
    {
        DB::transaction(function () use ($kind, $leave, $data): void {
            $record = $this->leaves->findForUpdate($kind, $leave->id);
            $oldEmployeeId = (int) $record->employee_id;
            $newEmployee = $this->lockEligibleEmployee($kind, (int) $data['employee_id'], $oldEmployeeId);

            $this->leaves->update($record, ['employee_id' => $newEmployee->id, ...$this->leaveFields($data)]);

            if ($oldEmployeeId !== (int) $newEmployee->id) {
                $oldEmployee = $this->employees->findForUpdate($oldEmployeeId);
                if ($oldEmployee !== null) {
                    $this->employees->update($oldEmployee, ['status' => 'Active']);
                }
                $this->employees->update($newEmployee, [
                    'status' => $this->status($record) === 'inactive' ? 'Inactive' : 'On Leave',
                ]);
            }
        });
    }

    /**
     * @param  array{leave_type: string, start_date: string, end_date: string, reason?: string|null}  $data
     * @return array<string, mixed>
     */
    private function leaveFields(array $data): array
    {
        return [
            'leave_type' => $data['leave_type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'days' => CarbonImmutable::parse($data['start_date'])->diffInDays(CarbonImmutable::parse($data['end_date'])) + 1,
            'reason' => $data['reason'] ?? null,
        ];
    }

    private function lockEligibleEmployee(LeaveKind $kind, int $employeeId, ?int $currentEmployeeId = null): Employee
    {
        $employee = $this->employees->findForUpdate($employeeId, ['position']);
        if ($employee === null) {
            throw new DomainException('The selected employee no longer exists.');
        }

        $required = $kind->requiredPosition()?->value;
        $title = $employee->position?->title;

        if ($required !== null && $title !== $required) {
            throw new DomainException("The selected employee is not assigned to the {$required} position.");
        }

        if ($required === null && in_array($title, [HrPositionType::Driver->value, HrPositionType::Conductor->value], true)) {
            throw new DomainException('Driver and Conductor employees must use their dedicated leave modules.');
        }

        if ($employee->id !== $currentEmployeeId && ! in_array((string) $employee->status, [...self::ELIGIBLE_STATUSES, 'On Leave'], true)) {
            throw new DomainException('The selected employee is not currently eligible for leave assignment.');
        }

        return $employee;
    }

    private function status(LeaveRecord $leave): string
    {
        return strtolower((string) ($leave->status ?? ''));
    }
}
