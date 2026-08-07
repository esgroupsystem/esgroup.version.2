@extends('layouts.app')
@section('title', 'Permanent Plotting Schedule')

@section('content')
    <div class="container-fluid" data-layout="container">
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
                <div class="alert alert-danger border-200 bg-soft-danger alert-dismissible fade show" role="alert">
                    <div class="d-flex align-items-start gap-2">
                        <span class="fas fa-exclamation-circle mt-1"></span>
                        <div>
                            <div class="fw-semibold">The schedule was not saved.</div>
                            <div>{{ $errors->first() }}</div>
                        </div>
                    </div>
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
                                Assign either 8 paid hours plus 1 unpaid lunch hour, or 9 paid hours plus 1 unpaid lunch
                                hour. You may select more than one weekly day off for each employee.
                            </p>

                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
                                    <span class="fas fa-users me-1"></span>
                                    {{ number_format($employees->total()) }} employee(s)
                                </span>
                                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                    <span class="fas fa-clock me-1"></span>
                                    {{ number_format($stats['eight_hours'] ?? 0) }} on 8-hour workday
                                </span>
                                <span class="badge rounded-pill bg-info-subtle text-info border border-info-subtle px-3 py-2">
                                    <span class="fas fa-business-time me-1"></span>
                                    {{ number_format($stats['nine_hours'] ?? 0) }} on 9-hour workday
                                </span>
                            </div>
                        </div>

                        <div class="alert alert-warning mb-0 py-2 px-3 border-200 bg-soft-warning">
                            <div class="fw-semibold text-warning-emphasis mb-1">
                                <span class="fas fa-exclamation-triangle me-1"></span>
                                Payroll safety reminder
                            </div>
                            <small class="text-800">
                                After saving schedule changes, rebuild Attendance Summary before regenerating a draft
                                payroll.
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
                            <p class="text-muted fs-10 mb-0">Current page after filtering</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-success fs-10 fw-bold text-uppercase mb-2">Scheduled</div>
                            <h3 class="mb-1">{{ number_format($stats['scheduled'] ?? 0) }}</h3>
                            <p class="text-muted fs-10 mb-0">Normal work schedules</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-secondary fs-10 fw-bold text-uppercase mb-2">Rest-Day Status</div>
                            <h3 class="mb-1">{{ number_format($stats['rest_day'] ?? 0) }}</h3>
                            <p class="text-muted fs-10 mb-0">Permanent non-working status</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-danger fs-10 fw-bold text-uppercase mb-2">Inactive</div>
                            <h3 class="mb-1">{{ number_format($stats['inactive'] ?? 0) }}</h3>
                            <p class="text-muted fs-10 mb-0">Excluded from payroll attendance</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-body-tertiary border-bottom border-200 py-3">
                    <h5 class="mb-1">
                        <span class="fas fa-filter text-primary me-2"></span>
                        Search and Quick Fill
                    </h5>
                    <p class="text-muted fs-10 mb-0">
                        Quick Fill applies the selected values to every visible employee row only.
                    </p>
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('payroll-plotting.index') }}" class="mb-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5 col-xl-4">
                                <label class="form-label fw-semibold">Search Employee</label>
                                <input type="text" name="search" class="form-control" value="{{ $search }}"
                                    placeholder="Name, employee no., or biometric ID">
                            </div>
                            <div class="col-md-3 col-xl-2">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="scheduled" @selected($status === 'scheduled')>Scheduled</option>
                                    <option value="rest_day" @selected($status === 'rest_day')>Rest Day</option>
                                    <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-xl-2">
                                <label class="form-label fw-semibold">Shift</label>
                                <select name="shift" class="form-select">
                                    <option value="">All Shifts</option>
                                    <option value="Regular Shift" @selected($shift === 'Regular Shift')>Regular Shift</option>
                                    <option value="Flexible Shift" @selected($shift === 'Flexible Shift')>Flexible Shift</option>
                                </select>
                            </div>
                            <div class="col-md-4 col-xl-2">
                                <label class="form-label fw-semibold">Garage / Group</label>
                                <select name="group_name" class="form-select">
                                    <option value="">All Groups</option>
                                    @foreach ($groups as $group)
                                        <option value="{{ $group }}" @selected($groupName === $group)>{{ $group }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 col-xl-1 d-grid">
                                <button class="btn btn-primary" type="submit">
                                    <span class="fas fa-search"></span>
                                </button>
                            </div>
                            <div class="col-md-2 col-xl-1 d-grid">
                                <a href="{{ route('payroll-plotting.index') }}" class="btn btn-outline-secondary">
                                    <span class="fas fa-undo"></span>
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="quick-fill-panel rounded-3 border border-200 bg-body-tertiary p-3">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="fas fa-wand-magic-sparkles text-primary"></span>
                            <div class="fw-semibold">Quick Fill Visible Rows</div>
                        </div>

                        <div class="row g-3 align-items-end">
                            <div class="col-md-3 col-xl-2">
                                <label class="form-label fs-10 fw-semibold">Status</label>
                                <select id="defaultStatus" class="form-select form-select-sm">
                                    <option value="scheduled">Scheduled</option>
                                    <option value="rest_day">Rest Day</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-xl-2">
                                <label class="form-label fs-10 fw-semibold">Shift</label>
                                <select id="defaultShift" class="form-select form-select-sm">
                                    <option value="Regular Shift">Regular Shift</option>
                                    <option value="Flexible Shift">Flexible Shift</option>
                                </select>
                            </div>
                            <div class="col-md-4 col-xl-2">
                                <label class="form-label fs-10 fw-semibold">Work Hours</label>
                                <select id="defaultWorkdayType" class="form-select form-select-sm">
                                    @foreach ($workdayOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 col-xl-1">
                                <label class="form-label fs-10 fw-semibold">Time In</label>
                                <input id="defaultTimeIn" type="time" class="form-control form-control-sm"
                                    value="08:00">
                            </div>
                            <div class="col-md-2 col-xl-1">
                                <label class="form-label fs-10 fw-semibold">Time Out</label>
                                <input id="defaultTimeOut" type="time" class="form-control form-control-sm"
                                    value="17:00">
                            </div>
                            <div class="col-md-2 col-xl-1">
                                <label class="form-label fs-10 fw-semibold">Grace</label>
                                <input id="defaultGrace" type="number" class="form-control form-control-sm"
                                    value="15" min="0" max="240">
                            </div>
                            <div class="col-md-6 col-xl-2">
                                <label class="form-label fs-10 fw-semibold">Weekly Days Off</label>
                                <div class="default-day-off-list">
                                    @foreach ($weekdays as $day)
                                        <label class="form-check form-check-inline mb-1 me-2">
                                            <input class="form-check-input default-day-off" type="checkbox"
                                                value="{{ $day }}">
                                            <span class="form-check-label fs-11">{{ substr($day, 0, 3) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-md-3 col-xl-1 d-grid">
                                <button id="applyDefault" type="button" class="btn btn-falcon-primary btn-sm">
                                    Apply
                                </button>
                            </div>
                        </div>
                        <div class="text-muted fs-11 mt-2">
                            Time Out is recalculated automatically when Work Hours or Time In changes.
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('payroll-plotting.save') }}">
                @csrf
                <input type="hidden" name="search" value="{{ $search }}">
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="hidden" name="shift" value="{{ $shift }}">
                <input type="hidden" name="group_name" value="{{ $groupName }}">

                <div class="card border-0 shadow-sm overflow-hidden">
                    <div class="card-header bg-white border-bottom border-200 py-3">
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2">
                            <div>
                                <h5 class="mb-1">Employee Work Schedule</h5>
                                <p class="text-muted fs-10 mb-0">
                                    Regular Shift must span exactly 9 clock hours for an 8-hour workday or 10 clock hours
                                    for a 9-hour workday. One lunch hour is unpaid.
                                </p>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <span class="fas fa-save me-1"></span>
                                Save Permanent Schedule
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive schedule-table-wrap">
                        <table class="table table-hover mb-0 permanent-plotting-table">
                            <thead class="bg-body-tertiary">
                                <tr>
                                    <th class="ps-3">Employee</th>
                                    <th>Status</th>
                                    <th>Shift</th>
                                    <th>Work Hours</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Grace</th>
                                    <th>Weekly Days Off</th>
                                    <th>Remarks</th>
                                    <th class="pe-3">Setup Preview</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($employees as $rowIndex => $employee)
                                    @php
                                        $schedule = $schedules->get((int) $employee->id);
                                        $rowStatus = old("schedule.$rowIndex.status", $schedule?->status ?? 'scheduled');
                                        $rowShift = old("schedule.$rowIndex.shift_name", $schedule?->shift_name ?? 'Regular Shift');
                                        $rowWorkdayType = old(
                                            "schedule.$rowIndex.workday_type",
                                            $schedule?->resolvedWorkdayType()->value ?? 'eight_hours',
                                        );
                                        $rowTimeIn = old("schedule.$rowIndex.time_in", $schedule?->time_in ? substr((string) $schedule->time_in, 0, 5) : '08:00');
                                        $defaultTimeOut = $rowWorkdayType === 'nine_hours' ? '18:00' : '17:00';
                                        $rowTimeOut = old("schedule.$rowIndex.time_out", $schedule?->time_out ? substr((string) $schedule->time_out, 0, 5) : $defaultTimeOut);
                                        $rowGrace = old("schedule.$rowIndex.grace_minutes", $schedule?->grace_minutes ?? 15);
                                        $rowDayOffs = old("schedule.$rowIndex.day_offs", $schedule?->resolvedDayOffs() ?? []);
                                        $rowDayOffs = is_array($rowDayOffs)
                                            ? array_values($rowDayOffs)
                                            : array_values(array_filter(array_map('trim', explode(',', (string) $rowDayOffs))));
                                        $rowRemarks = old("schedule.$rowIndex.remarks", $schedule?->remarks ?? '');
                                        $statusClass = match ($rowStatus) {
                                            'scheduled' => 'success',
                                            'rest_day' => 'secondary',
                                            'inactive' => 'danger',
                                            default => 'primary',
                                        };
                                    @endphp

                                    <tr class="schedule-row" data-row-index="{{ $rowIndex }}">
                                        <td class="ps-3 employee-cell">
                                            <input type="hidden" name="schedule[{{ $rowIndex }}][employee_biometric_id]"
                                                value="{{ $employee->plotting_employee_biometric_id }}">
                                            <div class="fw-semibold text-900">{{ $employee->plotting_employee_name }}</div>
                                            <div class="text-muted fs-11">
                                                {{ $employee->plotting_employee_no ?: 'No employee number' }}
                                            </div>
                                            @if ($employee->group_name)
                                                <span class="badge badge-phoenix badge-phoenix-info mt-1">
                                                    {{ $employee->group_name }}
                                                </span>
                                            @endif
                                        </td>

                                        <td class="status-cell">
                                            <select name="schedule[{{ $rowIndex }}][status]"
                                                class="form-select form-select-sm plot-status">
                                                <option value="scheduled" @selected($rowStatus === 'scheduled')>Scheduled</option>
                                                <option value="rest_day" @selected($rowStatus === 'rest_day')>Rest Day</option>
                                                <option value="inactive" @selected($rowStatus === 'inactive')>Inactive</option>
                                            </select>
                                            <div class="text-muted fs-11 mt-1 status-help"></div>
                                        </td>

                                        <td class="shift-cell">
                                            <select name="schedule[{{ $rowIndex }}][shift_name]"
                                                class="form-select form-select-sm plot-shift">
                                                <option value="Regular Shift" @selected($rowShift === 'Regular Shift')>Regular Shift</option>
                                                <option value="Flexible Shift" @selected($rowShift === 'Flexible Shift')>Flexible Shift</option>
                                            </select>
                                            <div class="text-muted fs-11 mt-1 shift-help"></div>
                                        </td>

                                        <td class="workday-cell">
                                            <select name="schedule[{{ $rowIndex }}][workday_type]"
                                                class="form-select form-select-sm plot-workday-type">
                                                @foreach ($workdayOptions as $value => $label)
                                                    <option value="{{ $value }}" @selected($rowWorkdayType === $value)>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="text-muted fs-11 mt-1 workday-help"></div>
                                        </td>

                                        <td class="time-cell">
                                            <input type="time" name="schedule[{{ $rowIndex }}][time_in]"
                                                class="form-control form-control-sm plot-time-in" value="{{ $rowTimeIn }}">
                                        </td>

                                        <td class="time-cell">
                                            <input type="time" name="schedule[{{ $rowIndex }}][time_out]"
                                                class="form-control form-control-sm plot-time-out" value="{{ $rowTimeOut }}">
                                        </td>

                                        <td class="grace-cell">
                                            <div class="input-group input-group-sm">
                                                <input type="number" name="schedule[{{ $rowIndex }}][grace_minutes]"
                                                    class="form-control plot-grace" value="{{ $rowGrace }}" min="0"
                                                    max="240">
                                                <span class="input-group-text">min</span>
                                            </div>
                                        </td>

                                        <td class="day-off-cell">
                                            <div class="day-off-grid">
                                                @foreach ($weekdays as $day)
                                                    <label class="form-check mb-1">
                                                        <input type="checkbox"
                                                            name="schedule[{{ $rowIndex }}][day_offs][]"
                                                            class="form-check-input plot-day-off" value="{{ $day }}"
                                                            @checked(in_array($day, $rowDayOffs, true))>
                                                        <span class="form-check-label fs-11">{{ $day }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </td>

                                        <td class="remarks-cell">
                                            <input type="text" name="schedule[{{ $rowIndex }}][remarks]"
                                                class="form-control form-control-sm plot-remarks" value="{{ $rowRemarks }}"
                                                maxlength="255" placeholder="Optional note">
                                        </td>

                                        <td class="pe-3 preview-cell">
                                            <span class="badge badge-phoenix badge-phoenix-{{ $statusClass }} px-3 py-2 setup-badge">
                                                {{ strtoupper(str_replace('_', ' ', $rowStatus)) }}
                                            </span>
                                            <div class="text-muted fs-11 mt-2 setup-preview"></div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5">
                                            <span class="fas fa-users fs-2 text-muted mb-3"></span>
                                            <h5 class="mb-1">No employees found</h5>
                                            <p class="text-muted mb-0">Change the filters or verify the biometric employee records.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer bg-body-tertiary border-top border-200 py-3">
                        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                            <div class="fs-10 text-muted">
                                @if ($employees->total() > 0)
                                    Showing {{ $employees->firstItem() }} to {{ $employees->lastItem() }} of
                                    {{ $employees->total() }} employee(s)
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

        .permanent-plotting-table {
            min-width: 1760px;
        }

        .permanent-plotting-table tbody tr:hover {
            background: rgba(44, 123, 229, 0.04);
        }

        .employee-cell {
            min-width: 220px;
        }

        .status-cell,
        .shift-cell {
            min-width: 170px;
        }

        .workday-cell {
            min-width: 230px;
        }

        .time-cell {
            min-width: 125px;
        }

        .grace-cell {
            min-width: 120px;
        }

        .day-off-cell {
            min-width: 210px;
        }

        .remarks-cell {
            min-width: 210px;
        }

        .preview-cell {
            min-width: 220px;
        }

        .day-off-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(80px, 1fr));
            column-gap: .75rem;
        }

        .default-day-off-list {
            max-height: 94px;
            overflow-y: auto;
            padding: .4rem .55rem;
            border: 1px solid var(--falcon-gray-300, #d8e2ef);
            border-radius: .375rem;
            background: var(--falcon-emphasis-bg, #fff);
        }

        .plot-time-disabled {
            background-color: var(--falcon-gray-200, #edf2f9) !important;
            opacity: .75;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const workdayRules = @json($workdayRules);
            const applyBtn = document.getElementById('applyDefault');
            const defaultStatus = document.getElementById('defaultStatus');
            const defaultShift = document.getElementById('defaultShift');
            const defaultWorkdayType = document.getElementById('defaultWorkdayType');
            const defaultTimeIn = document.getElementById('defaultTimeIn');
            const defaultTimeOut = document.getElementById('defaultTimeOut');
            const defaultGrace = document.getElementById('defaultGrace');

            function addMinutesToTime(time, minutes) {
                if (!time || !Number.isFinite(Number(minutes))) return '';

                const parts = time.split(':').map(Number);
                if (parts.length !== 2 || parts.some(Number.isNaN)) return '';

                const total = ((parts[0] * 60) + parts[1] + Number(minutes)) % (24 * 60);
                const normalized = total < 0 ? total + (24 * 60) : total;
                const hours = String(Math.floor(normalized / 60)).padStart(2, '0');
                const mins = String(normalized % 60).padStart(2, '0');

                return `${hours}:${mins}`;
            }

            function selectedDays(row) {
                return Array.from(row.querySelectorAll('.plot-day-off:checked'))
                    .map(input => input.value);
            }

            function syncTimeOut(timeInInput, timeOutInput, workdayInput) {
                const rule = workdayRules[workdayInput?.value];
                if (!timeInInput || !timeOutInput || !rule) return;

                timeOutInput.value = addMinutesToTime(timeInInput.value, rule.clock_minutes);
            }

            function updateRow(row) {
                const statusInput = row.querySelector('.plot-status');
                const shiftInput = row.querySelector('.plot-shift');
                const workdayInput = row.querySelector('.plot-workday-type');
                const timeInInput = row.querySelector('.plot-time-in');
                const timeOutInput = row.querySelector('.plot-time-out');
                const graceInput = row.querySelector('.plot-grace');
                const setupBadge = row.querySelector('.setup-badge');
                const setupPreview = row.querySelector('.setup-preview');
                const statusHelp = row.querySelector('.status-help');
                const shiftHelp = row.querySelector('.shift-help');
                const workdayHelp = row.querySelector('.workday-help');

                if (!statusInput || !shiftInput || !workdayInput) return;

                const status = statusInput.value;
                const shift = shiftInput.value;
                const rule = workdayRules[workdayInput.value] || workdayRules.eight_hours;
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
                    statusHelp.textContent = status === 'scheduled'
                        ? 'Normal attendance computation'
                        : status === 'rest_day'
                            ? 'Permanent rest-day status'
                            : 'Excluded from payroll attendance';
                }

                if (shiftHelp) {
                    shiftHelp.textContent = isFlexible
                        ? `Requires ${rule.clock_minutes / 60} clock hours`
                        : 'Fixed Time In and Time Out';
                }

                if (workdayHelp) {
                    workdayHelp.textContent = `${rule.paid_hours} paid + ${rule.lunch_minutes / 60} unpaid lunch hour`;
                }

                if (setupBadge) {
                    setupBadge.className = 'badge badge-phoenix px-3 py-2 setup-badge';
                    setupBadge.classList.add(
                        status === 'scheduled'
                            ? 'badge-phoenix-success'
                            : status === 'rest_day'
                                ? 'badge-phoenix-secondary'
                                : 'badge-phoenix-danger'
                    );
                    setupBadge.textContent = status.replace('_', ' ').toUpperCase();
                }

                if (setupPreview) {
                    let html = `${shift}<br>${rule.short_label}`;
                    const timeIn = timeInInput && !timeInInput.disabled ? timeInInput.value : '';
                    const timeOut = timeOutInput && !timeOutInput.disabled ? timeOutInput.value : '';
                    const days = selectedDays(row);

                    if (isScheduled && !isFlexible) {
                        html += `<br>${timeIn || '—'} to ${timeOut || '—'}`;
                    }

                    if (days.length > 0) {
                        html += `<br>Days off: ${days.join(', ')}`;
                    } else {
                        html += '<br>Days off: None';
                    }

                    setupPreview.innerHTML = html;
                }
            }

            document.querySelectorAll('.schedule-row').forEach(function(row) {
                updateRow(row);

                row.querySelectorAll('select, input').forEach(function(input) {
                    input.addEventListener('change', function() {
                        if (input.classList.contains('plot-time-in') || input.classList.contains('plot-workday-type')) {
                            syncTimeOut(
                                row.querySelector('.plot-time-in'),
                                row.querySelector('.plot-time-out'),
                                row.querySelector('.plot-workday-type')
                            );
                        }

                        updateRow(row);
                    });

                    input.addEventListener('keyup', function() {
                        updateRow(row);
                    });
                });
            });

            function syncDefaultTimeOut() {
                const rule = workdayRules[defaultWorkdayType?.value];
                if (!rule || !defaultTimeIn || !defaultTimeOut) return;

                defaultTimeOut.value = addMinutesToTime(defaultTimeIn.value, rule.clock_minutes);
            }

            defaultWorkdayType?.addEventListener('change', syncDefaultTimeOut);
            defaultTimeIn?.addEventListener('change', syncDefaultTimeOut);

            applyBtn?.addEventListener('click', function() {
                const defaultDays = Array.from(document.querySelectorAll('.default-day-off:checked'))
                    .map(input => input.value);

                document.querySelectorAll('.schedule-row').forEach(function(row) {
                    const statusInput = row.querySelector('.plot-status');
                    const shiftInput = row.querySelector('.plot-shift');
                    const workdayInput = row.querySelector('.plot-workday-type');
                    const timeInInput = row.querySelector('.plot-time-in');
                    const timeOutInput = row.querySelector('.plot-time-out');
                    const graceInput = row.querySelector('.plot-grace');

                    if (statusInput) statusInput.value = defaultStatus.value;
                    if (shiftInput) shiftInput.value = defaultShift.value;
                    if (workdayInput) workdayInput.value = defaultWorkdayType.value;
                    if (timeInInput) timeInInput.value = defaultTimeIn.value;
                    if (timeOutInput) timeOutInput.value = defaultTimeOut.value;
                    if (graceInput) graceInput.value = defaultGrace.value;

                    row.querySelectorAll('.plot-day-off').forEach(function(input) {
                        input.checked = defaultDays.includes(input.value);
                    });

                    updateRow(row);
                });
            });
        });
    </script>
@endsection
