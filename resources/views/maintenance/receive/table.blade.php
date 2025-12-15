<div class="table-responsive">
    <table class="table table-sm table-hover align-middle fs-10 mb-0">
        <thead class="bg-200 text-900">
            <tr>
                <th>PO Number</th>
                <th>Requester</th>
                <th class="text-center">Status</th>
                <th class="text-center">Created</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>

            @foreach ($pos as $po)
                @php
                    $total = $po->items->sum('purchased_qty');
                    $received = $po->items->sum('received_qty');
                @endphp

                <tr>
                    <td class="fw-bold">{{ $po->po_number }}</td>
                    <td>{{ $po->requester->full_name ?? 'N/A' }}</td>

                    <td class="text-center">
                        @if ($total == 0)
                            <span class="badge bg-secondary">Not Purchased</span>
                        @elseif ($received == 0)
                            <span class="badge bg-warning text-dark">Waiting For Delivery</span>
                        @elseif ($received < $total)
                            <span class="badge bg-info text-dark">Partial</span>
                        @else
                            <span class="badge bg-success">Completed</span>
                        @endif
                    </td>

                    <td class="text-center">{{ $po->created_at->format('d/m/Y') }}</td>

                    <td class="text-center">
                        <button class="btn btn-falcon-default btn-sm" onclick="openPOModal({{ $po->id }})">
                            <span class="fas fa-box-open"></span>
                            Details
                        </button>
                    </td>
                </tr>
            @endforeach

        </tbody>

    </table>
</div>

<div class="card-footer">
    {{ $pos->links('pagination.custom') }}
</div>
