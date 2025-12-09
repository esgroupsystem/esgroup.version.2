<div class="table-responsive scrollbar">
    <table class="table table-sm align-middle fs-10 mb-0">
        <thead class="bg-200">
            <tr>
                <th>Order</th>
                <th class="text-center">Date</th>
                <th class="text-center">Ship To</th>
                <th class="text-center">Status</th>
                <th style="width:40px;"></th>
            </tr>
        </thead>

        <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td class="py-2 white-space-nowrap">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#accountingPoModal{{ $order->id }}">
                            <strong>{{ $order->po_number }}</strong>
                        </a>
                        by <strong>{{ $order->requester->full_name }}</strong><br>
                        <span>{{ $order->requester->email }}</span>
                    </td>

                    <td class="text-center">
                        {{ $order->created_at->format('d/m/Y') }}
                    </td>

                    <td class="text-center">
                        {{ $order->garage }}<br>
                        <span class="text-500">Purchase Order</span>
                    </td>

                    <td class="text-center">
                        <span class="badge rounded-pill bg-info">
                            {{ $order->status }}
                        </span>
                    </td>

                    <td class="text-end">
                        <div class="dropdown position-static">
                            <button class="btn btn-link btn-sm text-600 dropdown-toggle btn-reveal" type="button"
                                data-bs-toggle="dropdown">
                                <span class="fas fa-ellipsis-h fs-10"></span>
                            </button>

                            <div class="dropdown-menu dropdown-menu-end py-0 shadow-sm">
                                <div class="py-2">

                                    <button class="dropdown-item" data-bs-toggle="modal"
                                        data-bs-target="#accountingPoModal{{ $order->id }}">
                                        <i class="fas fa-eye me-2"></i> View / Update
                                    </button>

                                    <div class="dropdown-divider"></div>

                                    <form action="#" method="POST">
                                        @csrf
                                        @method('DELETE')

                                        <button class="dropdown-item text-danger" type="submit">
                                            <i class="fas fa-trash me-2"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>

                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-3 text-muted">No approved orders found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="my-3 d-flex justify-content-end px-3">
    {{ $orders->links('pagination.custom') }}
</div>
