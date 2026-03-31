

<?php $__env->startSection('title', 'Philippine Holiday Calendar'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid" data-layout="container">
        <div class="content">

            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            <?php endif; ?>

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-body-tertiary border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">Philippine Holiday Calendar</h4>
                        <small class="text-muted">Connected to Payroll and Attendance Summary</small>
                    </div>

                    <a href="<?php echo e(route('holidays.create')); ?>" class="btn btn-primary btn-sm">
                        Add Holiday
                    </a>
                </div>

                <div class="card-body">
                    <form method="GET" class="row g-2 mb-4">
                        <div class="col-md-2">
                            <input type="number" name="year" class="form-control" value="<?php echo e($year); ?>"
                                placeholder="Year">
                        </div>
                        <div class="col-md-2">
                            <select name="month" class="form-select">
                                <option value="">All Months</option>
                                <?php for($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?php echo e($m); ?>" <?php echo e((int) $month === $m ? 'selected' : ''); ?>>
                                        <?php echo e(\Carbon\Carbon::create()->month($m)->format('F')); ?>

                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" value="<?php echo e($search); ?>"
                                placeholder="Search holiday...">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-outline-primary w-100">Filter</button>
                        </div>
                        <div class="col-md-2">
                            <a href="<?php echo e(route('holidays.index')); ?>" class="btn btn-outline-secondary w-100">Reset</a>
                        </div>
                    </form>

                    <div class="row g-3 mb-4">
                        <?php
                            $start = \Carbon\Carbon::create(
                                $year,
                                $month ?: now()->month,
                                1,
                                0,
                                0,
                                0,
                                'Asia/Manila',
                            )->startOfMonth();
                            $end = $start->copy()->endOfMonth();
                            $daysInMonth = $start->daysInMonth;
                            $firstDayOfWeek = $start->dayOfWeek; // 0 sunday
                        ?>

                        <div class="col-12">
                            <div class="border rounded-3 p-3 bg-light-subtle">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0"><?php echo e($start->format('F Y')); ?></h5>
                                    <div class="small text-muted">
                                        <span class="badge bg-danger-subtle text-danger me-1">Regular</span>
                                        <span class="badge bg-warning-subtle text-warning">Special</span>
                                    </div>
                                </div>

                                <div class="row text-center fw-bold small mb-2">
                                    <div class="col">Sun</div>
                                    <div class="col">Mon</div>
                                    <div class="col">Tue</div>
                                    <div class="col">Wed</div>
                                    <div class="col">Thu</div>
                                    <div class="col">Fri</div>
                                    <div class="col">Sat</div>
                                </div>

                                <?php
                                    $day = 1;
                                    $printed = 0;
                                ?>

                                <?php for($week = 0; $week < 6; $week++): ?>
                                    <div class="row g-2 mb-2">
                                        <?php for($dow = 0; $dow < 7; $dow++): ?>
                                            <?php
                                                $currentDate = null;
                                                $holidayForDay = collect();

                                                if (
                                                    ($week === 0 && $dow >= $firstDayOfWeek) ||
                                                    ($week > 0 && $day <= $daysInMonth)
                                                ) {
                                                    $currentDate = $start->copy()->day($day)->format('Y-m-d');
                                                    $holidayForDay = $calendar->get($currentDate, collect());
                                                    $day++;
                                                    $printed++;
                                                }
                                            ?>

                                            <div class="col">
                                                <div class="border rounded-3 bg-white p-2" style="min-height: 120px;">
                                                    <?php if($currentDate): ?>
                                                        <div class="fw-semibold mb-2">
                                                            <?php echo e(\Carbon\Carbon::parse($currentDate)->format('d')); ?>

                                                        </div>

                                                        <?php $__empty_1 = true; $__currentLoopData = $holidayForDay; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $holiday): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                            <div class="mb-1">
                                                                <span
                                                                    class="<?php echo e($holiday->type_badge_class); ?> d-inline-block mb-1">
                                                                    <?php echo e(ucfirst($holiday->holiday_type)); ?>

                                                                </span>
                                                                <div class="small fw-semibold">
                                                                    <?php echo e($holiday->name); ?>

                                                                </div>
                                                                <?php if($holiday->is_moved): ?>
                                                                    <div class="small text-muted">
                                                                        Actual: <?php echo e($holiday->actual_date->format('M d')); ?>

                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                            <div class="small text-muted">—</div>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endfor; ?>
                                    </div>

                                    <?php if($day > $daysInMonth): ?>
                                        <?php break; ?>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Holiday</th>
                                    <th>Type</th>
                                    <th>Actual Date</th>
                                    <th>Observed Date</th>
                                    <th>Not Worked</th>
                                    <th>Worked</th>
                                    <th>Source</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $holidays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $holiday): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?php echo e($holiday->name); ?></div>
                                            <?php if($holiday->is_moved): ?>
                                                <small class="text-muted">Moved holiday observance</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="<?php echo e($holiday->type_badge_class); ?>">
                                                <?php echo e(ucfirst($holiday->holiday_type)); ?>

                                            </span>
                                        </td>
                                        <td><?php echo e($holiday->actual_date->format('M d, Y')); ?></td>
                                        <td><?php echo e($holiday->observed_date->format('M d, Y')); ?></td>
                                        <td><?php echo e(number_format((float) $holiday->not_worked_multiplier, 2)); ?>x</td>
                                        <td><?php echo e(number_format((float) $holiday->worked_multiplier, 2)); ?>x</td>
                                        <td><?php echo e($holiday->source_proclamation ?: '—'); ?></td>
                                        <td class="text-end">
                                            <a href="<?php echo e(route('holidays.edit', $holiday)); ?>"
                                                class="btn btn-outline-primary btn-sm">
                                                Edit
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">No holidays found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <?php echo e($holidays->links()); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/payroll/holidays/index.blade.php ENDPATH**/ ?>