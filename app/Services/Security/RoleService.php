<?php

declare(strict_types=1);

namespace App\Services\Security;

use App\Models\User;
use App\Repositories\Contracts\Security\RoleRepositoryInterface;
use App\Repositories\Contracts\Security\UserRepositoryInterface;
use App\Services\Permissions\RoutePermissionSyncService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Security → Roles: roles, their permissions grouped by module with a plain description and a risk
 * level, and the sync of route permissions. Changes are Developer-only (checked by the controller).
 */
final class RoleService
{
    public function __construct(
        private readonly RoleRepositoryInterface $roles,
        private readonly UserRepositoryInterface $users,
        private readonly RoutePermissionSyncService $routePermissions,
    ) {}

    /**
     * @return array{roles: Collection<int, Role>, permissionGroups: Collection<string, Collection<int, array<string, mixed>>>, risks: Collection<string, string>, missingRoutePermissions: Collection<int, string>, stats: array<string, int>}
     */
    public function indexData(): array
    {
        // The Developer role is a system role: always every permission, never listed or edited.
        $roles = $this->roles->roles(false);
        $permissions = $this->roles->permissions();
        $groups = $this->permissionGroups($permissions);

        return [
            'roles' => $roles,
            'permissionGroups' => $groups,
            'risks' => $groups->flatten(1)->pluck('risk', 'name'),
            'missingRoutePermissions' => $this->routePermissions->scan()->diff($permissions->pluck('name'))->values(),
            'stats' => [
                'roles' => $roles->count(),
                'permissions' => $permissions->count(),
                'modules' => $groups->count(),
                'users' => $this->users->count(),
            ],
        ];
    }

    /** @param list<string> $permissions */
    public function create(string $name, array $permissions): Role
    {
        return DB::transaction(fn (): Role => $this->roles->create($name, $permissions));
    }

    /** @param list<string> $permissions */
    public function update(Role $role, string $name, array $permissions): void
    {
        $this->assertNotDeveloper($role);

        DB::transaction(fn () => $this->roles->update($role, $name, $permissions));
    }

    /** @throws ValidationException for the Developer role or a role still given to users */
    public function delete(Role $role): void
    {
        if (strcasecmp($role->name, User::DEVELOPER_ROLE) === 0) {
            throw ValidationException::withMessages(['role' => 'Developer role cannot be deleted.']);
        }
        if ($this->roles->hasUsers($role)) {
            throw ValidationException::withMessages(['role' => 'Role is still assigned to users.']);
        }

        $this->roles->delete($role);
    }

    /** @return array{created_count: int, deleted_count: int} */
    public function syncRoutePermissions(): array
    {
        $result = $this->routePermissions->sync();
        $this->roles->grantAllToRole(User::DEVELOPER_ROLE);

        return $result;
    }

    /** @throws ValidationException */
    private function assertNotDeveloper(Role $role): void
    {
        if (strcasecmp($role->name, User::DEVELOPER_ROLE) === 0) {
            throw ValidationException::withMessages(['role' => 'The Developer role is managed by the system and always has every permission.']);
        }
    }

    /**
     * Permissions grouped by module label ("payroll.view" → module "Payroll", action "view").
     *
     * @param  Collection<int, Permission>  $permissions
     * @return Collection<string, Collection<int, array<string, mixed>>>
     */
    private function permissionGroups(Collection $permissions): Collection
    {
        return $permissions
            ->map(function (Permission $permission): array {
                [$module, $action] = array_pad(explode('.', $permission->name, 2), 2, 'other');

                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'module' => $module,
                    'module_label' => self::humanize($module),
                    'action' => $action,
                    'action_label' => self::actionLabel($action),
                    'description' => self::description($module, $action),
                    'risk' => self::riskLevel($action),
                ];
            })
            ->groupBy('module_label');
    }

    private static function humanize(string $value): string
    {
        return str($value)->replace(['-', '_'], ' ')->title()->toString();
    }

    private static function actionLabel(string $action): string
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
            default => self::humanize($action),
        };
    }

    private static function description(string $module, string $action): string
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
            default => 'Can perform '.self::humanize($action).' action in '.self::humanize($module).'.',
        };
    }

    private static function riskLevel(string $action): string
    {
        return match ($action) {
            'delete', 'rollback', 'finalize' => 'high',
            'approve', 'update', 'cancel', 'receive' => 'medium',
            default => 'low',
        };
    }
}
