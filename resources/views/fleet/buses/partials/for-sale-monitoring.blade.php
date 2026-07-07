<div class="card mb-3">
    <div class="card-header bg-body-tertiary">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0 fleet-section-title">For Sale Units With Breakdown</h5>
                <small class="fleet-muted">
                    Connected to database table: bus_for_sale_records.
                </small>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('fleet.for-sale-units.index') }}" class="btn btn-sm btn-falcon-default">
                    Manage For Sale
                </a>

                <a href="{{ route('fleet.for-sale-units.create') }}" class="btn btn-sm btn-primary">
                    Add Unit
                </a>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 fleet-table">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th class="text-end">Mechanical Breakdown</th>
                        <th class="text-end">Accident Related</th>
                        <th class="text-end">On Hold due to Plate Reg.</th>
                        <th class="text-end">Breakdown Total</th>
                        <th class="text-end">Running Condition</th>
                        <th class="text-end">Total Units For Sale</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($for_sale_summary['rows'] as $company => $data)
                        <tr>
                            <td class="fw-bold">{{ $company }}</td>
                            <td class="text-end">{{ number_format($data['mechanical_breakdown']) }}</td>
                            <td class="text-end">{{ number_format($data['accident_related']) }}</td>
                            <td class="text-end">{{ number_format($data['on_hold']) }}</td>
                            <td class="text-end fw-bold text-danger">
                                {{ number_format($data['breakdown_total']) }}
                            </td>
                            <td class="text-end fw-bold text-success">
                                {{ number_format($data['running_condition']) }}
                            </td>
                            <td class="text-end fw-bold">
                                {{ number_format($data['total_for_sale']) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No for sale monitoring data available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                <tfoot>
                    <tr class="fleet-total-box">
                        <th>Total</th>
                        <th class="text-end">
                            {{ number_format($for_sale_summary['mechanical_breakdown_total']) }}
                        </th>
                        <th class="text-end">
                            {{ number_format($for_sale_summary['accident_related_total']) }}
                        </th>
                        <th class="text-end">
                            {{ number_format($for_sale_summary['on_hold_total']) }}
                        </th>
                        <th class="text-end text-danger">
                            {{ number_format($for_sale_summary['breakdown_total']) }}
                        </th>
                        <th class="text-end text-success">
                            {{ number_format($for_sale_summary['running_condition_total']) }}
                        </th>
                        <th class="text-end">
                            {{ number_format($for_sale_summary['total_for_sale']) }}
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header bg-body-tertiary">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0 fleet-section-title">Detailed For Sale Monitoring</h5>
                <small class="fleet-muted">
                    Paginated records from the For Sale database.
                </small>
            </div>

            @if ($for_sale_records instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <span class="badge badge-soft badge-subtle-secondary text-secondary">
                    {{ number_format($for_sale_records->total()) }} record(s)
                </span>
            @endif
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 fleet-table">
                <thead>
                    <tr>
                        <th>Bus Number</th>
                        <th>Plate Number</th>
                        <th>Company</th>
                        <th>Garage</th>
                        <th>Status</th>
                        <th>Storage Area</th>
                        <th>Breakdown Start</th>
                        <th>Breakdown End</th>
                        <th>Column 11</th>
                        <th class="text-end">Days</th>
                        <th>Unit Location</th>
                        <th>Progress</th>
                        <th>Remarks</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($for_sale_records as $record)
                        <tr>
                            <td class="fw-bold">{{ $record->bus_no }}</td>
                            <td>{{ $record->plate_no ?? '—' }}</td>
                            <td>{{ $record->company ?? '—' }}</td>
                            <td>{{ $record->garage ?? '—' }}</td>
                            <td>
                                <span class="badge badge-soft {{ $record->status_badge_class }}">
                                    {{ $record->status_label }}
                                </span>
                            </td>
                            <td>{{ $record->storage_area ?? '—' }}</td>
                            <td>{{ $record->breakdown_start_date?->format('M d, Y') ?? '—' }}</td>
                            <td>{{ $record->breakdown_end_date?->format('M d, Y') ?? '—' }}</td>
                            <td>{{ $record->column_11 ?? '—' }}</td>
                            <td class="text-end fw-bold">
                                {{ number_format($record->live_days_in_breakdown) }}
                            </td>
                            <td>{{ $record->unit_location ?? '—' }}</td>
                            <td>{{ $record->progress ?? '—' }}</td>
                            <td class="text-muted">{{ $record->remarks ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="text-center text-muted py-4">
                                No for sale records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($for_sale_records instanceof \Illuminate\Pagination\LengthAwarePaginator && $for_sale_records->hasPages())
        <div class="card-footer bg-body-tertiary">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <small class="fleet-muted">
                    Showing
                    <strong>{{ $for_sale_records->firstItem() ?? 0 }}</strong>
                    to
                    <strong>{{ $for_sale_records->lastItem() ?? 0 }}</strong>
                    of
                    <strong>{{ number_format($for_sale_records->total()) }}</strong>
                    for-sale record(s)
                </small>

                <div>
                    {{ $for_sale_records->links('pagination.custom') }}
                </div>
            </div>
        </div>
    @endif
</div>
