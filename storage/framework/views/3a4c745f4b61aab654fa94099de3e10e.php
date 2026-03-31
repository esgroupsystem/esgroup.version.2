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
                        <option value="<?php echo e($st); ?>" <?php if(request('status') === $st): echo 'selected'; endif; ?>><?php echo e($st); ?></option>
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
<?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/it_department/concern/partials/mobile-filter.blade.php ENDPATH**/ ?>