<?php

namespace App\Services\Permissions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoutePermissionSyncService
{
    public function scan(): Collection
    {
        return collect(Route::getRoutes())
            ->flatMap(function ($route) {
                return collect($route->gatherMiddleware())
                    ->filter(fn ($middleware) => is_string($middleware))
                    ->filter(fn ($middleware) => str_starts_with($middleware, 'permission:'))
                    ->flatMap(fn ($middleware) => $this->extractPermissions($middleware));
            })
            ->unique()
            ->sort()
            ->values();
    }

    public function sync(string $guardName = 'web'): array
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $routePermissions = $this->scan();

        $existingPermissions = Permission::query()
            ->where('guard_name', $guardName)
            ->pluck('name');

        // CREATE: missing in DB but exists in routes
        $toCreate = $routePermissions->diff($existingPermissions)->values();

        foreach ($toCreate as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => $guardName,
            ]);
        }

        // DELETE: exists in DB but NOT in routes (stale permissions)
        $toDelete = $existingPermissions->diff($routePermissions)->values();

        if ($toDelete->isNotEmpty()) {
            $permissions = Permission::query()
                ->where('guard_name', $guardName)
                ->whereIn('name', $toDelete)
                ->get();

            foreach ($permissions as $permission) {
                // detach from all roles first
                foreach ($permission->roles as $role) {
                    $role->revokePermissionTo($permission);
                }

                $permission->delete();
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return [
            'route_permissions' => $routePermissions,
            'created_permissions' => $toCreate,
            'deleted_permissions' => $toDelete,
            'created_count' => $toCreate->count(),
            'deleted_count' => $toDelete->count(),
        ];
    }

    private function extractPermissions(string $middleware): array
    {
        $value = str_replace('permission:', '', $middleware);

        $permissionPart = explode(',', $value)[0];

        return collect(explode('|', $permissionPart))
            ->map(fn ($permission) => trim($permission))
            ->filter()
            ->values()
            ->all();
    }
}
