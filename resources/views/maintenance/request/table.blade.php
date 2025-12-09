<div class="table-responsive scrollbar">
    <table class="table table-sm align-middle fs-10 mb-0">
        <thead class="bg-200">
            <tr>
                <th class="text-900 align-middle">Order</th>
                <th class="text-900 align-middle text-center">Date</th>
                <th class="text-900 align-middle text-center">Ship To</th>
                <th class="text-900 align-middle text-center">Status</th>
                <th style="width:40px;"></th>
            </tr>
        </thead>

        <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td class="order py-3 align-middle white-space-nowrap">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#poModal{{ $order->id }}">
                            <strong>{{ $order->po_number }}</strong>
                        </a>
                        by <strong>{{ $order->requester->full_name }}</strong><br>
                        <a>{{ $order->requester->email }}</a>
                    </td>

                    <td class="date py-3 align-middle text-center">
                        {{ $order->created_at->format('d/m/Y') }}
                    </td>

                    <td class="ship py-3 align-middle text-center">
                        {{ $order->garage }}<br>
                        <span class="text-500">Purchase Order</span>
                    </td>

                    <td class="status py-3 align-middle text-center">
                        @if ($order->status == 'Approved')
                            <span class="badge rounded-pill badge-subtle-success">
                                Approved <span class="fas fa-check ms-1"></span>
                            </span>
                        @elseif($order->status == 'For Delivery')
                            <span class="badge rounded-pill badge-subtle-info">
                                For Delivery <span class="fas fa-check ms-1"></span>
                            </span>
                        @elseif($order->status == 'Partial Order')
                            <span class="badge rounded-pill badge-subtle-primary">
                                Partial Order <span class="fas fa-check ms-1"></span>
                            </span>
                        @else
                            <span class="badge rounded-pill badge-subtle-warning">
                                Pending <span class="fas fa-stream ms-1"></span>
                            </span>
                        @endif
                    </td>

                    <td class="py-3 align-middle text-end">
                        <div class="dropdown position-static">
                            <button class="btn btn-link btn-sm text-600 dropdown-toggle btn-reveal" type="button"
                                data-bs-toggle="dropdown">
                                <span class="fas fa-ellipsis-h fs-10"></span>
                            </button>

                            <div class="dropdown-menu dropdown-menu-end py-0 shadow-sm">
                                <div class="py-2">
                                    <form action="{{ route('request.update', $order->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')

                                        <button type="submit" name="status" value="Approved"
                                            class="dropdown-item">Approved</button>

                                        <button type="submit" name="status" value="Pending"
                                            class="dropdown-item">Pending</button>

                                        <div class="dropdown-divider"></div>

                                        <a class="dropdown-item text-danger" href="#">Delete</a>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-3 text-muted">No orders found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="my-3 d-flex justify-content-end px-3">
    {{ $orders->links('pagination.custom') }}
</div>
