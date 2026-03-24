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

            {{-- HEADER --}}
            <div class="row g-3 mb-3">
                <div class="col-12">
                    <div class="card bg-body-tertiary border-0 shadow-sm overflow-hidden">
                        <div class="card-body py-3">
                            <div class="d-flex flex-column flex-sm-row align-items-sm-center">
                                <div>
                                    <h6 class="mb-1 text-primary">Maintenance</h6>
                                    <h4 class="mb-1 text-primary fw-bold">
                                        Stock Dashboard
                                    </h4>
                                    <p class="text-700 mb-0 fs-10">
                                        Easy stock monitoring for Main and Balintawak.
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

            {{-- SUMMARY --}}
            <div class="row g-3 mb-3">
                <div class="col-md-6 col-xl-2">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-600 fs-10 mb-1">Products</div>
                            <h4 class="mb-0">{{ number_format($totalItems) }}</h4>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-2">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-600 fs-10 mb-1">Combined Stock</div>
                            <h4 class="mb-0">{{ number_format($totalStock) }}</h4>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-2">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-600 fs-10 mb-1">Main Stock</div>
                            <h4 class="mb-0 text-primary">{{ number_format($mainTotalStock) }}</h4>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-2">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-600 fs-10 mb-1">Balintawak Stock</div>
                            <h4 class="mb-0 text-info">{{ number_format($balintawakTotalStock) }}</h4>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-2">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-600 fs-10 mb-1">Low Stock</div>
                            <h4 class="mb-0 text-warning">{{ number_format($lowStock) }}</h4>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-2">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-600 fs-10 mb-1">Out of Stock</div>
                            <h4 class="mb-0 text-danger">{{ number_format($outOfStock) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FILTER BAR --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('items.dashboard') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label">Search Product / Part Number</label>
                                <input type="text" name="search" class="form-control" value="{{ $search }}"
                                    placeholder="Search by product name or part number">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Filter View</label>
                                <select name="location" class="form-select">
                                    <option value="">All</option>
                                    <option value="main" {{ $locationFilter === 'main' ? 'selected' : '' }}>Main Only
                                    </option>
                                    <option value="balintawak" {{ $locationFilter === 'balintawak' ? 'selected' : '' }}>
                                        Balintawak Only</option>
                                    <option value="needs_transfer"
                                        {{ $locationFilter === 'needs_transfer' ? 'selected' : '' }}>Needs Transfer
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-2 d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i> Apply
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- MAIN STOCK TABLE --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">
                            <span class="fas fa-warehouse text-primary me-2"></span>
                            Main Stock
                        </h6>
                        <p class="text-muted fs-10 mb-0 mt-1">
                            Current available stock in {{ $mainLocation->name ?? 'Main' }}.
                        </p>
                    </div>
                    <span class="badge badge-subtle-primary fs-10 px-3 py-2">
                        Total Records: {{ $mainStocksPaginated->total() }}
                    </span>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive scrollbar">
                        <table class="table table-hover table-striped align-middle mb-0 fs-10">
                            <thead class="bg-200 text-900">
                                <tr>
                                    <th>Category</th>
                                    <th>Product</th>
                                    <th class="text-center">Unit</th>
                                    <th class="text-center">Available Qty</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mainStocksPaginated as $p)
                                    <tr>
                                        <td>{{ $p->category->name ?? ($p->category->category_name ?? '—') }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $p->product_name }}</div>
                                            <div class="text-500 fs-11">{{ $p->details ?: 'No details available' }}</div>
                                            @if ($p->part_number)
                                                <div class="text-500 fs-11">Part No: {{ $p->part_number }}</div>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $p->unit ?: '—' }}</td>
                                        <td class="text-center">
                                            @if ($p->main_qty <= 0)
                                                <span class="badge badge-subtle-danger px-3 py-2">
                                                    {{ $p->main_qty }}
                                                </span>
                                            @elseif($p->main_qty <= 5)
                                                <span class="badge badge-subtle-warning px-3 py-2">
                                                    {{ $p->main_qty }}
                                                </span>
                                            @else
                                                <span class="badge badge-subtle-success px-3 py-2">
                                                    {{ $p->main_qty }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($p->main_qty <= 0)
                                                <span class="badge badge-subtle-danger px-3 py-2">Out of Stock</span>
                                            @elseif($p->main_qty <= 5)
                                                <span class="badge badge-subtle-warning px-3 py-2">Low Stock</span>
                                            @else
                                                <span class="badge badge-subtle-success px-3 py-2">Available</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            No Main stock found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-body-tertiary py-2">
                    <div class="d-flex justify-content-end">
                        {{ $mainStocksPaginated->links('pagination.custom') }}
                    </div>
                </div>
            </div>

            {{-- BALINTAWAK STOCK TABLE --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">
                            <span class="fas fa-warehouse text-info me-2"></span>
                            Balintawak Stock
                        </h6>
                        <p class="text-muted fs-10 mb-0 mt-1">
                            Current available stock in {{ $balintawakLocation->name ?? 'Balintawak' }}.
                        </p>
                    </div>
                    <span class="badge badge-subtle-info fs-10 px-3 py-2">
                        Total Records: {{ $balintawakStocksPaginated->total() }}
                    </span>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive scrollbar">
                        <table class="table table-hover table-striped align-middle mb-0 fs-10">
                            <thead class="bg-200 text-900">
                                <tr>
                                    <th>Category</th>
                                    <th>Product</th>
                                    <th class="text-center">Unit</th>
                                    <th class="text-center">Available Qty</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($balintawakStocksPaginated as $p)
                                    <tr>
                                        <td>{{ $p->category->name ?? ($p->category->category_name ?? '—') }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $p->product_name }}</div>
                                            <div class="text-500 fs-11">{{ $p->details ?: 'No details available' }}</div>
                                            @if ($p->part_number)
                                                <div class="text-500 fs-11">Part No: {{ $p->part_number }}</div>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $p->unit ?: '—' }}</td>
                                        <td class="text-center">
                                            @if ($p->balintawak_qty <= 0)
                                                <span class="badge badge-subtle-danger px-3 py-2">
                                                    {{ $p->balintawak_qty }}
                                                </span>
                                            @elseif($p->balintawak_qty <= 5)
                                                <span class="badge badge-subtle-warning px-3 py-2">
                                                    {{ $p->balintawak_qty }}
                                                </span>
                                            @else
                                                <span class="badge badge-subtle-success px-3 py-2">
                                                    {{ $p->balintawak_qty }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($p->balintawak_qty <= 0)
                                                <span class="badge badge-subtle-danger px-3 py-2">Out of Stock</span>
                                            @elseif($p->balintawak_qty <= 5)
                                                <span class="badge badge-subtle-warning px-3 py-2">Low Stock</span>
                                            @else
                                                <span class="badge badge-subtle-success px-3 py-2">Available</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            No Balintawak stock found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-body-tertiary py-2">
                    <div class="d-flex justify-content-end">
                        {{ $balintawakStocksPaginated->links('pagination.custom') }}
                    </div>
                </div>
            </div>

            {{-- NEEDS TRANSFER --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">
                            <span class="fas fa-exchange-alt text-warning me-2"></span>
                            Needs Transfer Suggestion
                        </h6>
                        <p class="text-muted fs-10 mb-0 mt-1">
                            Suggested stock movement between Main and Balintawak.
                        </p>
                    </div>
                    <span class="badge badge-subtle-warning fs-10 px-3 py-2">
                        Total Records: {{ $needsTransferPaginated->total() }}
                    </span>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive scrollbar">
                        <table class="table table-hover table-striped align-middle mb-0 fs-10">
                            <thead class="bg-200 text-900">
                                <tr>
                                    <th>Category</th>
                                    <th>Product</th>
                                    <th class="text-center">Main Qty</th>
                                    <th class="text-center">Balintawak Qty</th>
                                    <th class="text-center">Suggestion</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($needsTransferPaginated as $p)
                                    <tr>
                                        <td>{{ $p->category->name ?? ($p->category->category_name ?? '—') }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $p->product_name }}</div>
                                            @if ($p->part_number)
                                                <div class="text-500 fs-11">Part No: {{ $p->part_number }}</div>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-subtle-primary px-3 py-2">
                                                {{ $p->main_qty }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-subtle-info px-3 py-2">
                                                {{ $p->balintawak_qty }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-subtle-warning px-3 py-2">
                                                {{ $p->transfer_suggestion }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            No transfer suggestions found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-body-tertiary py-2">
                    <div class="d-flex justify-content-end">
                        {{ $needsTransferPaginated->links('pagination.custom') }}
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
