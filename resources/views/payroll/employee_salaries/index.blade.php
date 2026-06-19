@extends('layouts.app')
@section('title', 'Employee Salary Master')

@section('content')
    <div class="container-fluid" data-layout="container">
        <div class="content">
            @if (session('success'))
                <div class="alert alert-success border-0 shadow-sm">
                    <span class="fas fa-check-circle me-1"></span>
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm">
                    <div class="fw-semibold mb-1">
                        <span class="fas fa-exclamation-circle me-1"></span>
                        Please check the following:
                    </div>
                    <ul class="mb-0 ps-3 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card shadow-sm border-0 salary-master-card">
                <div class="card-header bg-body-tertiary">
                    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                        <div>
                            <h5 class="mb-1">
                                <span class="fas fa-money-check-alt text-primary me-2"></span>
                                Employee Salary Master
                            </h5>
                            <p class="mb-0 text-muted small">
                                Manage salary rates, automatic SSS / Pag-IBIG / PhilHealth deductions, allowances,
                                loans, cash advances, and cutoff release rules.
                            </p>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('payroll-employee-salaries.sync') }}" class="btn btn-warning btn-sm">
                                <span class="fas fa-sync-alt me-1"></span> Sync from Biometrics
                            </a>

                            <a href="{{ route('payroll-employee-salaries.create') }}" class="btn btn-primary btn-sm">
                                <span class="fas fa-plus me-1"></span> Add Salary
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('payroll-employee-salaries.index') }}" class="row g-2 mb-3">
                        <div class="col-lg-7 col-xl-6">
                            <input type="text" name="search" class="form-control"
                                placeholder="Search employee name / employee no / biometric id / CrossChex id"
                                value="{{ $search }}">
                        </div>

                        <div class="col-auto">
                            <button class="btn btn-primary">
                                <span class="fas fa-search me-1"></span> Search
                            </button>
                        </div>

                        @if ($search)
                            <div class="col-auto">
                                <a href="{{ route('payroll-employee-salaries.index') }}" class="btn btn-light">
                                    Clear
                                </a>
                            </div>
                        @endif
                    </form>

                    <div class="alert alert-subtle-info border mb-3">
                        <div class="d-flex gap-2">
                            <span class="fas fa-info-circle mt-1"></span>
                            <div class="small">
                                <strong>Cutoff rule:</strong>
                                <span class="badge bg-primary mx-1">1st</span> full deduction on 1st cutoff only,
                                <span class="badge bg-info mx-1">2nd</span> full deduction on 2nd cutoff only,
                                <span class="badge bg-success mx-1">Every</span> split monthly deduction or fixed loan
                                deduction every cutoff.
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive salary-table-wrap">
                        <table class="table table-sm table-hover align-middle mb-0 salary-master-table">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="min-width: 100px;">Employee</th>
                                    <th style="min-width: 170px;">Salary Rate</th>
                                    <th style="min-width: 260px;">Gov. Deduction / Month</th>
                                    <th style="min-width: 230px;">Allowance</th>
                                    <th style="min-width: 390px;">Loans / CA</th>
                                    <th style="min-width: 230px;">Preview Take Home</th>
                                    <th style="min-width: 100px;">Status</th>
                                    <th class="text-end pe-3" style="min-width: 110px;">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($salaries as $salary)
                                    @php
                                        $preview = $salary->payroll_preview;
                                        $first = $preview['first'];
                                        $second = $preview['second'];

                                        $scheduleBadge = function (?string $schedule) {
                                            return match ($schedule) {
                                                'first_cutoff'
                                                    => '<span class="badge bg-primary salary-schedule-badge">1st</span>',
                                                'second_cutoff'
                                                    => '<span class="badge bg-info salary-schedule-badge">2nd</span>',
                                                'every_cutoff'
                                                    => '<span class="badge bg-success salary-schedule-badge">Every</span>',
                                                default
                                                    => '<span class="badge bg-secondary salary-schedule-badge">None</span>',
                                            };
                                        };

                                        $money = fn($amount) => number_format((float) $amount, 2);
                                        $decimal4 = fn($amount) => number_format((float) $amount, 4);
                                    @endphp

                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-semibold text-dark salary-employee-name">
                                                {{ $salary->employee_name }}
                                            </div>
                                            <div class="text-muted small">
                                                Emp No: {{ $salary->employee_no ?: '—' }}
                                            </div>
                                        </td>

                                        <td>
                                            <div class="salary-info-list compact">
                                                <div class="mb-1">
                                                    <span
                                                        class="badge {{ $salary->rate_type === 'monthly' ? 'bg-primary' : 'bg-info' }} salary-rate-badge">
                                                        {{ ucfirst($salary->rate_type) }}
                                                    </span>
                                                </div>

                                                <div class="salary-info-row two-col">
                                                    <span class="salary-label">Basic</span>
                                                    <strong class="salary-value">
                                                        {{ $money($salary->basic_salary) }}
                                                    </strong>
                                                </div>

                                                <div class="salary-info-row two-col text-muted">
                                                    <span class="salary-label">OT/Hr</span>
                                                    <span class="salary-value">
                                                        {{ $money($salary->ot_rate_per_hour) }}
                                                    </span>
                                                </div>

                                                <div class="salary-info-row two-col text-muted">
                                                    <span class="salary-label">Late/Min</span>
                                                    <span class="salary-value">
                                                        {{ $decimal4($salary->late_deduction_per_minute) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="salary-info-list">
                                                <div class="salary-info-row three-col">
                                                    <span class="salary-label">SSS</span>
                                                    <span>{!! $scheduleBadge($salary->sss_contribution_cutoff) !!}</span>
                                                    <strong class="salary-value">
                                                        {{ $money($preview['monthly_government']['sss']) }}
                                                    </strong>
                                                </div>

                                                <div class="salary-info-row three-col">
                                                    <span class="salary-label">Pag-IBIG</span>
                                                    <span>{!! $scheduleBadge($salary->pagibig_contribution_cutoff) !!}</span>
                                                    <strong class="salary-value">
                                                        {{ $money($preview['monthly_government']['pagibig']) }}
                                                    </strong>
                                                </div>

                                                <div class="salary-info-row three-col">
                                                    <span class="salary-label">PhilHealth</span>
                                                    <span>{!! $scheduleBadge($salary->philhealth_contribution_cutoff) !!}</span>
                                                    <strong class="salary-value">
                                                        {{ $money($preview['monthly_government']['philhealth']) }}
                                                    </strong>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="salary-info-list">
                                                <div class="salary-info-row three-col">
                                                    <span class="salary-label">Regular</span>
                                                    <span>{!! $scheduleBadge($salary->allowance_release_schedule) !!}</span>
                                                    <strong class="salary-value">
                                                        {{ $money($salary->allowance) }}
                                                    </strong>
                                                </div>

                                                <div class="salary-info-row three-col">
                                                    <span class="salary-label">SIM Load</span>
                                                    <span>{!! $scheduleBadge($salary->sim_load_release_schedule) !!}</span>
                                                    <strong class="salary-value">
                                                        {{ $money($salary->sim_load_allowance) }}
                                                    </strong>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="salary-info-list loans">
                                                <div class="salary-loan-row">
                                                    <span class="salary-label">SSS Loan</span>
                                                    <strong class="salary-value">
                                                        {{ $money($salary->sss_loan_payment_amount) }}
                                                    </strong>
                                                    <span>{!! $scheduleBadge($salary->sss_loan_deduction_schedule) !!}</span>
                                                    <span class="salary-last-payment">
                                                        Last: {{ $preview['last_payment']['sss_loan'] ?: '—' }}
                                                    </span>
                                                </div>

                                                <div class="salary-loan-row">
                                                    <span class="salary-label">Pag-IBIG Loan</span>
                                                    <strong class="salary-value">
                                                        {{ $money($salary->pagibig_loan_payment_amount) }}
                                                    </strong>
                                                    <span>{!! $scheduleBadge($salary->pagibig_loan_deduction_schedule) !!}</span>
                                                    <span class="salary-last-payment">
                                                        Last: {{ $preview['last_payment']['pagibig_loan'] ?: '—' }}
                                                    </span>
                                                </div>

                                                <div class="salary-loan-row">
                                                    <span class="salary-label">PhilHealth Loan</span>
                                                    <strong class="salary-value">
                                                        {{ $money($salary->philhealth_loan_payment_amount) }}
                                                    </strong>
                                                    <span>{!! $scheduleBadge($salary->philhealth_loan_deduction_schedule) !!}</span>
                                                    <span class="salary-last-payment">
                                                        Last: {{ $preview['last_payment']['philhealth_loan'] ?: '—' }}
                                                    </span>
                                                </div>

                                                <div class="salary-loan-row">
                                                    <span class="salary-label">CA / Vale</span>
                                                    <strong class="salary-value">
                                                        {{ $money($salary->cash_advance_payment_amount) }}
                                                    </strong>
                                                    <span>{!! $scheduleBadge($salary->cash_advance_deduction_schedule) !!}</span>
                                                    <span class="salary-last-payment">
                                                        Last: {{ $preview['last_payment']['cash_advance'] ?: '—' }}
                                                    </span>
                                                </div>

                                                <div class="salary-loan-row">
                                                    <span class="salary-label">Other Loan</span>
                                                    <strong class="salary-value">
                                                        {{ $money($salary->other_loan_payment_amount) }}
                                                    </strong>
                                                    <span>{!! $scheduleBadge($salary->other_loan_deduction_schedule) !!}</span>
                                                    <span class="salary-last-payment">
                                                        Last: {{ $preview['last_payment']['other_loan'] ?: '—' }}
                                                    </span>
                                                </div>

                                                @if ($salary->otherDeductions->isNotEmpty())
                                                    <div class="border-top pt-1 mt-1">
                                                        @foreach ($salary->otherDeductions as $deduction)
                                                            <div class="salary-loan-row">
                                                                <span class="salary-label">
                                                                    {{ $deduction->name }}
                                                                </span>
                                                                <strong class="salary-value">
                                                                    {{ $money($deduction->payment_amount) }}
                                                                </strong>
                                                                <span>
                                                                    {!! $scheduleBadge($deduction->deduction_schedule) !!}
                                                                </span>
                                                                <span class="salary-last-payment">
                                                                    Last:
                                                                    {{ $preview['last_payment']['other_deductions'][$deduction->id] ?? '—' }}
                                                                </span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </td>

                                        <td>
                                            <div class="salary-info-list">
                                                <div class="salary-info-row two-col">
                                                    <span class="salary-label">1st Net</span>
                                                    <strong class="salary-value">
                                                        {{ $money($first['net_preview']) }}
                                                    </strong>
                                                </div>

                                                <div class="salary-info-row two-col">
                                                    <span class="salary-label">2nd Net</span>
                                                    <strong class="salary-value">
                                                        {{ $money($second['net_preview']) }}
                                                    </strong>
                                                </div>

                                                <div class="salary-note mt-2">
                                                    Attendance, late, undertime, OT, and absence can still adjust final pay.
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            @if ($salary->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>

                                        <td class="text-end pe-3">
                                            <div class="d-inline-flex gap-1">
                                                <a href="{{ route('payroll-employee-salaries.edit', $salary) }}"
                                                    class="btn btn-sm btn-primary action-icon-btn" title="Edit">
                                                    <span class="fas fa-edit"></span>
                                                </a>

                                                <form action="{{ route('payroll-employee-salaries.destroy', $salary) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Delete this salary record?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger action-icon-btn"
                                                        title="Delete">
                                                        <span class="fas fa-trash-alt"></span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-5">
                                            <span class="fas fa-folder-open fa-2x mb-2 d-block"></span>
                                            No salary records found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $salaries->links('pagination.custom') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .salary-master-card {
            overflow: hidden;
        }

        .salary-table-wrap {
            border: 1px solid var(--falcon-border-color, #e5e7eb);
            border-radius: .5rem;
        }

        .salary-master-table {
            font-size: .8125rem;
        }

        .salary-master-table thead th {
            font-weight: 700;
            color: var(--falcon-800, #344050);
            white-space: nowrap;
            vertical-align: middle;
            padding-top: .75rem;
            padding-bottom: .75rem;
        }

        .salary-master-table tbody td {
            vertical-align: top;
            padding-top: .75rem;
            padding-bottom: .75rem;
        }

        .salary-master-table tbody tr:hover {
            background-color: rgba(44, 123, 229, .035);
        }

        .salary-employee-name {
            line-height: 1.25;
        }

        .salary-info-list {
            display: flex;
            flex-direction: column;
            gap: .32rem;
        }

        .salary-info-list.compact {
            gap: .22rem;
        }

        .salary-info-row {
            display: grid;
            align-items: center;
            column-gap: .5rem;
            min-height: 1.4rem;
        }

        .salary-info-row.two-col {
            grid-template-columns: minmax(75px, 1fr) minmax(72px, auto);
        }

        .salary-info-row.three-col {
            grid-template-columns: minmax(82px, 1fr) 54px minmax(72px, auto);
        }

        .salary-loan-row {
            display: grid;
            grid-template-columns: minmax(118px, 1fr) minmax(70px, auto) 54px minmax(90px, auto);
            align-items: center;
            column-gap: .5rem;
            min-height: 1.45rem;
        }

        .salary-label {
            color: var(--falcon-700, #4d5969);
            white-space: nowrap;
        }

        .salary-value {
            text-align: right;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        .salary-last-payment,
        .salary-note {
            color: var(--falcon-600, #748194);
            font-size: .75rem;
        }

        .salary-schedule-badge,
        .salary-rate-badge {
            font-size: .68rem;
            min-width: 42px;
            text-align: center;
            line-height: 1.1;
        }

        .action-icon-btn {
            width: 30px;
            height: 30px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        @media (max-width: 1400px) {
            .salary-master-table {
                font-size: .78rem;
            }
        }
    </style>
@endsection
