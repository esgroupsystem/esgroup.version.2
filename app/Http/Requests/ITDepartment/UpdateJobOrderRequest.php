<?php

declare(strict_types=1);

namespace App\Http\Requests\ITDepartment;

use App\Support\IT\SeatNumbers;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateJobOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('tickets.update') ?? false;
    }

    /** The seat map sends "13,12" style lists; store them as "12, 13". */
    protected function prepareForValidation(): void
    {
        if ($this->has('job_sitNumber')) {
            $this->merge(['job_sitNumber' => SeatNumbers::normalize($this->input('job_sitNumber'))]);
        }
    }

    public function rules(): array
    {
        return [
            'job_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'job_datestart' => ['sometimes', 'nullable', 'date'],
            'job_time_start' => ['sometimes', 'nullable', 'date_format:H:i'],
            'job_time_end' => ['sometimes', 'nullable', 'date_format:H:i'],
            'direction' => ['sometimes', 'nullable', 'string', 'in:South Bound,North Bound'],
            'job_sitNumber' => ['sometimes', ...SeatNumbers::rules()],
            'job_remarks' => ['sometimes', 'nullable', 'string'],
            'driver_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'conductor_name' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
