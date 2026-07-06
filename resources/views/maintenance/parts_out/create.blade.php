@extends('layouts.app')

@section('title', 'New Parts Out')

@push('styles')
    <style>
        .parts-out-hero {
            background:
                linear-gradient(135deg, rgba(var(--falcon-primary-rgb), .13), rgba(var(--falcon-warning-rgb), .06)),
                var(--falcon-card-bg);
        }

        .parts-icon {
            width: 42px;
            height: 42px;
            min-width: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            line-height: 1;
        }

        .parts-icon-lg {
            width: 54px;
            height: 54px;
            min-width: 54px;
            font-size: 1.25rem;
            border-radius: 16px;
        }

        .parts-section-card {
            border: 1px solid var(--falcon-border-color);
            border-radius: .95rem;
            background: var(--falcon-card-bg);
            overflow: visible;
        }

        .parts-step {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .85rem 1rem;
            border: 1px solid var(--falcon-border-color);
            border-radius: .85rem;
            background: var(--falcon-card-bg);
            height: 100%;
        }

        .parts-step-number {
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

        .parts-step-title {
            font-weight: 700;
            color: var(--falcon-900);
            line-height: 1.15;
        }

        .parts-step-subtitle {
            font-size: .72rem;
            color: var(--falcon-600);
            line-height: 1.25;
        }

        .parts-label {
            font-size: .74rem;
            font-weight: 700;
            color: var(--falcon-700);
            margin-bottom: .35rem;
        }

        .required-marker {
            color: var(--falcon-danger);
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

        .parts-items-table th {
            white-space: nowrap;
            font-size: .69rem;
            text-transform: uppercase;
            letter-spacing: .045em;
            color: var(--falcon-700);
            background: var(--falcon-200);
        }

        .parts-items-table td {
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

        .stock-negative {
            border-color: var(--falcon-danger) !important;
            background-color: rgba(var(--falcon-danger-rgb), .05) !important;
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

            .parts-items-table {
                min-width: 1150px;
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
            <form action="{{ route('parts-out.store') }}" method="POST" enctype="multipart/form-data" id="partsOutForm">
                @csrf

                {{-- HERO --}}
                <div class="card border-0 shadow-sm mb-3 parts-out-hero">
                    <div class="card-body p-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-lg">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="parts-icon parts-icon-lg bg-primary-subtle text-primary">
                                        <span class="fas fa-tools"></span>
                                    </div>

                                    <div>
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <h6 class="mb-0 text-primary fw-semibold">
                                                Maintenance Inventory
                                            </h6>

                                            <span class="badge badge-subtle-warning rounded-pill">
                                                New Parts Out Entry
                                            </span>
                                        </div>

                                        <h3 class="mb-1 fw-bold text-900">
                                            Encode New Parts Out
                                        </h3>

                                        <p class="mb-0 text-600">
                                            Issue vehicle parts, deduct stock from garage inventory, and record mechanic
                                            usage.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-auto">
                                <div class="d-flex create-actions">
                                    <a href="{{ route('parts-out.index') }}" class="btn btn-falcon-default">
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
                        <div class="parts-step">
                            <div class="parts-step-number bg-primary-subtle text-primary">1</div>
                            <div>
                                <div class="parts-step-title">Transaction Details</div>
                                <div class="parts-step-subtitle">Vehicle, garage, mechanic, and date.</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="parts-step">
                            <div class="parts-step-number bg-warning-subtle text-warning">2</div>
                            <div>
                                <div class="parts-step-title">Parts Used</div>
                                <div class="parts-step-subtitle">Select items from selected garage stock.</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="parts-step">
                            <div class="parts-step-number bg-success-subtle text-success">3</div>
                            <div>
                                <div class="parts-step-title">Deduct Stock</div>
                                <div class="parts-step-subtitle">Stock updates after successful saving.</div>
                            </div>
                        </div>
                    </div>
                </div>

                @if (session('error'))
                    <div class="alert alert-danger border-0 shadow-sm">
                        <span class="fas fa-exclamation-triangle me-1"></span>
                        {{ session('error') }}
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

                {{-- TRANSACTION DETAILS --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-body-tertiary border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <div class="parts-icon bg-primary-subtle text-primary">
                                <span class="fas fa-clipboard-list"></span>
                            </div>

                            <div>
                                <h6 class="mb-0 fw-bold text-900">
                                    Transaction Information
                                </h6>
                                <p class="mb-0 fs-10 text-600">
                                    These details identify the vehicle, mechanic, and source garage.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4 vehicle-select-wrapper">
                                <label class="form-label parts-label">
                                    Vehicle
                                </label>

                                <select name="vehicle_id" id="vehicle_id" class="form-select">
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

                                @error('vehicle_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label parts-label">
                                    Source Garage / Location <span class="required-marker">*</span>
                                </label>

                                <select name="location_id" id="location_id"
                                    class="form-select @error('location_id') is-invalid @enderror" required>
                                    @if ($locations->count() > 1)
                                        <option value="">Select Location</option>
                                    @endif

                                    @foreach ($locations as $location)
                                        <option value="{{ $location->id }}"
                                            {{ old('location_id', $locations->count() === 1 ? $location->id : null) == $location->id ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('location_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <div class="form-text fs-10">
                                    Stock will be deducted from this garage.
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label parts-label">
                                    Mechanic <span class="required-marker">*</span>
                                </label>

                                <input type="text" name="mechanic_name"
                                    class="form-control @error('mechanic_name') is-invalid @enderror"
                                    value="{{ old('mechanic_name') }}" placeholder="Mechanic name" required>

                                @error('mechanic_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label parts-label">
                                    Date Issued <span class="required-marker">*</span>
                                </label>

                                <input type="date" name="issued_date"
                                    class="form-control @error('issued_date') is-invalid @enderror"
                                    value="{{ old('issued_date', now()->format('Y-m-d')) }}" required>

                                @error('issued_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label parts-label">
                                    Requested By
                                </label>

                                <input type="text" name="requested_by"
                                    class="form-control @error('requested_by') is-invalid @enderror"
                                    value="{{ old('requested_by') }}" placeholder="Requester">

                                @error('requested_by')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label parts-label">
                                    Job Order No.
                                </label>

                                <input type="text" name="job_order_no"
                                    class="form-control @error('job_order_no') is-invalid @enderror"
                                    value="{{ old('job_order_no') }}" placeholder="JO number">

                                @error('job_order_no')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label parts-label">
                                    Odometer
                                </label>

                                <input type="text" name="odometer"
                                    class="form-control @error('odometer') is-invalid @enderror"
                                    value="{{ old('odometer') }}" placeholder="Odometer">

                                @error('odometer')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label parts-label">
                                    Purpose / Work Details
                                </label>

                                <textarea name="purpose" rows="4" class="form-control @error('purpose') is-invalid @enderror"
                                    placeholder="Describe repair, installation, or work done.">{{ old('purpose') }}</textarea>

                                @error('purpose')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label parts-label">
                                    Remarks
                                </label>

                                <textarea name="remarks" rows="4" class="form-control @error('remarks') is-invalid @enderror"
                                    placeholder="Optional transaction remarks.">{{ old('remarks') }}</textarea>

                                @error('remarks')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PARTS USED --}}
                <div class="parts-section-card mb-3">
                    <div class="card-header bg-body-tertiary border-bottom">
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="parts-icon bg-warning-subtle text-warning">
                                    <span class="fas fa-boxes"></span>
                                </div>

                                <div>
                                    <h6 class="mb-0 fw-bold text-900">
                                        Parts / Items Used
                                    </h6>
                                    <p class="mb-0 fs-10 text-600">
                                        Search available products from the selected garage and encode quantity used.
                                    </p>
                                </div>
                            </div>

                            <button type="button" class="btn btn-primary btn-sm" id="addRowBtn">
                                <span class="fas fa-plus me-1"></span>
                                Add Item
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive scrollbar" style="overflow: visible !important;">
                        <table class="table table-bordered align-middle mb-0 parts-items-table" id="itemsTable">
                            <thead>
                                <tr>
                                    <th style="width: 60px;" class="text-center">#</th>
                                    <th style="min-width: 380px;">Product Search</th>
                                    <th style="width: 120px;">Stock</th>
                                    <th style="width: 100px;">Unit</th>
                                    <th style="width: 140px;">Part No.</th>
                                    <th style="width: 130px;">Qty Used</th>
                                    <th style="width: 130px;">Stock After</th>
                                    <th style="min-width: 180px;">Remarks</th>
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
                                Saving this record will deduct stock from the selected garage. Review stock after values
                                before submitting.
                            </div>

                            <div class="d-flex flex-column flex-sm-row gap-2 form-footer-actions">
                                <a href="{{ route('parts-out.index') }}" class="btn btn-falcon-default">
                                    Cancel
                                </a>

                                <button type="submit" class="btn btn-primary" id="savePartsOutBtn">
                                    <span class="fas fa-save me-1"></span>
                                    Save Parts Out
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
            const itemsBody = document.getElementById('itemsBody');
            const addRowBtn = document.getElementById('addRowBtn');
            const locationSelect = document.getElementById('location_id');
            const form = document.getElementById('partsOutForm');
            const savePartsOutBtn = document.getElementById('savePartsOutBtn');

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

            function getRowHtml() {
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
                            <input type="text" class="form-control form-control-sm stock-display readonly-field" readonly placeholder="0">
                        </td>

                        <td>
                            <input type="text" class="form-control form-control-sm unit-display readonly-field" readonly placeholder="Unit">
                        </td>

                        <td>
                            <input type="text" class="form-control form-control-sm part-number-display readonly-field" readonly placeholder="Part no.">
                        </td>

                        <td>
                            <input type="number" name="qty_used[]" class="form-control form-control-sm qty-used" min="1" value="1" required>
                        </td>

                        <td>
                            <input type="text" class="form-control form-control-sm stock-after-display readonly-field" readonly placeholder="0">
                        </td>

                        <td>
                            <input type="text" name="item_remarks[]" class="form-control form-control-sm" placeholder="Optional remarks">
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
                refreshRowNumbers();
            }

            function resetRows() {
                itemsBody.innerHTML = '';
                addRow();
            }

            function resetRow(row) {
                row.querySelector('.product-id').value = '';
                row.querySelector('.product-search-input').value = '';
                row.querySelector('.product-search-input').dataset.selectedName = '';
                row.querySelector('.stock-display').value = '';
                row.querySelector('.unit-display').value = '';
                row.querySelector('.part-number-display').value = '';
                row.querySelector('.stock-after-display').value = '';
                row.querySelector('.stock-after-display').classList.remove('is-invalid', 'stock-negative');
                row.querySelector('.qty-used').classList.remove('is-invalid');
                row.querySelector('.selected-product-box').classList.add('d-none');
                row.querySelector('.selected-product-box').innerHTML = '';
                row.querySelector('.product-results').style.display = 'none';
                row.querySelector('.product-results').innerHTML = '';
            }

            function getSelectedProductIds(currentRow = null) {
                return Array.from(document.querySelectorAll('.product-id'))
                    .filter(input => !currentRow || input.closest('tr') !== currentRow)
                    .map(input => input.value)
                    .filter(value => value !== '');
            }

            function updateStockAfter(row) {
                const stock = Number(row.querySelector('.stock-display').value || 0);
                const qty = Number(row.querySelector('.qty-used').value || 0);
                const after = stock - qty;
                const stockAfterInput = row.querySelector('.stock-after-display');
                const qtyInput = row.querySelector('.qty-used');

                stockAfterInput.value = Number.isNaN(after) ? '' : after;

                if (after < 0) {
                    stockAfterInput.classList.add('is-invalid', 'stock-negative');
                    qtyInput.classList.add('is-invalid');
                } else {
                    stockAfterInput.classList.remove('is-invalid', 'stock-negative');
                    qtyInput.classList.remove('is-invalid');
                }
            }

            function setSelectedProduct(row, item) {
                const productIdInput = row.querySelector('.product-id');
                const searchInput = row.querySelector('.product-search-input');
                const selectedBox = row.querySelector('.selected-product-box');
                const locationText = locationSelect.selectedOptions[0]?.text ?? 'selected garage';

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
                                Category: ${escapeHtml(item.category ?? 'N/A')}
                            </div>

                            <div class="text-primary fs-10 fw-semibold">
                                Stock in ${escapeHtml(locationText)}: ${escapeHtml(item.stock ?? 0)}
                            </div>
                        </div>
                    </div>
                `;

                updateStockAfter(row);
            }

            function renderResults(row, data) {
                const resultsBox = row.querySelector('.product-results');

                resultsBox.innerHTML = '';

                if (!Array.isArray(data) || !data.length) {
                    resultsBox.innerHTML = `
                        <div class="product-result-item text-muted">
                            <div class="fw-semibold">No available products found</div>
                            <div class="fs-10">Try another product name, supplier, or part number.</div>
                        </div>
                    `;
                    resultsBox.style.display = 'block';
                    return;
                }

                data.forEach(function(item) {
                    const div = document.createElement('div');
                    div.className = 'product-result-item';

                    div.innerHTML = `
                        <div class="result-title">${escapeHtml(item.name ?? '')}</div>
                        <div class="result-meta">
                            Supplier: ${escapeHtml(item.supplier_name ?? 'N/A')} |
                            Unit: ${escapeHtml(item.unit ?? 'N/A')} |
                            Part #: ${escapeHtml(item.part_number ?? 'N/A')} |
                            Available Stock: ${escapeHtml(item.stock ?? 0)}
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

            addRowBtn.addEventListener('click', addRow);
            addRow();

            locationSelect.addEventListener('change', resetRows);

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
                if (event.target.classList.contains('qty-used')) {
                    updateStockAfter(event.target.closest('tr'));
                    return;
                }

                if (!event.target.classList.contains('product-search-input')) {
                    return;
                }

                const input = event.target;
                const row = input.closest('tr');
                const resultsBox = row.querySelector('.product-results');
                const keyword = input.value.trim();

                if (input.dataset.selectedName && input.dataset.selectedName !== keyword) {
                    resetRow(row);
                    input.value = keyword;
                }

                clearTimeout(searchTimeout);

                if (keyword.length < 2) {
                    resultsBox.style.display = 'none';
                    resultsBox.innerHTML = '';
                    return;
                }

                searchTimeout = setTimeout(function() {
                    const locationId = locationSelect.value;

                    if (!locationId) {
                        resultsBox.innerHTML = `
                            <div class="product-result-item text-danger">
                                <div class="fw-semibold">Select source garage first</div>
                                <div class="fs-10">Product stock depends on the selected location.</div>
                            </div>
                        `;
                        resultsBox.style.display = 'block';
                        return;
                    }

                    const selectedIds = getSelectedProductIds(row);

                    fetch(`{{ route('parts-out.search-products') }}?search=${encodeURIComponent(keyword)}&location_id=${encodeURIComponent(locationId)}&exclude_ids=${encodeURIComponent(selectedIds.join(','))}`, {
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

            form.addEventListener('submit', function(event) {
                const locationId = locationSelect.value;

                if (!locationId) {
                    event.preventDefault();
                    alert('Please select source garage / location first.');
                    return;
                }

                const rows = Array.from(itemsBody.querySelectorAll('tr'));
                let hasError = false;

                rows.forEach(function(row) {
                    const productId = row.querySelector('.product-id').value;
                    const qtyInput = row.querySelector('.qty-used');
                    const stockAfterValue = row.querySelector('.stock-after-display').value;
                    const stockAfter = Number(stockAfterValue);

                    if (!productId) {
                        hasError = true;
                    }

                    if (!qtyInput.value || Number(qtyInput.value) <= 0) {
                        hasError = true;
                        qtyInput.classList.add('is-invalid');
                    }

                    if (stockAfterValue === '' || stockAfter < 0) {
                        hasError = true;
                        row.querySelector('.stock-after-display').classList.add('is-invalid',
                            'stock-negative');
                        qtyInput.classList.add('is-invalid');
                    }
                });

                if (hasError) {
                    event.preventDefault();
                    alert(
                        'Please complete all item rows correctly. Check product selection and stock quantity.'
                    );
                    return;
                }

                savePartsOutBtn.disabled = true;
                savePartsOutBtn.innerHTML = `
                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Saving...
                `;
            });

            window.addEventListener('load', function() {
                if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') {
                    return;
                }

                const vehicleSelect = $('#vehicle_id');

                if (vehicleSelect.hasClass('select2-hidden-accessible')) {
                    vehicleSelect.select2('destroy');
                }

                vehicleSelect.select2({
                    width: '100%',
                    placeholder: 'Select Vehicle',
                    allowClear: true,
                    dropdownParent: $('.vehicle-select-wrapper'),
                    matcher: function(params, data) {
                        if ($.trim(params.term) === '') {
                            return data;
                        }

                        if (!data.text) {
                            return null;
                        }

                        const term = params.term.toLowerCase().replace(/[^a-z0-9]/g, '');
                        const text = data.text.toLowerCase().replace(/[^a-z0-9]/g, '');

                        return text.includes(term) ? data : null;
                    }
                });
            });
        });
    </script>
@endpush
