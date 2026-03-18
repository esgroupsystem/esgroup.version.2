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

                            <button type="button" class="btn btn-success btn-sm" onclick="addRow()">
                                <span class="fas fa-plus me-1"></span> Add Product
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="itemsTable">
                                <thead class="bg-200 text-900">
                                    <tr>
                                        <th style="width: 55%;">Product</th>
                                        <th style="width: 20%;">Current Stock</th>
                                        <th style="width: 15%;">Qty Delivered</th>
                                        <th style="width: 10%;" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select name="product_id[]" class="form-select product-select" required>
                                                <option value="">Select Product</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control stock-display bg-light" value="—"
                                                readonly>
                                        </td>
                                        <td>
                                            <input type="number" name="qty_delivered[]" class="form-control" min="1"
                                                required>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">
                                                Remove
                                            </button>
                                        </td>
                                    </tr>
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
@endsection

@php
    $productData = $products
        ->map(function ($p) {
            return [
                'id' => (string) $p->id,
                'name' => $p->product_name,
                'supplier_name' => $p->supplier_name,
                'category' => optional($p->category)->name,
                'unit' => $p->unit,
                'part_number' => $p->part_number,
                'details' => $p->details,
                'stock' => (string) $p->stock_qty,
            ];
        })
        ->values();
@endphp

