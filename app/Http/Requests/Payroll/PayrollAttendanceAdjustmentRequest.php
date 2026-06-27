<?php

namespace App\Http\Requests\Payroll;

use App\Models\PayrollAttendanceAdjustment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PayrollAttendanceAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (in_array($this->adjustment_type, [
            PayrollAttendanceAdjustment::TYPE_SICK_LEAVE,
            PayrollAttendanceAdjustment::TYPE_MEDICAL_LEAVE,
        ], true)) {
            $this->merge([
                'work_date' => $this->date_from,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'biometric_employee_id' => ['required', 'string', 'max:100'],
            'employee_no' => ['nullable', 'string', 'max:100'],
            'employee_name' => ['required', 'string', 'max:255'],

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
                'after:adjusted_time_in',
            ],

            'offset_source_date' => [
                Rule::requiredIf($this->isOffsetType()),
                'nullable',
                'date',
                'different:work_date',
            ],

            'is_paid' => ['nullable', 'boolean'],
            'ignore_late' => ['nullable', 'boolean'],
            'ignore_undertime' => ['nullable', 'boolean'],
            'reason' => ['nullable', 'string', 'max:5000'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'biometric_employee_id.required' => 'Please select an employee from biometrics.',
            'date_from.required' => 'Date from is required for Sick Leave and Medical Leave.',
            'date_to.required' => 'Date to is required for Sick Leave and Medical Leave.',
            'work_date.required' => 'Work date is required for this adjustment type.',
            'adjusted_time_in.required' => 'Time in is required for Change Schedule, Official Business, Holiday Work, and Overtime.',
            'adjusted_time_out.required' => 'Time out is required for Change Schedule, Official Business, Holiday Work, and Overtime.',
            'adjusted_time_out.after' => 'Time out must be later than time in.',
            'offset_source_date.required' => 'Please select the biometric proof date for offset.',
            'offset_source_date.different' => 'Offset proof date and transfer date must not be the same.',
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

    private function requiresManualTime(): bool
    {
        return in_array($this->adjustment_type, [
            PayrollAttendanceAdjustment::TYPE_CHANGE_SCHEDULE,
            PayrollAttendanceAdjustment::TYPE_OFFICIAL_BUSINESS,
            PayrollAttendanceAdjustment::TYPE_HOLIDAY_WORK,
            PayrollAttendanceAdjustment::TYPE_OVERTIME,
        ], true);
    }
}
