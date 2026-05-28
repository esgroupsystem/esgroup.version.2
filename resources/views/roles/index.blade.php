@extends('layouts.app')
@section('title', 'Roles Management')

@section('content')

    @php
        $groups = $permissions->groupBy(function ($item) {
            return explode('.', $item->name)[0];
        });
    @endphp

    {{-- HEADER --}}
    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <div class="row align-items-center">

                <div class="col-lg-8">

                    <h3 class="mb-1">

                        <i class="fas fa-user-shield text-primary me-2"></i>

                        Roles & Permissions

                    </h3>

                    <p class="text-muted mb-0">
                        Create and manage access permissions by department
                    </p>

                </div>

                <div class="col-lg-4 text-end">

                    <button class="btn btn-primary" onclick="openCreateRole()" data-bs-toggle="modal"
                        data-bs-target="#roleModal">

                        <i class="fas fa-plus me-1"></i>

                        Add Role

                    </button>

                </div>

            </div>

        </div>

    </div>

    {{-- STATS --}}
    <div class="row g-3 mb-4">

        <div class="col-md-3">

            <div class="card shadow-sm border-start border-primary border-4">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted">
                            Roles
                        </small>

                        <h3 class="mb-0">
                            {{ $roles->count() }}
                        </h3>

                    </div>

                    <i class="fas fa-user-shield fa-2x text-primary"></i>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card shadow-sm border-start border-success border-4">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted">
                            Permissions
                        </small>

                        <h3 class="mb-0">
                            {{ $permissions->count() }}
                        </h3>

                    </div>

                    <i class="fas fa-key fa-2x text-success"></i>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card shadow-sm border-start border-warning border-4">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted">
                            Modules
                        </small>

                        <h3 class="mb-0">
                            {{ $groups->count() }}
                        </h3>

                    </div>

                    <i class="fas fa-layer-group fa-2x text-warning"></i>

                </div>

            </div>

        </div>

        <div class="col-md-3">

            <div class="card shadow-sm border-start border-info border-4">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <small class="text-muted">
                            Users
                        </small>

                        <h3 class="mb-0">
                            {{ \App\Models\User::count() }}
                        </h3>

                    </div>

                    <i class="fas fa-users fa-2x text-info"></i>

                </div>

            </div>

        </div>

    </div>

    {{-- TABLE --}}
    <div class="card shadow-sm border-0">

        <div class="card-header">

            <div class="row align-items-center">

                <div class="col-md-6">

                    <input type="text" id="roleSearch" class="form-control" placeholder="Search role...">

                </div>

                <div class="col-md-6 text-end">

                    <button class="btn btn-falcon-default">

                        <i class="fas fa-download me-1"></i>

                        Export

                    </button>

                </div>

            </div>

        </div>

        <div id="roleTable">

            <div class="table-responsive overflow-visible">

                <table class="table align-middle table-hover mb-0">

                    <thead class="bg-light">

                        <tr>

                            <th>Role</th>

                            <th>Permissions</th>

                            <th width="170">
                                Access
                            </th>

                            <th width="80"></th>

                        </tr>

                    </thead>

                    <tbody id="roleBody">

                        @foreach ($roles as $role)
                            @php
                                $total = $permissions->count();
                                $assigned = $role->permissions->count();

                                $percent = $total ? ($assigned / $total) * 100 : 0;
                            @endphp

                            <tr class="role-row">

                                {{-- ROLE --}}
                                <td>

                                    <div class="fw-bold role_name">

                                        {{ $role->name }}

                                    </div>

                                    <small class="text-muted">

                                        Role Access

                                    </small>

                                </td>

                                {{-- PERMISSIONS --}}
                                <td>

                                    @forelse($role->permissions->take(4) as $permission)
                                        <span class="badge bg-primary me-1 mb-1">

                                            {{ $permission->name }}

                                        </span>

                                    @empty

                                        <span class="text-muted">

                                            No permissions

                                        </span>
                                    @endforelse

                                    @if ($role->permissions->count() > 4)
                                        <span class="badge bg-secondary">

                                            +{{ $role->permissions->count() - 4 }}

                                        </span>
                                    @endif

                                </td>

                                {{-- ACCESS --}}
                                <td>

                                    <div class="progress" style="height: 10px;">

                                        <div class="progress-bar" style="width: {{ $percent }}%">
                                        </div>

                                    </div>

                                    <small class="text-muted">

                                        {{ round($percent) }}%

                                    </small>

                                </td>

                                {{-- ACTIONS --}}
                                <td class="text-end">

                                    <div class="dropdown">

                                        <button class="btn btn-falcon-default btn-sm" data-bs-toggle="dropdown">

                                            <i class="fas fa-ellipsis-h"></i>

                                        </button>

                                        <div class="dropdown-menu dropdown-menu-end shadow-sm border-0">

                                            {{-- EDIT --}}
                                            <button type="button" class="dropdown-item"
                                                onclick='openEditRole(
                                                {{ $role->id }},
                                                @json($role->name),
                                                @json($role->permissions->pluck('name'))
                                            )'>

                                                <i class="fas fa-edit me-2 text-primary"></i>

                                                Edit

                                            </button>

                                            {{-- DELETE --}}
                                            <form action="{{ route('roles.destroy', $role->id) }}" method="POST"
                                                onsubmit="return confirm('Delete this role?')">

                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="dropdown-item text-danger">

                                                    <i class="fas fa-trash me-2"></i>

                                                    Delete

                                                </button>

                                            </form>

                                        </div>

                                    </div>

                                </td>

                            </tr>
                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    {{-- MODAL --}}
    <div class="modal fade" id="roleModal" tabindex="-1">

        <div class="modal-dialog modal-fullscreen-xl-down modal-xl">

            <div class="modal-content border-0 shadow-lg">

                <form id="roleForm" method="POST" action="{{ route('roles.store') }}">

                    @csrf

                    <div class="modal-header bg-white">

                        <div>

                            <h4 class="mb-1 fw-bold" id="modalTitle">

                                Add Role

                            </h4>

                            <small class="text-muted">

                                Configure role access and permissions

                            </small>

                        </div>

                        <button type="button" class="btn-close" data-bs-dismiss="modal">
                        </button>

                    </div>

                    <div class="modal-body">

                        {{-- TOP --}}
                        <div class="card border mb-3">

                            <div class="card-body">

                                <div class="row g-3">

                                    <div class="col-md-5">

                                        <label class="form-label fw-semibold">

                                            Role Name

                                        </label>

                                        <input id="roleName" name="name" class="form-control"
                                            placeholder="Example: HR Manager" required>

                                    </div>

                                    <div class="col-md-7">

                                        <label class="form-label fw-semibold">

                                            Search Permission

                                        </label>

                                        <div class="input-group">

                                            <span class="input-group-text bg-light">

                                                <i class="fas fa-search"></i>

                                            </span>

                                            <input id="permissionSearch" class="form-control"
                                                placeholder="Search permission...">

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                        {{-- PERMISSIONS --}}
                        <div class="row g-3 permissions-grid">

                            @foreach ($groups as $module => $group)
                                <div class="col-xxl-3 col-xl-4 col-lg-4 col-md-6">

                                    <div class="permission-card h-100">

                                        {{-- HEADER --}}
                                        <div class="permission-header">

                                            <div class="d-flex align-items-start justify-content-between">

                                                <div class="d-flex align-items-center">

                                                    <div class="form-check me-2">

                                                        <input type="checkbox" class="form-check-input check-group">

                                                    </div>

                                                    <div>

                                                        <div class="permission-title">

                                                            {{ strtoupper(str_replace('-', ' ', $module)) }}

                                                        </div>

                                                        <small class="text-muted">

                                                            {{ count($group) }} Permissions

                                                        </small>

                                                    </div>

                                                </div>

                                                <div class="permission-icon">

                                                    <i class="fas fa-layer-group"></i>

                                                </div>

                                            </div>

                                        </div>

                                        {{-- BODY --}}
                                        <div class="permission-body">

                                            @foreach ($group as $permission)
                                                <div class="permission-item">

                                                    <div class="form-check mb-0">

                                                        <input class="form-check-input permission-checkbox"
                                                            type="checkbox" name="permissions[]"
                                                            value="{{ $permission->name }}">

                                                        <label class="form-check-label">

                                                            {{ str_replace('.', ' → ', $permission->name) }}

                                                        </label>

                                                    </div>

                                                </div>
                                            @endforeach

                                        </div>

                                    </div>

                                </div>
                            @endforeach

                        </div>

                    </div>

                    <div class="modal-footer bg-white">

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
        /* =========================
                               MODAL
                            ========================== */

        .modal-xl {

            max-width: 1700px;

        }

        .modal-body {

            background: #f6f9fc;

            padding: 1.25rem;

        }

        /* =========================
                               GRID
                            ========================== */

        .permissions-grid {

            align-items: stretch;

        }

        /* =========================
                               CARD
                            ========================== */

        .permission-card {

            background: #fff;

            border-radius: 1rem;

            border: 1px solid #edf2f9;

            overflow: hidden;

            transition: .25s ease;

            box-shadow:
                0 .125rem .25rem rgba(0, 0, 0, .04);

            display: flex;

            flex-direction: column;

            height: 100%;

            min-height: 320px;

        }

        .permission-card:hover {

            transform: translateY(-4px);

            box-shadow:
                0 1rem 2rem rgba(0, 0, 0, .08);

        }

        /* =========================
                               HEADER
                            ========================== */

        .permission-header {

            padding: 1rem;

            border-bottom: 1px solid #edf2f9;

            background: #fff;

        }

        .permission-title {

            font-size: 13px;

            font-weight: 700;

            color: #344050;

            letter-spacing: .4px;

        }

        .permission-icon {

            width: 34px;
            height: 34px;

            border-radius: 10px;

            background: rgba(44, 123, 229, .1);

            display: flex;
            align-items: center;
            justify-content: center;

            color: #2c7be5;

            font-size: 13px;

        }

        /* =========================
                               BODY
                            ========================== */

        .permission-body {

            padding: .75rem;

            flex: 1;

            overflow-y: auto;

            max-height: 420px;

        }

        /* =========================
                               ITEMS
                            ========================== */

        .permission-item {

            border-radius: .75rem;

            padding: .7rem .75rem;

            transition: .2s;

            border: 1px solid transparent;

            margin-bottom: .5rem;

        }

        .permission-item:hover {

            background: #f8fafc;

            border-color: #d8e2ef;

        }

        .permission-item label {

            width: 100%;

            cursor: pointer;

            color: #5e6e82;

            font-size: 13px;

            font-weight: 500;

        }

        /* =========================
                               CHECKBOX
                            ========================== */

        .form-check-input {

            cursor: pointer;

        }

        .permission-checkbox:checked,
        .check-group:checked {

            background-color: #2c7be5;

            border-color: #2c7be5;

        }

        /* =========================
                               SEARCH
                            ========================== */

        #permissionSearch {

            height: 46px;

            border-radius: .75rem;

        }

        /* =========================
                               SCROLLBAR
                            ========================== */

        .permission-body::-webkit-scrollbar {

            width: 6px;

        }

        .permission-body::-webkit-scrollbar-thumb {

            background: #d8e2ef;

            border-radius: 20px;

        }

        /* =========================
                               MOBILE
                            ========================== */

        @media(max-width:768px) {

            .permission-card {

                min-height: auto;

            }

            .permission-body {

                max-height: none;

            }

        }
    </style>
