@extends('layouts.app')
@section('title', 'Users | Jell Group')

@section('content')
    <div class="container" data-layout="container">
        <div class="content">

            <!-- Header -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start flex-column flex-lg-row">
                        <div>
                            <h4 class="fw-bold mb-1 text-900">Users Directory</h4>
                            <p class="text-600 fs-9 mb-0">Manage user accounts, roles and access.</p>
                        </div>

                        <div class="mt-3 mt-lg-0">
                            <button class="btn btn-primary btn-sm px-3" data-bs-toggle="modal" data-bs-target="#userModal"
                                onclick="openCreateUser()">
                                <i class="fas fa-user-plus me-1"></i> Add User
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="card shadow-sm">
                <div class="card-body p-0">

                    <div class="table-responsive scrollbar">
                        <table class="table table-hover table-dashboard fs-10 mb-0 align-middle">
                            <thead class="bg-100 text-900">
                                <tr>
                                    <th style="width:230px;">Name</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Last Online</th>
                                    <th>Updated</th>
                                    <th class="text-end pe-3" style="width:50px;">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($users as $u)
                                    <tr>
                                        <!-- Name + email -->
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm rounded-circle bg-soft-primary text-primary me-2"
                                                    style="width:38px; height:38px;">
                                                    <span
                                                        class="fs-8 fw-semi-bold">{{ strtoupper(substr($u->full_name, 0, 1)) }}</span>
                                                </div>
                                                <div>
                                                    <div class="fw-semi-bold">{{ $u->full_name }}</div>
                                                    <div class="text-500 fs-10">{{ $u->email }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        <td>{{ $u->username }}</td>

                                        <td class="text-capitalize">{{ $u->role }}</td>

                                        <td>
                                            @if ($u->account_status == 'active')
                                                <span class="badge rounded-pill bg-soft-success text-success">Active</span>
                                            @else
                                                <span
                                                    class="badge rounded-pill bg-soft-danger text-danger">Deactivated</span>
                                            @endif
                                        </td>

                                        <td>{{ $u->last_online ? $u->last_online->format('M d, Y h:i A') : 'N/A' }}</td>
                                        <td>{{ $u->updated_at->format('M d, Y h:i A') }}</td>

                                        <!-- Actions -->
                                        <td class="text-end pe-3 position-static">
                                            <div class="dropdown font-sans-serif position-static">
                                                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                    type="button" data-bs-toggle="dropdown" data-boundary="window"
                                                    aria-haspopup="true" aria-expanded="false">
                                                    <span class="fas fa-ellipsis-h fs-10"></span>
                                                </button>

                                                <div class="dropdown-menu dropdown-menu-end border py-0 shadow-sm">
                                                    <div class="py-2">

                                                        <!-- Edit -->
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

                                                        <!-- Reset -->
                                                        <button class="dropdown-item reset-btn" data-bs-toggle="modal"
                                                            data-bs-target="#resetModal" data-id="{{ $u->id }}"
                                                            data-name="{{ $u->full_name }}">
                                                            <i class="fas fa-key me-2"></i> Reset Password
                                                        </button>

                                                        <!-- Toggle Status -->
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

                </div>
            </div>

        </div>
    </div>



    <!-- USER MODAL (Create + Edit) -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <form method="POST" id="userForm">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title" id="userModalTitle">Add User</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <input type="hidden" name="id" id="userId">

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="full_name" id="full_name" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" id="username" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" id="email" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" name="role" id="role" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Account Status</label>
                                <select name="account_status" id="account_status" class="form-select">
                                    <option value="active">Active</option>
                                    <option value="deactivated">Deactivated</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-primary btn-sm px-3" id="saveUserBtn">Save</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- RESET MODAL -->
    <div class="modal fade" id="resetModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">

                <form method="POST" id="resetForm">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title">Reset Password</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <p class="mb-2">Reset password for <strong id="resetName"></strong></p>
                        <p class="text-warning fs-9 mb-0">A new password will be auto-generated.</p>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-warning btn-sm px-3" type="submit">Reset</button>
                    </div>

                </form>

            </div>
        </div>
    </div>
@endsection


@push('scripts')
    <script>
        function openCreateUser() {
            document.getElementById("userModalTitle").innerHTML = "Add User";
            document.getElementById("userForm").action = "{{ route('authentication.users.store') }}";

            document.getElementById("userId").value = "";
            document.getElementById("full_name").value = "";
            document.getElementById("username").value = "";
            document.getElementById("email").value = "";
            document.getElementById("role").value = "";
            document.getElementById("account_status").value = "active";
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
    </script>

    <script>
        document.addEventListener("click", function(e) {
            if (e.target.closest(".reset-btn")) {
                let btn = e.target.closest(".reset-btn");
                let name = btn.dataset.name;
                let id = btn.dataset.id;

                document.getElementById("resetName").innerHTML = name;
                document.getElementById("resetForm").action = "/authentication/users/reset-password/" + id;
            }
        });
    </script>
@endpush
