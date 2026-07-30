@extends('layouts.app')

@section('title', 'Job Order Details')

@php
    use Carbon\Carbon;

    $statusKey = strtolower(trim((string) $job->job_status));

    $statusConfig = match ($statusKey) {
        'pending' => [
            'label' => 'Pending',
            'badge' => 'badge-subtle-warning',
            'icon' => 'fas fa-clock',
            'color' => 'warning',
        ],
        'in progress' => [
            'label' => 'In Progress',
            'badge' => 'badge-subtle-info',
            'icon' => 'fas fa-spinner',
            'color' => 'info',
        ],
        'completed' => [
            'label' => 'Completed',
            'badge' => 'badge-subtle-success',
            'icon' => 'fas fa-check-circle',
            'color' => 'success',
        ],
        default => [
            'label' => $job->job_status ?: 'Unknown',
            'badge' => 'badge-subtle-secondary',
            'icon' => 'fas fa-circle',
            'color' => 'secondary',
        ],
    };

    try {
        $jobDateInput = $job->job_datestart
            ? Carbon::parse($job->job_datestart)->format('Y-m-d')
            : '';
    } catch (\Throwable $exception) {
        $jobDateInput = '';
    }

    try {
        $jobDateDisplay = $job->job_datestart
            ? Carbon::parse($job->job_datestart)->format('F d, Y')
            : 'N/A';
    } catch (\Throwable $exception) {
        $jobDateDisplay = $job->job_datestart ?: 'N/A';
    }

    try {
        $startTimeInput = $job->job_time_start
            ? Carbon::parse($job->job_time_start)->format('H:i')
            : '';
    } catch (\Throwable $exception) {
        $startTimeInput = '';
    }

    try {
        $endTimeInput = $job->job_time_end
            ? Carbon::parse($job->job_time_end)->format('H:i')
            : '';
    } catch (\Throwable $exception) {
        $endTimeInput = '';
    }

    try {
        $startTimeDisplay = $job->job_time_start
            ? Carbon::parse($job->job_time_start)->format('h:i A')
            : 'N/A';
    } catch (\Throwable $exception) {
        $startTimeDisplay = $job->job_time_start ?: 'N/A';
    }

    try {
        $endTimeDisplay = $job->job_time_end
            ? Carbon::parse($job->job_time_end)->format('h:i A')
            : 'N/A';
    } catch (\Throwable $exception) {
        $endTimeDisplay = $job->job_time_end ?: 'N/A';
    }
@endphp