@push('scripts')
    <script>
        const productData = @json($productData);

        const productMap = {};
        productData.forEach(product => {
            productMap[String(product.id)] = product;
        });

        function escapeHtml(value) {
            if (value === null || value === undefined) return '';
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function buildProductChoiceHtml(product) {
            if (!product) return '';

            let meta = [];

            if (product.supplier_name) {
                meta.push(`<span class="product-badge">Supplier: ${escapeHtml(product.supplier_name)}</span>`);
            }

            if (product.unit) {
                meta.push(`<span class="product-badge">Unit: ${escapeHtml(product.unit)}</span>`);
            }

            if (product.part_number) {
                meta.push(`<span class="product-badge">Part #: ${escapeHtml(product.part_number)}</span>`);
            }

            if (product.category) {
                meta.push(`<span class="product-badge">Category: ${escapeHtml(product.category)}</span>`);
            }

            return `
            <div class="product-choice-wrap">
                <div class="product-choice-title">${escapeHtml(product.name)}</div>
                ${meta.length ? `<div class="product-choice-meta">${meta.join('')}</div>` : ''}
                ${product.details ? `<div class="product-choice-details">${escapeHtml(product.details)}</div>` : ''}
            </div>
        `;
        }

        function getSelectedProductIds(excludeSelect = null) {
            return Array.from(document.querySelectorAll('.product-select'))
                .filter(select => select !== excludeSelect)
                .map(select => String(select.value || ''))
                .filter(Boolean);
        }

        function filterProducts(search = '', currentValue = '', currentSelect = null) {
            const selectedIds = getSelectedProductIds(currentSelect);
            const keyword = (search || '').trim().toLowerCase();

            return productData.filter(product => {
                const productId = String(product.id);
                const usedElsewhere = selectedIds.includes(productId) && productId !== String(currentValue);

                if (usedElsewhere) return false;

                // If no search typed yet, do not show all products.
                // Only keep the currently selected product visible.
                if (!keyword) {
                    return productId === String(currentValue);
                }

                return (
                    (product.name || '').toLowerCase().includes(keyword) ||
                    (product.supplier_name || '').toLowerCase().includes(keyword) ||
                    (product.category || '').toLowerCase().includes(keyword) ||
                    (product.unit || '').toLowerCase().includes(keyword) ||
                    (product.part_number || '').toLowerCase().includes(keyword) ||
                    (product.details || '').toLowerCase().includes(keyword)
                );
            });
        }

        function updateStockLabel(select) {
            const row = select.closest('tr');
            const stockInput = row.querySelector('.stock-display');
            const productId = String(select.value || '');

            if (!productId || !productMap[productId]) {
                stockInput.value = '—';
                return;
            }

            stockInput.value = productMap[productId].stock ?? '—';
        }

        function buildChoicesForSelect(select, searchValue = '') {
            const currentValue = String(select.value || '');
            const keyword = (searchValue || '').trim();
            const products = filterProducts(searchValue, currentValue, select);

            const choices = [{
                value: '',
                label: keyword ? 'Select Product' : 'Type to search product...',
                selected: currentValue === '',
                disabled: false
            }];

            products.forEach(product => {
                const productId = String(product.id);
                choices.push({
                    value: productId,
                    label: buildProductChoiceHtml(product),
                    selected: productId === currentValue,
                    disabled: false
                });
            });

            return choices;
        }

        function setChoicesForSelect(select, searchValue = '') {
            const instance = select._choicesInstance;
            if (!instance) return;

            const currentValue = String(select.value || '');
            const choices = buildChoicesForSelect(select, searchValue);

            instance.clearChoices();
            instance.setChoices(choices, 'value', 'label', true);

            if (currentValue && productMap[currentValue]) {
                instance.setChoiceByValue(currentValue);
            }
        }

        function onProductChange(e) {
            const activeSelect = e.target;
            updateStockLabel(activeSelect);
            refreshOtherRows(activeSelect);
        }

        function initChoiceForSelect(select) {
            if (!window.Choices || !select) return;
            if (select._choicesInstance) return;

            const instance = new Choices(select, {
                searchEnabled: true,
                searchChoices: false,
                shouldSort: false,
                allowHTML: true,
                itemSelectText: '',
                placeholder: true,
                placeholderValue: 'Select Product',
                searchPlaceholderValue: 'Type product, supplier, unit, part number...',
                noChoicesText: 'Type to search product',
                noResultsText: 'No product found',
                duplicateItemsAllowed: false,
                removeItemButton: false,
                classNames: {
                    containerOuter: 'choices form-select p-0'
                }
            });

            select._choicesInstance = instance;

            // Start empty
            setChoicesForSelect(select, '');

            select.removeEventListener('change', onProductChange);
            select.addEventListener('change', onProductChange);

            select.addEventListener('search', function(event) {
                const searchValue = event.detail.value || '';
                setChoicesForSelect(select, searchValue);
            });

            select.addEventListener('showDropdown', function() {
                const input = select.closest('td')?.querySelector('.choices__input--cloned');
                const keyword = input ? input.value.trim() : '';
                setChoicesForSelect(select, keyword);
            });

            updateStockLabel(select);
        }

        function refreshOtherRows(activeSelect = null) {
            document.querySelectorAll('.product-select').forEach(select => {
                if (select === activeSelect) {
                    updateStockLabel(select);
                    return;
                }

                setChoicesForSelect(select);
                updateStockLabel(select);
            });
        }

        function refreshAllRows() {
            document.querySelectorAll('.product-select').forEach(select => {
                initChoiceForSelect(select);
                setChoicesForSelect(select);
                updateStockLabel(select);
            });
        }

        function addRow() {
            const tableBody = document.querySelector('#itemsTable tbody');
            const row = document.createElement('tr');

            row.innerHTML = `
            <td>
                <select name="product_id[]" class="form-select product-select" required>
                    <option value="">Select Product</option>
                </select>
            </td>
            <td>
                <input type="text" class="form-control stock-display bg-light" value="—" readonly>
            </td>
            <td>
                <input type="number" name="qty_delivered[]" class="form-control" min="1" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">
                    Remove
                </button>
            </td>
        `;

            tableBody.appendChild(row);

            const select = row.querySelector('.product-select');
            initChoiceForSelect(select);
            refreshAllRows();
        }

        function removeRow(button) {
            const rows = document.querySelectorAll('#itemsTable tbody tr');
            if (rows.length <= 1) return;

            const row = button.closest('tr');
            const select = row.querySelector('.product-select');

            if (select && select._choicesInstance) {
                select._choicesInstance.destroy();
                select._choicesInstance = null;
            }

            row.remove();
            refreshAllRows();
        }

        function removeProofPreview() {
            const proofInput = document.getElementById('proofImageInput');
            const previewWrapper = document.getElementById('proofPreviewWrapper');
            const previewImage = document.getElementById('proofPreview');

            proofInput.value = '';
            previewImage.src = '';
            previewWrapper.style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', function() {
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

            refreshAllRows();
        });
    </script>
@endpush
