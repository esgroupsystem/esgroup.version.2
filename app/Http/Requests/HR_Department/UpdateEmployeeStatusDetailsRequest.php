<?php

declare(strict_types=1);

namespace App\Http\Requests\HR_Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateEmployeeStatusDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('employees.update') === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'date_resigned' => ['nullable', 'date'],
            'type_of_status' => ['nullable', Rule::in(['Resigned', 'Terminated', 'Terminated due to AWOL', 'Retrenched'])],
            'last_duty' => ['nullable', 'date'],
            'clearance_date' => ['nullable', 'date'],
            'last_pay_status' => ['nullable', 'in:Not released,Released'],
            'last_pay_date' => ['nullable', 'date'],
        ];
    }
}
