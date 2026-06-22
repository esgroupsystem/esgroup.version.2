@extends('layouts.app')

@section('content')
    <div class="container-fluid py-3">

        <div class="card mb-3">
            <div class="card-header bg-body-tertiary">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-0 fleet-section-title">
                            Update Bus Information
                        </h5>
                        <small class="text-muted">
                            Edit monitoring details for Bus No. {{ $bus->bus_no }}
                        </small>
                    </div>

                    <a href="{{ route('fleet.buses.index', request()->query()) }}" class="btn btn-falcon-default btn-sm">
                        <span class="fas fa-arrow-left me-1"></span>
                        Back to Monitoring
                    </a>
                </div>
            </div>

            <div class="card-body">
                <form method="POST"
                    action="{{ route('fleet.buses.update', array_merge(['bus' => $bus->id], request()->query())) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="bus_no" class="form-label">Bus No. <span class="text-danger">*</span></label>
                            <input type="text" name="bus_no" id="bus_no" value="{{ old('bus_no', $bus->bus_no) }}"
                                class="form-control @error('bus_no') is-invalid @enderror" required>

                            @error('bus_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="plate_no" class="form-label">Plate No.</label>
                            <input type="text" name="plate_no" id="plate_no"
                                value="{{ old('plate_no', $bus->plate_no) }}"
                                class="form-control @error('plate_no') is-invalid @enderror">

                            @error('plate_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="company" class="form-label">Company</label>
                            <input type="text" name="company" id="company" value="{{ old('company', $bus->company) }}"
                                class="form-control @error('company') is-invalid @enderror">

                            @error('company')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="garage" class="form-label">Garage</label>
                            <input type="text" name="garage" id="garage" value="{{ old('garage', $bus->garage) }}"
                                class="form-control @error('garage') is-invalid @enderror">

                            @error('garage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="operational_status" class="form-label">Condition <span
                                    class="text-danger">*</span></label>
                            <select name="operational_status" id="operational_status"
                                class="form-select @error('operational_status') is-invalid @enderror" required>
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
                            <label for="sale_status" class="form-label">Sale Status <span
                                    class="text-danger">*</span></label>
                            <select name="sale_status" id="sale_status"
                                class="form-select @error('sale_status') is-invalid @enderror" required>
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

                        <div class="col-md-4">
                            <label for="chassis_number" class="form-label">Chassis Number</label>
                            <input type="text" name="chassis_number" id="chassis_number"
                                value="{{ old('chassis_number', $bus->chassis_number) }}"
                                class="form-control @error('chassis_number') is-invalid @enderror">

                            @error('chassis_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="engine_number" class="form-label">Engine Number</label>
                            <input type="text" name="engine_number" id="engine_number"
                                value="{{ old('engine_number', $bus->engine_number) }}"
                                class="form-control @error('engine_number') is-invalid @enderror">

                            @error('engine_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="case_number" class="form-label">Case Number</label>
                            <input type="text" name="case_number" id="case_number"
                                value="{{ old('case_number', $bus->case_number) }}"
                                class="form-control @error('case_number') is-invalid @enderror">

                            @error('case_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="monitoring_remarks" class="form-label">Remarks</label>
                            <textarea name="monitoring_remarks" id="monitoring_remarks" rows="4"
                                class="form-control @error('monitoring_remarks') is-invalid @enderror">{{ old('monitoring_remarks', $bus->monitoring_remarks) }}</textarea>

                            @error('monitoring_remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('fleet.buses.index', request()->query()) }}" class="btn btn-falcon-default">
                            Cancel
                        </a>

                        <button type="submit" class="btn btn-primary">
                            <span class="fas fa-save me-1"></span>
                            Save Updates
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection
