

<?php $__env->startSection('title', 'Manual WFH Cutoff Encoding'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid" data-layout="container">
        <div class="content">

            <?php if(session('success')): ?>
                <div class="alert alert-success border-0 shadow-sm">
                    <i class="fas fa-check-circle me-2"></i><?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger border-0 shadow-sm">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo e($errors->first()); ?>

                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                        <div>
                            <h4 class="mb-1">Manual WFH Cutoff Encoding</h4>
                            <p class="text-muted mb-0">
                                Search one employee from biometrics logs, load the whole cutoff, then encode daily Time In /
                                Time Out fast.
                            </p>
                        </div>

                        <span class="badge bg-primary-subtle text-primary fs-10 px-3 py-2">
                            <?php echo e($cutoffLabel); ?>

                        </span>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-body-tertiary border-bottom">
                    <h5 class="mb-0">Step 1: Select Cutoff and Employee</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?php echo e(route('manual-biometrics.index')); ?>" id="filterForm">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Month</label>
                                <select name="cutoff_month" class="form-select">
                                    <?php for($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?php echo e($m); ?>"
                                            <?php echo e((int) $cutoffMonth === $m ? 'selected' : ''); ?>>
                                            <?php echo e(\Carbon\Carbon::create(null, $m, 1)->format('F')); ?>

                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Year</label>
                                <select name="cutoff_year" class="form-select">
                                    <?php for($y = now('Asia/Manila')->year + 1; $y >= 2024; $y--): ?>
                                        <option value="<?php echo e($y); ?>"
                                            <?php echo e((int) $cutoffYear === $y ? 'selected' : ''); ?>>
                                            <?php echo e($y); ?>

                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Cutoff Type</label>
                                <select name="cutoff_type" class="form-select">
                                    <option value="first" <?php echo e($cutoffType === 'first' ? 'selected' : ''); ?>>1st Cutoff(11-25)
                                    </option>
                                    <option value="second" <?php echo e($cutoffType === 'second' ? 'selected' : ''); ?>>2nd Cutoff(26-10)
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Employee Search</label>
                                <div class="position-relative">
                                    <input type="text" class="form-control" id="employeeSearch"
                                        placeholder="Type employee name / employee no / crosschex id"
                                        value="<?php echo e($selectedEmployee['employee_name'] ?? ''); ?>">
                                    <div id="employeeResults" class="list-group position-absolute w-100 shadow-sm d-none"
                                        style="z-index: 1050; max-height: 260px; overflow-y: auto;"></div>
                                </div>
                                <input type="hidden" name="crosschex_id" id="crosschexId"
                                    value="<?php echo e($selectedCrosschexId); ?>">
                            </div>

                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i>Load
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <?php if($selectedEmployee): ?>
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-lg-4">
                                <div class="border rounded-3 p-3 h-100 bg-light">
                                    <small class="text-muted d-block">Employee Name</small>
                                    <div class="fw-semibold fs-9"><?php echo e($selectedEmployee['employee_name']); ?></div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="border rounded-3 p-3 h-100 bg-light">
                                    <small class="text-muted d-block">Employee No</small>
                                    <div class="fw-semibold fs-9"><?php echo e($selectedEmployee['employee_no'] ?: '-'); ?></div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="border rounded-3 p-3 h-100 bg-light">
                                    <small class="text-muted d-block">CrossChex ID</small>
                                    <div class="fw-semibold fs-9"><?php echo e($selectedEmployee['crosschex_id'] ?: '-'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form method="POST" action="<?php echo e(route('manual-biometrics.store')); ?>">
                    <?php echo csrf_field(); ?>

                    <input type="hidden" name="cutoff_month" value="<?php echo e($cutoffMonth); ?>">
                    <input type="hidden" name="cutoff_year" value="<?php echo e($cutoffYear); ?>">
                    <input type="hidden" name="cutoff_type" value="<?php echo e($cutoffType); ?>">

                    <input type="hidden" name="crosschex_id" value="<?php echo e($selectedEmployee['crosschex_id']); ?>">
                    <input type="hidden" name="employee_id" value="<?php echo e($selectedEmployee['employee_id']); ?>">
                    <input type="hidden" name="employee_no" value="<?php echo e($selectedEmployee['employee_no']); ?>">
                    <input type="hidden" name="employee_name" value="<?php echo e($selectedEmployee['employee_name']); ?>">

                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-body-tertiary border-bottom">
                            <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                                <div>
                                    <h5 class="mb-0">Step 2: Encode Whole Cutoff</h5>
                                    <small class="text-muted"><?php echo e($cutoffLabel); ?></small>
                                </div>

                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="copyMonSatBtn">
                                        <i class="fas fa-copy me-1"></i>Apply Mon-Sat Only
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="applyAllBtn">
                                        <i class="fas fa-bolt me-1"></i>Apply All Blank Dates
                                    </button>
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-save me-1"></i>Save Employee Logs
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card-body border-bottom bg-light">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Common Time In</label>
                                    <input type="time" class="form-control" id="bulkTimeIn" value="09:00">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Common Time Out</label>
                                    <input type="time" class="form-control" id="bulkTimeOut" value="18:00">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Common Remarks</label>
                                    <input type="text" class="form-control" id="bulkRemarks"
                                        placeholder="Optional remarks">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-dark w-100" id="clearAllBtn">
                                        Clear All
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="bg-body-tertiary">
                                    <tr>
                                        <th style="width: 80px;">#</th>
                                        <th style="min-width: 140px;">Date</th>
                                        <th style="width: 100px;">Day</th>
                                        <th style="width: 150px;">Time In</th>
                                        <th style="width: 150px;">Time Out</th>
                                        <th style="min-width: 220px;">Remarks</th>
                                        <th style="width: 130px;">Encoded</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $cutoffRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $isSunday = $row['day_name'] === 'Sun';
                                        ?>
                                        <tr class="<?php echo e($isSunday ? 'table-light' : ''); ?>">
                                            <td class="fw-semibold"><?php echo e($index + 1); ?></td>
                                            <td>
                                                <div class="fw-semibold">
                                                    <?php echo e(\Carbon\Carbon::parse($row['work_date'])->format('M d, Y')); ?></div>
                                            </td>
                                            <td>
                                                <?php if($isSunday): ?>
                                                    <span
                                                        class="badge bg-warning-subtle text-warning"><?php echo e($row['day_name']); ?></span>
                                                <?php else: ?>
                                                    <span
                                                        class="badge bg-primary-subtle text-primary"><?php echo e($row['day_name']); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <input type="hidden" name="rows[<?php echo e($index); ?>][work_date]"
                                                    value="<?php echo e($row['work_date']); ?>">
                                                <input type="time" name="rows[<?php echo e($index); ?>][time_in]"
                                                    class="form-control row-time-in" value="<?php echo e($row['time_in']); ?>">
                                            </td>
                                            <td>
                                                <input type="time" name="rows[<?php echo e($index); ?>][time_out]"
                                                    class="form-control row-time-out" value="<?php echo e($row['time_out']); ?>">
                                            </td>
                                            <td>
                                                <input type="text" name="rows[<?php echo e($index); ?>][remarks]"
                                                    class="form-control row-remarks" value="<?php echo e($row['remarks']); ?>"
                                                    placeholder="Optional remarks">
                                            </td>
                                            <td>
                                                <?php if($row['has_manual_log']): ?>
                                                    <span class="badge bg-success-subtle text-success">Manual</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary-subtle text-secondary">None</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-body-tertiary border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">Saved Manual Logs</h5>
                                <small class="text-muted"><?php echo e($cutoffLabel); ?> |
                                    <?php echo e($selectedEmployee['employee_name']); ?></small>
                            </div>
                            <span class="badge bg-info-subtle text-info"><?php echo e($recentLogs->count()); ?> log(s)</span>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Date Time</th>
                                        <th>State</th>
                                        <th>Device</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $recentLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td class="fw-semibold">
                                                <?php echo e(optional($log->check_time)->format('M d, Y h:i A')); ?></td>
                                            <td>
                                                <?php if($log->state === 'Check In'): ?>
                                                    <span class="badge bg-success-subtle text-success">Check In</span>
                                                <?php elseif($log->state === 'Check Out'): ?>
                                                    <span class="badge bg-danger-subtle text-danger">Check Out</span>
                                                <?php else: ?>
                                                    <span
                                                        class="badge bg-secondary-subtle text-secondary"><?php echo e($log->state); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo e($log->device_name); ?></td>
                                            <td><?php echo e(data_get($log->raw, 'remarks', '-')); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                No saved manual logs for this cutoff.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-5 text-center">
                        <div class="mb-3">
                            <span class="fas fa-user-clock fs-2 text-primary"></span>
                        </div>
                        <h5 class="mb-2">No employee selected yet</h5>
                        <p class="text-muted mb-0">
                            Choose cutoff, search employee from biometrics logs, then click Load.
                        </p>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        const employeeSearch = document.getElementById('employeeSearch');
        const employeeResults = document.getElementById('employeeResults');
        const crosschexIdInput = document.getElementById('crosschexId');
        let employeeDebounce = null;

        if (employeeSearch) {
            employeeSearch.addEventListener('input', function() {
                const keyword = this.value.trim();
                crosschexIdInput.value = '';

                clearTimeout(employeeDebounce);

                if (keyword.length < 2) {
                    employeeResults.innerHTML = '';
                    employeeResults.classList.add('d-none');
                    return;
                }

                employeeDebounce = setTimeout(() => {
                    fetch(
                            `<?php echo e(route('manual-biometrics.search-employees')); ?>?q=${encodeURIComponent(keyword)}`
                        )
                        .then(response => response.json())
                        .then(data => {
                            employeeResults.innerHTML = '';

                            if (!data.length) {
                                employeeResults.innerHTML =
                                    `<div class="list-group-item text-muted small">No employee found.</div>`;
                                employeeResults.classList.remove('d-none');
                                return;
                            }

                            data.forEach(emp => {
                                const item = document.createElement('button');
                                item.type = 'button';
                                item.className = 'list-group-item list-group-item-action';
                                item.innerHTML = `
                                <div class="fw-semibold">${emp.employee_name ?? ''}</div>
                                <small class="text-muted">
                                    Employee No: ${emp.employee_no ?? '-'} |
                                    CrossChex ID: ${emp.crosschex_id ?? '-'}
                                </small>
                            `;

                                item.addEventListener('click', function() {
                                    employeeSearch.value = emp.employee_name ?? '';
                                    crosschexIdInput.value = emp.crosschex_id ?? '';
                                    employeeResults.innerHTML = '';
                                    employeeResults.classList.add('d-none');
                                });

                                employeeResults.appendChild(item);
                            });

                            employeeResults.classList.remove('d-none');
                        })
                        .catch(() => {
                            employeeResults.innerHTML =
                                `<div class="list-group-item text-danger small">Failed to load employees.</div>`;
                            employeeResults.classList.remove('d-none');
                        });
                }, 300);
            });

            document.addEventListener('click', function(e) {
                if (!employeeSearch.closest('.position-relative')?.contains(e.target)) {
                    employeeResults.classList.add('d-none');
                }
            });
        }

        const applyAllBtn = document.getElementById('applyAllBtn');
        const copyMonSatBtn = document.getElementById('copyMonSatBtn');
        const clearAllBtn = document.getElementById('clearAllBtn');

        function applyCommonTimes(monSatOnly = false) {
            const bulkTimeIn = document.getElementById('bulkTimeIn')?.value || '';
            const bulkTimeOut = document.getElementById('bulkTimeOut')?.value || '';
            const bulkRemarks = document.getElementById('bulkRemarks')?.value || '';

            document.querySelectorAll('table tbody tr').forEach(row => {
                const badge = row.querySelector('td:nth-child(3) .badge');
                const dayText = badge ? badge.textContent.trim() : '';
                const isSunday = dayText === 'Sun';

                if (monSatOnly && isSunday) {
                    return;
                }

                const timeIn = row.querySelector('.row-time-in');
                const timeOut = row.querySelector('.row-time-out');
                const remarks = row.querySelector('.row-remarks');

                if (timeIn && bulkTimeIn && !timeIn.value) timeIn.value = bulkTimeIn;
                if (timeOut && bulkTimeOut && !timeOut.value) timeOut.value = bulkTimeOut;
                if (remarks && bulkRemarks && !remarks.value) remarks.value = bulkRemarks;
            });
        }

        if (applyAllBtn) {
            applyAllBtn.addEventListener('click', function() {
                applyCommonTimes(false);
            });
        }

        if (copyMonSatBtn) {
            copyMonSatBtn.addEventListener('click', function() {
                applyCommonTimes(true);
            });
        }

        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', function() {
                document.querySelectorAll('.row-time-in').forEach(el => el.value = '');
                document.querySelectorAll('.row-time-out').forEach(el => el.value = '');
                document.querySelectorAll('.row-remarks').forEach(el => el.value = '');
            });
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/payroll/manual_biometrics/index.blade.php ENDPATH**/ ?>