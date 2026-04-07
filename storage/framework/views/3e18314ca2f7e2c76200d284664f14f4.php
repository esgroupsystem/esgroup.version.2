<?php $__env->startSection('title', 'Users Management'); ?>

<?php $__env->startSection('content'); ?>
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

            
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6 class="mb-0">User List</h6>
                </div>

                
                <div class="p-3">
                    <form method="GET" action="<?php echo e(url()->current()); ?>">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-4">
                                <input name="q" value="<?php echo e(request('q')); ?>" class="form-control form-control-sm"
                                    placeholder="Search user...">
                            </div>

                            <div class="col-auto">
                                <button class="btn btn-sm btn-falcon-default" type="submit">
                                    <i class="fas fa-search me-1"></i> Search
                                </button>

                                <?php if(request()->filled('q')): ?>
                                    <a class="btn btn-sm btn-falcon-default ms-1" href="<?php echo e(url()->current()); ?>">
                                        Clear
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive scrollbar">
                        <table class="table table-hover table-striped fs-10 mb-0">
                            <thead class="bg-200 text-900">
                                <tr>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Last Online</th>
                                    <th>Updated</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm rounded-circle bg-soft-primary text-primary me-2"
                                                    style="width:38px; height:38px;">
                                                    <span class="fs-8 fw-semi-bold">
                                                        <?php echo e(strtoupper(substr($u->full_name, 0, 1))); ?>

                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="fw-semi-bold"><?php echo e($u->full_name); ?></div>
                                                    <div class="text-500 fs-10"><?php echo e($u->email); ?></div>
                                                </div>
                                            </div>
                                        </td>

                                        <td><?php echo e($u->username); ?></td>
                                        <td class="text-capitalize"><?php echo e($u->role); ?></td>

                                        <td>
                                            <?php if($u->account_status == 'active'): ?>
                                                <span class="badge rounded-pill bg-soft-success text-success">Active</span>
                                            <?php else: ?>
                                                <span
                                                    class="badge rounded-pill bg-soft-danger text-danger">Deactivated</span>
                                            <?php endif; ?>
                                        </td>

                                        <td><?php echo e($u->last_online ? $u->last_online->format('M d, Y h:i A') : 'N/A'); ?></td>
                                        <td><?php echo e($u->updated_at->format('M d, Y h:i A')); ?></td>

                                        <td class="text-end">
                                            <div class="dropdown font-sans-serif position-static">
                                                <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                    type="button" data-bs-toggle="dropdown">
                                                    <span class="fas fa-ellipsis-h fs-10"></span>
                                                </button>

                                                <div class="dropdown-menu dropdown-menu-end border py-0 shadow-sm">
                                                    <div class="py-2">

                                                        
                                                        <button class="dropdown-item"
                                                            onclick="openEditUser({
                                                                id: '<?php echo e($u->id); ?>',
                                                                full_name: <?php echo \Illuminate\Support\Js::from($u->full_name)->toHtml() ?>,
                                                                username: <?php echo \Illuminate\Support\Js::from($u->username)->toHtml() ?>,
                                                                email: <?php echo \Illuminate\Support\Js::from($u->email)->toHtml() ?>,
                                                                role: <?php echo \Illuminate\Support\Js::from($u->role)->toHtml() ?>,
                                                                status: <?php echo \Illuminate\Support\Js::from($u->account_status)->toHtml() ?>
                                                            })">
                                                            <i class="fas fa-edit me-2"></i> Edit
                                                        </button>

                                                        
                                                        <button class="dropdown-item reset-btn" data-bs-toggle="modal"
                                                            data-bs-target="#resetModal" data-id="<?php echo e($u->id); ?>"
                                                            data-name="<?php echo e($u->full_name); ?>">
                                                            <i class="fas fa-key me-2"></i> Reset Password
                                                        </button>

                                                        
                                                        <a class="dropdown-item"
                                                            href="<?php echo e(route('authentication.users.status', $u->id)); ?>">
                                                            <i class="fas fa-toggle-on me-2"></i>
                                                            <?php echo e($u->account_status === 'active' ? 'Deactivate' : 'Activate'); ?>

                                                        </a>

                                                    </div>
                                                </div>

                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            No users found.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    
                    <div class="p-3">
                        <?php echo e($users->links('pagination.custom')); ?>

                    </div>
                </div>
            </div>

        </div>
    </div>

    
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <form method="POST" id="userForm">
                    <?php echo csrf_field(); ?>

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
                                    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($r->name); ?>"><?php echo e($r->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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

    
    <div class="modal fade" id="resetModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <form method="POST" id="resetForm">
                    <?php echo csrf_field(); ?>

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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        function openCreateUser() {
            document.getElementById("userModalTitle").innerHTML = "Add User";
            document.getElementById("userForm").action = "<?php echo e(route('authentication.users.store')); ?>";

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

        document.addEventListener("click", function(e) {
            const btn = e.target.closest(".reset-btn");
            if (!btn) return;

            let id = btn.dataset.id;
            let name = btn.dataset.name;

            document.getElementById("resetText").innerHTML =
                "Are you sure you want to reset the password for <strong>" + name + "</strong>?";

            document.getElementById("resetForm").action =
                "/authentication/users/reset-password/" + id;
        });
    </script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        .pagination {
            font-size: 14px !important;
        }

        .pagination .page-link {
            padding: 4px 10px !important;
            font-size: 14px !important;
            border-radius: 4px !important;
            color: #4a4a4a !important;
            border: 1px solid #d0d5dd !important;
            background: #f8f9fa !important;
        }

        .pagination .page-item.active .page-link {
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
            color: #fff !important;
            font-weight: 600 !important;
        }

        .pagination .page-link:hover {
            background: #e2e6ea !important;
            border-color: #c4c9cf !important;
        }

        .pagination .page-item.disabled .page-link {
            opacity: .5 !important;
        }

        .pagination .page-item {
            margin: 0 2px !important;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/users/index.blade.php ENDPATH**/ ?>