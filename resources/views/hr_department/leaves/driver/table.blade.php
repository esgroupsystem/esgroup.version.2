<div class="table-responsive scrollbar" style="overflow: visible !important;">
    <table class="table table-hover table-striped fs-10 mb-0 w-100">
        <thead class="bg-200 text-900">
            <tr>
                <th>Employee</th>
                <th>Leave Type</th>
                <th>From</th>
                <th>To</th>
                <th>No. of Days</th>
                <th>Record Status</th>
                <th>Status of Leave</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($leaves as $leave)
                @php
                    $st = strtolower($leave->status ?? '');
                    $isLocked = in_array($st, ['cancelled', 'terminated', 'completed'], true);

                    $start = $leave->start_date
                        ? \Carbon\Carbon::parse($leave->start_date, 'Asia/Manila')->startOfDay()
                        : null;

                    $end = $leave->end_date
                        ? \Carbon\Carbon::parse($leave->end_date, 'Asia/Manila')->startOfDay()
                        : null;

                    $notStarted = $start && $today->lt($start);
                    $onLeave = $start && $end && $today->gte($start) && $today->lte($end);
                    $afterLeave = $end && $today->gt($end);

                    $canShowReady = true; // always available anytime
                    $canShowNotices = $afterLeave && !$isLocked;
                @endphp

                <tr>
                    <td class="employee">
                        <strong>{{ $leave->employee->full_name }}</strong><br>
                        <span class="text-muted">{{ $leave->employee->position?->title ?? '-' }}</span>
                    </td>

                    <td class="type">
                        {{ $leave->leave_type }}
                        <span class="ms-1" data-bs-toggle="tooltip"
                            title="{{ e($leave->reason ?? 'No reason provided') }}">
                            <i class="fas fa-exclamation-circle text-info"></i>
                        </span>
                    </td>

                    <td class="from">{{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}</td>
                    <td class="to">{{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</td>
                    <td class="days">{{ $leave->days }} Days</td>

                    <td>{!! $leave->record_status_badge ?? '<span class="badge bg-primary">Active</span>' !!}</td>

                    <td class="remaining">{!! $leave->remaining_status !!}</td>

                    <td class="text-center">
                        <div class="btn-group">
                            <button class="btn btn-sm btn-primary dropdown-toggle" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Actions
                            </button>

                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item {{ $isLocked ? 'disabled' : '' }}"
                                        href="{{ $isLocked ? '#' : route('driver-leave.driver.edit', $leave) }}">
                                        <i class="fas fa-edit me-2 text-primary"></i>Edit Leave
                                    </a>
                                </li>

                                @if ($canShowReady)
                                    <li>
                                        <a class="dropdown-item action-open-modal" href="#"
                                            data-id="{{ $leave->id }}" data-action="ready"
                                            data-employee="{{ e($leave->employee->full_name) }}"
                                            data-type="{{ e($leave->leave_type) }}">
                                            <i class="fas fa-user-check me-2 text-success"></i> Ready for Duty
                                        </a>
                                    </li>

                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                @endif

                                @if ($canShowNotices)
                                    <li>
                                        @if ($leave->first_notice_sent_at)
                                            <a class="dropdown-item disabled text-primary">
                                                <i class="fas fa-check-circle text-primary me-2"></i>
                                                1st Notice Sent
                                                <br>
                                                <small class="text-muted">
                                                    {{ $leave->first_notice_sent_at->format('M d, Y h:i A') }}
                                                </small>
                                            </a>
                                        @else
                                            <a class="dropdown-item action-open-modal text-primary" href="#"
                                                data-action="first" data-id="{{ $leave->id }}"
                                                data-employee="{{ e($leave->employee->full_name) }}"
                                                data-type="{{ e($leave->leave_type) }}">
                                                <i class="fas fa-paper-plane text-primary me-2"></i>
                                                Mark 1st Notice Sent
                                            </a>
                                        @endif
                                    </li>

                                    <li>
                                        @if ($leave->second_notice_sent_at)
                                            <a class="dropdown-item disabled text-warning">
                                                <i class="fas fa-check-circle text-warning me-2"></i>
                                                2nd Notice Sent
                                                <br>
                                                <small class="text-muted">
                                                    {{ $leave->second_notice_sent_at->format('M d, Y h:i A') }}
                                                </small>
                                            </a>
                                        @else
                                            <a class="dropdown-item action-open-modal text-warning {{ !$leave->first_notice_sent_at ? 'disabled' : '' }}"
                                                href="#" data-action="second" data-id="{{ $leave->id }}"
                                                data-employee="{{ e($leave->employee->full_name) }}"
                                                data-type="{{ e($leave->leave_type) }}">
                                                <i class="fas fa-envelope text-warning me-2"></i>
                                                Mark 2nd Notice Sent
                                            </a>
                                        @endif
                                    </li>

                                    <li>
                                        @if ($leave->final_notice_sent_at)
                                            <a class="dropdown-item disabled text-danger">
                                                <i class="fas fa-check-circle text-danger me-2"></i>
                                                Final Notice Sent
                                                <br>
                                                <small class="text-muted">
                                                    {{ $leave->final_notice_sent_at->format('M d, Y h:i A') }}
                                                </small>
                                            </a>
                                        @else
                                            <a class="dropdown-item action-open-modal text-danger {{ !$leave->second_notice_sent_at ? 'disabled' : '' }}"
                                                href="#" data-action="terminate" data-id="{{ $leave->id }}"
                                                data-employee="{{ e($leave->employee->full_name) }}"
                                                data-type="{{ e($leave->leave_type) }}">
                                                <i class="fas fa-file-signature text-danger me-2"></i>
                                                Mark Final Notice Sent
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
                                        data-employee="{{ e($leave->employee->full_name) }}"
                                        data-type="{{ e($leave->leave_type) }}">
                                        <i class="fas fa-ban me-2"></i> Cancel Leave
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-3 text-muted">No leave records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="my-3 d-flex justify-content-end px-3">
    {{ $leaves->links('pagination.custom') }}
</div>
