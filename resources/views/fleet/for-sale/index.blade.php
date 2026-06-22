@extends('layouts.app')

@section('content')
    <style>
        .fleet-page-title {
            font-weight: 700;
            color: #344050;
        }

        .fleet-card {
            border: 1px solid #edf2f9;
            border-radius: 1rem;
            box-shadow: 0 .35rem 1rem rgba(15, 34, 58, .06);
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

        .fleet-stat-icon {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: .85rem;
            font-size: 1.1rem;
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

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h3 class="fleet-page-title mb-1">For Sale Units Monitoring</h3>
                <p class="text-muted mb-0">
                    Database version of your Excel For Sale sheet with add, update, and delete.
                </p>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('fleet.buses.index') }}" class="btn btn-falcon-default">
                    <span class="fas fa-chart-line me-1"></span>
                    Dashboard
                </a>

                <a href="{{ route('fleet.for-sale-units.create') }}" class="btn btn-primary">
                    <span class="fas fa-plus me-1"></span>
                    Add Unit
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                <span class="fas fa-check-circle me-1"></span>
                {{ session('success') }}
            </div>
        @endif

        <div class="row g-3 mb-3">
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card fleet-card">
                    <div class="card-body d-flex justify-content-between">
                        <div>
                            <small class="text-muted fw-semibold">TOTAL FOR SALE</small>
                            <h3 class="mb-0">{{ number_format($summary['total']) }}</h3>
                        </div>
                        <div class="fleet-stat-icon bg-primary-subtle text-primary">
                            <span class="fas fa-bus"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card fleet-card">
                    <div class="card-body d-flex justify-content-between">
                        <div>
                            <small class="text-muted fw-semibold">RUNNING</small>
                            <h3 class="mb-0 text-success">{{ number_format($summary['running_condition']) }}</h3>
                        </div>
                        <div class="fleet-stat-icon bg-success-subtle text-success">
                            <span class="fas fa-check"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card fleet-card">
                    <div class="card-body d-flex justify-content-between">
                        <div>
                            <small class="text-muted fw-semibold">MECHANICAL</small>
                            <h3 class="mb-0 text-warning">{{ number_format($summary['mechanical_breakdown']) }}</h3>
                        </div>
                        <div class="fleet-stat-icon bg-warning-subtle text-warning">
                            <span class="fas fa-tools"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card fleet-card">
                    <div class="card-body d-flex justify-content-between">
                        <div>
                            <small class="text-muted fw-semibold">ACCIDENT</small>
                            <h3 class="mb-0 text-danger">{{ number_format($summary['accident_related']) }}</h3>
                        </div>
                        <div class="fleet-stat-icon bg-danger-subtle text-danger">
                            <span class="fas fa-car-crash"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card fleet-card">
                    <div class="card-body d-flex justify-content-between">
                        <div>
                            <small class="text-muted fw-semibold">ON HOLD</small>
                            <h3 class="mb-0 text-info">{{ number_format($summary['on_hold']) }}</h3>
                        </div>
                        <div class="fleet-stat-icon bg-info-subtle text-info">
                            <span class="fas fa-pause-circle"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="card fleet-card">
                    <div class="card-body d-flex justify-content-between">
                        <div>
                            <small class="text-muted fw-semibold">BREAKDOWN TOTAL</small>
                            <h3 class="mb-0 text-danger">{{ number_format($summary['breakdown_total']) }}</h3>
                        </div>
                        <div class="fleet-stat-icon bg-danger-subtle text-danger">
                            <span class="fas fa-exclamation-triangle"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card fleet-card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('fleet.for-sale-units.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label fw-semibold">Search</label>
                            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                                class="form-control" placeholder="Bus no, plate, location, progress, remarks...">
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
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                @foreach ($status_options as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-6 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                Filter
                            </button>

                            <a href="{{ route('fleet.for-sale-units.index') }}" class="btn btn-falcon-default">
                                Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card fleet-card">
            <div class="card-header bg-body-tertiary">
                <h5 class="mb-0">For Sale Unit List</h5>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 fleet-table">
                        <thead>
                            <tr>
                                <th>Bus Number</th>
                                <th>Plate Number</th>
                                <th>Company</th>
                                <th>Garage</th>
                                <th>Status</th>
                                <th>Storage Area</th>
                                <th>Breakdown Start</th>
                                <th>Breakdown End</th>
                                <th>Column 11</th>
                                <th class="text-end">Days</th>
                                <th>Unit Location</th>
                                <th>Progress</th>
                                <th>Remarks</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($records as $record)
                                <tr>
                                    <td class="fw-bold">{{ $record->bus_no }}</td>
                                    <td>{{ $record->plate_no ?? '—' }}</td>
                                    <td>{{ $record->company ?? '—' }}</td>
                                    <td>{{ $record->garage ?? '—' }}</td>
                                    <td>
                                        <span class="badge badge-soft {{ $record->status_badge_class }}">
                                            {{ $record->status_label }}
                                        </span>
                                    </td>
                                    <td>{{ $record->storage_area ?? '—' }}</td>
                                    <td>{{ $record->breakdown_start_date?->format('M d, Y') ?? '—' }}</td>
                                    <td>{{ $record->breakdown_end_date?->format('M d, Y') ?? '—' }}</td>
                                    <td>{{ $record->column_11 ?? '—' }}</td>
                                    <td class="text-end fw-bold">
                                        {{ number_format($record->live_days_in_breakdown) }}
                                    </td>
                                    <td>{{ $record->unit_location ?? '—' }}</td>
                                    <td>{{ $record->progress ?? '—' }}</td>
                                    <td class="text-muted">{{ $record->remarks ?? '—' }}</td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="{{ route('fleet.for-sale-units.edit', $record) }}"
                                                class="btn btn-sm btn-falcon-default">
                                                Edit
                                            </a>

                                            <form method="POST"
                                                action="{{ route('fleet.for-sale-units.destroy', $record) }}"
                                                onsubmit="return confirm('Delete this for sale unit?')">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="14" class="text-center py-5 text-muted">
                                        No for sale units found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($records->hasPages())
                <div class="card-footer bg-white">
                    {{ $records->links('pagination.custom') }}
                </div>
            @endif
        </div>
    </div>
@endsection
