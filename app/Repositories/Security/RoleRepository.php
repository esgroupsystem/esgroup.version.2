<?php

declare(strict_types=1);

namespace App\Repositories\Security;

use App\Repositories\Contracts\Security\RoleRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class RoleRepository implements RoleRepositoryInterface
{
    public function roles(bool $includeDeveloper): Collection
    {
        return Role::query()
            ->with('permissions')
            ->withCount('users')
            ->when(! $includeDeveloper, fn (Builder $query) => $query->where('name', '!=', 'Developer'))
            ->orderBy('name')
            ->get();
    }

    public function findByNames(array $names): Collection
    {
        return Role::query()->where('guard_name', 'web')->whereIn('name', $names)->orderBy('name')->get();
    }

    public function permissions(): Collection
    {
        return Permission::query()->orderBy('name')->get();
    }

    public function create(string $name, array $permissions): Role
    {
        $role = Role::query()->create(['name' => $name, 'guard_name' => 'web']);
        $role->syncPermissions($permissions);

        return $role;
    }

    public function update(Role $role, string $name, array $permissions): void
    {
        $role->update(['name' => $name]);
        $role->syncPermissions($permissions);
    }

    public function hasUsers(Role $role): bool
    {
        return $role->users()->exists();
    }

    public function delete(Role $role): void
    {
        $role->delete();
    }

    public function grantAllToRole(string $roleName): void
    {
        $role = Role::query()->where('name', $roleName)->where('guard_name', 'web')->first();

        $role?->syncPermissions(Permission::query()->where('guard_name', 'web')->get());
    }
}
