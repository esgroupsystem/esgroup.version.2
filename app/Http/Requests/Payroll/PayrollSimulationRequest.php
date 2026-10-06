<?php

declare(strict_types=1);

namespace App\Http\Requests\Payroll;

use App\Support\Payroll\PayrollSettingCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/** Payroll Settings → Test computation run (nothing is saved). */
final class PayrollSimulationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'employee_biometric_id' => ['nullable', 'required_without:garage_group', 'integer'],
            'garage_group' => ['nullable', 'required_without:employee_biometric_id', 'string', 'max:10'],
            'cutoff_month' => ['required', 'integer', 'min:1', 'max:12'],
            'cutoff_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'cutoff_type' => ['required', 'in:first,second'],
            'compare_values' => ['nullable', 'array'],
            'compare_version_id' => ['nullable', 'integer'],
            'compare_rule' => ['nullable', 'array'],
            'compare_rule.id' => ['nullable', 'integer'],
        ];

        if (is_array($this->input('compare_values'))) {
            $rules = array_merge($rules, PayrollSettingCatalog::rules('compare_values'));
        }

        if (is_array($this->input('compare_rule'))) {
            $rules = array_merge($rules, PayrollRuleRequest::fieldRules('compare_rule.', true));
        }

        return $rules;
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return PayrollRuleRequest::fieldMessages('compare_rule.');
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return PayrollSettingCatalog::attributes('compare_values');
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if (is_array($this->input('compare_values'))) {
                PayrollSettingVersionRequest::checkValues($validator, 'compare_values', (array) $this->input('compare_values'));
            }
        }];
    }
}
