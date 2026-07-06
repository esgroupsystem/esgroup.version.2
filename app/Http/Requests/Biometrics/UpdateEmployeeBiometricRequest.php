<?php

namespace App\Http\Requests\Biometrics;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeBiometricRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $status = trim((string) $this->input('employment_status', 'active'));

        $this->merge([
            'employment_status' => $status === '' ? 'active' : $status,
            'is_payroll_active' => $status === 'active' && $this->boolean('is_payroll_active'),
            'display_employee_no' => trim((string) $this->input('display_employee_no')),
            'display_name' => trim((string) $this->input('display_name')),
            'group_name' => trim((string) $this->input('group_name')),
        ]);
    }

    public function rules(): array
    {
        return [
            'biometric_company_id' => ['nullable', 'integer', 'exists:biometric_companies,id'],
            'display_employee_no' => ['nullable', 'string', 'max:100'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'employment_status' => ['required', Rule::in(['active', 'inactive'])],
            'group_name' => ['nullable', 'string', 'max:255'],
            'is_payroll_active' => ['nullable', 'boolean'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'employment_status.in' => 'Employment status must be Active or Inactive.',
            'biometric_company_id.exists' => 'Selected biometric company does not exist.',
        ];
    }
}
