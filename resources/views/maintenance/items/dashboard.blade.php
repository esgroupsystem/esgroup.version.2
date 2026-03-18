@extends('layouts.app')
@section('title', 'Stock Dashboard')

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

            {{-- TOP WELCOME CARD --}}
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <div class="card bg-body-tertiary dark__bg-opacity-50 shadow-none border overflow-hidden">
                        <div class="bg-holder bg-card d-none d-sm-block"
                            style="background-image:url({{ asset('assets/img/illustrations/ticket-bg.png') }});">
                        </div>

                        <div class="card-body position-relative py-3">
                            <div class="d-flex flex-column flex-sm-row align-items-sm-center">
                                <img src="{{ asset('assets/img/illustrations/ticket-welcome.png') }}" alt="dashboard"
                                    width="90" class="me-sm-3 mb-2 mb-sm-0" />
                                <div>
                                    <h6 class="mb-1 text-primary">Welcome to</h6>
                                    <h4 class="mb-1 text-primary fw-bold">
                                        Maintenance <span class="text-info fw-medium">Stock Dashboard</span>
                                    </h4>
                                    <p class="text-700 mb-0 fs-10">
                                        Overview of maintenance parts and current stock availability.
                                    </p>
                                </div>

                                <div class="ms-sm-auto mt-3 mt-sm-0">
                                    <a href="{{ route('items.index') }}" class="btn btn-falcon-default btn-sm">
                                        <i class="fas fa-arrow-left me-1"></i> Back to Items
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            {{-- SUMMARY CARDS --}}
            <div class="row g-3 mb-3">
                <div class="col-md-6 col-xl-3">
                    <div class="card h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-600 fs-10 mb-1">Total Products</div>
                                <h4 class="mb-0">{{ $totalItems }}</h4>
                            </div>
                            <div class="icon-item icon-item-lg bg-primary-subtle text-primary border border-primary-subtle">
                                <span class="fas fa-box-open"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-600 fs-10 mb-1">Total Stock Qty</div>
                                <h4 class="mb-0">{{ $totalStock }}</h4>
                            </div>
                            <div class="icon-item icon-item-lg bg-info-subtle text-info border border-info-subtle">
                                <span class="fas fa-cubes"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-600 fs-10 mb-1">Low Stock</div>
                                <h4 class="mb-0 text-warning">{{ $lowStock }}</h4>
                            </div>
                            <div class="icon-item icon-item-lg bg-warning-subtle text-warning border border-warning-subtle">
                                <span class="fas fa-exclamation-triangle"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card h-100">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-600 fs-10 mb-1">Out of Stock</div>
                                <h4 class="mb-0 text-danger">{{ $outOfStock }}</h4>
                            </div>
                            <div class="icon-item icon-item-lg bg-danger-subtle text-danger border border-danger-subtle">
                                <span class="fas fa-times-circle"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            {{-- AVAILABLE STOCK TABLE --}}
            <div class="card mb-4" id="availableStockTable">
                <div class="card-header border-bottom border-200 px-0">
                    <div class="d-lg-flex justify-content-between align-items-center">
                        <div class="row flex-between-center gy-2 px-x1">
                            <div class="col-auto pe-0">
                                <h6 class="mb-0">
                                    <span class="fas fa-check-circle text-success me-2"></span>
                                    Available Stocks
                                </h6>
                            </div>
                        </div>

                        <div class="border-bottom border-200 my-3 d-lg-none"></div>

                        <div class="d-flex align-items-center justify-content-lg-end px-x1">
                            <span class="badge badge-subtle-success fs-10 px-3 py-2">
                                Total Records: {{ $productsWithStock->total() }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive scrollbar" style="max-height: 500px;">
                        <table class="table table-sm table-hover mb-0 fs-10 align-middle">
                            <thead class="bg-body-tertiary">
                                <tr>
                                    <th class="text-800">Category</th>
                                    <th class="text-800">Product</th>
                                    <th class="text-800 text-center">Unit</th>
                                    <th class="text-800 text-center">Status</th>
                                    <th class="text-800 text-center">Stock</th>
                                    <th class="text-800" style="min-width: 150px;">Indicator</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($productsWithStock as $p)
                                    @php
                                        $qty = $p->stock_qty ?? 0;
                                        $percent = min(100, ($qty / 10) * 100);
                                    @endphp

                                    <tr>
                                        <td class="align-middle white-space-nowrap">
                                            <span class="text-700">{{ $p->category->name ?? '—' }}</span>
                                        </td>

                                        <td class="align-middle">
                                            <div class="fw-semi-bold text-900">{{ $p->product_name }}</div>
                                            <div class="text-500 fs-11">{{ $p->details ?: 'No details available' }}</div>
                                        </td>

                                        <td class="align-middle text-center">
                                            <span class="badge badge-subtle-secondary px-2 py-1">
                                                {{ $p->unit ?: '—' }}
                                            </span>
                                        </td>

                                        <td class="align-middle text-center">
                                            @if ($qty <= 5)
                                                <span class="badge rounded-pill badge-subtle-warning px-3 py-2">
                                                    Low
                                                </span>
                                            @else
                                                <span class="badge rounded-pill badge-subtle-success px-3 py-2">
                                                    Available
                                                </span>
                                            @endif
                                        </td>

                                        <td class="align-middle text-center fw-bold fs-9">
                                            {{ $qty }}
                                        </td>

                                        <td class="align-middle">
                                            <div class="progress bg-200" style="height: 8px;">
                                                <div class="progress-bar
                                                    @if ($qty <= 5) bg-warning
                                                    @else bg-success @endif"
                                                    role="progressbar" style="width: {{ $percent }}%;"
                                                    aria-valuenow="{{ $percent }}" aria-valuemin="0"
                                                    aria-valuemax="100">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <span class="fas fa-box-open fs-5 mb-2 d-block text-400"></span>
                                            No available stock found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-body-tertiary py-2">
                    <div class="d-flex justify-content-end">
                        {{ $productsWithStock->links('pagination.custom') }}
                    </div>
                </div>
            </div>


            {{-- LOW / OUT OF STOCK TABLE --}}
            <div class="card" id="criticalStockTable">
                <div class="card-header border-bottom border-200 px-0">
                    <div class="d-lg-flex justify-content-between align-items-center">
                        <div class="row flex-between-center gy-2 px-x1">
                            <div class="col-auto pe-0">
                                <h6 class="mb-0">
                                    <span class="fas fa-exclamation-triangle text-warning me-2"></span>
                                    Low / Out of Stock Items
                                </h6>
                            </div>
                        </div>

                        <div class="border-bottom border-200 my-3 d-lg-none"></div>

                        <div class="d-flex align-items-center justify-content-lg-end px-x1">
                            <span class="badge badge-subtle-warning fs-10 px-3 py-2">
                                Total Records: {{ $productsLowStock->total() }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive scrollbar" style="max-height: 500px;">
                        <table class="table table-sm table-hover mb-0 fs-10 align-middle">
                            <thead class="bg-body-tertiary">
                                <tr>
                                    <th class="text-800">Category</th>
                                    <th class="text-800">Product</th>
                                    <th class="text-800 text-center">Unit</th>
                                    <th class="text-800 text-center">Status</th>
                                    <th class="text-800 text-center">Stock</th>
                                    <th class="text-800" style="min-width: 150px;">Indicator</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($productsLowStock as $p)
                                    @php
                                        $qty = $p->stock_qty ?? 0;
                                        $percent = max(0, min(100, ($qty / 10) * 100));
                                    @endphp

                                    <tr>
                                        <td class="align-middle white-space-nowrap">
                                            <span class="text-700">{{ $p->category->name ?? '—' }}</span>
                                        </td>

                                        <td class="align-middle">
                                            <div class="fw-semi-bold text-900">{{ $p->product_name }}</div>
                                            <div class="text-500 fs-11">{{ $p->details ?: 'No details available' }}</div>
                                        </td>

                                        <td class="align-middle text-center">
                                            <span class="badge badge-subtle-secondary px-2 py-1">
                                                {{ $p->unit ?: '—' }}
                                            </span>
                                        </td>

                                        <td class="align-middle text-center">
                                            @if ($qty <= 0)
                                                <span class="badge rounded-pill badge-subtle-danger px-3 py-2">
                                                    Out of Stock
                                                </span>
                                            @elseif ($qty <= 5)
                                                <span class="badge rounded-pill badge-subtle-warning px-3 py-2">
                                                    Low
                                                </span>
                                            @endif
                                        </td>

                                        <td class="align-middle text-center fw-bold fs-9">
                                            {{ $qty }}
                                        </td>

                                        <td class="align-middle">
                                            <div class="progress bg-200" style="height: 8px;">
                                                <div class="progress-bar
                                                    @if ($qty <= 0) bg-danger
                                                    @elseif($qty <= 5) bg-warning @endif"
                                                    role="progressbar" style="width: {{ $percent }}%;"
                                                    aria-valuenow="{{ $percent }}" aria-valuemin="0"
                                                    aria-valuemax="100">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <span class="fas fa-box-open fs-5 mb-2 d-block text-400"></span>
                                            No low or out of stock items found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-body-tertiary py-2">
                    <div class="d-flex justify-content-end">
                        {{ $productsLowStock->links('pagination.custom') }}
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
