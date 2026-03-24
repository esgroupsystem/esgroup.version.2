<div class="card-body p-0">
    <div class="table-responsive scrollbar">
        <table class="table table-hover table-striped align-middle mb-0 fs-10">
            <thead class="bg-200 text-900">
                <tr>
                    <th class="ps-3">Transfer No.</th>
                    <th>Route</th>
                    <th>Requested / Received By</th>
                    <th class="text-center">Items</th>
                    <th>Remarks</th>
                    <th>Date Created</th>
                    <th class="text-center pe-3">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transfers as $transfer)
                    <tr>
                        <td class="ps-3">
                            <div class="fw-semibold text-primary">{{ $transfer->transfer_number }}</div>
                            <div class="text-500 fs-11">
                                Created by:
                                {{ $transfer->creator->name ?? 'System' }}
                            </div>
                        </td>

                        <td>
                            <div class="d-flex flex-column gap-1">
                                <div>
                                    <span class="badge badge-subtle-primary px-2 py-1">
                                        {{ $transfer->fromLocation->name ?? 'N/A' }}
                                    </span>
                                </div>
                                <div class="text-500 fs-11">
                                    <span class="fas fa-arrow-down me-1"></span>
                                    to
                                </div>
                                <div>
                                    <span class="badge badge-subtle-info px-2 py-1">
                                        {{ $transfer->toLocation->name ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="mb-1">
                                <span class="text-500 fs-11">Requested By</span>
                                <div class="fw-semibold">{{ $transfer->requested_by ?: '—' }}</div>
                            </div>
                            <div>
                                <span class="text-500 fs-11">Received By</span>
                                <div class="fw-semibold">{{ $transfer->received_by ?: '—' }}</div>
                            </div>
                        </td>

                        <td class="text-center">
                            <span class="badge badge-subtle-secondary px-3 py-2">
                                {{ $transfer->items_count ?? $transfer->items->count() }} item(s)
                            </span>
                        </td>

                        <td style="min-width: 220px;">
                            <div class="text-700">
                                {{ $transfer->remarks ?: 'No remarks provided.' }}
                            </div>
                        </td>

                        <td>
                            <div class="fw-semibold">
                                {{ optional($transfer->created_at)->format('M d, Y') }}
                            </div>
                            <div class="text-500 fs-11">
                                {{ optional($transfer->created_at)->format('h:i A') }}
                            </div>
                        </td>

                        <td class="text-center pe-3">
                            <a href="{{ route('stock-transfers.show', $transfer->id) }}"
                                class="btn btn-falcon-default btn-sm">
                                <span class="fas fa-eye me-1"></span> View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center justify-content-center text-muted">
                                <span class="fas fa-exchange-alt fs-3 mb-3 text-300"></span>
                                <h6 class="mb-1">No stock transfers found</h6>
                                <p class="mb-0 fs-10">
                                    Try changing your search keyword or create a new transfer record.
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($transfers->hasPages())
    <div class="card-footer bg-body-tertiary py-2">
        <div class="d-flex justify-content-end">
            {{ $transfers->links('pagination.custom') }}
        </div>
    </div>
@endif
