<?php

declare(strict_types=1);

namespace App\Http\Requests\Scheduling;

use App\Models\PayrollEmployeeSalary;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Employee Rates create / edit form. One rate per biometric person. */
final class EmployeeRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $schedule = Rule::in(PayrollEmployeeSalary::SCHEDULES);
        $salary = $this->route('payrollEmployeeSalary');

        $rules = [
            'employee_biometric_id' => [
                'required',
                'integer',
                'exists:employee_biometrics,id',
                Rule::unique('payroll_employee_salaries', 'employee_biometric_id')->ignore($salary instanceof PayrollEmployeeSalary ? $salary->id : null),
            ],
            'employee_no' => ['nullable', 'string', 'max:255'],
            'employee_name' => ['required', 'string', 'max:255'],
            'crosschex_id' => ['nullable', 'string', 'max:255'],
            'biometric_employee_id' => ['nullable', 'string', 'max:255'],

            'rate_type' => ['required', Rule::in(PayrollEmployeeSalary::RATE_TYPES)],
            'basic_salary' => ['required', 'numeric', 'min:0'],

            'allowance' => ['nullable', 'numeric', 'min:0'],
            'allowance_release_schedule' => ['required', $schedule],
            'sim_load_allowance' => ['nullable', 'numeric', 'min:0'],
            'sim_load_release_schedule' => ['required', $schedule],
            'paid_night_differential' => ['nullable', 'boolean'],
            'paid_day_off' => ['nullable', 'boolean'],

            'sss_contribution_cutoff' => ['required', $schedule],
            'pagibig_contribution_cutoff' => ['required', $schedule],
            'philhealth_contribution_cutoff' => ['required', $schedule],

            'other_deductions' => ['nullable', 'array', 'max:30'],
            'other_deductions.*.name' => ['nullable', 'string', 'max:255'],
            'other_deductions.*.total_amount' => ['nullable', 'numeric', 'min:0'],
            'other_deductions.*.payment_amount' => ['nullable', 'numeric', 'min:0'],
            'other_deductions.*.deduction_schedule' => ['nullable', $schedule],
            'other_deductions.*.start_date' => ['nullable', 'date'],
            'other_deductions.*.remarks' => ['nullable', 'string', 'max:1000'],

            'is_active' => ['nullable', 'boolean'],
            'remarks' => ['nullable', 'string'],
        ];

        foreach (PayrollEmployeeSalary::LOAN_PREFIXES as $prefix) {
            $rules["{$prefix}_total_amount"] = ['nullable', 'numeric', 'min:0'];
            $rules["{$prefix}_payment_amount"] = ['nullable', 'numeric', 'min:0'];
            $rules["{$prefix}_deduction_schedule"] = ['required', $schedule];
            $rules["{$prefix}_start_date"] = ['nullable', 'date'];
        }

        return $rules;
    }
}
