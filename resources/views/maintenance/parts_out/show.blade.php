@extends('layouts.app')

@section('title', 'Parts Out Details')

@push('styles')
    <style>
        .details-hero {
            background:
                linear-gradient(135deg, rgba(var(--falcon-primary-rgb), .13), rgba(var(--falcon-warning-rgb), .06)),
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
            width: 54px;
            height: 54px;
            min-width: 54px;
            font-size: 1.25rem;
            border-radius: 16px;
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
            font-weight: 700;
            margin-bottom: .25rem;
        }

        .info-value {
            color: var(--falcon-900);
            font-weight: 700;
        }

        .items-used-table th {
            white-space: nowrap;
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .045em;
        }

        .items-used-table td {
            vertical-align: middle;
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
            @php
                $status = strtolower($partsOut->status ?? '');

                $statusClass = match ($status) {
                    'posted' => 'success',
                    'cancelled' => 'danger',
                    'rolled_back' => 'warning',
                    default => 'secondary',
                };

                $statusLabel = match ($status) {
                    'posted' => 'Posted',
                    'cancelled' => 'Cancelled',
                    'rolled_back' => 'Rolled Back',
                    default => ucfirst(str_replace('_', ' ', $partsOut->status ?? 'N/A')),
                };
            @endphp

            {{-- HERO --}}
            <div class="card border-0 shadow-sm mb-3 details-hero">
                <div class="card-body p-4">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg">
                            <div class="d-flex align-items-start gap-3">
                                <div class="ui-icon ui-icon-lg bg-primary-subtle text-primary">
                                    <span class="fas fa-tools"></span>
                                </div>

                                <div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <h6 class="mb-0 text-primary fw-semibold">
                                            Parts Out Details
                                        </h6>

                                        <span class="badge badge-subtle-primary rounded-pill">
                                            {{ $partsOut->parts_out_number }}
                                        </span>

                                        <span
                                            class="badge bg-{{ $statusClass }}-subtle text-{{ $statusClass }} border border-{{ $statusClass }}-subtle rounded-pill">
                                            {{ $statusLabel }}
                                        </span>
                                    </div>

                                    <h3 class="mb-1 fw-bold text-900">
                                        Issued / Installed Parts
                                    </h3>

                                    <p class="mb-0 text-600">
                                        View vehicle usage, mechanic details, items used, stock before and stock after.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-auto">
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                @if ($partsOut->status === 'posted')
                                    @can('parts-out.rollback')
                                        <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                                            data-bs-target="#rollbackPartsOutModal">
                                            <span class="fas fa-undo me-1"></span>
                                            Rollback Transaction
                                        </button>
                                    @endcan
                                @endif

                                <a href="{{ route('parts-out.index') }}" class="btn btn-falcon-default">
                                    <span class="fas fa-arrow-left me-1"></span>
                                    Back to Records
                                </a>
                            </div>
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

            {{-- DETAILS --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-body-tertiary border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="ui-icon bg-primary-subtle text-primary">
                            <span class="fas fa-info-circle"></span>
                        </div>

                        <div>
                            <h6 class="mb-0 fw-bold text-900">Transaction Information</h6>
                            <p class="mb-0 fs-10 text-600">Main parts out reference and maintenance details.</p>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="info-tile">
                                <div class="info-label">Parts Out No.</div>
                                <div class="info-value text-primary">
                                    {{ $partsOut->parts_out_number }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="info-tile">
                                <div class="info-label">Date Issued</div>
                                <div class="info-value">
                                    {{ $partsOut->issued_date ? \Carbon\Carbon::parse($partsOut->issued_date)->format('M d, Y') : 'N/A' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="info-tile">
                                <div class="info-label">Mechanic</div>
                                <div class="info-value">
                                    {{ $partsOut->mechanic_name ?: '—' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="info-tile">
                                <div class="info-label">Status</div>
                                <div>
                                    <span
                                        class="badge bg-{{ $statusClass }}-subtle text-{{ $statusClass }} border border-{{ $statusClass }}-subtle rounded-pill px-3 py-2">
                                        {{ $statusLabel }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-tile">
                                <div class="info-label">Vehicle</div>

                                @if ($partsOut->vehicle)
                                    <div class="info-value">
                                        {{ $partsOut->vehicle->plate_number ?? 'N/A' }}
                                    </div>

                                    <div class="text-500 fs-11">
                                        Body No.: {{ $partsOut->vehicle->body_number ?? 'N/A' }}
                                        @if (!empty($partsOut->vehicle->name))
                                            | {{ $partsOut->vehicle->name }}
                                        @endif
                                        @if (!empty($partsOut->vehicle->garage))
                                            | Garage: {{ $partsOut->vehicle->garage }}
                                        @endif
                                    </div>
                                @else
                                    <div class="text-muted">No vehicle selected</div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="info-tile">
                                <div class="info-label">Source Garage</div>
                                <div class="info-value">
                                    {{ $partsOut->location->name ?? 'N/A' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="info-tile">
                                <div class="info-label">Encoded By</div>
                                <div class="info-value">
                                    {{ $partsOut->creator->full_name ?? ($partsOut->creator->name ?? '—') }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="info-tile">
                                <div class="info-label">Requested By</div>
                                <div class="info-value">
                                    {{ $partsOut->requested_by ?: '—' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="info-tile">
                                <div class="info-label">Job Order No.</div>
                                <div class="info-value">
                                    {{ $partsOut->job_order_no ?: '—' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="info-tile">
                                <div class="info-label">Odometer</div>
                                <div class="info-value">
                                    {{ $partsOut->odometer ?: '—' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-tile">
                                <div class="info-label">Purpose / Work Details</div>
                                <div class="text-800">
                                    {{ $partsOut->purpose ?: '—' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-tile">
                                <div class="info-label">Remarks</div>
                                <div class="text-800">
                                    {{ $partsOut->remarks ?: '—' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ITEMS USED --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body-tertiary border-bottom">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="ui-icon bg-warning-subtle text-warning">
                                <span class="fas fa-boxes"></span>
                            </div>

                            <div>
                                <h6 class="mb-0 fw-bold text-900">Items Used</h6>
                                <p class="mb-0 fs-10 text-600">
                                    Stock before and after are recorded for audit tracking.
                                </p>
                            </div>
                        </div>

                        <span class="badge badge-subtle-primary rounded-pill px-3 py-2">
                            {{ $partsOut->items->count() }} item{{ $partsOut->items->count() !== 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>

                <div class="table-responsive scrollbar">
                    <table class="table table-hover align-middle fs-10 mb-0 items-used-table">
                        <thead class="bg-200 text-900">
                            <tr>
                                <th class="ps-3">Product</th>
                                <th>Supplier</th>
                                <th>Unit</th>
                                <th>Part No.</th>
                                <th class="text-center">Qty Used</th>
                                <th class="text-center">Stock Before</th>
                                <th class="text-center">Stock After</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($partsOut->items as $item)
                                <tr>
                                    <td class="ps-3" style="min-width: 260px;">
                                        <div class="d-flex align-items-start gap-2">
                                            <div class="ui-icon bg-primary-subtle text-primary">
                                                <span class="fas fa-box"></span>
                                            </div>

                                            <div>
                                                <div class="fw-bold text-900">
                                                    {{ $item->product->product_name ?? 'N/A' }}
                                                </div>
                                                <div class="text-500 fs-11">
                                                    Product ID: {{ $item->product_id }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td>{{ $item->product->supplier_name ?? '—' }}</td>
                                    <td>{{ $item->product->unit ?? '—' }}</td>
                                    <td>{{ $item->product->part_number ?? '—' }}</td>

                                    <td class="text-center">
                                        <span class="badge badge-subtle-warning rounded-pill px-3 py-2">
                                            {{ number_format($item->qty_used) }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge badge-subtle-secondary rounded-pill px-3 py-2">
                                            {{ number_format($item->stock_before) }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge badge-subtle-info rounded-pill px-3 py-2">
                                            {{ number_format($item->stock_after) }}
                                        </span>
                                    </td>

                                    <td style="min-width: 220px;">
                                        {{ $item->remarks ?: '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="text-muted">
                                            <span class="fas fa-inbox fs-3 d-block mb-3 text-300"></span>
                                            <h6 class="mb-1">No items found</h6>
                                            <p class="mb-0 fs-10">This Parts Out record has no encoded items.</p>
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
                            Total Used Quantity:
                            <strong>{{ number_format($partsOut->items->sum('qty_used')) }}</strong>
                        </div>

                        <a href="{{ route('parts-out.index') }}" class="btn btn-falcon-default btn-sm">
                            <span class="fas fa-arrow-left me-1"></span>
                            Close
                        </a>
                    </div>
                </div>
            </div>

            {{-- ROLLBACK MODAL --}}
            @if ($partsOut->status === 'posted')
                @can('parts-out.rollback')
                    <div class="modal fade" id="rollbackPartsOutModal" tabindex="-1"
                        aria-labelledby="rollbackPartsOutModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <form action="{{ route('parts-out.rollback', $partsOut) }}" method="POST">
                                    @csrf
                                    @method('PATCH')

                                    <div class="modal-header bg-warning-subtle border-0">
                                        <div>
                                            <h5 class="modal-title text-warning-emphasis" id="rollbackPartsOutModalLabel">
                                                <span class="fas fa-undo me-1"></span>
                                                Confirm Parts Out Rollback
                                            </h5>
                                            <div class="fs-10 text-700">
                                                This will return all used quantities to
                                                {{ $partsOut->location->name ?? 'the source garage' }}.
                                            </div>
                                        </div>

                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="rollback-summary-card mb-3">
                                            <div class="fs-10 text-500 text-uppercase fw-semibold mb-1">
                                                Transaction
                                            </div>
                                            <div class="fw-bold text-900">
                                                {{ $partsOut->parts_out_number }}
                                            </div>
                                            <div class="text-600 fs-10">
                                                {{ $partsOut->items->count() }} item(s), total quantity
                                                {{ number_format($partsOut->items->sum('qty_used')) }}
                                            </div>
                                        </div>

                                        <div class="alert alert-warning rollback-warning-box mb-3">
                                            <div class="fw-semibold mb-1">Rollback effect</div>
                                            <div class="fs-10">
                                                All item quantities will be added back to stock. The transaction status will
                                                become Rolled Back.
                                            </div>
                                        </div>

                                        <label for="rollbackReason" class="form-label fw-semibold">
                                            Rollback Reason
                                        </label>

                                        <textarea name="rollback_reason" id="rollbackReason" class="form-control" rows="3" maxlength="1000"
                                            placeholder="Optional rollback reason...">Rollback from Parts Out details</textarea>
                                    </div>

                                    <div class="modal-footer bg-body-tertiary border-0">
                                        <button type="button" class="btn btn-falcon-default" data-bs-dismiss="modal">
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
                @endcan
            @endif
        </div>
    </div>
@endsection
