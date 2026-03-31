<?php $__env->startSection('title', 'CCTV Job Orders - IT Department'); ?>

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

            <?php if(session('success')): ?>
                <div class="alert alert-success mb-3"><?php echo e(session('success')); ?></div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger mb-3">
                    <ul class="mb-0">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($e); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            
            <?php
                $statsSource = $allJobOrders; // counts ALL records under current filters

                $statusCounts = $statsSource->groupBy('status')->map->count();

                $issueCounts = $statsSource->groupBy('issue_type')->map->count()->sortDesc();
                $topIssue = $issueCounts->keys()->first();
                $topIssueCount = $issueCounts->first() ?? 0;

                $partCounts = $statsSource
                    ->map(fn($x) => trim((string) ($x->cctv_part ?? '')))
                    ->filter()
                    ->groupBy(fn($p) => $p)
                    ->map->count()
                    ->sortDesc();

                $topPart = $partCounts->keys()->first();
                $topPartCount = $partCounts->first() ?? 0;

                $assigneeCounts = $statsSource
                    ->map(fn($x) => $x->assignee->full_name ?? null)
                    ->filter()
                    ->groupBy(fn($n) => $n)
                    ->map->count()
                    ->sortDesc();

                $topAssignee = $assigneeCounts->keys()->first();
                $topAssigneeCount = $assigneeCounts->first() ?? 0;

                $statuses = ['Open', 'In Progress', 'Fixed', 'Closed'];
            ?>

            <div class="card monitor-card shadow-sm mb-3">
                <div class="card-header bg-body-tertiary border-bottom border-200">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                        <div>
                            <h6 class="mb-0">Monitoring</h6>
                            <small class="text-muted">Quick overview to spot workload and common issues</small>
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
                                    href="<?php echo e(route('concern.cctv.index', array_merge(request()->all(), ['status' => $val ?: null]))); ?>">
                                    <?php echo e($label); ?>

                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-3">

                        
                        <div class="col-6 col-md-3">
                            <div class="p-3 border monitor-tile h-100">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-muted fs-11">Open</div>
                                    <span class="badge rounded-pill badge-subtle-warning">Now</span>
                                </div>
                                <div class="fs-4 fw-bold mt-1"><?php echo e($statusCounts['Open'] ?? 0); ?></div>
                                <div class="form-hint">Needs attention</div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="p-3 border monitor-tile h-100">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-muted fs-11">In Progress</div>
                                    <span class="badge rounded-pill badge-subtle-info">Working</span>
                                </div>
                                <div class="fs-4 fw-bold mt-1"><?php echo e($statusCounts['In Progress'] ?? 0); ?></div>
                                <div class="form-hint">Ongoing tasks</div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="p-3 border monitor-tile h-100">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-muted fs-11">Fixed</div>
                                    <span class="badge rounded-pill badge-subtle-success">Done</span>
                                </div>
                                <div class="fs-4 fw-bold mt-1"><?php echo e($statusCounts['Fixed'] ?? 0); ?></div>
                                <div class="form-hint">Ready to close</div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <div class="p-3 border monitor-tile h-100">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-muted fs-11">Closed</div>
                                    <span class="badge rounded-pill badge-subtle-secondary">Completed</span>
                                </div>
                                <div class="fs-4 fw-bold mt-1"><?php echo e($statusCounts['Closed'] ?? 0); ?></div>
                                <div class="form-hint">Finished</div>
                            </div>
                        </div>

                        
                        <div class="col-lg-4">
                            <div class="p-3 border monitor-tile h-100">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="fw-semi-bold">Most Issue Type</div>
                                    <span class="text-muted fs-11">Top</span>
                                </div>

                                <div class="mt-2">
                                    <div class="d-flex justify-content-between">
                                        <div class="fw-bold"><?php echo e($topIssue ?? '—'); ?></div>
                                        <div class="text-muted"><?php echo e($topIssueCount); ?></div>
                                    </div>

                                    <div class="mt-2">
                                        <?php $__empty_1 = true; $__currentLoopData = $issueCounts->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <div class="mini-row">
                                                <span class="text-muted"><?php echo e($k); ?></span>
                                                <span class="fw-semi-bold"><?php echo e($v); ?></span>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <div class="text-muted fs-11">No data yet.</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                        <div class="col-lg-4">
                            <div class="p-3 border monitor-tile h-100">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="fw-semi-bold">Most CCTV Part</div>
                                    <span class="text-muted fs-11">Top</span>
                                </div>

                                <div class="mt-2">
                                    <div class="d-flex justify-content-between">
                                        <div class="fw-bold"><?php echo e($topPart ?? '—'); ?></div>
                                        <div class="text-muted"><?php echo e($topPartCount); ?></div>
                                    </div>

                                    <div class="mt-2">
                                        <?php $__empty_1 = true; $__currentLoopData = $partCounts->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <div class="mini-row">
                                                <span class="text-muted"><?php echo e($k); ?></span>
                                                <span class="fw-semi-bold"><?php echo e($v); ?></span>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <div class="text-muted fs-11">No CCTV Part data yet.</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        
                        <div class="col-lg-4">
                            <div class="p-3 border monitor-tile h-100">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="fw-semi-bold">Assignee Workload</div>
                                    <span class="text-muted fs-11">Top</span>
                                </div>

                                <div class="mt-2">
                                    <div class="d-flex justify-content-between">
                                        <div class="fw-bold"><?php echo e($topAssignee ?? '—'); ?></div>
                                        <div class="text-muted"><?php echo e($topAssigneeCount); ?></div>
                                    </div>

                                    <div class="mt-2">
                                        <?php $__empty_1 = true; $__currentLoopData = $assigneeCounts->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <div class="mini-row">
                                                <span class="text-muted"><?php echo e($k); ?></span>
                                                <span class="fw-semi-bold"><?php echo e($v); ?></span>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <div class="text-muted fs-11">No assigned records yet.</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            

            <div class="row g-3">
                
                <div class="col-xxl-9 col-xl-8">
                    <div class="card jo-card shadow-sm">

                        <div class="card-header bg-body-tertiary border-bottom border-200">
                            <div class="d-flex flex-column flex-lg-row gap-2 align-items-lg-center justify-content-between">
                                <div>
                                    <h5 class="mb-0">CCTV Job Orders</h5>
                                    <small class="text-muted">Create and track CCTV issues per bus</small>
                                </div>

                                <div class="d-flex gap-2 align-items-center flex-wrap">
                                    
                                    <form method="GET" action="<?php echo e(route('concern.cctv.index')); ?>">
                                        <div class="input-group input-group-sm" style="width: 320px;">
                                            <span class="input-group-text bg-white border-300">
                                                <span class="fa fa-search fs-10"></span>
                                            </span>
                                            <input class="form-control shadow-none border-300" name="q"
                                                type="search" value="<?php echo e(request('q')); ?>"
                                                placeholder="Search JO / Bus / Reporter..." />
                                            <button class="btn btn-outline-secondary border-300" type="submit">
                                                Search
                                            </button>
                                        </div>
                                        <input type="hidden" name="status" value="<?php echo e(request('status')); ?>">
                                    </form>

                                    
                                    <button class="btn btn-sm btn-falcon-default d-xl-none" type="button"
                                        data-bs-toggle="offcanvas" data-bs-target="#filterCanvas"
                                        aria-controls="filterCanvas">
                                        <span class="fas fa-filter"></span>
                                        <span class="ms-1">Filter</span>
                                    </button>

                                    <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="modal"
                                        data-bs-target="#createModal">
                                        <span class="fas fa-plus" data-fa-transform="shrink-3"></span>
                                        <span class="ms-1">New Job Order</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive scrollbar jo-table-wrap">
                                <table class="table table-sm mb-0 fs-10 align-middle jo-table">
                                    <thead class="bg-body-tertiary border-bottom border-200">
                                        <tr>
                                            <th class="ps-3">JO #</th>
                                            <th>Bus No</th>
                                            <th>Issue Type</th>
                                            <th>CCTV Part</th>
                                            <th>Status</th>
                                            <th>Assigned</th>
                                            <th class="text-end pe-3" style="width: 160px;">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php $__empty_1 = true; $__currentLoopData = $jobOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <tr>
                                                <td class="ps-3 fw-semi-bold"><?php echo e($jo->jo_no); ?></td>
                                                <td><?php echo e($jo->bus_no); ?></td>
                                                <td class="fw-semi-bold"><?php echo e($jo->issue_type); ?></td>
                                                <td class="text-muted"><?php echo e($jo->cctv_part ?? '—'); ?></td>
                                                <td>
                                                    <?php
                                                        $s = $jo->status;
                                                        $cls = match ($s) {
                                                            'Open' => 'badge-subtle-warning',
                                                            'In Progress' => 'badge-subtle-info',
                                                            'Fixed' => 'badge-subtle-success',
                                                            'Closed' => 'badge-subtle-secondary',
                                                            default => 'badge-subtle-primary',
                                                        };
                                                    ?>
                                                    <span
                                                        class="badge rounded-pill <?php echo e($cls); ?>"><?php echo e($jo->status); ?></span>
                                                </td>
                                                <td class="text-muted"><?php echo e(optional($jo->assignee)->full_name ?? '—'); ?>

                                                </td>

                                                <td class="text-end pe-3 jo-actions">
                                                    <button class="btn btn-sm btn-falcon-default" type="button"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editModal<?php echo e($jo->id); ?>"
                                                        title="View / Update">
                                                        <span class="fas fa-eye"></span>
                                                    </button>

                                                    <form action="<?php echo e(route('concern.cctv.destroy', $jo->id)); ?>"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirm('Delete this job order?')">
                                                        <?php echo csrf_field(); ?>
                                                        <?php echo method_field('DELETE'); ?>
                                                        <button class="btn btn-sm btn-falcon-danger" type="submit"
                                                            title="Delete">
                                                            <span class="fas fa-trash"></span>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>

                                            
                                            <div class="modal fade" id="editModal<?php echo e($jo->id); ?>" tabindex="-1"
                                                aria-hidden="true">
                                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <form method="POST"
                                                            action="<?php echo e(route('concern.cctv.update', $jo->id)); ?>">
                                                            <?php echo csrf_field(); ?>
                                                            <?php echo method_field('PUT'); ?>

                                                            <div class="modal-header">
                                                                <div>
                                                                    <h5 class="modal-title mb-0">Job Order:
                                                                        <?php echo e($jo->jo_no); ?></h5>
                                                                    <div class="form-hint">Update status, details, actions
                                                                        and assignee.</div>
                                                                </div>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal"></button>
                                                            </div>

                                                            <div class="modal-body">
                                                                <div class="row g-3">
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Bus No</label>
                                                                        <select class="form-select" name="bus_no"
                                                                            required>
                                                                            <option value="">-- Select Bus --
                                                                            </option>
                                                                            <?php $__currentLoopData = $buses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bus): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                <?php
                                                                                    $label = trim(
                                                                                        ($bus->body_number ?? '') .
                                                                                            ' - ' .
                                                                                            ($bus->plate_number ?? '') .
                                                                                            ' - ' .
                                                                                            ($bus->name ?? ''),
                                                                                    );
                                                                                ?>
                                                                                <option value="<?php echo e($bus->body_number); ?>"
                                                                                    <?php if($jo->bus_no == $bus->body_number): echo 'selected'; endif; ?>>
                                                                                    <?php echo e($label); ?>

                                                                                    <?php echo e($bus->garage ? "({$bus->garage})" : ''); ?>

                                                                                </option>
                                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                        </select>
                                                                    </div>

                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Reported By</label>
                                                                        <input class="form-control" name="reported_by"
                                                                            value="<?php echo e($jo->reported_by); ?>">
                                                                    </div>

                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Status</label>
                                                                        <select class="form-select" name="status"
                                                                            required>
                                                                            <?php $__currentLoopData = ['Open', 'In Progress', 'Fixed', 'Closed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                <option value="<?php echo e($st); ?>"
                                                                                    <?php if($jo->status === $st): echo 'selected'; endif; ?>>
                                                                                    <?php echo e($st); ?>

                                                                                </option>
                                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                        </select>
                                                                    </div>

                                                                    <div class="col-12">
                                                                        <div class="soft-divider"></div>
                                                                    </div>

                                                                    <div class="col-md-6">
                                                                        <label class="form-label">Issue Type</label>
                                                                        <select class="form-select" name="issue_type"
                                                                            required>
                                                                            <?php $__currentLoopData = ['Camera', 'Monitor', 'DVR', 'Wiring', 'Power', 'Other']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                <option value="<?php echo e($it); ?>"
                                                                                    <?php if($jo->issue_type === $it): echo 'selected'; endif; ?>>
                                                                                    <?php echo e($it); ?>

                                                                                </option>
                                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                        </select>
                                                                    </div>

                                                                    <div class="col-md-6">
                                                                        <label class="form-label">CCTV Part</label>
                                                                        <input class="form-control" name="cctv_part"
                                                                            value="<?php echo e($jo->cctv_part); ?>"
                                                                            placeholder="ex: Camera #1 Front">
                                                                    </div>

                                                                    <div class="col-12">
                                                                        <label class="form-label">Problem Details</label>
                                                                        <textarea class="form-control" name="problem_details" rows="3" required><?php echo e($jo->problem_details); ?></textarea>
                                                                    </div>

                                                                    <div class="col-12">
                                                                        <label class="form-label">Action Taken</label>
                                                                        <textarea class="form-control" name="action_taken" rows="3"><?php echo e($jo->action_taken); ?></textarea>
                                                                    </div>

                                                                    <div class="col-md-6">
                                                                        <label class="form-label">Assign To</label>
                                                                        <select class="form-select" name="assigned_to">
                                                                            <option value="">— None —</option>
                                                                            <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                <option value="<?php echo e($a->id); ?>"
                                                                                    <?php if($jo->assigned_to == $a->id): echo 'selected'; endif; ?>>
                                                                                    <?php echo e($a->full_name); ?>

                                                                                </option>
                                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                        </select>
                                                                        <div class="form-hint mt-1">Optional. Choose who
                                                                            will handle this job order.</div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-falcon-default"
                                                                    data-bs-dismiss="modal">Close</button>
                                                                <button type="submit" class="btn btn-primary">
                                                                    <span class="fas fa-save me-1"></span> Update
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr>
                                                <td colspan="7" class="text-center">
                                                    <div class="empty-state">
                                                        <div class="icon">
                                                            <span class="fas fa-video"></span>
                                                        </div>
                                                        <div class="fw-bold">No Job Orders Found</div>
                                                        <div class="text-muted fs-11">Try clearing filters or create a new
                                                            job order.</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        
                        <div class="card-footer bg-body-tertiary border-top border-200">
                            <div
                                class="d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center px-3">
                                <small class="text-muted">
                                    Showing <?php echo e($jobOrders->firstItem() ?? 0); ?> to <?php echo e($jobOrders->lastItem() ?? 0); ?> of
                                    <?php echo e($jobOrders->total()); ?>

                                </small>

                                <div class="ms-md-auto">
                                    <?php echo e($jobOrders->links('pagination.custom')); ?>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                
                <div class="col-xxl-3 col-xl-4 d-none d-xl-block">
                    <div class="card filter-card shadow-sm">
                        <div class="card-header bg-body-tertiary border-bottom border-200">
                            <h6 class="mb-0">Filter</h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="<?php echo e(route('concern.cctv.index')); ?>">
                                <div class="mb-3">
                                    <label class="form-label mb-1">Status</label>
                                    <select class="form-select form-select-sm" name="status">
                                        <option value="">All</option>
                                        <?php $__currentLoopData = ['Open', 'In Progress', 'Fixed', 'Closed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($st); ?>" <?php if(request('status') === $st): echo 'selected'; endif; ?>>
                                                <?php echo e($st); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>

                                <input type="hidden" name="q" value="<?php echo e(request('q')); ?>">
                                <button class="btn btn-primary w-100" type="submit">
                                    <span class="fas fa-check me-1"></span> Apply
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

            
            <div class="offcanvas offcanvas-end" tabindex="-1" id="filterCanvas" aria-labelledby="filterCanvasLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="filterCanvasLabel">Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                </div>
                <div class="offcanvas-body">
                    <form method="GET" action="<?php echo e(route('concern.cctv.index')); ?>">
                        <div class="mb-3">
                            <label class="form-label mb-1">Status</label>
                            <select class="form-select" name="status">
                                <option value="">All</option>
                                <?php $__currentLoopData = ['Open', 'In Progress', 'Fixed', 'Closed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($st); ?>" <?php if(request('status') === $st): echo 'selected'; endif; ?>><?php echo e($st); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <input type="hidden" name="q" value="<?php echo e(request('q')); ?>">
                        <button class="btn btn-primary w-100" type="submit">
                            <span class="fas fa-check me-1"></span> Apply Filter
                        </button>
                    </form>
                </div>
            </div>

            
            <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="<?php echo e(route('concern.cctv.store')); ?>">
                            <?php echo csrf_field(); ?>

                            <div class="modal-header">
                                <div>
                                    <h5 class="modal-title mb-0">Create CCTV Job Order</h5>
                                    <div class="form-hint">Fill up the details then save.</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Bus</label>
                                        <select class="form-select bus-select" name="bus_no" required>
                                            <option value="">-- Select Bus --</option>
                                            <?php $__currentLoopData = $buses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bus): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($bus->body_number); ?>">
                                                    <?php echo e($bus->body_number); ?>

                                                    - <?php echo e($bus->plate_number ?? 'No Plate'); ?>

                                                    - <?php echo e($bus->name ?? 'No Name'); ?>

                                                    <?php echo e($bus->garage ? "({$bus->garage})" : ''); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Reported By</label>
                                        <input type="text" class="form-control"
                                            value="<?php echo e(auth()->user()->full_name); ?>" readonly>
                                        <input type="hidden" name="reported_by"
                                            value="<?php echo e(auth()->user()->full_name); ?>">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" name="status" required>
                                            <?php $__currentLoopData = ['Open', 'In Progress', 'Fixed', 'Closed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($st); ?>" <?php if($st === 'Open'): echo 'selected'; endif; ?>>
                                                    <?php echo e($st); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <div class="soft-divider"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Issue Type</label>
                                        <select class="form-select" name="issue_type" required>
                                            <?php $__currentLoopData = ['Camera', 'Monitor', 'DVR', 'Wiring', 'Power', 'Other']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($it); ?>"><?php echo e($it); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">CCTV Part</label>
                                        <input class="form-control" name="cctv_part" placeholder="ex: Camera #1 Front">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Problem Details</label>
                                        <textarea class="form-control" name="problem_details" rows="3" required></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Assign To</label>
                                        <select class="form-select" name="assigned_to">
                                            <option value="">— None —</option>
                                            <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($a->id); ?>"><?php echo e($a->full_name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <div class="form-hint mt-1">Optional.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-falcon-default"
                                    data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <span class="fas fa-save me-1"></span> Save Job Order
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            $(document).ready(function() {
                $('.bus-select').select2({
                    placeholder: "Select Bus",
                    allowClear: true,
                    width: '100%',
                    theme: 'bootstrap-5'
                });
            });
        </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        /* Small UI polish only */
        .jo-card {
            border-radius: 14px;
            overflow: hidden;
        }

        .jo-card .card-header {
            padding: 1rem 1.25rem;
        }

        .jo-card .card-footer {
            padding: .9rem 1.25rem;
        }

        .jo-table-wrap {
            max-height: 560px;
        }

        .jo-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .jo-table tbody tr:hover {
            background: rgba(0, 0, 0, .025);
        }

        .jo-actions .btn {
            padding: .32rem .55rem;
        }

        .soft-divider {
            border-top: 1px dashed rgba(0, 0, 0, .08);
            margin: .75rem 0;
        }

        .empty-state {
            padding: 2.5rem 1rem;
        }

        .empty-state .icon {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: rgba(0, 0, 0, .04);
            margin: 0 auto .75rem;
        }

        .filter-card {
            border-radius: 14px;
        }

        .modal .modal-content {
            border-radius: 14px;
        }

        .form-hint {
            font-size: .78rem;
            color: #6c757d;
        }

        /* Monitoring */
        .monitor-card {
            border-radius: 14px;
        }

        .monitor-tile {
            border-radius: 12px;
        }

        .mini-row {
            display: flex;
            justify-content: space-between;
            padding: .25rem 0;
        }

        .mini-row+.mini-row {
            border-top: 1px dashed rgba(0, 0, 0, .08);
        }

        /* Pagination (same as tickets) */
        .pagination {
            font-size: 14px !important;
            margin: 0 !important;
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/it_department/cctv_concern.blade.php ENDPATH**/ ?>