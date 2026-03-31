@foreach ($jobOrders as $jo)
    @php
        $badgeClass = match ($jo->status) {
            'Open' => 'badge-subtle-warning',
            'In Progress' => 'badge-subtle-info',
            'Fixed' => 'badge-subtle-success',
            'Closed' => 'badge-subtle-secondary',
            default => 'badge-subtle-primary',
        };
    @endphp

    <div class="modal fade" id="editModal{{ $jo->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content cctv-modal border-0 shadow-lg">
                <form method="POST" action="{{ route('concern.cctv.update', $jo->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="modal-header cctv-modal-header border-0">
                        <div class="w-100">
                            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="modal-icon">
                                        <span class="fas fa-video"></span>
                                    </div>
                                    <div>
                                        <h4 class="modal-title mb-1">Update CCTV Job Order</h4>
                                        <div class="text-muted small">Review and update job order details.</div>
                                    </div>
                                </div>

                                <div class="text-lg-end">
                                    <div class="fw-bold fs-5 text-dark">{{ $jo->jo_no }}</div>
                                    <span class="badge rounded-pill {{ $badgeClass }}">{{ $jo->status }}</span>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn-close ms-3" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body pt-0">
                        <div class="form-section">
                            <div class="section-title">
                                <span class="fas fa-info-circle me-2 text-primary"></span>
                                Basic Information
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Bus No</label>
                                    <select class="form-select form-select-modern" name="bus_no" required>
                                        <option value="">-- Select Bus --</option>
                                        @foreach ($buses as $bus)
                                            @php
                                                $label = trim(
                                                    ($bus->body_number ?? '') . ' - ' .
                                                    ($bus->plate_number ?? '') . ' - ' .
                                                    ($bus->name ?? '')
                                                );
                                            @endphp
                                            <option value="{{ $bus->body_number }}" @selected($jo->bus_no == $bus->body_number)>
                                                {{ $label }} {{ $bus->garage ? "({$bus->garage})" : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Reported By</label>
                                    <input type="text" class="form-control form-control-modern"
                                        name="reported_by" value="{{ $jo->reported_by }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <select class="form-select form-select-modern" name="status" required>
                                        @foreach (['Open', 'In Progress', 'Fixed', 'Closed'] as $st)
                                            <option value="{{ $st }}" @selected($jo->status === $st)>{{ $st }}</option>
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
                                            <option value="{{ $it }}" @selected($jo->issue_type === $it)>{{ $it }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Assign To</label>
                                    <select class="form-select form-select-modern" name="assigned_to">
                                        <option value="">— None —</option>
                                        @foreach ($agents as $a)
                                            <option value="{{ $a->id }}" @selected($jo->assigned_to == $a->id)>
                                                {{ $a->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-hint mt-1">Optional. Choose assigned personnel.</div>
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
                                    onclick="cctvAddItemRow('edit-items-wrapper-{{ $jo->id }}')">
                                    <span class="fas fa-plus me-1"></span> Add Item
                                </button>
                            </div>

                            <div class="items-card">
                                <div id="edit-items-wrapper-{{ $jo->id }}">
                                    @forelse($jo->usedItems as $idx => $used)
                                        <div class="row g-2 item-row item-row-modern mb-2">
                                            <div class="col-md-6">
                                                <label class="form-label small text-muted mb-1">Inventory Item</label>
                                                <select class="form-select form-select-modern" name="items[{{ $idx }}][it_inventory_item_id]">
                                                    <option value="">-- Select Inventory Item --</option>
                                                    @foreach ($inventoryItems as $item)
                                                        <option value="{{ $item->id }}" @selected($used->it_inventory_item_id == $item->id)>
                                                            {{ $item->item_name }} | Stock: {{ $item->stock_qty }}
                                                            {{ $item->unit }}{{ $item->brand ? ' | ' . $item->brand : '' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label small text-muted mb-1">Qty</label>
                                                <input type="number" min="1" class="form-control form-control-modern"
                                                    name="items[{{ $idx }}][qty_used]" value="{{ $used->qty_used }}" placeholder="Qty">
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label small text-muted mb-1">Remarks</label>
                                                <input type="text" class="form-control form-control-modern"
                                                    name="items[{{ $idx }}][remarks]" value="{{ $used->remarks }}" placeholder="Remarks">
                                            </div>

                                            <div class="col-md-1 d-grid">
                                                <label class="form-label small invisible mb-1">Remove</label>
                                                <button type="button" class="btn btn-outline-danger rounded-pill"
                                                    onclick="cctvRemoveItemRow(this)">
                                                    <span class="fas fa-times"></span>
                                                </button>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="row g-2 item-row item-row-modern mb-2">
                                            <div class="col-md-6">
                                                <label class="form-label small text-muted mb-1">Inventory Item</label>
                                                <select class="form-select form-select-modern" name="items[0][it_inventory_item_id]">
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
                                                <button type="button" class="btn btn-outline-danger rounded-pill"
                                                    onclick="cctvRemoveItemRow(this)">
                                                    <span class="fas fa-times"></span>
                                                </button>
                                            </div>
                                        </div>
                                    @endforelse
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
                                    <textarea class="form-control form-control-modern" name="problem_details" rows="4" required>{{ $jo->problem_details }}</textarea>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Action Taken</label>
                                    <textarea class="form-control form-control-modern" name="action_taken" rows="4">{{ $jo->action_taken }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                            <span class="fas fa-save me-1"></span> Update Job Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach