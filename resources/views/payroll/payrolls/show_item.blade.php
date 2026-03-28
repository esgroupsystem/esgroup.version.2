@extends('layouts.app')

@section('title', 'Payroll Employee Detail')

@section('content')
    <div class="container-fluid" data-layout="container">
        <div class="content">

            @php
                $attendanceDeduction =
                    (float) $item->late_deduction +
                    (float) $item->undertime_deduction +
                    (float) $item->absence_deduction;

                $salaryDeduction = (float) $item->other_deductions;
                $governmentDeduction = (float) $item->total_employee_government_deductions;
                $totalAllDeductions = $attendanceDeduction + $salaryDeduction + $governmentDeduction;

                $salaryBreakdown = data_get($item->meta, 'salary_deductions', []);
                $attendanceBreakdown = data_get($item->meta, 'attendance_deductions', []);
                $allowanceBreakdown = data_get($item->meta, 'allowance', []);

                $monthlyAllowance = (float) data_get($allowanceBreakdown, 'monthly_allowance', 0);
                $allowancePerCutoff = (float) data_get(
                    $allowanceBreakdown,
                    'allowance_per_cutoff',
                    $item->other_additions ?? 0,
                );

                // ADD ALLOWANCE TO PAY TOTALS
                $grossWithAllowance = (float) $item->gross_pay + $allowancePerCutoff;
                $finalNetPay = (float) $item->net_pay + $allowancePerCutoff;

                $workedHoursTotal = (float) ($summaries->sum('worked_minutes') / 60);
                $overtimeHoursTotal = (float) ($summaries->sum('overtime_minutes') / 60);
                $lateMinutesTotal = (int) $summaries->sum('late_minutes');
                $undertimeMinutesTotal = (int) $summaries->sum('undertime_minutes');
                $payableDaysTotal = (float) $summaries->sum('payable_days');
                $payableHoursTotal = (float) $summaries->sum('payable_hours');
            @endphp

            {{-- Header / Employee Overview --}}
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-body-tertiary border-bottom">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                        <div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar avatar-xl">
                                    <div class="avatar-name rounded-circle bg-primary-subtle text-primary">
                                        <span class="fs-4 fw-bold">
                                            {{ strtoupper(substr($item->employee_name ?? 'N', 0, 1)) }}
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="mb-1 text-dark">{{ $item->employee_name }}</h4>
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
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-lg-end">
                            <a href="{{ route('payroll.show', $payroll) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Payroll
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6 col-xl">
                            <div class="border rounded-3 p-3 h-100 bg-body">
                                <small class="text-muted d-block mb-1">Gross Pay</small>
                                <h5 class="mb-0 text-dark">₱ {{ number_format($item->gross_pay, 2) }}</h5>
                            </div>
                        </div>

                        <div class="col-md-6 col-xl">
                            <div class="border rounded-3 p-3 h-100 bg-primary-subtle border-primary">
                                <small class="text-primary d-block mb-1 fw-semibold">Allowance This Cutoff</small>
                                <h5 class="mb-0 text-primary fw-bold">₱ {{ number_format($allowancePerCutoff, 2) }}</h5>
                            </div>
                        </div>

                        <div class="col-md-6 col-xl">
                            <div class="border rounded-3 p-3 h-100 bg-info-subtle border-info">
                                <small class="text-info d-block mb-1 fw-semibold">Gross + Allowance</small>
                                <h5 class="mb-0 text-info fw-bold">₱ {{ number_format($grossWithAllowance, 2) }}</h5>
                            </div>
                        </div>

                        <div class="col-md-6 col-xl">
                            <div class="border rounded-3 p-3 h-100 bg-body">
                                <small class="text-muted d-block mb-1">Attendance Deductions</small>
                                <h5 class="mb-0 text-warning">₱ {{ number_format($attendanceDeduction, 2) }}</h5>
                            </div>
                        </div>

                        <div class="col-md-6 col-xl">
                            <div class="border rounded-3 p-3 h-100 bg-body">
                                <small class="text-muted d-block mb-1">Salary Deductions</small>
                                <h5 class="mb-0 text-danger">₱ {{ number_format($salaryDeduction, 2) }}</h5>
                            </div>
                        </div>

                        <div class="col-md-6 col-xl">
                            <div class="border rounded-3 p-3 h-100 bg-body">
                                <small class="text-muted d-block mb-1">Government Deduction</small>
                                <h5 class="mb-0 text-danger">₱ {{ number_format($governmentDeduction, 2) }}</h5>
                            </div>
                        </div>

                        <div class="col-md-6 col-xl">
                            <div class="border rounded-3 p-3 h-100 bg-body">
                                <small class="text-muted d-block mb-1">Overtime Pay</small>
                                <h5 class="mb-0 text-info">₱ {{ number_format($item->overtime_pay, 2) }}</h5>
                            </div>
                        </div>

                        <div class="col-md-6 col-xl">
                            <div class="border rounded-3 p-3 h-100 bg-body">
                                <small class="text-muted d-block mb-1">Payable Days</small>
                                <h5 class="mb-0 text-dark">{{ number_format($item->total_payable_days, 2) }}</h5>
                            </div>
                        </div>

                        <div class="col-md-6 col-xl">
                            <div class="border rounded-3 p-3 h-100 bg-danger-subtle border-danger">
                                <small class="text-danger d-block mb-1 fw-semibold">Total All Deductions</small>
                                <h5 class="mb-0 text-danger fw-bold">₱ {{ number_format($totalAllDeductions, 2) }}</h5>
                            </div>
                        </div>

                        <div class="col-md-6 col-xl">
                            <div class="border rounded-3 p-3 h-100 bg-success-subtle border-success">
                                <small class="text-success d-block mb-1 fw-semibold">Net Pay (with Allowance)</small>
                                <h4 class="mb-0 text-success fw-bold">₱ {{ number_format($finalNetPay, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Breakdown Cards --}}
            <div class="row g-3 mb-3">
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-body-tertiary border-bottom">
                            <h6 class="mb-0">
                                <i class="fas fa-money-bill-wave me-2 text-primary"></i>
                                Payroll Breakdown
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="text-muted">Gross Pay</td>
                                            <td class="text-end fw-semibold">₱ {{ number_format($item->gross_pay, 2) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Allowance This Cutoff</td>
                                            <td class="text-end text-primary fw-semibold">
                                                + ₱ {{ number_format($allowancePerCutoff, 2) }}
                                            </td>
                                        </tr>

                                        @if ($monthlyAllowance > 0)
                                            <tr>
                                                <td class="text-muted">Monthly Allowance</td>
                                                <td class="text-end fw-semibold">
                                                    ₱ {{ number_format($monthlyAllowance, 2) }}
                                                </td>
                                            </tr>
                                        @endif

                                        <tr class="table-light">
                                            <td class="fw-bold">Gross + Allowance</td>
                                            <td class="text-end fw-bold text-info">
                                                ₱ {{ number_format($grossWithAllowance, 2) }}
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="text-muted">Late Deduction</td>
                                            <td class="text-end text-warning fw-semibold">
                                                - ₱ {{ number_format($item->late_deduction, 2) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Undertime Deduction</td>
                                            <td class="text-end text-warning fw-semibold">
                                                - ₱ {{ number_format($item->undertime_deduction, 2) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Absence Deduction</td>
                                            <td class="text-end text-danger fw-semibold">
                                                - ₱ {{ number_format($item->absence_deduction, 2) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Total Attendance Deductions</td>
                                            <td class="text-end text-danger fw-bold">
                                                - ₱ {{ number_format($attendanceDeduction, 2) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Salary Deductions</td>
                                            <td class="text-end text-danger fw-semibold">
                                                - ₱ {{ number_format($salaryDeduction, 2) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Government Deduction</td>
                                            <td class="text-end text-danger fw-semibold">
                                                - ₱ {{ number_format($governmentDeduction, 2) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Overtime Pay</td>
                                            <td class="text-end text-info fw-semibold">
                                                ₱ {{ number_format($item->overtime_pay, 2) }}
                                            </td>
                                        </tr>
                                        <tr class="table-success">
                                            <td class="fw-bold">Net Pay (with Allowance)</td>
                                            <td class="text-end fw-bold text-success fs-6">
                                                ₱ {{ number_format($finalNetPay, 2) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            @if (!empty($attendanceBreakdown))
                                <hr>
                                <div>
                                    <h6 class="mb-3 text-dark">Attendance Deduction Details</h6>
                                    <div class="row g-3">
                                        <div class="col-sm-4">
                                            <div class="border rounded-3 p-3 h-100">
                                                <small class="text-muted d-block mb-1">Late</small>
                                                <div class="fw-semibold">
                                                    {{ number_format((float) data_get($attendanceBreakdown, 'late_minutes', 0), 0) }}
                                                    min
                                                </div>
                                                <small class="text-muted d-block">
                                                    Rate: ₱
                                                    {{ number_format((float) data_get($attendanceBreakdown, 'late_rate_per_minute', 0), 4) }}/min
                                                </small>
                                                <div class="text-warning fw-bold mt-2">
                                                    ₱
                                                    {{ number_format((float) data_get($attendanceBreakdown, 'late_deduction', 0), 2) }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="border rounded-3 p-3 h-100">
                                                <small class="text-muted d-block mb-1">Undertime</small>
                                                <div class="fw-semibold">
                                                    {{ number_format((float) data_get($attendanceBreakdown, 'undertime_minutes', 0), 0) }}
                                                    min
                                                </div>
                                                <small class="text-muted d-block">
                                                    Rate: ₱
                                                    {{ number_format((float) data_get($attendanceBreakdown, 'undertime_rate_per_minute', 0), 4) }}/min
                                                </small>
                                                <div class="text-warning fw-bold mt-2">
                                                    ₱
                                                    {{ number_format((float) data_get($attendanceBreakdown, 'undertime_deduction', 0), 2) }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-sm-4">
                                            <div class="border rounded-3 p-3 h-100">
                                                <small class="text-muted d-block mb-1">Absence</small>
                                                <div class="fw-semibold">
                                                    {{ number_format((float) data_get($attendanceBreakdown, 'absent_days', 0), 0) }}
                                                    day(s)
                                                </div>
                                                <small class="text-muted d-block">
                                                    Rate: ₱
                                                    {{ number_format((float) data_get($attendanceBreakdown, 'absence_rate_per_day', 0), 2) }}/day
                                                </small>
                                                <div class="text-danger fw-bold mt-2">
                                                    ₱
                                                    {{ number_format((float) data_get($attendanceBreakdown, 'absence_deduction', 0), 2) }}
                                                </div>
                                            </div>
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
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="border rounded-3 p-3 h-100">
                                        <small class="text-muted d-block mb-1">SSS Employee Share</small>
                                        <h6 class="mb-0">₱ {{ number_format($item->sss_employee, 2) }}</h6>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="border rounded-3 p-3 h-100">
                                        <small class="text-muted d-block mb-1">PhilHealth Employee Share</small>
                                        <h6 class="mb-0">₱ {{ number_format($item->philhealth_employee, 2) }}</h6>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="border rounded-3 p-3 h-100">
                                        <small class="text-muted d-block mb-1">Pag-IBIG Employee Share</small>
                                        <h6 class="mb-0">₱ {{ number_format($item->pagibig_employee, 2) }}</h6>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="small text-muted mb-2">
                                {{ data_get($item->meta, 'government_schedule', 'Government deduction schedule') }}
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold text-muted">Total Government Deduction</span>
                                <span class="fw-bold text-danger fs-6">
                                    ₱ {{ number_format($governmentDeduction, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-body-tertiary border-bottom">
                            <h6 class="mb-0">
                                <i class="fas fa-minus-circle me-2 text-warning"></i>
                                Salary Deductions
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="border rounded-3 p-3 h-100">
                                        <small class="text-muted d-block mb-1">SSS Loan</small>
                                        <h6 class="mb-0">₱
                                            {{ number_format((float) data_get($salaryBreakdown, 'sss_loan', 0), 2) }}</h6>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="border rounded-3 p-3 h-100">
                                        <small class="text-muted d-block mb-1">Pag-IBIG Loan</small>
                                        <h6 class="mb-0">₱
                                            {{ number_format((float) data_get($salaryBreakdown, 'pagibig_loan', 0), 2) }}
                                        </h6>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="border rounded-3 p-3 h-100">
                                        <small class="text-muted d-block mb-1">Vale</small>
                                        <h6 class="mb-0">₱
                                            {{ number_format((float) data_get($salaryBreakdown, 'vale', 0), 2) }}</h6>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="border rounded-3 p-3 h-100">
                                        <small class="text-muted d-block mb-1">Other Loans</small>
                                        <h6 class="mb-0">₱
                                            {{ number_format((float) data_get($salaryBreakdown, 'other_loans', 0), 2) }}
                                        </h6>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold text-muted">Total Salary Deductions</span>
                                <span class="fw-bold text-danger fs-6">
                                    ₱ {{ number_format($salaryDeduction, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Daily Attendance Breakdown --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-body-tertiary border-bottom">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2">
                        <div>
                            <h6 class="mb-0">
                                <i class="fas fa-calendar-check me-2 text-primary"></i>
                                Daily Attendance Breakdown
                            </h6>
                            <small class="text-muted">
                                Detailed day-by-day attendance, schedule, actual logs, payable hours, and remarks.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr class="text-nowrap">
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Schedule</th>
                                    <th>Actual</th>
                                    <th class="text-center">Late</th>
                                    <th class="text-center">UT</th>
                                    <th class="text-center">Worked</th>
                                    <th class="text-center">OT</th>
                                    <th class="text-center">Payable</th>
                                    <th style="min-width: 260px;">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($summaries as $row)
                                    @php
                                        $status = strtolower($row->attendance_status ?? 'n/a');

                                        $badgeClass = match ($status) {
                                            'present' => 'success',
                                            'late' => 'warning',
                                            'undertime' => 'warning',
                                            'absent' => 'danger',
                                            'rest_day', 'rest day', 'rest_day_worked' => 'secondary',
                                            'holiday', 'holiday_worked' => 'info',
                                            'leave' => 'primary',
                                            default => 'dark',
                                        };
                                    @endphp

                                    <tr>
                                        <td class="text-nowrap fw-semibold">
                                            {{ optional($row->work_date)->format('M d, Y') }}
                                        </td>

                                        <td class="text-nowrap">
                                            <span class="badge bg-{{ $badgeClass }}">
                                                {{ strtoupper(str_replace('_', ' ', $row->attendance_status ?? 'N/A')) }}
                                            </span>
                                        </td>

                                        <td class="text-nowrap">
                                            {{ $row->scheduled_time_in ? \Carbon\Carbon::parse($row->scheduled_time_in)->format('h:i A') : '--' }}
                                            -
                                            {{ $row->scheduled_time_out ? \Carbon\Carbon::parse($row->scheduled_time_out)->format('h:i A') : '--' }}
                                        </td>

                                        <td class="text-nowrap">
                                            {{ $row->actual_time_in ? \Carbon\Carbon::parse($row->actual_time_in)->format('h:i A') : '--' }}
                                            -
                                            {{ $row->actual_time_out ? \Carbon\Carbon::parse($row->actual_time_out)->format('h:i A') : '--' }}
                                        </td>

                                        <td class="text-center text-warning fw-semibold">
                                            {{ $row->late_minutes ?? 0 }} min
                                        </td>

                                        <td class="text-center text-warning fw-semibold">
                                            {{ $row->undertime_minutes ?? 0 }} min
                                        </td>

                                        <td class="text-center">
                                            <span class="fw-semibold">
                                                {{ number_format(($row->worked_minutes ?? 0) / 60, 2) }} hr
                                            </span>
                                        </td>

                                        <td class="text-center text-info fw-semibold">
                                            {{ number_format(($row->overtime_minutes ?? 0) / 60, 2) }} hr
                                        </td>

                                        <td class="text-center">
                                            <div class="fw-semibold">
                                                {{ number_format($row->payable_days ?? 0, 2) }} day
                                            </div>
                                            <small class="text-muted">
                                                {{ number_format($row->payable_hours ?? 0, 2) }} hr
                                            </small>
                                        </td>

                                        <td>
                                            <span class="text-muted">
                                                {{ $row->remarks ?: '—' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-5">
                                            <i class="fas fa-folder-open fa-2x mb-2 d-block text-muted"></i>
                                            No daily attendance summary found for this employee.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            @if ($summaries->isNotEmpty())
                                <tfoot class="table-light">
                                    <tr class="fw-bold">
                                        <th colspan="4" class="text-end">TOTAL</th>
                                        <th class="text-center text-warning">
                                            {{ $lateMinutesTotal }} min
                                        </th>
                                        <th class="text-center text-warning">
                                            {{ $undertimeMinutesTotal }} min
                                        </th>
                                        <th class="text-center">
                                            {{ number_format($workedHoursTotal, 2) }} hr
                                        </th>
                                        <th class="text-center text-info">
                                            {{ number_format($overtimeHoursTotal, 2) }} hr
                                        </th>
                                        <th class="text-center">
                                            <div>{{ number_format($payableDaysTotal, 2) }} day</div>
                                            <small>{{ number_format($payableHoursTotal, 2) }} hr</small>
                                        </th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
