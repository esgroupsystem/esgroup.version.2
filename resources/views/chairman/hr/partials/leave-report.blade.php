<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-2">
            <div>
                <h5 class="fw-bold mb-1">
                    Employee Leave Report
                </h5>
                <p class="text-muted small mb-0">
                    Admin, driver, and conductor leave summary for {{ $year }}
                </p>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="row g-3">
            @foreach ($leaveReports as $leaveReport)
                <div class="col-xl-4">
                    <div class="border rounded-4 p-3 h-100 bg-white">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <h6 class="fw-bold mb-1">
                                    {{ $leaveReport['label'] }}
                                </h6>
                                <p class="text-muted small mb-0">
                                    Total leave records: {{ number_format($leaveReport['total']) }}
                                </p>
                            </div>

                            <span class="badge bg-info-subtle text-info compact-badge">
                                {{ number_format($leaveReport['total_days']) }} days
                            </span>
                        </div>

                        <div class="mb-3">
                            <p class="small text-muted fw-semibold mb-2">
                                By Status
                            </p>

                            @forelse ($leaveReport['status_breakdown'] as $status)
                                <div class="d-flex justify-content-between small border-bottom py-1">
                                    <span>{{ $status->label }}</span>
                                    <span class="fw-semibold">
                                        {{ number_format($status->total) }}
                                    </span>
                                </div>
                            @empty
                                <p class="text-muted small mb-0">
                                    No leave status data.
                                </p>
                            @endforelse
                        </div>

                        <div>
                            <p class="small text-muted fw-semibold mb-2">
                                By Leave Type
                            </p>

                            @forelse ($leaveReport['type_breakdown']->take(5) as $type)
                                <div class="d-flex justify-content-between small border-bottom py-1">
                                    <span>{{ $type->label }}</span>
                                    <span class="fw-semibold">
                                        {{ number_format($type->total) }}
                                    </span>
                                </div>
                            @empty
                                <p class="text-muted small mb-0">
                                    No leave type data.
                                </p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <hr class="my-4">

        <h6 class="fw-bold mb-3">
            Recent Leave Records
        </h6>

        <div class="table-responsive">
            <table class="table table-hover report-table mb-0">
                <thead>
                    <tr>
                        <th>Group</th>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Leave Type</th>
                        <th>Date</th>
                        <th class="text-end">Days</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($leaveReports as $leaveReport)
                        @foreach ($leaveReport['recent'] as $leave)
                            <tr>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary compact-badge">
                                        {{ $leaveReport['label'] }}
                                    </span>
                                </td>
                                <td class="fw-semibold">
                                    {{ $leave->employee?->full_name ?? 'No employee record' }}
                                </td>
                                <td>
                                    {{ $leave->employee?->department?->name ?? 'N/A' }}
                                </td>
                                <td>
                                    {{ $leave->leave_type ?? 'N/A' }}
                                </td>
                                <td>
                                    {{ optional($leave->start_date)->format('M d, Y') }}
                                    -
                                    {{ optional($leave->end_date)->format('M d, Y') }}
                                </td>
                                <td class="text-end fw-semibold">
                                    {{ number_format($leave->days ?? 0) }}
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary compact-badge">
                                        {{ $leave->status ?? 'Unknown' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach

                    @if ($leaveReports->sum(fn($report) => $report['recent']->count()) === 0)
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No recent leave records found.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
