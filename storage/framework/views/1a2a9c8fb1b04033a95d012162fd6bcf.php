<form method="POST" action="<?php echo e(route('payroll-plotting.save-monthly')); ?>">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="cutoff_month" value="<?php echo e($cutoffMonth); ?>">
    <input type="hidden" name="cutoff_year" value="<?php echo e($cutoffYear); ?>">
    <input type="hidden" name="cutoff_type" value="<?php echo e($cutoffType); ?>">
    <input type="hidden" name="search" value="<?php echo e(request('search')); ?>">
    <input type="hidden" name="page" value="<?php echo e($employees->currentPage()); ?>">

    <div class="card-body p-0">
        <div class="table-responsive scrollbar">
            <table class="table table-bordered table-sm mb-0 align-middle plotting-table">
                <thead class="bg-light text-center">
                    <tr>
                        <th class="sticky-col sticky-head bg-light employee-col">Employee</th>
                        <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th
                                class="day-col <?php echo e($day['is_sunday'] ? 'bg-danger-subtle' : ($day['is_saturday'] ? 'bg-warning-subtle' : '')); ?>">
                                <div class="fw-semibold"><?php echo e($day['month_short']); ?> <?php echo e($day['day']); ?></div>
                                <div class="fs-11 text-muted"><?php echo e($day['dow_short']); ?></div>
                            </th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>
                </thead>

                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rowIndex => $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $employeeIdentity = $employee->biometric_employee_id ?: $employee->employee_no;
                        ?>

                        <tr>
                            <td class="sticky-col bg-white employee-col">
                                <div class="fw-semibold text-dark"><?php echo e($employee->employee_name); ?></div>
                                <div class="fs-11 text-muted">
                                    <?php echo e($employee->employee_no ?: 'No Employee No.'); ?>

                                </div>
                                <div class="fs-11 text-muted">
                                    Bio ID: <?php echo e($employee->biometric_employee_id ?: '-'); ?>

                                </div>

                                <input type="hidden" name="schedule[<?php echo e($rowIndex); ?>][crosschex_id]"
                                    value="<?php echo e($employee->crosschex_id); ?>">
                                <input type="hidden" name="schedule[<?php echo e($rowIndex); ?>][biometric_employee_id]"
                                    value="<?php echo e($employee->biometric_employee_id); ?>">
                                <input type="hidden" name="schedule[<?php echo e($rowIndex); ?>][employee_no]"
                                    value="<?php echo e($employee->employee_no); ?>">
                                <input type="hidden" name="schedule[<?php echo e($rowIndex); ?>][employee_name]"
                                    value="<?php echo e($employee->employee_name); ?>">
                            </td>

                            <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dayIndex => $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $groupKey = $employeeIdentity . '_' . $day['date'];
                                    $existing = optional($schedules->get($groupKey))->first();

                                    $cellStatus = old(
                                        "schedule.$rowIndex.days.$dayIndex.status",
                                        $existing->status ?? '',
                                    );
                                    $cellShift = old(
                                        "schedule.$rowIndex.days.$dayIndex.shift_name",
                                        $existing->shift_name ?? '',
                                    );
                                    $cellTimeIn = old(
                                        "schedule.$rowIndex.days.$dayIndex.time_in",
                                        !empty($existing?->time_in)
                                            ? \Illuminate\Support\Carbon::parse($existing->time_in)->format('H:i')
                                            : '',
                                    );

                                    $cellTimeOut = old(
                                        "schedule.$rowIndex.days.$dayIndex.time_out",
                                        !empty($existing?->time_out)
                                            ? \Illuminate\Support\Carbon::parse($existing->time_out)->format('H:i')
                                            : '',
                                    );
                                    $cellGrace = old(
                                        "schedule.$rowIndex.days.$dayIndex.grace_minutes",
                                        $existing->grace_minutes ?? 15,
                                    );
                                    $cellRemarks = old(
                                        "schedule.$rowIndex.days.$dayIndex.remarks",
                                        $existing->remarks ?? '',
                                    );

                                    $cellClass = '';
                                    if ($cellStatus === 'scheduled') {
                                        $cellClass = 'plot-scheduled';
                                    }
                                    if ($cellStatus === 'rest_day') {
                                        $cellClass = 'plot-rest-day';
                                    }
                                    if ($cellStatus === 'leave') {
                                        $cellClass = 'plot-leave';
                                    }
                                    if ($cellStatus === 'holiday') {
                                        $cellClass = 'plot-holiday';
                                    }
                                ?>

                                <td class="plot-cell <?php echo e($cellClass); ?>">
                                    <input type="hidden"
                                        name="schedule[<?php echo e($rowIndex); ?>][days][<?php echo e($dayIndex); ?>][work_date]"
                                        value="<?php echo e($day['date']); ?>">

                                    <div class="plot-mini-card">
                                        <select
                                            name="schedule[<?php echo e($rowIndex); ?>][days][<?php echo e($dayIndex); ?>][status]"
                                            class="form-select form-select-sm mb-1 plot-status">
                                            <option value="">-</option>
                                            <option value="scheduled"
                                                <?php echo e($cellStatus === 'scheduled' ? 'selected' : ''); ?>>
                                                Scheduled
                                            </option>
                                            <option value="rest_day"
                                                <?php echo e($cellStatus === 'rest_day' ? 'selected' : ''); ?>>
                                                Rest Day
                                            </option>
                                            <option value="leave" <?php echo e($cellStatus === 'leave' ? 'selected' : ''); ?>>
                                                Leave
                                            </option>
                                            <option value="holiday" <?php echo e($cellStatus === 'holiday' ? 'selected' : ''); ?>>
                                                Holiday
                                            </option>
                                        </select>

                                        <input type="text"
                                            name="schedule[<?php echo e($rowIndex); ?>][days][<?php echo e($dayIndex); ?>][shift_name]"
                                            class="form-control form-control-sm mb-1" placeholder="Shift"
                                            value="<?php echo e($cellShift); ?>">

                                        <input type="time"
                                            name="schedule[<?php echo e($rowIndex); ?>][days][<?php echo e($dayIndex); ?>][time_in]"
                                            class="form-control form-control-sm mb-1" value="<?php echo e($cellTimeIn); ?>">

                                        <input type="time"
                                            name="schedule[<?php echo e($rowIndex); ?>][days][<?php echo e($dayIndex); ?>][time_out]"
                                            class="form-control form-control-sm mb-1" value="<?php echo e($cellTimeOut); ?>">

                                        <input type="number" min="0"
                                            name="schedule[<?php echo e($rowIndex); ?>][days][<?php echo e($dayIndex); ?>][grace_minutes]"
                                            class="form-control form-control-sm mb-1" placeholder="Grace"
                                            value="<?php echo e($cellGrace); ?>">

                                        <input type="text"
                                            name="schedule[<?php echo e($rowIndex); ?>][days][<?php echo e($dayIndex); ?>][remarks]"
                                            class="form-control form-control-sm" placeholder="Remarks"
                                            value="<?php echo e($cellRemarks); ?>">
                                    </div>
                                </td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="<?php echo e(count($days) + 1); ?>" class="text-center py-4 text-muted">
                                <?php if(blank(request('search'))): ?>
                                    Please search an employee first before plotting schedule.
                                <?php else: ?>
                                    No employee found matching your search.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="text-muted fs-10">
            Coverage: <?php echo e($startDate->format('F d, Y')); ?> to <?php echo e($endDate->format('F d, Y')); ?>

            <br>
            Showing <?php echo e($employees->firstItem() ?? 0); ?> to <?php echo e($employees->lastItem() ?? 0); ?>

            of <?php echo e($employees->total()); ?> employee(s)
        </div>

        <button type="submit" class="btn btn-primary" <?php echo e($employees->count() === 0 ? 'disabled' : ''); ?>>
            <span class="fas fa-save me-1"></span> Save Cutoff Plotting
        </button>
    </div>

    <?php if($employees->hasPages()): ?>
        <div class="px-3 pb-3 pt-2">
            <?php echo e($employees->links('pagination.custom')); ?>

        </div>
    <?php endif; ?>
</form>
<?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/payroll/plotting/table.blade.php ENDPATH**/ ?>