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
            {{-- HEADER CARD (Falcon style) --}}
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
                            <a class="btn btn-falcon-default btn-sm" href="#!">
                                <span class="fas fa-plus fs-11 me-1"></span>Add note
                            </a>

                            {{-- Options --}}
                            <button class="btn btn-falcon-default btn-sm dropdown-toggle ms-2 dropdown-caret-none"
                                type="button" data-bs-toggle="dropdown">
                                <span class="fas fa-ellipsis-h"></span>
                            </button>

                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('tickets.joborder.index') }}">Back</a>
                                <a class="dropdown-item" href="#">Edit</a>
                                <a class="dropdown-item" href="#">Report</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#">Delete</a>
                            </div>
                        </div>

                        {{-- Right label --}}
                        <div class="col-auto d-none d-sm-block">
                            <h6 class="text-uppercase text-600">
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
                        </div>

                    </div>
                </div>

                {{-- Activity row --}}
                <div class="card-body border-top">
                    <div class="d-flex">
                        <span class="fas fa-user text-success me-2"></span>
                        <div class="flex-1">
                            <p class="mb-0">Job Order was created</p>
                            <p class="fs-10 mb-0 text-600">{{ $job->created_at->format('Y-m-d H:i:s') }}</p>
                        </div>
                    </div>
                </div>

            </div>



            {{-- ============================= --}}
            {{-- DETAILS CARD --}}
            {{-- ============================= --}}
            <div class="card mb-3">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">Details</h5>
                        </div>

                        <div class="col-auto">
                            <a class="btn btn-falcon-default btn-sm" href="#">
                                <span class="fas fa-pencil-alt fs-11 me-1"></span>Update details
                            </a>
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
                                <div class="col">{{ $job->job_type }}</div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-5 col-sm-4 fw-semi-bold">Date</div>
                                <div class="col">{{ $job->job_datestart }}</div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-5 col-sm-4 fw-semi-bold">Time</div>
                                <div class="col">{{ $job->job_time_start }} – {{ $job->job_time_end }}</div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-5 col-sm-4 fw-semi-bold">Direction</div>
                                <div class="col">{{ $job->direction }}</div>
                            </div>

                            @if ($job->job_sitNumber)
                                <div class="row mb-2">
                                    <div class="col-5 col-sm-4 fw-semi-bold">Seat No.</div>
                                    <div class="col">{{ $job->job_sitNumber }}</div>
                                </div>
                            @endif

                            <div class="row mb-2">
                                <div class="col-5 col-sm-4 fw-semi-bold">Assigned To</div>
                                <div class="col">{{ $job->job_assign_person ?? 'N/A' }}</div>
                            </div>

                            @if ($job->job_remarks)
                                <div class="row mb-2">
                                    <div class="col-5 col-sm-4 fw-semi-bold">Remarks</div>
                                    <div class="col">{{ $job->job_remarks }}</div>
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
                                <div class="col">{{ $job->driver_name ?? 'N/A' }}</div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-5 col-sm-4 fw-semi-bold">Conductor</div>
                                <div class="col">{{ $job->conductor_name ?? 'N/A' }}</div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>



            {{-- ============================= --}}
            {{-- FILES --}}
            {{-- ============================= --}}
            @if ($job->files->count() > 0)
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">Attached Files</h5>
                    </div>

                    <div class="card-body">
                        <div class="row">
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
                        </div>
                    </div>
                </div>
            @endif

            {{-- ============================= --}}
            {{-- LOGS WITH PAGINATION (Falcon Style + List.js) --}}
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
                                                        <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                                        {{ $value }}@if (!$loop->last)
                                                            ,
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
                </div>

            </div>
        @endsection
