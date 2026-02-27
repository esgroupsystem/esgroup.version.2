<div class="modal fade" id="edit201Modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('employees.assets.update', $employee->id) }}"
              method="POST" class="modal-content" enctype="multipart/form-data">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">Edit 201 File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">

                    {{-- SSS --}}
                    <div class="col-md-6">
                        <label class="fw-bold">SSS Number</label>
                        <input type="text" name="sss_number" class="form-control"
                               value="{{ old('sss_number', $employee->asset?->sss_number) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="fw-bold">SSS Date Updated</label>
                        <input type="date" name="sss_updated_at" class="form-control"
                               value="{{ optional($employee->asset?->sss_updated_at)->format('Y-m-d') }}">
                    </div>

                    {{-- TIN --}}
                    <div class="col-md-6">
                        <label class="fw-bold">TIN Number</label>
                        <input type="text" name="tin_number" class="form-control"
                               value="{{ old('tin_number', $employee->asset?->tin_number) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="fw-bold">TIN Date Updated</label>
                        <input type="date" name="tin_updated_at" class="form-control"
                               value="{{ optional($employee->asset?->tin_updated_at)->format('Y-m-d') }}">
                    </div>

                    {{-- PhilHealth --}}
                    <div class="col-md-6">
                        <label class="fw-bold">PhilHealth</label>
                        <input type="text" name="philhealth_number" class="form-control"
                               value="{{ old('philhealth_number', $employee->asset?->philhealth_number) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="fw-bold">PhilHealth Date Updated</label>
                        <input type="date" name="philhealth_updated_at" class="form-control"
                               value="{{ optional($employee->asset?->philhealth_updated_at)->format('Y-m-d') }}">
                    </div>

                    {{-- Pagibig --}}
                    <div class="col-md-6">
                        <label class="fw-bold">Pag-IBIG</label>
                        <input type="text" name="pagibig_number" class="form-control"
                               value="{{ old('pagibig_number', $employee->asset?->pagibig_number) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="fw-bold">Pag-IBIG Date Updated</label>
                        <input type="date" name="pagibig_updated_at" class="form-control"
                               value="{{ optional($employee->asset?->pagibig_updated_at)->format('Y-m-d') }}">
                    </div>

                    {{-- FILE UPLOADS --}}
                    @include('hr_department.employees.partials._file_upload_field', [
                        'label'=>'Birth Certificate',
                        'name'=>'birth_certificate',
                        'value'=>$employee->asset?->birth_certificate
                    ])

                    @include('hr_department.employees.partials._file_upload_field', [
                        'label'=>'Resume',
                        'name'=>'resume',
                        'value'=>$employee->asset?->resume
                    ])

                    @include('hr_department.employees.partials._file_upload_field', [
                        'label'=>'Contract',
                        'name'=>'contract',
                        'value'=>$employee->asset?->contract
                    ])

                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Save 201</button>
            </div>
        </form>
    </div>
</div>