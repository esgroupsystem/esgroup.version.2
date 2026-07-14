@extends('layouts.app')

@section('title', 'Create Maintenance Job Order')

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
            .jo-create .jo-hero {
                background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
                border: 1px solid rgba(216, 226, 239, .8);
            }

            .jo-create .jo-icon-box {
                width: 46px;
                height: 46px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 14px;
            }

            .jo-create .jo-section-number {
                width: 28px;
                height: 28px;
                border-radius: 50%;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: .78rem;
                font-weight: 700;
            }

            .jo-create .jo-preview-card {
                position: sticky;
                top: 1rem;
            }

            .jo-create .jo-data-row {
                display: flex;
                justify-content: space-between;
                gap: 1rem;
                padding: .75rem 0;
                border-bottom: 1px solid #edf2f9;
            }

            .jo-create .jo-data-row:last-child {
                border-bottom: 0;
            }

            .jo-create .jo-data-label {
                font-size: .72rem;
                color: #748194;
                text-transform: uppercase;
                letter-spacing: .04em;
            }

            .jo-create .jo-data-value {
                font-weight: 700;
                text-align: right;
            }

            .jo-create .jo-live-work {
                min-height: 100px;
                white-space: pre-line;
            }
        </style>

        <div class="content jo-create">
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
                                <div class="jo-icon-box bg-primary-subtle text-primary">
                                    <span class="fas fa-file-circle-plus fs-5"></span>
                                </div>

                                <div>
                                    <h4 class="mb-1">Create Maintenance Job Order</h4>
                                    <div class="text-600">
                                        Encode a maintenance request and connect it directly to the selected bus record.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 text-lg-end">
                            <a href="{{ route('maintenance.job-orders.index') }}" class="btn btn-falcon-default">
                                <span class="fas fa-arrow-left me-1"></span>
                                Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('maintenance.job-orders.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-xl-8">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="jo-section-number bg-primary-subtle text-primary">1</span>
                                    <div>
                                        <h5 class="mb-0">Job Order Number</h5>
                                        <div class="fs-11 text-600">
                                            Leave blank to auto-generate, or encode your own JO-NO.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <label for="job_order_no" class="form-label fw-semibold">JO-NO</label>

                                <input type="text" name="job_order_no" id="job_order_no"
                                    value="{{ old('job_order_no') }}"
                                    class="form-control @error('job_order_no') is-invalid @enderror" maxlength="50"
                                    placeholder="Example: JO-2026-0001">

                                @error('job_order_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <div class="form-text">
                                    Allowed: letters, numbers, dash, and slash only. If blank, the system will
                                    generate the JO-NO.
                                </div>
                            </div>
                        </div>
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="jo-section-number bg-primary-subtle text-primary">2</span>
                                    <div>
                                        <h5 class="mb-0">Bus Assignment</h5>
                                        <div class="fs-11 text-600">Required vehicle connection for maintenance analytics.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <label for="bus_id" class="form-label fw-semibold">
                                    Select Bus <span class="text-danger">*</span>
                                </label>

                                <select name="bus_id" id="bus_id"
                                    class="form-select js-choice @error('bus_id') is-invalid @enderror"
                                    data-options='{"searchEnabled":true,"shouldSort":false,"placeholder":true,"placeholderValue":"Search bus no., plate no., company, or garage"}'
                                    required>
                                    <option value="">Search and choose bus record</option>

                                    @foreach ($buses as $bus)
                                        <option value="{{ $bus->id }}" data-bus-no="{{ $bus->bus_no }}"
                                            data-plate-no="{{ $bus->plate_no }}" data-company="{{ $bus->company }}"
                                            data-garage="{{ $bus->garage }}"
                                            data-operational-status="{{ $bus->operational_status_label }}"
                                            data-operational-badge="{{ $bus->operational_status_badge_class }}"
                                            data-last-odometer="{{ $bus->latestJobOrderMaintenanceWithOdometer?->odometer_reading }}"
                                            @selected((string) old('bus_id') === (string) $bus->id)>
                                            {{ $bus->bus_no }} — {{ $bus->plate_no ?? 'No Plate' }} —
                                            {{ $bus->company ?? 'No Company' }} — {{ $bus->garage ?? 'No Garage' }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('bus_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <div id="selected-bus-card" class="alert alert-subtle-info mt-3 mb-0 d-none">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <div class="fs-11 text-600">Bus No.</div>
                                            <div class="fw-semibold" id="selected-bus-no">—</div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="fs-11 text-600">Plate No.</div>
                                            <div class="fw-semibold" id="selected-plate-no">—</div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="fs-11 text-600">Garage</div>
                                            <div class="fw-semibold" id="selected-garage">—</div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="fs-11 text-600">Bus Status</div>
                                            <div id="selected-operational-status">—</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="jo-section-number bg-primary-subtle text-primary">3</span>
                                    <div>
                                        <h5 class="mb-0">Work Request</h5>
                                        <div class="fs-11 text-600">Requester is optional. Work description is required.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="full_name" class="form-label fw-semibold">Full Name</label>

                                    <input type="text" name="full_name" id="full_name" value="{{ old('full_name') }}"
                                        class="form-control @error('full_name') is-invalid @enderror"
                                        placeholder="Optional requester, driver, mechanic, or staff name">

                                    @error('full_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div>
                                    <label for="description_of_work" class="form-label fw-semibold">
                                        Description of Work <span class="text-danger">*</span>
                                    </label>

                                    <textarea name="description_of_work" id="description_of_work" rows="7"
                                        class="form-control @error('description_of_work') is-invalid @enderror" required
                                        placeholder="Example: Inspect brake system, check air leak, replace worn brake lining, road test after repair.">{{ old('description_of_work') }}</textarea>

                                    @error('description_of_work')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror

                                    <div class="form-text">
                                        Write the actual maintenance concern clearly. This text will appear in the bus
                                        maintenance history.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="jo-section-number bg-primary-subtle text-primary">4</span>
                                    <div>
                                        <h5 class="mb-0">Repair Assignment</h5>
                                        <div class="fs-11 text-600">
                                            Optional during creation. Required before the unit can be marked Operational.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                @include('maintenance.job-orders._repair-details-fields', [
                                    'repairTypes' => $repairTypes,
                                ])
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="jo-section-number bg-primary-subtle text-primary">5</span>
                                    <div>
                                        <h5 class="mb-0">Odometer Reading</h5>
                                        <div class="fs-11 text-600">Optional, but useful for maintenance interval tracking.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <label for="odometer_reading" class="form-label fw-semibold">Current Odometer</label>

                                <div class="input-group">
                                    <input type="number" name="odometer_reading" id="odometer_reading"
                                        value="{{ old('odometer_reading') }}"
                                        class="form-control @error('odometer_reading') is-invalid @enderror"
                                        min="0" max="9999999" placeholder="Optional current odometer reading">

                                    <span class="input-group-text">km</span>

                                    @error('odometer_reading')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div id="odometer-comparison" class="alert alert-subtle-secondary mt-3 mb-0">
                                    Select a bus to preview odometer comparison.
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-body d-flex justify-content-end gap-2">
                                <a href="{{ route('maintenance.job-orders.index') }}" class="btn btn-falcon-default">
                                    Cancel
                                </a>

                                <button type="submit" class="btn btn-primary">
                                    <span class="fas fa-save me-1"></span>
                                    Save Job Order
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card border-0 shadow-sm jo-preview-card">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0">
                                    <span class="fas fa-eye me-2 text-primary"></span>
                                    Live Preview
                                </h5>
                            </div>

                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div>
                                        <div class="fs-11 text-600">Initial Status</div>
                                        <span class="badge rounded-pill badge-subtle-secondary text-secondary">
                                            <span class="fas fa-pause-circle me-1"></span>
                                            Standby
                                        </span>
                                    </div>

                                    <div class="text-end">
                                        <div class="fs-11 text-600">Job Order No.</div>
                                        <div class="fw-semibold" id="preview-job-order-no">
                                            {{ old('job_order_no') ?: 'Auto-generated' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="jo-data-row">
                                    <div>
                                        <div class="jo-data-label">Bus No.</div>
                                    </div>
                                    <div class="jo-data-value" id="preview-bus-no">Not selected</div>
                                </div>

                                <div class="jo-data-row">
                                    <div>
                                        <div class="jo-data-label">Plate No.</div>
                                    </div>
                                    <div class="jo-data-value" id="preview-plate-no">—</div>
                                </div>

                                <div class="jo-data-row">
                                    <div>
                                        <div class="jo-data-label">Requester</div>
                                    </div>
                                    <div class="jo-data-value" id="preview-full-name">Not specified</div>
                                </div>

                                <div class="jo-data-row">
                                    <div>
                                        <div class="jo-data-label">Mechanic(s)</div>
                                    </div>
                                    <div class="jo-data-value" id="preview-mechanics">Not assigned</div>
                                </div>

                                <div class="jo-data-row">
                                    <div>
                                        <div class="jo-data-label">Repair Type(s)</div>
                                    </div>
                                    <div class="jo-data-value" id="preview-repair-types">Not selected</div>
                                </div>

                                <div class="jo-data-row">
                                    <div>
                                        <div class="jo-data-label">Odometer</div>
                                    </div>
                                    <div class="jo-data-value" id="preview-odometer">Not encoded</div>
                                </div>

                                <hr>

                                <div class="fs-11 text-600 mb-2">Description of Work</div>
                                <div class="rounded-3 bg-light p-3 jo-live-work" id="preview-description">
                                    No work description encoded yet.
                                </div>

                                <div class="alert alert-subtle-info mt-3 mb-0">
                                    <span class="fas fa-info-circle me-1"></span>
                                    New job orders are saved as <strong>Standby</strong> until the maintenance status is
                                    updated.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const busSelect = document.getElementById('bus_id');
            const odometerInput = document.getElementById('odometer_reading');
            const fullNameInput = document.getElementById('full_name');
            const descriptionInput = document.getElementById('description_of_work');
            const jobOrderNoInput = document.getElementById('job_order_no');

            const selectedBusCard = document.getElementById('selected-bus-card');
            const selectedBusNo = document.getElementById('selected-bus-no');
            const selectedPlateNo = document.getElementById('selected-plate-no');
            const selectedGarage = document.getElementById('selected-garage');
            const selectedOperationalStatus = document.getElementById('selected-operational-status');

            const odometerComparison = document.getElementById('odometer-comparison');

            const previewBusNo = document.getElementById('preview-bus-no');
            const previewPlateNo = document.getElementById('preview-plate-no');
            const previewFullName = document.getElementById('preview-full-name');
            const previewOdometer = document.getElementById('preview-odometer');
            const previewDescription = document.getElementById('preview-description');
            const previewJobOrderNo = document.getElementById('preview-job-order-no');
            const previewMechanics = document.getElementById('preview-mechanics');
            const previewRepairTypes = document.getElementById('preview-repair-types');

            if (window.Choices && busSelect && !busSelect.classList.contains('choices__input')) {
                new Choices(busSelect, {
                    searchEnabled: true,
                    shouldSort: false,
                    itemSelectText: '',
                    placeholder: true,
                    placeholderValue: 'Search bus no., plate no., company, or garage',
                    searchPlaceholderValue: 'Type bus no., plate no., company, or garage'
                });
            }

            function updateJobOrderNoPreview() {
                previewJobOrderNo.textContent = jobOrderNoInput.value.trim() || 'Auto-generated';
            }

            function numberFormat(value) {
                return Number(value).toLocaleString();
            }

            function selectedOption() {
                return busSelect.options[busSelect.selectedIndex];
            }

            function updateBusSection() {
                const selected = selectedOption();

                if (!selected || !selected.value) {
                    selectedBusCard.classList.add('d-none');
                    previewBusNo.textContent = 'Not selected';
                    previewPlateNo.textContent = '—';
                    updateOdometerComparison();
                    return;
                }

                const busNo = selected.dataset.busNo || '—';
                const plateNo = selected.dataset.plateNo || 'No Plate';
                const company = selected.dataset.company || 'No Company';
                const garage = selected.dataset.garage || 'No Garage';
                const operationalStatus = selected.dataset.operationalStatus || 'Unknown';
                const operationalBadge = selected.dataset.operationalBadge ||
                    'badge-subtle-secondary text-secondary';

                selectedBusNo.textContent = busNo;
                selectedPlateNo.textContent = plateNo;
                selectedGarage.textContent = garage;
                selectedOperationalStatus.innerHTML =
                    `<span class="badge rounded-pill ${operationalBadge}">${operationalStatus}</span>`;

                selectedBusCard.classList.remove('d-none');

                previewBusNo.textContent = `${busNo} — ${company}`;
                previewPlateNo.textContent = plateNo;

                updateOdometerComparison();
            }

            function updateRequesterPreview() {
                previewFullName.textContent = fullNameInput.value.trim() || 'Not specified';
            }

            function updateDescriptionPreview() {
                previewDescription.textContent = descriptionInput.value.trim() ||
                    'No work description encoded yet.';
            }

            function updateOdometerComparison() {
                const selected = selectedOption();

                if (!selected || !selected.value) {
                    odometerComparison.className = 'alert alert-subtle-secondary mt-3 mb-0';
                    odometerComparison.textContent = 'Select a bus to preview odometer comparison.';
                    previewOdometer.textContent = odometerInput.value ? `${numberFormat(odometerInput.value)} km` :
                        'Not encoded';
                    return;
                }

                const lastOdometer = selected.dataset.lastOdometer ?
                    Number(selected.dataset.lastOdometer) :
                    null;

                const currentOdometer = odometerInput.value !== '' ?
                    Number(odometerInput.value) :
                    null;

                previewOdometer.textContent = currentOdometer !== null ?
                    `${numberFormat(currentOdometer)} km` :
                    'Not encoded';

                if (currentOdometer === null) {
                    odometerComparison.className = 'alert alert-subtle-info mt-3 mb-0';
                    odometerComparison.textContent =
                        'No current odometer reading encoded. The job order can still be saved.';
                    return;
                }

                if (lastOdometer === null) {
                    odometerComparison.className = 'alert alert-subtle-primary mt-3 mb-0';
                    odometerComparison.textContent =
                        'No previous maintenance odometer reading found. This will become the first reading for this bus.';
                    return;
                }

                const difference = currentOdometer - lastOdometer;

                if (difference < 0) {
                    odometerComparison.className = 'alert alert-subtle-danger mt-3 mb-0';
                    odometerComparison.textContent =
                        `Warning: current reading is lower than the last reading by ${numberFormat(Math.abs(difference))} km.`;
                    return;
                }

                odometerComparison.className = 'alert alert-subtle-success mt-3 mb-0';
                odometerComparison.textContent =
                    `Current reading is ${numberFormat(difference)} km higher than the last maintenance reading.`;
            }

            function updateRepairDetailsPreview() {
                const mechanicNames = Array.from(document.querySelectorAll('input[name="mechanic_names[]"]'))
                    .map(input => input.value.trim())
                    .filter(Boolean);

                const repairTypes = Array.from(document.querySelectorAll('input[name="repair_types[]"]:checked'))
                    .map(input => input.closest('label')?.innerText.trim())
                    .filter(Boolean);

                previewMechanics.textContent = mechanicNames.length ? mechanicNames.join(', ') : 'Not assigned';
                previewRepairTypes.textContent = repairTypes.length ? repairTypes.join(', ') : 'Not selected';
            }

            document.addEventListener('input', function(event) {
                if (event.target.matches('input[name="mechanic_names[]"]')) {
                    updateRepairDetailsPreview();
                }
            });

            document.addEventListener('change', function(event) {
                if (event.target.matches('input[name="repair_types[]"]')) {
                    updateRepairDetailsPreview();
                }
            });

            jobOrderNoInput.addEventListener('input', updateJobOrderNoPreview);
            busSelect.addEventListener('change', updateBusSection);
            odometerInput.addEventListener('input', updateOdometerComparison);
            fullNameInput.addEventListener('input', updateRequesterPreview);
            descriptionInput.addEventListener('input', updateDescriptionPreview);

            updateJobOrderNoPreview();
            updateBusSection();
            updateRequesterPreview();
            updateDescriptionPreview();
            updateOdometerComparison();
            updateRepairDetailsPreview();
        });
    </script>
@endpush
