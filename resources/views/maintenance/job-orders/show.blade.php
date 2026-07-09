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
            .jo-show .jo-hero {
                background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
                border: 1px solid rgba(216, 226, 239, .8);
            }

            .jo-show .jo-icon-box {
                width: 50px;
                height: 50px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 16px;
            }

            .jo-show .jo-info-row {
                padding: .85rem 0;
                border-bottom: 1px solid #edf2f9;
            }

            .jo-show .jo-info-row:last-child {
                border-bottom: 0;
            }

            .jo-show .jo-label {
                font-size: .72rem;
                color: #748194;
                text-transform: uppercase;
                letter-spacing: .04em;
                margin-bottom: .2rem;
            }

            .jo-show .jo-value {
                font-weight: 700;
                color: #344050;
            }

            .jo-show .jo-work-box {
                background: #f8fafd;
                border: 1px solid #edf2f9;
                border-radius: .75rem;
                min-height: 180px;
            }

            .jo-show .jo-metric-card {
                border: 1px solid #edf2f9;
                border-radius: .75rem;
            }

            .jo-show .jo-timeline {
                position: relative;
                padding-left: 1.4rem;
            }

            .jo-show .jo-timeline::before {
                content: "";
                position: absolute;
                left: .45rem;
                top: .35rem;
                bottom: .35rem;
                width: 2px;
                background: #edf2f9;
            }

            .jo-show .jo-timeline-item {
                position: relative;
                padding-bottom: 1rem;
            }

            .jo-show .jo-timeline-item:last-child {
                padding-bottom: 0;
            }

            .jo-show .jo-timeline-dot {
                position: absolute;
                left: -1.33rem;
                top: .15rem;
                width: .7rem;
                height: .7rem;
                border-radius: 50%;
                background: #d8e2ef;
                border: 2px solid #fff;
                box-shadow: 0 0 0 1px #d8e2ef;
            }

            .jo-show .jo-timeline-item.active .jo-timeline-dot {
                background: var(--falcon-primary);
                box-shadow: 0 0 0 1px var(--falcon-primary);
            }
        </style>

        <div class="content jo-show">
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
                                <div class="jo-icon-box {{ $jobOrder->status_badge_class }}">
                                    <span class="{{ $jobOrder->status_icon }} fs-5"></span>
                                </div>

                                <div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <h4 class="mb-0">{{ $jobOrder->job_order_no }}</h4>
                                        <span class="badge rounded-pill {{ $jobOrder->status_badge_class }}">
                                            <span class="{{ $jobOrder->status_icon }} me-1"></span>
                                            {{ $jobOrder->status_label }}
                                        </span>
                                    </div>

                                    <div class="text-600">
                                        {{ $jobOrder->status_description }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 text-lg-end">
                            <div class="btn-group">
                                <a href="{{ route('maintenance.job-orders.index') }}" class="btn btn-falcon-default">
                                    <span class="fas fa-arrow-left me-1"></span>
                                    Back
                                </a>

                                @can('job-orders.update-number')
                                    <a href="{{ route('maintenance.job-orders.edit-number', $jobOrder) }}"
                                        class="btn btn-falcon-primary">
                                        <span class="fas fa-hashtag me-1"></span>
                                        Edit JO-NO
                                    </a>
                                @endcan

                                @can('job-orders.update-status')
                                    <a href="{{ route('maintenance.job-orders.edit-status', $jobOrder) }}"
                                        class="btn btn-warning">
                                        <span class="fas fa-pen-to-square me-1"></span>
                                        Edit Status
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-xl-8">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0">
                                <span class="fas fa-file-lines me-2 text-primary"></span>
                                Job Order Sheet
                            </h5>
                        </div>

                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="jo-metric-card p-3 h-100">
                                        <div class="jo-label">Bus No.</div>
                                        <h4 class="mb-0">
                                            <span class="fas fa-bus text-primary me-1"></span>
                                            {{ $jobOrder->bus?->bus_no ?? ($jobOrder->bus_no_snapshot ?? 'N/A') }}
                                        </h4>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="jo-metric-card p-3 h-100">
                                        <div class="jo-label">Plate No.</div>
                                        <h4 class="mb-0">
                                            {{ $jobOrder->bus?->plate_no ?? ($jobOrder->plate_no_snapshot ?? 'N/A') }}
                                        </h4>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="jo-metric-card p-3 h-100">
                                        <div class="jo-label">Requester / Staff</div>
                                        <h5 class="mb-0">
                                            {{ $jobOrder->full_name ?: 'Not specified' }}
                                        </h5>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="jo-metric-card p-3 h-100">
                                        <div class="jo-label">Created Date</div>
                                        <h5 class="mb-0">
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

                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0">
                                <span class="fas fa-clipboard-check me-2 text-primary"></span>
                                Description of Work
                            </h5>
                        </div>

                        <div class="card-body">
                            <div class="jo-work-box p-3">
                                <div style="white-space: pre-line;">{{ $jobOrder->description_of_work }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0">
                                <span class="fas fa-gauge-high me-2 text-primary"></span>
                                Odometer Comparison
                            </h5>
                        </div>

                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="jo-metric-card p-3 h-100">
                                        <div class="jo-label">Current Reading</div>
                                        <h4 class="mb-0">
                                            {{ $jobOrder->odometer_reading !== null ? number_format($jobOrder->odometer_reading) . ' km' : 'Not encoded' }}
                                        </h4>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="jo-metric-card p-3 h-100">
                                        <div class="jo-label">Previous Reading</div>
                                        <h4 class="mb-0">
                                            {{ $jobOrder->last_odometer_reading !== null ? number_format($jobOrder->last_odometer_reading) . ' km' : 'No previous record' }}
                                        </h4>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="jo-metric-card p-3 h-100">
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
                </div>

                <div class="col-xl-4">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0">
                                <span class="fas fa-route me-2 text-primary"></span>
                                Maintenance Workflow
                            </h5>
                        </div>

                        <div class="card-body">
                            <div class="jo-timeline">
                                @foreach (\App\Enums\JobOrderStatus::cases() as $status)
                                    <div
                                        class="jo-timeline-item {{ $jobOrder->status?->value === $status->value ? 'active' : '' }}">
                                        <span class="jo-timeline-dot"></span>

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

                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0">
                                <span class="fas fa-bus me-2 text-primary"></span>
                                Bus Information
                            </h5>
                        </div>

                        <div class="card-body">
                            <div class="jo-info-row">
                                <div class="jo-label">Bus No.</div>
                                <div class="jo-value">
                                    {{ $jobOrder->bus?->bus_no ?? ($jobOrder->bus_no_snapshot ?? 'N/A') }}
                                </div>
                            </div>

                            <div class="jo-info-row">
                                <div class="jo-label">Plate No.</div>
                                <div class="jo-value">
                                    {{ $jobOrder->bus?->plate_no ?? ($jobOrder->plate_no_snapshot ?? 'N/A') }}
                                </div>
                            </div>

                            <div class="jo-info-row">
                                <div class="jo-label">Company</div>
                                <div class="jo-value">
                                    {{ $jobOrder->bus?->company ?? ($jobOrder->company_snapshot ?? 'N/A') }}
                                </div>
                            </div>

                            <div class="jo-info-row">
                                <div class="jo-label">Garage</div>
                                <div class="jo-value">
                                    {{ $jobOrder->bus?->garage ?? ($jobOrder->garage_snapshot ?? 'N/A') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0">
                                <span class="fas fa-user-gear me-2 text-primary"></span>
                                Record Information
                            </h5>
                        </div>

                        <div class="card-body">
                            <div class="jo-info-row">
                                <div class="jo-label">Created By</div>
                                <div class="jo-value">{{ $jobOrder->creator?->name ?? 'System' }}</div>
                            </div>

                            <div class="jo-info-row">
                                <div class="jo-label">Current Status</div>
                                <span class="badge rounded-pill {{ $jobOrder->status_badge_class }}">
                                    <span class="{{ $jobOrder->status_icon }} me-1"></span>
                                    {{ $jobOrder->status_label }}
                                </span>
                            </div>

                            <div class="jo-info-row">
                                <div class="jo-label">Created At</div>
                                <div class="jo-value">
                                    {{ $jobOrder->created_at->format('M d, Y h:i A') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
