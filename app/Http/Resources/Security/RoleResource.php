<?php

declare(strict_types=1);

namespace App\Http\Resources\Security;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * One role card of Security → Roles (`authentication/roles/index`), with how many of its
 * permissions are high and medium risk.
 *
 * @mixin Role
 */
final class RoleResource extends JsonResource
{
    /** @param Collection<string, string> $risks permission name => low / medium / high */
    public function __construct(Role $resource, private readonly Collection $risks)
    {
        parent::__construct($resource);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $risk = fn (Permission $permission): string => $this->risks[$permission->name] ?? 'low';

        return [
            'id' => $this->id,
            'name' => $this->name,
            'users_count' => (int) $this->users_count,
            'permissions' => $this->permissions->pluck('name')->sort()->values(),
            'high_risk' => $this->permissions->filter(fn (Permission $permission): bool => $risk($permission) === 'high')->count(),
            'medium_risk' => $this->permissions->filter(fn (Permission $permission): bool => $risk($permission) === 'medium')->count(),
            'update_url' => route('roles.update', $this->id),
            'destroy_url' => route('roles.destroy', $this->id),
        ];
    }
}
