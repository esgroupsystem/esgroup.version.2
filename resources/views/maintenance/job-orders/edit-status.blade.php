@extends('layouts.app')

@section('title', 'Edit Maintenance Status')

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

        <style>
            .jo-status-edit .jo-hero {
                background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
                border: 1px solid rgba(216, 226, 239, .8);
            }

            .jo-status-edit .jo-icon-box {
                width: 50px;
                height: 50px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 16px;
            }

            .jo-status-edit .jo-status-option {
                display: block;
                cursor: pointer;
                height: 100%;
            }

            .jo-status-edit .jo-status-option-body {
                height: 100%;
                border: 1px solid #d8e2ef;
                border-radius: .85rem;
                padding: 1rem;
                background: #fff;
                transition: all .15s ease-in-out;
            }

            .jo-status-edit .jo-status-option:hover .jo-status-option-body {
                border-color: var(--falcon-primary);
                box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .06);
                transform: translateY(-1px);
            }

            .jo-status-edit .jo-status-option input:checked+.jo-status-option-body {
                border-color: var(--falcon-primary);
                box-shadow: inset 0 0 0 1px var(--falcon-primary), 0 .5rem 1rem rgba(0, 0, 0, .06);
                background: #f8fbff;
            }

            .jo-status-edit .jo-radio-circle {
                width: 22px;
                height: 22px;
                border-radius: 50%;
                border: 2px solid #b6c2d2;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .jo-status-edit .jo-status-option input:checked+.jo-status-option-body .jo-radio-circle {
                border-color: var(--falcon-primary);
                background: var(--falcon-primary);
                color: #fff;
            }

            .jo-status-edit .jo-summary-row {
                padding: .8rem 0;
                border-bottom: 1px solid #edf2f9;
            }

            .jo-status-edit .jo-summary-row:last-child {
                border-bottom: 0;
            }

            .jo-status-edit .jo-label {
                font-size: .72rem;
                color: #748194;
                text-transform: uppercase;
                letter-spacing: .04em;
                margin-bottom: .2rem;
            }

            .jo-status-edit .jo-value {
                font-weight: 700;
                color: #344050;
            }

            .jo-status-edit .jo-sticky-card {
                position: sticky;
                top: 1rem;
            }
        </style>

        <div class="content jo-status-edit">
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

            <div class="card jo-hero border-0 shadow-sm mb-3">
                <div class="card-body py-3">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg-8">
                            <div class="d-flex align-items-center gap-3">
                                <div class="jo-icon-box {{ $jobOrder->status_badge_class }}">
                                    <span class="{{ $jobOrder->status_icon }} fs-5"></span>
                                </div>

                                <div>
                                    <h4 class="mb-1">Update Maintenance Status</h4>
                                    <div class="text-600">
                                        Change the repair state for <strong>{{ $jobOrder->job_order_no }}</strong>.
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

            <form method="POST" action="{{ route('maintenance.job-orders.update-status', $jobOrder) }}">
                @csrf
                @method('PATCH')

                <div class="row g-3">
                    <div class="col-xl-8">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0">
                                    <span class="fas fa-route me-2 text-primary"></span>
                                    Select Repair Status
                                </h5>
                            </div>

                            <div class="card-body">
                                <div class="row g-3">
                                    @foreach ($statuses as $status)
                                        <div class="col-md-6">
                                            <label class="jo-status-option">
                                                <input type="radio" name="status" value="{{ $status->value }}"
                                                    class="d-none" @checked(old('status', $jobOrder->status?->value) === $status->value)>

                                                <div class="jo-status-option-body">
                                                    <div
                                                        class="d-flex align-items-start justify-content-between gap-3 mb-3">
                                                        <span class="badge rounded-pill {{ $status->badgeClass() }}">
                                                            <span class="{{ $status->icon() }} me-1"></span>
                                                            {{ $status->label() }}
                                                        </span>

                                                        <span class="jo-radio-circle">
                                                            <span class="fas fa-check fs-11"></span>
                                                        </span>
                                                    </div>

                                                    <div class="fw-semibold mb-1">
                                                        {{ $status->label() }}
                                                    </div>

                                                    <div class="fs-11 text-600">
                                                        {{ $status->description() }}
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>

                                @error('status')
                                    <div class="text-danger fs-11 mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-body d-flex justify-content-end gap-2">
                                <a href="{{ route('maintenance.job-orders.show', $jobOrder) }}"
                                    class="btn btn-falcon-default">
                                    Cancel
                                </a>

                                <button type="submit" class="btn btn-primary">
                                    <span class="fas fa-save me-1"></span>
                                    Save Status
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card border-0 shadow-sm jo-sticky-card">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0">
                                    <span class="fas fa-file-lines me-2 text-primary"></span>
                                    Job Order Summary
                                </h5>
                            </div>

                            <div class="card-body">
                                <div class="jo-summary-row">
                                    <div class="jo-label">Job Order No.</div>
                                    <div class="jo-value">{{ $jobOrder->job_order_no }}</div>
                                </div>

                                <div class="jo-summary-row">
                                    <div class="jo-label">Bus No.</div>
                                    <div class="jo-value">
                                        {{ $jobOrder->bus?->bus_no ?? ($jobOrder->bus_no_snapshot ?? 'N/A') }}
                                    </div>
                                </div>

                                <div class="jo-summary-row">
                                    <div class="jo-label">Plate No.</div>
                                    <div class="jo-value">
                                        {{ $jobOrder->bus?->plate_no ?? ($jobOrder->plate_no_snapshot ?? 'N/A') }}
                                    </div>
                                </div>

                                <div class="jo-summary-row">
                                    <div class="jo-label">Current Status</div>
                                    <span class="badge rounded-pill {{ $jobOrder->status_badge_class }}">
                                        <span class="{{ $jobOrder->status_icon }} me-1"></span>
                                        {{ $jobOrder->status_label }}
                                    </span>
                                </div>

                                <div class="jo-summary-row">
                                    <div class="jo-label">Requester</div>
                                    <div class="jo-value">
                                        {{ $jobOrder->full_name ?: 'Not specified' }}
                                    </div>
                                </div>

                                <div class="jo-summary-row">
                                    <div class="jo-label">Work Description</div>
                                    <div class="text-700" style="white-space: pre-line;">
                                        {{ \Illuminate\Support\Str::limit($jobOrder->description_of_work, 220) }}
                                    </div>
                                </div>

                                <div class="alert alert-subtle-info mb-0 mt-3">
                                    <div class="fw-semibold mb-1">
                                        <span class="fas fa-circle-info me-1"></span>
                                        Status Rules
                                    </div>

                                    <div class="fs-11">
                                        <strong>Standby</strong> means no repair action yet.
                                        <strong>Waiting Parts</strong> means blocked by parts availability.
                                        <strong>On Going Repair</strong> means active repair.
                                        <strong>Operational</strong> means ready for use.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
