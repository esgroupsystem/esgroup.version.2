@extends('layouts.app')
@section('title', 'Create Job Order')

@section('content')
    <div class="container" data-layout="container">

        <script>
            var isFluid = JSON.parse(localStorage.getItem('isFluid'));
            if (isFluid) {
                var container = document.querySelector('[data-layout]');
                container.classList.remove('container');
                container.classList.add('container-fluid');
            }
        </script>

        {{-- ✅ MASTER FORM START --}}
        <form id="joborderForm" method="POST" action="{{ route('tickets.storejoborder.post') }}" enctype="multipart/form-data">
            @csrf

            <div class="content">

                {{-- Header --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row flex-between-center">
                            <div class="col-md">
                                <h5 class="mb-2 mb-md-0">Create Job Order</h5>
                            </div>
                            <div class="col-auto">
                                {{-- ✅ THIS IS NOW THE REAL SUBMIT BUTTON --}}
                                <button type="submit" class="btn btn-falcon-default btn-sm me-2">Save</button>
                                <a href="{{ route('tickets.joborder.index') }}"
                                    class="btn btn-falcon-danger btn-sm">Exit</a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bus Seat Layout --}}
                <div class="card cover-image mb-3">
                    <img src="{{ asset('assets/img/bus/seat_arrangement.png') }}" class="card-img-top" alt="Bus Layout" />
                </div>

                <div class="row g-0">

                    {{-- LEFT PANEL --}}
                    <div class="col-lg-8 pe-lg-2">

                        {{-- Job Order Details --}}
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0">Job Order Details</h5>
                            </div>

                            <div class="card-body bg-body-tertiary">
                                <div class="row gx-2">
                                    <div class="col-sm-4 mb-3">
                                        <div class="d-flex flex-between-center">
                                            <label class="form-label" for="busnumber">Bus Number
                                                <span class="text-danger">(required)</span>
                                            </label>
                                        </div>

                                        <select class="form-select js-choice" name="body_number" id="busnumber" size="1"
                                            data-options='{"searchEnabled": true, "placeholder": true,"removeItemButton": true }' required>
                                            <option value="">Select Bus...</option>
                                            @foreach ($buses as $bus)
                                                <option value="{{ $bus->body_number }}">
                                                    {{ $bus->name }} — {{ $bus->body_number }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-sm-4 mb-3">
                                        <label class="form-label">Incident Date<span
                                                class="text-danger">(required)</span></label>
                                        <input name="job_datestart" class="form-control datetimepicker"
                                            placeholder="dd/mm/yy" data-options='{"dateFormat":"d/m/y"}' required>
                                    </div>

                                    <div class="col-sm-4 mb-3">
                                        <label class="form-label">Issue Type<span
                                                class="text-danger">(required)</span></label>
                                        <select name="job_type" class="form-select" required>
                                            <option value="ACCIDENT">ACCIDENT</option>
                                                <option value="COLLECTING FARE">COLLECTING FARE</option>
                                                <option value="CUTTING FARE">CUTTING FARE</option>
                                                <option value="RE- ISSUEING TICKET">RE- ISSUEING TICKET</option>
                                                <option value="TAMPERING TICKET">TAMPERING TICKET</option>
                                                <option value="UNREGISTERED TICKET">UNREGISTERED TICKET</option>
                                                <option value="DELAYING ISSUANCE OF TICKET">DELAYING ISSUANCE OF TICKET</option>
                                                <option value="ROLLING TICKETS">ROLLING TICKETS</option>
                                                <option value="REMOVING HEADSTAB OF TICKET">REMOVING HEADSTAB OF TICKET</option>
                                                <option value="USING STUB TICKET">USING STAB TICKET</option>
                                                <option value="WRONG CLOSING / OPEN">WRONG CLOSING / OPEN</option>
                                                <option value="OTHERS">OTHERS</option>
                                        </select>
                                    </div>

                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label">Incident Time Start<span
                                                class="text-danger">(required)</span></label>
                                        <input name="job_time_start" class="form-control datetimepicker" placeholder="H:i"
                                            data-options='{"enableTime":true,"noCalendar":true,"dateFormat":"H:i"}'
                                            required>
                                    </div>

                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label">Incident Time End<span
                                                class="text-danger">(required)</span></label>
                                        <input name="job_time_end" class="form-control datetimepicker" placeholder="H:i"
                                            data-options='{"enableTime":true,"noCalendar":true,"dateFormat":"H:i"}'
                                            required>
                                    </div>

                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label">Driver Name</label>
                                        <input name="driver_name" class="form-control">
                                    </div>

                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label">Conductor Name</label>
                                        <input name="conductor_name" class="form-control">
                                    </div>

                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label">Seat Number</label>
                                        <input name="job_sitNumber" type="number" min="1" max="60"
                                            class="form-control">
                                    </div>

                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label">Direction<span
                                                class="text-danger">(required)</span></label>
                                        <select name="direction" class="form-select">
                                            <option>South Bound</option>
                                            <option>North Bound</option>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Description / Remarks</label>
                                        <textarea name="job_remarks" class="form-control" rows="5"></textarea>
                                    </div>

                                    {{-- ✅ Hidden input for real file upload --}}
                                    <div class="col-12 mt-3">
                                        <input type="file" name="files[]" multiple class="d-none" id="hiddenFiles">
                                    </div>

                                </div>
                            </div>
                        </div>

                        {{-- Dropzone UI --}}
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0">Upload Photos</h5>
                            </div>

                            <div class="card-body bg-body-tertiary">

                                {{-- ✅ Dropzone PREVIEW ONLY (no form tag) --}}
                                <div class="dropzone dropzone-multiple p-0" id="joborder-dropzone">
                                    <div class="fallback">
                                        <input type="file" multiple />
                                    </div>

                                    <div class="dz-message">
                                        <img class="me-2" src="{{ asset('assets/img/icons/cloud-upload.svg') }}"
                                            width="25" alt="" />
                                        Drop your files here
                                    </div>

                                    <div class="dz-preview dz-preview-multiple m-0 d-flex flex-column"></div>
                                </div>

                            </div>
                        </div>

                    </div>

                    {{-- RIGHT PANEL --}}
                    <div class="col-lg-4 ps-lg-2">

                        <div class="sticky-sidebar">
                            <div class="card mb-lg-0">
                                <div class="card-header">
                                    <h5 class="mb-0">Additional Info</h5>
                                </div>

                                <div class="card-body bg-body-tertiary">

                                    <div class="mb-3">
                                        <label class="form-label">Reported By<span
                                                class="text-danger">(automatic)</span></label>
                                        <input type="text" class="form-control"
                                            value="{{ auth()->user()->full_name }}" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Status<span
                                                class="text-danger">(automatic)</span></label>
                                        <select name="job_status" class="form-select" readonly required>
                                            <option>Pending</option>
                                            <option>Assigned</option>
                                            <option>In Progress</option>
                                            <option>Completed</option>
                                        </select>
                                    </div>

                                </div>

                            </div>
                        </div>

                    </div>

                </div>
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="row justify-content-between align-items-center">
                            <div class="col-md">
                                <h5 class="mb-2 mb-md-0">Nice Job! You're almost done</h5>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-falcon-default btn-sm me-2">Save</button>
                                <button class="btn btn-falcon-danger btn-sm">Exit </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ✅ MASTER FORM END --}}
        </form>

        {{-- Footer --}}
        <footer class="footer">
            <div class="row g-0 justify-content-between fs-10 mt-4 mb-3">
                <div class="col-12 col-sm-auto text-center">
                    <p class="mb-0 text-600">
                        ES Group | Job Order System
                    </p>
                </div>
            </div>
        </footer>

    </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            let dz = new Dropzone("#joborder-dropzone", {
                url: "#",
                autoProcessQueue: false,
                maxFilesize: 5,
                addRemoveLinks: true
            });

            dz.on("addedfile", function(file) {
                // Attach files to hidden input for real form submission
                let fileInput = document.getElementById("hiddenFiles");
                let dt = new DataTransfer();

                for (let i = 0; i < dz.files.length; i++) {
                    dt.items.add(dz.files[i]);
                }

                fileInput.files = dt.files;
            });

        });
    </script>
@endpush
