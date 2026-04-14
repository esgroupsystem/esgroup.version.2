
<?php $__env->startSection('title', 'Parts Out Details'); ?>

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
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <span class="fas fa-tools text-primary me-2"></span>
                            Parts Out Details
                        </h5>
                        <p class="text-muted fs-10 mb-0 mt-1">
                            View issued / installed parts transaction details
                        </p>
                    </div>

                    <div class="d-flex gap-2">
                        <?php if($partsOut->status === 'posted'): ?>
                            <form action="<?php echo e(route('parts-out.rollback', $partsOut->id)); ?>" method="POST"
                                onsubmit="return confirm('Are you sure you want to rollback this Parts Out? This will return all used quantities back to stock.');">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-falcon-warning btn-sm">
                                    <span class="fas fa-undo me-1"></span> Rollback
                                </button>
                            </form>
                        <?php endif; ?>

                        <a href="<?php echo e(route('parts-out.index')); ?>" class="btn btn-falcon-default btn-sm">
                            <span class="fas fa-arrow-left me-1"></span> Back
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="border rounded p-3 bg-light">
                                <small class="text-muted d-block">Parts Out No.</small>
                                <div class="fw-bold text-primary"><?php echo e($partsOut->parts_out_number); ?></div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded p-3 bg-light">
                                <small class="text-muted d-block">Date Issued</small>
                                <div class="fw-semibold">
                                    <?php echo e(\Carbon\Carbon::parse($partsOut->issued_date)->format('M d, Y')); ?>

                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded p-3 bg-light">
                                <small class="text-muted d-block">Mechanic</small>
                                <div class="fw-semibold"><?php echo e($partsOut->mechanic_name); ?></div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded p-3 bg-light">
                                <small class="text-muted d-block">Status</small>
                                <div>
                                    <?php if($partsOut->status === 'posted'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            Posted
                                        </span>
                                    <?php elseif($partsOut->status === 'cancelled'): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            Cancelled
                                        </span>
                                    <?php elseif($partsOut->status === 'rolled_back'): ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                            Rolled Back
                                        </span>
                                    <?php else: ?>
                                        <span
                                            class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                            <?php echo e(ucfirst(str_replace('_', ' ', $partsOut->status))); ?>

                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <small class="text-muted d-block mb-1">Vehicle</small>
                                <?php if($partsOut->vehicle): ?>
                                    <div class="fw-semibold">
                                        <?php echo e($partsOut->vehicle->plate_number ?? 'N/A'); ?>

                                    </div>
                                    <small class="text-muted">
                                        Body No.: <?php echo e($partsOut->vehicle->body_number ?? 'N/A'); ?>

                                        <?php if(!empty($partsOut->vehicle->name)): ?>
                                            | <?php echo e($partsOut->vehicle->name); ?>

                                        <?php endif; ?>
                                        <?php if(!empty($partsOut->vehicle->garage)): ?>
                                            | Garage: <?php echo e($partsOut->vehicle->garage); ?>

                                        <?php endif; ?>
                                    </small>
                                <?php else: ?>
                                    <div class="text-muted">No vehicle selected</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <small class="text-muted d-block mb-1">Requested By</small>
                                <div class="fw-semibold"><?php echo e($partsOut->requested_by ?: '—'); ?></div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <small class="text-muted d-block mb-1">Encoded By</small>
                                <div class="fw-semibold"><?php echo e($partsOut->creator->full_name ?? '—'); ?></div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <small class="text-muted d-block mb-1">Job Order No.</small>
                                <div class="fw-semibold"><?php echo e($partsOut->job_order_no ?: '—'); ?></div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <small class="text-muted d-block mb-1">Odometer</small>
                                <div class="fw-semibold"><?php echo e($partsOut->odometer ?: '—'); ?></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <small class="text-muted d-block mb-1">Purpose / Work Details</small>
                                <div><?php echo e($partsOut->purpose ?: '—'); ?></div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="border rounded p-3">
                                <small class="text-muted d-block mb-1">Remarks</small>
                                <div><?php echo e($partsOut->remarks ?: '—'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <span class="fas fa-boxes text-primary me-2"></span>
                        Items Used
                    </h6>
                </div>

                <div class="card-body">
                    <div class="table-responsive scrollbar">
                        <table class="table table-striped table-hover fs-10 mb-0">
                            <thead class="bg-200 text-900">
                                <tr>
                                    <th>Product</th>
                                    <th>Supplier</th>
                                    <th>Unit</th>
                                    <th>Part No.</th>
                                    <th>Qty Used</th>
                                    <th>Stock Before</th>
                                    <th>Stock After</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $partsOut->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td class="fw-semibold"><?php echo e($item->product->product_name ?? 'N/A'); ?></td>
                                        <td><?php echo e($item->product->supplier_name ?? '—'); ?></td>
                                        <td><?php echo e($item->product->unit ?? '—'); ?></td>
                                        <td><?php echo e($item->product->part_number ?? '—'); ?></td>
                                        <td><?php echo e($item->qty_used); ?></td>
                                        <td><?php echo e($item->stock_before); ?></td>
                                        <td><?php echo e($item->stock_after); ?></td>
                                        <td><?php echo e($item->remarks ?? '—'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            No items found.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/maintenance/parts_out/show.blade.php ENDPATH**/ ?>