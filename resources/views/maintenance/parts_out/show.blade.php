@extends('layouts.app')
@section('title', 'Parts Out Details')

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
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <span class="fas fa-tools text-primary me-2"></span>
                            Parts Out Details
                        </h5>
                        <p class="text-muted fs-10 mb-0 mt-1">
                            View issued / installed parts transaction details
                        </p>
                    </div>

                    <div class="d-flex gap-2">
                        @role('Developer', 'Maintenace Engineer')
                            @if ($partsOut->status === 'posted')
                                <form action="{{ route('parts-out.rollback', $partsOut->id) }}" method="POST"
                                    onsubmit="return confirm('Are you sure you want to rollback this Parts Out? This will return all used quantities back to stock.');">
                                    @csrf
                                    <button type="submit" class="btn btn-falcon-warning btn-sm">
                                        <span class="fas fa-undo me-1"></span> Rollback
                                    </button>
                                </form>
                            @endif
                        @endrole

                        <a href="{{ route('parts-out.index') }}" class="btn btn-falcon-default btn-sm">
                            <span class="fas fa-arrow-left me-1"></span> Back
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="border rounded p-3 bg-light">
                                <small class="text-muted d-block">Parts Out No.</small>
                                <div class="fw-bold text-primary">{{ $partsOut->parts_out_number }}</div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded p-3 bg-light">
                                <small class="text-muted d-block">Date Issued</small>
                                <div class="fw-semibold">
                                    {{ \Carbon\Carbon::parse($partsOut->issued_date)->format('M d, Y') }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded p-3 bg-light">
                                <small class="text-muted d-block">Mechanic</small>
                                <div class="fw-semibold">{{ $partsOut->mechanic_name }}</div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded p-3 bg-light">
                                <small class="text-muted d-block">Status</small>
                                <div>
                                    @if ($partsOut->status === 'posted')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            Posted
                                        </span>
                                    @elseif($partsOut->status === 'cancelled')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            Cancelled
                                        </span>
                                    @elseif($partsOut->status === 'rolled_back')
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                            Rolled Back
                                        </span>
                                    @else
                                        <span
                                            class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                            {{ ucfirst(str_replace('_', ' ', $partsOut->status)) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <small class="text-muted d-block mb-1">Vehicle</small>
                                @if ($partsOut->vehicle)
                                    <div class="fw-semibold">
                                        {{ $partsOut->vehicle->plate_number ?? 'N/A' }}
                                    </div>
                                    <small class="text-muted">
                                        Body No.: {{ $partsOut->vehicle->body_number ?? 'N/A' }}
                                        @if (!empty($partsOut->vehicle->name))
                                            | {{ $partsOut->vehicle->name }}
                                        @endif
                                        @if (!empty($partsOut->vehicle->garage))
                                            | Garage: {{ $partsOut->vehicle->garage }}
                                        @endif
                                    </small>
                                @else
                                    <div class="text-muted">No vehicle selected</div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <small class="text-muted d-block mb-1">Requested By</small>
                                <div class="fw-semibold">{{ $partsOut->requested_by ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <small class="text-muted d-block mb-1">Encoded By</small>
                                <div class="fw-semibold">{{ $partsOut->creator->full_name ?? '—' }}</div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <small class="text-muted d-block mb-1">Job Order No.</small>
                                <div class="fw-semibold">{{ $partsOut->job_order_no ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <small class="text-muted d-block mb-1">Odometer</small>
                                <div class="fw-semibold">{{ $partsOut->odometer ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="border rounded p-3">
                                <small class="text-muted d-block mb-1">Purpose / Work Details</small>
                                <div>{{ $partsOut->purpose ?: '—' }}</div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="border rounded p-3">
                                <small class="text-muted d-block mb-1">Remarks</small>
                                <div>{{ $partsOut->remarks ?: '—' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <span class="fas fa-boxes text-primary me-2"></span>
                        Items Used
                    </h6>
                </div>

                <div class="card-body">
                    <div class="table-responsive scrollbar">
                        <table class="table table-striped table-hover fs-10 mb-0">
                            <thead class="bg-200 text-900">
                                <tr>
                                    <th>Product</th>
                                    <th>Supplier</th>
                                    <th>Unit</th>
                                    <th>Part No.</th>
                                    <th>Qty Used</th>
                                    <th>Stock Before</th>
                                    <th>Stock After</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($partsOut->items as $item)
                                    <tr>
                                        <td class="fw-semibold">{{ $item->product->product_name ?? 'N/A' }}</td>
                                        <td>{{ $item->product->supplier_name ?? '—' }}</td>
                                        <td>{{ $item->product->unit ?? '—' }}</td>
                                        <td>{{ $item->product->part_number ?? '—' }}</td>
                                        <td>{{ $item->qty_used }}</td>
                                        <td>{{ $item->stock_before }}</td>
                                        <td>{{ $item->stock_after }}</td>
                                        <td>{{ $item->remarks ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            No items found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
