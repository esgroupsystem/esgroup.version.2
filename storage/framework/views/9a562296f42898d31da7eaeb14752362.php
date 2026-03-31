<?php $__env->startSection('title', 'Driver Leaves'); ?>

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
                            <h3 class="mb-2">Driver Leaves</h3>
                            <p class="text-muted">
                                Manage leaves for drivers — HR will approve automatically. Use actions to record notices or
                                terminate.
                            </p>
                        </div>

                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <a href="<?php echo e(route('driver-leave.driver.create')); ?>" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Add Leave
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Active</h6>
                                <div class="fs-4 fw-bold"><?php echo e($counts['active'] ?? 0); ?></div>
                            </div>
                            <div class="text-muted">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">1st Notice</h6>
                                <div class="fs-4 fw-bold"><?php echo e($counts['first'] ?? 0); ?></div>
                            </div>
                            <div class="text-muted">
                                <i class="fas fa-paper-plane fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">2nd Notice</h6>
                                <div class="fs-4 fw-bold"><?php echo e($counts['second'] ?? 0); ?></div>
                            </div>
                            <div class="text-muted">
                                <i class="fas fa-envelope fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Termination</h6>
                                <div class="fs-4 fw-bold"><?php echo e($counts['termination'] ?? 0); ?></div>
                            </div>
                            <div class="text-muted">
                                <i class="fas fa-user-times fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Driver Leave Records</h6>
                </div>

                
                <div class="p-3">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <input id="liveSearch" class="form-control form-control-sm" placeholder="Search driver leave..."
                                value="<?php echo e(request('search')); ?>">
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div id="driverLeaveTable">
                        <?php echo $__env->make('hr_department.leaves.driver.table', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

    
    <div class="modal fade" id="leaveActionModal" tabindex="-1" aria-labelledby="leaveActionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="leaveActionForm" method="POST" action="">
                <?php echo csrf_field(); ?>
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="leaveActionModalLabel">Confirm Action</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="action_type" id="modal_action_type" value="">

                        <div class="mb-2">
                            <strong id="modal_employee_name"></strong>
                            <div class="small text-muted" id="modal_leave_type"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Note (optional)</label>
                            <textarea name="note" id="modal_note" class="form-control" rows="4"
                                placeholder="Add note, reason, or letter reference..."></textarea>
                        </div>

                        <div class="alert alert-warning small">
                            This will record the selected action and update the leave record. HR will manually send
                            messages/letters.
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="modal_submit_btn">Confirm</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            function initTooltips() {
                document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                    const inst = bootstrap.Tooltip.getInstance(el);
                    if (inst) inst.dispose();
                    bootstrap.Tooltip.getOrCreateInstance(el, {
                        container: 'body',
                        trigger: 'hover'
                    });
                });
            }

            function bindActionModal() {
                const modalEl = document.getElementById('leaveActionModal');
                const modal = new bootstrap.Modal(modalEl);
                const modalForm = document.getElementById('leaveActionForm');

                document.querySelectorAll('.action-open-modal').forEach(a => {
                    a.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (this.classList.contains('disabled')) return;

                        const id = this.dataset.id;
                        const action = this.dataset.action;
                        const employee = this.dataset.employee || '';
                        const type = this.dataset.type || '';

                        modalForm.action = "<?php echo e(route('driver-leave.driver.action', ':id')); ?>"
                            .replace(':id', id);

                        document.getElementById('modal_action_type').value = action;
                        document.getElementById('modal_employee_name').innerText = employee;
                        document.getElementById('modal_leave_type').innerText = type;
                        document.getElementById('modal_note').value = '';

                        let title = 'Confirm Action';
                        let btnText = 'Confirm';

                        if (action === 'first') {
                            title = 'Mark 1st Notice Sent';
                            btnText = 'Mark 1st Notice';
                        }
                        if (action === 'second') {
                            title = 'Mark 2nd Notice Sent';
                            btnText = 'Mark 2nd Notice';
                        }
                        if (action === 'terminate') {
                            title = 'Mark Final Notice Sent (Termination)';
                            btnText = 'Mark Final Notice';
                        }
                        if (action === 'cancel') {
                            title = 'Cancel Leave';
                            btnText = 'Cancel Leave';
                        }
                        if (action === 'ready') {
                            title = 'Mark As Ready for Duty';
                            btnText = 'Set As Ready';
                        }

                        document.getElementById('leaveActionModalLabel').innerText = title;
                        document.getElementById('modal_submit_btn').innerText = btnText;

                        modal.show();
                    });
                });
            }

            function refreshBindings() {
                document.querySelectorAll(".table-responsive").forEach(el => el.style.overflow = "visible");
                initTooltips();
                bindActionModal();
            }

            refreshBindings();

            // ✅ AJAX Live Search
            let timer = null;
            const input = document.getElementById("liveSearch");

            if (input) {
                input.addEventListener("keyup", function() {
                    const search = this.value;
                    clearTimeout(timer);

                    timer = setTimeout(() => {
                        fetch(`?search=${encodeURIComponent(search)}`, {
                                headers: {
                                    "X-Requested-With": "XMLHttpRequest"
                                }
                            })
                            .then(res => res.text())
                            .then(html => {
                                document.getElementById("driverLeaveTable").innerHTML = html;
                                refreshBindings();
                            });
                    }, 300);
                });
            }

            // ✅ AJAX pagination clicks
            document.addEventListener('click', function(e) {
                const link = e.target.closest('#driverLeaveTable .pagination a');
                if (!link) return;

                e.preventDefault();
                fetch(link.href, {
                        headers: {
                            "X-Requested-With": "XMLHttpRequest"
                        }
                    })
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById("driverLeaveTable").innerHTML = html;
                        refreshBindings();
                    });
            });

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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/hr_department/leaves/driver/index.blade.php ENDPATH**/ ?>