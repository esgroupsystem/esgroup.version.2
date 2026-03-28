<form method="POST" action="{{ route('payroll-plotting.save-monthly') }}">
    @csrf
    <input type="hidden" name="cutoff_month" value="{{ $cutoffMonth }}">
    <input type="hidden" name="cutoff_year" value="{{ $cutoffYear }}">
    <input type="hidden" name="cutoff_type" value="{{ $cutoffType }}">
    <input type="hidden" name="search" value="{{ request('search') }}">
    <input type="hidden" name="page" value="{{ $employees->currentPage() }}">

    <div class="card-body p-0">
        <div class="table-responsive scrollbar">
            <table class="table table-bordered table-sm mb-0 align-middle plotting-table">
                <thead class="bg-light text-center">
                    <tr>
                        <th class="sticky-col sticky-head bg-light employee-col">Employee</th>
                        @foreach ($days as $day)
                            <th
                                class="day-col {{ $day['is_sunday'] ? 'bg-danger-subtle' : ($day['is_saturday'] ? 'bg-warning-subtle' : '') }}">
                                <div class="fw-semibold">{{ $day['month_short'] }} {{ $day['day'] }}</div>
                                <div class="fs-11 text-muted">{{ $day['dow_short'] }}</div>
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @forelse($employees as $rowIndex => $employee)
                        @php
                            $employeeIdentity = $employee->biometric_employee_id ?: $employee->employee_no;
                        @endphp

                        <tr>
                            <td class="sticky-col bg-white employee-col">
                                <div class="fw-semibold text-dark">{{ $employee->employee_name }}</div>
                                <div class="fs-11 text-muted">
                                    {{ $employee->employee_no ?: 'No Employee No.' }}
                                </div>
                                <div class="fs-11 text-muted">
                                    Bio ID: {{ $employee->biometric_employee_id ?: '-' }}
                                </div>

                                <input type="hidden" name="schedule[{{ $rowIndex }}][crosschex_id]"
                                    value="{{ $employee->crosschex_id }}">
                                <input type="hidden" name="schedule[{{ $rowIndex }}][biometric_employee_id]"
                                    value="{{ $employee->biometric_employee_id }}">
                                <input type="hidden" name="schedule[{{ $rowIndex }}][employee_no]"
                                    value="{{ $employee->employee_no }}">
                                <input type="hidden" name="schedule[{{ $rowIndex }}][employee_name]"
                                    value="{{ $employee->employee_name }}">
                            </td>

                            @foreach ($days as $dayIndex => $day)
                                @php
                                    $groupKey = $employeeIdentity . '_' . $day['date'];
                                    $existing = optional($schedules->get($groupKey))->first();

                                    $cellStatus = old(
                                        "schedule.$rowIndex.days.$dayIndex.status",
                                        $existing->status ?? '',
                                    );
                                    $cellShift = old(
                                        "schedule.$rowIndex.days.$dayIndex.shift_name",
                                        $existing->shift_name ?? '',
                                    );
                                    $cellTimeIn = old(
                                        "schedule.$rowIndex.days.$dayIndex.time_in",
                                        !empty($existing?->time_in)
                                            ? \Illuminate\Support\Carbon::parse($existing->time_in)->format('H:i')
                                            : '',
                                    );

                                    $cellTimeOut = old(
                                        "schedule.$rowIndex.days.$dayIndex.time_out",
                                        !empty($existing?->time_out)
                                            ? \Illuminate\Support\Carbon::parse($existing->time_out)->format('H:i')
                                            : '',
                                    );
                                    $cellGrace = old(
                                        "schedule.$rowIndex.days.$dayIndex.grace_minutes",
                                        $existing->grace_minutes ?? 15,
                                    );
                                    $cellRemarks = old(
                                        "schedule.$rowIndex.days.$dayIndex.remarks",
                                        $existing->remarks ?? '',
                                    );

                                    $cellClass = '';
                                    if ($cellStatus === 'scheduled') {
                                        $cellClass = 'plot-scheduled';
                                    }
                                    if ($cellStatus === 'rest_day') {
                                        $cellClass = 'plot-rest-day';
                                    }
                                    if ($cellStatus === 'leave') {
                                        $cellClass = 'plot-leave';
                                    }
                                    if ($cellStatus === 'holiday') {
                                        $cellClass = 'plot-holiday';
                                    }
                                @endphp

                                <td class="plot-cell {{ $cellClass }}">
                                    <input type="hidden"
                                        name="schedule[{{ $rowIndex }}][days][{{ $dayIndex }}][work_date]"
                                        value="{{ $day['date'] }}">

                                    <div class="plot-mini-card">
                                        <select
                                            name="schedule[{{ $rowIndex }}][days][{{ $dayIndex }}][status]"
                                            class="form-select form-select-sm mb-1 plot-status">
                                            <option value="">-</option>
                                            <option value="scheduled"
                                                {{ $cellStatus === 'scheduled' ? 'selected' : '' }}>
                                                Scheduled
                                            </option>
                                            <option value="rest_day"
                                                {{ $cellStatus === 'rest_day' ? 'selected' : '' }}>
                                                Rest Day
                                            </option>
                                            <option value="leave" {{ $cellStatus === 'leave' ? 'selected' : '' }}>
                                                Leave
                                            </option>
                                            <option value="holiday" {{ $cellStatus === 'holiday' ? 'selected' : '' }}>
                                                Holiday
                                            </option>
                                        </select>

                                        <input type="text"
                                            name="schedule[{{ $rowIndex }}][days][{{ $dayIndex }}][shift_name]"
                                            class="form-control form-control-sm mb-1" placeholder="Shift"
                                            value="{{ $cellShift }}">

                                        <input type="time"
                                            name="schedule[{{ $rowIndex }}][days][{{ $dayIndex }}][time_in]"
                                            class="form-control form-control-sm mb-1" value="{{ $cellTimeIn }}">

                                        <input type="time"
                                            name="schedule[{{ $rowIndex }}][days][{{ $dayIndex }}][time_out]"
                                            class="form-control form-control-sm mb-1" value="{{ $cellTimeOut }}">

                                        <input type="number" min="0"
                                            name="schedule[{{ $rowIndex }}][days][{{ $dayIndex }}][grace_minutes]"
                                            class="form-control form-control-sm mb-1" placeholder="Grace"
                                            value="{{ $cellGrace }}">

                                        <input type="text"
                                            name="schedule[{{ $rowIndex }}][days][{{ $dayIndex }}][remarks]"
                                            class="form-control form-control-sm" placeholder="Remarks"
                                            value="{{ $cellRemarks }}">
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($days) + 1 }}" class="text-center py-4 text-muted">
                                @if (blank(request('search')))
                                    Please search an employee first before plotting schedule.
                                @else
                                    No employee found matching your search.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="text-muted fs-10">
            Coverage: {{ $startDate->format('F d, Y') }} to {{ $endDate->format('F d, Y') }}
            <br>
            Showing {{ $employees->firstItem() ?? 0 }} to {{ $employees->lastItem() ?? 0 }}
            of {{ $employees->total() }} employee(s)
        </div>

        <button type="submit" class="btn btn-primary" {{ $employees->count() === 0 ? 'disabled' : '' }}>
            <span class="fas fa-save me-1"></span> Save Cutoff Plotting
        </button>
    </div>

    @if ($employees->hasPages())
        <div class="px-3 pb-3 pt-2">
            {{ $employees->links('pagination.custom') }}
        </div>
    @endif
</form>
