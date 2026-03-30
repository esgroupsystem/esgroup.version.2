<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Biometrics Employee</label>
        <select name="employee_picker" id="employee_picker" class="form-select" required>
            <option value="">Select employee</option>
            <?php $__currentLoopData = $people; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $person): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($person->biometric_employee_id ?: $person->employee_no ?: $person->employee_name); ?>"
                    data-biometric-id="<?php echo e($person->biometric_employee_id); ?>" data-employee-no="<?php echo e($person->employee_no); ?>"
                    data-employee-name="<?php echo e($person->employee_name); ?>" <?php if(old('biometric_employee_id', $payrollAttendanceAdjustment->biometric_employee_id ?? '') == $person->biometric_employee_id && old('employee_no', $payrollAttendanceAdjustment->employee_no ?? '') == $person->employee_no): echo 'selected'; endif; ?>>
                    <?php echo e($person->employee_name); ?>

                    <?php if(!empty($person->employee_no)): ?>
                        - <?php echo e($person->employee_no); ?>

                    <?php endif; ?>
                    <?php if(!empty($person->biometric_employee_id)): ?>
                        (Bio ID: <?php echo e($person->biometric_employee_id); ?>)
                    <?php endif; ?>
                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>

        <input type="hidden" name="biometric_employee_id" id="biometric_employee_id"
            value="<?php echo e(old('biometric_employee_id', $payrollAttendanceAdjustment->biometric_employee_id ?? '')); ?>">

        <input type="hidden" name="employee_no" id="employee_no"
            value="<?php echo e(old('employee_no', $payrollAttendanceAdjustment->employee_no ?? '')); ?>">

        <input type="hidden" name="employee_name" id="employee_name"
            value="<?php echo e(old('employee_name', $payrollAttendanceAdjustment->employee_name ?? '')); ?>">

        <?php $__errorArgs = ['biometric_employee_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <small class="text-danger"><?php echo e($message); ?></small>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        <?php $__errorArgs = ['employee_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <small class="text-danger"><?php echo e($message); ?></small>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="col-md-3">
        <label class="form-label">Work Date</label>
        <input type="date" name="work_date" class="form-control"
            value="<?php echo e(old('work_date', isset($payrollAttendanceAdjustment) && $payrollAttendanceAdjustment->work_date ? $payrollAttendanceAdjustment->work_date->format('Y-m-d') : '')); ?>"
            required>
        <?php $__errorArgs = ['work_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <small class="text-danger"><?php echo e($message); ?></small>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="col-md-3">
        <label class="form-label">Adjustment Type</label>
        <select name="adjustment_type" class="form-select" required>
            <option value="">Select type</option>
            <?php $__currentLoopData = [
        'change_schedule' => 'Change Schedule',
        'change_time' => 'Change Time',
        'offset' => 'Offset',
        'rest_day_work' => 'Rest Day Work',
        'holiday_work' => 'Holiday Work',
        'official_business' => 'Official Business',
        'training' => 'Training',
        'manual_time_in_out' => 'Manual Time In/Out',
        'manual_present' => 'Manual Present',
        'manual_absent' => 'Manual Absent',
    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($value); ?>" <?php if(old('adjustment_type', $payrollAttendanceAdjustment->adjustment_type ?? '') == $value): echo 'selected'; endif; ?>>
                    <?php echo e($label); ?>

                </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php $__errorArgs = ['adjustment_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <small class="text-danger"><?php echo e($message); ?></small>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="col-md-3">
        <label class="form-label">Adjusted Time In</label>
        <input type="time" name="adjusted_time_in" class="form-control"
            value="<?php echo e(old('adjusted_time_in', $payrollAttendanceAdjustment->adjusted_time_in ?? '')); ?>">
        <?php $__errorArgs = ['adjusted_time_in'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <small class="text-danger"><?php echo e($message); ?></small>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="col-md-3">
        <label class="form-label">Adjusted Time Out</label>
        <input type="time" name="adjusted_time_out" class="form-control"
            value="<?php echo e(old('adjusted_time_out', $payrollAttendanceAdjustment->adjusted_time_out ?? '')); ?>">
        <?php $__errorArgs = ['adjusted_time_out'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <small class="text-danger"><?php echo e($message); ?></small>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="col-md-3">
        <label class="form-label">Adjusted Day Type</label>
        <input type="text" name="adjusted_day_type" class="form-control"
            placeholder="rest_day / holiday / offset / present"
            value="<?php echo e(old('adjusted_day_type', $payrollAttendanceAdjustment->adjusted_day_type ?? '')); ?>">
        <?php $__errorArgs = ['adjusted_day_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <small class="text-danger"><?php echo e($message); ?></small>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="col-md-3">
        <label class="form-label d-block">Options</label>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_paid" value="1"
                <?php echo e(old('is_paid', $payrollAttendanceAdjustment->is_paid ?? true) ? 'checked' : ''); ?>>
            <label class="form-check-label">Paid</label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="ignore_late" value="1"
                <?php echo e(old('ignore_late', $payrollAttendanceAdjustment->ignore_late ?? false) ? 'checked' : ''); ?>>
            <label class="form-check-label">Ignore Late</label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="ignore_undertime" value="1"
                <?php echo e(old('ignore_undertime', $payrollAttendanceAdjustment->ignore_undertime ?? false) ? 'checked' : ''); ?>>
            <label class="form-check-label">Ignore Undertime</label>
        </div>
    </div>

    <div class="col-md-12">
        <label class="form-label">Reason</label>
        <textarea name="reason" rows="3" class="form-control"><?php echo e(old('reason', $payrollAttendanceAdjustment->reason ?? '')); ?></textarea>
        <?php $__errorArgs = ['reason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <small class="text-danger"><?php echo e($message); ?></small>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="col-md-12">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" rows="2" class="form-control"><?php echo e(old('remarks', $payrollAttendanceAdjustment->remarks ?? '')); ?></textarea>
        <?php $__errorArgs = ['remarks'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <small class="text-danger"><?php echo e($message); ?></small>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const picker = document.getElementById('employee_picker');
        const biometricIdInput = document.getElementById('biometric_employee_id');
        const employeeNoInput = document.getElementById('employee_no');
        const employeeNameInput = document.getElementById('employee_name');

        function syncSelectedEmployee() {
            const selected = picker.options[picker.selectedIndex];

            if (!selected || !selected.value) {
                biometricIdInput.value = '';
                employeeNoInput.value = '';
                employeeNameInput.value = '';
                return;
            }

            biometricIdInput.value = selected.dataset.biometricId || '';
            employeeNoInput.value = selected.dataset.employeeNo || '';
            employeeNameInput.value = selected.dataset.employeeName || '';
        }

        picker.addEventListener('change', syncSelectedEmployee);
        syncSelectedEmployee();
    });
</script>
<?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/payroll/attendance_adjustments/_form.blade.php ENDPATH**/ ?>