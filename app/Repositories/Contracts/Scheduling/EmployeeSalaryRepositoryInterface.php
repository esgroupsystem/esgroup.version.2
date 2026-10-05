<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Scheduling;

use App\Models\PayrollEmployeeSalary;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/** Employee Rates (payroll_employee_salaries) and their other deductions. */
interface EmployeeSalaryRepositoryInterface
{
    /**
     * One rate per person (the latest with a salary, else the first), limited to the user's
     * payroll groups. Search on name / numbers / group; filter by group and employment status
     * (active = payroll active, inactive). Included people first, then by surname.
     *
     * @param  string|list<int|string>|null  $allowedGroups
     * @return LengthAwarePaginator<int, PayrollEmployeeSalary>
     */
    public function paginateDirectory(string $search, string $group, string $employmentStatus, string|array|null $allowedGroups, int $perPage = 15): LengthAwarePaginator;

    /** The person's latest rate, locked. */
    public function latestForEmployeeForUpdate(int $employeeBiometricId): ?PayrollEmployeeSalary;

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): PayrollEmployeeSalary;

    /** @param array<string, mixed> $attributes */
    public function update(PayrollEmployeeSalary $salary, array $attributes): void;

    public function delete(PayrollEmployeeSalary $salary): void;

    /** @param list<array<string, mixed>> $rows */
    public function replaceOtherDeductions(PayrollEmployeeSalary $salary, array $rows): void;

    /** @param list<string>|array<string, \Closure> $relations */
    public function load(PayrollEmployeeSalary $salary, array $relations): PayrollEmployeeSalary;
}
