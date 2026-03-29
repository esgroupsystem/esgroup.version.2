
<?php $__env->startSection('title', 'Attendance Summary'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid" data-layout="container">
        <script>
            var isFluid = JSON.parse(localStorage.getItem('isFluid'));
            if (isFluid) {
                var container = document.querySelector('[data-layout]');
                container.classList.remove('container');
                container.classList.add('container-fluid');
            }
        </script>

        <?php
            $presentCount = $stats['present'] ?? 0;
            $lateCount = $stats['late'] ?? 0;
            $undertimeCount = $stats['undertime'] ?? 0;
            $absentCount = $stats['absent'] ?? 0;
            $incompleteCount = $stats['incomplete'] ?? 0;
            $holidayCount = $stats['holiday'] ?? 0;
            $restDayCount = $stats['rest_day'] ?? 0;
            $leaveCount = $stats['leave'] ?? 0;
            $adjustmentCount = $stats['adjustment'] ?? 0;

            $totalLateMinutes = $stats['total_late_minutes'] ?? 0;
            $totalUndertimeMinutes = $stats['total_undertime_minutes'] ?? 0;
            $totalWorkedMinutes = $stats['total_worked_minutes'] ?? 0;
            $totalOvertimeMinutes = $stats['total_overtime_minutes'] ?? 0;
            $totalPayableDays = $stats['total_payable_days'] ?? 0;
            $totalPayableHours = $stats['total_payable_hours'] ?? 0;
        ?>

        <div class="content attendance-summary-page">
            <?php if(session('success')): ?>
                <div class="alert alert-success border-200 bg-soft-success d-flex align-items-center gap-2">
                    <span class="fas fa-check-circle"></span>
                    <span><?php echo e(session('success')); ?></span>
                </div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger border-200 bg-soft-danger d-flex align-items-center gap-2">
                    <span class="fas fa-exclamation-circle"></span>
                    <span><?php echo e($errors->first()); ?></span>
                </div>
            <?php endif; ?>

            
            <div class="card border-0 shadow-sm mb-3 overflow-hidden">
                <div class="card-body">
                    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="fas fa-clipboard-list text-primary"></span>
                                <h4 class="mb-0">Attendance Summary</h4>
                            </div>

                            <p class="text-muted mb-3">
                                This page combines plotted schedule, biometrics logs, attendance adjustments, and
                                holidays into one daily attendance result that payroll will use for computation.
                            </p>

                            <div class="d-flex flex-wrap gap-2">
                                <span
                                    class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
                                    <span class="fas fa-calendar-alt me-1"></span>
                                    <?php echo e($cutoffLabel); ?>

                                </span>

                                <span class="badge rounded-pill bg-light text-dark border px-3 py-2">
                                    <span class="fas fa-database me-1"></span>
                                    <?php echo e(number_format($stats['total'] ?? 0)); ?> total record(s)
                                </span>

                                <span class="badge rounded-pill bg-light text-dark border px-3 py-2">
                                    <span class="fas fa-file-invoice-dollar me-1"></span>
                                    <?php echo e(number_format((float) $totalPayableDays, 2)); ?> payable day(s)
                                </span>
                            </div>
                        </div>

                        <div class="text-xl-end">
                            <form method="POST" action="<?php echo e(route('attendance-summary.rebuild')); ?>">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="cutoff_month" value="<?php echo e($cutoffMonth); ?>">
                                <input type="hidden" name="cutoff_year" value="<?php echo e($cutoffYear); ?>">
                                <input type="hidden" name="cutoff_type" value="<?php echo e($cutoffType); ?>">
                                <input type="hidden" name="search" value="<?php echo e($search); ?>">
                                <input type="hidden" name="status" value="<?php echo e($status); ?>">
                                <input type="hidden" name="day_type" value="<?php echo e($dayType); ?>">

                                <button type="submit" class="btn btn-success">
                                    <span class="fas fa-sync-alt me-1"></span>
                                    Rebuild Current Cutoff Summary
                                </button>
                            </form>
                            <small class="text-muted d-block mt-2">
                                Rebuild this cutoff after changing plotting, logs, adjustments, or holidays.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-body-tertiary border-bottom border-200">
                    <div>
                        <h6 class="mb-0">Filter Attendance Summary</h6>
                        <small class="text-muted">Choose cutoff, filter type, and search employee attendance
                            records.</small>
                    </div>
                </div>

                <div class="card-body">
                    <form method="GET" action="<?php echo e(route('attendance-summary.index')); ?>">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3 col-lg-2">
                                <label class="form-label fw-semibold">Month</label>
                                <select name="cutoff_month" class="form-select">
                                    <?php for($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?php echo e($m); ?>" <?php echo e($cutoffMonth == $m ? 'selected' : ''); ?>>
                                            <?php echo e(\Carbon\Carbon::create()->month($m)->format('F')); ?>

                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="col-md-3 col-lg-2">
                                <label class="form-label fw-semibold">Year</label>
                                <select name="cutoff_year" class="form-select">
                                    <?php for($y = now('Asia/Manila')->year + 1; $y >= now('Asia/Manila')->year - 3; $y--): ?>
                                        <option value="<?php echo e($y); ?>" <?php echo e($cutoffYear == $y ? 'selected' : ''); ?>>
                                            <?php echo e($y); ?>

                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="col-md-3 col-lg-2">
                                <label class="form-label fw-semibold">Cutoff</label>
                                <select name="cutoff_type" class="form-select">
                                    <option value="first" <?php echo e($cutoffType === 'first' ? 'selected' : ''); ?>>
                                        1st Cutoff (11-25)
                                    </option>
                                    <option value="second" <?php echo e($cutoffType === 'second' ? 'selected' : ''); ?>>
                                        2nd Cutoff (26-10)
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-3 col-lg-2">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="status" class="form-select">
                                    <?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value); ?>"
                                            <?php echo e((string) $status === (string) $value ? 'selected' : ''); ?>>
                                            <?php echo e($label); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div class="col-md-3 col-lg-2">
                                <label class="form-label fw-semibold">Day Type</label>
                                <select name="day_type" class="form-select">
                                    <?php $__currentLoopData = $dayTypeOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value); ?>"
                                            <?php echo e((string) $dayType === (string) $value ? 'selected' : ''); ?>>
                                            <?php echo e($label); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div class="col-md-9 col-lg-2">
                                <label class="form-label fw-semibold">Search</label>
                                <input type="text" class="form-control" name="search" value="<?php echo e($search); ?>"
                                    placeholder="Name, emp no, bio id">
                            </div>

                            <div class="col-md-6 col-lg-2 d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <span class="fas fa-search me-1"></span>
                                    Apply Filter
                                </button>
                            </div>

                            <div class="col-md-6 col-lg-2 d-grid">
                                <a href="<?php echo e(route('attendance-summary.index')); ?>" class="btn btn-outline-secondary">
                                    <span class="fas fa-undo me-1"></span>
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            
            <div class="row g-3 mb-3">
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-success fs-9 fw-bold text-uppercase">Present</span>
                                <span class="fas fa-user-check text-success"></span>
                            </div>
                            <h3 class="mb-1"><?php echo e(number_format($presentCount)); ?></h3>
                            <p class="text-muted mb-0 fs-10">Present and adjusted present records</p>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-warning fs-9 fw-bold text-uppercase">Late / Undertime</span>
                                <span class="fas fa-clock text-warning"></span>
                            </div>
                            <h3 class="mb-1"><?php echo e(number_format($lateCount + $undertimeCount)); ?></h3>
                            <p class="text-muted mb-0 fs-10">Attendance with time deduction indicators</p>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-danger fs-9 fw-bold text-uppercase">Absent / Incomplete</span>
                                <span class="fas fa-exclamation-triangle text-danger"></span>
                            </div>
                            <h3 class="mb-1"><?php echo e(number_format($absentCount + $incompleteCount)); ?></h3>
                            <p class="text-muted mb-0 fs-10">Needs checking or adjustment</p>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-info fs-9 fw-bold text-uppercase">Holiday / Rest Day</span>
                                <span class="fas fa-calendar-day text-info"></span>
                            </div>
                            <h3 class="mb-1"><?php echo e(number_format($holidayCount + $restDayCount)); ?></h3>
                            <p class="text-muted mb-0 fs-10">Special day related attendance records</p>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="row g-3 mb-3">
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-primary fs-9 fw-bold text-uppercase mb-2">With Adjustment</div>
                            <h4 class="mb-1"><?php echo e(number_format($adjustmentCount)); ?></h4>
                            <small class="text-muted">Records affected by attendance adjustment</small>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-info fs-9 fw-bold text-uppercase mb-2">Holiday Count</div>
                            <h4 class="mb-1"><?php echo e(number_format($holidayCount)); ?></h4>
                            <small class="text-muted">Holiday and holiday worked records</small>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-secondary fs-9 fw-bold text-uppercase mb-2">Rest Day Count</div>
                            <h4 class="mb-1"><?php echo e(number_format($restDayCount)); ?></h4>
                            <small class="text-muted">Rest day and rest day worked records</small>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-primary fs-9 fw-bold text-uppercase mb-2">Leave Count</div>
                            <h4 class="mb-1"><?php echo e(number_format($leaveCount)); ?></h4>
                            <small class="text-muted">Employees marked as leave</small>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="row g-3 mb-3">
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-warning fs-9 fw-bold text-uppercase mb-2">Total Late Minutes</div>
                            <h4 class="mb-1"><?php echo e(number_format((float) $totalLateMinutes, 0)); ?> min</h4>
                            <small class="text-muted"><?php echo e(number_format($totalLateMinutes / 60, 2)); ?> hr equivalent</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-warning fs-9 fw-bold text-uppercase mb-2">Total Undertime Minutes</div>
                            <h4 class="mb-1"><?php echo e(number_format((float) $totalUndertimeMinutes, 0)); ?> min</h4>
                            <small class="text-muted"><?php echo e(number_format($totalUndertimeMinutes / 60, 2)); ?> hr
                                equivalent</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-success fs-9 fw-bold text-uppercase mb-2">Total Worked Hours</div>
                            <h4 class="mb-1"><?php echo e(number_format($totalWorkedMinutes / 60, 2)); ?> hr</h4>
                            <small class="text-muted"><?php echo e(number_format((float) $totalWorkedMinutes, 0)); ?> total
                                minutes</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-primary fs-9 fw-bold text-uppercase mb-2">Payable Summary</div>
                            <h4 class="mb-1"><?php echo e(number_format((float) $totalPayableDays, 2)); ?> day(s)</h4>
                            <small class="text-muted"><?php echo e(number_format((float) $totalPayableHours, 2)); ?> payable
                                hour(s)</small>
                        </div>
                    </div>
                </div>
            </div>

            <?php echo $__env->make('payroll.attendance_summary.table', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/payroll/attendance_summary/index.blade.php ENDPATH**/ ?>