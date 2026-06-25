<div class="card border-0 shadow-sm h-100">
    <div class="card-header bg-white border-0">
        <h5 class="fw-bold mb-1">
            Department Summary
        </h5>
        <p class="text-muted small mb-0">
            Total employees and active employees per department
        </p>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 report-table">
                <thead>
                    <tr>
                        <th>Department</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Active</th>
                        <th class="text-end">Other</th>
                        <th class="text-end">Active Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($departmentSummary as $department)
                        @php
                            $activeRate =
                                $department->total_employees > 0
                                    ? round(($department->active_employees / $department->total_employees) * 100)
                                    : 0;
                        @endphp

                        <tr>
                            <td class="fw-semibold">
                                {{ $department->name }}
                            </td>
                            <td class="text-end">
                                {{ number_format($department->total_employees) }}
                            </td>
                            <td class="text-end text-success fw-semibold">
                                {{ number_format($department->active_employees) }}
                            </td>
                            <td class="text-end text-warning fw-semibold">
                                {{ number_format($department->other_status_employees) }}
                            </td>
                            <td class="text-end">
                                <span class="badge bg-primary-subtle text-primary compact-badge">
                                    {{ $activeRate }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No department data found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
