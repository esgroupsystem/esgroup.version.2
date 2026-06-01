<div class="card mb-3 shadow-sm border-0">
    <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-900">
            <i class="fas fa-folder-open text-primary me-2"></i>
            Employee Benefits and Assets
        </h5>
        <div class="fs--2 text-500">
            Last updated: {{ $employee->asset?->updated_at?->diffForHumans() ?? '—' }}
        </div>
    </div>

    <div class="card-body">
        <div class="row gy-3">

            {{-- Identification Numbers --}}
            @include('hr_department.employees.partials._view_number', [
                'label' => 'SSS Number',
                'value' => $employee->asset?->sss_number,
                'date' => $employee->asset?->sss_updated_at,
            ])
            @include('hr_department.employees.partials._view_number', [
                'label' => 'TIN Number',
                'value' => $employee->asset?->tin_number,
                'date' => $employee->asset?->tin_updated_at,
            ])
            @include('hr_department.employees.partials._view_number', [
                'label' => 'PhilHealth',
                'value' => $employee->asset?->philhealth_number,
                'date' => $employee->asset?->philhealth_updated_at,
            ])
            @include('hr_department.employees.partials._view_number', [
                'label' => 'Pag-IBIG',
                'value' => $employee->asset?->pagibig_number,
                'date' => $employee->asset?->pagibig_updated_at,
            ])

            {{-- Files --}}
            <div class="col-12 mt-3">
                <div class="row g-3">
                    @include('hr_department.employees.partials._view_file', [
                        'label' => 'Birth Certificate',
                        'path' => $employee->asset?->birth_certificate,
                        'date' => $employee->asset?->birth_certificate_updated_at,
                    ])
                    @include('hr_department.employees.partials._view_file', [
                        'label' => 'Resume',
                        'path' => $employee->asset?->resume,
                        'date' => $employee->asset?->resume_updated_at,
                    ])
                    @include('hr_department.employees.partials._view_file', [
                        'label' => 'Contract',
                        'path' => $employee->asset?->contract,
                        'date' => $employee->asset?->contract_updated_at,
                    ])
                </div>
            </div>

        </div>

        {{-- Edit Button --}}
        <div class="mt-3">
            <button class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#edit201Modal">
                <i class="fas fa-edit me-1"></i> Edit 201 File
            </button>
        </div>
    </div>
</div>
