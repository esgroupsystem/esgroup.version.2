@once
    <style>
        .for-sale-form-section {
            border: 1px solid rgba(0, 0, 0, .075);
            border-radius: 0.75rem;
            background: #fff;
        }

        .for-sale-section-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(0, 0, 0, .075);
            background: var(--falcon-gray-100, #f9fafd);
            border-radius: 0.75rem 0.75rem 0 0;
        }

        .for-sale-section-body {
            padding: 1.25rem;
        }

        .for-sale-icon-box {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
        }

        .for-sale-readonly {
            background-color: var(--falcon-gray-100, #f9fafd) !important;
            cursor: not-allowed;
        }

        .for-sale-action-bar {
            background: var(--falcon-gray-100, #f9fafd);
            border: 1px solid rgba(0, 0, 0, .075);
            border-radius: 0.75rem;
            padding: 1rem;
        }
    </style>
@endonce

<form method="POST" action="{{ $action }}">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <input type="hidden" name="bus_id" id="bus_id" value="{{ old('bus_id', $forSaleRecord->bus_id) }}">

    <div class="row g-3">

        {{-- UNIT IDENTIFICATION --}}
        <div class="col-12">
            <div class="for-sale-form-section">
                <div class="for-sale-section-header">
                    <div class="d-flex align-items-center gap-2">
                        <div class="for-sale-icon-box bg-primary-subtle text-primary">
                            <span class="fas fa-bus"></span>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-semibold">Unit Identification</h6>
                            <small class="text-muted">
                                Select the exact bus record. This prevents wrong syncing when bus numbers are
                                duplicated.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="for-sale-section-body">
                    <div class="row g-3">

                        <div class="col-lg-4 col-md-6">
                            <label class="form-label fw-semibold">
                                Select Existing Bus
                            </label>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="fas fa-search"></span>
                                </span>

                                <select id="bus_selector" class="form-select @error('bus_id') is-invalid @enderror">
                                    <option value="">Manual Entry / New Bus</option>

                                    @foreach ($buses as $bus)
                                        <option value="{{ $bus->id }}" @selected((int) old('bus_id', $forSaleRecord->bus_id) === (int) $bus->id)>
                                            {{ $bus->bus_no }}
                                            @if ($bus->plate_no)
                                                | {{ $bus->plate_no }}
                                            @endif
                                            @if ($bus->company)
                                                | {{ $bus->company }}
                                            @endif
                                            @if ($bus->garage)
                                                | {{ $bus->garage }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            @error('bus_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <small class="text-muted">
                                Use this when the bus already exists in Bus Monitoring.
                            </small>
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <label class="form-label fw-semibold">
                                Bus Number <span class="text-danger">*</span>
                            </label>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="fas fa-hashtag"></span>
                                </span>

                                <input type="text" name="bus_no" id="bus_no"
                                    value="{{ old('bus_no', $forSaleRecord->bus_no) }}"
                                    class="form-control @error('bus_no') is-invalid @enderror"
                                    placeholder="Example: 4720035">
                            </div>

                            @error('bus_no')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <label class="form-label fw-semibold">Plate Number</label>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="fas fa-id-card"></span>
                                </span>

                                <input type="text" name="plate_no" id="plate_no"
                                    value="{{ old('plate_no', $forSaleRecord->plate_no) }}"
                                    class="form-control for-sale-readonly @error('plate_no') is-invalid @enderror"
                                    placeholder="Example: NFZ 4739" readonly>
                            </div>

                            @error('plate_no')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <label class="form-label fw-semibold">Company</label>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="fas fa-building"></span>
                                </span>

                                <input type="text" name="company" id="company"
                                    value="{{ old('company', $forSaleRecord->company) }}"
                                    class="form-control for-sale-readonly @error('company') is-invalid @enderror"
                                    placeholder="Example: JELL" readonly>
                            </div>

                            @error('company')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <label class="form-label fw-semibold">Garage</label>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="fas fa-warehouse"></span>
                                </span>

                                <input type="text" name="garage" id="garage"
                                    value="{{ old('garage', $forSaleRecord->garage) }}"
                                    class="form-control for-sale-readonly @error('garage') is-invalid @enderror"
                                    placeholder="Example: BALINTAWAK" readonly>
                            </div>

                            @error('garage')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- SALE AND STATUS DETAILS --}}
        <div class="col-12">
            <div class="for-sale-form-section">
                <div class="for-sale-section-header">
                    <div class="d-flex align-items-center gap-2">
                        <div class="for-sale-icon-box bg-success-subtle text-success">
                            <span class="fas fa-tags"></span>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-semibold">Status and Location</h6>
                            <small class="text-muted">
                                Current status, storage area, and unit location monitoring.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="for-sale-section-body">
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold">
                                Status <span class="text-danger">*</span>
                            </label>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="fas fa-signal"></span>
                                </span>

                                <select name="status" class="form-select @error('status') is-invalid @enderror">
                                    @foreach ($status_options as $value => $label)
                                        <option value="{{ $value }}" @selected(old('status', $forSaleRecord->status ?? \App\Models\Bus::STATUS_ACTIVE) === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            @error('status')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold">Storage Area</label>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="fas fa-map-marker-alt"></span>
                                </span>

                                <input type="text" name="storage_area"
                                    value="{{ old('storage_area', $forSaleRecord->storage_area) }}"
                                    class="form-control @error('storage_area') is-invalid @enderror"
                                    placeholder="Storage area">
                            </div>

                            @error('storage_area')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold">Unit Location</label>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="fas fa-location-arrow"></span>
                                </span>

                                <input type="text" name="unit_location"
                                    value="{{ old('unit_location', $forSaleRecord->unit_location) }}"
                                    class="form-control @error('unit_location') is-invalid @enderror"
                                    placeholder="Example: Trouble, HOLD, Unit at CASA">
                            </div>

                            @error('unit_location')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold">Progress</label>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="fas fa-tasks"></span>
                                </span>

                                <input type="text" name="progress"
                                    value="{{ old('progress', $forSaleRecord->progress) }}"
                                    class="form-control @error('progress') is-invalid @enderror"
                                    placeholder="Example: Broken glass">
                            </div>

                            @error('progress')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- BREAKDOWN DETAILS --}}
        <div class="col-12">
            <div class="for-sale-form-section">
                <div class="for-sale-section-header">
                    <div class="d-flex align-items-center gap-2">
                        <div class="for-sale-icon-box bg-warning-subtle text-warning">
                            <span class="fas fa-tools"></span>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-semibold">Breakdown Details</h6>
                            <small class="text-muted">Breakdown date range and computed duration.</small>
                        </div>
                    </div>
                </div>

                <div class="for-sale-section-body">
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold">Breakdown Start Date</label>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="fas fa-calendar-alt"></span>
                                </span>

                                <input type="date" name="breakdown_start_date"
                                    value="{{ old('breakdown_start_date', $forSaleRecord->breakdown_start_date?->format('Y-m-d')) }}"
                                    class="form-control @error('breakdown_start_date') is-invalid @enderror">
                            </div>

                            @error('breakdown_start_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold">Breakdown End Date</label>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="fas fa-calendar-check"></span>
                                </span>

                                <input type="date" name="breakdown_end_date"
                                    value="{{ old('breakdown_end_date', $forSaleRecord->breakdown_end_date?->format('Y-m-d')) }}"
                                    class="form-control @error('breakdown_end_date') is-invalid @enderror">
                            </div>

                            @error('breakdown_end_date')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold">Column 11</label>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="fas fa-table-columns"></span>
                                </span>

                                <input type="text" name="column_11"
                                    value="{{ old('column_11', $forSaleRecord->column_11) }}"
                                    class="form-control @error('column_11') is-invalid @enderror"
                                    placeholder="Column 11 value">
                            </div>

                            @error('column_11')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold">Days in Breakdown</label>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <span class="fas fa-clock"></span>
                                </span>

                                <input type="text"
                                    value="{{ number_format($forSaleRecord->exists ? $forSaleRecord->live_days_in_breakdown : 0) }}"
                                    class="form-control for-sale-readonly fw-semibold" readonly>
                            </div>

                            <small class="text-muted">
                                Auto-computed from start/end date.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- REMARKS --}}
        <div class="col-12">
            <div class="for-sale-form-section">
                <div class="for-sale-section-header">
                    <div class="d-flex align-items-center gap-2">
                        <div class="for-sale-icon-box bg-info-subtle text-info">
                            <span class="fas fa-comment-dots"></span>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-semibold">Remarks and Notes</h6>
                            <small class="text-muted">Add detailed notes, issues, or repair observations.</small>
                        </div>
                    </div>
                </div>

                <div class="for-sale-section-body">
                    <label class="form-label fw-semibold">Remarks</label>

                    <textarea name="remarks" rows="4" class="form-control @error('remarks') is-invalid @enderror"
                        placeholder="Example: #5 left side, clutch disc, waiting parts...">{{ old('remarks', $forSaleRecord->remarks) }}</textarea>

                    @error('remarks')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ACTION BUTTONS --}}
        <div class="col-12">
            <div class="for-sale-action-bar">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="text-muted small">
                        <span class="fas fa-info-circle me-1"></span>
                        Fields marked with <span class="text-danger">*</span> are required.
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('fleet.for-sale-units.index') }}" class="btn btn-falcon-default">
                            <span class="fas fa-times me-1"></span>
                            Cancel
                        </a>

                        <button type="submit" class="btn btn-primary">
                            <span class="fas fa-save me-1"></span>
                            {{ $buttonLabel }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const buses = @json($buses_json);

        const selector = document.getElementById('bus_selector');
        const busIdInput = document.getElementById('bus_id');
        const busNoInput = document.getElementById('bus_no');
        const plateInput = document.getElementById('plate_no');
        const companyInput = document.getElementById('company');
        const garageInput = document.getElementById('garage');

        if (!selector || !busIdInput || !busNoInput) {
            return;
        }

        function fillBusDetails(busId) {
            const bus = buses[String(busId)];

            busIdInput.value = busId || '';

            if (!bus) {
                return;
            }

            busNoInput.value = bus.bus_no || '';

            if (plateInput) {
                plateInput.value = bus.plate_no || '';
            }

            if (companyInput) {
                companyInput.value = bus.company || '';
            }

            if (garageInput) {
                garageInput.value = bus.garage || '';
            }
        }

        selector.addEventListener('change', function() {
            fillBusDetails(this.value);
        });

        busNoInput.addEventListener('input', function() {
            if (selector.value) {
                return;
            }

            busIdInput.value = '';
        });
    });
</script>
