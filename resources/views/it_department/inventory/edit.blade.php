@extends('layouts.app')

@section('title', 'Edit IT Inventory Item')

@section('content')
    <div class="container-fluid" data-layout="container">
        <div class="content">

            {{-- HEADER --}}
            <div class="card mb-3">
                <div class="card-body py-3">
                    <div class="row flex-between-center g-3">
                        <div class="col-auto">
                            <div class="d-flex align-items-center">
                                <span class="fas fa-edit text-primary fs-5 me-3"></span>
                                <div>
                                    <h5 class="mb-0">Edit IT Inventory Item</h5>
                                    <p class="fs-10 mb-0 text-600">
                                        Update item details and stock information.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-auto">
                            <a href="{{ route('it-inventory.index') }}" class="btn btn-falcon-default btn-sm">
                                <span class="fas fa-arrow-left me-1"></span>Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('it-inventory.update', $item->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- ITEM INFO --}}
                <div class="card mb-3">
                    <div class="card-header bg-body-tertiary py-3">
                        <h6 class="mb-0">
                            <span class="fas fa-box text-primary me-2"></span>Item Information
                        </h6>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-lg-6">
                                <label class="form-label">Item Name <span class="text-danger">*</span></label>
                                <input type="text" name="item_name" class="form-control form-control-sm"
                                    value="{{ old('item_name', $item->item_name) }}" required>
                            </div>

                            <div class="col-lg-3">
                                <label class="form-label">Category</label>
                                <input type="text" name="category" class="form-control form-control-sm"
                                    value="{{ old('category', $item->category) }}">
                            </div>

                            <div class="col-lg-3">
                                <label class="form-label">Unit <span class="text-danger">*</span></label>
                                <input type="text" name="unit" class="form-control form-control-sm"
                                    value="{{ old('unit', $item->unit) }}" required>
                            </div>

                            <div class="col-lg-3">
                                <label class="form-label">Brand</label>
                                <input type="text" name="brand" class="form-control form-control-sm"
                                    value="{{ old('brand', $item->brand) }}">
                            </div>

                            <div class="col-lg-3">
                                <label class="form-label">Model</label>
                                <input type="text" name="model" class="form-control form-control-sm"
                                    value="{{ old('model', $item->model) }}">
                            </div>

                            <div class="col-lg-3">
                                <label class="form-label">Part Number</label>
                                <input type="text" name="part_number" class="form-control form-control-sm"
                                    value="{{ old('part_number', $item->part_number) }}">
                            </div>

                            <div class="col-lg-3">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" class="form-control form-control-sm"
                                    value="{{ old('location', $item->location) }}">
                            </div>

                        </div>
                    </div>
                </div>

                {{-- STOCK --}}
                <div class="card mb-3">
                    <div class="card-header bg-body-tertiary py-3">
                        <h6 class="mb-0">
                            <span class="fas fa-layer-group text-primary me-2"></span>Stock Details
                        </h6>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-lg-3">
                                <label class="form-label">Stock Qty <span class="text-danger">*</span></label>
                                <input type="number" name="stock_qty" class="form-control form-control-sm"
                                    value="{{ old('stock_qty', $item->stock_qty) }}" min="0" required>
                            </div>

                            <div class="col-lg-3">
                                <label class="form-label">Minimum Stock</label>
                                <input type="number" name="minimum_stock" class="form-control form-control-sm"
                                    value="{{ old('minimum_stock', $item->minimum_stock) }}" min="0">
                            </div>

                            <div class="col-lg-6 d-flex align-items-end">
                                <div class="form-check form-switch mb-1">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                        id="is_active" {{ old('is_active', $item->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active Item</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" rows="3" class="form-control form-control-sm">{{ old('description', $item->description) }}</textarea>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="card">
                    <div class="card-footer bg-body-tertiary text-end">
                        <a href="{{ route('it-inventory.index') }}" class="btn btn-falcon-default btn-sm">
                            Cancel
                        </a>

                        <button type="submit" class="btn btn-primary btn-sm">
                            <span class="fas fa-save me-1"></span>Update Item
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </div>
@endsection
