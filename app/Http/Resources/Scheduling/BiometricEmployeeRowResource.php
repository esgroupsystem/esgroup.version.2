<?php

declare(strict_types=1);

namespace App\Http\Resources\Scheduling;

use App\Models\EmployeeBiometric;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One row of Scheduling & Rates → Employees (`biometrics/employees/index`).
 * Load `company` and `hrEmployee` first.
 *
 * @mixin EmployeeBiometric
 */
final class BiometricEmployeeRowResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $isActive = $this->employment_status === EmployeeBiometric::STATUS_ACTIVE;

        return [
            'id' => $this->id,
            'display_name' => $this->payroll_display_name,
            'display_no' => $this->display_employee_no ?: 'N/A',
            'source_no' => $this->source_employee_no ?: 'N/A',
            'group_label' => EmployeeBiometric::GROUP_LABELS[(int) $this->group_name] ?? null,
            'company' => $this->company?->name,
            'active' => $isActive,
            'payroll_included' => $isActive && (bool) ($this->is_payroll_active ?? true),
            'device_name' => $this->device_name ?: 'N/A',
            'device_sn' => $this->device_sn ?: 'N/A',
            'last_check_date' => $this->last_check_time?->format('M d, Y'),
            'last_check_time' => $this->last_check_time?->format('h:i A'),
            'total_logs' => (int) ($this->total_logs ?? 0),
            'hr_employee' => $this->hrEmployee ? [
                'name' => (string) $this->hrEmployee->full_name,
                'url' => route('employees.staff.show', $this->hrEmployee->id),
            ] : null,
            'edit_url' => route('biometrics.employees.edit', $this->resource),
        ];
    }
}
