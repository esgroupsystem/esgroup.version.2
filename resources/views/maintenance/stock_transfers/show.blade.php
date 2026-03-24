@extends('layouts.app')
@section('title', 'Stock Transfer Details')

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
            <div class="card border-0 shadow-sm overflow-hidden">
                {{-- Header --}}
                <div class="card-header bg-light border-bottom py-3">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <div class="d-flex align-items-center mb-1">
                                <div class="icon-item icon-item-sm rounded-circle bg-warning-subtle text-warning me-2">
                                    <span class="fas fa-exchange-alt"></span>
                                </div>
                                <h5 class="mb-0">Stock Transfer Details</h5>
                            </div>
                            <p class="text-muted fs-10 mb-0">
                                View full transfer information, item movement, and source to destination details.
                            </p>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('stock-transfers.index') }}" class="btn btn-falcon-default btn-sm">
                                <span class="fas fa-arrow-left me-1"></span> Back
                            </a>
                            <a href="{{ route('stock-transfers.create') }}" class="btn btn-primary btn-sm">
                                <span class="fas fa-plus me-1"></span> New Transfer
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body bg-body-tertiary">
                    {{-- Top Summary --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="text-muted fs-10 text-uppercase mb-1">Transfer Number</div>
                                    <h5 class="mb-0 text-primary">{{ $transfer->transfer_number }}</h5>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="text-muted fs-10 text-uppercase mb-1">Transfer Date</div>
                                    <h6 class="mb-0">
                                        {{ optional($transfer->transfer_date)->format('F d, Y') ?? '—' }}
                                    </h6>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <div class="text-muted fs-10 text-uppercase mb-1">Total Items</div>
                                    <h5 class="mb-0 text-info">
                                        {{ number_format($transfer->items->count()) }}
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Transfer Route --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0">
                                <span class="fas fa-route text-warning me-2"></span>
                                Transfer Route
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center text-center g-3">
                                <div class="col-md-5">
                                    <div class="p-3 rounded-3 border bg-primary-subtle h-100">
                                        <div class="text-muted fs-10 text-uppercase mb-1">From Location</div>
                                        <div class="fw-bold text-primary fs-8">
                                            {{ $transfer->fromLocation->name ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="d-flex justify-content-center align-items-center h-100">
                                        <span class="badge rounded-pill bg-warning text-dark px-3 py-2">
                                            <i class="fas fa-arrow-right me-1"></i> Transfer
                                        </span>
                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <div class="p-3 rounded-3 border bg-warning-subtle h-100">
                                        <div class="text-muted fs-10 text-uppercase mb-1">To Location</div>
                                        <div class="fw-bold text-warning fs-8">
                                            {{ $transfer->toLocation->name ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Information --}}
                    <div class="row g-3 mb-4">
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white border-bottom">
                                    <h6 class="mb-0">
                                        <span class="fas fa-user-check text-primary me-2"></span>
                                        Personnel Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="text-muted fs-10 text-uppercase">Requested By</div>
                                        <div class="fw-semibold">{{ $transfer->requested_by ?? '—' }}</div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="text-muted fs-10 text-uppercase">Received By</div>
                                        <div class="fw-semibold">{{ $transfer->received_by ?? '—' }}</div>
                                    </div>

                                    <div>
                                        <div class="text-muted fs-10 text-uppercase">Created By</div>
                                        <div class="fw-semibold">{{ $transfer->creator->full_name ?? '—' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white border-bottom">
                                    <h6 class="mb-0">
                                        <span class="fas fa-sticky-note text-info me-2"></span>
                                        Remarks / Notes
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @if (!empty($transfer->remarks))
                                        <div class="p-3 rounded-3 bg-light border text-800" style="min-height: 120px;">
                                            {{ $transfer->remarks }}
                                        </div>
                                    @else
                                        <div class="d-flex flex-column align-items-center justify-content-center text-center text-muted py-4"
                                            style="min-height: 120px;">
                                            <span class="fas fa-comment-slash fs-4 mb-2"></span>
                                            <div>No remarks provided for this transfer.</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Items Table --}}
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">
                                    <span class="fas fa-boxes text-success me-2"></span>
                                    Transferred Items
                                </h6>
                                <p class="text-muted fs-10 mb-0 mt-1">
                                    List of all products included in this stock transfer.
                                </p>
                            </div>
                            <span class="badge bg-success-subtle text-success fs-10">
                                {{ number_format($transfer->items->count()) }} item(s)
                            </span>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive scrollbar">
                                <table class="table table-hover table-striped align-middle mb-0">
                                    <thead class="bg-200 text-900">
                                        <tr>
                                            <th width="60" class="text-center">#</th>
                                            <th>Product</th>
                                            <th>Category</th>
                                            <th>Part Number</th>
                                            <th>Unit</th>
                                            <th class="text-center">Transferred Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($transfer->items as $index => $item)
                                            <tr>
                                                <td class="text-center fw-semibold">{{ $index + 1 }}</td>

                                                <td>
                                                    <div class="fw-semibold text-dark">
                                                        {{ $item->product->product_name ?? 'N/A' }}
                                                    </div>
                                                </td>

                                                <td>
                                                    @if (!empty($item->product?->category?->category_name))
                                                        <span class="badge bg-primary-subtle text-primary">
                                                            {{ $item->product->category->category_name }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>

                                                <td>
                                                    <span class="text-800">
                                                        {{ $item->product->part_number ?? '—' }}
                                                    </span>
                                                </td>

                                                <td>
                                                    <span class="badge bg-secondary-subtle text-secondary">
                                                        {{ $item->product->unit ?? '—' }}
                                                    </span>
                                                </td>

                                                <td class="text-center">
                                                    <span
                                                        class="badge rounded-pill bg-info-subtle text-info px-3 py-2 fs-9">
                                                        {{ number_format($item->qty) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6">
                                                    <div class="text-center py-5">
                                                        <div class="mb-3">
                                                            <span class="fas fa-box-open text-400 fs-1"></span>
                                                        </div>
                                                        <h6 class="text-muted mb-1">No transfer items found</h6>
                                                        <p class="text-muted fs-10 mb-0">
                                                            There are no products recorded in this transfer yet.
                                                        </p>
                                                    </div>
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
        </div>
    </div>
@endsection
