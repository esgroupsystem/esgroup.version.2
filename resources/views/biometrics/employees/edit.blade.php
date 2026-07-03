@extends('layouts.app')

@section('title', 'Edit Biometric Employee')

@section('content')
    <div class="container-fluid" data-layout="container">
        <script>
            (function() {
                try {
                    var isFluid = JSON.parse(localStorage.getItem('isFluid'));

                    if (isFluid) {
                        var container = document.querySelector('[data-layout]');

                        if (container) {
                            container.classList.remove('container');
                            container.classList.add('container-fluid');
                        }
                    }
                } catch (error) {}
            })();
        </script>

        <div class="content employee-biometric-edit-page">
            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="alert alert-danger border-200 bg-soft-danger d-flex align-items-start gap-2 alert-dismissible fade show"
                    role="alert">
                    <span class="fas fa-exclamation-triangle mt-1"></span>
                    <div>
                        <div class="fw-semibold mb-1">Please check the following:</div>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Header --}}
            <div class="card border-0 shadow-sm mb-3 overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                        <div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-2">
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('biometrics.employees.index') }}">
                                            Employee Biometrics
                                        </a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">
                                        Edit Biometric Employee
                                    </li>
                                </ol>
                            </nav>

                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar avatar-xl">
                                    <div class="avatar-name rounded-circle bg-primary-subtle text-primary">
                                        <span class="fas fa-fingerprint"></span>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="mb-1">Edit Biometric Employee</h4>
                                    <p class="text-muted mb-0">
                                        Update manual fields only. Source data from Mirasol CrossChex remains protected.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('biometrics.employees.index') }}" class="btn btn-falcon-default">
                            <span class="fas fa-arrow-left me-1"></span>
                            Back to List
                        </a>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                {{-- Main Form --}}
                <div class="col-xl-8">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-body-tertiary border-bottom border-200 py-3">
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                                <div>
                                    <h5 class="mb-1">
                                        <span class="fas fa-user-edit text-primary me-2"></span>
                                        Editable Information
                                    </h5>
                                    <p class="text-muted fs-10 mb-0">
                                        These fields are used internally for HR, payroll, and biometric employee tagging.
                                    </p>
                                </div>

                                @if ($employeeBiometric->employment_status === 'active')
                                    <span class="badge badge-phoenix badge-phoenix-success px-3 py-2">
                                        <span class="fas fa-check me-1"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="badge badge-phoenix badge-phoenix-secondary px-3 py-2">
                                        <span class="fas fa-ban me-1"></span>
                                        Inactive
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="card-body">
                            <form method="POST" action="{{ route('biometrics.employees.update', $employeeBiometric) }}">
                                @csrf
                                @method('PUT')

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" for="biometric_company_id">
                                            Company
                                        </label>

                                        <select name="biometric_company_id" id="biometric_company_id"
                                            class="form-select @error('biometric_company_id') is-invalid @enderror">
                                            <option value="">No Company / Not Tagged</option>

                                            @foreach ($companies as $company)
                                                <option value="{{ $company->id }}" @selected((string) old('biometric_company_id', $employeeBiometric->biometric_company_id) === (string) $company->id)>
                                                    {{ $company->name }}
                                                </option>
                                            @endforeach
                                        </select>

                                        @error('biometric_company_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @else
                                            <div class="form-text">
                                                Group this biometric employee under a company tag.
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" for="employment_status">
                                            Employment Status <span class="text-danger">*</span>
                                        </label>

                                        <select name="employment_status" id="employment_status"
                                            class="form-select @error('employment_status') is-invalid @enderror" required>
                                            <option value="active" @selected(old('employment_status', $employeeBiometric->employment_status) === 'active')>
                                                Active
                                            </option>
                                            <option value="inactive" @selected(old('employment_status', $employeeBiometric->employment_status) === 'inactive')>
                                                Inactive
                                            </option>
                                        </select>

                                        @error('employment_status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @else
                                            <div class="form-text">
                                                Use inactive for resigned, archived, or hidden biometric records.
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" for="display_employee_no">
                                            Display Employee No
                                        </label>

                                        <input type="text" name="display_employee_no" id="display_employee_no"
                                            value="{{ old('display_employee_no', $employeeBiometric->display_employee_no) }}"
                                            class="form-control @error('display_employee_no') is-invalid @enderror"
                                            placeholder="Example: EMP-0001">

                                        @error('display_employee_no')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @else
                                            <div class="form-text">
                                                Editable employee number shown in biometric lists.
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold" for="display_name">
                                            Display Name <span class="text-danger">*</span>
                                        </label>

                                        <input type="text" name="display_name" id="display_name"
                                            value="{{ old('display_name', $employeeBiometric->display_name) }}"
                                            class="form-control @error('display_name') is-invalid @enderror"
                                            placeholder="Employee full name" required>

                                        @error('display_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @else
                                            <div class="form-text">
                                                Main name displayed in biometric employee records.
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold" for="remarks">
                                            Remarks
                                        </label>

                                        <textarea name="remarks" id="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="4"
                                            placeholder="Example: Resigned last June 2026, transferred company, duplicate reviewed, etc.">{{ old('remarks', $employeeBiometric->remarks) }}</textarea>

                                        @error('remarks')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @else
                                            <div class="form-text">
                                                Optional notes for HR, payroll, or admin reference.
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <hr class="my-4">

                                <div class="d-flex flex-column flex-sm-row justify-content-between gap-2">
                                    <a href="{{ route('biometrics.employees.index') }}" class="btn btn-falcon-default">
                                        <span class="fas fa-times me-1"></span>
                                        Cancel
                                    </a>

                                    @can('biometrics.update')
                                        <button type="submit" class="btn btn-primary">
                                            <span class="fas fa-save me-1"></span>
                                            Save Changes
                                        </button>
                                    @endcan
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Source Data --}}
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-body-tertiary border-bottom border-200 py-3">
                            <h5 class="mb-1">
                                <span class="fas fa-database text-info me-2"></span>
                                Source Biometrics Data
                            </h5>
                            <p class="text-muted fs-10 mb-0">
                                Read-only values copied from Mirasol CrossChex logs during sync.
                            </p>
                        </div>

                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="source-info-box">
                                        <div class="text-muted fs-10 mb-1">Source Employee Name</div>
                                        <div class="fw-semibold">{{ $employeeBiometric->source_employee_name ?: 'N/A' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="source-info-box">
                                        <div class="text-muted fs-10 mb-1">Source Employee No</div>
                                        <div class="fw-semibold">{{ $employeeBiometric->source_employee_no ?: 'N/A' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="source-info-box">
                                        <div class="text-muted fs-10 mb-1">CrossChex ID</div>
                                        <div class="fw-semibold font-monospace">
                                            {{ $employeeBiometric->source_crosschex_id ?: 'N/A' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="source-info-box">
                                        <div class="text-muted fs-10 mb-1">Source Employee ID</div>
                                        <div class="fw-semibold">
                                            {{ $employeeBiometric->source_employee_id ?: 'N/A' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="source-info-box">
                                        <div class="text-muted fs-10 mb-1">CrossChex Account</div>
                                        <div class="fw-semibold">
                                            {{ $employeeBiometric->source_crosschex_account ?: 'N/A' }}
                                        </div>
                                        <div class="text-muted fs-11">
                                            {{ $employeeBiometric->source_crosschex_account_name ?: 'No account name' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="source-info-box">
                                        <div class="text-muted fs-10 mb-1">Device</div>
                                        <div class="fw-semibold">{{ $employeeBiometric->device_name ?: 'N/A' }}</div>
                                        <div class="text-muted fs-11">
                                            SN: {{ $employeeBiometric->device_sn ?: 'N/A' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="source-info-box">
                                        <div class="text-muted fs-10 mb-1">Last Check Time</div>
                                        <div class="fw-semibold">
                                            {{ $employeeBiometric->last_check_time?->format('M d, Y h:i A') ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="source-info-box">
                                        <div class="text-muted fs-10 mb-1">Total Logs</div>
                                        <div class="fw-semibold">
                                            {{ number_format($employeeBiometric->total_logs) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info border-200 bg-soft-info mt-4 mb-0">
                                <div class="d-flex gap-3">
                                    <span class="fas fa-info-circle fs-4 mt-1"></span>
                                    <div>
                                        <h6 class="alert-heading mb-1">Source data is protected.</h6>
                                        <p class="mb-0">
                                            Mirasol fields are not edited here. This form only updates display fields,
                                            company tagging, status, and remarks.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Summary --}}
                <div class="col-xl-4">
                    <div class="sticky-xl-top edit-summary-sticky">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-body-tertiary border-bottom border-200 py-3">
                                <h5 class="mb-0">
                                    <span class="fas fa-id-card text-primary me-2"></span>
                                    Record Summary
                                </h5>
                            </div>

                            <div class="card-body">
                                <div class="text-center mb-4">
                                    <div class="avatar avatar-4xl mx-auto mb-3">
                                        <div class="avatar-name rounded-circle bg-primary-subtle text-primary">
                                            <span class="fas fa-user"></span>
                                        </div>
                                    </div>

                                    <h5 class="mb-1">{{ $employeeBiometric->display_name }}</h5>

                                    <p class="text-muted mb-2">
                                        {{ $employeeBiometric->display_employee_no ?: 'No employee number' }}
                                    </p>

                                    @if ($employeeBiometric->employment_status === 'active')
                                        <span class="badge badge-phoenix badge-phoenix-success px-3 py-2">
                                            <span class="fas fa-check me-1"></span>
                                            Active
                                        </span>
                                    @else
                                        <span class="badge badge-phoenix badge-phoenix-secondary px-3 py-2">
                                            <span class="fas fa-ban me-1"></span>
                                            Inactive
                                        </span>
                                    @endif
                                </div>

                                <div class="border-top border-200 pt-3">
                                    <div class="summary-line">
                                        <span class="text-muted">Company</span>
                                        <span class="fw-semibold text-end">
                                            {{ $employeeBiometric->company?->name ?? 'Not Tagged' }}
                                        </span>
                                    </div>

                                    <div class="summary-line">
                                        <span class="text-muted">CrossChex ID</span>
                                        <span class="fw-semibold font-monospace text-end">
                                            {{ $employeeBiometric->source_crosschex_id ?: 'N/A' }}
                                        </span>
                                    </div>

                                    <div class="summary-line">
                                        <span class="text-muted">Device</span>
                                        <span class="fw-semibold text-end">
                                            {{ $employeeBiometric->device_name ?: 'N/A' }}
                                        </span>
                                    </div>

                                    <div class="summary-line mb-0">
                                        <span class="text-muted">Logs</span>
                                        <span class="fw-semibold">
                                            {{ number_format($employeeBiometric->total_logs) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-body-tertiary border-bottom border-200 py-3">
                                <h5 class="mb-0">
                                    <span class="fas fa-lightbulb text-warning me-2"></span>
                                    Usage Guide
                                </h5>
                            </div>

                            <div class="card-body">
                                <div class="guide-item">
                                    <div class="guide-icon bg-info-subtle text-info">
                                        <span class="fas fa-building"></span>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Company</h6>
                                        <p class="text-muted fs-10 mb-0">
                                            Use this for grouping biometric employees by company or branch.
                                        </p>
                                    </div>
                                </div>

                                <div class="guide-item">
                                    <div class="guide-icon bg-success-subtle text-success">
                                        <span class="fas fa-user-check"></span>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Active</h6>
                                        <p class="text-muted fs-10 mb-0">
                                            Use for employees who should remain visible in active biometric records.
                                        </p>
                                    </div>
                                </div>

                                <div class="guide-item mb-0">
                                    <div class="guide-icon bg-secondary-subtle text-secondary">
                                        <span class="fas fa-user-slash"></span>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Inactive</h6>
                                        <p class="text-muted fs-10 mb-0">
                                            Use for resigned or archived biometric records that must remain stored.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .employee-biometric-edit-page .source-info-box {
                border: 1px solid var(--falcon-border-color, #d8e2ef);
                border-radius: .5rem;
                padding: 1rem;
                height: 100%;
                background: var(--falcon-white, #fff);
                border-left: 3px solid rgba(44, 123, 229, .35);
            }

            .employee-biometric-edit-page .summary-line {
                display: flex;
                align-items: start;
                justify-content: space-between;
                gap: 1rem;
                margin-bottom: .75rem;
                font-size: .875rem;
            }

            .employee-biometric-edit-page .guide-item {
                display: flex;
                gap: .75rem;
                margin-bottom: 1rem;
            }

            .employee-biometric-edit-page .guide-icon {
                width: 2rem;
                height: 2rem;
                min-width: 2rem;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
            }

            .employee-biometric-edit-page .edit-summary-sticky {
                top: 1rem;
            }
        </style>
    </div>
@endsection
