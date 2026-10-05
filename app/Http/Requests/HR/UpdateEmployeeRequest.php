<?php

declare(strict_types=1);

namespace App\Http\Requests\HR;

use App\Enums\EmployeeStatus;
use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('employees.update') === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $employeeId = $this->route('employee')?->getKey();

        return [
            'employee_id_permanent' => ['nullable', 'digits_between:1,10', Rule::unique('employees', 'employee_id_permanent')->ignore($employeeId)],
            'full_name' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::enum(EmployeeStatus::class)],
            'date_hired' => ['nullable', 'date'],
            'company' => ['required', Rule::in(Employee::COMPANIES)],
            'department_id' => ['nullable', 'exists:departments,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone_number' => ['nullable', 'digits:11', 'regex:/^[0-9]*$/'],
            'garage' => ['required', Rule::in(Employee::GARAGES)],
            'date_of_birth' => ['nullable', 'date'],
            'address_1' => ['nullable', 'string', 'max:255'],
            'address_2' => ['nullable', 'string', 'max:255'],
            'emergency_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact' => ['nullable', 'digits:11'],
            'remove_profile_picture' => ['nullable', 'boolean'],
            'profile_picture_cropped' => ['nullable', 'string'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
