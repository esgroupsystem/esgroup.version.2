@extends('layouts.app')
@section('title', 'Users Management')

@section('content')
    <div class="container" data-layout="container">
        <script>
            var isFluid = JSON.parse(localStorage.getItem('isFluid'));
            if (isFluid) {
                var container = document.querySelector('[data-layout]');
                container.classList.remove('container');
                container.classList.add('container-fluid');
            }
        </script>

        <div class="content">

            {{-- 🧩 TOP CARD --}}
            <div class="card mb-4">
                <div class="bg-holder d-none d-lg-block bg-card"
                    style="background-image:url(/assets/img/icons/spot-illustrations/corner-4.png);">
                </div>

                <div class="card-body position-relative">
                    <div class="row">
                        <div class="col-lg-8">
                            <h3 class="mb-2">Users Directory</h3>
                            <p class="text-muted">
                                Manage user accounts, roles, access level, and login status.
                            </p>
                        </div>
                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal"
                                onclick="openCreateUser()">
                                <i class="fas fa-user-plus me-1"></i> Add User
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 🧭 TABLE CARD --}}
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6 class="mb-0">User List</h6>
                </div>

                {{-- SEARCH ONLY --}}
                <div class="p-3">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <input class="form-control form-control-sm search" placeholder="Search user...">
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div id="userTable"
                        data-list='{"valueNames":["name","username","role","status"],"page":10,"pagination":true}'>

                        <div class="table-responsive scrollbar">
                            <table class="table table-hover table-striped fs-10 mb-0">
                                <thead class="bg-200 text-900">
                                    <tr>
                                        <th class="sort" data-sort="name">Name</th>
                                        <th class="sort" data-sort="username">Username</th>
                                        <th class="sort" data-sort="role">Role</th>
                                        <th class="sort" data-sort="status">Status</th>
                                        <th>Last Online</th>
                                        <th>Updated</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>

                                <tbody class="list">
                                    @foreach ($users as $u)
                                        <tr>
                                            <td class="name">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm rounded-circle bg-soft-primary text-primary me-2"
                                                        style="width:38px; height:38px;">
                                                        <span class="fs-8 fw-semi-bold">
                                                            {{ strtoupper(substr($u->full_name, 0, 1)) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semi-bold">{{ $u->full_name }}</div>
                                                        <div class="text-500 fs-10">{{ $u->email }}</div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="username">{{ $u->username }}</td>
                                            <td class="role text-capitalize">{{ $u->role }}</td>

                                            <td class="status">
                                                @if ($u->account_status == 'active')
                                                    <span
                                                        class="badge rounded-pill bg-soft-success text-success">Active</span>
                                                @else
                                                    <span
                                                        class="badge rounded-pill bg-soft-danger text-danger">Deactivated</span>
                                                @endif
                                            </td>

                                            <td>{{ $u->last_online ? $u->last_online->format('M d, Y h:i A') : 'N/A' }}</td>
                                            <td>{{ $u->updated_at->format('M d, Y h:i A') }}</td>

                                            <td class="text-end">
                                                <div class="dropdown font-sans-serif position-static">
                                                    <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                        type="button" data-bs-toggle="dropdown">
                                                        <span class="fas fa-ellipsis-h fs-10"></span>
                                                    </button>

                                                    <div class="dropdown-menu dropdown-menu-end border py-0 shadow-sm">
                                                        <div class="py-2">

                                                            {{-- EDIT --}}
                                                            <button class="dropdown-item"
                                                                onclick="openEditUser({
                                                                id: '{{ $u->id }}',
                                                                full_name: '{{ $u->full_name }}',
                                                                username: '{{ $u->username }}',
                                                                email: '{{ $u->email }}',
                                                                role: '{{ $u->role }}',
                                                                status: '{{ $u->account_status }}'
                                                            })">
                                                                <i class="fas fa-edit me-2"></i> Edit
                                                            </button>

                                                            {{-- RESET PASSWORD --}}
                                                            <button class="dropdown-item reset-btn" data-bs-toggle="modal"
                                                                data-bs-target="#resetModal" data-id="{{ $u->id }}"
                                                                data-name="{{ $u->full_name }}">
                                                                <i class="fas fa-key me-2"></i> Reset Password
                                                            </button>

                                                            {{-- ACTIVATE / DEACTIVATE (Instant Action) --}}
                                                            <a class="dropdown-item"
                                                                href="{{ route('authentication.users.status', $u->id) }}">
                                                                <i class="fas fa-toggle-on me-2"></i>
                                                                {{ $u->account_status === 'active' ? 'Deactivate' : 'Activate' }}
                                                            </a>

                                                        </div>
                                                    </div>

                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- PAGINATION --}}
                        <div class="d-flex justify-content-center my-3">
                            <button class="btn btn-sm btn-falcon-default me-1" data-list-pagination="prev">
                                <span class="fas fa-chevron-left"></span>
                            </button>

                            <ul class="pagination mb-0"></ul>

                            <button class="btn btn-sm btn-falcon-default ms-1" data-list-pagination="next">
                                <span class="fas fa-chevron-right"></span>
                            </button>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- USER MODAL --}}
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <form method="POST" id="userForm">
                    @csrf

                    <div class="modal-header bg-light">
                        <h5 class="modal-title" id="userModalTitle">Add User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <input type="hidden" id="userId" name="id">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <select name="role" id="role" class="form-select" required>
                                    @foreach ($roles as $r)
                                        <option value="{{ $r->name }}">{{ $r->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Account Status</label>
                                <select name="account_status" id="account_status" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="deactivated">Deactivated</option>
                                </select>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    {{-- RESET PASSWORD MODAL --}}
    <div class="modal fade" id="resetModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <form method="POST" id="resetForm">
                    @csrf

                    <div class="modal-header bg-light">
                        <h5 class="modal-title">Reset Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <p id="resetText" class="fs-9"></p>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-sm">Reset Now</button>
                    </div>

                </form>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const userTable = new List("userTable", {
                valueNames: ["name", "username", "role", "status"],
                page: 10,
                pagination: true
            });

            document.querySelector(".search").addEventListener("keyup", e => {
                userTable.search(e.target.value);
            });
        });

        function openCreateUser() {
            document.getElementById("userModalTitle").innerHTML = "Add User";
            document.getElementById("userForm").action = "{{ route('authentication.users.store') }}";

            document.getElementById("userId").value = "";
            document.getElementById("full_name").value = "";
            document.getElementById("username").value = "";
            document.getElementById("email").value = "";
            document.getElementById("role").value = "";
        }

        function openEditUser(user) {
            document.getElementById("userModalTitle").innerHTML = "Edit User";
            document.getElementById("userForm").action = "/authentication/users/update/" + user.id;

            document.getElementById("userId").value = user.id;
            document.getElementById("full_name").value = user.full_name;
            document.getElementById("username").value = user.username;
            document.getElementById("email").value = user.email;
            document.getElementById("role").value = user.role;

            document.getElementById("account_status").value = user.status;

            new bootstrap.Modal(document.getElementById("userModal")).show();
        }

        document.addEventListener("click", function(e) {
            if (e.target.closest(".reset-btn")) {

                let id = e.target.closest(".reset-btn").dataset.id;
                let name = e.target.closest(".reset-btn").dataset.name;

                document.getElementById("resetText").innerHTML =
                    "Are you sure you want to reset the password for <strong>" + name + "</strong>?";

                document.getElementById("resetForm").action =
                    "/authentication/users/reset-password/" + id;
            }
        });
    </script>
@endpush
