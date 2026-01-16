@extends('layouts.app')
@section('title', 'Job Order Details')

@section('content')
    <div class="container" data-layout="container">

        <script>
            var isFluid = JSON.parse(localStorage.getItem("isFluid"));
            if (isFluid) {
                var container = document.querySelector("[data-layout]");
                container.classList.remove("container");
                container.classList.add("container-fluid");
            }
        </script>

        <div class="content">

            {{-- ============================= --}}
            {{-- HEADER CARD --}}
            {{-- ============================= --}}
            <div class="card mb-3">
                <div class="card-header">
                    <div class="row">

                        {{-- Left section --}}
                        <div class="col">
                            <h5 class="mb-2">
                                Job Order #{{ str_pad($job->id, 5, '0', STR_PAD_LEFT) }}
                            </h5>

                            <p class="text-muted mb-2">
                                Created by <strong>{{ $job->job_creator }}</strong>
                            </p>

                            {{-- Add note --}}
                            <button class="btn btn-falcon-default btn-sm" data-bs-toggle="modal"
                                data-bs-target="#addNoteModal">
                                <span class="fas fa-plus fs-11 me-1"></span>Add note
                            </button>

                            {{-- Options --}}
                            <button class="btn btn-falcon-default btn-sm dropdown-toggle ms-2 dropdown-caret-none"
                                type="button" data-bs-toggle="dropdown">
                                <span class="fas fa-ellipsis-h"></span>
                            </button>

                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('tickets.joborder.index') }}">Back</a>
                                <a class="dropdown-item" href="{{ route('tickets.joborder.print', $job->id) }}" target="_blank" class="btn btn-outline-dark btn-sm">Print</a>
                                {{-- <a class="dropdown-item" href="#">Report</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#">Delete</a> --}}
                            </div>
                        </div>

                        {{-- RIGHT STATUS + ACTION BUTTONS --}}
                        <div class="col-auto d-none d-sm-flex flex-column align-items-end">

                            {{-- STATUS --}}
                            <h6 class="text-uppercase text-600 mb-2">
                                Status
                                @if ($job->job_status === 'Pending')
                                    <span class="badge badge-subtle-warning ms-2">Pending</span>
                                @elseif($job->job_status === 'In Progress')
                                    <span class="badge badge-subtle-info ms-2">In Progress</span>
                                @elseif($job->job_status === 'Completed')
                                    <span class="badge badge-subtle-success ms-2">Completed</span>
                                @else
                                    <span class="badge badge-subtle-secondary ms-2">{{ $job->job_status }}</span>
                                @endif
                            </h6>

                            {{-- ACTION BUTTONS BELOW STATUS --}}
                            @if ($job->job_status === 'Pending')
                                <form action="{{ route('tickets.joborder.accept', $job->id) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-sm btn-success mt-1 btn-heartbeat">
                                        <i class="fas fa-check-circle me-1"></i> Accept Task
                                    </button>
                                </form>
                            @elseif ($job->job_status === 'In Progress')
                                <form action="{{ route('tickets.joborder.done', $job->id) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-sm btn-primary mt-1 btn-heartbeat">
                                        <i class="fas fa-flag-checkered me-1"></i> Mark as Done
                                    </button>
                                </form>
                            @endif
                        </div>

                    </div>
                </div>

                {{-- Activity row --}}
                <div class="card-body border-top">
                    <div class="d-flex">
                        <span class="fas fa-user text-success me-2"></span>
                        <div class="flex-1">
                            <p class="mb-0">Job Order was created</p>
                            <p class="fs-10 mb-0 text-600">
                                {{ optional($job->created_at)->format('Y-m-d H:i:s') ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>

            </div> {{-- END HEADER CARD --}}

            {{-- ============================= --}}
            {{-- ✅ NOTES CARD (Moved to correct place) --}}
            {{-- ============================= --}}
            @if ($job->notes->count())
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">Notes</h5>
                    </div>

                    <div class="card-body">
                        @foreach ($job->notes as $note)
                            <div class="border-bottom pb-2 mb-3">
                                <strong>{{ $note->reason }}</strong>
                                <span class="text-muted fs-10"> — {{ $note->created_at->format('Y-m-d H:i') }}</span>

                                @if ($note->details)
                                    <p class="text-muted mb-1">{{ $note->details }}</p>
                                @endif

                                <small class="text-primary">
                                    Added by {{ optional($note->user)->full_name ?? 'System' }}
                                </small>
                            </div>
                        @endforeach
                    </div> 
                </div>
            @endif

            {{-- ============================= --}}
            {{-- DETAILS CARD --}}
            {{-- ============================= --}}
            <div class="card mb-3">
                <form method="POST" action="{{ route('tickets.joborder.update', $job->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="mb-0">Details</h5>
                            </div>

                            <div class="col-auto" id="detailActions">
                                <button type="button" class="btn btn-falcon-default btn-sm" id="editBtn">
                                    <span class="fas fa-pencil-alt fs-11 me-1"></span>Update details
                                </button>

                                <button type="submit" class="btn btn-success btn-sm d-none" id="saveBtn" disabled>
                                    <i class="fas fa-save me-1"></i> Update
                                </button>

                                <button class="btn btn-danger btn-sm d-none" id="cancelBtn">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body bg-body-tertiary border-top">
                        <div class="row">

                            {{-- LEFT --}}
                            <div class="col-lg col-xxl-5">
                                <h6 class="fw-semi-bold ls mb-3 text-uppercase">Job Information</h6>

                                <div class="row mb-2">
                                    <div class="col-5 col-sm-4 fw-semi-bold">Job Type</div>
                                    <div class="col">
                                        <span class="viewMode">{{ $job->job_type }}</span>

                                        <select name="job_type" class="form-control editMode d-none">
                                            <option value="ACCIDENT" {{ $job->job_type == 'ACCIDENT' ? 'selected' : '' }}>
                                                ACCIDENT</option>
                                            <option value="COLLECTING FARE"
                                                {{ $job->job_type == 'COLLECTING FARE' ? 'selected' : '' }}>COLLECTING FARE
                                            </option>
                                            <option value="CUTTING FARE"
                                                {{ $job->job_type == 'CUTTING FARE' ? 'selected' : '' }}>CUTTING FARE
                                            </option>
                                            <option value="RE- ISSUEING TICKET"
                                                {{ $job->job_type == 'RE- ISSUEING TICKET' ? 'selected' : '' }}>RE-
                                                ISSUEING TICKET</option>
                                            <option value="TAMPERING TICKET"
                                                {{ $job->job_type == 'TAMPERING TICKET' ? 'selected' : '' }}>TAMPERING
                                                TICKET</option>
                                            <option value="UNREGISTERED TICKET"
                                                {{ $job->job_type == 'UNREGISTERED TICKET' ? 'selected' : '' }}>
                                                UNREGISTERED TICKET</option>
                                            <option value="DELAYING ISSUANCE OF TICKET"
                                                {{ $job->job_type == 'DELAYING ISSUANCE OF TICKET' ? 'selected' : '' }}>
                                                DELAYING ISSUANCE OF TICKET</option>
                                            <option value="ROLLING TICKETS"
                                                {{ $job->job_type == 'ROLLING TICKETS' ? 'selected' : '' }}>ROLLING TICKETS
                                            </option>
                                            <option value="REMOVING HEADSTAB OF TICKET"
                                                {{ $job->job_type == 'REMOVING HEADSTAB OF TICKET' ? 'selected' : '' }}>
                                                REMOVING HEADSTAB OF TICKET</option>
                                            <option value="USING STUB TICKET"
                                                {{ $job->job_type == 'USING STUB TICKET' ? 'selected' : '' }}>USING STUB
                                                TICKET</option>
                                            <option value="WRONG CLOSING / OPEN"
                                                {{ $job->job_type == 'WRONG CLOSING / OPEN' ? 'selected' : '' }}>WRONG
                                                CLOSING / OPEN</option>
                                            <option value="OTHERS" {{ $job->job_type == 'OTHERS' ? 'selected' : '' }}>
                                                OTHERS</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-5 col-sm-4 fw-semi-bold">Date</div>
                                    <div class="col">
                                        <span class="viewMode">{{ $job->job_datestart }}</span>

                                        @php
                                            function safeDate($date)
                                            {
                                                try {
                                                    return \Carbon\Carbon::parse($date)->format('Y-m-d');
                                                } catch (\Exception $e1) {
                                                    try {
                                                        return \Carbon\Carbon::createFromFormat('d/m/y', $date)->format(
                                                            'Y-m-d',
                                                        );
                                                    } catch (\Exception $e2) {
                                                        return ''; // fallback
                                                    }
                                                }
                                            }
                                        @endphp

                                        <input type="date" name="job_datestart" class="form-control editMode d-none"
                                            value="{{ safeDate($job->job_datestart) }}">
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-5 col-sm-4 fw-semi-bold">Time</div>
                                    <div class="col">
                                        <span class="viewMode">{{ $job->job_time_start }} –
                                            {{ $job->job_time_end }}</span>

                                        <input type="time" name="job_time_start" class="form-control editMode d-none"
                                            value="{{ \Carbon\Carbon::parse($job->job_time_start)->format('H:i') }}">

                                        <input type="time" name="job_time_end"
                                            class="form-control editMode d-none mt-1"
                                            value="{{ \Carbon\Carbon::parse($job->job_time_end)->format('H:i') }}">
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-5 col-sm-4 fw-semi-bold">Direction</div>
                                    <div class="col">
                                        <span class="viewMode">{{ $job->direction }}</span>

                                        <select name="direction" class="form-control editMode d-none">
                                            <option value="South Bound"
                                                {{ $job->direction == 'South Bound' ? 'selected' : '' }}>South Bound
                                            </option>
                                            <option value="North Bound"
                                                {{ $job->direction == 'North Bound' ? 'selected' : '' }}>North Bound
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                @if ($job->job_sitNumber)
                                    <div class="row mb-2">
                                        <div class="col-5 col-sm-4 fw-semi-bold">Seat No.</div>
                                        <div class="col">
                                            <span class="viewMode">{{ $job->job_sitNumber }}</span>

                                            <input type="text" name="job_sitNumber"
                                                class="form-control editMode d-none" value="{{ $job->job_sitNumber }}">
                                        </div>
                                    </div>
                                @endif

                                <div class="row mb-2">
                                    <div class="col-5 col-sm-4 fw-semi-bold">Assigned To</div>
                                    <div class="col">{{ $job->job_assign_person ?? 'N/A' }}</div>
                                </div>

                                @if ($job->job_remarks)
                                    <div class="row mb-2">
                                        <div class="col-5 col-sm-4 fw-semi-bold">Remarks</div>
                                        <div class="col">
                                            <span class="viewMode">{{ $job->job_remarks }}</span>

                                            <input type="text" name="job_remarks" class="form-control editMode d-none"
                                                value="{{ $job->job_remarks }}">
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- RIGHT --}}
                            <div class="col-lg col-xxl-5 mt-4 mt-lg-0 offset-xxl-1">

                                <h6 class="fw-semi-bold ls mb-3 text-uppercase">Bus Information</h6>

                                @if ($job->bus)
                                    <div class="row mb-2">
                                        <div class="col-5 col-sm-4 fw-semi-bold">Bus Name</div>
                                        <div class="col">{{ $job->bus->name }}</div>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-5 col-sm-4 fw-semi-bold">Body No.</div>
                                        <div class="col">{{ $job->bus->body_number }}</div>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-5 col-sm-4 fw-semi-bold">Plate No.</div>
                                        <div class="col">{{ $job->bus->plate_number }}</div>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-5 col-sm-4 fw-semi-bold">Garage</div>
                                        <div class="col">{{ $job->bus->garage }}</div>
                                    </div>
                                @else
                                    <p class="text-muted fst-italic">No bus linked</p>
                                @endif

                                <h6 class="fw-semi-bold ls mt-4 mb-3 text-uppercase">People</h6>

                                <div class="row mb-2">
                                    <div class="col-5 col-sm-4 fw-semi-bold">Driver</div>
                                    <div class="col">
                                        <span class="viewMode">{{ $job->driver_name ?? 'N/A' }}</span>

                                        <input type="text" name="driver_name" class="form-control editMode d-none"
                                            value="{{ $job->driver_name ?? '' }}">
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-5 col-sm-4 fw-semi-bold">Conductor</div>
                                    <div class="col">
                                        <span class="viewMode">{{ $job->conductor_name ?? 'N/A' }}</span>

                                        <input type="text" name="conductor_name" class="form-control editMode d-none"
                                            value="{{ $job->conductor_name ?? '' }}">
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>

                </form>

            </div>

            {{-- ============================= --}}
            {{-- FILES SECTION (Always Visible) --}}
            {{-- ============================= --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Attached Files</h5>

                    {{-- ✅ Add Files Button --}}
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addFileModal">
                        <i class="fas fa-upload me-1"></i> Add Files
                    </button>
                </div>

                <div class="card-body">
                    <div class="row">
                        @if ($job->files->count() > 0)
                            @foreach ($job->files as $file)
                                <div class="col-md-4 mb-3">
                                    <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank">
                                        <div class="border rounded p-2 text-center bg-light">
                                            <i class="fas fa-file-image fa-2x text-primary mb-2"></i>
                                            <p class="mb-0">{{ $file->file_name }}</p>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        @else
                            <div class="col-12 text-center text-muted py-3">
                                <i class="fas fa-folder-open fa-2x mb-2"></i>
                                <p class="mb-0">No files attached yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ✅ Add File Modal (always available) --}}
            <div class="modal fade" id="addFileModal" tabindex="-1">
                <div class="modal-dialog">
                    <form id="uploadForm" enctype="multipart/form-data">
                        @csrf

                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Upload File</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <label class="fw-bold">Attach Files</label>
                                <input type="file" name="files[]" id="fileInput" class="form-control mb-3" multiple
                                    required>

                                {{-- Progress Bar --}}
                                <div class="progress d-none" id="uploadProgressWrapper">
                                    <div id="uploadProgress"
                                        class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                        style="width: 0%">0%</div>
                                </div>

                                <div class="mt-2 text-center text-muted" id="uploadStatus"></div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" id="uploadBtn" class="btn btn-primary btn-sm">Upload</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ============================= --}}
            {{-- LOGS --}}
            {{-- ============================= --}}
            <div class="card mb-5">
                <div class="card-header">
                    <h5 class="mb-0">Logs</h5>
                </div>

                <div class="card-body border-top p-0">

                    <div id="logList"
                        data-list='{
                "valueNames": ["log-user", "log-action", "log-meta", "log-date"],
                "page": 5,
                "pagination": true
            }'>

                        <div class="list">

                            @forelse($logs as $log)

                                @php
                                    $meta = is_string($log->meta)
                                        ? json_decode($log->meta, true)
                                        : (is_array($log->meta)
                                            ? $log->meta
                                            : []);
                                    $user = optional($log->user)->full_name ?? 'System';
                                @endphp

                                <div class="row g-0 align-items-center border-bottom py-3 px-3">

                                    {{-- ICON --}}
                                    <div class="col-md-auto pe-3">
                                        <span class="fas fa-history text-primary fs-6"></span>
                                    </div>

                                    {{-- MAIN --}}
                                    <div class="col-md">
                                        <div class="d-flex flex-column">

                                            {{-- USER --}}
                                            <span class="fw-bold log-user">
                                                {{ $user }}
                                            </span>

                                            {{-- ACTION --}}
                                            <span class="badge bg-info-subtle text-info mt-1 mb-2 log-action"
                                                style="width: fit-content;">
                                                {{ ucfirst($log->action) }}
                                            </span>

                                            {{-- META --}}
                                            <small class="text-muted log-meta">
                                                @if (is_array($meta) && count($meta))
                                                    @foreach ($meta as $key => $value)
                                                        {{-- If simple string --}}
                                                        @if (is_string($value))
                                                            <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                                            {{ $value }}

                                                            {{-- If array from update logs --}}
                                                        @elseif (is_array($value) && isset($value['old'], $value['new']))
                                                            <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                                            <span class="text-danger">[Old: {{ $value['old'] }}]</span> →
                                                            <span class="text-success">[New: {{ $value['new'] }}]</span>

                                                            {{-- Unknown data format --}}
                                                        @else
                                                            <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                                            {{ json_encode($value) }}
                                                        @endif

                                                        @if (!$loop->last)
                                                            <br>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    <i>No details</i>
                                                @endif
                                            </small>


                                        </div>
                                    </div>

                                    {{-- DATE --}}
                                    <div class="col-md-auto text-end">
                                        <span class="text-muted fs-10 log-date">
                                            {{ $log->created_at->format('M d, Y h:i A') }}
                                        </span>
                                    </div>

                                </div>

                            @empty
                                <p class="text-center py-3 text-muted">No logs available.</p>
                            @endforelse

                        </div>

                        {{-- PAGINATION --}}
                        <div class="d-flex justify-content-center my-3">
                            <button class="btn btn-sm btn-falcon-default me-1" data-list-pagination="prev">
                                <span class="fas fa-chevron-left"></span>
                            </button>

                            <ul class="pagination mb-0"></ul>

                            <button class="btn btn-sm btn-falcon-default ms-1" data-list-pagination="next">
                                <span class="fas fa-chevron-right"></span>
                            </button>
                        </div>

                    </div>
                </div>

                {{-- ============================= --}}
                {{-- ADD NOTE MODAL --}}
                {{-- ============================= --}}
                <div class="modal fade" id="addNoteModal" tabindex="-1">
                    <div class="modal-dialog">
                        <form method="POST" action="{{ route('tickets.joborder.addnote', $job->id) }}">
                            @csrf
                            <div class="modal-content">

                                <div class="modal-header">
                                    <h5 class="modal-title">Add Note</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">

                                    <label class="fw-bold">Select Reason</label>
                                    <div class="mb-2">
                                        <label><input type="radio" name="reason" value="Defective DVR" required>
                                            Defective DVR</label><br>
                                        <label><input type="radio" name="reason" value="Camera not working"> Camera
                                            not working</label><br>
                                        <label><input type="radio" name="reason" value="Weak signal / interference">
                                            Weak signal / interference</label><br>
                                        <label><input type="radio" name="reason" value="Other"> Other</label>
                                    </div>

                                    <label class="fw-bold mt-2">Details (optional)</label>
                                    <textarea name="details" class="form-control" rows="3" placeholder="Additional details..."></textarea>

                                </div>

                                <div class="modal-footer">
                                    <button class="btn btn-primary btn-sm">Add Note</button>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    @endsection
    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function() {

                const editBtn = document.getElementById("editBtn");
                const saveBtn = document.getElementById("saveBtn");
                const cancelBtn = document.getElementById("cancelBtn");

                const viewFields = document.querySelectorAll(".viewMode");
                const editFields = document.querySelectorAll(".editMode");

                // Store original valid values
                editFields.forEach(input => {
                    input.dataset.original = input.value;
                });

                // Detect changes
                editFields.forEach(input => {
                    input.addEventListener("input", function() {
                        saveBtn.disabled = false;
                    });
                });

                // Enable edit mode
                editBtn.addEventListener("click", function() {
                    editBtn.classList.add("d-none");
                    saveBtn.classList.remove("d-none");
                    cancelBtn.classList.remove("d-none");

                    viewFields.forEach(v => v.classList.add("d-none"));
                    editFields.forEach(e => e.classList.remove("d-none"));
                });

                // Cancel edit properly
                cancelBtn.addEventListener("click", function() {

                    editBtn.classList.remove("d-none");
                    saveBtn.classList.add("d-none");
                    cancelBtn.classList.add("d-none");
                    saveBtn.disabled = true;

                    // Restore each input's original valid value
                    editFields.forEach(e => {
                        e.classList.add("d-none");
                        e.value = e.dataset.original;
                    });

                    viewFields.forEach(v => v.classList.remove("d-none"));
                });

            });
        </script>

        <style>
            @keyframes heartbeat {
                0% {
                    transform: scale(1);
                }

                25% {
                    transform: scale(1.07);
                }

                40% {
                    transform: scale(0.97);
                }

                60% {
                    transform: scale(1.05);
                }

                100% {
                    transform: scale(1);
                }
            }

            .btn-heartbeat {
                animation: heartbeat 1.4s infinite;
            }
        </style>

        <script>
            document.getElementById("uploadBtn").addEventListener("click", function() {

                const form = document.getElementById("uploadForm");
                const formData = new FormData(form);

                const progressBar = document.getElementById("uploadProgress");
                const progressWrapper = document.getElementById("uploadProgressWrapper");
                const statusText = document.getElementById("uploadStatus");

                progressWrapper.classList.remove("d-none");
                statusText.innerHTML = "Uploading...";

                let xhr = new XMLHttpRequest();

                xhr.open("POST", "{{ route('tickets.joborder.addfile', $job->id) }}", true);

                xhr.upload.addEventListener("progress", function(e) {
                    if (e.lengthComputable) {
                        let percent = Math.round((e.loaded / e.total) * 100);
                        progressBar.style.width = percent + "%";
                        progressBar.innerHTML = percent + "%";
                    }
                });

                xhr.onload = function() {
                    if (xhr.status === 200) {
                        progressBar.classList.remove("bg-danger");
                        progressBar.classList.add("bg-success");
                        statusText.innerHTML = "<span class='text-success'>Upload completed!</span>";

                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        progressBar.classList.add("bg-danger");
                        statusText.innerHTML = "<span class='text-danger'>Upload failed!</span>";
                    }
                };

                xhr.onerror = function() {
                    progressBar.classList.add("bg-danger");
                    statusText.innerHTML = "<span class='text-danger'>Upload failed (network error)</span>";
                };

                xhr.send(formData);
            });
        </script>
    @endpush
