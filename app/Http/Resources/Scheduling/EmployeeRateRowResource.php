<?php

declare(strict_types=1);

namespace App\Http\Resources\Scheduling;

use App\Models\PayrollEmployeeSalary;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One row of Employee Rates (`payroll/employee-salaries/index`). Needs the `payroll_preview`
 * attribute from EmployeeRateService::paginate(); load `employeeBiometric` first.
 *
 * @mixin PayrollEmployeeSalary
 */
final class EmployeeRateRowResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $preview = $this->payroll_preview ?? [];
        $person = $this->employeeBiometric;

        return [
            'id' => $this->id,
            'name' => $this->payroll_display_name,
            'employee_no' => $this->employee_no,
            'employee_biometric_id' => $this->employee_biometric_id,
            'rate_type' => $this->rate_type,
            'paid_day_off' => (bool) ($this->paid_day_off ?? true),
            'basic_salary' => (float) $this->basic_salary,
            'ot_rate_per_hour' => (float) $this->ot_rate_per_hour,
            'late_deduction_per_minute' => (float) $this->late_deduction_per_minute,
            'government' => [
                ['label' => 'SSS', 'schedule' => $this->sss_contribution_cutoff, 'amount' => (float) data_get($preview, 'monthly_government.sss', 0)],
                ['label' => 'Pag-IBIG', 'schedule' => $this->pagibig_contribution_cutoff, 'amount' => (float) data_get($preview, 'monthly_government.pagibig', 0)],
                ['label' => 'PhilHealth', 'schedule' => $this->philhealth_contribution_cutoff, 'amount' => (float) data_get($preview, 'monthly_government.philhealth', 0)],
            ],
            'allowances' => [
                ['label' => 'Regular', 'schedule' => $this->allowance_release_schedule, 'amount' => (float) $this->allowance],
                ['label' => 'SIM Load', 'schedule' => $this->sim_load_release_schedule, 'amount' => (float) $this->sim_load_allowance],
            ],
            'is_active' => (bool) $this->is_active,
            'bio_included' => ($person?->employment_status ?? 'active') === 'active' && ($person?->is_payroll_active ?? true),
            'urls' => [
                'edit' => route('payroll-employee-salaries.edit', $this->resource),
                'destroy' => route('payroll-employee-salaries.destroy', $this->resource),
            ],
        ];
    }
}
