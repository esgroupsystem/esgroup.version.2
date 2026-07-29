<?php

namespace App\Http\Requests\Biometrics;

use App\Models\EmployeeBiometric;
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
        $status = trim(
            (string) $this->input(
                'employment_status',
                EmployeeBiometric::STATUS_ACTIVE
            )
        );

        $this->merge([
            'employment_status' => $status === ''
                    ? EmployeeBiometric::STATUS_ACTIVE
                    : $status,

            'is_payroll_active' => $status === EmployeeBiometric::STATUS_ACTIVE
                && $this->boolean('is_payroll_active'),

            'display_employee_no' => trim(
                (string) $this->input('display_employee_no')
            ),

            'display_name' => trim(
                (string) $this->input('display_name')
            ),

            /*
             * Do not cast this to integer. The accepted values are
             * Mirasol and Gonzales group constants.
             */
            'group_name' => trim(
                (string) $this->input('group_name')
            ),

            'remarks' => trim(
                (string) $this->input('remarks')
            ),
        ]);
    }

    public function rules(): array
    {
        return [
            'biometric_company_id' => [
                'nullable',
                'integer',
                'exists:biometric_companies,id',
            ],

            'display_employee_no' => [
                'nullable',
                'string',
                'max:100',
            ],

            'display_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'employment_status' => [
                'required',
                Rule::in([
                    EmployeeBiometric::STATUS_ACTIVE,
                    EmployeeBiometric::STATUS_INACTIVE,
                ]),
            ],

            'group_name' => [
                'required',
                Rule::in([
                    EmployeeBiometric::PAYROLL_GROUP_MIRASOL,
                    EmployeeBiometric::PAYROLL_GROUP_GONZALES,
                ]),
            ],

            'is_payroll_active' => [
                'nullable',
                'boolean',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'employment_status.in' => 'Employment status must be Active or Inactive.',

            'group_name.in' => 'Payroll group must be Mirasol or Gonzales.',

            'biometric_company_id.exists' => 'Selected biometric company does not exist.',
        ];
    }
}
