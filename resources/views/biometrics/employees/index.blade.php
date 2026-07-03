@extends('layouts.app')

@section('title', 'Employee Biometrics')

@section('content')
    @php
        $canCreateCompany = auth()->user()?->can('biometrics.company.create') ?? false;

        $totalCount = (int) ($counts['total'] ?? 0);
        $activeCount = (int) ($counts['active'] ?? 0);
        $inactiveCount = (int) ($counts['inactive'] ?? 0);
        $withoutCompanyCount = (int) ($counts['without_company'] ?? 0);

        $activePercent = $totalCount > 0 ? round(($activeCount / $totalCount) * 100) : 0;
        $inactivePercent = $totalCount > 0 ? round(($inactiveCount / $totalCount) * 100) : 0;
        $withoutCompanyPercent = $totalCount > 0 ? round(($withoutCompanyCount / $totalCount) * 100) : 0;

        $hasActiveFilters =
            filled($filters['search'] ?? null) ||
            filled($filters['employment_status'] ?? null) ||
            filled($filters['biometric_company_id'] ?? null);
    @endphp

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

        <div class="content employee-biometrics-page">
            @if (session('success'))
                <div class="alert alert-success border-200 bg-soft-success d-flex align-items-center gap-2 alert-dismissible fade show mb-3"
                    role="alert">
                    <span class="fas fa-check-circle"></span>
                    <div class="fw-semibold">{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger border-200 bg-soft-danger d-flex align-items-start gap-2 alert-dismissible fade show mb-3"
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
            <div class="card border-0 shadow-sm mb-3 overflow-hidden bio-header-card">
                <div class="bio-header">
                    <div class="row g-0 align-items-stretch">
                        <div class="col-xl-8">
                            <div class="bio-header-main">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb bio-breadcrumb mb-3">
                                        <li class="breadcrumb-item">
                                            <a href="{{ url('/') }}">Dashboard</a>
                                        </li>
                                        <li class="breadcrumb-item active" aria-current="page">
                                            Employee Biometrics
                                        </li>
                                    </ol>
                                </nav>

                                <div class="d-flex align-items-start gap-3">
                                    <div class="bio-icon-circle bio-icon-white">
                                        <span class="fas fa-fingerprint"></span>
                                    </div>

                                    <div class="min-w-0">
                                        <h3 class="mb-2 text-white">Employee Biometrics</h3>
                                        <p class="bio-header-description mb-3">
                                            Clean biometric employee registry generated from Mirasol CrossChex logs.
                                            Sync keeps one employee record using employee ID, employee number, or name.
                                        </p>

                                        <div class="d-flex flex-wrap gap-2">
                                            <span class="bio-header-pill">
                                                <span class="fas fa-database"></span>
                                                {{ number_format($totalCount) }} total records
                                            </span>

                                            <span class="bio-header-pill bio-header-pill-success">
                                                <span class="fas fa-user-check"></span>
                                                {{ number_format($activeCount) }} active
                                            </span>

                                            <span class="bio-header-pill bio-header-pill-warning">
                                                <span class="fas fa-building"></span>
                                                {{ number_format($withoutCompanyCount) }} not tagged
                                            </span>

                                            @if ($hasActiveFilters)
                                                <span class="bio-header-pill bio-header-pill-info">
                                                    <span class="fas fa-filter"></span>
                                                    Filtered view
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4">
                            <div class="bio-sync-panel-wrap">
                                <div class="bio-sync-panel">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div>
                                            <h6 class="mb-1">Sync Control</h6>
                                            <p class="text-muted fs-10 mb-0">Refresh employee records from Mirasol logs.</p>
                                        </div>

                                        <div class="bio-icon-circle bio-icon-primary">
                                            <span class="fas fa-cloud-download-alt"></span>
                                        </div>
                                    </div>

                                    <div class="d-grid gap-2">
                                        @can('biometrics.sync')
                                            <form method="POST" action="{{ route('biometrics.employees.sync') }}"
                                                class="bio-sync-form">
                                                @csrf

                                                <button type="submit" class="btn btn-primary w-100 bio-sync-button">
                                                    <span class="fas fa-cloud-download-alt me-1"></span>
                                                    Sync from Mirasol
                                                </button>
                                            </form>
                                        @endcan

                                        <a href="{{ route('biometrics.employees.index') }}" class="btn btn-falcon-default">
                                            <span class="fas fa-sync-alt me-1"></span>
                                            Refresh Page
                                        </a>
                                    </div>

                                    <div class="border-top border-200 mt-3 pt-3">
                                        <div class="d-flex align-items-center gap-2 text-muted fs-10">
                                            <span class="fas fa-shield-alt text-success"></span>
                                            <span>Manual display fields are preserved during sync.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Metrics --}}
                    <div class="row g-0 bio-metric-row">
                        <div class="col-sm-6 col-xl-3">
                            <div class="bio-metric-card">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="bio-metric-label text-primary">Total Records</div>
                                        <div class="bio-metric-value">{{ number_format($totalCount) }}</div>
                                    </div>

                                    <div class="bio-icon-circle bio-icon-primary">
                                        <span class="fas fa-users"></span>
                                    </div>
                                </div>

                                <p class="bio-metric-caption mb-0 mt-2">
                                    Unique biometric employees after sync.
                                </p>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="bio-metric-card">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="bio-metric-label text-success">Active Employees</div>
                                        <div class="bio-metric-value text-success">{{ number_format($activeCount) }}</div>
                                    </div>

                                    <div class="bio-icon-circle bio-icon-success">
                                        <span class="fas fa-user-check"></span>
                                    </div>
                                </div>

                                <div class="progress bio-progress mt-2 mb-2">
                                    <div class="progress-bar bg-success" role="progressbar"
                                        style="width: {{ $activePercent }}%" aria-valuenow="{{ $activePercent }}"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>

                                <p class="bio-metric-caption mb-0">
                                    {{ $activePercent }}% of total records.
                                </p>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="bio-metric-card">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="bio-metric-label text-secondary">Inactive Employees</div>
                                        <div class="bio-metric-value text-secondary">{{ number_format($inactiveCount) }}
                                        </div>
                                    </div>

                                    <div class="bio-icon-circle bio-icon-secondary">
                                        <span class="fas fa-user-slash"></span>
                                    </div>
                                </div>

                                <div class="progress bio-progress mt-2 mb-2">
                                    <div class="progress-bar bg-secondary" role="progressbar"
                                        style="width: {{ $inactivePercent }}%" aria-valuenow="{{ $inactivePercent }}"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>

                                <p class="bio-metric-caption mb-0">
                                    {{ $inactivePercent }}% archived or resigned.
                                </p>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="bio-metric-card">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="bio-metric-label text-warning">No Company Tagged</div>
                                        <div class="bio-metric-value text-warning">
                                            {{ number_format($withoutCompanyCount) }}
                                        </div>
                                    </div>

                                    <div class="bio-icon-circle bio-icon-warning">
                                        <span class="fas fa-building"></span>
                                    </div>
                                </div>

                                <div class="progress bio-progress mt-2 mb-2">
                                    <div class="progress-bar bg-warning" role="progressbar"
                                        style="width: {{ $withoutCompanyPercent }}%"
                                        aria-valuenow="{{ $withoutCompanyPercent }}" aria-valuemin="0"
                                        aria-valuemax="100"></div>
                                </div>

                                <p class="bio-metric-caption mb-0">
                                    {{ $withoutCompanyPercent }}% need company assignment.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Search --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-bottom border-200 py-3">
                    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                        <div class="d-flex align-items-start gap-2">
                            <div class="bio-section-icon">
                                <span class="fas fa-filter"></span>
                            </div>

                            <div>
                                <h5 class="mb-1">Search and Filters</h5>
                                <p class="text-muted fs-10 mb-0">
                                    Narrow the list by employee name, employee number, CrossChex ID, status, or company.
                                </p>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            @if ($hasActiveFilters)
                                <span
                                    class="badge rounded-pill bg-info-subtle text-info border border-info-subtle px-3 py-2">
                                    <span class="fas fa-filter me-1"></span>
                                    Filters active
                                </span>
                            @endif

                            <a href="{{ route('biometrics.employees.index') }}" class="btn btn-sm btn-falcon-default">
                                <span class="fas fa-undo me-1"></span>
                                Clear
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('biometrics.employees.index') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-4 col-lg-5">
                                <label class="form-label fw-semibold" for="search">Search Employee</label>

                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <span class="fas fa-search text-muted"></span>
                                    </span>

                                    <input type="text" name="search" id="search"
                                        value="{{ $filters['search'] ?? '' }}" class="form-control"
                                        placeholder="Name, employee no, CrossChex ID">
                                </div>

                                <div class="form-text">
                                    Searches display name, source name, employee no, and CrossChex ID.
                                </div>
                            </div>

                            <div class="col-xl-3 col-lg-3">
                                <label class="form-label fw-semibold" for="employment_status">Status</label>

                                <select name="employment_status" id="employment_status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active" @selected(($filters['employment_status'] ?? '') === 'active')>
                                        Active
                                    </option>
                                    <option value="inactive" @selected(($filters['employment_status'] ?? '') === 'inactive')>
                                        Inactive
                                    </option>
                                </select>

                                <div class="form-text">Active or inactive records.</div>
                            </div>

                            <div class="col-xl-3 col-lg-4">
                                <label class="form-label fw-semibold" for="biometric_company_id">Company</label>

                                <select name="biometric_company_id" id="biometric_company_id" class="form-select">
                                    <option value="">All Companies</option>

                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}" @selected((string) ($filters['biometric_company_id'] ?? '') === (string) $company->id)>
                                            {{ $company->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <div class="form-text">Filter by company tag.</div>
                            </div>

                            <div class="col-xl-2">
                                <div class="d-grid d-sm-flex gap-2 justify-content-xl-end">
                                    <a href="{{ route('biometrics.employees.index') }}" class="btn btn-falcon-default">
                                        Cancel
                                    </a>

                                    <button type="submit" class="btn btn-primary">
                                        <span class="fas fa-search me-1"></span>
                                        Apply
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    @can('biometrics.company.create')
                        <div class="border-top border-200 mt-4 pt-4">
                            <button class="btn btn-falcon-success btn-sm mb-3" type="button" data-bs-toggle="collapse"
                                data-bs-target="#addCompanyCollapse" aria-expanded="false"
                                aria-controls="addCompanyCollapse">
                                <span class="fas fa-plus-circle me-1"></span>
                                Add New Company Tag
                            </button>

                            <div class="collapse" id="addCompanyCollapse">
                                <div class="bio-company-panel">
                                    <form method="POST" action="{{ route('biometrics.companies.store') }}">
                                        @csrf

                                        <div class="row g-3 align-items-end">
                                            <div class="col-lg-4">
                                                <label class="form-label fw-semibold" for="company_name">
                                                    Company Name <span class="text-danger">*</span>
                                                </label>

                                                <input type="text" name="name" id="company_name"
                                                    value="{{ old('name') }}"
                                                    class="form-control @error('name') is-invalid @enderror"
                                                    placeholder="Example: Jell Transport" required>

                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @else
                                                    <div class="form-text">Company, branch, or internal grouping.</div>
                                                @enderror
                                            </div>

                                            <div class="col-lg-6">
                                                <label class="form-label fw-semibold" for="company_remarks">
                                                    Remarks
                                                </label>

                                                <input type="text" name="remarks" id="company_remarks"
                                                    value="{{ old('remarks') }}"
                                                    class="form-control @error('remarks') is-invalid @enderror"
                                                    placeholder="Optional description">

                                                @error('remarks')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @else
                                                    <div class="form-text">Optional note for this company tag.</div>
                                                @enderror
                                            </div>

                                            <div class="col-lg-2 d-grid">
                                                <button type="submit" class="btn btn-success">
                                                    <span class="fas fa-save me-1"></span>
                                                    Save Tag
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endcan
                </div>
            </div>

            {{-- Table --}}
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white border-bottom border-200 py-3">
                    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-2">
                        <div class="d-flex align-items-start gap-2">
                            <div class="bio-section-icon">
                                <span class="fas fa-list"></span>
                            </div>

                            <div>
                                <h5 class="mb-1">Biometric Employees</h5>
                                <p class="text-muted fs-10 mb-0">
                                    Showing {{ $employeeBiometrics->firstItem() ?? 0 }}
                                    to {{ $employeeBiometrics->lastItem() ?? 0 }}
                                    of {{ number_format($employeeBiometrics->total()) }} unique biometric record(s).
                                </p>
                            </div>
                        </div>

                        <span
                            class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
                            {{ number_format($employeeBiometrics->total()) }}
                            Result{{ $employeeBiometrics->total() === 1 ? '' : 's' }}
                        </span>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive scrollbar bio-table-wrapper">
                        <table class="table table-hover align-middle mb-0 bio-table">
                            <thead>
                                <tr>
                                    <th class="ps-3 bio-sticky-col bio-employee-col">Employee</th>
                                    <th class="text-nowrap">Company</th>
                                    <th class="text-nowrap">Status</th>
                                    <th class="bio-source-col">Source Biometrics</th>
                                    <th class="bio-device-col">Device</th>
                                    <th class="text-nowrap">Last Check</th>
                                    <th class="text-center text-nowrap">Logs</th>
                                    <th class="text-end pe-3 text-nowrap">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($employeeBiometrics as $employeeBiometric)
                                    @php
                                        $isActive = $employeeBiometric->employment_status === 'active';

                                        $statusClass = $isActive ? 'success' : 'secondary';
                                        $statusIcon = $isActive ? 'fa-check' : 'fa-ban';
                                        $statusLabel = $isActive ? 'Active' : 'Inactive';

                                        $displayName = $employeeBiometric->display_name ?: 'No Display Name';
                                        $displayNo = $employeeBiometric->display_employee_no ?: 'N/A';
                                        $sourceNo = $employeeBiometric->source_employee_no ?: 'N/A';
                                        $sourceName = $employeeBiometric->source_employee_name ?: 'N/A';
                                        $sourceCrosschexId = $employeeBiometric->source_crosschex_id ?: 'N/A';
                                        $sourceEmployeeId = $employeeBiometric->source_employee_id ?: 'N/A';
                                        $deviceName = $employeeBiometric->device_name ?: 'N/A';
                                        $deviceSn = $employeeBiometric->device_sn ?: 'N/A';

                                        $nameParts = preg_split('/\s+/', trim($displayName));
                                        $firstInitial = strtoupper(substr($nameParts[0] ?? 'B', 0, 1));
                                        $lastInitial = strtoupper(
                                            substr($nameParts[count($nameParts) - 1] ?? '', 0, 1),
                                        );
                                        $initials = trim($firstInitial . $lastInitial) ?: 'B';
                                    @endphp

                                    <tr>
                                        <td class="ps-3 bio-sticky-col bg-white bio-employee-col">
                                            <div class="bio-employee-cell">
                                                <div class="bio-employee-avatar">
                                                    {{ $initials }}
                                                </div>

                                                <div class="min-w-0">
                                                    <div class="fw-bold text-dark text-truncate bio-employee-name">
                                                        {{ $displayName }}
                                                    </div>

                                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                                        <span class="bio-meta-pill">
                                                            <span class="fas fa-id-badge"></span>
                                                            Display: {{ $displayNo }}
                                                        </span>

                                                        <span class="bio-meta-pill">
                                                            <span class="fas fa-fingerprint"></span>
                                                            Source: {{ $sourceNo }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            @if ($employeeBiometric->company)
                                                <span
                                                    class="badge rounded-pill bg-info-subtle text-info border border-info-subtle px-3 py-2">
                                                    <span class="fas fa-building me-1"></span>
                                                    {{ $employeeBiometric->company->name }}
                                                </span>
                                            @else
                                                <span
                                                    class="badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle px-3 py-2">
                                                    <span class="fas fa-exclamation-circle me-1"></span>
                                                    Not Tagged
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            @if ($isActive)
                                                <span class="bio-status-badge bio-status-active">
                                                    <span class="fas fa-check"></span>
                                                    Active
                                                </span>
                                            @else
                                                <span class="bio-status-badge bio-status-inactive">
                                                    <span class="fas fa-ban"></span>
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>

                                        <td class="bio-source-col">
                                            <div class="bio-source-card">
                                                <div class="bio-source-line">
                                                    <span>Name</span>
                                                    <strong>{{ $sourceName }}</strong>
                                                </div>

                                                <div class="bio-source-line">
                                                    <span>CrossChex</span>
                                                    <strong
                                                        class="font-monospace text-break">{{ $sourceCrosschexId }}</strong>
                                                </div>

                                                <div class="bio-source-line">
                                                    <span>Employee ID</span>
                                                    <strong>{{ $sourceEmployeeId }}</strong>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="bio-device-col">
                                            <div class="fw-semibold text-dark">{{ $deviceName }}</div>
                                            <div class="text-muted fs-11 text-break">
                                                SN: {{ $deviceSn }}
                                            </div>
                                        </td>

                                        <td>
                                            @if ($employeeBiometric->last_check_time)
                                                <div class="fw-semibold text-dark">
                                                    {{ $employeeBiometric->last_check_time->format('M d, Y') }}
                                                </div>
                                                <div class="text-muted fs-11">
                                                    {{ $employeeBiometric->last_check_time->format('h:i A') }}
                                                </div>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            <span class="bio-logs-badge">
                                                {{ number_format($employeeBiometric->total_logs) }}
                                            </span>
                                        </td>

                                        <td class="text-end pe-3">
                                            @can('biometrics.edit')
                                                <a href="{{ route('biometrics.employees.edit', $employeeBiometric) }}"
                                                    class="btn btn-sm btn-falcon-primary">
                                                    <span class="fas fa-edit me-1"></span>
                                                    Edit
                                                </a>
                                            @else
                                                <span class="text-muted fs-10">No access</span>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-6">
                                            <div class="bio-empty-state">
                                                <div class="bio-empty-icon">
                                                    <span class="fas fa-fingerprint"></span>
                                                </div>

                                                <h5 class="mb-1">No biometric employees found</h5>
                                                <p class="text-muted mb-3">
                                                    Sync from Mirasol Biometrics to generate unique employee records.
                                                </p>

                                                @can('biometrics.sync')
                                                    <form method="POST" action="{{ route('biometrics.employees.sync') }}"
                                                        class="d-inline bio-sync-form">
                                                        @csrf

                                                        <button type="submit" class="btn btn-primary bio-sync-button">
                                                            <span class="fas fa-cloud-download-alt me-1"></span>
                                                            Sync from Mirasol
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-white border-top border-200 py-3">
                    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                        <div class="fs-10 text-muted">
                            @if ($employeeBiometrics->total() > 0)
                                Showing {{ $employeeBiometrics->firstItem() }} to {{ $employeeBiometrics->lastItem() }}
                                of {{ number_format($employeeBiometrics->total()) }} unique record(s)
                            @else
                                Showing 0 record(s)
                            @endif
                        </div>

                        @if ($employeeBiometrics->hasPages())
                            <div>
                                {{ $employeeBiometrics->links('pagination.custom') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Info Cards --}}
            <div class="row g-3 mt-3">
                <div class="col-lg-4">
                    <div class="bio-info-card">
                        <div class="bio-icon-circle bio-icon-primary">
                            <span class="fas fa-fingerprint"></span>
                        </div>

                        <div>
                            <h6 class="mb-1">Source</h6>
                            <p class="text-muted fs-10 mb-0">
                                Records are generated from Mirasol CrossChex biometric logs only.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="bio-info-card">
                        <div class="bio-icon-circle bio-icon-warning">
                            <span class="fas fa-user-shield"></span>
                        </div>

                        <div>
                            <h6 class="mb-1">Duplicate Control</h6>
                            <p class="text-muted fs-10 mb-0">
                                Sync should merge records with the same employee ID, employee number, or employee name.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="bio-info-card">
                        <div class="bio-icon-circle bio-icon-success">
                            <span class="fas fa-user-cog"></span>
                        </div>

                        <div>
                            <h6 class="mb-1">Manual Editing</h6>
                            <p class="text-muted fs-10 mb-0">
                                Edit company, display name, employee number, active status, and remarks only.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @push('styles')

        @include('biometrics.employees.styles')
        
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.bio-sync-form').forEach(function(form) {
                    form.addEventListener('submit', function() {
                        const button = form.querySelector('.bio-sync-button');

                        if (!button) {
                            return;
                        }

                        button.disabled = true;
                        button.innerHTML =
                            '<span class="fas fa-spinner fa-spin me-1"></span> Syncing...';
                    });
                });
            });
        </script>
    </div>
@endpush
