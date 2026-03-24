@extends('layouts.app')
@section('title', 'New Parts Out')

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
            <form action="{{ route('parts-out.store') }}" method="POST" enctype="multipart/form-data" id="partsOutForm">
                @csrf

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">
                                <span class="fas fa-tools text-primary me-2"></span>
                                New Parts Out
                            </h5>
                            <p class="text-muted fs-10 mb-0 mt-1">
                                Encode issued / installed parts and automatically deduct stock
                            </p>
                        </div>

                        <a href="{{ route('parts-out.index') }}" class="btn btn-falcon-default btn-sm">
                            <span class="fas fa-arrow-left me-1"></span> Back
                        </a>
                    </div>

                    <div class="card-body">
                        @if (session('error'))
                            <div class="alert alert-danger py-2">{{ session('error') }}</div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <div class="fw-bold mb-1">Please fix the following:</div>
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Vehicle</label>
                                <select name="vehicle_id" class="form-select">
                                    <option value="">Select Vehicle</option>
                                    @foreach ($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}"
                                            {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                            {{ $vehicle->plate_number ?? 'N/A' }}
                                            @if (!empty($vehicle->body_number))
                                                | {{ $vehicle->body_number }}
                                            @endif
                                            @if (!empty($vehicle->name))
                                                | {{ $vehicle->name }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Source Garage / Location <span
                                        class="text-danger">*</span></label>
                                <select name="location_id" id="location_id" class="form-select" required>
                                    <option value="">Select Location</option>
                                    @foreach ($locations as $location)
                                        <option value="{{ $location->id }}"
                                            {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">This is the stock source to deduct from.</small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Mechanic <span class="text-danger">*</span></label>
                                <input type="text" name="mechanic_name" class="form-control"
                                    value="{{ old('mechanic_name') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Date Issued <span class="text-danger">*</span></label>
                                <input type="date" name="issued_date" class="form-control"
                                    value="{{ old('issued_date', now()->format('Y-m-d')) }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Requested By</label>
                                <input type="text" name="requested_by" class="form-control"
                                    value="{{ old('requested_by') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Job Order No.</label>
                                <input type="text" name="job_order_no" class="form-control"
                                    value="{{ old('job_order_no') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Odometer</label>
                                <input type="text" name="odometer" class="form-control" value="{{ old('odometer') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Purpose / Work Details</label>
                                <textarea name="purpose" rows="3" class="form-control">{{ old('purpose') }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" rows="3" class="form-control">{{ old('remarks') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">
                                <span class="fas fa-boxes text-primary me-2"></span>
                                Parts / Items Used
                            </h6>
                            <p class="text-muted fs-10 mb-0 mt-1">
                                Add one or more products used for installation or repair
                            </p>
                        </div>

                        <button type="button" class="btn btn-primary btn-sm" id="addRowBtn">
                            <span class="fas fa-plus me-1"></span> Add Item
                        </button>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive scrollbar" style="overflow: visible !important;">
                            <table class="table table-bordered align-middle fs-10" id="itemsTable">
                                <thead class="bg-200 text-900">
                                    <tr>
                                        <th style="min-width: 260px;">Product</th>
                                        <th style="width: 100px;">Stock</th>
                                        <th style="width: 90px;">Unit</th>
                                        <th style="width: 120px;">Part No.</th>
                                        <th style="width: 110px;">Qty Used</th>
                                        <th style="width: 110px;">Stock After</th>
                                        <th>Remarks</th>
                                        <th style="width: 70px;" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary">
                                <span class="fas fa-save me-1"></span> Save Parts Out
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <style>
        .product-search-wrapper {
            position: relative;
        }

        .product-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1055;
            background: #fff;
            border: 1px solid #d8e2ef;
            border-radius: 0.375rem;
            box-shadow: 0 0.5rem 1rem rgba(15, 23, 42, 0.08);
            max-height: 280px;
            overflow-y: auto;
            display: none;
        }

        .product-result-item {
            padding: 10px 12px;
            border-bottom: 1px solid #edf2f9;
            cursor: pointer;
        }

        .product-result-item:hover {
            background: #f8f9fa;
        }

        .product-result-item:last-child {
            border-bottom: 0;
        }

        .result-title {
            font-weight: 600;
            color: #2c3e50;
            font-size: 12px;
        }

        .result-meta {
            font-size: 11px;
            color: #6c757d;
            margin-top: 2px;
        }

        .selected-product-box {
            background: #f8f9fa;
            border: 1px solid #d8e2ef;
            border-radius: 0.375rem;
            padding: 8px 10px;
            min-height: 38px;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const itemsBody = document.getElementById('itemsBody');
            const addRowBtn = document.getElementById('addRowBtn');
            const locationSelect = document.getElementById('location_id');
            const form = document.getElementById('partsOutForm');

            function getRowHtml(index) {
                return `
                    <tr>
                        <td>
                            <input type="hidden" name="product_id[]" class="product-id">
                            <div class="product-search-wrapper">
                                <input type="text" class="form-control form-control-sm product-search-input"
                                    placeholder="Type at least 2 letters to search product...">
                                <div class="product-results"></div>
                            </div>
                            <div class="selected-product-box mt-2 d-none"></div>
                        </td>

                        <td>
                            <input type="text" class="form-control form-control-sm stock-display bg-light" readonly>
                        </td>

                        <td>
                            <input type="text" class="form-control form-control-sm unit-display bg-light" readonly>
                        </td>

                        <td>
                            <input type="text" class="form-control form-control-sm part-number-display bg-light" readonly>
                        </td>

                        <td>
                            <input type="number" name="qty_used[]" class="form-control form-control-sm qty-used"
                                min="1" value="1" required>
                        </td>

                        <td>
                            <input type="text" class="form-control form-control-sm stock-after-display bg-light" readonly>
                        </td>

                        <td>
                            <input type="text" name="item_remarks[]" class="form-control form-control-sm"
                                placeholder="Optional remarks">
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
                itemsBody.insertAdjacentHTML('beforeend', getRowHtml(Date.now()));
            }

            function resetRows() {
                itemsBody.innerHTML = '';
                addRow();
            }

            addRowBtn.addEventListener('click', addRow);
            addRow();

            locationSelect.addEventListener('change', function() {
                resetRows();
            });

            function getSelectedProductIds(currentRow = null) {
                return Array.from(document.querySelectorAll('.product-id'))
                    .filter(input => currentRow ? input !== currentRow.querySelector('.product-id') : true)
                    .map(input => input.value)
                    .filter(val => val !== '');
            }

            function updateStockAfter(row) {
                const stock = parseInt(row.querySelector('.stock-display').value || 0);
                const qty = parseInt(row.querySelector('.qty-used').value || 0);
                const after = stock - qty;

                row.querySelector('.stock-after-display').value = isNaN(after) ? '' : after;

                if (after < 0) {
                    row.querySelector('.stock-after-display').classList.add('is-invalid');
                    row.querySelector('.qty-used').classList.add('is-invalid');
                } else {
                    row.querySelector('.stock-after-display').classList.remove('is-invalid');
                    row.querySelector('.qty-used').classList.remove('is-invalid');
                }
            }

            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('qty-used')) {
                    const row = e.target.closest('tr');
                    updateStockAfter(row);
                }
            });

            document.addEventListener('click', function(e) {
                if (e.target.closest('.remove-row')) {
                    const rows = itemsBody.querySelectorAll('tr');
                    if (rows.length > 1) {
                        e.target.closest('tr').remove();
                    }
                }
            });

            let searchTimeout;

            document.addEventListener('input', function(e) {
                if (!e.target.classList.contains('product-search-input')) return;

                const input = e.target;
                const row = input.closest('tr');
                const resultsBox = row.querySelector('.product-results');
                const selectedBox = row.querySelector('.selected-product-box');
                const keyword = input.value.trim();

                // clear selected product if user edits search again
                row.querySelector('.product-id').value = '';
                row.querySelector('.stock-display').value = '';
                row.querySelector('.unit-display').value = '';
                row.querySelector('.part-number-display').value = '';
                row.querySelector('.stock-after-display').value = '';
                selectedBox.classList.add('d-none');
                selectedBox.innerHTML = '';

                clearTimeout(searchTimeout);

                if (keyword.length < 2) {
                    resultsBox.style.display = 'none';
                    resultsBox.innerHTML = '';
                    return;
                }

                searchTimeout = setTimeout(() => {
                    const locationId = locationSelect.value;

                    if (!locationId) {
                        resultsBox.innerHTML =
                            `<div class="product-result-item text-danger">Please select source location first.</div>`;
                        resultsBox.style.display = 'block';
                        return;
                    }

                    const selectedIds = getSelectedProductIds(row);

                    fetch(`{{ route('parts-out.search-products') }}?search=${encodeURIComponent(keyword)}&location_id=${encodeURIComponent(locationId)}&exclude_ids=${encodeURIComponent(selectedIds.join(','))}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            resultsBox.innerHTML = '';

                            if (!Array.isArray(data) || !data.length) {
                                resultsBox.innerHTML =
                                    `<div class="product-result-item text-muted">No products found in selected location.</div>`;
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
                                    const locationText = locationSelect
                                        .selectedOptions[0]?.text ?? '';

                                    row.querySelector('.product-id').value =
                                        item.id;
                                    row.querySelector('.stock-display').value =
                                        item.stock ?? 0;
                                    row.querySelector('.unit-display').value =
                                        item.unit ?? '';
                                    row.querySelector('.part-number-display')
                                        .value = item.part_number ?? '';
                                    row.querySelector('.selected-product-box')
                                        .classList.remove('d-none');
                                    row.querySelector('.selected-product-box')
                                        .innerHTML = `
                                        <div class="fw-semibold text-primary">${item.name ?? ''}</div>
                                        <small class="text-muted d-block">
                                            Supplier: ${item.supplier_name ?? 'N/A'} |
                                            Category: ${item.category ?? 'N/A'}
                                        </small>
                                        <small class="text-primary d-block">
                                            Stock in ${locationText}: ${item.stock ?? 0}
                                        </small>
                                    `;
                                    input.value = item.name ?? '';
                                    resultsBox.style.display = 'none';
                                    updateStockAfter(row);
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
                    if (!box.closest('.product-search-wrapper').contains(e.target)) {
                        box.style.display = 'none';
                    }
                });
            });

            form.addEventListener('submit', function(e) {
                const locationId = locationSelect.value;

                if (!locationId) {
                    e.preventDefault();
                    alert('Please select source garage / location first.');
                    return;
                }

                const rows = Array.from(itemsBody.querySelectorAll('tr'));
                let hasError = false;

                rows.forEach(row => {
                    const productId = row.querySelector('.product-id').value;
                    const qtyInput = row.querySelector('.qty-used');
                    const stockAfter = parseInt(row.querySelector('.stock-after-display').value ||
                        0);

                    if (!productId) {
                        hasError = true;
                    }

                    if (!qtyInput.value || parseInt(qtyInput.value) <= 0) {
                        hasError = true;
                        qtyInput.classList.add('is-invalid');
                    }

                    if (stockAfter < 0) {
                        hasError = true;
                        row.querySelector('.stock-after-display').classList.add('is-invalid');
                        qtyInput.classList.add('is-invalid');
                    }
                });

                if (hasError) {
                    e.preventDefault();
                    alert(
                        'Please complete all item rows correctly. Check product selection and stock quantity.'
                    );
                }
            });
        });
    </script>
@endsection
