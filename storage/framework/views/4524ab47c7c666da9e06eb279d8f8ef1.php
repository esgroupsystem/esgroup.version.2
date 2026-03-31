
<?php $__env->startSection('title', 'Cutoff Plotting Schedule'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid" data-layout="container">
        <script>
            var isFluid = JSON.parse(localStorage.getItem('isFluid'));
            if (isFluid) {
                var container = document.querySelector('[data-layout]');
                container.classList.remove('container-fluid');
                container.classList.add('container-fluid');
            }
        </script>

        <div class="content">

            <?php if(session('success')): ?>
                <div class="alert alert-success border-0 shadow-sm">
                    <span class="fas fa-check-circle me-2"></span><?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-light border-bottom">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg">
                            <h5 class="mb-1">
                                <span class="fas fa-calendar-alt text-primary me-2"></span>
                                Cutoff Plotting Schedule
                            </h5>
                            <p class="text-muted fs-10 mb-0">
                                Create and manage employee plotting based on payroll cutoff periods.
                            </p>
                        </div>

                        <div class="col-lg-auto">
                            <form method="GET" action="<?php echo e(route('payroll-plotting.index')); ?>"
                                class="row g-2 align-items-center" id="filterForm">
                                <div class="col-auto position-relative">
                                    <input type="text" name="search" id="employeeSearchInput"
                                        class="form-control form-control-sm" style="width: 280px;"
                                        placeholder="Search employee name / employee no / bio id..."
                                        value="<?php echo e(request('search')); ?>" autocomplete="off">

                                    <div id="employeeSearchSuggestions" class="list-group position-absolute w-100 shadow-sm"
                                        style="top: 100%; left: 0; z-index: 1050; display: none; max-height: 260px; overflow-y: auto;">
                                    </div>
                                </div>

                                <div class="col-auto">
                                    <select name="cutoff_month" class="form-select form-select-sm">
                                        <?php for($m = 1; $m <= 12; $m++): ?>
                                            <option value="<?php echo e($m); ?>"
                                                <?php echo e((int) $cutoffMonth === $m ? 'selected' : ''); ?>>
                                                <?php echo e(\Carbon\Carbon::create(null, $m, 1)->format('F')); ?>

                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <select name="cutoff_year" class="form-select form-select-sm">
                                        <?php for($y = now()->year - 2; $y <= now()->year + 3; $y++): ?>
                                            <option value="<?php echo e($y); ?>"
                                                <?php echo e((int) $cutoffYear === $y ? 'selected' : ''); ?>>
                                                <?php echo e($y); ?>

                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <select name="cutoff_type" class="form-select form-select-sm">
                                        <option value="11_25" <?php echo e($cutoffType === '11_25' ? 'selected' : ''); ?>>
                                            11 - 25
                                        </option>
                                        <option value="26_10" <?php echo e($cutoffType === '26_10' ? 'selected' : ''); ?>>
                                            26 - 10
                                        </option>
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <button class="btn btn-primary btn-sm">
                                        <span class="fas fa-filter me-1"></span> Load
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body bg-body-tertiary border-bottom">
                    <div class="row g-3">
                        <div class="col-lg-8">
                            <form method="POST" action="<?php echo e(route('payroll-plotting.quick-fill')); ?>"
                                class="row g-2 align-items-end">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="cutoff_month" value="<?php echo e($cutoffMonth); ?>">
                                <input type="hidden" name="cutoff_year" value="<?php echo e($cutoffYear); ?>">
                                <input type="hidden" name="cutoff_type" value="<?php echo e($cutoffType); ?>">
                                <input type="hidden" name="search" value="<?php echo e(request('search')); ?>">
                                <input type="hidden" name="page" value="<?php echo e($employees->currentPage()); ?>">

                                <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $employeeKey = $employee->biometric_employee_id
                                            ? 'bio:' . $employee->biometric_employee_id
                                            : 'empno:' . $employee->employee_no;
                                    ?>
                                    <input type="hidden" name="employee_keys[]" value="<?php echo e($employeeKey); ?>">
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                <div class="col-md-3">
                                    <label class="form-label fs-10 mb-1">Shift Name</label>
                                    <input type="text" name="default_shift_name" class="form-control form-control-sm"
                                        value="Regular Shift">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fs-10 mb-1">Time In</label>
                                    <input type="time" name="default_time_in" class="form-control form-control-sm"
                                        value="08:00">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fs-10 mb-1">Time Out</label>
                                    <input type="time" name="default_time_out" class="form-control form-control-sm"
                                        value="17:00">
                                </div>

                                <div class="col-md-1">
                                    <label class="form-label fs-10 mb-1">Grace</label>
                                    <input type="number" name="default_grace_minutes" class="form-control form-control-sm"
                                        value="15" min="0">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fs-10 mb-1">Rest Day Rule</label>
                                    <select name="rest_day_mode" class="form-select form-select-sm">
                                        <option value="sunday">Sunday Only</option>
                                        <option value="sat_sun">Saturday & Sunday</option>
                                        <option value="none">No Rest Day</option>
                                    </select>
                                </div>

                                <div class="col-md-auto">
                                    <button type="submit" class="btn btn-warning btn-sm"
                                        <?php echo e(blank(request('search')) ? 'disabled' : ''); ?>>
                                        <span class="fas fa-magic me-1"></span> Generate Default Cutoff
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="col-lg-4">
                            <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                                <span class="badge bg-success-subtle text-success border">Scheduled</span>
                                <span class="badge bg-warning-subtle text-warning border">Rest Day</span>
                                <span class="badge bg-info-subtle text-info border">Leave</span>
                                <span class="badge bg-danger-subtle text-danger border">Holiday</span>
                            </div>
                            <div class="text-muted fs-10 mt-2 text-lg-end">
                                Current Coverage: <strong><?php echo e($cutoffLabel); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="plottingTableWrapper">
                    <?php echo $__env->make('payroll.plotting.table', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function bindPlottingEvents(scope = document) {
                const statusFields = scope.querySelectorAll('.plot-status');

                function applyCellColor(select) {
                    const td = select.closest('.plot-cell');
                    if (!td) return;

                    td.classList.remove('plot-scheduled', 'plot-rest-day', 'plot-leave', 'plot-holiday');

                    if (select.value === 'scheduled') td.classList.add('plot-scheduled');
                    if (select.value === 'rest_day') td.classList.add('plot-rest-day');
                    if (select.value === 'leave') td.classList.add('plot-leave');
                    if (select.value === 'holiday') td.classList.add('plot-holiday');
                }

                statusFields.forEach(function(select) {
                    applyCellColor(select);

                    select.addEventListener('change', function() {
                        applyCellColor(this);

                        const cell = this.closest('.plot-cell');
                        const timeInputs = cell.querySelectorAll('input[type="time"]');

                        if (this.value !== 'scheduled') {
                            timeInputs.forEach(function(input) {
                                input.value = '';
                            });
                        }
                    });
                });
            }

            bindPlottingEvents(document);

            document.addEventListener('click', function(e) {
                const link = e.target.closest('#plottingTableWrapper .pagination a');
                if (!link) return;

                e.preventDefault();

                fetch(link.href, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('plottingTableWrapper').innerHTML = html;
                        bindPlottingEvents(document.getElementById('plottingTableWrapper'));
                    });
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function bindPlottingEvents(scope = document) {
                const statusFields = scope.querySelectorAll('.plot-status');

                function applyCellColor(select) {
                    const td = select.closest('.plot-cell');
                    if (!td) return;

                    td.classList.remove('plot-scheduled', 'plot-rest-day', 'plot-leave', 'plot-holiday');

                    if (select.value === 'scheduled') td.classList.add('plot-scheduled');
                    if (select.value === 'rest_day') td.classList.add('plot-rest-day');
                    if (select.value === 'leave') td.classList.add('plot-leave');
                    if (select.value === 'holiday') td.classList.add('plot-holiday');
                }

                statusFields.forEach(function(select) {
                    applyCellColor(select);

                    select.addEventListener('change', function() {
                        applyCellColor(this);

                        const cell = this.closest('.plot-cell');
                        const timeInputs = cell.querySelectorAll('input[type="time"]');

                        if (this.value !== 'scheduled') {
                            timeInputs.forEach(function(input) {
                                input.value = '';
                            });
                        }
                    });
                });
            }

            bindPlottingEvents(document);

            document.addEventListener('click', function(e) {
                const link = e.target.closest('#plottingTableWrapper .pagination a');
                if (!link) return;

                e.preventDefault();

                fetch(link.href, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('plottingTableWrapper').innerHTML = html;
                        bindPlottingEvents(document.getElementById('plottingTableWrapper'));
                    });
            });

            const searchInput = document.getElementById('employeeSearchInput');
            const suggestionBox = document.getElementById('employeeSearchSuggestions');

            let debounceTimer = null;

            if (searchInput && suggestionBox) {
                searchInput.addEventListener('input', function() {
                    const keyword = this.value.trim();

                    clearTimeout(debounceTimer);

                    if (keyword.length < 2) {
                        suggestionBox.style.display = 'none';
                        suggestionBox.innerHTML = '';
                        return;
                    }

                    debounceTimer = setTimeout(() => {
                        fetch(`<?php echo e(route('payroll-plotting.search-suggestions')); ?>?q=${encodeURIComponent(keyword)}`, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                suggestionBox.innerHTML = '';

                                if (!data.length) {
                                    suggestionBox.style.display = 'none';
                                    return;
                                }

                                data.forEach(item => {
                                    const button = document.createElement('button');
                                    button.type = 'button';
                                    button.className =
                                        'list-group-item list-group-item-action';
                                    button.textContent = item.label;

                                    button.addEventListener('click', function() {
                                        searchInput.value = item.value;
                                        suggestionBox.style.display = 'none';
                                    });

                                    suggestionBox.appendChild(button);
                                });

                                suggestionBox.style.display = 'block';
                            })
                            .catch(() => {
                                suggestionBox.style.display = 'none';
                            });
                    }, 250);
                });

                document.addEventListener('click', function(e) {
                    if (!searchInput.contains(e.target) && !suggestionBox.contains(e.target)) {
                        suggestionBox.style.display = 'none';
                    }
                });
            }
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/payroll/plotting/index.blade.php ENDPATH**/ ?>