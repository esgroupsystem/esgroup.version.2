<?php

declare(strict_types=1);

namespace App\Http\Requests\HR_Department;

use Illuminate\Foundation\Http\FormRequest;

final class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('employees.create') === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'employee_id_permanent' => ['nullable', 'digits_between:1,10', 'unique:employees,employee_id_permanent'],
            'full_name' => ['required', 'string', 'max:255'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone_number' => ['nullable', 'digits:11', 'regex:/^[0-9]*$/'],
            'company' => ['required', 'in:Jell Transport,ES Transport,Earthstar Transport,Kellen Transport'],
            'garage' => ['required', 'in:Mirasol,Balintawak,Gonzales'],
        ];
    }
}
