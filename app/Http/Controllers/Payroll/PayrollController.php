<?php

namespace App\Http\Controllers\Payroll;

use App\Exports\PayrollItemsExport;
use App\Http\Controllers\Controller;
use App\Models\DailyAttendanceSummary;
use App\Models\Payroll;
use App\Models\PayrollAttendanceAdjustment;
use App\Models\PayrollEmployeeSalary;
use App\Models\PayrollItem;
use App\Services\Payroll\DailyAttendanceSummaryService;
use App\Services\Payroll\GovernmentDeductionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->search);

        $payrolls = Payroll::query()
            ->with(['generator', 'finalizer'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('payroll_number', 'like', "%{$search}%")
                        ->orWhere('cutoff_type', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('cutoff_year')
            ->orderByDesc('cutoff_month')
            ->orderByRaw("CASE WHEN cutoff_type = 'second' THEN 1 ELSE 0 END DESC")
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('payroll.payrolls.index', compact('payrolls', 'search'));
    }

    public function create()
    {
        [$defaultCutoffMonth, $defaultCutoffYear, $defaultCutoffType] = $this->getDefaultCutoff();

        return view('payroll.payrolls.create', compact(
            'defaultCutoffMonth',
            'defaultCutoffYear',
            'defaultCutoffType'
        ));
    }

    public function store(
        Request $request,
        DailyAttendanceSummaryService $summaryService,
        GovernmentDeductionService $governmentDeductionService
    ) {
        $validated = $request->validate([
            'cutoff_month' => ['required', 'integer', 'min:1', 'max:12'],
            'cutoff_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'cutoff_type' => ['required', 'in:first,second'],
            'remarks' => ['nullable', 'string'],
            'rebuild_summary' => ['nullable', 'boolean'],
        ]);

        [$startDate, $endDate] = $this->resolveCutoffRange(
            (int) $validated['cutoff_month'],
            (int) $validated['cutoff_year'],
            $validated['cutoff_type']
        );

        $existing = Payroll::query()
            ->where('cutoff_month', $validated['cutoff_month'])
            ->where('cutoff_year', $validated['cutoff_year'])
            ->where('cutoff_type', $validated['cutoff_type'])
            ->first();

        if ($existing) {
            return back()->withInput()->withErrors([
                'cutoff_type' => 'Payroll already exists for this cutoff.',
            ]);
        }

        if ($request->boolean('rebuild_summary', true)) {
            $summaryService->buildForPeriod($startDate, $endDate);
        }

        $summaries = DailyAttendanceSummary::query()
            ->whereBetween('work_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('employee_name')
            ->get();

        if ($summaries->isEmpty()) {
            return back()->withInput()->withErrors([
                'cutoff_type' => 'No daily attendance summaries found for the selected cutoff.',
            ]);
        }

        $payroll = null;

        DB::transaction(function () use (
            $validated,
            $startDate,
            $endDate,
            $request,
            $summaries,
            &$payroll,
            $governmentDeductionService
        ) {
            $payroll = Payroll::create([
                'payroll_number' => $this->generatePayrollNumber(
                    (int) $validated['cutoff_year'],
                    (int) $validated['cutoff_month'],
                    $validated['cutoff_type']
                ),
                'cutoff_month' => (int) $validated['cutoff_month'],
                'cutoff_year' => (int) $validated['cutoff_year'],
                'cutoff_type' => $validated['cutoff_type'],
                'period_start' => $startDate->toDateString(),
                'period_end' => $endDate->toDateString(),
                'remarks' => $request->remarks,
                'generated_by' => auth()->id(),
                'generated_at' => now('Asia/Manila'),
                'status' => 'draft',
            ]);

            $grouped = $summaries->groupBy(function ($row) {
                if (! empty($row->biometric_employee_id)) {
                    return 'BIO:'.$row->biometric_employee_id;
                }

                if (! empty($row->employee_no)) {
                    return 'EMP:'.$row->employee_no;
                }

                return 'NAME:'.mb_strtoupper($row->employee_name ?: 'UNKNOWN');
            });

            foreach ($grouped as $rows) {
                $first = $rows->first();
                $rates = $this->resolveEmployeeRates($first);

                $totalWorkedMinutes = (int) $rows->sum('worked_minutes');
                $totalPayableDays = round((float) $rows->sum('payable_days'), 2); // for display/reference only
                $totalPayableHours = round((float) $rows->sum('payable_hours'), 2);
                $totalWorkedDays = round($totalWorkedMinutes / 480, 2);

                $totalLateMinutes = (int) $rows->sum('late_minutes');
                $totalUndertimeMinutes = (int) $rows->sum('undertime_minutes');

                $approvedOtDates = $this->getApprovedOvertimeDates($first, $startDate, $endDate);

                $totalOvertimeMinutes = (int) $rows
                    ->filter(function ($row) use ($approvedOtDates) {
                        $workDate = optional($row->work_date)->toDateString();

                        return $workDate && in_array($workDate, $approvedOtDates, true);
                    })
                    ->sum('overtime_minutes');

                $totalAbsentDays = (int) $rows->filter(function ($row) {
                    return (bool) ($row->is_absent ?? false)
                        || strtolower((string) ($row->attendance_status ?? '')) === 'absent';
                })->count();

                $totalRestDayWorked = (int) $rows->where('attendance_status', 'rest_day_worked')->count();
                $totalHolidayWorked = (int) $rows->where('attendance_status', 'holiday_worked')->count();
                $totalLeaveDays = (int) $rows->where('attendance_status', 'leave')->count();

                $expectedWorkingDays = (int) $rows->filter(function ($row) {
                    $status = strtolower((string) ($row->attendance_status ?? ''));

                    return ! in_array($status, ['rest_day'], true);
                })->count();

                $grossPay = round(((float) $rates['daily_rate']) * $expectedWorkingDays, 2);

                $lateRatePerMinute = round((float) ($rates['late_deduction_per_minute'] ?? $rates['minute_rate'] ?? 0), 6);
                $undertimeRatePerMinute = round((float) ($rates['undertime_deduction_per_minute'] ?? $rates['minute_rate'] ?? 0), 6);
                $absenceRatePerDay = round((float) ($rates['absent_deduction_per_day'] ?? $rates['daily_rate'] ?? 0), 2);

                if ($lateRatePerMinute <= 0) {
                    $lateRatePerMinute = round((float) ($rates['minute_rate'] ?? 0), 6);
                }

                if ($undertimeRatePerMinute <= 0) {
                    $undertimeRatePerMinute = round((float) ($rates['minute_rate'] ?? 0), 6);
                }

                if ($absenceRatePerDay <= 0) {
                    $absenceRatePerDay = round((float) ($rates['daily_rate'] ?? 0), 2);
                }

                $lateDeduction = round($lateRatePerMinute * $totalLateMinutes, 2);
                $undertimeDeduction = round($undertimeRatePerMinute * $totalUndertimeMinutes, 2);
                $absenceDeduction = round($absenceRatePerDay * $totalAbsentDays, 2);

                $attendanceDeductions = round(
                    $lateDeduction + $undertimeDeduction + $absenceDeduction,
                    2
                );

                $overtimeHours = $totalOvertimeMinutes / 60;
                $overtimePay = round($rates['ot_rate_per_hour'] * $overtimeHours, 2);

                $dailyRate = (float) ($rates['daily_rate'] ?? 0);

                $holidayPay = 0;
                $restDayPay = 0;
                $leavePay = 0;

                /*
                |--------------------------------------------------------------------------
                | Holiday Pay
                |--------------------------------------------------------------------------
                | Assumption:
                | - regular holiday worked  = +100% extra (total 200% including base day)
                | - special holiday worked  = +30% extra
                |
                | Since gross pay already includes payable days, we only add the EXTRA here.
                */
                $holidayRows = $rows->filter(function ($row) {
                    return strtolower((string) ($row->attendance_status ?? '')) === 'holiday_worked';
                });

                foreach ($holidayRows as $row) {
                    $holidayType = strtolower((string) ($row->holiday_type ?? ''));

                    if (str_contains($holidayType, 'regular')) {
                        // Gross already contains 1 day, so add only extra 100%
                        $holidayPay += $dailyRate * 1.00;
                    } elseif (str_contains($holidayType, 'special')) {
                        // Gross already contains 1 day, so add only extra 30%
                        $holidayPay += $dailyRate * 0.30;
                    } else {
                        // Fallback if holiday type is unknown
                        $holidayPay += $dailyRate * 0.30;
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Rest Day Worked
                |--------------------------------------------------------------------------
                | If you want rest day worked to add 30%, keep this.
                | If your company uses a different rule, adjust multiplier.
                */
                $restDayPay = round($totalRestDayWorked * ($dailyRate * 0.30), 2);

                $holidayPay = round($holidayPay, 2);
                $leavePay = round($leavePay, 2);

                // Monthly allowance split into 2 cutoffs
                $allowancePerCutoff = round(((float) ($rates['allowance'] ?? 0)) / 2, 2);
                $otherAdditions = round($allowancePerCutoff + $holidayPay + $restDayPay + $leavePay, 2);

                $loanDeductions = [
                    'sss_loan' => round((float) ($rates['sss_loan'] ?? 0), 2),
                    'pagibig_loan' => round((float) ($rates['pagibig_loan'] ?? 0), 2),
                    'vale' => round((float) ($rates['vale'] ?? 0), 2),
                    'other_loans' => round((float) ($rates['other_loans'] ?? 0), 2),
                ];

                $otherDeductions = round(array_sum($loanDeductions), 2);

                $taxableCompensation = round(
                    $grossPay
                    + $overtimePay
                    + $otherAdditions,
                    2
                );

                $government = $governmentDeductionService->compute([
                    'monthly_basic' => $rates['monthly_rate'] > 0
                        ? $rates['monthly_rate']
                        : round($rates['daily_rate'] * 26, 2),
                    'taxable_cutoff_compensation' => $taxableCompensation,
                ]);

                $government = $this->applyGovernmentDeductionRules(
                    $government,
                    $validated['cutoff_type']
                );

                $netPay = round(
                    $grossPay
                    + $overtimePay
                    + $otherAdditions
                    - $attendanceDeductions
                    - $otherDeductions
                    - $government['total_employee_government_deductions'],
                    2
                );

                PayrollItem::create([
                    'payroll_id' => $payroll->id,
                    'employee_id' => $rates['employee_id'],
                    'biometric_employee_id' => $first->biometric_employee_id,
                    'employee_no' => $first->employee_no,
                    'employee_name' => $first->employee_name,

                    'monthly_rate' => $rates['monthly_rate'],
                    'daily_rate' => $rates['daily_rate'],
                    'hourly_rate' => $rates['hourly_rate'],
                    'minute_rate' => $rates['minute_rate'],

                    'total_worked_days' => $totalWorkedDays,
                    'total_payable_days' => $totalPayableDays,
                    'total_payable_hours' => $totalPayableHours,
                    'total_worked_minutes' => $totalWorkedMinutes,
                    'total_late_minutes' => $totalLateMinutes,
                    'total_undertime_minutes' => $totalUndertimeMinutes,
                    'total_overtime_minutes' => $totalOvertimeMinutes,
                    'total_absent_days' => $totalAbsentDays,
                    'total_rest_day_worked' => $totalRestDayWorked,
                    'total_holiday_worked' => $totalHolidayWorked,
                    'total_leave_days' => $totalLeaveDays,

                    'gross_pay' => $grossPay,
                    'late_deduction' => $lateDeduction,
                    'undertime_deduction' => $undertimeDeduction,
                    'absence_deduction' => $absenceDeduction,
                    'overtime_pay' => $overtimePay,
                    'holiday_pay' => $holidayPay,
                    'rest_day_pay' => $restDayPay,
                    'leave_pay' => $leavePay,
                    'taxable_compensation' => $taxableCompensation,

                    'sss_employee' => $government['sss_employee'],
                    'sss_employer' => $government['sss_employer'],
                    'philhealth_employee' => $government['philhealth_employee'],
                    'philhealth_employer' => $government['philhealth_employer'],
                    'pagibig_employee' => $government['pagibig_employee'],
                    'pagibig_employer' => $government['pagibig_employer'],
                    'withholding_tax' => 0,

                    'total_employee_government_deductions' => $government['total_employee_government_deductions'],
                    'total_employer_government_contributions' => $government['total_employer_government_contributions'],

                    'other_additions' => $otherAdditions,
                    'other_deductions' => $otherDeductions,
                    'net_pay' => $netPay,

                    'meta' => [
                        'period_start' => $startDate->toDateString(),
                        'period_end' => $endDate->toDateString(),
                        'cutoff_type' => $validated['cutoff_type'],
                        'government_schedule' => $validated['cutoff_type'] === 'first'
                            ? '1st cutoff (11-25): SSS only'
                            : '2nd cutoff (26-10): PhilHealth + Pag-IBIG only',
                        'attendance_status_breakdown' => $rows
                            ->groupBy('attendance_status')
                            ->map(fn ($items) => $items->count())
                            ->toArray(),
                        'salary_deductions' => $loanDeductions,
                        'allowance' => [
                            'monthly_allowance' => round((float) ($rates['allowance'] ?? 0), 2),
                            'allowance_per_cutoff' => $allowancePerCutoff,
                        ],
                        'attendance_deductions' => [
                            'late_minutes' => $totalLateMinutes,
                            'late_rate_per_minute' => $lateRatePerMinute,
                            'late_deduction' => $lateDeduction,
                            'undertime_minutes' => $totalUndertimeMinutes,
                            'undertime_rate_per_minute' => $undertimeRatePerMinute,
                            'undertime_deduction' => $undertimeDeduction,
                            'absent_days' => $totalAbsentDays,
                            'absence_rate_per_day' => $absenceRatePerDay,
                            'absence_deduction' => $absenceDeduction,
                            'total_attendance_deductions' => $attendanceDeductions,
                        ], 'holiday_breakdown' => [
                            'total_holiday_worked' => $totalHolidayWorked,
                            'holiday_pay' => $holidayPay,
                            'total_rest_day_worked' => $totalRestDayWorked,
                            'rest_day_pay' => $restDayPay,
                            'leave_pay' => $leavePay,
                        ],
                        'allowance' => [
                            'monthly_allowance' => round((float) ($rates['allowance'] ?? 0), 2),
                            'allowance_per_cutoff' => $allowancePerCutoff,
                            'holiday_pay' => $holidayPay,
                            'rest_day_pay' => $restDayPay,
                            'leave_pay' => $leavePay,
                            'total_other_additions' => $otherAdditions,
                        ],
                    ],
                ]);
            }
        });

        return redirect()
            ->route('payroll.show', $payroll)
            ->with('success', 'Payroll generated successfully.');
    }

    public function show(Payroll $payroll)
    {
        $payroll->load(['items', 'generator', 'finalizer']);

        $totals = $this->getPayrollTotals($payroll);

        return view('payroll.payrolls.show', compact('payroll', 'totals'));
    }

    public function showItem(Payroll $payroll, PayrollItem $item)
    {
        try {
            Log::info('Payroll item page accessed', [
                'payroll_id' => $payroll->id,
                'item_id' => $item->id,
                'item_payroll_id' => $item->payroll_id,
                'employee_no' => $item->employee_no,
                'employee_name' => $item->employee_name,
                'user_id' => auth()->id(),
                'url' => request()->fullUrl(),
            ]);

            if ((int) $item->payroll_id !== (int) $payroll->id) {
                Log::warning('Payroll item does not belong to payroll', [
                    'payroll_id' => $payroll->id,
                    'item_id' => $item->id,
                    'item_payroll_id' => $item->payroll_id,
                    'user_id' => auth()->id(),
                ]);

                abort(404, 'Payroll item not found for this payroll.');
            }

            $summaries = $this->getItemSummaries($payroll, $item);

            Log::info('Payroll item summaries loaded', [
                'payroll_id' => $payroll->id,
                'item_id' => $item->id,
                'summary_count' => $summaries->count(),
            ]);

            return view('payroll.payrolls.show_item', compact('payroll', 'item', 'summaries'));
        } catch (\Throwable $e) {
            Log::error('Failed to load payroll item page', [
                'payroll_id' => $payroll->id ?? null,
                'item_id' => $item->id ?? null,
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('payroll.show', $payroll)
                ->with('error', 'Unable to open payroll employee details. Please check logs.');
        }
    }

    public function finalize(Payroll $payroll)
    {
        if ($payroll->status === 'finalized') {
            return back()->withErrors([
                'payroll' => 'Payroll is already finalized.',
            ]);
        }

        $payroll->update([
            'status' => 'finalized',
            'finalized_by' => auth()->id(),
            'finalized_at' => now('Asia/Manila'),
        ]);

        return redirect()
            ->route('payroll.show', $payroll)
            ->with('success', 'Payroll finalized successfully.');
    }

    public function exportExcel(Payroll $payroll)
    {
        $filename = $payroll->payroll_number.'.xlsx';

        return Excel::download(new PayrollItemsExport($payroll), $filename);
    }

    public function exportPdf(Payroll $payroll)
    {
        $payroll->load(['items', 'generator', 'finalizer']);
        $totals = $this->getPayrollTotals($payroll);

        $pdf = Pdf::loadView('payroll.payrolls.pdf', compact('payroll', 'totals'))
            ->setPaper('a4', 'landscape');

        return $pdf->download($payroll->payroll_number.'.pdf');
    }

    public function destroy(Payroll $payroll)
    {
        if ($payroll->status === 'finalized') {
            return back()->withErrors([
                'payroll' => 'Finalized payroll can no longer be deleted.',
            ]);
        }

        $payroll->delete();

        return redirect()
            ->route('payroll.index')
            ->with('success', 'Payroll deleted successfully.');
    }

    protected function getPayrollTotals(Payroll $payroll): array
    {
        return [
            'gross_pay' => round((float) $payroll->items->sum('gross_pay'), 2),
            'other_additions' => round((float) $payroll->items->sum('other_additions'), 2),
            'late_deduction' => round((float) $payroll->items->sum('late_deduction'), 2),
            'undertime_deduction' => round((float) $payroll->items->sum('undertime_deduction'), 2),
            'absence_deduction' => round((float) $payroll->items->sum('absence_deduction'), 2),
            'overtime_pay' => round((float) $payroll->items->sum('overtime_pay'), 2),
            'holiday_pay' => round((float) $payroll->items->sum('holiday_pay'), 2),
            'rest_day_pay' => round((float) $payroll->items->sum('rest_day_pay'), 2),
            'leave_pay' => round((float) $payroll->items->sum('leave_pay'), 2),
            'sss_employee' => round((float) $payroll->items->sum('sss_employee'), 2),
            'philhealth_employee' => round((float) $payroll->items->sum('philhealth_employee'), 2),
            'pagibig_employee' => round((float) $payroll->items->sum('pagibig_employee'), 2),
            'withholding_tax' => 0,
            'total_employee_government_deductions' => round((float) $payroll->items->sum('total_employee_government_deductions'), 2),
            'other_deductions' => round((float) $payroll->items->sum('other_deductions'), 2),
            'net_pay' => round((float) $payroll->items->sum('net_pay'), 2),
        ];
    }

    protected function getItemSummaries(Payroll $payroll, PayrollItem $item)
    {
        return DailyAttendanceSummary::query()
            ->whereBetween('work_date', [
                $payroll->period_start->toDateString(),
                $payroll->period_end->toDateString(),
            ])
            ->where(function ($q) use ($item) {
                $matched = false;

                if (! empty($item->biometric_employee_id)) {
                    $q->orWhere('biometric_employee_id', $item->biometric_employee_id);
                    $matched = true;
                }

                if (! empty($item->employee_no)) {
                    $q->orWhere('employee_no', $item->employee_no);
                    $matched = true;
                }

                if (! empty($item->employee_name)) {
                    $q->orWhere('employee_name', $item->employee_name);
                    $matched = true;
                }

                if (! $matched) {
                    $q->whereRaw('1 = 0');
                }
            })
            ->orderBy('work_date')
            ->get();
    }

    protected function resolveCutoffRange(int $month, int $year, string $cutoffType): array
    {
        $base = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Manila')->startOfDay();

        if ($cutoffType === 'first') {
            $periodStart = $base->copy()->day(11)->startOfDay();
            $periodEnd = $base->copy()->day(25)->endOfDay();
        } else {
            $periodStart = $base->copy()->day(26)->startOfDay();
            $periodEnd = $base->copy()->addMonthNoOverflow()->day(10)->endOfDay();
        }

        return [$periodStart, $periodEnd];
    }

    protected function getDefaultCutoff(): array
    {
        $today = now('Asia/Manila')->startOfDay();

        if ($today->day >= 11 && $today->day <= 25) {
            return [
                (int) $today->month,
                (int) $today->year,
                'first',
            ];
        }

        if ($today->day >= 26) {
            return [
                (int) $today->month,
                (int) $today->year,
                'second',
            ];
        }

        $previousMonth = $today->copy()->subMonthNoOverflow();

        return [
            (int) $previousMonth->month,
            (int) $previousMonth->year,
            'second',
        ];
    }

    protected function generatePayrollNumber(int $year, int $month, string $cutoffType): string
    {
        $prefix = $cutoffType === 'first' ? '1' : '2';
        $base = sprintf('PR-%04d%02d-%s', $year, $month, $prefix);

        $exists = Payroll::query()->where('payroll_number', $base)->exists();

        if (! $exists) {
            return $base;
        }

        $counter = 2;

        do {
            $number = $base.'-'.$counter;
            $exists = Payroll::query()->where('payroll_number', $number)->exists();
            $counter++;
        } while ($exists);

        return $number;
    }

    protected function resolveEmployeeRates($summary): array
    {
        $query = PayrollEmployeeSalary::query()->where('is_active', true);

        $hasMatch = false;

        $query->where(function ($q) use ($summary, &$hasMatch) {
            if (! empty($summary->biometric_employee_id)) {
                $q->orWhere('biometric_employee_id', $summary->biometric_employee_id);
                $hasMatch = true;
            }

            if (! empty($summary->employee_no)) {
                $q->orWhere('employee_no', $summary->employee_no);
                $hasMatch = true;
            }

            if (! empty($summary->employee_name)) {
                $q->orWhere('employee_name', $summary->employee_name);
                $hasMatch = true;
            }
        });

        if (! $hasMatch) {
            return $this->emptyRatePayload();
        }

        $salary = $query->first();

        if (! $salary) {
            return $this->emptyRatePayload();
        }

        $monthlyRate = 0;
        $dailyRate = 0;

        if ($salary->rate_type === 'monthly') {
            $monthlyRate = (float) $salary->basic_salary;
            $dailyRate = $monthlyRate > 0 ? ($monthlyRate / 26) : 0;
        } else {
            $dailyRate = (float) $salary->basic_salary;
            $monthlyRate = $dailyRate > 0 ? ($dailyRate * 26) : 0;
        }

        $hourlyRate = $dailyRate > 0 ? ($dailyRate / 8) : 0;
        $minuteRate = $hourlyRate > 0 ? ($hourlyRate / 60) : 0;

        // Fallback rates:
        // if custom rates are not set, use computed regular salary rates
        $lateRatePerMinute = (float) ($salary->late_deduction_per_minute ?? 0);
        if ($lateRatePerMinute <= 0) {
            $lateRatePerMinute = $minuteRate;
        }

        $undertimeRatePerMinute = (float) ($salary->undertime_deduction_per_minute ?? 0);
        if ($undertimeRatePerMinute <= 0) {
            $undertimeRatePerMinute = $minuteRate;
        }

        $absentRatePerDay = (float) ($salary->absent_deduction_per_day ?? 0);
        if ($absentRatePerDay <= 0) {
            $absentRatePerDay = $dailyRate;
        }

        return [
            'employee_id' => null,
            'salary_id' => $salary->id,
            'monthly_rate' => round($monthlyRate, 2),
            'daily_rate' => round($dailyRate, 6),
            'hourly_rate' => round($hourlyRate, 6),
            'minute_rate' => round($minuteRate, 6),

            'allowance' => round((float) ($salary->allowance ?? 0), 2),
            'ot_rate_per_hour' => round((float) ($salary->ot_rate_per_hour ?? 0), 2),

            'late_deduction_per_minute' => round($lateRatePerMinute, 6),
            'undertime_deduction_per_minute' => round($undertimeRatePerMinute, 6),
            'absent_deduction_per_day' => round($absentRatePerDay, 2),

            'sss_loan' => round((float) ($salary->sss_loan ?? 0), 2),
            'pagibig_loan' => round((float) ($salary->pagibig_loan ?? 0), 2),
            'vale' => round((float) ($salary->vale ?? 0), 2),
            'other_loans' => round((float) ($salary->other_loans ?? 0), 2),
        ];
    }

    protected function emptyRatePayload(): array
    {
        return [
            'employee_id' => null,
            'salary_id' => null,
            'monthly_rate' => 0,
            'daily_rate' => 0,
            'hourly_rate' => 0,
            'minute_rate' => 0,
            'allowance' => 0,
            'ot_rate_per_hour' => 0,
            'late_deduction_per_minute' => 0,
            'undertime_deduction_per_minute' => 0,
            'absent_deduction_per_day' => 0,
            'sss_loan' => 0,
            'pagibig_loan' => 0,
            'vale' => 0,
            'other_loans' => 0,
        ];
    }

    protected function applyGovernmentDeductionRules(array $government, string $cutoffType): array
    {
        $government['withholding_tax'] = 0;

        if ($cutoffType === 'first') {
            $government['philhealth_employee'] = 0;
            $government['philhealth_employer'] = 0;
            $government['pagibig_employee'] = 0;
            $government['pagibig_employer'] = 0;
        } else {
            $government['sss_employee'] = 0;
            $government['sss_employer'] = 0;
        }

        $government['total_employee_government_deductions'] = round(
            (float) ($government['sss_employee'] ?? 0)
            + (float) ($government['philhealth_employee'] ?? 0)
            + (float) ($government['pagibig_employee'] ?? 0),
            2
        );

        $government['total_employer_government_contributions'] = round(
            (float) ($government['sss_employer'] ?? 0)
            + (float) ($government['philhealth_employer'] ?? 0)
            + (float) ($government['pagibig_employer'] ?? 0),
            2
        );

        return $government;
    }

    protected function columnExists(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function getApprovedOvertimeDates($summary, $startDate, $endDate): array
    {
        $query = PayrollAttendanceAdjustment::query()
            ->whereBetween('work_date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ]);

        $matched = false;

        $query->where(function ($q) use ($summary, &$matched) {
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

        return $query
            ->where(function ($q) {
                $q->where('adjustment_type', 'overtime')
                    ->orWhere('adjustment_type', 'ot');
            })
            ->where(function ($q) {
                if ($this->columnExists('payroll_attendance_adjustments', 'status')) {
                    $q->where('status', 'approved');
                } elseif ($this->columnExists('payroll_attendance_adjustments', 'is_approved')) {
                    $q->where('is_approved', true);
                }
            })
            ->pluck('work_date')
            ->map(function ($date) {
                return Carbon::parse($date)->toDateString();
            })
            ->unique()
            ->values()
            ->all();
    }
}
