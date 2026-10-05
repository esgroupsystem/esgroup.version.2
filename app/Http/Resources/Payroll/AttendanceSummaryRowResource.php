<?php

declare(strict_types=1);

namespace App\Http\Resources\Payroll;

use App\Models\DailyAttendanceSummary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * One day of one person on the Summary page (`payroll/attendance-summary/index`).
 *
 * @mixin DailyAttendanceSummary
 */
final class AttendanceSummaryRowResource extends JsonResource
{
    /** Statuses flagged for HR review. */
    private const CHECK = ['holiday_unpaid', 'no_schedule', 'incomplete_log'];

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $status = (string) ($this->attendance_status ?? '');
        $paidMinutes = max(60, (int) data_get($this->meta, 'paid_minutes_per_day', 480));
        $clockMinutes = max($paidMinutes, (int) data_get($this->meta, 'scheduled_clock_minutes', $paidMinutes + 60));
        $noSchedule = ! $this->hasConfiguredSchedule() || $status === 'no_schedule' || strtolower((string) $this->shift_name) === 'no schedule';
        $payableDays = (float) $this->payable_days;
        $time = fn ($value): ?string => $value ? Carbon::parse($value)->format('h:i A') : null;
        $hours = fn (float $value): string => number_format($value, $value == floor($value) ? 0 : 2);
        $label = fn (?string $value, string $empty): string => $value ? strtoupper(str_replace('_', ' ', $value)) : $empty;

        return [
            'id' => $this->id,
            'work_date' => $this->work_date ? Carbon::parse($this->work_date)->format('M d, Y') : null,
            'weekday' => $this->work_date ? Carbon::parse($this->work_date)->format('l') : null,
            'employee_name' => $this->payroll_display_name,
            'employee_no' => $this->employee_no,
            'biometric_employee_id' => $this->biometric_employee_id,
            'schedule' => [
                'kind' => $noSchedule ? 'none' : ($this->isFlexibleShift() ? 'flexible' : (($this->scheduled_time_in || $this->scheduled_time_out) ? 'fixed' : 'other')),
                'shift_name' => $this->shift_name,
                'time_in' => $time($this->scheduled_time_in),
                'time_out' => $time($this->scheduled_time_out),
                'paid_hours' => $hours($paidMinutes / 60),
                'clock_hours' => $hours($clockMinutes / 60),
                'has_lunch' => $clockMinutes > $paidMinutes,
                'grace_minutes' => (int) $this->grace_minutes,
                'status_label' => $label($this->schedule_status, 'NO STATUS'),
            ],
            'actual_in' => $this->actual_time_in ? ['time' => $time($this->actual_time_in), 'date' => Carbon::parse($this->actual_time_in)->format('M d')] : null,
            'actual_out' => $this->actual_time_out ? ['time' => $time($this->actual_time_out), 'date' => Carbon::parse($this->actual_time_out)->format('M d')] : null,
            'late_minutes' => (int) $this->late_minutes,
            'undertime_minutes' => (int) $this->undertime_minutes,
            'worked_minutes' => (int) $this->worked_minutes,
            'status' => $status,
            'status_label' => $label($status ?: 'N/A', 'N/A'),
            'needs_check' => in_array($status, self::CHECK, true),
            'day' => match (true) {
                (bool) $this->is_holiday => [
                    'kind' => 'holiday',
                    'name' => $this->holiday_name ?: 'Holiday',
                    'type' => $label($this->holiday_type, 'Type not set'),
                ],
                (bool) $this->is_rest_day => ['kind' => 'rest_day'],
                (bool) $this->is_leave => ['kind' => 'leave'],
                default => ['kind' => 'regular'],
            },
            'adjustment' => $this->has_adjustment ? [
                'type' => $label($this->adjustment_type, 'Manual Adjustment'),
                'remarks' => $this->adjustment_remarks ? Str::limit($this->adjustment_remarks, 60) : null,
            ] : null,
            'pay_label' => match (true) {
                $payableDays > 1 => 'Premium Pay',
                $payableDays == 1.0 => 'Full Pay',
                $payableDays > 0 => 'Partial Pay',
                default => 'No Pay',
            },
            'payable_days' => number_format($payableDays, 2),
            'payable_hours' => number_format((float) $this->payable_hours, 2),
            'remarks' => trim((string) $this->remarks) !== '' ? Str::limit(trim((string) $this->remarks), 180) : null,
        ];
    }
}
