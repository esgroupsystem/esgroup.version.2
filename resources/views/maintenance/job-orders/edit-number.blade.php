@extends('layouts.app')

@section('title', 'Edit Job Order Number')

@section('content')
    <div class="container" data-layout="container">
        <script>
            const isFluid = JSON.parse(localStorage.getItem('isFluid'));
            if (isFluid) {
                const container = document.querySelector('[data-layout]');
                container.classList.remove('container');
                container.classList.add('container-fluid');
            }
        </script>

        <div class="content">
            @if (session('error'))
                <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center" role="alert">
                    <span class="fas fa-exclamation-circle me-2"></span>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm">
                    <div class="fw-semibold mb-2">
                        <span class="fas fa-exclamation-triangle me-1"></span>
                        Please fix the following:
                    </div>

                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body py-3">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg-8">
                            <div class="d-flex align-items-center gap-3">
                                <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-3"
                                    style="width: 46px; height: 46px;">
                                    <span class="fas fa-pen-to-square fs-5"></span>
                                </div>

                                <div>
                                    <h4 class="mb-1">Edit Job Order Number</h4>
                                    <div class="text-600">
                                        Update JO-NO for <strong>{{ $jobOrder->job_order_no }}</strong>.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 text-lg-end">
                            <a href="{{ route('maintenance.job-orders.show', $jobOrder) }}" class="btn btn-falcon-default">
                                <span class="fas fa-arrow-left me-1"></span>
                                Back to Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('maintenance.job-orders.update-number', $jobOrder) }}">
                @csrf
                @method('PATCH')

                <div class="row g-3">
                    <div class="col-xl-8">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0">
                                    <span class="fas fa-hashtag me-2 text-primary"></span>
                                    JO-NO Information
                                </h5>
                            </div>

                            <div class="card-body">
                                <label for="job_order_no" class="form-label fw-semibold">
                                    Job Order Number <span class="text-danger">*</span>
                                </label>

                                <input type="text" name="job_order_no" id="job_order_no"
                                    value="{{ old('job_order_no', $jobOrder->job_order_no) }}"
                                    class="form-control @error('job_order_no') is-invalid @enderror" maxlength="50"
                                    required>

                                @error('job_order_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <div class="form-text">
                                    Allowed: letters, numbers, dash, and slash only.
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0">
                                    <span class="fas fa-comment-alt me-2 text-primary"></span>
                                    Edit Remarks
                                </h5>
                            </div>

                            <div class="card-body">
                                <label for="remarks" class="form-label fw-semibold">
                                    Remarks
                                </label>

                                <textarea name="remarks" id="remarks" rows="4" class="form-control @error('remarks') is-invalid @enderror"
                                    maxlength="1000" placeholder="Example: Corrected JO-NO based on the physical maintenance job order form.">{{ old('remarks') }}</textarea>

                                @error('remarks')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <div class="form-text">
                                    Optional. This will be saved in the job order update history.
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-footer bg-white d-flex justify-content-end gap-2">
                                <a href="{{ route('maintenance.job-orders.show', $jobOrder) }}"
                                    class="btn btn-falcon-default">
                                    Cancel
                                </a>

                                <button type="submit" class="btn btn-primary">
                                    <span class="fas fa-save me-1"></span>
                                    Save JO-NO
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0">
                                    <span class="fas fa-file-lines me-2 text-primary"></span>
                                    Job Order Summary
                                </h5>
                            </div>

                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="fs-11 text-600 text-uppercase">Current JO-NO</div>
                                    <div class="fw-bold">{{ $jobOrder->job_order_no }}</div>
                                </div>

                                <div class="mb-3">
                                    <div class="fs-11 text-600 text-uppercase">Bus No.</div>
                                    <div class="fw-bold">
                                        {{ $jobOrder->bus?->bus_no ?? ($jobOrder->bus_no_snapshot ?? 'N/A') }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="fs-11 text-600 text-uppercase">Plate No.</div>
                                    <div class="fw-bold">
                                        {{ $jobOrder->bus?->plate_no ?? ($jobOrder->plate_no_snapshot ?? 'N/A') }}
                                    </div>
                                </div>

                                <div>
                                    <div class="fs-11 text-600 text-uppercase">Status</div>
                                    <span class="badge rounded-pill {{ $jobOrder->status_badge_class }}">
                                        <span class="{{ $jobOrder->status_icon }} me-1"></span>
                                        {{ $jobOrder->status_label }}
                                    </span>
                                </div>

                                <div class="alert alert-subtle-info mt-3 mb-0">
                                    <span class="fas fa-circle-info me-1"></span>
                                    Remarks will be stored in the update history for audit tracking.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
