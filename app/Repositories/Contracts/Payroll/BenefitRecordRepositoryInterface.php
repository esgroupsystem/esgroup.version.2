<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Payroll;

use App\Models\BenefitContributionRecord;
use App\Models\EmployeeBiometric;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Benefits Records ledger: posted monthly government contributions and the people they
 * belong to. "Ledger people" = payroll-active people plus anyone with a record that month
 * (so separated employees stay visible), within the user's groups, optional group and search.
 */
interface BenefitRecordRepositoryInterface
{
    /**
     * @param  string|list<int|string>|null  $allowedGroups
     * @return LengthAwarePaginator<int, EmployeeBiometric>
     */
    public function paginatePeople(int $month, int $year, string $search, ?int $group, string|array|null $allowedGroups, int $perPage = 25): LengthAwarePaginator;

    /**
     * @param  string|list<int|string>|null  $allowedGroups
     * @return Collection<int, EmployeeBiometric>
     */
    public function people(int $month, int $year, string $search, ?int $group, string|array|null $allowedGroups): Collection;

    /** @param string|list<int|string>|null $allowedGroups payroll-active people within the scope */
    public function countActivePeople(string $search, ?int $group, string|array|null $allowedGroups): int;

    /**
     * The month's records for these people, with their payroll.
     *
     * @param  Collection<int, int>  $employeeBiometricIds
     * @param  'posted'|'company'  $order  by posting time, or by company / name / period
     * @return Collection<int, BenefitContributionRecord>
     */
    public function recordsFor(int $month, int $year, Collection $employeeBiometricIds, string $order): Collection;

    /**
     * Summed totals of every ledger person's records for the month.
     *
     * @param  string|list<int|string>|null  $allowedGroups
     */
    public function totals(int $month, int $year, string $search, ?int $group, string|array|null $allowedGroups): object;

    /**
     * People with at least one record that month (only payroll-active people when $activeOnly).
     *
     * @param  string|list<int|string>|null  $allowedGroups
     */
    public function countPostedPeople(int $month, int $year, string $search, ?int $group, string|array|null $allowedGroups, bool $activeOnly): int;
}
