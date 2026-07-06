<div class="card-body p-0">
    <div class="table-responsive scrollbar">
        <table class="table table-hover align-middle mb-0 fs-10 parts-out-table">
            <thead class="bg-200 text-900">
                <tr>
                    <th class="ps-3">Parts Out</th>
                    <th>Vehicle</th>
                    <th>Garage</th>
                    <th>Mechanic</th>
                    <th>Date</th>
                    <th>JO No.</th>
                    <th class="text-center">Items</th>
                    <th>Status</th>
                    <th>Encoded By</th>
                    <th class="text-center pe-3">Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($partsOuts as $row)
                    @php
                        $status = strtolower($row->status ?? '');

                        $statusClass = match ($status) {
                            'posted' => 'success',
                            'cancelled' => 'danger',
                            'rolled_back' => 'warning',
                            default => 'secondary',
                        };

                        $statusLabel = match ($status) {
                            'posted' => 'Posted',
                            'cancelled' => 'Cancelled',
                            'rolled_back' => 'Rolled Back',
                            default => ucfirst(str_replace('_', ' ', $row->status ?? 'N/A')),
                        };

                        $itemsCount = $row->items_count ?? ($row->items->count() ?? 0);
                    @endphp

                    <tr>
                        <td class="ps-3">
                            <div class="parts-out-number-chip bg-body-tertiary rounded-2 px-3 py-2">
                                <div class="fw-bold text-primary">
                                    {{ $row->parts_out_number }}
                                </div>
                                <div class="text-500 fs-11">
                                    Parts out reference
                                </div>
                            </div>
                        </td>

                        <td style="min-width: 220px;">
                            @if ($row->vehicle)
                                <div class="d-flex align-items-start gap-2">
                                    <div class="ui-icon ui-icon-sm bg-info-subtle text-info">
                                        <span class="fas fa-bus"></span>
                                    </div>

                                    <div>
                                        <div class="fw-semibold text-900">
                                            {{ $row->vehicle->plate_number ?? 'N/A' }}
                                        </div>

                                        <div class="text-500 fs-11">
                                            {{ $row->vehicle->body_number ?? 'No Body No.' }}
                                            @if (!empty($row->vehicle->name))
                                                | {{ $row->vehicle->name }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="text-500 fst-italic">No vehicle selected</span>
                            @endif
                        </td>

                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="ui-icon ui-icon-sm bg-primary-subtle text-primary">
                                    <span class="fas fa-warehouse"></span>
                                </div>

                                <div>
                                    <div class="fw-semibold text-900">
                                        {{ $row->location->name ?? 'N/A' }}
                                    </div>
                                    <div class="text-500 fs-11">
                                        Source stock
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="ui-icon ui-icon-sm bg-secondary-subtle text-secondary">
                                    <span class="fas fa-user-cog"></span>
                                </div>

                                <div>
                                    <div class="fw-semibold text-900">
                                        {{ $row->mechanic_name ?? '—' }}
                                    </div>
                                    <div class="text-500 fs-11">
                                        Mechanic
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td>
                            @if ($row->issued_date)
                                <div class="fw-semibold text-900">
                                    {{ \Carbon\Carbon::parse($row->issued_date)->format('M d, Y') }}
                                </div>
                                <div class="text-500 fs-11">
                                    {{ \Carbon\Carbon::parse($row->issued_date)->format('l') }}
                                </div>
                            @else
                                —
                            @endif
                        </td>

                        <td>
                            <span class="badge badge-subtle-secondary rounded-pill px-3 py-2">
                                {{ $row->job_order_no ?: 'No JO' }}
                            </span>
                        </td>

                        <td class="text-center">
                            <span class="badge badge-subtle-primary rounded-pill px-3 py-2">
                                <span class="fas fa-box-open me-1"></span>
                                {{ $itemsCount }} item{{ $itemsCount === 1 ? '' : 's' }}
                            </span>
                        </td>

                        <td>
                            <span
                                class="badge bg-{{ $statusClass }}-subtle text-{{ $statusClass }} border border-{{ $statusClass }}-subtle rounded-pill px-3 py-2">
                                {{ $statusLabel }}
                            </span>
                        </td>

                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="ui-icon ui-icon-sm bg-success-subtle text-success">
                                    <span class="fas fa-user-check"></span>
                                </div>

                                <div>
                                    <div class="fw-semibold text-900">
                                        {{ $row->creator->full_name ?? ($row->creator->name ?? '—') }}
                                    </div>
                                    <div class="text-500 fs-11">
                                        Encoder
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="text-center pe-3">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="{{ route('parts-out.show', $row->id) }}"
                                    class="btn btn-falcon-primary btn-sm">
                                    <span class="fas fa-eye me-1"></span>
                                    Details
                                </a>

                                @if ($row->status === 'posted')
                                    @can('parts-out.rollback')
                                        <form action="{{ route('parts-out.rollback', $row) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Rollback this Parts Out transaction? All used quantities will be returned to stock.');">
                                            @csrf
                                            @method('PATCH')

                                            <input type="hidden" name="rollback_reason"
                                                value="Rollback from Parts Out table">

                                            <button type="submit" class="btn btn-falcon-warning btn-sm">
                                                <span class="fas fa-undo"></span>
                                            </button>
                                        </form>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">
                            <div
                                class="parts-out-empty-state d-flex flex-column align-items-center justify-content-center px-3 py-5">
                                <div class="ui-icon bg-primary-subtle text-primary mb-3"
                                    style="width: 64px; height: 64px; font-size: 1.5rem;">
                                    <span class="fas fa-tools"></span>
                                </div>

                                <h5 class="mb-1 text-900">
                                    No parts out records found
                                </h5>

                                <p class="text-600 mb-3">
                                    No issued parts record matched your search keyword.
                                </p>

                                <div class="d-flex flex-column flex-sm-row gap-2">
                                    <a href="{{ route('parts-out.index') }}" class="btn btn-falcon-default btn-sm">
                                        <span class="fas fa-redo me-1"></span>
                                        Reset Search
                                    </a>

                                    <a href="{{ route('parts-out.create') }}" class="btn btn-primary btn-sm">
                                        <span class="fas fa-plus me-1"></span>
                                        New Parts Out
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

@if (method_exists($partsOuts, 'hasPages') && $partsOuts->hasPages())
    <div class="card-footer bg-body-tertiary border-top py-2">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <div class="text-500 fs-10">
                Showing
                <strong>{{ $partsOuts->firstItem() }}</strong>
                to
                <strong>{{ $partsOuts->lastItem() }}</strong>
                of
                <strong>{{ $partsOuts->total() }}</strong>
                parts out record(s)
            </div>

            <div>
                {{ $partsOuts->appends(request()->query())->links('pagination.custom') }}
            </div>
        </div>
    </div>
@endif
