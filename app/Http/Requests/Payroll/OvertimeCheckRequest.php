<?php

declare(strict_types=1);

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;

/** The adjustment form's live OT check. */
final class OvertimeCheckRequest extends FormRequest
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
            'adjusted_time_in' => ['required', 'date_format:H:i'],
            'adjusted_time_out' => ['required', 'date_format:H:i', 'different:adjusted_time_in'],
            'adjustment_id' => ['nullable', 'integer'],
        ];
    }
}
