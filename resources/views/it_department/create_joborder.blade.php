@extends('layouts.app')
@section('title', 'Tickets Job Order')

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
        <div class="content">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row flex-between-center">
                        <div class="col-md">
                            <h5 class="mb-2 mb-md-0">Create Event</h5>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-falcon-default btn-sm me-2" role="button">Save</button>
                            <button class="btn btn-falcon-danger btn-sm" role="button">Exit</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card cover-image mb-3"><img src="{{ asset('assets/img/bus/seat_arrangement.png') }}" alt="" />yy</div>
            <div class="row g-0">
                <div class="col-lg-8 pe-lg-2">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="mb-0">Event Details</h5>
                        </div>
                        <div class="card-body bg-body-tertiary">
                            <form>
                                <div class="row gx-2">
                                    <div class="col-12 mb-3">
                                        <label class="form-label" for="event-name">Event Title</label>
                                        <input class="form-control" id="event-name" type="text"
                                            placeholder="Event Title" />
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="start-date">Start Date</label>
                                        <input class="form-control datetimepicker" id="start-date" type="text"
                                            placeholder="dd/mm/yy"
                                            data-options='{"dateFormat":"d/m/y","disableMobile":true}' />
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="start-time">Start Time</label>
                                        <input class="form-control datetimepicker" id="start-time" type="text"
                                            placeholder="H:i"
                                            data-options='{"enableTime":true,"noCalendar":true,"dateFormat":"H:i","disableMobile":true}' />
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="end-date">End Date</label>
                                        <input class="form-control datetimepicker" id="end-date" type="text"
                                            placeholder="dd/mm/yy"
                                            data-options='{"dateFormat":"d/m/y","disableMobile":true}' />
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="end-time">End Time</label>
                                        <input class="form-control datetimepicker" id="end-time" type="text"
                                            placeholder="H:i"
                                            data-options='{"enableTime":true,"noCalendar":true,"dateFormat":"H:i","disableMobile":true}' />
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label" for="registration-deadline">Registration Deadline</label>
                                        <input class="form-control datetimepicker" id="registration-deadline" type="text"
                                            placeholder="dd/mm/yy"
                                            data-options='{"dateFormat":"d/m/y","disableMobile":true}' />
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label" for="time-zone">Timezone
                                        </label>
                                        <select class="form-select" id="time-zone">
                                            <option>GMT-12:00 Etc/GMT-12</option>
                                            <option>GMT-11:00 Etc/GMT-11</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <div class="border-bottom border-dashed my-3"></div>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="event-venue">Venue</label>
                                        <input class="form-control" id="event-venue" type="text" placeholder="Venue" />
                                        <button class="btn btn-link btn-sm btn p-0" type="button">Online Event</button>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="event-address">Address</label>
                                        <input class="form-control" id="event-address" type="text"
                                            placeholder="Address" />
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <label class="form-label" for="event-city">City</label>
                                        <input class="form-control" id="event-city" type="text" placeholder="City" />
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <label class="form-label" for="event-state">State</label>
                                        <input class="form-control" id="event-state" type="text"
                                            placeholder="State" />
                                    </div>
                                    <div class="col-sm-4 mb-3">
                                        <label class="form-label" for="event-country">Country</label>
                                        <input class="form-control" id="event-country" type="text"
                                            placeholder="Country" />
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="event-description">Description</label>
                                        <textarea class="form-control" id="event-description" rows="6"></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="mb-0">Schedule</h5>
                        </div>
                        <div class="card-body bg-body-tertiary">
                            <div class="border rounded-1 position-relative bg-body-emphasis p-3">
                                <div class="position-absolute end-0 top-0 mt-2 me-3 z-1">
                                    <button class="btn btn-link btn-sm p-0" type="button"><span
                                            class="fas fa-times-circle text-danger"
                                            data-fa-transform="shrink-1"></span></button>
                                </div>
                                <div class="row gx-2">
                                    <div class="col-12 mb-3">
                                        <label class="form-label" for="schedule-title">Title</label>
                                        <input class="form-control form-control-sm" id="schedule-title" type="text"
                                            placeholder="Title" />
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="schedule-start-date">Start Date</label>
                                        <input class="form-control form-control-sm datetimepicker"
                                            id="schedule-start-date" type="text" placeholder="dd/mm/yy"
                                            data-options='{"dateFormat":"d/m/y","enableTime":false}' />
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label class="form-label" for="schedule-start-time">Start Time</label>
                                        <input class="form-control form-control-sm datetimepicker"
                                            id="schedule-start-time" type="text" placeholder="H:i"
                                            data-options='{"enableTime":true,"noCalendar":true,"dateFormat":"H:i"}' />
                                    </div>
                                    <div class="col-sm-6 mb-3 mb-sm-0">
                                        <label class="form-label" for="schedule-end-date">End Date</label>
                                        <input class="form-control form-control-sm datetimepicker" id="schedule-end-date"
                                            type="text" placeholder="dd/mm/yy"
                                            data-options='{"dateFormat":"d/m/y","enableTime":false}' />
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label" for="schedule-end-time">End Time</label>
                                        <input class="form-control form-control-sm datetimepicker" id="schedule-end-time"
                                            type="text" placeholder="H:i"
                                            data-options='{"enableTime":true,"noCalendar":true,"dateFormat":"H:i"}' />
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-falcon-default btn-sm mt-2" type="button"><span
                                    class="fas fa-plus fs-11 me-1" data-fa-transform="up-1"></span>Add Item </button>
                        </div>
                    </div>
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="mb-0">Upload Photos</h5>
                        </div>
                        <div class="card-body bg-body-tertiary">
                            <form class="dropzone dropzone-multiple p-0" id="my-awesome-dropzone"action="#!">
                                <div class="fallback">
                                    <input name="file" type="file" multiple="multiple" />
                                </div>
                                <div class="dz-message" data-dz-message="data-dz-message"> <img class="me-2"
                                        src="../../assets/img/icons/cloud-upload.svg" width="25"
                                        alt="" />Drop your files here</div>
                                <div class="dz-preview dz-preview-multiple m-0 d-flex flex-column">
                                    <div
                                        class="d-flex media align-items-center mb-3 pb-3 border-bottom btn-reveal-trigger">
                                        <img class="dz-image" src="../../assets/img/generic/image-file-2.png"
                                            alt="..." data-dz-thumbnail="data-dz-thumbnail" />
                                        <div class="flex-1 d-flex flex-between-center">
                                            <div>
                                                <h6 data-dz-name="data-dz-name"></h6>
                                                <div class="d-flex align-items-center">
                                                    <p class="mb-0 fs-10 text-400 lh-1" data-dz-size="data-dz-size"></p>
                                                    <div class="dz-progress"><span class="dz-upload"
                                                            data-dz-uploadprogress=""></span></div>
                                                </div>
                                            </div>
                                            <div class="dropdown font-sans-serif">
                                                <button
                                                    class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal dropdown-caret-none"
                                                    type="button" data-bs-toggle="dropdown" aria-haspopup="true"
                                                    aria-expanded="false"><span class="fas fa-ellipsis-h"></span></button>
                                                <div class="dropdown-menu dropdown-menu-end border py-2"><a
                                                        class="dropdown-item" href="#!"
                                                        data-dz-remove="data-dz-remove">Remove File</a></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 ps-lg-2">
                    <div class="sticky-sidebar">
                        <div class="card mb-lg-0">
                            <div class="card-header">
                                <h5 class="mb-0">Other Info</h5>
                            </div>
                            <div class="card-body bg-body-tertiary">
                                <div class="mb-3">
                                    <div class="d-flex flex-between-center">
                                        <label class="form-label" for="organizer">Organizer</label>
                                        <button class="btn btn-link btn-sm pe-0" type="button">Add New</button>
                                    </div>
                                    <select class="form-select js-choice" id="organizer" multiple="multiple"
                                        size="1" name="organizer"
                                        data-options='{"removeItemButton":true,"placeholder":true}'>
                                        <option value="">Select organizer...</option>
                                        <option>Massachusetts Institute of Technology</option>
                                        <option>University of Chicago</option>
                                        <option>GSAS Open Labs At Harvard</option>
                                        <option>California Institute of Technology</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex flex-between-center">
                                        <label class="form-label" for="sponsors">Sponsors</label>
                                        <button class="btn btn-link btn-sm pe-0" type="button">Add New</button>
                                    </div>
                                    <select class="form-select js-choice" id="sponsors" multiple="multiple"
                                        size="1" name="sponsors"
                                        data-options='{"removeItemButton":true,"placeholder":true}'>
                                        <option value="">Select sponsors...</option>
                                        <option>Microsoft Corporation</option>
                                        <option>Technext Limited</option>
                                        <option>Hewlett-Packard</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="event-type">Event Type</label>
                                    <select class="form-select" id="event-type" name="event-type">
                                        <option>Select event type...</option>
                                        <option>Class, Training, or Workshop</option>
                                        <option>Concert or Performance</option>
                                        <option>Conference</option>
                                        <option>Convention</option>
                                        <option>Dinner or Gala</option>
                                        <option>Festival or Fair</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="event-topic">Event Topic</label>
                                    <select class="form-select" id="event-topic" name="even-topic">
                                        <option value="" selected="selected">Select a topic</option>
                                        <option>Auto, Boat &amp; Air</option>
                                        <option>Business &amp; Professional</option>
                                        <option>Charity &amp; Causes</option>
                                        <option>Community &amp; Culture</option>
                                        <option>Family &amp; Education</option>
                                        <option>Fashion &amp; Beauty</option>
                                        <option>Film, Media &amp; Entertainment</option>
                                        <option>Food &amp; Drink</option>
                                        <option>Government &amp; Politics</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="mb-0" for="event-tags">Tags</label>
                                        <button class="btn btn-link btn-sm pe-0" type="button">Add New</button>
                                    </div>
                                    <select class="form-select js-choice" id="event-tags" multiple="multiple"
                                        size="1" name="tags"
                                        data-options='{"removeItemButton":true,"placeholder":true}'>
                                        <option value="">Select tags...</option>
                                        <option>Concert</option>
                                        <option>New Year</option>
                                        <option>Party</option>
                                    </select>
                                </div>
                                <div class="border-bottom border-dashed my-3"></div>
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
                            <button class="btn btn-falcon-primary btn-sm">Make your event live </button>
                        </div>
                    </div>
                </div>
            </div>
            <footer class="footer">
                <div class="row g-0 justify-content-between fs-10 mt-4 mb-3">
                    <div class="col-12 col-sm-auto text-center">
                        <p class="mb-0 text-600">Thank you for creating with Falcon <span
                                class="d-none d-sm-inline-block">| </span><br class="d-sm-none" /> 2024 &copy; <a
                                href="https://themewagon.com">Themewagon</a></p>
                    </div>
                    <div class="col-12 col-sm-auto text-center">
                        <p class="mb-0 text-600">v3.23.0</p>
                    </div>
                </div>
            </footer>
        </div>
        <div class="modal fade" id="authentication-modal" tabindex="-1" role="dialog"
            aria-labelledby="authentication-modal-label" aria-hidden="true">
            <div class="modal-dialog mt-6" role="document">
                <div class="modal-content border-0">
                    <div class="modal-header px-5 position-relative modal-shape-header bg-shape">
                        <div class="position-relative z-1">
                            <h4 class="mb-0 text-white" id="authentication-modal-label">Register</h4>
                            <p class="fs-10 mb-0 text-white">Please create your free Falcon account</p>
                        </div>
                        <div data-bs-theme="dark">
                            <button class="btn-close position-absolute top-0 end-0 mt-2 me-2" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                    </div>
                    <div class="modal-body py-4 px-5">
                        <form>
                            <div class="mb-3">
                                <label class="form-label" for="modal-auth-name">Name</label>
                                <input class="form-control" type="text" autocomplete="on" id="modal-auth-name" />
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="modal-auth-email">Email address</label>
                                <input class="form-control" type="email" autocomplete="on" id="modal-auth-email" />
                            </div>
                            <div class="row gx-2">
                                <div class="mb-3 col-sm-6">
                                    <label class="form-label" for="modal-auth-password">Password</label>
                                    <input class="form-control" type="password" autocomplete="on"
                                        id="modal-auth-password" />
                                </div>
                                <div class="mb-3 col-sm-6">
                                    <label class="form-label" for="modal-auth-confirm-password">Confirm Password</label>
                                    <input class="form-control" type="password" autocomplete="on"
                                        id="modal-auth-confirm-password" />
                                </div>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="modal-auth-register-checkbox" />
                                <label class="form-label" for="modal-auth-register-checkbox">I accept the <a
                                        href="#!">terms </a>and <a class="white-space-nowrap" href="#!">privacy
                                        policy</a></label>
                            </div>
                            <div class="mb-3">
                                <button class="btn btn-primary d-block w-100 mt-3" type="submit"
                                    name="submit">Register</button>
                            </div>
                        </form>
                        <div class="position-relative mt-5">
                            <hr />
                            <div class="divider-content-center">or register with</div>
                        </div>
                        <div class="row g-2 mt-2">
                            <div class="col-sm-6"><a class="btn btn-outline-google-plus btn-sm d-block w-100"
                                    href="#"><span class="fab fa-google-plus-g me-2"
                                        data-fa-transform="grow-8"></span> google</a></div>
                            <div class="col-sm-6"><a class="btn btn-outline-facebook btn-sm d-block w-100"
                                    href="#"><span class="fab fa-facebook-square me-2"
                                        data-fa-transform="grow-8"></span> facebook</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        new Dropzone("#my-awesome-dropzone", {
            url: "#",
            maxFilesize: 5
        });
    });
</script>
@endpush
