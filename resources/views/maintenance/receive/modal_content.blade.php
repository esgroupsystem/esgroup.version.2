{{-- REQUEST INFO --}}
<div class="card shadow-none border-0 bg-100 mb-3">
    <div class="card-body p-3 fs-10">
        <strong>Requester:</strong> {{ $po->requester->full_name }} <br>
        <strong>Garage:</strong> {{ $po->garage }} <br>
        <strong>Date:</strong> {{ $po->created_at->format('d/m/Y') }}
    </div>
</div>

{{-- ITEMS TABLE --}}
<div class="table-responsive">
    <table class="table table-sm table-hover align-middle">
        <thead class="bg-200">
            <tr>
                <th>Item</th>
                <th class="text-center">Requested</th>
                <th class="text-center">Purchased</th>
                <th class="text-center">Received</th>
                <th class="text-center">Remaining</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($po->items as $item)
                @php $remaining = $item->purchased_qty - $item->received_qty; @endphp

                <tr>
                    <td>
                        <strong>{{ $item->product->product_name }}</strong><br>
                        <span class="text-muted fs-9">{{ $item->product->details }}</span>
                    </td>

                    <td class="text-center">{{ $item->qty }}</td>
                    <td class="text-center">{{ $item->purchased_qty }}</td>
                    <td class="text-center text-primary">{{ $item->received_qty }}</td>
                    <td class="text-center text-warning">{{ $remaining }}</td>

                    <td class="text-center">

                        {{-- RECEIVE BUTTON --}}
                        @if ($remaining > 0)
                            <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#receiveModal"
                                onclick="setReceiveItem({{ $item->id }}, '{{ $item->product->product_name }}', {{ $remaining }})">
                                Receive
                            </button>
                        @endif

                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
