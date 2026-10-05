<?php

declare(strict_types=1);

namespace App\Http\Requests\HR;

use App\Models\Employee;
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
            'type_of_status' => ['nullable', Rule::in(Employee::STATUS_TYPES)],
            'last_duty' => ['nullable', 'date'],
            'clearance_date' => ['nullable', 'date'],
            'last_pay_status' => ['nullable', Rule::in(Employee::LAST_PAY_STATUSES)],
            'last_pay_date' => ['nullable', 'date'],
        ];
    }
}
