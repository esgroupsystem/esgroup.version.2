<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\General;

use App\Enums\LeaveKind;
use App\Models\DriverLeave;
use App\Models\Employee;
use App\Models\Holiday;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Read-only aggregates for the hidden HR dashboard (`hr.dashboard`) and the All Data report
 * (`chairman.hr-data.index`). Counts and breakdowns only; writes live in the HR repositories.
 */
interface HrReportRepositoryInterface
{
    public function employeeCount(): int;

    /** Employees whose status is "active" (any case). */
    public function activeEmployeeCount(): int;

    /** @return Collection<int, object{label: string, total: int}> blank status counts as "Unknown" */
    public function employeeStatusSummary(): Collection;

    /** @return Collection<int, object> departments by name with total_employees, active_employees, other_status_employees */
    public function departmentSummary(): Collection;

    /** @return Collection<int, Employee> employees with the most history (IR) records, with latestHistory.offense */
    public function employeesWithMostHistory(int $limit): Collection;

    /**
     * Leaves of one kind that start in $year.
     *
     * @return array{total: int, total_days: float, status_breakdown: Collection<int, object>, type_breakdown: Collection<int, object>, recent: Collection<int, \App\Models\LeaveRecord>}
     */
    public function leaveReport(LeaveKind $kind, int $year): array;

    /** @return Collection<int, Holiday> active holidays observed in $year, by date */
    public function holidaysObservedIn(int $year): Collection;

    /**
     * @param  array{q: string, department: string, position: string, status: string, company: string}  $filters
     * @return LengthAwarePaginator<int, Employee>
     */
    public function paginateEmployees(array $filters, int $perPage = 20): LengthAwarePaginator;

    /** Employees on a non-cancelled driver leave covering $day. */
    public function employeesOnDriverLeave(CarbonInterface $day): int;

    /** @return array{first: int, second: int, termination: int} driver leaves by offense_level 1, 2, 3+ */
    public function driverOffenseLevels(): array;

    /** @return array{active: int, not_started: int, ongoing: int, expired_today: int, cancelled: int, completed: int} */
    public function driverLeaveSummary(CarbonInterface $day): array;

    /** @return Collection<int, DriverLeave> latest updated driver leaves, with employee */
    public function recentDriverLeaves(int $limit, bool $offensesOnly = false): Collection;

    /** @return Collection<int, Employee> */
    public function recentlyUpdatedEmployees(int $limit): Collection;

    /** @return list<array{label: string, value: int}> headcount per department, largest first */
    public function employeesByDepartment(): array;

    /** @return list<array{label: string, value: int}> driver leaves per leave type, largest first */
    public function driverLeavesByType(): array;

    /** @return array{departments: Collection<int, array{value: string, label: string}>, positions: Collection<int, array{value: string, label: string}>, statuses: Collection<int, string>} */
    public function employeeFilterOptions(): array;
}
