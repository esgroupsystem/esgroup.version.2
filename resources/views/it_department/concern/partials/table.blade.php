<div class="card jo-card border-0 shadow-sm">
    <div class="card-header bg-body-tertiary border-bottom border-200 py-3 px-4">
        <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between">
            <div>
                <h5 class="mb-1">Job Order List</h5>
                <small class="text-muted">Manage CCTV concerns and track assigned work.</small>
            </div>

            <div class="d-flex gap-2 align-items-center flex-wrap">
                <form method="GET" action="{{ route('concern.cctv.index') }}">
                    <div class="input-group input-group-sm search-box">
                        <span class="input-group-text bg-white border-300">
                            <span class="fa fa-search fs-10"></span>
                        </span>
                        <input class="form-control shadow-none border-300" name="q" type="search"
                            value="{{ request('q') }}" placeholder="Search JO #, bus no, reporter, issue..." />
                        <button class="btn btn-outline-secondary border-300" type="submit">
                            Search
                        </button>
                    </div>
                    <input type="hidden" name="status" value="{{ request('status') }}">
                </form>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive jo-table-wrap">
            <table class="table align-middle mb-0 jo-table">
                <thead>
                    <tr>
                        <th class="ps-4">JO #</th>
                        <th>Bus No</th>
                        <th>Issue Type</th>
                        <th>Items Used</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th class="text-end pe-4" style="width: 160px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jobOrders as $jo)
                        @php
                            $badgeClass = match ($jo->status) {
                                'Open' => 'badge-subtle-warning',
                                'In Progress' => 'badge-subtle-info',
                                'Fixed' => 'badge-subtle-success',
                                'Closed' => 'badge-subtle-secondary',
                                default => 'badge-subtle-primary',
                            };
                        @endphp

                        <tr>
                            <td class="ps-4">
                                <div class="fw-semi-bold text-dark">{{ $jo->jo_no }}</div>
                                <div class="small text-muted">{{ $jo->reported_by ?: '—' }}</div>
                            </td>
                            <td>
                                <div class="fw-semi-bold">{{ $jo->bus_no }}</div>
                            </td>
                            <td>
                                <span class="table-chip">{{ $jo->issue_type }}</span>
                            </td>
                            <td class="text-muted">
                                @forelse($jo->usedItems as $used)
                                    <div class="small mb-1">
                                        {{ $used->inventoryItem->item_name ?? 'Item' }}
                                        <span class="text-dark fw-semi-bold">× {{ $used->qty_used }}</span>
                                    </div>
                                @empty
                                    —
                                @endforelse
                            </td>
                            <td>
                                <span class="badge rounded-pill {{ $badgeClass }}">{{ $jo->status }}</span>
                            </td>
                            <td>
                                <span class="text-muted">{{ optional($jo->assignee)->full_name ?? '—' }}</span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <button class="btn btn-sm btn-falcon-default" type="button" data-bs-toggle="modal"
                                        data-bs-target="#editModal{{ $jo->id }}">
                                        <span class="fas fa-eye me-1"></span> View
                                    </button>

                                    <form action="{{ route('concern.cctv.destroy', $jo->id) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Delete this job order?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-falcon-danger" type="submit">
                                            <span class="fas fa-trash"></span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <span class="fas fa-video"></span>
                                    </div>
                                    <h6 class="mb-1">No Job Orders Found</h6>
                                    <p class="text-muted mb-0">Try changing your filters or create a new job order.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer bg-body-tertiary border-top border-200 py-3 px-4">
        <div class="d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center">
            <small class="text-muted">
                Showing {{ $jobOrders->firstItem() ?? 0 }} to {{ $jobOrders->lastItem() ?? 0 }} of
                {{ $jobOrders->total() }} entries
            </small>

            <div class="ms-md-auto">
                {{ $jobOrders->links('pagination.custom') }}
            </div>
        </div>
    </div>
</div>
