
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
            ?>

            <?php echo $__env->make('it_department.concern.partials.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('it_department.concern.partials.monitoring', [
                'statusCounts' => $statusCounts,
                'issueCounts' => $issueCounts,
                'topIssue' => $topIssue,
                'topIssueCount' => $topIssueCount,
                'partCounts' => $partCounts,
                'topPart' => $topPart,
                'topPartCount' => $topPartCount,
                'assigneeCounts' => $assigneeCounts,
                'topAssignee' => $topAssignee,
                'topAssigneeCount' => $topAssigneeCount,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <div class="row g-3">
                <div class="col-xxl-9 col-xl-8">
                    <?php echo $__env->make('it_department.concern.partials.table', ['jobOrders' => $jobOrders], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>

                <div class="col-xxl-3 col-xl-4 d-none d-xl-block">
                    <?php echo $__env->make('it_department.concern.partials.desktop-filter', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>

            <?php echo $__env->make('it_department.concern.partials.mobile-filter', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('it_department.concern.partials.create-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('it_department.concern.partials.edit-modals', ['jobOrders' => $jobOrders], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('it_department.concern.partials.scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->make('it_department.concern.partials.styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/it_department/concern/index.blade.php ENDPATH**/ ?>