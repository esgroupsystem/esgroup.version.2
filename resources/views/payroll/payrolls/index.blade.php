@extends('layouts.app')

@section('title', 'Payroll Batches')

@push('styles')
    <style>
        .payroll-batches-page {
            --payroll-border: var(--falcon-border-color, #d8e2ef);
            --payroll-muted: var(--falcon-gray-600, #748194);
        }

        .payroll-batches-header {
            position: relative;
            overflow: hidden;
            background:
                linear-gradient(135deg,
                    rgba(44, 123, 229, 0.14) 0%,
                    rgba(44, 123, 229, 0.04) 55%,
                    rgba(39, 188, 253, 0.08) 100%);
        }

        .payroll-batches-header::before {
            position: absolute;
            top: -110px;
            right: -55px;
            width: 260px;
            height: 260px;
            content: "";
            border-radius: 50%;
            background: rgba(44, 123, 229, 0.08);
            pointer-events: none;
        }

        .payroll-batches-header::after {
            position: absolute;
            right: 165px;
            bottom: -95px;
            width: 180px;
            height: 180px;
            content: "";
            border-radius: 50%;
            background: rgba(39, 188, 253, 0.06);
            pointer-events: none;
        }

        .payroll-batches-header .card-body {
            position: relative;
            z-index: 1;
        }

        .payroll-header-icon {
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

        .filter-label {
            margin-bottom: 0.4rem;
            color: var(--falcon-gray-700, #5e6e82);
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.035em;
            text-transform: uppercase;
        }

        .filter-control {
            min-height: 41px;
        }

        .payroll-table-card {
            overflow: hidden;
        }

        .payroll-table {
            min-width: 1180px;
        }

        .payroll-table thead th {
            padding-top: 0.9rem;
            padding-bottom: 0.9rem;
            border-bottom-width: 1px;
            color: var(--falcon-gray-700, #5e6e82);
            background: var(--falcon-gray-100, #f9fafd);
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.035em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .payroll-table tbody td {
            padding-top: 1rem;
            padding-bottom: 1rem;
            border-color: var(--payroll-border);
            vertical-align: middle;
        }

        .payroll-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .payroll-number-cell {
            min-width: 190px;
        }

        .payroll-number-icon {
            display: inline-flex;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            color: var(--falcon-primary, #2c7be5);
            background: rgba(44, 123, 229, 0.12);
            font-size: 0.95rem;
        }

        .payroll-number-link {
            display: inline-block;
            max-width: 185px;
            overflow: hidden;
            color: var(--falcon-gray-900, #344050);
            font-weight: 700;
            text-decoration: none;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .payroll-number-link:hover {
            color: var(--falcon-primary, #2c7be5);
        }

        .coverage-cell {
            min-width: 190px;
        }

        .contribution-cell {
            min-width: 150px;
        }

        .group-cell {
            min-width: 150px;
        }

        .generated-cell {
            min-width: 205px;
        }

        .action-cell {
            min-width: 120px;
        }

        .employee-count {
            display: inline-flex;
            min-width: 42px;
            height: 34px;
            align-items: center;
            justify-content: center;
            padding: 0 0.7rem;
            border-radius: 10px;
            color: var(--falcon-primary, #2c7be5);
            background: rgba(44, 123, 229, 0.1);
            font-size: 0.82rem;
            font-weight: 700;
        }

        .payroll-badge {
            display: inline-flex;
            min-height: 28px;
            align-items: center;
            padding: 0.38rem 0.65rem;
            border-radius: 999px;
            font-size: 0.68rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .generator-avatar {
            display: inline-flex;
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: var(--falcon-primary, #2c7be5);
            background: rgba(44, 123, 229, 0.12);
            font-size: 0.75rem;
            font-weight: 700;
        }

        .generator-name {
            max-width: 145px;
            overflow: hidden;
            color: var(--falcon-gray-900, #344050);
            font-size: 0.78rem;
            font-weight: 600;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .action-button {
            display: inline-flex;
            width: 34px;
            height: 34px;
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
            .payroll-batches-header::before {
                right: -130px;
            }

            .payroll-header-icon {
                width: 49px;
                height: 49px;
                flex-basis: 49px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid payroll-batches-page" data-layout="container">
        <div class="content">

            {{-- Success message --}}
            @if (session('success'))
                <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-3" role="alert">
                    <span class="fas fa-check-circle fs-5 me-3"></span>

                    <div class="flex-1">
                        <div class="fw-semibold">Operation completed</div>
                        <div class="fs-10">
                            {{ session('success') }}
                        </div>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Error message --}}
            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center mb-3" role="alert">
                    <span class="fas fa-exclamation-circle fs-5 me-3"></span>

                    <div class="flex-1">
                        <div class="fw-semibold">Unable to process request</div>
                        <div class="fs-10">
                            {{ $errors->first() }}
                        </div>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Page header --}}
            <div class="card payroll-batches-header border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-4">
                        <div class="d-flex align-items-start">
                            <div class="payroll-header-icon me-3">
                                <span class="fas fa-money-check-alt"></span>
                            </div>

                            <div>
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <h3 class="mb-0 text-900">
                                        Payroll Batches
                                    </h3>

                                    <span class="badge rounded-pill bg-primary-subtle text-primary">
                                        Payroll Management
                                    </span>
                                </div>

                                <p class="mb-0 text-600" style="max-width: 820px;">
                                    Review generated payroll batches, attendance computations,
                                    salary earnings, government contributions, loans, deductions,
                                    adjustments, and payroll processing history.
                                </p>
                            </div>
                        </div>

                        <a href="{{ route('payroll.create') }}" class="btn btn-primary shadow-sm">
                            <span class="fas fa-plus me-2"></span>
                            Generate Payroll
                        </a>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-body-tertiary border-bottom py-3">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                        <div class="d-flex align-items-center">
                            <span class="fas fa-filter text-primary me-3"></span>

                            <div>
                                <h6 class="mb-1 text-900">
                                    Filter Payroll Batches
                                </h6>

                                <p class="mb-0 fs-10 text-600">
                                    Search payroll records by number, cutoff, status, or remarks.
                                </p>
                            </div>
                        </div>

                        <span class="badge rounded-pill bg-primary-subtle text-primary px-3 py-2">
                            {{ number_format($payrolls->total()) }}
                            {{ \Illuminate\Support\Str::plural('batch', $payrolls->total()) }}
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('payroll.index') }}" class="row g-3 align-items-end">
                        <div class="col-12 col-lg-5">
                            <label for="payroll-search" class="filter-label">
                                Search
                            </label>

                            <div class="input-group">
                                <span class="input-group-text bg-body-tertiary">
                                    <span class="fas fa-search text-500"></span>
                                </span>

                                <input id="payroll-search" type="text" name="search" class="form-control filter-control"
                                    placeholder="Payroll number, status, or remarks" value="{{ request('search') }}">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-2">
                            <label for="cutoff-type" class="filter-label">
                                Cutoff Type
                            </label>

                            <select id="cutoff-type" name="cutoff_type" class="form-select filter-control">
                                <option value="">All cutoffs</option>

                                <option value="second" @selected(request('cutoff_type') === 'second')>
                                    {{ config('payroll.cutoff_display.second.full', '1st Cutoff (26-10)') }}
                                </option>

                                <option value="first" @selected(request('cutoff_type') === 'first')>
                                    {{ config('payroll.cutoff_display.first.full', '2nd Cutoff (11-25)') }}
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-2">
                            <label for="payroll-status" class="filter-label">
                                Status
                            </label>

                            <select id="payroll-status" name="status" class="form-select filter-control">
                                <option value="">All statuses</option>

                                <option value="draft" @selected(request('status') === 'draft')>
                                    Draft
                                </option>

                                <option value="finalized" @selected(request('status') === 'finalized')>
                                    Finalized
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-2">
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary filter-control">
                                    <span class="fas fa-search me-2"></span>
                                    Apply Filter
                                </button>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-1">
                            <div class="d-grid">
                                <a href="{{ route('payroll.index') }}"
                                    class="btn btn-falcon-default filter-control d-flex align-items-center justify-content-center"
                                    title="Clear filters">
                                    <span class="fas fa-redo-alt"></span>
                                </a>
                            </div>
                        </div>

                        @if (request()->filled('search') || request()->filled('cutoff_type') || request()->filled('status'))
                            <div class="col-12">
                                <div
                                    class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-3 border-top">
                                    <div class="fs-10 text-600">
                                        <span class="fas fa-info-circle text-primary me-1"></span>
                                        Filtered payroll records are currently displayed.
                                    </div>

                                    <a href="{{ route('payroll.index') }}"
                                        class="btn btn-link btn-sm text-danger text-decoration-none p-0">
                                        <span class="fas fa-times me-1"></span>
                                        Clear all filters
                                    </a>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Payroll table --}}
            <div class="card payroll-table-card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                        <div>
                            <h5 class="mb-1 text-900">
                                Payroll Batch Records
                            </h5>

                            <p class="mb-0 fs-10 text-600">
                                Generated payroll periods and their current processing status.
                            </p>
                        </div>

                        <a href="{{ route('payroll.create') }}" class="btn btn-falcon-primary btn-sm">
                            <span class="fas fa-calculator me-1"></span>
                            New Payroll Run
                        </a>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive scrollbar">
                        <table class="table payroll-table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Payroll Batch</th>
                                    <th>Coverage</th>
                                    <th>Contribution Month</th>
                                    <th class="text-center">Employees</th>
                                    <th>Payroll Group</th>
                                    <th>Status</th>
                                    <th>Generated By</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($payrolls as $payroll)
                                    @php
                                        $isFinalized = $payroll->status === 'finalized';

                                        $generatorName =
                                            $payroll->generator->full_name ?? ($payroll->generator->name ?? 'N/A');

                                        $generatorInitials = collect(
                                            preg_split('/\s+/', trim((string) $generatorName)) ?: [],
                                        )
                                            ->filter()
                                            ->take(2)
                                            ->map(fn(string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
                                            ->implode('');

                                        $generatorInitials = $generatorInitials ?: 'NA';
                                    @endphp

                                    <tr>
                                        {{-- Payroll batch --}}
                                        <td class="payroll-number-cell ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="payroll-number-icon me-3">
                                                    <span class="fas fa-file-invoice-dollar"></span>
                                                </div>

                                                <div>
                                                    <a href="{{ route('payroll.show', $payroll) }}"
                                                        class="payroll-number-link"
                                                        title="{{ $payroll->payroll_number }}">
                                                        {{ $payroll->payroll_number }}
                                                    </a>

                                                    <div class="fs-10 text-600 mt-1">
                                                        <span class="fas fa-calendar-week me-1"></span>
                                                        {{ $payroll->cutoff_label }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Coverage --}}
                                        <td class="coverage-cell">
                                            <div class="fw-semibold text-900">
                                                {{ optional($payroll->period_start)->format('M d, Y') ?: 'N/A' }}
                                            </div>

                                            <div class="fs-10 text-600 mt-1">
                                                <span class="fas fa-arrow-right me-1"></span>
                                                {{ optional($payroll->period_end)->format('M d, Y') ?: 'N/A' }}
                                            </div>
                                        </td>

                                        {{-- Contribution --}}
                                        <td class="contribution-cell">
                                            <span class="payroll-badge bg-info-subtle text-info">
                                                <span class="fas fa-calendar-check me-2"></span>
                                                {{ $payroll->contribution_label }}
                                            </span>
                                        </td>

                                        {{-- Employees --}}
                                        <td class="text-center">
                                            <span class="employee-count" title="Employees included">
                                                {{ number_format($payroll->items_count ?? 0) }}
                                            </span>
                                        </td>

                                        {{-- Group --}}
                                        <td class="group-cell">
                                            <span class="payroll-badge bg-primary-subtle text-primary">
                                                <span class="fas fa-users me-2"></span>
                                                {{ $payroll->garage_group_label }}
                                            </span>
                                        </td>

                                        {{-- Status --}}
                                        <td>
                                            @if ($isFinalized)
                                                <span class="payroll-badge bg-success-subtle text-success">
                                                    <span class="fas fa-check-circle me-2"></span>
                                                    Finalized
                                                </span>
                                            @else
                                                <span class="payroll-badge bg-warning-subtle text-warning">
                                                    <span class="fas fa-pen me-2"></span>
                                                    Draft
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Generated --}}
                                        <td class="generated-cell">
                                            <div class="d-flex align-items-center">
                                                <div class="generator-avatar me-2" title="{{ $generatorName }}">
                                                    {{ $generatorInitials }}
                                                </div>

                                                <div>
                                                    <div class="generator-name" title="{{ $generatorName }}">
                                                        {{ $generatorName }}
                                                    </div>

                                                    <div class="fs-10 text-600 mt-1">
                                                        {{ optional($payroll->generated_at)->format('M d, Y') ?: 'N/A' }}
                                                    </div>

                                                    <div class="fs-10 text-500">
                                                        {{ optional($payroll->generated_at)->format('h:i A') ?: '' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Actions --}}
                                        <td class="action-cell text-end pe-4">
                                            <div class="d-inline-flex align-items-center gap-1">
                                                <a href="{{ route('payroll.show', $payroll) }}"
                                                    class="btn btn-falcon-primary btn-sm action-button"
                                                    title="View payroll" aria-label="View payroll">
                                                    <span class="fas fa-eye"></span>
                                                </a>

                                                @if (!$isFinalized)
                                                    <form method="POST"
                                                        action="{{ route('payroll.destroy', $payroll) }}"
                                                        class="d-inline"
                                                        onsubmit="return confirm('Delete this draft payroll? This action cannot be undone.');">
                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="submit"
                                                            class="btn btn-falcon-danger btn-sm action-button"
                                                            title="Delete draft payroll"
                                                            aria-label="Delete draft payroll">
                                                            <span class="fas fa-trash-alt"></span>
                                                        </button>
                                                    </form>
                                                @else
                                                    <button type="button"
                                                        class="btn btn-falcon-default btn-sm action-button"
                                                        title="Finalized payroll cannot be deleted"
                                                        aria-label="Finalized payroll cannot be deleted" disabled>
                                                        <span class="fas fa-lock"></span>
                                                    </button>
                                                @endif
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
                                                    No payroll batches found
                                                </h5>

                                                <p class="text-600 mb-3">
                                                    No payroll records match the current search and filter criteria.
                                                </p>

                                                @if (request()->filled('search') || request()->filled('cutoff_type') || request()->filled('status'))
                                                    <a href="{{ route('payroll.index') }}"
                                                        class="btn btn-falcon-default btn-sm">
                                                        <span class="fas fa-times me-1"></span>
                                                        Clear Filters
                                                    </a>
                                                @else
                                                    <a href="{{ route('payroll.create') }}"
                                                        class="btn btn-primary btn-sm">
                                                        <span class="fas fa-plus me-1"></span>
                                                        Generate First Payroll
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

                @if ($payrolls->isNotEmpty())
                    <div class="card-footer bg-body-tertiary border-top">
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                            <div class="fs-10 text-600">
                                Showing
                                <span class="fw-semibold text-800">
                                    {{ number_format($payrolls->firstItem() ?? 0) }}
                                </span>
                                to
                                <span class="fw-semibold text-800">
                                    {{ number_format($payrolls->lastItem() ?? 0) }}
                                </span>
                                of
                                <span class="fw-semibold text-800">
                                    {{ number_format($payrolls->total()) }}
                                </span>
                                payroll batches
                            </div>

                            @if ($payrolls->hasPages())
                                <div>
                                    {{ $payrolls->links('pagination.custom') }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
