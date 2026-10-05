<?php

declare(strict_types=1);

namespace App\Http\Requests\Biometrics;

use Illuminate\Foundation\Http\FormRequest;

/** Manual Biometrics grid save (manual-biometrics.store). */
final class StoreManualBiometricsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'cutoff_month' => ['required', 'integer', 'min:1', 'max:12'],
            'cutoff_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'cutoff_type' => ['required', 'in:first,second'],
            'employee_biometric_id' => ['required', 'integer', 'exists:employee_biometrics,id'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.work_date' => ['required', 'date'],
            'rows.*.time_in' => ['nullable', 'date_format:H:i'],
            'rows.*.time_out' => ['nullable', 'date_format:H:i'],
            'rows.*.remarks' => ['nullable', 'string', 'max:500'],
        ];
    }
}
