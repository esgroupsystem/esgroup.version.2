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
            <div class="card border-0 shadow-sm">
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

                <form action="{{ route('stock-transfers.store') }}" method="POST" id="stockTransferForm">
                    @csrf

                    <div class="card-body">

                        @if ($errors->any())
                            <div class="alert alert-danger border-0">
                                <div class="fw-bold mb-1">Please fix the following:</div>
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger border-0">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">From Location <span class="text-danger">*</span></label>
                                <select name="from_location_id" id="from_location_id"
                                    class="form-select @error('from_location_id') is-invalid @enderror" required>
                                    <option value="">Select source location</option>
                                    @foreach ($locations as $location)
                                        <option value="{{ $location->id }}"
                                            {{ old('from_location_id') == $location->id ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('from_location_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">To Location <span class="text-danger">*</span></label>
                                <select name="to_location_id" id="to_location_id"
                                    class="form-select @error('to_location_id') is-invalid @enderror" required>
                                    <option value="">Select destination location</option>
                                    @foreach ($locations as $location)
                                        <option value="{{ $location->id }}"
                                            {{ old('to_location_id') == $location->id ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('to_location_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Transfer Date <span class="text-danger">*</span></label>
                                <input type="date" name="transfer_date"
                                    class="form-control @error('transfer_date') is-invalid @enderror"
                                    value="{{ old('transfer_date', date('Y-m-d')) }}" required>
                                @error('transfer_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Requested By</label>
                                <input type="text" name="requested_by"
                                    class="form-control @error('requested_by') is-invalid @enderror"
                                    value="{{ old('requested_by') }}" placeholder="Enter requester name">
                                @error('requested_by')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Received By</label>
                                <input type="text" name="received_by"
                                    class="form-control @error('received_by') is-invalid @enderror"
                                    value="{{ old('received_by') }}" placeholder="Enter receiver name">
                                @error('received_by')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" rows="2" class="form-control @error('remarks') is-invalid @enderror"
                                    placeholder="Optional remarks...">{{ old('remarks') }}</textarea>
                                @error('remarks')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-0">Transfer Items</h6>
                                <p class="text-muted fs-10 mb-0">
                                    Search products from the selected source location.
                                </p>
                            </div>

                            <button type="button" class="btn btn-success btn-sm" id="addRowBtn">
                                <span class="fas fa-plus me-1"></span> Add Item
                            </button>
                        </div>

                        <div class="table-responsive scrollbar" style="overflow: visible !important;">
                            <table class="table table-bordered align-middle" id="itemsTable">
                                <thead class="bg-200 text-900">
                                    <tr>
                                        <th style="min-width: 320px;">Product</th>
                                        <th style="width: 120px;">Available Stock</th>
                                        <th style="width: 90px;">Unit</th>
                                        <th style="width: 130px;">Part No.</th>
                                        <th style="width: 120px;">Qty</th>
                                        <th style="width: 70px;" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer bg-light d-flex justify-content-between align-items-center">
                        <div class="text-muted fs-10">
                            Make sure the quantity does not exceed the available stock from the selected source location.
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('stock-transfers.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <span class="fas fa-save me-1"></span> Save Transfer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .product-search-wrapper {
            position: relative;
        }

        .product-results {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            z-index: 1055;
            display: none;
            background: #fff;
            border: 1px solid #d8e2ef;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(15, 23, 42, 0.12);
            max-height: 280px;
            overflow-y: auto;
        }

        .product-result-item {
            padding: 0.75rem 0.875rem;
            border-bottom: 1px solid #edf2f9;
            cursor: pointer;
        }

        .product-result-item:last-child {
            border-bottom: 0;
        }

        .product-result-item:hover {
            background-color: #f8f9fa;
        }

        .product-result-item.disabled {
            cursor: not-allowed;
            opacity: 0.7;
        }

        .result-title {
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.875rem;
        }

        .result-meta {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 2px;
            line-height: 1.4;
        }

        .selected-product-box {
            border: 1px solid #d8e2ef;
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 0.625rem 0.75rem;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const itemsBody = document.getElementById('itemsBody');
            const addRowBtn = document.getElementById('addRowBtn');
            const fromLocationInput = document.getElementById('from_location_id');

            function getRowHtml() {
                return `
                    <tr>
                        <td>
                            <input type="hidden" name="product_id[]" class="product-id">
                            <div class="product-search-wrapper">
                                <input type="text" class="form-control form-control-sm product-search-input"
                                    placeholder="Type at least 2 letters to search product..." autocomplete="off">
                                <div class="product-results"></div>
                            </div>
                            <div class="selected-product-box mt-2 d-none"></div>
                            <small class="text-muted d-block mt-1 location-note"></small>
                        </td>

                        <td>
                            <input type="text" class="form-control form-control-sm stock-display bg-light text-center" readonly value="0">
                        </td>

                        <td>
                            <input type="text" class="form-control form-control-sm unit-display bg-light text-center" readonly>
                        </td>

                        <td>
                            <input type="text" class="form-control form-control-sm part-number-display bg-light text-center" readonly>
                        </td>

                        <td>
                            <input type="number" name="qty[]" class="form-control form-control-sm qty-input text-center"
                                min="1" value="1" required>
                        </td>

                        <td class="text-center">
                            <button type="button" class="btn btn-falcon-danger btn-sm remove-row">
                                <span class="fas fa-trash"></span>
                            </button>
                        </td>
                    </tr>
                `;
            }

            function addRow() {
                itemsBody.insertAdjacentHTML('beforeend', getRowHtml());
            }

            function getSelectedProductIds() {
                return Array.from(document.querySelectorAll('.product-id'))
                    .map(input => input.value)
                    .filter(val => val !== '');
            }

            function resetRow(row) {
                row.querySelector('.product-id').value = '';
                row.querySelector('.product-search-input').value = '';
                row.querySelector('.stock-display').value = 0;
                row.querySelector('.unit-display').value = '';
                row.querySelector('.part-number-display').value = '';
                row.querySelector('.qty-input').value = 1;
                row.querySelector('.qty-input').removeAttribute('max');
                row.querySelector('.product-results').innerHTML = '';
                row.querySelector('.product-results').style.display = 'none';
                row.querySelector('.selected-product-box').classList.add('d-none');
                row.querySelector('.selected-product-box').innerHTML = '';
                row.querySelector('.location-note').textContent = '';
            }

            addRowBtn.addEventListener('click', addRow);
            addRow();

            document.addEventListener('click', function(e) {
                if (e.target.closest('.remove-row')) {
                    const rows = itemsBody.querySelectorAll('tr');
                    if (rows.length > 1) {
                        e.target.closest('tr').remove();
                    }
                }
            });

            fromLocationInput.addEventListener('change', function() {
                const rows = itemsBody.querySelectorAll('tr');
                rows.forEach(row => resetRow(row));
            });

            let searchTimeout;

            document.addEventListener('input', function(e) {
                if (!e.target.classList.contains('product-search-input')) return;

                const input = e.target;
                const row = input.closest('tr');
                const resultsBox = row.querySelector('.product-results');
                const keyword = input.value.trim();
                const fromLocationId = fromLocationInput.value;

                clearTimeout(searchTimeout);

                if (!fromLocationId) {
                    resultsBox.innerHTML =
                        `<div class="product-result-item text-warning">Please select source location first.</div>`;
                    resultsBox.style.display = 'block';
                    return;
                }

                if (keyword.length < 2) {
                    resultsBox.style.display = 'none';
                    resultsBox.innerHTML = '';
                    return;
                }

                searchTimeout = setTimeout(() => {
                    const currentRowProductId = row.querySelector('.product-id').value;
                    const selectedIds = getSelectedProductIds().filter(id => id !==
                        currentRowProductId);

                    const url = new URL(`{{ route('stock-transfers.search-products') }}`, window
                        .location.origin);
                    url.searchParams.append('q', keyword);
                    url.searchParams.append('from_location_id', fromLocationId);

                    selectedIds.forEach(id => {
                        url.searchParams.append('exclude_ids[]', id);
                    });

                    fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            resultsBox.innerHTML = '';

                            if (!data.length) {
                                resultsBox.innerHTML =
                                    `<div class="product-result-item text-muted">No products found.</div>`;
                                resultsBox.style.display = 'block';
                                return;
                            }

                            data.forEach(item => {
                                const div = document.createElement('div');
                                div.className = 'product-result-item';
                                div.innerHTML = `
                                    <div class="result-title">${item.name ?? ''}</div>
                                    <div class="result-meta">
                                        Supplier: ${item.supplier_name ?? 'N/A'} |
                                        Unit: ${item.unit ?? 'N/A'} |
                                        Part #: ${item.part_number ?? 'N/A'} |
                                        Stock: ${item.stock ?? 0}
                                    </div>
                                `;

                                div.addEventListener('click', function() {
                                    row.querySelector('.product-id').value =
                                        item.id;
                                    row.querySelector('.stock-display').value =
                                        item.stock ?? 0;
                                    row.querySelector('.unit-display').value =
                                        item.unit ?? '';
                                    row.querySelector('.part-number-display')
                                        .value = item.part_number ?? '';
                                    row.querySelector('.qty-input').max = item
                                        .stock ?? 0;

                                    row.querySelector('.selected-product-box')
                                        .classList.remove('d-none');
                                    row.querySelector('.selected-product-box')
                                        .innerHTML = `
                                        <div class="fw-semibold text-warning">${item.name ?? ''}</div>
                                        <small class="text-muted">
                                            Supplier: ${item.supplier_name ?? 'N/A'} |
                                            Category: ${item.category ?? 'N/A'} |
                                            Stock: ${item.stock ?? 0}
                                            ${item.details ? ' | Details: ' + item.details : ''}
                                        </small>
                                    `;

                                    row.querySelector('.location-note')
                                        .textContent =
                                        'Available from selected source location only.';

                                    input.value = item.name ?? '';
                                    resultsBox.style.display = 'none';
                                });

                                resultsBox.appendChild(div);
                            });

                            resultsBox.style.display = 'block';
                        })
                        .catch(err => {
                            console.error(err);
                            resultsBox.innerHTML =
                                `<div class="product-result-item text-danger">Error loading products.</div>`;
                            resultsBox.style.display = 'block';
                        });
                }, 350);
            });

            document.addEventListener('click', function(e) {
                document.querySelectorAll('.product-results').forEach(box => {
                    const wrapper = box.closest('.product-search-wrapper');
                    if (wrapper && !wrapper.contains(e.target)) {
                        box.style.display = 'none';
                    }
                });
            });

            document.getElementById('stockTransferForm').addEventListener('submit', function(e) {
                let hasError = false;

                document.querySelectorAll('#itemsBody tr').forEach(row => {
                    const productId = row.querySelector('.product-id').value;
                    const qtyInput = row.querySelector('.qty-input');
                    const stock = parseFloat(row.querySelector('.stock-display').value || 0);
                    const qty = parseFloat(qtyInput.value || 0);

                    qtyInput.classList.remove('is-invalid');

                    if (!productId) {
                        hasError = true;
                        row.querySelector('.product-search-input').classList.add('is-invalid');
                    } else {
                        row.querySelector('.product-search-input').classList.remove('is-invalid');
                    }

                    if (qty <= 0 || qty > stock) {
                        hasError = true;
                        qtyInput.classList.add('is-invalid');
                    }
                });

                const fromLocationId = document.getElementById('from_location_id').value;
                const toLocationId = document.getElementById('to_location_id').value;

                if (fromLocationId && toLocationId && fromLocationId === toLocationId) {
                    e.preventDefault();
                    alert('Source location and destination location must be different.');
                    return;
                }

                if (hasError) {
                    e.preventDefault();
                    alert(
                        'Please complete all selected products and make sure quantity does not exceed available stock.'
                    );
                }
            });
        });
    </script>
@endsection
