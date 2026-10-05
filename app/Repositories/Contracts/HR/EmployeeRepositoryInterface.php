<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\HR;

use App\Models\Employee;
use App\Models\EmployeeAsset;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface EmployeeRepositoryInterface
{
    /**
     * Employee List: search across name, IDs, contact, company, garage, position and department,
     * plus exact `status` / `company` / `garage` filters. By name, with position and department.
     *
     * @param  array<string, string>  $exact
     * @param  array<string, mixed>  $appends  query string kept on the page links
     * @return LengthAwarePaginator<int, Employee>
     */
    public function paginateDirectory(string $search, array $exact, int $perPage, array $appends): LengthAwarePaginator;

    public function count(): int;

    /** @param list<string> $statuses */
    public function countWithStatus(array $statuses): int;

    public function countDistinct(string $column): int;

    /** @return Collection<int, string> distinct non-blank values, sorted */
    public function distinctValues(string $column): Collection;

    /** @return Collection<int, Employee> id and full_name, by name */
    public function options(): Collection;

    /**
     * Employees who can be put on leave: in one of $statuses (or $includeId), holding
     * $position, or — when $position is null — any position except $excludedPositions.
     *
     * @param  list<string>  $statuses
     * @param  list<string>  $excludedPositions
     * @return Collection<int, Employee>
     */
    public function leaveCandidates(?string $position, array $excludedPositions, array $statuses, ?int $includeId): Collection;

    /** @param list<string> $with */
    public function findOrFail(int $id, array $with = []): Employee;

    /** @param list<string> $with */
    public function findForUpdate(int $id, array $with = []): ?Employee;

    /** Highest employee id (locked), used to number the next EMP-xxxx. */
    public function highestIdForUpdate(): int;

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Employee;

    /** @param array<string, mixed> $attributes */
    public function update(Employee $employee, array $attributes): void;

    public function delete(Employee $employee): void;

    public function permanentIdTaken(string $permanentId, ?int $ignoreId): bool;

    /**
     * Employees a biometric record can link to: unlinked, or already linked to it. With position.
     *
     * @return Collection<int, Employee>
     */
    public function biometricLinkCandidates(int $biometricId): Collection;

    /** Links the biometric record to exactly one employee (null = to nobody). */
    public function moveBiometricLink(int $biometricId, ?int $employeeId): void;

    /** The employee's 201 asset row, created empty when missing. */
    public function assetOf(Employee $employee): EmployeeAsset;

    /** @param list<string>|array<string, \Closure> $relations */
    public function load(Employee $employee, array $relations): Employee;
}
