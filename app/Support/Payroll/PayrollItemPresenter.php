<?php

declare(strict_types=1);

namespace App\Support\Payroll;

use App\Models\Payroll;
use App\Models\PayrollAttendanceAdjustment;
use App\Models\PayrollItem;
use App\Models\User;
use App\Support\PayrollEmployeeNameFormatter;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Props for the payroll item detail page (React). Carries over every value
 * and audit rule the former Blade page (payroll/items/show + partials)
 * computed, so the screen shows the same numbers.
 */
final class PayrollItemPresenter
{
    /**
     * @param  Collection<int, \App\Models\DailyAttendanceSummary>  $summaries
     */
    public function present(Payroll $payroll, PayrollItem $item, Collection $summaries, User $user): array
    {
        $meta = $item->meta ?? [];
        $divisor = data_get($meta, 'pay_architecture.monthly_divisor_meta');
        $monthlyRate = (float) data_get($divisor, 'monthly_rate', 0);
        $monthlyDivisor = (float) data_get($divisor, 'monthly_salary_divisor', 0);
        $cutoffPaidDays = (float) data_get($divisor, 'monthly_cutoff_paid_days', 0);
        $dailyRate = (float) data_get($divisor, 'daily_rate_display', 0);

        if ($dailyRate <= 0 && $monthlyRate > 0 && $monthlyDivisor > 0) {
            $dailyRate = round($monthlyRate / $monthlyDivisor, 2);
        }

        $isDraft = $payroll->status !== 'finalized';
        $restDay = data_get($meta, 'rest_day_qualification', []);
        $holidayBreakdown = data_get($meta, 'holiday_breakdown', []);
        $restDayBreakdown = data_get($meta, 'rest_day_breakdown', []);
        $allowance = data_get($meta, 'allowance', []);

        return [
            'payroll' => [
                'id' => $payroll->id,
                'payroll_number' => $payroll->payroll_number,
                'status' => $payroll->status,
                'cutoff_label' => $payroll->cutoff_label,
                'period_start' => $payroll->period_start?->toDateString(),
                'period_end' => $payroll->period_end?->toDateString(),
                'period_label' => $payroll->period_start?->format('M d, Y').' – '.$payroll->period_end?->format('M d, Y'),
            ],
            'item' => [
                'id' => $item->id,
                'name' => $item->payroll_display_name,
                'employee_no' => $item->employee_no,
                'biometric_employee_id' => $item->biometric_employee_id,
                'pay_model' => str_replace('_', ' ', ucfirst((string) data_get($meta, 'pay_architecture.money_model', $item->rate_type))),
                'regular_pay' => (float) $item->regular_pay,
                'late_deduction' => (float) $item->late_deduction,
                'undertime_deduction' => (float) $item->undertime_deduction,
                'absence_deduction' => (float) $item->absence_deduction,
                'holiday_pay' => (float) ($item->holiday_pay ?? 0),
                'rest_day_pay' => (float) ($item->rest_day_pay ?? 0),
                'leave_pay' => (float) ($item->leave_pay ?? 0),
                'overtime_pay' => (float) ($item->overtime_pay ?? 0),
                'night_differential_pay' => (float) ($item->night_differential_pay ?? 0),
                'other_additions' => (float) ($item->other_additions ?? 0),
                'other_deductions' => (float) $item->other_deductions,
                'government' => (float) $item->total_employee_government_deductions,
                'sss_employee' => (float) $item->sss_employee,
                'philhealth_employee' => (float) $item->philhealth_employee,
                'pagibig_employee' => (float) $item->pagibig_employee,
                'gross_pay' => (float) $item->gross_pay,
                'net_pay' => (float) $item->net_pay,
                'payable_days' => (float) $item->total_payable_days,
                'payable_hours' => (float) $item->total_payable_hours,
                'overtime_hours' => round(((int) ($item->total_overtime_minutes ?? 0)) / 60, 2),
                'night_differential_hours' => round(((int) ($item->total_night_differential_minutes ?? 0)) / 60, 2),
                'salary_adjustment_addition' => (float) data_get($meta, 'manual_adjustments.additions', 0),
                'salary_adjustment_deduction' => (float) data_get($meta, 'manual_adjustments.deductions', 0),
                'attendance_deducted_from_gross' => (bool) data_get($meta, 'attendance_deductions_are_deducted_from_monthly_base', false),
                'monthly_formula' => $divisor && $monthlyRate > 0 && $monthlyDivisor > 0 && $cutoffPaidDays > 0
                    ? ['monthly_rate' => $monthlyRate, 'divisor' => $monthlyDivisor, 'paid_days' => $cutoffPaidDays]
                    : null,
                'daily_rate' => $dailyRate > 0 ? $dailyRate : null,
                'government_schedule' => is_array(data_get($meta, 'government_schedule'))
                    ? 'Employee profile / config'
                    : (string) data_get($meta, 'government_schedule', 'Default'),
            ],
            'attendanceRates' => data_get($meta, 'attendance_deductions') ? [
                'late_minutes' => (float) data_get($meta, 'attendance_deductions.late_minutes', 0),
                'late_rate' => (float) data_get($meta, 'attendance_deductions.late_rate_per_minute', 0),
                'undertime_minutes' => (float) data_get($meta, 'attendance_deductions.undertime_minutes', 0),
                'undertime_rate' => (float) data_get($meta, 'attendance_deductions.undertime_rate_per_minute', 0),
                'absent_days' => (float) data_get($meta, 'attendance_deductions.absent_days', 0),
                'absence_rate' => (float) data_get($meta, 'attendance_deductions.absence_rate_per_day', 0),
            ] : null,
            'restDay' => $restDay ? [
                'qualified' => (bool) data_get($restDay, 'qualified', true),
                'by_exception' => (bool) data_get($restDay, 'qualified_by_exception', false),
                'valid_log_days' => (int) data_get($restDay, 'valid_log_days', 0),
                'minimum_valid_log_days' => (int) data_get($restDay, 'minimum_valid_log_days', 3),
                'unpaid_count' => (int) data_get($restDay, 'unpaid_rest_day_count', 0),
                'deduction' => (float) data_get($restDay, 'deduction', 0),
            ] : null,
            'attendance' => [
                'worked_hours' => round($summaries->sum('worked_minutes') / 60, 2),
                'late_minutes' => (int) $summaries->sum('late_minutes'),
                'undertime_minutes' => (int) $summaries->sum('undertime_minutes'),
                'holiday_worked' => (int) data_get($holidayBreakdown, 'worked_days', data_get($holidayBreakdown, 'total_holiday_worked', 0)),
                'rest_day_worked' => (int) data_get($restDayBreakdown, 'worked_days', data_get($restDayBreakdown, 'total_rest_day_worked', 0)),
            ],
            'allowance' => [
                'monthly_allowance' => (float) data_get($allowance, 'monthly_allowance', 0),
                'allowance_schedule' => str_replace('_', ' ', (string) data_get($allowance, 'allowance_release_schedule', '—')),
                'regular_per_cutoff' => (float) data_get($allowance, 'regular_per_cutoff', 0),
                'monthly_sim_load' => (float) data_get($allowance, 'monthly_sim_load_allowance', 0),
                'sim_load_schedule' => str_replace('_', ' ', (string) data_get($allowance, 'sim_load_release_schedule', '—')),
                'sim_load_per_cutoff' => (float) data_get($allowance, 'sim_load_per_cutoff', 0),
                'total_per_cutoff' => (float) data_get($allowance, 'allowance_per_cutoff', 0),
            ],
            'salaryDeductions' => collect(data_get($meta, 'salary_deductions', []))->map(fn ($row): array => [
                'name' => (string) data_get($row, 'name', 'Deduction'),
                'schedule' => str_replace('_', ' ', (string) data_get($row, 'deduction_schedule', '—')),
                'balance_after' => data_get($row, 'balance_after') !== null ? (float) data_get($row, 'balance_after') : null,
                'remarks' => data_get($row, 'remarks'),
                'amount' => (float) data_get($row, 'amount', 0),
            ])->values(),
            'adjustmentTags' => collect(data_get($meta, 'adjustment_tags', []))->map(function ($tag): array {
                $date = data_get($tag, 'date', data_get($tag, 'work_date', data_get($tag, 'effective_date')));

                return [
                    'label' => (string) data_get($tag, 'label', data_get($tag, 'type', 'Adjustment')),
                    'paid_this_cutoff' => (bool) data_get($tag, 'paid_this_cutoff', false),
                    'effect' => (string) data_get($tag, 'effect', 'Payroll adjustment'),
                    'date' => $date ? Carbon::parse($date)->format('M d, Y') : null,
                    'amount' => (float) data_get($tag, 'amount', 0),
                    'reason' => data_get($tag, 'reason'),
                ];
            })->values(),
            'settlement' => $this->settlement($payroll, $item, $user),
            'auditRows' => $summaries->map(fn ($row): array => $this->auditRow($row))->values(),
            'fileAdjustment' => $isDraft && $user->can('create', PayrollAttendanceAdjustment::class) ? [
                'people' => [[
                    'employee_biometric_id' => (int) $item->employee_biometric_id,
                    'biometric_employee_id' => $item->biometric_employee_id,
                    'employee_no' => $item->employee_no,
                    'employee_name' => (string) $item->employee_name,
                    'display_name' => PayrollEmployeeNameFormatter::display($item->employee_name),
                    'crosschex_id' => $item->crosschex_id,
                    'group_name' => null,
                ]],
                // Typhoon/Disaster applies to all employees, not a single filing.
                'types' => collect(PayrollAttendanceAdjustment::TYPES)
                    ->reject(fn ($label, $key): bool => PayrollAttendanceAdjustment::isTyphoonDisasterType($key))
                    ->all(),
                'urls' => [
                    'submit' => route('payroll-attendance-adjustments.store'),
                    'offsetProof' => route('payroll-attendance-adjustments.offset-proof'),
                ],
            ] : null,
            'can' => [
                'recompute' => $isDraft && $user->can('create', Payroll::class),
            ],
            'urls' => [
                'back' => route('payroll.show', $payroll),
                'recompute' => route('payroll.items.recompute', [$payroll, $item]),
                'settlement' => route('payroll.items.benefit-settlement.store', [$payroll, $item]),
            ],
        ];
    }

