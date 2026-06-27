<?php

namespace App\Services\Payroll;

use App\Models\DailyAttendanceSummary;
use App\Models\Payroll;
use App\Models\PayrollAttendanceAdjustment;
use App\Models\PayrollItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class PayrollPayslipService
{
    public function build(Payroll $payroll): array
    {
        $payroll->load([
            'items' => fn ($query) => $query->orderBy('employee_name'),
            'generator',
            'finalizer',
        ]);

        $startDate = Carbon::parse($payroll->period_start, 'Asia/Manila')->startOfDay();
        $endDate = Carbon::parse($payroll->period_end, 'Asia/Manila')->endOfDay();

        $summaryGroups = DailyAttendanceSummary::query()
            ->whereBetween('work_date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ])
            ->orderBy('employee_name')
            ->orderBy('work_date')
            ->get()
            ->groupBy(fn ($row): string => $this->employeeGroupKey($row));

        $adjustmentGroups = $this->adjustmentGroups($startDate, $endDate);

        $slips = $payroll->items
            ->map(fn (PayrollItem $item): array => $this->makeSlip(
                $payroll,
                $item,
                $summaryGroups,
                $adjustmentGroups
            ))
            ->values();

        return [
            'payroll' => $payroll,
            'slipPages' => $slips->chunk(4),
            'periodLabel' => $startDate->format('M d').' - '.$endDate->format('M d, Y'),
            'periodEnding' => $endDate->format('F d, Y'),
            'printedAt' => now('Asia/Manila')->format('F d, Y h:i A'),
        ];
    }

    protected function makeSlip(
        Payroll $payroll,
        PayrollItem $item,
        Collection $summaryGroups,
        Collection $adjustmentGroups
    ): array {
        $employeeKey = $this->employeeGroupKey($item);
        $rows = $summaryGroups->get($employeeKey, collect())->values();

        $attendanceRows = $rows
            ->map(function ($row) use ($adjustmentGroups): array {
                $dateKey = $this->employeeDateKey($row);
                $adjustments = $adjustmentGroups->get($dateKey, collect());

                return $this->dailyRow($row, $adjustments);
            })
            ->values();

        return [
            'item' => $item,
            'employee_name' => $item->employee_name ?: 'Unknown Employee',
            'employee_no' => $item->employee_no ?: '—',
            'biometric_employee_id' => $item->biometric_employee_id ?: '—',
            'period_ending' => Carbon::parse($payroll->period_end)->format('F d, Y'),
            'earnings' => $this->earnings($item),
            'deductions' => $this->deductions($item),
            'summary' => $this->summary($item, $rows),
            'attendanceRows' => $attendanceRows,
            'gross_pay' => round((float) $item->gross_pay, 2),
            'net_pay' => round((float) $item->net_pay, 2),
            'total_deductions' => round(max(0, (float) $item->gross_pay - (float) $item->net_pay), 2),
        ];
    }

    protected function earnings(PayrollItem $item): array
    {
        $allowance = round((float) data_get($item->meta, 'allowance.allowance_per_cutoff', 0), 2);
        $adjustment = round((float) data_get($item->meta, 'manual_adjustments.additions', 0), 2);

        if ($allowance <= 0 && $adjustment <= 0 && (float) $item->other_additions > 0) {
            $allowance = round((float) $item->other_additions, 2);
        }

        return [
            [
                'label' => 'Regular Days Worked',
                'unit' => number_format((float) $item->total_scheduled_days, 2),
                'amount' => round((float) $item->regular_pay, 2),
            ],
            [
                'label' => 'Overtime (Reg.)',
                'unit' => number_format(((float) $item->total_overtime_minutes) / 60, 2),
                'amount' => round((float) $item->overtime_pay, 2),
            ],
            [
                'label' => 'Overtime (RD. or Sp. Day)',
                'unit' => '0.00',
                'amount' => 0.00,
            ],
            [
                'label' => 'Overtime (Reg. Holiday)',
                'unit' => '0.00',
                'amount' => 0.00,
            ],
            [
                'label' => 'Overtime (Reg. Hday on RD)',
                'unit' => '0.00',
                'amount' => 0.00,
            ],
            [
                'label' => 'Night Shift Hours',
                'unit' => '0.00',
                'amount' => 0.00,
            ],
            [
                'label' => 'Restday/Special Holiday',
                'unit' => number_format((float) $item->total_rest_day_worked, 2),
                'amount' => round((float) $item->rest_day_pay, 2),
            ],
            [
                'label' => 'Holidays Worked',
                'unit' => number_format((float) $item->total_holiday_worked, 2),
                'amount' => round((float) $item->holiday_pay, 2),
            ],
            [
                'label' => 'COLA',
                'unit' => '0.00',
                'amount' => 0.00,
            ],
            [
                'label' => 'Allowance',
                'unit' => '',
                'amount' => $allowance,
            ],
            [
                'label' => 'Adjustment',
                'unit' => '',
                'amount' => $adjustment,
            ],
        ];
    }

    protected function deductions(PayrollItem $item): array
    {
        $salaryDeductions = collect(data_get($item->meta, 'salary_deductions', []));

        $pagibigLoan = $this->sumDeductions($salaryDeductions, ['pagibig loan', 'pag-ibig loan']);
        $sssLoan = $this->sumDeductions($salaryDeductions, ['sss loan']);
        $uniform = $this->sumDeductions($salaryDeductions, ['uniform']);
        $vale = $this->sumDeductions($salaryDeductions, ['vale']);
        $sunCellular = $this->sumDeductions($salaryDeductions, ['sun', 'cellular', 'sim', 'load']);

        $lines = [
            [
                'label' => 'Pag-Ibig',
                'amount' => round((float) $item->pagibig_employee, 2),
            ],
            [
                'label' => 'Pag-Ibig Loan',
                'amount' => $pagibigLoan,
            ],
            [
                'label' => 'SSS',
                'amount' => round((float) $item->sss_employee, 2),
            ],
            [
                'label' => 'SSS Loan',
                'amount' => $sssLoan,
            ],
            [
                'label' => 'Philhealth',
                'amount' => round((float) $item->philhealth_employee, 2),
            ],
            [
                'label' => 'Uniform',
                'amount' => $uniform,
            ],
            [
                'label' => 'Vale',
                'amount' => $vale,
            ],
            [
                'label' => 'Sun Cellular',
                'amount' => $sunCellular,
            ],
            [
                'label' => 'Withholding Tax',
                'amount' => 0.00,
            ],
        ];

        $totalDeductions = round(max(0, (float) $item->gross_pay - (float) $item->net_pay), 2);
        $shownDeductions = round(collect($lines)->sum('amount'), 2);
        $otherDeductions = round(max(0, $totalDeductions - $shownDeductions), 2);

        if ($otherDeductions > 0) {
            array_splice($lines, 8, 0, [[
                'label' => 'Other Deductions',
                'amount' => $otherDeductions,
            ]]);
        }

        return $lines;
    }

    protected function summary(PayrollItem $item, Collection $rows): array
    {
        if ($rows->isEmpty()) {
            return [
                'absent' => number_format((float) $item->total_absent_days, 0),
                'review' => '0',
                'holiday_paid' => '0',
                'holiday_unpaid' => '0',
                'late_minutes' => number_format((float) $item->total_late_minutes, 0),
                'undertime_minutes' => number_format((float) $item->total_undertime_minutes, 0),
                'pay_units' => number_format((float) $item->total_payable_days, 2),
            ];
        }

        $holidayRows = $rows->filter(fn ($row): bool => $this->isHolidayRow($row));

        return [
            'absent' => number_format($rows->filter(fn ($row): bool => $this->isAbsent($row))->count()),
            'review' => number_format($rows->filter(fn ($row): bool => $this->needsReview($row))->count()),
            'holiday_paid' => number_format($holidayRows->filter(fn ($row): bool => $this->payUnits($row) > 0)->count()),
            'holiday_unpaid' => number_format($holidayRows->filter(fn ($row): bool => $this->payUnits($row) <= 0)->count()),
            'late_minutes' => number_format((float) $rows->sum(fn ($row): int => (int) ($row->late_minutes ?? 0)), 0),
            'undertime_minutes' => number_format((float) $rows->sum(fn ($row): int => (int) ($row->undertime_minutes ?? 0)), 0),
            'pay_units' => number_format((float) $rows->sum(fn ($row): float => $this->payUnits($row)), 2),
        ];
    }

    protected function dailyRow(object $row, Collection $adjustments): array
    {
        $date = Carbon::parse($row->work_date);
        $payUnits = $this->payUnits($row);
        $lateMinutes = (int) ($row->late_minutes ?? 0);
        $undertimeMinutes = (int) ($row->undertime_minutes ?? 0);

        return [
            'date' => $date->format('m/d'),
            'day' => $date->format('D'),
            'status' => $this->shortStatus($row, $adjustments, $payUnits, $lateMinutes, $undertimeMinutes),
            'pay_units' => number_format($payUnits, 2),
        ];
    }

    protected function shortStatus(
        object $row,
        Collection $adjustments,
        float $payUnits,
        int $lateMinutes,
        int $undertimeMinutes
    ): string {
        $adjustmentLabel = $this->adjustmentLabel($adjustments);

        if ($adjustmentLabel !== null) {
            return $adjustmentLabel;
        }

        if ($this->isAbsent($row)) {
            return 'Absent';
        }

        if ($this->needsReview($row)) {
            return 'Review';
        }

        if ($this->isHolidayRow($row)) {
            if ($this->hasWorkRecord($row) && $payUnits >= 2) {
                return 'Double Pay';
            }

            if ($this->hasWorkRecord($row)) {
                return 'Hol Work';
            }

            return $payUnits > 0 ? 'Holiday' : 'Hol Unpaid';
        }

        if ($this->isRestDayWorked($row)) {
            return 'RD Work';
        }

        if ($this->isRestDayRow($row)) {
            return 'Rest';
        }

        if ($lateMinutes > 0 && $undertimeMinutes > 0) {
            return 'Late/UT';
        }

        if ($lateMinutes > 0) {
            return 'Late';
        }

        if ($undertimeMinutes > 0) {
            return 'UT';
        }

        if ($payUnits >= 2) {
            return 'Double Pay';
        }

        return $payUnits > 0 ? 'Present' : '—';
    }

    protected function adjustmentLabel(Collection $adjustments): ?string
    {
        if ($adjustments->isEmpty()) {
            return null;
        }

        $adjustment = $adjustments->first();

        $raw = strtolower(trim((string) (
            $adjustment->adjustment_type
            ?? $adjustment->type
            ?? $adjustment->reason
            ?? $adjustment->remarks
            ?? ''
        )));

        if (str_contains($raw, 'ob') || str_contains($raw, 'official business')) {
            return 'OB';
        }

        if (str_contains($raw, 'offset')) {
            return 'OFFSET';
        }

        if ($raw === 'ot' || str_contains($raw, 'overtime')) {
            return 'OT';
        }

        if (str_contains($raw, 'addition') || str_contains($raw, 'add')) {
            return 'ADJ+';
        }

        if (str_contains($raw, 'deduction') || str_contains($raw, 'deduct')) {
            return 'ADJ-';
        }

        return 'ADJ';
    }

    protected function sumDeductions(Collection $deductions, array $needles): float
    {
        return round((float) $deductions
            ->filter(function ($deduction) use ($needles): bool {
                $sourceType = strtolower((string) data_get($deduction, 'source_type', ''));
                $name = strtolower((string) data_get($deduction, 'name', ''));

                foreach ($needles as $needle) {
                    if (str_contains($sourceType, $needle) || str_contains($name, $needle)) {
                        return true;
                    }
                }

                return false;
            })
            ->sum(fn ($deduction): float => (float) data_get($deduction, 'amount', 0)), 2);
    }

    protected function adjustmentGroups(Carbon $startDate, Carbon $endDate): Collection
    {
        if (
            ! class_exists(PayrollAttendanceAdjustment::class)
            || ! $this->tableExists('payroll_attendance_adjustments')
        ) {
            return collect();
        }

        $query = PayrollAttendanceAdjustment::query()
            ->whereBetween('work_date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ]);

        if ($this->columnExists('payroll_attendance_adjustments', 'status')) {
            $query->where('status', 'approved');
        } elseif ($this->columnExists('payroll_attendance_adjustments', 'is_approved')) {
            $query->where('is_approved', true);
        }

        return $query
            ->get()
            ->groupBy(fn ($row): string => $this->employeeDateKey($row));
    }

    protected function employeeDateKey(object $row): string
    {
        return $this->employeeGroupKey($row).'|'.Carbon::parse($row->work_date)->toDateString();
    }

    protected function employeeGroupKey(object $row): string
    {
        if (! empty($row->employee_no)) {
            return 'EMP:'.trim((string) $row->employee_no);
        }

        if (! empty($row->crosschex_id)) {
            return 'CROSSCHEX:'.trim((string) $row->crosschex_id);
        }

        if (! empty($row->biometric_employee_id)) {
            return 'BIO:'.trim((string) $row->biometric_employee_id);
        }

        return 'NAME:'.mb_strtoupper(trim((string) ($row->employee_name ?: 'UNKNOWN')));
    }

    protected function payUnits(object $row): float
    {
        if (isset($row->payable_days)) {
            return round(max(0, (float) $row->payable_days), 2);
        }

        if (isset($row->payable_hours)) {
            return round(max(0, (float) $row->payable_hours) / (float) config('payroll.attendance.paid_hours_per_day', 8), 2);
        }

        return 0.00;
    }

    protected function isAbsent(object $row): bool
    {
        return (bool) ($row->is_absent ?? false) || $this->statusIs($row, 'absent');
    }

    protected function needsReview(object $row): bool
    {
        $status = $this->normalizedStatus($row);

        return (bool) ($row->needs_review ?? false)
            || in_array($status, [
                'no_schedule',
                'incomplete_log',
                'half_day',
                'missing_log',
                'for_review',
                'needs_review',
            ], true);
    }

    protected function isHolidayRow(object $row): bool
    {
        $status = $this->normalizedStatus($row);

        return str_contains($status, 'holiday')
            || ! empty($row->holiday_id)
            || ! empty($row->holiday_name);
    }

    protected function isRestDayRow(object $row): bool
    {
        return in_array($this->normalizedStatus($row), [
            'rest_day',
            'day_off',
            'rest_day_worked',
        ], true);
    }

    protected function isRestDayWorked(object $row): bool
    {
        return $this->normalizedStatus($row) === 'rest_day_worked'
            || ($this->isRestDayRow($row) && $this->hasWorkRecord($row));
    }

    protected function hasWorkRecord(object $row): bool
    {
        return ! empty($row->actual_time_in)
            || ! empty($row->time_in)
            || ((int) ($row->worked_minutes ?? 0)) > 0;
    }

    protected function statusIs(object $row, string $status): bool
    {
        return $this->normalizedStatus($row) === $status;
    }

    protected function normalizedStatus(object $row): string
    {
        return strtolower(str_replace(' ', '_', (string) ($row->attendance_status ?? '')));
    }

    protected function tableExists(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }

    protected function columnExists(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (\Throwable) {
            return false;
        }
    }
}
