<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * The Developer role holds every permission. Only adds the missing ones; users, other roles
     * and their permissions are not touched.
     */
    public function up(): void
    {
        $role = Role::query()->where('name', 'Developer')->where('guard_name', 'web')->first();

        if ($role === null) {
            return;
        }

        $role->givePermissionTo(
            Permission::query()
                ->where('guard_name', 'web')
                ->whereNotIn('id', $role->permissions()->pluck('id'))
                ->get()
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Nothing to undo: the Developer role keeps its permissions.
    }
};
