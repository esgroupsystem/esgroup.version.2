<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content cctv-modal border-0 shadow-lg">
            <form method="POST" action="{{ route('concern.cctv.store') }}">
                @csrf

                <div class="modal-header cctv-modal-header border-0">
                    <div>
                        <h5 class="modal-title mb-1">Create CCTV Job Order</h5>
                        <div class="form-hint">Fill in all required details and save.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body pt-0">
                    <div class="form-section">
                        <div class="section-title">
                            <span class="fas fa-info-circle me-2 text-primary"></span>
                            Basic Information
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Bus</label>
                                <select class="form-select form-select-modern bus-select" name="bus_no" required>
                                    <option value="">-- Select Bus --</option>
                                    @foreach ($buses as $bus)
                                        <option value="{{ $bus->body_number }}">
                                            {{ $bus->body_number }} - {{ $bus->plate_number ?? 'No Plate' }} -
                                            {{ $bus->name ?? 'No Name' }}
                                            {{ $bus->garage ? "({$bus->garage})" : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Reported By</label>
                                <input type="text" class="form-control form-control-modern"
                                    value="{{ auth()->user()->full_name }}" readonly>
                                <input type="hidden" name="reported_by" value="{{ auth()->user()->full_name }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select class="form-select form-select-modern" name="status" required>
                                    @foreach (['Open', 'In Progress', 'Fixed', 'Closed'] as $st)
                                        <option value="{{ $st }}" @selected($st === 'Open')>
                                            {{ $st }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-title">
                            <span class="fas fa-tools me-2 text-primary"></span>
                            Issue & Assignment
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Issue Type</label>
                                <select class="form-select form-select-modern" name="issue_type" required>
                                    @foreach (['Camera', 'Monitor', 'DVR', 'Wiring', 'Power', 'Other'] as $it)
                                        <option value="{{ $it }}">{{ $it }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Assign To</label>
                                <select class="form-select form-select-modern" name="assigned_to">
                                    <option value="">— None —</option>
                                    @foreach ($agents as $a)
                                        <option value="{{ $a->id }}">{{ $a->full_name }}</option>
                                    @endforeach
                                </select>
                                <div class="form-hint mt-1">Optional.</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <div class="section-title mb-0">
                                <span class="fas fa-box-open me-2 text-primary"></span>
                                Items Used
                            </div>

                            <button type="button" class="btn btn-sm btn-primary"
                                onclick="cctvAddItemRow('create-items-wrapper')">
                                <span class="fas fa-plus me-1"></span> Add Item
                            </button>
                        </div>

                        <div class="items-card">
                            <div id="create-items-wrapper">
                                <div class="row g-2 item-row item-row-modern mb-2">
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted mb-1">Inventory Item</label>
                                        <select class="form-select form-select-modern"
                                            name="items[0][it_inventory_item_id]">
                                            <option value="">-- Select Inventory Item --</option>
                                            @foreach ($inventoryItems as $item)
                                                <option value="{{ $item->id }}">
                                                    {{ $item->item_name }} | Stock: {{ $item->stock_qty }}
                                                    {{ $item->unit }}{{ $item->brand ? ' | ' . $item->brand : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label small text-muted mb-1">Qty</label>
                                        <input type="number" min="1" class="form-control form-control-modern"
                                            name="items[0][qty_used]" placeholder="Qty">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label small text-muted mb-1">Remarks</label>
                                        <input type="text" class="form-control form-control-modern"
                                            name="items[0][remarks]" placeholder="Remarks">
                                    </div>

                                    <div class="col-md-1 d-grid">
                                        <label class="form-label small invisible mb-1">Remove</label>
                                        <button type="button"
                                            class="btn btn-outline-danger rounded-pill remove-item-row">
                                            <span class="fas fa-times"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-title">
                            <span class="fas fa-align-left me-2 text-primary"></span>
                            Work Details
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Problem Details</label>
                                <textarea class="form-control form-control-modern" name="problem_details" rows="4" required></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Action Taken</label>
                                <textarea class="form-control form-control-modern" name="action_taken" rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <span class="fas fa-save me-1"></span> Save Job Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