@section('content')
    <div class="container" data-layout="container">
        <script>
            const isFluid = JSON.parse(localStorage.getItem('isFluid'));

            if (isFluid) {
                const container = document.querySelector('[data-layout]');
                container?.classList.remove('container');
                container?.classList.add('container-fluid');
            }
        </script>

        <div class="content">

            {{-- Flash messages --}}
            @if (session('success'))
                <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-3" role="alert">
                    <span class="fas fa-check-circle me-2"></span>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center mb-3" role="alert">
                    <span class="fas fa-exclamation-circle me-2"></span>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm mb-3" role="alert">
                    <div class="d-flex align-items-start">
                        <span class="fas fa-exclamation-triangle mt-1 me-2"></span>

                        <div>
                            <div class="fw-semi-bold">Please correct the following:</div>

                            <ul class="mb-0 mt-1 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('tickets.joborder.index') }}">Job Orders</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        Job Order #{{ str_pad($job->id, 5, '0', STR_PAD_LEFT) }}
                    </li>
                </ol>
            </nav>

            {{-- Header --}}
            <div class="card job-order-header border-0 shadow-sm overflow-hidden mb-3">
                <div class="card-body position-relative p-4">
                    <div class="job-order-header-shape"></div>

                    <div class="row position-relative align-items-center g-4">
                        <div class="col-lg">
                            <div class="d-flex align-items-start">
                                <div class="job-order-icon flex-shrink-0">
                                    <span class="fas fa-clipboard-list"></span>
                                </div>

                                <div class="ms-3">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <h3 class="mb-0 text-dark">
                                            Job Order #{{ str_pad($job->id, 5, '0', STR_PAD_LEFT) }}
                                        </h3>

                                        <span class="badge {{ $statusConfig['badge'] }} fs-10">
                                            <span class="{{ $statusConfig['icon'] }} me-1"></span>
                                            {{ $statusConfig['label'] }}
                                        </span>
                                    </div>

                                    <p class="text-600 mb-2">
                                        {{ $job->job_type ?: 'No job type specified' }}
                                    </p>

                                    <div class="d-flex flex-wrap gap-3 text-600 fs-10">
                                        <span>
                                            <span class="fas fa-user-circle me-1"></span>
                                            Created by
                                            <strong class="text-dark">
                                                {{ $job->job_creator ?: 'System' }}
                                            </strong>
                                        </span>

                                        <span>
                                            <span class="fas fa-calendar-alt me-1"></span>
                                            {{ optional($job->created_at)->format('M d, Y h:i A') ?? 'N/A' }}
                                        </span>

                                        <span>
                                            <span class="fas fa-map-marker-alt me-1"></span>
                                            {{ $job->direction ?: 'No direction' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-auto">
                            <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                                <button
                                    type="button"
                                    class="btn btn-falcon-default btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#addNoteModal"
                                >
                                    <span class="fas fa-plus me-1"></span>
                                    Add Note
                                </button>

                                <a
                                    href="{{ route('tickets.joborder.print', $job->id) }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="btn btn-falcon-default btn-sm"
                                >
                                    <span class="fas fa-print me-1"></span>
                                    Print
                                </a>

                                @if ($job->job_status === 'Pending')
                                    <form
                                        action="{{ route('tickets.joborder.accept', $job->id) }}"
                                        method="POST"
                                        class="d-inline"
                                    >
                                        @csrf

                                        <button type="submit" class="btn btn-success btn-sm">
                                            <span class="fas fa-check-circle me-1"></span>
                                            Accept Task
                                        </button>
                                    </form>
                                @elseif ($job->job_status === 'In Progress')
                                    <form
                                        action="{{ route('tickets.joborder.done', $job->id) }}"
                                        method="POST"
                                        class="d-inline"
                                    >
                                        @csrf

                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <span class="fas fa-flag-checkered me-1"></span>
                                            Mark as Done
                                        </button>
                                    </form>
                                @endif

                                <div class="dropdown">
                                    <button
                                        type="button"
                                        class="btn btn-falcon-default btn-sm dropdown-toggle dropdown-caret-none"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false"
                                        aria-label="More options"
                                    >
                                        <span class="fas fa-ellipsis-h"></span>
                                    </button>

                                    <div class="dropdown-menu dropdown-menu-end py-0">
                                        <div class="py-2">
                                            <a
                                                class="dropdown-item"
                                                href="{{ route('tickets.joborder.index') }}"
                                            >
                                                <span class="fas fa-arrow-left me-2"></span>
                                                Back to Job Orders
                                            </a>

                                            <a
                                                class="dropdown-item"
                                                href="{{ route('tickets.joborder.print', $job->id) }}"
                                                target="_blank"
                                                rel="noopener"
                                            >
                                                <span class="fas fa-print me-2"></span>
                                                Print Job Order
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Summary cards --}}
            <div class="row g-3 mb-3">
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="summary-icon bg-primary-subtle text-primary">
                                    <span class="fas fa-tools"></span>
                                </div>

                                <div class="ms-3 min-w-0">
                                    <div class="text-600 fs-10 text-uppercase fw-semi-bold">
                                        Job Type
                                    </div>

                                    <div class="fw-bold text-dark text-truncate">
                                        {{ $job->job_type ?: 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="summary-icon bg-info-subtle text-info">
                                    <span class="fas fa-calendar-day"></span>
                                </div>

                                <div class="ms-3 min-w-0">
                                    <div class="text-600 fs-10 text-uppercase fw-semi-bold">
                                        Schedule
                                    </div>

                                    <div class="fw-bold text-dark text-truncate">
                                        {{ $jobDateDisplay }}
                                    </div>

                                    <small class="text-600">
                                        {{ $startTimeDisplay }} – {{ $endTimeDisplay }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="summary-icon bg-warning-subtle text-warning">
                                    <span class="fas fa-user-check"></span>
                                </div>

                                <div class="ms-3 min-w-0">
                                    <div class="text-600 fs-10 text-uppercase fw-semi-bold">
                                        Assigned To
                                    </div>

                                    <div class="fw-bold text-dark text-truncate">
                                        {{ $job->job_assign_person ?: 'Not assigned' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="summary-icon bg-success-subtle text-success">
                                    <span class="fas fa-bus"></span>
                                </div>

                                <div class="ms-3 min-w-0">
                                    <div class="text-600 fs-10 text-uppercase fw-semi-bold">
                                        Bus
                                    </div>

                                    <div class="fw-bold text-dark text-truncate">
                                        {{ optional($job->bus)->name ?: 'No bus linked' }}
                                    </div>

                                    @if ($job->bus)
                                        <small class="text-600">
                                            {{ $job->bus->body_number ?: 'No body number' }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                {{-- Main content --}}
                <div class="col-xl-8">

                    {{-- Details --}}
                    <div class="card border-0 shadow-sm mb-3">
                        <form
                            method="POST"
                            action="{{ route('tickets.joborder.update', $job->id) }}"
                            id="jobOrderDetailsForm"
                        >
                            @csrf
                            @method('PUT')

                            <div class="card-header bg-body-tertiary border-bottom py-3">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <div>
                                        <h5 class="mb-1">
                                            <span class="fas fa-info-circle text-primary me-2"></span>
                                            Job Order Details
                                        </h5>

                                        <p class="text-600 fs-10 mb-0">
                                            Review and update the job order information.
                                        </p>
                                    </div>

                                    <div class="d-flex gap-2" id="detailActions">
                                        <button
                                            type="button"
                                            class="btn btn-falcon-default btn-sm"
                                            id="editBtn"
                                        >
                                            <span class="fas fa-pencil-alt me-1"></span>
                                            Update Details
                                        </button>

                                        <button
                                            type="submit"
                                            class="btn btn-success btn-sm d-none"
                                            id="saveBtn"
                                            disabled
                                        >
                                            <span class="fas fa-save me-1"></span>
                                            Save Changes
                                        </button>

                                        <button
                                            type="button"
                                            class="btn btn-falcon-default btn-sm d-none"
                                            id="cancelBtn"
                                        >
                                            <span class="fas fa-times me-1"></span>
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body p-0">
                                <div class="row g-0">
                                    {{-- Job information --}}
                                    <div class="col-lg-6 border-end-lg">
                                        <div class="p-4">
                                            <div class="section-heading mb-4">
                                                <span class="section-heading-icon bg-primary-subtle text-primary">
                                                    <span class="fas fa-clipboard"></span>
                                                </span>

                                                <div>
                                                    <h6 class="mb-0">Job Information</h6>
                                                    <small class="text-600">
                                                        Schedule and incident details
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="detail-item">
                                                <div class="detail-label">Job Type</div>

                                                <div class="detail-value">
                                                    <span class="viewMode">
                                                        {{ $job->job_type ?: 'N/A' }}
                                                    </span>

                                                    <select
                                                        name="job_type"
                                                        class="form-select editMode d-none"
                                                    >
                                                        @foreach ([
                                                            'ACCIDENT',
                                                            'COLLECTING FARE',
                                                            'CUTTING FARE',
                                                            'RE- ISSUEING TICKET',
                                                            'TAMPERING TICKET',
                                                            'UNREGISTERED TICKET',
                                                            'DELAYING ISSUANCE OF TICKET',
                                                            'ROLLING TICKETS',
                                                            'REMOVING HEADSTAB OF TICKET',
                                                            'USING STUB TICKET',
                                                            'WRONG CLOSING / OPEN',
                                                            'OTHERS',
                                                        ] as $jobType)
                                                            <option
                                                                value="{{ $jobType }}"
                                                                @selected(old('job_type', $job->job_type) === $jobType)
                                                            >
                                                                {{ $jobType }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="detail-item">
                                                <div class="detail-label">Date</div>

                                                <div class="detail-value">
                                                    <span class="viewMode">
                                                        {{ $jobDateDisplay }}
                                                    </span>

                                                    <input
                                                        type="date"
                                                        name="job_datestart"
                                                        class="form-control editMode d-none"
                                                        value="{{ old('job_datestart', $jobDateInput) }}"
                                                    >
                                                </div>
                                            </div>

                                            <div class="detail-item">
                                                <div class="detail-label">Time</div>

                                                <div class="detail-value">
                                                    <span class="viewMode">
                                                        {{ $startTimeDisplay }} – {{ $endTimeDisplay }}
                                                    </span>

                                                    <div class="row g-2 editMode d-none">
                                                        <div class="col-sm-6">
                                                            <label
                                                                for="job_time_start"
                                                                class="form-label fs-10 text-600"
                                                            >
                                                                Start time
                                                            </label>

                                                            <input
                                                                type="time"
                                                                id="job_time_start"
                                                                name="job_time_start"
                                                                class="form-control"
                                                                value="{{ old('job_time_start', $startTimeInput) }}"
                                                            >
                                                        </div>

                                                        <div class="col-sm-6">
                                                            <label
                                                                for="job_time_end"
                                                                class="form-label fs-10 text-600"
                                                            >
                                                                End time
                                                            </label>

                                                            <input
                                                                type="time"
                                                                id="job_time_end"
                                                                name="job_time_end"
                                                                class="form-control"
                                                                value="{{ old('job_time_end', $endTimeInput) }}"
                                                            >
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="detail-item">
                                                <div class="detail-label">Direction</div>

                                                <div class="detail-value">
                                                    <span class="viewMode">
                                                        {{ $job->direction ?: 'N/A' }}
                                                    </span>

                                                    <select
                                                        name="direction"
                                                        class="form-select editMode d-none"
                                                    >
                                                        <option
                                                            value="South Bound"
                                                            @selected(old('direction', $job->direction) === 'South Bound')
                                                        >
                                                            South Bound
                                                        </option>

                                                        <option
                                                            value="North Bound"
                                                            @selected(old('direction', $job->direction) === 'North Bound')
                                                        >
                                                            North Bound
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="detail-item">
                                                <div class="detail-label">Seat Number</div>

                                                <div class="detail-value">
                                                    <span class="viewMode">
                                                        {{ $job->job_sitNumber ?: 'N/A' }}
                                                    </span>

                                                    <input
                                                        type="text"
                                                        name="job_sitNumber"
                                                        class="form-control editMode d-none"
                                                        value="{{ old('job_sitNumber', $job->job_sitNumber) }}"
                                                        placeholder="Enter seat number"
                                                    >
                                                </div>
                                            </div>

                                            <div class="detail-item">
                                                <div class="detail-label">Assigned To</div>

                                                <div class="detail-value">
                                                    {{ $job->job_assign_person ?: 'Not assigned' }}
                                                </div>
                                            </div>

                                            <div class="detail-item border-bottom-0 pb-0">
                                                <div class="detail-label">Remarks</div>

                                                <div class="detail-value">
                                                    <span class="viewMode text-break">
                                                        {{ $job->job_remarks ?: 'No remarks provided' }}
                                                    </span>

                                                    <textarea
                                                        name="job_remarks"
                                                        class="form-control editMode d-none"
                                                        rows="3"
                                                        placeholder="Enter remarks"
                                                    >{{ old('job_remarks', $job->job_remarks) }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Bus and people --}}
                                    <div class="col-lg-6">
                                        <div class="p-4">
                                            <div class="section-heading mb-4">
                                                <span class="section-heading-icon bg-success-subtle text-success">
                                                    <span class="fas fa-bus-alt"></span>
                                                </span>

                                                <div>
                                                    <h6 class="mb-0">Bus Information</h6>
                                                    <small class="text-600">
                                                        Linked vehicle information
                                                    </small>
                                                </div>
                                            </div>

                                            @if ($job->bus)
                                                <div class="detail-item">
                                                    <div class="detail-label">Bus Name</div>
                                                    <div class="detail-value">
                                                        {{ $job->bus->name ?: 'N/A' }}
                                                    </div>
                                                </div>

                                                <div class="detail-item">
                                                    <div class="detail-label">Body Number</div>
                                                    <div class="detail-value">
                                                        {{ $job->bus->body_number ?: 'N/A' }}
                                                    </div>
                                                </div>

                                                <div class="detail-item">
                                                    <div class="detail-label">Plate Number</div>
                                                    <div class="detail-value">
                                                        {{ $job->bus->plate_number ?: 'N/A' }}
                                                    </div>
                                                </div>

                                                <div class="detail-item">
                                                    <div class="detail-label">Garage</div>
                                                    <div class="detail-value">
                                                        {{ $job->bus->garage ?: 'N/A' }}
                                                    </div>
                                                </div>
                                            @else
                                                <div class="empty-state py-4 mb-4">
                                                    <div class="empty-state-icon">
                                                        <span class="fas fa-bus"></span>
                                                    </div>

                                                    <h6 class="mb-1">No bus linked</h6>
                                                    <p class="text-600 fs-10 mb-0">
                                                        This job order has no associated bus record.
                                                    </p>
                                                </div>
                                            @endif

                                            <div class="section-heading mt-4 mb-4">
                                                <span class="section-heading-icon bg-info-subtle text-info">
                                                    <span class="fas fa-users"></span>
                                                </span>

                                                <div>
                                                    <h6 class="mb-0">Assigned Personnel</h6>
                                                    <small class="text-600">
                                                        Driver and conductor information
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="detail-item">
                                                <div class="detail-label">Driver</div>

                                                <div class="detail-value">
                                                    <span class="viewMode">
                                                        {{ $job->driver_name ?: 'N/A' }}
                                                    </span>

                                                    <input
                                                        type="text"
                                                        name="driver_name"
                                                        class="form-control editMode d-none"
                                                        value="{{ old('driver_name', $job->driver_name) }}"
                                                        placeholder="Enter driver name"
                                                    >
                                                </div>
                                            </div>

                                            <div class="detail-item border-bottom-0 pb-0">
                                                <div class="detail-label">Conductor</div>

                                                <div class="detail-value">
                                                    <span class="viewMode">
                                                        {{ $job->conductor_name ?: 'N/A' }}
                                                    </span>

                                                    <input
                                                        type="text"
                                                        name="conductor_name"
                                                        class="form-control editMode d-none"
                                                        value="{{ old('conductor_name', $job->conductor_name) }}"
                                                        placeholder="Enter conductor name"
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- Attached files --}}
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-body-tertiary border-bottom py-3">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div>
                                    <h5 class="mb-1">
                                        <span class="fas fa-paperclip text-primary me-2"></span>
                                        Attached Files
                                    </h5>

                                    <p class="text-600 fs-10 mb-0">
                                        Images, videos, documents, and supporting evidence.
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    class="btn btn-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#addFileModal"
                                >
                                    <span class="fas fa-cloud-upload-alt me-1"></span>
                                    Upload Files
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            @if ($job->files->isNotEmpty())
                                <div class="row g-3">
                                    @foreach ($job->files as $file)
                                        @php
                                            $fileName = $file->file_name ?: basename($file->file_path);
                                            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                            $fileUrl = asset('storage/' . ltrim($file->file_path, '/'));

                                            $isImage = in_array($extension, [
                                                'jpg',
                                                'jpeg',
                                                'png',
                                                'gif',
                                                'webp',
                                                'bmp',
                                            ]);

                                            $isBrowserVideo = in_array($extension, [
                                                'mp4',
                                                'webm',
                                                'ogg',
                                            ]);

                                            $isVideo = $isBrowserVideo || $extension === 'avi';

                                            $fileIcon = match ($extension) {
                                                'pdf' => 'fas fa-file-pdf text-danger',
                                                'doc', 'docx' => 'fas fa-file-word text-primary',
                                                'xls', 'xlsx', 'csv' => 'fas fa-file-excel text-success',
                                                'zip', 'rar', '7z' => 'fas fa-file-archive text-warning',
                                                'ppt', 'pptx' => 'fas fa-file-powerpoint text-warning',
                                                'txt' => 'fas fa-file-alt text-secondary',
                                                default => 'fas fa-file text-primary',
                                            };
                                        @endphp

                                        <div class="col-sm-6 col-lg-4">
                                            <div class="attachment-card h-100">
                                                <div class="attachment-preview">
                                                    @if ($isImage)
                                                        <a
                                                            href="{{ $fileUrl }}"
                                                            target="_blank"
                                                            rel="noopener"
                                                            class="d-block h-100"
                                                        >
                                                            <img
                                                                src="{{ $fileUrl }}"
                                                                alt="{{ $fileName }}"
                                                                loading="lazy"
                                                            >
                                                        </a>
                                                    @elseif ($isBrowserVideo)
                                                        <video
                                                            controls
                                                            preload="metadata"
                                                            class="w-100 h-100"
                                                        >
                                                            <source
                                                                src="{{ $fileUrl }}"
                                                                type="video/{{ $extension }}"
                                                            >
                                                            Your browser does not support this video.
                                                        </video>
                                                    @elseif ($isVideo)
                                                        <div class="attachment-file-icon">
                                                            <span class="fas fa-file-video text-warning"></span>
                                                        </div>
                                                    @else
                                                        <div class="attachment-file-icon">
                                                            <span class="{{ $fileIcon }}"></span>
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="attachment-content">
                                                    <div
                                                        class="fw-semi-bold text-dark text-truncate"
                                                        title="{{ $fileName }}"
                                                    >
                                                        {{ $fileName }}
                                                    </div>

                                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                                        <span class="badge badge-subtle-secondary text-uppercase">
                                                            {{ $extension ?: 'FILE' }}
                                                        </span>

                                                        <a
                                                            href="{{ $fileUrl }}"
                                                            download="{{ $fileName }}"
                                                            class="btn btn-falcon-default btn-sm"
                                                        >
                                                            <span class="fas fa-download me-1"></span>
                                                            Download
                                                        </a>
                                                    </div>

                                                    @if ($extension === 'avi')
                                                        <small class="text-warning d-block mt-2">
                                                            <span class="fas fa-info-circle me-1"></span>
                                                            Browser preview is unavailable.
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="empty-state py-5">
                                    <div class="empty-state-icon">
                                        <span class="fas fa-folder-open"></span>
                                    </div>

                                    <h6 class="mb-1">No files attached</h6>

                                    <p class="text-600 fs-10 mb-3">
                                        Upload images, videos, or documents related to this job order.
                                    </p>

                                    <button
                                        type="button"
                                        class="btn btn-primary btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#addFileModal"
                                    >
                                        <span class="fas fa-cloud-upload-alt me-1"></span>
                                        Upload First File
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="col-xl-4">

                    {{-- Workflow --}}
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-body-tertiary border-bottom py-3">
                            <h5 class="mb-0">
                                <span class="fas fa-tasks text-primary me-2"></span>
                                Workflow Status
                            </h5>
                        </div>

                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4">
                                <div class="workflow-status-icon bg-{{ $statusConfig['color'] }}-subtle text-{{ $statusConfig['color'] }}">
                                    <span class="{{ $statusConfig['icon'] }}"></span>
                                </div>

                                <div class="ms-3">
                                    <small class="text-600 text-uppercase fw-semi-bold">
                                        Current Status
                                    </small>

                                    <h5 class="mb-0">
                                        {{ $statusConfig['label'] }}
                                    </h5>
                                </div>
                            </div>

                            <div class="workflow-timeline">
                                <div class="workflow-item completed">
                                    <div class="workflow-marker">
                                        <span class="fas fa-check"></span>
                                    </div>

                                    <div class="workflow-content">
                                        <div class="fw-semi-bold">Job order created</div>
                                        <small class="text-600">
                                            {{ optional($job->created_at)->format('M d, Y h:i A') ?? 'N/A' }}
                                        </small>
                                    </div>
                                </div>

                                <div
                                    class="workflow-item
                                        {{ in_array($job->job_status, ['In Progress', 'Completed']) ? 'completed' : '' }}"
                                >
                                    <div class="workflow-marker">
                                        <span class="fas fa-check"></span>
                                    </div>

                                    <div class="workflow-content">
                                        <div class="fw-semi-bold">Task accepted</div>
                                        <small class="text-600">
                                            {{ in_array($job->job_status, ['In Progress', 'Completed'])
                                                ? 'Task is being processed'
                                                : 'Waiting for acceptance' }}
                                        </small>
                                    </div>
                                </div>

                                <div
                                    class="workflow-item {{ $job->job_status === 'Completed' ? 'completed' : '' }}"
                                >
                                    <div class="workflow-marker">
                                        <span class="fas fa-check"></span>
                                    </div>

                                    <div class="workflow-content">
                                        <div class="fw-semi-bold">Job completed</div>
                                        <small class="text-600">
                                            {{ $job->job_status === 'Completed'
                                                ? 'Task has been completed'
                                                : 'Waiting for completion' }}
                                        </small>
                                    </div>
                                </div>
                            </div>

                            @if ($job->job_status === 'Pending')
                                <form
                                    action="{{ route('tickets.joborder.accept', $job->id) }}"
                                    method="POST"
                                    class="mt-4"
                                >
                                    @csrf

                                    <button type="submit" class="btn btn-success w-100">
                                        <span class="fas fa-check-circle me-1"></span>
                                        Accept This Task
                                    </button>
                                </form>
                            @elseif ($job->job_status === 'In Progress')
                                <form
                                    action="{{ route('tickets.joborder.done', $job->id) }}"
                                    method="POST"
                                    class="mt-4"
                                >
                                    @csrf

                                    <button type="submit" class="btn btn-primary w-100">
                                        <span class="fas fa-flag-checkered me-1"></span>
                                        Mark as Completed
                                    </button>
                                </form>
                            @else
                                <div class="alert alert-success border-0 mb-0 mt-4">
                                    <span class="fas fa-check-circle me-1"></span>
                                    This job order has been completed.
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-body-tertiary border-bottom py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">
                                        <span class="fas fa-sticky-note text-warning me-2"></span>
                                        Notes
                                    </h5>
                                </div>

                                <button
                                    type="button"
                                    class="btn btn-falcon-default btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#addNoteModal"
                                >
                                    <span class="fas fa-plus me-1"></span>
                                    Add
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            @forelse ($job->notes as $note)
                                <div class="note-item {{ !$loop->last ? 'border-bottom pb-3 mb-3' : '' }}">
                                    <div class="d-flex align-items-start">
                                        <div class="note-icon">
                                            <span class="fas fa-comment-alt"></span>
                                        </div>

                                        <div class="ms-3 flex-1 min-w-0">
                                            <div class="d-flex justify-content-between align-items-start gap-2">
                                                <h6 class="mb-1">
                                                    {{ $note->reason }}
                                                </h6>

                                                <small class="text-600 text-nowrap">
                                                    {{ optional($note->created_at)->format('M d, Y') }}
                                                </small>
                                            </div>

                                            @if ($note->details)
                                                <p class="text-700 fs-10 mb-2 text-break">
                                                    {{ $note->details }}
                                                </p>
                                            @endif

                                            <small class="text-600">
                                                <span class="fas fa-user me-1"></span>
                                                {{ optional($note->user)->full_name ?? 'System' }}

                                                <span class="mx-1">•</span>

                                                {{ optional($note->created_at)->format('h:i A') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state py-4">
                                    <div class="empty-state-icon">
                                        <span class="fas fa-comment-slash"></span>
                                    </div>

                                    <h6 class="mb-1">No notes yet</h6>

                                    <p class="text-600 fs-10 mb-3">
                                        Record issues, findings, or additional information.
                                    </p>

                                    <button
                                        type="button"
                                        class="btn btn-falcon-default btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#addNoteModal"
                                    >
                                        <span class="fas fa-plus me-1"></span>
                                        Add First Note
                                    </button>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Logs --}}
            <div class="card border-0 shadow-sm mb-5">
                <div class="card-header bg-body-tertiary border-bottom py-3">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h5 class="mb-1">
                                <span class="fas fa-history text-primary me-2"></span>
                                Activity Logs
                            </h5>

                            <p class="text-600 fs-10 mb-0">
                                Complete history of changes and actions for this job order.
                            </p>
                        </div>

                        <span class="badge badge-subtle-primary">
                            {{ $logs->count() }} {{ Str::plural('record', $logs->count()) }}
                        </span>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div
                        id="logList"
                        data-list='{
                            "valueNames": ["log-user", "log-action", "log-meta", "log-date"],
                            "page": 5,
                            "pagination": true
                        }'
                    >
                        <div class="list">
                            @forelse ($logs as $log)
                                @php
                                    if (is_string($log->meta)) {
                                        $decodedMeta = json_decode($log->meta, true);
                                        $meta = is_array($decodedMeta) ? $decodedMeta : [];
                                    } elseif (is_array($log->meta)) {
                                        $meta = $log->meta;
                                    } else {
                                        $meta = [];
                                    }

                                    $logUser = optional($log->user)->full_name ?? 'System';
                                @endphp

                                <div class="log-row border-bottom">
                                    <div class="row align-items-start g-3">
                                        <div class="col-auto">
                                            <div class="log-icon">
                                                <span class="fas fa-history"></span>
                                            </div>
                                        </div>

                                        <div class="col min-w-0">
                                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                                <span class="fw-bold text-dark log-user">
                                                    {{ $logUser }}
                                                </span>

                                                <span class="badge badge-subtle-info log-action">
                                                    {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                                </span>
                                            </div>

                                            <div class="text-600 fs-10 log-meta">
                                                @if (count($meta))
                                                    @foreach ($meta as $key => $value)
                                                        <div class="{{ !$loop->last ? 'mb-1' : '' }}">
                                                            <span class="fw-semi-bold text-700">
                                                                {{ ucfirst(str_replace('_', ' ', $key)) }}:
                                                            </span>

                                                            @if (
                                                                is_array($value) &&
                                                                array_key_exists('old', $value) &&
                                                                array_key_exists('new', $value)
                                                            )
                                                                <span class="badge badge-subtle-danger ms-1">
                                                                    Old:
                                                                    {{ is_scalar($value['old'])
                                                                        ? $value['old']
                                                                        : json_encode($value['old']) }}
                                                                </span>

                                                                <span class="fas fa-long-arrow-alt-right mx-1"></span>

                                                                <span class="badge badge-subtle-success">
                                                                    New:
                                                                    {{ is_scalar($value['new'])
                                                                        ? $value['new']
                                                                        : json_encode($value['new']) }}
                                                                </span>
                                                            @elseif (is_scalar($value) || $value === null)
                                                                {{ $value ?? 'N/A' }}
                                                            @else
                                                                {{ json_encode($value) }}
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <span class="fst-italic">No additional details</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-md-auto">
                                            <div class="text-md-end">
                                                <span class="text-600 fs-10 log-date">
                                                    <span class="fas fa-calendar-alt me-1"></span>
                                                    {{ optional($log->created_at)->format('M d, Y') }}
                                                </span>

                                                <small class="text-500 d-block mt-1">
                                                    {{ optional($log->created_at)->format('h:i A') }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-state py-5">
                                    <div class="empty-state-icon">
                                        <span class="fas fa-history"></span>
                                    </div>

                                    <h6 class="mb-1">No activity logs</h6>
                                    <p class="text-600 fs-10 mb-0">
                                        Changes and workflow actions will appear here.
                                    </p>
                                </div>
                            @endforelse
                        </div>

                        @if ($logs->count() > 5)
                            <div class="d-flex justify-content-center align-items-center gap-2 py-3">
                                <button
                                    type="button"
                                    class="btn btn-falcon-default btn-sm"
                                    data-list-pagination="prev"
                                >
                                    <span class="fas fa-chevron-left"></span>
                                </button>

                                <ul class="pagination pagination-sm mb-0"></ul>

                                <button
                                    type="button"
                                    class="btn btn-falcon-default btn-sm"
                                    data-list-pagination="next"
                                >
                                    <span class="fas fa-chevron-right"></span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add File Modal --}}
    <div
        class="modal fade"
        id="addFileModal"
        tabindex="-1"
        aria-labelledby="addFileModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">
            <form
                id="uploadForm"
                enctype="multipart/form-data"
                class="modal-content border-0 shadow"
            >
                @csrf

                <div class="modal-header bg-body-tertiary border-bottom">
                    <div>
                        <h5 class="modal-title" id="addFileModalLabel">
                            Upload Files
                        </h5>

                        <p class="text-600 fs-10 mb-0">
                            Add supporting documents, images, or videos.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>

                <div class="modal-body p-4">
                    <div class="upload-dropzone">
                        <div class="upload-dropzone-icon">
                            <span class="fas fa-cloud-upload-alt"></span>
                        </div>

                        <label for="fileInput" class="form-label fw-semi-bold">
                            Select files to upload
                        </label>

                        <input
                            type="file"
                            name="files[]"
                            id="fileInput"
                            class="form-control"
                            multiple
                            required
                        >

                        <small class="text-600 d-block mt-2">
                            Multiple files may be selected.
                        </small>
                    </div>

                    <div class="progress d-none mt-4" id="uploadProgressWrapper" style="height: 10px;">
                        <div
                            id="uploadProgress"
                            class="progress-bar progress-bar-striped progress-bar-animated"
                            role="progressbar"
                            style="width: 0%"
                            aria-valuenow="0"
                            aria-valuemin="0"
                            aria-valuemax="100"
                        ></div>
                    </div>

                    <div
                        class="text-center fs-10 mt-2"
                        id="uploadStatus"
                        aria-live="polite"
                    ></div>
                </div>

                <div class="modal-footer bg-body-tertiary border-top">
                    <button
                        type="button"
                        class="btn btn-falcon-default btn-sm"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button type="button" id="uploadBtn" class="btn btn-primary btn-sm">
                        <span class="fas fa-upload me-1"></span>
                        Upload Files
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Add Note Modal --}}
    <div
        class="modal fade"
        id="addNoteModal"
        tabindex="-1"
        aria-labelledby="addNoteModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">
            <form
                method="POST"
                action="{{ route('tickets.joborder.addnote', $job->id) }}"
                class="modal-content border-0 shadow"
            >
                @csrf

                <div class="modal-header bg-body-tertiary border-bottom">
                    <div>
                        <h5 class="modal-title" id="addNoteModalLabel">
                            Add Job Order Note
                        </h5>

                        <p class="text-600 fs-10 mb-0">
                            Record an issue, observation, or additional information.
                        </p>
                    </div>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>

                <div class="modal-body p-4">
                    <label class="form-label fw-semi-bold">
                        Reason
                        <span class="text-danger">*</span>
                    </label>

                    <div class="note-reason-list mb-4">
                        @foreach ([
                            'Defective DVR' => 'fas fa-hdd',
                            'Camera not working' => 'fas fa-video-slash',
                            'Weak signal / interference' => 'fas fa-signal',
                            'Other' => 'fas fa-ellipsis-h',
                        ] as $reason => $icon)
                            <label class="note-reason-option">
                                <input
                                    type="radio"
                                    name="reason"
                                    value="{{ $reason }}"
                                    class="form-check-input"
                                    required
                                >

                                <span class="note-reason-icon">
                                    <span class="{{ $icon }}"></span>
                                </span>

                                <span class="fw-semi-bold">
                                    {{ $reason }}
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <div>
                        <label for="noteDetails" class="form-label fw-semi-bold">
                            Additional Details
                        </label>

                        <textarea
                            name="details"
                            id="noteDetails"
                            class="form-control"
                            rows="4"
                            placeholder="Enter additional details about the issue..."
                        >{{ old('details') }}</textarea>

                        <small class="text-600">
                            Optional supporting information for this note.
                        </small>
                    </div>
                </div>

                <div class="modal-footer bg-body-tertiary border-top">
                    <button
                        type="button"
                        class="btn btn-falcon-default btn-sm"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary btn-sm">
                        <span class="fas fa-plus me-1"></span>
                        Add Note
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .job-order-header {
            background:
                linear-gradient(
                    135deg,
                    rgba(var(--falcon-primary-rgb, 44, 123, 229), 0.08),
                    rgba(var(--falcon-info-rgb, 39, 188, 253), 0.03)
                ),
                var(--falcon-card-bg, #fff);
        }

        .job-order-header-shape {
            position: absolute;
            top: -85px;
            right: -70px;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: rgba(var(--falcon-primary-rgb, 44, 123, 229), 0.06);
            pointer-events: none;
        }

        .job-order-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 58px;
            height: 58px;
            border-radius: 14px;
            background: var(--falcon-primary, #2c7be5);
            color: #fff;
            font-size: 1.35rem;
            box-shadow: 0 0.35rem 0.75rem rgba(44, 123, 229, 0.2);
        }

        .summary-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            font-size: 1rem;
        }

        .min-w-0 {
            min-width: 0;
        }

        .section-heading {
            display: flex;
            align-items: center;
        }

        .section-heading-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 38px;
            height: 38px;
            margin-right: 0.75rem;
            border-radius: 10px;
        }

        .detail-item {
            display: grid;
            grid-template-columns: minmax(115px, 36%) minmax(0, 1fr);
            gap: 0.75rem;
            padding: 0.85rem 0;
            border-bottom: 1px solid var(--falcon-border-color, #d8e2ef);
        }

        .detail-label {
            color: var(--falcon-gray-600, #748194);
            font-size: 0.833333rem;
            font-weight: 600;
        }

        .detail-value {
            min-width: 0;
            color: var(--falcon-gray-900, #344050);
            font-size: 0.875rem;
            font-weight: 500;
            overflow-wrap: anywhere;
        }

        .attachment-card {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid var(--falcon-border-color, #d8e2ef);
            border-radius: 0.5rem;
            background: var(--falcon-card-bg, #fff);
            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                border-color 0.2s ease;
        }

        .attachment-card:hover {
            transform: translateY(-2px);
            border-color: rgba(var(--falcon-primary-rgb, 44, 123, 229), 0.35);
            box-shadow: 0 0.5rem 1rem rgba(18, 38, 63, 0.08);
        }

        .attachment-preview {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 170px;
            overflow: hidden;
            background: var(--falcon-gray-100, #f9fafd);
        }

        .attachment-preview img,
        .attachment-preview video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .attachment-file-icon {
            font-size: 3rem;
        }

        .attachment-content {
            flex: 1;
            padding: 0.9rem;
        }

        .workflow-status-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-size: 1.15rem;
        }

        .workflow-timeline {
            position: relative;
            padding-left: 0.25rem;
        }

        .workflow-item {
            position: relative;
            display: flex;
            min-height: 66px;
        }

        .workflow-item:not(:last-child)::before {
            position: absolute;
            top: 26px;
            bottom: -4px;
            left: 12px;
            width: 2px;
            content: "";
            background: var(--falcon-border-color, #d8e2ef);
        }

        .workflow-item.completed:not(:last-child)::before {
            background: rgba(var(--falcon-success-rgb, 0, 210, 122), 0.45);
        }

        .workflow-marker {
            z-index: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 26px;
            height: 26px;
            border: 2px solid var(--falcon-border-color, #d8e2ef);
            border-radius: 50%;
            background: var(--falcon-card-bg, #fff);
            color: transparent;
            font-size: 0.6rem;
        }

        .workflow-item.completed .workflow-marker {
            border-color: var(--falcon-success, #00d27a);
            background: var(--falcon-success, #00d27a);
            color: #fff;
        }

        .workflow-content {
            padding: 0.15rem 0 1rem 0.8rem;
        }

        .note-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(var(--falcon-warning-rgb, 245, 128, 62), 0.12);
            color: var(--falcon-warning, #f5803e);
            font-size: 0.8rem;
        }

        .log-row {
            padding: 1.1rem 1.25rem;
            transition: background-color 0.15s ease;
        }

        .log-row:hover {
            background: var(--falcon-gray-100, #f9fafd);
        }

        .log-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(var(--falcon-primary-rgb, 44, 123, 229), 0.1);
            color: var(--falcon-primary, #2c7be5);
        }

        .empty-state {
            text-align: center;
        }

        .empty-state-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 58px;
            height: 58px;
            margin-bottom: 0.75rem;
            border-radius: 50%;
            background: var(--falcon-gray-100, #f9fafd);
            color: var(--falcon-gray-500, #9da9bb);
            font-size: 1.35rem;
        }

        .upload-dropzone {
            padding: 1.5rem;
            border: 2px dashed var(--falcon-border-color, #d8e2ef);
            border-radius: 0.625rem;
            background: var(--falcon-gray-100, #f9fafd);
            text-align: center;
        }

        .upload-dropzone-icon {
            margin-bottom: 0.75rem;
            color: var(--falcon-primary, #2c7be5);
            font-size: 2rem;
        }

        .note-reason-list {
            display: grid;
            gap: 0.65rem;
        }

        .note-reason-option {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            border: 1px solid var(--falcon-border-color, #d8e2ef);
            border-radius: 0.5rem;
            cursor: pointer;
            transition:
                border-color 0.15s ease,
                background-color 0.15s ease;
        }

        .note-reason-option:hover {
            border-color: rgba(var(--falcon-primary-rgb, 44, 123, 229), 0.45);
            background: rgba(var(--falcon-primary-rgb, 44, 123, 229), 0.04);
        }

        .note-reason-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--falcon-gray-100, #f9fafd);
            color: var(--falcon-primary, #2c7be5);
        }

        @media (min-width: 992px) {
            .border-end-lg {
                border-right: 1px solid var(--falcon-border-color, #d8e2ef);
            }
        }

        @media (max-width: 767.98px) {
            .detail-item {
                grid-template-columns: 1fr;
                gap: 0.35rem;
            }

            .job-order-icon {
                width: 48px;
                height: 48px;
                border-radius: 12px;
                font-size: 1.1rem;
            }

            .attachment-preview {
                height: 210px;
            }

            .log-row {
                padding: 1rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const detailsForm = document.getElementById('jobOrderDetailsForm');
            const editBtn = document.getElementById('editBtn');
            const saveBtn = document.getElementById('saveBtn');
            const cancelBtn = document.getElementById('cancelBtn');

            const viewFields = document.querySelectorAll('.viewMode');
            const editFields = document.querySelectorAll('.editMode');

            const editableInputs = detailsForm
                ? detailsForm.querySelectorAll(
                    '.editMode input, .editMode select, .editMode textarea, input.editMode, select.editMode, textarea.editMode'
                )
                : [];

            const originalValues = new Map();

            editableInputs.forEach(function (input) {
                originalValues.set(input, input.value);
            });

            function hasChanges() {
                return Array.from(editableInputs).some(function (input) {
                    return input.value !== originalValues.get(input);
                });
            }

            function updateSaveButtonState() {
                if (saveBtn) {
                    saveBtn.disabled = !hasChanges();
                }
            }

            function enableEditMode() {
                editBtn?.classList.add('d-none');
                saveBtn?.classList.remove('d-none');
                cancelBtn?.classList.remove('d-none');

                viewFields.forEach(function (field) {
                    field.classList.add('d-none');
                });

                editFields.forEach(function (field) {
                    field.classList.remove('d-none');
                });

                updateSaveButtonState();
            }

            function disableEditMode() {
                editableInputs.forEach(function (input) {
                    input.value = originalValues.get(input);
                });

                editBtn?.classList.remove('d-none');
                saveBtn?.classList.add('d-none');
                cancelBtn?.classList.add('d-none');

                if (saveBtn) {
                    saveBtn.disabled = true;
                }

                editFields.forEach(function (field) {
                    field.classList.add('d-none');
                });

                viewFields.forEach(function (field) {
                    field.classList.remove('d-none');
                });
            }

            editBtn?.addEventListener('click', enableEditMode);
            cancelBtn?.addEventListener('click', disableEditMode);

            editableInputs.forEach(function (input) {
                input.addEventListener('input', updateSaveButtonState);
                input.addEventListener('change', updateSaveButtonState);
            });

            detailsForm?.addEventListener('submit', function () {
                if (saveBtn) {
                    saveBtn.disabled = true;
                    saveBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Saving...';
                }
            });

            const uploadForm = document.getElementById('uploadForm');
            const uploadBtn = document.getElementById('uploadBtn');
            const fileInput = document.getElementById('fileInput');
            const progressBar = document.getElementById('uploadProgress');
            const progressWrapper = document.getElementById('uploadProgressWrapper');
            const statusText = document.getElementById('uploadStatus');
            const addFileModal = document.getElementById('addFileModal');

            function resetUploadState() {
                uploadForm?.reset();

                progressWrapper?.classList.add('d-none');

                if (progressBar) {
                    progressBar.style.width = '0%';
                    progressBar.setAttribute('aria-valuenow', '0');
                    progressBar.classList.remove('bg-success', 'bg-danger');
                    progressBar.classList.add(
                        'progress-bar-striped',
                        'progress-bar-animated'
                    );
                    progressBar.textContent = '';
                }

                if (statusText) {
                    statusText.innerHTML = '';
                }

                if (uploadBtn) {
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML =
                        '<span class="fas fa-upload me-1"></span>Upload Files';
                }
            }

            addFileModal?.addEventListener('hidden.bs.modal', resetUploadState);

            uploadBtn?.addEventListener('click', function () {
                if (!fileInput || fileInput.files.length === 0) {
                    statusText.innerHTML =
                        '<span class="text-danger">' +
                        '<span class="fas fa-exclamation-circle me-1"></span>' +
                        'Select at least one file.' +
                        '</span>';

                    fileInput?.focus();

                    return;
                }

                const formData = new FormData(uploadForm);
                const xhr = new XMLHttpRequest();

                uploadBtn.disabled = true;
                uploadBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-1" role="status"></span>' +
                    'Uploading...';

                progressWrapper.classList.remove('d-none');
                progressBar.classList.remove('bg-success', 'bg-danger');
                progressBar.classList.add(
                    'progress-bar-striped',
                    'progress-bar-animated'
                );

                statusText.innerHTML =
                    '<span class="text-600">Preparing upload...</span>';

                xhr.open(
                    'POST',
                    @json(route('tickets.joborder.addfile', $job->id)),
                    true
                );

                xhr.setRequestHeader('Accept', 'application/json');

                xhr.upload.addEventListener('progress', function (event) {
                    if (!event.lengthComputable) {
                        return;
                    }

                    const percentage = Math.round(
                        (event.loaded / event.total) * 100
                    );

                    progressBar.style.width = percentage + '%';
                    progressBar.setAttribute('aria-valuenow', percentage);
                    progressBar.textContent = percentage + '%';

                    statusText.innerHTML =
                        '<span class="text-600">Uploading files...</span>';
                });

                xhr.onload = function () {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        progressBar.style.width = '100%';
                        progressBar.setAttribute('aria-valuenow', '100');
                        progressBar.textContent = '100%';
                        progressBar.classList.remove(
                            'progress-bar-striped',
                            'progress-bar-animated',
                            'bg-danger'
                        );
                        progressBar.classList.add('bg-success');

                        statusText.innerHTML =
                            '<span class="text-success">' +
                            '<span class="fas fa-check-circle me-1"></span>' +
                            'Upload completed successfully.' +
                            '</span>';

                        window.setTimeout(function () {
                            window.location.reload();
                        }, 700);

                        return;
                    }

                    let errorMessage = 'File upload failed.';

                    try {
                        const response = JSON.parse(xhr.responseText);

                        if (response.message) {
                            errorMessage = response.message;
                        }

                        if (response.errors) {
                            const firstError = Object.values(response.errors)
                                .flat()
                                .shift();

                            if (firstError) {
                                errorMessage = firstError;
                            }
                        }
                    } catch (error) {
                        // Keep the default message for non-JSON responses.
                    }

                    progressBar.classList.remove(
                        'progress-bar-striped',
                        'progress-bar-animated',
                        'bg-success'
                    );
                    progressBar.classList.add('bg-danger');

                    statusText.innerHTML =
                        '<span class="text-danger">' +
                        '<span class="fas fa-exclamation-circle me-1"></span>' +
                        errorMessage +
                        '</span>';

                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML =
                        '<span class="fas fa-redo me-1"></span>Try Again';
                };

                xhr.onerror = function () {
                    progressBar.classList.remove(
                        'progress-bar-striped',
                        'progress-bar-animated',
                        'bg-success'
                    );
                    progressBar.classList.add('bg-danger');

                    statusText.innerHTML =
                        '<span class="text-danger">' +
                        '<span class="fas fa-wifi me-1"></span>' +
                        'Network error. Upload was not completed.' +
                        '</span>';

                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML =
                        '<span class="fas fa-redo me-1"></span>Try Again';
                };

                xhr.send(formData);
            });
        });
    </script>
@endpush
