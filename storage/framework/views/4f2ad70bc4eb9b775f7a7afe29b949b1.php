
<?php $__env->startSection('title', 'Payroll Attendance Adjustments'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid" data-layout="container">
        <div class="content">

            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Payroll Attendance Adjustments</h5>
                        <small class="text-muted">Manual encoding of paper-approved attendance adjustments</small>
                    </div>

                    <a href="<?php echo e(route('payroll-attendance-adjustments.create')); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> New Adjustment
                    </a>
                </div>

                <div class="card-body">
                    <form method="GET" action="<?php echo e(route('payroll-attendance-adjustments.index')); ?>" class="row g-2 mb-3">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control"
                                placeholder="Search employee name / employee ID..." value="<?php echo e(request('search')); ?>">
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-secondary">Search</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Employee</th>
                                    <th>Type</th>
                                    <th>Adjusted Time</th>
                                    <th>Day Type</th>
                                    <th>Options</th>
                                    <th>Encoded By</th>
                                    <th width="160">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $adjustments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e(optional($item->work_date)->format('M d, Y')); ?></td>
                                        <td>
                                            <strong><?php echo e($item->employee->full_name ?? ($item->employee->name ?? 'N/A')); ?></strong><br>
                                            <small class="text-muted">
                                                <?php echo e($item->employee->employee_id_permanent ?? ''); ?>

                                            </small>
                                        </td>
                                        <td><?php echo e(ucwords(str_replace('_', ' ', $item->adjustment_type))); ?></td>
                                        <td>
                                            <?php echo e($item->adjusted_time_in ?? '--:--'); ?> -
                                            <?php echo e($item->adjusted_time_out ?? '--:--'); ?>

                                        </td>
                                        <td><?php echo e($item->adjusted_day_type ?? '—'); ?></td>
                                        <td>
                                            <?php if($item->is_paid): ?>
                                                <span class="badge bg-success">Paid</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Unpaid</span>
                                            <?php endif; ?>

                                            <?php if($item->ignore_late): ?>
                                                <span class="badge bg-info">Ignore Late</span>
                                            <?php endif; ?>

                                            <?php if($item->ignore_undertime): ?>
                                                <span class="badge bg-warning text-dark">Ignore UT</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo e($item->encoder->name ?? 'N/A'); ?><br>
                                            <small class="text-muted">
                                                <?php echo e(optional($item->encoded_at)->format('M d, Y h:i A')); ?>

                                            </small>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="<?php echo e(route('payroll-attendance-adjustments.edit', $item)); ?>"
                                                    class="btn btn-warning btn-sm">
                                                    Edit
                                                </a>

                                                <form method="POST"
                                                    action="<?php echo e(route('payroll-attendance-adjustments.destroy', $item)); ?>"
                                                    onsubmit="return confirm('Delete this adjustment?')">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button class="btn btn-danger btn-sm">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            No payroll attendance adjustments found.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php echo e($adjustments->links('pagination.custom')); ?>

                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const picker = document.getElementById('employee_picker');
            const biometricIdInput = document.getElementById('biometric_employee_id');
            const employeeNoInput = document.getElementById('employee_no');
            const employeeNameInput = document.getElementById('employee_name');

            function syncSelectedEmployee() {
                const selected = picker.options[picker.selectedIndex];

                if (!selected || !selected.value) {
                    biometricIdInput.value = '';
                    employeeNoInput.value = '';
                    employeeNameInput.value = '';
                    return;
                }

                biometricIdInput.value = selected.dataset.biometricId || '';
                employeeNoInput.value = selected.dataset.employeeNo || '';
                employeeNameInput.value = selected.dataset.employeeName || '';
            }

            picker.addEventListener('change', syncSelectedEmployee);

            syncSelectedEmployee();
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/payroll/attendance_adjustments/index.blade.php ENDPATH**/ ?>