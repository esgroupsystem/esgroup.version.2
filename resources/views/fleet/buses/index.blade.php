@extends('layouts.app')

@section('content')
    <style>
        .fleet-hero {
            background: linear-gradient(135deg, #0f4c81 0%, #1b74e4 55%, #3da5ff 100%);
            border: 0;
            overflow: hidden;
            position: relative;
        }

        .fleet-hero::after {
            content: "";
            position: absolute;
            right: -80px;
            top: -80px;
            width: 240px;
            height: 240px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .12);
        }

        .fleet-stat-card {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 .35rem 1rem rgba(15, 34, 58, .08);
            transition: .2s ease-in-out;
        }

        .fleet-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 .5rem 1.25rem rgba(15, 34, 58, .12);
        }

        .fleet-stat-icon {
            width: 44px;
            height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: .85rem;
            font-size: 1.15rem;
        }

        .fleet-table th {
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #5e6e82;
            background: #f9fafd;
            white-space: nowrap;
        }

        .fleet-table td {
            vertical-align: middle;
            white-space: nowrap;
        }

        .fleet-section-title {
            font-weight: 700;
            color: #344050;
        }

        .fleet-muted {
            color: #748194;
        }

        .fleet-garage-card {
            border: 1px solid #edf2f9;
            border-radius: 1rem;
            overflow: hidden;
        }

        .fleet-company-header {
            background: #f9fafd;
            border: 1px solid #edf2f9;
            border-radius: .75rem;
        }

        .fleet-filter-card {
            border: 1px solid #edf2f9;
            border-radius: 1rem;
        }

        .fleet-total-box {
            background: #fff7e6;
            border: 1px solid #ffe6ad;
            border-radius: .75rem;
        }

        .fleet-for-sale-total {
            background: linear-gradient(135deg, #ff8a00 0%, #ff6500 100%);
            color: #fff;
            border-radius: .85rem;
        }

        .badge-soft {
            padding: .45rem .7rem;
            border-radius: 999px;
            font-weight: 600;
        }

        .badge-subtle-success {
            background: #d9f8eb;
        }

        .badge-subtle-warning {
            background: #fff0cc;
        }

        .badge-subtle-danger {
            background: #ffe0e0;
        }

        .badge-subtle-info {
            background: #dff4ff;
        }

        .badge-subtle-secondary {
            background: #edf2f9;
        }
    </style>

    @php
        $forSaleBusIds = collect($for_sale_bus_ids ?? []);
        $forSaleBusNumbers = collect($for_sale_bus_numbers ?? []);

        $isForSaleBus = function ($bus) use ($forSaleBusIds, $forSaleBusNumbers): bool {
            return $forSaleBusIds->contains((int) $bus->id) ||
                $forSaleBusNumbers->contains(strtoupper(trim((string) $bus->bus_no)));
        };

        $netActiveCount = function ($items) use ($isForSaleBus): int {
            return collect($items)
                ->filter(function ($bus) use ($isForSaleBus): bool {
                    return $bus->operational_status === \App\Models\Bus::STATUS_ACTIVE && !$isForSaleBus($bus);
                })
                ->count();
        };

        $forSaleCount = function ($items) use ($isForSaleBus): int {
            return collect($items)->filter(fn($bus): bool => $isForSaleBus($bus))->count();
        };
    @endphp

    <div class="container-fluid">

        {{-- PAGE HEADER --}}
        <div class="card fleet-hero text-white mb-3">
            <div class="card-body position-relative">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-center gap-3">
                            <div class="fleet-stat-icon bg-white bg-opacity-25">
                                <span class="fas fa-bus"></span>
                            </div>
                            <div>
                                <h3 class="mb-1 text-white">Fleet Monitoring Dashboard</h3>
                                <p class="mb-0 text-white-50">
                                    Real-time monitoring summary by garage, company, unit condition, and for-sale status.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <div class="h1 fw-bold mb-0 text-white">{{ number_format($totals['total_units']) }}</div>
                        <div class="text-white-50">Total Registered Units</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- FILTERS --}}
        <div class="card fleet-filter-card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('fleet.buses.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label fw-semibold">Search Unit</label>
                            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control"
                                placeholder="Bus no, plate, chassis, engine...">
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <label class="form-label fw-semibold">Garage</label>
                            <select name="garage" class="form-select">
                                <option value="">All Garages</option>
                                @foreach ($garages as $garage)
                                    <option value="{{ $garage }}" @selected(($filters['garage'] ?? '') === $garage)>
                                        {{ $garage }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <label class="form-label fw-semibold">Company</label>
                            <select name="company" class="form-select">
                                <option value="">All Companies</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company }}" @selected(($filters['company'] ?? '') === $company)>
                                        {{ $company }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <label class="form-label fw-semibold">Condition</label>
                            <select name="operational_status" class="form-select">
                                <option value="">All Conditions</option>
                                @foreach ($operational_status_options as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['operational_status'] ?? '') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-6">
                            <label class="form-label fw-semibold">Sale Status</label>
                            <select name="sale_status" class="form-select">
                                <option value="">All Sale Status</option>
                                @foreach ($sale_status_options as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['sale_status'] ?? '') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-1 col-md-6">
                            <button type="submit" class="btn btn-primary w-100">
                                Filter
                            </button>
                        </div>
                    </div>

                    <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <small class="fleet-muted">
                            Showing <strong>{{ number_format($filtered_count) }}</strong> unit(s) based on current filter.
                        </small>

                        <a href="{{ route('fleet.buses.index') }}" class="btn btn-sm btn-falcon-default">
                            Clear Filter
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- TOP SUMMARY CARDS --}}
        <div class="row g-3 mb-3">
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card fleet-stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="fleet-muted small fw-semibold">TOTAL UNITS</div>
                                <h3 class="mb-0">{{ number_format($totals['total_units']) }}</h3>
                            </div>
                            <div class="fleet-stat-icon bg-primary-subtle text-primary">
                                <span class="fas fa-layer-group"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card fleet-stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="fleet-muted small fw-semibold">ACTIVE NOT FOR SALE</div>
                                <h3 class="mb-0 text-success">{{ number_format($totals['active']) }}</h3>
                            </div>
                            <div class="fleet-stat-icon bg-success-subtle text-success">
                                <span class="fas fa-check-circle"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card fleet-stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="fleet-muted small fw-semibold">MECHANICAL</div>
                                <h3 class="mb-0 text-warning">{{ number_format($totals['mechanical_breakdown']) }}</h3>
                            </div>
                            <div class="fleet-stat-icon bg-warning-subtle text-warning">
                                <span class="fas fa-tools"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card fleet-stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="fleet-muted small fw-semibold">ACCIDENT</div>
                                <h3 class="mb-0 text-danger">{{ number_format($totals['accident_related']) }}</h3>
                            </div>
                            <div class="fleet-stat-icon bg-danger-subtle text-danger">
                                <span class="fas fa-car-crash"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card fleet-stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="fleet-muted small fw-semibold">ON HOLD</div>
                                <h3 class="mb-0 text-info">{{ number_format($totals['on_hold']) }}</h3>
                            </div>
                            <div class="fleet-stat-icon bg-info-subtle text-info">
                                <span class="fas fa-id-card"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card fleet-stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="fleet-muted small fw-semibold">FOR SALE</div>
                                <h3 class="mb-0 text-danger">{{ number_format($totals['for_sale']) }}</h3>
                            </div>
                            <div class="fleet-stat-icon bg-danger-subtle text-danger">
                                <span class="fas fa-tags"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ANALYTICS TABLES --}}
        <div class="row g-3 mb-3">
            {{-- GARAGE SUMMARY --}}
            {{-- GARAGE SUMMARY --}}
            <div class="col-xl-6">
                <div class="card h-100">
                    <div class="card-header bg-body-tertiary">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fleet-section-title">Garage Summary</h5>
                            <span class="badge badge-soft badge-subtle-info text-info">Not For Sale Only</span>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 fleet-table">
                                <thead>
                                    <tr>
                                        <th>Garage</th>
                                        <th class="text-end">Active</th>
                                        <th class="text-end">Mechanical</th>
                                        <th class="text-end">Accident</th>
                                        <th class="text-end">On Hold</th>
                                        <th class="text-end">Not For Sale</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse ($garage_summary as $garage => $data)
                                        <tr>
                                            <td class="fw-bold">{{ $garage }}</td>

                                            <td class="text-end text-success fw-semibold">
                                                {{ number_format($data['active']) }}
                                            </td>

                                            <td class="text-end text-warning fw-semibold">
                                                {{ number_format($data['mechanical_breakdown']) }}
                                            </td>

                                            <td class="text-end text-danger fw-semibold">
                                                {{ number_format($data['accident_related']) }}
                                            </td>

                                            <td class="text-end text-info fw-semibold">
                                                {{ number_format($data['on_hold']) }}
                                            </td>

                                            <td class="text-end fw-bold">
                                                {{ number_format($data['not_for_sale'] ?? $data['total']) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                No garage data available.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>

                                <tfoot>
                                    <tr class="fleet-total-box">
                                        <th>Total</th>

                                        <th class="text-end">
                                            {{ number_format($totals['active']) }}
                                        </th>

                                        <th class="text-end">
                                            {{ number_format($totals['mechanical_breakdown']) }}
                                        </th>

                                        <th class="text-end">
                                            {{ number_format($totals['accident_related']) }}
                                        </th>

                                        <th class="text-end">
                                            {{ number_format($totals['on_hold']) }}
                                        </th>

                                        <th class="text-end">
                                            {{ number_format($totals['not_for_sale']) }}
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- COMPANY SUMMARY --}}
            {{-- COMPANY SUMMARY --}}
            <div class="col-xl-6">
                <div class="card h-100">
                    <div class="card-header bg-body-tertiary">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fleet-section-title">Company Summary</h5>
                            <span class="badge badge-soft badge-subtle-info text-info">Not For Sale Only</span>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 fleet-table">
                                <thead>
                                    <tr>
                                        <th>Company</th>
                                        <th class="text-end">Active</th>
                                        <th class="text-end">Mechanical</th>
                                        <th class="text-end">Accident</th>
                                        <th class="text-end">On Hold</th>
                                        <th class="text-end">Not For Sale</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse ($company_summary as $company => $data)
                                        <tr>
                                            <td class="fw-bold">{{ $company }}</td>

                                            <td class="text-end text-success fw-semibold">
                                                {{ number_format($data['active']) }}
                                            </td>

                                            <td class="text-end text-warning fw-semibold">
                                                {{ number_format($data['mechanical_breakdown']) }}
                                            </td>

                                            <td class="text-end text-danger fw-semibold">
                                                {{ number_format($data['accident_related']) }}
                                            </td>

                                            <td class="text-end text-info fw-semibold">
                                                {{ number_format($data['on_hold']) }}
                                            </td>

                                            <td class="text-end fw-bold">
                                                {{ number_format($data['not_for_sale'] ?? $data['total']) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                No company data available.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>

                                <tfoot>
                                    <tr class="fleet-total-box">
                                        <th>Total</th>

                                        <th class="text-end">
                                            {{ number_format($totals['active']) }}
                                        </th>

                                        <th class="text-end">
                                            {{ number_format($totals['mechanical_breakdown']) }}
                                        </th>

                                        <th class="text-end">
                                            {{ number_format($totals['accident_related']) }}
                                        </th>

                                        <th class="text-end">
                                            {{ number_format($totals['on_hold']) }}
                                        </th>

                                        <th class="text-end">
                                            {{ number_format($totals['not_for_sale']) }}
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- FOR SALE SUMMARY --}}
        @include('fleet.buses.partials.for-sale-monitoring')

        {{-- DETAILED MONITORING LIST --}}
        <div class="card">
            <div class="card-header bg-body-tertiary">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-0 fleet-section-title">Detailed Bus Monitoring List</h5>
                        <small class="fleet-muted">
                            Grouped by garage and company.
                        </small>
                    </div>
                </div>
            </div>

            <div class="card-body">
                @forelse ($grouped_buses as $garage => $companies)
                    @php
                        $garageBuses = $companies->flatten(1);
                    @endphp

                    <div class="fleet-garage-card mb-4">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <h5 class="mb-0">{{ $garage }}</h5>
                                    <small class="fleet-muted">
                                        {{ number_format($garageBuses->count()) }} unit(s)
                                    </small>
                                </div>

                                <div class="d-flex gap-2 flex-wrap">
                                    <span class="badge badge-soft badge-subtle-success text-success">
                                        Active:
                                        {{ $netActiveCount($garageBuses) }}
                                    </span>
                                    <span class="badge badge-soft badge-subtle-warning text-warning">
                                        Mechanical:
                                        {{ $garageBuses->where('operational_status', \App\Models\Bus::STATUS_MECHANICAL_BREAKDOWN)->count() }}
                                    </span>
                                    <span class="badge badge-soft badge-subtle-danger text-danger">
                                        Accident:
                                        {{ $garageBuses->where('operational_status', \App\Models\Bus::STATUS_ACCIDENT_RELATED_BREAKDOWN)->count() }}
                                    </span>
                                    <span class="badge badge-soft badge-subtle-info text-info">
                                        On Hold:
                                        {{ $garageBuses->where('operational_status', \App\Models\Bus::STATUS_ON_HOLD_PLATE_REGISTRATION)->count() }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            @foreach ($companies as $company => $buses)
                                <div class="fleet-company-header p-3 mb-2">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <div>
                                            <h6 class="mb-0">{{ $company }}</h6>
                                            <small class="fleet-muted">
                                                {{ number_format($buses->count()) }} unit(s)
                                            </small>
                                        </div>

                                        <div class="d-flex gap-2 flex-wrap">
                                            <span class="badge badge-soft badge-subtle-success text-success">
                                                Active
                                                {{ $netActiveCount($buses) }}
                                            </span>
                                            <span class="badge badge-soft badge-subtle-danger text-danger">
                                                For Sale
                                                {{ $forSaleCount($buses) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive mb-4">
                                    <table class="table table-sm table-hover align-middle mb-0 fleet-table">
                                        <thead>
                                            <tr>
                                                <th>Bus No.</th>
                                                <th>Plate No.</th>
                                                <th>Company</th>
                                                <th>Garage</th>
                                                <th>Condition</th>
                                                <th>Sale Status</th>
                                                <th>Chassis Number</th>
                                                <th>Engine Number</th>
                                                <th>Case Number</th>
                                                <th>Remarks</th>
                                                @can('fleet.manage.update')
                                                    <th class="text-end">Action</th>
                                                @endcan
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($buses as $bus)
                                                <tr>
                                                    <td class="fw-bold">{{ $bus->bus_no }}</td>
                                                    <td>{{ $bus->plate_no ?? '—' }}</td>
                                                    <td>{{ $bus->company ?? '—' }}</td>
                                                    <td>{{ $bus->garage ?? '—' }}</td>
                                                    <td>
                                                        <span
                                                            class="badge badge-soft {{ $bus->operational_status_badge_class }}">
                                                            {{ $bus->operational_status_label }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span
                                                            class="badge badge-soft {{ $bus->sale_status_badge_class }}">
                                                            {{ $bus->sale_status_label }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $bus->chassis_number ?? '—' }}</td>
                                                    <td>{{ $bus->engine_number ?? '—' }}</td>
                                                    <td>{{ $bus->case_number ?? '—' }}</td>
                                                    <td class="text-muted">
                                                        {{ $bus->monitoring_remarks ?? '—' }}
                                                    </td>

                                                    @can('fleet.manage.update')
                                                        <td class="text-end">
                                                            <a href="{{ route('fleet.buses.edit', array_merge(['bus' => $bus->id], request()->query())) }}"
                                                                class="btn btn-falcon-primary btn-sm">
                                                                <span class="fas fa-pen me-1"></span>
                                                                Update
                                                            </a>
                                                        </td>
                                                    @endcan
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <span class="fas fa-bus fs-1 text-muted"></span>
                        </div>
                        <h5>No buses found</h5>
                        <p class="text-muted mb-0">
                            Try clearing the filter or import your bus master list first.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
