@extends('layouts.app')

@section('title', 'IT Department Inventory')

@section('content')
    <div class="container-fluid" data-layout="container">
        <div class="content">

            @if (session('success'))
                <div class="alert alert-success border-0">
                    <span class="fas fa-check-circle me-2"></span>{{ session('success') }}
                </div>
            @endif

            <div class="card mb-3">
                <div class="card-body py-3">
                    <div class="row flex-between-center g-3">
                        <div class="col-auto">
                            <div class="d-flex align-items-center">
                                <span class="fas fa-boxes text-primary fs-5 me-3"></span>
                                <div>
                                    <h5 class="mb-0">IT Department Inventory</h5>
                                    <p class="fs-10 mb-0 text-600">
                                        Manage IT parts, accessories, devices, and supplies.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-auto">
                            <a href="{{ route('it-inventory.create') }}" class="btn btn-primary btn-sm">
                                <span class="fas fa-plus me-1"></span>Add Item
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-body-tertiary py-3">
                    <form method="GET" action="{{ route('it-inventory.index') }}">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white">
                                        <span class="fas fa-search"></span>
                                    </span>
                                    <input type="search" name="search" value="{{ $search }}" class="form-control"
                                        placeholder="Search item, brand, model, part number, location...">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <select name="category" class="form-select form-select-sm">
                                    <option value="">All Categories</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat }}" @selected($category === $cat)>
                                            {{ $cat }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 d-flex gap-2">
                                <button class="btn btn-falcon-primary btn-sm flex-fill">
                                    Filter
                                </button>

                                <a href="{{ route('it-inventory.index') }}" class="btn btn-falcon-default btn-sm">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive scrollbar">
                        <table class="table table-sm table-hover mb-0 inventory-table">
                            <thead class="bg-200">
                                <tr>
                                    <th class="ps-3">Item</th>
                                    <th>Category</th>
                                    <th>Brand / Model</th>
                                    <th>Part No.</th>
                                    <th class="text-center">Stock</th>
                                    <th class="text-center">Min</th>
                                    <th>Unit</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($items as $item)
                                    @php
                                        $isLowStock =
                                            $item->minimum_stock > 0 && $item->stock_qty <= $item->minimum_stock;
                                        $isOutStock = $item->stock_qty <= 0;
                                    @endphp

                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-semibold text-900 item-name">
                                                {{ $item->item_name }}
                                            </div>

                                            @if ($item->description)
                                                <div class="fs-11 text-600 item-desc">
                                                    {{ Str::limit($item->description, 65) }}
                                                </div>
                                            @endif
                                        </td>

                                        <td>
                                            <span class="badge rounded-pill badge-subtle-primary">
                                                {{ $item->category ?: 'Uncategorized' }}
                                            </span>
                                        </td>

                                        <td>
                                            <div class="text-800">{{ $item->brand ?: '—' }}</div>
                                            @if ($item->model)
                                                <div class="fs-11 text-600">{{ $item->model }}</div>
                                            @endif
                                        </td>

                                        <td class="text-600">
                                            {{ $item->part_number ?: '—' }}
                                        </td>

                                        <td class="text-center">
                                            @if ($isOutStock)
                                                <span class="badge rounded-pill badge-subtle-danger">
                                                    Out
                                                </span>
                                            @elseif ($isLowStock)
                                                <span class="badge rounded-pill badge-subtle-warning">
                                                    {{ number_format($item->stock_qty) }} Low
                                                </span>
                                            @else
                                                <span class="badge rounded-pill badge-subtle-success">
                                                    {{ number_format($item->stock_qty) }}
                                                </span>
                                            @endif
                                        </td>

                                        <td class="text-center text-600">
                                            {{ number_format($item->minimum_stock) }}
                                        </td>

                                        <td>{{ $item->unit }}</td>

                                        <td class="text-600">
                                            {{ $item->location ?: '—' }}
                                        </td>

                                        <td>
                                            @if ($item->is_active)
                                                <span class="badge rounded-pill badge-subtle-success">Active</span>
                                            @else
                                                <span class="badge rounded-pill badge-subtle-secondary">Inactive</span>
                                            @endif
                                        </td>

                                        <td class="text-end pe-3">
                                            <div class="btn-group">
                                                <a href="{{ route('it-inventory.edit', $item->id) }}"
                                                    class="btn btn-falcon-warning btn-sm">
                                                    <span class="fas fa-edit"></span>
                                                </a>

                                                <form action="{{ route('it-inventory.destroy', $item->id) }}"
                                                    method="POST" onsubmit="return confirm('Delete this item?')">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit" class="btn btn-falcon-danger btn-sm">
                                                        <span class="fas fa-trash"></span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5">
                                            <span class="fas fa-box-open text-300 fs-4 mb-3 d-block"></span>
                                            <h6 class="mb-1">No inventory item found</h6>
                                            <p class="text-600 mb-0">Try changing your search or category filter.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-body-tertiary py-3">
                    <div class="row flex-between-center g-2">
                        <div class="col-auto">
                            <small class="text-600">
                                Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }}
                                of {{ $items->total() }} items
                            </small>
                        </div>

                        <div class="col-auto">
                            {{ $items->links('pagination.custom') }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('styles')
    <style>
        .inventory-table {
            font-size: .83rem;
            min-width: 1050px;
        }

        .inventory-table thead th {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .02em;
            color: #5e6e82;
            padding-top: .75rem;
            padding-bottom: .75rem;
            white-space: nowrap;
        }

        .inventory-table tbody td {
            padding-top: .65rem;
            padding-bottom: .65rem;
            vertical-align: middle;
        }

        .item-name {
            max-width: 280px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .item-desc {
            max-width: 320px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .inventory-table .btn-group .btn {
            border-radius: .25rem !important;
            margin-left: .25rem;
        }

        .pagination {
            margin-bottom: 0 !important;
            font-size: 13px !important;
        }

        .pagination .page-link {
            padding: 4px 9px !important;
            font-size: 13px !important;
            border-radius: 6px !important;
        }
    </style>
@endpush
