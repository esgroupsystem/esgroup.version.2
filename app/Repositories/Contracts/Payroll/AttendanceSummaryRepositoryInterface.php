<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Payroll;

use App\Models\DailyAttendanceSummary;
use App\Models\PayrollItem;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Daily attendance summaries (one row per payroll-active person and day), as built by
 * DailyAttendanceSummaryService. List filters: search, status, day_type, group_name.
 */
interface AttendanceSummaryRepositoryInterface
{
    /**
     * @param  array{search: string, status: string, day_type: string, group_name: string}  $filters
     * @return LengthAwarePaginator<int, DailyAttendanceSummary> by date, then name
     */
    public function paginate(CarbonInterface $start, CarbonInterface $end, array $filters, int $perPage = 25): LengthAwarePaginator;

    /**
     * @param  array{search: string, status: string, day_type: string, group_name: string}  $filters
     * @return Collection<int, DailyAttendanceSummary> by name, then date
     */
    public function allMatching(CarbonInterface $start, CarbonInterface $end, array $filters): Collection;

    /**
     * Every count and total of the stat cards, in one query.
     *
     * @param  array{search: string, status: string, day_type: string, group_name: string}  $filters
     * @return array<string, int|float>
     */
    public function stats(CarbonInterface $start, CarbonInterface $end, array $filters): array;

    /**
     * @param  Collection<int, int>  $employeeBiometricIds
     * @return Collection<int, int> the ids that have at least one row in the period
     */
    public function employeeIdsWithRows(CarbonInterface $start, CarbonInterface $end, Collection $employeeBiometricIds): Collection;

    public function forEmployeeOn(int $employeeBiometricId, string $date): ?DailyAttendanceSummary;

    /**
     * A payroll item's rows in the period, matched by biometric id, else by legacy number / name.
     *
     * @return Collection<int, DailyAttendanceSummary>
     */
    public function forPayrollItem(string $start, string $end, PayrollItem $item): Collection;
}
