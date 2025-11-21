<div class="table-responsive scrollbar">
    <table class="table table-hover table-striped fs-10 mb-0">
        <thead class="bg-200 text-900">
            <tr>
                <th>Bus Info</th>
                <th>Requester</th>
                <th>Job Issue</th>
                <th>Status</th>
                <th>Date</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($list as $job)
                <tr>
                    <td>
                        {{ $job->bus->name ?? 'Unknown Bus' }}
                        @if ($job->bus?->body_number)
                            - {{ $job->bus->body_number }}
                        @endif
                    </td>

                    <td>{{ $job->job_creator }}</td>

                    <td>{{ $job->job_type }}</td>

                    <td>
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

                    <td>{{ \Carbon\Carbon::parse($job->job_date_filled)->format('Y-m-d') }}</td>

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

<div class="my-3 d-flex justify-content-end px-3">
    {{ $list->links('pagination.custom') }}
</div>
