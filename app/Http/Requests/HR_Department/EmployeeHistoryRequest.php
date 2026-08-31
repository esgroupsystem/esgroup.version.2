<?php

declare(strict_types=1);

namespace App\Http\Requests\HR_Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class EmployeeHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('employees.update') === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'ir_number' => ['required', 'string', 'max:255'],
            'offense_id' => ['required', 'array', 'min:1'],
            'offense_id.*' => ['required', 'integer', 'exists:hr_offenses,id'],
            'description' => ['nullable', 'array'],
            'description.*' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string', 'max:5000'],
            'disciplinary_action' => ['nullable', 'array'],
            'disciplinary_action.*' => ['string', Rule::in(['Salary Deduction Authorization', 'Suspension', 'Final Warning'])],
            'sda_amount' => ['nullable', 'numeric', 'min:0'],
            'sda_terms' => ['nullable', 'numeric', 'min:0'],
            'sda_start_date' => ['nullable', 'date'],
            'sda_end_date' => ['nullable', 'date', 'after_or_equal:sda_start_date'],
            'suspension_start_date' => ['nullable', 'date'],
            'suspension_end_date' => ['nullable', 'date', 'after_or_equal:suspension_start_date'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $actions = $this->input('disciplinary_action', []);
            if (! is_array($actions)) {
                return;
            }
            if (in_array('Salary Deduction Authorization', $actions, true)) {
                foreach (['sda_amount', 'sda_terms', 'sda_start_date'] as $field) {
                    if (blank($this->input($field))) {
                        $validator->errors()->add($field, match ($field) {
                            'sda_amount' => 'SDA total amount is required when Salary Deduction Authorization is selected.',
                            'sda_terms' => 'Per cutoff amount / deduction terms is required when Salary Deduction Authorization is selected.',
                            default => 'SDA start date is required when Salary Deduction Authorization is selected.',
                        });
                    }
                }
            }
            if (in_array('Suspension', $actions, true) && blank($this->input('suspension_start_date'))) {
                $validator->errors()->add('suspension_start_date', 'Suspension start date is required when Suspension is selected.');
            }
        }];
    }
}
