
@php
    $totalViolations  = $groupedIrHistories->sum('count');
    $suspensionCount  = $groupedIrHistories->filter(fn($g) => in_array('Suspension', $g['actions']))->count();
    $sdaCount         = $groupedIrHistories->filter(fn($g) => in_array('Salary Deduction Authorization', $g['actions']))->count();
@endphp

<div class="vh-card mb-3">

    <div class="vh-card-header">
        <h6 class="vh-card-title">
            <span class="icon-wrap"><i class="fas fa-shield-alt"></i></span>
            Violation Histories
        </h6>
        <button class="btn btn-sm btn-falcon-primary" data-bs-toggle="modal" data-bs-target="#addHistoryModal">
            <i class="fas fa-plus me-1"></i> Add Record
        </button>
    </div>

    @if ($groupedIrHistories->isNotEmpty())
    <div class="vh-stats">
        <div class="vh-stat-item">
            <div class="vh-stat-value primary">{{ $groupedIrHistories->count() }}</div>
            <div class="vh-stat-label">IR Cases</div>
        </div>
        <div class="vh-stat-item">
            <div class="vh-stat-value danger">{{ $totalViolations }}</div>
            <div class="vh-stat-label">Total Violations</div>
        </div>
        <div class="vh-stat-item">
            <div class="vh-stat-value warning">{{ $sdaCount }}</div>
            <div class="vh-stat-label">With SDA</div>
        </div>
        <div class="vh-stat-item">
            <div class="vh-stat-value danger">{{ $suspensionCount }}</div>
            <div class="vh-stat-label">Suspended</div>
        </div>
    </div>
    @endif

    <div class="vh-timeline">

        @forelse($groupedIrHistories as $group)

            @php
                $first    = $group['first_record'];
                $hasSus   = in_array('Suspension', $group['actions']);
                $hasSda   = in_array('Salary Deduction Authorization', $group['actions']);
                $dotClass = $hasSus ? 'has-suspension' : ($hasSda ? 'has-sda' : 'plain');
                $modalId  = 'irModal_' . md5($group['ir_number']);
            @endphp

            <div class="vh-item">
                <div class="vh-rail">
                    <span class="vh-dot {{ $dotClass }}"></span>
                </div>

                <div class="vh-bubble">
                    <div class="vh-bubble-head">
                        <div>
                            <div class="vh-ir-number mb-1">
                                <i class="fas fa-hashtag me-1" style="font-size:.7rem;opacity:.6;"></i>{{ $group['ir_number'] }}
                            </div>
                            <div class="vh-badges">
                                <span class="vh-count">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $group['count'] }} {{ Str::plural('Violation', $group['count']) }}
                                </span>
                                @if ($hasSda)
                                    <span class="vh-badge-sda"><i class="fas fa-file-invoice-dollar me-1"></i>SDA</span>
                                @endif
                                @if ($hasSus)
                                    <span class="vh-badge-sus"><i class="fas fa-ban me-1"></i>Suspension</span>
                                @endif
                            </div>
                        </div>

                        <div class="vh-actions flex-shrink-0">
                            <button class="vh-btn-icon view"
                                    data-bs-toggle="modal"
                                    data-bs-target="#{{ $modalId }}"
                                    title="View IR Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            @role('Developer')
                                <form action="{{ route('employees.staff.history.destroy', [$employee->id, $first->id]) }}"
                                      method="POST" class="d-inline confirm-delete">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="vh-btn-icon trash" title="Delete IR">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endrole
                        </div>
                    </div>

                    <div class="vh-bubble-body">
                        <span class="vh-meta">
                            <i class="far fa-clock"></i>
                            {{ $first->created_at->format('M d, Y') }}
                            <span class="text-300">·</span>
                            {{ $first->created_at->format('h:i A') }}
                        </span>

                        @if ($hasSda || $hasSus)
                        <span class="vh-meta ms-auto">
                            @if ($hasSda)
                                <i class="fas fa-peso-sign text-warning me-1"></i>
                                &#8369;{{ number_format($first->sda_amount ?? 0, 2) }}
                            @endif
                            @if ($hasSus)
                                <i class="fas fa-calendar-times text-danger ms-2 me-1"></i>
                                {{ $first->suspension_start_date?->format('M d') ?? '—' }}
                                &rarr;
                                {{ $first->suspension_end_date?->format('M d, Y') ?? 'Ongoing' }}
                            @endif
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- VIEW MODAL --}}
            <div class="modal fade vh-modal" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg">

                        <div class="modal-header">
                            <h5 class="modal-title d-flex align-items-center gap-2" id="{{ $modalId }}Label">
                                <span style="width:30px;height:30px;border-radius:6px;background:rgba(44,123,229,.1);display:grid;place-items:center;color:#2c7be5;font-size:.8rem;flex-shrink:0;">
                                    <i class="fas fa-file-alt"></i>
                                </span>
                                IR #{{ $group['ir_number'] }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">

                            <div class="vh-modal-summary">
                                <div>
                                    <div class="label">Total Violations</div>
                                    <div class="value">{{ $group['count'] }}</div>
                                </div>
                                <div class="ms-auto vh-badges">
                                    @if ($hasSda)
                                        <span class="vh-badge-sda"><i class="fas fa-file-invoice-dollar me-1"></i>SDA</span>
                                    @endif
                                    @if ($hasSus)
                                        <span class="vh-badge-sus"><i class="fas fa-ban me-1"></i>Suspension</span>
                                    @endif
                                </div>
                            </div>

                            <p class="fs--2 text-uppercase fw-bold text-600 mb-2">
                                <i class="fas fa-list-ul me-1"></i> Violations Breakdown
                            </p>

                            @foreach ($group['records'] as $record)
                                <div class="vh-offense-card">
                                    <div class="vh-offense-head">
                                        <span class="vh-section-tag">Section {{ $record->offense?->section }}</span>
                                        @if($record->offense?->type)
                                            <span class="badge text-bg-light text-600" style="font-size:.65rem;">
                                                Type {{ $record->offense?->type }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="vh-offense-desc">
                                        {{ $record->description ?: ($record->offense?->description ?? 'No description provided.') }}
                                    </div>
                                </div>
                            @endforeach

                            @if ($hasSda)
                                <div class="vh-detail-box sda">
                                    <div class="box-title"><i class="fas fa-file-invoice-dollar me-1"></i>Salary Deduction Authorization</div>
                                    <div class="vh-detail-grid">
                                        <div class="vh-detail-row">
                                            <div class="dkey">Total Amount</div>
                                            <div class="dval">&#8369;{{ number_format($first->sda_amount ?? 0, 2) }}</div>
                                        </div>
                                        <div class="vh-detail-row">
                                            <div class="dkey">Per Cutoff</div>
                                            <div class="dval">&#8369;{{ number_format($first->sda_terms ?? 0, 2) }}</div>
                                        </div>
                                        @if($first->sda_start_date)
                                        <div class="vh-detail-row">
                                            <div class="dkey">Start Date</div>
                                            <div class="dval">{{ $first->sda_start_date->format('M d, Y') }}</div>
                                        </div>
                                        @endif
                                        @if($first->sda_end_date)
                                        <div class="vh-detail-row">
                                            <div class="dkey">End Date</div>
                                            <div class="dval">{{ $first->sda_end_date->format('M d, Y') }}</div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if ($hasSus)
                                <div class="vh-detail-box sus">
                                    <div class="box-title"><i class="fas fa-ban me-1"></i>Suspension Details</div>
                                    <div class="vh-detail-grid">
                                        <div class="vh-detail-row">
                                            <div class="dkey">Start Date</div>
                                            <div class="dval">{{ $first->suspension_start_date?->format('M d, Y') ?? '—' }}</div>
                                        </div>
                                        <div class="vh-detail-row">
                                            <div class="dkey">End Date</div>
                                            <div class="dval text-danger">{{ $first->suspension_end_date?->format('M d, Y') ?? 'Ongoing' }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                        </div>

                        <div class="modal-footer bg-body-tertiary border-top border-200 py-2">
                            <span class="me-auto fs--2 text-500">
                                <i class="far fa-calendar-alt me-1"></i>
                                Recorded {{ $first->created_at->format('M d, Y h:i A') }}
                            </span>
                            <button type="button" class="btn btn-sm btn-falcon-default" data-bs-dismiss="modal">Close</button>
                        </div>

                    </div>
                </div>
            </div>

        @empty

            <div class="vh-empty">
                <div class="vh-empty-icon"><i class="fas fa-clipboard-check"></i></div>
                <p class="mb-0 fw-semibold" style="font-size:.85rem;color:#748194;">No violation records found.</p>
                <p class="mb-0 fs--2 text-400">This employee has a clean record.</p>
            </div>

        @endforelse

    </div>

</div>
