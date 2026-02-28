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

            @forelse($historyItems as $item)
                @php($h = $item['model'])

                <div class="eh-item">
                    <div class="eh-left">
                        <span class="eh-dot {{ $item['is_present'] ? 'is-present' : '' }}"></span>
                        <span class="eh-line"></span>
                    </div>

                    <div class="eh-content">
                        <div class="eh-top d-flex justify-content-between align-items-start gap-2">
                            <div class="eh-title flex-grow-1">

                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <div class="fw-semibold">{{ $item['title'] }}</div>

                                    @if ($item['offense_section'])
                                        <span class="badge text-bg-secondary">Section
                                            {{ $item['offense_section'] }}</span>
                                    @endif

                                    @foreach ($item['actions'] as $a)
                                        <span
                                            class="badge text-bg-dark">{{ $a === 'Salary Deduction Authorization' ? 'SDA' : $a }}</span>
                                    @endforeach

                                    @if ($item['has_sda'] && !is_null($item['sda_total']))
                                        <span class="badge text-bg-warning">SDA Total:
                                            ₱{{ number_format($item['sda_total'], 2) }}</span>
                                    @endif

                                    @if (!is_null($item['per_cutoff_amount']))
                                        <span class="badge text-bg-info">
                                            Per Cutoff: ₱{{ number_format($item['per_cutoff_amount'], 2) }}
                                            @if (!is_null($item['months_duration']))
                                                ({{ $item['months_duration'] }} months)
                                            @endif
                                        </span>
                                    @endif

                                    @if ($item['is_present'])
                                        <span class="badge text-bg-success">
                                            <i class="fas fa-check-circle me-1"></i> Current
                                        </span>
                                    @endif
                                </div>

                                <div class="eh-meta mt-1 d-flex flex-wrap align-items-center gap-2">
                                    <span class="eh-range text-muted">
                                        <i class="far fa-calendar-alt me-1"></i> {{ $item['range_text'] }}
                                    </span>

                                    @if ($item['duration_text'])
                                        <span class="eh-pill"><i class="far fa-clock"></i>
                                            {{ $item['duration_text'] }}</span>
                                    @endif

                                    @if ($item['sda_range_text'])
                                        <span class="eh-pill"><i class="fas fa-money-bill-wave me-1"></i> Deduction:
                                            {{ $item['sda_range_text'] }}</span>
                                    @endif

                                    @if ($item['sus_range_text'])
                                        <span class="eh-pill"><i class="fas fa-ban me-1"></i> Suspension:
                                            {{ $item['sus_range_text'] }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="eh-actions text-end">
                                @role('Developer')
                                    <form action="{{ route('employees.staff.history.destroy', [$employee->id, $h->id]) }}"
                                        method="POST" class="d-inline confirm-delete">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0">
                                            Remove
                                        </button>
                                    </form>
                                @endrole
                            </div>
                        </div>

                        <div class="mt-2">
                            @if (!empty($h->description))
                                <div class="eh-desc">{{ $h->description }}</div>
                            @else
                                <div class="eh-desc text-muted">No additional details provided.</div>
                            @endif
                        </div>
                    </div>
                </div>

            @empty
                <div class="text-muted">No history records.</div>
            @endforelse

        </div>
    </div>
</div>
