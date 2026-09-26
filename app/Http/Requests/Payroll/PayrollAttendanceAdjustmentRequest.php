<?php

declare(strict_types=1);

namespace App\Http\Requests\Payroll;

use App\Models\Holiday;
use App\Models\PayrollAttendanceAdjustment;
use App\Support\Payroll\OvertimeCheck;
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

        if ($type === PayrollAttendanceAdjustment::TYPE_OFFSET) {
            $this->merge($this->normalizedOffsetSourceInput());
        } else {
            // The form always posts its (hidden, blank) Offset source rows;
            // they must not be validated for any other adjustment type.
            $this->merge(['offset_sources' => []]);
        }

        if (PayrollAttendanceAdjustment::isTyphoonDisasterType($type)) {
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

            'offset_sources' => [
                Rule::requiredIf($this->isOffsetType()),
                'nullable',
                'array',
                'max:31',
            ],
            'offset_sources.*.date' => [
                'required',
                'date',
                'before:work_date',
                'distinct',
            ],
            'offset_sources.*.hours' => [
                'required',
                'numeric',
                'min:0.01',
                'max:24',
            ],

            'amount' => [
                Rule::requiredIf($this->isCashAdjustmentType()),
                'nullable',
                'numeric',
                'min:-1000000',
                'max:1000000',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (is_numeric($value) && round((float) $value, 2) == 0.0) {
                        $fail('Amount cannot be zero. Use a positive amount to add pay or a negative amount (e.g. -1000) to deduct.');
                    }
                },
            ],

            'is_paid' => ['nullable', 'boolean'],
            'ignore_late' => ['nullable', 'boolean'],
            'ignore_undertime' => ['nullable', 'boolean'],
            'reason' => ['required', 'string', 'max:5000'],
            'remarks' => ['nullable', 'string', 'max:5000'],

            // Overtime needs the signed/approved OT form (a scan, photo or PDF).
            'ot_form' => [
                Rule::requiredIf($this->isOvertimeType() && ! $this->existingAttachment()),
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png,webp',
                'max:5120',
            ],
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

            // OT checker: biometric logs must cover the window, max 12 hours, no double filing.
            if (
                $this->isOvertimeType()
                && $timeIn !== $timeOut
                && $this->employee_biometric_id
                && $this->work_date
                && ! $validator->errors()->hasAny(['employee_biometric_id', 'work_date', 'adjusted_time_in', 'adjusted_time_out'])
            ) {
                $result = app(OvertimeCheck::class)->check(
                    (int) $this->employee_biometric_id,
                    $this->biometric_employee_id ? (string) $this->biometric_employee_id : null,
                    $this->employee_no ? (string) $this->employee_no : null,
                    (string) $this->employee_name,
                    (string) $this->work_date,
                    $timeIn,
                    $timeOut,
                    $this->existingAdjustment()?->id,
                );

                foreach ($result['errors'] as $message) {
                    $validator->errors()->add('adjusted_time_out', $message);
                }
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
            'offset_sources.required' => 'Please add at least one earlier source date containing excess work time for this Offset.',
            'offset_sources.*.date.required' => 'Every Offset source row needs a source date.',
            'offset_sources.*.date.before' => 'Every Offset source date must be earlier than the target attendance date.',
            'offset_sources.*.date.distinct' => 'The same Offset source date is listed more than once.',
            'offset_sources.*.hours.required' => 'Every Offset source row needs the hours to transfer.',
            'offset_sources.*.hours.min' => 'Offset hours for each source date must be greater than zero.',
            'offset_sources.*.hours.max' => 'Offset hours for one source date cannot exceed 24.',
            'amount.required' => 'Please enter the salary adjustment amount. Use a negative amount (e.g. -1000) for a deduction.',
            'amount.numeric' => 'Amount must be a valid number.',
            'amount.min' => 'A salary adjustment deduction cannot exceed ₱1,000,000.',
            'amount.max' => 'A salary adjustment addition cannot exceed ₱1,000,000.',
            'reason.required' => 'Please enter the approved reason or supporting reference for this adjustment.',
            'ot_form.required' => 'Upload the approved OT form (PDF or photo) to file overtime.',
            'ot_form.mimes' => 'The OT form must be a PDF, JPG, PNG or WEBP file.',
            'ot_form.max' => 'The OT form must not be larger than 5 MB.',
        ];
    }

    private function isLeaveType(): bool
    {
        return in_array($this->adjustment_type, [
            PayrollAttendanceAdjustment::TYPE_SICK_LEAVE,
            PayrollAttendanceAdjustment::TYPE_MEDICAL_LEAVE,
        ], true);
    }

    private function isOvertimeType(): bool
    {
        return $this->adjustment_type === PayrollAttendanceAdjustment::TYPE_OVERTIME;
    }

    /** The adjustment being edited (null when filing a new one). */
    private function existingAdjustment(): ?PayrollAttendanceAdjustment
    {
        $adjustment = $this->route('payrollAttendanceAdjustment');

        return $adjustment instanceof PayrollAttendanceAdjustment ? $adjustment : null;
    }

    /** Editing an OT that already has its form: a new upload is optional. */
    private function existingAttachment(): bool
    {
        return filled($this->existingAdjustment()?->attachment_path);
    }

    private function isOffsetType(): bool
    {
        return $this->adjustment_type === PayrollAttendanceAdjustment::TYPE_OFFSET;
    }

    /**
     * Offset may pool excess time from several source dates. Blank rows are
     * dropped; the legacy single-date fields are derived from the list (or,
     * for old clients, the list is built from them).
     */
    private function normalizedOffsetSourceInput(): array
    {
        $sources = collect(is_array($this->input('offset_sources')) ? $this->input('offset_sources') : [])
            ->filter(fn (mixed $row): bool => is_array($row)
                && (filled($row['date'] ?? null) || filled($row['hours'] ?? null)))
            ->map(fn (array $row): array => [
                'date' => trim((string) ($row['date'] ?? '')),
                'hours' => trim((string) ($row['hours'] ?? '')),
            ])
            ->values();

        if ($sources->isEmpty() && filled($this->input('offset_source_date'))) {
            $sources = collect([[
                'date' => trim((string) $this->input('offset_source_date')),
                'hours' => trim((string) $this->input('offset_hours')),
            ]]);
        }

        if ($sources->isEmpty()) {
            return ['offset_sources' => []];
        }

        $validDates = $sources->pluck('date')->filter(fn (string $date): bool => strtotime($date) !== false);

        return [
            'offset_sources' => $sources->all(),
            'offset_source_date' => $validDates->sort()->first() ?? $sources->first()['date'],
            'offset_hours' => (string) round(
                $sources->sum(fn (array $row): float => is_numeric($row['hours']) ? (float) $row['hours'] : 0.0),
                2
            ),
        ];
    }

    private function isCashAdjustmentType(): bool
    {
        return PayrollAttendanceAdjustment::isCashAdjustmentType((string) $this->adjustment_type);
    }

    private function isGlobalDisasterType(): bool
    {
        return PayrollAttendanceAdjustment::isTyphoonDisasterType((string) $this->adjustment_type);
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
