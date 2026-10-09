<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Payroll;

use App\Models\Payroll;
use App\Models\PayrollAttendanceAdjustment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Payroll attendance adjustments (leave, offset, OT, schedule change, disaster, salary adjustment).
 * List filters: search, type, group_name, date_from, date_to (on COALESCE(date_from, work_date)).
 */
interface AttendanceAdjustmentRepositoryInterface
{
    /**
     * @param  array{search: string, type: string, group_name: string, date_from: string, date_to: string}  $filters
     * @return LengthAwarePaginator<int, PayrollAttendanceAdjustment> newest date first
     */
    public function paginate(array $filters, string $status, int $perPage = 15): LengthAwarePaginator;

    /**
     * Rows per status under the list filters (ignores the status filter).
     *
     * @param  array{search: string, type: string, group_name: string, date_from: string, date_to: string}  $filters
     * @return Collection<string, int>
     */
    public function countByStatus(array $filters): Collection;

    /**
     * Rows under the list filters and status, optionally only these types / this status.
     *
     * @param  array{search: string, type: string, group_name: string, date_from: string, date_to: string}  $filters
     * @param  list<string>|null  $types
     */
    public function count(array $filters, string $status, ?array $types = null, ?string $onlyStatus = null): int;

    /**
     * A pending or approved adjustment of the same kind overlapping the dates (rejected rows never
     * count — they have no effect on payroll and must not block a new filing). Disaster types are
     * one per work date for everybody; other types are per employee and date range.
     *
     * @param  list<string>  $types
     */
    public function overlapping(array $types, ?int $employeeBiometricId, string $dateFrom, string $dateTo, ?int $ignoreId): bool;

    /** @return Collection<int, PayrollAttendanceAdjustment> non-rejected offsets that may use $date as a source */
    public function offsetsUsingSourceDate(int $employeeBiometricId, string $date, ?int $ignoreId): Collection;

    /**
     * Pending / approved OT filings whose work_date is $date or an adjacent calendar day.
     * An overnight filing is stored under its start day, so a filing that actually overlaps
     * $date in real time can have a work_date one day before or after it.
     *
     * @return Collection<int, PayrollAttendanceAdjustment>
     */
    public function overtimeFilingsOn(int $employeeBiometricId, string $date, ?int $ignoreId): Collection;

    /**
     * Pending OT / offset adjustments in the period for these employees.
     *
     * @param  list<int>  $employeeBiometricIds
     */
    public function countPendingApproval(string $start, string $end, array $employeeBiometricIds): int;

    /**
     * Approved adjustments touching the payroll's period and employees (or any disaster) that
     * changed after the payroll was generated and are not already paid in it.
     *
     * @param  list<int>  $employeeBiometricIds
     */
    public function countApprovedChangedSince(Payroll $payroll, string $start, string $end, array $employeeBiometricIds): int;

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): PayrollAttendanceAdjustment;

    /** @param array<string, mixed> $attributes */
    public function update(PayrollAttendanceAdjustment $adjustment, array $attributes): void;

    public function delete(PayrollAttendanceAdjustment $adjustment): void;
}
