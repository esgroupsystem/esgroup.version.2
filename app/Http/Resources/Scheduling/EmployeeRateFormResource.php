<?php

declare(strict_types=1);

namespace App\Http\Resources\Scheduling;

use App\Http\Resources\Concerns\FormatsDates;
use App\Models\PayrollEmployeeSalary;
use App\Models\PayrollEmployeeSalaryOtherDeduction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The Employee Rates form `values`. Wrap null for a new rate (the defaults below).
 * Load `otherDeductions` first when editing.
 *
 * @property-read PayrollEmployeeSalary|null $resource
 */
final class EmployeeRateFormResource extends JsonResource
{
    use FormatsDates;

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $salary = $this->resource;
        $field = fn (string $key, mixed $default): mixed => $salary ? (data_get($salary, $key) ?? $default) : $default;

        $values = [
            'employee_biometric_id' => $salary?->employee_biometric_id,
            'employee_no' => (string) ($salary?->employee_no ?? ''),
            'employee_name' => (string) ($salary?->employee_name ?? ''),
            'crosschex_id' => (string) ($salary?->crosschex_id ?? ''),
            'biometric_employee_id' => (string) ($salary?->biometric_employee_id ?? ''),
            'rate_type' => (string) $field('rate_type', 'daily'),
            'basic_salary' => (string) $field('basic_salary', '0'),
            'allowance' => (string) $field('allowance', '0'),
            'allowance_release_schedule' => (string) $field('allowance_release_schedule', 'every_cutoff'),
            'sim_load_allowance' => (string) $field('sim_load_allowance', '0'),
            'sim_load_release_schedule' => (string) $field('sim_load_release_schedule', 'every_cutoff'),
            'paid_night_differential' => (bool) $field('paid_night_differential', false),
            'paid_day_off' => (bool) $field('paid_day_off', true),
            'sss_contribution_cutoff' => (string) $field('sss_contribution_cutoff', 'first_cutoff'),
            'pagibig_contribution_cutoff' => (string) $field('pagibig_contribution_cutoff', 'second_cutoff'),
            'philhealth_contribution_cutoff' => (string) $field('philhealth_contribution_cutoff', 'second_cutoff'),
            'other_deductions' => $salary
                ? $salary->otherDeductions->map(fn (PayrollEmployeeSalaryOtherDeduction $deduction): array => [
                    'name' => (string) $deduction->name,
                    'total_amount' => (string) $deduction->total_amount,
                    'payment_amount' => (string) $deduction->payment_amount,
                    'deduction_schedule' => (string) ($deduction->deduction_schedule ?? 'none'),
                    'start_date' => $this->formatDate($deduction->start_date, 'Y-m-d') ?? '',
                    'remarks' => (string) ($deduction->remarks ?? ''),
                ])->values()->all()
                : [],
            'is_active' => (bool) $field('is_active', true),
            'remarks' => (string) ($salary?->remarks ?? ''),
        ];

        foreach (PayrollEmployeeSalary::LOAN_PREFIXES as $prefix) {
            $values["{$prefix}_total_amount"] = (string) $field("{$prefix}_total_amount", '0');
            $values["{$prefix}_payment_amount"] = (string) $field("{$prefix}_payment_amount", '0');
            $values["{$prefix}_deduction_schedule"] = (string) $field("{$prefix}_deduction_schedule", 'none');
            $values["{$prefix}_start_date"] = $this->formatDate(data_get($salary, "{$prefix}_start_date"), 'Y-m-d') ?? '';
        }

        return $values;
    }
}
