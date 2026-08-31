<?php

declare(strict_types=1);

namespace App\Http\Requests\ITDepartment;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateJobOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('tickets.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'job_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'job_datestart' => ['sometimes', 'nullable', 'date'],
            'job_time_start' => ['sometimes', 'nullable', 'date_format:H:i'],
            'job_time_end' => ['sometimes', 'nullable', 'date_format:H:i'],
            'direction' => ['sometimes', 'nullable', 'string', 'in:South Bound,North Bound'],
            'job_sitNumber' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:60'],
            'job_remarks' => ['sometimes', 'nullable', 'string'],
            'driver_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'conductor_name' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
