<div class="card monitor-card border-0 shadow-sm mb-3">
    <div class="card-header bg-body-tertiary border-bottom border-200 py-3 px-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <h5 class="mb-1">Monitoring Overview</h5>
                <small class="text-muted">Quick summary of current CCTV workload and activity.</small>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <?php
                    $pill = [
                        '' => 'All',
                        'Open' => 'Open',
                        'In Progress' => 'In Progress',
                        'Fixed' => 'Fixed',
                        'Closed' => 'Closed',
                    ];
                ?>

                <?php $__currentLoopData = $pill; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a class="btn btn-sm <?php echo e(request('status') === $val ? 'btn-primary' : 'btn-falcon-default'); ?>"
                        href="<?php echo e(route('concern.cctv.index', array_merge(request()->except('page'), ['status' => $val ?: null]))); ?>">
                        <?php echo e($label); ?>

                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="monitor-tile status-open h-100">
                    <div class="tile-label">Open</div>
                    <div class="tile-value"><?php echo e($statusCounts['Open'] ?? 0); ?></div>
                    <div class="tile-subtext">Needs attention</div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="monitor-tile status-progress h-100">
                    <div class="tile-label">In Progress</div>
                    <div class="tile-value"><?php echo e($statusCounts['In Progress'] ?? 0); ?></div>
                    <div class="tile-subtext">Currently being worked on</div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="monitor-tile status-fixed h-100">
                    <div class="tile-label">Fixed</div>
                    <div class="tile-value"><?php echo e($statusCounts['Fixed'] ?? 0); ?></div>
                    <div class="tile-subtext">Ready to close</div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="monitor-tile status-closed h-100">
                    <div class="tile-label">Closed</div>
                    <div class="tile-value"><?php echo e($statusCounts['Closed'] ?? 0); ?></div>
                    <div class="tile-subtext">Completed concerns</div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="insight-card h-100">
                    <div class="insight-title">Top Issue Type</div>
                    <div class="insight-main">
                        <span><?php echo e($topIssue ?? '—'); ?></span>
                        <strong><?php echo e($topIssueCount); ?></strong>
                    </div>

                    <div class="insight-list">
                        <?php $__empty_1 = true; $__currentLoopData = $issueCounts->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="insight-row">
                                <span><?php echo e($k); ?></span>
                                <strong><?php echo e($v); ?></strong>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-muted small">No data yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="insight-card h-100">
                    <div class="insight-title">Most Used CCTV Part</div>
                    <div class="insight-main">
                        <span><?php echo e($topPart ?? '—'); ?></span>
                        <strong><?php echo e($topPartCount); ?></strong>
                    </div>

                    <div class="insight-list">
                        <?php $__empty_1 = true; $__currentLoopData = $partCounts->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="insight-row">
                                <span><?php echo e($k); ?></span>
                                <strong><?php echo e($v); ?></strong>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-muted small">No parts used yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="insight-card h-100">
                    <div class="insight-title">Top Assignee Workload</div>
                    <div class="insight-main">
                        <span><?php echo e($topAssignee ?? '—'); ?></span>
                        <strong><?php echo e($topAssigneeCount); ?></strong>
                    </div>

                    <div class="insight-list">
                        <?php $__empty_1 = true; $__currentLoopData = $assigneeCounts->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="insight-row">
                                <span><?php echo e($k); ?></span>
                                <strong><?php echo e($v); ?></strong>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-muted small">No assigned records yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/it_department/concern/partials/monitoring.blade.php ENDPATH**/ ?>