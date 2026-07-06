@extends('layouts.app')

@section('title', 'New Receiving')

@push('styles')
    <style>
        .receiving-page-hero {
            background:
                linear-gradient(135deg, rgba(var(--falcon-primary-rgb), .13), rgba(var(--falcon-info-rgb), .05)),
                var(--falcon-card-bg);
        }

        .receiving-icon {
            width: 42px;
            height: 42px;
            min-width: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            line-height: 1;
        }

        .receiving-icon-lg {
            width: 54px;
            height: 54px;
            min-width: 54px;
            font-size: 1.25rem;
            border-radius: 16px;
        }

        .receiving-section-card {
            border: 1px solid var(--falcon-border-color);
            border-radius: .95rem;
            background: var(--falcon-card-bg);
            overflow: visible;
        }

        .receiving-section-card .card-header {
            border-top-left-radius: .95rem;
            border-top-right-radius: .95rem;
        }

        .receiving-step {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .85rem 1rem;
            border: 1px solid var(--falcon-border-color);
            border-radius: .85rem;
            background: var(--falcon-card-bg);
            height: 100%;
        }

        .receiving-step-number {
            width: 30px;
            height: 30px;
            min-width: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: .78rem;
        }

        .receiving-step-title {
            font-weight: 700;
            color: var(--falcon-900);
            line-height: 1.15;
        }

        .receiving-step-subtitle {
            font-size: .72rem;
            color: var(--falcon-600);
            line-height: 1.25;
        }

        .receiving-label {
            font-size: .74rem;
            font-weight: 700;
            color: var(--falcon-700);
            margin-bottom: .35rem;
        }

        .required-marker {
            color: var(--falcon-danger);
        }

        .receiving-help-text {
            font-size: .72rem;
            color: var(--falcon-600);
        }

        .proof-dropzone {
            border: 1px dashed rgba(var(--falcon-primary-rgb), .35);
            border-radius: .9rem;
            background: rgba(var(--falcon-primary-rgb), .04);
            padding: 1rem;
        }

        .proof-preview-frame {
            border: 1px solid var(--falcon-border-color);
            border-radius: .9rem;
            background: var(--falcon-card-bg);
            padding: 1rem;
        }

        .proof-preview-image {
            max-height: 280px;
            object-fit: contain;
        }

        .product-search-wrapper {
            position: relative;
        }

        .product-results {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            z-index: 1060;
            display: none;
            max-height: 320px;
            overflow-y: auto;
            background: var(--falcon-card-bg);
            border: 1px solid var(--falcon-border-color);
            border-radius: .7rem;
            box-shadow: var(--falcon-box-shadow);
        }

        .product-result-item {
            padding: .8rem .9rem;
            cursor: pointer;
            border-bottom: 1px solid var(--falcon-border-color);
        }

        .product-result-item:hover {
            background: var(--falcon-body-tertiary-bg, #f9fafd);
        }

        .product-result-item:last-child {
            border-bottom: 0;
        }

        .result-title {
            font-weight: 700;
            color: var(--falcon-900);
            margin-bottom: .18rem;
        }

        .result-meta {
            font-size: .72rem;
            color: var(--falcon-600);
            line-height: 1.35;
        }

        .selected-product-box {
            border: 1px solid rgba(var(--falcon-success-rgb), .25);
            background: rgba(var(--falcon-success-rgb), .06);
            border-radius: .75rem;
            padding: .7rem .85rem;
        }

        .receiving-items-table th {
            white-space: nowrap;
            font-size: .69rem;
            text-transform: uppercase;
            letter-spacing: .045em;
            color: var(--falcon-700);
            background: var(--falcon-200);
        }

        .receiving-items-table td {
            vertical-align: top;
            padding: .85rem;
        }

        .readonly-field {
            background-color: var(--falcon-body-tertiary-bg, #f9fafd);
            cursor: not-allowed;
        }

        .item-row-number {
            width: 28px;
            height: 28px;
            min-width: 28px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: .75rem;
            font-weight: 700;
        }

        .sticky-submit-card {
            position: sticky;
            bottom: 0;
            z-index: 20;
            border-top: 1px solid var(--falcon-border-color);
            box-shadow: 0 -6px 18px rgba(0, 0, 0, .04);
        }

        @media (max-width: 767.98px) {

            .create-actions,
            .form-footer-actions {
                width: 100%;
            }

            .create-actions .btn,
            .form-footer-actions .btn {
                width: 100%;
            }

            .receiving-items-table {
                min-width: 900px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container" data-layout="container">
        <script>
            const isFluid = JSON.parse(localStorage.getItem('isFluid'));

            if (isFluid) {
                const container = document.querySelector('[data-layout]');
                container.classList.remove('container');
                container.classList.add('container-fluid');
            }
        </script>

        <div class="content">
            @php
                $oldProductIds = collect(old('product_id', []))->values();
                $oldQuantities = collect(old('qty_delivered', []))->values();

                $oldRowsPayload = $oldProductIds
                    ->map(function ($id, $index) use ($oldQuantities) {
                        return [
                            'product_id' => (string) $id,
                            'qty' => (int) $oldQuantities->get($index, 1),
                        ];
                    })
                    ->values();

                $oldProductPayload = $products
                    ->whereIn(
                        'id',
                        $oldProductIds
                            ->map(function ($id) {
                                return (int) $id;
                            })
                            ->all(),
                    )
                    ->mapWithKeys(function ($product) {
                        return [
                            (string) $product->id => [
                                'id' => $product->id,
                                'name' => $product->product_name,
                                'supplier_name' => $product->supplier_name,
                                'unit' => $product->unit,
                                'part_number' => $product->part_number,
                                'stock' => (int) $product->stock_qty,
                                'category' => optional($product->category)->name,
                            ],
                        ];
                    });
            @endphp

            {{-- HERO --}}
            <div class="card border-0 shadow-sm mb-3 receiving-page-hero">
                <div class="card-body p-4">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg">
                            <div class="d-flex align-items-start gap-3">
                                <div class="receiving-icon receiving-icon-lg bg-primary-subtle text-primary">
                                    <span class="fas fa-dolly-flatbed"></span>
                                </div>

                                <div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <h6 class="mb-0 text-primary fw-semibold">
                                            Maintenance Inventory
                                        </h6>

                                        <span class="badge badge-subtle-success rounded-pill">
                                            New Receiving Entry
                                        </span>
                                    </div>

                                    <h3 class="mb-1 fw-bold text-900">
                                        Encode New Receiving
                                    </h3>

                                    <p class="mb-0 text-600">
                                        Record delivered products, attach proof, and update garage stock in one transaction.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-auto">
                            <div class="d-flex create-actions">
                                <a href="{{ route('receivings.index') }}" class="btn btn-falcon-default">
                                    <span class="fas fa-arrow-left me-1"></span>
                                    Back to Records
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PROCESS STEPS --}}
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="receiving-step">
                        <div class="receiving-step-number bg-primary-subtle text-primary">1</div>
                        <div>
                            <div class="receiving-step-title">Delivery Details</div>
                            <div class="receiving-step-subtitle">Garage, delivery date, and sender.</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="receiving-step">
                        <div class="receiving-step-number bg-success-subtle text-success">2</div>
                        <div>
                            <div class="receiving-step-title">Delivered Products</div>
                            <div class="receiving-step-subtitle">Select products and encode quantity.</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="receiving-step">
                        <div class="receiving-step-number bg-warning-subtle text-warning">3</div>
                        <div>
                            <div class="receiving-step-title">Save Stock</div>
                            <div class="receiving-step-subtitle">Stock updates after successful saving.</div>
                        </div>
                    </div>
                </div>
            </div>

            <form id="receivingForm" action="{{ route('receivings.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                @if (session('error'))
                    <div class="alert alert-danger border-0 shadow-sm">
                        <span class="fas fa-exclamation-triangle me-1"></span>
                        {{ session('error') }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success border-0 shadow-sm">
                        <span class="fas fa-check-circle me-1"></span>
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm">
                        <div class="fw-bold mb-1">
                            <span class="fas fa-exclamation-circle me-1"></span>
                            Please fix the following:
                        </div>

                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- DELIVERY + PROOF --}}
                <div class="row g-3 mb-3">
                    <div class="col-xl-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-body-tertiary border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="receiving-icon bg-primary-subtle text-primary">
                                        <span class="fas fa-clipboard-list"></span>
                                    </div>

                                    <div>
                                        <h6 class="mb-0 fw-bold text-900">
                                            Delivery Information
                                        </h6>
                                        <p class="mb-0 fs-10 text-600">
                                            These details identify where and when the stock was received.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label receiving-label">
                                            Garage / Location <span class="required-marker">*</span>
                                        </label>

                                        <select name="location_id"
                                            class="form-select @error('location_id') is-invalid @enderror" required>
                                            @if ($locations->count() > 1)
                                                <option value="">Select Garage</option>
                                            @endif

                                            @foreach ($locations as $loc)
                                                <option value="{{ $loc->id }}"
                                                    {{ old('location_id') == $loc->id ? 'selected' : '' }}>
                                                    {{ $loc->name }}
                                                </option>
                                            @endforeach
                                        </select>

                                        @error('location_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                        <div class="receiving-help-text mt-1">
                                            Stock will be added to this garage.
                                        </div>
                                    </div>

                                    <div class="col-md-5">
                                        <label class="form-label receiving-label">
                                            Delivered By <span class="required-marker">*</span>
                                        </label>

                                        <input type="text" name="delivered_by"
                                            class="form-control @error('delivered_by') is-invalid @enderror"
                                            value="{{ old('delivered_by') }}"
                                            placeholder="Supplier, driver, or delivery personnel" required>

                                        @error('delivered_by')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label receiving-label">
                                            Delivery Date <span class="required-marker">*</span>
                                        </label>

                                        <input type="date" name="delivery_date"
                                            class="form-control @error('delivery_date') is-invalid @enderror"
                                            value="{{ old('delivery_date', date('Y-m-d')) }}" required>

                                        @error('delivery_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label receiving-label">
                                            Remarks
                                        </label>

                                        <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="4"
                                            placeholder="Optional notes about delivery condition, supplier reference, receipt number, or remarks.">{{ old('remarks') }}</textarea>

                                        @error('remarks')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-body-tertiary border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="receiving-icon bg-info-subtle text-info">
                                        <span class="fas fa-receipt"></span>
                                    </div>

                                    <div>
                                        <h6 class="mb-0 fw-bold text-900">
                                            Proof of Delivery
                                        </h6>
                                        <p class="mb-0 fs-10 text-600">
                                            Attach receipt, DR, or delivery image.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="proof-dropzone mb-3">
                                    <label class="form-label receiving-label">
                                        Upload Proof
                                    </label>

                                    <input type="file" name="proof_image" id="proofImageInput"
                                        class="form-control @error('proof_image') is-invalid @enderror"
                                        accept="image/png,image/jpeg,image/jpg">

                                    @error('proof_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror

                                    <div class="receiving-help-text mt-2">
                                        Accepted: JPG, JPEG, PNG. Max 2MB.
                                    </div>
                                </div>

                                <div id="proofPreviewWrapper" style="display: none;">
                                    <div class="proof-preview-frame">
                                        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                            <div class="fw-bold text-900">
                                                <span class="fas fa-image text-primary me-1"></span>
                                                Preview
                                            </div>

                                            <button type="button" class="btn btn-falcon-danger btn-sm"
                                                id="removeProofPreviewBtn">
                                                <span class="fas fa-times me-1"></span>
                                                Remove
                                            </button>
                                        </div>

                                        <div class="text-center">
                                            <img id="proofPreview" src="" alt="Proof Preview"
                                                class="img-fluid rounded border proof-preview-image">
                                        </div>
                                    </div>
                                </div>

                                <div id="proofEmptyState" class="text-center py-4 text-muted">
                                    <span class="fas fa-cloud-upload-alt fs-3 d-block mb-2 text-300"></span>
                                    <div class="fw-semibold">No proof selected</div>
                                    <div class="fs-10">
                                        Preview appears after choosing an image.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PRODUCTS DELIVERED --}}
                <div class="receiving-section-card mb-3">
                    <div class="card-header bg-body-tertiary border-bottom">
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="receiving-icon bg-success-subtle text-success">
                                    <span class="fas fa-boxes"></span>
                                </div>

                                <div>
                                    <h6 class="mb-0 fw-bold text-900">
                                        Products Delivered
                                    </h6>
                                    <p class="mb-0 fs-10 text-600">
                                        Search each product, confirm the selected item, then encode the delivered quantity.
                                    </p>
                                </div>
                            </div>

                            <button type="button" class="btn btn-success btn-sm" id="addRowBtn">
                                <span class="fas fa-plus me-1"></span>
                                Add Product
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive scrollbar" style="overflow: visible !important;">
                        <table class="table table-bordered align-middle mb-0 receiving-items-table" id="itemsTable">
                            <thead>
                                <tr>
                                    <th style="width: 60px;" class="text-center">#</th>
                                    <th style="min-width: 380px;">Product Search</th>
                                    <th style="width: 130px;">Current Stock</th>
                                    <th style="width: 110px;">Unit</th>
                                    <th style="width: 150px;">Part No.</th>
                                    <th style="width: 150px;">Qty Delivered</th>
                                    <th style="width: 90px;" class="text-center">Remove</th>
                                </tr>
                            </thead>

                            <tbody id="itemsBody"></tbody>
                        </table>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="card border-0 shadow-sm sticky-submit-card">
                    <div class="card-footer bg-body-tertiary border-0">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <div class="text-muted fs-10">
                                <span class="fas fa-shield-alt me-1"></span>
                                Saving this record will add stock to the selected garage. Review product rows before
                                submitting.
                            </div>

                            <div class="d-flex flex-column flex-sm-row gap-2 form-footer-actions">
                                <a href="{{ route('receivings.index') }}" class="btn btn-falcon-default">
                                    Cancel
                                </a>

                                <button type="submit" class="btn btn-primary" id="saveReceivingBtn">
                                    <span class="fas fa-save me-1"></span>
                                    Save Receiving
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const receivingForm = document.getElementById('receivingForm');
            const itemsBody = document.getElementById('itemsBody');
            const addRowBtn = document.getElementById('addRowBtn');
            const saveReceivingBtn = document.getElementById('saveReceivingBtn');

            const oldRows = @json($oldRowsPayload);
            const oldProducts = @json($oldProductPayload);

            let searchTimeout = null;

            function escapeHtml(value) {
                return String(value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function refreshRowNumbers() {
                document.querySelectorAll('#itemsBody tr').forEach(function(row, index) {
                    const numberBox = row.querySelector('.item-row-number');

                    if (numberBox) {
                        numberBox.textContent = index + 1;
                    }
                });
            }

            function getRowHtml(qty = 1) {
                return `
                    <tr>
                        <td class="text-center">
                            <span class="item-row-number bg-primary-subtle text-primary">1</span>
                        </td>

                        <td>
                            <input type="hidden" name="product_id[]" class="product-id">

                            <div class="product-search-wrapper">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-body-tertiary">
                                        <span class="fas fa-search text-500"></span>
                                    </span>

                                    <input
                                        type="text"
                                        class="form-control product-search-input"
                                        placeholder="Search product name, supplier, part no., or details..."
                                        autocomplete="off"
                                    >
                                </div>

                                <div class="product-results"></div>
                            </div>

                            <div class="selected-product-box mt-2 d-none"></div>
                        </td>

                        <td>
                            <input
                                type="text"
                                class="form-control form-control-sm stock-display readonly-field"
                                readonly
                                placeholder="0"
                            >
                        </td>

                        <td>
                            <input
                                type="text"
                                class="form-control form-control-sm unit-display readonly-field"
                                readonly
                                placeholder="Unit"
                            >
                        </td>

                        <td>
                            <input
                                type="text"
                                class="form-control form-control-sm part-number-display readonly-field"
                                readonly
                                placeholder="Part no."
                            >
                        </td>

                        <td>
                            <input
                                type="number"
                                name="qty_delivered[]"
                                class="form-control form-control-sm qty-delivered-input"
                                min="1"
                                value="${qty}"
                                required
                            >
                        </td>

                        <td class="text-center">
                            <button type="button" class="btn btn-falcon-danger btn-sm remove-row">
                                <span class="fas fa-trash"></span>
                            </button>
                        </td>
                    </tr>
                `;
            }

            function addRow(product = null, qty = 1) {
                itemsBody.insertAdjacentHTML('beforeend', getRowHtml(qty));

                const row = itemsBody.lastElementChild;

                if (product) {
                    setSelectedProduct(row, product);
                }

                refreshRowNumbers();
            }

            function resetRow(row) {
                row.querySelector('.product-id').value = '';
                row.querySelector('.product-search-input').value = '';
                row.querySelector('.product-search-input').dataset.selectedName = '';
                row.querySelector('.stock-display').value = '';
                row.querySelector('.unit-display').value = '';
                row.querySelector('.part-number-display').value = '';
                row.querySelector('.selected-product-box').classList.add('d-none');
                row.querySelector('.selected-product-box').innerHTML = '';
                row.querySelector('.product-results').style.display = 'none';
                row.querySelector('.product-results').innerHTML = '';
            }

            function setSelectedProduct(row, item) {
                const productIdInput = row.querySelector('.product-id');
                const searchInput = row.querySelector('.product-search-input');
                const selectedBox = row.querySelector('.selected-product-box');

                productIdInput.value = item.id;
                searchInput.value = item.name ?? '';
                searchInput.dataset.selectedName = item.name ?? '';

                row.querySelector('.stock-display').value = item.stock ?? 0;
                row.querySelector('.unit-display').value = item.unit ?? '';
                row.querySelector('.part-number-display').value = item.part_number ?? '';

                selectedBox.classList.remove('d-none');
                selectedBox.innerHTML = `
                    <div class="d-flex align-items-start gap-2">
                        <span class="fas fa-check-circle text-success mt-1"></span>

                        <div>
                            <div class="fw-bold text-900">
                                ${escapeHtml(item.name ?? '')}
                            </div>

                            <div class="text-muted fs-10">
                                Supplier: ${escapeHtml(item.supplier_name ?? 'N/A')} |
                                Category: ${escapeHtml(item.category ?? 'N/A')} |
                                Current Stock: ${escapeHtml(item.stock ?? 0)}
                            </div>
                        </div>
                    </div>
                `;
            }

            function getSelectedProductIds(currentRow = null) {
                return Array.from(document.querySelectorAll('.product-id'))
                    .filter(input => !currentRow || input.closest('tr') !== currentRow)
                    .map(input => input.value)
                    .filter(value => value !== '');
            }

            function renderResults(row, data) {
                const resultsBox = row.querySelector('.product-results');

                resultsBox.innerHTML = '';

                if (!data.length) {
                    resultsBox.innerHTML = `
                        <div class="product-result-item text-muted">
                            <div class="fw-semibold">No products found</div>
                            <div class="fs-10">Try another product name, supplier, or part number.</div>
                        </div>
                    `;
                    resultsBox.style.display = 'block';
                    return;
                }

                data.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'product-result-item';

                    div.innerHTML = `
                        <div class="result-title">${escapeHtml(item.name ?? '')}</div>
                        <div class="result-meta">
                            Supplier: ${escapeHtml(item.supplier_name ?? 'N/A')} |
                            Unit: ${escapeHtml(item.unit ?? 'N/A')} |
                            Part #: ${escapeHtml(item.part_number ?? 'N/A')} |
                            Stock: ${escapeHtml(item.stock ?? 0)}
                        </div>
                    `;

                    div.addEventListener('click', function() {
                        setSelectedProduct(row, item);
                        resultsBox.style.display = 'none';
                    });

                    resultsBox.appendChild(div);
                });

                resultsBox.style.display = 'block';
            }

            addRowBtn.addEventListener('click', function() {
                addRow();
            });

            document.addEventListener('click', function(event) {
                if (event.target.closest('.remove-row')) {
                    const rows = itemsBody.querySelectorAll('tr');

                    if (rows.length > 1) {
                        event.target.closest('tr').remove();
                        refreshRowNumbers();
                        return;
                    }

                    resetRow(event.target.closest('tr'));
                }
            });

            document.addEventListener('input', function(event) {
                if (!event.target.classList.contains('product-search-input')) {
                    return;
                }

                const input = event.target;
                const row = input.closest('tr');
                const resultsBox = row.querySelector('.product-results');
                const keyword = input.value.trim();

                if (input.dataset.selectedName && input.dataset.selectedName !== keyword) {
                    row.querySelector('.product-id').value = '';
                    row.querySelector('.stock-display').value = '';
                    row.querySelector('.unit-display').value = '';
                    row.querySelector('.part-number-display').value = '';
                    row.querySelector('.selected-product-box').classList.add('d-none');
                    row.querySelector('.selected-product-box').innerHTML = '';
                }

                clearTimeout(searchTimeout);

                if (keyword.length < 2) {
                    resultsBox.style.display = 'none';
                    resultsBox.innerHTML = '';
                    return;
                }

                searchTimeout = setTimeout(function() {
                    const selectedIds = getSelectedProductIds(row);

                    fetch(`{{ route('receivings.search-products') }}?search=${encodeURIComponent(keyword)}&exclude_ids=${encodeURIComponent(selectedIds.join(','))}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => renderResults(row, data))
                        .catch(error => {
                            console.error(error);

                            resultsBox.innerHTML = `
                                <div class="product-result-item text-danger">
                                    <div class="fw-semibold">Error loading products</div>
                                    <div class="fs-10">Refresh the page or check the search route.</div>
                                </div>
                            `;
                            resultsBox.style.display = 'block';
                        });
                }, 350);
            });

            document.addEventListener('click', function(event) {
                document.querySelectorAll('.product-results').forEach(function(box) {
                    const wrapper = box.closest('.product-search-wrapper');

                    if (wrapper && !wrapper.contains(event.target)) {
                        box.style.display = 'none';
                    }
                });
            });

            receivingForm.addEventListener('submit', function(event) {
                const productInputs = Array.from(document.querySelectorAll('.product-id'));
                const quantityInputs = Array.from(document.querySelectorAll('.qty-delivered-input'));

                const hasEmptyProduct = productInputs.some(input => input.value === '');
                const hasInvalidQuantity = quantityInputs.some(input => Number(input.value) < 1 || input
                    .value === '');

                if (hasEmptyProduct) {
                    event.preventDefault();
                    alert('Please select a valid product for every row before saving.');
                    return;
                }

                if (hasInvalidQuantity) {
                    event.preventDefault();
                    alert('Quantity delivered must be at least 1 for every product row.');
                    return;
                }

                saveReceivingBtn.disabled = true;
                saveReceivingBtn.innerHTML = `
                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Saving...
                `;
            });

            const proofInput = document.getElementById('proofImageInput');
            const previewWrapper = document.getElementById('proofPreviewWrapper');
            const proofEmptyState = document.getElementById('proofEmptyState');
            const previewImage = document.getElementById('proofPreview');
            const removeProofPreviewBtn = document.getElementById('removeProofPreviewBtn');

            function clearProofPreview() {
                proofInput.value = '';
                previewImage.src = '';
                previewWrapper.style.display = 'none';
                proofEmptyState.style.display = 'block';
            }

            if (proofInput) {
                proofInput.addEventListener('change', function(event) {
                    const file = event.target.files[0];

                    if (!file) {
                        clearProofPreview();
                        return;
                    }

                    const reader = new FileReader();

                    reader.onload = function(readerEvent) {
                        previewImage.src = readerEvent.target.result;
                        previewWrapper.style.display = 'block';
                        proofEmptyState.style.display = 'none';
                    };

                    reader.readAsDataURL(file);
                });
            }

            if (removeProofPreviewBtn) {
                removeProofPreviewBtn.addEventListener('click', clearProofPreview);
            }

            if (oldRows.length > 0) {
                oldRows.forEach(function(row) {
                    const product = oldProducts[row.product_id] ?? null;

                    addRow(product, row.qty || 1);
                });
            } else {
                addRow();
            }
        });
    </script>
@endpush
