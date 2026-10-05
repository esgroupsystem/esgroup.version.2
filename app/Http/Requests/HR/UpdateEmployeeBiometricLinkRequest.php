<?php

declare(strict_types=1);

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateEmployeeBiometricLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('employees.update') === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'employee_biometric_id' => [
                'nullable',
                'integer',
                'exists:employee_biometrics,id',
                Rule::unique('employees', 'employee_biometric_id')->ignore($this->route('employee')?->getKey()),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'employee_biometric_id.unique' => 'That biometric record is already linked to another employee. Unlink it there first.',
        ];
    }
}
