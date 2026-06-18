@extends('layouts.app')

@section('title', 'Diesel Stock and Odometer Monitoring')

@section('content')
    @php
        $stockStatusClass = 'success';
        $stockStatusText = 'Good Stock';

        if (($currentDieselStock ?? 0) <= 0) {
            $stockStatusClass = 'danger';
            $stockStatusText = 'No Stock';
        } elseif (($currentDieselStock ?? 0) <= 100) {
            $stockStatusClass = 'warning';
            $stockStatusText = 'Low Stock';
        }

        $averageStatusClass = 'success';
        $averageStatusText = 'Efficient';

        if (($averageKmPerLiter ?? 0) <= 0) {
            $averageStatusClass = 'secondary';
            $averageStatusText = 'No Data';
        } elseif (($averageKmPerLiter ?? 0) < 3) {
            $averageStatusClass = 'danger';
            $averageStatusText = 'High Consumption';
        } elseif (($averageKmPerLiter ?? 0) < 5) {
            $averageStatusClass = 'warning';
            $averageStatusText = 'Monitor';
        }

        $movementCount = $dieselStockMovements->count();
        $odometerCount = $records->count();
    @endphp

    <div class="container-fluid">

        {{-- ALERTS --}}
        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm d-flex align-items-center">
                <span class="fas fa-check-circle me-2"></span>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center">
                <span class="fas fa-exclamation-circle me-2"></span>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        {{-- PAGE HEADER --}}
        <div class="card border-0 shadow-sm mb-3 monitoring-hero">
            <div class="card-body p-4">
                <div class="row align-items-center g-3">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-start">
                            <div class="hero-icon me-3">
                                <span class="fas fa-gas-pump"></span>
                            </div>

                            <div>
                                <h3 class="mb-1 fw-bold text-900">
                                    Diesel Stock & Odometer Monitoring
                                </h3>

                                <p class="text-muted mb-2">
                                    Track diesel inventory, diesel usage, odometer entries, kilometers travelled, and fuel
                                    efficiency.
                                </p>

                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-primary-subtle text-primary">
                                        <span class="fas fa-warehouse me-1"></span>
                                        Stock Monitoring
                                    </span>

                                    <span class="badge bg-success-subtle text-success">
                                        <span class="fas fa-tachometer-alt me-1"></span>
                                        Odometer Tracking
                                    </span>

                                    <span class="badge bg-warning-subtle text-warning">
                                        <span class="fas fa-chart-line me-1"></span>
                                        KM/L Analysis
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="d-flex justify-content-lg-end gap-2 flex-wrap">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#addManualOdometerModal">
                                <span class="fas fa-tachometer-alt me-1"></span>
                                Add Odometer
                            </button>

                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#addDieselStockModal">
                                <span class="fas fa-plus me-1"></span>
                                Add Diesel Stock
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- FILTER CARD --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-1 text-900">
                            <span class="fas fa-filter me-2 text-primary"></span>
                            Report Filters
                        </h5>

                        <small class="text-muted">
                            Select period, bus unit, and last change oil kilometer reading.
                        </small>
                    </div>

                    <span class="badge bg-light text-dark border">
                        {{ $periodLabel }}
                    </span>
                </div>
            </div>

            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">

                    <div class="col-xl-2 col-md-3">
                        <label class="form-label fw-semibold">
                            Filter Type
                        </label>

                        <select name="filter_type" id="filterType" class="form-select">
                            <option value="month" {{ $filterType === 'month' ? 'selected' : '' }}>
                                Monthly
                            </option>

                            <option value="day" {{ $filterType === 'day' ? 'selected' : '' }}>
                                Per Day
                            </option>

                            <option value="range" {{ $filterType === 'range' ? 'selected' : '' }}>
                                Date Range
                            </option>
                        </select>
                    </div>

                    <div class="col-xl-2 col-md-3 filter-month">
                        <label class="form-label fw-semibold">
                            Month
                        </label>

                        <input type="month" name="month" value="{{ $month }}" class="form-control">
                    </div>

                    <div class="col-xl-2 col-md-3 filter-day d-none">
                        <label class="form-label fw-semibold">
                            Select Day
                        </label>

                        <input type="date" name="date" value="{{ $selectedDate }}" class="form-control">
                    </div>

                    <div class="col-xl-2 col-md-3 filter-range d-none">
                        <label class="form-label fw-semibold">
                            Date From
                        </label>

                        <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                    </div>

                    <div class="col-xl-2 col-md-3 filter-range d-none">
                        <label class="form-label fw-semibold">
                            Date To
                        </label>

                        <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <label class="form-label fw-semibold">
                            Bus Unit
                        </label>

                        <select name="bus_detail_id" id="busDetailSelect" class="form-select">
                            <option value="">
                                All / Select Bus Unit
                            </option>

                            @foreach ($buses as $bus)
                                <option value="{{ $bus->id }}"
                                    data-custom-properties="{{ $bus->body_number }} {{ $bus->plate_number }} {{ $bus->name }} {{ $bus->garage }}"
                                    {{ (string) $busId === (string) $bus->id ? 'selected' : '' }}>
                                    {{ $bus->body_number }} - {{ $bus->name }} - {{ $bus->garage }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-2 col-md-3">
                        <label class="form-label fw-semibold">
                            Last Change Oil KM
                        </label>

                        <input type="number" name="last_change_oil" value="{{ $lastChangeOilKm ?? '' }}"
                            class="form-control" placeholder="Example: 250000">
                    </div>

                    <div class="col-xl-1 col-md-2">
                        <button class="btn btn-primary w-100">
                            <span class="fas fa-search me-xl-0 me-1"></span>
                            <span class="d-xl-none">Search</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MAIN SUMMARY --}}
        <div class="row g-3 mb-3">

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm summary-card summary-primary h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 fw-semibold">
                                    Current Diesel Stock
                                </p>

                                <h3 class="mb-0 fw-bold text-primary">
                                    {{ number_format($currentDieselStock, 2) }}
                                </h3>

                                <small class="text-muted">
                                    Liters remaining
                                </small>
                            </div>

                            <div class="summary-icon">
                                <span class="fas fa-warehouse"></span>
                            </div>
                        </div>

                        <div class="mt-3">
                            <span class="badge bg-{{ $stockStatusClass }}-subtle text-{{ $stockStatusClass }}">
                                {{ $stockStatusText }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm summary-card summary-success h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 fw-semibold">
                                    Diesel IN
                                </p>

                                <h3 class="mb-0 fw-bold text-success">
                                    {{ number_format($periodDieselIn, 2) }}
                                </h3>

                                <small class="text-muted">
                                    Liters added during {{ $periodLabel }}
                                </small>
                            </div>

                            <div class="summary-icon">
                                <span class="fas fa-arrow-down"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm summary-card summary-danger h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 fw-semibold">
                                    Diesel OUT / Used
                                </p>

                                <h3 class="mb-0 fw-bold text-danger">
                                    {{ number_format($periodDieselOut, 2) }}
                                </h3>

                                <small class="text-muted">
                                    Liters consumed during {{ $periodLabel }}
                                </small>
                            </div>

                            <div class="summary-icon">
                                <span class="fas fa-arrow-up"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm summary-card summary-warning h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <p class="text-muted mb-1 fw-semibold">
                                    Adjustment
                                </p>

                                <h3 class="mb-0 fw-bold text-warning">
                                    {{ number_format($periodDieselAdjustment, 2) }}
                                </h3>

                                <small class="text-muted">
                                    Manual correction during {{ $periodLabel }}
                                </small>
                            </div>

                            <div class="summary-icon">
                                <span class="fas fa-sliders-h"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- ODOMETER SUMMARY --}}
        <div class="row g-3 mb-3">

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm metric-card h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1 fw-semibold">
                            Selected Period
                        </p>

                        <h5 class="mb-0 text-900">
                            {{ $periodLabel }}
                        </h5>

                        <small class="text-muted">
                            Current report coverage
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm metric-card h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1 fw-semibold">
                            Total KM Run
                        </p>

                        <h5 class="mb-0 text-900">
                            {{ number_format($totalKm) }}
                        </h5>

                        <small class="text-muted">
                            Total kilometers travelled
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm metric-card h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1 fw-semibold">
                            Total Diesel Used
                        </p>

                        <h5 class="mb-0 text-900">
                            {{ number_format($totalLiters, 2) }}
                        </h5>

                        <small class="text-muted">
                            Liters recorded from odometer entries
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm metric-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1 fw-semibold">
                                    Average Consumption
                                </p>

                                <h5 class="mb-0 text-900">
                                    {{ number_format($averageKmPerLiter, 2) }} KM/L
                                </h5>

                                <small class="text-muted">
                                    Kilometer per liter
                                </small>
                            </div>

                            <span
                                class="badge bg-{{ $averageStatusClass }}-subtle text-{{ $averageStatusClass }} align-self-start">
                                {{ $averageStatusText }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- DIESEL STOCK MOVEMENT DETAILS --}}
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-1 text-900">
                            <span class="fas fa-exchange-alt me-2 text-primary"></span>
                            Diesel Stock Movement Details
                        </h5>

                        <small class="text-muted">
                            Detailed list of diesel IN, OUT, and adjustment transactions.
                        </small>
                    </div>

                    <span class="badge bg-light text-dark border">
                        {{ $movementCount }} record(s)
                    </span>
                </div>
            </div>

            @if ($dieselStockMovements->isEmpty())
                <div class="card-body text-center py-5">
                    <div class="empty-icon mb-3">
                        <span class="fas fa-folder-open"></span>
                    </div>

                    <h5 class="text-700">
                        No diesel stock movement found
                    </h5>

                    <p class="text-muted mb-0">
                        No diesel inventory records for {{ $periodLabel }}.
                    </p>
                </div>
            @else
                <div class="card-body p-0">
                    <div class="table-responsive scrollbar">
                        <table class="table table-hover mb-0 align-middle modern-table">
                            <thead>
                                <tr>
                                    <th class="ps-3">Date</th>
                                    <th>Movement</th>
                                    <th>Reference</th>
                                    <th>Bus / Unit</th>
                                    <th class="text-end">Liters</th>
                                    <th class="text-end">Unit Cost</th>
                                    <th class="text-end">Total Cost</th>
                                    <th class="pe-3">Remarks</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($dieselStockMovements as $stock)
                                    <tr>
                                        <td class="ps-3 text-nowrap">
                                            <div class="fw-semibold text-900">
                                                {{ $stock->date ? $stock->date->format('M d, Y') : '-' }}
                                            </div>
                                        </td>

                                        <td>
                                            @if ($stock->type === 'in')
                                                <span class="badge rounded-pill bg-success-subtle text-success">
                                                    <span class="fas fa-arrow-down me-1"></span>
                                                    Diesel IN
                                                </span>
                                            @elseif ($stock->type === 'out')
                                                <span class="badge rounded-pill bg-danger-subtle text-danger">
                                                    <span class="fas fa-arrow-up me-1"></span>
                                                    Diesel OUT
                                                </span>
                                            @else
                                                <span class="badge rounded-pill bg-warning-subtle text-warning">
                                                    <span class="fas fa-sliders-h me-1"></span>
                                                    Adjustment
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            <span class="text-900">
                                                {{ $stock->reference_no ?? '-' }}
                                            </span>
                                        </td>

                                        <td>
                                            @if ($stock->bus)
                                                <div class="fw-semibold text-900">
                                                    {{ $stock->bus->body_number }} - {{ $stock->bus->name }}
                                                </div>

                                                <small class="text-muted">
                                                    Bus consumption record
                                                </small>
                                            @else
                                                <div class="fw-semibold text-900">
                                                    Stock Only
                                                </div>

                                                <small class="text-muted">
                                                    No bus assigned
                                                </small>
                                            @endif
                                        </td>

                                        <td class="text-end fw-bold text-900">
                                            {{ number_format($stock->liters, 2) }}
                                        </td>

                                        <td class="text-end">
                                            {{ $stock->unit_cost ? number_format($stock->unit_cost, 2) : '-' }}
                                        </td>

                                        <td class="text-end fw-semibold">
                                            {{ $stock->total_cost ? number_format($stock->total_cost, 2) : '-' }}
                                        </td>

                                        <td class="pe-3">
                                            <span class="text-muted">
                                                {{ $stock->remarks ?? '-' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="4" class="ps-3 fw-bold">
                                        Period Total
                                    </td>

                                    <td class="text-end fw-bold">
                                        IN: {{ number_format($periodDieselIn, 2) }}
                                        <br>
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
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-1 text-900">
                            <span class="fas fa-list me-2 text-primary"></span>
                            Odometer Encoding Details
                        </h5>

                        <small class="text-muted">
                            Detailed bus odometer records with previous reading, new reading, KM run, diesel used, and KM/L.
                        </small>
                    </div>

                    <span class="badge bg-light text-dark border">
                        {{ $odometerCount }} record(s)
                    </span>
                </div>
            </div>

            @if ($records->isEmpty())
                <div class="card-body text-center py-5">
                    <div class="empty-icon mb-3">
                        <span class="fas fa-folder-open"></span>
                    </div>

                    <h5 class="text-700">
                        No odometer records found
                    </h5>

                    <p class="text-muted mb-0">
                        No encoded odometer records for {{ $periodLabel }}.
                    </p>
                </div>
            @else
                <div class="card-body p-0">
                    <div class="table-responsive scrollbar">
                        <table class="table table-hover mb-0 align-middle modern-table">
                            <thead>
                                <tr>
                                    <th class="ps-3">Date Deployed</th>
                                    <th>Bus Details</th>
                                    <th>Time</th>
                                    <th>Driver</th>
                                    <th class="text-end">Previous ODO</th>
                                    <th class="text-end">New ODO</th>
                                    <th class="text-end">KM Run</th>
                                    <th class="text-end">Diesel</th>
                                    <th class="text-center pe-3">KM/L</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($records as $row)
                                    @php
                                        $kmPerLiterClass = 'success';

                                        if (($row['km_per_liter'] ?? 0) <= 0) {
                                            $kmPerLiterClass = 'secondary';
                                        } elseif (($row['km_per_liter'] ?? 0) < 3) {
                                            $kmPerLiterClass = 'danger';
                                        } elseif (($row['km_per_liter'] ?? 0) < 5) {
                                            $kmPerLiterClass = 'warning';
                                        }
                                    @endphp

                                    <tr>
                                        <td class="ps-3 text-nowrap">
                                            <div class="fw-semibold text-900">
                                                {{ \Carbon\Carbon::parse($row['date'])->format('M d, Y') }}
                                            </div>
                                        </td>

                                        <td>
                                            <div class="fw-bold text-900">
                                                {{ $row['body_number'] ?? 'N/A' }}
                                            </div>

                                            <div class="fw-semibold text-primary">
                                                {{ $row['bus_name'] ?? ($row['name'] ?? 'No Bus Name') }}
                                            </div>

                                            <small class="text-muted">
                                                {{ $row['plate_number'] ?? '-' }} |
                                                {{ $row['garage'] ?? '-' }}
                                            </small>
                                        </td>

                                        <td class="text-nowrap">
                                            {{ \Carbon\Carbon::parse($row['time'])->format('g:i A') }}
                                        </td>

                                        <td>
                                            <span class="fw-semibold">
                                                {{ strtoupper($row['driver_name']) }}
                                            </span>
                                        </td>

                                        <td class="text-end text-muted">
                                            {{ $row['previous_odometer'] ? number_format($row['previous_odometer']) : '-' }}
                                        </td>

                                        <td class="text-end fw-bold text-900">
                                            {{ number_format($row['new_odometer']) }}
                                        </td>

                                        <td class="text-end fw-semibold">
                                            {{ number_format($row['total_km_run']) }}
                                        </td>

                                        <td class="text-end">
                                            {{ number_format($row['diesel_consumption'], 2) }} L
                                        </td>

                                        <td class="text-center pe-3">
                                            <span
                                                class="badge rounded-pill bg-{{ $kmPerLiterClass }}-subtle text-{{ $kmPerLiterClass }}">
                                                {{ number_format($row['km_per_liter'], 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="6" class="ps-3 fw-bold">
                                        Total / Average
                                    </td>

                                    <td class="text-end fw-bold">
                                        {{ number_format($totalKm) }}
                                    </td>

                                    <td class="text-end fw-bold">
                                        {{ number_format($totalLiters, 2) }} L
                                    </td>

                                    <td class="text-center pe-3 fw-bold">
                                        {{ number_format($averageKmPerLiter, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif

            @if ($submissions->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <small class="text-muted">
                            Showing {{ $submissions->firstItem() }} to {{ $submissions->lastItem() }}
                            of {{ $submissions->total() }} records
                        </small>

                        <div>
                            {{ $submissions->links('pagination.custom') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>

    </div>

    {{-- ADD MANUAL ODOMETER MODAL --}}
    <div class="modal fade" id="addManualOdometerModal" tabindex="-1" aria-labelledby="addManualOdometerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form method="POST" action="{{ route('odometer.manual.store') }}" class="modal-content border-0 shadow-lg">
                @csrf

                <input type="hidden" name="manual_odometer_form" value="1">

                <div class="modal-header bg-white border-bottom">
                    <div>
                        <h5 class="modal-title fw-bold" id="addManualOdometerModalLabel">
                            <span class="fas fa-tachometer-alt me-2 text-success"></span>
                            Add Manual Odometer
                        </h5>

                        <small class="text-muted">
                            Encode bus odometer, diesel used, and optional diesel stock deduction.
                        </small>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body bg-light">
                    <div class="alert alert-info border-0 shadow-sm">
                        <div class="d-flex">
                            <span class="fas fa-info-circle me-2 mt-1"></span>

                            <div>
                                <strong>Reminder:</strong>
                                New odometer should not be lower than the previous odometer reading of the selected bus.
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="row g-3">

                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">
                                        Bus Unit <span class="text-danger">*</span>
                                    </label>

                                    <select name="bus_detail_id" id="manualBusDetailSelect" class="form-select" required>
                                        <option value="">
                                            Select Bus Unit
                                        </option>

                                        @foreach ($buses as $bus)
                                            <option value="{{ $bus->id }}"
                                                data-custom-properties="{{ $bus->body_number }} {{ $bus->plate_number }} {{ $bus->name }} {{ $bus->garage }}"
                                                {{ old('bus_detail_id') == $bus->id ? 'selected' : '' }}>
                                                {{ $bus->body_number }} - {{ $bus->name }} - {{ $bus->garage }} -
                                                {{ $bus->plate_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        Date Bus Deployed
                                    </label>

                                    <input type="date" name="date_bus_deployed"
                                        value="{{ old('date_bus_deployed', now()->toDateString()) }}"
                                        class="form-control">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        Odometer Date <span class="text-danger">*</span>
                                    </label>

                                    <input type="date" name="date"
                                        value="{{ old('date', now()->toDateString()) }}" class="form-control" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        Time <span class="text-danger">*</span>
                                    </label>

                                    <input type="time" name="time" value="{{ old('time', now()->format('H:i')) }}"
                                        class="form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        Driver Name
                                    </label>

                                    <input type="text" name="driver_name" value="{{ old('driver_name') }}"
                                        class="form-control" placeholder="Enter driver name">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">
                                        New Odometer <span class="text-danger">*</span>
                                    </label>

                                    <input type="number" name="new_odometer" value="{{ old('new_odometer') }}"
                                        class="form-control" min="0" placeholder="Example: 250000" required>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">
                                        Diesel Used
                                    </label>

                                    <div class="input-group">
                                        <input type="number" step="0.01" name="diesel_consumption"
                                            value="{{ old('diesel_consumption') }}" class="form-control" min="0"
                                            placeholder="0.00">

                                        <span class="input-group-text">
                                            L
                                        </span>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-check border rounded-3 p-3 ps-5 bg-soft-warning">
                                        <input class="form-check-input" type="checkbox" value="1"
                                            name="also_deduct_diesel_stock" id="alsoDeductDieselStock"
                                            {{ old('also_deduct_diesel_stock') ? 'checked' : '' }}>

                                        <label class="form-check-label fw-semibold" for="alsoDeductDieselStock">
                                            Also deduct this diesel consumption from Diesel Stock
                                        </label>

                                        <div class="text-muted fs--1 mt-1">
                                            Check this only if this odometer entry should automatically create a Diesel OUT
                                            record.
                                            Leave unchecked if diesel OUT is already encoded separately.
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top">
                    <button type="button" class="btn btn-falcon-default" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-success">
                        <span class="fas fa-save me-1"></span>
                        Save Odometer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ADD DIESEL STOCK MODAL --}}
    <div class="modal fade" id="addDieselStockModal" tabindex="-1" aria-labelledby="addDieselStockModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form method="POST" action="{{ route('odometer.diesel-stock.store') }}"
                class="modal-content border-0 shadow-lg">
                @csrf

                <div class="modal-header bg-white border-bottom">
                    <div>
                        <h5 class="modal-title fw-bold" id="addDieselStockModalLabel">
                            <span class="fas fa-gas-pump me-2 text-primary"></span>
                            Add Diesel Stock Record
                        </h5>

                        <small class="text-muted">
                            Record diesel delivery, manual usage, or inventory adjustment.
                        </small>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body bg-light">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="row g-3">

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        Date
                                    </label>

                                    <input type="date" name="date" value="{{ now()->toDateString() }}"
                                        class="form-control" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        Type
                                    </label>

                                    <select name="type" class="form-select" required>
                                        <option value="in">
                                            Diesel IN / Delivery
                                        </option>

                                        <option value="out">
                                            Diesel OUT / Manual Usage
                                        </option>

                                        <option value="adjustment">
                                            Adjustment
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        Liters
                                    </label>

                                    <div class="input-group">
                                        <input type="number" step="0.01" name="liters" class="form-control"
                                            placeholder="0.00" required>

                                        <span class="input-group-text">
                                            L
                                        </span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        Unit Cost
                                    </label>

                                    <input type="number" step="0.01" name="unit_cost" class="form-control"
                                        placeholder="Optional">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        Reference No.
                                    </label>

                                    <input type="text" name="reference_no" class="form-control"
                                        placeholder="DR / Invoice No.">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">
                                        Bus Unit
                                    </label>

                                    <select name="bus_detail_id" class="form-select js-choice"
                                        data-options='{"searchEnabled":true,"searchResultLimit":20,"shouldSort":false,"placeholder":true,"itemSelectText":""}'>
                                        <option value="">
                                            No Bus / Stock Only
                                        </option>

                                        @foreach ($buses as $bus)
                                            <option value="{{ $bus->id }}">
                                                {{ $bus->body_number }} - {{ $bus->name }} - {{ $bus->garage }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">
                                        Remarks
                                    </label>

                                    <textarea name="remarks" rows="3" class="form-control" placeholder="Optional notes"></textarea>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-white border-top">
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

@push('styles')
    <style>
        .monitoring-hero {
            background:
                linear-gradient(135deg, rgba(44, 123, 229, .10), rgba(0, 210, 122, .05)),
                #fff;
        }

        .hero-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: rgba(44, 123, 229, .12);
            color: #2c7be5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .summary-card,
        .metric-card {
            transition: .2s ease;
            position: relative;
            overflow: hidden;
        }

        .summary-card:hover,
        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 .75rem 1.5rem rgba(0, 0, 0, .08) !important;
        }

        .summary-card::before {
            content: "";
            position: absolute;
            width: 4px;
            height: 100%;
            left: 0;
            top: 0;
        }

        .summary-primary::before {
            background: #2c7be5;
        }

        .summary-success::before {
            background: #00d27a;
        }

        .summary-danger::before {
            background: #e63757;
        }

        .summary-warning::before {
            background: #f6c343;
        }

        .summary-icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            background: #f6f9fc;
            color: #5e6e82;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .metric-card {
            border-left: 4px solid #edf2f9 !important;
        }

        .modern-table thead th {
            background: #f9fbfd;
            color: #5e6e82;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            border-bottom: 1px solid #edf2f9;
            padding-top: .85rem;
            padding-bottom: .85rem;
        }

        .modern-table tbody td {
            padding-top: .85rem;
            padding-bottom: .85rem;
            border-bottom: 1px solid #edf2f9;
        }

        .modern-table tbody tr:hover {
            background: #f8fafc;
        }

        .modern-table tfoot td {
            background: #f9fbfd;
            color: #344050;
            border-top: 1px solid #edf2f9;
            padding-top: .85rem;
            padding-bottom: .85rem;
        }

        .empty-icon {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            background: #f6f9fc;
            color: #b6c2d2;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
        }

        .bg-primary-subtle {
            background: rgba(44, 123, 229, .12) !important;
        }

        .bg-success-subtle {
            background: rgba(0, 210, 122, .12) !important;
        }

        .bg-danger-subtle {
            background: rgba(230, 55, 87, .12) !important;
        }

        .bg-warning-subtle {
            background: rgba(246, 195, 67, .20) !important;
        }

        .bg-secondary-subtle {
            background: rgba(116, 129, 148, .14) !important;
        }

        .bg-soft-warning {
            background: rgba(246, 195, 67, .12) !important;
        }

        .text-warning {
            color: #b76e00 !important;
        }

        .form-label {
            color: #344050;
        }

        .modal-header .modal-title {
            color: #344050;
        }

        @media (max-width: 768px) {
            .hero-icon {
                width: 46px;
                height: 46px;
                border-radius: 14px;
                font-size: 20px;
            }

            .modern-table {
                min-width: 980px;
            }

            .summary-card h3 {
                font-size: 1.4rem;
            }
        }
    </style>
@endpush

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

            const manualBusSelect = document.getElementById('manualBusDetailSelect');

            if (manualBusSelect) {
                new Choices(manualBusSelect, {
                    searchEnabled: true,
                    searchResultLimit: 50,
                    shouldSort: false,
                    itemSelectText: '',
                    placeholder: true,
                    placeholderValue: 'Select Bus Unit',
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
                if (!filterType) {
                    return;
                }

                const value = filterType.value;

                monthFields.forEach(function(element) {
                    element.classList.add('d-none');
                });

                dayFields.forEach(function(element) {
                    element.classList.add('d-none');
                });

                rangeFields.forEach(function(element) {
                    element.classList.add('d-none');
                });

                if (value === 'day') {
                    dayFields.forEach(function(element) {
                        element.classList.remove('d-none');
                    });
                } else if (value === 'range') {
                    rangeFields.forEach(function(element) {
                        element.classList.remove('d-none');
                    });
                } else {
                    monthFields.forEach(function(element) {
                        element.classList.remove('d-none');
                    });
                }
            }

            toggleDateFilters();

            if (filterType) {
                filterType.addEventListener('change', toggleDateFilters);
            }

            @if ($errors->any() && old('manual_odometer_form'))
                const manualOdometerModal = document.getElementById('addManualOdometerModal');

                if (manualOdometerModal) {
                    new bootstrap.Modal(manualOdometerModal).show();
                }
            @endif
        });
    </script>
@endpush
