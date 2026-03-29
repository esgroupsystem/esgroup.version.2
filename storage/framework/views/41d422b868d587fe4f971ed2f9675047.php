<div class="card border-0 shadow-sm overflow-hidden">
    <div class="card-header bg-body-tertiary border-bottom border-200 py-3">
        <div class="d-flex flex-column flex-xl-row gap-3 justify-content-between align-items-xl-center">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="fas fa-table text-primary"></span>
                    <h5 class="mb-0">Attendance Records</h5>
                </div>
                <p class="text-muted mb-0 fs-10">
                    Review each employee's daily attendance result. Payroll will use these summary records for
                    computation.
                </p>
            </div>

            <div class="text-xl-end">
                <div class="fw-semibold text-dark"><?php echo e($summaries->total()); ?> record(s)</div>
                <small class="text-muted"><?php echo e($cutoffLabel); ?></small>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive scrollbar">
            <table class="table table-hover table-sm align-middle mb-0 fs-10">
                <thead class="bg-200 text-900">
                    <tr>
                        <th class="text-nowrap ps-3">Date</th>
                        <th style="min-width: 240px;">Employee</th>
                        <th class="text-nowrap">Shift</th>
                        <th class="text-nowrap">Schedule</th>
                        <th class="text-nowrap">Time In</th>
                        <th class="text-nowrap">Time Out</th>
                        <th class="text-center text-nowrap">Late</th>
                        <th class="text-center text-nowrap">UT</th>
                        <th class="text-center text-nowrap">Worked</th>
                        <th class="text-nowrap">Status</th>
                        <th class="text-nowrap">Adjustment</th>
                        <th class="text-nowrap">Holiday</th>
                        <th class="text-nowrap">Payable</th>
                        
                    </tr>
                </thead>

                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $summaries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $statusClass = match ($row->attendance_status) {
                                'present', 'adjusted_present' => 'success',
                                'late', 'undertime', 'late_undertime' => 'warning',
                                'absent', 'incomplete_log' => 'danger',
                                'holiday', 'holiday_worked' => 'info',
                                'rest_day', 'rest_day_worked' => 'secondary',
                                'leave' => 'primary',
                                default => 'light',
                            };

                            $statusLabel = strtoupper(str_replace('_', ' ', $row->attendance_status ?? 'N/A'));

                            $scheduleStatusLabel = $row->schedule_status
                                ? strtoupper(str_replace('_', ' ', $row->schedule_status))
                                : 'NO STATUS';

                            $workedHours = ((int) $row->worked_minutes) / 60;
                        ?>

                        <tr>
                            <td class="text-nowrap ps-3">
                                <div class="fw-semibold text-dark">
                                    <?php echo e(optional($row->work_date)->format('M d, Y')); ?>

                                </div>
                                <div class="text-muted fs-11">
                                    <?php echo e(optional($row->work_date)->format('l')); ?>

                                </div>
                            </td>

                            <td>
                                <div class="d-flex flex-column">
                                    <div class="fw-semibold text-dark"><?php echo e($row->employee_name); ?></div>
                                    <div class="text-muted fs-11">
                                        <strong>Emp No:</strong> <?php echo e($row->employee_no ?: '—'); ?>

                                    </div>
                                    <div class="text-muted fs-11">
                                        <strong>Biometric ID:</strong> <?php echo e($row->biometric_employee_id ?: '—'); ?>

                                    </div>
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <div class="fw-semibold text-dark"><?php echo e($row->shift_name ?: '—'); ?></div>
                                <div class="text-muted fs-11"><?php echo e($scheduleStatusLabel); ?></div>
                            </td>

                            <td class="text-nowrap">
                                <?php if($row->scheduled_time_in || $row->scheduled_time_out): ?>
                                    <div class="fw-semibold text-dark">
                                        <?php echo e($row->scheduled_time_in ? \Carbon\Carbon::parse($row->scheduled_time_in)->format('h:i A') : '—'); ?>

                                        <span class="text-muted mx-1">to</span>
                                        <?php echo e($row->scheduled_time_out ? \Carbon\Carbon::parse($row->scheduled_time_out)->format('h:i A') : '—'); ?>

                                    </div>
                                    <div class="text-muted fs-11">
                                        Grace Period: <?php echo e((int) $row->grace_minutes); ?> min
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-nowrap">
                                <?php if($row->actual_time_in): ?>
                                    <div class="fw-semibold text-success">
                                        <?php echo e($row->actual_time_in->format('h:i A')); ?>

                                    </div>
                                    <div class="text-muted fs-11">
                                        <?php echo e($row->actual_time_in->format('M d, Y')); ?>

                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-nowrap">
                                <?php if($row->actual_time_out): ?>
                                    <div class="fw-semibold text-primary">
                                        <?php echo e($row->actual_time_out->format('h:i A')); ?>

                                    </div>
                                    <div class="text-muted fs-11">
                                        <?php echo e($row->actual_time_out->format('M d, Y')); ?>

                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <?php if((int) $row->late_minutes > 0): ?>
                                    <span class="badge badge-phoenix badge-phoenix-warning px-2 py-1">
                                        <?php echo e((int) $row->late_minutes); ?> min
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">0 min</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <?php if((int) $row->undertime_minutes > 0): ?>
                                    <span class="badge badge-phoenix badge-phoenix-warning px-2 py-1">
                                        <?php echo e((int) $row->undertime_minutes); ?> min
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">0 min</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-center">
                                <div class="fw-semibold text-dark"><?php echo e((int) $row->worked_minutes); ?> min</div>
                                <div class="text-muted fs-11">
                                    <?php echo e(number_format($workedHours, 2)); ?> hr
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <span class="badge badge-phoenix badge-phoenix-<?php echo e($statusClass); ?> px-3 py-2">
                                    <?php echo e($statusLabel); ?>

                                </span>
                            </td>

                            <td>
                                <?php if($row->has_adjustment): ?>
                                    <div>
                                        <span class="badge badge-phoenix badge-phoenix-primary px-2 py-1">
                                            ADJUSTED
                                        </span>
                                    </div>
                                    <div class="text-muted fs-11 mt-1">
                                        <?php echo e($row->adjustment_type ? strtoupper(str_replace('_', ' ', $row->adjustment_type)) : 'Manual Adjustment'); ?>

                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if($row->is_holiday): ?>
                                    <div>
                                        <span class="badge badge-phoenix badge-phoenix-info px-2 py-1">
                                            <?php echo e($row->holiday_type ?: 'HOLIDAY'); ?>

                                        </span>
                                    </div>
                                    <div class="text-muted fs-11 mt-1">
                                        <?php echo e($row->holiday_name ?: 'Holiday Applied'); ?>

                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>

                            <td class="text-nowrap">
                                <div class="fw-semibold text-dark">
                                    <?php echo e(number_format((float) $row->payable_days, 2)); ?> day
                                </div>
                                <div class="text-muted fs-11">
                                    <?php echo e(number_format((float) $row->payable_hours, 2)); ?> hr
                                </div>
                            </td>

                            
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="14" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center justify-content-center">
                                    <div class="avatar avatar-4xl mb-3">
                                        <div class="avatar-name rounded-circle bg-soft-secondary text-secondary">
                                            <span class="fas fa-folder-open fs-2"></span>
                                        </div>
                                    </div>
                                    <h5 class="mb-1">No attendance summary records found</h5>
                                    <p class="text-muted mb-0">
                                        Try changing the cutoff filter, rebuilding the summary, or checking if records
                                        exist for the selected period.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if($summaries->hasPages()): ?>
        <div class="card-footer bg-body-tertiary border-top border-200 py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <small class="text-muted">
                    Showing <?php echo e($summaries->firstItem()); ?> to <?php echo e($summaries->lastItem()); ?>

                    of <?php echo e($summaries->total()); ?> records
                </small>

                <div>
                    <?php echo e($summaries->links('pagination::bootstrap-5')); ?>

                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/payroll/attendance_summary/table.blade.php ENDPATH**/ ?>