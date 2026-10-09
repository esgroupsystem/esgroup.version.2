<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Maintenance;

use App\Models\JobOrderMaintenance;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Maintenance Job Orders, their status periods (downtime) and update history.
 * Filters: search, status, bus_id, date_filter (day / month / year) with filter_date / filter_month / filter_year.
 */
interface JobOrderMaintenanceRepositoryInterface
{
    /**
     * @param  array{search: string, status: string, bus_id: ?int, date_filter: string, filter_date: string, filter_month: string, filter_year: string}  $filters
     * @return LengthAwarePaginator<int, JobOrderMaintenance> newest first
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator;

    /**
     * Rows per status under the filters (ignores the status filter).
     *
     * @param  array{search: string, status: string, bus_id: ?int, date_filter: string, filter_date: string, filter_month: string, filter_year: string}  $filters
     * @return Collection<string, int>
     */
    public function countByStatus(array $filters): Collection;

    /**
     * Runs $callback on every filtered job order, newest first, 500 at a time.
     *
     * @param  array{search: string, status: string, bus_id: ?int, date_filter: string, filter_date: string, filter_month: string, filter_year: string}  $filters
     */
    public function eachForExport(array $filters, Closure $callback): void;

    /** @param list<string> $relations */
    public function load(JobOrderMaintenance $jobOrder, array $relations): JobOrderMaintenance;

    public function findForUpdate(int $id): JobOrderMaintenance;

    /** Last odometer reading encoded for the bus, or null. */
    public function lastOdometerReading(int $busId): ?int;

    /** Highest job order number with the prefix, including deleted ones (locked). */
    public function latestNumberLike(string $prefix): ?string;

    public function numberExists(string $jobOrderNo): bool;

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): JobOrderMaintenance;

    /** @param array<string, mixed> $attributes */
    public function update(JobOrderMaintenance $jobOrder, array $attributes): void;

    public function delete(JobOrderMaintenance $jobOrder): void;

    /** Ends every open status period at $endedAt. */
    public function endOpenPeriods(JobOrderMaintenance $jobOrder, \DateTimeInterface $endedAt): void;

    /** @param array<string, mixed> $attributes */
    public function startPeriod(JobOrderMaintenance $jobOrder, array $attributes): void;

    /** @param array<string, mixed> $attributes */
    public function addHistory(JobOrderMaintenance $jobOrder, array $attributes): void;
}
