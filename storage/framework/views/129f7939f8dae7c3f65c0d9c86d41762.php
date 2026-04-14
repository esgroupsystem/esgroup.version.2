<div class="table-responsive scrollbar rounded-3 border" style="max-height: 500px;">
    <table class="table table-sm table-hover align-middle fs-10 mb-0">
        <thead class="bg-200 text-900">
            <tr>
                <th>Category</th>
                <th>Product</th>
                <th class="text-center">Unit</th>
                <th class="text-center">Status</th>
                <th class="text-center">Stock</th>
                <th>Indicator</th>
            </tr>
        </thead>

        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $qty = (int) ($p->stock_qty ?? 0);
                    $percent = $qty <= 0 ? 0 : min(100, ($qty / 10) * 100);
                ?>

                <tr>
                    <td><?php echo e($p->category->name ?? '—'); ?></td>

                    <td>
                        <div class="fw-semibold"><?php echo e($p->product_name); ?></div>
                        <div class="text-500 fs-11"><?php echo e($p->details ?: 'N/A'); ?></div>
                    </td>

                    <td class="text-center">
                        <span class="badge badge-subtle-secondary px-2">
                            <?php echo e($p->unit ?: '—'); ?>

                        </span>
                    </td>

                    <td class="text-center">
                        <?php if($qty <= 0): ?>
                            <span class="badge badge-subtle-danger px-3">Out of Stock</span>
                        <?php elseif($qty <= 5): ?>
                            <span class="badge badge-subtle-warning px-3">Low</span>
                        <?php else: ?>
                            <span class="badge badge-subtle-success px-3">Available</span>
                        <?php endif; ?>
                    </td>

                    <td class="text-center fw-bold"><?php echo e($qty); ?></td>

                    <td>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar
                                <?php if($qty <= 0): ?> bg-danger
                                <?php elseif($qty <= 5): ?> bg-warning
                                <?php else: ?> bg-success <?php endif; ?>"
                                style="width: <?php echo e($percent); ?>%">
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-3">No stock items found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="my-3 d-flex justify-content-end px-3">
    <?php echo e($products->appends(request()->except('page'))->links('pagination.custom')); ?>

</div>
<?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/maintenance/items/stock_table.blade.php ENDPATH**/ ?>