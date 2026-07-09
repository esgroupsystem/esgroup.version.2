<div class="table-responsive scrollbar">
    <table class="table table-sm table-hover align-middle fs-10 mb-0 w-100 conductor-leave-table">
        <thead class="bg-200 text-900">
            <tr>
                <th style="min-width: 230px;">Employee / Conductor</th>
                <th style="min-width: 160px;">Garage</th>
                <th style="min-width: 170px;">Leave Details</th>
                <th style="min-width: 190px;">Leave Period</th>
                <th class="text-center">Days</th>
                <th style="min-width: 230px;">Notice Tracker</th>
                <th style="min-width: 180px;">Record Status</th>
                <th class="text-center" style="min-width: 110px;">Action</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($leaves as $leave)
                @php
                    $status = strtolower($leave->status ?? '');
                    $isLocked = in_array($status, ['cancelled', 'terminated', 'completed'], true);

                    $end = $leave->end_date
                        ? \Carbon\Carbon::parse($leave->end_date, 'Asia/Manila')->startOfDay()
                        : null;

                    $afterLeave = $end && $today->gt($end);
                    $canShowReady = true;
                    $canShowNotices = $afterLeave && !$isLocked;

                    $employee = $leave->employee;
                    $garage = $employee?->garage ?: 'No Garage Assigned';
                    $company = $employee?->company ?: 'No Company';
                    $employeeNo = $employee?->employee_id_permanent ?: ($employee?->employee_id ?: 'No Employee ID');
                @endphp

                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-xl me-2">
                                <div class="avatar-name rounded-circle bg-primary-subtle text-primary">
                                    <span>{{ strtoupper(substr($employee?->full_name ?? 'C', 0, 1)) }}</span>
                                </div>
                            </div>

                            <div>
                                <div class="fw-semi-bold text-900">
                                    {{ $employee?->full_name ?? 'No employee record' }}
                                </div>
                                <div class="text-600 small">
                                    {{ $employeeNo }}
                                </div>
                                <div class="text-600 small">
                                    {{ $employee?->position?->title ?? 'No position' }}
                                </div>
                            </div>
                        </div>
                    </td>

                    <td>
                        <div class="fw-semi-bold text-900">
                            <span class="fas fa-warehouse text-primary me-1"></span>
                            {{ $garage }}
                        </div>
                        <div class="text-600 small">
                            {{ $company }}
                        </div>
                    </td>

                    <td>
                        <div class="fw-semi-bold text-900">
                            {{ $leave->leave_type }}
                        </div>

                        <div class="small text-600 text-truncate" style="max-width: 230px;" data-bs-toggle="tooltip"
                            title="{{ e($leave->reason ?? 'No reason provided') }}">
                            <span class="fas fa-comment-dots me-1"></span>
                            {{ $leave->reason ?: 'No reason provided' }}
                        </div>
                    </td>

                    <td>
                        <div class="d-flex flex-column gap-1">
                            <div>
                                <span class="badge badge-subtle-primary text-primary">
                                    From
                                </span>
                                <span class="ms-1">
                                    {{ $leave->start_date ? \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') : '-' }}
                                </span>
                            </div>

                            <div>
                                <span class="badge badge-subtle-info text-info">
                                    To
                                </span>
                                <span class="ms-1">
                                    {{ $leave->end_date ? \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') : '-' }}
                                </span>
                            </div>
                        </div>
                    </td>

                    <td class="text-center">
                        <span class="badge rounded-pill badge-subtle-secondary text-secondary">
                            {{ number_format($leave->days ?? 0) }}
                        </span>
                    </td>

                    <td>
                        <div class="notice-tracker">
                            <div class="notice-step {{ $leave->first_notice_sent_at ? 'done' : '' }}">
                                <span class="notice-dot"></span>
                                <div>
                                    <div class="fw-semi-bold">1st Notice</div>
                                    <div class="small text-600">
                                        {{ $leave->first_notice_sent_at ? $leave->first_notice_sent_at->format('M d, Y h:i A') : 'Pending' }}
                                    </div>
                                </div>
                            </div>

                            <div class="notice-step {{ $leave->second_notice_sent_at ? 'done inactive-step' : '' }}">
                                <span class="notice-dot"></span>
                                <div>
                                    <div class="fw-semi-bold">2nd Notice</div>
                                    <div class="small text-600">
                                        {{ $leave->second_notice_sent_at ? $leave->second_notice_sent_at->format('M d, Y h:i A') : 'Pending' }}
                                    </div>
                                </div>
                            </div>

                            <div class="notice-step {{ $leave->final_notice_sent_at ? 'done final-step' : '' }}">
                                <span class="notice-dot"></span>
                                <div>
                                    <div class="fw-semi-bold">Final Notice</div>
                                    <div class="small text-600">
                                        {{ $leave->final_notice_sent_at ? $leave->final_notice_sent_at->format('M d, Y h:i A') : 'Pending' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>

                    <td>
                        <div class="mb-1">
                            {!! $leave->record_status_badge ??
                                '<span class="badge rounded-pill badge-subtle-primary text-primary">Active</span>' !!}
                        </div>

                        <div class="mb-1">
                            {!! $leave->remaining_status !!}
                        </div>

                        @if ($leave->last_action_note)
                            <div class="small text-600 text-truncate" style="max-width: 220px;" data-bs-toggle="tooltip"
                                title="{{ e($leave->last_action_note) }}">
                                <span class="fas fa-sticky-note me-1"></span>
                                {{ $leave->last_action_note }}
                            </div>
                        @endif
                    </td>

                    <td class="text-center">
                        <div class="dropdown font-sans-serif position-static">
                            <button class="btn btn-sm btn-falcon-default dropdown-toggle" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Actions
                            </button>

                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item {{ $isLocked ? 'disabled' : '' }}"
                                        href="{{ $isLocked ? '#' : route('conductor-leave.conductor.edit', $leave) }}">
                                        <span class="fas fa-edit me-2 text-primary"></span>
                                        Edit Leave
                                    </a>
                                </li>

                                @if ($canShowReady)
                                    <li>
                                        <a class="dropdown-item action-open-modal" href="#"
                                            data-id="{{ $leave->id }}" data-action="ready"
                                            data-employee="{{ e($employee?->full_name ?? '') }}"
                                            data-type="{{ e($leave->leave_type) }}" data-garage="{{ e($garage) }}">
                                            <span class="fas fa-user-check me-2 text-success"></span>
                                            Ready for Duty
                                        </a>
                                    </li>

                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                @endif

                                @if ($canShowNotices)
                                    <li>
                                        @if ($leave->first_notice_sent_at)
                                            <span class="dropdown-item disabled">
                                                <span class="fas fa-check-circle text-info me-2"></span>
                                                1st Notice Sent
                                            </span>
                                        @else
                                            <a class="dropdown-item action-open-modal" href="#"
                                                data-action="first" data-id="{{ $leave->id }}"
                                                data-employee="{{ e($employee?->full_name ?? '') }}"
                                                data-type="{{ e($leave->leave_type) }}"
                                                data-garage="{{ e($garage) }}">
                                                <span class="fas fa-paper-plane text-info me-2"></span>
                                                Mark 1st Notice Sent
                                            </a>
                                        @endif
                                    </li>

                                    <li>
                                        @if ($leave->second_notice_sent_at)
                                            <span class="dropdown-item disabled">
                                                <span class="fas fa-check-circle text-warning me-2"></span>
                                                2nd Notice Sent / Inactive
                                            </span>
                                        @else
                                            <a class="dropdown-item action-open-modal {{ !$leave->first_notice_sent_at ? 'disabled' : '' }}"
                                                href="#" data-action="second" data-id="{{ $leave->id }}"
                                                data-employee="{{ e($employee?->full_name ?? '') }}"
                                                data-type="{{ e($leave->leave_type) }}"
                                                data-garage="{{ e($garage) }}">
                                                <span class="fas fa-user-slash text-warning me-2"></span>
                                                Mark 2nd Notice + Inactive
                                            </a>
                                        @endif
                                    </li>

                                    <li>
                                        @if ($leave->final_notice_sent_at)
                                            <span class="dropdown-item disabled">
                                                <span class="fas fa-check-circle text-danger me-2"></span>
                                                Final Notice Sent
                                            </span>
                                        @else
                                            <a class="dropdown-item action-open-modal {{ !$leave->second_notice_sent_at ? 'disabled' : '' }}"
                                                href="#" data-action="terminate" data-id="{{ $leave->id }}"
                                                data-employee="{{ e($employee?->full_name ?? '') }}"
                                                data-type="{{ e($leave->leave_type) }}"
                                                data-garage="{{ e($garage) }}">
                                                <span class="fas fa-file-signature text-danger me-2"></span>
                                                Mark Final Notice
                                            </a>
                                        @endif
                                    </li>

                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                @endif

                                <li>
                                    <a class="dropdown-item action-open-modal {{ $isLocked ? 'disabled' : '' }}"
                                        href="#" data-id="{{ $leave->id }}" data-action="cancel"
                                        data-employee="{{ e($employee?->full_name ?? '') }}"
                                        data-type="{{ e($leave->leave_type) }}" data-garage="{{ e($garage) }}">
                                        <span class="fas fa-ban me-2 text-secondary"></span>
                                        Cancel Leave
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <div class="empty-state">
                            <div class="empty-state-icon mb-2">
                                <span class="fas fa-folder-open"></span>
                            </div>
                            <h6 class="mb-1">No conductor leave records found</h6>
                            <p class="text-600 mb-0">Create a new conductor leave record or adjust your search.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="px-3 py-3 d-flex justify-content-end">
    {{ $leaves->links('pagination.custom') }}
</div>

<style>
    .conductor-leave-table .notice-tracker {
        display: flex;
        flex-direction: column;
        gap: .45rem;
    }

    .conductor-leave-table .notice-step {
        display: flex;
        align-items: flex-start;
        gap: .5rem;
        color: var(--falcon-gray-600, #748194);
    }

    .conductor-leave-table .notice-dot {
        width: .65rem;
        height: .65rem;
        border-radius: 50%;
        margin-top: .25rem;
        background: var(--falcon-gray-300, #d8e2ef);
        flex-shrink: 0;
    }

    .conductor-leave-table .notice-step.done {
        color: var(--falcon-info, #27bcfd);
    }

    .conductor-leave-table .notice-step.done .notice-dot {
        background: var(--falcon-info, #27bcfd);
    }

    .conductor-leave-table .notice-step.inactive-step {
        color: var(--falcon-warning, #f5803e);
    }

    .conductor-leave-table .notice-step.inactive-step .notice-dot {
        background: var(--falcon-warning, #f5803e);
    }

    .conductor-leave-table .notice-step.final-step {
        color: var(--falcon-danger, #e63757);
    }

    .conductor-leave-table .notice-step.final-step .notice-dot {
        background: var(--falcon-danger, #e63757);
    }

    .empty-state-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 50%;
        display: inline-grid;
        place-items: center;
        background: var(--falcon-gray-100, #f9fafd);
        color: var(--falcon-gray-500, #9da9bb);
        font-size: 1.35rem;
    }
</style>
