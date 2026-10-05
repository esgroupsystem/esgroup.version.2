<?php

declare(strict_types=1);

namespace App\Http\Resources\HR;

use App\Models\EmployeeBiometric;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The linked biometric record on the HR profile's Biometrics card. Load `company` first.
 *
 * @mixin EmployeeBiometric
 */
final class BiometricSummaryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->payroll_display_name,
            'employee_no' => $this->display_employee_no ?: $this->source_employee_no,
            'company' => $this->company?->name,
            'active' => $this->employment_status === EmployeeBiometric::STATUS_ACTIVE,
            'last_check' => $this->last_check_time?->format('M d, Y h:i A'),
            'total_logs' => (int) ($this->total_logs ?? 0),
            'edit_url' => route('biometrics.employees.edit', $this->resource),
        ];
    }
}
