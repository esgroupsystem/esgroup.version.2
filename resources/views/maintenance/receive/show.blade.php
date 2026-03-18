@extends('layouts.app')
@section('title', 'Receiving Details')

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

            {{-- HEADER CARD --}}
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <span class="fas fa-truck-loading text-primary me-2"></span>
                            Receiving Details
                        </h5>
                        <p class="text-muted fs-10 mb-0 mt-1">
                            View delivered products and receiving information
                        </p>
                    </div>

                    <div>
                        <a href="{{ route('receivings.index') }}" class="btn btn-falcon-default btn-sm">
                            <span class="fas fa-arrow-left me-1"></span> Back
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-3">
                            <div class="border rounded-3 p-3 bg-100 h-100">
                                <div class="text-muted fs-10 mb-1">Receiving No.</div>
                                <div class="fw-semi-bold fs-9 text-primary">
                                    {{ $receiving->receiving_number }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded-3 p-3 bg-100 h-100">
                                <div class="text-muted fs-10 mb-1">Delivered By</div>
                                <div class="fw-semi-bold fs-9">
                                    {{ $receiving->delivered_by ?? 'N/A' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded-3 p-3 bg-100 h-100">
                                <div class="text-muted fs-10 mb-1">Delivery Date</div>
                                <div class="fw-semi-bold fs-9">
                                    {{ $receiving->delivery_date ? \Carbon\Carbon::parse($receiving->delivery_date)->format('F d, Y') : 'N/A' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="border rounded-3 p-3 bg-100 h-100">
                                <div class="text-muted fs-10 mb-1">Received By</div>
                                <div class="fw-semi-bold fs-9">
                                    {{ optional($receiving->receiver)->full_name ?? (optional($receiving->receiver)->name ?? 'N/A') }}
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="border rounded-3 p-3 bg-100">
                                <div class="text-muted fs-10 mb-1">Remarks</div>
                                <div class="fs-9 text-800">
                                    {{ $receiving->remarks ?: 'No remarks provided.' }}
                                </div>
                            </div>
                        </div>

                        @if ($receiving->proof_image)
                            <div class="col-12">
                                <div class="border rounded-3 bg-100 overflow-hidden">
                                    <div class="d-flex justify-content-between align-items-center p-3"
                                        style="cursor: pointer;" role="button" tabindex="0" data-bs-toggle="collapse"
                                        data-bs-target="#proofDeliveryCollapse" aria-expanded="false"
                                        aria-controls="proofDeliveryCollapse">

                                        <div class="text-muted fs-10 fw-semi-bold">
                                            <span class="fas fa-receipt text-primary me-1"></span>
                                            Proof of Delivery
                                        </div>

                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fas fa-chevron-down text-600" id="proofCollapseIcon"></span>
                                        </div>
                                    </div>

                                    <div class="collapse" id="proofDeliveryCollapse">
                                        <div class="px-3 pb-3 border-top">
                                            <div class="text-center pt-3">
                                                <a href="{{ asset('storage/' . $receiving->proof_image) }}" target="_blank">
                                                    <img src="{{ asset('storage/' . $receiving->proof_image) }}"
                                                        alt="Proof of Delivery" class="img-fluid rounded border shadow-sm"
                                                        style="max-height: 360px;">
                                                </a>
                                                <div class="text-muted fs-10 mt-2">
                                                    Use this proof to double-check if the encoded delivery matches the
                                                    actual receipt.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>

            {{-- ITEMS CARD --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">
                            <span class="fas fa-boxes text-success me-2"></span>
                            Delivered Products
                        </h6>
                    </div>
                    <div>
                        <span class="badge bg-primary fs-10">
                            {{ $receiving->items->count() }} Item{{ $receiving->items->count() != 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="bg-200 text-900">
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 30%;">Product</th>
                                <th>Details</th>
                                <th class="text-center" style="width: 15%;">Qty Delivered</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($receiving->items as $index => $item)
                                <tr>
                                    <td class="text-center text-muted">{{ $index + 1 }}</td>

                                    <td>
                                        <div class="fw-semi-bold text-900">
                                            {{ optional($item->product)->product_name ?? 'N/A' }}
                                        </div>
                                    </td>

                                    <td>
                                        <span class="text-muted">
                                            {{ optional($item->product)->details ?? 'No details available.' }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <span
                                            class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                            {{ $item->qty_delivered }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <div class="text-muted">
                                            <span class="fas fa-inbox fa-2x mb-3 d-block"></span>
                                            No delivered products found.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-light d-flex justify-content-between align-items-center">
                    <div class="text-muted fs-10">
                        Total Delivered Quantity:
                        <strong>{{ $receiving->items->sum('qty_delivered') }}</strong>
                    </div>

                    <a href="{{ route('receivings.index') }}" class="btn btn-falcon-default btn-sm">
                        <span class="fas fa-arrow-left me-1"></span>
                        Close
                    </a>
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

            if (collapseEl && icon) {
                collapseEl.addEventListener('show.bs.collapse', function() {
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                });

                collapseEl.addEventListener('hide.bs.collapse', function() {
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                });
            }
        });
    </script>
@endpush
