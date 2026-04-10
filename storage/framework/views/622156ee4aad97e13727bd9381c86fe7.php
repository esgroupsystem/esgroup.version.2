<div class="card-body p-0">
    <div class="table-responsive scrollbar">
        <table class="table table-hover table-striped align-middle mb-0 fs-10">
            <thead class="bg-200 text-900">
                <tr>
                    <th class="ps-3">Receiving No.</th>
                    <th>Garage</th>
                    <th>Delivered By</th>
                    <th>Delivery Date</th>
                    <th class="text-center">Items</th>
                    <th>Remarks</th>
                    <th>Received By</th>
                    <th>Date Created</th>
                    <th class="text-center pe-3">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $receivings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $receiving): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="ps-3">
                            <div class="fw-semibold text-primary"><?php echo e($receiving->receiving_number); ?></div>
                            <div class="text-500 fs-11">
                                Ref. record
                            </div>
                        </td>

                        <td><?php echo e($receiving->location->name ?? 'N/A'); ?></td>

                        <td>
                            <div class="fw-semibold"><?php echo e($receiving->delivered_by ?: '—'); ?></div>
                        </td>

                        <td>
                            <div class="fw-semibold">
                                <?php echo e($receiving->delivery_date ? \Carbon\Carbon::parse($receiving->delivery_date)->format('M d, Y') : '—'); ?>

                            </div>
                        </td>

                        <td class="text-center">
                            <span class="badge badge-subtle-secondary px-3 py-2">
                                <?php echo e($receiving->items_count ?? ($receiving->items->count() ?? 0)); ?> item(s)
                            </span>
                        </td>

                        <td style="min-width: 220px;">
                            <div class="text-700">
                                <?php echo e($receiving->remarks ?: 'No remarks provided.'); ?>

                            </div>
                        </td>

                        <td>
                            <div class="fw-semibold"><?php echo e($receiving->receiver->full_name ?? 'System'); ?></div>
                        </td>

                        <td>
                            <div class="fw-semibold">
                                <?php echo e(optional($receiving->created_at)->format('M d, Y')); ?>

                            </div>
                            <div class="text-500 fs-11">
                                <?php echo e(optional($receiving->created_at)->format('h:i A')); ?>

                            </div>
                        </td>

                        <td class="text-center pe-3">
                            <a href="<?php echo e(route('receivings.show', $receiving->id)); ?>"
                                class="btn btn-falcon-default btn-sm">
                                <span class="fas fa-eye me-1"></span> View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center justify-content-center text-muted">
                                <span class="fas fa-truck-loading fs-3 mb-3 text-300"></span>
                                <h6 class="mb-1">No receiving records found</h6>
                                <p class="mb-0 fs-10">
                                    Try changing your search keyword or create a new receiving record.
                                </p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if($receivings->hasPages()): ?>
    <div class="card-footer bg-body-tertiary py-2">
        <div class="d-flex justify-content-end">
            <?php echo e($receivings->links('pagination.custom')); ?>

        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/maintenance/receive/table.blade.php ENDPATH**/ ?>