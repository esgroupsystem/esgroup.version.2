<?php

declare(strict_types=1);

namespace App\Http\Resources\Payroll;

use App\Models\PayrollAttendanceAdjustment as Adjustment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/**
 * One row of the Adjustment list (`payroll/adjustments/index`), including who approved /
 * rejected it and the approve / reject wording. Load encoder, employeeBiometric, approver,
 * rejector and paidPayroll first.
 *
 * @mixin Adjustment
 */
final class AttendanceAdjustmentRowResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $type = (string) $this->adjustment_type;
        $isOffset = $type === Adjustment::TYPE_OFFSET;
        $isOvertime = $type === Adjustment::TYPE_OVERTIME;
        $isSalary = $type === Adjustment::TYPE_CASH_ADJUSTMENT;
        $approved = $this->status === Adjustment::STATUS_APPROVED;
        $noun = $isOvertime ? 'OT' : ($isSalary ? 'Salary Adjustment' : 'Offset');

        return [
            'id' => $this->id,
            'is_disaster' => $this->isGlobalDisasterAdjustment(),
            'disaster_hours' => Adjustment::typhoonDisasterRequiredHours($type),
            'employee' => [
                'name' => $this->payroll_display_name,
                'employee_no' => $this->employee_no,
                'employee_biometric_id' => $this->employee_biometric_id,
                'biometric_employee_id' => $this->biometric_employee_id,
            ],
            'type' => $type,
            'type_label' => $this->type_label,
            'status' => $this->status ?: Adjustment::STATUS_APPROVED,
            'period_label' => $this->period_label,
            'day_type_label' => $this->adjusted_day_type ? Str::headline($this->adjusted_day_type) : 'Standard day',
            'adjusted_time_label' => $this->adjusted_time_label,
            'offset' => $isOffset ? [
                'proof_label' => $this->offset_proof_label,
                'approved_hours' => $this->approved_minutes ? round($this->approved_minutes / 60, 2) : null,
                'paid_payroll_number' => $this->paidPayroll?->payroll_number,
            ] : null,
            'effect' => match (true) {
                $isOffset => 'Comp Time Credit',
                $isOvertime => $approved ? 'OT Pay Authorized' : 'No OT Pay Yet',
                $type === Adjustment::TYPE_HOLIDAY_WORK => 'Holiday Premium',
                (bool) $this->is_paid => 'Paid Attendance',
                default => 'Attendance Rule Only',
            },
            'effect_positive' => ! ($isOvertime && ! $approved),
            'ignore_late' => (bool) $this->ignore_late,
            'ignore_undertime' => (bool) $this->ignore_undertime,
            'encoder_name' => $this->encoder?->name,
            'decision' => match (true) {
                $approved && $this->approved_by => [
                    'by' => $this->approver?->full_name ?: ($this->approver?->name ?: 'Unknown user'),
                    'at' => $this->approved_at?->timezone('Asia/Manila')->format('M d, Y h:i A'),
                ],
                $this->status === Adjustment::STATUS_REJECTED && $this->rejected_by => [
                    'by' => $this->rejector?->full_name ?: ($this->rejector?->name ?: 'Unknown user'),
                    'at' => $this->rejected_at?->timezone('Asia/Manila')->format('M d, Y h:i A'),
                    'reason' => $this->rejection_reason,
                ],
                default => null,
            },
            'attachment' => $this->attachment_path ? [
                'name' => (string) $this->attachment_name,
                'url' => route('payroll-attendance-adjustments.attachment', $this->resource),
            ] : null,
            'encoded_at' => $this->encoded_at?->timezone('Asia/Manila')->format('M d, Y h:i A'),
            'can_decide' => $this->isApprovalRequired() && $this->status === Adjustment::STATUS_PENDING,
            'approve_title' => "Approve {$noun}",
            'reject_title' => "Reject {$noun}",
            'approve_confirm' => match (true) {
                $isOvertime => 'Approve this overtime adjustment for payroll payment?',
                $isSalary => 'Approve this Salary Adjustment of '.$this->adjusted_time_label.'?',
                default => 'Approve this Offset credit and apply it to the target attendance date?',
            },
            'reject_confirm' => match (true) {
                $isOvertime => 'Reject this overtime adjustment? It will not be paid.',
                $isSalary => 'Reject this Salary Adjustment? It will not be applied to payroll.',
                default => 'Reject this Offset request? No compensatory credit will be applied.',
            },
            'urls' => [
                'edit' => route('payroll-attendance-adjustments.edit', $this->resource),
                'destroy' => route('payroll-attendance-adjustments.destroy', $this->resource),
                'approve' => route('payroll-attendance-adjustments.approve', $this->resource),
                'reject' => route('payroll-attendance-adjustments.reject', $this->resource),
            ],
        ];
    }
}
