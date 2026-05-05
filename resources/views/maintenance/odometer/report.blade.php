@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        {{-- FILTER CARD --}}
        <div class="card mb-3 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-1 text-900">
                    <span class="fas fa-gas-pump me-2 text-primary"></span>
                    Diesel and Odometer Monitoring
                </h5>
                <small class="text-muted">Search one bus unit to view monthly records</small>
            </div>

            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Month</label>
                        <input type="month" name="month" value="{{ $month }}" class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Bus Unit</label>
                        <select name="bus_detail_id" class="form-select js-choice" required
                            data-options='{"searchEnabled":true,"searchResultLimit":20,"shouldSort":false,"placeholder":true,"itemSelectText":""}'>
                            <option value="">Search / Select Bus Unit</option>
                            @foreach ($buses as $bus)
                                <option value="{{ $bus->id }}"
                                    {{ (string) $busId === (string) $bus->id ? 'selected' : '' }}>
                                    {{ $bus->body_number }} - {{ $bus->name }} - {{ $bus->garage }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Last Change Oil Odometer</label>
                        <input type="number" name="last_change_oil" value="{{ $lastChangeOilKm ?? '' }}"
                            class="form-control" placeholder="Example: 250000">
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">
                            <span class="fas fa-search me-1"></span> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if (!$busId)
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <span class="fas fa-bus fa-3x text-300 mb-3"></span>
                    <h5 class="text-700">Please select one bus unit first</h5>
                    <p class="text-muted mb-0">Choose month and bus unit, then click Search.</p>
                </div>
            </div>
        @else
            @php
                $totalLiters = $records->sum('diesel_consumption');
                $totalKm = $records->sum('total_km_run');
                $monthlyAve = $totalLiters > 0 ? $totalKm / $totalLiters : 0;
            @endphp

            {{-- BUS DETAILS CARD --}}
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
                                {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TOTAL / COMPUTATION CARD --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 text-900">
                        <span class="fas fa-calculator me-2 text-primary"></span>
                        Monthly Computation Summary
                    </h6>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="border rounded-3 p-3 h-100">
                                <p class="text-muted mb-1 fs--1">Selected Month</p>
                                <h5 class="mb-0 text-900">
                                    {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}
                                </h5>
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
                                <p class="text-muted mb-1 fs--1">Total Diesel</p>
                                <h5 class="mb-0 text-900">{{ number_format($totalLiters, 2) }}</h5>
                                <small class="text-muted">Liters</small>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded-3 p-3 h-100">
                                <p class="text-muted mb-1 fs--1">Monthly Average</p>
                                <h5 class="mb-0 text-900">{{ number_format($monthlyAve, 2) }}</h5>
                                <small class="text-muted">KM/L</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DETAILS TABLE CARD --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 text-900">
                        <span class="fas fa-list me-2 text-primary"></span>
                        Odometer Encoding Details
                    </h6>
                </div>

                @if ($records->isEmpty())
                    <div class="card-body text-center py-5">
                        <span class="fas fa-folder-open fa-3x text-300 mb-3"></span>
                        <h5 class="text-700">No odometer records found</h5>
                        <p class="text-muted mb-0">
                            No encoded records for this bus in {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}.
                        </p>
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
                                        <th class="text-center pe-3">KM/L</th>
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
                                            <td class="text-end">{{ number_format($row['total_km_run']) }}</td>
                                            <td class="text-end">{{ number_format($row['diesel_consumption'], 2) }}</td>
                                            <td class="text-center pe-3">
                                                <span class="badge rounded-pill bg-light text-dark border">
                                                    {{ number_format($row['km_per_liter'], 2) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>

                                <tfoot class="bg-light fw-semi-bold text-900">
                                    <tr>
                                        <td colspan="5" class="ps-3">
                                            Monthly Total
                                        </td>
                                        <td class="text-end">{{ number_format($totalKm) }}</td>
                                        <td class="text-end">{{ number_format($totalLiters, 2) }}</td>
                                        <td class="text-center pe-3">{{ number_format($monthlyAve, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        @endif

    </div>
@endsection
