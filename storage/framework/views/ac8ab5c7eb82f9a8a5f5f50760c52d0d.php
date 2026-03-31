<div class="table-responsive scrollbar" style="overflow: visible !important;">
    <table class="table table-hover table-striped fs-10 mb-0 w-100">
        <thead class="bg-200 text-900">
            <tr>
                <th>Employee</th>
                <th>Leave Type</th>
                <th>From</th>
                <th>To</th>
                <th>No. of Days</th>
                <th>Record Status</th>
                <th>Status of Leave</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>

        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $leaves; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leave): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $st = strtolower($leave->status ?? '');
                    $isLocked = in_array($st, ['cancelled', 'terminated', 'completed'], true);

                    $start = $leave->start_date
                        ? \Carbon\Carbon::parse($leave->start_date, 'Asia/Manila')->startOfDay()
                        : null;

                    $end = $leave->end_date
                        ? \Carbon\Carbon::parse($leave->end_date, 'Asia/Manila')->startOfDay()
                        : null;

                    $notStarted = $start && $today->lt($start);
                    $onLeave = $start && $end && $today->gte($start) && $today->lte($end);
                    $afterLeave = $end && $today->gt($end);

                    // ✅ show ready + notices ONLY after leave ends (and not locked)
                    $canShowReady = true;
                    $canShowNotices = $afterLeave && !$isLocked;
                ?>

                <tr>
                    <td class="employee">
                        <strong><?php echo e($leave->employee->full_name); ?></strong><br>
                        <span class="text-muted"><?php echo e($leave->employee->position?->title ?? '-'); ?></span>
                    </td>

                    <td class="type">
                        <?php echo e($leave->leave_type); ?>

                        <span class="ms-1" data-bs-toggle="tooltip"
                            title="<?php echo e(e($leave->reason ?? 'No reason provided')); ?>">
                            <i class="fas fa-exclamation-circle text-info"></i>
                        </span>
                    </td>

                    <td class="from"><?php echo e(\Carbon\Carbon::parse($leave->start_date)->format('d M Y')); ?></td>
                    <td class="to"><?php echo e(\Carbon\Carbon::parse($leave->end_date)->format('d M Y')); ?></td>
                    <td class="days"><?php echo e($leave->days); ?> Days</td>

                    
                    <td><?php echo $leave->record_status_badge ?? '<span class="badge bg-primary">Active</span>'; ?></td>

                    
                    <td class="remaining"><?php echo $leave->remaining_status; ?></td>

                    <td class="text-center">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-primary dropdown-toggle">
                                Actions
                            </button>

                            <ul class="dropdown-menu dropdown-menu-end">
                                
                                <li>
                                    <a class="dropdown-item <?php echo e($isLocked ? 'disabled' : ''); ?>"
                                        href="<?php echo e($isLocked ? '#' : route('driver-leave.driver.edit', $leave)); ?>">
                                        <i class="fas fa-edit me-2 text-primary"></i>Edit Leave
                                    </a>
                                </li>

                                
                                <?php if($canShowReady): ?>
                                    <li>
                                        <a class="dropdown-item action-open-modal" href="#"
                                            data-id="<?php echo e($leave->id); ?>" data-action="ready"
                                            data-employee="<?php echo e(e($leave->employee->full_name)); ?>"
                                            data-type="<?php echo e(e($leave->leave_type)); ?>">
                                            <i class="fas fa-user-check me-2 text-success"></i> Ready for Duty
                                        </a>
                                    </li>

                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                <?php endif; ?>

                                
                                <?php if($canShowNotices): ?>
                                    
                                    <li>
                                        <?php if($leave->first_notice_sent_at): ?>
                                            <a class="dropdown-item disabled text-primary">
                                                <i class="fas fa-check-circle text-primary me-2"></i>
                                                1st Notice Sent
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo e($leave->first_notice_sent_at->format('M d, Y h:i A')); ?>

                                                </small>
                                            </a>
                                        <?php else: ?>
                                            <a class="dropdown-item action-open-modal text-primary" href="#"
                                                data-action="first" data-id="<?php echo e($leave->id); ?>"
                                                data-employee="<?php echo e(e($leave->employee->full_name)); ?>"
                                                data-type="<?php echo e(e($leave->leave_type)); ?>">
                                                <i class="fas fa-paper-plane text-primary me-2"></i>
                                                Mark 1st Notice Sent
                                            </a>
                                        <?php endif; ?>
                                    </li>

                                    
                                    <li>
                                        <?php if($leave->second_notice_sent_at): ?>
                                            <a class="dropdown-item disabled text-warning">
                                                <i class="fas fa-check-circle text-warning me-2"></i>
                                                2nd Notice Sent
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo e($leave->second_notice_sent_at->format('M d, Y h:i A')); ?>

                                                </small>
                                            </a>
                                        <?php else: ?>
                                            <a class="dropdown-item action-open-modal text-warning <?php echo e(!$leave->first_notice_sent_at ? 'disabled' : ''); ?>"
                                                href="#" data-action="second" data-id="<?php echo e($leave->id); ?>"
                                                data-employee="<?php echo e(e($leave->employee->full_name)); ?>"
                                                data-type="<?php echo e(e($leave->leave_type)); ?>">
                                                <i class="fas fa-envelope text-warning me-2"></i>
                                                Mark 2nd Notice Sent
                                            </a>
                                        <?php endif; ?>
                                    </li>

                                    
                                    <li>
                                        <?php if($leave->final_notice_sent_at): ?>
                                            <a class="dropdown-item disabled text-danger">
                                                <i class="fas fa-check-circle text-danger me-2"></i>
                                                Final Notice Sent
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo e($leave->final_notice_sent_at->format('M d, Y h:i A')); ?>

                                                </small>
                                            </a>
                                        <?php else: ?>
                                            <a class="dropdown-item action-open-modal text-danger <?php echo e(!$leave->second_notice_sent_at ? 'disabled' : ''); ?>"
                                                href="#" data-action="terminate" data-id="<?php echo e($leave->id); ?>"
                                                data-employee="<?php echo e(e($leave->employee->full_name)); ?>"
                                                data-type="<?php echo e(e($leave->leave_type)); ?>">
                                                <i class="fas fa-file-signature text-danger me-2"></i>
                                                Mark Final Notice Sent
                                            </a>
                                        <?php endif; ?>
                                    </li>

                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                <?php endif; ?>

                                
                                <li>
                                    <a class="dropdown-item action-open-modal <?php echo e($isLocked ? 'disabled' : ''); ?>"
                                        href="#" data-id="<?php echo e($leave->id); ?>" data-action="cancel"
                                        data-employee="<?php echo e(e($leave->employee->full_name)); ?>"
                                        data-type="<?php echo e(e($leave->leave_type)); ?>">
                                        <i class="fas fa-ban me-2"></i> Cancel Leave
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="8" class="text-center py-3 text-muted">No leave records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="my-3 d-flex justify-content-end px-3">
    <?php echo e($leaves->links('pagination.custom')); ?>

</div>
<?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/hr_department/leaves/driver/table.blade.php ENDPATH**/ ?>