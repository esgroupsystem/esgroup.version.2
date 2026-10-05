<?php

declare(strict_types=1);

namespace App\Http\Resources\HR;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One row of the Employee List (`hr/employees/index`). Load `position` and `department` first.
 *
 * @mixin Employee
 */
final class EmployeeRowResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => trim((string) $this->full_name) ?: 'Unnamed Employee',
            'employee_no' => $this->employee_id_permanent ?: $this->employee_id,
            'age' => $this->age(),
            'position' => $this->position?->title ?? '—',
            'department' => $this->department?->name ?? '—',
            'company' => $this->company ?: '—',
            'garage' => $this->garage ?: '—',
            'email' => $this->email,
            'phone' => $this->phone_number,
            'hired' => $this->date_hired?->format('M d, Y') ?? '—',
            'tenure' => $this->tenure(),
            'status' => $this->status ?: 'Active',
            'show_url' => route('employees.staff.show', $this->id),
            'destroy_url' => route('employees.staff.destroy', $this->id),
        ];
    }
}
