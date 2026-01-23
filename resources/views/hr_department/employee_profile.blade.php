    @extends('layouts.app')
    @section('title', $employee->full_name . ' | Employee 201')

    @section('content')
        <div class="container" data-layout="container">

            <div class="card mb-4 shadow-sm profile-header-card">
                <div class="profile-hero rounded-top"></div>

                <div class="d-flex align-items-center p-3 header-flex">

                    {{-- LEFT SIDE: PROFILE PICTURE + NAME --}}
                    <div class="d-flex align-items-center flex-grow-1 profile-left">

                        <div class="me-3">
                            @php
                                $profilePath = $employee->asset?->profile_picture
                                    ? asset('storage/' . $employee->asset->profile_picture)
                                    : asset('assets/img/no-image-default.png');
                            @endphp
                            <img class="rounded-circle" src="{{ $profilePath }}" alt="Profile">
                        </div>

                        <div>
                            <h3 class="mb-1 fw-bold">
                                <i class="fas fa-user mono-icon me-2"></i> {{ $employee->full_name }}
                            </h3>

                            <div class="small-muted">
                                <i class="fas fa-id-badge mono-icon me-1"></i>
                                {{ $employee->position->title ?? 'No Position' }}
                                &nbsp;&nbsp;|&nbsp;&nbsp;
                                <i class="fas fa-building mono-icon me-1"></i>
                                {{ $employee->department->name ?? 'No Department' }}
                                &nbsp;&nbsp;|&nbsp;&nbsp;
                                <i class="fas fa-clipboard-check mono-icon me-1"></i>
                                Status: <strong>{{ $employee->status ?? 'Active' }}</strong>
                                &nbsp;&nbsp;|&nbsp;&nbsp;
                                <i class="fas fa-calendar-alt mono-icon me-1"></i>
                                Hired: <strong>{{ optional($employee->date_hired)->format('M d, Y') ?? '—' }}</strong>
                            </div>

                            <div class="mt-3">
                                <a href="{{ session('employees_back_url', route('employees.staff.index')) }}"
                                    class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left me-1"></i> Back
                                </a>

                                <button class="btn btn-primary btn-sm ms-2" data-bs-toggle="modal"
                                    data-bs-target="#editProfileModal">
                                    <i class="fas fa-edit me-1"></i> Edit Profile
                                </button>

                                <a class="btn btn-outline-dark btn-sm ms-2"
                                    href="{{ route('employees.staff.print', $employee->id) }}" target="_blank">
                                    <i class="fas fa-print me-1"></i> Print 201 (PDF)
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT SIDE: QR --}}
                    <div class="text-end ms-4 qr-side">
                        {!! QrCode::size(90)->style('round')->margin(0)->backgroundColor(255, 255, 255)->generate($employee->employee_id) !!}
                        <div class="small-muted">ID: {{ $employee->employee_id }}</div>
                    </div>

                </div>
            </div>

            {{-- main row --}}
            <div class="row g-3">
                {{-- left: 201 file & timeline --}}
                <div class="col-lg-8">
                    <div class="card mb-3 shadow-sm">
                        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold"><i class="fas fa-folder-open mono-icon me-2"></i> Employee 201 File
                            </h5>
                            <div class="small-muted">Last updated:
                                {{ optional($employee->asset)?->updated_at ? optional($employee->asset->updated_at)->diffForHumans() : '—' }}
                            </div>
                        </div>

                        <div class="card-body">
                            {{-- DISPLAY ONLY (we moved editing into modal) --}}
                            <div id="view201">
                                <div class="row gy-3">
                                    <div class="col-md-6">
                                        <label class="fw-bold">SSS Number</label>
                                        <p class="text-muted">{{ $employee->asset?->sss_number ?? '—' }}</p>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="fw-bold">TIN Number</label>
                                        <p class="text-muted">{{ $employee->asset?->tin_number ?? '—' }}</p>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="fw-bold">PhilHealth</label>
                                        <p class="text-muted">{{ $employee->asset?->philhealth_number ?? '—' }}</p>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="fw-bold">Pag-IBIG</label>
                                        <p class="text-muted">{{ $employee->asset?->pagibig_number ?? '—' }}</p>
                                    </div>

                                    <div class="col-12 mt-2">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label class="fw-bold">Birth Cert.</label>
                                                @if ($employee->asset?->birth_certificate)
                                                    <a href="{{ asset('storage/' . $employee->asset->birth_certificate) }}"
                                                        target="_blank" class="btn btn-sm btn-light border">
                                                        <i class="fas fa-eye me-1 mono-icon"></i> View
                                                    </a>
                                                @else
                                                    <p class="text-muted">—</p>
                                                @endif
                                            </div>

                                            <div class="col-md-4">
                                                <label class="fw-bold">Resume</label>
                                                @if ($employee->asset?->resume)
                                                    <a href="{{ asset('storage/' . $employee->asset->resume) }}"
                                                        target="_blank" class="btn btn-sm btn-light border">
                                                        <i class="fas fa-eye me-1 mono-icon"></i> View
                                                    </a>
                                                @else
                                                    <p class="text-muted">—</p>
                                                @endif
                                            </div>

                                            <div class="col-md-4">
                                                <label class="fw-bold">Contract</label>
                                                @if ($employee->asset?->contract)
                                                    <a href="{{ asset('storage/' . $employee->asset->contract) }}"
                                                        target="_blank" class="btn btn-sm btn-light border">
                                                        <i class="fas fa-eye me-1 mono-icon"></i> View
                                                    </a>
                                                @else
                                                    <p class="text-muted">—</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Notice: editing moved into modal --}}
                            <div class="mt-3">
                                <button class="btn btn-outline-dark btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#edit201Modal">
                                    <i class="fas fa-edit me-1"></i> Edit 201 File
                                </button>
                            </div>

                        </div>
                    </div>

                    {{-- Employment history --}}
                    <div class="card mb-3 shadow-sm">
                        <div class="card-header bg-body-tertiary d-flex justify-content-between">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-stream mono-icon me-2"></i> Employment History
                                Timeline
                            </h6>
                            <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal"
                                data-bs-target="#addHistoryModal">
                                <i class="fas fa-plus me-1"></i> Add
                            </button>
                        </div>

                        <div class="card-body">
                            <div class="timeline">
                                @forelse($employee->histories as $h)
                                    <div class="timeline-item d-flex align-items-start">
                                        <span class="timeline-dot mt-1"></span>
                                        <div>
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <strong>{{ $h->title }}</strong>
                                                    <div class="small-muted">{{ \Str::limit($h->description, 180) }}</div>
                                                </div>
                                                <div class="small-muted text-end">
                                                    <div>{{ optional($h->start_date)->format('M Y') ?? '—' }} -
                                                        {{ optional($h->end_date)->format('M Y') ?? 'Present' }}</div>
                                                    <form
                                                        action="{{ route('employees.staff.history.destroy', [$employee->id, $h->id]) }}"
                                                        method="POST" class="d-inline confirm-delete ms-2">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-sm btn-link text-danger">Remove</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted">No history records.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                {{-- right: attachments --}}
                <div class="col-lg-4">
                    <div class="card mb-3 shadow-sm">
                        <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-paperclip mono-icon me-2"></i> Attachments
                            </h6>
                            <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal"
                                data-bs-target="#uploadAttachmentModal">
                                <i class="fas fa-upload me-1"></i> Upload
                            </button>
                        </div>

                        <div class="card-body">
                            @forelse($employee->attachments as $att)
                                <div class="d-flex mb-3 attachment-row">
                                    <div class="flex-grow-1 me-2 text-truncate">
                                        <i class="fas fa-file mono-icon me-2"></i>

                                        <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank"
                                            class="text-truncate d-inline-block" style="max-width: 180px;">
                                            {{ $att->file_name }}
                                        </a>

                                        <div class="small text-muted">
                                            {{ strtoupper($att->mime_type) }} • {{ round($att->size / 1024, 1) }} KB
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <form
                                            action="{{ route('employees.staff.attachments.destroy', [$employee->id, $att->id]) }}"
                                            method="POST" class="confirm-delete">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>

                                </div>
                            @empty
                                <p class="text-muted">No attachments.</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <div class="small-muted mb-2">Contact</div>
                            <div><i class="fas fa-envelope mono-icon me-2"></i> {{ $employee->email ?? '—' }}</div>
                            <div class="mt-2"><i class="fas fa-phone mono-icon me-2"></i>
                                {{ $employee->phone_number ?? '—' }}</div>

                            <div class="mt-3 small-muted">Company</div>
                            <div><i class="fas fa-building mono-icon me-2"></i>{{ $employee->company ?? '—' }}</div>
                            <div><i class="fas fa-warehouse mono-icon me-2"></i>{{ $employee->garage ?? '—' }}</div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Add History Modal --}}
        <div class="modal fade" id="addHistoryModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('employees.staff.history.store', $employee->id) }}" method="POST"
                    class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add History</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label">Title</label>
                            <input name="title" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="row g-2">
                            <div class="col">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control">
                            </div>
                            <div class="col">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary">Add</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Upload Attachment Modal --}}
        <div class="modal fade" id="uploadAttachmentModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('employees.staff.attachments.store', $employee->id) }}" method="POST"
                    class="modal-content" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Attachment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label">File</label>
                            <input type="file" name="attachment" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- EDIT 201 Modal (with previews) --}}
        <div class="modal fade" id="edit201Modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form action="{{ route('employees.assets.update', $employee->id) }}" method="POST"
                    class="modal-content" enctype="multipart/form-data">
                    @csrf
                    {{-- Note: your controller's updateAssets expects POST and handles storage --}}
                    <div class="modal-header">
                        <h5 class="modal-title">Edit 201 File</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="fw-bold">SSS Number</label>
                                <input type="text" name="sss_number" class="form-control"
                                    value="{{ $employee->asset?->sss_number ?? '' }}">
                            </div>

                            <div class="col-md-4">
                                <label class="fw-bold">TIN Number</label>
                                <input type="text" name="tin_number" class="form-control"
                                    value="{{ $employee->asset?->tin_number ?? '' }}">
                            </div>

                            <div class="col-md-4">
                                <label class="fw-bold">PhilHealth</label>
                                <input type="text" name="philhealth_number" class="form-control"
                                    value="{{ $employee->asset?->philhealth_number ?? '' }}">
                            </div>

                            <div class="col-md-4">
                                <label class="fw-bold">Pag-IBIG</label>
                                <input type="text" name="pagibig_number" class="form-control"
                                    value="{{ $employee->asset?->pagibig_number ?? '' }}">
                            </div>

                            {{-- files with preview/view --}}
                            <div class="col-md-4">
                                <label class="fw-bold">Birth Certificate</label>
                                <div class="d-flex align-items-center gap-2">
                                    @if ($employee->asset?->birth_certificate)
                                        <a href="{{ asset('storage/' . $employee->asset->birth_certificate) }}"
                                            target="_blank" class="btn btn-sm btn-outline-secondary">View</a>
                                    @else
                                        <span class="text-muted">No file</span>
                                    @endif
                                    <input type="file" name="birth_certificate"
                                        class="form-control form-control-sm file-input" data-target="#birthFilename">
                                </div>
                                <div id="birthFilename" class="avatar-file-label mt-1 text-muted">
                                    {{ $employee->asset?->birth_certificate ? basename($employee->asset->birth_certificate) : '' }}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="fw-bold">Resume / CV</label>
                                <div class="d-flex align-items-center gap-2">
                                    @if ($employee->asset?->resume)
                                        <a href="{{ asset('storage/' . $employee->asset->resume) }}" target="_blank"
                                            class="btn btn-sm btn-outline-secondary">View</a>
                                    @else
                                        <span class="text-muted">No file</span>
                                    @endif
                                    <input type="file" name="resume" class="form-control form-control-sm file-input"
                                        data-target="#resumeFilename">
                                </div>
                                <div id="resumeFilename" class="avatar-file-label mt-1 text-muted">
                                    {{ $employee->asset?->resume ? basename($employee->asset->resume) : '' }}</div>
                            </div>

                            <div class="col-md-4">
                                <label class="fw-bold">Employment Contract</label>
                                <div class="d-flex align-items-center gap-2">
                                    @if ($employee->asset?->contract)
                                        <a href="{{ asset('storage/' . $employee->asset->contract) }}" target="_blank"
                                            class="btn btn-sm btn-outline-secondary">View</a>
                                    @else
                                        <span class="text-muted">No file</span>
                                    @endif
                                    <input type="file" name="contract" class="form-control form-control-sm file-input"
                                        data-target="#contractFilename">
                                </div>
                                <div id="contractFilename" class="avatar-file-label mt-1 text-muted">
                                    {{ $employee->asset?->contract ? basename($employee->asset->contract) : '' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary">Save 201</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- EDIT PROFILE Modal --}}
        <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form action="{{ route('employees.update', $employee->id) }}" method="POST" class="modal-content">
                    @csrf @method('PUT')

                    <div class="modal-header">
                        <h5 class="modal-title">Edit Employee Profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Full Name</label>
                                <input type="text" name="full_name" class="form-control"
                                    value="{{ $employee->full_name }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status</label>
                                <select name="status" class="form-control">
                                    <option value="Active" {{ $employee->status === 'Active' ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="Suspended" {{ $employee->status === 'Suspended' ? 'selected' : '' }}>
                                        Suspended</option>
                                    <option value="Terminated" {{ $employee->status === 'Terminated' ? 'selected' : '' }}>
                                        Terminated</option>
                                    <option value="Retrench" {{ $employee->status === 'Retrench' ? 'selected' : '' }}>
                                        Retrench</option>
                                    <option value="Retired" {{ $employee->status === 'Retired' ? 'selected' : '' }}>
                                        Retired</option>
                                    <option value="Resigned" {{ $employee->status === 'Resigned' ? 'selected' : '' }}>
                                        Resigned</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Date Hired</label>
                                <input type="date" name="date_hired" class="form-control"
                                    value="{{ optional($employee->date_hired)->format('Y-m-d') ?? '' }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Company</label>
                                <select name="company" class="form-control" required>
                                    <option value="">-- Select Company --</option>
                                    <option value="Jell Transport"
                                        {{ $employee->company === 'Jell Transport' ? 'selected' : '' }}>
                                        Jell Transport
                                    </option>
                                    <option value="ES Transport"
                                        {{ $employee->company === 'ES Transport' ? 'selected' : '' }}>
                                        ES Transport
                                    </option>
                                    <option value="Kellen Transport"
                                        {{ $employee->company === 'Kellen Transport' ? 'selected' : '' }}>
                                        Kellen Transport
                                    </option>
                                    <option value="Earthstar Transport"
                                        {{ $employee->company === 'Earthstar Transport' ? 'selected' : '' }}>
                                        Earthstar Transport
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Department</label>
                                <select name="department_id" id="editDepartmentSelect" class="form-control">

                                    {{-- Always show the employee's current department first --}}
                                    @if ($employee->department)
                                        <option value="{{ $employee->department->id }}" selected>
                                            {{ $employee->department->name }} (Current)
                                        </option>
                                    @else
                                        <option value="">-- Select department --</option>
                                    @endif

                                    {{-- List ALL departments except the currently selected one --}}
                                    @foreach ($departments as $dept)
                                        @if ($dept->id != $employee->department_id)
                                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>


                            <div class="col-md-6">
                                <label class="form-label fw-bold">Position</label>
                                <select name="position_id" id="editPositionSelect" class="form-control">

                                    {{-- If employee has a position, show it as the first option --}}
                                    @if ($employee->position)
                                        <option value="{{ $employee->position->id }}" selected>
                                            {{ $employee->position->title }}
                                            (Current)
                                        </option>
                                    @else
                                        <option value="">-- Select position --</option>
                                    @endif

                                    {{-- Load positions of the employee’s department --}}
                                    @foreach ($employee->department?->positions ?? [] as $pos)
                                        {{-- Avoid duplicate of the selected position --}}
                                        @if ($employee->position_id != $pos->id)
                                            <option value="{{ $pos->id }}">{{ $pos->title }}</option>
                                        @endif
                                    @endforeach

                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Garage</label>
                                <select name="garage" class="form-control" required>
                                    <option value="Mirasol" {{ $employee->garage === 'Mirasol' ? 'selected' : '' }}>
                                        Mirasol</option>
                                    <option value="Balintawak" {{ $employee->garage === 'Balintawak' ? 'selected' : '' }}>
                                        Balintawak</option>
                                </select>
                            </div>


                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" name="email" class="form-control"
                                    value="{{ $employee->email }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Phone Number</label>
                                <input type="text" name="phone_number" class="form-control" inputmode="numeric"
                                    pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11)"
                                    value="{{ $employee->phone_number }}">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

    @endsection

    @push('scripts')
        <script>
            // show filename next to file input in Edit 201 modal
            document.querySelectorAll('.file-input').forEach(function(el) {
                el.addEventListener('change', function(e) {
                    const target = document.querySelector(this.dataset.target);
                    if (!target) return;
                    const file = this.files[0];
                    target.textContent = file ? file.name : '';
                });
            });
            // --- Dynamic Position Loading for EDIT PROFILE MODAL ---
            document.getElementById('editDepartmentSelect')?.addEventListener('change', function() {
                const deptId = this.value;
                const posSelect = document.getElementById('editPositionSelect');
                const url = "{{ url('/employees/departments') }}/" + deptId + "/positions";

                posSelect.innerHTML = '<option value="">Loading...</option>';

                if (!deptId) {
                    posSelect.innerHTML = '<option value="">-- Select position --</option>';
                    return;
                }

                fetch(url)
                    .then(res => res.json())
                    .then(list => {
                        posSelect.innerHTML = '<option value="">-- Select position --</option>';
                        list.forEach(pos => {
                            posSelect.innerHTML += `<option value="${pos.id}">${pos.title}</option>`;
                        });
                    })
                    .catch(() => {
                        posSelect.innerHTML = '<option value="">-- Select position --</option>';
                    });
            });

            // confirmation for delete actions
            document.querySelectorAll('.confirm-delete').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    if (!confirm('Are you sure?')) e.preventDefault();
                });
            });
        </script>
    @endpush

    @push('styles')
        <style>
            /* small utility tweaks for the header/profile */
            .profile-header-card {
                position: relative;
            }

            .profile-left img {
                width: 120px;
                height: 120px;
                object-fit: cover;
                border-radius: 50%;
            }

            .avatar-file-label {
                font-size: 0.9rem;
            }

            .small-muted {
                color: #6c757d;
            }
        </style>
    @endpush
