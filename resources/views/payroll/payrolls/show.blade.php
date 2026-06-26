@extends('layouts.app')

@section('title', 'Payroll Details')

@section('content')
    @php
        $items = collect($payroll->items ?? []);

        $money = fn($value) => '₱ ' . number_format((float) $value, 2);
        $num = fn($value, $decimals = 2) => number_format((float) $value, $decimals);

        $totalEmployees = (int) data_get($totals, 'employees', $items->count());

        $totalRegularPay = (float) data_get($totals, 'regular_pay', $items->sum('regular_pay'));
        $totalGrossPay = (float) data_get($totals, 'gross_pay', $items->sum('gross_pay'));

        $totalHolidayPay = (float) data_get($totals, 'holiday_pay', $items->sum('holiday_pay'));
        $totalRestDayPay = (float) data_get($totals, 'rest_day_pay', $items->sum('rest_day_pay'));
        $totalOvertimePay = (float) data_get($totals, 'overtime_pay', $items->sum('overtime_pay'));
        $totalLeavePay = (float) data_get($totals, 'leave_pay', $items->sum('leave_pay'));
        $totalOtherAdditions = (float) data_get($totals, 'other_additions', $items->sum('other_additions'));

        $totalAdditions =
            $totalHolidayPay + $totalRestDayPay + $totalOvertimePay + $totalLeavePay + $totalOtherAdditions;

        $totalGovernmentDeductions = (float) data_get(
            $totals,
            'total_employee_government_deductions',
            $items->sum('total_employee_government_deductions'),
        );

        $totalOtherDeductions = (float) data_get($totals, 'other_deductions', $items->sum('other_deductions'));

        $totalDeductions = $totalGovernmentDeductions + $totalOtherDeductions;
        $totalNetPay = (float) data_get($totals, 'net_pay', $items->sum('net_pay'));

        $totalPayableDays = (float) $items->sum('total_payable_days');
        $totalPayableHours = (float) $items->sum('total_payable_hours');

        $employeesWithAdditions = $items
            ->filter(function ($item) {
                return (float) ($item->holiday_pay ?? 0) +
                    (float) ($item->rest_day_pay ?? 0) +
                    (float) ($item->overtime_pay ?? 0) +
                    (float) ($item->leave_pay ?? 0) +
                    (float) ($item->other_additions ?? 0) >
                    0;
            })
            ->count();

        $employeesWithDeductions = $items
            ->filter(function ($item) {
                return (float) ($item->total_employee_government_deductions ?? 0) +
                    (float) ($item->other_deductions ?? 0) >
                    0;
            })
            ->count();

        $employeesNeedsChecking = $items
            ->filter(function ($item) {
                $regularPay = (float) ($item->regular_pay ?? 0);
                $grossPay = (float) ($item->gross_pay ?? 0);
                $netPay = (float) ($item->net_pay ?? 0);
                $payableDays = (float) ($item->total_payable_days ?? 0);
                $payableHours = (float) ($item->total_payable_hours ?? 0);

                $deductions =
                    (float) ($item->total_employee_government_deductions ?? 0) + (float) ($item->other_deductions ?? 0);

                return $regularPay <= 0 ||
                    $grossPay <= 0 ||
                    $netPay <= 0 ||
                    ($payableDays <= 0 && $payableHours <= 0) ||
                    ($grossPay > 0 && $deductions > $grossPay * 0.6);
            })
            ->count();

        $statusTone = match ($payroll->status) {
            'finalized' => 'success',
            'processing' => 'info',
            'draft' => 'warning',
            default => 'secondary',
        };

        $netRate = $totalGrossPay > 0 ? ($totalNetPay / $totalGrossPay) * 100 : 0;
        $deductionRate = $totalGrossPay > 0 ? ($totalDeductions / $totalGrossPay) * 100 : 0;
    @endphp

    @once
        <style>
            .payroll-page {
                font-size: .875rem;
            }

            .payroll-header-card {
                border-radius: .75rem;
            }

            .payroll-muted {
                color: var(--falcon-600, #748194);
            }

            .payroll-soft-box {
                border: 1px solid var(--falcon-border-color, #d8e2ef);
                border-radius: .75rem;
                background: var(--falcon-card-bg, #fff);
                height: 100%;
                padding: 1rem;
            }

            .payroll-kpi-label {
                font-size: .68rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .04em;
                color: var(--falcon-600, #748194);
                margin-bottom: .35rem;
            }

            .payroll-kpi-value {
                font-size: 1.15rem;
                font-weight: 700;
                color: var(--falcon-900, #344050);
                margin-bottom: .2rem;
            }

            .payroll-kpi-note {
                font-size: .72rem;
                color: var(--falcon-600, #748194);
            }

            .payroll-mini-card {
                border: 1px solid var(--falcon-border-color, #d8e2ef);
                border-radius: .65rem;
                padding: .85rem;
                background: var(--falcon-gray-100, #f9fafd);
            }

            .payroll-mini-card .label {
                font-size: .72rem;
                color: var(--falcon-600, #748194);
            }

            .payroll-mini-card .value {
                font-weight: 700;
                color: var(--falcon-900, #344050);
            }

            .payroll-breakdown-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                padding: .65rem 0;
                border-bottom: 1px dashed var(--falcon-border-color, #d8e2ef);
            }

            .payroll-breakdown-row:last-child {
                border-bottom: 0;
            }

            .payroll-breakdown-row span {
                color: var(--falcon-600, #748194);
            }

            .payroll-breakdown-row strong {
                color: var(--falcon-900, #344050);
                white-space: nowrap;
            }

            .payroll-progress {
                height: .45rem;
                border-radius: 999px;
                background: var(--falcon-gray-200, #edf2f9);
                overflow: hidden;
            }

            .payroll-progress-bar {
                height: 100%;
                border-radius: inherit;
            }

            .payroll-table {
                font-size: .78rem;
            }

            .payroll-table thead th {
                background: var(--falcon-gray-100, #f9fafd);
                color: var(--falcon-700, #5e6e82);
                text-transform: uppercase;
                letter-spacing: .04em;
                font-size: .65rem;
                font-weight: 700;
                white-space: nowrap;
                border-bottom: 1px solid var(--falcon-border-color, #d8e2ef);
                vertical-align: middle;
            }

            .payroll-table tbody td {
                vertical-align: middle;
                border-bottom: 1px solid var(--falcon-border-color, #edf2f9);
            }

            .payroll-table tbody tr.row-review {
                background: rgba(var(--falcon-warning-rgb, 244, 174, 0), .06);
            }

            .payroll-table tbody tr.row-danger {
                background: rgba(var(--falcon-danger-rgb, 230, 55, 87), .045);
            }

            .payroll-employee-name {
                min-width: 190px;
            }

            .payroll-employee-name .name {
                font-weight: 700;
                color: var(--falcon-900, #344050);
            }

            .payroll-employee-name .meta {
                font-size: .7rem;
                color: var(--falcon-600, #748194);
                line-height: 1.45;
            }

            .payroll-amount {
                font-weight: 700;
                white-space: nowrap;
            }

            .payroll-sub {
                display: block;
                margin-top: .15rem;
                font-size: .68rem;
                color: var(--falcon-600, #748194);
                white-space: nowrap;
            }

            .payroll-audit-badges {
                display: flex;
                flex-wrap: wrap;
                gap: .25rem;
                justify-content: flex-end;
                min-width: 150px;
            }

            .payroll-action-col {
                min-width: 75px;
            }

            .payroll-empty-state {
                padding: 3rem 1rem;
                text-align: center;
                color: var(--falcon-600, #748194);
            }

            @media (max-width: 767.98px) {

                .payroll-action-group,
                .payroll-action-group .btn,
                .payroll-action-group form,
                .payroll-action-group form button {
                    width: 100%;
                }
            }
        </style>
    @endonce

    <div class="container-fluid payroll-page" data-layout="container">
        <div class="content">
            @if (session('success'))
                <div class="alert alert-success border-0 shadow-sm">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="card border-0 shadow-sm mb-3 payroll-header-card">
                <div class="card-header bg-body-tertiary border-bottom py-3">
                    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                        <div>
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <h4 class="mb-0 text-900 fw-bold">
                                    {{ $payroll->payroll_number }}
                                </h4>

                                <span class="badge badge-subtle-{{ $statusTone }} text-{{ $statusTone }}">
                                    {{ ucfirst($payroll->status) }}
                                </span>

                                @if ($employeesNeedsChecking > 0)
                                    <span class="badge badge-subtle-danger text-danger">
                                        {{ number_format($employeesNeedsChecking) }} for review
                                    </span>
                                @else
                                    <span class="badge badge-subtle-success text-success">
                                        Audit clean
                                    </span>
                                @endif
                            </div>

                            <div class="d-flex flex-wrap gap-3 payroll-muted fs-10">
                                <span>
                                    <i class="fas fa-calendar me-1 text-primary"></i>
                                    {{ $payroll->cutoff_label }}
                                </span>

                                <span>
                                    <i class="fas fa-calendar-check me-1 text-info"></i>
                                    Contribution: {{ $payroll->contribution_label }}
                                </span>

                                <span>
                                    <i class="fas fa-clock me-1 text-warning"></i>
                                    {{ optional($payroll->period_start)->format('M d, Y') }}
                                    -
                                    {{ optional($payroll->period_end)->format('M d, Y') }}
                                </span>

                                <span>
                                    <i class="fas fa-users me-1 text-success"></i>
                                    {{ number_format($totalEmployees) }} employees
                                </span>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 payroll-action-group">
                            <a href="{{ route('payroll.export.excel', $payroll) }}" class="btn btn-falcon-success btn-sm">
                                <i class="fas fa-file-excel me-1"></i>
                                Excel
                            </a>

                            <a href="{{ route('payroll.export.pdf', $payroll) }}" target="_blank" rel="noopener"
                                class="btn btn-falcon-danger btn-sm">
                                <i class="fas fa-file-pdf me-1"></i>
                                Payslips PDF
                            </a>

                            <a href="{{ route('payroll.index') }}" class="btn btn-falcon-default btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>
                                Back
                            </a>

                            @if ($payroll->status !== 'finalized')
                                <form method="POST" action="{{ route('payroll.finalize', $payroll) }}"
                                    onsubmit="return confirm('Finalize this payroll?')">
                                    @csrf

                                    <button type="submit" class="btn btn-falcon-primary btn-sm">
                                        <i class="fas fa-lock me-1"></i>
                                        Finalize
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-body bg-white">
                    <div class="row g-3">
                        <div class="col-sm-6 col-xl-2">
                            <div class="payroll-soft-box">
                                <div class="payroll-kpi-label">Employees</div>
                                <div class="payroll-kpi-value">{{ number_format($totalEmployees) }}</div>
                                <div class="payroll-kpi-note">Included records</div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-2">
                            <div class="payroll-soft-box">
                                <div class="payroll-kpi-label">Regular Pay</div>
                                <div class="payroll-kpi-value">{{ $money($totalRegularPay) }}</div>
                                <div class="payroll-kpi-note">Base salary amount</div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-2">
                            <div class="payroll-soft-box">
                                <div class="payroll-kpi-label">Additions</div>
                                <div class="payroll-kpi-value text-info">{{ $money($totalAdditions) }}</div>
                                <div class="payroll-kpi-note">Holiday, rest, OT</div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-2">
                            <div class="payroll-soft-box">
                                <div class="payroll-kpi-label">Gross Pay</div>
                                <div class="payroll-kpi-value">{{ $money($totalGrossPay) }}</div>
                                <div class="payroll-kpi-note">Before deduction</div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-2">
                            <div class="payroll-soft-box">
                                <div class="payroll-kpi-label">Deductions</div>
                                <div class="payroll-kpi-value text-danger">{{ $money($totalDeductions) }}</div>
                                <div class="payroll-kpi-note">Gov. + other</div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-2">
                            <div class="payroll-soft-box">
                                <div class="payroll-kpi-label">Net Pay</div>
                                <div class="payroll-kpi-value text-success">{{ $money($totalNetPay) }}</div>
                                <div class="payroll-kpi-note">Final payable</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-xl-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-body-tertiary border-bottom">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-clipboard-check me-2 text-primary"></i>
                                Payroll Audit Summary
                            </h6>
                        </div>

                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="payroll-mini-card">
                                        <div class="label">With Additions</div>
                                        <div class="value text-info">
                                            {{ number_format($employeesWithAdditions) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-6">
                                    <div class="payroll-mini-card">
                                        <div class="label">With Deductions</div>
                                        <div class="value text-danger">
                                            {{ number_format($employeesWithDeductions) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-6">
                                    <div class="payroll-mini-card">
                                        <div class="label">For Review</div>
                                        <div
                                            class="value {{ $employeesNeedsChecking > 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format($employeesNeedsChecking) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-6">
                                    <div class="payroll-mini-card">
                                        <div class="label">Clean Records</div>
                                        <div class="value text-success">
                                            {{ number_format(max($totalEmployees - $employeesNeedsChecking, 0)) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="payroll-breakdown-row">
                                <span>Total Payable Days</span>
                                <strong>{{ $num($totalPayableDays, 2) }}</strong>
                            </div>

                            <div class="payroll-breakdown-row">
                                <span>Total Payable Hours</span>
                                <strong>{{ $num($totalPayableHours, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-body-tertiary border-bottom">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-chart-simple me-2 text-info"></i>
                                Gross to Net Ratio
                            </h6>
                        </div>

                        <div class="card-body">
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small payroll-muted">Net Pay Ratio</span>
                                    <strong class="small text-success">{{ $num($netRate, 1) }}%</strong>
                                </div>

                                <div class="payroll-progress">
                                    <div class="payroll-progress-bar bg-success"
                                        style="width: {{ min($netRate, 100) }}%;"></div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small payroll-muted">Deduction Ratio</span>
                                    <strong class="small text-danger">{{ $num($deductionRate, 1) }}%</strong>
                                </div>

                                <div class="payroll-progress">
                                    <div class="payroll-progress-bar bg-danger"
                                        style="width: {{ min($deductionRate, 100) }}%;"></div>
                                </div>
                            </div>

                            <div class="alert alert-subtle-info mb-0 py-2">
                                <small>
                                    High deduction rows are tagged when total deductions exceed 60% of gross pay.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-body-tertiary border-bottom">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-money-check-dollar me-2 text-success"></i>
                                Amount Breakdown
                            </h6>
                        </div>

                        <div class="card-body">
                            <div class="payroll-breakdown-row">
                                <span>Holiday Pay</span>
                                <strong class="text-info">{{ $money($totalHolidayPay) }}</strong>
                            </div>

                            <div class="payroll-breakdown-row">
                                <span>Rest Day Pay</span>
                                <strong class="text-info">{{ $money($totalRestDayPay) }}</strong>
                            </div>

                            <div class="payroll-breakdown-row">
                                <span>Overtime Pay</span>
                                <strong class="text-info">{{ $money($totalOvertimePay) }}</strong>
                            </div>

                            <div class="payroll-breakdown-row">
                                <span>Government Deductions</span>
                                <strong class="text-danger">{{ $money($totalGovernmentDeductions) }}</strong>
                            </div>

                            <div class="payroll-breakdown-row">
                                <span>Loans / Other Deductions</span>
                                <strong class="text-danger">{{ $money($totalOtherDeductions) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body-tertiary border-bottom py-3">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2">
                        <div>
                            <h5 class="mb-1 fw-bold text-900">
                                Employee Payroll Breakdown
                            </h5>
                            <small class="payroll-muted">
                                Falcon-compatible compact table. Soft badges identify rows that need checking.
                            </small>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge badge-subtle-success text-success">OK</span>
                            <span class="badge badge-subtle-warning text-warning">Review</span>
                            <span class="badge badge-subtle-danger text-danger">Problem</span>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive scrollbar">
                        <table class="table table-hover align-middle mb-0 payroll-table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th class="text-end">Payable</th>
                                    <th class="text-end">Regular</th>
                                    <th class="text-end">Additions</th>
                                    <th class="text-end">Government</th>
                                    <th class="text-end">Other Deduct.</th>
                                    <th class="text-end">Gross</th>
                                    <th class="text-end">Net</th>
                                    <th class="text-end">Audit</th>
                                    <th class="text-end payroll-action-col">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($items as $item)
                                    @php
                                        $itemHolidayPay = (float) ($item->holiday_pay ?? 0);
                                        $itemRestDayPay = (float) ($item->rest_day_pay ?? 0);
                                        $itemOvertimePay = (float) ($item->overtime_pay ?? 0);
                                        $itemLeavePay = (float) ($item->leave_pay ?? 0);
                                        $itemOtherAdditions = (float) ($item->other_additions ?? 0);

                                        $itemAdditions =
                                            $itemHolidayPay +
                                            $itemRestDayPay +
                                            $itemOvertimePay +
                                            $itemLeavePay +
                                            $itemOtherAdditions;

                                        $itemGovernment = (float) ($item->total_employee_government_deductions ?? 0);
                                        $itemOtherDeductions = (float) ($item->other_deductions ?? 0);
                                        $itemTotalDeductions = $itemGovernment + $itemOtherDeductions;

                                        $itemRegularPay = (float) ($item->regular_pay ?? 0);
                                        $itemGrossPay = (float) ($item->gross_pay ?? 0);
                                        $itemNetPay = (float) ($item->net_pay ?? 0);
                                        $itemPayableDays = (float) ($item->total_payable_days ?? 0);
                                        $itemPayableHours = (float) ($item->total_payable_hours ?? 0);

                                        $auditBadges = [];

                                        if ($itemRegularPay <= 0) {
                                            $auditBadges[] = ['label' => 'No Regular', 'tone' => 'danger'];
                                        }

                                        if ($itemGrossPay <= 0) {
                                            $auditBadges[] = ['label' => 'No Gross', 'tone' => 'danger'];
                                        }

                                        if ($itemNetPay <= 0) {
                                            $auditBadges[] = ['label' => 'No Net', 'tone' => 'danger'];
                                        }

                                        if ($itemPayableDays <= 0 && $itemPayableHours <= 0) {
                                            $auditBadges[] = ['label' => 'No Payable', 'tone' => 'warning'];
                                        }

                                        if ($itemGrossPay > 0 && $itemTotalDeductions > $itemGrossPay * 0.6) {
                                            $auditBadges[] = ['label' => 'High Deduct.', 'tone' => 'warning'];
                                        }

                                        if ($itemAdditions > 0) {
                                            $auditBadges[] = ['label' => 'Additions', 'tone' => 'info'];
                                        }

                                        $hasDanger = collect($auditBadges)->contains(
                                            fn($badge) => $badge['tone'] === 'danger',
                                        );
                                        $hasWarning = collect($auditBadges)->contains(
                                            fn($badge) => $badge['tone'] === 'warning',
                                        );

                                        if (empty($auditBadges)) {
                                            $auditBadges[] = ['label' => 'OK', 'tone' => 'success'];
                                        }

                                        $rowClass = match (true) {
                                            $hasDanger => 'row-danger',
                                            $hasWarning => 'row-review',
                                            default => '',
                                        };
                                    @endphp

                                    <tr class="{{ $rowClass }}">
                                        <td>
                                            <div class="payroll-employee-name">
                                                <div class="name">
                                                    {{ $item->employee_name }}
                                                </div>

                                                <div class="meta">
                                                    {{ $item->employee_no ?: 'No Employee No' }}

                                                    @if ($item->biometric_employee_id)
                                                        <span class="mx-1">|</span>
                                                        Bio: {{ $item->biometric_employee_id }}
                                                    @endif
                                                </div>

                                                @if (!empty($item->rate_type))
                                                    <div class="meta">
                                                        Rate: {{ str_replace('_', ' ', ucfirst($item->rate_type)) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </td>

                                        <td class="text-end">
                                            <span class="payroll-amount">
                                                {{ $num($itemPayableDays, 2) }} day
                                            </span>
                                            <span class="payroll-sub">
                                                {{ $num($itemPayableHours, 2) }} hr
                                            </span>
                                        </td>

                                        <td class="text-end">
                                            <span class="payroll-amount">
                                                {{ $money($itemRegularPay) }}
                                            </span>
                                        </td>

                                        <td class="text-end">
                                            <span class="payroll-amount text-info">
                                                {{ $money($itemAdditions) }}
                                            </span>
                                            <span class="payroll-sub">
                                                H {{ $num($itemHolidayPay, 2) }}
                                                /
                                                R {{ $num($itemRestDayPay, 2) }}
                                                /
                                                OT {{ $num($itemOvertimePay, 2) }}
                                            </span>
                                        </td>

                                        <td class="text-end">
                                            <span class="payroll-amount text-danger">
                                                {{ $money($itemGovernment) }}
                                            </span>
                                            <span class="payroll-sub">
                                                SSS {{ $num($item->sss_employee ?? 0, 2) }}
                                                /
                                                PH {{ $num($item->philhealth_employee ?? 0, 2) }}
                                                /
                                                PI {{ $num($item->pagibig_employee ?? 0, 2) }}
                                            </span>
                                        </td>

                                        <td class="text-end">
                                            <span class="payroll-amount text-danger">
                                                {{ $money($itemOtherDeductions) }}
                                            </span>
                                        </td>

                                        <td class="text-end">
                                            <span class="payroll-amount">
                                                {{ $money($itemGrossPay) }}
                                            </span>
                                        </td>

                                        <td class="text-end">
                                            <span class="payroll-amount text-success">
                                                {{ $money($itemNetPay) }}
                                            </span>

                                            @if ($itemGrossPay > 0)
                                                <span class="payroll-sub">
                                                    {{ $num(($itemNetPay / $itemGrossPay) * 100, 1) }}% of gross
                                                </span>
                                            @endif
                                        </td>

                                        <td class="text-end">
                                            <div class="payroll-audit-badges">
                                                @foreach ($auditBadges as $badge)
                                                    <span
                                                        class="badge badge-subtle-{{ $badge['tone'] }} text-{{ $badge['tone'] }}">
                                                        {{ $badge['label'] }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </td>

                                        <td class="text-end">
                                            <a href="{{ route('payroll.items.show', [$payroll, $item]) }}"
                                                class="btn btn-falcon-primary btn-sm">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10">
                                            <div class="payroll-empty-state">
                                                <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                                No payroll items found.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            @if ($items->isNotEmpty())
                                <tfoot class="bg-body-tertiary fw-bold">
                                    <tr>
                                        <td>Total</td>

                                        <td class="text-end">
                                            {{ $num($totalPayableDays, 2) }} day
                                            <span class="payroll-sub">
                                                {{ $num($totalPayableHours, 2) }} hr
                                            </span>
                                        </td>

                                        <td class="text-end">
                                            {{ $money($totalRegularPay) }}
                                        </td>

                                        <td class="text-end text-info">
                                            {{ $money($totalAdditions) }}
                                        </td>

                                        <td class="text-end text-danger">
                                            {{ $money($totalGovernmentDeductions) }}
                                        </td>

                                        <td class="text-end text-danger">
                                            {{ $money($totalOtherDeductions) }}
                                        </td>

                                        <td class="text-end">
                                            {{ $money($totalGrossPay) }}
                                        </td>

                                        <td class="text-end text-success fs-6">
                                            {{ $money($totalNetPay) }}
                                        </td>

                                        <td class="text-end">
                                            @if ($employeesNeedsChecking > 0)
                                                <span class="badge badge-subtle-danger text-danger">
                                                    {{ number_format($employeesNeedsChecking) }} review
                                                </span>
                                            @else
                                                <span class="badge badge-subtle-success text-success">
                                                    Clean
                                                </span>
                                            @endif
                                        </td>

                                        <td></td>
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
