<div id="{{ $id }}"
    data-list='{
        "valueNames":["bus_info","requester","job_type","status","date"],
        "page":10,
        "pagination":true
    }'>

    <div class="table-responsive scrollbar">
        <table class="table table-hover table-striped fs-10 mb-0">
            <thead class="bg-200 text-900">
                <tr>
                    <th class="sort" data-sort="bus_info">Bus Info</th>
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
                        {{-- 🚌 Bus Info --}}
                        <td class="bus_info">
                            @php
                                $busName = $job->bus->name ?? 'Unknown Bus';
                                $busNumber = $job->bus->body_number ?? null;
                            @endphp

                            {{ $busName }} @if ($busNumber)
                                - {{ $busNumber }}
                            @endif
                        </td>

                        {{-- 👤 Requester --}}
                        <td class="requester">{{ $job->job_creator ?? 'Unknown' }}</td>

                        {{-- 🧰 Job Type --}}
                        <td class="job_type">{{ $job->job_type ?? 'N/A' }}</td>

                        {{-- 🏷️ Status --}}
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

                        {{-- 📅 Date --}}
                        <td class="date">
                            {{ \Carbon\Carbon::parse($job->job_date_filled)->format('Y-m-d') }}
                        </td>

                        {{-- 🔍 Actions --}}
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
