<style>
    .employee-table {
        font-size: .78rem;
    }

    .employee-table th {
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        white-space: nowrap;
        color: #4d5969;
    }

    .employee-table td {
        vertical-align: middle;
        padding-top: .65rem;
        padding-bottom: .65rem;
    }

    .employee-avatar {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        background: var(--falcon-primary-bg-subtle, #e7f0ff);
        color: var(--falcon-primary, #2c7be5);
        flex-shrink: 0;
    }

    .employee-name {
        font-size: .82rem;
        font-weight: 700;
        color: #344050;
    }

    .employee-sub {
        font-size: .7rem;
        color: #748194;
    }

    .employee-contact-line {
        max-width: 210px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: .7rem;
        color: #5e6e82;
    }

    .employee-action-btn {
        padding: .2rem .42rem;
        font-size: .72rem;
        line-height: 1.2;
    }

    .employee-table tbody tr:hover {
        background: #f9fafd;
    }

    .employee-id-pill {
        font-size: .65rem;
        border-radius: 999px;
        padding: .18rem .45rem;
    }
</style>

@php
    $statusColors = [
        'Active' => 'success',
        'Active(Re-Entry)' => 'success',
        'Suspended' => 'warning',
        'Inactive' => 'secondary',
        'Terminated' => 'danger',
        'Terminated(due to AWOL)' => 'danger',
        'End of Contract' => 'danger',
        'Retrench' => 'danger',
        'Retired' => 'danger',
        'Resigned' => 'danger',
    ];

    $formatDate = function ($value) {
        if (blank($value)) {
            return '—';
        }

        try {
            return $value instanceof \Carbon\CarbonInterface
                ? $value->format('M d, Y')
                : \Illuminate\Support\Carbon::parse($value)->format('M d, Y');
        } catch (\Throwable $e) {
            return '—';
        }
    };

    $calculateAge = function ($value) {
        if (blank($value)) {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->age;
        } catch (\Throwable $e) {
            return null;
        }
    };

    $calculateTenure = function ($value) {
        if (blank($value)) {
            return '—';
        }

        try {
            $date = \Illuminate\Support\Carbon::parse($value);

            return $date->diffForHumans(null, true);
        } catch (\Throwable $e) {
            return '—';
        }
    };
@endphp

<div class="card-body p-0">
    <div class="table-responsive scrollbar">
        <table class="table table-hover employee-table mb-0 w-100">
            <thead class="bg-200 text-900">
                <tr>
                    <th style="width: 45px;">#</th>
                    <th>Employee</th>
                    <th>Position / Department</th>
                    <th>Company / Garage</th>
                    <th>Contact</th>
                    <th>Employment Details</th>
                    <th>Status</th>
                    <th class="text-center" style="width: 130px;">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($employees as $index => $employee)
                    @php
                        $rowNumber = $employees->firstItem() ? $employees->firstItem() + $index : $index + 1;

                        $fullName =
                            $employee->full_name ??
                            trim(
                                ($employee->first_name ?? '') .
                                    ' ' .
                                    ($employee->middle_name ?? '') .
                                    ' ' .
                                    ($employee->last_name ?? ''),
                            );

                        $fullName = trim($fullName) ?: 'Unnamed Employee';

                        $initials = collect(explode(' ', $fullName))
                            ->filter()
                            ->take(2)
                            ->map(fn($part) => strtoupper(substr($part, 0, 1)))
                            ->implode('');

                        $employeeNo =
                            $employee->employee_no ?? ($employee->employee_number ?? ($employee->permanent_id ?? null));

                        $positionTitle = optional($employee->position)->title ?? ($employee->position_title ?? '—');

                        $departmentName =
                            optional($employee->department)->name ??
                            (optional($employee->department)->title ?? ($employee->department_name ?? '—'));

                        $company = $employee->company ?? '—';
                        $garage = $employee->garage ?? '—';

                        $email = $employee->email ?? null;
                        $phone =
                            $employee->phone_number ??
                            ($employee->contact_number ?? ($employee->mobile_number ?? null));

                        $status = $employee->status ?? 'Active';
                        $badgeColor = $statusColors[$status] ?? 'secondary';

                        $hiredDate = $employee->date_hired ?? ($employee->hired_at ?? ($employee->hire_date ?? null));

                        $birthDate = $employee->birth_date ?? ($employee->date_of_birth ?? null);

                        $age = $calculateAge($birthDate);
                        $tenure = $calculateTenure($hiredDate);
                    @endphp

                    <tr>
                        <td class="text-600">
                            {{ $rowNumber }}
                        </td>

                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="employee-avatar">
                                    {{ $initials ?: 'E' }}
                                </div>

                                <div class="min-w-0">
                                    <div class="employee-name text-truncate" style="max-width: 220px;">
                                        {{ $fullName }}
                                    </div>

                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        @if ($employeeNo)
                                            <span class="badge badge-subtle-primary employee-id-pill">
                                                ID: {{ $employeeNo }}
                                            </span>
                                        @else
                                            <span class="badge badge-subtle-secondary employee-id-pill">
                                                No ID
                                            </span>
                                        @endif

                                        @if ($age)
                                            <span class="badge badge-subtle-info employee-id-pill">
                                                {{ $age }} yrs old
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="fw-semibold text-900">
                                {{ $positionTitle }}
                            </div>
                            <div class="employee-sub">
                                <span class="fas fa-sitemap me-1"></span>
                                {{ $departmentName }}
                            </div>
                        </td>

                        <td>
                            <div class="fw-semibold text-900">
                                <span class="fas fa-building text-primary me-1"></span>
                                {{ $company }}
                            </div>
                            <div class="employee-sub">
                                <span class="fas fa-warehouse me-1"></span>
                                {{ $garage }}
                            </div>
                        </td>

                        <td>
                            <div class="employee-contact-line">
                                <span class="fas fa-envelope text-primary me-1"></span>
                                {{ $email ?: 'No email' }}
                            </div>

                            <div class="employee-contact-line">
                                <span class="fas fa-phone text-success me-1"></span>
                                {{ $phone ?: 'No contact number' }}
                            </div>
                        </td>

                        <td>
                            <div class="employee-contact-line">
                                <span class="fas fa-calendar-check text-primary me-1"></span>
                                Hired: {{ $formatDate($hiredDate) }}
                            </div>

                            <div class="employee-contact-line">
                                <span class="fas fa-hourglass-half text-warning me-1"></span>
                                Tenure: {{ $tenure }}
                            </div>
                        </td>

                        <td>
                            <span class="badge badge-subtle-{{ $badgeColor }}">
                                <span class="fas fa-circle me-1" style="font-size:.45rem;"></span>
                                {{ $status }}
                            </span>
                        </td>

                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <a href="{{ route('employees.staff.show', $employee->id) }}"
                                    class="btn btn-sm btn-falcon-default employee-action-btn" data-bs-toggle="tooltip"
                                    title="View Employee">
                                    <span class="fas fa-eye"></span>
                                </a>

                                @if (\Illuminate\Support\Facades\Route::has('employees.staff.edit'))
                                    <a href="{{ route('employees.staff.edit', $employee->id) }}"
                                        class="btn btn-sm btn-outline-primary employee-action-btn"
                                        data-bs-toggle="tooltip" title="Edit Employee">
                                        <span class="fas fa-edit"></span>
                                    </a>
                                @endif

                                <form action="{{ route('employees.staff.destroy', $employee->id) }}" method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('Are you sure you want to delete this employee?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-sm btn-falcon-danger employee-action-btn"
                                        data-bs-toggle="tooltip" title="Delete Employee">
                                        <span class="fas fa-trash-alt"></span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="text-center py-5">
                                <div class="avatar avatar-4xl mx-auto mb-3">
                                    <div class="avatar-name rounded-circle bg-primary-subtle text-primary">
                                        <span class="fas fa-user-slash"></span>
                                    </div>
                                </div>

                                <h6 class="text-700 mb-1">No employees found.</h6>
                                <p class="fs--1 text-500 mb-3">
                                    Try adjusting your search or filter criteria.
                                </p>

                                <a href="{{ url()->current() }}" class="btn btn-sm btn-falcon-default">
                                    Clear Filters
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card-footer bg-body-tertiary border-top py-2">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 px-1">
        <small class="text-600">
            @if ($employees->total() > 0)
                Showing
                <strong>{{ $employees->firstItem() }}</strong>
                to
                <strong>{{ $employees->lastItem() }}</strong>
                of
                <strong>{{ number_format($employees->total()) }}</strong>
                employees
            @else
                Showing 0 employees
            @endif
        </small>

        <div>
            {{ $employees->appends(request()->query())->links('pagination.custom') }}
        </div>
    </div>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));

            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endonce
