<div class="modal fade" id="accountingPoModal{{ $order->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-light">
                <h5 class="modal-title">
                    <span class="fas fa-file-invoice text-primary me-2"></span>
                    Purchase Order <strong>{{ $order->po_number }}</strong>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('purchase.update', $order->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="card shadow-none border-0 mb-3 bg-100">
                        <div class="card-body p-3">
                            <h6 class="fw-bold text-primary mb-3">
                                <span class="fas fa-user me-1"></span> Request Information
                            </h6>
                            <div class="row g-3 fs-10">
                                <div class="col-md-6"><strong>Requested
                                        By:</strong><br>{{ $order->requester->full_name }}</div>
                                <div class="col-md-6"><strong>Email:</strong><br>{{ $order->requester->email }}</div>
                                <div class="col-md-6"><strong>Date
                                        Created:</strong><br>{{ $order->created_at->format('d/m/Y') }}</div>
                                <div class="col-md-6"><strong>Garage:</strong><br>{{ $order->garage }}</div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- ITEM LIST --}}
                    <h6 class="fw-bold text-primary mb-2"><span class="fas fa-box me-1"></span> Item List </h6>

                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-hover">
                            <thead class="bg-200">
                                <tr>
                                    <th>Category</th>
                                    <th>Product Name</th>
                                    <th class="text-center">Unit</th>
                                    <th class="text-center">Requested Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->items as $item)
                                    <tr>    
                                        <td>{{ $item->product->category->name ?? '—' }}</td>
                                        <td>{{ $item->product->product_name }}</td>
                                        <td class="text-center">{{ $item->product->unit ?? 'pc' }}</td>
                                        <td class="text-center fw-bold">{{ $item->qty }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- ACCOUNTING SECTION --}}
                    <h6 class="fw-bold text-primary mb-2"><span class="fas fa-calculator me-1"></span> Accounting
                        Purchase </h6>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="bg-200">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Purchased Qty</th>
                                    <th class="text-center">Store Name</th>
                                    <th class="text-center">Remove?</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td>{{ $item->product->product_name }}</td>

                                        <td class="text-center">
                                            <input type="number" name="items[{{ $item->id }}][purchased_qty]"
                                                class="form-control form-control-sm text-center" min="0"
                                                max="{{ $item->qty }}" value="{{ $item->purchased_qty }}">
                                        </td>

                                        <td class="text-center">
                                            <input type="text" name="items[{{ $item->id }}][store_name]"
                                                class="form-control form-control-sm" value="{{ $item->store_name }}">
                                        </td>

                                        <td class="text-center">
                                            <input type="checkbox" name="items[{{ $item->id }}][remove]"
                                                value="1" {{ $item->removed ? 'checked' : '' }}>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- STATUS --}}
                    <div class="mt-3">
                        <label class="fw-bold">Purchase Order Status</label>
                        <select class="form-select form-select-sm" name="status">
                            <option value="Approved" {{ $order->status == 'Approved' ? 'selected' : '' }}>Approved
                            </option>
                            <option value="Partial Order" {{ $order->status == 'Partial Order' ? 'selected' : '' }}>
                                Partial Order</option>
                            <option value="For Delivery" {{ $order->status == 'For Delivery' ? 'selected' : '' }}>For
                                Delivery</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer bg-light">
                    <button class="btn btn-success btn-sm" type="submit">
                        <span class="fas fa-save me-1"></span> Save
                    </button>

                    <button type="button" class="btn btn-falcon-default" data-bs-dismiss="modal">
                        <span class="fas fa-times me-1"></span> Close
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
