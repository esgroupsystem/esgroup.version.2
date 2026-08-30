<?php

declare(strict_types=1);

namespace App\Http\Requests\UserManagement;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.update') === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $routeUser = $this->route('id');
        $target = $routeUser instanceof User ? $routeUser : User::find($routeUser);
        $roles = User::availableAssignableRoles($this->user(), $target);
        $ignoreId = $target?->id;

        return [
            'full_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:191', 'alpha_dash', Rule::unique('users', 'username')->ignore($ignoreId)],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($ignoreId)],
            'role' => ['required', 'string', Rule::in(array_values(array_unique(array_merge($roles, ['Developer']))))],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'account_status' => ['required', Rule::in(['active', 'deactivated'])],
        ];
    }
}
