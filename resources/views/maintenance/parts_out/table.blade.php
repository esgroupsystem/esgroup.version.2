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
                <th class="text-center" style="width: 140px;">Action</th>
            </tr>
        </thead>

        <tbody>
            @forelse($partsOuts as $row)
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
                @endphp

                <tr>
                    {{-- Parts Out Number --}}
                    <td class="fw-semibold text-primary">
                        {{ $row->parts_out_number }}
                    </td>

                    {{-- Vehicle --}}
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
                            <span class="text-muted">
                                No vehicle selected
                            </span>
                        @endif
                    </td>
                    <td>
                        {{ $row->mechanic_name ?? '—' }}
                    </td>
                    <td>
                        @if ($row->issued_date)
                            {{ \Carbon\Carbon::parse($row->issued_date)->format('M d, Y') }}
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        {{ $row->job_order_no ?? '—' }}
                    </td>
                    <td>
                        <span
                            class="badge bg-{{ $statusClass }}-subtle text-{{ $statusClass }} border border-{{ $statusClass }}-subtle">
                            {{ $statusLabel }}
                        </span>
                    </td>
                    <td>
                        {{ $row->creator->full_name ?? ($row->creator->name ?? '—') }}
                    </td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            <a href="{{ route('parts-out.show', $row->id) }}" class="btn btn-falcon-info btn-sm"
                                data-bs-toggle="tooltip" title="View Details">
                                <span class="fas fa-eye"></span>
                            </a>
                            @if ($row->status === 'posted')
                                @can('parts-out.rollback')
                                    <form action="{{ route('parts-out.rollback', $row) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to rollback this Parts Out? This will return all used quantities back to stock and remove this record from Vehicle History.');">
                                        @csrf
                                        @method('PATCH')

                                        <input type="hidden" name="rollback_reason" value="Rollback from Parts Out table">

                                        <button type="submit" class="btn btn-falcon-warning btn-sm"
                                            data-bs-toggle="tooltip" title="Rollback">
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
                    <td colspan="8" class="text-center py-4 text-muted">
                        <span class="fas fa-inbox fa-2x mb-2 d-block text-300"></span>
                        No parts out records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if (method_exists($partsOuts, 'hasPages') && $partsOuts->hasPages())
    <div class="d-flex justify-content-end mt-3">
        {{ $partsOuts->links('pagination::bootstrap-5') }}
    </div>
@endif
