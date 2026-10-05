<?php

declare(strict_types=1);

namespace App\Http\Resources\Payroll;

use App\Models\PayrollAuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One entry of Payroll Transaction Logs (`payroll/audit-logs/index`), with old / new values as
 * pretty JSON. Load user, payroll and employeeBiometric first.
 *
 * @mixin PayrollAuditLog
 */
final class PayrollAuditLogResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $json = fn ($value): ?string => $value === null || $value === []
            ? null
            : json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $person = $this->employeeBiometric;

        return [
            'id' => $this->id,
            'date' => $this->created_at?->timezone('Asia/Manila')->format('M d, Y'),
            'time' => $this->created_at?->timezone('Asia/Manila')->format('h:i:s A'),
            'user_name' => $this->user?->full_name ?: ($this->user?->username ?: 'System / Console'),
            'user_email' => $this->user?->email,
            'module' => self::headline($this->module),
            'action' => $this->action,
            'action_label' => self::headline($this->action),
            'payroll_number' => $this->payroll?->payroll_number,
            'employee_name' => $person?->payroll_display_name,
            'employee_no' => $person
                ? ($person->effective_employee_no ?? 'Bio ID: '.$this->employee_biometric_id)
                : ($this->employee_biometric_id ? 'Bio ID: '.$this->employee_biometric_id : null),
            'garage_group' => $this->garage_group ?: 'N/A',
            'description' => $this->description ?: 'Payroll-related change',
            'request_id' => $this->request_id,
            'ip_address' => $this->ip_address ?: 'System / Console',
            'user_agent' => $this->user_agent ?: 'N/A',
            'old_values' => $json($this->old_values),
            'new_values' => $json($this->new_values),
            'context' => $json($this->context),
        ];
    }

    /** "employee_sync_completed" → "Employee Sync Completed". */
    public static function headline(?string $value): string
    {
        return ucwords(str_replace('_', ' ', (string) $value));
    }
}
