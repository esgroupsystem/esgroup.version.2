@extends('layouts.app')

@section('title', 'Edit IT Inventory Item')

@section('content')
    <div class="container-fluid" data-layout="container">
        <div class="content">

            <div class="card shadow-sm border-0">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">Edit IT Inventory Item</h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('it-inventory.update', $item->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Item Name <span class="text-danger">*</span></label>
                                <input type="text" name="item_name" class="form-control"
                                    value="{{ old('item_name', $item->item_name) }}" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Category</label>
                                <input type="text" name="category" class="form-control"
                                    value="{{ old('category', $item->category) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Unit <span class="text-danger">*</span></label>
                                <input type="text" name="unit" class="form-control"
                                    value="{{ old('unit', $item->unit) }}" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Brand</label>
                                <input type="text" name="brand" class="form-control"
                                    value="{{ old('brand', $item->brand) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Model</label>
                                <input type="text" name="model" class="form-control"
                                    value="{{ old('model', $item->model) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Part Number</label>
                                <input type="text" name="part_number" class="form-control"
                                    value="{{ old('part_number', $item->part_number) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" class="form-control"
                                    value="{{ old('location', $item->location) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Stock Qty <span class="text-danger">*</span></label>
                                <input type="number" name="stock_qty" class="form-control"
                                    value="{{ old('stock_qty', $item->stock_qty) }}" min="0" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Minimum Stock</label>
                                <input type="number" name="minimum_stock" class="form-control"
                                    value="{{ old('minimum_stock', $item->minimum_stock) }}" min="0">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" rows="3" class="form-control">{{ old('description', $item->description) }}</textarea>
                            </div>

                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                        id="is_active" {{ old('is_active', $item->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active Item</label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                Update Item
                            </button>
                            <a href="{{ route('it-inventory.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection
