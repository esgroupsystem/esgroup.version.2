@extends('layouts.app')

@section('title', 'HR Data Report | Chairman Dashboard')

@section('content')

    <div class="container-fluid py-4 chairman-hr-report">

        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                            <div>
                                <p class="text-uppercase text-muted small fw-semibold mb-1">
                                    Chairman / CEO Report
                                </p>
                                <h3 class="fw-bold mb-1">
                                    HR Data Overview
                                </h3>
                                <p class="text-muted mb-0">
                                    Read-only summary of employee status, department count, leave records, violations, and
                                    holidays.
                                </p>
                            </div>

                            <form method="GET" action="{{ route('chairman.hr-data.index') }}"
                                class="d-flex align-items-end gap-2">
                                <div>
                                    <label for="year" class="form-label small text-muted mb-1">
                                        Report Year
                                    </label>
                                    <select name="year" id="year" class="form-select form-select-sm"
                                        onchange="this.form.submit()">
                                        @for ($reportYear = now()->year + 1; $reportYear >= now()->year - 5; $reportYear--)
                                            <option value="{{ $reportYear }}" @selected($year === $reportYear)>
                                                {{ $reportYear }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('chairman.hr.partials.summary-cards')

        <div class="row g-3 mb-4">
            <div class="col-xl-7">
                @include('chairman.hr.partials.status-chart')
            </div>

            <div class="col-xl-5">
                @include('chairman.hr.partials.active-chart')
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-7">
                @include('chairman.hr.partials.department-summary')
            </div>

            <div class="col-xl-5">
                @include('chairman.hr.partials.holiday-calendar')
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12">
                @include('chairman.hr.partials.leave-report')
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12">
                @include('chairman.hr.partials.employee-history')
            </div>
        </div>

    </div>

@endsection

@push('styles')
    <style>
        .chairman-hr-report {
            background: #f8f9fc;
        }

        .chairman-hr-report .card {
            border-radius: 1rem;
        }

        .report-stat-card {
            min-height: 132px;
        }

        .report-stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .report-table th {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #6c757d;
            background: #f8f9fa;
            white-space: nowrap;
        }

        .report-table td {
            vertical-align: middle;
            font-size: .86rem;
        }

        .chart-box {
            height: 310px;
            position: relative;
        }

        .compact-badge {
            font-size: .7rem;
            padding: .35rem .55rem;
            border-radius: 999px;
        }

        .holiday-month {
            border-left: 3px solid var(--falcon-primary, #2c7be5);
            padding-left: .85rem;
        }

        .holiday-item {
            padding: .65rem .75rem;
            border-radius: .75rem;
            background: #f8f9fa;
        }

        .employee-history-box {
            max-height: 520px;
            overflow-y: auto;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusLabels = @json($employeeStatusSummary->pluck('label'));
            const statusTotals = @json($employeeStatusSummary->pluck('total'));

            const activeLabels = @json($activeVsOtherSummary->pluck('label'));
            const activeTotals = @json($activeVsOtherSummary->pluck('total'));

            const statusCanvas = document.getElementById('employeeStatusChart');
            const activeCanvas = document.getElementById('activeVsOtherChart');

            if (statusCanvas) {
                new Chart(statusCanvas, {
                    type: 'bar',
                    data: {
                        labels: statusLabels,
                        datasets: [{
                            label: 'Employees',
                            data: statusTotals,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }

            if (activeCanvas) {
                new Chart(activeCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: activeLabels,
                        datasets: [{
                            data: activeTotals,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush
