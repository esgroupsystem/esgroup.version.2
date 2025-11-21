<div class="table-responsive scrollbar">
    <table class="table table-hover table-striped fs-10 mb-0 w-100">
        <thead class="bg-200 text-900">
            <tr>
                <th>Employee ID</th>
                <th>Full Name</th>
                <th>Department</th>
                <th>Position</th>
                <th>Company</th>
                <th>Garage</th>
                <th>Status</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($employees as $employee)
                <tr>
                    <td>{{ $employee->employee_id }}</td>
                    <td>{{ $employee->full_name }}</td>
                    <td>{{ $employee->department?->name ?? '-' }}</td>
                    <td>{{ $employee->position?->title ?? '-' }}</td>
                    <td>{{ $employee->company ?? '-' }}</td>
                    <td>{{ $employee->garage ?? '-' }}</td>
                    <td>
                        @php
                            $status = $employee->status;

                            $colors = [
                                'Active' => 'success',
                                'Suspended' => 'warning',
                                'Inactive' => 'secondary',
                                'Terminated' => 'danger',
                                'Retrench' => 'danger',
                                'Retired' => 'danger',
                                'Resigned' => 'danger',
                            ];

                            $badgeColor = $colors[$status] ?? 'secondary';
                        @endphp

                        <span class="badge bg-{{ $badgeColor }}">
                            {{ $status }}
                        </span>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('employees.staff.show', $employee->id) }}" class="btn btn-sm btn-info me-1">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-3 text-muted">No employees found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="my-3 d-flex justify-content-end px-3">
    {{ $employees->links('pagination.custom') }}
</div>
