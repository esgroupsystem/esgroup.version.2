@extends('layouts.app')
@section('title', 'Create Employee Leave')

@section('content')
    <div class="container" data-layout="container">
        <div class="content employee-leave-form-page">

            <div class="card mb-4 overflow-hidden">
                <div class="bg-holder d-none d-lg-block bg-card"
                    style="background-image:url(/assets/img/icons/spot-illustrations/corner-4.png);">
                </div>

                <div class="card-body position-relative">
                    <div class="row align-items-center g-3">
                        <div class="col-lg-8">
                            <h3 class="mb-1">Create Employee Leave</h3>
                            <p class="text-700 mb-0">
                                Record admin/regular employee leave details with garage visibility and notice workflow
                                tracking.
                            </p>
                        </div>

                        <div class="col-lg-4 text-lg-end">
                            <a href="{{ route('employee-leave.employee.index') }}" class="btn btn-falcon-default">
                                <span class="fas fa-arrow-left me-1"></span>
                                Back to Records
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm">
                    <h6 class="alert-heading">Please fix the following:</h6>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('employee-leave.employee.store') }}" method="POST">
                @csrf

                <div class="row g-4">
                    <div class="col-xl-8">
                        <div class="card">
                            <div class="card-header bg-body-tertiary">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <h5 class="mb-0">Leave Information</h5>
                                        <p class="mb-0 text-600 small">
                                            Select the employee and encode leave period details.
                                        </p>
                                    </div>

                                    <div>
                                        <span class="badge rounded-pill badge-subtle-primary text-primary">
                                            New Leave Record
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="employee_id" class="form-label fw-semi-bold">
                                            Employee <span class="text-danger">*</span>
                                        </label>

                                        <select name="employee_id" id="employee_id"
                                            class="form-select @error('employee_id') is-invalid @enderror" required>
                                            <option value="">Select Employee</option>

                                            @foreach ($employees as $employee)
                                                <option value="{{ $employee->id }}" data-name="{{ $employee->full_name }}"
                                                    data-position="{{ $employee->position?->title ?? 'No position' }}"
                                                    data-garage="{{ $employee->garage ?? 'No Garage Assigned' }}"
                                                    data-company="{{ $employee->company ?? 'No Company' }}"
                                                    data-status="{{ $employee->status ?? 'No Status' }}"
                                                    data-employee-no="{{ $employee->employee_id_permanent ?? $employee->employee_id }}"
                                                    {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                                    {{ $employee->full_name }}
                                                    | {{ $employee->garage ?? 'No Garage' }}
                                                    | {{ $employee->position?->title ?? 'Employee' }}
                                                </option>
                                            @endforeach
                                        </select>

                                        @error('employee_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                        <div class="form-text">
                                            Driver and Conductor positions are excluded from this module.
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semi-bold">
                                            Leave Type <span class="text-danger">*</span>
                                        </label>

                                        <select name="leave_type"
                                            class="form-select @error('leave_type') is-invalid @enderror" required>
                                            @foreach (['Medical Leave', 'Emergency Leave', 'Vacation Leave', 'Others'] as $type)
                                                <option value="{{ $type }}"
                                                    {{ old('leave_type') === $type ? 'selected' : '' }}>
                                                    {{ $type }}
                                                </option>
                                            @endforeach
                                        </select>

                                        @error('leave_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label fw-semi-bold">
                                            Start Date <span class="text-danger">*</span>
                                        </label>

                                        <input type="date" name="start_date"
                                            class="form-control @error('start_date') is-invalid @enderror"
                                            value="{{ old('start_date') }}" required>

                                        @error('start_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label fw-semi-bold">
                                            End Date <span class="text-danger">*</span>
                                        </label>

                                        <input type="date" name="end_date"
                                            class="form-control @error('end_date') is-invalid @enderror"
                                            value="{{ old('end_date') }}" required>

                                        @error('end_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-semi-bold">Reason / Remarks</label>

                                        <textarea name="reason" class="form-control @error('reason') is-invalid @enderror" rows="4"
                                            placeholder="Enter leave reason, supporting details, or HR note.">{{ old('reason') }}</textarea>

                                        @error('reason')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer bg-body-tertiary text-end">
                                <a href="{{ route('employee-leave.employee.index') }}" class="btn btn-falcon-default me-2">
                                    Cancel
                                </a>

                                <button type="submit" class="btn btn-primary">
                                    <span class="fas fa-save me-1"></span>
                                    Save Leave
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card mb-3">
                            <div class="card-header bg-body-tertiary">
                                <h5 class="mb-0">Selected Employee Details</h5>
                            </div>

                            <div class="card-body">
                                <div class="employee-preview-empty text-center py-4" id="employeePreviewEmpty">
                                    <div class="preview-icon mb-2">
                                        <span class="fas fa-user"></span>
                                    </div>
                                    <p class="text-600 mb-0">
                                        Select an employee to view garage and employee details.
                                    </p>
                                </div>

                                <div id="employeePreview" class="d-none">
                                    <h5 id="previewName" class="mb-1">-</h5>
                                    <div class="text-600 small mb-3" id="previewEmployeeNo">-</div>

                                    <div class="detail-row">
                                        <span>Position</span>
                                        <strong id="previewPosition">-</strong>
                                    </div>

                                    <div class="detail-row">
                                        <span>Garage</span>
                                        <strong id="previewGarage">-</strong>
                                    </div>

                                    <div class="detail-row">
                                        <span>Company</span>
                                        <strong id="previewCompany">-</strong>
                                    </div>

                                    <div class="detail-row">
                                        <span>Status</span>
                                        <strong id="previewStatus">-</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header bg-body-tertiary">
                                <h5 class="mb-0">Automatic Rule</h5>
                            </div>

                            <div class="card-body">
                                <div class="alert alert-warning small mb-0">
                                    When the record reaches <strong>2nd Notice</strong>, the system automatically sets
                                    the leave record and employee record to <strong>Inactive</strong>.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.addEventListener('load', function() {
            const employeeSelect = document.getElementById('employee_id');

            function updateEmployeePreview() {
                const selected = employeeSelect.options[employeeSelect.selectedIndex];

                const preview = document.getElementById('employeePreview');
                const empty = document.getElementById('employeePreviewEmpty');

                if (!selected || !selected.value) {
                    preview.classList.add('d-none');
                    empty.classList.remove('d-none');
                    return;
                }

                document.getElementById('previewName').innerText = selected.dataset.name || '-';
                document.getElementById('previewEmployeeNo').innerText = selected.dataset.employeeNo ||
                    'No Employee ID';
                document.getElementById('previewPosition').innerText = selected.dataset.position || '-';
                document.getElementById('previewGarage').innerText = selected.dataset.garage || '-';
                document.getElementById('previewCompany').innerText = selected.dataset.company || '-';
                document.getElementById('previewStatus').innerText = selected.dataset.status || '-';

                empty.classList.add('d-none');
                preview.classList.remove('d-none');
            }

            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
                const select = $('#employee_id');

                if (select.hasClass('select2-hidden-accessible')) {
                    select.select2('destroy');
                }

                select.select2({
                    width: '100%',
                    placeholder: 'Search employee by name, garage, or position...',
                    allowClear: true,
                    matcher: function(params, data) {
                        if ($.trim(params.term) === '') {
                            return data;
                        }

                        if (!data.text) {
                            return null;
                        }

                        const term = params.term.toLowerCase().replace(/[^a-z0-9]/g, '');
                        const text = data.text.toLowerCase().replace(/[^a-z0-9]/g, '');

                        return text.includes(term) ? data : null;
                    }
                });

                select.on('change', updateEmployeePreview);
            } else {
                employeeSelect.addEventListener('change', updateEmployeePreview);
            }

            updateEmployeePreview();
        });
    </script>
@endpush

@push('styles')
    <style>
        .employee-leave-form-page .preview-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            display: inline-grid;
            place-items: center;
            background: var(--falcon-gray-100, #f9fafd);
            color: var(--falcon-gray-500, #9da9bb);
            font-size: 1.25rem;
        }

        .employee-leave-form-page .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            border-bottom: 1px solid var(--falcon-gray-200, #edf2f9);
            padding: .75rem 0;
        }

        .employee-leave-form-page .detail-row:last-child {
            border-bottom: 0;
        }

        .employee-leave-form-page .detail-row span {
            color: var(--falcon-gray-600, #748194);
        }
    </style>
@endpush
