<?php

declare(strict_types=1);

namespace App\Repositories\Scheduling;

use App\Models\EmployeeBiometric;
use App\Models\PayrollEmployeeSalary;
use App\Repositories\Contracts\Scheduling\EmployeeSalaryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class EmployeeSalaryRepository implements EmployeeSalaryRepositoryInterface
{
    public function paginateDirectory(string $search, string $group, string $employmentStatus, string|array|null $allowedGroups, int $perPage = 15): LengthAwarePaginator
    {
        return PayrollEmployeeSalary::query()
            ->with(['otherDeductions', 'employeeBiometric'])
            ->when($allowedGroups !== 'all', fn (Builder $query) => empty($allowedGroups)
                ? $query->whereRaw('1 = 0')
                : $query->whereHas('employeeBiometric', fn (Builder $person) => $person->whereIn('group_name', (array) $allowedGroups)))
            ->whereIn('id', fn ($query) => $query
                ->selectRaw('COALESCE(MAX(CASE WHEN basic_salary > 0 THEN id END), MIN(id))')
                ->from('payroll_employee_salaries')
                ->groupBy('employee_biometric_id'))
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('employee_name', 'like', "%{$search}%")
                ->orWhere('employee_no', 'like', "%{$search}%")
                ->orWhere('biometric_employee_id', 'like', "%{$search}%")
                ->orWhere('crosschex_id', 'like', "%{$search}%")
                ->orWhereHas('employeeBiometric', fn (Builder $person) => $person
                    ->where('display_name', 'like', "%{$search}%")
                    ->orWhere('display_employee_no', 'like', "%{$search}%")
                    ->orWhere('group_name', 'like', "%{$search}%"))))
            ->when($group !== '', fn (Builder $query) => $query->whereHas('employeeBiometric', fn (Builder $person) => $person->where('group_name', $group)))
            ->when($employmentStatus === EmployeeBiometric::STATUS_ACTIVE, fn (Builder $query) => $query->whereHas('employeeBiometric', fn (Builder $person) => $person
                ->where('is_payroll_active', true)
                ->where('employment_status', EmployeeBiometric::STATUS_ACTIVE)))
            ->when($employmentStatus === EmployeeBiometric::STATUS_INACTIVE, fn (Builder $query) => $query->whereHas('employeeBiometric', fn (Builder $person) => $person
                ->where('employment_status', EmployeeBiometric::STATUS_INACTIVE)))
            ->orderByRaw("CASE WHEN EXISTS (
                SELECT 1 FROM employee_biometrics eb
                WHERE eb.id = payroll_employee_salaries.employee_biometric_id
                  AND (eb.employment_status = 'inactive' OR eb.is_payroll_active = 0)
            ) THEN 1 ELSE 0 END ASC")
            ->when(
                DB::connection()->getDriverName() !== 'sqlite',
                fn (Builder $query) => $query->orderByRaw("LOWER(CASE WHEN employee_name LIKE '%,%' THEN TRIM(SUBSTRING_INDEX(employee_name, ',', 1)) ELSE SUBSTRING_INDEX(TRIM(employee_name), ' ', -1) END) ASC"),
                fn (Builder $query) => $query->orderByRaw('LOWER(employee_name) ASC'),
            )
            ->orderBy('employee_no')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function latestForEmployeeForUpdate(int $employeeBiometricId): ?PayrollEmployeeSalary
    {
        return PayrollEmployeeSalary::query()
            ->where('employee_biometric_id', $employeeBiometricId)
            ->lockForUpdate()
            ->latest('id')
            ->first();
    }

    public function create(array $attributes): PayrollEmployeeSalary
    {
        return PayrollEmployeeSalary::query()->create($attributes);
    }

    public function update(PayrollEmployeeSalary $salary, array $attributes): void
    {
        $salary->update($attributes);
    }

    public function delete(PayrollEmployeeSalary $salary): void
    {
        $salary->delete();
    }

    public function replaceOtherDeductions(PayrollEmployeeSalary $salary, array $rows): void
    {
        $salary->otherDeductions()->delete();
        if ($rows !== []) {
            $salary->otherDeductions()->createMany($rows);
        }
    }

    public function load(PayrollEmployeeSalary $salary, array $relations): PayrollEmployeeSalary
    {
        return $salary->load($relations);
    }
}
