@extends('layouts.app')
@section('title', 'Mirasol Biometrics Logs - HR')

@section('content')
    <div class="container" data-layout="container">
        <script>
            (function() {
                const isFluid = JSON.parse(localStorage.getItem('isFluid') || 'false');
                if (!isFluid) return;

                const container = document.querySelector('[data-layout]');
                if (!container) return;

                container.classList.remove('container');
                container.classList.add('container-fluid');
            })();
        </script>

        <div class="content">

            {{-- Summary Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-700 mb-1">Total Logs</h6>
                                <h3 class="text-900" id="total-logs">{{ number_format($totalLogs ?? 0) }}</h3>
                            </div>
                            <div class="icon icon-shape icon-sm rounded-circle bg-primary text-white">
                                <i class="fas fa-list-check"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-700 mb-1">Today</h6>
                                <h3 class="text-900" id="today-logs">{{ number_format($todayLogs ?? 0) }}</h3>
                            </div>
                            <div class="icon icon-shape icon-sm rounded-circle bg-success text-white">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-700 mb-1">Devices</h6>
                                <h3 class="text-900" id="device-count">{{ number_format($deviceCount ?? 0) }}</h3>
                            </div>
                            <div class="icon icon-shape icon-sm rounded-circle bg-warning text-white">
                                <i class="fas fa-microchip"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-700 mb-1">Unique Employees</h6>
                                <h3 class="text-900" id="unique-employees">{{ number_format($uniqueEmployees ?? 0) }}</h3>
                            </div>
                            <div class="icon icon-shape icon-sm rounded-circle bg-info text-white">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Search / Filter Panel --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-body-tertiary border-bottom border-200">
                    <h6 class="mb-0">Search / Filter Logs</h6>
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('mirasol-logs.index') }}" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Employee Name / No</label>
                            <input type="text" name="q" class="form-control form-control-sm"
                                placeholder="Search..." value="{{ request('q') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="from" class="form-control form-control-sm"
                                value="{{ request('from') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="to" class="form-control form-control-sm"
                                value="{{ request('to') }}">
                        </div>

                        <div class="col-md-3 d-flex gap-2">
                            <button class="btn btn-primary btn-sm flex-grow-1" type="submit">
                                <i class="fas fa-search me-1"></i> Search
                            </button>

                            <a href="{{ route('mirasol-logs.index') }}" class="btn btn-outline-secondary btn-sm">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Logs Table --}}
            <div class="card shadow-sm">
                <div class="card-header bg-body-tertiary border-bottom border-200">
                    <h6 class="mb-0">
                        <i class="fas fa-clock text-primary me-2"></i>
                        Recent Biometric Logs
                    </h6>
                </div>

                <div class="table-responsive mb-0">
                    <table class="table table-hover table-sm mb-0 align-middle" id="biometrics-table">
                        <thead class="bg-light text-900 small text-uppercase">
                            <tr>
                                <th class="ps-3" style="width: 70px;">#</th>
                                <th>Employee Name</th>
                                <th>Employee No</th>
                                <th>Check Time</th>
                                <th>Device</th>
                                <th>State</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($logs as $i => $log)
                                <tr>
                                    <td class="ps-3 text-muted">
                                        {{ ($logs->firstItem() ?? 0) + $i }}
                                    </td>

                                    <td class="fw-semibold">
                                        {{ $log->employee_name ?? '—' }}
                                    </td>

                                    <td class="text-muted">
                                        {{ $log->employee_no ?? '—' }}
                                    </td>

                                    <td>
                                        {{ $log->check_time ? $log->check_time->format('F d, Y (l) h:i A') : '—' }}
                                    </td>

                                    <td>
                                        {{ $log->device_name ?? '—' }}

                                        @if (!empty($log->device_sn))
                                            <div class="text-muted fs-11">
                                                {{ $log->device_sn }}
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        @php
                                            $state = strtolower($log->state ?? '');
                                        @endphp

                                        <span class="badge {{ $state === 'checkin' ? 'bg-success' : 'bg-warning' }}">
                                            {{ $log->state ? ucfirst($log->state) : 'Unknown' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="py-4">
                                            <div class="mb-2">
                                                <span class="fas fa-fingerprint fa-2x text-muted"></span>
                                            </div>
                                            <div class="fw-bold">No Logs Found</div>
                                            <div class="text-muted fs-11">
                                                No biometric logs matched your selected filters.
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="card-footer bg-body-tertiary border-top border-200">
                    <div class="d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center">
                        <small class="text-muted">
                            Showing {{ $logs->firstItem() ?? 0 }} to {{ $logs->lastItem() ?? 0 }} of
                            {{ $logs->total() }}
                        </small>

                        <div class="ms-md-auto">
                            {{ $logs->links('pagination.custom') }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
