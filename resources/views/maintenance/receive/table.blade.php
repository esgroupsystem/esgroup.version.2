<div class="table-responsive">
    <table class="table table-sm table-hover align-middle fs-10 mb-0">
        <thead class="bg-200 text-900">
            <tr>
                <th>Receiving No.</th>
                <th>Delivered By</th>
                <th class="text-center">Delivery Date</th>
                <th class="text-center">Items</th>
                <th class="text-center">Received By</th>
                <th class="text-center">Created</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($receivings as $receiving)
                <tr>
                    <td class="fw-bold">{{ $receiving->receiving_number }}</td>
                    <td>{{ $receiving->delivered_by }}</td>
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($receiving->delivery_date)->format('d/m/Y') }}
                    </td>
                    <td class="text-center">{{ $receiving->items->count() }}</td>
                    <td class="text-center">{{ $receiving->receiver->name ?? 'N/A' }}</td>
                    <td class="text-center">{{ $receiving->created_at->format('d/m/Y') }}</td>
                    <td class="text-center">
                        <a href="{{ route('receivings.show', $receiving->id) }}" class="btn btn-falcon-default btn-sm">
                            <span class="fas fa-eye"></span> View
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No receiving records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="card-footer">
    {{ $receivings->links('pagination.custom') }}
</div>
