@extends('layouts.app')

@section('title', 'Payroll Employee Detail')

@section('content')
    <div class="container-fluid" data-layout="container">
        <div class="content">

            @php
                $money = fn($value) => '₱ ' . number_format((float) $value, 2);

                $attendanceDeduction =
                    (float) $item->late_deduction +
                    (float) $item->undertime_deduction +
                    (float) $item->absence_deduction;

                $salaryDeduction = (float) $item->other_deductions;
                $governmentDeduction = (float) $item->total_employee_government_deductions;
                $totalCashDeductionsAfterGross = $salaryDeduction + $governmentDeduction;

                $payArchitecture = data_get($item->meta, 'pay_architecture', []);
                $attendanceBreakdown = data_get($item->meta, 'attendance_deductions', []);
                $allowanceBreakdown = data_get($item->meta, 'allowance', []);
                $holidayBreakdown = data_get($item->meta, 'holiday_breakdown', []);
                $restDayBreakdown = data_get($item->meta, 'rest_day_breakdown', []);
                $manualAdjustments = data_get($item->meta, 'manual_adjustments', []);

                $baseCutoffPay = (float) data_get($payArchitecture, 'base_cutoff_pay', $item->regular_pay);
                $payModel = (string) data_get($payArchitecture, 'money_model', $item->rate_type);

                $attendanceDeductedFromGross = (bool) data_get(
                    $item->meta,
                    'attendance_deductions_are_deducted_from_monthly_base',
                    false,
                );

                $allowancePerCutoff = (float) data_get($allowanceBreakdown, 'allowance_per_cutoff', 0);
                $holidayPay = (float) ($item->holiday_pay ?? 0);
                $restDayPay = (float) ($item->rest_day_pay ?? 0);
                $leavePay = (float) ($item->leave_pay ?? 0);
                $overtimePay = (float) ($item->overtime_pay ?? 0);
                $grossPay = (float) $item->gross_pay;
                $netPay = (float) $item->net_pay;

                $totalAdditions = round(
                    (float) $item->other_additions + $holidayPay + $restDayPay + $leavePay + $overtimePay,
                    2,
                );

                $workedHoursTotal = (float) ($summaries->sum('worked_minutes') / 60);
                $lateMinutesTotal = (int) $summaries->sum('late_minutes');
                $undertimeMinutesTotal = (int) $summaries->sum('undertime_minutes');

                $totalHolidayWorked = (int) data_get(
                    $holidayBreakdown,
                    'worked_days',
                    data_get($holidayBreakdown, 'total_holiday_worked', 0),
                );

                $totalRestDayWorked = (int) data_get(
                    $restDayBreakdown,
                    'worked_days',
                    data_get($restDayBreakdown, 'total_rest_day_worked', 0),
                );

                $divisorMeta = data_get($item->meta, 'pay_architecture.monthly_divisor_meta');

                $monthlyRate = (float) data_get($divisorMeta, 'monthly_rate', 0);
                $monthlyDivisor = (float) data_get($divisorMeta, 'monthly_salary_divisor', 0);
                $cutoffPaidDays = (float) data_get($divisorMeta, 'monthly_cutoff_paid_days', 0);
                $dailyRate = (float) data_get($divisorMeta, 'daily_rate_display', 0);

                if ($dailyRate <= 0 && $monthlyRate > 0 && $monthlyDivisor > 0) {
                    $dailyRate = round($monthlyRate / $monthlyDivisor, 2);
                }

                $monthlyCycleLabel =
                    $divisorMeta && $monthlyRate > 0 && $monthlyDivisor > 0 && $cutoffPaidDays > 0
                        ? 'Monthly: ' .
                            $money($monthlyRate) .
                            ' ÷ ' .
                            $monthlyDivisor .
                            ' days × ' .
                            $cutoffPaidDays .
                            ' days'
                        : 'Monthly: salary computation';

                $dailyRateLabel = $dailyRate > 0 ? 'Daily: ' . $money($dailyRate) . ' / day' : null;
            @endphp

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-body-tertiary border-bottom">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                        <div>
                            <h4 class="mb-1 text-dark">
                                {{ $item->employee_name }}
                            </h4>

                            <div class="text-muted small d-flex flex-wrap gap-3">
                                <span>
                                    <i class="fas fa-calendar-alt me-1 text-primary"></i>
                                    {{ $payroll->cutoff_label }}
                                </span>

                                @if ($item->employee_no)
                                    <span>
                                        <i class="fas fa-id-badge me-1 text-info"></i>
                                        Employee No: <strong>{{ $item->employee_no }}</strong>
                                    </span>
                                @endif

                                @if ($item->biometric_employee_id)
                                    <span>
                                        <i class="fas fa-fingerprint me-1 text-warning"></i>
                                        Bio ID: <strong>{{ $item->biometric_employee_id }}</strong>
                                    </span>
                                @endif

                                <span>
                                    <i class="fas fa-cogs me-1 text-secondary"></i>
                                    Model: <strong>{{ str_replace('_', ' ', ucfirst($payModel)) }}</strong>
                                </span>
                            </div>
                        </div>

                        <a href="{{ route('payroll.show', $payroll) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Back to Payroll
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6 col-xl-2">
                            <div class="border rounded-3 p-3 h-100 bg-primary-subtle border-primary">
                                <small class="text-primary fw-semibold d-block mb-1">
                                    Regular Base Pay
                                </small>

                                <h4 class="mb-0 text-primary">
                                    {{ $money($item->regular_pay) }}
                                </h4>

                                <small class="text-muted d-block">
                                    {{ $monthlyCycleLabel }}
                                </small>

                                @if ($dailyRateLabel)
                                    <small class="text-muted d-block">
                                        {{ $dailyRateLabel }}
                                    </small>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-6 col-xl-2">
                            <div class="border rounded-3 p-3 h-100 bg-warning-subtle border-warning">
                                <small class="text-warning fw-semibold d-block mb-1">
                                    Attendance Loss
                                </small>

                                <h4 class="mb-0 text-warning">
                                    {{ $money($attendanceDeduction) }}
                                </h4>

                                <small class="text-muted">
                                    {{ $attendanceDeductedFromGross ? 'Deducted from gross' : 'Already reflected in hours' }}
                                </small>
                            </div>
                        </div>

                        <div class="col-md-6 col-xl-2">
                            <div class="border rounded-3 p-3 h-100">
                                <small class="text-muted d-block mb-1">
                                    Additions
                                </small>

                                <h4 class="mb-0 text-dark">
                                    {{ $money($totalAdditions) }}
                                </h4>

                                <small class="text-muted">
                                    Allowance, holiday, rest, leave, OT
                                </small>
                            </div>
                        </div>

                        <div class="col-md-6 col-xl-2">
                            <div class="border rounded-3 p-3 h-100 bg-info-subtle border-info">
                                <small class="text-info fw-semibold d-block mb-1">
                                    Gross Pay
                                </small>

                                <h4 class="mb-0 text-info">
                                    {{ $money($grossPay) }}
                                </h4>

                                <small class="text-muted">
                                    Before gov./loan deductions
                                </small>
                            </div>
                        </div>

                        <div class="col-md-6 col-xl-2">
                            <div class="border rounded-3 p-3 h-100 bg-danger-subtle border-danger">
                                <small class="text-danger fw-semibold d-block mb-1">
                                    Gov. + Loans
                                </small>

                                <h4 class="mb-0 text-danger">
                                    {{ $money($totalCashDeductionsAfterGross) }}
                                </h4>

                                <small class="text-muted">
                                    After gross pay
                                </small>
                            </div>
                        </div>

                        <div class="col-md-6 col-xl-2">
                            <div class="border rounded-3 p-3 h-100 bg-success-subtle border-success">
                                <small class="text-success fw-semibold d-block mb-1">
                                    Net Pay
                                </small>

                                <h3 class="mb-0 text-success">
                                    {{ $money($netPay) }}
                                </h3>

                                <small class="text-muted">
                                    Final payable
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 mt-3 mb-0">
                        <strong>Payroll formula:</strong>

                        @if ($attendanceDeductedFromGross)
                            @if ($divisorMeta && $monthlyRate > 0 && $monthlyDivisor > 0 && $cutoffPaidDays > 0)
                                Regular Base Pay =
                                {{ $money($monthlyRate) }}
                                ÷ {{ number_format($monthlyDivisor, 0) }} days
                                × {{ number_format($cutoffPaidDays, 0) }} days
                                = {{ $money($item->regular_pay) }}.
                            @else
                                Regular Base Pay {{ $money($item->regular_pay) }}.
                            @endif

                            Then deduct Attendance Loss {{ $money($attendanceDeduction) }}
                            + Additions {{ $money($totalAdditions) }}
                            = Gross Pay {{ $money($grossPay) }}.
                            Then deduct Government and Loans/Other.
                        @else
                            Payable Hours × Hourly Rate = Regular Pay. Attendance loss is displayed for audit only and is
                            not deducted twice.
                        @endif
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-body-tertiary border-bottom">
                            <h6 class="mb-0">
                                <i class="fas fa-money-bill-wave me-2 text-primary"></i>
                                Payroll Formula
                            </h6>
                        </div>

                        <div class="card-body">
                            <table class="table table-sm align-middle mb-0">
                                <tbody>
                                    <tr>
                                        <td class="text-muted">Regular Base Pay</td>
                                        <td class="text-end fw-bold">
                                            {{ $money($item->regular_pay) }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="text-muted">Late Deduction</td>
                                        <td class="text-end text-warning">
                                            - {{ $money($item->late_deduction) }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="text-muted">Undertime Deduction</td>
                                        <td class="text-end text-warning">
                                            - {{ $money($item->undertime_deduction) }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="text-muted">Absence Deduction</td>
                                        <td class="text-end text-danger">
                                            - {{ $money($item->absence_deduction) }}
                                        </td>
                                    </tr>

                                    <tr class="table-light">
                                        <td class="fw-bold">Total Attendance Loss</td>
                                        <td class="text-end fw-bold text-danger">
                                            - {{ $money($attendanceDeduction) }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="text-muted">Allowance This Cutoff</td>
                                        <td class="text-end text-primary">
                                            + {{ $money($allowancePerCutoff) }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="text-muted">Holiday Pay</td>
                                        <td class="text-end text-info">
                                            + {{ $money($holidayPay) }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="text-muted">Rest Day Pay</td>
                                        <td class="text-end text-warning">
                                            + {{ $money($restDayPay) }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="text-muted">Leave Pay</td>
                                        <td class="text-end text-secondary">
                                            + {{ $money($leavePay) }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="text-muted">Overtime Pay</td>
                                        <td class="text-end text-info">
                                            + {{ $money($overtimePay) }}
                                        </td>
                                    </tr>

                                    <tr class="table-info">
                                        <td class="fw-bold">Gross Pay</td>
                                        <td class="text-end fw-bold">
                                            {{ $money($grossPay) }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="text-muted">Salary / Loan Deductions</td>
                                        <td class="text-end text-danger">
                                            - {{ $money($salaryDeduction) }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="text-muted">Government Deduction</td>
                                        <td class="text-end text-danger">
                                            - {{ $money($governmentDeduction) }}
                                        </td>
                                    </tr>

                                    <tr class="table-success">
                                        <td class="fw-bold">Net Pay</td>
                                        <td class="text-end fw-bold text-success fs-6">
                                            {{ $money($netPay) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            @if (!empty($attendanceBreakdown))
                                <hr>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="border rounded-3 p-3 h-100">
                                            <small class="text-muted d-block">Late</small>

                                            <div class="fw-semibold">
                                                {{ number_format((float) data_get($attendanceBreakdown, 'late_minutes', 0), 0) }}
                                                min
                                            </div>

                                            <small class="text-muted">
                                                Rate:
                                                {{ $money(data_get($attendanceBreakdown, 'late_rate_per_minute', 0)) }}/min
                                            </small>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="border rounded-3 p-3 h-100">
                                            <small class="text-muted d-block">Undertime</small>

                                            <div class="fw-semibold">
                                                {{ number_format((float) data_get($attendanceBreakdown, 'undertime_minutes', 0), 0) }}
                                                min
                                            </div>

                                            <small class="text-muted">
                                                Rate:
                                                {{ $money(data_get($attendanceBreakdown, 'undertime_rate_per_minute', 0)) }}/min
                                            </small>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="border rounded-3 p-3 h-100">
                                            <small class="text-muted d-block">Absence</small>

                                            <div class="fw-semibold">
                                                {{ number_format((float) data_get($attendanceBreakdown, 'absent_days', 0), 0) }}
                                                day(s)
                                            </div>

                                            <small class="text-muted">
                                                Rate:
                                                {{ $money(data_get($attendanceBreakdown, 'absence_rate_per_day', 0)) }}/day
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-body-tertiary border-bottom">
                            <h6 class="mb-0">
                                <i class="fas fa-file-invoice-dollar me-2 text-danger"></i>
                                Government Deductions
                            </h6>
                        </div>

                        <div class="card-body">
                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span class="text-muted">SSS Employee</span>
                                <strong>{{ $money($item->sss_employee) }}</strong>
                            </div>

                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span class="text-muted">PhilHealth Employee</span>
                                <strong>{{ $money($item->philhealth_employee) }}</strong>
                            </div>

                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span class="text-muted">Pag-IBIG Employee</span>
                                <strong>{{ $money($item->pagibig_employee) }}</strong>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="fw-semibold text-muted">Total</span>
                                <span class="fw-bold text-danger fs-6">
                                    {{ $money($governmentDeduction) }}
                                </span>
                            </div>

                            <hr>

                            <small class="text-muted d-block">
                                Government schedule source:
                                {{ is_array(data_get($item->meta, 'government_schedule')) ? 'Employee profile / config' : data_get($item->meta, 'government_schedule', 'Default') }}
                            </small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-body-tertiary border-bottom">
                            <h6 class="mb-0">
                                <i class="fas fa-clock me-2 text-info"></i>
                                Attendance Audit
                            </h6>
                        </div>

                        <div class="card-body">
                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span class="text-muted">Payable Days</span>
                                <strong>{{ number_format($item->total_payable_days, 2) }}</strong>
                            </div>

                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span class="text-muted">Payable Hours</span>
                                <strong>{{ number_format($item->total_payable_hours, 2) }}</strong>
                            </div>

                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span class="text-muted">Worked Hours</span>
                                <strong>{{ number_format($workedHoursTotal, 2) }}</strong>
                            </div>

                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span class="text-muted">Late / UT</span>
                                <strong>{{ $lateMinutesTotal }} / {{ $undertimeMinutesTotal }} min</strong>
                            </div>

                            <div class="d-flex justify-content-between border-bottom py-2">
                                <span class="text-muted">Holiday Worked</span>
                                <strong>{{ $totalHolidayWorked }}</strong>
                            </div>

                            <div class="d-flex justify-content-between py-2">
                                <span class="text-muted">Rest Day Worked</span>
                                <strong>{{ $totalRestDayWorked }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @include('payroll.items.partials.attendance-audit-table', [
                'summaries' => $summaries,
            ])

        </div>
    </div>
@endsection
