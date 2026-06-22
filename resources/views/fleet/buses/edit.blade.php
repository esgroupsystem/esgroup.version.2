@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        {{-- PAGE HEADER --}}
        <div class="card mb-3 border-0 shadow-sm overflow-hidden">
            <div class="card-body bg-body-tertiary">

                <div class="row align-items-center g-3">

                    <div class="col">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-2 fs-10">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('fleet.buses.index', request()->query()) }}">
                                        Bus Monitoring
                                    </a>
                                </li>
                                <li class="breadcrumb-item active">Update Bus</li>
                            </ol>
                        </nav>

                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center"
                                style="width:46px;height:46px;">
                                <span class="fas fa-bus fs-6"></span>
                            </div>

                            <div>
                                <h3 class="mb-1 fw-bold">
                                    Update Bus Information
                                </h3>
                                <p class="text-muted mb-0">
                                    Edit monitoring details for Bus No. <strong>{{ $bus->bus_no }}</strong>
                                </p>
                            </div>
                        </div>

                    </div>

                    <div class="col-auto">
                        <a href="{{ route('fleet.buses.index', request()->query()) }}" class="btn btn-falcon-default">
                            <span class="fas fa-arrow-left me-1"></span>
                            Back
                        </a>
                    </div>

                </div>

            </div>
        </div>

        {{-- ALERTS --}}
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm">
                <div class="d-flex">
                    <span class="fas fa-exclamation-circle me-2 mt-1"></span>
                    <div>
                        <h6 class="mb-1">Please fix the following errors</h6>
                        <small>Some fields are invalid or required.</small>
                    </div>
                </div>
            </div>
        @endif

        {{-- FORM CARD --}}
        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                    <div>
                        <h5 class="mb-0 fw-semibold">
                            <span class="fas fa-clipboard-check text-primary me-2"></span>
                            Bus Monitoring Form
                        </h5>
                        <small class="text-muted">
                            Update operational and fleet tracking information
                        </small>
                    </div>

                    <span class="badge badge-soft-warning text-warning">
                        Editing Record
                    </span>

                </div>
            </div>

            <div class="card-body">

                <form method="POST"
                    action="{{ route('fleet.buses.update', array_merge(['bus' => $bus->id], request()->query())) }}">

                    @csrf
                    @method('PUT')

                    <div class="row g-3">

                        {{-- BUS IDENTITY --}}
                        <div class="col-12">
                            <div class="p-3 border rounded-3 bg-light">

                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="fas fa-id-card text-primary"></span>
                                    <h6 class="mb-0 fw-semibold">Bus Identity</h6>
                                </div>

                                <div class="row g-3">

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">
                                            Bus No. <span class="text-danger">*</span>
                                        </label>

                                        <input type="text" name="bus_no" value="{{ old('bus_no', $bus->bus_no) }}"
                                            class="form-control @error('bus_no') is-invalid @enderror">

                                        @error('bus_no')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Plate No.</label>

                                        <input type="text" name="plate_no" value="{{ old('plate_no', $bus->plate_no) }}"
                                            class="form-control @error('plate_no') is-invalid @enderror">

                                        @error('plate_no')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Company</label>

                                        <input type="text" name="company" value="{{ old('company', $bus->company) }}"
                                            class="form-control @error('company') is-invalid @enderror">

                                        @error('company')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Garage</label>

                                        <input type="text" name="garage" value="{{ old('garage', $bus->garage) }}"
                                            class="form-control @error('garage') is-invalid @enderror">

                                        @error('garage')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                </div>
                            </div>
                        </div>

                        {{-- STATUS SECTION --}}
                        <div class="col-12">
                            <div class="p-3 border rounded-3 bg-light">

                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="fas fa-tasks text-success"></span>
                                    <h6 class="mb-0 fw-semibold">Operational Status</h6>
                                </div>

                                <div class="row g-3">

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">
                                            Condition <span class="text-danger">*</span>
                                        </label>

                                        <select name="operational_status"
                                            class="form-select @error('operational_status') is-invalid @enderror">
                                            @foreach ($operationalStatusOptions as $value => $label)
                                                <option value="{{ $value }}" @selected(old('operational_status', $bus->operational_status) === $value)>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>

                                        @error('operational_status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">
                                            Sale Status <span class="text-danger">*</span>
                                        </label>

                                        <select name="sale_status"
                                            class="form-select @error('sale_status') is-invalid @enderror">
                                            @foreach ($saleStatusOptions as $value => $label)
                                                <option value="{{ $value }}" @selected(old('sale_status', $bus->sale_status) === $value)>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>

                                        @error('sale_status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                </div>
                            </div>
                        </div>

                        {{-- TECHNICAL INFO --}}
                        <div class="col-12">
                            <div class="p-3 border rounded-3 bg-light">

                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="fas fa-cogs text-warning"></span>
                                    <h6 class="mb-0 fw-semibold">Technical Details</h6>
                                </div>

                                <div class="row g-3">

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Chassis Number</label>
                                        <input type="text" name="chassis_number"
                                            value="{{ old('chassis_number', $bus->chassis_number) }}"
                                            class="form-control @error('chassis_number') is-invalid @enderror">
                                        @error('chassis_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Engine Number</label>
                                        <input type="text" name="engine_number"
                                            value="{{ old('engine_number', $bus->engine_number) }}"
                                            class="form-control @error('engine_number') is-invalid @enderror">
                                        @error('engine_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Case Number</label>
                                        <input type="text" name="case_number"
                                            value="{{ old('case_number', $bus->case_number) }}"
                                            class="form-control @error('case_number') is-invalid @enderror">
                                        @error('case_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                </div>
                            </div>
                        </div>

                        {{-- REMARKS --}}
                        <div class="col-12">
                            <div class="p-3 border rounded-3 bg-light">

                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="fas fa-comment-dots text-info"></span>
                                    <h6 class="mb-0 fw-semibold">Monitoring Remarks</h6>
                                </div>

                                <textarea name="monitoring_remarks" rows="4"
                                    class="form-control @error('monitoring_remarks') is-invalid @enderror">{{ old('monitoring_remarks', $bus->monitoring_remarks) }}</textarea>

                                @error('monitoring_remarks')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                            </div>
                        </div>

                    </div>

                    {{-- ACTION BAR --}}
                    <div
                        class="d-flex justify-content-between align-items-center mt-4 p-3 border rounded-3 bg-body-tertiary">

                        <small class="text-muted">
                            Update carefully — changes affect fleet monitoring.
                        </small>

                        <div class="d-flex gap-2">
                            <a href="{{ route('fleet.buses.index', request()->query()) }}"
                                class="btn btn-falcon-default">
                                Cancel
                            </a>

                            <button type="submit" class="btn btn-primary">
                                <span class="fas fa-save me-1"></span>
                                Save Updates
                            </button>
                        </div>

                    </div>

                </form>

            </div>
        </div>

    </div>
@endsection
