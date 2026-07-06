@extends('layouts.app')

@section('title', 'Receiving Details')

@push('styles')
    <style>
        .detail-hero {
            background:
                linear-gradient(135deg, rgba(var(--falcon-primary-rgb), .12), rgba(var(--falcon-info-rgb), .05)),
                var(--falcon-card-bg);
        }

        .ui-icon {
            width: 34px;
            height: 34px;
            min-width: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            line-height: 1;
        }

        .ui-icon-lg {
            width: 46px;
            height: 46px;
            min-width: 46px;
            font-size: 1.1rem;
        }

        .info-tile {
            height: 100%;
            border: 1px solid var(--falcon-border-color);
            border-radius: .75rem;
            background: var(--falcon-card-bg);
            padding: 1rem;
        }

        .info-label {
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .045em;
            color: var(--falcon-500);
            font-weight: 600;
            margin-bottom: .25rem;
        }

        .info-value {
            color: var(--falcon-900);
            font-weight: 700;
        }

        .rollback-product-cell {
            min-width: 280px;
        }

        .rollback-summary-card {
            border: 1px solid var(--falcon-border-color);
            border-radius: .75rem;
            padding: .85rem;
            background: var(--falcon-body-tertiary-bg);
        }

        .rollback-warning-box {
            border-left: 4px solid var(--falcon-warning);
        }

        .delivered-products-table th {
            white-space: nowrap;
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .045em;
        }

        .delivered-products-table td {
            vertical-align: middle;
        }
    </style>
@endpush

@section('content')
    <div class="container" data-layout="container">
        <script>
            const isFluid = JSON.parse(localStorage.getItem('isFluid'));

            if (isFluid) {
                const container = document.querySelector('[data-layout]');
                container.classList.remove('container');
                container.classList.add('container-fluid');
            }
        </script>

        <div class="content">
            <div class="card border-0 shadow-sm mb-3 detail-hero">
                <div class="card-body p-4">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg">
                            <div class="d-flex align-items-start gap-3">
                                <div class="ui-icon ui-icon-lg bg-primary-subtle text-primary">
                                    <span class="fas fa-truck-loading"></span>
                                </div>

                                <div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <h6 class="mb-0 text-primary fw-semibold">
                                            Receiving Details
                                        </h6>

                                        <span class="badge badge-subtle-primary rounded-pill">
                                            {{ $receiving->receiving_number }}
                                        </span>
                                    </div>

                                    <h3 class="mb-1 fw-bold text-900">
                                        Delivered Products
                                    </h3>

                                    <p class="mb-0 text-600">
                                        View receiving information, delivered items, proof of delivery, and rollback status.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-auto">
                            <a href="{{ route('receivings.index') }}" class="btn btn-falcon-default">
                                <span class="fas fa-arrow-left me-1"></span>
                                Back to Records
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success border-0 shadow-sm">
                    <span class="fas fa-check-circle me-1"></span>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger border-0 shadow-sm">
                    <span class="fas fa-exclamation-triangle me-1"></span>
                    {{ session('error') }}
                </div>
            @endif

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-body-tertiary border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="ui-icon bg-primary-subtle text-primary">
                            <span class="fas fa-info-circle"></span>
                        </div>

                        <div>
                            <h6 class="mb-0 fw-bold text-900">Receiving Information</h6>
                            <p class="mb-0 fs-10 text-600">Main delivery reference and encoder details.</p>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="info-tile">
                                <div class="info-label">Receiving No.</div>
                                <div class="info-value text-primary">
                                    {{ $receiving->receiving_number }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="info-tile">
                                <div class="info-label">Garage / Location</div>
                                <div class="info-value">
                                    {{ $receiving->location->name ?? 'N/A' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="info-tile">
                                <div class="info-label">Delivered By</div>
                                <div class="info-value">
                                    {{ $receiving->delivered_by ?? 'N/A' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="info-tile">
                                <div class="info-label">Delivery Date</div>
                                <div class="info-value">
                                    {{ $receiving->delivery_date ? \Carbon\Carbon::parse($receiving->delivery_date)->format('F d, Y') : 'N/A' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="info-tile">
                                <div class="info-label">Received By</div>
                                <div class="info-value">
                                    {{ optional($receiving->receiver)->full_name ?? (optional($receiving->receiver)->name ?? 'System') }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="info-tile">
                                <div class="info-label">Date Created</div>
                                <div class="info-value">
                                    {{ optional($receiving->created_at)->format('M d, Y') ?? 'N/A' }}
                                </div>
                                <div class="text-500 fs-11">
                                    {{ optional($receiving->created_at)->format('h:i A') ?? '' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-tile">
                                <div class="info-label">Remarks</div>
                                <div class="text-800">
                                    {{ $receiving->remarks ?: 'No remarks provided.' }}
                                </div>
                            </div>
                        </div>

                        @if ($receiving->proof_image)
                            <div class="col-12">
                                <div class="border rounded-3 overflow-hidden">
                                    <button type="button"
                                        class="btn w-100 text-start bg-body-tertiary border-0 rounded-0 p-3 d-flex justify-content-between align-items-center"
                                        data-bs-toggle="collapse" data-bs-target="#proofDeliveryCollapse"
                                        aria-expanded="false" aria-controls="proofDeliveryCollapse">
                                        <span class="fw-semibold text-900">
                                            <span class="fas fa-receipt text-primary me-1"></span>
                                            Proof of Delivery
                                        </span>

                                        <span class="fas fa-chevron-down text-600" id="proofCollapseIcon"></span>
                                    </button>

                                    <div class="collapse" id="proofDeliveryCollapse">
                                        <div class="p-3 border-top text-center">
                                            <a href="{{ asset('storage/' . $receiving->proof_image) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $receiving->proof_image) }}"
                                                    alt="Proof of Delivery" class="img-fluid rounded border shadow-sm"
                                                    style="max-height: 380px;">
                                            </a>

                                            <div class="text-muted fs-10 mt-2">
                                                Open the image to verify the encoded delivered products.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body-tertiary border-bottom">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="ui-icon bg-success-subtle text-success">
                                <span class="fas fa-boxes"></span>
                            </div>

                            <div>
                                <h6 class="mb-0 fw-bold text-900">Delivered Product List</h6>
                                <p class="mb-0 fs-10 text-600">
                                    Each row shows delivered, rolled back, remaining balance, and rollback availability.
                                </p>
                            </div>
                        </div>

                        <span class="badge badge-subtle-primary rounded-pill px-3 py-2">
                            {{ $receiving->items->count() }} item{{ $receiving->items->count() !== 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>

                <div class="table-responsive scrollbar">
                    @can('receivings.rollback')
                        @php $canRollback = true; @endphp
                    @else
                        @php $canRollback = false; @endphp
                    @endcan

                    <table class="table table-hover align-middle mb-0 fs-10 delivered-products-table">
                        <thead class="bg-200 text-900">
                            <tr>
                                <th class="ps-3" style="width: 5%;">#</th>
                                <th>Product</th>
                                <th>Details</th>
                                <th class="text-center">Delivered</th>

                                @if ($canRollback)
                                    <th class="text-center">Rolled Back</th>
                                    <th class="text-center">Balance</th>
                                    <th class="text-center">Current Stock</th>
                                    <th class="text-center pe-3">Rollback</th>
                                @endif
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($receiving->items as $index => $item)
                                @php
                                    $rolledBack = (int) ($item->qty_rolled_back ?? 0);
                                    $deliveredQty = (int) $item->qty_delivered;
                                    $remaining = max(0, $deliveredQty - $rolledBack);
                                    $locationStock =
                                        \App\Models\ProductStock::query()
                                            ->where('product_id', $item->product_id)
                                            ->where('location_id', $receiving->location_id)
                                            ->value('qty') ?? 0;
                                    $rollbackLimit = min($remaining, (int) $locationStock);
                                    $productName = optional($item->product)->product_name ?? 'N/A';
                                    $productDetails = optional($item->product)->details ?? 'No details available.';
                                @endphp

                                <tr>
                                    <td class="ps-3 text-muted fw-semibold">
                                        {{ $index + 1 }}
                                    </td>

                                    <td class="rollback-product-cell">
                                        <div class="d-flex align-items-start gap-2">
                                            <div class="ui-icon bg-primary-subtle text-primary">
                                                <span class="fas fa-box"></span>
                                            </div>

                                            <div>
                                                <div class="fw-bold text-900">
                                                    {{ $productName }}
                                                </div>
                                                <div class="text-500 fs-11">
                                                    Product ID: {{ $item->product_id }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td style="min-width: 260px;">
                                        <div class="text-700">
                                            {{ $productDetails }}
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge badge-subtle-success rounded-pill px-3 py-2">
                                            {{ number_format($deliveredQty) }}
                                        </span>
                                    </td>

                                    @if ($canRollback)
                                        <td class="text-center">
                                            <span class="badge badge-subtle-warning rounded-pill px-3 py-2">
                                                {{ number_format($rolledBack) }}
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            @if ($remaining > 0)
                                                <span class="badge badge-subtle-info rounded-pill px-3 py-2">
                                                    {{ number_format($remaining) }}
                                                </span>
                                            @else
                                                <span class="badge badge-subtle-secondary rounded-pill px-3 py-2">
                                                    Fully rolled back
                                                </span>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            <span class="badge badge-subtle-primary rounded-pill px-3 py-2">
                                                {{ number_format($locationStock) }}
                                            </span>
                                        </td>

                                        <td class="text-center pe-3">
                                            @if ($remaining <= 0)
                                                <button type="button" class="btn btn-falcon-default btn-sm" disabled>
                                                    <span class="fas fa-check me-1"></span>
                                                    Done
                                                </button>
                                            @elseif ($locationStock <= 0)
                                                <span class="badge badge-subtle-danger rounded-pill px-3 py-2">
                                                    No stock
                                                </span>
                                            @else
                                                <button type="button" class="btn btn-falcon-warning btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#rollbackModal{{ $item->id }}">
                                                    <span class="fas fa-undo me-1"></span>
                                                    Rollback
                                                </button>

                                                <div class="modal fade" id="rollbackModal{{ $item->id }}"
                                                    tabindex="-1"
                                                    aria-labelledby="rollbackModalLabel{{ $item->id }}"
                                                    aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content border-0 shadow">
                                                            <form
                                                                action="{{ route('receivings.rollback', [$receiving->id, $item->id]) }}"
                                                                method="POST">
                                                                @csrf

                                                                <div class="modal-header bg-warning-subtle border-0">
                                                                    <div>
                                                                        <h5 class="modal-title text-warning-emphasis"
                                                                            id="rollbackModalLabel{{ $item->id }}">
                                                                            <span class="fas fa-undo me-1"></span>
                                                                            Confirm Product Rollback
                                                                        </h5>
                                                                        <div class="fs-10 text-700">
                                                                            This will deduct stock from
                                                                            {{ $receiving->location->name ?? 'this location' }}.
                                                                        </div>
                                                                    </div>

                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal"
                                                                        aria-label="Close"></button>
                                                                </div>

                                                                <div class="modal-body text-start">
                                                                    <div class="rollback-summary-card mb-3">
                                                                        <div class="d-flex align-items-start gap-2">
                                                                            <div
                                                                                class="ui-icon bg-primary-subtle text-primary">
                                                                                <span class="fas fa-box"></span>
                                                                            </div>

                                                                            <div>
                                                                                <div
                                                                                    class="fs-10 text-500 text-uppercase fw-semibold mb-1">
                                                                                    Product to Rollback
                                                                                </div>
                                                                                <div class="fw-bold text-900">
                                                                                    {{ $productName }}
                                                                                </div>
                                                                                <div class="text-600 fs-10">
                                                                                    {{ $productDetails }}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row g-2 mb-3">
                                                                        <div class="col-6">
                                                                            <div class="rollback-summary-card">
                                                                                <div class="fs-11 text-500">Delivered</div>
                                                                                <div class="fw-bold text-success">
                                                                                    {{ number_format($deliveredQty) }}
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-6">
                                                                            <div class="rollback-summary-card">
                                                                                <div class="fs-11 text-500">Already Rolled
                                                                                    Back</div>
                                                                                <div class="fw-bold text-warning">
                                                                                    {{ number_format($rolledBack) }}
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-6">
                                                                            <div class="rollback-summary-card">
                                                                                <div class="fs-11 text-500">Remaining
                                                                                    Balance</div>
                                                                                <div class="fw-bold text-info">
                                                                                    {{ number_format($remaining) }}
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-6">
                                                                            <div class="rollback-summary-card">
                                                                                <div class="fs-11 text-500">Current
                                                                                    Location Stock</div>
                                                                                <div class="fw-bold text-primary">
                                                                                    {{ number_format($locationStock) }}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div
                                                                        class="alert alert-warning rollback-warning-box mb-3">
                                                                        <div class="fw-semibold mb-1">Rollback limit</div>
                                                                        <div class="fs-10">
                                                                            Maximum allowed rollback for this product is
                                                                            <strong>{{ number_format($rollbackLimit) }}</strong>.
                                                                        </div>
                                                                    </div>

                                                                    <label for="rollbackQty{{ $item->id }}"
                                                                        class="form-label fw-semibold">
                                                                        Quantity to Rollback
                                                                    </label>

                                                                    <input type="number"
                                                                        id="rollbackQty{{ $item->id }}"
                                                                        name="rollback_qty" class="form-control"
                                                                        min="1" max="{{ $rollbackLimit }}"
                                                                        value="1" required>

                                                                    <div class="form-text">
                                                                        Enter only the quantity that should be deducted from
                                                                        stock.
                                                                    </div>
                                                                </div>

                                                                <div class="modal-footer bg-body-tertiary border-0">
                                                                    <button type="button" class="btn btn-falcon-default"
                                                                        data-bs-dismiss="modal">
                                                                        Cancel
                                                                    </button>

                                                                    <button type="submit" class="btn btn-warning">
                                                                        <span class="fas fa-undo me-1"></span>
                                                                        Confirm Rollback
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $canRollback ? 8 : 4 }}" class="text-center py-5">
                                        <div class="text-muted">
                                            <span class="fas fa-inbox fs-3 d-block mb-3 text-300"></span>
                                            <h6 class="mb-1">No delivered products found</h6>
                                            <p class="mb-0 fs-10">This receiving record has no encoded products.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-body-tertiary border-top">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                        <div class="text-muted fs-10">
                            Total Delivered Quantity:
                            <strong>{{ number_format($receiving->items->sum('qty_delivered')) }}</strong>
                        </div>

                        <a href="{{ route('receivings.index') }}" class="btn btn-falcon-default btn-sm">
                            <span class="fas fa-arrow-left me-1"></span>
                            Close
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const collapseEl = document.getElementById('proofDeliveryCollapse');
            const icon = document.getElementById('proofCollapseIcon');

            if (!collapseEl || !icon) {
                return;
            }

            collapseEl.addEventListener('show.bs.collapse', function() {
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            });

            collapseEl.addEventListener('hide.bs.collapse', function() {
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            });
        });
    </script>
@endpush
