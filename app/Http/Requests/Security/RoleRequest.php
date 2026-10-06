<?php

declare(strict_types=1);

namespace App\Http\Requests\Security;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

/** Create or edit a role (roles.store / roles.update). Only a Developer may change roles. */
final class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isDeveloper() === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $role = $this->route('role');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($role instanceof Role ? $role->id : null),
                // No second "developer" role: that name is the system role with every permission.
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (strcasecmp(trim((string) $value), User::DEVELOPER_ROLE) === 0) {
                        $fail('"Developer" is a reserved system role name.');
                    }
                },
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'web')],
        ];
    }
}
