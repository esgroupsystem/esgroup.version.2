<div id="{{ $id }}"
    data-list='{
        "valueNames":["ticket_id","requester","job_type","status","date"],
        "page":10,
        "pagination":true
    }'>

    <div class="table-responsive scrollbar">
        <table class="table table-hover table-striped fs-10 mb-0">
            <thead class="bg-200 text-900">
                <tr>
                    <th class="sort" data-sort="ticket_id">Ticket ID</th>
                    <th class="sort" data-sort="requester">Requester</th>
                    <th class="sort" data-sort="job_type">Job Issue</th>
                    <th class="sort" data-sort="status">Status</th>
                    <th class="sort" data-sort="date">Date</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>

            <tbody class="list">

                @forelse ($list as $job)
                    <tr>
                        <td class="ticket_id">JOB-{{ str_pad($job->id, 5, '0', STR_PAD_LEFT) }}</td>

                        <td class="requester">{{ $job->job_creator ?? 'Unknown' }}</td>

                        <td class="job_type">{{ $job->job_type ?? 'N/A' }}</td>

                        <td class="status">
                            @if ($job->job_status == 'Pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($job->job_status == 'In Progress')
                                <span class="badge bg-info text-dark">In Progress</span>
                            @elseif($job->job_status == 'Completed')
                                <span class="badge bg-success">Completed</span>
                            @else
                                <span class="badge bg-secondary">{{ $job->job_status }}</span>
                            @endif
                        </td>

                        <td class="date">
                            {{ \Carbon\Carbon::parse($job->job_date_filled)->format('Y-m-d') }}
                        </td>

                        <td class="text-center">
                            <a href="{{ route('tickets.joborder.view', $job->id) }}"
                                class="btn btn-sm btn-info text-white">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-3 text-muted">No records found.</td>
                    </tr>
                @endforelse

            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    <div class="d-flex justify-content-center my-3">
        <button class="btn btn-sm btn-falcon-default me-1" data-list-pagination="prev">
            <span class="fas fa-chevron-left"></span>
        </button>

        <ul class="pagination mb-0"></ul>

        <button class="btn btn-sm btn-falcon-default ms-1" data-list-pagination="next">
            <span class="fas fa-chevron-right"></span>
        </button>
    </div>

</div>
