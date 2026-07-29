@extends('layouts.app')

@section('title', 'Edit Biometric Employee')

@section('content')
    @php
        $currentStatus = old('employment_status', $employeeBiometric->employment_status ?? 'active');
        $isActive = $currentStatus === 'active';
        $isPayrollActive = (bool) old('is_payroll_active', $employeeBiometric->is_payroll_active ?? true);

        $legacyBiometricId =
            $employeeBiometric->legacy_biometric_employee_id ??
            ($employeeBiometric->source_employee_id ??
                ($employeeBiometric->source_crosschex_id ?? ($employeeBiometric->source_employee_no ?? 'N/A')));

        $displayName = old('display_name', $employeeBiometric->display_name ?: 'Unnamed Employee');
        $displayEmployeeNo = old(
            'display_employee_no',
            $employeeBiometric->display_employee_no ?: 'No employee number',
        );
    @endphp

    <div class="container-fluid employee-biometric-edit-page" data-layout="container">
        <script>
            (function() {
                try {
                    const isFluid = JSON.parse(localStorage.getItem('isFluid'));
                    const container = document.querySelector('[data-layout="container"]');

                    if (isFluid && container) {
                        container.classList.remove('container');
                        container.classList.add('container-fluid');
                    }
                } catch (error) {
                    // Keep the default layout when localStorage cannot be read.
                }
            })();
        </script>

        <div class="content px-0 pt-3 pb-4">
            @if ($errors->any())
                <div class="alert app-alert app-alert-danger alert-dismissible fade show mb-3" role="alert">
                    <div class="app-alert-icon" aria-hidden="true">
                        <span class="fas fa-exclamation-triangle"></span>
                    </div>

                    <div class="flex-grow-1">
                        <h6 class="mb-1">Unable to save the record</h6>
                        <p class="mb-2">Correct the following validation errors:</p>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card page-header-card mb-3">
                <div class="card-body p-3 p-lg-4">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                        <div class="min-w-0">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb breadcrumb-sm mb-3">
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('biometrics.employees.index') }}">
                                            Employee Biometrics
                                        </a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page">Edit Employee</li>
                                </ol>
                            </nav>

                            <div class="d-flex align-items-center gap-3">
                                <div class="page-title-icon" aria-hidden="true">
                                    <span class="fas fa-fingerprint"></span>
                                </div>

                                <div class="min-w-0">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <h1 class="page-title mb-0">Edit Biometric Employee</h1>

                                        <span id="headerStatusBadge"
                                            class="status-pill {{ $isActive ? 'status-pill-success' : 'status-pill-secondary' }}">
                                            <span id="headerStatusIcon"
                                                class="fas {{ $isActive ? 'fa-check-circle' : 'fa-user-slash' }}"></span>
                                            <span id="headerStatusText">{{ $isActive ? 'Active' : 'Inactive' }}</span>
                                        </span>
                                    </div>

                                    <p class="page-subtitle mb-0">
                                        Update HR and payroll fields without changing protected CrossChex source data.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('biometrics.employees.index') }}" class="btn btn-falcon-default">
                                <span class="fas fa-arrow-left me-2"></span>
                                Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 align-items-start">
                <div class="col-12 col-xl-8">
                    <form method="POST" action="{{ route('biometrics.employees.update', $employeeBiometric) }}"
                        id="biometricEmployeeEditForm">
                        @csrf
                        @method('PUT')

                        <div class="card app-card mb-3">
                            <div class="card-header app-card-header">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="section-icon section-icon-primary" aria-hidden="true">
                                        <span class="fas fa-user-edit"></span>
                                    </div>

                                    <div class="min-w-0">
                                        <h2 class="section-title mb-1">Editable Information</h2>
                                        <p class="section-description mb-0">
                                            Internal fields used for employee grouping, attendance, adjustments, and payroll
                                            processing.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body p-3 p-lg-4">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label" for="biometric_company_id">
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
                                                Assign the employee to a company, branch, or operating unit.
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-semibold" for="group_name">
                                            Payroll Group
                                            <span class="text-danger">*</span>
                                        </label>

                                        <select name="group_name" id="group_name"
                                            class="form-select @error('group_name') is-invalid @enderror" required>

                                            <option value="">
                                                Select Payroll Group
                                            </option>

                                            <option value="1" @selected(old('group_name', $employeeBiometric->group_name) == 1)>
                                                Mirasol / Balintawak Payroll
                                            </option>

                                            <option value="2" @selected(old('group_name', $employeeBiometric->group_name) == 2)>
                                                Gonzales Payroll
                                            </option>

                                        </select>


                                        @error('group_name')
                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>
                                        @else
                                            <div class="form-text">
                                                Determines the payroll source group.
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label" for="employment_status">
                                            Employment Status <span class="text-danger">*</span>
                                        </label>

                                        <select name="employment_status" id="employment_status"
                                            class="form-select @error('employment_status') is-invalid @enderror" required>
                                            <option value="active" @selected($currentStatus === 'active')>
                                                Active
                                            </option>
                                            <option value="inactive" @selected($currentStatus === 'inactive')>
                                                Inactive
                                            </option>
                                        </select>

                                        @error('employment_status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @else
                                            <div class="form-text">
                                                Inactive records remain stored but are excluded from new processing.
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label d-block" for="is_payroll_active">
                                            Payroll Inclusion
                                        </label>

                                        <div class="payroll-switch-panel">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="field-icon field-icon-success" aria-hidden="true">
                                                    <span class="fas fa-money-check-alt"></span>
                                                </div>

                                                <div class="flex-grow-1 min-w-0">
                                                    <label class="form-check-label fw-semibold d-block"
                                                        for="is_payroll_active">
                                                        Include in payroll workflow
                                                    </label>
                                                    <span class="small text-muted">
                                                        Controls adjustments, summaries, plotting, salary sync, and payroll.
                                                    </span>
                                                </div>

                                                <div class="form-check form-switch m-0">
                                                    <input type="hidden" name="is_payroll_active" value="0">
                                                    <input
                                                        class="form-check-input @error('is_payroll_active') is-invalid @enderror"
                                                        type="checkbox" role="switch" name="is_payroll_active"
                                                        id="is_payroll_active" value="1"
                                                        @checked($isPayrollActive)>
                                                </div>
                                            </div>
                                        </div>

                                        @error('is_payroll_active')
                                            <div class="text-danger small mt-2">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label" for="display_employee_no">
                                            Display Employee No.
                                        </label>

                                        <div class="input-group app-input-group">
                                            <span class="input-group-text" aria-hidden="true">
                                                <span class="fas fa-hashtag"></span>
                                            </span>
                                            <input type="text" name="display_employee_no" id="display_employee_no"
                                                value="{{ old('display_employee_no', $employeeBiometric->display_employee_no) }}"
                                                class="form-control @error('display_employee_no') is-invalid @enderror"
                                                placeholder="EMP-0001" autocomplete="off">
                                            @error('display_employee_no')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        @unless ($errors->has('display_employee_no'))
                                            <div class="form-text">
                                                Employee number displayed in biometric and payroll lists.
                                            </div>
                                        @endunless
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label class="form-label" for="display_name">
                                            Display Name <span class="text-danger">*</span>
                                        </label>

                                        <div class="input-group app-input-group">
                                            <span class="input-group-text" aria-hidden="true">
                                                <span class="fas fa-user"></span>
                                            </span>
                                            <input type="text" name="display_name" id="display_name"
                                                value="{{ old('display_name', $employeeBiometric->display_name) }}"
                                                class="form-control @error('display_name') is-invalid @enderror"
                                                placeholder="Employee full name" autocomplete="off" required>
                                            @error('display_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        @unless ($errors->has('display_name'))
                                            <div class="form-text">
                                                Primary employee name shown throughout the system.
                                            </div>
                                        @endunless
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label" for="remarks">
                                            Remarks
                                        </label>

                                        <textarea name="remarks" id="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="4"
                                            placeholder="Add HR, payroll, transfer, resignation, or duplicate-record notes.">{{ old('remarks', $employeeBiometric->remarks) }}</textarea>

                                        @error('remarks')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @else
                                            <div class="form-text">
                                                Optional internal notes. Do not place passwords or API credentials here.
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer app-card-footer">
                                <div class="d-flex flex-column-reverse flex-sm-row justify-content-between gap-2">
                                    <a href="{{ route('biometrics.employees.index') }}" class="btn btn-falcon-default">
                                        <span class="fas fa-times me-2"></span>
                                        Cancel
                                    </a>

                                    @can('biometrics.update')
                                        <button type="submit" class="btn btn-primary px-4" id="saveChangesButton">
                                            <span class="fas fa-save me-2"></span>
                                            Save Changes
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="card app-card">
                        <div class="card-header app-card-header">
                            <div class="d-flex flex-column flex-md-row align-items-md-start justify-content-between gap-3">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="section-icon section-icon-info" aria-hidden="true">
                                        <span class="fas fa-database"></span>
                                    </div>

                                    <div class="min-w-0">
                                        <h2 class="section-title mb-1">Source Biometrics Data</h2>
                                        <p class="section-description mb-0">
                                            Read-only information captured from the employee's CrossChex source account.
                                        </p>
                                    </div>
                                </div>

                                <span class="protected-pill">
                                    <span class="fas fa-lock"></span>
                                    Protected
                                </span>
                            </div>
                        </div>

                        <div class="card-body p-3 p-lg-4">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <div class="source-data-item">
                                        <div class="source-data-icon source-data-icon-primary" aria-hidden="true">
                                            <span class="fas fa-fingerprint"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="source-data-label">Canonical Bio ID</div>
                                            <div class="source-data-value text-primary">#{{ $employeeBiometric->id }}
                                            </div>
                                            <div class="source-data-note">Primary ID used by payroll relationships.</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="source-data-item">
                                        <div class="source-data-icon source-data-icon-secondary" aria-hidden="true">
                                            <span class="fas fa-history"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="source-data-label">Legacy Biometric ID</div>
                                            <div class="source-data-value source-data-value-mono">{{ $legacyBiometricId }}
                                            </div>
                                            <div class="source-data-note">Retained as a legacy snapshot reference.</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="source-data-item">
                                        <div class="source-data-icon source-data-icon-info" aria-hidden="true">
                                            <span class="fas fa-id-badge"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="source-data-label">Source Employee Name</div>
                                            <div class="source-data-value">
                                                {{ $employeeBiometric->source_employee_name ?: 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="source-data-item">
                                        <div class="source-data-icon source-data-icon-info" aria-hidden="true">
                                            <span class="fas fa-address-card"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="source-data-label">Source Employee No.</div>
                                            <div class="source-data-value">
                                                {{ $employeeBiometric->source_employee_no ?: 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="source-data-item">
                                        <div class="source-data-icon source-data-icon-warning" aria-hidden="true">
                                            <span class="fas fa-key"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="source-data-label">CrossChex ID</div>
                                            <div class="source-data-value source-data-value-mono">
                                                {{ $employeeBiometric->source_crosschex_id ?: 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="source-data-item">
                                        <div class="source-data-icon source-data-icon-warning" aria-hidden="true">
                                            <span class="fas fa-user-tag"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="source-data-label">Source Employee ID</div>
                                            <div class="source-data-value source-data-value-mono">
                                                {{ $employeeBiometric->source_employee_id ?: 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="source-data-item">
                                        <div class="source-data-icon source-data-icon-success" aria-hidden="true">
                                            <span class="fas fa-cloud"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="source-data-label">CrossChex Account</div>
                                            <div class="source-data-value">
                                                {{ $employeeBiometric->source_crosschex_account ?: 'N/A' }}
                                            </div>
                                            <div class="source-data-note">
                                                {{ $employeeBiometric->source_crosschex_account_name ?: 'No account name' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="source-data-item">
                                        <div class="source-data-icon source-data-icon-success" aria-hidden="true">
                                            <span class="fas fa-microchip"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="source-data-label">Device</div>
                                            <div class="source-data-value">
                                                {{ $employeeBiometric->device_name ?: 'N/A' }}
                                            </div>
                                            <div class="source-data-note source-data-value-mono">
                                                SN: {{ $employeeBiometric->device_sn ?: 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="source-data-item">
                                        <div class="source-data-icon source-data-icon-secondary" aria-hidden="true">
                                            <span class="fas fa-clock"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="source-data-label">Last Check Time</div>
                                            <div class="source-data-value">
                                                {{ $employeeBiometric->last_check_time?->format('M d, Y h:i A') ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="source-data-item">
                                        <div class="source-data-icon source-data-icon-primary" aria-hidden="true">
                                            <span class="fas fa-list-ol"></span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="source-data-label">Total Logs</div>
                                            <div class="source-data-value">
                                                {{ number_format((int) ($employeeBiometric->total_logs ?? 0)) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="protected-notice mt-3">
                                <div class="protected-notice-icon" aria-hidden="true">
                                    <span class="fas fa-shield-alt"></span>
                                </div>
                                <div>
                                    <h6 class="mb-1">CrossChex source fields are read-only</h6>
                                    <p class="mb-0">
                                        This screen only updates company tagging, group, status, payroll inclusion,
                                        display values, and remarks.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-4">
                    <div class="edit-summary-sticky">
                        <div class="card app-card mb-3">
                            <div class="card-header app-card-header">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="section-icon section-icon-primary" aria-hidden="true">
                                        <span class="fas fa-id-card"></span>
                                    </div>
                                    <h2 class="section-title mb-0">Record Summary</h2>
                                </div>
                            </div>

                            <div class="card-body p-0">
                                <div class="employee-summary-head">
                                    <div class="employee-avatar" aria-hidden="true">
                                        <span class="fas fa-user"></span>
                                    </div>

                                    <h3 class="employee-summary-name" id="summaryDisplayName">
                                        {{ $displayName }}
                                    </h3>

                                    <p class="employee-summary-number" id="summaryEmployeeNo">
                                        {{ $displayEmployeeNo }}
                                    </p>

                                    <span id="summaryStatusBadge"
                                        class="status-pill {{ $isActive ? 'status-pill-success' : 'status-pill-secondary' }}">
                                        <span id="summaryStatusIcon"
                                            class="fas {{ $isActive ? 'fa-check-circle' : 'fa-user-slash' }}"></span>
                                        <span id="summaryStatusText">{{ $isActive ? 'Active' : 'Inactive' }}</span>
                                    </span>
                                </div>

                                <div class="summary-list">
                                    <div class="summary-row">
                                        <div class="summary-label">
                                            <span class="fas fa-fingerprint"></span>
                                            Canonical Bio ID
                                        </div>
                                        <div class="summary-value text-primary">#{{ $employeeBiometric->id }}</div>
                                    </div>

                                    <div class="summary-row">
                                        <div class="summary-label">
                                            <span class="fas fa-history"></span>
                                            Legacy Bio ID
                                        </div>
                                        <div class="summary-value summary-value-mono">{{ $legacyBiometricId }}</div>
                                    </div>

                                    <div class="summary-row">
                                        <div class="summary-label">
                                            <span class="fas fa-users"></span>
                                            Group
                                        </div>
                                        <div class="summary-value" id="summaryGroupName">
                                            {{ old('group_name', $employeeBiometric->group_name) ?: 'Ungrouped' }}
                                        </div>
                                    </div>

                                    <div class="summary-row">
                                        <div class="summary-label">
                                            <span class="fas fa-building"></span>
                                            Company
                                        </div>
                                        <div class="summary-value" id="summaryCompanyName">
                                            {{ $employeeBiometric->company?->name ?? 'Not Tagged' }}
                                        </div>
                                    </div>

                                    <div class="summary-row">
                                        <div class="summary-label">
                                            <span class="fas fa-key"></span>
                                            CrossChex ID
                                        </div>
                                        <div class="summary-value summary-value-mono">
                                            {{ $employeeBiometric->source_crosschex_id ?: 'N/A' }}
                                        </div>
                                    </div>

                                    <div class="summary-row">
                                        <div class="summary-label">
                                            <span class="fas fa-microchip"></span>
                                            Device
                                        </div>
                                        <div class="summary-value">
                                            {{ $employeeBiometric->device_name ?: 'N/A' }}
                                        </div>
                                    </div>

                                    <div class="summary-row">
                                        <div class="summary-label">
                                            <span class="fas fa-list-ol"></span>
                                            Logs
                                        </div>
                                        <div class="summary-value">
                                            {{ number_format((int) ($employeeBiometric->total_logs ?? 0)) }}
                                        </div>
                                    </div>

                                    <div class="summary-row">
                                        <div class="summary-label">
                                            <span class="fas fa-money-check-alt"></span>
                                            Payroll
                                        </div>
                                        <div class="summary-value">
                                            <span id="summaryPayrollBadge"
                                                class="mini-status {{ $isPayrollActive ? 'mini-status-success' : 'mini-status-secondary' }}">
                                                {{ $isPayrollActive ? 'Included' : 'Excluded' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card app-card">
                            <div class="card-header app-card-header">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="section-icon section-icon-warning" aria-hidden="true">
                                        <span class="fas fa-lightbulb"></span>
                                    </div>
                                    <h2 class="section-title mb-0">Usage Guide</h2>
                                </div>
                            </div>

                            <div class="card-body p-3">
                                <div class="guide-item">
                                    <div class="guide-icon guide-icon-info" aria-hidden="true">
                                        <span class="fas fa-building"></span>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Company and Group</h6>
                                        <p class="mb-0">
                                            Use these fields to organize employees by company, branch, department, or
                                            payroll group.
                                        </p>
                                    </div>
                                </div>

                                <div class="guide-item">
                                    <div class="guide-icon guide-icon-success" aria-hidden="true">
                                        <span class="fas fa-user-check"></span>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Active</h6>
                                        <p class="mb-0">
                                            Use for employees who are still eligible for attendance and payroll workflows.
                                        </p>
                                    </div>
                                </div>

                                <div class="guide-item mb-0">
                                    <div class="guide-icon guide-icon-secondary" aria-hidden="true">
                                        <span class="fas fa-user-slash"></span>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Inactive</h6>
                                        <p class="mb-0">
                                            Use for resigned, archived, duplicate, or otherwise excluded records.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('styles')
        @include('biometrics.employees.styles')
    @endpush

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('biometricEmployeeEditForm');
            const saveButton = document.getElementById('saveChangesButton');
            const displayNameInput = document.getElementById('display_name');
            const employeeNoInput = document.getElementById('display_employee_no');
            const groupInput = document.getElementById('group_name');
            const companySelect = document.getElementById('biometric_company_id');
            const statusSelect = document.getElementById('employment_status');
            const payrollSwitch = document.getElementById('is_payroll_active');

            const summaryDisplayName = document.getElementById('summaryDisplayName');
            const summaryEmployeeNo = document.getElementById('summaryEmployeeNo');
            const summaryGroupName = document.getElementById('summaryGroupName');
            const summaryCompanyName = document.getElementById('summaryCompanyName');
            const summaryPayrollBadge = document.getElementById('summaryPayrollBadge');

            const statusElements = [{
                    badge: document.getElementById('headerStatusBadge'),
                    icon: document.getElementById('headerStatusIcon'),
                    text: document.getElementById('headerStatusText')
                },
                {
                    badge: document.getElementById('summaryStatusBadge'),
                    icon: document.getElementById('summaryStatusIcon'),
                    text: document.getElementById('summaryStatusText')
                }
            ];

            function setText(element, value, fallback) {
                if (!element) {
                    return;
                }

                const normalizedValue = String(value ?? '').trim();
                element.textContent = normalizedValue || fallback;
            }

            function updateStatusPreview() {
                const isActive = statusSelect && statusSelect.value === 'active';

                statusElements.forEach(function(item) {
                    if (!item.badge || !item.icon || !item.text) {
                        return;
                    }

                    item.badge.classList.toggle('status-pill-success', isActive);
                    item.badge.classList.toggle('status-pill-secondary', !isActive);
                    item.icon.classList.toggle('fa-check-circle', isActive);
                    item.icon.classList.toggle('fa-user-slash', !isActive);
                    item.text.textContent = isActive ? 'Active' : 'Inactive';
                });
            }

            function updatePayrollPreview() {
                if (!payrollSwitch || !summaryPayrollBadge) {
                    return;
                }

                const isIncluded = payrollSwitch.checked;

                summaryPayrollBadge.classList.toggle('mini-status-success', isIncluded);
                summaryPayrollBadge.classList.toggle('mini-status-secondary', !isIncluded);
                summaryPayrollBadge.textContent = isIncluded ? 'Included' : 'Excluded';
            }

            displayNameInput?.addEventListener('input', function() {
                setText(summaryDisplayName, this.value, 'Unnamed Employee');
            });

            employeeNoInput?.addEventListener('input', function() {
                setText(summaryEmployeeNo, this.value, 'No employee number');
            });

            groupInput?.addEventListener('input', function() {
                setText(summaryGroupName, this.value, 'Ungrouped');
            });

            companySelect?.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const companyName = this.value ? selectedOption.text : 'Not Tagged';
                setText(summaryCompanyName, companyName, 'Not Tagged');
            });

            statusSelect?.addEventListener('change', updateStatusPreview);
            payrollSwitch?.addEventListener('change', updatePayrollPreview);

            form?.addEventListener('submit', function() {
                if (!saveButton) {
                    return;
                }

                saveButton.disabled = true;
                saveButton.innerHTML = '<span class="fas fa-spinner fa-spin me-2"></span>Saving...';
            });
        });
    </script>
@endsection
