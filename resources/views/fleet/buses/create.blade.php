@extends('layouts.app')

@section('title', 'Add Bus | Fleet Monitoring')

@section('content')
    <style>
        .fleet-create-wrapper {
            min-height: calc(100vh - 110px);
        }

        .fleet-create-hero {
            position: relative;
            overflow: hidden;
            border: 0;
            border-radius: 1.15rem;
            background:
                radial-gradient(circle at top left, rgba(44, 123, 229, .18), transparent 32%),
                radial-gradient(circle at bottom right, rgba(0, 210, 122, .13), transparent 34%),
                var(--falcon-card-bg, #fff);
            box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, .045);
        }

        .fleet-create-hero::after {
            content: "";
            position: absolute;
            right: -90px;
            top: -90px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: rgba(44, 123, 229, .08);
        }

        .fleet-icon-box {
            width: 52px;
            height: 52px;
            border-radius: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(44, 123, 229, .12);
            color: var(--falcon-primary, #2c7be5);
            font-size: 1.35rem;
        }

        .fleet-form-card {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 .45rem 1.15rem rgba(0, 0, 0, .045);
        }

        .fleet-section-card {
            border: 1px solid var(--falcon-border-color, #edf2f9);
            border-radius: 1rem;
            background: var(--falcon-card-bg, #fff);
        }

        .fleet-section-card-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--falcon-border-color, #edf2f9);
            background: var(--falcon-body-bg, #f9fafd);
            border-radius: 1rem 1rem 0 0;
        }

        .fleet-section-title-sm {
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--falcon-700, #344050);
            margin-bottom: .15rem;
        }

        .fleet-section-subtitle {
            font-size: .82rem;
            color: var(--falcon-600, #748194);
        }

        .fleet-field-hint {
            font-size: .75rem;
            color: var(--falcon-500, #9da9bb);
        }

        .fleet-status-preview {
            border: 1px dashed var(--falcon-border-color, #d8e2ef);
            border-radius: 1rem;
            background: var(--falcon-body-bg, #f9fafd);
            padding: 1rem;
        }

        .fleet-required-dot {
            width: .45rem;
            height: .45rem;
            display: inline-block;
            border-radius: 50%;
            background: var(--falcon-danger, #e63757);
            margin-left: .25rem;
            vertical-align: middle;
        }

        .fleet-form-actions {
            position: sticky;
            bottom: 0;
            z-index: 5;
            background: rgba(var(--falcon-body-bg-rgb, 249, 250, 253), .92);
            backdrop-filter: blur(10px);
            border-top: 1px solid var(--falcon-border-color, #edf2f9);
            padding: 1rem 0 0;
        }

        .fleet-soft-panel {
            border-radius: 1rem;
            background: linear-gradient(135deg, rgba(44, 123, 229, .08), rgba(0, 210, 122, .06));
            border: 1px solid rgba(44, 123, 229, .12);
        }

        .fleet-input-icon {
            position: relative;
        }

        .fleet-input-icon .form-control,
        .fleet-input-icon .form-select {
            padding-left: 2.45rem;
        }

        .fleet-input-icon .fleet-input-symbol {
            position: absolute;
            top: 50%;
            left: .9rem;
            transform: translateY(-50%);
            color: var(--falcon-500, #9da9bb);
            z-index: 2;
        }

        .fleet-textarea-icon .fleet-input-symbol {
            top: 1.15rem;
            transform: none;
        }

        @media (max-width: 767.98px) {
            .fleet-form-actions {
                position: static;
            }
        }
    </style>

    <div class="container" data-layout="container">
        <script>
            var isFluid = JSON.parse(localStorage.getItem('isFluid'));
            if (isFluid) {
                var container = document.querySelector('[data-layout]');
                container.classList.remove('container');
                container.classList.add('container-fluid');
            }
        </script>

        <div class="content fleet-create-wrapper">
            <form method="POST" action="{{ route('fleet.buses.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-12">
                        <div class="card fleet-create-hero mb-3">
                            <div class="card-body position-relative">
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                    <div class="d-flex gap-3 align-items-start">
                                        <div class="fleet-icon-box">
                                            <span class="fas fa-bus"></span>
                                        </div>

                                        <div>
                                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                                <h4 class="mb-0 fw-bold">Add New Bus</h4>
                                                <span class="badge badge-soft badge-subtle-primary text-primary">
                                                    Fleet Monitoring
                                                </span>
                                            </div>

                                            <p class="mb-0 text-muted">
                                                Register a bus unit with operating condition, sale status, vehicle identifiers,
                                                and monitoring remarks.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="{{ route('fleet.buses.index', request()->query()) }}"
                                            class="btn btn-falcon-default btn-sm">
                                            <span class="fas fa-arrow-left me-1"></span>
                                            Back to List
                                        </a>

                                        <button type="submit" class="btn btn-falcon-primary btn-sm">
                                            <span class="fas fa-save me-1"></span>
                                            Save Bus
                                        </button>
                                    </div>
                                </div>

                                <div class="row g-3 mt-3">
                                    <div class="col-md-4">
                                        <div class="fleet-soft-panel p-3 h-100">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fas fa-hashtag text-primary"></span>
                                                <div>
                                                    <div class="fw-semibold">Bus Identity</div>
                                                    <small class="text-muted">Bus number, plate, company, garage</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="fleet-soft-panel p-3 h-100">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fas fa-clipboard-check text-success"></span>
                                                <div>
                                                    <div class="fw-semibold">Monitoring Status</div>
                                                    <small class="text-muted">Condition and sale availability</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="fleet-soft-panel p-3 h-100">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fas fa-fingerprint text-info"></span>
                                                <div>
                                                    <div class="fw-semibold">Unit References</div>
                                                    <small class="text-muted">Chassis, engine, case, remarks</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="col-12">
                            <div class="alert alert-danger border-0 shadow-sm">
                                <div class="d-flex">
                                    <span class="fas fa-exclamation-circle fs-5 me-2 mt-1"></span>
                                    <div>
                                        <strong>Please check the form.</strong>
                                        <ul class="mb-0 mt-2">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="col-xl-8">
                        <div class="card fleet-form-card mb-3">
                            <div class="card-body p-0">
                                <div class="fleet-section-card">
                                    <div class="fleet-section-card-header">
                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                            <div>
                                                <div class="fleet-section-title-sm">Primary Details</div>
                                                <div class="fleet-section-subtitle">
                                                    Main information used in the monitoring list.
                                                </div>
                                            </div>
                                            <span class="badge badge-soft badge-subtle-info text-info">
                                                Required: Bus No.
                                            </span>
                                        </div>
                                    </div>

                                    <div class="p-3 p-lg-4">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="bus_no" class="form-label">
                                                    Bus No.
                                                    <span class="fleet-required-dot"></span>
                                                </label>

                                                <div class="fleet-input-icon">
                                                    <span class="fas fa-bus fleet-input-symbol"></span>
                                                    <input type="text"
                                                        name="bus_no"
                                                        id="bus_no"
                                                        value="{{ old('bus_no') }}"
                                                        class="form-control @error('bus_no') is-invalid @enderror"
                                                        placeholder="Example: 4719005"
                                                        required>
                                                </div>

                                                <div class="fleet-field-hint mt-1">
                                                    This must be unique in the bus master list.
                                                </div>

                                                @error('bus_no')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label for="plate_no" class="form-label">Plate No.</label>

                                                <div class="fleet-input-icon">
                                                    <span class="fas fa-id-card fleet-input-symbol"></span>
                                                    <input type="text"
                                                        name="plate_no"
                                                        id="plate_no"
                                                        value="{{ old('plate_no') }}"
                                                        class="form-control @error('plate_no') is-invalid @enderror"
                                                        placeholder="Example: NFZ 4616">
                                                </div>

                                                @error('plate_no')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label for="company" class="form-label">Company</label>

                                                <div class="fleet-input-icon">
                                                    <span class="fas fa-building fleet-input-symbol"></span>
                                                    <input type="text"
                                                        name="company"
                                                        id="company"
                                                        value="{{ old('company') }}"
                                                        class="form-control @error('company') is-invalid @enderror"
                                                        placeholder="Example: JELL">
                                                </div>

                                                @error('company')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label for="garage" class="form-label">Garage</label>

                                                <div class="fleet-input-icon">
                                                    <span class="fas fa-warehouse fleet-input-symbol"></span>
                                                    <input type="text"
                                                        name="garage"
                                                        id="garage"
                                                        value="{{ old('garage') }}"
                                                        class="form-control @error('garage') is-invalid @enderror"
                                                        placeholder="Example: BALINTAWAK">
                                                </div>

                                                @error('garage')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card fleet-form-card mb-3">
                            <div class="card-body p-0">
                                <div class="fleet-section-card">
                                    <div class="fleet-section-card-header">
                                        <div>
                                            <div class="fleet-section-title-sm">Vehicle References</div>
                                            <div class="fleet-section-subtitle">
                                                Optional identifiers for technical and documentation tracking.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-3 p-lg-4">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label for="chassis_number" class="form-label">Chassis Number</label>

                                                <div class="fleet-input-icon">
                                                    <span class="fas fa-barcode fleet-input-symbol"></span>
                                                    <input type="text"
                                                        name="chassis_number"
                                                        id="chassis_number"
                                                        value="{{ old('chassis_number') }}"
                                                        class="form-control @error('chassis_number') is-invalid @enderror"
                                                        placeholder="Chassis ref.">
                                                </div>

                                                @error('chassis_number')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-4">
                                                <label for="engine_number" class="form-label">Engine Number</label>

                                                <div class="fleet-input-icon">
                                                    <span class="fas fa-cogs fleet-input-symbol"></span>
                                                    <input type="text"
                                                        name="engine_number"
                                                        id="engine_number"
                                                        value="{{ old('engine_number') }}"
                                                        class="form-control @error('engine_number') is-invalid @enderror"
                                                        placeholder="Engine ref.">
                                                </div>

                                                @error('engine_number')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-4">
                                                <label for="case_number" class="form-label">Case Number</label>

                                                <div class="fleet-input-icon">
                                                    <span class="fas fa-folder-open fleet-input-symbol"></span>
                                                    <input type="text"
                                                        name="case_number"
                                                        id="case_number"
                                                        value="{{ old('case_number') }}"
                                                        class="form-control @error('case_number') is-invalid @enderror"
                                                        placeholder="Case ref.">
                                                </div>

                                                @error('case_number')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-12">
                                                <label for="monitoring_remarks" class="form-label">Monitoring Remarks</label>

                                                <div class="fleet-input-icon fleet-textarea-icon">
                                                    <span class="fas fa-comment-dots fleet-input-symbol"></span>
                                                    <textarea name="monitoring_remarks"
                                                        id="monitoring_remarks"
                                                        rows="5"
                                                        class="form-control @error('monitoring_remarks') is-invalid @enderror"
                                                        placeholder="Optional notes for this unit">{{ old('monitoring_remarks') }}</textarea>
                                                </div>

                                                @error('monitoring_remarks')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card fleet-form-card mb-3">
                            <div class="card-body p-0">
                                <div class="fleet-section-card">
                                    <div class="fleet-section-card-header">
                                        <div>
                                            <div class="fleet-section-title-sm">Status Setup</div>
                                            <div class="fleet-section-subtitle">
                                                This affects dashboard count and grouping.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-3 p-lg-4">
                                        <div class="mb-3">
                                            <label for="operational_status" class="form-label">Condition</label>

                                            <div class="fleet-input-icon">
                                                <span class="fas fa-tools fleet-input-symbol"></span>
                                                <select name="operational_status"
                                                    id="operational_status"
                                                    class="form-select @error('operational_status') is-invalid @enderror">
                                                    @foreach ($operationalStatusOptions as $value => $label)
                                                        <option value="{{ $value }}"
                                                            @selected(old('operational_status', \App\Models\Bus::STATUS_ACTIVE) === $value)>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            @error('operational_status')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="sale_status" class="form-label">Sale Status</label>

                                            <div class="fleet-input-icon">
                                                <span class="fas fa-tag fleet-input-symbol"></span>
                                                <select name="sale_status"
                                                    id="sale_status"
                                                    class="form-select @error('sale_status') is-invalid @enderror">
                                                    @foreach ($saleStatusOptions as $value => $label)
                                                        <option value="{{ $value }}"
                                                            @selected(old('sale_status', \App\Models\Bus::SALE_NOT_FOR_SALE) === $value)>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            @error('sale_status')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="fleet-status-preview">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="fw-semibold">Default Setup</span>
                                                <span class="badge badge-soft badge-subtle-success text-success">
                                                    Active
                                                </span>
                                            </div>

                                            <div class="small text-muted mb-3">
                                                New buses are normally saved as Active and Not For Sale unless changed here.
                                            </div>

                                            <div class="d-flex flex-column gap-2">
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-muted">Condition</span>
                                                    <span class="fw-semibold">Operational</span>
                                                </div>

                                                <div class="d-flex justify-content-between">
                                                    <span class="text-muted">Sale Status</span>
                                                    <span class="fw-semibold">Not For Sale</span>
                                                </div>

                                                <div class="d-flex justify-content-between">
                                                    <span class="text-muted">Dashboard</span>
                                                    <span class="fw-semibold">Included</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card fleet-form-card">
                            <div class="card-body">
                                <div class="d-flex gap-3">
                                    <div class="avatar avatar-xl">
                                        <div class="avatar-name rounded-circle bg-primary-subtle text-primary">
                                            <span class="fas fa-info"></span>
                                        </div>
                                    </div>

                                    <div>
                                        <h6 class="mb-1">Entry Reminder</h6>
                                        <p class="text-muted small mb-0">
                                            Use the same bus number format used in your imported master list to avoid duplicate
                                            records and wrong dashboard counts.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="fleet-form-actions">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body py-3">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <div class="small text-muted">
                                            Required fields are marked with a red dot.
                                        </div>

                                        <div class="d-flex gap-2">
                                            <a href="{{ route('fleet.buses.index', request()->query()) }}"
                                                class="btn btn-falcon-default">
                                                Cancel
                                            </a>

                                            <button type="submit" class="btn btn-falcon-primary">
                                                <span class="fas fa-save me-1"></span>
                                                Save Bus
                                            </button>
                                        </div>
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
