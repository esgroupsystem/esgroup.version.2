@extends('layouts.app')

@section('title', 'Manual WFH Cutoff Encoding')

@section('content')
    <div class="container-fluid" data-layout="container">
        <div class="content">

            @if (session('success'))
                <div class="alert alert-success border-0 shadow-sm">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ $errors->first() }}
                </div>
            @endif

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                        <div>
                            <h4 class="mb-1">Manual WFH Cutoff Encoding</h4>
                            <p class="text-muted mb-0">
                                Search one employee from biometrics logs, load the whole cutoff, then encode daily Time In /
                                Time Out fast.
                            </p>
                        </div>

                        <span class="badge bg-primary-subtle text-primary fs-10 px-3 py-2">
                            {{ $cutoffLabel }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-body-tertiary border-bottom">
                    <h5 class="mb-0">Step 1: Select Cutoff and Employee</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('manual-biometrics.index') }}" id="filterForm">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Month</label>
                                <select name="cutoff_month" class="form-select">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}"
                                            {{ (int) $cutoffMonth === $m ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Year</label>
                                <select name="cutoff_year" class="form-select">
                                    @for ($y = now('Asia/Manila')->year + 1; $y >= 2024; $y--)
                                        <option value="{{ $y }}"
                                            {{ (int) $cutoffYear === $y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Cutoff Type</label>
                                <select name="cutoff_type" class="form-select">
                                    <option value="first" {{ $cutoffType === 'first' ? 'selected' : '' }}>1st Cutoff(11-25)
                                    </option>
                                    <option value="second" {{ $cutoffType === 'second' ? 'selected' : '' }}>2nd Cutoff(26-10)
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Employee Search</label>
                                <div class="position-relative">
                                    <input type="text" class="form-control" id="employeeSearch"
                                        placeholder="Type employee name / employee no / crosschex id"
                                        value="{{ $selectedEmployee['employee_name'] ?? '' }}">
                                    <div id="employeeResults" class="list-group position-absolute w-100 shadow-sm d-none"
                                        style="z-index: 1050; max-height: 260px; overflow-y: auto;"></div>
                                </div>
                                <input type="hidden" name="crosschex_id" id="crosschexId"
                                    value="{{ $selectedCrosschexId }}">
                            </div>

                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i>Load
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if ($selectedEmployee)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-lg-4">
                                <div class="border rounded-3 p-3 h-100 bg-light">
                                    <small class="text-muted d-block">Employee Name</small>
                                    <div class="fw-semibold fs-9">{{ $selectedEmployee['employee_name'] }}</div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="border rounded-3 p-3 h-100 bg-light">
                                    <small class="text-muted d-block">Employee No</small>
                                    <div class="fw-semibold fs-9">{{ $selectedEmployee['employee_no'] ?: '-' }}</div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="border rounded-3 p-3 h-100 bg-light">
                                    <small class="text-muted d-block">CrossChex ID</small>
                                    <div class="fw-semibold fs-9">{{ $selectedEmployee['crosschex_id'] ?: '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('manual-biometrics.store') }}">
                    @csrf

                    <input type="hidden" name="cutoff_month" value="{{ $cutoffMonth }}">
                    <input type="hidden" name="cutoff_year" value="{{ $cutoffYear }}">
                    <input type="hidden" name="cutoff_type" value="{{ $cutoffType }}">

                    <input type="hidden" name="crosschex_id" value="{{ $selectedEmployee['crosschex_id'] }}">
                    <input type="hidden" name="employee_id" value="{{ $selectedEmployee['employee_id'] }}">
                    <input type="hidden" name="employee_no" value="{{ $selectedEmployee['employee_no'] }}">
                    <input type="hidden" name="employee_name" value="{{ $selectedEmployee['employee_name'] }}">

                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-body-tertiary border-bottom">
                            <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                                <div>
                                    <h5 class="mb-0">Step 2: Encode Whole Cutoff</h5>
                                    <small class="text-muted">{{ $cutoffLabel }}</small>
                                </div>

                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="copyMonSatBtn">
                                        <i class="fas fa-copy me-1"></i>Apply Mon-Sat Only
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="applyAllBtn">
                                        <i class="fas fa-bolt me-1"></i>Apply All Blank Dates
                                    </button>
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-save me-1"></i>Save Employee Logs
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card-body border-bottom bg-light">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Common Time In</label>
                                    <input type="time" class="form-control" id="bulkTimeIn" value="09:00">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Common Time Out</label>
                                    <input type="time" class="form-control" id="bulkTimeOut" value="18:00">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Common Remarks</label>
                                    <input type="text" class="form-control" id="bulkRemarks"
                                        placeholder="Optional remarks">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-dark w-100" id="clearAllBtn">
                                        Clear All
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="bg-body-tertiary">
                                    <tr>
                                        <th style="width: 80px;">#</th>
                                        <th style="min-width: 140px;">Date</th>
                                        <th style="width: 100px;">Day</th>
                                        <th style="width: 150px;">Time In</th>
                                        <th style="width: 150px;">Time Out</th>
                                        <th style="min-width: 220px;">Remarks</th>
                                        <th style="width: 130px;">Encoded</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cutoffRows as $index => $row)
                                        @php
                                            $isSunday = $row['day_name'] === 'Sun';
                                        @endphp
                                        <tr class="{{ $isSunday ? 'table-light' : '' }}">
                                            <td class="fw-semibold">{{ $index + 1 }}</td>
                                            <td>
                                                <div class="fw-semibold">
                                                    {{ \Carbon\Carbon::parse($row['work_date'])->format('M d, Y') }}</div>
                                            </td>
                                            <td>
                                                @if ($isSunday)
                                                    <span
                                                        class="badge bg-warning-subtle text-warning">{{ $row['day_name'] }}</span>
                                                @else
                                                    <span
                                                        class="badge bg-primary-subtle text-primary">{{ $row['day_name'] }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <input type="hidden" name="rows[{{ $index }}][work_date]"
                                                    value="{{ $row['work_date'] }}">
                                                <input type="time" name="rows[{{ $index }}][time_in]"
                                                    class="form-control row-time-in" value="{{ $row['time_in'] }}">
                                            </td>
                                            <td>
                                                <input type="time" name="rows[{{ $index }}][time_out]"
                                                    class="form-control row-time-out" value="{{ $row['time_out'] }}">
                                            </td>
                                            <td>
                                                <input type="text" name="rows[{{ $index }}][remarks]"
                                                    class="form-control row-remarks" value="{{ $row['remarks'] }}"
                                                    placeholder="Optional remarks">
                                            </td>
                                            <td>
                                                @if ($row['has_manual_log'])
                                                    <span class="badge bg-success-subtle text-success">Manual</span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary">None</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-body-tertiary border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">Saved Manual Logs</h5>
                                <small class="text-muted">{{ $cutoffLabel }} |
                                    {{ $selectedEmployee['employee_name'] }}</small>
                            </div>
                            <span class="badge bg-info-subtle text-info">{{ $recentLogs->count() }} log(s)</span>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Date Time</th>
                                        <th>State</th>
                                        <th>Device</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($recentLogs as $log)
                                        <tr>
                                            <td class="fw-semibold">
                                                {{ optional($log->check_time)->format('M d, Y h:i A') }}</td>
                                            <td>
                                                @if ($log->state === 'Check In')
                                                    <span class="badge bg-success-subtle text-success">Check In</span>
                                                @elseif ($log->state === 'Check Out')
                                                    <span class="badge bg-danger-subtle text-danger">Check Out</span>
                                                @else
                                                    <span
                                                        class="badge bg-secondary-subtle text-secondary">{{ $log->state }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $log->device_name }}</td>
                                            <td>{{ data_get($log->raw, 'remarks', '-') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                No saved manual logs for this cutoff.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-5 text-center">
                        <div class="mb-3">
                            <span class="fas fa-user-clock fs-2 text-primary"></span>
                        </div>
                        <h5 class="mb-2">No employee selected yet</h5>
                        <p class="text-muted mb-0">
                            Choose cutoff, search employee from biometrics logs, then click Load.
                        </p>
                    </div>
                </div>
            @endif

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const employeeSearch = document.getElementById('employeeSearch');
        const employeeResults = document.getElementById('employeeResults');
        const crosschexIdInput = document.getElementById('crosschexId');
        let employeeDebounce = null;

        if (employeeSearch) {
            employeeSearch.addEventListener('input', function() {
                const keyword = this.value.trim();
                crosschexIdInput.value = '';

                clearTimeout(employeeDebounce);

                if (keyword.length < 2) {
                    employeeResults.innerHTML = '';
                    employeeResults.classList.add('d-none');
                    return;
                }

                employeeDebounce = setTimeout(() => {
                    fetch(
                            `{{ route('manual-biometrics.search-employees') }}?q=${encodeURIComponent(keyword)}`
                        )
                        .then(response => response.json())
                        .then(data => {
                            employeeResults.innerHTML = '';

                            if (!data.length) {
                                employeeResults.innerHTML =
                                    `<div class="list-group-item text-muted small">No employee found.</div>`;
                                employeeResults.classList.remove('d-none');
                                return;
                            }

                            data.forEach(emp => {
                                const item = document.createElement('button');
                                item.type = 'button';
                                item.className = 'list-group-item list-group-item-action';
                                item.innerHTML = `
                                <div class="fw-semibold">${emp.employee_name ?? ''}</div>
                                <small class="text-muted">
                                    Employee No: ${emp.employee_no ?? '-'} |
                                    CrossChex ID: ${emp.crosschex_id ?? '-'}
                                </small>
                            `;

                                item.addEventListener('click', function() {
                                    employeeSearch.value = emp.employee_name ?? '';
                                    crosschexIdInput.value = emp.crosschex_id ?? '';
                                    employeeResults.innerHTML = '';
                                    employeeResults.classList.add('d-none');
                                });

                                employeeResults.appendChild(item);
                            });

                            employeeResults.classList.remove('d-none');
                        })
                        .catch(() => {
                            employeeResults.innerHTML =
                                `<div class="list-group-item text-danger small">Failed to load employees.</div>`;
                            employeeResults.classList.remove('d-none');
                        });
                }, 300);
            });

            document.addEventListener('click', function(e) {
                if (!employeeSearch.closest('.position-relative')?.contains(e.target)) {
                    employeeResults.classList.add('d-none');
                }
            });
        }

        const applyAllBtn = document.getElementById('applyAllBtn');
        const copyMonSatBtn = document.getElementById('copyMonSatBtn');
        const clearAllBtn = document.getElementById('clearAllBtn');

        function applyCommonTimes(monSatOnly = false) {
            const bulkTimeIn = document.getElementById('bulkTimeIn')?.value || '';
            const bulkTimeOut = document.getElementById('bulkTimeOut')?.value || '';
            const bulkRemarks = document.getElementById('bulkRemarks')?.value || '';

            document.querySelectorAll('table tbody tr').forEach(row => {
                const badge = row.querySelector('td:nth-child(3) .badge');
                const dayText = badge ? badge.textContent.trim() : '';
                const isSunday = dayText === 'Sun';

                if (monSatOnly && isSunday) {
                    return;
                }

                const timeIn = row.querySelector('.row-time-in');
                const timeOut = row.querySelector('.row-time-out');
                const remarks = row.querySelector('.row-remarks');

                if (timeIn && bulkTimeIn && !timeIn.value) timeIn.value = bulkTimeIn;
                if (timeOut && bulkTimeOut && !timeOut.value) timeOut.value = bulkTimeOut;
                if (remarks && bulkRemarks && !remarks.value) remarks.value = bulkRemarks;
            });
        }

        if (applyAllBtn) {
            applyAllBtn.addEventListener('click', function() {
                applyCommonTimes(false);
            });
        }

        if (copyMonSatBtn) {
            copyMonSatBtn.addEventListener('click', function() {
                applyCommonTimes(true);
            });
        }

        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', function() {
                document.querySelectorAll('.row-time-in').forEach(el => el.value = '');
                document.querySelectorAll('.row-time-out').forEach(el => el.value = '');
                document.querySelectorAll('.row-remarks').forEach(el => el.value = '');
            });
        }
    </script>
@endpush
