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

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'biometric_company_id' => [
                'nullable',
                'integer',
                Rule::exists('biometric_companies', 'id'),
            ],
            'display_employee_no' => [
                'nullable',
                'string',
                'max:100',
            ],
            'display_name' => [
                'required',
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
            'remarks' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}
