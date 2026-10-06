<?php

declare(strict_types=1);

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Http\Requests\Security\RoleRequest;
use App\Http\Resources\Security\RoleResource;
use App\Services\Security\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Throwable;

/** Security → Roles (roles.*). Anyone with roles.view may look; only a Developer may change roles. */
final class RoleController extends Controller
{
    public function __construct(private readonly RoleService $roleService) {}

    public function index(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        $isDeveloper = $user?->isDeveloper() === true;

        try {
            $data = $this->roleService->indexData();
        } catch (Throwable $e) {
            Log::error('Role index error', ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);

            return back()->with('error', 'Unable to load roles.');
        }

        return Inertia::render('authentication/roles/index', [
            'roles' => $data['roles']->map(fn (Role $role): array => (new RoleResource($role, $data['risks']))->resolve($request))->values(),
            'permissionGroups' => $data['permissionGroups']->map(fn (Collection $group, string $module): array => [
                'module' => $module,
                'permissions' => $group->values(),
            ])->values(),
            'missingRoutePermissions' => $data['missingRoutePermissions'],
            'stats' => $data['stats'],
            // Every role change is Developer-only, on top of the route permission.
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
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        try {
            $this->roleService->create($request->validated('name'), $request->validated('permissions') ?? []);

            return back()->with('success', 'Role created successfully.');
        } catch (Throwable $e) {
            Log::error('Role create error', ['message' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Failed to create role.');
        }
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        try {
            $this->roleService->update($role, $request->validated('name'), $request->validated('permissions') ?? []);

            return back()->with('success', 'Role updated successfully.');
        } catch (ValidationException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            Log::error('Role update error', ['role_id' => $role->id, 'message' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Failed to update role.');
        }
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        $this->assertDeveloper($request);

        try {
            $this->roleService->delete($role);

            return back()->with('success', 'Role deleted successfully.');
        } catch (ValidationException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            Log::error('Role delete error', ['role_id' => $role->id, 'message' => $e->getMessage()]);

            return back()->with('error', 'Failed to delete role.');
        }
    }

    public function syncPermissions(Request $request): RedirectResponse
    {
        $this->assertDeveloper($request);

        try {
            $result = $this->roleService->syncRoutePermissions();

            return back()->with('success', 'Permissions synced. Created: '.$result['created_count'].' | Deleted: '.$result['deleted_count']);
        } catch (Throwable $e) {
            Log::error('Role permission sync error', ['message' => $e->getMessage()]);

            return back()->with('error', 'Failed to sync route permissions.');
        }
    }

    private function assertDeveloper(Request $request): void
    {
        abort_unless($request->user()?->isDeveloper() === true, 403);
    }
}
