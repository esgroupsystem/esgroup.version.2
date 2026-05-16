@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm">
                <span class="fas fa-check-circle me-2"></span>
                {{ session('success') }}
            </div>
        @endif

        {{-- FILTER CARD --}}
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-1 text-900">
                            <span class="fas fa-gas-pump me-2 text-primary"></span>
                            Diesel Stock and Odometer Monitoring
                        </h5>
                        <small class="text-muted">
                            Monitor diesel stock, consumption, odometer, KM run, and KM/L.
                        </small>
                    </div>

                    <div class="col-auto">
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addDieselStockModal">
                            <span class="fas fa-plus me-1"></span>
                            Add Diesel Stock
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">

                    <div class="col-md-2">
                        <label class="form-label">Filter Type</label>
                        <select name="filter_type" id="filterType" class="form-select">
                            <option value="month" {{ $filterType === 'month' ? 'selected' : '' }}>Monthly</option>
                            <option value="day" {{ $filterType === 'day' ? 'selected' : '' }}>Per Day</option>
                            <option value="range" {{ $filterType === 'range' ? 'selected' : '' }}>Date Range</option>
                        </select>
                    </div>

                    <div class="col-md-2 filter-month">
                        <label class="form-label">Month</label>
                        <input type="month" name="month" value="{{ $month }}" class="form-control">
                    </div>

                    <div class="col-md-2 filter-day d-none">
                        <label class="form-label">Select Day</label>
                        <input type="date" name="date" value="{{ $selectedDate }}" class="form-control">
                    </div>

                    <div class="col-md-2 filter-range d-none">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                    </div>

                    <div class="col-md-2 filter-range d-none">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Bus Unit</label>
                        <select name="bus_detail_id" id="busDetailSelect" class="form-select">
                            <option value="">All / Select Bus Unit</option>
                            @foreach ($buses as $bus)
                                <option value="{{ $bus->id }}"
                                    data-custom-properties="{{ $bus->body_number }} {{ $bus->plate_number }} {{ $bus->name }} {{ $bus->garage }}"
                                    {{ (string) $busId === (string) $bus->id ? 'selected' : '' }}>
                                    {{ $bus->body_number }} - {{ $bus->name }} - {{ $bus->garage }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Last Change Oil KM</label>
                        <input type="number" name="last_change_oil" value="{{ $lastChangeOilKm ?? '' }}"
                            class="form-control" placeholder="Example: 250000">
                    </div>

                    <div class="col-md-1">
                        <button class="btn btn-primary w-100">
                            <span class="fas fa-search"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MAIN DIESEL STOCK SUMMARY --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 text-900">
                    <span class="fas fa-warehouse me-2 text-primary"></span>
                    Diesel Stock Inventory Summary
                </h6>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100 bg-light">
                            <p class="text-muted mb-1 fs--1">Current Diesel Stock</p>
                            <h4 class="mb-0 text-primary">{{ number_format($currentDieselStock, 2) }}</h4>
                            <small class="text-muted">Liters remaining</small>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <p class="text-muted mb-1 fs--1">Diesel IN</p>
                            <h4 class="mb-0 text-success">{{ number_format($periodDieselIn, 2) }}</h4>
                            <small class="text-muted">{{ $periodLabel }}</small>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <p class="text-muted mb-1 fs--1">Diesel OUT / Consumed</p>
                            <h4 class="mb-0 text-danger">{{ number_format($periodDieselOut, 2) }}</h4>
                            <small class="text-muted">{{ $periodLabel }}</small>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <p class="text-muted mb-1 fs--1">Adjustment</p>
                            <h4 class="mb-0 text-warning">{{ number_format($periodDieselAdjustment, 2) }}</h4>
                            <small class="text-muted">{{ $periodLabel }}</small>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- SELECTED BUS CARD --}}
        @if ($busId)
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <h5 class="mb-1 text-900">
                                <span class="fas fa-bus me-2 text-primary"></span>
                                BUS #{{ $selectedBus->body_number ?? 'N/A' }}
                            </h5>
                            <small class="text-muted">
                                {{ $selectedBus->name ?? 'N/A' }} |
                                Plate: {{ $selectedBus->plate_number ?? 'N/A' }} |
                                Garage: {{ $selectedBus->garage ?? 'N/A' }}
                            </small>
                        </div>

                        <div class="col-md-5 text-md-end mt-2 mt-md-0">
                            <span class="badge rounded-pill bg-light text-dark border">
                                {{ $periodLabel }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ODOMETER COMPUTATION --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 text-900">
                    <span class="fas fa-calculator me-2 text-primary"></span>
                    Odometer Consumption Summary
                </h6>
            </div>

            <div class="card-body">
                <div class="row g-3">

                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <p class="text-muted mb-1 fs--1">Selected Period</p>
                            <h5 class="mb-0 text-900">{{ $periodLabel }}</h5>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <p class="text-muted mb-1 fs--1">Total KM Run</p>
                            <h5 class="mb-0 text-900">{{ number_format($totalKm) }}</h5>
                            <small class="text-muted">Kilometers</small>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <p class="text-muted mb-1 fs--1">Total Diesel Used</p>
                            <h5 class="mb-0 text-900">{{ number_format($totalLiters, 2) }}</h5>
                            <small class="text-muted">Liters</small>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100">
                            <p class="text-muted mb-1 fs--1">Average Consumption</p>
                            <h5 class="mb-0 text-900">{{ number_format($averageKmPerLiter, 2) }}</h5>
                            <small class="text-muted">KM/L</small>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- DIESEL STOCK MOVEMENT DETAILS --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 text-900">
                    <span class="fas fa-exchange-alt me-2 text-primary"></span>
                    Diesel Stock Movement Details
                </h6>
            </div>

            @if ($dieselStockMovements->isEmpty())
                <div class="card-body text-center py-5">
                    <span class="fas fa-folder-open fa-3x text-300 mb-3"></span>
                    <h5 class="text-700">No diesel stock movement found</h5>
                    <p class="text-muted mb-0">No diesel inventory records for {{ $periodLabel }}.</p>
                </div>
            @else
                <div class="card-body p-0">
                    <div class="table-responsive scrollbar">
                        <table class="table table-sm table-hover mb-0 fs--1 align-middle">
                            <thead class="bg-light text-700">
                                <tr>
                                    <th class="ps-3">Date</th>
                                    <th>Type</th>
                                    <th>Reference</th>
                                    <th>Bus</th>
                                    <th class="text-end">Liters</th>
                                    <th class="text-end">Unit Cost</th>
                                    <th class="text-end">Total Cost</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($dieselStockMovements as $stock)
                                    <tr>
                                        <td class="ps-3 text-nowrap">
                                            {{ $stock->date ? $stock->date->format('M d, Y') : '-' }}
                                        </td>

                                        <td>
                                            @if ($stock->type === 'in')
                                                <span class="badge rounded-pill bg-success">IN</span>
                                            @elseif ($stock->type === 'out')
                                                <span class="badge rounded-pill bg-danger">OUT</span>
                                            @else
                                                <span class="badge rounded-pill bg-warning text-dark">ADJUSTMENT</span>
                                            @endif
                                        </td>

                                        <td>{{ $stock->reference_no ?? '-' }}</td>

                                        <td>
                                            @if ($stock->bus)
                                                {{ $stock->bus->body_number }} - {{ $stock->bus->name }}
                                            @else
                                                <span class="text-muted">Stock Only</span>
                                            @endif
                                        </td>

                                        <td class="text-end fw-semi-bold">
                                            {{ number_format($stock->liters, 2) }}
                                        </td>

                                        <td class="text-end">
                                            {{ $stock->unit_cost ? number_format($stock->unit_cost, 2) : '-' }}
                                        </td>

                                        <td class="text-end">
                                            {{ $stock->total_cost ? number_format($stock->total_cost, 2) : '-' }}
                                        </td>

                                        <td>{{ $stock->remarks ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot class="bg-light fw-semi-bold text-900">
                                <tr>
                                    <td colspan="4" class="ps-3">Period Total</td>
                                    <td class="text-end">
                                        IN: {{ number_format($periodDieselIn, 2) }} |
                                        OUT: {{ number_format($periodDieselOut, 2) }}
                                    </td>
                                    <td colspan="3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        {{-- ODOMETER DETAILS --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0 text-900">
                    <span class="fas fa-list me-2 text-primary"></span>
                    Odometer Encoding Details
                </h6>
            </div>

            @if (!$busId)
                <div class="card-body text-center py-5">
                    <span class="fas fa-bus fa-3x text-300 mb-3"></span>
                    <h5 class="text-700">Please select one bus unit</h5>
                    <p class="text-muted mb-0">Select a bus to view odometer details.</p>
                </div>
            @elseif ($records->isEmpty())
                <div class="card-body text-center py-5">
                    <span class="fas fa-folder-open fa-3x text-300 mb-3"></span>
                    <h5 class="text-700">No odometer records found</h5>
                    <p class="text-muted mb-0">No encoded records for this bus in {{ $periodLabel }}.</p>
                </div>
            @else
                <div class="card-body p-0">
                    <div class="table-responsive scrollbar">
                        <table class="table table-sm table-hover mb-0 fs--1 align-middle">
                            <thead class="bg-light text-700">
                                <tr>
                                    <th class="ps-3">Date</th>
                                    <th>Time</th>
                                    <th>Driver</th>
                                    <th class="text-end">Prev Odometer</th>
                                    <th class="text-end">New Odometer</th>
                                    <th class="text-end">KM Run</th>
                                    <th class="text-end">Diesel</th>
                                    <th class="text-center">KM/L</th>
                                    <th class="text-end pe-3">Change Oil Remaining</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($records as $row)
                                    <tr>
                                        <td class="ps-3 text-nowrap">
                                            {{ \Carbon\Carbon::parse($row['date'])->format('M d, Y') }}
                                        </td>

                                        <td class="text-nowrap">
                                            {{ \Carbon\Carbon::parse($row['time'])->format('g:i A') }}
                                        </td>

                                        <td>{{ strtoupper($row['driver_name']) }}</td>

                                        <td class="text-end text-muted">
                                            {{ $row['previous_odometer'] ? number_format($row['previous_odometer']) : '-' }}
                                        </td>

                                        <td class="text-end fw-semi-bold text-900">
                                            {{ number_format($row['new_odometer']) }}
                                        </td>

                                        <td class="text-end">
                                            {{ number_format($row['total_km_run']) }}
                                        </td>

                                        <td class="text-end">
                                            {{ number_format($row['diesel_consumption'], 2) }}
                                        </td>

                                        <td class="text-center">
                                            <span class="badge rounded-pill bg-light text-dark border">
                                                {{ number_format($row['km_per_liter'], 2) }}
                                            </span>
                                        </td>

                                        <td class="text-end pe-3">
                                            @if ($row['remaining_change_oil'] <= 0)
                                                <span class="badge rounded-pill bg-danger">
                                                    Due
                                                </span>
                                            @elseif ($row['remaining_change_oil'] <= 1000)
                                                <span class="badge rounded-pill bg-warning text-dark">
                                                    {{ number_format($row['remaining_change_oil']) }} KM
                                                </span>
                                            @else
                                                <span class="badge rounded-pill bg-light text-dark border">
                                                    {{ number_format($row['remaining_change_oil']) }} KM
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot class="bg-light fw-semi-bold text-900">
                                <tr>
                                    <td colspan="5" class="ps-3">Total</td>
                                    <td class="text-end">{{ number_format($totalKm) }}</td>
                                    <td class="text-end">{{ number_format($totalLiters, 2) }}</td>
                                    <td class="text-center">{{ number_format($averageKmPerLiter, 2) }}</td>
                                    <td class="pe-3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif
        </div>

    </div>

    {{-- ADD DIESEL STOCK MODAL --}}
    <div class="modal fade" id="addDieselStockModal" tabindex="-1" aria-labelledby="addDieselStockModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form method="POST" action="{{ route('odometer.diesel-stock.store') }}" class="modal-content">
                @csrf

                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="addDieselStockModalLabel">
                        <span class="fas fa-gas-pump me-2 text-primary"></span>
                        Add Diesel Stock Record
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-md-4">
                            <label class="form-label">Date</label>
                            <input type="date" name="date" value="{{ now()->toDateString() }}"
                                class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select" required>
                                <option value="in">Diesel IN / Delivery</option>
                                <option value="out">Diesel OUT / Manual Usage</option>
                                <option value="adjustment">Adjustment</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Liters</label>
                            <input type="number" step="0.01" name="liters" class="form-control" placeholder="0.00"
                                required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Unit Cost</label>
                            <input type="number" step="0.01" name="unit_cost" class="form-control"
                                placeholder="Optional">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Reference No.</label>
                            <input type="text" name="reference_no" class="form-control"
                                placeholder="DR / Invoice No.">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Bus Unit</label>
                            <select name="bus_detail_id" class="form-select js-choice"
                                data-options='{"searchEnabled":true,"searchResultLimit":20,"shouldSort":false,"placeholder":true,"itemSelectText":""}'>
                                <option value="">No Bus / Stock Only</option>
                                @foreach ($buses as $bus)
                                    <option value="{{ $bus->id }}">
                                        {{ $bus->body_number }} - {{ $bus->name }} - {{ $bus->garage }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" rows="3" class="form-control" placeholder="Optional notes"></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-falcon-default" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary">
                        <span class="fas fa-save me-1"></span>
                        Save Diesel Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const busSelect = document.getElementById('busDetailSelect');

            if (busSelect) {
                new Choices(busSelect, {
                    searchEnabled: true,
                    searchResultLimit: 50,
                    shouldSort: false,
                    itemSelectText: '',
                    placeholder: true,
                    placeholderValue: 'All / Select Bus Unit',
                    searchFields: ['label', 'customProperties'],
                    fuseOptions: {
                        threshold: 0,
                        distance: 0,
                        ignoreLocation: true,
                        minMatchCharLength: 1
                    }
                });
            }

            const filterType = document.getElementById('filterType');
            const monthFields = document.querySelectorAll('.filter-month');
            const dayFields = document.querySelectorAll('.filter-day');
            const rangeFields = document.querySelectorAll('.filter-range');

            function toggleDateFilters() {
                if (!filterType) return;

                const value = filterType.value;

                monthFields.forEach(el => el.classList.add('d-none'));
                dayFields.forEach(el => el.classList.add('d-none'));
                rangeFields.forEach(el => el.classList.add('d-none'));

                if (value === 'day') {
                    dayFields.forEach(el => el.classList.remove('d-none'));
                } else if (value === 'range') {
                    rangeFields.forEach(el => el.classList.remove('d-none'));
                } else {
                    monthFields.forEach(el => el.classList.remove('d-none'));
                }
            }

            toggleDateFilters();
            filterType?.addEventListener('change', toggleDateFilters);
        });
    </script>
@endpush
