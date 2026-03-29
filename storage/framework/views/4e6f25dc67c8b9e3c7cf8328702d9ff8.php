
<?php $__env->startSection('title', 'Employee Salary Master'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid" data-layout="container">
        <div class="content">

            <?php if(session('success')): ?>
                <div class="alert alert-success border-0 shadow-sm"><?php echo e(session('success')); ?></div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div
                    class="card-header bg-body-tertiary d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                    <div>
                        <h5 class="mb-0">Employee Salary Master</h5>
                        <small class="text-muted">Based on biometrics employee records</small>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?php echo e(route('payroll-employee-salaries.sync')); ?>" class="btn btn-warning btn-sm">
                            <span class="fas fa-sync-alt me-1"></span> Sync from Biometrics
                        </a>
                        <a href="<?php echo e(route('payroll-employee-salaries.create')); ?>" class="btn btn-primary btn-sm">
                            <span class="fas fa-plus me-1"></span> Add Salary
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form method="GET" action="<?php echo e(route('payroll-employee-salaries.index')); ?>" class="row g-2 mb-3">
                        <div class="col-md-5">
                            <input type="text" name="search" class="form-control"
                                placeholder="Search employee name / employee no / biometric id / crosschex id"
                                value="<?php echo e($search); ?>">
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary">
                                <span class="fas fa-search me-1"></span> Search
                            </button>
                        </div>
                        <?php if($search): ?>
                            <div class="col-auto">
                                <a href="<?php echo e(route('payroll-employee-salaries.index')); ?>" class="btn btn-light">
                                    Clear
                                </a>
                            </div>
                        <?php endif; ?>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 220px;">Employee</th>
                                    <th style="min-width: 110px;">Employee No</th>
                                    <th style="min-width: 100px;">Rate Type</th>
                                    <th class="text-end" style="min-width: 120px;">Basic Salary</th>
                                    <th class="text-end" style="min-width: 110px;">Allowance</th>
                                    <th class="text-end" style="min-width: 110px;">OT / Hr</th>
                                    <th class="text-end" style="min-width: 120px;">Late / Min</th>
                                    <th class="text-end" style="min-width: 140px;">Undertime / Min</th>
                                    <th class="text-end" style="min-width: 130px;">Absent / Day</th>
                                    <th class="text-end" style="min-width: 110px;">SSS Loan</th>
                                    <th class="text-end" style="min-width: 120px;">Pagibig Loan</th>
                                    <th class="text-end" style="min-width: 90px;">Vale</th>
                                    <th class="text-end" style="min-width: 120px;">Other Loans</th>
                                    <th style="min-width: 90px;">Status</th>
                                    <th width="150">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $salaries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $salary): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark"><?php echo e($salary->employee_name); ?></div>
                                        </td>
                                        <td><?php echo e($salary->employee_no ?: '—'); ?></td>
                                        <td>
                                            <span
                                                class="badge <?php echo e($salary->rate_type === 'monthly' ? 'bg-primary' : 'bg-info'); ?>">
                                                <?php echo e(ucfirst($salary->rate_type)); ?>

                                            </span>
                                        </td>
                                        <td class="text-end"><?php echo e(number_format((float) $salary->basic_salary, 2)); ?></td>
                                        <td class="text-end"><?php echo e(number_format((float) $salary->allowance, 2)); ?></td>
                                        <td class="text-end"><?php echo e(number_format((float) $salary->ot_rate_per_hour, 2)); ?></td>
                                        <td class="text-end">
                                            <?php echo e(number_format((float) $salary->late_deduction_per_minute, 4)); ?></td>
                                        <td class="text-end">
                                            <?php echo e(number_format((float) $salary->undertime_deduction_per_minute, 4)); ?></td>
                                        <td class="text-end">
                                            <?php echo e(number_format((float) $salary->absent_deduction_per_day, 2)); ?></td>
                                        <td class="text-end"><?php echo e(number_format((float) ($salary->sss_loan ?? 0), 2)); ?></td>
                                        <td class="text-end"><?php echo e(number_format((float) ($salary->pagibig_loan ?? 0), 2)); ?>

                                        </td>
                                        <td class="text-end"><?php echo e(number_format((float) ($salary->vale ?? 0), 2)); ?></td>
                                        <td class="text-end"><?php echo e(number_format((float) ($salary->other_loans ?? 0), 2)); ?>

                                        </td>
                                        <td>
                                            <?php if($salary->is_active): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="<?php echo e(route('payroll-employee-salaries.edit', $salary)); ?>"
                                                    class="btn btn-sm btn-primary">
                                                    Edit
                                                </a>

                                                <form action="<?php echo e(route('payroll-employee-salaries.destroy', $salary)); ?>"
                                                    method="POST" onsubmit="return confirm('Delete this salary record?')">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="15" class="text-center text-muted py-4">No salary records found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <?php echo e($salaries->links('pagination.custom')); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/payroll/employee_salaries/index.blade.php ENDPATH**/ ?>