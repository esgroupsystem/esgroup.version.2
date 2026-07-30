@extends('layouts.app')

@section('title', 'Payroll Attendance Adjustments')

@push('styles')
    <style>
        .payroll-adjustments-page {
            --adjustment-border: var(--falcon-border-color, #d8e2ef);
            --adjustment-muted: var(--falcon-gray-600, #748194);
        }

        .payroll-page-header {
            position: relative;
            overflow: hidden;
            background:
                linear-gradient(
                    135deg,
                    rgba(44, 123, 229, 0.12) 0%,
                    rgba(44, 123, 229, 0.04) 52%,
                    rgba(39, 188, 253, 0.08) 100%
                );
        }

        .payroll-page-header::after {
            position: absolute;
            top: -90px;
            right: -50px;
            width: 230px;
            height: 230px;
            content: "";
            border-radius: 50%;
            background: rgba(44, 123, 229, 0.08);
            pointer-events: none;
        }

        .payroll-page-header .card-body {
            position: relative;
            z-index: 1;
        }

        .page-icon-wrapper {
            display: inline-flex;
            width: 54px;
            height: 54px;
            flex: 0 0 54px;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            color: var(--falcon-primary, #2c7be5);
            background: rgba(44, 123, 229, 0.14);
            font-size: 1.35rem;
        }

        .stat-card {
            position: relative;
            overflow: hidden;
            transition:
                transform 0.18s ease,
                box-shadow 0.18s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(18, 38, 63, 0.08) !important;
        }

        .stat-card::after {
            position: absolute;
            right: -24px;
            bottom: -34px;
            width: 105px;
            height: 105px;
            content: "";
            border-radius: 50%;
            background: currentColor;
            opacity: 0.035;
            pointer-events: none;
        }

        .stat-icon {
            display: inline-flex;
            width: 43px;
            height: 43px;
            flex: 0 0 43px;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1rem;
        }

        .filter-label {
            margin-bottom: 0.4rem;
            color: var(--falcon-gray-700, #5e6e82);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.025em;
            text-transform: uppercase;
        }

        .filter-control {
            min-height: 40px;
        }

        .table-card {
            overflow: hidden;
        }

        .payroll-table {
            min-width: 1180px;
        }

        .payroll-table thead th {
            padding-top: 0.85rem;
            padding-bottom: 0.85rem;
            border-bottom-width: 1px;
            color: var(--falcon-gray-700, #5e6e82);
            background: var(--falcon-gray-100, #f9fafd);
            font-size: 0.69rem;
            font-weight: 700;
            letter-spacing: 0.035em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .payroll-table tbody td {
            padding-top: 1rem;
            padding-bottom: 1rem;
            border-color: var(--adjustment-border);
            vertical-align: middle;
        }

        .payroll-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .employee-cell {
            min-width: 275px;
        }

        .employee-avatar {
            display: inline-flex;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            color: var(--falcon-primary, #2c7be5);
            background: rgba(44, 123, 229, 0.12);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.03em;
        }

        .employee-avatar.employee-avatar-danger {
            color: var(--falcon-danger, #e63757);
            background: rgba(230, 55, 87, 0.12);
        }

        .employee-name {
            max-width: 205px;
            overflow: hidden;
            color: var(--falcon-gray-900, #344050);
            font-weight: 600;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .employee-meta {
            color: var(--adjustment-muted);
            font-size: 0.7rem;
            line-height: 1.55;
        }

        .payroll-type-badge {
            display: inline-flex;
            min-height: 28px;
            align-items: center;
            padding: 0.35rem 0.65rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .period-cell {
            min-width: 165px;
        }

        .adjusted-time-cell {
            min-width: 135px;
        }

        .proof-cell {
            min-width: 155px;
        }

        .effect-cell {
            min-width: 175px;
        }

        .encoded-cell {
            min-width: 175px;
        }

        .payroll-effect-badge {
            padding: 0.38rem 0.55rem;
            border-radius: 0.35rem;
            font-size: 0.66rem;
            font-weight: 600;
        }

        .action-cell {
            min-width: 115px;
        }

        .action-button {
            display: inline-flex;
            width: 33px;
            height: 33px;
            align-items: center;
            justify-content: center;
            padding: 0;
            border-radius: 0.375rem;
        }

        .empty-state {
            padding: 4rem 1rem;
        }

        .empty-state-icon {
            display: inline-flex;
            width: 70px;
            height: 70px;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: var(--falcon-gray-500, #9da9bb);
            background: var(--falcon-gray-100, #f9fafd);
            font-size: 1.65rem;
        }

        @media (max-width: 991.98px) {
            .payroll-page-header::after {
                right: -100px;
            }

            .page-icon-wrapper {
                width: 48px;
                height: 48px;
                flex-basis: 48px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid payroll-adjustments-page" data-layout="container">
        <div class="content">

            {{-- Success Message --}}
            @if (session('success'))
                <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-3" role="alert">
                    <span class="fas fa-check-circle fs-5 me-3"></span>

                    <div class="flex-1">
                        <div class="fw-semibold">Operation completed</div>
                        <div class="fs-10">{{ session('success') }}</div>
                    </div>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close"
                    ></button>
                </div>
            @endif

            {{-- Page Header --}}
            <div class="card payroll-page-header border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-4">
                        <div class="d-flex align-items-start">
                            <div class="page-icon-wrapper me-3">
                                <span class="fas fa-user-clock"></span>
                            </div>

                            <div>
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <h3 class="mb-0 text-900">
                                        Payroll Attendance Adjustments
                                    </h3>

                                    <span class="badge rounded-pill bg-primary-subtle text-primary">
                                        Payroll Module
                                    </span>
                                </div>

                                <p class="mb-0 text-600" style="max-width: 850px;">
                                    Review and manage leave, schedule, offset, official business,
                                    overtime, holiday work, manual time, and disaster-related payroll
                                    attendance adjustments.
                                </p>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <a
                                href="{{ route('payroll-attendance-adjustments.index') }}"
                                class="btn btn-falcon-default"
                            >
                                <span class="fas fa-sync-alt me-2"></span>
                                Refresh
                            </a>

                            <a
                                href="{{ route('payroll-attendance-adjustments.create') }}"
                                class="btn btn-primary shadow-sm"
                            >
                                <span class="fas fa-plus me-2"></span>
                                New Adjustment
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Statistics --}}
            <div class="row g-3 mb-3">
                <div class="col-sm-6 col-xl">
                    <div class="card stat-card border-0 shadow-sm h-100 text-primary">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-primary-subtle text-primary me-3">
                                    <span class="fas fa-layer-group"></span>
                                </div>

                                <div class="flex-1">
                                    <div class="fs-8 text-600 fw-semibold mb-1">
                                        Total Adjustments
                                    </div>
                                    <h3 class="mb-0 text-900">
                                        {{ number_format($stats['total'] ?? 0) }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl">
                    <div class="card stat-card border-0 shadow-sm h-100 text-success">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-success-subtle text-success me-3">
                                    <span class="fas fa-notes-medical"></span>
                                </div>

                                <div class="flex-1">
                                    <div class="fs-8 text-600 fw-semibold mb-1">
                                        Leave Adjustments
                                    </div>
                                    <h3 class="mb-0 text-900">
                                        {{ number_format($stats['leaves'] ?? 0) }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl">
                    <div class="card stat-card border-0 shadow-sm h-100 text-warning">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-warning-subtle text-warning me-3">
                                    <span class="fas fa-exchange-alt"></span>
                                </div>

                                <div class="flex-1">
                                    <div class="fs-8 text-600 fw-semibold mb-1">
                                        Offset Requests
                                    </div>
                                    <h3 class="mb-0 text-900">
                                        {{ number_format($stats['offsets'] ?? 0) }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl">
                    <div class="card stat-card border-0 shadow-sm h-100 text-info">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-info-subtle text-info me-3">
                                    <span class="fas fa-stopwatch"></span>
                                </div>

                                <div class="flex-1">
                                    <div class="fs-8 text-600 fw-semibold mb-1">
                                        Manual Time
                                    </div>
                                    <h3 class="mb-0 text-900">
                                        {{ number_format($stats['manual_time'] ?? 0) }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl">
                    <div class="card stat-card border-0 shadow-sm h-100 text-danger">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-danger-subtle text-danger me-3">
                                    <span class="fas fa-cloud-showers-heavy"></span>
                                </div>

                                <div class="flex-1">
                                    <div class="fs-8 text-600 fw-semibold mb-1">
                                        Typhoon / Disaster
                                    </div>
                                    <h3 class="mb-0 text-900">
                                        {{ number_format($stats['disasters'] ?? 0) }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-body-tertiary border-bottom py-3">
                    <div class="d-flex align-items-center">
                        <span class="fas fa-filter text-primary me-2"></span>

                        <div>
                            <h6 class="mb-0 text-900">Filter Adjustments</h6>
                            <div class="fs-10 text-600">
                                Search and narrow down payroll attendance records.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <form
                        method="GET"
                        action="{{ route('payroll-attendance-adjustments.index') }}"
                        class="row g-3 align-items-end"
                    >
                        <div class="col-12 col-lg-4">
                            <label for="adjustment-search" class="filter-label">
                                Search
                            </label>

                            <div class="input-group">
                                <span class="input-group-text bg-body-tertiary">
                                    <span class="fas fa-search text-500"></span>
                                </span>

                                <input
                                    id="adjustment-search"
                                    type="text"
                                    name="search"
                                    class="form-control filter-control"
                                    placeholder="Employee, number, type, or reason"
                                    value="{{ $search }}"
                                >
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <label for="adjustment-type" class="filter-label">
                                Adjustment Type
                            </label>

                            <select
                                id="adjustment-type"
                                name="type"
                                class="form-select filter-control"
                            >
                                <option value="">All adjustment types</option>

                                @foreach ($types as $value => $label)
                                    <option value="{{ $value }}" @selected($type === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-2">
                            <label for="adjustment-date-from" class="filter-label">
                                Date From
                            </label>

                            <input
                                id="adjustment-date-from"
                                type="date"
                                name="date_from"
                                class="form-control filter-control"
                                value="{{ $dateFrom }}"
                            >
                        </div>

                        <div class="col-12 col-sm-6 col-lg-2">
                            <label for="adjustment-date-to" class="filter-label">
                                Date To
                            </label>

                            <input
                                id="adjustment-date-to"
                                type="date"
                                name="date_to"
                                class="form-control filter-control"
                                value="{{ $dateTo }}"
                            >
                        </div>

                        <div class="col-12 col-sm-6 col-lg-1">
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary filter-control">
                                    <span class="fas fa-search"></span>
                                    <span class="d-lg-none ms-2">Apply Filters</span>
                                </button>
                            </div>
                        </div>

                        @if ($search || $type || $dateFrom || $dateTo)
                            <div class="col-12">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-2 border-top">
                                    <div class="fs-10 text-600">
                                        <span class="fas fa-info-circle me-1"></span>
                                        Filtered results are currently displayed.
                                    </div>

                                    <a
                                        href="{{ route('payroll-attendance-adjustments.index') }}"
                                        class="btn btn-link btn-sm text-danger text-decoration-none p-0"
                                    >
                                        <span class="fas fa-times me-1"></span>
                                        Clear all filters
                                    </a>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Adjustment Table --}}
            <div class="card table-card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                        <div>
                            <h5 class="mb-1 text-900">
                                Adjustment Records
                            </h5>

                            <p class="mb-0 fs-10 text-600">
                                Payroll attendance corrections and approved exceptions.
                            </p>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <span class="badge rounded-pill bg-primary-subtle text-primary px-3 py-2">
                                {{ number_format($adjustments->total()) }}
                                {{ \Illuminate\Support\Str::plural('record', $adjustments->total()) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive scrollbar">
                        <table class="table payroll-table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Employee</th>
                                    <th>Adjustment Type</th>
                                    <th>Period / Date</th>
                                    <th>Adjusted Time</th>
                                    <th>Offset Proof</th>
                                    <th>Payroll Effect</th>
                                    <th>Encoded By</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($adjustments as $item)
                                    @php
                                        $typeConfig = match ($item->adjustment_type) {
                                            'sick_leave' => [
                                                'color' => 'success',
                                                'icon' => 'fa-briefcase-medical',
                                            ],
                                            'medical_leave' => [
                                                'color' => 'success',
                                                'icon' => 'fa-notes-medical',
                                            ],
                                            'change_schedule' => [
                                                'color' => 'info',
                                                'icon' => 'fa-calendar-alt',
                                            ],
                                            'offset' => [
                                                'color' => 'warning',
                                                'icon' => 'fa-exchange-alt',
                                            ],
                                            'official_business' => [
                                                'color' => 'primary',
                                                'icon' => 'fa-building',
                                            ],
                                            'holiday_work' => [
                                                'color' => 'dark',
                                                'icon' => 'fa-calendar-check',
                                            ],
                                            'overtime' => [
                                                'color' => 'dark',
                                                'icon' => 'fa-business-time',
                                            ],
                                            'typhoon_disaster' => [
                                                'color' => 'danger',
                                                'icon' => 'fa-cloud-showers-heavy',
                                            ],
                                            default => [
                                                'color' => 'secondary',
                                                'icon' => 'fa-clock',
                                            ],
                                        };

                                        $employeeName = trim((string) $item->employee_name);

                                        $employeeInitials = collect(
                                            preg_split('/\s+/', $employeeName) ?: []
                                        )
                                            ->filter()
                                            ->take(2)
                                            ->map(
                                                fn (string $part): string => mb_strtoupper(
                                                    mb_substr($part, 0, 1)
                                                )
                                            )
                                            ->implode('');

                                        $employeeInitials = $employeeInitials ?: 'NA';
                                    @endphp

                                    <tr>
                                        {{-- Employee --}}
                                        <td class="employee-cell ps-4">
                                            <div class="d-flex align-items-center">
                                                @if ($item->adjustment_type === 'typhoon_disaster')
                                                    <div class="employee-avatar employee-avatar-danger me-3">
                                                        <span class="fas fa-users"></span>
                                                    </div>

                                                    <div>
                                                        <div class="employee-name text-danger">
                                                            All Qualified Employees
                                                        </div>

                                                        <div class="employee-meta">
                                                            Employees with valid time-in records
                                                        </div>

                                                        <div class="employee-meta">
                                                            No individual employee selection
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="employee-avatar me-3">
                                                        {{ $employeeInitials }}
                                                    </div>

                                                    <div>
                                                        <div
                                                            class="employee-name"
                                                            title="{{ $item->employee_name }}"
                                                        >
                                                            {{ $item->employee_name ?: 'Unnamed Employee' }}
                                                        </div>

                                                        <div class="employee-meta">
                                                            Employee No:
                                                            <span class="text-700">
                                                                {{ $item->employee_no ?: 'N/A' }}
                                                            </span>
                                                        </div>

                                                        <div class="employee-meta">
                                                            Bio ID:
                                                            <span class="text-700">
                                                                {{ $item->employee_biometric_id ?: 'N/A' }}
                                                            </span>

                                                            <span class="mx-1">•</span>

                                                            Legacy:
                                                            <span class="text-700">
                                                                {{ $item->biometric_employee_id ?: 'N/A' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Type --}}
                                        <td>
                                            <span
                                                class="badge rounded-pill payroll-type-badge
                                                    bg-{{ $typeConfig['color'] }}-subtle
                                                    text-{{ $typeConfig['color'] }}"
                                            >
                                                <span class="fas {{ $typeConfig['icon'] }} me-2"></span>
                                                {{ $item->type_label }}
                                            </span>
                                        </td>

                                        {{-- Period --}}
                                        <td class="period-cell">
                                            <div class="fw-semibold text-900">
                                                {{ $item->period_label }}
                                            </div>

                                            <div class="fs-10 text-600 mt-1">
                                                <span class="fas fa-calendar-day me-1"></span>
                                                {{ $item->adjusted_day_type
                                                    ? \Illuminate\Support\Str::headline($item->adjusted_day_type)
                                                    : 'Standard day' }}
                                            </div>
                                        </td>

                                        {{-- Adjusted Time --}}
                                        <td class="adjusted-time-cell">
                                            <div class="d-flex align-items-center">
                                                <span class="fas fa-clock text-primary me-2"></span>

                                                <span class="fw-semibold text-800">
                                                    {{ $item->adjusted_time_label }}
                                                </span>
                                            </div>
                                        </td>

                                        {{-- Offset Proof --}}
                                        <td class="proof-cell">
                                            @if ($item->adjustment_type === 'offset')
                                                <div class="fw-semibold text-900 mb-1">
                                                    {{ $item->offset_proof_label }}
                                                </div>

                                                <div class="fs-10 text-success">
                                                    <span class="fas fa-check-circle me-1"></span>
                                                    Biometrics verified
                                                </div>
                                            @else
                                                <span class="text-400">Not applicable</span>
                                            @endif
                                        </td>

                                        {{-- Payroll Effect --}}
                                        <td class="effect-cell">
                                            <div class="d-flex flex-wrap gap-1">
                                                @if ($item->is_paid)
                                                    <span class="badge payroll-effect-badge bg-success-subtle text-success">
                                                        <span class="fas fa-coins me-1"></span>
                                                        Paid
                                                    </span>
                                                @else
                                                    <span class="badge payroll-effect-badge bg-secondary-subtle text-secondary">
                                                        <span class="fas fa-ban me-1"></span>
                                                        Unpaid
                                                    </span>
                                                @endif

                                                @if ($item->ignore_late)
                                                    <span class="badge payroll-effect-badge bg-info-subtle text-info">
                                                        <span class="fas fa-forward me-1"></span>
                                                        Ignore Late
                                                    </span>
                                                @endif

                                                @if ($item->ignore_undertime)
                                                    <span class="badge payroll-effect-badge bg-warning-subtle text-warning">
                                                        <span class="fas fa-hourglass-end me-1"></span>
                                                        Ignore UT
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Encoded --}}
                                        <td class="encoded-cell">
                                            <div class="d-flex align-items-center">
                                                <div
                                                    class="avatar avatar-2xl me-2"
                                                    title="{{ $item->encoder->name ?? 'Unknown encoder' }}"
                                                >
                                                    <div class="avatar-name rounded-circle bg-primary-subtle text-primary">
                                                        <span class="fas fa-user"></span>
                                                    </div>
                                                </div>

                                                <div>
                                                    <div class="fw-semibold text-900">
                                                        {{ $item->encoder->name ?? 'N/A' }}
                                                    </div>

                                                    <div class="fs-10 text-600">
                                                        {{ $item->encoded_at?->format('M d, Y') ?? '—' }}
                                                    </div>

                                                    <div class="fs-10 text-500">
                                                        {{ $item->encoded_at?->format('h:i A') ?? '' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Actions --}}
                                        <td class="action-cell text-end pe-4">
                                            <div class="d-inline-flex align-items-center gap-1">
                                                <a
                                                    href="{{ route('payroll-attendance-adjustments.edit', $item) }}"
                                                    class="btn btn-falcon-warning btn-sm action-button"
                                                    title="Edit adjustment"
                                                    aria-label="Edit adjustment"
                                                >
                                                    <span class="fas fa-edit"></span>
                                                </a>

                                                <form
                                                    method="POST"
                                                    action="{{ route('payroll-attendance-adjustments.destroy', $item) }}"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Delete this payroll attendance adjustment? This action cannot be undone.');"
                                                >
                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="btn btn-falcon-danger btn-sm action-button"
                                                        title="Delete adjustment"
                                                        aria-label="Delete adjustment"
                                                    >
                                                        <span class="fas fa-trash-alt"></span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">
                                            <div class="empty-state text-center">
                                                <div class="empty-state-icon mb-3">
                                                    <span class="fas fa-folder-open"></span>
                                                </div>

                                                <h5 class="text-900 mb-2">
                                                    No adjustment records found
                                                </h5>

                                                <p class="text-600 mb-3">
                                                    No payroll attendance adjustments match the current filters.
                                                </p>

                                                @if ($search || $type || $dateFrom || $dateTo)
                                                    <a
                                                        href="{{ route('payroll-attendance-adjustments.index') }}"
                                                        class="btn btn-falcon-default btn-sm"
                                                    >
                                                        <span class="fas fa-times me-1"></span>
                                                        Clear Filters
                                                    </a>
                                                @else
                                                    <a
                                                        href="{{ route('payroll-attendance-adjustments.create') }}"
                                                        class="btn btn-primary btn-sm"
                                                    >
                                                        <span class="fas fa-plus me-1"></span>
                                                        Create First Adjustment
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

                @if ($adjustments->isNotEmpty())
                    <div class="card-footer bg-body-tertiary border-top">
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                            <div class="fs-10 text-600">
                                Showing
                                <span class="fw-semibold text-800">
                                    {{ number_format($adjustments->firstItem() ?? 0) }}
                                </span>
                                to
                                <span class="fw-semibold text-800">
                                    {{ number_format($adjustments->lastItem() ?? 0) }}
                                </span>
                                of
                                <span class="fw-semibold text-800">
                                    {{ number_format($adjustments->total()) }}
                                </span>
                                records
                            </div>

                            @if ($adjustments->hasPages())
                                <div>
                                    {{ $adjustments->links('pagination.custom') }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
