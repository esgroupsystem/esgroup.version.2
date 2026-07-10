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


        .fleet-action-panel {
            border: 1px solid #edf2f9;
            border-radius: 1.15rem;
            background: linear-gradient(180deg, #ffffff 0%, #f9fbff 100%);
            box-shadow: 0 .35rem 1rem rgba(15, 34, 58, .07);
        }

        .fleet-action-eyebrow {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #2c7be5;
        }

        .fleet-action-card {
            min-height: 112px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem;
            border: 1px solid #e6edf7;
            border-radius: 1rem;
            background: #ffffff;
            color: #344050;
            text-decoration: none;
            transition: .18s ease-in-out;
        }

        .fleet-action-card:hover {
            color: #344050;
            border-color: #bad7ff;
            transform: translateY(-2px);
            box-shadow: 0 .55rem 1.25rem rgba(15, 34, 58, .11);
        }

        .fleet-action-main {
            display: flex;
            align-items: center;
            gap: .85rem;
            min-width: 0;
        }

        .fleet-action-icon {
            width: 48px;
            height: 48px;
            flex: 0 0 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            font-size: 1.15rem;
        }

        .fleet-action-primary .fleet-action-icon {
            background: #e7f0ff;
            color: #2c7be5;
        }

        .fleet-action-success .fleet-action-icon {
            background: #d9f8eb;
            color: #00d27a;
        }

        .fleet-action-danger .fleet-action-icon {
            background: #ffe0e9;
            color: #e63757;
        }

        .fleet-action-muted-card .fleet-action-icon {
            background: #edf2f9;
            color: #5e6e82;
        }

        .fleet-action-title {
            display: block;
            font-weight: 700;
            color: #263442;
            line-height: 1.2;
        }

        .fleet-action-description {
            display: block;
            margin-top: .25rem;
            color: #748194;
            font-size: .78rem;
            line-height: 1.35;
        }

        .fleet-action-button {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            border-radius: 999px;
            padding: .45rem .75rem;
            font-size: .78rem;
            font-weight: 700;
            color: #ffffff;
            background: #2c7be5;
            white-space: nowrap;
        }

        .fleet-action-success .fleet-action-button {
            background: #00a96e;
        }

        .fleet-action-danger .fleet-action-button {
            background: #e63757;
        }

        .fleet-action-muted-card .fleet-action-button {
            color: #344050;
            background: #edf2f9;
        }

        .fleet-folder-panel {
            border: 1px solid #edf2f9;
            border-radius: 1rem;
            overflow: hidden;
        }

        .fleet-folder-tabs {
            gap: .35rem;
            border-bottom: 1px solid #edf2f9;
            background: #f9fafd;
            padding: .75rem .75rem 0;
        }

        .fleet-folder-tabs .nav-link {
            border: 1px solid #d8e2ef;
            border-bottom: 0;
            border-radius: .75rem .75rem 0 0;
            background: #eef3f9;
            color: #5e6e82;
            font-weight: 700;
            padding: .75rem 1.1rem;
            min-width: 150px;
            text-align: left;
        }

        .fleet-folder-tabs .nav-link.active {
            background: #ffffff;
            color: #2c7be5;
            border-color: #edf2f9;
            box-shadow: 0 -.15rem .5rem rgba(15, 34, 58, .05);
        }

        .fleet-folder-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 24px;
            height: 22px;
            padding: 0 .45rem;
            border-radius: 999px;
            background: #d8e2ef;
            color: #344050;
            font-size: .7rem;
            margin-left: .4rem;
        }

        .fleet-folder-tabs .nav-link.active .fleet-folder-count {
            background: #e7f0ff;
            color: #2c7be5;
        }

        .fleet-folder-company-row td {
            background: #f4f8fd;
            color: #344050;
            font-weight: 800;
            border-top: 1px solid #edf2f9;
            border-bottom: 1px solid #edf2f9;
        }

        .fleet-folder-company-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
        }

        .fleet-folder-company-count {
            border-radius: 999px;
            background: #edf2f9;
            color: #5e6e82;
            padding: .25rem .6rem;
            font-size: .7rem;
            font-weight: 700;
        }

        .fleet-folder-empty {
            padding: 4rem 1rem;
            text-align: center;
            color: #748194;
        }

        @media (max-width: 767.98px) {
            .fleet-folder-tabs {
                flex-wrap: nowrap;
                overflow-x: auto;
                padding-bottom: .25rem;
            }

            .fleet-folder-tabs .nav-link {
                min-width: 135px;
            }
        }

        @media (max-width: 575.98px) {
            .fleet-action-card {
                align-items: flex-start;
                flex-direction: column;
            }

            .fleet-action-button {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    @php
        $forSaleBusIds = collect($for_sale_bus_ids ?? []);
        $forSaleBusNumbers = collect($for_sale_bus_numbers ?? []);

        $isForSaleBus = function ($bus) use ($forSaleBusIds, $forSaleBusNumbers): bool {
            return $forSaleBusIds->contains((int) $bus->id) ||
                $forSaleBusNumbers->contains(strtoupper(trim((string) $bus->bus_no)));
        };

        $activeStatusValues = [
            \App\Models\Bus::STATUS_ACTIVE,
            'Active',
            'ACTIVE',
            'active',
            'Running',
            'RUNNING',
            'running',
            'Running Condition',
            'RUNNING CONDITION',
            'running_condition',
        ];

        $netActiveCount = function ($items) use ($isForSaleBus, $activeStatusValues): int {
            return collect($items)
                ->filter(function ($bus) use ($isForSaleBus, $activeStatusValues): bool {
                    return in_array((string) $bus->operational_status, $activeStatusValues, true) &&
                        !$isForSaleBus($bus);
                })
                ->count();
        };

        $forSaleCount = function ($items) use ($isForSaleBus): int {
            return collect($items)->filter(fn($bus): bool => $isForSaleBus($bus))->count();
        };

        $resolveRouteUrl = function (array $routeNames, string $fallback): string {
            foreach ($routeNames as $routeName) {
                if (\Illuminate\Support\Facades\Route::has($routeName)) {
                    return route($routeName, request()->query());
                }
            }

            return $fallback;
        };

        $manageForSaleUrl = $resolveRouteUrl(
            [
                'fleet.bus-for-sale-records.index',
                'fleet.bus-for-sale.index',
                'fleet.for-sale.index',
                'fleet.buses.for-sale.index',
            ],
            '#for-sale-monitoring',
        );

        $addForSaleUrl = $resolveRouteUrl(
            [
                'fleet.bus-for-sale-records.create',
                'fleet.bus-for-sale.create',
                'fleet.for-sale.create',
                'fleet.buses.for-sale.create',
            ],
            '#for-sale-monitoring',
        );
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
                            @if ($grouped_buses instanceof \Illuminate\Pagination\LengthAwarePaginator)
                                Showing
                                <strong>{{ $grouped_buses->firstItem() ?? 0 }}</strong>
                                to
                                <strong>{{ $grouped_buses->lastItem() ?? 0 }}</strong>
                                of
                                <strong>{{ number_format($grouped_buses->total()) }}</strong>
                                unit(s) based on current filter.
                            @else
                                Showing <strong>{{ number_format($filtered_count) }}</strong> unit(s) based on current
                                filter.
                            @endif
                        </small>

                        <a href="{{ route('fleet.buses.index') }}" class="btn btn-sm btn-falcon-default">
                            Clear Filter
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- QUICK ACTIONS --}}
        <div class="card fleet-action-panel mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                    <div>
                        <div class="fleet-action-eyebrow mb-1">Quick Actions</div>
                        <h5 class="mb-1 fleet-section-title">Unit Management Shortcuts</h5>
                        <small class="fleet-muted">
                            Main actions are placed here before the total cards and computation summaries.
                        </small>
                    </div>
                </div>

                <div class="row g-3">
                    @can('fleet.manage.update')
                        <div class="col-xl-4 col-md-6">
                            <a href="{{ route('fleet.buses.create', request()->query()) }}"
                                class="fleet-action-card fleet-action-primary h-100">
                                <span class="fleet-action-main">
                                    <span class="fleet-action-icon">
                                        <span class="fas fa-bus"></span>
                                    </span>
                                    <span>
                                        <span class="fleet-action-title">Add Bus</span>
                                        <span class="fleet-action-description">
                                            Register a new unit in the fleet monitoring master list.
                                        </span>
                                    </span>
                                </span>
                                <span class="fleet-action-button">
                                    Add
                                    <span class="fas fa-arrow-right"></span>
                                </span>
                            </a>
                        </div>

                        <div class="col-xl-4 col-md-6">
                            <a href="{{ $addForSaleUrl }}" class="fleet-action-card fleet-action-danger h-100">
                                <span class="fleet-action-main">
                                    <span class="fleet-action-icon">
                                        <span class="fas fa-tag"></span>
                                    </span>
                                    <span>
                                        <span class="fleet-action-title">Add For Sale Unit</span>
                                        <span class="fleet-action-description">
                                            Add a unit to the for-sale monitoring list and mark its condition.
                                        </span>
                                    </span>
                                </span>
                                <span class="fleet-action-button">
                                    Add Unit
                                    <span class="fas fa-arrow-right"></span>
                                </span>
                            </a>
                        </div>

                        <div class="col-xl-4 col-md-6">
                            <a href="{{ $manageForSaleUrl }}" class="fleet-action-card fleet-action-success h-100">
                                <span class="fleet-action-main">
                                    <span class="fleet-action-icon">
                                        <span class="fas fa-clipboard-list"></span>
                                    </span>
                                    <span>
                                        <span class="fleet-action-title">Manage For Sale</span>
                                        <span class="fleet-action-description">
                                            Review running units, breakdown units, and company totals for sale.
                                        </span>
                                    </span>
                                </span>
                                <span class="fleet-action-button">
                                    Manage
                                    <span class="fas fa-arrow-right"></span>
                                </span>
                            </a>
                        </div>
                    @endcan

                    <div class="col-xl-4 col-md-6">
                        <a href="#for-sale-monitoring" class="fleet-action-card fleet-action-muted-card h-100">
                            <span class="fleet-action-main">
                                <span class="fleet-action-icon">
                                    <span class="fas fa-chart-pie"></span>
                                </span>
                                <span>
                                    <span class="fleet-action-title">View For Sale Computation</span>
                                    <span class="fleet-action-description">
                                        Jump directly to the for-sale breakdown computation table.
                                    </span>
                                </span>
                            </span>
                            <span class="fleet-action-button">
                                View
                                <span class="fas fa-arrow-down"></span>
                            </span>
                        </a>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <a href="{{ route('fleet.buses.index') }}"
                            class="fleet-action-card fleet-action-muted-card h-100">
                            <span class="fleet-action-main">
                                <span class="fleet-action-icon">
                                    <span class="fas fa-filter"></span>
                                </span>
                                <span>
                                    <span class="fleet-action-title">Clear Current Filter</span>
                                    <span class="fleet-action-description">
                                        Reset search, garage, company, condition, and sale status filters.
                                    </span>
                                </span>
                            </span>
                            <span class="fleet-action-button">
                                Reset
                                <span class="fas fa-redo"></span>
                            </span>
                        </a>
                    </div>
                </div>
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
        <div id="for-sale-monitoring">
            @include('fleet.buses.partials.for-sale-monitoring')
        </div>

        {{-- DETAILED MONITORING LIST --}}
        {{-- FOLDER BUS MONITORING LIST --}}
        @php
            $folderTabs = collect($folder_tabs ?? []);

            $isFolderForSaleBus = function ($bus, array $tab): bool {
                $forSaleBusIds = collect($tab['for_sale_bus_ids'] ?? []);
                $forSaleBusNumbers = collect($tab['for_sale_bus_numbers'] ?? []);

                return $forSaleBusIds->contains((int) $bus->id) ||
                    $forSaleBusNumbers->contains(strtoupper(trim((string) $bus->bus_no)));
            };
        @endphp

        <div class="card fleet-folder-panel">
            <div class="card-header bg-body-tertiary">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="mb-0 fleet-section-title">Fleet Folder Monitoring List</h5>
                        <small class="fleet-muted">
                            Click a garage folder to view its bus list. Click For Sale to view all for-sale records.
                        </small>
                    </div>

                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge badge-soft badge-subtle-secondary text-secondary">
                            Bus Master: {{ number_format($folder_total_units ?? 0) }}
                        </span>

                        <span class="badge badge-soft badge-subtle-danger text-danger">
                            For Sale: {{ number_format($folder_total_for_sale ?? 0) }}
                        </span>

                        @can('fleet.manage.update')
                            <a href="{{ route('fleet.buses.create', request()->query()) }}"
                                class="btn btn-falcon-primary btn-sm">
                                <span class="fas fa-plus me-1"></span>
                                Add Bus
                            </a>
                        @endcan
                    </div>
                </div>
            </div>

            <div class="card-header p-0">
                <ul class="nav nav-tabs fleet-folder-tabs" id="fleetFolderTabs" role="tablist">
                    @forelse ($folderTabs as $index => $tab)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $index === 0 ? 'active' : '' }}"
                                id="fleet-folder-tab-{{ $tab['key'] }}" data-bs-toggle="tab"
                                data-bs-target="#fleet-folder-pane-{{ $tab['key'] }}" type="button" role="tab"
                                aria-controls="fleet-folder-pane-{{ $tab['key'] }}"
                                aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                                <span class="fas {{ $tab['type'] === 'for_sale' ? 'fa-tags' : 'fa-folder' }} me-1"></span>
                                {{ $tab['label'] }}
                                <span class="fleet-folder-count">{{ number_format($tab['count']) }}</span>
                            </button>
                        </li>
                    @empty
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" type="button">
                                No Records
                            </button>
                        </li>
                    @endforelse
                </ul>
            </div>

            <div class="card-body p-0">
                <div class="tab-content" id="fleetFolderTabsContent">
                    @forelse ($folderTabs as $index => $tab)
                        <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                            id="fleet-folder-pane-{{ $tab['key'] }}" role="tabpanel"
                            aria-labelledby="fleet-folder-tab-{{ $tab['key'] }}" tabindex="0">

                            @if ($tab['type'] === 'garage')
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 fleet-table">
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
                                            @forelse ($tab['companies'] as $company => $buses)
                                                <tr class="fleet-folder-company-row">
                                                    <td
                                                        colspan="{{ auth()->user()?->can('fleet.manage.update') ? 11 : 10 }}">
                                                        <div class="fleet-folder-company-label">
                                                            <span>
                                                                <span class="fas fa-building me-2 text-primary"></span>
                                                                {{ $company }}
                                                            </span>

                                                            <span class="fleet-folder-company-count">
                                                                {{ number_format($buses->count()) }} unit(s)
                                                            </span>
                                                        </div>
                                                    </td>
                                                </tr>

                                                @foreach ($buses as $bus)
                                                    @php
                                                        $busIsForSale = $isFolderForSaleBus($bus, $tab);
                                                    @endphp

                                                    <tr>
                                                        <td class="fw-bold text-dark">
                                                            {{ $bus->bus_no }}
                                                        </td>

                                                        <td>
                                                            {{ $bus->plate_no ?? '—' }}
                                                        </td>

                                                        <td>
                                                            {{ $bus->company ?? '—' }}
                                                        </td>

                                                        <td>
                                                            {{ $bus->garage ?? '—' }}
                                                        </td>

                                                        <td>
                                                            <span
                                                                class="badge badge-soft {{ $bus->operational_status_badge_class ?? 'badge-subtle-secondary text-secondary' }}">
                                                                {{ $bus->operational_status_label ?? ($bus->operational_status ?? '—') }}
                                                            </span>
                                                        </td>

                                                        <td>
                                                            @if ($busIsForSale)
                                                                <span
                                                                    class="badge badge-soft badge-subtle-danger text-danger">
                                                                    For Sale
                                                                </span>
                                                            @else
                                                                <span
                                                                    class="badge badge-soft {{ $bus->sale_status_badge_class ?? 'badge-subtle-secondary text-secondary' }}">
                                                                    {{ $bus->sale_status_label ?? 'Not For Sale' }}
                                                                </span>
                                                            @endif
                                                        </td>

                                                        <td>
                                                            {{ $bus->chassis_number ?? '—' }}
                                                        </td>

                                                        <td>
                                                            {{ $bus->engine_number ?? '—' }}
                                                        </td>

                                                        <td>
                                                            {{ $bus->case_number ?? '—' }}
                                                        </td>

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
                                            @empty
                                                <tr>
                                                    <td colspan="{{ auth()->user()?->can('fleet.manage.update') ? 11 : 10 }}"
                                                        class="fleet-folder-empty">
                                                        No bus records found for this folder.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            @if ($tab['type'] === 'for_sale')
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 fleet-table">
                                        <thead>
                                            <tr>
                                                <th>Bus No.</th>
                                                <th>Plate No.</th>
                                                <th>Company</th>
                                                <th>Garage</th>
                                                <th>Status</th>
                                                <th>Storage Area</th>
                                                <th>Breakdown Start</th>
                                                <th>Breakdown End</th>
                                                <th class="text-end">Days</th>
                                                <th>Unit Location</th>
                                                <th>Progress</th>
                                                <th>Remarks</th>

                                                @can('fleet.manage.update')
                                                    <th class="text-end">Action</th>
                                                @endcan
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @forelse ($tab['records'] as $company => $records)
                                                <tr class="fleet-folder-company-row">
                                                    <td
                                                        colspan="{{ auth()->user()?->can('fleet.manage.update') ? 13 : 12 }}">
                                                        <div class="fleet-folder-company-label">
                                                            <span>
                                                                <span class="fas fa-building me-2 text-danger"></span>
                                                                {{ $company }}
                                                            </span>

                                                            <span class="fleet-folder-company-count">
                                                                {{ number_format($records->count()) }} unit(s)
                                                            </span>
                                                        </div>
                                                    </td>
                                                </tr>

                                                @foreach ($records as $record)
                                                    <tr>
                                                        <td class="fw-bold text-dark">
                                                            {{ $record->bus_no ?? '—' }}
                                                        </td>

                                                        <td>
                                                            {{ $record->plate_no ?? '—' }}
                                                        </td>

                                                        <td>
                                                            {{ $record->company ?? '—' }}
                                                        </td>

                                                        <td>
                                                            {{ $record->garage ?? '—' }}
                                                        </td>

                                                        <td>
                                                            <span
                                                                class="badge badge-soft {{ $record->status_badge_class ?? 'badge-subtle-secondary text-secondary' }}">
                                                                {{ $record->status_label ?? 'For Sale' }}
                                                            </span>
                                                        </td>

                                                        <td>
                                                            {{ $record->storage_area ?? '—' }}
                                                        </td>

                                                        <td>
                                                            {{ $record->breakdown_start_date ? \Carbon\Carbon::parse($record->breakdown_start_date)->format('M d, Y') : '—' }}
                                                        </td>

                                                        <td>
                                                            {{ $record->breakdown_end_date ? \Carbon\Carbon::parse($record->breakdown_end_date)->format('M d, Y') : '—' }}
                                                        </td>

                                                        <td class="text-end fw-bold">
                                                            {{ number_format($record->live_days_in_breakdown ?? 0) }}
                                                        </td>

                                                        <td>
                                                            {{ $record->unit_location ?? '—' }}
                                                        </td>

                                                        <td>
                                                            {{ $record->progress ?? '—' }}
                                                        </td>

                                                        <td class="text-muted">
                                                            {{ $record->remarks ?? '—' }}
                                                        </td>

                                                        @can('fleet.manage.update')
                                                            <td class="text-end">
                                                                <a href="{{ route('fleet.for-sale-units.edit', $record->id) }}"
                                                                    class="btn btn-falcon-primary btn-sm">
                                                                    <span class="fas fa-pen me-1"></span>
                                                                    Update
                                                                </a>
                                                            </td>
                                                        @endcan
                                                    </tr>
                                                @endforeach
                                            @empty
                                                <tr>
                                                    <td colspan="{{ auth()->user()?->can('fleet.manage.update') ? 13 : 12 }}"
                                                        class="fleet-folder-empty">
                                                        No for-sale records found.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="fleet-folder-empty">
                            No fleet records found.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
