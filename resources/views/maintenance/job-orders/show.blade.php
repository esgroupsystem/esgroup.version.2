@extends('layouts.app')

@section('title', 'Maintenance Job Order Details')

@section('content')
    <div class="container" data-layout="container">
        <script>
            const isFluid = JSON.parse(localStorage.getItem('isFluid'));
            if (isFluid) {
                const container = document.querySelector('[data-layout]');
                container.classList.remove('container');
                container.classList.add('container-fluid');
            }
        </script>

        <style>
            .jo-show-v2 {
                --jo-border: #e6edf7;
                --jo-muted: #748194;
                --jo-soft: #f8fafd;
                --jo-dark: #1f2a44;
            }

            .jo-show-v2 .jo-hero {
                position: relative;
                overflow: hidden;
                border: 1px solid rgba(216, 226, 239, .9);
                border-radius: 1.1rem;
                background:
                    radial-gradient(circle at top right, rgba(47, 111, 237, .10), transparent 34%),
                    linear-gradient(135deg, #ffffff 0%, #f7faff 100%);
            }

            .jo-show-v2 .jo-hero::before {
                content: "";
                position: absolute;
                left: 0;
                top: 0;
                width: 7px;
                height: 100%;
                background: var(--falcon-primary);
            }

            .jo-show-v2 .jo-hero-icon {
                width: 62px;
                height: 62px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 20px;
                box-shadow: 0 .75rem 1.5rem rgba(15, 23, 42, .08);
            }

            .jo-show-v2 .jo-title {
                font-weight: 800;
                color: var(--jo-dark);
                letter-spacing: -.02em;
            }

            .jo-show-v2 .jo-subtitle {
                color: var(--jo-muted);
                font-size: .92rem;
            }

            .jo-show-v2 .jo-pill {
                display: inline-flex;
                align-items: center;
                gap: .35rem;
                border-radius: 999px;
                padding: .45rem .7rem;
                background: #fff;
                border: 1px solid var(--jo-border);
                color: #526273;
                font-size: .78rem;
                font-weight: 700;
            }

            .jo-show-v2 .jo-action-card {
                border: 1px solid var(--jo-border);
                border-radius: 1rem;
                background: #fff;
                box-shadow: 0 .45rem 1.1rem rgba(15, 23, 42, .035);
            }

            .jo-show-v2 .jo-action-btn {
                min-height: 42px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: .4rem;
                border-radius: .75rem;
                font-weight: 700;
            }

            .jo-show-v2 .jo-card {
                border: 1px solid var(--jo-border);
                border-radius: 1rem;
                background: #fff;
                box-shadow: 0 .45rem 1.1rem rgba(15, 23, 42, .035);
            }

            .jo-show-v2 .jo-card-header {
                padding: 1rem 1.15rem;
                border-bottom: 1px solid var(--jo-border);
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
            }

            .jo-show-v2 .jo-card-title {
                margin: 0;
                color: var(--jo-dark);
                font-weight: 800;
                font-size: 1rem;
            }

            .jo-show-v2 .jo-section-icon {
                width: 34px;
                height: 34px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: .8rem;
                background: #edf4ff;
                color: var(--falcon-primary);
            }

            .jo-show-v2 .jo-stat {
                min-height: 108px;
                border: 1px solid var(--jo-border);
                border-radius: 1rem;
                padding: 1rem;
                background: linear-gradient(180deg, #fff 0%, #fbfdff 100%);
            }

            .jo-show-v2 .jo-label {
                font-size: .68rem;
                color: var(--jo-muted);
                text-transform: uppercase;
                letter-spacing: .06em;
                font-weight: 800;
                margin-bottom: .35rem;
            }

            .jo-show-v2 .jo-value {
                color: var(--jo-dark);
                font-weight: 800;
                line-height: 1.25;
            }

            .jo-show-v2 .jo-description-box {
                min-height: 190px;
                border: 1px solid var(--jo-border);
                border-radius: 1rem;
                background: #f8fafd;
                padding: 1.15rem;
                color: #344050;
                line-height: 1.65;
                white-space: pre-line;
            }

            .jo-show-v2 .jo-side-row {
                padding: .95rem 0;
                border-bottom: 1px solid var(--jo-border);
            }

            .jo-show-v2 .jo-side-row:last-child {
                border-bottom: 0;
            }

            .jo-show-v2 .jo-workflow {
                position: relative;
                padding-left: 1.6rem;
            }

            .jo-show-v2 .jo-workflow::before {
                content: "";
                position: absolute;
                left: .45rem;
                top: .3rem;
                bottom: .3rem;
                width: 2px;
                background: #e8eef7;
            }

            .jo-show-v2 .jo-workflow-item {
                position: relative;
                padding-bottom: 1.1rem;
            }

            .jo-show-v2 .jo-workflow-item:last-child {
                padding-bottom: 0;
            }

            .jo-show-v2 .jo-workflow-dot {
                position: absolute;
                left: -1.48rem;
                top: .2rem;
                width: .8rem;
                height: .8rem;
                border-radius: 50%;
                background: #c8d4e5;
                border: 2px solid #fff;
                box-shadow: 0 0 0 1px #c8d4e5;
            }

            .jo-show-v2 .jo-workflow-item.active .jo-workflow-dot {
                background: var(--falcon-primary);
                box-shadow: 0 0 0 2px rgba(47, 111, 237, .18);
            }

            .jo-show-v2 .jo-history-item {
                border-left: 3px solid #d8e2ef;
                padding: .25rem 0 .25rem 1rem;
                margin-bottom: 1rem;
            }

            .jo-show-v2 .jo-history-item:last-child {
                margin-bottom: 0;
            }

            .jo-show-v2 .jo-history-meta {
                font-size: .76rem;
                color: var(--jo-muted);
            }

            .jo-show-v2 .jo-empty {
                border: 1px dashed #ccd8e8;
                border-radius: 1rem;
                background: #fbfdff;
            }

            @media print {

                .navbar,
                .navbar-vertical,
                .jo-action-card,
                .btn,
                .footer {
                    display: none !important;
                }

                .jo-show-v2 .jo-card,
                .jo-show-v2 .jo-hero {
                    box-shadow: none !important;
                    border: 1px solid #d8e2ef !important;
                }
            }
        </style>

        <div class="content jo-show-v2">
            @if (session('success'))
                <div class="alert alert-success border-0 shadow-sm d-flex align-items-center" role="alert">
                    <span class="fas fa-check-circle me-2"></span>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center" role="alert">
                    <span class="fas fa-exclamation-circle me-2"></span>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            <div class="card jo-hero border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <div class="row g-4 align-items-center">
                        <div class="col-xl-8">
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <div class="jo-hero-icon {{ $jobOrder->status_badge_class }}">
                                    <span class="{{ $jobOrder->status_icon }} fs-3"></span>
                                </div>

                                <div class="flex-1">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                        <h3 class="jo-title mb-0">{{ $jobOrder->job_order_no }}</h3>

                                        <span class="badge rounded-pill {{ $jobOrder->status_badge_class }}">
                                            <span class="{{ $jobOrder->status_icon }} me-1"></span>
                                            {{ $jobOrder->status_label }}
                                        </span>
                                    </div>

                                    <div class="jo-subtitle mb-3">
                                        {{ $jobOrder->status_description }}
                                    </div>

                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="jo-pill">
                                            <span class="fas fa-bus text-primary"></span>
                                            {{ $jobOrder->bus?->bus_no ?? ($jobOrder->bus_no_snapshot ?? 'N/A') }}
                                        </span>

                                        <span class="jo-pill">
                                            <span class="fas fa-id-card text-primary"></span>
                                            {{ $jobOrder->bus?->plate_no ?? ($jobOrder->plate_no_snapshot ?? 'N/A') }}
                                        </span>

                                        <span class="jo-pill">
                                            <span class="fas fa-building text-primary"></span>
                                            {{ $jobOrder->bus?->company ?? ($jobOrder->company_snapshot ?? 'N/A') }}
                                        </span>

                                        <span class="jo-pill">
                                            <span class="fas fa-calendar-day text-primary"></span>
                                            {{ $jobOrder->created_at->format('M d, Y h:i A') }}
                                        </span>

                                        <span class="jo-pill">
                                            <span class="fas fa-stopwatch text-primary"></span>
                                            Downtime: {{ $jobOrder->total_downtime_label }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4">
                            <div class="jo-action-card p-3">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <a href="{{ route('maintenance.job-orders.index') }}"
                                            class="btn btn-falcon-default jo-action-btn w-100">
                                            <span class="fas fa-arrow-left"></span>
                                            Back
                                        </a>
                                    </div>

                                    <div class="col-6">
                                        <a href="{{ route('maintenance.job-orders.export-single', ['jobOrderMaintenance' => $jobOrder, 'export_type' => 'csv']) }}"
                                            class="btn btn-falcon-success jo-action-btn w-100">
                                            <span class="fas fa-file-csv"></span>
                                            CSV
                                        </a>
                                    </div>

                                    <div class="col-6">
                                        <a href="{{ route('maintenance.job-orders.export-single', ['jobOrderMaintenance' => $jobOrder, 'export_type' => 'xls']) }}"
                                            class="btn btn-falcon-primary jo-action-btn w-100">
                                            <span class="fas fa-file-excel"></span>
                                            Excel
                                        </a>
                                    </div>

                                    <div class="col-6">
                                        <button type="button" onclick="window.print()"
                                            class="btn btn-falcon-info jo-action-btn w-100">
                                            <span class="fas fa-print"></span>
                                            Print
                                        </button>
                                    </div>

                                    @can('job-orders.update-number')
                                        <div class="col-6">
                                            <a href="{{ route('maintenance.job-orders.edit-number', $jobOrder) }}"
                                                class="btn btn-falcon-primary jo-action-btn w-100">
                                                <span class="fas fa-hashtag"></span>
                                                Edit JO-NO
                                            </a>
                                        </div>
                                    @endcan

                                    @can('job-orders.update-status')
                                        <div class="col-6">
                                            <a href="{{ route('maintenance.job-orders.edit-status', $jobOrder) }}"
                                                class="btn btn-warning jo-action-btn w-100">
                                                <span class="fas fa-pen-to-square"></span>
                                                Edit Status
                                            </a>
                                        </div>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-xl-8">
                    <div class="jo-card mb-3">
                        <div class="jo-card-header">
                            <div class="d-flex align-items-center gap-2">
                                <span class="jo-section-icon">
                                    <span class="fas fa-file-lines"></span>
                                </span>
                                <h5 class="jo-card-title">Job Order Overview</h5>
                            </div>

                            <span class="badge badge-subtle-secondary">
                                Created by {{ $jobOrder->creator?->name ?? 'System' }}
                            </span>
                        </div>

                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="jo-stat">
                                        <div class="jo-label">Bus No.</div>
                                        <h4 class="jo-value mb-0">
                                            <span class="fas fa-bus text-primary me-1"></span>
                                            {{ $jobOrder->bus?->bus_no ?? ($jobOrder->bus_no_snapshot ?? 'N/A') }}
                                        </h4>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="jo-stat">
                                        <div class="jo-label">Plate No.</div>
                                        <h4 class="jo-value mb-0">
                                            {{ $jobOrder->bus?->plate_no ?? ($jobOrder->plate_no_snapshot ?? 'N/A') }}
                                        </h4>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="jo-stat">
                                        <div class="jo-label">Requester / Staff</div>
                                        <h5 class="jo-value mb-0">
                                            {{ $jobOrder->full_name ?: 'Not specified' }}
                                        </h5>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="jo-stat">
                                        <div class="jo-label">Created Date</div>
                                        <h5 class="jo-value mb-1">
                                            {{ $jobOrder->created_at->format('M d, Y') }}
                                        </h5>
                                        <div class="fs-11 text-600">
                                            {{ $jobOrder->created_at->format('h:i A') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="jo-card mb-3">
                        <div class="jo-card-header">
                            <div class="d-flex align-items-center gap-2">
                                <span class="jo-section-icon">
                                    <span class="fas fa-stopwatch"></span>
                                </span>
                                <h5 class="jo-card-title">Downtime Analysis</h5>
                            </div>

                            <span class="badge rounded-pill {{ $jobOrder->is_downtime_running ? 'badge-subtle-warning text-warning' : 'badge-subtle-success text-success' }}">
                                {{ $jobOrder->is_downtime_running ? 'Counter Running' : 'Counter Stopped' }}
                            </span>
                        </div>

                        <div class="card-body p-3">
                            @include('maintenance.job-orders._downtime-summary', [
                                'jobOrder' => $jobOrder,
                            ])

                            <div class="table-responsive scrollbar mt-3">
                                <table class="table table-sm table-bordered align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Status Period</th>
                                            <th>Started</th>
                                            <th>Ended</th>
                                            <th>Duration</th>
                                            <th>Changed By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($jobOrder->statusPeriods as $period)
                                            <tr>
                                                <td>
                                                    <span class="badge rounded-pill {{ $period->status->badgeClass() }}">
                                                        <span class="{{ $period->status->icon() }} me-1"></span>
                                                        {{ $period->status->label() }}
                                                    </span>
                                                </td>
                                                <td>{{ $period->started_at?->format('M d, Y h:i A') ?? 'N/A' }}</td>
                                                <td>
                                                    @if ($period->ended_at)
                                                        {{ $period->ended_at->format('M d, Y h:i A') }}
                                                    @else
                                                        <span class="badge badge-subtle-warning text-warning">Still counting</span>
                                                    @endif
                                                </td>
                                                <td class="fw-semibold">{{ $period->duration_label }}</td>
                                                <td>{{ $period->changedBy?->name ?? 'System' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-600 py-3">
                                                    No downtime periods are available for this legacy record.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="jo-card mb-3">
                        <div class="jo-card-header">
                            <div class="d-flex align-items-center gap-2">
                                <span class="jo-section-icon">
                                    <span class="fas fa-user-gear"></span>
                                </span>
                                <h5 class="jo-card-title">Repair Completion Details</h5>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="jo-stat h-100">
                                        <div class="jo-label">Mechanic(s) Who Fixed the Unit</div>

                                        @forelse ($jobOrder->mechanic_names_list as $mechanicName)
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <span class="fas fa-user-check text-success"></span>
                                                <span class="jo-value">{{ $mechanicName }}</span>
                                            </div>
                                        @empty
                                            <div class="text-600">Not assigned</div>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="jo-stat h-100">
                                        <div class="jo-label">Type of Repair Done</div>

                                        <div class="d-flex flex-wrap gap-2">
                                            @forelse ($jobOrder->repair_type_enums as $repairType)
                                                <span class="badge rounded-pill {{ $repairType->badgeClass() }}">
                                                    <span class="{{ $repairType->icon() }} me-1"></span>
                                                    {{ $repairType->label() }}
                                                </span>
                                            @empty
                                                <span class="text-600">Not encoded</span>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="jo-card mb-3">
                        <div class="jo-card-header">
                            <div class="d-flex align-items-center gap-2">
                                <span class="jo-section-icon">
                                    <span class="fas fa-clipboard-check"></span>
                                </span>
                                <h5 class="jo-card-title">Description of Work</h5>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            <div class="jo-description-box">
                                {{ $jobOrder->description_of_work }}
                            </div>
                        </div>
                    </div>

                    <div class="jo-card mb-3">
                        <div class="jo-card-header">
                            <div class="d-flex align-items-center gap-2">
                                <span class="jo-section-icon">
                                    <span class="fas fa-gauge-high"></span>
                                </span>
                                <h5 class="jo-card-title">Odometer Analysis</h5>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="jo-stat">
                                        <div class="jo-label">Current Reading</div>
                                        <h4 class="jo-value mb-0">
                                            {{ $jobOrder->odometer_reading !== null ? number_format($jobOrder->odometer_reading) . ' km' : 'Not encoded' }}
                                        </h4>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="jo-stat">
                                        <div class="jo-label">Previous Reading</div>
                                        <h4 class="jo-value mb-0">
                                            {{ $jobOrder->last_odometer_reading !== null ? number_format($jobOrder->last_odometer_reading) . ' km' : 'No previous record' }}
                                        </h4>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="jo-stat">
                                        <div class="jo-label">Difference</div>
                                        <h4
                                            class="mb-0 {{ $jobOrder->is_odometer_lower_than_last ? 'text-danger' : 'text-success' }}">
                                            {{ $jobOrder->odometer_difference !== null ? number_format($jobOrder->odometer_difference) . ' km' : 'N/A' }}
                                        </h4>
                                    </div>
                                </div>
                            </div>

                            @if ($jobOrder->is_odometer_lower_than_last)
                                <div class="alert alert-subtle-danger mt-3 mb-0">
                                    <span class="fas fa-triangle-exclamation me-1"></span>
                                    Current odometer reading is lower than the previous reading. Verify the encoded value
                                    before using this record for analytics.
                                </div>
                            @else
                                <div class="alert alert-subtle-info mt-3 mb-0">
                                    <span class="fas fa-circle-info me-1"></span>
                                    {{ $jobOrder->odometer_comparison_label }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="jo-card">
                        <div class="jo-card-header">
                            <div class="d-flex align-items-center gap-2">
                                <span class="jo-section-icon">
                                    <span class="fas fa-clock-rotate-left"></span>
                                </span>
                                <h5 class="jo-card-title">Update History</h5>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            @forelse ($jobOrder->histories as $history)
                                <div class="jo-history-item">
                                    <div class="d-flex flex-wrap justify-content-between gap-2">
                                        <div>
                                            <div class="fw-bold text-800">
                                                {{ $history->action }}
                                            </div>

                                            <div class="jo-history-meta">
                                                {{ $history->created_at?->format('M d, Y h:i A') }}
                                                by
                                                {{ $history->user?->name ?? 'System' }}
                                            </div>
                                        </div>

                                        @if ($history->old_value || $history->new_value)
                                            <div>
                                                @if ($history->old_value)
                                                    <span class="badge badge-subtle-secondary text-secondary">
                                                        {{ $history->old_value }}
                                                    </span>
                                                @endif

                                                <span class="mx-1 text-600">→</span>

                                                @if ($history->new_value)
                                                    <span class="badge badge-subtle-primary text-primary">
                                                        {{ $history->new_value }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    @if ($history->remarks)
                                        <div class="alert alert-subtle-info mt-2 mb-0 py-2">
                                            <div class="fs-11 text-600 mb-1">Remarks</div>
                                            <div style="white-space: pre-line;">{{ $history->remarks }}</div>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="jo-empty text-center text-600 py-4">
                                    <span class="fas fa-clock-rotate-left fa-2x text-300 mb-2"></span>
                                    <div>No update history available.</div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="jo-card mb-3">
                        <div class="jo-card-header">
                            <div class="d-flex align-items-center gap-2">
                                <span class="jo-section-icon">
                                    <span class="fas fa-route"></span>
                                </span>
                                <h5 class="jo-card-title">Maintenance Workflow</h5>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            <div class="jo-workflow">
                                @foreach (\App\Enums\JobOrderStatus::cases() as $status)
                                    <div
                                        class="jo-workflow-item {{ $jobOrder->status?->value === $status->value ? 'active' : '' }}">
                                        <span class="jo-workflow-dot"></span>

                                        <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                            <span class="badge rounded-pill {{ $status->badgeClass() }}">
                                                <span class="{{ $status->icon() }} me-1"></span>
                                                {{ $status->label() }}
                                            </span>

                                            @if ($jobOrder->status?->value === $status->value)
                                                <span class="badge badge-subtle-primary text-primary">Current</span>
                                            @endif
                                        </div>

                                        <div class="fs-11 text-600">
                                            {{ $status->description() }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="jo-card mb-3">
                        <div class="jo-card-header">
                            <div class="d-flex align-items-center gap-2">
                                <span class="jo-section-icon">
                                    <span class="fas fa-bus"></span>
                                </span>
                                <h5 class="jo-card-title">Vehicle Information</h5>
                            </div>
                        </div>

                        <div class="card-body px-3 py-2">
                            <div class="jo-side-row">
                                <div class="jo-label">Bus No.</div>
                                <div class="jo-value">
                                    {{ $jobOrder->bus?->bus_no ?? ($jobOrder->bus_no_snapshot ?? 'N/A') }}
                                </div>
                            </div>

                            <div class="jo-side-row">
                                <div class="jo-label">Plate No.</div>
                                <div class="jo-value">
                                    {{ $jobOrder->bus?->plate_no ?? ($jobOrder->plate_no_snapshot ?? 'N/A') }}
                                </div>
                            </div>

                            <div class="jo-side-row">
                                <div class="jo-label">Company</div>
                                <div class="jo-value">
                                    {{ $jobOrder->bus?->company ?? ($jobOrder->company_snapshot ?? 'N/A') }}
                                </div>
                            </div>

                            <div class="jo-side-row">
                                <div class="jo-label">Garage</div>
                                <div class="jo-value">
                                    {{ $jobOrder->bus?->garage ?? ($jobOrder->garage_snapshot ?? 'N/A') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="jo-card">
                        <div class="jo-card-header">
                            <div class="d-flex align-items-center gap-2">
                                <span class="jo-section-icon">
                                    <span class="fas fa-user-gear"></span>
                                </span>
                                <h5 class="jo-card-title">Record Information</h5>
                            </div>
                        </div>

                        <div class="card-body px-3 py-2">
                            <div class="jo-side-row">
                                <div class="jo-label">Created By</div>
                                <div class="jo-value">
                                    {{ $jobOrder->creator?->name ?? 'System' }}
                                </div>
                            </div>

                            <div class="jo-side-row">
                                <div class="jo-label">Current Status</div>
                                <span class="badge rounded-pill {{ $jobOrder->status_badge_class }}">
                                    <span class="{{ $jobOrder->status_icon }} me-1"></span>
                                    {{ $jobOrder->status_label }}
                                </span>
                            </div>

                            <div class="jo-side-row">
                                <div class="jo-label">Mechanic(s)</div>
                                <div class="jo-value">{{ $jobOrder->mechanic_names_label }}</div>
                            </div>

                            <div class="jo-side-row">
                                <div class="jo-label">Repair Type(s)</div>
                                <div class="jo-value">{{ $jobOrder->repair_types_label }}</div>
                            </div>

                            <div class="jo-side-row">
                                <div class="jo-label">Created At</div>
                                <div class="jo-value">
                                    {{ $jobOrder->created_at->format('M d, Y h:i A') }}
                                </div>
                            </div>

                            <div class="jo-side-row">
                                <div class="jo-label">Last Updated</div>
                                <div class="jo-value">
                                    {{ $jobOrder->updated_at?->format('M d, Y h:i A') ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
