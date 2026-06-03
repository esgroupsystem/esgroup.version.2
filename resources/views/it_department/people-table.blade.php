@php
    $isPaginator = $list instanceof \Illuminate\Pagination\AbstractPaginator;

    $user = auth()->user();

    $isApprover =
        in_array($user->role ?? '', ['IT Head', 'Developer']) ||
        (method_exists($user, 'can') && $user->can('tickets.approve')) ||
        (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo('tickets.approve'));
@endphp

<div class="table-responsive">
    <table class="table people-table mb-0">
        <thead>
            <tr>
                <th>Bus Info</th>
                <th>Requester</th>
                <th>Job Issue</th>
                <th>Status</th>
                <th>Date</th>
                <th width="180" class="text-center">Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($list as $ticket)
                @php
                    /*
                    |--------------------------------------------------------------------------
                    | DATA MAPPING
                    |--------------------------------------------------------------------------
                    */

                    $busInfo = data_get($ticket, 'bus.name') ?? (data_get($ticket, 'bus.bus_name') ?? 'ES Transport');

                    $busNumber =
                        data_get($ticket, 'bus.body_number') ?? (data_get($ticket, 'bus.plate_number') ?? null);

                    $requester =
                        data_get($ticket, 'job_creator') ??
                        (data_get($ticket, 'requester') ??
                            (data_get($ticket, 'requester_name') ?? (data_get($ticket, 'user.name') ?? 'System')));

                    $jobIssue =
                        data_get($ticket, 'job_type') ??
                        (data_get($ticket, 'job_issue') ?? (data_get($ticket, 'issue') ?? 'General'));

                    $jobStatus = data_get($ticket, 'job_status', 'Pending');
                    $approvalStatus = data_get($ticket, 'approval_status', null);

                    $jobStatusKey = strtolower(trim(str_replace(['_', '-'], ' ', (string) $jobStatus)));
                    $approvalStatusKey = strtolower(trim(str_replace(['_', '-'], ' ', (string) $approvalStatus)));

                    /*
                    |--------------------------------------------------------------------------
                    | STATUS DISPLAY
                    |--------------------------------------------------------------------------
                    */

                    if ($jobStatusKey === 'approval') {
                        $statusClass = 'status-pending';
                        $statusLabel = 'Pending';
                    } elseif ($jobStatusKey === 'pending') {
                        $statusClass = 'status-pending';
                        $statusLabel = 'Pending';
                    } elseif (in_array($jobStatusKey, ['disapproved', 'rejected', 'reject'])) {
                        $statusClass = 'status-rejected';
                        $statusLabel = 'Rejected';
                    } elseif ($jobStatusKey === 'in progress') {
                        $statusClass = 'status-progress';
                        $statusLabel = 'In Progress';
                    } elseif ($jobStatusKey === 'completed') {
                        $statusClass = 'status-completed';
                        $statusLabel = 'Completed';
                    } else {
                        $statusClass = 'status-pending';
                        $statusLabel = ucwords($jobStatus);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | DATE
                    |--------------------------------------------------------------------------
                    */

                    $dateValue =
                        data_get($ticket, 'job_date_filled') ??
                        (data_get($ticket, 'created_at') ?? data_get($ticket, 'date'));

                    try {
                        $displayDate = $dateValue ? \Carbon\Carbon::parse($dateValue)->format('Y-m-d') : '-';
                    } catch (\Throwable $e) {
                        $displayDate = $dateValue ?: '-';
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | ROUTES
                    |--------------------------------------------------------------------------
                    */

                    $ticketId = data_get($ticket, 'id');

                    $viewUrl =
                        $ticketId && \Illuminate\Support\Facades\Route::has('tickets.joborder.view')
                            ? route('tickets.joborder.view', $ticketId)
                            : '#';

                    $deleteUrl =
                        $ticketId && \Illuminate\Support\Facades\Route::has('tickets.joborder.delete')
                            ? route('tickets.joborder.delete', $ticketId)
                            : '#';

                    $approveUrl =
                        $ticketId && \Illuminate\Support\Facades\Route::has('tickets.approve')
                            ? route('tickets.approve', $ticketId)
                            : '#';

                    $rejectUrl =
                        $ticketId && \Illuminate\Support\Facades\Route::has('tickets.disapprove')
                            ? route('tickets.disapprove', $ticketId)
                            : '#';

                    /*
                    |--------------------------------------------------------------------------
                    | ACTION RULE
                    |--------------------------------------------------------------------------
                    | job_status = Approval    → Approve + Disapprove
                    | job_status = Pending     → View + Delete
                    | job_status = Disapproved → Delete only
                    */

                    $showApproveReject = $jobStatusKey === 'approval' && $isApprover;

                    $showView = in_array($jobStatusKey, ['pending', 'in progress', 'completed']);

                    $showDelete = in_array($jobStatusKey, ['pending', 'disapproved', 'rejected', 'reject']);
                @endphp

                <tr>
                    <td>
                        {{ $busInfo }}

                        @if ($busNumber)
                            - {{ $busNumber }}
                        @endif
                    </td>

                    <td>
                        {{ $requester }}
                    </td>

                    <td>
                        {{ strtoupper($jobIssue) }}
                    </td>

                    <td>
                        <span class="status-pill {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </td>

                    <td>
                        {{ $displayDate }}
                    </td>

                    <td class="text-center">
                        <div class="action-group">

                            {{-- APPROVAL STATUS: APPROVE + DISAPPROVE --}}
                            @if ($showApproveReject)
                                <form action="{{ $approveUrl }}" method="POST" class="js-confirm-form"
                                    data-title="Approve Ticket?" data-text="This ticket will be approved."
                                    data-icon="success" data-confirm-text="Yes, approve"
                                    data-confirm-class="swal-approve">
                                    @csrf

                                    <button type="submit" class="btn-action btn-action-approve" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>

                                <form action="{{ $rejectUrl }}" method="POST" class="js-confirm-form"
                                    data-title="Disapprove Ticket?" data-text="This ticket will be disapproved."
                                    data-icon="warning" data-confirm-text="Yes, disapprove"
                                    data-confirm-class="swal-reject">
                                    @csrf

                                    <button type="submit" class="btn-action btn-action-reject" title="Disapprove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            @endif

                            {{-- PENDING / IN PROGRESS / COMPLETED: VIEW --}}
                            @if ($showView)
                                <a href="{{ $viewUrl }}" class="btn-action btn-action-view" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            @endif

                            {{-- PENDING / REJECTED: DELETE --}}
                            @if ($showDelete)
                                <form action="{{ $deleteUrl }}" method="POST" class="js-confirm-form"
                                    data-title="Delete Ticket?" data-text="This ticket will be permanently removed."
                                    data-icon="error" data-confirm-text="Yes, delete" data-confirm-class="swal-delete">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn-action btn-action-delete" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif

                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="fas fa-bus"></i>
                            <div class="empty-state-title">No tickets found</div>
                            <div class="empty-state-subtitle">Try changing your search.</div>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="card-footer bg-body-tertiary py-3">
    <div class="row flex-between-center g-2">
        <div class="col-auto">
            <small class="text-600">
                Showing {{ $list->firstItem() ?? 0 }} to {{ $list->lastItem() ?? 0 }} of
                {{ $list->total() }} entries
            </small>
        </div>

        <div class="col-auto">
            {{ $list->links('pagination.custom') }}
        </div>
    </div>
</div>
