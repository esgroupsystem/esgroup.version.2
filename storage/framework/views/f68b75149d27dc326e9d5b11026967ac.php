<div class="table-responsive scrollbar">
    <table class="table table-hover table-striped fs-10 mb-0">
        <thead class="bg-200 text-900">
            <tr>
                <th>Item Name</th>
                <th>Category</th>
                <th>Supplier / Shop</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>

        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="align-middle">
                    <td>
                        <div class="fw-semibold text-110">
                            <?php echo e($item->product_name); ?>

                            <?php if($item->unit): ?>
                                (<?php echo e($item->unit); ?>)
                            <?php endif; ?>
                        </div>
                        <div class="text-500 fs-12"><?php echo e($item->details ?? 'N/A'); ?></div>
                    </td>

                    <td>
                        <div class="fw-semibold text-110"><?php echo e($item->category->name ?? 'N/A'); ?></div>
                        <?php if($item->part_number): ?>
                            <div class="text-600 fs-9">#<?php echo e($item->part_number); ?></div>
                        <?php endif; ?>
                    </td>

                    <td>
                        <div class="fw-semibold text-110">
                            <?php echo e($item->supplier_name ?: 'N/A'); ?>

                        </div>
                    </td>

                    <td class="text-center">
                        <div class="dropdown font-sans-serif position-static">
                            <button type="button" class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                data-bs-toggle="dropdown">
                                <span class="fas fa-ellipsis-h fs-10"></span>
                            </button>

                            <div class="dropdown-menu dropdown-menu-end border py-0 shadow-sm">
                                <div class="py-2">
                                    <button type="button" class="dropdown-item"
                                        data-item='<?php echo json_encode($item, 15, 512) ?>'
                                        onclick="openEditItem(JSON.parse(this.dataset.item))">
                                        <i class="fas fa-edit me-2"></i> Edit
                                    </button>

                                    <form action="<?php echo e(route('items.destroy', $item->id)); ?>" method="POST"
                                        class="confirm-delete m-0"
                                        onsubmit="return confirm('Are you sure you want to delete this item?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-trash me-2"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="4" class="text-center text-muted py-3">No items found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="my-3 d-flex justify-content-end px-3">
    <?php echo e($items->appends(request()->except('page'))->links('pagination.custom')); ?>

</div>
<?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/maintenance/items/items_table.blade.php ENDPATH**/ ?>