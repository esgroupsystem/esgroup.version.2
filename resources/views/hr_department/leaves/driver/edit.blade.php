@extends('layouts.app')
@section('title', 'Edit Driver Leave')

@section('content')
    <div class="container" data-layout="container">
        <div class="content driver-leave-form-page">

            <div class="card mb-4 overflow-hidden">
                <div class="bg-holder d-none d-lg-block bg-card"
                    style="background-image:url(/assets/img/icons/spot-illustrations/corner-4.png);">
                </div>

                <div class="card-body position-relative">
                    <div class="row align-items-center g-3">
                        <div class="col-lg-8">
                            <h3 class="mb-1">Edit Driver Leave</h3>
                            <p class="text-700 mb-0">
                                Update driver leave details, garage assignment visibility, and HR notes.
                            </p>
                        </div>

                        <div class="col-lg-4 text-lg-end">
                            <a href="{{ route('driver-leave.driver.index') }}" class="btn btn-falcon-default">
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

            <form action="{{ route('driver-leave.driver.update', $leave) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    <div class="col-xl-8">
                        <div class="card">
                            <div class="card-header bg-body-tertiary">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <h5 class="mb-0">Leave Information</h5>
                                        <p class="mb-0 text-600 small">
                                            Review and update the selected leave record.
                                        </p>
                                    </div>

                                    <div>
                                        {!! $leave->record_status_badge ??
                                            '<span class="badge rounded-pill badge-subtle-primary text-primary">Active</span>' !!}
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label for="employee_id" class="form-label fw-semi-bold">
                                            Driver <span class="text-danger">*</span>
                                        </label>

                                        <select name="employee_id" id="employee_id"
                                            class="form-select @error('employee_id') is-invalid @enderror" required>
                                            <option value="">Select Driver</option>

                                            @foreach ($drivers as $driver)
                                                <option value="{{ $driver->id }}" data-name="{{ $driver->full_name }}"
                                                    data-position="{{ $driver->position?->title ?? 'No position' }}"
                                                    data-garage="{{ $driver->garage ?? 'No Garage Assigned' }}"
                                                    data-company="{{ $driver->company ?? 'No Company' }}"
                                                    data-status="{{ $driver->status ?? 'No Status' }}"
                                                    data-employee-no="{{ $driver->employee_id_permanent ?? $driver->employee_id }}"
                                                    {{ old('employee_id', $leave->employee_id) == $driver->id ? 'selected' : '' }}>
                                                    {{ $driver->full_name }}
                                                    | {{ $driver->garage ?? 'No Garage' }}
                                                    | {{ $driver->position?->title ?? 'Driver' }}
                                                </option>
                                            @endforeach
                                        </select>

                                        @error('employee_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semi-bold">
                                            Leave Type <span class="text-danger">*</span>
                                        </label>

                                        <select name="leave_type"
                                            class="form-select @error('leave_type') is-invalid @enderror" required>
                                            @foreach (['Medical Leave', 'Emergency Leave', 'Vacation Leave', 'Others'] as $type)
                                                <option value="{{ $type }}"
                                                    {{ old('leave_type', $leave->leave_type) === $type ? 'selected' : '' }}>
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
                                            value="{{ old('start_date', optional($leave->start_date)->format('Y-m-d')) }}"
                                            required>

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
                                            value="{{ old('end_date', optional($leave->end_date)->format('Y-m-d')) }}"
                                            required>

                                        @error('end_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-semi-bold">Reason / Remarks</label>

                                        <textarea name="reason" class="form-control @error('reason') is-invalid @enderror" rows="4"
                                            placeholder="Enter leave reason, supporting details, or HR note.">{{ old('reason', $leave->reason) }}</textarea>

                                        @error('reason')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer bg-body-tertiary text-end">
                                <a href="{{ route('driver-leave.driver.index') }}" class="btn btn-falcon-default me-2">
                                    Cancel
                                </a>

                                <button type="submit" class="btn btn-primary">
                                    <span class="fas fa-save me-1"></span>
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card mb-3">
                            <div class="card-header bg-body-tertiary">
                                <h5 class="mb-0">Selected Driver Details</h5>
                            </div>

                            <div class="card-body">
                                <div id="driverPreview">
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

                        <div class="card mb-3">
                            <div class="card-header bg-body-tertiary">
                                <h5 class="mb-0">Current Notice Status</h5>
                            </div>

                            <div class="card-body">
                                <div class="notice-list">
                                    <div class="notice-line">
                                        <span class="fas fa-paper-plane text-info me-2"></span>
                                        <div>
                                            <strong>1st Notice</strong>
                                            <div class="small text-600">
                                                {{ $leave->first_notice_sent_at ? $leave->first_notice_sent_at->format('M d, Y h:i A') : 'Pending' }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="notice-line">
                                        <span class="fas fa-user-slash text-warning me-2"></span>
                                        <div>
                                            <strong>2nd Notice</strong>
                                            <div class="small text-600">
                                                {{ $leave->second_notice_sent_at ? $leave->second_notice_sent_at->format('M d, Y h:i A') : 'Pending' }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="notice-line">
                                        <span class="fas fa-file-signature text-danger me-2"></span>
                                        <div>
                                            <strong>Final Notice</strong>
                                            <div class="small text-600">
                                                {{ $leave->final_notice_sent_at ? $leave->final_notice_sent_at->format('M d, Y h:i A') : 'Pending' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <div class="small text-700">
                                    2nd Notice automatically makes the driver <strong>Inactive</strong>.
                                </div>
                            </div>
                        </div>

                        @if ($leave->last_action_note)
                            <div class="card">
                                <div class="card-header bg-body-tertiary">
                                    <h5 class="mb-0">Last Action Note</h5>
                                </div>

                                <div class="card-body">
                                    <p class="mb-0 text-700">{{ $leave->last_action_note }}</p>
                                </div>
                            </div>
                        @endif
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

            function updateDriverPreview() {
                const selected = employeeSelect.options[employeeSelect.selectedIndex];

                if (!selected || !selected.value) {
                    return;
                }

                document.getElementById('previewName').innerText = selected.dataset.name || '-';
                document.getElementById('previewEmployeeNo').innerText = selected.dataset.employeeNo ||
                    'No Employee ID';
                document.getElementById('previewPosition').innerText = selected.dataset.position || '-';
                document.getElementById('previewGarage').innerText = selected.dataset.garage || '-';
                document.getElementById('previewCompany').innerText = selected.dataset.company || '-';
                document.getElementById('previewStatus').innerText = selected.dataset.status || '-';
            }

            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
                const select = $('#employee_id');

                if (select.hasClass('select2-hidden-accessible')) {
                    select.select2('destroy');
                }

                select.select2({
                    width: '100%',
                    placeholder: 'Search driver by name, garage, or position...',
                    allowClear: true
                });

                select.on('change', updateDriverPreview);
            } else {
                employeeSelect.addEventListener('change', updateDriverPreview);
            }

            updateDriverPreview();
        });
    </script>
@endpush

@push('styles')
    <style>
        .driver-leave-form-page .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            border-bottom: 1px solid var(--falcon-gray-200, #edf2f9);
            padding: .75rem 0;
        }

        .driver-leave-form-page .detail-row:last-child {
            border-bottom: 0;
        }

        .driver-leave-form-page .detail-row span {
            color: var(--falcon-gray-600, #748194);
        }

        .driver-leave-form-page .notice-list {
            display: flex;
            flex-direction: column;
            gap: .85rem;
        }

        .driver-leave-form-page .notice-line {
            display: flex;
            align-items: flex-start;
        }
    </style>
@endpush
