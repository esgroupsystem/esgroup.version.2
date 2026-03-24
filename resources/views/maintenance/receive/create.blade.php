@extends('layouts.app')
@section('title', 'New Receiving')

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
                            <span class="fas fa-truck-loading text-primary me-2"></span>
                            New Receiving
                        </h5>
                        <p class="text-muted fs-10 mb-0 mt-1">
                            Encode delivered items and automatically update stocks
                        </p>
                    </div>

                    <a href="{{ route('receivings.index') }}" class="btn btn-falcon-default btn-sm">
                        <span class="fas fa-arrow-left me-1"></span> Back
                    </a>
                </div>

                <form action="{{ route('receivings.store') }}" method="POST" enctype="multipart/form-data">
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

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Delivered By <span class="text-danger">*</span></label>
                                <input type="text" name="delivered_by"
                                    class="form-control @error('delivered_by') is-invalid @enderror" required
                                    value="{{ old('delivered_by') }}" placeholder="Enter supplier, driver, or person name">
                                @error('delivered_by')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Delivery Date <span class="text-danger">*</span></label>
                                <input type="date" name="delivery_date"
                                    class="form-control @error('delivery_date') is-invalid @enderror" required
                                    value="{{ old('delivery_date', date('Y-m-d')) }}">
                                @error('delivery_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Proof of Delivery</label>
                                <input type="file" name="proof_image" id="proofImageInput"
                                    class="form-control @error('proof_image') is-invalid @enderror"
                                    accept="image/png,image/jpeg,image/jpg">
                                @error('proof_image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Upload receipt, DR, or delivery proof.</small>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="2"
                                    placeholder="Optional remarks...">{{ old('remarks') }}</textarea>
                                @error('remarks')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12" id="proofPreviewWrapper" style="display: none;">
                                <div class="border rounded-3 p-3 bg-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="fw-semi-bold text-900">
                                            <span class="fas fa-image text-primary me-1"></span>
                                            Proof Preview
                                        </div>
                                        <button type="button" class="btn btn-falcon-danger btn-sm"
                                            onclick="removeProofPreview()">
                                            <span class="fas fa-times me-1"></span> Remove
                                        </button>
                                    </div>

                                    <div class="text-center">
                                        <img id="proofPreview" src="" alt="Proof Preview"
                                            class="img-fluid rounded border" style="max-height: 320px;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-0">Products Delivered</h6>
                                <p class="text-muted fs-10 mb-0">You can add multiple products in one receiving record.</p>
                            </div>

                            <button type="button" class="btn btn-success btn-sm" id="addRowBtn">
                                <span class="fas fa-plus me-1"></span> Add Product
                            </button>
                        </div>

                        <div class="table-responsive scrollbar" style="overflow: visible !important;">
                            <table class="table table-bordered align-middle" id="itemsTable">
                                <thead class="bg-200 text-900">
                                    <tr>
                                        <th style="min-width: 280px;">Product</th>
                                        <th style="width: 110px;">Current Stock</th>
                                        <th style="width: 90px;">Unit</th>
                                        <th style="width: 130px;">Part No.</th>
                                        <th style="width: 120px;">Qty Delivered</th>
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
                            Make sure the delivered quantities match the receipt before saving.
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('receivings.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <span class="fas fa-save me-1"></span> Save Receiving
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const itemsBody = document.getElementById('itemsBody');
            const addRowBtn = document.getElementById('addRowBtn');

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
                            <input type="number" name="qty_delivered[]" class="form-control form-control-sm"
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

            addRowBtn.addEventListener('click', addRow);
            addRow();

            function getSelectedProductIds() {
                return Array.from(document.querySelectorAll('.product-id'))
                    .map(input => input.value)
                    .filter(val => val !== '');
            }

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
                const keyword = input.value.trim();

                clearTimeout(searchTimeout);

                if (keyword.length < 2) {
                    resultsBox.style.display = 'none';
                    resultsBox.innerHTML = '';
                    return;
                }

                searchTimeout = setTimeout(() => {
                    const selectedIds = getSelectedProductIds()
                        .filter(id => id !== row.querySelector('.product-id').value);

                    fetch(`{{ route('receivings.search-products') }}?search=${encodeURIComponent(keyword)}&exclude_ids=${encodeURIComponent(selectedIds.join(','))}`, {
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
                                    row.querySelector('.selected-product-box')
                                        .classList.remove('d-none');
                                    row.querySelector('.selected-product-box')
                                        .innerHTML = `
                                        <div class="fw-semibold text-primary">${item.name ?? ''}</div>
                                        <small class="text-muted">
                                            Supplier: ${item.supplier_name ?? 'N/A'} |
                                            Category: ${item.category ?? 'N/A'} |
                                            Stock: ${item.stock ?? 0}
                                        </small>
                                    `;
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
                    if (!box.closest('.product-search-wrapper').contains(e.target)) {
                        box.style.display = 'none';
                    }
                });
            });

            const proofInput = document.getElementById('proofImageInput');
            const previewWrapper = document.getElementById('proofPreviewWrapper');
            const previewImage = document.getElementById('proofPreview');

            if (proofInput) {
                proofInput.addEventListener('change', function(event) {
                    const file = event.target.files[0];

                    if (!file) {
                        previewWrapper.style.display = 'none';
                        previewImage.src = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        previewWrapper.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                });
            }
        });

        function removeProofPreview() {
            const proofInput = document.getElementById('proofImageInput');
            const previewWrapper = document.getElementById('proofPreviewWrapper');
            const previewImage = document.getElementById('proofPreview');

            proofInput.value = '';
            previewImage.src = '';
            previewWrapper.style.display = 'none';
        }
    </script>
@endsection
