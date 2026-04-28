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

    <div class="modal fade cctv-edit-modal" id="editModal{{ $jo->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <form method="POST" action="{{ route('concern.cctv.update', $jo->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="modal-header border-0 bg-primary text-white px-4 py-3">
                        <div>
                            <h5 class="modal-title mb-1 text-white">
                                <span class="fas fa-video me-2"></span>Update CCTV Job Order
                            </h5>
                            <div class="small opacity-85">
                                {{ $jo->jo_no }}
                                <span class="badge rounded-pill bg-white text-primary ms-2">{{ $jo->status }}</span>
                            </div>
                        </div>

                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body bg-light px-4 py-4">

                        <small class="text-muted d-block mt-1">
                            <span class="fas fa-lock me-1"></span>
                            Locked fields cannot be edited.
                        </small>

                        <div class="card border-0 shadow-sm rounded-4 mb-3">
                            <div class="card-body p-3 p-lg-4">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar avatar-xl me-3">
                                        <div class="avatar-name rounded-circle bg-primary-subtle text-primary">
                                            <span class="fas fa-bus"></span>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Job Information</h6>
                                        <small class="text-muted">Locked request details and editable job status</small>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-lg-5">
                                        <label class="form-label fw-semibold">Bus</label>
                                        <input type="text"
                                            class="form-control form-control-sm bg-200 text-700 border-0"
                                            value="{{ $busDisplayMap[$jo->bus_no] ?? $jo->bus_no }}" readonly>
                                    </div>

                                    <div class="col-lg-4">
                                        <label class="form-label fw-semibold">Reported By</label>
                                        <input type="text"
                                            class="form-control form-control-sm bg-200 text-700 border-0"
                                            value="{{ $jo->reported_by }}" readonly>
                                    </div>

                                    <div class="col-lg-3">
                                        <label class="form-label fw-semibold">Status</label>
                                        <select class="form-select form-select-sm" name="status" required>
                                            @foreach (['Open', 'In Progress', 'Fixed', 'Closed'] as $st)
                                                <option value="{{ $st }}" @selected($jo->status === $st)>
                                                    {{ $st }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-lg-6">
                                        <label class="form-label fw-semibold">Issue Type</label>
                                        <input type="text"
                                            class="form-control form-control-sm bg-200 text-700 border-0"
                                            value="{{ $jo->issue_type }}" readonly>
                                    </div>

                                    <div class="col-lg-6">
                                        <label class="form-label fw-semibold">Assign To</label>
                                        <select class="form-select form-select-sm" name="assigned_to">
                                            <option value="">None</option>
                                            @foreach ($agents as $a)
                                                <option value="{{ $a->id }}" @selected($jo->assigned_to == $a->id)>
                                                    {{ $a->full_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-4 mb-3">
                            <div class="card-body p-3 p-lg-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="mb-0">
                                            <span class="fas fa-box-open text-primary me-2"></span>Items Used
                                        </h6>
                                        <small class="text-muted">Select parts consumed for this job order</small>
                                    </div>

                                    <button type="button" class="btn btn-primary btn-sm rounded-pill"
                                        onclick="cctvAddItemRow('edit-items-wrapper-{{ $jo->id }}')">
                                        <span class="fas fa-plus me-1"></span>Add Item
                                    </button>
                                </div>

                                <div id="edit-items-wrapper-{{ $jo->id }}">
                                    @forelse($jo->usedItems as $idx => $used)
                                        <div class="item-row bg-white border rounded-3 p-3 mb-2">
                                            <div class="row g-2 align-items-end">
                                                <div class="col-lg-6">
                                                    <label class="form-label fs-11 fw-semibold text-muted">Inventory
                                                        Item</label>
                                                    <select class="form-select form-select-sm"
                                                        name="items[{{ $idx }}][it_inventory_item_id]">
                                                        <option value="">Select Inventory Item</option>
                                                        @foreach ($inventoryItems as $item)
                                                            <option value="{{ $item->id }}"
                                                                @selected($used->it_inventory_item_id == $item->id)>
                                                                {{ $item->item_name }} | Stock: {{ $item->stock_qty }}
                                                                {{ $item->unit }}{{ $item->brand ? ' | ' . $item->brand : '' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-lg-2">
                                                    <label class="form-label fs-11 fw-semibold text-muted">Qty</label>
                                                    <input type="number" min="1"
                                                        class="form-control form-control-sm"
                                                        name="items[{{ $idx }}][qty_used]"
                                                        value="{{ $used->qty_used }}" placeholder="Qty">
                                                </div>

                                                <div class="col-lg-3">
                                                    <label
                                                        class="form-label fs-11 fw-semibold text-muted">Remarks</label>
                                                    <input type="text" class="form-control form-control-sm"
                                                        name="items[{{ $idx }}][remarks]"
                                                        value="{{ $used->remarks }}" placeholder="Remarks">
                                                </div>

                                                <div class="col-lg-1">
                                                    <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                                        onclick="cctvRemoveItemRow(this)">
                                                        <span class="fas fa-times"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="item-row bg-white border rounded-3 p-3 mb-2">
                                            <div class="row g-2 align-items-end">
                                                <div class="col-lg-6">
                                                    <label class="form-label fs-11 fw-semibold text-muted">Inventory
                                                        Item</label>
                                                    <select class="form-select form-select-sm"
                                                        name="items[0][it_inventory_item_id]">
                                                        <option value="">Select Inventory Item</option>
                                                        @foreach ($inventoryItems as $item)
                                                            <option value="{{ $item->id }}">
                                                                {{ $item->item_name }} | Stock: {{ $item->stock_qty }}
                                                                {{ $item->unit }}{{ $item->brand ? ' | ' . $item->brand : '' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-lg-2">
                                                    <label class="form-label fs-11 fw-semibold text-muted">Qty</label>
                                                    <input type="number" min="1"
                                                        class="form-control form-control-sm" name="items[0][qty_used]"
                                                        placeholder="Qty">
                                                </div>

                                                <div class="col-lg-3">
                                                    <label
                                                        class="form-label fs-11 fw-semibold text-muted">Remarks</label>
                                                    <input type="text" class="form-control form-control-sm"
                                                        name="items[0][remarks]" placeholder="Remarks">
                                                </div>

                                                <div class="col-lg-1">
                                                    <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                                        onclick="cctvRemoveItemRow(this)">
                                                        <span class="fas fa-times"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-3 p-lg-4">
                                <h6 class="mb-3">
                                    <span class="fas fa-clipboard-check text-primary me-2"></span>Work Details
                                </h6>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Problem Details</label>
                                    <textarea class="form-control bg-200 text-700 border-0" rows="3" readonly>{{ $jo->problem_details }}</textarea>
                                </div>

                                <div>
                                    <label class="form-label fw-semibold">Action Taken</label>
                                    <textarea class="form-control" name="action_taken" rows="4" placeholder="Enter action taken...">{{ $jo->action_taken }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 bg-white px-4 py-3">
                        <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm px-3">
                            <span class="fas fa-save me-1"></span>Update Job Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
