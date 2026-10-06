<?php

declare(strict_types=1);

namespace App\Repositories\Biometrics;

use App\Models\Employee;
use App\Models\EmployeeBiometric;
use App\Models\EmployeePlottingSchedule;
use App\Repositories\Contracts\Biometrics\EmployeeBiometricRepositoryInterface;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class EmployeeBiometricRepository implements EmployeeBiometricRepositoryInterface
{
    private const DIRECTORY_SEARCH = [
        'display_employee_no', 'display_name', 'source_employee_no', 'source_employee_id', 'source_employee_name',
        'source_crosschex_id', 'source_crosschex_account_name', 'source_crosschex_account', 'group_name', 'device_name', 'device_sn',
    ];

    private const SCHEDULE_SEARCH = [
        'display_employee_no', 'display_name', 'source_employee_no', 'source_employee_id', 'source_employee_name',
        'source_crosschex_id', 'source_crosschex_account_name', 'source_crosschex_account', 'group_name',
    ];

    public function paginateDirectory(array $filters, int $perPage = 25): LengthAwarePaginator
    {
        $status = $filters['employment_status'];

        return EmployeeBiometric::query()
            ->with(['company', 'hrEmployee', 'permanentSchedule', 'activeSalaryProfile'])
            ->tap(fn (Builder $query) => $this->search($query, $filters['search'], self::DIRECTORY_SEARCH))
            ->when($status !== '', fn (Builder $query) => match ($status) {
                EmployeeBiometric::STATUS_ACTIVE => $query->payrollActive(),
                EmployeeBiometric::STATUS_INACTIVE => $query->inactive(),
                default => $query->where('employment_status', $status),
            })
            ->when($filters['biometric_company_id'] !== '', fn (Builder $query) => $query->where('biometric_company_id', (int) $filters['biometric_company_id']))
            ->when($filters['group_name'] !== '', fn (Builder $query) => $query->where('group_name', $filters['group_name']))
            ->when($filters['payroll_active'] !== '', fn (Builder $query) => $query->where('is_payroll_active', (bool) (int) $filters['payroll_active']))
            ->payrollDirectoryOrder()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateForSchedule(string $search, string $group, string $status, string $shift, int $perPage = 25): LengthAwarePaginator
    {
        return EmployeeBiometric::query()
            ->with(['company', 'permanentSchedule'])
            ->payrollActive()
            ->group($group)
            ->tap(fn (Builder $query) => $this->search($query, $search, self::SCHEDULE_SEARCH))
            ->when($status !== '', fn (Builder $query) => $this->wherePermanentSchedule($query, 'status', $status, EmployeePlottingSchedule::DEFAULT_STATUS))
            ->when($shift !== '', fn (Builder $query) => $this->wherePermanentSchedule($query, 'shift_name', $shift, EmployeePlottingSchedule::REGULAR_SHIFT))
            ->payrollDirectoryOrder()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function payrollActiveWithSchedule(string|array|null $allowedGroups): Collection
    {
        return EmployeeBiometric::query()
            ->with('permanentSchedule')
            ->payrollActive()
            ->when($allowedGroups !== 'all', fn (Builder $query) => empty($allowedGroups)
                ? $query->whereRaw('1 = 0')
                : $query->whereIn('group_name', (array) $allowedGroups))
            ->payrollDirectoryOrder()
            ->get();
    }

    public function payrollActive(string $group = '', ?Collection $ids = null): Collection
    {
        return EmployeeBiometric::query()
            ->payrollActive()
            ->when(trim($group) !== '', fn (Builder $query) => $query->where('group_name', trim($group)))
            ->when($ids !== null, fn (Builder $query) => $query->whereIn('id', $ids))
            ->payrollDirectoryOrder()
            ->get();
    }

    public function findManyInDirectoryOrder(Collection $ids): Collection
    {
        return EmployeeBiometric::query()->whereIn('id', $ids)->payrollDirectoryOrder()->get();
    }

    public function eachPayrollActiveChunk(int $size, Closure $callback): void
    {
        EmployeeBiometric::query()->payrollActive()->orderBy('id')->chunkById($size, $callback);
    }

    public function linkableToEmployee(int $employeeId): Collection
    {
        return EmployeeBiometric::query()
            ->with('company')
            ->whereNotIn('id', Employee::query()
                ->whereNotNull('employee_biometric_id')
                ->whereKeyNot($employeeId)
                ->select('employee_biometric_id'))
            ->get();
    }

    public function count(): int
    {
        return EmployeeBiometric::query()->count();
    }

    public function countPayrollActive(): int
    {
        return EmployeeBiometric::query()->payrollActive()->count();
    }

    public function countWithStatus(string $status): int
    {
        return EmployeeBiometric::query()->where('employment_status', $status)->count();
    }

    public function countInactive(): int
    {
        return EmployeeBiometric::query()->inactive()->count();
    }

    public function countWithoutCompany(): int
    {
        return EmployeeBiometric::query()->whereNull('biometric_company_id')->count();
    }

    public function groups(bool $payrollActiveOnly = false): Collection
    {
        return EmployeeBiometric::query()
            ->when($payrollActiveOnly, fn (Builder $query) => $query->payrollActive())
            ->whereNotNull('group_name')
            ->where('group_name', '!=', '')
            ->distinct()
            ->orderBy('group_name')
            ->pluck('group_name')
            ->map(fn ($group): string => (string) $group);
    }

    public function findOrFail(int $id): EmployeeBiometric
    {
        return EmployeeBiometric::query()->findOrFail($id);
    }

    public function find(int $id): ?EmployeeBiometric
    {
        return EmployeeBiometric::query()->find($id);
    }

    public function searchPayrollActive(string $search, int $limit): Collection
    {
        return EmployeeBiometric::query()
            ->payrollActive()
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('display_name', 'like', "%{$search}%")
                ->orWhere('source_employee_name', 'like', "%{$search}%")
                ->orWhere('display_employee_no', 'like', "%{$search}%")
                ->orWhere('source_employee_no', 'like', "%{$search}%")
                ->orWhere('source_employee_id', 'like', "%{$search}%")))
            ->payrollDirectoryOrder()
            ->limit($limit)
            ->get();
    }

    public function findPayrollActiveOrFail(int $id): EmployeeBiometric
    {
        return EmployeeBiometric::query()->payrollActive()->findOrFail($id);
    }

    public function findForUpdate(int $id): ?EmployeeBiometric
    {
        return EmployeeBiometric::query()->whereKey($id)->lockForUpdate()->first();
    }

    public function update(EmployeeBiometric $employee, array $attributes): void
    {
        $employee->update($attributes);
    }

    public function load(EmployeeBiometric $employee, array $relations): EmployeeBiometric
    {
        return $employee->load($relations);
    }

    /**
     * @param  Builder<EmployeeBiometric>  $query
     * @param  list<string>  $columns
     */
    private function search(Builder $query, string $search, array $columns): void
    {
        if ($search === '') {
            return;
        }

        $query->where(function (Builder $query) use ($search, $columns): void {
            foreach ($columns as $column) {
                $query->orWhere($column, 'like', "%{$search}%");
            }
        });
    }

    /**
     * Permanent schedule column equals $value; people without one count as $default.
     *
     * @param  Builder<EmployeeBiometric>  $query
     */
    private function wherePermanentSchedule(Builder $query, string $column, string $value, string $default): void
    {
        $query->where(function (Builder $query) use ($column, $value, $default): void {
            $query->whereHas('permanentSchedule', fn (Builder $schedule) => $schedule->where($column, $value));
            if ($value === $default) {
                $query->orWhereDoesntHave('permanentSchedule');
            }
        });
    }
}
