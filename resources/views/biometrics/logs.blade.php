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
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-700 mb-1">Total Logs</h6>
                                    <h3 class="text-900" id="total-logs">{{ \App\Models\MirasolBiometricsLog::count() }}</h3>
                                </div>
                                <div class="icon icon-shape icon-sm rounded-circle bg-primary text-white">
                                    <i class="fas fa-list-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-700 mb-1">Today</h6>
                                    <h3 class="text-900" id="today-logs">
                                        {{ \App\Models\MirasolBiometricsLog::whereDate('check_time', now())->count() }}</h3>
                                </div>
                                <div class="icon icon-shape icon-sm rounded-circle bg-success text-white">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-700 mb-1">Devices</h6>
                                    <h3 class="text-900" id="device-count">
                                        {{ \App\Models\MirasolBiometricsLog::distinct('device_sn')->count('device_sn') }}
                                    </h3>
                                </div>
                                <div class="icon icon-shape icon-sm rounded-circle bg-warning text-white">
                                    <i class="fas fa-microchip"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-700 mb-1">Unique Employees</h6>
                                    <h3 class="text-900" id="unique-employees">
                                        {{ \App\Models\MirasolBiometricsLog::distinct('employee_no')->count('employee_no') }}
                                    </h3>
                                </div>
                                <div class="icon icon-shape icon-sm rounded-circle bg-info text-white">
                                    <i class="fas fa-users"></i>
                                </div>
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
                        @csrf
                        <div class="col-md-3">
                            <label class="form-label">Employee Name / No</label>
                            <input type="text" name="q" class="form-control form-control-sm"
                                placeholder="Search..." value="{{ request('q') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="from" class="form-control form-control-sm"
                                value="{{ old('from', now()->toDateString()) }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="to" class="form-control form-control-sm"
                                value="{{ old('to', now()->toDateString()) }}">
                        </div>

                        <div class="col-md-3 d-flex gap-2">
                            <button class="btn btn-primary btn-sm flex-grow-1" type="submit"><i
                                    class="fas fa-search me-1"></i> Search</button>
                            <a href="{{ route('mirasol-logs.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Logs Table --}}
            <div class="card shadow-sm">
                <div class="card-header bg-body-tertiary border-bottom border-200">
                    <h6 class="mb-0"><i class="fas fa-clock text-primary me-2"></i>Recent Biometric Logs</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0" id="biometrics-table">
                        <thead class="bg-light text-900 small text-uppercase">
                            <tr>
                                <th>#</th>
                                <th>Employee Name</th>
                                <th>Employee No</th>
                                <th>Check Time</th>
                                <th>Device</th>
                                <th>State</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>{{ $log->employee_name }}</td>
                                    <td>{{ $log->employee_no }}</td>
                                    <td>{{ $log->check_time->format('F d, Y (l) h:i A') }}</td>
                                    <td>{{ $log->device_name }} ({{ $log->device_sn }})</td>
                                    <td>
                                        <span
                                            class="badge {{ $log->state == 'checkin' ? 'bg-success' : 'bg-warning' }}">{{ ucfirst($log->state) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-3">
                                        No logs found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Polling for live updates every 5 seconds
        function fetchLatestLogs() {
            fetch('{{ route('biometrics.latest') }}')
                .then(res => res.json())
                .then(data => {
                    const tbody = document.querySelector('#biometrics-table tbody');
                    tbody.innerHTML = '';
                    data.logs.forEach(log => {
                        tbody.innerHTML += `
                        <tr>
                            <td>${log.id}</td>
                            <td>${log.employee_name}</td>
                            <td>${log.employee_no}</td>
                            <td>${log.check_time}</td>
                            <td>${log.device_name} (${log.device_sn})</td>
                            <td>
                                <span class="badge ${log.state === 'checkin' ? 'bg-success' : 'bg-warning'}">
                                    ${log.state ? log.state.charAt(0).toUpperCase() + log.state.slice(1) : 'Unknown'}
                                </span>
                            </td>
                        </tr>
                    `;
                    });

                    // Update summary counts (optional, can create API for dynamic counts)
                    document.getElementById('total-logs').textContent = data.logs.length;
                });
        }

        setInterval(fetchLatestLogs, 5000);
    </script>
@endpush
