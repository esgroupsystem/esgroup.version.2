@extends('layouts.app')
@section('title', 'Vehicle Maintenance History')

@section('content')
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

            {{-- HEADER --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <span class="fas fa-bus text-primary me-2"></span>
                            Vehicle Maintenance History
                        </h5>
                        <p class="text-muted fs-10 mb-0 mt-1">
                            View complete maintenance and parts replacement history for this bus
                        </p>
                    </div>

                    <a href="{{ route('buses.index') }}" class="btn btn-falcon-default btn-sm">
                        <span class="fas fa-arrow-left me-1"></span> Back
                    </a>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="border rounded-3 p-3 bg-100 h-100">
                                <div class="text-muted fs-10 mb-1">Plate Number</div>
                                <div class="fw-semi-bold text-900">{{ $busDetail->plate_number ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded-3 p-3 bg-100 h-100">
                                <div class="text-muted fs-10 mb-1">Body Number</div>
                                <div class="fw-semi-bold text-900">{{ $busDetail->body_number ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded-3 p-3 bg-100 h-100">
                                <div class="text-muted fs-10 mb-1">Bus Name</div>
                                <div class="fw-semi-bold text-900">{{ $busDetail->name ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded-3 p-3 bg-100 h-100">
                                <div class="text-muted fs-10 mb-1">Garage</div>
                                <div class="fw-semi-bold text-900">{{ $busDetail->garage ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded-3 p-3 bg-100 h-100">
                                <div class="text-muted fs-10 mb-1">Status</div>
                                <div class="fw-semi-bold text-900">{{ $busDetail->status ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SUMMARY CARDS --}}
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted fs-10">Total Transactions</div>
                            <h3 class="mb-0 text-primary">{{ $totalTransactions }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted fs-10">Total Parts Used</div>
                            <h3 class="mb-0 text-success">{{ $totalPartsUsed }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted fs-10">Latest Maintenance</div>
                            <div class="fw-semi-bold text-900 mt-2">
                                {{ $latestMaintenanceDate ? \Carbon\Carbon::parse($latestMaintenanceDate)->format('F d, Y') : 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-muted fs-10">Most Used Part</div>
                            <div class="fw-semi-bold text-900 mt-2">
                                {{ $mostUsedPart?->product?->product_name ?? 'N/A' }}
                            </div>
                            @if ($mostUsedPart)
                                <small class="text-muted">
                                    Qty Used: {{ (int) $mostUsedPart->total_used }}
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- HISTORY TABLE --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="mb-0">Maintenance Records</h5>
                        <p class="text-muted fs-10 mb-0 mt-1">
                            All repair, replacement, and issued parts history for this vehicle
                        </p>
                    </div>

                    <form method="GET" class="d-flex" style="max-width: 320px; width: 100%;">
                        <input type="text" name="search" value="{{ $search }}"
                            class="form-control form-control-sm" placeholder="Search part, mechanic, JO no...">
                    </form>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive scrollbar">
                        <table class="table table-hover table-striped fs-10 mb-0">
                            <thead class="bg-200 text-900">
                                <tr>
                                    <th style="min-width: 110px;">Date</th>
                                    <th style="min-width: 130px;">Reference No.</th>
                                    <th style="min-width: 130px;">Mechanic</th>
                                    <th style="min-width: 120px;">Requested By</th>
                                    <th style="min-width: 120px;">Job Order No.</th>
                                    <th style="min-width: 100px;">Odometer</th>
                                    <th style="min-width: 200px;">Purpose / Work Details</th>
                                    <th style="min-width: 260px;">Parts Used</th>
                                    <th style="min-width: 180px;">Remarks</th>
                                    <th style="min-width: 100px;">Created By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($partsOuts as $record)
                                    <tr>
                                        <td>
                                            {{ $record->issued_date ? \Carbon\Carbon::parse($record->issued_date)->format('M d, Y') : 'N/A' }}
                                        </td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info">
                                                {{ $record->parts_out_number ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>{{ $record->mechanic_name ?? 'N/A' }}</td>
                                        <td>{{ $record->requested_by ?? 'N/A' }}</td>
                                        <td>{{ $record->job_order_no ?? 'N/A' }}</td>
                                        <td>{{ $record->odometer ?? 'N/A' }}</td>
                                        <td>
                                            <div style="max-width: 240px; white-space: normal;">
                                                {{ $record->purpose ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td>
                                            @forelse ($record->items as $item)
                                                <div class="mb-2 p-2 border rounded-2 bg-100">
                                                    <div class="fw-semi-bold text-900">
                                                        {{ $item->product->product_name ?? 'N/A' }}
                                                    </div>
                                                    <div class="text-muted">
                                                        Qty: {{ $item->qty_used ?? 0 }}
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
                                            <div style="max-width: 200px; white-space: normal;">
                                                {{ $record->remarks ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td>{{ $record->creator->name ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4 text-muted">
                                            No maintenance history found for this vehicle.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($partsOuts->hasPages())
                    <div class="card-footer bg-light">
                        {{ $partsOuts->links('pagination.custom') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
