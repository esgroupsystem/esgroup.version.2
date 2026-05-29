<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        try {

            $roles = Role::with('permissions')
                ->when(
                    ! auth()->user()->hasRole('Developer'),
                    function ($query) {
                        $query->where('name', '!=', 'Developer');
                    }
                )
                ->orderBy('name')
                ->get();

            $permissions = Permission::orderBy('name')->get();

            $permissionGroups = $permissions
                ->groupBy(function ($permission) {

                    return explode(
                        '.',
                        $permission->name
                    )[0];
                })

                ->map(function ($group) {

                    return $group->groupBy(function ($permission) {

                        $parts = explode(
                            '.',
                            $permission->name
                        );

                        return $parts[1] ?? 'other';
                    });
                });

            return view(
                'roles.index',
                compact(
                    'roles',
                    'permissions',
                    'permissionGroups'
                )
            );

        } catch (\Exception $e) {

            Log::error(
                'Role index error: '.
                $e->getMessage()
            );

            return back()->with(
                'error',
                'Unable to load roles.'
            );
        }
    }

    public function store(Request $request)
    {
        try {

            $validated = $request->validate([

                'name' => 'required|unique:roles,name',
                'permissions' => 'nullable|array',

            ]);

            $role = Role::create([

                'name' => $validated['name'],
                'guard_name' => 'web',

            ]);

            $role->syncPermissions(
                $validated['permissions'] ?? []
            );

            return back()->with(
                'success',
                'Role created successfully'
            );

        } catch (\Exception $e) {

            Log::error(
                'Role create error: '.
                $e->getMessage()
            );

            return back()->withInput()->with(
                'error',
                'Failed to create role.'
            );
        }
    }

    public function update(
        Request $request,
        Role $role
    ) {
        try {

            $validated = $request->validate([

                'name' => 'required|unique:roles,name,'.$role->id,
                'permissions' => 'nullable|array',

            ]);

            $role->update([

                'name' => $validated['name'],

            ]);

            $role->syncPermissions(
                $validated['permissions'] ?? []
            );

            return back()->with(
                'success',
                'Role updated successfully'
            );

        } catch (\Exception $e) {

            Log::error(
                'Role update error: '.
                $e->getMessage()
            );

            return back()->withInput()->with(
                'error',
                'Failed to update role.'
            );
        }
    }

    public function destroy(Role $role)
    {
        try {

            if (
                $role->users()->count()
            ) {

                return back()->with(
                    'error',
                    'Role still assigned'
                );
            }

            $role->delete();

            return back()->with(
                'success',
                'Role deleted successfully'
            );

        } catch (\Exception $e) {

            Log::error(
                'Role delete error: '.
                $e->getMessage()
            );

            return back()->with(
                'error',
                'Failed to delete role.'
            );
        }
    }
}
