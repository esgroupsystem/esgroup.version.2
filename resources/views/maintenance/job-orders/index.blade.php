@extends('layouts.app')

@section('title', 'Maintenance Job Orders')

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
            .jo-page .jo-hero {
                background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
                border: 1px solid rgba(216, 226, 239, .8);
            }

            .jo-page .jo-icon-box {
                width: 46px;
                height: 46px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 14px;
            }

            .jo-page .jo-status-strip {
                border: 1px solid rgba(216, 226, 239, .85);
                transition: all .15s ease-in-out;
            }

            .jo-page .jo-status-strip:hover {
                transform: translateY(-1px);
                box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .06);
            }

            .jo-page .jo-status-strip.active {
                border-color: var(--falcon-primary);
                box-shadow: inset 0 0 0 1px var(--falcon-primary);
            }

            .jo-page .jo-status-indicator {
                width: 8px;
                min-height: 100%;
                border-radius: .5rem 0 0 .5rem;
            }

            .jo-page .jo-record-title {
                font-size: .9rem;
                font-weight: 700;
            }

            .jo-page .jo-muted {
                color: #748194;
            }

            .jo-page .jo-table thead th {
                font-size: .72rem;
                text-transform: uppercase;
                letter-spacing: .04em;
                color: #748194;
                background-color: #f8fafd;
                border-bottom: 1px solid #edf2f9;
            }

            .jo-page .jo-table tbody td {
                border-bottom: 1px solid #edf2f9;
                vertical-align: middle;
            }

            .jo-page .jo-work-preview {
                max-width: 420px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .jo-page .jo-filter-card {
                border: 1px solid rgba(216, 226, 239, .85);
            }
        </style>

        <div class="content jo-page">
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
                <div class="card-body py-3">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg-8">
                            <div class="d-flex align-items-center gap-3">
                                <div class="jo-icon-box bg-primary-subtle text-primary">
                                    <span class="fas fa-screwdriver-wrench fs-5"></span>
                                </div>

                                <div>
                                    <h4 class="mb-1">Maintenance Job Orders</h4>
                                    <div class="text-600">
                                        Centralized maintenance tracking for bus repairs, odometer checks, and repair
                                        status.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 text-lg-end">
                            @can('job-orders.create')
                                <a href="{{ route('maintenance.job-orders.create') }}" class="btn btn-primary">
                                    <span class="fas fa-plus me-1"></span>
                                    Create Job Order
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                @foreach ($statusCards as $statusCard)
                    @php
                        $isActiveStatus = request('status') === $statusCard['value'];

                        $statusFilterUrl = route(
                            'maintenance.job-orders.index',
                            array_filter(
                                [
                                    'status' => $statusCard['value'],
                                    'search' => request('search'),
                                    'bus_id' => request('bus_id'),
                                    'date_filter' => request('date_filter'),
                                    'filter_date' => request('filter_date'),
                                    'filter_month' => request('filter_month'),
                                    'filter_year' => request('filter_year'),
                                ],
                                fn($value) => filled($value),
                            ),
                        );
                    @endphp

                    <div class="col-md-6 col-xl-3">
                        <a href="{{ $statusFilterUrl }}" class="text-decoration-none">
                            <div
                                class="card jo-status-strip {{ $isActiveStatus ? 'active' : '' }} border-0 shadow-sm h-100 overflow-hidden">
                                <div class="card-body p-0">
                                    <div class="d-flex">
                                        <div class="jo-status-indicator {{ $statusCard['badge_class'] }}"></div>

                                        <div class="p-3 flex-1 w-100">
                                            <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                                                <div>
                                                    <div class="text-600 fs-11 mb-1">Status</div>
                                                    <span class="badge rounded-pill {{ $statusCard['badge_class'] }}">
                                                        <span class="{{ $statusCard['icon'] }} me-1"></span>
                                                        {{ $statusCard['label'] }}
                                                    </span>
                                                </div>

                                                <h3 class="mb-0">{{ number_format($statusCard['count']) }}</h3>
                                            </div>

                                            <div class="fs-11 jo-muted">
                                                {{ $statusCard['description'] }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            <div class="card jo-filter-card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('maintenance.job-orders.index') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-3 col-lg-6">
                                <label class="form-label fw-semibold">Search Job Order</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <span class="fas fa-search text-600"></span>
                                    </span>

                                    <input type="text" name="search" value="{{ request('search') }}"
                                        class="form-control" placeholder="JO no., bus no., plate no., requester, work">
                                </div>
                            </div>

                            <div class="col-xl-2 col-lg-6">
                                <label class="form-label fw-semibold">Bus</label>
                                <select name="bus_id" class="form-select">
                                    <option value="">All buses</option>

                                    @foreach ($buses as $bus)
                                        <option value="{{ $bus->id }}" @selected((string) request('bus_id') === (string) $bus->id)>
                                            {{ $bus->bus_no }} — {{ $bus->plate_no ?? 'No Plate' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-xl-2 col-lg-6">
                                <label class="form-label fw-semibold">Maintenance Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All statuses</option>

                                    @foreach ($statuses as $value => $label)
                                        <option value="{{ $value }}" @selected(request('status') === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-xl-2 col-lg-6">
                                <label class="form-label fw-semibold">Date Filter</label>
                                <select name="date_filter" id="date_filter" class="form-select">
                                    <option value="">All dates</option>
                                    <option value="day" @selected(request('date_filter') === 'day')>By Day</option>
                                    <option value="month" @selected(request('date_filter') === 'month')>By Month</option>
                                    <option value="year" @selected(request('date_filter') === 'year')>By Year</option>
                                </select>
                            </div>

                            <div class="col-xl-2 col-lg-6 date-filter-input" id="filter_day_wrapper">
                                <label class="form-label fw-semibold">Day</label>
                                <input type="date" name="filter_date" value="{{ request('filter_date') }}"
                                    class="form-control">
                            </div>

                            <div class="col-xl-2 col-lg-6 date-filter-input d-none" id="filter_month_wrapper">
                                <label class="form-label fw-semibold">Month</label>
                                <input type="month" name="filter_month" value="{{ request('filter_month') }}"
                                    class="form-control">
                            </div>

                            <div class="col-xl-2 col-lg-6 date-filter-input d-none" id="filter_year_wrapper">
                                <label class="form-label fw-semibold">Year</label>
                                <input type="number" name="filter_year" value="{{ request('filter_year') }}"
                                    class="form-control" min="2000" max="2100" placeholder="2026">
                            </div>

                            <div class="col-xl-1 col-lg-6">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-fill">
                                        <span class="fas fa-filter"></span>
                                    </button>

                                    <a href="{{ route('maintenance.job-orders.index') }}" class="btn btn-falcon-default">
                                        <span class="fas fa-rotate-right"></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <h5 class="mb-0">
                                <span class="fas fa-list-check me-2 text-primary"></span>
                                Maintenance Records
                            </h5>
                        </div>

                        <div class="col-auto">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="badge badge-subtle-secondary">
                                    {{ number_format($jobOrders->total()) }} records
                                </span>

                                <div class="btn-group">
                                    <a href="{{ route('maintenance.job-orders.export', array_merge(request()->query(), ['export_type' => 'csv'])) }}"
                                        class="btn btn-falcon-success btn-sm">
                                        <span class="fas fa-file-csv me-1"></span>
                                        CSV
                                    </a>

                                    <a href="{{ route('maintenance.job-orders.export', array_merge(request()->query(), ['export_type' => 'xls'])) }}"
                                        class="btn btn-falcon-primary btn-sm">
                                        <span class="fas fa-file-excel me-1"></span>
                                        Excel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive scrollbar">
                        <table class="table table-hover jo-table mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Job Order</th>
                                    <th>Vehicle</th>
                                    <th>Requester</th>
                                    <th>Work Required</th>
                                    <th>Odometer</th>
                                    <th>Downtime</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($jobOrders as $jobOrder)
                                    <tr>
                                        <td class="ps-3">
                                            <a href="{{ route('maintenance.job-orders.show', $jobOrder) }}"
                                                class="jo-record-title text-primary">
                                                {{ $jobOrder->job_order_no }}
                                            </a>

                                            <div class="fs-11 jo-muted">
                                                Created by {{ $jobOrder->creator?->name ?? 'System' }}
                                            </div>
                                        </td>

                                        <td>
                                            <div class="fw-semibold">
                                                <span class="fas fa-bus text-primary me-1"></span>
                                                {{ $jobOrder->bus?->bus_no ?? ($jobOrder->bus_no_snapshot ?? 'N/A') }}
                                            </div>

                                            <div class="fs-11 jo-muted">
                                                Plate:
                                                {{ $jobOrder->bus?->plate_no ?? ($jobOrder->plate_no_snapshot ?? 'N/A') }}
                                            </div>

                                            <div class="fs-11 jo-muted">
                                                Company:
                                                {{ $jobOrder->bus?->company ?? ($jobOrder->company_snapshot ?? 'N/A') }}
                                            </div>

                                            <div class="fs-11 jo-muted">
                                                Garage:
                                                {{ $jobOrder->bus?->garage ?? ($jobOrder->garage_snapshot ?? 'N/A') }}
                                            </div>
                                        </td>

                                        <td>
                                            <div class="fw-semibold">
                                                {{ $jobOrder->full_name ?: 'Not specified' }}
                                            </div>
                                        </td>

                                        <td>
                                            <div class="jo-work-preview" title="{{ $jobOrder->description_of_work }}">
                                                {{ $jobOrder->description_of_work }}
                                            </div>

                                            @if ($jobOrder->repair_type_enums->isNotEmpty())
                                                <div class="d-flex flex-wrap gap-1 mt-2">
                                                    @foreach ($jobOrder->repair_type_enums as $repairType)
                                                        <span class="badge rounded-pill {{ $repairType->badgeClass() }}">
                                                            {{ $repairType->label() }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif

                                            @if ($jobOrder->mechanic_names_list !== [])
                                                <div class="fs-11 jo-muted mt-2">
                                                    <span class="fas fa-user-gear me-1"></span>
                                                    {{ $jobOrder->mechanic_names_label }}
                                                </div>
                                            @endif
                                        </td>

                                        <td>
                                            @if ($jobOrder->odometer_reading !== null)
                                                <div class="fw-semibold">
                                                    {{ number_format($jobOrder->odometer_reading) }} km
                                                </div>

                                                <div
                                                    class="fs-11 {{ $jobOrder->is_odometer_lower_than_last ? 'text-danger' : 'jo-muted' }}">
                                                    {{ $jobOrder->odometer_comparison_label }}
                                                </div>
                                            @else
                                                <span class="badge badge-subtle-secondary text-secondary">
                                                    No reading
                                                </span>
                                            @endif
                                        </td>

                                        <td style="min-width: 170px;">
                                            <div class="fw-bold text-800">
                                                {{ $jobOrder->total_downtime_label }}
                                            </div>

                                            <div class="fs-11 {{ $jobOrder->is_downtime_running ? 'text-warning' : 'text-success' }} mt-1">
                                                <span class="fas {{ $jobOrder->is_downtime_running ? 'fa-stopwatch' : 'fa-circle-check' }} me-1"></span>
                                                {{ $jobOrder->is_downtime_running ? 'Counting' : 'Stopped' }}
                                            </div>
                                        </td>

                                        <td style="min-width: 190px;">
                                            <span class="badge rounded-pill {{ $jobOrder->status_badge_class }}">
                                                <span class="{{ $jobOrder->status_icon }} me-1"></span>
                                                {{ $jobOrder->status_label }}
                                            </span>

                                            <div class="fs-11 jo-muted mt-1">
                                                {{ $jobOrder->status_description }}
                                            </div>
                                        </td>

                                        <td>
                                            <div class="fw-semibold">{{ $jobOrder->created_at->format('M d, Y') }}</div>
                                            <div class="fs-11 jo-muted">{{ $jobOrder->created_at->format('h:i A') }}</div>
                                        </td>

                                        <td class="text-end pe-3">
                                            <div class="btn-group">
                                                <a href="{{ route('maintenance.job-orders.show', $jobOrder) }}"
                                                    class="btn btn-falcon-primary btn-sm" title="View">
                                                    <span class="fas fa-eye"></span>
                                                </a>

                                                @can('job-orders.update-status')
                                                    <a href="{{ route('maintenance.job-orders.edit-status', $jobOrder) }}"
                                                        class="btn btn-falcon-warning btn-sm" title="Edit status">
                                                        <span class="fas fa-pen-to-square"></span>
                                                    </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-5">
                                            <div class="mb-3">
                                                <span class="fas fa-clipboard-list fa-3x text-300"></span>
                                            </div>

                                            <h5 class="mb-1">No maintenance job orders found</h5>
                                            <p class="text-600 mb-3">
                                                Create a maintenance record to start tracking bus repair activity.
                                            </p>

                                            @can('job-orders.create')
                                                <a href="{{ route('maintenance.job-orders.create') }}"
                                                    class="btn btn-primary">
                                                    <span class="fas fa-plus me-1"></span>
                                                    Create Job Order
                                                </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($jobOrders->hasPages())
                    <div class="card-footer bg-white">
                        {{ $jobOrders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dateFilter = document.getElementById('date_filter');

            const dayWrapper = document.getElementById('filter_day_wrapper');
            const monthWrapper = document.getElementById('filter_month_wrapper');
            const yearWrapper = document.getElementById('filter_year_wrapper');

            function toggleDateInputs() {
                const selectedFilter = dateFilter.value;

                dayWrapper.classList.add('d-none');
                monthWrapper.classList.add('d-none');
                yearWrapper.classList.add('d-none');

                if (selectedFilter === 'day') {
                    dayWrapper.classList.remove('d-none');
                    return;
                }

                if (selectedFilter === 'month') {
                    monthWrapper.classList.remove('d-none');
                    return;
                }

                if (selectedFilter === 'year') {
                    yearWrapper.classList.remove('d-none');
                }
            }

            dateFilter.addEventListener('change', toggleDateInputs);

            toggleDateInputs();
        });
    </script>
@endpush
