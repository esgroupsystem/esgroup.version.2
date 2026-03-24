@extends('layouts.app')
@section('title', 'New Stock Transfer')

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
            <form action="{{ route('stock-transfers.store') }}" method="POST" id="stockTransferForm">
                @csrf

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">
                                <span class="fas fa-exchange-alt text-warning me-2"></span>
                                New Stock Transfer
                            </h5>
                            <p class="text-muted fs-10 mb-0 mt-1">
                                Transfer available stock from one location to another
                            </p>
                        </div>

                        <a href="{{ route('stock-transfers.index') }}" class="btn btn-falcon-default btn-sm">
                            <span class="fas fa-arrow-left me-1"></span> Back
                        </a>
                    </div>

                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">From Location <span class="text-danger">*</span></label>
                                <select name="from_location_id" id="from_location_id" class="form-select" required>
                                    <option value="">Select source location</option>
                                    @foreach ($locations as $location)
                                        <option value="{{ $location->id }}"
                                            {{ old('from_location_id') == $location->id ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">To Location <span class="text-danger">*</span></label>
                                <select name="to_location_id" id="to_location_id" class="form-select" required>
                                    <option value="">Select destination location</option>
                                    @foreach ($locations as $location)
                                        <option value="{{ $location->id }}"
                                            {{ old('to_location_id') == $location->id ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Transfer Date <span class="text-danger">*</span></label>
                                <input type="date" name="transfer_date" class="form-control"
                                    value="{{ old('transfer_date', date('Y-m-d')) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Requested By</label>
                                <input type="text" name="requested_by" class="form-control"
                                    value="{{ old('requested_by') }}" placeholder="Enter requester name">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Received By</label>
                                <input type="text" name="received_by" class="form-control"
                                    value="{{ old('received_by') }}" placeholder="Enter receiver name">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" rows="2" class="form-control" placeholder="Optional remarks...">{{ old('remarks') }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-0">Transfer Items</h6>
                                <small class="text-muted">Search products from the selected source location</small>
                            </div>

                            <button type="button" class="btn btn-success btn-sm" id="addRowBtn">
                                <span class="fas fa-plus me-1"></span> Add Item
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="itemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 45%;">Product Search</th>
                                        <th style="width: 15%;">Available Stock</th>
                                        <th style="width: 15%;">Unit</th>
                                        <th style="width: 15%;">Qty</th>
                                        <th style="width: 10%;" class="text-center">Remove</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody">
                                </tbody>
                            </table>
                        </div>

                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary">
                                <span class="fas fa-save me-1"></span> Save Transfer
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addRowBtn = document.getElementById('addRowBtn');
            const tableBody = document.getElementById('itemsTableBody');
            const fromLocation = document.getElementById('from_location_id');
            let rowIndex = 0;

            function getSelectedProductIds() {
                return Array.from(document.querySelectorAll('.product-id-input'))
                    .map(el => el.value)
                    .filter(val => val !== '');
            }

            function createRow() {
                rowIndex++;

                const tr = document.createElement('tr');
                tr.innerHTML = `
            <td>
                <div class="position-relative">
                    <input type="text" class="form-control product-search-input" placeholder="Type at least 2 letters to search..." autocomplete="off">
                    <input type="hidden" name="product_id[]" class="product-id-input">
                    <div class="list-group position-absolute w-100 shadow-sm search-results" style="z-index: 20; display:none; max-height:250px; overflow:auto;"></div>
                    <small class="text-muted product-meta d-block mt-1"></small>
                </div>
            </td>
            <td>
                <input type="text" class="form-control available-stock text-center" readonly value="0">
            </td>
            <td>
                <input type="text" class="form-control product-unit text-center" readonly>
            </td>
            <td>
                <input type="number" name="qty[]" class="form-control qty-input text-center" min="1" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm remove-row">
                    <span class="fas fa-trash"></span>
                </button>
            </td>
        `;

                tableBody.appendChild(tr);

                const searchInput = tr.querySelector('.product-search-input');
                const productIdInput = tr.querySelector('.product-id-input');
                const searchResults = tr.querySelector('.search-results');
                const availableStock = tr.querySelector('.available-stock');
                const productUnit = tr.querySelector('.product-unit');
                const productMeta = tr.querySelector('.product-meta');
                const qtyInput = tr.querySelector('.qty-input');

                let searchTimeout = null;

                searchInput.addEventListener('keyup', function() {
                    const query = this.value.trim();
                    const locationId = fromLocation.value;
                    const excludeIds = getSelectedProductIds().filter(id => id !== productIdInput.value);

                    if (!locationId) {
                        searchResults.style.display = 'none';
                        productMeta.textContent = 'Please select source location first.';
                        return;
                    }

                    if (query.length < 2) {
                        searchResults.style.display = 'none';
                        return;
                    }

                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        const url = new URL(`{{ route('stock-transfers.search-products') }}`,
                            window.location.origin);
                        url.searchParams.append('q', query);
                        url.searchParams.append('from_location_id', locationId);
                        excludeIds.forEach(id => url.searchParams.append('exclude_ids[]', id));

                        fetch(url, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(response => response.json())
                            .then(products => {
                                searchResults.innerHTML = '';

                                if (!products.length) {
                                    searchResults.innerHTML =
                                        `<button type="button" class="list-group-item list-group-item-action text-muted">No matching products found.</button>`;
                                    searchResults.style.display = 'block';
                                    return;
                                }

                                products.forEach(product => {
                                    const item = document.createElement('button');
                                    item.type = 'button';
                                    item.className =
                                        'list-group-item list-group-item-action';

                                    item.innerHTML = `
                            <div class="fw-semibold">${product.name}</div>
                            <div class="small text-muted">
                                ${product.part_number ? 'Part #: ' + product.part_number + ' • ' : ''}
                                ${product.supplier_name ? 'Supplier: ' + product.supplier_name + ' • ' : ''}
                                ${product.category ? 'Category: ' + product.category + ' • ' : ''}
                                Stock: ${product.stock}
                            </div>
                        `;

                                    item.addEventListener('click', function() {
                                        searchInput.value = product.name;
                                        productIdInput.value = product.id;
                                        availableStock.value = product.stock;
                                        productUnit.value = product.unit ?? '';
                                        productMeta.textContent = [
                                            product.part_number ?
                                            'Part #: ' + product
                                            .part_number : null,
                                            product.supplier_name ?
                                            'Supplier: ' + product
                                            .supplier_name : null,
                                            product.category ?
                                            'Category: ' + product
                                            .category : null,
                                            product.details ? 'Details: ' +
                                            product.details : null
                                        ].filter(Boolean).join(' | ');

                                        qtyInput.max = product.stock;
                                        searchResults.style.display = 'none';
                                    });

                                    searchResults.appendChild(item);
                                });

                                searchResults.style.display = 'block';
                            })
                            .catch(error => {
                                console.error('Search error:', error);
                                searchResults.style.display = 'none';
                            });
                    }, 300);
                });

                document.addEventListener('click', function(e) {
                    if (!tr.contains(e.target)) {
                        searchResults.style.display = 'none';
                    }
                });

                tr.querySelector('.remove-row').addEventListener('click', function() {
                    tr.remove();
                });
            }

            addRowBtn.addEventListener('click', createRow);

            fromLocation.addEventListener('change', function() {
                tableBody.innerHTML = '';
            });

            createRow();
        });
    </script>
@endsection
