<?php

namespace App\Http\Requests\Biometrics;

use Illuminate\Foundation\Http\FormRequest;

class StepBiometricsSyncRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'job' => ['required', 'uuid'],
        ];
    }
}
