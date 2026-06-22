<form method="POST" action="{{ $action }}">
    @csrf

    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="row g-3">
        <div class="col-lg-3 col-md-6">
            <label class="form-label fw-semibold">Bus Number <span class="text-danger">*</span></label>
            <input type="text" name="bus_no" list="busNumberList" value="{{ old('bus_no', $forSaleRecord->bus_no) }}"
                class="form-control @error('bus_no') is-invalid @enderror" placeholder="Example: 4720035">

            <datalist id="busNumberList">
                @foreach ($buses as $bus)
                    <option value="{{ $bus->bus_no }}">
                        {{ $bus->plate_no }} / {{ $bus->company }} / {{ $bus->garage }}
                    </option>
                @endforeach
            </datalist>

            @error('bus_no')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-3 col-md-6">
            <label class="form-label fw-semibold">Plate Number</label>
            <input type="text" name="plate_no" value="{{ old('plate_no', $forSaleRecord->plate_no) }}"
                class="form-control @error('plate_no') is-invalid @enderror" placeholder="Example: NFZ 4739" readonly>

            @error('plate_no')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-3 col-md-6">
            <label class="form-label fw-semibold">Company</label>
            <input type="text" name="company" value="{{ old('company', $forSaleRecord->company) }}"
                class="form-control @error('company') is-invalid @enderror" placeholder="Example: JELL" readonly>

            @error('company')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-3 col-md-6">
            <label class="form-label fw-semibold">Garage</label>
            <input type="text" name="garage" value="{{ old('garage', $forSaleRecord->garage) }}"
                class="form-control @error('garage') is-invalid @enderror" placeholder="Example: BALINTAWAK" readonly>

            @error('garage')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-3 col-md-6">
            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
            <select name="status" class="form-select @error('status') is-invalid @enderror">
                @foreach ($status_options as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $forSaleRecord->status ?? \App\Models\Bus::STATUS_ACTIVE) === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-3 col-md-6">
            <label class="form-label fw-semibold">Storage Area</label>
            <input type="text" name="storage_area" value="{{ old('storage_area', $forSaleRecord->storage_area) }}"
                class="form-control @error('storage_area') is-invalid @enderror">

            @error('storage_area')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-3 col-md-6">
            <label class="form-label fw-semibold">Breakdown Start Date</label>
            <input type="date" name="breakdown_start_date"
                value="{{ old('breakdown_start_date', $forSaleRecord->breakdown_start_date?->format('Y-m-d')) }}"
                class="form-control @error('breakdown_start_date') is-invalid @enderror">

            @error('breakdown_start_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-3 col-md-6">
            <label class="form-label fw-semibold">Breakdown End Date</label>
            <input type="date" name="breakdown_end_date"
                value="{{ old('breakdown_end_date', $forSaleRecord->breakdown_end_date?->format('Y-m-d')) }}"
                class="form-control @error('breakdown_end_date') is-invalid @enderror">

            @error('breakdown_end_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-3 col-md-6">
            <label class="form-label fw-semibold">Column 11</label>
            <input type="text" name="column_11" value="{{ old('column_11', $forSaleRecord->column_11) }}"
                class="form-control @error('column_11') is-invalid @enderror">

            @error('column_11')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-3 col-md-6">
            <label class="form-label fw-semibold">Days in Breakdown</label>
            <input type="text"
                value="{{ number_format($forSaleRecord->exists ? $forSaleRecord->live_days_in_breakdown : 0) }}"
                class="form-control bg-light" readonly>
            <small class="text-muted">Auto-computed from start/end date.</small>
        </div>

        <div class="col-lg-3 col-md-6">
            <label class="form-label fw-semibold">Unit Location</label>
            <input type="text" name="unit_location"
                value="{{ old('unit_location', $forSaleRecord->unit_location) }}"
                class="form-control @error('unit_location') is-invalid @enderror"
                placeholder="Example: Trouble, HOLD, Unit at CASA">

            @error('unit_location')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-lg-3 col-md-6">
            <label class="form-label fw-semibold">Progress</label>
            <input type="text" name="progress" value="{{ old('progress', $forSaleRecord->progress) }}"
                class="form-control @error('progress') is-invalid @enderror" placeholder="Example: Broken glass">

            @error('progress')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12">
            <label class="form-label fw-semibold">Remarks</label>
            <textarea name="remarks" rows="4" class="form-control @error('remarks') is-invalid @enderror"
                placeholder="Example: #5 left side, clutch disc, waiting parts...">{{ old('remarks', $forSaleRecord->remarks) }}</textarea>

            @error('remarks')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('fleet.for-sale-units.index') }}" class="btn btn-falcon-default">
            Cancel
        </a>

        <button type="submit" class="btn btn-primary">
            <span class="fas fa-save me-1"></span>
            {{ $buttonLabel }}
        </button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const buses = @json($buses_json);

        const busNoInput = document.querySelector('[name="bus_no"]');

        if (!busNoInput) {
            return;
        }

        busNoInput.addEventListener('change', function() {
            const busNo = this.value.trim().toUpperCase();
            const bus = buses[busNo];

            if (!bus) {
                return;
            }

            const plateInput = document.querySelector('[name="plate_no"]');
            const companyInput = document.querySelector('[name="company"]');
            const garageInput = document.querySelector('[name="garage"]');

            if (plateInput && !plateInput.value) {
                plateInput.value = bus.plate_no ?? '';
            }

            if (companyInput && !companyInput.value) {
                companyInput.value = bus.company ?? '';
            }

            if (garageInput && !garageInput.value) {
                garageInput.value = bus.garage ?? '';
            }
        });
    });
</script>
