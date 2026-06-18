@extends('layouts.app')

@section('title', 'Roles Management')

@section('content')

    @php
        use Illuminate\Support\Facades\Route;

        /*
        |--------------------------------------------------------------------------
        | Permission Display Helpers
        |--------------------------------------------------------------------------
        | This keeps the Blade understandable even if the controller only passes:
        | $roles and $permissions.
        */

        $humanize = function (?string $value): string {
            return str($value ?? '')
                ->replace(['-', '_', '.'], ' ')
                ->title()
                ->toString();
        };

        $actionLabel = function (?string $action) use ($humanize): string {
            return match ($action) {
                'view' => 'View / Open Records',
                'create' => 'Create / Save New Record',
                'update' => 'Edit / Update Record',
                'delete' => 'Delete / Remove Record',
                'export' => 'Export / Download Report',
                'approve' => 'Approve / Authorize',
                'finalize' => 'Finalize Transaction',
                'sync' => 'Sync Data',
                'rollback' => 'Rollback Transaction',
                'cancel' => 'Cancel Transaction',
                'receive' => 'Receive Items',
                'analytics' => 'Analytics Dashboard',
                'crm' => 'CRM Dashboard',
                'it' => 'IT Dashboard',
                default => $humanize($action),
            };
        };

        $permissionDescription = function (?string $module, ?string $action) use ($humanize): string {
            return match ($action) {
                'view' => 'Can view lists, record details, print pages, and search records.',
                'create' => 'Can encode and save new records in this module.',
                'update' => 'Can edit existing records and update saved details.',
                'delete' => 'Can delete or remove records from this module.',
                'export' => 'Can download reports such as Excel, PDF, or printable files.',
                'approve' => 'Can approve, disapprove, or authorize submitted records.',
                'finalize' => 'Can finalize records. This usually locks the transaction from normal editing.',
                'sync' => 'Can synchronize external, biometric, or imported data into the system.',
                'rollback' => 'Can reverse a completed transaction and restore affected records or stock balances.',
                'cancel' => 'Can cancel an active transaction before it is fully completed.',
                'receive' => 'Can mark purchase order items as received and update stock/receiving records.',
                default => 'Can perform '.$humanize($action).' action in '.$humanize($module).'.',
            };
        };

        $riskLevel = function (?string $action): string {
            return match ($action) {
                'delete', 'rollback', 'finalize' => 'high',
                'approve', 'update', 'cancel', 'receive', 'sync' => 'medium',
                default => 'low',
            };
        };

        $riskBadgeClass = function (?string $risk): string {
            return match ($risk) {
                'high' => 'danger',
                'medium' => 'warning',
                default => 'success',
            };
        };

        /*
        |--------------------------------------------------------------------------
        | Build Detailed Permission Groups
        |--------------------------------------------------------------------------
        */

        $modulePermissionGroups = $permissions
            ->map(function ($permission) use ($humanize, $actionLabel, $permissionDescription, $riskLevel) {
                $parts = explode('.', $permission->name, 2);

                $module = $parts[0] ?? 'other';
                $action = $parts[1] ?? 'other';

                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'module' => $module,
                    'module_label' => $humanize($module),
                    'action' => $action,
                    'action_label' => $actionLabel($action),
                    'description' => $permissionDescription($module, $action),
                    'risk' => $riskLevel($action),
                ];
            })
            ->groupBy('module_label')
            ->sortKeys();

        $missingRoutePermissions = collect($missingRoutePermissions ?? []);
        $totalPermissions = $permissions->count();
        $totalModules = $modulePermissionGroups->count();
        $syncRouteExists = Route::has('roles.sync-permissions');
    @endphp

    {{-- PAGE HEADER --}}
    <div class="card border-0 shadow-sm mb-4 role-hero-card">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <div class="d-flex align-items-start">
                        <div class="role-hero-icon me-3">
                            <i class="fas fa-user-shield"></i>
                        </div>

                        <div>
                            <h3 class="mb-1 fw-bold">
                                Roles & Permissions
                            </h3>

                            <p class="text-muted mb-0">
                                Manage system access by role, module, action type, and sensitive transaction permission.
                            </p>

                            <div class="mt-2">
                                <span class="badge bg-primary-subtle text-primary me-1">
                                    <i class="fas fa-layer-group me-1"></i>
                                    Module-based
                                </span>

                                <span class="badge bg-warning-subtle text-warning me-1">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Risk-labeled
                                </span>

                                <span class="badge bg-success-subtle text-success">
                                    <i class="fas fa-check-circle me-1"></i>
                                    Spatie Ready
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 text-lg-end">
                    @if ($syncRouteExists)
                        @can('roles.update')
                            <form action="{{ route('roles.sync-permissions') }}" method="POST" class="d-inline">
                                @csrf

                                <button type="submit" class="btn btn-falcon-default me-2">
                                    <i class="fas fa-sync-alt me-1"></i>
                                    Sync Permissions
                                </button>
                            </form>
                        @endcan
                    @endif

                    @can('roles.create')
                        <button
                            type="button"
                            class="btn btn-primary"
                            onclick="openCreateRole()"
                            data-bs-toggle="modal"
                            data-bs-target="#roleModal"
                        >
                            <i class="fas fa-plus me-1"></i>
                            Add Role
                        </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    {{-- ALERTS --}}
    @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm">
            <i class="fas fa-check-circle me-1"></i>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm">
            <i class="fas fa-exclamation-circle me-1"></i>
            {{ session('error') }}
        </div>
    @endif

    {{-- MISSING PERMISSIONS ALERT --}}
    @if ($missingRoutePermissions->isNotEmpty())
        <div class="alert alert-warning border-0 shadow-sm">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <h6 class="fw-bold mb-1">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Missing Route Permissions Detected
                    </h6>

                    <p class="mb-2">
                        These permissions exist in your route middleware but are not yet saved in your permissions table.
                    </p>

                    @foreach ($missingRoutePermissions as $missingPermission)
                        <span class="badge bg-warning text-dark me-1 mb-1">
                            {{ $missingPermission }}
                        </span>
                    @endforeach
                </div>

                @if ($syncRouteExists)
                    @can('roles.update')
                        <form action="{{ route('roles.sync-permissions') }}" method="POST">
                            @csrf

                            <button type="submit" class="btn btn-warning btn-sm">
                                <i class="fas fa-sync-alt me-1"></i>
                                Create Missing
                            </button>
                        </form>
                    @endcan
                @endif
            </div>
        </div>
    @endif

    {{-- STATS --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm stat-card stat-primary">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-semibold">
                            Total Roles
                        </small>

                        <h3 class="mb-0 fw-bold">
                            {{ $roles->count() }}
                        </h3>
                    </div>

                    <div class="stat-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm stat-card stat-success">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-semibold">
                            Permissions
                        </small>

                        <h3 class="mb-0 fw-bold">
                            {{ $totalPermissions }}
                        </h3>
                    </div>

                    <div class="stat-icon">
                        <i class="fas fa-key"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm stat-card stat-warning">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-semibold">
                            Modules
                        </small>

                        <h3 class="mb-0 fw-bold">
                            {{ $totalModules }}
                        </h3>
                    </div>

                    <div class="stat-icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm stat-card stat-info">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted fw-semibold">
                            Users
                        </small>

                        <h3 class="mb-0 fw-bold">
                            {{ \App\Models\User::count() }}
                        </h3>
                    </div>

                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ROLES TABLE --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom">
            <div class="row align-items-center g-2">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-search"></i>
                        </span>

                        <input
                            type="text"
                            id="roleSearch"
                            class="form-control border-start-0"
                            placeholder="Search role name or permission..."
                        >
                    </div>
                </div>

                <div class="col-md-6 text-md-end">
                    <span class="text-muted fs-11">
                        Showing {{ $roles->count() }} role(s)
                    </span>
                </div>
            </div>
        </div>

        <div class="table-responsive overflow-visible">
            <table class="table align-middle table-hover mb-0 role-table">
                <thead class="bg-light">
                    <tr>
                        <th style="min-width: 220px;">
                            Role
                        </th>

                        <th style="min-width: 360px;">
                            Permission Summary
                        </th>

                        <th style="width: 220px;">
                            Access Coverage
                        </th>

                        <th style="width: 160px;">
                            Sensitive Access
                        </th>

                        <th class="text-end" style="width: 90px;">
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody id="roleBody">
                    @forelse ($roles as $role)
                        @php
                            $rolePermissionNames = $role->permissions->pluck('name');
                            $assigned = $rolePermissionNames->count();
                            $percent = $totalPermissions > 0 ? ($assigned / $totalPermissions) * 100 : 0;

                            $roleDetailedPermissions = $rolePermissionNames->map(function ($permissionName) use ($riskLevel) {
                                $parts = explode('.', $permissionName, 2);

                                return [
                                    'name' => $permissionName,
                                    'module' => $parts[0] ?? 'other',
                                    'action' => $parts[1] ?? 'other',
                                    'risk' => $riskLevel($parts[1] ?? 'other'),
                                ];
                            });

                            $highRiskCount = $roleDetailedPermissions->where('risk', 'high')->count();
                            $mediumRiskCount = $roleDetailedPermissions->where('risk', 'medium')->count();

                            $moduleCount = $roleDetailedPermissions
                                ->pluck('module')
                                ->unique()
                                ->count();
                        @endphp

                        <tr
                            class="role-row"
                            data-search="{{ strtolower($role->name.' '.$rolePermissionNames->implode(' ')) }}"
                        >
                            {{-- ROLE --}}
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="role-avatar me-3">
                                        {{ strtoupper(str($role->name)->substr(0, 1)) }}
                                    </div>

                                    <div>
                                        <div class="fw-bold role_name text-dark">
                                            {{ $role->name }}
                                        </div>

                                        <small class="text-muted">
                                            {{ $assigned }} permission(s) assigned
                                        </small>

                                        @if (isset($role->users_count))
                                            <div class="fs-11 text-muted">
                                                <i class="fas fa-users me-1"></i>
                                                {{ $role->users_count }} user(s)
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- PERMISSIONS --}}
                            <td>
                                @if ($assigned > 0)
                                    <div class="mb-2">
                                        <span class="badge bg-primary-subtle text-primary me-1">
                                            <i class="fas fa-layer-group me-1"></i>
                                            {{ $moduleCount }} module(s)
                                        </span>

                                        <span class="badge bg-success-subtle text-success me-1">
                                            <i class="fas fa-key me-1"></i>
                                            {{ $assigned }} permission(s)
                                        </span>
                                    </div>

                                    <div class="permission-preview">
                                        @foreach ($rolePermissionNames->take(5) as $permissionName)
                                            <span class="badge bg-light text-dark border me-1 mb-1">
                                                {{ $permissionName }}
                                            </span>
                                        @endforeach

                                        @if ($assigned > 5)
                                            <span class="badge bg-secondary me-1 mb-1">
                                                +{{ $assigned - 5 }} more
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        No permissions assigned
                                    </span>
                                @endif
                            </td>

                            {{-- ACCESS --}}
                            <td>
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">
                                        Coverage
                                    </small>

                                    <small class="fw-semibold">
                                        {{ round($percent) }}%
                                    </small>
                                </div>

                                <div class="progress access-progress">
                                    <div
                                        class="progress-bar"
                                        role="progressbar"
                                        style="width: {{ $percent }}%;"
                                        aria-valuenow="{{ round($percent) }}"
                                        aria-valuemin="0"
                                        aria-valuemax="100"
                                    ></div>
                                </div>

                                <small class="text-muted">
                                    {{ $assigned }} of {{ $totalPermissions }}
                                </small>
                            </td>

                            {{-- SENSITIVE ACCESS --}}
                            <td>
                                @if ($highRiskCount > 0)
                                    <span class="badge bg-danger-subtle text-danger d-inline-block mb-1">
                                        <i class="fas fa-shield-alt me-1"></i>
                                        {{ $highRiskCount }} high risk
                                    </span>
                                @else
                                    <span class="badge bg-success-subtle text-success d-inline-block mb-1">
                                        <i class="fas fa-check-circle me-1"></i>
                                        No high risk
                                    </span>
                                @endif

                                @if ($mediumRiskCount > 0)
                                    <div>
                                        <span class="badge bg-warning-subtle text-warning">
                                            {{ $mediumRiskCount }} medium
                                        </span>
                                    </div>
                                @endif
                            </td>

                            {{-- ACTIONS --}}
                            <td class="text-end">
                                <div class="dropdown">
                                    <button
                                        class="btn btn-falcon-default btn-sm"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false"
                                    >
                                        <i class="fas fa-ellipsis-h"></i>
                                    </button>

                                    <div class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                        @can('roles.update')
                                            <button
                                                type="button"
                                                class="dropdown-item"
                                                onclick='openEditRole(
                                                    {{ $role->id }},
                                                    @json($role->name),
                                                    @json($rolePermissionNames->values())
                                                )'
                                            >
                                                <i class="fas fa-edit me-2 text-primary"></i>
                                                Edit Role
                                            </button>
                                        @endcan

                                        @can('roles.delete')
                                            <form
                                                action="{{ route('roles.destroy', $role->id) }}"
                                                method="POST"
                                                onsubmit="return confirm('Delete this role? This cannot be undone.')"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fas fa-trash me-2"></i>
                                                    Delete Role
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-user-shield fa-2x mb-3"></i>
                                    <div>No roles found.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ROLE MODAL --}}
    <div class="modal fade" id="roleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-xl-down modal-xl">
            <div class="modal-content border-0 shadow-lg">
                <form id="roleForm" method="POST" action="{{ route('roles.store') }}">
                    @csrf

                    <div class="modal-header bg-white border-bottom">
                        <div>
                            <h4 class="mb-1 fw-bold" id="modalTitle">
                                Add Role
                            </h4>

                            <small class="text-muted">
                                Configure access by module, action, and risk level.
                            </small>
                        </div>

                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        {{-- ROLE DETAILS --}}
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body">
                                <div class="row g-3 align-items-end">
                                    <div class="col-lg-4">
                                        <label class="form-label fw-semibold">
                                            Role Name
                                        </label>

                                        <input
                                            id="roleName"
                                            name="name"
                                            class="form-control"
                                            placeholder="Example: Maintenance Supervisor"
                                            required
                                        >

                                        <small class="text-muted">
                                            Use a clear name based on user responsibility.
                                        </small>
                                    </div>

                                    <div class="col-lg-5">
                                        <label class="form-label fw-semibold">
                                            Search Permission
                                        </label>

                                        <div class="input-group">
                                            <span class="input-group-text bg-light">
                                                <i class="fas fa-search"></i>
                                            </span>

                                            <input
                                                id="permissionSearch"
                                                type="text"
                                                class="form-control"
                                                placeholder="Search module, action, rollback, export, delete..."
                                            >
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="selected-summary">
                                            <small class="text-muted d-block">
                                                Selected Permissions
                                            </small>

                                            <div class="fw-bold fs-4 mb-0">
                                                <span id="selectedPermissionCount">0</span>
                                                <span class="fs-11 text-muted">
                                                    / {{ $totalPermissions }}
                                                </span>
                                            </div>

                                            <div class="progress selected-progress mt-1">
                                                <div id="selectedPermissionBar" class="progress-bar" style="width: 0%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <button type="button" class="btn btn-falcon-default btn-sm" id="selectAllPermissions">
                                        <i class="fas fa-check-double me-1"></i>
                                        Select All
                                    </button>

                                    <button type="button" class="btn btn-falcon-default btn-sm" id="clearAllPermissions">
                                        <i class="fas fa-times me-1"></i>
                                        Clear All
                                    </button>

                                    <button type="button" class="btn btn-falcon-default btn-sm" id="showHighRiskOnly">
                                        <i class="fas fa-shield-alt me-1"></i>
                                        High Risk Only
                                    </button>

                                    <button type="button" class="btn btn-falcon-default btn-sm" id="showAllPermissions">
                                        <i class="fas fa-list me-1"></i>
                                        Show All
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- PERMISSION GUIDE --}}
                        <div class="alert alert-info border-0 shadow-sm mb-3">
                            <div class="row g-2 align-items-center">
                                <div class="col-lg-8">
                                    <strong>
                                        Permission Guide:
                                    </strong>

                                    <span class="text-muted">
                                        High-risk permissions like delete, finalize, and rollback should only be assigned to trusted users.
                                    </span>
                                </div>

                                <div class="col-lg-4 text-lg-end">
                                    <span class="badge bg-success-subtle text-success me-1">
                                        LOW
                                    </span>

                                    <span class="badge bg-warning-subtle text-warning me-1">
                                        MEDIUM
                                    </span>

                                    <span class="badge bg-danger-subtle text-danger">
                                        HIGH
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- PERMISSIONS GRID --}}
                        <div class="row g-3 permissions-grid">
                            @foreach ($modulePermissionGroups as $moduleLabel => $group)
                                @php
                                    $totalGroupPermissions = count($group);
                                    $highRiskCount = collect($group)->where('risk', 'high')->count();
                                    $mediumRiskCount = collect($group)->where('risk', 'medium')->count();
                                @endphp

                                <div
                                    class="col-xxl-3 col-xl-4 col-lg-4 col-md-6 permission-module"
                                    data-module="{{ strtolower($moduleLabel) }}"
                                >
                                    <div class="permission-card h-100">
                                        {{-- MODULE HEADER --}}
                                        <div class="permission-header">
                                            <div class="d-flex align-items-start justify-content-between">
                                                <div class="d-flex align-items-start">
                                                    <div class="form-check me-2 mt-1">
                                                        <input
                                                            type="checkbox"
                                                            class="form-check-input check-group"
                                                            title="Select all in this module"
                                                        >
                                                    </div>

                                                    <div>
                                                        <div class="permission-title">
                                                            {{ $moduleLabel }}
                                                        </div>

                                                        <small class="text-muted">
                                                            {{ $totalGroupPermissions }} permission(s)
                                                        </small>

                                                        <div class="mt-1">
                                                            @if ($highRiskCount > 0)
                                                                <span class="badge bg-danger-subtle text-danger me-1">
                                                                    {{ $highRiskCount }} high
                                                                </span>
                                                            @endif

                                                            @if ($mediumRiskCount > 0)
                                                                <span class="badge bg-warning-subtle text-warning">
                                                                    {{ $mediumRiskCount }} medium
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="permission-icon">
                                                    <i class="fas fa-layer-group"></i>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- MODULE BODY --}}
                                        <div class="permission-body">
                                            @foreach ($group as $permission)
                                                @php
                                                    $badgeClass = $riskBadgeClass($permission['risk']);
                                                @endphp

                                                <label
                                                    class="permission-item d-block"
                                                    data-risk="{{ $permission['risk'] }}"
                                                    data-search="{{ strtolower($permission['name'].' '.$permission['module_label'].' '.$permission['action_label'].' '.$permission['description']) }}"
                                                >
                                                    <div class="d-flex align-items-start">
                                                        <div class="form-check me-2 mt-1">
                                                            <input
                                                                class="form-check-input permission-checkbox"
                                                                type="checkbox"
                                                                name="permissions[]"
                                                                value="{{ $permission['name'] }}"
                                                            >
                                                        </div>

                                                        <div class="flex-grow-1">
                                                            <div class="d-flex justify-content-between gap-2 align-items-start">
                                                                <span class="fw-semibold permission-action">
                                                                    {{ $permission['action_label'] }}
                                                                </span>

                                                                <span class="badge bg-{{ $badgeClass }}-subtle text-{{ $badgeClass }}">
                                                                    {{ strtoupper($permission['risk']) }}
                                                                </span>
                                                            </div>

                                                            <div class="text-muted fs-11 mt-1">
                                                                {{ $permission['description'] }}
                                                            </div>

                                                            <code class="permission-code d-inline-block mt-2">
                                                                {{ $permission['name'] }}
                                                            </code>
                                                        </div>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="modal-footer bg-white border-top">
                        <button type="button" class="btn btn-falcon-default" data-bs-dismiss="modal">
                            Close
                        </button>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            Save Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        .role-hero-card {
            background:
                linear-gradient(135deg, rgba(44, 123, 229, .08), rgba(0, 210, 122, .04)),
                #fff;
        }

        .role-hero-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: rgba(44, 123, 229, .12);
            color: #2c7be5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .stat-card {
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            width: 4px;
            height: 100%;
        }

        .stat-primary::before {
            background: #2c7be5;
        }

        .stat-success::before {
            background: #00d27a;
        }

        .stat-warning::before {
            background: #f6c343;
        }

        .stat-info::before {
            background: #27bcfd;
        }

        .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f6f9fc;
            color: #5e6e82;
            font-size: 18px;
        }

        .role-table thead th {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #5e6e82;
            font-weight: 700;
            border-bottom: 1px solid #edf2f9;
        }

        .role-avatar {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: rgba(44, 123, 229, .1);
            color: #2c7be5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .permission-preview {
            max-width: 520px;
        }

        .access-progress,
        .selected-progress {
            height: 8px;
            border-radius: 999px;
            background: #edf2f9;
        }

        .access-progress .progress-bar,
        .selected-progress .progress-bar {
            background: #2c7be5;
            border-radius: 999px;
        }

        .modal-xl {
            max-width: 1700px;
        }

        .modal-body {
            background: #f6f9fc;
            padding: 1.25rem;
        }

        .permissions-grid {
            align-items: stretch;
        }

        .permission-card {
            background: #fff;
            border-radius: 1rem;
            border: 1px solid #edf2f9;
            overflow: hidden;
            transition: .25s ease;
            box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .04);
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 360px;
        }

        .permission-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 1rem 2rem rgba(0, 0, 0, .08);
        }

        .permission-header {
            padding: 1rem;
            border-bottom: 1px solid #edf2f9;
            background: #fff;
        }

        .permission-title {
            font-size: 13px;
            font-weight: 800;
            color: #344050;
            letter-spacing: .4px;
            text-transform: uppercase;
        }

        .permission-icon {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            background: rgba(44, 123, 229, .1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2c7be5;
            font-size: 13px;
        }

        .permission-body {
            padding: .75rem;
            flex: 1;
            overflow-y: auto;
            max-height: 470px;
        }

        .permission-item {
            border-radius: .85rem;
            padding: .8rem;
            transition: .2s;
            border: 1px solid transparent;
            margin-bottom: .6rem;
            cursor: pointer;
            background: #fff;
        }

        .permission-item:hover {
            background: #f8fafc;
            border-color: #d8e2ef;
        }

        .permission-item.is-selected {
            background: rgba(44, 123, 229, .06);
            border-color: rgba(44, 123, 229, .25);
        }

        .permission-action {
            color: #344050;
            font-size: 13px;
        }

        .permission-code {
            font-size: 11px;
            background: #f6f9fc;
            border: 1px solid #edf2f9;
            color: #5e6e82;
            padding: .2rem .4rem;
            border-radius: .4rem;
        }

        .form-check-input {
            cursor: pointer;
        }

        .permission-checkbox:checked,
        .check-group:checked {
            background-color: #2c7be5;
            border-color: #2c7be5;
        }

        #permissionSearch {
            height: 42px;
        }

        .selected-summary {
            background: #f8fafc;
            border: 1px solid #edf2f9;
            border-radius: .85rem;
            padding: .75rem;
        }

        .permission-body::-webkit-scrollbar {
            width: 6px;
        }

        .permission-body::-webkit-scrollbar-thumb {
            background: #d8e2ef;
            border-radius: 20px;
        }

        .bg-primary-subtle {
            background: rgba(44, 123, 229, .12) !important;
        }

        .bg-success-subtle {
            background: rgba(0, 210, 122, .12) !important;
        }

        .bg-warning-subtle {
            background: rgba(246, 195, 67, .18) !important;
        }

        .bg-danger-subtle {
            background: rgba(230, 55, 87, .12) !important;
        }

        .text-warning {
            color: #b76e00 !important;
        }

        .fs-11 {
            font-size: 11px;
        }

        @media (max-width: 768px) {
            .modal-body {
                padding: .75rem;
            }

            .permission-card {
                min-height: auto;
            }

            .permission-body {
                max-height: none;
            }

            .role-hero-icon {
                width: 44px;
                height: 44px;
                border-radius: 12px;
                font-size: 18px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roleSearch = document.getElementById('roleSearch');
            const permissionSearch = document.getElementById('permissionSearch');
            const roleForm = document.getElementById('roleForm');
            const roleName = document.getElementById('roleName');
            const modalTitle = document.getElementById('modalTitle');
            const selectedPermissionCount = document.getElementById('selectedPermissionCount');
            const selectedPermissionBar = document.getElementById('selectedPermissionBar');

            const totalPermissions = {{ $totalPermissions }};

            function permissionCheckboxes() {
                return Array.from(document.querySelectorAll('.permission-checkbox'));
            }

            function groupCheckboxes() {
                return Array.from(document.querySelectorAll('.check-group'));
            }

            function permissionItems() {
                return Array.from(document.querySelectorAll('.permission-item'));
            }

            function updateSelectedUI() {
                const checkboxes = permissionCheckboxes();
                const checkedCount = checkboxes.filter((checkbox) => checkbox.checked).length;
                const percentage = totalPermissions > 0 ? (checkedCount / totalPermissions) * 100 : 0;

                if (selectedPermissionCount) {
                    selectedPermissionCount.innerText = checkedCount;
                }

                if (selectedPermissionBar) {
                    selectedPermissionBar.style.width = percentage + '%';
                }

                permissionItems().forEach((item) => {
                    const checkbox = item.querySelector('.permission-checkbox');

                    if (!checkbox) {
                        return;
                    }

                    item.classList.toggle('is-selected', checkbox.checked);
                });

                groupCheckboxes().forEach((groupCheckbox) => {
                    const card = groupCheckbox.closest('.permission-card');

                    if (!card) {
                        return;
                    }

                    const cardCheckboxes = Array.from(card.querySelectorAll('.permission-checkbox'));
                    const checkedCardCheckboxes = cardCheckboxes.filter((checkbox) => checkbox.checked);

                    groupCheckbox.checked = cardCheckboxes.length > 0 && checkedCardCheckboxes.length === cardCheckboxes.length;
                    groupCheckbox.indeterminate = checkedCardCheckboxes.length > 0 && checkedCardCheckboxes.length < cardCheckboxes.length;
                });
            }

            function clearMethodInput() {
                const method = roleForm.querySelector('input[name="_method"]');

                if (method) {
                    method.remove();
                }
            }

            function setMethodInput(value) {
                let method = roleForm.querySelector('input[name="_method"]');

                if (!method) {
                    method = document.createElement('input');
                    method.type = 'hidden';
                    method.name = '_method';
                    roleForm.appendChild(method);
                }

                method.value = value;
            }

            function clearPermissionSearch() {
                if (permissionSearch) {
                    permissionSearch.value = '';
                }

                permissionItems().forEach((item) => {
                    item.style.display = '';
                });

                document.querySelectorAll('.permission-module').forEach((module) => {
                    module.style.display = '';
                });
            }

            window.openCreateRole = function () {
                modalTitle.innerText = 'Add Role';
                roleForm.action = "{{ route('roles.store') }}";
                roleName.value = '';

                clearMethodInput();
                clearPermissionSearch();

                permissionCheckboxes().forEach((checkbox) => {
                    checkbox.checked = false;
                });

                updateSelectedUI();
            };

            window.openEditRole = function (id, name, permissions) {
                modalTitle.innerText = 'Edit Role';
                roleForm.action = "{{ url('roles/update') }}/" + id;

                setMethodInput('PUT');
                clearPermissionSearch();

                roleName.value = name;

                permissionCheckboxes().forEach((checkbox) => {
                    checkbox.checked = permissions.includes(checkbox.value);
                });

                updateSelectedUI();

                new bootstrap.Modal(document.getElementById('roleModal')).show();
            };

            if (roleSearch) {
                roleSearch.addEventListener('keyup', function () {
                    const value = this.value.toLowerCase();

                    document.querySelectorAll('.role-row').forEach((row) => {
                        const searchData = row.dataset.search || '';

                        row.style.display = searchData.includes(value) ? '' : 'none';
                    });
                });
            }

            if (permissionSearch) {
                permissionSearch.addEventListener('keyup', function () {
                    const value = this.value.toLowerCase();

                    permissionItems().forEach((item) => {
                        const searchData = item.dataset.search || item.innerText.toLowerCase();

                        item.style.display = searchData.includes(value) ? '' : 'none';
                    });

                    document.querySelectorAll('.permission-module').forEach((module) => {
                        const visibleItems = Array.from(module.querySelectorAll('.permission-item'))
                            .filter((item) => item.style.display !== 'none');

                        module.style.display = visibleItems.length > 0 ? '' : 'none';
                    });
                });
            }

            groupCheckboxes().forEach((groupCheckbox) => {
                groupCheckbox.addEventListener('change', function () {
                    const card = this.closest('.permission-card');

                    if (!card) {
                        return;
                    }

                    card.querySelectorAll('.permission-checkbox').forEach((checkbox) => {
                        checkbox.checked = this.checked;
                    });

                    updateSelectedUI();
                });
            });

            permissionCheckboxes().forEach((checkbox) => {
                checkbox.addEventListener('change', updateSelectedUI);
            });

            const selectAllButton = document.getElementById('selectAllPermissions');
            const clearAllButton = document.getElementById('clearAllPermissions');
            const showHighRiskOnlyButton = document.getElementById('showHighRiskOnly');
            const showAllPermissionsButton = document.getElementById('showAllPermissions');

            if (selectAllButton) {
                selectAllButton.addEventListener('click', function () {
                    permissionCheckboxes().forEach((checkbox) => {
                        checkbox.checked = true;
                    });

                    updateSelectedUI();
                });
            }

            if (clearAllButton) {
                clearAllButton.addEventListener('click', function () {
                    permissionCheckboxes().forEach((checkbox) => {
                        checkbox.checked = false;
                    });

                    updateSelectedUI();
                });
            }

            if (showHighRiskOnlyButton) {
                showHighRiskOnlyButton.addEventListener('click', function () {
                    if (permissionSearch) {
                        permissionSearch.value = '';
                    }

                    permissionItems().forEach((item) => {
                        item.style.display = item.dataset.risk === 'high' ? '' : 'none';
                    });

                    document.querySelectorAll('.permission-module').forEach((module) => {
                        const visibleItems = Array.from(module.querySelectorAll('.permission-item'))
                            .filter((item) => item.style.display !== 'none');

                        module.style.display = visibleItems.length > 0 ? '' : 'none';
                    });
                });
            }

            if (showAllPermissionsButton) {
                showAllPermissionsButton.addEventListener('click', function () {
                    clearPermissionSearch();
                });
            }

            updateSelectedUI();
        });
    </script>
@endpush
