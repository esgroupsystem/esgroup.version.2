<div class="card-body p-0">
    <div class="table-responsive scrollbar">
        <table class="table table-hover table-striped align-middle mb-0 fs-10">
            <thead class="bg-200 text-900">
                <tr>
                    <th class="ps-3">Transfer No.</th>
                    <th>Route</th>
                    <th>Requested / Received By</th>
                    <th class="text-center">Items</th>
                    <th>Remarks</th>
                    <th>Date Created</th>
                    <th class="text-center pe-3">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $transfers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transfer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="ps-3">
                            <div class="fw-semibold text-primary"><?php echo e($transfer->transfer_number); ?></div>
                            <div class="text-500 fs-11">
                                Created by:
                                <?php echo e($transfer->creator->name ?? 'System'); ?>

                            </div>
                        </td>

                        <td>
                            <div class="d-flex flex-column gap-1">
                                <div>
                                    <span class="badge badge-subtle-primary px-2 py-1">
                                        <?php echo e($transfer->fromLocation->name ?? 'N/A'); ?>

                                    </span>
                                </div>
                                <div class="text-500 fs-11">
                                    <span class="fas fa-arrow-down me-1"></span>
                                    to
                                </div>
                                <div>
                                    <span class="badge badge-subtle-info px-2 py-1">
                                        <?php echo e($transfer->toLocation->name ?? 'N/A'); ?>

                                    </span>
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="mb-1">
                                <span class="text-500 fs-11">Requested By</span>
                                <div class="fw-semibold"><?php echo e($transfer->requested_by ?: '—'); ?></div>
                            </div>
                            <div>
                                <span class="text-500 fs-11">Received By</span>
                                <div class="fw-semibold"><?php echo e($transfer->received_by ?: '—'); ?></div>
                            </div>
                        </td>

                        <td class="text-center">
                            <span class="badge badge-subtle-secondary px-3 py-2">
                                <?php echo e($transfer->items_count ?? $transfer->items->count()); ?> item(s)
                            </span>
                        </td>

                        <td style="min-width: 220px;">
                            <div class="text-700">
                                <?php echo e($transfer->remarks ?: 'No remarks provided.'); ?>

                            </div>
                        </td>

                        <td>
                            <div class="fw-semibold">
                                <?php echo e(optional($transfer->created_at)->format('M d, Y')); ?>

                            </div>
                            <div class="text-500 fs-11">
                                <?php echo e(optional($transfer->created_at)->format('h:i A')); ?>

                            </div>
                        </td>

                        <td class="text-center pe-3">
                            <a href="<?php echo e(route('stock-transfers.show', $transfer->id)); ?>"
                                class="btn btn-falcon-default btn-sm">
                                <span class="fas fa-eye me-1"></span> View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center justify-content-center text-muted">
                                <span class="fas fa-exchange-alt fs-3 mb-3 text-300"></span>
                                <h6 class="mb-1">No stock transfers found</h6>
                                <p class="mb-0 fs-10">
                                    Try changing your search keyword or create a new transfer record.
                                </p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if($transfers->hasPages()): ?>
    <div class="card-footer bg-body-tertiary py-2">
        <div class="d-flex justify-content-end">
            <?php echo e($transfers->links('pagination.custom')); ?>

        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/maintenance/stock_transfers/table.blade.php ENDPATH**/ ?>