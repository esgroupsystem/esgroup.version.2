<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Biometrics Employee</label>
        <select name="employee_picker" id="employee_picker" class="form-select" required>
            <option value="">Select employee</option>
            @foreach ($people as $person)
                <option value="{{ $person->biometric_employee_id ?: $person->employee_no ?: $person->employee_name }}"
                    data-biometric-id="{{ $person->biometric_employee_id }}" data-employee-no="{{ $person->employee_no }}"
                    data-employee-name="{{ $person->employee_name }}" @selected(old('biometric_employee_id', $payrollAttendanceAdjustment->biometric_employee_id ?? '') == $person->biometric_employee_id && old('employee_no', $payrollAttendanceAdjustment->employee_no ?? '') == $person->employee_no)>
                    {{ $person->employee_name }}
                    @if (!empty($person->employee_no))
                        - {{ $person->employee_no }}
                    @endif
                    @if (!empty($person->biometric_employee_id))
                        (Bio ID: {{ $person->biometric_employee_id }})
                    @endif
                </option>
            @endforeach
        </select>

        <input type="hidden" name="biometric_employee_id" id="biometric_employee_id"
            value="{{ old('biometric_employee_id', $payrollAttendanceAdjustment->biometric_employee_id ?? '') }}">

        <input type="hidden" name="employee_no" id="employee_no"
            value="{{ old('employee_no', $payrollAttendanceAdjustment->employee_no ?? '') }}">

        <input type="hidden" name="employee_name" id="employee_name"
            value="{{ old('employee_name', $payrollAttendanceAdjustment->employee_name ?? '') }}">

        @error('biometric_employee_id')
            <small class="text-danger">{{ $message }}</small>
        @enderror
        @error('employee_name')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Work Date</label>
        <input type="date" name="work_date" class="form-control"
            value="{{ old('work_date', isset($payrollAttendanceAdjustment) && $payrollAttendanceAdjustment->work_date ? $payrollAttendanceAdjustment->work_date->format('Y-m-d') : '') }}"
            required>
        @error('work_date')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Adjustment Type</label>
        <select name="adjustment_type" class="form-select" required>
            <option value="">Select type</option>
            @foreach ([
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
    ] as $value => $label)
                <option value="{{ $value }}" @selected(old('adjustment_type', $payrollAttendanceAdjustment->adjustment_type ?? '') == $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('adjustment_type')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Adjusted Time In</label>
        <input type="time" name="adjusted_time_in" class="form-control"
            value="{{ old('adjusted_time_in', $payrollAttendanceAdjustment->adjusted_time_in ?? '') }}">
        @error('adjusted_time_in')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Adjusted Time Out</label>
        <input type="time" name="adjusted_time_out" class="form-control"
            value="{{ old('adjusted_time_out', $payrollAttendanceAdjustment->adjusted_time_out ?? '') }}">
        @error('adjusted_time_out')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Adjusted Day Type</label>
        <input type="text" name="adjusted_day_type" class="form-control"
            placeholder="rest_day / holiday / offset / present"
            value="{{ old('adjusted_day_type', $payrollAttendanceAdjustment->adjusted_day_type ?? '') }}">
        @error('adjusted_day_type')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label d-block">Options</label>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_paid" value="1"
                {{ old('is_paid', $payrollAttendanceAdjustment->is_paid ?? true) ? 'checked' : '' }}>
            <label class="form-check-label">Paid</label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="ignore_late" value="1"
                {{ old('ignore_late', $payrollAttendanceAdjustment->ignore_late ?? false) ? 'checked' : '' }}>
            <label class="form-check-label">Ignore Late</label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="ignore_undertime" value="1"
                {{ old('ignore_undertime', $payrollAttendanceAdjustment->ignore_undertime ?? false) ? 'checked' : '' }}>
            <label class="form-check-label">Ignore Undertime</label>
        </div>
    </div>

    <div class="col-md-12">
        <label class="form-label">Reason</label>
        <textarea name="reason" rows="3" class="form-control">{{ old('reason', $payrollAttendanceAdjustment->reason ?? '') }}</textarea>
        @error('reason')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-md-12">
        <label class="form-label">Remarks</label>
        <textarea name="remarks" rows="2" class="form-control">{{ old('remarks', $payrollAttendanceAdjustment->remarks ?? '') }}</textarea>
        @error('remarks')
            <small class="text-danger">{{ $message }}</small>
        @enderror
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
