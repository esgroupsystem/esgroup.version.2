@extends('layouts.app')
@section('title', 'Permanent Plotting Schedule')

@section('content')
    <div class="container-fluid py-3">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <span class="fas fa-check-circle me-1"></span> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm">

            <div class="card-header bg-light">
                <h5 class="mb-1">Permanent Plotting Schedule</h5>
                <p class="text-muted fs-10 mb-0">One-time plotting schedule for all employees</p>
            </div>

            {{-- Quick Fill Controls --}}
            <div class="card-body border-bottom">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label fs-10 mb-1">Shift Name</label>
                        <select id="defaultShift" class="form-select form-select-sm">
                            <option value="Regular Shift">Regular Shift</option>
                            <option value="Flexible Shift">Flexible Shift</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fs-10 mb-1">Day Off</label>
                        <select id="defaultDayOff" class="form-select form-select-sm">
                            <option value="">None</option>
                            @foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                <option value="{{ $day }}">{{ $day }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fs-10 mb-1">Time In</label>
                        <input type="time" id="defaultTimeIn" class="form-control form-control-sm" value="08:00">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fs-10 mb-1">Time Out</label>
                        <input type="time" id="defaultTimeOut" class="form-control form-control-sm" value="17:00">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label fs-10 mb-1">Grace</label>
                        <input type="number" id="defaultGrace" class="form-control form-control-sm" value="15"
                            min="0">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fs-10 mb-1">Status</label>
                        <select id="defaultStatus" class="form-select form-select-sm">
                            <option value="scheduled">Scheduled</option>
                            <option value="rest_day">Rest Day</option>
                            <option value="leave">Leave</option>
                            <option value="holiday">Holiday</option>
                        </select>
                    </div>

                    <div class="col-md-auto">
                        <button type="button" class="btn btn-warning btn-sm" id="applyDefault">
                            <span class="fas fa-magic me-1"></span> Apply Default to All
                        </button>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('payroll-plotting.save') }}">
                @csrf
                <div class="card-body p-0">
                    <div class="table-responsive scrollbar">
                        <table class="table table-bordered table-sm mb-0 align-middle">
                            <thead class="bg-light text-center">
                                <tr>
                                    <th style="width: 18%;">Employee</th>
                                    <th style="width: 12%;">Status</th>
                                    <th style="width: 15%;">Shift Name</th>
                                    <th style="width: 10%;">Time In</th>
                                    <th style="width: 10%;">Time Out</th>
                                    <th style="width: 12%;">Grace</th>
                                    <th style="width: 12%;">Day Off</th>
                                    <th style="width: 20%;">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($employees as $rowIndex => $employee)
                                    @php
                                        $employeeNo = trim((string) $employee->employee_no);
                                        $biometricEmployeeId = trim((string) ($employee->biometric_employee_id ?? ''));
                                        $schedule = $schedules->get($employeeNo);
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $employee->employee_name ?: 'No Name' }}
                                            </div>
                                            <small class="text-muted">{{ $employeeNo }}</small>
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
                                                    {{ ($schedule->status ?? 'scheduled') === 'scheduled' ? 'selected' : '' }}>
                                                    Scheduled</option>
                                                <option value="rest_day"
                                                    {{ ($schedule->status ?? '') === 'rest_day' ? 'selected' : '' }}>Rest
                                                    Day</option>
                                                <option value="leave"
                                                    {{ ($schedule->status ?? '') === 'leave' ? 'selected' : '' }}>Leave
                                                </option>
                                                <option value="holiday"
                                                    {{ ($schedule->status ?? '') === 'holiday' ? 'selected' : '' }}>Holiday
                                                </option>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="schedule[{{ $rowIndex }}][shift_name]"
                                                class="form-select form-select-sm plot-shift">
                                                <option value="Regular Shift"
                                                    {{ ($schedule->shift_name ?? 'Regular Shift') === 'Regular Shift' ? 'selected' : '' }}>
                                                    Regular Shift</option>
                                                <option value="Flexible Shift"
                                                    {{ ($schedule->shift_name ?? '') === 'Flexible Shift' ? 'selected' : '' }}>
                                                    Flexible Shift</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="time" name="schedule[{{ $rowIndex }}][time_in]"
                                                class="form-control form-control-sm plot-time-in"
                                                value="{{ $schedule->time_in ?? '' }}">
                                        </td>
                                        <td>
                                            <input type="time" name="schedule[{{ $rowIndex }}][time_out]"
                                                class="form-control form-control-sm plot-time-out"
                                                value="{{ $schedule->time_out ?? '' }}">
                                        </td>
                                        <td>
                                            <input type="number" name="schedule[{{ $rowIndex }}][grace_minutes]"
                                                class="form-control form-control-sm plot-grace"
                                                value="{{ $schedule->grace_minutes ?? 15 }}" min="0">
                                        </td>
                                        <td>
                                            <select name="schedule[{{ $rowIndex }}][day_off]"
                                                class="form-select form-select-sm plot-day-off">
                                                <option value="">None</option>
                                                @foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                                    <option value="{{ $day }}"
                                                        {{ ($schedule->day_off ?? '') === $day ? 'selected' : '' }}>
                                                        {{ $day }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="schedule[{{ $rowIndex }}][remarks]"
                                                class="form-control form-control-sm plot-remarks"
                                                value="{{ $schedule->remarks ?? '' }}" placeholder="Remarks">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">No employees found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Pagination --}}
                <div class="card-footer bg-body-tertiary d-flex justify-content-between align-items-center">
                    <div class="fs-10 text-muted">
                        @if ($employees->total() > 0)
                            Showing {{ $employees->firstItem() }} to {{ $employees->lastItem() }} of
                            {{ $employees->total() }} unique employees
                        @else
                            Showing 0 employees
                        @endif
                    </div>
                    @if ($employees->hasPages())
                        <div>{{ $employees->links('pagination.custom') }}</div>
                    @endif
                </div>

                <div class="card-footer bg-light text-end">
                    <button type="submit" class="btn btn-primary btn-sm"><span class="fas fa-save me-1"></span> Save
                        Permanent Schedule</button>
                </div>

            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const applyBtn = document.getElementById('applyDefault');
            const defaultShift = document.getElementById('defaultShift');
            const defaultGrace = document.getElementById('defaultGrace');
            const defaultStatus = document.getElementById('defaultStatus');
            const defaultDayOff = document.getElementById('defaultDayOff');
            const defaultTimeIn = document.getElementById('defaultTimeIn');
            const defaultTimeOut = document.getElementById('defaultTimeOut');

            if (!applyBtn) return;

            applyBtn.addEventListener('click', function() {
                const shift = defaultShift.value;
                const grace = defaultGrace.value;
                const status = defaultStatus.value;
                const dayOff = defaultDayOff ? defaultDayOff.value : '';
                const timeIn = defaultTimeIn ? defaultTimeIn.value : '';
                const timeOut = defaultTimeOut ? defaultTimeOut.value : '';

                document.querySelectorAll('tbody tr').forEach(function(row) {
                    const shiftInput = row.querySelector('.plot-shift');
                    const graceInput = row.querySelector('.plot-grace');
                    const statusInput = row.querySelector('.plot-status');
                    const dayOffInput = row.querySelector('.plot-day-off');
                    const timeInInput = row.querySelector('.plot-time-in');
                    const timeOutInput = row.querySelector('.plot-time-out');

                    if (shiftInput) shiftInput.value = shift;
                    if (graceInput) graceInput.value = grace;
                    if (statusInput) statusInput.value = status;
                    if (dayOffInput) dayOffInput.value = dayOff;
                    if (timeInInput) timeInInput.value = timeIn;
                    if (timeOutInput) timeOutInput.value = timeOut;
                });
            });
        });
    </script>
@endsection