    private function settlement(Payroll $payroll, PayrollItem $item, User $user): ?array
    {
        if ((string) $payroll->cutoff_type !== 'first') {
            return null;
        }

        $settlement = $item->benefitSettlement;
        $meta = data_get($item->meta, 'government_settlement', []);
        $caps = (array) data_get($meta, 'manual_reimbursement_caps', []);

        return [
            'mode_label' => $settlement ? ucwords(str_replace('_', ' ', $settlement->mode)) : 'Auto Cap (Default)',
            'unrecovered' => (float) data_get($meta, 'employee_share_unrecovered', 0),
            'lines' => collect([
                'SSS' => ['sss_employee', 'sss_employee_statutory_due'],
                'PhilHealth' => ['philhealth_employee', 'philhealth_employee_statutory_due'],
                'Pag-IBIG' => ['pagibig_employee', 'pagibig_employee_statutory_due'],
            ])->map(fn (array $config, string $label): array => [
                'label' => $label,
                'monthly_due' => (float) data_get($meta, $config[1], 0),
                'this_cutoff' => (float) $item->{$config[0]},
            ])->values(),
            'can_edit' => $payroll->status === 'draft' && $user->can('payroll-benefit-settlements.manage'),
            'values' => [
                'mode' => $settlement?->mode ?? 'auto_cap',
                'sss_employee_reimbursement' => (string) ($settlement?->sss_employee_reimbursement ?? 0),
                'philhealth_employee_reimbursement' => (string) ($settlement?->philhealth_employee_reimbursement ?? 0),
                'pagibig_employee_reimbursement' => (string) ($settlement?->pagibig_employee_reimbursement ?? 0),
                'reason' => (string) ($settlement?->reason ?? ''),
            ],
            'caps' => [
                'sss' => (float) ($caps['sss'] ?? 0),
                'philhealth' => (float) ($caps['philhealth'] ?? 0),
                'pagibig' => (float) ($caps['pagibig'] ?? 0),
            ],
        ];
    }

