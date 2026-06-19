<?php

namespace App\Services\Payroll;

use App\Models\DailyAttendanceSummary;
use App\Models\Holiday;
use App\Models\PaymentLog;
use App\Models\Payroll;
use App\Models\PayrollAttendanceAdjustment;
use App\Models\PayrollEmployeeSalary;
use App\Models\PayrollItem;
use App\Models\PayrollReportLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PayrollComputationService
{
    public function __construct(
        protected PayrollPeriodService $periodService,
        protected GovernmentDeductionService $governmentDeductionService,
        protected PaymentLogService $paymentLogService,
    ) {}

    public function generate(array $data, ?int $userId = null): Payroll
    {
        [$startDate, $endDate] = $this->periodService->resolveCutoffRange(
            (int) $data['cutoff_month'],
            (int) $data['cutoff_year'],
            (string) $data['cutoff_type']
        );

        $contribution = $this->periodService->contributionMonth(
            (int) $data['cutoff_month'],
            (int) $data['cutoff_year'],
            (string) $data['cutoff_type']
        );

        $existing = Payroll::query()
            ->where('cutoff_month', (int) $data['cutoff_month'])
            ->where('cutoff_year', (int) $data['cutoff_year'])
            ->where('cutoff_type', (string) $data['cutoff_type'])
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'cutoff_type' => 'Payroll already exists for this cutoff. Delete the draft first if you need to regenerate.',
            ]);
        }

        $summaries = DailyAttendanceSummary::query()
            ->whereBetween('work_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('employee_name')
            ->orderBy('work_date')
            ->get();

        if ($summaries->isEmpty()) {
            throw ValidationException::withMessages([
                'cutoff_type' => 'No daily attendance summaries found for the selected cutoff.',
            ]);
        }

        return DB::transaction(function () use ($data, $startDate, $endDate, $contribution, $summaries, $userId): Payroll {
            $payroll = Payroll::create([
                'payroll_number' => $this->periodService->generatePayrollNumber(
                    (int) $data['cutoff_year'],
                    (int) $data['cutoff_month'],
                    (string) $data['cutoff_type'],
                    fn (string $number): bool => Payroll::query()->where('payroll_number', $number)->exists()
                ),
                'cutoff_month' => (int) $data['cutoff_month'],
                'cutoff_year' => (int) $data['cutoff_year'],
                'cutoff_type' => (string) $data['cutoff_type'],
                'contribution_month' => $contribution['month'],
                'contribution_year' => $contribution['year'],
                'period_start' => $startDate->toDateString(),
                'period_end' => $endDate->toDateString(),
                'remarks' => $data['remarks'] ?? null,
                'generated_by' => $userId,
                'generated_at' => now('Asia/Manila'),
                'status' => 'draft',
                'meta' => [
                    'hours_per_day' => $this->hoursPerDay(),
                    'minutes_per_day' => $this->minutesPerDay(),
                    'late_grace_minutes' => (int) config('payroll.attendance.late_grace_minutes', 15),
                    'contribution_cycle_start' => $contribution['cycle_start']->toDateString(),
                    'contribution_cycle_end' => $contribution['cycle_end']->toDateString(),
                ],
            ]);

            $summaries->groupBy(fn ($row): string => $this->employeeGroupKey($row))->each(function (Collection $rows) use ($payroll, $startDate, $endDate, $data, $userId): void {
                $this->createPayrollItem($payroll, $rows, $startDate, $endDate, (string) $data['cutoff_type'], $userId);
            });

            return $payroll->load(['items', 'paymentLogs']);
        });
    }

    protected function createPayrollItem(Payroll $payroll, Collection $rows, Carbon $startDate, Carbon $endDate, string $cutoffType, ?int $userId): PayrollItem
    {
        $first = $rows->first();
        $rates = $this->resolveEmployeeRates($first);

        $totalWorkedMinutes = (int) $rows->sum(fn ($row): int => (int) ($row->worked_minutes ?? 0));
        $totalLateMinutes = $this->totalPayrollLateMinutes($rows);
        $totalUndertimeMinutes = (int) $rows->sum(fn ($row): int => (int) ($row->undertime_minutes ?? 0));
        $totalAbsentDays = (float) $rows->filter(fn ($row): bool => $this->isAbsent($row))->count();
        $totalLeaveDays = (int) $rows->filter(fn ($row): bool => $this->statusIs($row, 'leave'))->count();

        $approvedOtDates = $this->getApprovedOvertimeDates($first, $startDate, $endDate);
        $totalOvertimeMinutes = (int) $rows
            ->filter(fn ($row): bool => in_array($this->dateString($row->work_date), $approvedOtDates, true))
            ->sum(fn ($row): int => (int) ($row->overtime_minutes ?? 0));

        /*
         | Payroll architecture fix:
         | DailyAttendanceSummary remains the attendance/audit source.
         | Money computation now uses ONE payroll money model:
         | - Monthly employees: base cutoff salary = monthly salary / 2, then deduct attendance loss.
         | - Daily/hourly employees: regular pay = payable hours x hourly rate.
         | This prevents the old issue where 12.94 payroll units x hourly rate produced ₱7,999
         | instead of the expected ₱9,500 monthly cutoff base.
         */
        $regularRows = $rows->reject(
            fn ($row): bool => $this->isHolidayRow($row)
                || $this->isRestDayRow($row)
                || $this->statusIs($row, 'leave')
        );

        $regularPayableHours = round(
            $regularRows->sum(fn ($row): float => $this->payableHours($row)),
            2
        );

        $totalSummaryPayableHours = round(
            $rows->sum(fn ($row): float => $this->payableHours($row)),
            2
        );

        $attendanceDeductions = $this->computeAttendanceDeductions(
            $rows,
            $rates,
            $totalLateMinutes,
            $totalUndertimeMinutes,
            $totalAbsentDays
        );

        $attendanceLoss = round(
            (float) $attendanceDeductions['late_deduction']
            + (float) $attendanceDeductions['undertime_deduction']
            + (float) $attendanceDeductions['absence_deduction'],
            2
        );

        $isMonthlyEmployee = strtolower((string) $rates['rate_type']) === 'monthly';

        $baseCutoffPay = $isMonthlyEmployee
            ? round(((float) $rates['monthly_rate']) / 2, 2)
            : round($regularPayableHours * (float) $rates['hourly_rate'], 2);

        $regularPay = $baseCutoffPay;

        $leavePay = $isMonthlyEmployee
            ? 0.00
            : round($rows
                ->filter(fn ($row): bool => $this->statusIs($row, 'leave'))
                ->sum(fn ($row): float => $this->payableHours($row) * (float) $rates['hourly_rate']), 2);

        $holiday = $this->computeHolidayPay($rows, $rates, $first);
        $restDay = $this->computeRestDayPay($rows, $rates);
        $overtimePay = round(($totalOvertimeMinutes / 60) * (float) $rates['ot_rate_per_hour'], 2);

        $allowancePerCutoff = round(((float) ($rates['allowance'] ?? 0)) / 2, 2);
        $manualAdjustments = $this->computeApprovedPayrollAdjustments($first, $startDate, $endDate);

        $salaryDeductions = $this->resolveSalaryDeductions($rates, $payroll, $first);
        $salaryDeductionAmount = round(array_sum(array_column($salaryDeductions, 'amount')), 2);

        $otherAdditions = round($allowancePerCutoff + (float) $manualAdjustments['additions'], 2);
        $otherDeductions = round($salaryDeductionAmount + (float) $manualAdjustments['deductions'], 2);

        $attendanceDeductionForNet = $isMonthlyEmployee ? $attendanceLoss : 0.00;

        $grossPay = round(
            $regularPay
            - $attendanceDeductionForNet
            + $holiday['holiday_pay']
            + $restDay['rest_day_pay']
            + $leavePay
            + $overtimePay
            + $otherAdditions,
            2
        );

        $taxableCompensation = $grossPay;

        $currentGovernmentBasis = round(
            max(0, $regularPay - $attendanceDeductionForNet)
            + $holiday['government_basis']
            + $leavePay
            + $restDay['government_basis'],
            2
        );

        $monthlyCycleBasis = $this->monthlyCycleGovernmentBasis($payroll, $first, $currentGovernmentBasis);

        $basisMode = config('payroll.government_basis', []);
        $sssBasis = ($basisMode['sss'] ?? 'actual_cycle_basic') === 'fixed_monthly_basic'
            ? $rates['monthly_rate']
            : $monthlyCycleBasis['amount'];
        $philHealthBasis = ($basisMode['philhealth'] ?? 'fixed_monthly_basic') === 'actual_cycle_basic'
            ? $monthlyCycleBasis['amount']
            : $rates['monthly_rate'];
        $pagibigBasis = ($basisMode['pagibig'] ?? 'actual_cycle_basic') === 'fixed_monthly_basic'
            ? $rates['monthly_rate']
            : $monthlyCycleBasis['amount'];

        $governmentRaw = $this->governmentDeductionService->compute([
            'monthly_basic' => $monthlyCycleBasis['amount'],
            'sss_monthly_basic' => $sssBasis,
            'philhealth_monthly_basic' => $philHealthBasis,
            'pagibig_monthly_basic' => $pagibigBasis,
            'taxable_cutoff_compensation' => $taxableCompensation,
        ]);

        $governmentRaw['sss_basis'] = $sssBasis;
        $governmentRaw['philhealth_basis'] = $philHealthBasis;
        $governmentRaw['pagibig_basis'] = $pagibigBasis;

        $government = $this->governmentDeductionService->applyDeductionSchedule($governmentRaw, $cutoffType);
        $government = $this->applyEmployeeGovernmentProfileIfConfigured($government, $governmentRaw, $rates, $cutoffType);

        $netPay = round(
            $grossPay
            - $otherDeductions
            - $government['total_employee_government_deductions'],
            2
        );

        $item = PayrollItem::create([
            'payroll_id' => $payroll->id,
            'employee_id' => $rates['employee_id'],
            'payroll_employee_salary_id' => $rates['salary_id'],
            'biometric_employee_id' => $first->biometric_employee_id ?? null,
            'employee_no' => $first->employee_no ?? null,
            'employee_name' => $first->employee_name ?: 'Unknown Employee',
            'crosschex_id' => $first->crosschex_id ?? null,
            'rate_type' => $rates['rate_type'],
            'monthly_rate' => $rates['monthly_rate'],
            'daily_rate' => $rates['daily_rate'],
            'hourly_rate' => $rates['hourly_rate'],
            'minute_rate' => $rates['minute_rate'],
            'total_scheduled_days' => $this->scheduledWorkingDays($rows),
            'total_worked_days' => round($totalWorkedMinutes / $this->minutesPerDay(), 2),
            'total_payable_days' => round($totalSummaryPayableHours / $this->hoursPerDay(), 2),
            'total_payable_hours' => $totalSummaryPayableHours,
            'total_worked_minutes' => $totalWorkedMinutes,
            'total_late_minutes' => $totalLateMinutes,
            'total_undertime_minutes' => $totalUndertimeMinutes,
            'total_overtime_minutes' => $totalOvertimeMinutes,
            'total_absent_days' => $totalAbsentDays,
            'total_rest_day_worked' => $restDay['worked_days'],
            'total_holiday_worked' => $holiday['worked_days'],
            'total_leave_days' => $totalLeaveDays,
            'regular_pay' => $regularPay,
            'gross_pay' => $grossPay,
            'late_deduction' => $attendanceDeductions['late_deduction'],
            'undertime_deduction' => $attendanceDeductions['undertime_deduction'],
            'absence_deduction' => $attendanceDeductions['absence_deduction'],
            'overtime_pay' => $overtimePay,
            'holiday_pay' => $holiday['holiday_pay'],
            'rest_day_pay' => $restDay['rest_day_pay'],
            'leave_pay' => $leavePay,
            'taxable_compensation' => $taxableCompensation,
            'sss_employee' => $government['sss_employee'],
            'sss_employer' => $government['sss_employer'],
            'philhealth_employee' => $government['philhealth_employee'],
            'philhealth_employer' => $government['philhealth_employer'],
            'pagibig_employee' => $government['pagibig_employee'],
            'pagibig_employer' => $government['pagibig_employer'],
            'withholding_tax' => $government['withholding_tax'],
            'total_employee_government_deductions' => $government['total_employee_government_deductions'],
            'total_employer_government_contributions' => $government['total_employer_government_contributions'],
            'other_additions' => $otherAdditions,
            'other_deductions' => $otherDeductions,
            'net_pay' => $netPay,
            'meta' => [
                'pay_architecture' => [
                    'money_model' => $isMonthlyEmployee ? 'monthly_cutoff_less_attendance_loss' : 'hourly_payable_hours',
                    'base_cutoff_pay' => $baseCutoffPay,
                    'regular_payable_hours_for_audit' => $regularPayableHours,
                    'summary_payable_hours_for_audit' => $totalSummaryPayableHours,
                    'attendance_loss_deducted_from_gross' => $attendanceDeductionForNet,
                    'regular_pay_note' => $isMonthlyEmployee
                        ? 'Regular pay is monthly salary divided by 2. Attendance loss is deducted after the base cutoff pay.'
                        : 'Regular pay is payable hours multiplied by hourly rate.',
                ],
                'hours_per_day' => $this->hoursPerDay(),
                'minutes_per_day' => $this->minutesPerDay(),
                'attendance_deductions_are_deducted_from_monthly_base' => $isMonthlyEmployee,
                'government_schedule' => $government['schedule_meta'] ?? config('payroll.government_deduction_schedule'),
                'government_raw_before_schedule' => $governmentRaw,
                'government_after_profile_schedule' => $government,
                'government_monthly_cycle_basis' => $monthlyCycleBasis,
                'salary_deductions' => $salaryDeductions,
                'manual_adjustments' => $manualAdjustments,
                'allowance' => [
                    'monthly_allowance' => round((float) ($rates['allowance'] ?? 0), 2),
                    'allowance_per_cutoff' => $allowancePerCutoff,
                ],
                'attendance_deductions' => $attendanceDeductions,
                'holiday_breakdown' => $holiday,
                'rest_day_breakdown' => $restDay,
                'daily_status_breakdown' => $rows->groupBy(fn ($row) => strtolower((string) ($row->attendance_status ?? 'none')))->map->count()->toArray(),
            ],
        ]);

        $this->paymentLogService->logPayrollItem($payroll, $item, $salaryDeductions, $userId);
        $this->createFutureReportPlaceholders($payroll, $item, $userId);

        return $item;
    }

    protected function computeHolidayPay(Collection $rows, array $rates, object $employeeReference): array
    {
        $details = [];
        $holidayPay = 0.0;
        $governmentBasis = 0.0;
        $payableHours = 0.0;
        $workedDays = 0;
        $paidNotWorkedDays = 0;

        foreach ($rows->filter(fn ($row): bool => $this->isHolidayRow($row)) as $row) {
            $date = $this->dateString($row->work_date);
            $holidayType = $this->holidayType($row);
            $hasWork = $this->hasWorkRecord($row);
            $isRegular = str_contains($holidayType, 'regular');
            $workedFraction = min(1, max($this->payableHours($row) / $this->hoursPerDay(), ((int) ($row->worked_minutes ?? 0)) / $this->minutesPerDay()));
            $eligible = $hasWork || $this->isEligibleForHolidayNotWorked($employeeReference, Carbon::parse($date));
            $multiplier = 0.0;
            $amount = 0.0;

            if ($hasWork) {
                $workedDays++;
                $multiplier = $isRegular
                    ? (float) config('payroll.holiday.regular_worked_multiplier', 2.00)
                    : (float) config('payroll.holiday.special_worked_multiplier', 1.30);
                $amount = round($rates['daily_rate'] * $multiplier * max($workedFraction, 0.01), 2);
                $payableHours += max($this->payableHours($row), $this->hoursPerDay() * max($workedFraction, 0));
            } elseif ($eligible) {
                $multiplier = $isRegular
                    ? (float) config('payroll.holiday.regular_not_worked_multiplier', 1.00)
                    : (float) config('payroll.holiday.special_not_worked_multiplier', 0.00);
                $amount = round($rates['daily_rate'] * $multiplier, 2);

                if ($amount > 0) {
                    $paidNotWorkedDays++;
                }
            }

            $holidayPay += $amount;
            $governmentBasis += $amount;
            $details[] = [
                'date' => $date,
                'holiday_type' => $holidayType,
                'has_work_record' => $hasWork,
                'before_after_eligible' => $eligible,
                'multiplier' => $multiplier,
                'amount' => $amount,
                'remarks' => $this->holidayRemarks($hasWork, $eligible, $isRegular),
            ];
        }

        return [
            'holiday_pay' => round($holidayPay, 2),
            'government_basis' => round($governmentBasis, 2),
            'payable_hours' => round($payableHours, 2),
            'worked_days' => $workedDays,
            'paid_not_worked_days' => $paidNotWorkedDays,
            'details' => $details,
        ];
    }

    protected function computeRestDayPay(Collection $rows, array $rates): array
    {
        $restRows = $rows->filter(fn ($row): bool => $this->isRestDayWorked($row));
        $pay = 0.0;
        $payableHours = 0.0;
        $details = [];
        $multiplier = (float) config('payroll.holiday.rest_day_worked_multiplier', 1.30);

        foreach ($restRows as $row) {
            $fraction = min(1, max($this->payableHours($row) / $this->hoursPerDay(), ((int) ($row->worked_minutes ?? 0)) / $this->minutesPerDay()));
            $amount = round($rates['daily_rate'] * $multiplier * max($fraction, 0.01), 2);
            $pay += $amount;
            $payableHours += max($this->payableHours($row), $this->hoursPerDay() * max($fraction, 0));
            $details[] = [
                'date' => $this->dateString($row->work_date),
                'multiplier' => $multiplier,
                'amount' => $amount,
            ];
        }

        return [
            'rest_day_pay' => round($pay, 2),
            'government_basis' => round($pay, 2),
            'payable_hours' => round($payableHours, 2),
            'worked_days' => $restRows->count(),
            'details' => $details,
        ];
    }

    protected function resolveEmployeeRates(object $summary): array
    {
        if (! class_exists(PayrollEmployeeSalary::class)) {
            return $this->emptyRatePayload();
        }

        $query = PayrollEmployeeSalary::query();

        if ($this->columnExists('payroll_employee_salaries', 'is_active')) {
            $query->where('is_active', true);
        }

        $matched = false;
        $query->where(function ($q) use ($summary, &$matched): void {
            if (! empty($summary->biometric_employee_id)) {
                $q->orWhere('biometric_employee_id', $summary->biometric_employee_id);
                $matched = true;
            }

            if (! empty($summary->employee_no)) {
                $q->orWhere('employee_no', $summary->employee_no);
                $matched = true;
            }

            if (! empty($summary->employee_name)) {
                $q->orWhere('employee_name', $summary->employee_name);
                $matched = true;
            }
        });

        if (! $matched) {
            return $this->emptyRatePayload();
        }

        $salary = $query->latest('id')->first();

        if (! $salary) {
            return $this->emptyRatePayload();
        }

        $rateType = strtolower((string) ($salary->rate_type ?? 'daily'));
        $basicSalary = (float) ($salary->basic_salary ?? $salary->monthly_rate ?? $salary->daily_rate ?? 0);

        /*
         | Company payroll standard:
         | 9 hours = 1 payroll day.
         | For monthly employees, the money base is salary / 2 per cutoff.
         | The daily divisor is only used for absence/late/UT rates and premium pay basis.
         */
        $monthlyWorkingDays = (float) (
            $salary->monthly_working_days
            ?? $salary->working_days_per_month
            ?? config('payroll.monthly_working_days', 22)
        );

        if ($rateType === 'monthly') {
            $monthlyRate = $basicSalary;

            $fallbackDailyRate = $monthlyWorkingDays > 0
                ? $monthlyRate / $monthlyWorkingDays
                : 0;

            $dailyRate = (float) (
                $salary->daily_rate
                ?? $salary->regular_daily_rate
                ?? $salary->absent_deduction_per_day
                ?? $fallbackDailyRate
            );
        } else {
            $dailyRate = $basicSalary;
            $monthlyRate = $dailyRate * $monthlyWorkingDays;
        }

        $hourlyRate = $dailyRate > 0 ? $dailyRate / $this->hoursPerDay() : 0;
        $minuteRate = $dailyRate > 0 ? $dailyRate / $this->minutesPerDay() : 0;

        return [
            'employee_id' => $salary->employee_id ?? null,
            'salary_id' => $salary->id ?? null,
            'rate_type' => $rateType,
            'salary_model' => $salary,
            'monthly_rate' => round($monthlyRate, 2),
            'daily_rate' => round($dailyRate, 6),
            'hourly_rate' => round((float) ($salary->hourly_rate ?? $hourlyRate), 6),
            'minute_rate' => round((float) ($salary->minute_rate ?? $minuteRate), 6),
            'allowance' => round((float) ($salary->allowance ?? $salary->regular_allowance ?? 0), 2),
            'ot_rate_per_hour' => round((float) ($salary->ot_rate_per_hour ?? $salary->overtime_rate_per_hour ?? ($hourlyRate * 1.25)), 6),
            'late_deduction_per_minute' => round((float) ($salary->late_deduction_per_minute ?? $salary->late_rate_per_minute ?? $minuteRate), 6),
            'undertime_deduction_per_minute' => round((float) ($salary->undertime_deduction_per_minute ?? $salary->undertime_rate_per_minute ?? $minuteRate), 6),
            'absent_deduction_per_day' => round((float) ($salary->absent_deduction_per_day ?? $salary->absence_rate_per_day ?? $dailyRate), 2),
            'simple_deductions' => [
                'sss_loan' => round((float) ($salary->sss_loan ?? 0), 2),
                'pagibig_loan' => round((float) ($salary->pagibig_loan ?? 0), 2),
                'vale' => round((float) ($salary->vale ?? 0), 2),
                'other_loans' => round((float) ($salary->other_loans ?? 0), 2),
            ],
        ];
    }

    protected function resolveSalaryDeductions(array $rates, Payroll $payroll, object $summary): array
    {
        $salary = $rates['salary_model'] ?? null;
        $deductions = [];

        foreach (($rates['simple_deductions'] ?? []) as $key => $amount) {
            $amount = round((float) $amount, 2);

            if ($amount <= 0) {
                continue;
            }

            $deductions[] = [
                'source_type' => $key,
                'source_id' => null,
                'name' => strtoupper(str_replace('_', ' ', $key)),
                'amount' => $amount,
                'balance_before' => null,
                'balance_after' => null,
                'deduction_schedule' => 'per_cutoff',
                'remarks' => 'Legacy fixed deduction column from employee rate setup.',
            ];
        }

        if ($salary && method_exists($salary, 'loans')) {
            $salary->loadMissing('loans');
            foreach ($salary->loans as $loan) {
                $this->appendBalanceAwareDeduction($deductions, $loan, $payroll, $summary, 'loan');
            }
        }

        if ($salary && method_exists($salary, 'otherDeductions')) {
            $salary->loadMissing('otherDeductions');
            foreach ($salary->otherDeductions as $deduction) {
                $this->appendBalanceAwareDeduction($deductions, $deduction, $payroll, $summary, 'other_deduction');
            }
        }

        return $deductions;
    }

    protected function appendBalanceAwareDeduction(array &$deductions, object $deduction, Payroll $payroll, object $summary, string $sourceType): void
    {
        $schedule = (string) ($deduction->deduction_schedule ?? 'per_cutoff');

        if (! $this->deductionMatchesCutoff($schedule, (string) $payroll->cutoff_type)) {
            return;
        }

        if (isset($deduction->status) && in_array(strtolower((string) $deduction->status), ['paid', 'closed', 'inactive', 'completed'], true)) {
            return;
        }

        $paymentAmount = round((float) ($deduction->payment_amount ?? $deduction->amount_per_cutoff ?? $deduction->amount ?? 0), 2);
        $principal = round((float) ($deduction->principal_amount ?? $deduction->loan_amount ?? $deduction->total_amount ?? 0), 2);
        $alreadyPaid = PaymentLog::query()
            ->where('source_type', $sourceType)
            ->where('source_id', $deduction->id ?? 0)
            ->sum('amount');

        $balanceBefore = $principal > 0 ? max(0, $principal - $alreadyPaid) : null;

        if ($balanceBefore !== null && $balanceBefore <= 0) {
            return;
        }

        $amount = $balanceBefore !== null ? min($paymentAmount, $balanceBefore) : $paymentAmount;

        if ($amount <= 0) {
            return;
        }

        $deductions[] = [
            'source_type' => $sourceType,
            'source_id' => $deduction->id ?? null,
            'name' => (string) ($deduction->name ?? $deduction->loan_type ?? 'Deduction'),
            'amount' => round($amount, 2),
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceBefore !== null ? round($balanceBefore - $amount, 2) : null,
            'deduction_schedule' => $schedule,
            'remarks' => $balanceBefore !== null && ($balanceBefore - $amount) <= 0 ? 'Final payment. Deduction stops after this payroll.' : null,
        ];
    }

    protected function deductionMatchesCutoff(string $schedule, string $cutoffType): bool
    {
        return $this->scheduleMatchesCutoff($schedule, $cutoffType);
    }

    protected function applyEmployeeGovernmentProfileIfConfigured(array $government, array $governmentRaw, array $rates, string $cutoffType): array
    {
        $salary = $rates['salary_model'] ?? null;

        if (! $salary) {
            return $this->refreshGovernmentTotals($government);
        }

        $scheduleMeta = [];

        foreach (['sss', 'philhealth', 'pagibig'] as $key) {
            $schedule = $this->profileSchedule($salary, $key);
            $matchesCutoff = $this->scheduleMatchesCutoff($schedule, $cutoffType);

            $employeeAmount = $this->profileGovernmentAmount($salary, $key, 'employee');
            $employerAmount = $this->profileGovernmentAmount($salary, $key, 'employer');

            $employeeField = $key.'_employee';
            $employerField = $key.'_employer';

            if ($employeeAmount !== null || $schedule !== null) {
                $government[$employeeField] = $matchesCutoff ? round((float) ($employeeAmount ?? $government[$employeeField] ?? 0), 2) : 0.00;
            }

            if ($employerAmount !== null || $schedule !== null) {
                $government[$employerField] = $matchesCutoff ? round((float) ($employerAmount ?? $government[$employerField] ?? 0), 2) : 0.00;
            }

            $scheduleMeta[$key] = [
                'schedule' => $schedule ?? 'computed/default',
                'matched_cutoff' => $matchesCutoff,
                'profile_employee_amount' => $employeeAmount,
                'profile_employer_amount' => $employerAmount,
                'raw_employee_before_profile' => $governmentRaw[$employeeField] ?? null,
                'raw_employer_before_profile' => $governmentRaw[$employerField] ?? null,
            ];
        }

        $government['schedule_meta'] = $scheduleMeta;

        return $this->refreshGovernmentTotals($government);
    }

    protected function profileSchedule(object $salary, string $key): ?string
    {
        $candidates = match ($key) {
            'sss' => [
                'sss_deduction_schedule',
                'sss_schedule',
                'sss_cutoff_schedule',
                'sss_contribution_schedule',
            ],
            'philhealth' => [
                'philhealth_deduction_schedule',
                'philhealth_schedule',
                'philhealth_cutoff_schedule',
                'philhealth_contribution_schedule',
            ],
            'pagibig' => [
                'pagibig_deduction_schedule',
                'pagibig_schedule',
                'pagibig_cutoff_schedule',
                'pagibig_contribution_schedule',
            ],
            default => [],
        };

        foreach ($candidates as $field) {
            $value = data_get($salary, $field);

            if ($value !== null && trim((string) $value) !== '') {
                return (string) $value;
            }
        }

        return null;
    }

    protected function profileGovernmentAmount(object $salary, string $key, string $share): ?float
    {
        $label = $share === 'employer' ? 'employer' : 'employee';

        $candidates = match ($key) {
            'sss' => [
                "monthly_sss_{$label}_share",
                "sss_{$label}_share",
                "sss_{$label}",
                $label === 'employee' ? 'monthly_sss_employee_share' : 'monthly_sss_employer_share',
            ],
            'philhealth' => [
                "monthly_philhealth_{$label}_share",
                "philhealth_{$label}_share",
                "philhealth_{$label}",
                $label === 'employee' ? 'monthly_philhealth_employee_share' : 'monthly_philhealth_employer_share',
            ],
            'pagibig' => [
                "monthly_pagibig_{$label}_share",
                "pagibig_{$label}_share",
                "pagibig_{$label}",
                $label === 'employee' ? 'monthly_pagibig_employee_share' : 'monthly_pagibig_employer_share',
            ],
            default => [],
        };

        foreach ($candidates as $field) {
            $value = data_get($salary, $field);

            if ($value === null || $value === '') {
                continue;
            }

            if (is_numeric($value)) {
                return round((float) $value, 2);
            }
        }

        return null;
    }

    protected function scheduleMatchesCutoff(?string $schedule, string $cutoffType): bool
    {
        $schedule = strtolower(trim((string) $schedule));

        if ($schedule === '') {
            return true;
        }

        $schedule = str_replace([' ', '-', '/'], '_', $schedule);
        $schedule = preg_replace('/_+/', '_', $schedule);

        return match (true) {
            in_array($schedule, ['none', 'no', 'no_deduction', 'not_applicable', 'n_a', 'na'], true) => false,
            in_array($schedule, ['first', '1st', 'first_cutoff', '1st_cutoff', 'first_cutoff_only', '1st_cutoff_only'], true) => $cutoffType === 'first',
            in_array($schedule, ['second', '2nd', 'second_cutoff', '2nd_cutoff', 'second_cutoff_only', '2nd_cutoff_only'], true) => $cutoffType === 'second',
            in_array($schedule, ['monthly', 'per_cutoff', 'every_cutoff', 'both', 'all'], true) => true,
            default => true,
        };
    }

    protected function refreshGovernmentTotals(array $government): array
    {
        foreach ([
            'sss_employee',
            'sss_employer',
            'philhealth_employee',
            'philhealth_employer',
            'pagibig_employee',
            'pagibig_employer',
            'withholding_tax',
        ] as $key) {
            $government[$key] = round((float) ($government[$key] ?? 0), 2);
        }

        $government['total_employee_government_deductions'] = round(
            $government['sss_employee']
            + $government['philhealth_employee']
            + $government['pagibig_employee']
            + $government['withholding_tax'],
            2
        );

        $government['total_employer_government_contributions'] = round(
            $government['sss_employer']
            + $government['philhealth_employer']
            + $government['pagibig_employer'],
            2
        );

        return $government;
    }

    protected function monthlyCycleGovernmentBasis(Payroll $payroll, object $summary, float $currentBasis): array
    {
        $basis = $currentBasis;
        $previousItem = null;

        if ($payroll->cutoff_type === 'first') {
            $previous = $this->periodService->previousSecondCutoffForFirst((int) $payroll->cutoff_month, (int) $payroll->cutoff_year);

            $previousPayroll = Payroll::query()
                ->where('cutoff_month', $previous['month'])
                ->where('cutoff_year', $previous['year'])
                ->where('cutoff_type', 'second')
                ->latest('id')
                ->first();

            if ($previousPayroll) {
                $previousItem = PayrollItem::query()
                    ->where('payroll_id', $previousPayroll->id)
                    ->where(function ($query) use ($summary): void {
                        if (! empty($summary->biometric_employee_id)) {
                            $query->orWhere('biometric_employee_id', $summary->biometric_employee_id);
                        }

                        if (! empty($summary->employee_no)) {
                            $query->orWhere('employee_no', $summary->employee_no);
                        }

                        if (! empty($summary->employee_name)) {
                            $query->orWhere('employee_name', $summary->employee_name);
                        }
                    })
                    ->first();

                if ($previousItem) {
                    $basis += (float) data_get($previousItem->meta, 'government_monthly_cycle_basis.current_cutoff_basis', $previousItem->regular_pay ?? 0);
                }
            }
        }

        return [
            'amount' => round($basis, 2),
            'current_cutoff_basis' => round($currentBasis, 2),
            'previous_second_payroll_item_id' => $previousItem?->id,
            'warning' => $payroll->cutoff_type === 'first' && ! $previousItem
                ? 'Previous 2nd cutoff payroll item not found; government basis used current cutoff only.'
                : null,
        ];
    }

    protected function computeAttendanceDeductions(Collection $rows, array $rates, int $lateMinutes, int $undertimeMinutes, float $absentDays): array
    {
        /*
         | IMPORTANT:
         | Do NOT subtract the 15-minute grace period again here.
         | DailyAttendanceSummaryService already stores grace-adjusted late_minutes.
         | Payroll must use those stored late_minutes so Attendance Summary and Payroll match.
         */
        $lateRate = (float) ($rates['late_deduction_per_minute'] ?? $rates['minute_rate']);
        $undertimeRate = (float) ($rates['undertime_deduction_per_minute'] ?? $rates['minute_rate']);
        $absentRate = (float) ($rates['absent_deduction_per_day'] ?? $rates['daily_rate']);
        $graceMinutes = $this->effectiveLateGraceMinutes($rows);

        return [
            'late_minutes' => max(0, $lateMinutes),
            'late_grace_minutes' => $graceMinutes,
            'late_rate_per_minute' => round($lateRate, 6),
            'late_deduction' => round(max(0, $lateMinutes) * $lateRate, 2),
            'undertime_minutes' => max(0, $undertimeMinutes),
            'undertime_rate_per_minute' => round($undertimeRate, 6),
            'undertime_deduction' => round(max(0, $undertimeMinutes) * $undertimeRate, 2),
            'absent_days' => $absentDays,
            'absence_rate_per_day' => round($absentRate, 2),
            'absence_deduction' => round($absentDays * $absentRate, 2),
            'note' => 'Payroll late minutes are computed with the company 15-minute grace period using schedule and actual time-in. If time fields are missing, payroll falls back to saved summary late_minutes.',
        ];
    }

    protected function effectiveLateGraceMinutes(Collection $rows): int
    {
        $summaryGrace = $rows
            ->map(fn ($row): int => (int) ($row->grace_minutes ?? 0))
            ->filter(fn (int $minutes): bool => $minutes > 0)
            ->first();

        if ($summaryGrace) {
            return $summaryGrace;
        }

        return max(0, (int) config('payroll.attendance.late_grace_minutes', 15));
    }

    protected function computeApprovedPayrollAdjustments(object $summary, Carbon $startDate, Carbon $endDate): array
    {
        if (! class_exists(PayrollAttendanceAdjustment::class)) {
            return ['additions' => 0, 'deductions' => 0, 'details' => []];
        }

        $query = PayrollAttendanceAdjustment::query()
            ->whereBetween('work_date', [$startDate->toDateString(), $endDate->toDateString()]);

        $matched = false;
        $query->where(function ($q) use ($summary, &$matched): void {
            if (! empty($summary->biometric_employee_id)) {
                $q->orWhere('biometric_employee_id', $summary->biometric_employee_id);
                $matched = true;
            }

            if (! empty($summary->employee_no)) {
                $q->orWhere('employee_no', $summary->employee_no);
                $matched = true;
            }

            if (! empty($summary->employee_name)) {
                $q->orWhere('employee_name', $summary->employee_name);
                $matched = true;
            }
        });

        if (! $matched) {
            return ['additions' => 0, 'deductions' => 0, 'details' => []];
        }

        if ($this->columnExists('payroll_attendance_adjustments', 'status')) {
            $query->where('status', 'approved');
        }

        $rows = $query->get();
        $additions = 0.0;
        $deductions = 0.0;
        $details = [];

        foreach ($rows as $row) {
            $type = strtolower((string) ($row->adjustment_type ?? $row->type ?? ''));
            $amount = round((float) ($row->amount ?? 0), 2);

            if (in_array($type, ['addition', 'add', 'allowance', 'bonus'], true)) {
                $additions += $amount;
            } elseif (in_array($type, ['deduction', 'deduct'], true)) {
                $deductions += $amount;
            }

            $details[] = [
                'date' => $this->dateString($row->work_date),
                'type' => $type,
                'amount' => $amount,
                'remarks' => $row->remarks ?? null,
            ];
        }

        return ['additions' => round($additions, 2), 'deductions' => round($deductions, 2), 'details' => $details];
    }

    protected function createFutureReportPlaceholders(Payroll $payroll, PayrollItem $item, ?int $userId): void
    {
        foreach (['13th_month', '14th_month'] as $type) {
            PayrollReportLog::create([
                'payroll_id' => $payroll->id,
                'payroll_item_id' => $item->id,
                'employee_id' => $item->employee_id,
                'biometric_employee_id' => $item->biometric_employee_id,
                'employee_no' => $item->employee_no,
                'employee_name' => $item->employee_name,
                'report_type' => $type,
                'cutoff_month' => $payroll->cutoff_month,
                'cutoff_year' => $payroll->cutoff_year,
                'cutoff_type' => $payroll->cutoff_type,
                'contribution_month' => $payroll->contribution_month,
                'contribution_year' => $payroll->contribution_year,
                'period_start' => $payroll->period_start,
                'period_end' => $payroll->period_end,
                'basis_amount' => $item->regular_pay,
                'computed_amount' => 0,
                'status' => 'placeholder',
                'remarks' => 'Reserved log for future payroll report enhancement.',
                'generated_at' => now('Asia/Manila'),
                'generated_by' => $userId,
                'meta' => ['source' => 'payroll_generation_placeholder'],
            ]);
        }
    }

    protected function isEligibleForHolidayNotWorked(object $employeeReference, Carbon $holidayDate): bool
    {
        if (! config('payroll.holiday_requires_before_after_work', true)) {
            return true;
        }

        return $this->adjacentDatePassesHolidayRule($employeeReference, $holidayDate->copy()->subDay())
            && $this->adjacentDatePassesHolidayRule($employeeReference, $holidayDate->copy()->addDay());
    }

    protected function adjacentDatePassesHolidayRule(object $employeeReference, Carbon $date): bool
    {
        $row = DailyAttendanceSummary::query()
            ->whereDate('work_date', $date->toDateString())
            ->where(function ($query) use ($employeeReference): void {
                if (! empty($employeeReference->biometric_employee_id)) {
                    $query->orWhere('biometric_employee_id', $employeeReference->biometric_employee_id);
                }

                if (! empty($employeeReference->employee_no)) {
                    $query->orWhere('employee_no', $employeeReference->employee_no);
                }

                if (! empty($employeeReference->employee_name)) {
                    $query->orWhere('employee_name', $employeeReference->employee_name);
                }
            })
            ->first();

        if (! $row) {
            return false;
        }

        $status = strtolower(str_replace(' ', '_', (string) ($row->attendance_status ?? '')));

        if (in_array($status, ['rest_day', 'day_off', 'holiday', 'regular_holiday', 'special_holiday', 'leave'], true)) {
            return true;
        }

        return $this->hasWorkRecord($row) || in_array($status, ['present', 'late', 'undertime', 'rest_day_worked', 'holiday_worked'], true);
    }

    protected function getApprovedOvertimeDates(object $summary, Carbon $startDate, Carbon $endDate): array
    {
        if (! class_exists(PayrollAttendanceAdjustment::class)) {
            return [];
        }

        $query = PayrollAttendanceAdjustment::query()
            ->whereBetween('work_date', [$startDate->toDateString(), $endDate->toDateString()]);

        $matched = false;
        $query->where(function ($q) use ($summary, &$matched): void {
            if (! empty($summary->biometric_employee_id)) {
                $q->orWhere('biometric_employee_id', $summary->biometric_employee_id);
                $matched = true;
            }

            if (! empty($summary->employee_no)) {
                $q->orWhere('employee_no', $summary->employee_no);
                $matched = true;
            }

            if (! empty($summary->employee_name)) {
                $q->orWhere('employee_name', $summary->employee_name);
                $matched = true;
            }
        });

        if (! $matched) {
            return [];
        }

        $query->where(function ($q): void {
            $q->where('adjustment_type', 'overtime')->orWhere('adjustment_type', 'ot');
        });

        if ($this->columnExists('payroll_attendance_adjustments', 'status')) {
            $query->where('status', 'approved');
        } elseif ($this->columnExists('payroll_attendance_adjustments', 'is_approved')) {
            $query->where('is_approved', true);
        }

        return $query->pluck('work_date')->map(fn ($date): string => Carbon::parse($date)->toDateString())->unique()->values()->toArray();
    }

    protected function isHolidayRow(object $row): bool
    {
        $status = strtolower(str_replace(' ', '_', (string) ($row->attendance_status ?? '')));

        return str_contains($status, 'holiday')
            || ! empty($row->holiday_id)
            || ! empty($row->holiday_name)
            || $this->holidayModelExistsOnDate($row);
    }

    protected function holidayModelExistsOnDate(object $row): bool
    {
        if (! class_exists(Holiday::class) || empty($row->work_date)) {
            return false;
        }

        $dateColumn = $this->columnExists('holidays', 'holiday_date') ? 'holiday_date' : 'date';

        if (! $this->columnExists('holidays', $dateColumn)) {
            return false;
        }

        return Holiday::query()->whereDate($dateColumn, $this->dateString($row->work_date))->exists();
    }

    protected function holidayType(object $row): string
    {
        if (! empty($row->holiday_type)) {
            return strtolower((string) $row->holiday_type);
        }

        if (class_exists(Holiday::class) && ! empty($row->work_date)) {
            $dateColumn = $this->columnExists('holidays', 'holiday_date') ? 'holiday_date' : 'date';

            if ($this->columnExists('holidays', $dateColumn)) {
                $holiday = Holiday::query()->whereDate($dateColumn, $this->dateString($row->work_date))->first();

                if ($holiday) {
                    return strtolower((string) ($holiday->holiday_type ?? $holiday->type ?? 'regular'));
                }
            }
        }

        return 'regular';
    }

    protected function holidayRemarks(bool $hasWork, bool $eligible, bool $isRegular): string
    {
        if ($hasWork) {
            return 'Paid because employee has holiday work record/time-in.';
        }

        if ($eligible && $isRegular) {
            return 'Paid regular holiday because before/after holiday rule passed.';
        }

        if ($eligible) {
            return 'Eligible, but special non-working holiday not worked has no pay by default config.';
        }

        return 'Not paid because before/after holiday rule failed.';
    }

    protected function employeeGroupKey(object $row): string
    {
        if (! empty($row->biometric_employee_id)) {
            return 'BIO:'.$row->biometric_employee_id;
        }

        if (! empty($row->employee_no)) {
            return 'EMP:'.$row->employee_no;
        }

        return 'NAME:'.mb_strtoupper((string) ($row->employee_name ?: 'UNKNOWN'));
    }

    protected function scheduledWorkingDays(Collection $rows): float
    {
        return (float) $rows
            ->reject(fn ($row): bool => $this->isRestDayRow($row) || $this->isHolidayRow($row))
            ->count();
    }

    protected function payableHours(object $row): float
    {
        if (isset($row->payable_hours)) {
            return max(0, (float) $row->payable_hours);
        }

        if (isset($row->payable_days)) {
            return max(0, (float) $row->payable_days * $this->hoursPerDay());
        }

        return max(0, ((int) ($row->worked_minutes ?? 0)) / 60);
    }

    protected function isAbsent(object $row): bool
    {
        return (bool) ($row->is_absent ?? false) || $this->statusIs($row, 'absent');
    }

    protected function hasWorkRecord(object $row): bool
    {
        return ! empty($row->actual_time_in) || ! empty($row->time_in) || ((int) ($row->worked_minutes ?? 0)) > 0;
    }

    protected function isRestDayRow(object $row): bool
    {
        $status = strtolower(str_replace(' ', '_', (string) ($row->attendance_status ?? '')));

        return in_array($status, ['rest_day', 'day_off', 'rest_day_worked'], true);
    }

    protected function isRestDayWorked(object $row): bool
    {
        return strtolower(str_replace(' ', '_', (string) ($row->attendance_status ?? ''))) === 'rest_day_worked'
            || ($this->isRestDayRow($row) && $this->hasWorkRecord($row));
    }

    protected function statusIs(object $row, string $status): bool
    {
        return strtolower(str_replace(' ', '_', (string) ($row->attendance_status ?? ''))) === $status;
    }

    /**
     * Payroll-side late computation with company grace period.
     *
     * This keeps payroll aligned with Attendance Summary:
     * Scheduled In 07:00 + 15-minute grace = late count starts at 07:15.
     * Example: 07:18 = 3 minutes late, 07:38 = 23 minutes late.
     */
    protected function totalPayrollLateMinutes(Collection $rows): int
    {
        return (int) $rows->sum(fn ($row): int => $this->payrollLateMinutesForRow($row));
    }

    protected function payrollLateMinutesForRow(object $row): int
    {
        if ($this->isAbsent($row) || $this->isRestDayRow($row) || $this->statusIs($row, 'leave')) {
            return 0;
        }

        $scheduledIn = $this->parsePayrollDateTime($row->scheduled_time_in ?? null, $row->work_date ?? null);
        $actualIn = $this->parsePayrollDateTime($row->actual_time_in ?? ($row->time_in ?? null), $row->work_date ?? null);

        if (! $scheduledIn || ! $actualIn) {
            return max(0, (int) ($row->late_minutes ?? 0));
        }

        /*
         | If the actual time was stored as time-only and becomes earlier than
         | scheduled because of an overnight parsing edge case, move it forward.
         */
        if ($actualIn->lt($scheduledIn->copy()->subHours(12))) {
            $actualIn->addDay();
        }

        $rawLateMinutes = $scheduledIn->diffInMinutes($actualIn, false);

        if ($rawLateMinutes <= 0) {
            return 0;
        }

        return max(0, $rawLateMinutes - $this->lateGraceMinutes($row));
    }

    protected function lateGraceMinutes(?object $row = null): int
    {
        $rowGrace = $row ? data_get($row, 'grace_minutes') : null;

        if (is_numeric($rowGrace) && (int) $rowGrace > 0) {
            return max(0, (int) $rowGrace);
        }

        return max(0, (int) config('payroll.attendance.late_grace_minutes', config('payroll.late_grace_minutes', 15)));
    }

    protected function parsePayrollDateTime(mixed $value, mixed $workDate = null): ?Carbon
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        try {
            $valueString = trim((string) $value);
            $hasDatePart = preg_match('/\d{4}-\d{2}-\d{2}|\d{1,2}\/\d{1,2}\/\d{2,4}|[A-Za-z]{3,9}\s+\d{1,2},\s*\d{4}/', $valueString) === 1;

            if (! $hasDatePart && $workDate !== null) {
                return Carbon::parse($this->dateString($workDate).' '.$valueString, 'Asia/Manila');
            }

            return Carbon::parse($valueString, 'Asia/Manila');
        } catch (\Throwable) {
            return null;
        }
    }

    protected function dateString(mixed $date): string
    {
        return Carbon::parse($date)->toDateString();
    }

    protected function hoursPerDay(): float
    {
        return (float) config('payroll.attendance.hours_per_day', config('payroll.hours_per_day', 9));
    }

    protected function minutesPerDay(): int
    {
        return (int) config('payroll.attendance.minutes_per_day', config('payroll.minutes_per_day', 540));
    }

    protected function columnExists(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (\Throwable) {
            return false;
        }
    }

    protected function emptyRatePayload(): array
    {
        $dailyRate = 0;
        $hourlyRate = 0;
        $minuteRate = 0;

        return [
            'employee_id' => null,
            'salary_id' => null,
            'salary_model' => null,
            'rate_type' => 'daily',
            'monthly_rate' => 0,
            'daily_rate' => $dailyRate,
            'hourly_rate' => $hourlyRate,
            'minute_rate' => $minuteRate,
            'allowance' => 0,
            'ot_rate_per_hour' => 0,
            'late_deduction_per_minute' => $minuteRate,
            'undertime_deduction_per_minute' => $minuteRate,
            'absent_deduction_per_day' => $dailyRate,
            'simple_deductions' => [],
        ];
    }
}
