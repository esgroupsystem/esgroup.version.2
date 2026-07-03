<?php

namespace App\Http\Requests\Biometrics;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBiometricCompanyRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('biometric_companies', 'name'),
            ],
            'remarks' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}
