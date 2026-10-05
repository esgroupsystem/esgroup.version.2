<?php

declare(strict_types=1);

namespace App\Http\Resources\Payroll;

use App\Models\PayrollAttendanceAdjustment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The adjustment being edited in `payroll/adjustments/form` (the `adjustment` prop).
 *
 * @mixin PayrollAttendanceAdjustment
 */
final class AttendanceAdjustmentFormResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $time = fn (?string $value): string => $value ? substr($value, 0, 5) : '';

        return [
            'id' => $this->id,
            'employee_biometric_id' => $this->employee_biometric_id,
            'adjustment_type' => (string) $this->adjustment_type,
            'work_date' => $this->work_date?->toDateString() ?? '',
            'date_from' => $this->date_from?->toDateString() ?? '',
            'date_to' => $this->date_to?->toDateString() ?? '',
            'adjusted_time_in' => $time($this->adjusted_time_in),
            'adjusted_time_out' => $time($this->adjusted_time_out),
            'offset_sources' => collect($this->resolvedOffsetSources())
                ->map(fn (array $source): array => ['date' => $source['date'], 'hours' => number_format($source['minutes'] / 60, 2, '.', '')])
                ->values()
                ->all(),
            'amount' => $this->amount !== null ? (string) $this->amount : '',
            'is_paid' => (bool) $this->is_paid,
            'ignore_late' => (bool) $this->ignore_late,
            'ignore_undertime' => (bool) $this->ignore_undertime,
            'reason' => (string) ($this->reason ?? ''),
            'remarks' => (string) ($this->remarks ?? ''),
            'status' => $this->status,
            'is_locked' => (bool) $this->paid_payroll_id,
            'attachment' => $this->attachment_path ? [
                'name' => (string) $this->attachment_name,
                'url' => route('payroll-attendance-adjustments.attachment', $this->resource),
            ] : null,
        ];
    }
}
