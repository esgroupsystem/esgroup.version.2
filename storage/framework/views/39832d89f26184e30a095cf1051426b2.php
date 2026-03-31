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
                <div class="alert alert-success border-0 shadow-sm mb-3"><?php echo e(session('success')); ?></div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger border-0 shadow-sm mb-3">
                    <ul class="mb-0 ps-3">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($e); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php
                $statsSource = $allJobOrders;

                $statusCounts = $statsSource->groupBy('status')->map->count();

                $issueCounts = $statsSource->groupBy('issue_type')->map->count()->sortDesc();
                $topIssue = $issueCounts->keys()->first();
                $topIssueCount = $issueCounts->first() ?? 0;

                $partCounts = $statsSource
                    ->flatMap(function ($job) {
                        return $job->usedItems->map(function ($used) {
                            return $used->inventoryItem->item_name ?? null;
                        });
                    })
                    ->filter()
                    ->groupBy(fn($name) => $name)
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

                $statusMap = [
                    'Open' => ['class' => 'warning', 'label' => 'Needs Attention'],
                    'In Progress' => ['class' => 'info', 'label' => 'Ongoing'],
                    'Fixed' => ['class' => 'success', 'label' => 'Resolved'],
                    'Closed' => ['class' => 'secondary', 'label' => 'Completed'],
                ];
            ?>

            
            <div class="card page-hero-card border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                        <div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="hero-icon">
                                    <span class="fas fa-video"></span>
                                </div>
                                <div>
                                    <h4 class="mb-1 text-dark">CCTV Job Orders</h4>
                                    <p class="text-muted mb-0">
                                        Monitor, assign, and resolve CCTV concerns for buses and equipment.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-falcon-default d-xl-none" type="button" data-bs-toggle="offcanvas"
                                data-bs-target="#filterCanvas" aria-controls="filterCanvas">
                                <span class="fas fa-filter me-1"></span> Filter
                            </button>

                            <button class="btn btn-primary shadow-sm" type="button" data-bs-toggle="modal"
                                data-bs-target="#createModal">
                                <span class="fas fa-plus me-1"></span> New Job Order
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            
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

            <div class="row g-3">
                
                <div class="col-xxl-9 col-xl-8">
                    <div class="card jo-card border-0 shadow-sm">
                        <div class="card-header bg-body-tertiary border-bottom border-200 py-3 px-4">
                            <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between">
                                <div>
                                    <h5 class="mb-1">Job Order List</h5>
                                    <small class="text-muted">Manage CCTV concerns and track assigned work.</small>
                                </div>

                                <div class="d-flex gap-2 align-items-center flex-wrap">
                                    <form method="GET" action="<?php echo e(route('concern.cctv.index')); ?>">
                                        <div class="input-group input-group-sm search-box">
                                            <span class="input-group-text bg-white border-300">
                                                <span class="fa fa-search fs-10"></span>
                                            </span>
                                            <input class="form-control shadow-none border-300" name="q" type="search"
                                                value="<?php echo e(request('q')); ?>"
                                                placeholder="Search JO #, bus no, reporter, issue..." />
                                            <button class="btn btn-outline-secondary border-300" type="submit">
                                                Search
                                            </button>
                                        </div>
                                        <input type="hidden" name="status" value="<?php echo e(request('status')); ?>">
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive jo-table-wrap">
                                <table class="table align-middle mb-0 jo-table">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">JO #</th>
                                            <th>Bus No</th>
                                            <th>Issue Type</th>
                                            <th>Items Used</th>
                                            <th>Status</th>
                                            <th>Assigned To</th>
                                            <th class="text-end pe-4" style="width: 160px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__empty_1 = true; $__currentLoopData = $jobOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <?php
                                                $badgeClass = match ($jo->status) {
                                                    'Open' => 'badge-subtle-warning',
                                                    'In Progress' => 'badge-subtle-info',
                                                    'Fixed' => 'badge-subtle-success',
                                                    'Closed' => 'badge-subtle-secondary',
                                                    default => 'badge-subtle-primary',
                                                };
                                            ?>

                                            <tr>
                                                <td class="ps-4">
                                                    <div class="fw-semi-bold text-dark"><?php echo e($jo->jo_no); ?></div>
                                                    <div class="small text-muted"><?php echo e($jo->reported_by ?: '—'); ?></div>
                                                </td>
                                                <td>
                                                    <div class="fw-semi-bold"><?php echo e($jo->bus_no); ?></div>
                                                </td>
                                                <td>
                                                    <span class="table-chip"><?php echo e($jo->issue_type); ?></span>
                                                </td>
                                                <td class="text-muted">
                                                    <?php $__empty_2 = true; $__currentLoopData = $jo->usedItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $used): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                                        <div class="small mb-1">
                                                            <?php echo e($used->inventoryItem->item_name ?? 'Item'); ?>

                                                            <span class="text-dark fw-semi-bold">×
                                                                <?php echo e($used->qty_used); ?></span>
                                                        </div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                                        —
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge rounded-pill <?php echo e($badgeClass); ?>"><?php echo e($jo->status); ?></span>
                                                </td>
                                                <td>
                                                    <span
                                                        class="text-muted"><?php echo e(optional($jo->assignee)->full_name ?? '—'); ?></span>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <button class="btn btn-sm btn-falcon-default" type="button"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editModal<?php echo e($jo->id); ?>">
                                                            <span class="fas fa-eye me-1"></span> View
                                                        </button>

                                                        <form action="<?php echo e(route('concern.cctv.destroy', $jo->id)); ?>"
                                                            method="POST" class="d-inline"
                                                            onsubmit="return confirm('Delete this job order?')">
                                                            <?php echo csrf_field(); ?>
                                                            <?php echo method_field('DELETE'); ?>
                                                            <button class="btn btn-sm btn-falcon-danger" type="submit">
                                                                <span class="fas fa-trash"></span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr>
                                                <td colspan="7" class="text-center">
                                                    <div class="empty-state">
                                                        <div class="empty-state-icon">
                                                            <span class="fas fa-video"></span>
                                                        </div>
                                                        <h6 class="mb-1">No Job Orders Found</h6>
                                                        <p class="text-muted mb-0">Try changing your filters or create a
                                                            new
                                                            job order.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card-footer bg-body-tertiary border-top border-200 py-3 px-4">
                            <div
                                class="d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center">
                                <small class="text-muted">
                                    Showing <?php echo e($jobOrders->firstItem() ?? 0); ?> to <?php echo e($jobOrders->lastItem() ?? 0); ?> of
                                    <?php echo e($jobOrders->total()); ?> entries
                                </small>

                                <div class="ms-md-auto">
                                    <?php echo e($jobOrders->links('pagination.custom')); ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="col-xxl-3 col-xl-4 d-none d-xl-block">
                    <div class="card filter-card border-0 shadow-sm">
                        <div class="card-header bg-body-tertiary border-bottom border-200 py-3 px-4">
                            <h6 class="mb-0">Filter Panel</h6>
                        </div>
                        <div class="card-body p-4">
                            <form method="GET" action="<?php echo e(route('concern.cctv.index')); ?>">
                                <div class="mb-3">
                                    <label class="form-label mb-1">Status</label>
                                    <select class="form-select form-select-sm" name="status">
                                        <option value="">All</option>
                                        <?php $__currentLoopData = ['Open', 'In Progress', 'Fixed', 'Closed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($st); ?>" <?php if(request('status') === $st): echo 'selected'; endif; ?>>
                                                <?php echo e($st); ?>

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
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg">
                        <form method="POST" action="<?php echo e(route('concern.cctv.store')); ?>">
                            <?php echo csrf_field(); ?>

                            <div class="modal-header">
                                <div>
                                    <h5 class="modal-title mb-1">Create CCTV Job Order</h5>
                                    <div class="form-hint">Fill in all required details and save.</div>
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
                                                    <?php echo e($bus->body_number); ?> - <?php echo e($bus->plate_number ?? 'No Plate'); ?> -
                                                    <?php echo e($bus->name ?? 'No Name'); ?>

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
                                        <label class="form-label">Assign To</label>
                                        <select class="form-select" name="assigned_to">
                                            <option value="">— None —</option>
                                            <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($a->id); ?>"><?php echo e($a->full_name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <div class="form-hint mt-1">Optional.</div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Items Used</label>
                                        <div id="create-items-wrapper">
                                            <div class="row g-2 item-row mb-2">
                                                <div class="col-md-6">
                                                    <select class="form-select" name="items[0][it_inventory_item_id]">
                                                        <option value="">-- Select Inventory Item --</option>
                                                        <?php $__currentLoopData = $inventoryItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <option value="<?php echo e($item->id); ?>">
                                                                <?php echo e($item->item_name); ?> | Stock: <?php echo e($item->stock_qty); ?>

                                                                <?php echo e($item->unit); ?>

                                                                <?php echo e($item->brand ? '| ' . $item->brand : ''); ?>

                                                            </option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                </div>

                                                <div class="col-md-2">
                                                    <input type="number" min="1" class="form-control"
                                                        name="items[0][qty_used]" placeholder="Qty">
                                                </div>

                                                <div class="col-md-3">
                                                    <input type="text" class="form-control" name="items[0][remarks]"
                                                        placeholder="Remarks">
                                                </div>

                                                <div class="col-md-1 d-grid">
                                                    <button type="button"
                                                        class="btn btn-outline-danger remove-item-row">&times;</button>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="button" class="btn btn-sm btn-falcon-default mt-2"
                                            id="add-create-item-row">
                                            <span class="fas fa-plus me-1"></span> Add Item
                                        </button>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Problem Details</label>
                                        <textarea class="form-control" name="problem_details" rows="3" required></textarea>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Action Taken</label>
                                        <textarea class="form-control" name="action_taken" rows="3"></textarea>
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

            
            
            <?php $__currentLoopData = $jobOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $badgeClass = match ($jo->status) {
                        'Open' => 'badge-subtle-warning',
                        'In Progress' => 'badge-subtle-info',
                        'Fixed' => 'badge-subtle-success',
                        'Closed' => 'badge-subtle-secondary',
                        default => 'badge-subtle-primary',
                    };
                ?>

                <div class="modal fade" id="editModal<?php echo e($jo->id); ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content cctv-modal border-0 shadow-lg">
                            <form method="POST" action="<?php echo e(route('concern.cctv.update', $jo->id)); ?>">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>

                                <div class="modal-header cctv-modal-header border-0">
                                    <div class="w-100">
                                        <div
                                            class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="modal-icon">
                                                    <span class="fas fa-video"></span>
                                                </div>
                                                <div>
                                                    <h4 class="modal-title mb-1">Update CCTV Job Order</h4>
                                                    <div class="text-muted small">Review and update job order details.
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="text-lg-end">
                                                <div class="fw-bold fs-5 text-dark"><?php echo e($jo->jo_no); ?></div>
                                                <span
                                                    class="badge rounded-pill <?php echo e($badgeClass); ?>"><?php echo e($jo->status); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" class="btn-close ms-3" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body pt-0">
                                    
                                    <div class="form-section">
                                        <div class="section-title">
                                            <span class="fas fa-info-circle me-2 text-primary"></span>
                                            Basic Information
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Bus No</label>
                                                <select class="form-select form-select-modern" name="bus_no" required>
                                                    <option value="">-- Select Bus --</option>
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
                                                <input type="text" class="form-control form-control-modern"
                                                    name="reported_by" value="<?php echo e($jo->reported_by); ?>">
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label">Status</label>
                                                <select class="form-select form-select-modern" name="status" required>
                                                    <?php $__currentLoopData = ['Open', 'In Progress', 'Fixed', 'Closed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($st); ?>" <?php if($jo->status === $st): echo 'selected'; endif; ?>>
                                                            <?php echo e($st); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    
                                    <div class="form-section">
                                        <div class="section-title">
                                            <span class="fas fa-tools me-2 text-primary"></span>
                                            Issue & Assignment
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Issue Type</label>
                                                <select class="form-select form-select-modern" name="issue_type" required>
                                                    <?php $__currentLoopData = ['Camera', 'Monitor', 'DVR', 'Wiring', 'Power', 'Other']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($it); ?>" <?php if($jo->issue_type === $it): echo 'selected'; endif; ?>>
                                                            <?php echo e($it); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Assign To</label>
                                                <select class="form-select form-select-modern" name="assigned_to">
                                                    <option value="">— None —</option>
                                                    <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($a->id); ?>" <?php if($jo->assigned_to == $a->id): echo 'selected'; endif; ?>>
                                                            <?php echo e($a->full_name); ?>

                                                        </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                                <div class="form-hint mt-1">Optional. Choose assigned personnel.</div>
                                            </div>
                                        </div>
                                    </div>

                                    
                                    <div class="form-section">
                                        <div
                                            class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                            <div class="section-title mb-0">
                                                <span class="fas fa-box-open me-2 text-primary"></span>
                                                Items Used
                                            </div>

                                            <button type="button" class="btn btn-sm btn-primary add-edit-item-row"
                                                data-wrapper="edit-items-wrapper-<?php echo e($jo->id); ?>">
                                                <span class="fas fa-plus me-1"></span> Add Item
                                            </button>
                                        </div>

                                        <div class="items-card">
                                            <div id="edit-items-wrapper-<?php echo e($jo->id); ?>">
                                                <?php $__empty_1 = true; $__currentLoopData = $jo->usedItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $used): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                    <div class="row g-2 item-row item-row-modern mb-2">
                                                        <div class="col-md-6">
                                                            <label class="form-label small text-muted mb-1">Inventory
                                                                Item</label>
                                                            <select class="form-select form-select-modern"
                                                                name="items[<?php echo e($idx); ?>][it_inventory_item_id]">
                                                                <option value="">-- Select Inventory Item --</option>
                                                                <?php $__currentLoopData = $inventoryItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <option value="<?php echo e($item->id); ?>"
                                                                        <?php if($used->it_inventory_item_id == $item->id): echo 'selected'; endif; ?>>
                                                                        <?php echo e($item->item_name); ?> | Stock:
                                                                        <?php echo e($item->stock_qty); ?>

                                                                        <?php echo e($item->unit); ?><?php echo e($item->brand ? ' | ' . $item->brand : ''); ?>

                                                                    </option>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            </select>
                                                        </div>

                                                        <div class="col-md-2">
                                                            <label class="form-label small text-muted mb-1">Qty</label>
                                                            <input type="number" min="1"
                                                                class="form-control form-control-modern"
                                                                name="items[<?php echo e($idx); ?>][qty_used]"
                                                                value="<?php echo e($used->qty_used); ?>" placeholder="Qty">
                                                        </div>

                                                        <div class="col-md-3">
                                                            <label class="form-label small text-muted mb-1">Remarks</label>
                                                            <input type="text" class="form-control form-control-modern"
                                                                name="items[<?php echo e($idx); ?>][remarks]"
                                                                value="<?php echo e($used->remarks); ?>" placeholder="Remarks">
                                                        </div>

                                                        <div class="col-md-1 d-grid">
                                                            <label class="form-label small invisible mb-1">Remove</label>
                                                            <button type="button"
                                                                class="btn btn-outline-danger rounded-pill remove-item-row">
                                                                <span class="fas fa-times"></span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                    <div class="row g-2 item-row item-row-modern mb-2">
                                                        <div class="col-md-6">
                                                            <label class="form-label small text-muted mb-1">Inventory
                                                                Item</label>
                                                            <select class="form-select form-select-modern"
                                                                name="items[0][it_inventory_item_id]">
                                                                <option value="">-- Select Inventory Item --</option>
                                                                <?php $__currentLoopData = $inventoryItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <option value="<?php echo e($item->id); ?>">
                                                                        <?php echo e($item->item_name); ?> | Stock:
                                                                        <?php echo e($item->stock_qty); ?>

                                                                        <?php echo e($item->unit); ?><?php echo e($item->brand ? ' | ' . $item->brand : ''); ?>

                                                                    </option>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            </select>
                                                        </div>

                                                        <div class="col-md-2">
                                                            <label class="form-label small text-muted mb-1">Qty</label>
                                                            <input type="number" min="1"
                                                                class="form-control form-control-modern"
                                                                name="items[0][qty_used]" placeholder="Qty">
                                                        </div>

                                                        <div class="col-md-3">
                                                            <label class="form-label small text-muted mb-1">Remarks</label>
                                                            <input type="text" class="form-control form-control-modern"
                                                                name="items[0][remarks]" placeholder="Remarks">
                                                        </div>

                                                        <div class="col-md-1 d-grid">
                                                            <label class="form-label small invisible mb-1">Remove</label>
                                                            <button type="button"
                                                                class="btn btn-outline-danger rounded-pill remove-item-row">
                                                                <span class="fas fa-times"></span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    
                                    <div class="form-section">
                                        <div class="section-title">
                                            <span class="fas fa-align-left me-2 text-primary"></span>
                                            Work Details
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label">Problem Details</label>
                                                <textarea class="form-control form-control-modern" name="problem_details" rows="4" required><?php echo e($jo->problem_details); ?></textarea>
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label">Action Taken</label>
                                                <textarea class="form-control form-control-modern" name="action_taken" rows="4"><?php echo e($jo->action_taken); ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-light rounded-pill px-4"
                                        data-bs-dismiss="modal">
                                        Close
                                    </button>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                        <span class="fas fa-save me-1"></span> Update Job Order
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        $(document).ready(function() {
            $('.bus-select').select2({
                placeholder: "Select Bus",
                allowClear: true,
                width: '100%',
                theme: 'bootstrap-5',
                dropdownParent: $('#createModal')
            });

            const inventoryOptions = `
                <option value="">-- Select Inventory Item --</option>
                <?php $__currentLoopData = $inventoryItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($item->id); ?>">
                        <?php echo e($item->item_name); ?> | Stock: <?php echo e($item->stock_qty); ?> <?php echo e($item->unit); ?><?php echo e($item->brand ? ' | ' . $item->brand : ''); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            `;

            $('#add-create-item-row').on('click', function() {
                let wrapper = $('#create-items-wrapper');
                let index = wrapper.find('.item-row').length;

                wrapper.append(`
                    <div class="row g-2 item-row mb-2">
                        <div class="col-md-6">
                            <select class="form-select" name="items[${index}][it_inventory_item_id]">
                                ${inventoryOptions}
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" min="1" class="form-control" name="items[${index}][qty_used]" placeholder="Qty">
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" name="items[${index}][remarks]" placeholder="Remarks">
                        </div>
                        <div class="col-md-1 d-grid">
                            <button type="button" class="btn btn-outline-danger remove-item-row">&times;</button>
                        </div>
                    </div>
                `);
            });

            $(document).on('click', '.add-edit-item-row', function() {
                let targetId = $(this).data('target');
                let wrapper = $('#' + targetId);
                let index = wrapper.find('.item-row').length;

                wrapper.append(`
        <div class="row g-2 item-row item-row-modern mb-2">
            <div class="col-md-6">
                <label class="form-label small text-muted mb-1">Inventory Item</label>
                <select class="form-select form-select-modern" name="items[${index}][it_inventory_item_id]">
                    ${inventoryOptions}
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Qty</label>
                <input type="number" min="1" class="form-control form-control-modern"
                    name="items[${index}][qty_used]" placeholder="Qty">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Remarks</label>
                <input type="text" class="form-control form-control-modern"
                    name="items[${index}][remarks]" placeholder="Remarks">
            </div>
            <div class="col-md-1 d-grid">
                <label class="form-label small text-transparent mb-1">.</label>
                <button type="button" class="btn btn-outline-danger remove-item-row rounded-pill">
                    <span class="fas fa-times"></span>
                </button>
            </div>
        </div>
    `);
            });

            $(document).on('click', '.remove-item-row', function() {
                let row = $(this).closest('.item-row');
                let wrapper = row.parent();

                if (wrapper.find('.item-row').length > 1) {
                    row.remove();
                } else {
                    row.find('input').val('');
                    row.find('select').val('');
                }
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        .page-hero-card {
            border-radius: 18px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
        }

        .hero-icon {
            width: 58px;
            height: 58px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(13, 110, 253, 0.12);
            color: #0d6efd;
            font-size: 1.3rem;
        }

        .monitor-card,
        .jo-card,
        .filter-card {
            border-radius: 18px;
            overflow: hidden;
        }

        .monitor-tile,
        .insight-card {
            border: 1px solid #e9ecef;
            border-radius: 16px;
            background: #fff;
            padding: 1rem;
            transition: .2s ease;
            height: 100%;
        }

        .monitor-tile:hover,
        .insight-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }

        .tile-label {
            font-size: .8rem;
            color: #6c757d;
            margin-bottom: .35rem;
            font-weight: 600;
        }

        .tile-value {
            font-size: 1.9rem;
            font-weight: 700;
            line-height: 1.1;
            color: #212529;
        }

        .tile-subtext {
            font-size: .8rem;
            color: #6c757d;
            margin-top: .35rem;
        }

        .status-open {
            border-left: 4px solid #f0ad4e;
        }

        .status-progress {
            border-left: 4px solid #0dcaf0;
        }

        .status-fixed {
            border-left: 4px solid #198754;
        }

        .status-closed {
            border-left: 4px solid #6c757d;
        }

        .insight-title {
            font-size: .85rem;
            font-weight: 700;
            color: #6c757d;
            margin-bottom: .75rem;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .insight-main {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: .75rem;
            gap: .75rem;
        }

        .insight-main strong {
            font-size: 1.1rem;
            color: #0d6efd;
        }

        .insight-list .insight-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: .45rem 0;
            border-top: 1px dashed rgba(0, 0, 0, 0.08);
            font-size: .88rem;
        }

        .search-box {
            width: 320px;
        }

        .jo-table-wrap {
            max-height: 620px;
        }

        .jo-table thead th {
            position: sticky;
            top: 0;
            z-index: 3;
            background: #f8f9fa;
            font-size: .8rem;
            font-weight: 700;
            color: #495057;
            padding-top: .9rem;
            padding-bottom: .9rem;
            white-space: nowrap;
        }

        .jo-table tbody td {
            padding-top: 1rem;
            padding-bottom: 1rem;
            vertical-align: middle;
            border-color: #f1f3f5;
        }

        .jo-table tbody tr:hover {
            background: #fafcff;
        }

        .table-chip {
            display: inline-flex;
            align-items: center;
            padding: .35rem .6rem;
            border-radius: 999px;
            background: rgba(13, 110, 253, 0.08);
            color: #0d6efd;
            font-size: .78rem;
            font-weight: 600;
        }

        .empty-state {
            padding: 3rem 1rem;
        }

        .empty-state-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            color: #0d6efd;
            font-size: 1.2rem;
        }

        .soft-divider {
            border-top: 1px dashed rgba(0, 0, 0, 0.1);
            margin: .5rem 0;
        }

        .form-hint {
            font-size: .78rem;
            color: #6c757d;
        }

        .modal .modal-content {
            border-radius: 18px;
        }

        .pagination {
            font-size: 14px !important;
            margin: 0 !important;
        }

        .pagination .page-link {
            padding: 4px 10px !important;
            font-size: 14px !important;
            border-radius: 8px !important;
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
            background: #e9ecef !important;
            border-color: #c4c9cf !important;
        }

        .pagination .page-item.disabled .page-link {
            opacity: .5 !important;
        }

        .pagination .page-item {
            margin: 0 2px !important;
        }

        @media (max-width: 767.98px) {
            .search-box {
                width: 100%;
            }

            .tile-value {
                font-size: 1.5rem;
            }
        }
    </style>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/it_department/cctv_concern.blade.php ENDPATH**/ ?>