    /**
     * Attendance audit row with the issue rules from the former
     * attendance-audit-table partial.
     */
    private function auditRow($row): array
    {
        $status = Str::of($row->attendance_status ?: 'n/a')->lower()->replace([' ', '-'], '_')->toString();
        $late = (int) ($row->late_minutes ?? 0);
        $undertime = (int) ($row->undertime_minutes ?? 0);
        $isFlexible = $row->isFlexibleShift();
        $hasSchedule = $row->hasConfiguredSchedule();
        $paidMinutes = $row->paidMinutesPerDay();
        $clockMinutes = $row->scheduledClockMinutes();
        $hasIn = ! empty($row->actual_time_in);
        $hasOut = ! empty($row->actual_time_out);
        $expectedToLog = ! in_array($status, ['rest_day', 'rest_day_paid', 'holiday', 'paid_holiday', 'leave', 'paid_leave', 'day_off'], true);

        $issues = [];
        if (in_array($status, ['absent', 'holiday_unpaid'], true)) {
            $issues[] = ['label' => 'Absent / Unpaid', 'tone' => 'danger'];
        }
        if ($status === 'incomplete_log') {
            $issues[] = ['label' => 'Incomplete Log', 'tone' => 'danger'];
        }
        if ($status === 'no_schedule') {
            $issues[] = ['label' => 'No Schedule', 'tone' => 'danger'];
        }
        if ($expectedToLog && ! $hasSchedule) {
            $issues[] = ['label' => 'Missing Schedule', 'tone' => 'danger'];
        }
        if ($expectedToLog && $hasIn !== $hasOut) {
            $issues[] = ['label' => 'Missing Pair Log', 'tone' => 'danger'];
        }
        if ($late > 0) {
            $issues[] = ['label' => "Late {$late}m", 'tone' => 'warning'];
        }
        if ($undertime > 0) {
            $issues[] = ['label' => "Undertime {$undertime}m", 'tone' => 'warning'];
        }
        if ($status === 'half_day') {
            $issues[] = ['label' => 'Half Day', 'tone' => 'warning'];
        }

        $tones = array_column($issues, 'tone');
        $time = fn ($value): string => $value ? Carbon::parse($value)->format('h:i A') : '—';

        return [
            'date' => $row->work_date ? Carbon::parse($row->work_date)->format('M d, Y') : '—',
            'weekday' => $row->work_date ? Carbon::parse($row->work_date)->format('D') : '—',
            'status' => $status,
            'status_label' => Str::of($row->attendance_status ?: 'N/A')->replace('_', ' ')->title()->toString(),
            'issues' => $issues,
            'severity' => match (true) {
                in_array('danger', $tones, true) => 'danger',
                in_array('warning', $tones, true) => 'warning',
                in_array($status, ['holiday_worked', 'rest_day_worked'], true) => 'info',
                default => 'clean',
            },
            'is_flexible' => $isFlexible,
            'paid_hours' => round($paidMinutes / 60, 2),
            'clock_hours' => round($clockMinutes / 60, 2),
            'has_lunch' => $clockMinutes > $paidMinutes,
            'scheduled_in' => $time($row->scheduled_time_in),
            'scheduled_out' => $time($row->scheduled_time_out),
            'actual_in' => $time($row->actual_time_in),
            'actual_out' => $time($row->actual_time_out),
            'late_minutes' => $late,
            'undertime_minutes' => $undertime,
            'worked_hours' => round(((int) ($row->worked_minutes ?? 0)) / 60, 2),
            'overtime_hours' => round(((int) ($row->overtime_minutes ?? 0)) / 60, 2),
            'payable_days' => (float) ($row->payable_days ?? 0),
            'payable_hours' => (float) ($row->payable_hours ?? 0),
            'remarks' => filled($row->remarks) ? (string) $row->remarks : null,
        ];
    }
}
