<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Biometrics;

use App\Models\EmployeeBiometric;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/** Biometric (CrossChex) employee records: the payroll identity of each person. */
interface EmployeeBiometricRepositoryInterface
{
    /**
     * Biometric Employees list. Filters: search, employment_status (active = payroll active),
     * biometric_company_id, group_name, payroll_active ("1"/"0"). Payroll directory order.
     *
     * @param  array{search: string, employment_status: string, biometric_company_id: string, group_name: string, payroll_active: string}  $filters
     * @return LengthAwarePaginator<int, EmployeeBiometric>
     */
    public function paginateDirectory(array $filters, int $perPage = 25): LengthAwarePaginator;

    /**
     * Work Schedule list: payroll-active people with their permanent schedule. A person with no
     * saved schedule counts as status "scheduled" and shift "Regular Shift".
     *
     * @return LengthAwarePaginator<int, EmployeeBiometric>
     */
    public function paginateForSchedule(string $search, string $group, string $status, string $shift, int $perPage = 25): LengthAwarePaginator;

    /**
     * Payroll-active people with their permanent schedule, limited to the user's payroll groups
     * ("all", or a list of group numbers).
     *
     * @param  string|list<int|string>|null  $allowedGroups
     * @return Collection<int, EmployeeBiometric>
     */
    public function payrollActiveWithSchedule(string|array|null $allowedGroups): Collection;

    /**
     * Payroll-active people in payroll directory order, optionally one group / these ids.
     *
     * @param  Collection<int, int>|null  $ids
     * @return Collection<int, EmployeeBiometric>
     */
    public function payrollActive(string $group = '', ?Collection $ids = null): Collection;

    /**
     * @param  Collection<int, int>  $ids
     * @return Collection<int, EmployeeBiometric> in payroll directory order
     */
    public function findManyInDirectoryOrder(Collection $ids): Collection;

    /** Runs $callback on every payroll-active person, $size at a time, by id. */
    public function eachPayrollActiveChunk(int $size, Closure $callback): void;

    /**
     * Records an HR employee can link to: not linked to anyone else. With company.
     *
     * @return Collection<int, EmployeeBiometric>
     */
    public function linkableToEmployee(int $employeeId): Collection;

    public function count(): int;

    public function countPayrollActive(): int;

    public function countWithStatus(string $status): int;

    public function countInactive(): int;

    public function countWithoutCompany(): int;

    /** @return Collection<int, string> distinct payroll groups in use */
    public function groups(bool $payrollActiveOnly = false): Collection;

    public function findOrFail(int $id): EmployeeBiometric;

    public function find(int $id): ?EmployeeBiometric;

    /**
     * Payroll-active people matching a name / employee no / id search, in directory order.
     *
     * @return Collection<int, EmployeeBiometric>
     */
    public function searchPayrollActive(string $search, int $limit): Collection;

    public function findPayrollActiveOrFail(int $id): EmployeeBiometric;

    public function findForUpdate(int $id): ?EmployeeBiometric;

    /** @param array<string, mixed> $attributes */
    public function update(EmployeeBiometric $employee, array $attributes): void;

    /** @param list<string>|array<string, Closure> $relations */
    public function load(EmployeeBiometric $employee, array $relations): EmployeeBiometric;
}
