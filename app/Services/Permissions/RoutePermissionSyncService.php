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

        $missingPermissions = $routePermissions
            ->diff($existingPermissions)
            ->values();

        foreach ($missingPermissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => $guardName,
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return [
            'route_permissions' => $routePermissions,
            'created_permissions' => $missingPermissions,
            'created_count' => $missingPermissions->count(),
        ];
    }

    private function extractPermissions(string $middleware): array
    {
        $value = str_replace('permission:', '', $middleware);

        /*
         * Supports:
         * permission:users.view
         * permission:users.view|users.create
         * permission:users.view,web
         */
        $permissionPart = explode(',', $value)[0];

        return collect(explode('|', $permissionPart))
            ->map(fn ($permission) => trim($permission))
            ->filter()
            ->values()
            ->all();
    }
}
