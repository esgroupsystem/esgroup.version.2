<?php

declare(strict_types=1);

namespace App\Http\Controllers\Scheduling\Concerns;

use App\Models\PayrollEmployeeSalary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Props of the Employee Rates form (`payroll/employee-salaries/form`), shared by the
 * Employee Rates page and the Rates tab of the employee profile.
 */
trait BuildsEmployeeRateForm
{
    /**
     * @param  Collection<int, array<string, mixed>>|list<array<string, mixed>>  $people
     * @param  array<string, mixed>  $values  EmployeeRateFormResource values
     * @return array<string, mixed>
     */
    private function rateFormProps(Request $request, ?PayrollEmployeeSalary $salary, Collection|array $people, array $values, ?array $personHours = null): array
    {
        $schedule = $salary?->employeeBiometric?->permanentSchedule;
        $first = config('payroll.cutoff_display.first.label', '2nd Cutoff');
        $firstRange = config('payroll.cutoff_display.first.range', '11-25');
        $second = config('payroll.cutoff_display.second.label', '1st Cutoff');
        $secondRange = config('payroll.cutoff_display.second.range', '26-10');

        return [
            'salary' => $salary ? ['id' => $salary->id, 'name' => $salary->payroll_display_name] : null,
            'values' => $values,
            'people' => $people,
            'workday' => $personHours ?? [
                'paid_hours' => (float) ($schedule?->paidWorkHours() ?? 8.0),
                'label' => $schedule?->resolvedWorkdayType()->shortLabel() ?? '8 hrs + 1 hr lunch',
            ],
            'scheduleOptions' => [
                'none' => 'No Deduction / Not Applicable',
                'second_cutoff' => "{$second} Only ({$secondRange})",
                'first_cutoff' => "{$first} Only ({$firstRange})",
                'every_cutoff' => 'Every Cutoff',
            ],
            'cutoffLabels' => [
                'first' => "{$first} ({$firstRange})",
                'second' => "{$second} ({$secondRange})",
            ],
            'sssRules' => config('sss.business_employee'),
            'sssCircular' => [
                'number' => config('sss.business_employee.circular_number', '2024-006'),
                'effective' => Carbon::parse(config('sss.business_employee.effective_from', '2025-01-01'))->format('F Y'),
            ],
            'urls' => [
                'index' => route('payroll-employee-salaries.index'),
                'submit' => $salary ? route('payroll-employee-salaries.update', $salary) : route('payroll-employee-salaries.store'),
            ],
        ];
    }
}
