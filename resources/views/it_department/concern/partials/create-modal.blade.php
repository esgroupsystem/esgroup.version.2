<div class="modal fade cctv-edit-modal" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">

            <form method="POST" action="{{ route('concern.cctv.store') }}">
                @csrf

                {{-- HEADER --}}
                <div class="modal-header text-white px-4 py-3">
                    <div>
                        <h5 class="mb-1">
                            <span class="fas fa-plus-circle me-2"></span>Create CCTV Job Order
                        </h5>
                        <small class="opacity-75">Fill in the details to create a new job order</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                {{-- BODY --}}
                <div class="modal-body">

                    {{-- BASIC INFO --}}
                    <div class="card mb-3">
                        <div class="card-body">

                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar avatar-md me-3">
                                    <div class="avatar-name rounded-circle bg-primary-subtle text-primary">
                                        <span class="fas fa-info"></span>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-0">Basic Information</h6>
                                    <small class="text-muted">Bus, reporter, and status</small>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-lg-5">
                                    <label class="form-label fw-semibold">Bus</label>
                                    <select class="form-select form-select-sm bus-select" name="bus_no" required>
                                        <option value="">Select Bus</option>
                                        @foreach ($buses as $bus)
                                            <option value="{{ $bus->body_number }}">
                                                {{ $bus->display_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-4">
                                    <label class="form-label fw-semibold">Reported By</label>
                                    <input type="text" class="form-control form-control-sm bg-200 text-700 border-0"
                                        value="{{ request()->user()?->full_name }}" readonly>
                                </div>

                                <div class="col-lg-3">
                                    <label class="form-label fw-semibold">Status</label>
                                    <select class="form-select form-select-sm" name="status" required>
                                        @foreach (['Open', 'In Progress', 'Fixed', 'Closed'] as $st)
                                            <option value="{{ $st }}" @selected($st === 'Open')>
                                                {{ $st }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- ISSUE --}}
                    <div class="card mb-3">
                        <div class="card-body">

                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar avatar-md me-3">
                                    <div class="avatar-name rounded-circle bg-primary-subtle text-primary">
                                        <span class="fas fa-tools"></span>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-0">Issue & Assignment</h6>
                                    <small class="text-muted">Define issue and assign technician</small>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-lg-6">
                                    <label class="form-label fw-semibold">Issue Type</label>
                                    <select class="form-select form-select-sm" name="issue_type" required>
                                        @foreach (['Camera', 'Monitor', 'DVR', 'Wiring', 'Power', 'Other'] as $it)
                                            <option value="{{ $it }}">{{ $it }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-6">
                                    <label class="form-label fw-semibold">Assign To</label>
                                    <select class="form-select form-select-sm" name="assigned_to">
                                        <option value="">Unassigned</option>
                                        @foreach ($agents as $a)
                                            <option value="{{ $a->id }}">
                                                {{ $a->full_name }} (IT Officer / Technician)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- ITEMS --}}
                    <div class="card mb-3">
                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="mb-0">
                                        <span class="fas fa-box-open text-primary me-2"></span>Items Used
                                    </h6>
                                    <small class="text-muted">Optional parts used</small>
                                </div>

                                <button type="button" class="btn btn-primary btn-sm rounded-pill"
                                    onclick="cctvAddItemRow('create-items-wrapper')">
                                    <span class="fas fa-plus me-1"></span>Add Item
                                </button>
                            </div>

                            <div id="create-items-wrapper">
                                <div class="item-row mb-2">
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
                                            <input type="number" min="1" class="form-control form-control-sm"
                                                name="items[0][qty_used]" placeholder="Qty">
                                        </div>

                                        <div class="col-lg-3">
                                            <label class="form-label fs-11 fw-semibold text-muted">Remarks</label>
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
                            </div>

                        </div>
                    </div>

                    {{-- WORK --}}
                    <div class="card">
                        <div class="card-body">

                            <h6 class="mb-3">
                                <span class="fas fa-clipboard text-primary me-2"></span>Work Details
                            </h6>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Problem Details</label>
                                <textarea class="form-control" name="problem_details" rows="3" required></textarea>
                            </div>

                            <div>
                                <label class="form-label fw-semibold">Action Taken</label>
                                <textarea class="form-control" name="action_taken" rows="3"></textarea>
                            </div>

                        </div>
                    </div>

                </div>

                {{-- FOOTER --}}
                <div class="modal-footer bg-white px-4 py-3">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary btn-sm">
                        <span class="fas fa-save me-1"></span>Save Job Order
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
