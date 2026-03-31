@extends('layouts.app')

@section('title', 'IT Department Inventory')

@section('content')
    <div class="container-fluid" data-layout="container">
        <div class="content">

            @if (session('success'))
                <div class="alert alert-success border-0 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">IT Department Inventory</h5>
                        <small class="text-muted">Manage IT parts, accessories, devices, and supplies</small>
                    </div>

                    <a href="{{ route('it-inventory.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Add Item
                    </a>
                </div>

                <div class="card-body">
                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-md-6">
                            <input type="text" name="search" value="{{ $search }}" class="form-control"
                                placeholder="Search item name, brand, model, part number, location">
                        </div>

                        <div class="col-md-3">
                            <select name="category" class="form-select">
                                <option value="">All Categories</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>
                                        {{ $cat }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 d-grid">
                            <button class="btn btn-outline-primary">
                                <i class="fas fa-search me-1"></i> Filter
                            </button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle">
                            <thead class="bg-body-tertiary">
                                <tr>
                                    <th>Item</th>
                                    <th>Category</th>
                                    <th>Brand / Model</th>
                                    <th>Part No.</th>
                                    <th>Stock</th>
                                    <th>Min Stock</th>
                                    <th>Unit</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($items as $item)
                                    @php
                                        $isLowStock =
                                            $item->stock_qty <= $item->minimum_stock && $item->minimum_stock > 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $item->item_name }}</div>
                                            @if ($item->description)
                                                <small
                                                    class="text-muted">{{ \Illuminate\Support\Str::limit($item->description, 60) }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $item->category ?: '—' }}</td>
                                        <td>
                                            <div>{{ $item->brand ?: '—' }}</div>
                                            @if ($item->model)
                                                <small class="text-muted">{{ $item->model }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $item->part_number ?: '—' }}</td>
                                        <td>
                                            <span class="badge {{ $isLowStock ? 'bg-danger' : 'bg-info' }}">
                                                {{ number_format($item->stock_qty) }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($item->minimum_stock) }}</td>
                                        <td>{{ $item->unit }}</td>
                                        <td>{{ $item->location ?: '—' }}</td>
                                        <td>
                                            @if ($item->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('it-inventory.edit', $item->id) }}"
                                                class="btn btn-warning btn-sm">
                                                Edit
                                            </a>

                                            <form action="{{ route('it-inventory.destroy', $item->id) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('Delete this item?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            No inventory item found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $items->links('pagination.custom') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
