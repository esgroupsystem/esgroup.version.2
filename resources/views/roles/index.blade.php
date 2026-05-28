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
                <div class="card-body d-flex justify-content-between">

                    <div>
                        <small class="text-muted">Roles</small>
                        <h3>{{ $roles->count() }}</h3>
                    </div>

                    <i class="fas fa-user-shield fa-2x text-primary"></i>

                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-start border-success border-4">
                <div class="card-body d-flex justify-content-between">

                    <div>
                        <small class="text-muted">Permissions</small>
                        <h3>{{ $permissions->count() }}</h3>
                    </div>

                    <i class="fas fa-key fa-2x text-success"></i>

                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-start border-warning border-4">
                <div class="card-body d-flex justify-content-between">

                    <div>
                        <small class="text-muted">Modules</small>
                        <h3>{{ $groups->count() }}</h3>
                    </div>

                    <i class="fas fa-layer-group fa-2x text-warning"></i>

                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-start border-info border-4">
                <div class="card-body d-flex justify-content-between">

                    <div>
                        <small class="text-muted">Users</small>
                        <h3>{{ \App\Models\User::count() }}</h3>
                    </div>

                    <i class="fas fa-users fa-2x text-info"></i>

                </div>
            </div>
        </div>

    </div>


    {{-- TABLE --}}
    <div class="card shadow-sm border-0">

        <div class="card-header">

            <div class="row">

                <div class="col-md-6">
                    <input class="form-control search" placeholder="Search role...">
                </div>

                <div class="col-md-6 text-end">
                    <button class="btn btn-falcon-default">
                        <i class="fas fa-download me-1"></i>
                        Export
                    </button>
                </div>

            </div>

        </div>

        <div id="roleTable" data-list='{"valueNames":["role_name"],"page":10,"pagination":true}'>

            <div class="table-responsive">

                <table class="table align-middle table-hover mb-0">

                    <thead class="bg-light">

                        <tr>
                            <th>Role</th>
                            <th>Permissions</th>
                            <th width="150">Access</th>
                            <th width="80"></th>
                        </tr>

                    </thead>

                    <tbody class="list">

                        @foreach ($roles as $role)
                            @php
                                $total = $permissions->count();
                                $assigned = $role->permissions->count();
                                $percent = $total ? ($assigned / $total) * 100 : 0;
                            @endphp

                            <tr>

                                <td class="role_name">

                                    <div class="fw-bold">
                                        {{ $role->name }}
                                    </div>

                                    <small class="text-muted">
                                        Role Access
                                    </small>

                                </td>


                                <td>

                                    @forelse($role->permissions->take(4) as $permission)
                                        <span class="badge bg-primary me-1">

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


                                <td>

                                    <div class="progress">

                                        <div class="progress-bar" style="width:{{ $percent }}%">
                                        </div>

                                    </div>

                                    <small>

                                        {{ round($percent) }}%

                                    </small>

                                </td>


                                <td>

                                    <div class="dropdown">

                                        <button class="btn btn-falcon-default btn-sm" data-bs-toggle="dropdown">

                                            <i class="fas fa-ellipsis-h"></i>

                                        </button>

                                        <div class="dropdown-menu dropdown-menu-end">

                                            <button type="button" class="dropdown-item"
                                                onclick='openEditRole(
                                                    {{ $role->id }},
                                                    @json($role->name),
                                                    @json($role->permissions->pluck('name'))
                                                )'>

                                                <i class="fas fa-edit me-2"></i>
                                                Edit

                                            </button>


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

            <div class="card-footer">
                <div class="pagination"></div>
            </div>

        </div>

    </div>


    {{-- MODAL --}}
    {{-- MODAL --}}
    <div class="modal fade" id="roleModal" tabindex="-1">

        <div class="modal-dialog modal-fullscreen-xl-down modal-xl">

            <div class="modal-content border-0 shadow-lg">

                <form id="roleForm" method="POST" action="{{ route('roles.store') }}">

                    @csrf

                    <div class="modal-header bg-white d-flex justify-content-between align-items-start">

                        <div>

                            <h4 class="mb-1 fw-bold" id="modalTitle">
                                Add Role
                            </h4>

                            <small class="text-muted">
                                Configure role access and permissions
                            </small>

                        </div>

                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        </button>

                    </div>


                    <div class="modal-body">

                        <div class="card border mb-3">

                            <div class="card-body">

                                <div class="row">

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
                                                placeholder="Search module or permission">

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>


                        <div class="row g-2">

                            @foreach ($groups as $module => $group)
                                <div class="col-xxl-3 col-lg-4 col-md-6">

                                    <div class="permission-card">

                                        <div class="permission-header">

                                            <div class="d-flex justify-content-between align-items-center">

                                                <div class="d-flex align-items-center">

                                                    <input type="checkbox" class="check-group form-check-input me-2">

                                                    <div>

                                                        <div class="fw-bold text-uppercase small">

                                                            {{ str_replace('-', ' ', $module) }}

                                                        </div>

                                                        <small class="text-muted">

                                                            Module

                                                        </small>

                                                    </div>

                                                </div>

                                                <span class="badge rounded-pill bg-primary">

                                                    {{ count($group) }}

                                                </span>

                                            </div>

                                        </div>


                                        <div class="permission-body">

                                            @foreach ($group as $permission)
                                                <div class="permission-item">

                                                    <div class="form-check">

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
        /* MODAL */
        .modal-xl {
            max-width: 1600px;
        }

        .modal-content {
            border: 0;
            border-radius: 1rem;
            overflow: hidden;
        }

        .modal-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #edf2f9;
        }

        .modal-body {
            background: #f9fbfd;
            padding: 1rem;
            max-height: 75vh;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #edf2f9;
            background: #fff;
        }


        /* SEARCH BAR */
        #permissionSearch {
            border-radius: .75rem;
            height: 46px;
            border: 1px solid #d8e2ef;
            padding-left: 15px;
            box-shadow: none;
        }

        #permissionSearch:focus {
            border-color: #2c7be5;
            box-shadow: 0 0 0 .15rem rgba(44, 123, 229, .15);
        }


        /* GRID */
        .permission-card {
            border: 1px solid #edf2f9;
            border-radius: 1rem;
            overflow: hidden;
            background: #fff;
            transition: .25s;
            box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .03);

            height: 260px;
        }

        .permission-card:hover {
            transform: translateY(-3px);
            box-shadow:
                0 .5rem 1rem rgba(0, 0, 0, .08);
        }


        /* HEADER */
        .permission-header {
            background: #fff;
            padding: .85rem 1rem;
            border-bottom: 1px solid #edf2f9;
            min-height: 58px;
        }

        .permission-header .badge {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }


        /* BODY */
        .permission-body {
            height: 190px;
            overflow-y: auto;
            overflow-x: hidden;
            padding: .75rem;
        }


        /* PERMISSION ITEMS */
        .permission-item {
            border-radius: .6rem;
            padding: 10px 12px;
            margin-bottom: 6px;
            transition: .2s;
            border: 1px solid transparent;
        }

        .permission-item:hover {
            background: #f8fafc;
            border-color: #edf2f9;
        }

        .permission-item label {
            cursor: pointer;
            width: 100%;
            color: #5e6e82;
        }


        /* CHECKBOX */
        .form-check-input {
            cursor: pointer;
        }

        .permission-checkbox:checked {
            background-color: #2c7be5;
            border-color: #2c7be5;
        }


        /* SCROLLBAR */
        .permission-body::-webkit-scrollbar {
            width: 6px;
        }

        .permission-body::-webkit-scrollbar-track {
            background: #f1f3f7;
        }

        .permission-body::-webkit-scrollbar-thumb {
            background: #d8e2ef;
            border-radius: 50px;
        }

        .permission-body::-webkit-scrollbar-thumb:hover {
            background: #b6c1d2;
        }


        /* MOBILE */
        @media(max-width:768px) {

            .permission-card {
                height: auto;
            }

            .permission-body {
                height: auto;
                max-height: 220px;
            }

        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            new List("roleTable", {
                valueNames: ['role_name'],
                page: 10,
                pagination: true
            });

        });


        window.openCreateRole = function() {

            document.getElementById('modalTitle').innerText = 'Add Role';

            document.getElementById('roleForm').action =
                "{{ route('roles.store') }}";

            document.getElementById('roleName').value = '';

            document
                .querySelectorAll('.permission-checkbox')
                .forEach(x => x.checked = false);

            let method = document.querySelector(
                '#roleForm input[name="_method"]'
            );

            if (method) {
                method.remove();
            }

        };


        window.openEditRole = function(
            id,
            name,
            permissions
        ) {

            document.getElementById(
                'modalTitle'
            ).innerText = 'Edit Role';

            document.getElementById(
                    'roleForm'
                ).action =
                "{{ url('roles/update') }}/" + id;


            let method = document.querySelector(
                '#roleForm input[name="_method"]'
            );

            if (!method) {

                method = document.createElement(
                    'input'
                );

                method.type = 'hidden';
                method.name = '_method';

                document
                    .getElementById('roleForm')
                    .appendChild(method);
            }

            // FIX HERE
            method.value = 'PUT';


            document.getElementById(
                'roleName'
            ).value = name;


            document
                .querySelectorAll(
                    '.permission-checkbox'
                )
                .forEach(c => {

                    c.checked =
                        permissions.includes(
                            c.value
                        );

                });


            let modal = new bootstrap.Modal(
                document.getElementById(
                    'roleModal'
                )
            );

            modal.show();

        };

        document
            .querySelectorAll('.check-group')
            .forEach(check => {

                check.addEventListener(
                    'change',
                    function() {

                        this.closest('.permission-card')

                            .querySelectorAll(
                                '.permission-checkbox'
                            )

                            .forEach(x => {

                                x.checked =
                                    this.checked;

                            });

                    });

            });



        document
            .getElementById(
                'permissionSearch'
            )

            .addEventListener(
                'keyup',

                function() {

                    let value =
                        this.value.toLowerCase();

                    document
                        .querySelectorAll(
                            '.permission-item'
                        )

                        .forEach(item => {

                            item.style.display =

                                item.innerText
                                .toLowerCase()
                                .includes(value)

                                ?

                                ''

                                :

                                'none';

                        });

                });
    </script>
@endpush
