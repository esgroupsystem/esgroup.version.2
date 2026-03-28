<div class="card border-0 shadow-sm">
    <div class="card-header bg-body-tertiary border-bottom border-200">
        <div class="d-flex flex-column flex-lg-row gap-2 justify-content-between align-items-lg-center">
            <div>
                <h5 class="mb-1">Attendance Records</h5>
                <p class="text-muted mb-0 fs-10">
                    Review the daily result per employee. Payroll should compute using these summary records.
                </p>
            </div>
            <div class="text-lg-end">
                <div class="fw-semibold">{{ $summaries->total() }} record(s)</div>
                <small class="text-muted">{{ $cutoffLabel }}</small>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive scrollbar">
            <table class="table table-sm align-middle mb-0 fs-10">
                <thead class="bg-200 text-900">
                    <tr>
                        <th class="text-nowrap">Date</th>
                        <th style="min-width: 240px;">Employee Details</th>
                        <th class="text-nowrap">Shift</th>
                        <th class="text-nowrap">Scheduled Time</th>
                        <th class="text-nowrap">Actual Time In</th>
                        <th class="text-nowrap">Actual Time Out</th>
                        <th class="text-center text-nowrap">Late</th>
                        <th class="text-center text-nowrap">UT</th>
                        <th class="text-center text-nowrap">Worked</th>
                        <th class="text-nowrap">Status</th>
                        <th class="text-center text-nowrap">Adjustment</th>
                        <th class="text-center text-nowrap">Holiday</th>
                        <th class="text-center text-nowrap">Payable</th>
                        <th style="min-width: 240px;">Remarks</th>
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
                        @endphp

                        <tr>
                            <td class="text-nowrap">
                                <div class="fw-semibold">{{ optional($row->work_date)->format('M d, Y') }}</div>
                                <div class="text-muted fs-11">
                                    {{ optional($row->work_date)->format('D') }}
                                </div>
                            </td>

                            <td>
                                <div class="fw-semibold text-dark">{{ $row->employee_name }}</div>
                                <div class="text-muted fs-11">Employee No: {{ $row->employee_no ?: '—' }}</div>
                                <div class="text-muted fs-11">Biometric ID: {{ $row->biometric_employee_id ?: '—' }}
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <div class="fw-semibold">{{ $row->shift_name ?: '—' }}</div>
                                <div class="text-muted fs-11">
                                    {{ $row->schedule_status ? strtoupper(str_replace('_', ' ', $row->schedule_status)) : 'NO STATUS' }}
                                </div>
                            </td>

                            <td class="text-nowrap">
                                @if ($row->scheduled_time_in || $row->scheduled_time_out)
                                    <div class="fw-semibold">
                                        {{ $row->scheduled_time_in ? \Carbon\Carbon::parse($row->scheduled_time_in)->format('h:i A') : '—' }}
                                        -
                                        {{ $row->scheduled_time_out ? \Carbon\Carbon::parse($row->scheduled_time_out)->format('h:i A') : '—' }}
                                    </div>
                                    <div class="text-muted fs-11">
                                        Grace: {{ (int) $row->grace_minutes }} min
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td class="text-nowrap">
                                @if ($row->actual_time_in)
                                    <div class="fw-semibold">{{ $row->actual_time_in->format('h:i A') }}</div>
                                    <div class="text-muted fs-11">{{ $row->actual_time_in->format('M d, Y') }}</div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td class="text-nowrap">
                                @if ($row->actual_time_out)
                                    <div class="fw-semibold">{{ $row->actual_time_out->format('h:i A') }}</div>
                                    <div class="text-muted fs-11">{{ $row->actual_time_out->format('M d, Y') }}</div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td class="text-center">
                                @if ((int) $row->late_minutes > 0)
                                    <span class="badge badge-phoenix badge-phoenix-warning">
                                        {{ (int) $row->late_minutes }} min
                                    </span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>

                            <td class="text-center">
                                @if ((int) $row->undertime_minutes > 0)
                                    <span class="badge badge-phoenix badge-phoenix-warning">
                                        {{ (int) $row->undertime_minutes }} min
                                    </span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="fw-semibold">{{ (int) $row->worked_minutes }} min</div>
                                <div class="text-muted fs-11">
                                    {{ number_format(((int) $row->worked_minutes) / 60, 2) }} hr
                                </div>
                            </td>

                            <td class="text-nowrap">
                                <span class="badge badge-phoenix badge-phoenix-{{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>

                            <td class="text-center">
                                @if ($row->has_adjustment)
                                    <div>
                                        <span class="badge badge-phoenix badge-phoenix-primary">YES</span>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        {{ $row->adjustment_type ?: 'Adjusted' }}
                                    </small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td class="text-center">
                                @if ($row->is_holiday)
                                    <span class="badge badge-phoenix badge-phoenix-info">
                                        {{ $row->holiday_type ?: 'HOLIDAY' }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="fw-semibold">{{ number_format((float) $row->payable_days, 2) }} day</div>
                                <div class="text-muted fs-11">{{ number_format((float) $row->payable_hours, 2) }} hr
                                </div>
                            </td>

                            <td>
                                <div class="text-wrap">
                                    {{ $row->remarks ?: '—' }}
                                </div>

                                @if ($row->schedule_remarks)
                                    <div class="mt-1 text-muted fs-11">
                                        <strong>Schedule Note:</strong> {{ $row->schedule_remarks }}
                                    </div>
                                @endif

                                @if ($row->adjustment_remarks)
                                    <div class="mt-1 text-primary fs-11">
                                        <strong>Adjustment Note:</strong> {{ $row->adjustment_remarks }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center">
                                    <span class="fas fa-folder-open text-400 fs-1 mb-3"></span>
                                    <h6 class="mb-1">No attendance summary records found</h6>
                                    <p class="text-muted mb-0">
                                        Try changing the cutoff filter or rebuild the summary for this cutoff.
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
        <div class="card-footer bg-body-tertiary border-top border-200">
            <div class="d-flex justify-content-end">
                {{ $summaries->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>
