<div class="card border-0 shadow-sm overflow-hidden">
    <div class="card-header bg-body-tertiary border-bottom border-200 py-3">
        <div class="d-flex flex-column flex-xl-row gap-3 justify-content-between align-items-xl-center">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="fas fa-table text-primary"></span>
                    <h5 class="mb-0">Attendance Records</h5>
                </div>
                <p class="text-muted mb-0 fs-10">
                    Review each employee's daily attendance result. Payroll will use these summary records for
                    computation.
                </p>
            </div>

            <div class="text-xl-end">
                <div class="fw-semibold text-dark">{{ $summaries->total() }} record(s)</div>
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
                        <th class="text-nowrap">Shift</th>
                        <th class="text-nowrap">Schedule</th>
                        <th class="text-nowrap">Time In</th>
                        <th class="text-nowrap">Time Out</th>
                        <th class="text-center text-nowrap">Late</th>
                        <th class="text-center text-nowrap">UT</th>
                        <th class="text-center text-nowrap">Worked</th>
                        <th class="text-nowrap">Status</th>
                        <th class="text-nowrap">Day Type</th>
                        <th class="text-nowrap">Adjustment</th>
                        <th class="text-nowrap">Payable</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($summaries as $row)
                        @php
                            $statusClass = match ($row->attendance_status) {
                                'present', 'adjusted_present' => 'success',
                                'late', 'undertime', 'late_undertime' => 'warning',
                                'absent', 'incomplete_log' => 'danger',
                                'holiday', 'holiday_worked' => 'info',
                                'rest_day', 'rest_day_worked' => 'secondary',
                                'leave' => 'primary',
                                default => 'light',
                            };

                            $statusLabel = strtoupper(str_replace('_', ' ', $row->attendance_status ?? 'N/A'));
                            $scheduleStatusLabel = $row->schedule_status
                                ? strtoupper(str_replace('_', ' ', $row->schedule_status))
                                : 'NO STATUS';

                            $workedHours = ((int) $row->worked_minutes) / 60;
                            $isFlexible = str_contains(strtolower((string) $row->shift_name), 'flexible');
                        @endphp

                        <tr>
                            <td class="text-nowrap ps-3">
                                <div class="fw-semibold text-dark">
                                    {{ optional($row->work_date)->format('M d, Y') }}
                                </div>
                                <div class="text-muted fs-11">
                                    {{ optional($row->work_date)->format('l') }}
                                </div>
                            </td>

                            <td>
                                <div class="fw-semibold text-dark">{{ $row->employee_name }}</div>
                                <div class="text-muted fs-11">
                                    <strong>Emp No:</strong> {{ $row->employee_no ?: '—' }}
                                </div>
                                <div class="text-muted fs-11">
                                    <strong>Biometric ID:</strong> {{ $row->biometric_employee_id ?: '—' }}
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <div class="fw-semibold {{ $isFlexible ? 'text-info' : 'text-primary' }}">
                                    {{ $row->shift_name ?: '—' }}
                                </div>
                                <div class="text-muted fs-11">{{ $scheduleStatusLabel }}</div>
                            </td>

                            <td class="text-nowrap">
                                @if ($isFlexible)
                                    <div class="fw-semibold text-info">Flexible 9 hrs</div>
                                    <div class="text-muted fs-11">No fixed schedule</div>
                                @elseif ($row->scheduled_time_in || $row->scheduled_time_out)
                                    <div class="fw-semibold text-dark">
                                        {{ $row->scheduled_time_in ? \Carbon\Carbon::parse($row->scheduled_time_in)->format('h:i A') : '—' }}
                                        <span class="text-muted mx-1">to</span>
                                        {{ $row->scheduled_time_out ? \Carbon\Carbon::parse($row->scheduled_time_out)->format('h:i A') : '—' }}
                                    </div>
                                    <div class="text-muted fs-11">
                                        Grace Period: {{ (int) $row->grace_minutes }} min
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td class="text-nowrap">
                                @if ($row->actual_time_in)
                                    <div class="fw-semibold text-success">
                                        {{ $row->actual_time_in->format('h:i A') }}
                                    </div>
                                    <div class="text-muted fs-11">
                                        {{ $row->actual_time_in->format('M d, Y') }}
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td class="text-nowrap">
                                @if ($row->actual_time_out)
                                    <div class="fw-semibold text-primary">
                                        {{ $row->actual_time_out->format('h:i A') }}
                                    </div>
                                    <div class="text-muted fs-11">
                                        {{ $row->actual_time_out->format('M d, Y') }}
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td class="text-center">
                                @if ((int) $row->late_minutes > 0)
                                    <span class="badge badge-phoenix badge-phoenix-warning px-2 py-1">
                                        {{ (int) $row->late_minutes }} min
                                    </span>
                                @else
                                    <span class="text-muted">0 min</span>
                                @endif
                            </td>

                            <td class="text-center">
                                @if ((int) $row->undertime_minutes > 0)
                                    <span class="badge badge-phoenix badge-phoenix-warning px-2 py-1">
                                        {{ (int) $row->undertime_minutes }} min
                                    </span>
                                @else
                                    <span class="text-muted">0 min</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="fw-semibold text-dark">{{ (int) $row->worked_minutes }} min</div>
                                <div class="text-muted fs-11">
                                    {{ number_format($workedHours, 2) }} hr
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <span class="badge badge-phoenix badge-phoenix-{{ $statusClass }} px-3 py-2">
                                    {{ $statusLabel }}
                                </span>
                            </td>

                            <td class="text-nowrap">
                                @if ($row->is_holiday)
                                    <span class="badge badge-phoenix badge-phoenix-info px-2 py-1">
                                        HOLIDAY
                                    </span>
                                @elseif ($row->is_rest_day)
                                    <span class="badge badge-phoenix badge-phoenix-secondary px-2 py-1">
                                        REST DAY
                                    </span>
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
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td class="text-nowrap">
                                <div class="fw-semibold text-dark">
                                    {{ number_format((float) $row->payable_days, 2) }} day
                                </div>
                                <div class="text-muted fs-11">
                                    {{ number_format((float) $row->payable_hours, 2) }} hr
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center justify-content-center">
                                    <div class="avatar avatar-4xl mb-3">
                                        <div class="avatar-name rounded-circle bg-soft-secondary text-secondary">
                                            <span class="fas fa-folder-open fs-2"></span>
                                        </div>
                                    </div>
                                    <h5 class="mb-1">No attendance summary records found</h5>
                                    <p class="text-muted mb-0">
                                        Try changing the cutoff filter, rebuilding the summary, or checking if records
                                        exist for the selected period.
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
