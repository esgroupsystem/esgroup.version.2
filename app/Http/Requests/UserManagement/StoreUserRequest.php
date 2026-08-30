<?php

declare(strict_types=1);

namespace App\Http\Requests\UserManagement;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.create') === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $roles = User::availableAssignableRoles($this->user());

        return [
            'full_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:191', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', Rule::in(array_values(array_unique(array_merge($roles, ['Developer']))))],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
        ];
    }
}
