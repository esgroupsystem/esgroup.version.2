<?php

declare(strict_types=1);

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;

/**
 * The adjustment form's "Check available offset credit". Multi-date sources; the older single
 * source fields are still accepted.
 */
final class OffsetProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'employee_biometric_id' => ['required', 'integer', 'exists:employee_biometrics,id'],
            'biometric_employee_id' => ['nullable', 'string'],
            'employee_no' => ['nullable', 'string'],
            'employee_name' => ['required', 'string'],
            'work_date' => ['required', 'date'],
            'offset_sources' => ['nullable', 'array', 'max:31'],
            'offset_sources.*.date' => ['required', 'date', 'before:work_date', 'distinct'],
            'offset_sources.*.hours' => ['required', 'numeric', 'min:0.01', 'max:24'],
            'offset_source_date' => ['required_without:offset_sources', 'nullable', 'date', 'before:work_date'],
            'offset_hours' => ['required_without:offset_sources', 'nullable', 'numeric', 'min:0.01', 'max:24'],
            'adjustment_id' => ['nullable', 'integer', 'exists:payroll_attendance_adjustments,id'],
        ];
    }
}
