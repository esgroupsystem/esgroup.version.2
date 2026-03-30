
<?php $__env->startSection('title', 'Edit Payroll Attendance Adjustment'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid" data-layout="container">
        <div class="content">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Edit Payroll Attendance Adjustment</h5>
                </div>

                <div class="card-body">
                    <form method="POST"
                        action="<?php echo e(route('payroll-attendance-adjustments.update', $payrollAttendanceAdjustment)); ?>">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <?php echo $__env->make('payroll.attendance_adjustments._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Update Adjustment</button>
                            <a href="<?php echo e(route('payroll-attendance-adjustments.index')); ?>" class="btn btn-light">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/payroll/attendance_adjustments/edit.blade.php ENDPATH**/ ?>