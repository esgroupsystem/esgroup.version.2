<?php

namespace App\Http\Requests\Biometrics;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StartBiometricsSyncRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $accountKeys = array_keys(config('services.crosschex.accounts', []));

        return [
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'accounts' => ['required', 'array', 'min:1'],
            'accounts.*' => ['required', 'string', Rule::in($accountKeys)],
        ];
    }

    public function messages(): array
    {
        return [
            'accounts.required' => 'Select at least one biometric source to synchronize.',
            'accounts.min' => 'Select at least one biometric source to synchronize.',
        ];
    }
}
