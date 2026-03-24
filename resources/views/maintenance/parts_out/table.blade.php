<div class="table-responsive scrollbar">
    <table class="table table-hover table-striped fs-10 mb-0">
        <thead class="bg-200 text-900">
            <tr>
                <th>Parts Out No.</th>
                <th>Vehicle</th>
                <th>Mechanic</th>
                <th>Date</th>
                <th>JO No.</th>
                <th>Status</th>
                <th>Encoded By</th>
                <th class="text-center" style="width: 120px;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($partsOuts as $row)
                <tr>
                    <td class="fw-semibold text-primary">{{ $row->parts_out_number }}</td>
                    <td>
                        @if ($row->vehicle)
                            <div class="fw-semibold">
                                {{ $row->vehicle->plate_number ?? 'N/A' }}
                            </div>
                            <small class="text-muted">
                                {{ $row->vehicle->body_number ?? 'No Body No.' }}
                                @if (!empty($row->vehicle->name))
                                    | {{ $row->vehicle->name }}
                                @endif
                            </small>
                        @else
                            <span class="text-muted">No vehicle selected</span>
                        @endif
                    </td>
                    <td>{{ $row->mechanic_name }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->issued_date)->format('M d, Y') }}</td>
                    <td>{{ $row->job_order_no ?? '—' }}</td>
                    <td>
                        @if ($row->status === 'posted')
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                Posted
                            </span>
                        @elseif($row->status === 'cancelled')
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                Cancelled
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">
                                {{ ucfirst($row->status) }}
                            </span>
                        @endif
                    </td>
                    <td>{{ $row->creator->full_name ?? '—' }}</td>
                    <td class="text-center">
                        <a href="{{ route('parts-out.show', $row->id) }}" class="btn btn-falcon-info btn-sm"
                            data-bs-toggle="tooltip" title="View Details">
                            <span class="fas fa-eye"></span>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        <span class="fas fa-inbox fa-2x mb-2 d-block text-300"></span>
                        No parts out records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($partsOuts->hasPages())
    <div class="d-flex justify-content-end mt-3">
        {{ $partsOuts->links('pagination::bootstrap-5') }}
    </div>
@endif
