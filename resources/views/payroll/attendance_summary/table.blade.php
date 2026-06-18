<div class="card border-0 shadow-sm overflow-hidden">
    <div class="card-header bg-body-tertiary border-bottom border-200 py-3">
        <div class="d-flex flex-column flex-xl-row gap-3 justify-content-between align-items-xl-center">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="fas fa-table text-primary"></span>
                    <h5 class="mb-0">Daily Payroll Attendance Records</h5>
                </div>
                <p class="text-muted mb-0 fs-10">
                    Review schedule, biometrics, adjustment, holiday/rest day status, pay units, and payroll remarks.
                </p>
            </div>

            <div class="text-xl-end">
                <div class="fw-semibold text-dark">{{ number_format($summaries->total()) }} record(s)</div>
                <small class="text-muted">{{ $cutoffLabel }}</small>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive scrollbar">
            <table class="table table-hover table-sm align-middle mb-0 fs-10">
                <thead class="bg-200 text-900">
                    <tr>
                        <th class="text-nowrap ps-3">Date</th>
                        <th style="min-width: 230px;">Employee</th>
                        <th class="text-nowrap" style="min-width: 210px;">Plotted Schedule</th>
                        <th class="text-nowrap" style="min-width: 170px;">Biometrics</th>
                        <th class="text-center text-nowrap">Late</th>
                        <th class="text-center text-nowrap">UT</th>
                        <th class="text-center text-nowrap">Worked</th>
                        <th class="text-nowrap" style="min-width: 165px;">Payroll Status</th>
                        <th class="text-nowrap" style="min-width: 165px;">Day / Holiday</th>
                        <th class="text-nowrap" style="min-width: 150px;">Adjustment</th>
                        <th class="text-nowrap" style="min-width: 135px;">Pay Units</th>
                        <th class="text-nowrap" style="min-width: 260px;">Audit Remarks</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($summaries as $row)
                        @php
                            $workDate = $row->work_date ? \Carbon\Carbon::parse($row->work_date) : null;
                            $actualIn = $row->actual_time_in ? \Carbon\Carbon::parse($row->actual_time_in) : null;
                            $actualOut = $row->actual_time_out ? \Carbon\Carbon::parse($row->actual_time_out) : null;

                            $statusClass = match ($row->attendance_status) {
                                'present', 'adjusted_present' => 'success',
                                'late', 'undertime', 'late_undertime', 'half_day' => 'warning',
                                'absent', 'incomplete_log', 'holiday_unpaid', 'no_schedule' => 'danger',
                                'holiday', 'holiday_worked' => 'info',
                                'rest_day', 'rest_day_worked' => 'secondary',
                                'leave' => 'primary',
                                default => 'light',
                            };

                            $statusIcon = match ($row->attendance_status) {
                                'present', 'adjusted_present' => 'fa-check-circle',
                                'late', 'undertime', 'late_undertime', 'half_day' => 'fa-clock',
                                'absent',
                                'incomplete_log',
                                'holiday_unpaid',
                                'no_schedule'
                                    => 'fa-triangle-exclamation',
                                'holiday', 'holiday_worked' => 'fa-star',
                                'rest_day', 'rest_day_worked' => 'fa-bed',
                                'leave' => 'fa-calendar-check',
                                default => 'fa-circle-info',
                            };

                            $statusLabel = strtoupper(str_replace('_', ' ', $row->attendance_status ?? 'N/A'));
                            $scheduleStatusLabel = $row->schedule_status
                                ? strtoupper(str_replace('_', ' ', $row->schedule_status))
                                : 'NO STATUS';

                            $workedHours = ((int) $row->worked_minutes) / 60;
                            $isFlexible = str_contains(strtolower((string) $row->shift_name), 'flexible');
                            $isNoSchedule =
                                ($row->attendance_status ?? '') === 'no_schedule' ||
                                strtolower((string) $row->shift_name) === 'no schedule';

                            $payableDays = (float) $row->payable_days;
                            $payableHours = (float) $row->payable_hours;

                            $payBadgeClass = $payableDays > 0 ? 'success' : 'danger';
                            $payLabel =
                                $payableDays > 1
                                    ? 'Premium Pay'
                                    : ($payableDays == 1.0
                                        ? 'Full Pay'
                                        : ($payableDays > 0
                                            ? 'Partial Pay'
                                            : 'No Pay'));

                            $holidayTypeText = $row->holiday_type
                                ? strtoupper(str_replace('_', ' ', $row->holiday_type))
                                : null;

                            $remarks = trim((string) $row->remarks);
                        @endphp

                        <tr
                            class="{{ in_array($row->attendance_status, ['holiday_unpaid', 'no_schedule', 'incomplete_log'], true) ? 'table-warning' : '' }}">
                            <td class="text-nowrap ps-3">
                                <div class="fw-semibold text-dark">
                                    {{ $workDate ? $workDate->format('M d, Y') : '—' }}
                                </div>
                                <div class="text-muted fs-11">
                                    {{ $workDate ? $workDate->format('l') : '—' }}
                                </div>
                            </td>

                            <td>
                                <div class="fw-semibold text-dark">{{ $row->employee_name ?: 'NO NAME' }}</div>
                                <div class="text-muted fs-11">
                                    <strong>Emp No:</strong> {{ $row->employee_no ?: '—' }}
                                </div>
                                <div class="text-muted fs-11">
                                    <strong>Biometric ID:</strong> {{ $row->biometric_employee_id ?: '—' }}
                                </div>
                            </td>

                            <td class="text-nowrap">
                                @if ($isNoSchedule)
                                    <div class="fw-bold text-danger">
                                        <span class="fas fa-calendar-times me-1"></span>
                                        No Plotted Schedule
                                    </div>
                                    <div class="text-muted fs-11">Please fix plotting before payroll</div>
                                @elseif ($isFlexible)
                                    <div class="fw-semibold text-info">
                                        <span class="fas fa-stopwatch me-1"></span>
                                        {{ $row->shift_name ?: 'Flexible Shift' }}
                                    </div>
                                    <div class="text-muted fs-11">Required: 9 worked hours</div>
                                @elseif ($row->scheduled_time_in || $row->scheduled_time_out)
                                    <div class="fw-semibold text-dark">
                                        {{ $row->scheduled_time_in ? \Carbon\Carbon::parse($row->scheduled_time_in)->format('h:i A') : '—' }}
                                        <span class="text-muted mx-1">to</span>
                                        {{ $row->scheduled_time_out ? \Carbon\Carbon::parse($row->scheduled_time_out)->format('h:i A') : '—' }}
                                    </div>
                                    <div class="text-muted fs-11">
                                        {{ $row->shift_name ?: 'Regular Shift' }} |
                                        Grace: {{ (int) $row->grace_minutes }} min
                                    </div>
                                @else
                                    <div class="fw-semibold text-muted">{{ $row->shift_name ?: '—' }}</div>
                                    <div class="text-muted fs-11">{{ $scheduleStatusLabel }}</div>
                                @endif

                                <div class="mt-1">
                                    <span class="badge badge-phoenix badge-phoenix-secondary px-2 py-1">
                                        {{ $scheduleStatusLabel }}
                                    </span>
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <div class="d-flex flex-column gap-1">
                                    <div>
                                        <span class="text-muted">In:</span>
                                        @if ($actualIn)
                                            <span
                                                class="fw-semibold text-success">{{ $actualIn->format('h:i A') }}</span>
                                            <span class="text-muted fs-11">{{ $actualIn->format('M d') }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </div>

                                    <div>
                                        <span class="text-muted">Out:</span>
                                        @if ($actualOut)
                                            <span
                                                class="fw-semibold text-primary">{{ $actualOut->format('h:i A') }}</span>
                                            <span class="text-muted fs-11">{{ $actualOut->format('M d') }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="text-center">
                                @if ((int) $row->late_minutes > 0)
                                    <span class="badge badge-phoenix badge-phoenix-warning px-2 py-1">
                                        {{ (int) $row->late_minutes }} min
                                    </span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>

                            <td class="text-center">
                                @if ((int) $row->undertime_minutes > 0)
                                    <span class="badge badge-phoenix badge-phoenix-warning px-2 py-1">
                                        {{ (int) $row->undertime_minutes }} min
                                    </span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="fw-semibold text-dark">{{ (int) $row->worked_minutes }} min</div>
                                <div class="text-muted fs-11">{{ number_format($workedHours, 2) }} hr</div>
                            </td>

                            <td class="text-nowrap">
                                <span class="badge badge-phoenix badge-phoenix-{{ $statusClass }} px-3 py-2">
                                    <span class="fas {{ $statusIcon }} me-1"></span>
                                    {{ $statusLabel }}
                                </span>

                                @if (in_array($row->attendance_status, ['holiday_unpaid', 'no_schedule', 'incomplete_log'], true))
                                    <div class="text-danger fs-11 fw-semibold mt-1">
                                        Must check before payroll
                                    </div>
                                @endif
                            </td>

                            <td class="text-nowrap">
                                @if ($row->is_holiday)
                                    <div>
                                        <span class="badge badge-phoenix badge-phoenix-info px-2 py-1">
                                            HOLIDAY
                                        </span>
                                    </div>

                                    <div class="fw-semibold text-dark mt-1">
                                        {{ $row->holiday_name ?: 'Holiday' }}
                                    </div>

                                    <div class="text-muted fs-11">
                                        {{ $holidayTypeText ?: 'Type not set' }}
                                    </div>
                                @elseif ($row->is_rest_day)
                                    <span class="badge badge-phoenix badge-phoenix-secondary px-2 py-1">
                                        REST DAY / DAY OFF
                                    </span>
                                    <div class="text-success fs-11 fw-semibold mt-1">
                                        100% paid
                                    </div>
                                @elseif ($row->is_leave)
                                    <span class="badge badge-phoenix badge-phoenix-primary px-2 py-1">
                                        LEAVE
                                    </span>
                                @else
                                    <span class="text-muted">Regular Day</span>
                                @endif
                            </td>

                            <td>
                                @if ($row->has_adjustment)
                                    <span class="badge badge-phoenix badge-phoenix-primary px-2 py-1">
                                        ADJUSTED
                                    </span>
                                    <div class="text-muted fs-11 mt-1">
                                        {{ $row->adjustment_type ? strtoupper(str_replace('_', ' ', $row->adjustment_type)) : 'Manual Adjustment' }}
                                    </div>
                                    @if ($row->adjustment_remarks)
                                        <div class="text-muted fs-11">
                                            {{ \Illuminate\Support\Str::limit($row->adjustment_remarks, 60) }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td class="text-nowrap">
                                <span class="badge badge-phoenix badge-phoenix-{{ $payBadgeClass }} px-3 py-2">
                                    {{ $payLabel }}
                                </span>

                                <div class="fw-semibold text-dark mt-1">
                                    {{ number_format($payableDays, 2) }} unit(s)
                                </div>

                                <div class="text-muted fs-11">
                                    {{ number_format($payableHours, 2) }} hr
                                </div>
                            </td>

                            <td>
                                @if ($remarks !== '')
                                    <div class="text-700 fs-10">
                                        {{ \Illuminate\Support\Str::limit($remarks, 180) }}
                                    </div>
                                @else
                                    <span class="text-muted">No remarks</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center justify-content-center">
                                    <div class="avatar avatar-4xl mb-3">
                                        <div class="avatar-name rounded-circle bg-soft-secondary text-secondary">
                                            <span class="fas fa-folder-open fs-2"></span>
                                        </div>
                                    </div>
                                    <h5 class="mb-1">No attendance summary records found</h5>
                                    <p class="text-muted mb-0">
                                        Try changing the cutoff filter, rebuilding the summary, or checking if plotted
                                        schedules exist.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($summaries->hasPages())
        <div class="card-footer bg-body-tertiary border-top border-200 py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <small class="text-muted">
                    Showing {{ $summaries->firstItem() }} to {{ $summaries->lastItem() }}
                    of {{ $summaries->total() }} records
                </small>

                <div>
                    {{ $summaries->links('pagination.custom') }}
                </div>
            </div>
        </div>
    @endif
</div>
