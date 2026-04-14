<?php $__env->startSection('title', 'Roles Management'); ?>

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
                            <h3 class="mb-2">Roles Management</h3>
                            <p class="text-muted">
                                Manage system roles and control access levels across all modules.
                            </p>
                        </div>

                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#roleModal"
                                onclick="openCreateRole()">
                                <i class="fas fa-plus me-1"></i> Add Role
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Role List</h6>
                </div>

                
                <div class="p-3">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <input class="form-control form-control-sm search" placeholder="Search role...">
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div id="roleTable" data-list='{"valueNames":["role_name"],"page":10,"pagination":true}'>

                        <div class="table-responsive scrollbar">
                            <table class="table table-hover table-striped fs-10 mb-0">
                                <thead class="bg-200 text-900">
                                    <tr>
                                        <th class="sort" data-sort="role_name">Role Name</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>

                                <tbody class="list">
                                    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr class="align-middle">

                                            <td class="role_name fw-semibold"><?php echo e($role->name); ?></td>

                                            <td class="text-end">
                                                <div class="dropdown font-sans-serif position-static">
                                                    <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                        type="button" data-bs-toggle="dropdown" data-boundary="window"
                                                        aria-haspopup="true" aria-expanded="false">
                                                        <span class="fas fa-ellipsis-h fs-10"></span>
                                                    </button>

                                                    <div class="dropdown-menu dropdown-menu-end border py-0 shadow-sm">
                                                        <div class="py-2">

                                                            
                                                            <button class="dropdown-item"
                                                                onclick="openEditRole(<?php echo e($role->id); ?>, '<?php echo e($role->name); ?>')">
                                                                <i class="fas fa-edit me-2"></i> Edit
                                                            </button>

                                                            
                                                            <form action="<?php echo e(route('roles.destroy', $role->id)); ?>"
                                                                method="POST" class="d-inline">
                                                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                                                <button class="dropdown-item text-danger">
                                                                    <i class="fas fa-trash me-2"></i> Delete
                                                                </button>
                                                            </form>

                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>

                            </table>
                        </div>

                        
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

    
    <div class="modal fade" id="roleModal" tabindex="-1">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">

                <form id="roleForm" method="POST" action="<?php echo e(route('roles.store')); ?>">
                    <?php echo csrf_field(); ?>

                    <div class="modal-header bg-light">
                        <h5 class="modal-title" id="modalTitle">Add Role</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <input type="hidden" id="roleId" name="role_id">

                        <div class="mb-3">
                            <label class="form-label">Role Name</label>
                            <input type="text" id="roleName" name="name" class="form-control" required>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-primary btn-sm" type="submit">Save</button>
                    </div>

                </form>

            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const listjs = new List("roleTable", {
                valueNames: ["role_name"],
                page: 10,
                pagination: true
            });

            document.querySelector(".search").addEventListener("keyup", e => {
                listjs.search(e.target.value);
            });
        });

        function openCreateRole() {
            document.getElementById("modalTitle").innerText = "Add Role";
            document.getElementById("roleForm").action = "<?php echo e(route('roles.store')); ?>";
            document.getElementById("roleName").value = "";

            let method = document.querySelector('#roleForm input[name="_method"]');
            if (method) method.remove();
        }

        function openEditRole(id, name) {
            document.getElementById("modalTitle").innerText = "Edit Role";
            document.getElementById("roleForm").action = "/roles/" + id;

            if (!document.querySelector('#roleForm input[name="_method"]')) {
                let m = document.createElement("input");
                m.type = "hidden";
                m.name = "_method";
                m.value = "PUT";
                document.getElementById("roleForm").appendChild(m);
            }

            document.getElementById("roleName").value = name;

            new bootstrap.Modal(document.getElementById('roleModal')).show();
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/roles/index.blade.php ENDPATH**/ ?>