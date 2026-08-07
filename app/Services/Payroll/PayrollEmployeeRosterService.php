<?php

namespace App\Services\Payroll;

use App\Models\EmployeeBiometric;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PayrollEmployeeRosterService
{
    /**
     * Canonical payroll roster.
     *
     * An employee is eligible only when:
     * - employment status is Active (legacy NULL is treated as Active),
     * - Payroll Inclusion is ON (legacy NULL is treated as ON), and
     * - the employee belongs to the selected payroll group.
     *
     * Attendance summaries, plotting schedules, biometric logs and salary
     * profiles are NOT roster sources. They are downstream payroll data. This
     * prevents an otherwise eligible employee from disappearing from payroll
     * simply because one of those downstream records is missing.
     */
    public function queryForGroup(int|string $groupName): Builder
    {
        return EmployeeBiometric::query()
            ->payrollActive()
            ->where('group_name', (string) $groupName)
            ->orderByRaw("CASE WHEN NULLIF(TRIM(display_name), '') IS NULL THEN 1 ELSE 0 END")
            ->orderBy('display_name')
            ->orderBy('source_employee_name')
            ->orderBy('display_employee_no')
            ->orderBy('id');
    }

    public function forGroup(int|string $groupName): Collection
    {
        return $this->queryForGroup($groupName)->get();
    }
}
