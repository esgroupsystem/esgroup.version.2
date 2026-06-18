@extends('layouts.app')
@section('title', 'Permanent Plotting Schedule')

@section('content')
    <div class="container-fluid" data-layout="container">
        <script>
            (function() {
                try {
                    var isFluid = JSON.parse(localStorage.getItem('isFluid'));
                    if (isFluid) {
                        var container = document.querySelector('[data-layout]');
                        if (container) {
                            container.classList.remove('container');
                            container.classList.add('container-fluid');
                        }
                    }
                } catch (error) {}
            })();
        </script>

        <div class="content permanent-plotting-page">
            @if (session('success'))
                <div class="alert alert-success border-200 bg-soft-success d-flex align-items-center gap-2 alert-dismissible fade show"
                    role="alert">
                    <span class="fas fa-check-circle"></span>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger border-200 bg-soft-danger d-flex align-items-center gap-2 alert-dismissible fade show"
                    role="alert">
                    <span class="fas fa-exclamation-circle"></span>
                    <div>{{ $errors->first() }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card border-0 shadow-sm mb-3 overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="fas fa-calendar-check text-primary"></span>
                                <h4 class="mb-0">Permanent Plotting Schedule</h4>
                            </div>
                            <p class="text-muted mb-3">
                                Save one fixed schedule per employee. This schedule applies to all payroll summary dates.
                                Holidays and leave should not be plotted here; use Holiday Setup and Attendance Adjustment
                                instead.
                            </p>

                            <div class="d-flex flex-wrap gap-2">
                                <span
                                    class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
                                    <span class="fas fa-users me-1"></span>
                                    {{ number_format($employees->total()) }} unique employee(s)
                                </span>
                                <span
                                    class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                    <span class="fas fa-save me-1"></span>
                                    {{ number_format($stats['saved_permanent'] ?? 0) }} saved on this page
                                </span>
                                <span
                                    class="badge rounded-pill bg-info-subtle text-info border border-info-subtle px-3 py-2">
                                    <span class="fas fa-clock me-1"></span>
                                    {{ number_format($stats['regular'] ?? 0) }} regular /
                                    {{ number_format($stats['flexible'] ?? 0) }} flexible
                                </span>
                            </div>
                        </div>

                        <div class="alert alert-warning mb-0 py-2 px-3 border-200 bg-soft-warning">
                            <div class="fw-semibold text-warning-emphasis mb-1">
                                <span class="fas fa-exclamation-triangle me-1"></span>
                                Payroll safety reminder
                            </div>
                            <small class="text-800">
                                After saving schedule changes, rebuild Attendance Summary before checking payroll.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-primary fs-10 fw-bold text-uppercase mb-2">Visible Employees</div>
                            <h3 class="mb-1">{{ number_format($stats['visible_employees'] ?? 0) }}</h3>
                            <p class="text-muted fs-10 mb-0">Current page after filter</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-success fs-10 fw-bold text-uppercase mb-2">Scheduled</div>
                            <h3 class="mb-1">{{ number_format($stats['scheduled'] ?? 0) }}</h3>
                            <p class="text-muted fs-10 mb-0">Normal working schedule</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-secondary fs-10 fw-bold text-uppercase mb-2">Fixed Rest Day</div>
                            <h3 class="mb-1">{{ number_format($stats['rest_day'] ?? 0) }}</h3>
                            <p class="text-muted fs-10 mb-0">Always paid rest day</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-danger fs-10 fw-bold text-uppercase mb-2">Inactive</div>
                            <h3 class="mb-1">{{ number_format($stats['inactive'] ?? 0) }}</h3>
                            <p class="text-muted fs-10 mb-0">Excluded from normal schedule</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-body-tertiary border-bottom border-200 py-3">
                    <div class="d-flex flex-column flex-xl-row justify-content-between gap-3">
                        <div>
                            <h5 class="mb-1">
                                <span class="fas fa-filter text-primary me-2"></span>
                                Search and Quick Fill
                            </h5>
                            <p class="text-muted fs-10 mb-0">
                                Use filters to find employees, then use quick fill to apply common schedule values to
                                visible rows.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('payroll-plotting.index') }}" class="mb-3">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5 col-xl-4">
                                <label class="form-label fw-semibold">Search Employee</label>
                                <input type="text" name="search" class="form-control" value="{{ $search }}"
                                    placeholder="Employee name, employee no, or biometric ID">
                            </div>
                            <div class="col-md-3 col-xl-2">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="scheduled" {{ $status === 'scheduled' ? 'selected' : '' }}>Scheduled
                                    </option>
                                    <option value="rest_day" {{ $status === 'rest_day' ? 'selected' : '' }}>Fixed Rest Day
                                    </option>
                                    <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3 col-xl-2">
                                <label class="form-label fw-semibold">Shift</label>
                                <select name="shift" class="form-select">
                                    <option value="">All Shifts</option>
                                    <option value="Regular Shift" {{ $shift === 'Regular Shift' ? 'selected' : '' }}>
                                        Regular Shift</option>
                                    <option value="Flexible Shift" {{ $shift === 'Flexible Shift' ? 'selected' : '' }}>
                                        Flexible Shift</option>
                                </select>
                            </div>
                            <div class="col-md-2 col-xl-2 d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <span class="fas fa-search me-1"></span>
                                    Filter
                                </button>
                            </div>
                            <div class="col-md-2 col-xl-2 d-grid">
                                <a href="{{ route('payroll-plotting.index') }}" class="btn btn-outline-secondary">
                                    <span class="fas fa-undo me-1"></span>
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="border rounded-3 p-3 bg-light">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3 col-xl-2">
                                <label class="form-label fs-10 fw-semibold mb-1">Default Status</label>
                                <select id="defaultStatus" class="form-select form-select-sm">
                                    <option value="scheduled">Scheduled</option>
                                    <option value="rest_day">Fixed Rest Day</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-xl-2">
                                <label class="form-label fs-10 fw-semibold mb-1">Default Shift</label>
                                <select id="defaultShift" class="form-select form-select-sm">
                                    <option value="Regular Shift">Regular Shift</option>
                                    <option value="Flexible Shift">Flexible Shift</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-xl-2">
                                <label class="form-label fs-10 fw-semibold mb-1">Time In</label>
                                <input type="time" id="defaultTimeIn" class="form-control form-control-sm"
                                    value="08:00">
                            </div>
                            <div class="col-md-3 col-xl-2">
                                <label class="form-label fs-10 fw-semibold mb-1">Time Out</label>
                                <input type="time" id="defaultTimeOut" class="form-control form-control-sm"
                                    value="17:00">
                            </div>
                            <div class="col-md-3 col-xl-1">
                                <label class="form-label fs-10 fw-semibold mb-1">Grace</label>
                                <input type="number" id="defaultGrace" class="form-control form-control-sm"
                                    value="15" min="0">
                            </div>
                            <div class="col-md-3 col-xl-2">
                                <label class="form-label fs-10 fw-semibold mb-1">Weekly Day Off</label>
                                <select id="defaultDayOff" class="form-select form-select-sm">
                                    <option value="">None</option>
                                    @foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                        <option value="{{ $day }}">{{ $day }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 col-xl-1 d-grid">
                                <button type="button" class="btn btn-warning btn-sm" id="applyDefault">
                                    <span class="fas fa-magic me-1"></span>
                                    Apply
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('payroll-plotting.save') }}" id="permanentScheduleForm">
                @csrf

                <input type="hidden" name="search" value="{{ $search }}">
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="hidden" name="shift" value="{{ $shift }}">

                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-header bg-body-tertiary border-bottom border-200 py-3">
                        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-2">
                            <div>
                                <h5 class="mb-1">
                                    <span class="fas fa-table text-primary me-2"></span>
                                    Employee Permanent Schedule Records
                                </h5>
                                <p class="text-muted fs-10 mb-0">
                                    One row per employee only. The selected weekly day off becomes paid rest day in the
                                    summary.
                                </p>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <span class="fas fa-save me-1"></span>
                                Save Permanent Schedule
                            </button>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive scrollbar">
                            <table class="table table-hover table-sm align-middle mb-0 fs-10 permanent-plotting-table">
                                <thead class="bg-200 text-900">
                                    <tr>
                                        <th class="ps-3" style="min-width: 260px;">Employee</th>
                                        <th class="text-nowrap" style="min-width: 160px;">Schedule Status</th>
                                        <th class="text-nowrap" style="min-width: 170px;">Shift Type</th>
                                        <th class="text-nowrap" style="min-width: 130px;">Time In</th>
                                        <th class="text-nowrap" style="min-width: 130px;">Time Out</th>
                                        <th class="text-nowrap" style="min-width: 110px;">Grace</th>
                                        <th class="text-nowrap" style="min-width: 170px;">Weekly Day Off</th>
                                        <th style="min-width: 230px;">Remarks</th>
                                        <th class="text-nowrap pe-3" style="min-width: 150px;">Current Setup</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($employees as $rowIndex => $employee)
                                        @php
                                            $employeeNo = trim((string) $employee->employee_no);
                                            $biometricEmployeeId = trim(
                                                (string) ($employee->biometric_employee_id ?? ''),
                                            );
                                            $schedule = $schedules->get($employeeNo);

                                            $rowStatus = old(
                                                "schedule.$rowIndex.status",
                                                $schedule->status ?? 'scheduled',
                                            );
                                            $rowShift = old(
                                                "schedule.$rowIndex.shift_name",
                                                $schedule->shift_name ?? 'Regular Shift',
                                            );
                                            $rowTimeIn = old(
                                                "schedule.$rowIndex.time_in",
                                                $schedule?->formatted_time_in ?? '08:00',
                                            );
                                            $rowTimeOut = old(
                                                "schedule.$rowIndex.time_out",
                                                $schedule?->formatted_time_out ?? '17:00',
                                            );
                                            $rowGrace = old(
                                                "schedule.$rowIndex.grace_minutes",
                                                $schedule->grace_minutes ?? 15,
                                            );
                                            $rowDayOff = old("schedule.$rowIndex.day_off", $schedule->day_off ?? '');
                                            $rowRemarks = old("schedule.$rowIndex.remarks", $schedule->remarks ?? '');

                                            $statusClass = match ($rowStatus) {
                                                'scheduled' => 'success',
                                                'rest_day' => 'secondary',
                                                'inactive' => 'danger',
                                                default => 'light',
                                            };
                                        @endphp

                                        <tr class="schedule-row">
                                            <td class="ps-3">
                                                <div class="fw-semibold text-dark">
                                                    {{ $employee->employee_name ?: 'No Name' }}</div>
                                                <div class="text-muted fs-11">
                                                    <strong>Emp No:</strong> {{ $employeeNo ?: '—' }}
                                                </div>
                                                <div class="text-muted fs-11">
                                                    <strong>Biometric ID:</strong> {{ $biometricEmployeeId ?: '—' }}
                                                </div>

                                                <input type="hidden"
                                                    name="schedule[{{ $rowIndex }}][biometric_employee_id]"
                                                    value="{{ $biometricEmployeeId }}">
                                                <input type="hidden" name="schedule[{{ $rowIndex }}][employee_no]"
                                                    value="{{ $employeeNo }}">
                                                <input type="hidden" name="schedule[{{ $rowIndex }}][employee_name]"
                                                    value="{{ $employee->employee_name }}">
                                            </td>

                                            <td>
                                                <select name="schedule[{{ $rowIndex }}][status]"
                                                    class="form-select form-select-sm plot-status">
                                                    <option value="scheduled"
                                                        {{ $rowStatus === 'scheduled' ? 'selected' : '' }}>Scheduled
                                                    </option>
                                                    <option value="rest_day"
                                                        {{ $rowStatus === 'rest_day' ? 'selected' : '' }}>Fixed Rest Day
                                                    </option>
                                                    <option value="inactive"
                                                        {{ $rowStatus === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                </select>
                                                <div class="text-muted fs-11 mt-1 status-help">Normal working schedule
                                                </div>
                                            </td>

                                            <td>
                                                <select name="schedule[{{ $rowIndex }}][shift_name]"
                                                    class="form-select form-select-sm plot-shift">
                                                    <option value="Regular Shift"
                                                        {{ $rowShift === 'Regular Shift' ? 'selected' : '' }}>Regular Shift
                                                    </option>
                                                    <option value="Flexible Shift"
                                                        {{ $rowShift === 'Flexible Shift' ? 'selected' : '' }}>Flexible
                                                        Shift</option>
                                                </select>
                                                <div class="text-muted fs-11 mt-1 shift-help">Regular uses fixed in/out
                                                </div>
                                            </td>

                                            <td>
                                                <input type="time" name="schedule[{{ $rowIndex }}][time_in]"
                                                    class="form-control form-control-sm plot-time-in"
                                                    value="{{ $rowTimeIn }}">
                                            </td>

                                            <td>
                                                <input type="time" name="schedule[{{ $rowIndex }}][time_out]"
                                                    class="form-control form-control-sm plot-time-out"
                                                    value="{{ $rowTimeOut }}">
                                            </td>

                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <input type="number"
                                                        name="schedule[{{ $rowIndex }}][grace_minutes]"
                                                        class="form-control plot-grace" value="{{ $rowGrace }}"
                                                        min="0" max="240">
                                                    <span class="input-group-text">min</span>
                                                </div>
                                            </td>

                                            <td>
                                                <select name="schedule[{{ $rowIndex }}][day_off]"
                                                    class="form-select form-select-sm plot-day-off">
                                                    <option value="">No weekly day off</option>
                                                    @foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                                        <option value="{{ $day }}"
                                                            {{ $rowDayOff === $day ? 'selected' : '' }}>
                                                            {{ $day }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="text-muted fs-11 mt-1">This day is paid rest day.</div>
                                            </td>

                                            <td>
                                                <input type="text" name="schedule[{{ $rowIndex }}][remarks]"
                                                    class="form-control form-control-sm plot-remarks"
                                                    value="{{ $rowRemarks }}"
                                                    placeholder="Example: Main office schedule">
                                            </td>

                                            <td class="pe-3">
                                                <span
                                                    class="badge badge-phoenix badge-phoenix-{{ $statusClass }} px-3 py-2 setup-badge">
                                                    {{ strtoupper(str_replace('_', ' ', $rowStatus)) }}
                                                </span>
                                                <div class="text-muted fs-11 mt-2 setup-preview">
                                                    {{ $rowShift }}
                                                    @if ($rowShift === 'Regular Shift' && $rowStatus === 'scheduled')
                                                        <br>{{ $rowTimeIn ?: '—' }} to {{ $rowTimeOut ?: '—' }}
                                                    @endif
                                                    @if ($rowDayOff)
                                                        <br>Day off: {{ $rowDayOff }}
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center justify-content-center">
                                                    <div class="avatar avatar-4xl mb-3">
                                                        <div
                                                            class="avatar-name rounded-circle bg-soft-secondary text-secondary">
                                                            <span class="fas fa-users fs-2"></span>
                                                        </div>
                                                    </div>
                                                    <h5 class="mb-1">No employees found</h5>
                                                    <p class="text-muted mb-0">
                                                        Try changing the search filter or check the biometrics employee
                                                        records.
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer bg-body-tertiary border-top border-200 py-3">
                        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                            <div class="fs-10 text-muted">
                                @if ($employees->total() > 0)
                                    Showing {{ $employees->firstItem() }} to {{ $employees->lastItem() }} of
                                    {{ $employees->total() }} unique employee(s)
                                @else
                                    Showing 0 employee(s)
                                @endif
                            </div>

                            <div class="d-flex flex-wrap align-items-center gap-2">
                                @if ($employees->hasPages())
                                    <div>{{ $employees->links('pagination.custom') }}</div>
                                @endif

                                <button type="submit" class="btn btn-primary">
                                    <span class="fas fa-save me-1"></span>
                                    Save Permanent Schedule
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <style>
        .permanent-plotting-page .table th,
        .permanent-plotting-page .table td {
            vertical-align: middle;
        }

        .permanent-plotting-table tbody tr:hover {
            background: rgba(44, 123, 229, 0.04);
        }

        .plot-time-disabled {
            background-color: var(--falcon-gray-200, #edf2f9) !important;
            opacity: .75;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const applyBtn = document.getElementById('applyDefault');
            const defaultStatus = document.getElementById('defaultStatus');
            const defaultShift = document.getElementById('defaultShift');
            const defaultTimeIn = document.getElementById('defaultTimeIn');
            const defaultTimeOut = document.getElementById('defaultTimeOut');
            const defaultGrace = document.getElementById('defaultGrace');
            const defaultDayOff = document.getElementById('defaultDayOff');

            function updateRow(row) {
                const statusInput = row.querySelector('.plot-status');
                const shiftInput = row.querySelector('.plot-shift');
                const timeInInput = row.querySelector('.plot-time-in');
                const timeOutInput = row.querySelector('.plot-time-out');
                const graceInput = row.querySelector('.plot-grace');
                const dayOffInput = row.querySelector('.plot-day-off');
                const setupBadge = row.querySelector('.setup-badge');
                const setupPreview = row.querySelector('.setup-preview');
                const statusHelp = row.querySelector('.status-help');
                const shiftHelp = row.querySelector('.shift-help');

                if (!statusInput || !shiftInput) return;

                const status = statusInput.value;
                const shift = shiftInput.value;
                const isScheduled = status === 'scheduled';
                const isFlexible = shift === 'Flexible Shift';
                const disableTime = !isScheduled || isFlexible;

                [timeInInput, timeOutInput].forEach(function(input) {
                    if (!input) return;
                    input.disabled = disableTime;
                    input.classList.toggle('plot-time-disabled', disableTime);
                });

                if (graceInput) {
                    graceInput.disabled = !isScheduled;
                    graceInput.classList.toggle('plot-time-disabled', !isScheduled);
                }

                if (statusHelp) {
                    statusHelp.textContent = status === 'scheduled' ?
                        'Normal working schedule' :
                        status === 'rest_day' ?
                        'Always paid rest day' :
                        'Not included as normal workday';
                }

                if (shiftHelp) {
                    shiftHelp.textContent = isFlexible ? 'Summary checks 9 worked hours' :
                        'Regular uses fixed in/out';
                }

                if (setupBadge) {
                    setupBadge.className = 'badge badge-phoenix px-3 py-2 setup-badge';
                    if (status === 'scheduled') setupBadge.classList.add('badge-phoenix-success');
                    if (status === 'rest_day') setupBadge.classList.add('badge-phoenix-secondary');
                    if (status === 'inactive') setupBadge.classList.add('badge-phoenix-danger');
                    setupBadge.textContent = status.replace('_', ' ').toUpperCase();
                }

                if (setupPreview) {
                    const timeIn = timeInInput && !timeInInput.disabled ? timeInInput.value : '';
                    const timeOut = timeOutInput && !timeOutInput.disabled ? timeOutInput.value : '';
                    const dayOff = dayOffInput ? dayOffInput.value : '';
                    let html = shift;

                    if (isScheduled && !isFlexible) {
                        html += '<br>' + (timeIn || '—') + ' to ' + (timeOut || '—');
                    }

                    if (dayOff) {
                        html += '<br>Day off: ' + dayOff;
                    }

                    setupPreview.innerHTML = html;
                }
            }

            document.querySelectorAll('.schedule-row').forEach(function(row) {
                updateRow(row);

                row.querySelectorAll('select, input').forEach(function(input) {
                    input.addEventListener('change', function() {
                        updateRow(row);
                    });
                    input.addEventListener('keyup', function() {
                        updateRow(row);
                    });
                });
            });

            if (applyBtn) {
                applyBtn.addEventListener('click', function() {
                    document.querySelectorAll('.schedule-row').forEach(function(row) {
                        const statusInput = row.querySelector('.plot-status');
                        const shiftInput = row.querySelector('.plot-shift');
                        const timeInInput = row.querySelector('.plot-time-in');
                        const timeOutInput = row.querySelector('.plot-time-out');
                        const graceInput = row.querySelector('.plot-grace');
                        const dayOffInput = row.querySelector('.plot-day-off');

                        if (statusInput) statusInput.value = defaultStatus.value;
                        if (shiftInput) shiftInput.value = defaultShift.value;
                        if (timeInInput) timeInInput.value = defaultTimeIn.value;
                        if (timeOutInput) timeOutInput.value = defaultTimeOut.value;
                        if (graceInput) graceInput.value = defaultGrace.value;
                        if (dayOffInput) dayOffInput.value = defaultDayOff.value;

                        updateRow(row);
                    });
                });
            }
        });
    </script>
@endsection
