<div class="table-responsive scrollbar">
    <table class="table table-hover table-striped fs-10 mb-0">
        <thead class="bg-200 text-900">
            <tr>
                <th>Parts Out No.</th>
                <th>Vehicle</th>
                <th>Mechanic</th>
                <th>Date</th>
                <th>JO No.</th>
                <th>Status</th>
                <th>Encoded By</th>
                <th class="text-center" style="width: 120px;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $partsOuts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="fw-semibold text-primary"><?php echo e($row->parts_out_number); ?></td>
                    <td>
                        <?php if($row->vehicle): ?>
                            <div class="fw-semibold">
                                <?php echo e($row->vehicle->plate_number ?? 'N/A'); ?>

                            </div>
                            <small class="text-muted">
                                <?php echo e($row->vehicle->body_number ?? 'No Body No.'); ?>

                                <?php if(!empty($row->vehicle->name)): ?>
                                    | <?php echo e($row->vehicle->name); ?>

                                <?php endif; ?>
                            </small>
                        <?php else: ?>
                            <span class="text-muted">No vehicle selected</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($row->mechanic_name); ?></td>
                    <td><?php echo e(\Carbon\Carbon::parse($row->issued_date)->format('M d, Y')); ?></td>
                    <td><?php echo e($row->job_order_no ?? '—'); ?></td>
                    <td>
                        <?php if($row->status === 'posted'): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                Posted
                            </span>
                        <?php elseif($row->status === 'cancelled'): ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                Cancelled
                            </span>
                        <?php elseif($row->status === 'rolled_back'): ?>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                Rolled Back
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                <?php echo e(ucfirst(str_replace('_', ' ', $row->status))); ?>

                            </span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($row->creator->full_name ?? '—'); ?></td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            <a href="<?php echo e(route('parts-out.show', $row->id)); ?>" class="btn btn-falcon-info btn-sm"
                                data-bs-toggle="tooltip" title="View Details">
                                <span class="fas fa-eye"></span>
                            </a>

                            <?php if (\Illuminate\Support\Facades\Blade::check('role', 'Developer', 'Maintenance Engineer')): ?>
                                <?php if($row->status === 'posted'): ?>
                                    <form action="<?php echo e(route('parts-out.rollback', $row->id)); ?>" method="POST"
                                        onsubmit="return confirm('Are you sure you want to rollback this Parts Out? This will return all used quantities back to stock.');"
                                        class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="btn btn-falcon-warning btn-sm"
                                            data-bs-toggle="tooltip" title="Rollback">
                                            <span class="fas fa-undo"></span>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        <span class="fas fa-inbox fa-2x mb-2 d-block text-300"></span>
                        No parts out records found.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if($partsOuts->hasPages()): ?>
    <div class="d-flex justify-content-end mt-3">
        <?php echo e($partsOuts->links('pagination::bootstrap-5')); ?>

    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/maintenance/parts_out/table.blade.php ENDPATH**/ ?>