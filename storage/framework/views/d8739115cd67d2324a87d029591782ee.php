<?php if($paginator->hasPages()): ?>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center w-100">

        
        <div class="small text-muted mb-2 mb-md-0">
            Showing
            <?php echo e($paginator->firstItem()); ?>

            to
            <?php echo e($paginator->lastItem()); ?>

            of
            <?php echo e($paginator->total()); ?>

            results
        </div>

        
        <ul class="pagination pagination-sm mb-0">

            
            <?php if($paginator->onFirstPage()): ?>
                <li class="page-item disabled"><span class="page-link">‹</span></li>
            <?php else: ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev">‹</a>
                </li>
            <?php endif; ?>


            
            <?php
                $start = max($paginator->currentPage() - 2, 1);
                $end = min($paginator->currentPage() + 2, $paginator->lastPage());
            ?>

            
            <?php if($start > 1): ?>
                <li class="page-item"><a class="page-link" href="<?php echo e($paginator->url(1)); ?>">1</a></li>
                <?php if($start > 2): ?>
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                <?php endif; ?>
            <?php endif; ?>

            
            <?php for($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?php echo e($i == $paginator->currentPage() ? 'active' : ''); ?>">
                    <a class="page-link" href="<?php echo e($paginator->url($i)); ?>"><?php echo e($i); ?></a>
                </li>
            <?php endfor; ?>

            
            <?php if($end < $paginator->lastPage()): ?>
                <?php if($end < $paginator->lastPage() - 1): ?>
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                <?php endif; ?>
                <li class="page-item"><a class="page-link"
                        href="<?php echo e($paginator->url($paginator->lastPage())); ?>"><?php echo e($paginator->lastPage()); ?></a></li>
            <?php endif; ?>


            
            <?php if($paginator->hasMorePages()): ?>
                <li class="page-item">
                    <a class="page-link" href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next">›</a>
                </li>
            <?php else: ?>
                <li class="page-item disabled"><span class="page-link">›</span></li>
            <?php endif; ?>

        </ul>

    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/pagination/custom.blade.php ENDPATH**/ ?>