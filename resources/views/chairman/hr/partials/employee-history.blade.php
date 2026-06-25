<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0">
        <h5 class="fw-bold mb-1">
            Employee History / Violation Report
        </h5>
        <p class="text-muted small mb-0">
            Employees with recorded history, violations, or disciplinary records
        </p>
    </div>

    <div class="card-body p-0 employee-history-box">
        <div class="table-responsive">
            <table class="table table-hover report-table mb-0">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th class="text-end">History Count</th>
                        <th>Latest IR</th>
                        <th>Latest History</th>
                        <th>Action / Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employeeHistoryReport as $employee)
                        @php
                            $latestHistory = $employee->latestHistory;
                        @endphp

                        <tr>
                            <td>
                                {{ $employee->employee_id ?? ($employee->employee_id_permanent ?? 'N/A') }}
                            </td>
                            <td class="fw-semibold">
                                {{ $employee->full_name }}
                            </td>
                            <td>
                                {{ $employee->department?->name ?? 'N/A' }}
                            </td>
                            <td>
                                {{ $employee->position?->title ?? 'N/A' }}
                            </td>
                            <td class="text-end">
                                <span class="badge bg-danger-subtle text-danger compact-badge">
                                    {{ number_format($employee->histories_count) }}
                                </span>
                            </td>
                            <td>
                                {{ $latestHistory?->ir_number ?? 'N/A' }}
                            </td>
                            <td>
                                <div class="fw-semibold">
                                    {{ $latestHistory?->title ?? 'N/A' }}
                                </div>

                                @if ($latestHistory?->offense)
                                    <div class="text-muted small">
                                        Offense:
                                        {{ $latestHistory->offense->name ?? ($latestHistory->offense->title ?? 'Recorded offense') }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="small">
                                    {{ $latestHistory?->remarks ?? ($latestHistory?->description ?? 'No remarks') }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                No employee history or violation records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
