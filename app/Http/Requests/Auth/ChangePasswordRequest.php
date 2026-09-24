<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

final class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $rules = [
            'password' => [
                'required',
                'string',
                'confirmed',
                // Kept short on purpose: at least 7 characters with a number and a special character.
                Password::min(7)->numbers()->symbols(),
            ],
        ];

        if (! (bool) $this->user()?->must_change_password) {
            $rules['current_password'] = ['required', 'current_password:web'];
        }

        return $rules;
    }
}