@endpush


@push('scripts')
    <script>
        // SEARCH
        document.getElementById('roleSearch')
            .addEventListener('keyup', function() {

                let value = this.value.toLowerCase();

                document.querySelectorAll('.role-row')
                    .forEach(row => {

                        let name = row.querySelector('.role_name')
                            .innerText
                            .toLowerCase();

                        row.style.display =
                            name.includes(value) ?
                            '' :
                            'none';

                    });

            });

        // CREATE
        window.openCreateRole = function() {

            document.getElementById('modalTitle').innerText =
                'Add Role';

            document.getElementById('roleForm').action =
                "{{ route('roles.store') }}";

            document.getElementById('roleName').value = '';

            document.querySelectorAll('.permission-checkbox')
                .forEach(x => x.checked = false);

            let method = document.querySelector(
                '#roleForm input[name="_method"]'
            );

            if (method) method.remove();

        };

        // EDIT
        window.openEditRole = function(
            id,
            name,
            permissions
        ) {

            document.getElementById('modalTitle').innerText =
                'Edit Role';

            document.getElementById('roleForm').action =
                "{{ url('roles/update') }}/" + id;

            let method = document.querySelector(
                '#roleForm input[name="_method"]'
            );

            if (!method) {

                method = document.createElement('input');

                method.type = 'hidden';
                method.name = '_method';

                document.getElementById('roleForm')
                    .appendChild(method);

            }

            method.value = 'PUT';

            document.getElementById('roleName').value =
                name;

            document.querySelectorAll('.permission-checkbox')
                .forEach(c => {

                    c.checked = permissions.includes(c.value);

                });

            new bootstrap.Modal(
                document.getElementById('roleModal')
            ).show();

        };

        // GROUP CHECK
        document.querySelectorAll('.check-group')
            .forEach(check => {

                check.addEventListener('change', function() {

                    this.closest('.permission-card')
                        .querySelectorAll('.permission-checkbox')
                        .forEach(x => {

                            x.checked = this.checked;

                        });

                });

            });

        // PERMISSION SEARCH
        document.getElementById('permissionSearch')
            .addEventListener('keyup', function() {

                let value = this.value.toLowerCase();

                document.querySelectorAll('.permission-item')
                    .forEach(item => {

                        item.style.display =
                            item.innerText.toLowerCase()
                            .includes(value)

                            ?
                            ''

                            :
                            'none';

                    });

            });
    </script>
@endpush
