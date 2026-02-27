<div class="card mb-3 shadow-sm">
    <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">
            <i class="fas fa-stream me-2"></i> Employment History Timeline
        </h6>

        <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#addHistoryModal">
            <i class="fas fa-plus me-1"></i> Add
        </button>
    </div>

    <div class="card-body">
        <div class="eh-timeline">
            @forelse($employee->histories as $h)
                @php
                    $start = $h->start_date ? \Carbon\Carbon::parse($h->start_date) : null;
                    $end = $h->end_date ? \Carbon\Carbon::parse($h->end_date) : null;
                    $isPresent = !$h->end_date;

                    $rangeText = ($start ? $start->format('M d, Y') : '—') . ' • ' . ($end ? $end->format('M d, Y') : 'Present');

                    $durationText = null;
                    if ($start) {
                        $durationText = $end ? $start->diffForHumans($end, true) : $start->diffForHumans(now(), true);
                    }
                @endphp

                <div class="eh-item">
                    <div class="eh-left">
                        <span class="eh-dot {{ $isPresent ? 'is-present' : '' }}"></span>
                        <span class="eh-line"></span>
                    </div>

                    <div class="eh-content">
                        <div class="eh-top">
                            <div class="eh-title">
                                <div class="fw-semibold">{{ $h->title }}</div>

                                <div class="eh-meta">
                                    <span class="eh-range">{{ $rangeText }}</span>

                                    @if ($durationText)
                                        <span class="eh-pill">
                                            <i class="far fa-clock"></i> {{ $durationText }}
                                        </span>
                                    @endif

                                    @if ($isPresent)
                                        <span class="eh-pill eh-pill-success">
                                            <i class="fas fa-check-circle"></i> Current
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="eh-actions text-end">
                                <form
                                    action="{{ route('employees.staff.history.destroy', [$employee->id, $h->id]) }}"
                                    method="POST"
                                    class="d-inline confirm-delete"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </div>

                        @if (!empty($h->description))
                            <div class="eh-desc">{{ $h->description }}</div>
                        @else
                            <div class="eh-desc text-muted">No additional details provided.</div>
                        @endif
                    </div>
                </div>

            @empty
                <div class="text-muted">No history records.</div>
            @endforelse
        </div>
    </div>
</div>