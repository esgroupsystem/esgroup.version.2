<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Permissions\RoutePermissionSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(RoutePermissionSyncService $routePermissionSyncService): Response|RedirectResponse
    {
        try {
            $roles = Role::query()
                ->with('permissions')
                ->withCount('users')
                ->when(! auth()->user()->hasRole('Developer'), function ($query) {
                    $query->where('name', '!=', 'Developer');
                })
                ->orderBy('name')
                ->get();

            $permissions = Permission::query()
                ->orderBy('name')
                ->get();

            $routePermissions = $routePermissionSyncService->scan();

            $missingRoutePermissions = $routePermissions
                ->diff($permissions->pluck('name'))
                ->values();

            $permissionGroups = $this->buildPermissionGroups($permissions);
            $risks = $permissionGroups->flatten(1)->pluck('risk', 'name');
            $user = auth()->user();
            $isDeveloper = $user?->isDeveloper() === true;

            return Inertia::render('authentication/roles/index', [
                'roles' => $roles->map(fn (Role $role): array => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'users_count' => (int) $role->users_count,
                    'permissions' => $role->permissions->pluck('name')->sort()->values(),
                    'high_risk' => $role->permissions->filter(fn (Permission $permission): bool => ($risks[$permission->name] ?? 'low') === 'high')->count(),
                    'medium_risk' => $role->permissions->filter(fn (Permission $permission): bool => ($risks[$permission->name] ?? 'low') === 'medium')->count(),
                    'update_url' => route('roles.update', $role->id),
                    'destroy_url' => route('roles.destroy', $role->id),
                ])->values(),
                'permissionGroups' => $permissionGroups->map(fn (Collection $group, string $module): array => [
                    'module' => $module,
                    'permissions' => $group->values(),
                ])->values(),
                'missingRoutePermissions' => $missingRoutePermissions,
                'stats' => [
                    'roles' => $roles->count(),
                    'permissions' => $permissions->count(),
                    'modules' => $permissionGroups->count(),
                    'users' => User::query()->count(),
                ],
                // Every role change is Developer-only (assertDeveloper), on top of the route permission.
                'can' => [
                    'create' => $isDeveloper && (bool) $user?->can('roles.create'),
                    'update' => $isDeveloper && (bool) $user?->can('roles.update'),
                    'delete' => $isDeveloper && (bool) $user?->can('roles.delete'),
                ],
                'urls' => [
                    'store' => route('roles.store'),
                    'sync' => route('roles.sync-permissions'),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Role index error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()->with('error', 'Unable to load roles.');
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $this->assertDeveloper();

        try {
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('roles', 'name')->where('guard_name', 'web'),
                ],
                'permissions' => ['nullable', 'array'],
                'permissions.*' => [
                    'string',
                    Rule::exists('permissions', 'name')->where('guard_name', 'web'),
                ],
            ]);

            DB::transaction(function () use ($validated): void {
                $role = Role::create([
                    'name' => $validated['name'],
                    'guard_name' => 'web',
                ]);

                $role->syncPermissions($validated['permissions'] ?? []);
            });

            return back()->with('success', 'Role created successfully.');
        } catch (ValidationException $e) {
            // Let field errors (e.g. duplicate role name) reach the form.
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Role create error', [
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create role.');
        }
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->assertDeveloper();

        try {
            if ($role->name === 'Developer' && ! auth()->user()->hasRole('Developer')) {
                return back()->with('error', 'You are not allowed to update the Developer role.');
            }

            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('roles', 'name')
                        ->ignore($role->id)
                        ->where('guard_name', 'web'),
                ],
                'permissions' => ['nullable', 'array'],
                'permissions.*' => [
                    'string',
                    Rule::exists('permissions', 'name')->where('guard_name', 'web'),
                ],
            ]);

            DB::transaction(function () use ($role, $validated): void {
                $role->update([
                    'name' => $validated['name'],
                ]);

                $role->syncPermissions($validated['permissions'] ?? []);
            });

            return back()->with('success', 'Role updated successfully.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Role update error', [
                'role_id' => $role->id,
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update role.');
        }
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->assertDeveloper();

        try {
            if ($role->name === 'Developer') {
                return back()->with('error', 'Developer role cannot be deleted.');
            }

            if ($role->users()->exists()) {
                return back()->with('error', 'Role is still assigned to users.');
            }

            $role->delete();

            return back()->with('success', 'Role deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('Role delete error', [
                'role_id' => $role->id,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to delete role.');
        }
    }

    public function syncPermissions(RoutePermissionSyncService $service): RedirectResponse
    {
        $this->assertDeveloper();

        try {
            $result = $service->sync();

            return back()->with(
                'success',
                'Permissions synced. Created: '.
                $result['created_count'].
                ' | Deleted: '.
                $result['deleted_count']
            );
        } catch (\Throwable $e) {
            Log::error('Role permission sync error', [
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to sync route permissions.');
        }
    }

    private function assertDeveloper(): void
    {
        abort_unless(auth()->user()?->isDeveloper() === true, 403);
    }

    private function buildPermissionGroups(Collection $permissions): Collection
    {
        return $permissions
            ->map(function (Permission $permission) {
                [$module, $action] = array_pad(explode('.', $permission->name, 2), 2, 'other');

                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'module' => $module,
                    'module_label' => $this->humanize($module),
                    'action' => $action,
                    'action_label' => $this->actionLabel($action),
                    'description' => $this->permissionDescription($module, $action),
                    'risk' => $this->riskLevel($action),
                ];
            })
            ->groupBy('module_label');
    }

    private function humanize(string $value): string
    {
        return str($value)
            ->replace(['-', '_'], ' ')
            ->title()
            ->toString();
    }

    private function actionLabel(string $action): string
    {
        return match ($action) {
            'view' => 'View / Open Records',
            'create' => 'Create / Save New Record',
            'update' => 'Edit / Update Record',
            'delete' => 'Delete / Remove Record',
            'export' => 'Export / Download Report',
            'approve' => 'Approve / Disapprove',
            'finalize' => 'Finalize Transaction',
            'sync' => 'Sync Data',
            'rollback' => 'Rollback Transaction',
            'cancel' => 'Cancel Transaction',
            'receive' => 'Receive Items',
            'analytics' => 'Analytics Dashboard',
            'crm' => 'CRM Dashboard',
            'it' => 'IT Dashboard',
            default => $this->humanize($action),
        };
    }

    private function permissionDescription(string $module, string $action): string
    {
        return match ($action) {
            'view' => 'Can view lists, details, print pages, and search records in this module.',
            'create' => 'Can create and save new records in this module.',
            'update' => 'Can edit existing records and update details in this module.',
            'delete' => 'Can delete, deactivate, or remove records in this module.',
            'export' => 'Can download Excel, PDF, or report exports from this module.',
            'approve' => 'Can approve, disapprove, or authorize records in this module.',
            'finalize' => 'Can finalize records. Usually this locks the transaction from normal editing.',
            'sync' => 'Can synchronize external or biometric data into the system.',
            'rollback' => 'Can reverse a completed transaction and restore affected records or stock balances.',
            'cancel' => 'Can cancel an active transaction before it is fully completed.',
            'receive' => 'Can mark purchase order items as received and update receiving records.',
            default => 'Can perform '.$this->humanize($action).' action in '.$this->humanize($module).'.',
        };
    }

    private function riskLevel(string $action): string
    {
        return match ($action) {
            'delete', 'rollback', 'finalize' => 'high',
            'approve', 'update', 'cancel', 'receive' => 'medium',
            default => 'low',
        };
    }
}
