@extends('layouts.app')

@section('title', 'Employee Salary Master')

@push('styles')
    <style>
        .salary-master-page {
            --salary-border: var(--falcon-border-color, #d8e2ef);
            --salary-muted: var(--falcon-gray-600, #748194);
            --salary-heading: var(--falcon-gray-900, #344050);
        }

        /*
             |--------------------------------------------------------------------------
             | Page Header
             |--------------------------------------------------------------------------
             */

        .salary-master-header {
            position: relative;
            overflow: hidden;
            background:
                linear-gradient(135deg,
                    rgba(44, 123, 229, 0.14) 0%,
                    rgba(44, 123, 229, 0.04) 55%,
                    rgba(39, 188, 253, 0.08) 100%);
        }

        .salary-master-header::before {
            position: absolute;
            top: -115px;
            right: -55px;
            width: 270px;
            height: 270px;
            content: "";
            border-radius: 50%;
            background: rgba(44, 123, 229, 0.08);
            pointer-events: none;
        }

        .salary-master-header::after {
            position: absolute;
            right: 180px;
            bottom: -105px;
            width: 190px;
            height: 190px;
            content: "";
            border-radius: 50%;
            background: rgba(39, 188, 253, 0.06);
            pointer-events: none;
        }

        .salary-master-header .card-body {
            position: relative;
            z-index: 1;
        }

        .salary-header-icon {
            display: inline-flex;
            width: 56px;
            height: 56px;
            flex: 0 0 56px;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
            color: var(--falcon-primary, #2c7be5);
            background: rgba(44, 123, 229, 0.14);
            font-size: 1.35rem;
        }

        /*
             |--------------------------------------------------------------------------
             | Search and Information
             |--------------------------------------------------------------------------
             */

        .salary-filter-label {
            margin-bottom: 0.4rem;
            color: var(--falcon-gray-700, #5e6e82);
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.035em;
            text-transform: uppercase;
        }

        .salary-filter-control {
            min-height: 41px;
        }

        .cutoff-information-card {
            border: 1px solid rgba(44, 123, 229, 0.18);
            border-radius: 0.625rem;
            background: rgba(44, 123, 229, 0.055);
        }

        .cutoff-information-icon {
            display: inline-flex;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            color: var(--falcon-primary, #2c7be5);
            background: rgba(44, 123, 229, 0.12);
        }

        /*
             |--------------------------------------------------------------------------
             | Table
             |--------------------------------------------------------------------------
             */

        .salary-table-card {
            overflow: hidden;
        }

        .salary-table {
            min-width: 1200px;
            font-size: 0.79rem;
        }

        .salary-table thead th {
            padding-top: 0.9rem;
            padding-bottom: 0.9rem;
            border-bottom-width: 1px;
            color: var(--falcon-gray-700, #5e6e82);
            background: var(--falcon-gray-100, #f9fafd);
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.035em;
            text-transform: uppercase;
            vertical-align: middle;
            white-space: nowrap;
        }

        .salary-table tbody td {
            padding-top: 1rem;
            padding-bottom: 1rem;
            border-color: var(--salary-border);
            vertical-align: top;
        }

        .salary-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .salary-table tbody tr:hover {
            background: rgba(44, 123, 229, 0.025);
        }

        /*
             |--------------------------------------------------------------------------
             | Employee
             |--------------------------------------------------------------------------
             */

        .salary-employee-cell {
            min-width: 245px;
        }

        .salary-employee-avatar {
            display: inline-flex;
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            color: var(--falcon-primary, #2c7be5);
            background: rgba(44, 123, 229, 0.12);
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.03em;
        }

        .salary-employee-name {
            max-width: 180px;
            overflow: hidden;
            color: var(--salary-heading);
            font-size: 0.82rem;
            font-weight: 700;
            line-height: 1.3;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .salary-employee-meta {
            margin-top: 0.2rem;
            color: var(--salary-muted);
            font-size: 0.69rem;
            line-height: 1.5;
        }

        /*
             |--------------------------------------------------------------------------
             | Salary Information
             |--------------------------------------------------------------------------
             */

        .salary-rate-cell {
            min-width: 190px;
        }

        .salary-government-cell {
            min-width: 250px;
        }

        .salary-allowance-cell {
            min-width: 230px;
        }

        .salary-status-cell {
            min-width: 145px;
        }

        .salary-action-cell {
            min-width: 110px;
        }

        .salary-data-panel {
            padding: 0.7rem 0.8rem;
            border: 1px solid var(--salary-border);
            border-radius: 0.5rem;
            background: var(--falcon-gray-100, #f9fafd);
        }

        .salary-data-row {
            display: grid;
            grid-template-columns: minmax(75px, 1fr) auto;
            align-items: center;
            gap: 0.75rem;
            min-height: 1.65rem;
        }

        .salary-data-row+.salary-data-row {
            border-top: 1px dashed var(--salary-border);
        }

        .salary-data-label {
            color: var(--salary-muted);
            font-size: 0.7rem;
            white-space: nowrap;
        }

        .salary-data-value {
            color: var(--salary-heading);
            font-size: 0.75rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            text-align: right;
            white-space: nowrap;
        }

        .salary-breakdown-row {
            display: grid;
            grid-template-columns: minmax(72px, 1fr) 58px minmax(82px, auto);
            align-items: center;
            gap: 0.5rem;
            min-height: 2rem;
        }

        .salary-breakdown-row+.salary-breakdown-row {
            border-top: 1px dashed var(--salary-border);
        }

        .salary-breakdown-label {
            color: var(--falcon-gray-700, #5e6e82);
            font-size: 0.71rem;
            white-space: nowrap;
        }

        .salary-breakdown-value {
            color: var(--salary-heading);
            font-size: 0.74rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            text-align: right;
            white-space: nowrap;
        }

        /*
             |--------------------------------------------------------------------------
             | Badges and Actions
             |--------------------------------------------------------------------------
             */

        .salary-rate-badge {
            display: inline-flex;
            min-height: 26px;
            align-items: center;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
            font-size: 0.66rem;
            font-weight: 700;
        }

        .salary-schedule-badge {
            display: inline-flex;
            min-width: 48px;
            min-height: 23px;
            align-items: center;
            justify-content: center;
            padding: 0.25rem 0.4rem;
            border-radius: 999px;
            font-size: 0.61rem;
            font-weight: 700;
        }

        .salary-status-badge {
            display: inline-flex;
            min-height: 27px;
            align-items: center;
            padding: 0.35rem 0.6rem;
            border-radius: 999px;
            font-size: 0.65rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .salary-action-button {
            display: inline-flex;
            width: 34px;
            height: 34px;
            align-items: center;
            justify-content: center;
            padding: 0;
            border-radius: 0.375rem;
        }

        /*
             |--------------------------------------------------------------------------
             | Empty State
             |--------------------------------------------------------------------------
             */

        .salary-empty-state {
            padding: 4rem 1rem;
        }

        .salary-empty-icon {
            display: inline-flex;
            width: 72px;
            height: 72px;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: var(--falcon-gray-500, #9da9bb);
            background: var(--falcon-gray-100, #f9fafd);
            font-size: 1.65rem;
        }

        @media (max-width: 991.98px) {
            .salary-master-header::before {
                right: -140px;
            }

            .salary-header-icon {
                width: 49px;
                height: 49px;
                flex-basis: 49px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid salary-master-page" data-layout="container">
        <div class="content">
            {{-- Page header --}}
            <div class="card salary-master-header border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-4">
                        <div class="d-flex align-items-start">
                            <div class="salary-header-icon me-3">
                                <span class="fas fa-money-check-alt"></span>
                            </div>

                            <div>
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <h3 class="mb-0 text-900">
                                        Employee Salary Master
                                    </h3>

                                    <span class="badge rounded-pill bg-primary-subtle text-primary">
                                        Payroll Configuration
                                    </span>
                                </div>

                                <p class="mb-0 text-600" style="max-width: 850px;">
                                    Configure employee salary rates, allowances, government
                                    contribution schedules, overtime rates, attendance deductions,
                                    loans, cash advances, and payroll eligibility.
                                </p>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-sm-row gap-2">
                            <a href="{{ route('payroll-employee-salaries.sync') }}" class="btn btn-falcon-warning">
                                <span class="fas fa-sync-alt me-2"></span>
                                Sync from Biometrics
                            </a>

                            <a href="{{ route('payroll-employee-salaries.create') }}" class="btn btn-primary shadow-sm">
                                <span class="fas fa-plus me-2"></span>
                                Add Employee Salary
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Search --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-body-tertiary border-bottom py-3">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                        <div class="d-flex align-items-center">
                            <span class="fas fa-search text-primary me-3"></span>

                            <div>
                                <h6 class="mb-1 text-900">
                                    Search Salary Records
                                </h6>

                                <p class="mb-0 fs-10 text-600">
                                    Find employees by name, employee number, biometric ID, or CrossChex ID.
                                </p>
                            </div>
                        </div>

                        <span class="badge rounded-pill bg-primary-subtle text-primary px-3 py-2">
                            {{ number_format($salaries->total()) }}
                            {{ \Illuminate\Support\Str::plural('record', $salaries->total()) }}
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('payroll-employee-salaries.index') }}"
                        class="row g-3 align-items-end">
                        <div class="col-12 col-lg-8 col-xl-7">
                            <label for="salary-search" class="salary-filter-label">
                                Employee Search
                            </label>

                            <div class="input-group">
                                <span class="input-group-text bg-body-tertiary">
                                    <span class="fas fa-search text-500"></span>
                                </span>

                                <input id="salary-search" type="text" name="search"
                                    class="form-control salary-filter-control"
                                    placeholder="Employee name, employee no., biometric ID, or CrossChex ID"
                                    value="{{ $search }}">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-auto">
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary salary-filter-control">
                                    <span class="fas fa-search me-2"></span>
                                    Search
                                </button>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-auto">
                            <div class="d-grid">
                                <a href="{{ route('payroll-employee-salaries.index') }}"
                                    class="btn btn-falcon-default salary-filter-control d-flex align-items-center justify-content-center">
                                    <span class="fas fa-redo-alt me-2"></span>
                                    Reset
                                </a>
                            </div>
                        </div>

                        @if ($search)
                            <div class="col-12">
                                <div
                                    class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3 border-top">
                                    <div class="fs-10 text-600">
                                        <span class="fas fa-info-circle text-primary me-1"></span>
                                        Showing salary records matching
                                        <span class="fw-semibold text-800">
                                            “{{ $search }}”
                                        </span>
                                    </div>

                                    <a href="{{ route('payroll-employee-salaries.index') }}"
                                        class="btn btn-link btn-sm text-danger text-decoration-none p-0">
                                        <span class="fas fa-times me-1"></span>
                                        Clear search
                                    </a>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Cutoff rule information --}}
            <div class="cutoff-information-card p-3 mb-3">
                <div class="d-flex align-items-start">
                    <div class="cutoff-information-icon me-3">
                        <span class="fas fa-calendar-check"></span>
                    </div>

                    <div>
                        <h6 class="mb-1 text-900">
                            Deduction and Release Schedule
                        </h6>

                        <p class="mb-2 fs-10 text-600">
                            Government contributions, allowances, and loan deductions follow
                            the schedule configured for each employee.
                        </p>

                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="salary-schedule-badge bg-primary-subtle text-primary">
                                1st
                            </span>

                            <span class="fs-10 text-700 me-2">
                                Apply on first cutoff only
                            </span>

                            <span class="salary-schedule-badge bg-info-subtle text-info">
                                2nd
                            </span>

                            <span class="fs-10 text-700 me-2">
                                Apply on second cutoff only
                            </span>

                            <span class="salary-schedule-badge bg-success-subtle text-success">
                                Every
                            </span>

                            <span class="fs-10 text-700">
                                Apply or split every cutoff
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Salary records --}}
            <div class="card salary-table-card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                        <div>
                            <h5 class="mb-1 text-900">
                                Employee Salary Records
                            </h5>

                            <p class="mb-0 fs-10 text-600">
                                Employee salary rates and payroll deduction configuration.
                            </p>
                        </div>

                        <a href="{{ route('payroll-employee-salaries.create') }}" class="btn btn-falcon-primary btn-sm">
                            <span class="fas fa-user-plus me-1"></span>
                            New Salary Record
                        </a>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive scrollbar">
                        <table class="table salary-table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Employee</th>
                                    <th>Salary Rate</th>
                                    <th>Government Deduction / Month</th>
                                    <th>Allowances</th>
                                    <th>Payroll Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($salaries as $salary)
                                    @php
                                        $preview = $salary->payroll_preview ?? [];

                                        $monthlyGovernment = $preview['monthly_government'] ?? [];

                                        $scheduleConfig = function (?string $schedule): array {
                                            return match ($schedule) {
                                                'first_cutoff' => [
                                                    'label' => '1st',
                                                    'class' => 'bg-primary-subtle text-primary',
                                                    'title' => 'First cutoff only',
                                                ],
                                                'second_cutoff' => [
                                                    'label' => '2nd',
                                                    'class' => 'bg-info-subtle text-info',
                                                    'title' => 'Second cutoff only',
                                                ],
                                                'every_cutoff' => [
                                                    'label' => 'Every',
                                                    'class' => 'bg-success-subtle text-success',
                                                    'title' => 'Every cutoff',
                                                ],
                                                default => [
                                                    'label' => 'None',
                                                    'class' => 'bg-secondary-subtle text-secondary',
                                                    'title' => 'No schedule',
                                                ],
                                            };
                                        };

                                        $money = fn($amount): string => number_format((float) $amount, 2);

                                        $decimal4 = fn($amount): string => number_format((float) $amount, 4);

                                        $employeeName = trim((string) $salary->employee_name);

                                        $employeeInitials = collect(preg_split('/\s+/', $employeeName) ?: [])
                                            ->filter()
                                            ->take(2)
                                            ->map(fn(string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
                                            ->implode('');

                                        $employeeInitials = $employeeInitials ?: 'NA';

                                        $isBiometricIncluded =
                                            ($salary->employeeBiometric?->employment_status ?? 'active') === 'active' &&
                                            ($salary->employeeBiometric?->is_payroll_active ?? true);

                                        $sssSchedule = $scheduleConfig($salary->sss_contribution_cutoff);

                                        $pagibigSchedule = $scheduleConfig($salary->pagibig_contribution_cutoff);

                                        $philhealthSchedule = $scheduleConfig($salary->philhealth_contribution_cutoff);

                                        $allowanceSchedule = $scheduleConfig($salary->allowance_release_schedule);

                                        $simLoadSchedule = $scheduleConfig($salary->sim_load_release_schedule);
                                    @endphp

                                    <tr>
                                        {{-- Employee --}}
                                        <td class="salary-employee-cell ps-4">
                                            <div class="d-flex align-items-start">
                                                <div class="salary-employee-avatar me-3">
                                                    {{ $employeeInitials }}
                                                </div>

                                                <div>
                                                    <div class="salary-employee-name"
                                                        title="{{ $salary->employee_name }}">
                                                        {{ $salary->employee_name ?: 'Unnamed Employee' }}
                                                    </div>

                                                    <div class="salary-employee-meta">
                                                        Employee No:
                                                        <span class="text-700 fw-semibold">
                                                            {{ $salary->employee_no ?: 'N/A' }}
                                                        </span>
                                                    </div>

                                                    <div class="salary-employee-meta">
                                                        Biometric ID:
                                                        <span class="text-700">
                                                            {{ $salary->employee_biometric_id ?: 'N/A' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Salary rate --}}
                                        <td class="salary-rate-cell">
                                            <div class="salary-data-panel">
                                                <div class="salary-data-row">
                                                    <span class="salary-data-label">
                                                        Basic Salary
                                                    </span>

                                                    <span class="salary-data-value">
                                                        PHP {{ $money($salary->basic_salary) }}
                                                    </span>
                                                </div>

                                                <div class="salary-data-row">
                                                    <span class="salary-data-label">
                                                        OT / Hour
                                                    </span>

                                                    <span class="salary-data-value">
                                                        PHP {{ $money($salary->ot_rate_per_hour) }}
                                                    </span>
                                                </div>

                                                <div class="salary-data-row">
                                                    <span class="salary-data-label">
                                                        Late / Minute
                                                    </span>

                                                    <span class="salary-data-value">
                                                        PHP {{ $decimal4($salary->late_deduction_per_minute) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Government deductions --}}
                                        <td class="salary-government-cell">
                                            <div class="salary-data-panel">
                                                <div class="salary-breakdown-row">
                                                    <span class="salary-breakdown-label">
                                                        SSS
                                                    </span>

                                                    <span class="salary-schedule-badge {{ $sssSchedule['class'] }}"
                                                        title="{{ $sssSchedule['title'] }}">
                                                        {{ $sssSchedule['label'] }}
                                                    </span>

                                                    <span class="salary-breakdown-value">
                                                        PHP {{ $money($monthlyGovernment['sss'] ?? 0) }}
                                                    </span>
                                                </div>

                                                <div class="salary-breakdown-row">
                                                    <span class="salary-breakdown-label">
                                                        Pag-IBIG
                                                    </span>

                                                    <span class="salary-schedule-badge {{ $pagibigSchedule['class'] }}"
                                                        title="{{ $pagibigSchedule['title'] }}">
                                                        {{ $pagibigSchedule['label'] }}
                                                    </span>

                                                    <span class="salary-breakdown-value">
                                                        PHP {{ $money($monthlyGovernment['pagibig'] ?? 0) }}
                                                    </span>
                                                </div>

                                                <div class="salary-breakdown-row">
                                                    <span class="salary-breakdown-label">
                                                        PhilHealth
                                                    </span>

                                                    <span class="salary-schedule-badge {{ $philhealthSchedule['class'] }}"
                                                        title="{{ $philhealthSchedule['title'] }}">
                                                        {{ $philhealthSchedule['label'] }}
                                                    </span>

                                                    <span class="salary-breakdown-value">
                                                        PHP {{ $money($monthlyGovernment['philhealth'] ?? 0) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Allowances --}}
                                        <td class="salary-allowance-cell">
                                            <div class="salary-data-panel">
                                                <div class="salary-breakdown-row">
                                                    <span class="salary-breakdown-label">
                                                        Regular
                                                    </span>

                                                    <span class="salary-schedule-badge {{ $allowanceSchedule['class'] }}"
                                                        title="{{ $allowanceSchedule['title'] }}">
                                                        {{ $allowanceSchedule['label'] }}
                                                    </span>

                                                    <span class="salary-breakdown-value">
                                                        PHP {{ $money($salary->allowance) }}
                                                    </span>
                                                </div>

                                                <div class="salary-breakdown-row">
                                                    <span class="salary-breakdown-label">
                                                        SIM Load
                                                    </span>

                                                    <span class="salary-schedule-badge {{ $simLoadSchedule['class'] }}"
                                                        title="{{ $simLoadSchedule['title'] }}">
                                                        {{ $simLoadSchedule['label'] }}
                                                    </span>

                                                    <span class="salary-breakdown-value">
                                                        PHP {{ $money($salary->sim_load_allowance) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Status --}}
                                        <td class="salary-status-cell">
                                            <div class="d-flex flex-column align-items-start gap-2">
                                                @if ($salary->is_active)
                                                    <span class="salary-status-badge bg-success-subtle text-success">
                                                        <span class="fas fa-check-circle me-1"></span>
                                                        Salary Active
                                                    </span>
                                                @else
                                                    <span class="salary-status-badge bg-secondary-subtle text-secondary">
                                                        <span class="fas fa-pause-circle me-1"></span>
                                                        Salary Inactive
                                                    </span>
                                                @endif

                                                @if ($isBiometricIncluded)
                                                    <span class="salary-status-badge bg-primary-subtle text-primary">
                                                        <span class="fas fa-fingerprint me-1"></span>
                                                        Bio Included
                                                    </span>
                                                @else
                                                    <span class="salary-status-badge bg-danger-subtle text-danger">
                                                        <span class="fas fa-user-times me-1"></span>
                                                        Bio Excluded
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Actions --}}
                                        <td class="salary-action-cell text-end pe-4">
                                            <div class="d-inline-flex align-items-center gap-1">
                                                <a href="{{ route('payroll-employee-salaries.edit', $salary) }}"
                                                    class="btn btn-falcon-warning btn-sm salary-action-button"
                                                    title="Edit salary record" aria-label="Edit salary record">
                                                    <span class="fas fa-edit"></span>
                                                </a>

                                                <form action="{{ route('payroll-employee-salaries.destroy', $salary) }}"
                                                    method="POST" class="d-inline"
                                                    onsubmit="return confirm('Delete this employee salary record? This action cannot be undone.');">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit"
                                                        class="btn btn-falcon-danger btn-sm salary-action-button"
                                                        title="Delete salary record" aria-label="Delete salary record">
                                                        <span class="fas fa-trash-alt"></span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="salary-empty-state text-center">
                                                <div class="salary-empty-icon mb-3">
                                                    <span class="fas fa-folder-open"></span>
                                                </div>

                                                <h5 class="mb-2 text-900">
                                                    No salary records found
                                                </h5>

                                                <p class="mb-3 text-600">
                                                    No employee salary records match the current search criteria.
                                                </p>

                                                @if ($search)
                                                    <a href="{{ route('payroll-employee-salaries.index') }}"
                                                        class="btn btn-falcon-default btn-sm">
                                                        <span class="fas fa-times me-1"></span>
                                                        Clear Search
                                                    </a>
                                                @else
                                                    <a href="{{ route('payroll-employee-salaries.create') }}"
                                                        class="btn btn-primary btn-sm">
                                                        <span class="fas fa-plus me-1"></span>
                                                        Add First Salary Record
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($salaries->isNotEmpty())
                    <div class="card-footer bg-body-tertiary border-top">
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                            <div class="fs-10 text-600">
                                Showing
                                <span class="fw-semibold text-800">
                                    {{ number_format($salaries->firstItem() ?? 0) }}
                                </span>
                                to
                                <span class="fw-semibold text-800">
                                    {{ number_format($salaries->lastItem() ?? 0) }}
                                </span>
                                of
                                <span class="fw-semibold text-800">
                                    {{ number_format($salaries->total()) }}
                                </span>
                                salary records
                            </div>

                            @if ($salaries->hasPages())
                                <div>
                                    {{ $salaries->links('pagination.custom') }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
