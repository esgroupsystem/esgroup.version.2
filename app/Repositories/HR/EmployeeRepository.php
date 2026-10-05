<?php

declare(strict_types=1);

namespace App\Repositories\HR;

use App\Models\Employee;
use App\Models\EmployeeAsset;
use App\Repositories\Contracts\HR\EmployeeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class EmployeeRepository implements EmployeeRepositoryInterface
{
    public function paginateDirectory(string $search, array $exact, int $perPage, array $appends): LengthAwarePaginator
    {
        return Employee::query()
            ->with(['position', 'department'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%")
                        ->orWhere('employee_id_permanent', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%")
                        ->orWhere('garage', 'like', "%{$search}%")
                        ->orWhereHas('position', fn (Builder $position) => $position->where('title', 'like', "%{$search}%"))
                        ->orWhereHas('department', fn (Builder $department) => $department->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($exact !== [], fn (Builder $query) => $query->where($exact))
            ->orderBy('full_name')
            ->paginate($perPage)
            ->appends($appends);
    }

    public function count(): int
    {
        return Employee::query()->count();
    }

    public function countWithStatus(array $statuses): int
    {
        return Employee::query()->whereIn('status', $statuses)->count();
    }

    public function countDistinct(string $column): int
    {
        return $this->nonBlank($column)->distinct()->count($column);
    }

    public function distinctValues(string $column): Collection
    {
        return $this->nonBlank($column)->distinct()->orderBy($column)->pluck($column);
    }

    public function options(): Collection
    {
        return Employee::query()->select('id', 'full_name')->orderBy('full_name')->get();
    }

    public function leaveCandidates(?string $position, array $excludedPositions, array $statuses, ?int $includeId): Collection
    {
        return Employee::query()
            ->with('position')
            ->when($position !== null, fn (Builder $query) => $query->whereHas('position', fn (Builder $title) => $title->where('title', $position)))
            ->when($position === null, fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->whereDoesntHave('position')
                ->orWhereHas('position', fn (Builder $title) => $title->whereNotIn('title', $excludedPositions))))
            ->where(function (Builder $query) use ($statuses, $includeId): void {
                $query->whereIn('status', $statuses);
                if ($includeId !== null) {
                    $query->orWhere('id', $includeId);
                }
            })
            ->orderBy('garage')
            ->orderBy('full_name')
            ->get();
    }

    public function findOrFail(int $id, array $with = []): Employee
    {
        return Employee::query()->with($with)->findOrFail($id);
    }

    public function findForUpdate(int $id, array $with = []): ?Employee
    {
        return Employee::query()->with($with)->lockForUpdate()->find($id);
    }

    public function highestIdForUpdate(): int
    {
        return (int) (Employee::query()->lockForUpdate()->max('id') ?? 0);
    }

    public function create(array $attributes): Employee
    {
        return Employee::query()->create($attributes);
    }

    public function update(Employee $employee, array $attributes): void
    {
        $employee->update($attributes);
    }

    public function delete(Employee $employee): void
    {
        $employee->delete();
    }

    public function permanentIdTaken(string $permanentId, ?int $ignoreId): bool
    {
        return Employee::query()
            ->where('employee_id_permanent', $permanentId)
            ->when($ignoreId !== null, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    public function biometricLinkCandidates(int $biometricId): Collection
    {
        return Employee::query()
            ->with('position')
            ->where(fn (Builder $query) => $query->whereNull('employee_biometric_id')->orWhere('employee_biometric_id', $biometricId))
            ->get(['id', 'full_name', 'employee_id_permanent', 'position_id', 'status', 'employee_biometric_id']);
    }

    public function moveBiometricLink(int $biometricId, ?int $employeeId): void
    {
        Employee::query()
            ->where('employee_biometric_id', $biometricId)
            ->when($employeeId !== null, fn (Builder $query) => $query->whereKeyNot($employeeId))
            ->update(['employee_biometric_id' => null]);

        if ($employeeId !== null) {
            Employee::query()->whereKey($employeeId)->update(['employee_biometric_id' => $biometricId]);
        }
    }

    public function assetOf(Employee $employee): EmployeeAsset
    {
        return $employee->asset ?? $employee->asset()->create([]);
    }

    public function load(Employee $employee, array $relations): Employee
    {
        return $employee->load($relations);
    }

    /** @return Builder<Employee> */
    private function nonBlank(string $column): Builder
    {
        return Employee::query()->whereNotNull($column)->where($column, '!=', '');
    }
}
