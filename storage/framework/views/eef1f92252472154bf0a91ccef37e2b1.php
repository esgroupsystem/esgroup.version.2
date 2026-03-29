
<?php $__env->startSection('title', 'Add Employee Salary'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid" data-layout="container">
        <div class="content">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-body-tertiary">
                    <h5 class="mb-0">Add Employee Salary</h5>
                </div>

                <div class="card-body">
                    <form action="<?php echo e(route('payroll-employee-salaries.store')); ?>" method="POST">
                        <?php echo csrf_field(); ?>

                        <div class="mb-3 position-relative">
                            <label class="form-label">Select Employee from Biometrics</label>
                            <input type="text" id="employeePicker" class="form-control"
                                placeholder="Search employee name or employee no" autocomplete="off">

                            <div id="employeeDropdown" class="card shadow-sm border mt-1 d-none position-absolute w-100"
                                style="z-index: 1050; max-height: 260px; overflow-y: auto;">
                                <div class="list-group list-group-flush">
                                    <?php $__currentLoopData = $people; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $person): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <button type="button"
                                            class="list-group-item list-group-item-action employee-option"
                                            data-biometric="<?php echo e($person->biometric_employee_id); ?>"
                                            data-empno="<?php echo e($person->employee_no); ?>" data-name="<?php echo e($person->employee_name); ?>"
                                            data-crosschex="<?php echo e($person->crosschex_id); ?>"
                                            data-search="<?php echo e(strtolower(trim(($person->employee_name ?? '') . ' ' . ($person->employee_no ?? '') . ' ' . ($person->crosschex_id ?? '')))); ?>">
                                            <div>
                                                <div class="fw-semibold text-dark"><?php echo e($person->employee_name); ?></div>
                                                <div class="small text-muted">
                                                    <?php echo e($person->employee_no ?: 'No Employee No'); ?>

                                                    <?php if($person->crosschex_id): ?>
                                                        | Crosschex: <?php echo e($person->crosschex_id); ?>

                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </button>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                    <div id="employeeNoResult" class="list-group-item text-muted d-none">
                                        No employee found.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="biometric_employee_id" id="biometric_employee_id"
                            value="<?php echo e(old('biometric_employee_id')); ?>">
                        <input type="hidden" name="crosschex_id" id="crosschex_id" value="<?php echo e(old('crosschex_id')); ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Employee No</label>
                                <input type="text" name="employee_no" id="employee_no"
                                    class="form-control <?php $__errorArgs = ['employee_no'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    value="<?php echo e(old('employee_no')); ?>">
                                <?php $__errorArgs = ['employee_no'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Employee Name</label>
                                <input type="text" name="employee_name" id="employee_name"
                                    class="form-control <?php $__errorArgs = ['employee_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    value="<?php echo e(old('employee_name')); ?>" required>
                                <?php $__errorArgs = ['employee_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Rate Type</label>
                                <select name="rate_type" id="rate_type"
                                    class="form-select <?php $__errorArgs = ['rate_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <option value="daily" <?php echo e(old('rate_type') == 'daily' ? 'selected' : ''); ?>>Daily
                                    </option>
                                    <option value="monthly" <?php echo e(old('rate_type') == 'monthly' ? 'selected' : ''); ?>>Monthly
                                    </option>
                                </select>
                                <?php $__errorArgs = ['rate_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Basic Salary</label>
                                <input type="number" step="0.01" name="basic_salary" id="basic_salary"
                                    class="form-control <?php $__errorArgs = ['basic_salary'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    value="<?php echo e(old('basic_salary', 0)); ?>" required>
                                <?php $__errorArgs = ['basic_salary'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Allowance</label>
                                <input type="number" step="0.01" name="allowance"
                                    class="form-control <?php $__errorArgs = ['allowance'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    value="<?php echo e(old('allowance', 0)); ?>">
                                <?php $__errorArgs = ['allowance'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">OT Rate / Hour</label>
                                <input type="number" step="0.01" name="ot_rate_per_hour" id="ot_rate_per_hour"
                                    class="form-control <?php $__errorArgs = ['ot_rate_per_hour'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    value="<?php echo e(old('ot_rate_per_hour', 0)); ?>" readonly>
                                <?php $__errorArgs = ['ot_rate_per_hour'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Late Deduction / Minute</label>
                                <input type="number" step="0.0001" name="late_deduction_per_minute"
                                    id="late_deduction_per_minute"
                                    class="form-control <?php $__errorArgs = ['late_deduction_per_minute'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    value="<?php echo e(old('late_deduction_per_minute', 0)); ?>" readonly>
                                <?php $__errorArgs = ['late_deduction_per_minute'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Undertime Deduction / Minute</label>
                                <input type="number" step="0.0001" name="undertime_deduction_per_minute"
                                    id="undertime_deduction_per_minute"
                                    class="form-control <?php $__errorArgs = ['undertime_deduction_per_minute'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    value="<?php echo e(old('undertime_deduction_per_minute', 0)); ?>" readonly>
                                <?php $__errorArgs = ['undertime_deduction_per_minute'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Absent Deduction / Day</label>
                                <input type="number" step="0.01" name="absent_deduction_per_day"
                                    id="absent_deduction_per_day"
                                    class="form-control <?php $__errorArgs = ['absent_deduction_per_day'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    value="<?php echo e(old('absent_deduction_per_day', 0)); ?>" readonly>
                                <?php $__errorArgs = ['absent_deduction_per_day'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">SSS Loan</label>
                                <input type="number" step="0.01" name="sss_loan"
                                    class="form-control <?php $__errorArgs = ['sss_loan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    value="<?php echo e(old('sss_loan', 0)); ?>">
                                <?php $__errorArgs = ['sss_loan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Pagibig Loan</label>
                                <input type="number" step="0.01" name="pagibig_loan"
                                    class="form-control <?php $__errorArgs = ['pagibig_loan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    value="<?php echo e(old('pagibig_loan', 0)); ?>">
                                <?php $__errorArgs = ['pagibig_loan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Vale</label>
                                <input type="number" step="0.01" name="vale"
                                    class="form-control <?php $__errorArgs = ['vale'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    value="<?php echo e(old('vale', 0)); ?>">
                                <?php $__errorArgs = ['vale'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Other Loans Deduction</label>
                                <input type="number" step="0.01" name="other_loans"
                                    class="form-control <?php $__errorArgs = ['other_loans'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    value="<?php echo e(old('other_loans', 0)); ?>">
                                <?php $__errorArgs = ['other_loans'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" rows="3" class="form-control <?php $__errorArgs = ['remarks'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"><?php echo e(old('remarks')); ?></textarea>
                                <?php $__errorArgs = ['remarks'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                        <?php echo e(old('is_active', 1) ? 'checked' : ''); ?> id="is_active">
                                    <label class="form-check-label" for="is_active">
                                        Active
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button class="btn btn-primary">Save Salary</button>
                            <a href="<?php echo e(route('payroll-employee-salaries.index')); ?>" class="btn btn-light">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        #employeeDropdown .list-group-item {
            border-left: 0;
            border-right: 0;
        }

        #employeeDropdown .list-group-item:first-child {
            border-top: 0;
        }

        #employeeDropdown .list-group-item:last-child {
            border-bottom: 0;
        }

        #employeeDropdown .employee-option:hover,
        #employeeDropdown .employee-option.active {
            background-color: rgba(44, 123, 229, 0.08);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const picker = document.getElementById('employeePicker');
            const dropdown = document.getElementById('employeeDropdown');
            const wrapper = picker.closest('.position-relative');
            const options = Array.from(document.querySelectorAll('.employee-option'));
            const noResult = document.getElementById('employeeNoResult');

            const biometricInput = document.getElementById('biometric_employee_id');
            const employeeNoInput = document.getElementById('employee_no');
            const employeeNameInput = document.getElementById('employee_name');
            const crosschexInput = document.getElementById('crosschex_id');

            const rateTypeInput = document.getElementById('rate_type');
            const basicSalaryInput = document.getElementById('basic_salary');
            const otRateInput = document.getElementById('ot_rate_per_hour');
            const lateInput = document.getElementById('late_deduction_per_minute');
            const undertimeInput = document.getElementById('undertime_deduction_per_minute');
            const absentInput = document.getElementById('absent_deduction_per_day');

            function showDropdown() {
                dropdown.classList.remove('d-none');
            }

            function hideDropdown() {
                dropdown.classList.add('d-none');
            }

            function clearSelectedFields() {
                biometricInput.value = '';
                employeeNoInput.value = '';
                employeeNameInput.value = '';
                crosschexInput.value = '';
            }

            function fillEmployee(option) {
                const name = option.dataset.name || '';
                const empno = option.dataset.empno || '';

                picker.value = name + (empno ? ' | ' + empno : '');
                biometricInput.value = option.dataset.biometric || '';
                employeeNoInput.value = empno;
                employeeNameInput.value = name;
                crosschexInput.value = option.dataset.crosschex || '';

                hideDropdown();
            }

            function filterOptions() {
                const keyword = picker.value.trim().toLowerCase();
                let visibleCount = 0;

                options.forEach(option => {
                    const haystack = option.dataset.search || '';
                    const matched = keyword === '' || haystack.includes(keyword);

                    option.classList.toggle('d-none', !matched);

                    if (matched) {
                        visibleCount++;
                    }
                });

                noResult.classList.toggle('d-none', visibleCount !== 0);
            }

            function roundTo(value, decimals) {
                return Number(value).toFixed(decimals);
            }

            function computeSalaryRates() {
                const rateType = rateTypeInput.value;
                const basicSalary = parseFloat(basicSalaryInput.value) || 0;

                const workingDaysPerMonth = 22;
                const hoursPerDay = 8;
                const minutesPerHour = 60;

                if (basicSalary <= 0) {
                    otRateInput.value = roundTo(0, 2);
                    lateInput.value = roundTo(0, 4);
                    undertimeInput.value = roundTo(0, 4);
                    absentInput.value = roundTo(0, 2);
                    return;
                }

                let dailyRate = 0;

                if (rateType === 'monthly') {
                    dailyRate = basicSalary / workingDaysPerMonth;
                } else {
                    dailyRate = basicSalary;
                }

                const hourlyRate = dailyRate / hoursPerDay;
                const perMinuteRate = hourlyRate / minutesPerHour;

                otRateInput.value = roundTo(hourlyRate, 2);
                lateInput.value = roundTo(perMinuteRate, 4);
                undertimeInput.value = roundTo(perMinuteRate, 4);
                absentInput.value = roundTo(dailyRate, 2);
            }

            picker.addEventListener('focus', function() {
                filterOptions();
                showDropdown();
            });

            picker.addEventListener('input', function() {
                clearSelectedFields();
                filterOptions();
                showDropdown();
            });

            options.forEach(option => {
                option.addEventListener('click', function() {
                    fillEmployee(option);
                });
            });

            document.addEventListener('click', function(e) {
                if (!wrapper.contains(e.target)) {
                    hideDropdown();
                }
            });

            basicSalaryInput.addEventListener('input', computeSalaryRates);
            rateTypeInput.addEventListener('change', computeSalaryRates);

            computeSalaryRates();
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/payroll/employee_salaries/create.blade.php ENDPATH**/ ?>