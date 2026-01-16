<div class="table-responsive scrollbar ticket-table-wrap">
    <table class="table table-hover table-striped fs-10 mb-0 ticket-table">
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
                @php
                    $isApproval = $job->job_status === 'Approval';
                    $isApprover = in_array(auth()->user()->role, ['IT Head', 'Developer']);
                    $overlayText = $isApprover ? 'FOR APPROVAL' : 'WAITING FOR IT HEAD APPROVAL';
                @endphp

                <tr class="{{ $isApproval ? 'blur-row' : '' }}"
                    @if ($isApproval) data-overlay="{{ $overlayText }}" @endif
                    @if ($isApproval) title="{{ $isApprover ? 'This ticket requires your approval' : 'Waiting for IT Head approval' }}" @endif>
                    <td class="align-middle">
                        {{ $job->bus->name ?? 'Unknown Bus' }}
                        @if ($job->bus?->body_number)
                            - {{ $job->bus->body_number }}
                        @endif
                    </td>

                    <td class="align-middle">{{ $job->job_creator }}</td>

                    <td class="align-middle">{{ $job->job_type }}</td>

                    <td class="align-middle">
                        @if ($job->job_status == 'Pending')
                            <span class="badge bg-warning text-dark">Pending</span>
                        @elseif($job->job_status == 'In Progress')
                            <span class="badge bg-info text-dark">In Progress</span>
                        @elseif($job->job_status == 'Completed')
                            <span class="badge bg-success">Completed</span>
                        @elseif($job->job_status == 'Approval')
                            <span class="badge bg-secondary">Approval</span>
                        @else
                            <span class="badge bg-secondary">{{ $job->job_status }}</span>
                        @endif
                    </td>

                    <td class="align-middle">
                        {{ \Carbon\Carbon::parse($job->job_date_filled)->format('Y-m-d') }}
                    </td>

                    {{-- ACTIONS --}}
                    {{-- ACTIONS --}}
                    <td class="text-center align-middle actions-cell">
                        <div class="actions-wrap">

                            {{-- VIEW (show for ALL except Approval rows) --}}
                            @if (!$isApproval || ($isApproval && $isApprover))
                                <a href="{{ route('tickets.joborder.view', $job->id) }}"
                                    class="btn btn-sm btn-info text-white btn-approval">
                                    <i class="fas fa-eye"></i>
                                </a>
                            @endif

                            {{-- APPROVE / DISAPPROVE (ONLY on Approval + ONLY IT HEAD & DEV) --}}
                            @if ($isApproval && $isApprover)
                                <form method="POST" action="{{ route('tickets.approve', $job->id) }}"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm btn-approval">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('tickets.disapprove', $job->id) }}"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm btn-approval">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            @endif

                        </div>
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

<style>
    /* FIX ROW HEIGHT */
    .ticket-table tbody tr td {
        height: 56px;
        vertical-align: middle;
    }

    /* ACTIONS CELL ALWAYS CLICKABLE + ABOVE OVERLAY */
    .actions-cell {
        position: relative;
        z-index: 10;
        width: 140px;
        white-space: nowrap;
    }

    /* ONE LINE BUTTONS */
    .actions-wrap {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    /* SAME SIZE ICON BUTTONS */
    .btn-approval {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0 !important;
    }

    /* BLUR ROW CONTENT (EXCEPT ACTIONS) */
    .blur-row td:not(.actions-cell) {
        filter: blur(7.5px);
        pointer-events: none;
    }

    /* ROW MUST BE RELATIVE FOR PSEUDO OVERLAY */
    .blur-row {
        position: relative;
    }

    /* FULL-WIDTH OVERLAY (CENTER TEXT PERFECTLY) */
    .blur-row::after {
        content: attr(data-overlay);
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.55);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        /* ✅ true center of the row */
        font-weight: 600;
        font-size: 13px;
        z-index: 5;
        pointer-events: none;
        /* buttons still clickable */
    }
</style>
