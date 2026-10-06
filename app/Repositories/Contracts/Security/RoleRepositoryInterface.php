<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Security;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/** Roles and permissions (spatie/laravel-permission, guard "web"). */
interface RoleRepositoryInterface
{
    /** @return Collection<int, Role> by name, with permissions and users_count; Developer left out unless $includeDeveloper */
    public function roles(bool $includeDeveloper): Collection;

    /** @param list<string> $names @return Collection<int, Role> */
    public function findByNames(array $names): Collection;

    /** @return Collection<int, Permission> by name */
    public function permissions(): Collection;

    /** @param list<string> $permissions */
    public function create(string $name, array $permissions): Role;

    /** @param list<string> $permissions */
    public function update(Role $role, string $name, array $permissions): void;

    public function hasUsers(Role $role): bool;

    public function delete(Role $role): void;

    /** Give a role every permission that exists (the Developer role). No-op when the role is missing. */
    public function grantAllToRole(string $roleName): void;
}
