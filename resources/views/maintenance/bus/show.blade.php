@extends('layouts.app')

@section('title', 'Vehicle Maintenance History')

@section('content')
    @php
        $search = $search ?? request('search', '');
    @endphp

    <div class="container" data-layout="container">

        <script>
            var isFluid = JSON.parse(localStorage.getItem('isFluid'));

            if (isFluid) {
                var container = document.querySelector('[data-layout]');
                container.classList.remove('container');
                container.classList.add('container-fluid');
            }
        </script>

        <div class="content">

            {{-- Header --}}
            <div class="card border-0 shadow-sm mb-3 vehicle-history-header">
                <div
                    class="card-header bg-white border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">

                    <div class="d-flex align-items-center">
                        <div class="vehicle-icon me-3">
                            <span class="fas fa-bus"></span>
                        </div>

                        <div>
                            <h5 class="mb-0 text-900">
                                Vehicle Maintenance History
                            </h5>

                            <p class="text-muted fs-10 mb-0 mt-1">
                                Active posted parts-out maintenance records only. Rolled back records are excluded.
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('buses.index') }}" class="btn btn-falcon-default btn-sm">
                        <span class="fas fa-arrow-left me-1"></span>
                        Back
                    </a>

                </div>

                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-3">
                            <div class="vehicle-info-box">
                                <div class="text-muted fs-10 mb-1">Plate Number</div>
                                <div class="fw-semibold text-900">
                                    {{ $busDetail->plate_number ?? 'N/A' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="vehicle-info-box">
                                <div class="text-muted fs-10 mb-1">Body Number</div>
                                <div class="fw-semibold text-900">
                                    {{ $busDetail->body_number ?? 'N/A' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="vehicle-info-box">
                                <div class="text-muted fs-10 mb-1">Bus Name</div>
                                <div class="fw-semibold text-900">
                                    {{ $busDetail->name ?? 'N/A' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="vehicle-info-box">
                                <div class="text-muted fs-10 mb-1">Garage</div>
                                <div class="fw-semibold text-900">
                                    {{ $busDetail->garage ?? 'N/A' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="vehicle-info-box">
                                <div class="text-muted fs-10 mb-1">Status</div>

                                @php
                                    $busStatus = strtolower($busDetail->status ?? '');

                                    $busStatusClass = match ($busStatus) {
                                        'active', 'available' => 'success',
                                        'maintenance', 'repair', 'under repair' => 'warning',
                                        'inactive', 'not active' => 'secondary',
                                        'out of service' => 'danger',
                                        default => 'info',
                                    };
                                @endphp

                                <span
                                    class="badge bg-{{ $busStatusClass }}-subtle text-{{ $busStatusClass }} border border-{{ $busStatusClass }}-subtle">
                                    {{ $busDetail->status ?? 'N/A' }}
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Summary Cards --}}
            <div class="row g-3 mb-3">

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 summary-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="summary-icon bg-primary-subtle text-primary me-3">
                                <span class="fas fa-clipboard-list"></span>
                            </div>

                            <div>
                                <div class="text-muted fs-10 text-uppercase fw-semibold">
                                    Total Transactions
                                </div>

                                <h3 class="mb-0 text-primary">
                                    {{ number_format((int) $totalTransactions) }}
                                </h3>

                                <small class="text-muted">
                                    Posted only
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 summary-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="summary-icon bg-success-subtle text-success me-3">
                                <span class="fas fa-cogs"></span>
                            </div>

                            <div>
                                <div class="text-muted fs-10 text-uppercase fw-semibold">
                                    Total Parts Used
                                </div>

                                <h3 class="mb-0 text-success">
                                    {{ number_format((int) $totalPartsUsed) }}
                                </h3>

                                <small class="text-muted">
                                    Rolled back excluded
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 summary-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="summary-icon bg-info-subtle text-info me-3">
                                <span class="fas fa-calendar-alt"></span>
                            </div>

                            <div>
                                <div class="text-muted fs-10 text-uppercase fw-semibold">
                                    Latest Maintenance
                                </div>

                                <div class="fw-semibold text-900 mt-1">
                                    {{ $latestMaintenanceDate ? \Carbon\Carbon::parse($latestMaintenanceDate)->format('F d, Y') : 'N/A' }}
                                </div>

                                <small class="text-muted">
                                    Latest posted record
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 summary-card">
                        <div class="card-body d-flex align-items-center">
                            <div class="summary-icon bg-warning-subtle text-warning me-3">
                                <span class="fas fa-star"></span>
                            </div>

                            <div>
                                <div class="text-muted fs-10 text-uppercase fw-semibold">
                                    Most Used Part
                                </div>

                                <div class="fw-semibold text-900 mt-1">
                                    {{ $mostUsedPart?->product?->product_name ?? 'N/A' }}
                                </div>

                                @if ($mostUsedPart)
                                    <small class="text-muted">
                                        Qty Used: {{ number_format((int) $mostUsedPart->total_used) }}
                                    </small>
                                @else
                                    <small class="text-muted">
                                        No parts recorded
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- History Table --}}
            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white border-bottom">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

                        <div>
                            <h5 class="mb-0 text-900">
                                <span class="fas fa-history text-primary me-2"></span>
                                Maintenance Records
                            </h5>

                            <p class="text-muted fs-10 mb-0 mt-1">
                                All active posted parts-out records for this vehicle
                            </p>
                        </div>

                        <form method="GET" action="{{ route('buses.show', $busDetail->id) }}"
                            style="max-width: 420px; width: 100%;">
                            <div class="input-group input-group-sm">

                                <span class="input-group-text bg-white">
                                    <span class="fas fa-search text-primary"></span>
                                </span>

                                <input type="text" name="search" value="{{ $search }}" class="form-control"
                                    placeholder="Search part, mechanic, JO no...">

                                @if ($search)
                                    <a href="{{ route('buses.show', $busDetail->id) }}" class="btn btn-outline-secondary">
                                        Clear
                                    </a>
                                @endif

                                <button type="submit" class="btn btn-primary">
                                    Search
                                </button>

                            </div>
                        </form>

                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive scrollbar">

                        <table class="table table-hover table-striped fs-10 mb-0 vehicle-history-table">
                            <thead class="bg-200 text-900">
                                <tr>
                                    <th style="min-width: 110px;">Date</th>
                                    <th style="min-width: 140px;">Reference No.</th>
                                    <th style="min-width: 140px;">Mechanic</th>
                                    <th style="min-width: 130px;">Requested By</th>
                                    <th style="min-width: 130px;">Job Order No.</th>
                                    <th style="min-width: 100px;">Odometer</th>
                                    <th style="min-width: 220px;">Purpose / Work Details</th>
                                    <th style="min-width: 280px;">Parts Used</th>
                                    <th style="min-width: 200px;">Remarks</th>
                                    <th style="min-width: 120px;">Created By</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($partsOuts as $record)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-900">
                                                {{ $record->issued_date ? \Carbon\Carbon::parse($record->issued_date)->format('M d, Y') : 'N/A' }}
                                            </div>
                                        </td>

                                        <td>
                                            <a href="{{ route('parts-out.show', $record->id) }}"
                                                class="badge bg-info-subtle text-info border border-info-subtle text-decoration-none">
                                                {{ $record->parts_out_number ?? 'N/A' }}
                                            </a>
                                        </td>

                                        <td>
                                            {{ $record->mechanic_name ?? 'N/A' }}
                                        </td>

                                        <td>
                                            {{ $record->requested_by ?? 'N/A' }}
                                        </td>

                                        <td>
                                            {{ $record->job_order_no ?? 'N/A' }}
                                        </td>

                                        <td>
                                            {{ $record->odometer ?? 'N/A' }}
                                        </td>

                                        <td>
                                            <div class="text-wrap-cell">
                                                {{ $record->purpose ?? 'N/A' }}
                                            </div>
                                        </td>

                                        <td>
                                            @forelse ($record->items as $item)
                                                <div class="part-used-box mb-2">
                                                    <div class="fw-semibold text-900">
                                                        {{ $item->product->product_name ?? 'N/A' }}
                                                    </div>

                                                    <div class="text-muted">
                                                        Qty:
                                                        <span class="fw-semibold text-dark">
                                                            {{ number_format((int) ($item->qty_used ?? 0)) }}
                                                        </span>
                                                        {{ $item->product->unit ?? '' }}
                                                    </div>

                                                    @if (!empty($item->product->part_number))
                                                        <div class="text-muted">
                                                            Part #: {{ $item->product->part_number }}
                                                        </div>
                                                    @endif

                                                    @if (!empty($item->remarks))
                                                        <div class="text-muted">
                                                            Note: {{ $item->remarks }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @empty
                                                <span class="text-muted">No parts recorded</span>
                                            @endforelse
                                        </td>

                                        <td>
                                            <div class="text-wrap-cell">
                                                {{ $record->remarks ?? 'N/A' }}
                                            </div>
                                        </td>

                                        <td>
                                            {{ $record->creator->full_name ?? ($record->creator->name ?? 'N/A') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5 text-muted">

                                            <span class="fas fa-inbox fa-2x mb-2 d-block text-300"></span>

                                            @if ($search)
                                                No maintenance history found for
                                                <strong>{{ $search }}</strong>.

                                                <div class="mt-3">
                                                    <a href="{{ route('buses.show', $busDetail->id) }}"
                                                        class="btn btn-sm btn-outline-primary">
                                                        Clear Search
                                                    </a>
                                                </div>
                                            @else
                                                No posted maintenance history found for this vehicle.
                                            @endif

                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                    </div>
                </div>

                @if (method_exists($partsOuts, 'hasPages') && $partsOuts->hasPages())
                    <div class="card-footer bg-white border-top">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

                            <div class="text-muted fs-10">
                                Showing
                                {{ $partsOuts->firstItem() }}
                                to
                                {{ $partsOuts->lastItem() }}
                                of
                                {{ $partsOuts->total() }}
                                records
                            </div>

                            <div>
                                {{ $partsOuts->links('pagination.custom') }}
                            </div>

                        </div>
                    </div>
                @endif

            </div>

        </div>
    </div>

    <style>
        .vehicle-history-header {
            border-radius: 14px;
        }

        .vehicle-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: #2c7be5;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 8px 16px rgba(44, 123, 229, 0.18);
        }

        .vehicle-info-box {
            border: 1px solid #edf2f9;
            border-radius: 12px;
            padding: 14px;
            background: #f9fafd;
            height: 100%;
        }

        .summary-card {
            border-radius: 14px;
        }

        .summary-icon {
            width: 42px;
            height: 42px;
            min-width: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .vehicle-history-table thead th {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-weight: 700;
            padding: 12px 14px;
            white-space: nowrap;
        }

        .vehicle-history-table tbody td {
            padding: 12px 14px;
            vertical-align: top;
        }

        .text-wrap-cell {
            max-width: 240px;
            white-space: normal;
            line-height: 1.45;
        }

        .part-used-box {
            padding: 10px;
            border: 1px solid #edf2f9;
            border-radius: 10px;
            background: #f9fafd;
        }

        @media (max-width: 767.98px) {
            .vehicle-icon {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }

            .vehicle-history-table thead th,
            .vehicle-history-table tbody td {
                padding: 10px;
            }
        }
    </style>
@endsection
