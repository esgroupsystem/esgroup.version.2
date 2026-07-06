<div class="card-body p-0">
    <div class="table-responsive scrollbar">
        <table class="table table-hover align-middle mb-0 fs-10 receiving-table">
            <thead class="bg-200 text-900">
                <tr>
                    <th class="ps-3">Receiving</th>
                    <th>Garage</th>
                    <th>Delivered By</th>
                    <th>Delivery Date</th>
                    <th class="text-center">Items</th>
                    <th>Remarks</th>
                    <th>Received By</th>
                    <th>Date Created</th>
                    <th class="text-center pe-3">Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($receivings as $receiving)
                    @php
                        $itemsCount = $receiving->items_count ?? ($receiving->items->count() ?? 0);
                        $deliveryDate = $receiving->delivery_date
                            ? \Carbon\Carbon::parse($receiving->delivery_date)
                            : null;
                    @endphp

                    <tr>
                        <td class="ps-3">
                            <div class="receiving-number-chip bg-body-tertiary rounded-2 px-3 py-2">
                                <div class="fw-bold text-primary">
                                    {{ $receiving->receiving_number }}
                                </div>
                                <div class="text-500 fs-11">
                                    Receiving reference
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="ui-icon ui-icon-sm bg-info-subtle text-info">
                                    <span class="fas fa-warehouse"></span>
                                </div>

                                <div>
                                    <div class="fw-semibold text-900">
                                        {{ $receiving->location->name ?? 'N/A' }}
                                    </div>
                                    <div class="text-500 fs-11">
                                        Stock location
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="ui-icon ui-icon-sm bg-secondary-subtle text-secondary">
                                    <span class="fas fa-truck"></span>
                                </div>

                                <div>
                                    <div class="fw-semibold text-900">
                                        {{ $receiving->delivered_by ?: 'Not specified' }}
                                    </div>
                                    <div class="text-500 fs-11">
                                        Supplier / driver / handler
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td>
                            @if ($deliveryDate)
                                <div class="d-flex align-items-center gap-2">
                                    <div class="ui-icon ui-icon-sm bg-primary-subtle text-primary">
                                        <span class="fas fa-calendar-day"></span>
                                    </div>

                                    <div>
                                        <div class="fw-semibold text-900">
                                            {{ $deliveryDate->format('M d, Y') }}
                                        </div>
                                        <div class="text-500 fs-11">
                                            {{ $deliveryDate->format('l') }}
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="badge badge-subtle-secondary">No date</span>
                            @endif
                        </td>

                        <td class="text-center">
                            <span class="badge badge-subtle-primary rounded-pill px-3 py-2">
                                <span class="fas fa-box-open me-1"></span>
                                {{ $itemsCount }} item{{ $itemsCount === 1 ? '' : 's' }}
                            </span>
                        </td>

                        <td style="min-width: 240px;">
                            @if ($receiving->remarks)
                                <div class="text-700 remarks-clamp">
                                    {{ $receiving->remarks }}
                                </div>
                            @else
                                <span class="text-500 fst-italic">
                                    No remarks provided.
                                </span>
                            @endif
                        </td>

                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="ui-icon ui-icon-sm bg-success-subtle text-success">
                                    <span class="fas fa-user-check"></span>
                                </div>

                                <div>
                                    <div class="fw-semibold text-900">
                                        {{ $receiving->receiver->full_name ?? ($receiving->receiver->name ?? 'System') }}
                                    </div>
                                    <div class="text-500 fs-11">
                                        Encoder
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="ui-icon ui-icon-sm bg-warning-subtle text-warning">
                                    <span class="fas fa-clock"></span>
                                </div>

                                <div>
                                    <div class="fw-semibold text-900">
                                        {{ optional($receiving->created_at)->format('M d, Y') ?? 'N/A' }}
                                    </div>
                                    <div class="text-500 fs-11">
                                        {{ optional($receiving->created_at)->format('h:i A') ?? '' }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="text-center pe-3">
                            <a href="{{ route('receivings.show', $receiving->id) }}"
                                class="btn btn-falcon-primary btn-sm">
                                <span class="fas fa-eye me-1"></span>
                                Details
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">
                            <div
                                class="receiving-empty-state d-flex flex-column align-items-center justify-content-center px-3 py-5">
                                <div class="ui-icon bg-primary-subtle text-primary mb-3"
                                    style="width: 64px; height: 64px; font-size: 1.5rem;">
                                    <span class="fas fa-truck-loading"></span>
                                </div>

                                <h5 class="mb-1 text-900">
                                    No receiving records found
                                </h5>

                                <p class="text-600 mb-3">
                                    No delivery record matched your search keyword.
                                </p>

                                <div class="d-flex flex-column flex-sm-row gap-2">
                                    <a href="{{ route('receivings.index') }}" class="btn btn-falcon-default btn-sm">
                                        <span class="fas fa-redo me-1"></span>
                                        Reset Search
                                    </a>

                                    <a href="{{ route('receivings.create') }}" class="btn btn-primary btn-sm">
                                        <span class="fas fa-plus me-1"></span>
                                        Create Receiving
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($receivings->hasPages())
    <div class="card-footer bg-body-tertiary border-top py-2">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <div class="text-500 fs-10">
                Showing
                <strong>{{ $receivings->firstItem() }}</strong>
                to
                <strong>{{ $receivings->lastItem() }}</strong>
                of
                <strong>{{ $receivings->total() }}</strong>
                receiving record(s)
            </div>

            <div>
                {{ $receivings->appends(request()->query())->links('pagination.custom') }}
            </div>
        </div>
    </div>
@endif
