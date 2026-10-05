<?php

declare(strict_types=1);

namespace App\Http\Resources\Scheduling;

use App\Models\EmployeeBiometric;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The record on `biometrics/employees/edit`: read-only CrossChex `employee` details and the
 * editable `values`. Load `company` and `hrEmployee` first.
 *
 * @mixin EmployeeBiometric
 */
final class BiometricEmployeeFormResource extends JsonResource
{
    /** @return array{employee: array<string, mixed>, values: array<string, mixed>} */
    public function toArray(Request $request): array
    {
        return [
            'employee' => [
                'id' => $this->id,
                'display_name' => $this->display_name,
                'display_employee_no' => $this->display_employee_no,
                'company_name' => $this->company?->name,
                'payroll_group_label' => $this->payroll_group_label,
                'legacy_id' => $this->legacy_biometric_employee_id
                    ?? $this->source_employee_id
                    ?? $this->source_crosschex_id
                    ?? $this->source_employee_no
                    ?? 'N/A',
                'source_employee_name' => $this->source_employee_name ?: 'N/A',
                'source_employee_no' => $this->source_employee_no ?: 'N/A',
                'source_crosschex_id' => $this->source_crosschex_id ?: 'N/A',
                'source_employee_id' => $this->source_employee_id ?: 'N/A',
                'crosschex_account' => $this->source_crosschex_account ?: 'N/A',
                'crosschex_account_name' => $this->source_crosschex_account_name ?: 'No account name',
                'device_name' => $this->device_name ?: 'N/A',
                'device_sn' => $this->device_sn ?: 'N/A',
                'last_check_time' => $this->last_check_time?->format('M d, Y h:i A') ?? 'N/A',
                'total_logs' => (int) ($this->total_logs ?? 0),
            ],
            'values' => [
                'biometric_company_id' => $this->biometric_company_id ? (string) $this->biometric_company_id : '',
                'group_name' => $this->group_name ? (string) $this->group_name : '',
                'employment_status' => $this->employment_status ?: EmployeeBiometric::STATUS_ACTIVE,
                'is_payroll_active' => (bool) ($this->is_payroll_active ?? true),
                'display_employee_no' => (string) ($this->display_employee_no ?? ''),
                'display_name' => (string) ($this->display_name ?? ''),
                'remarks' => (string) ($this->remarks ?? ''),
                'hr_employee_id' => $this->hrEmployee ? (string) $this->hrEmployee->id : '',
            ],
        ];
    }
}
