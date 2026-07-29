<?php

namespace App\Services\Permissions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoutePermissionSyncService
{
    /**
     * System permissions not directly attached to routes.
     *
     * These are used for data-level security.
     */
    private array $systemPermissions = [

        /*
        |--------------------------------------------------------------------------
        | Payroll Salary Security
        |--------------------------------------------------------------------------
        */

        'payroll.all-access',

        'payroll.mirasol',

        'payroll.gonzales',

        /*
        |--------------------------------------------------------------------------
        | Add other non-route permissions here
        |--------------------------------------------------------------------------
        */

    ];

    public function scan(): Collection
    {
        return collect(Route::getRoutes())
            ->flatMap(function ($route) {

                return collect($route->gatherMiddleware())
                    ->filter(fn ($middleware) => is_string($middleware))
                    ->filter(
                        fn ($middleware) => str_starts_with($middleware, 'permission:')
                    )
                    ->flatMap(
                        fn ($middleware) => $this->extractPermissions($middleware)
                    );

            })
            ->merge($this->systemPermissions)
            ->unique()
            ->sort()
            ->values();
    }

    public function sync(string $guardName = 'web'): array
    {
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        $permissions = $this->scan();

        $existingPermissions = Permission::query()
            ->where('guard_name', $guardName)
            ->pluck('name');

        /*
        |--------------------------------------------------------------------------
        | CREATE missing permissions
        |--------------------------------------------------------------------------
        */

        $toCreate = $permissions
            ->diff($existingPermissions)
            ->values();

        foreach ($toCreate as $permissionName) {

            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => $guardName,
            ]);

        }

        /*
        |--------------------------------------------------------------------------
        | DELETE stale permissions
        |--------------------------------------------------------------------------
        |
        | Do not delete system permissions.
        |
        */

        $protectedPermissions = collect(
            $this->systemPermissions
        );

        $toDelete = $existingPermissions
            ->diff($permissions)
            ->diff($protectedPermissions)
            ->values();

        if ($toDelete->isNotEmpty()) {

            $permissionsToDelete = Permission::query()
                ->where('guard_name', $guardName)
                ->whereIn('name', $toDelete)
                ->get();

            foreach ($permissionsToDelete as $permission) {

                foreach ($permission->roles as $role) {

                    $role->revokePermissionTo($permission);

                }

                $permission->delete();

            }

        }

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        return [

            'route_permissions' => $permissions,

            'created_permissions' => $toCreate,

            'deleted_permissions' => $toDelete,

            'created_count' => $toCreate->count(),

            'deleted_count' => $toDelete->count(),

        ];
    }

    private function extractPermissions(
        string $middleware
    ): array {

        $value = str_replace(
            'permission:',
            '',
            $middleware
        );

        $permissionPart = explode(',', $value)[0];

        return collect(
            explode('|', $permissionPart)
        )
            ->map(
                fn ($permission) => trim($permission)
            )
            ->filter()
            ->values()
            ->all();

    }
}
