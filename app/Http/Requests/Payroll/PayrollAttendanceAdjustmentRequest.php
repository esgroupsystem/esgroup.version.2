<?php

namespace App\Http\Requests\Payroll;

use App\Models\Holiday;
use App\Models\PayrollAttendanceAdjustment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PayrollAttendanceAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $type = (string) $this->adjustment_type;
        $rules = PayrollAttendanceAdjustment::rulesFor($type);

        if (($rules['date_mode'] ?? 'single') === 'range') {
            $this->merge([
                'work_date' => $this->date_from,
            ]);
        }

        if ($type === PayrollAttendanceAdjustment::TYPE_TYPHOON_DISASTER) {
            $this->merge([
                'employee_biometric_id' => null,
                'biometric_employee_id' => PayrollAttendanceAdjustment::GLOBAL_DISASTER_BIOMETRIC_ID,
                'employee_no' => null,
                'employee_name' => PayrollAttendanceAdjustment::GLOBAL_DISASTER_EMPLOYEE_NAME,
                'date_from' => null,
                'date_to' => null,
                'adjusted_time_in' => null,
                'adjusted_time_out' => null,
                'offset_source_date' => null,
                'is_paid' => true,
                'ignore_late' => true,
                'ignore_undertime' => true,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'employee_biometric_id' => [
                Rule::requiredIf(! $this->isGlobalDisasterType()),
                'nullable',
                'integer',
                'exists:employee_biometrics,id',
            ],

            'biometric_employee_id' => ['nullable', 'string', 'max:100'],
            'employee_no' => ['nullable', 'string', 'max:100'],
            'employee_name' => [
                Rule::requiredIf(! $this->isGlobalDisasterType()),
                'nullable',
                'string',
                'max:255',
            ],

            'adjustment_type' => [
                'required',
                Rule::in(array_keys(PayrollAttendanceAdjustment::TYPES)),
            ],

            'work_date' => [
                Rule::requiredIf(! $this->isLeaveType()),
                'nullable',
                'date',
            ],

            'date_from' => [
                Rule::requiredIf($this->isLeaveType()),
                'nullable',
                'date',
            ],

            'date_to' => [
                Rule::requiredIf($this->isLeaveType()),
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],

            'adjusted_time_in' => [
                Rule::requiredIf($this->requiresManualTime()),
                'nullable',
                'date_format:H:i',
            ],

            'adjusted_time_out' => [
                Rule::requiredIf($this->requiresManualTime()),
                'nullable',
                'date_format:H:i',
            ],

            'offset_source_date' => [
                Rule::requiredIf($this->isOffsetType()),
                'nullable',
                'date',
                'different:work_date',
                'before:work_date',
            ],

            'offset_hours' => [
                Rule::requiredIf($this->isOffsetType()),
                'nullable',
                'numeric',
                'min:0.01',
                'max:24',
            ],

            'is_paid' => ['nullable', 'boolean'],
            'ignore_late' => ['nullable', 'boolean'],
            'ignore_undertime' => ['nullable', 'boolean'],
            'reason' => ['required', 'string', 'max:5000'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->requiresManualTime()) {
                return;
            }

            $timeIn = trim((string) $this->adjusted_time_in);
            $timeOut = trim((string) $this->adjusted_time_out);

            if ($timeIn === '' || $timeOut === '') {
                return;
            }

            if ($timeIn === $timeOut) {
                $validator->errors()->add(
                    'adjusted_time_out',
                    'Time out must be different from time in. Overnight ranges are allowed.'
                );
            }

            if (
                $this->adjustment_type === PayrollAttendanceAdjustment::TYPE_HOLIDAY_WORK
                && $this->work_date
                && ! Holiday::query()->active()->onDate((string) $this->work_date)->exists()
            ) {
                $validator->errors()->add(
                    'work_date',
                    'Holiday Work can only be filed on an active date in the Holiday Calendar. Plot the holiday first, then file the Holiday Work adjustment.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'employee_biometric_id.required' => 'Please select an active payroll employee.',
            'employee_biometric_id.exists' => 'The selected biometric employee does not exist.',
            'employee_name.required' => 'Please select an employee from biometrics.',
            'date_from.required' => 'Date from is required for leave adjustments.',
            'date_to.required' => 'Date to is required for leave adjustments.',
            'work_date.required' => 'Work date is required for this adjustment type.',
            'adjusted_time_in.required' => 'Time in is required for this adjustment type.',
            'adjusted_time_out.required' => 'Time out is required for this adjustment type.',
            'offset_source_date.required' => 'Please select the earlier source date containing the excess work time for this Offset.',
            'offset_source_date.different' => 'Offset source date and target date must not be the same.',
            'offset_source_date.before' => 'Offset source date must be earlier than the target attendance date.',
            'offset_hours.required' => 'Please enter the number of compensatory hours to transfer.',
            'offset_hours.numeric' => 'Offset hours must be a valid number.',
            'offset_hours.min' => 'Offset hours must be greater than zero.',
            'offset_hours.max' => 'Offset hours cannot exceed 24 hours in one request.',
            'reason.required' => 'Please enter the approved reason or supporting reference for this adjustment.',
        ];
    }

    private function isLeaveType(): bool
    {
        return in_array($this->adjustment_type, [
            PayrollAttendanceAdjustment::TYPE_SICK_LEAVE,
            PayrollAttendanceAdjustment::TYPE_MEDICAL_LEAVE,
        ], true);
    }

    private function isOffsetType(): bool
    {
        return $this->adjustment_type === PayrollAttendanceAdjustment::TYPE_OFFSET;
    }

    private function isGlobalDisasterType(): bool
    {
        return $this->adjustment_type === PayrollAttendanceAdjustment::TYPE_TYPHOON_DISASTER;
    }

    private function requiresManualTime(): bool
    {
        $rules = PayrollAttendanceAdjustment::rulesFor((string) $this->adjustment_type);

        return in_array(
            (string) ($rules['manual_time_mode'] ?? 'none'),
            ['schedule', 'actual', 'overtime'],
            true
        );
    }
}
