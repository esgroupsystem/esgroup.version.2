@php
    $totalViolations = $groupedIrHistories->sum('count');
    $suspensionCount = $groupedIrHistories->filter(fn($g) => in_array('Suspension', $g['actions']))->count();
    $sdaCount = $groupedIrHistories
        ->filter(fn($g) => in_array('Salary Deduction Authorization', $g['actions']))
        ->count();
@endphp

<div class="card shadow-sm border-0 mb-3">
    <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
        <h6 class="mb-0 text-900">
            <span class="fas fa-shield-alt text-primary me-2"></span>
            Violation Histories
        </h6>

        <button class="btn btn-sm btn-falcon-primary" data-bs-toggle="modal" data-bs-target="#addHistoryModal">
            <span class="fas fa-plus me-1"></span>
            Add Record
        </button>
    </div>

    @if ($groupedIrHistories->isNotEmpty())
        <div class="card-body border-bottom">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="card bg-primary-subtle border-0 h-100">
                        <div class="card-body py-3">
                            <h4 class="mb-0 text-primary">{{ $groupedIrHistories->count() }}</h4>
                            <p class="fs--1 mb-0 text-700">IR Cases</p>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="card bg-danger-subtle border-0 h-100">
                        <div class="card-body py-3">
                            <h4 class="mb-0 text-danger">{{ $totalViolations }}</h4>
                            <p class="fs--1 mb-0 text-700">Total Violations</p>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="card bg-warning-subtle border-0 h-100">
                        <div class="card-body py-3">
                            <h4 class="mb-0 text-warning">{{ $sdaCount }}</h4>
                            <p class="fs--1 mb-0 text-700">With SDA</p>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="card bg-danger-subtle border-0 h-100">
                        <div class="card-body py-3">
                            <h4 class="mb-0 text-danger">{{ $suspensionCount }}</h4>
                            <p class="fs--1 mb-0 text-700">Suspended</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="card-body">
        @forelse($groupedIrHistories as $group)
            @php
                $first = $group['first_record'];
                $hasSus = in_array('Suspension', $group['actions']);
                $hasSda = in_array('Salary Deduction Authorization', $group['actions']);
                $modalId = 'irModal_' . md5($group['ir_number']);
            @endphp

            <div class="border rounded-3 p-3 mb-3 bg-body-tertiary">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <div class="fw-bold text-900 mb-1">
                            <span class="fas fa-hashtag text-500 me-1"></span>
                            {{ $group['ir_number'] }}
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge badge-subtle-primary">
                                <span class="fas fa-exclamation-circle me-1"></span>
                                {{ $group['count'] }} {{ Str::plural('Violation', $group['count']) }}
                            </span>

                            @if ($hasSda)
                                <span class="badge badge-subtle-warning">
                                    <span class="fas fa-file-invoice-dollar me-1"></span>
                                    SDA
                                </span>
                            @endif

                            @if ($hasSus)
                                <span class="badge badge-subtle-danger">
                                    <span class="fas fa-ban me-1"></span>
                                    Suspension
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-falcon-default" data-bs-toggle="modal"
                            data-bs-target="#{{ $modalId }}" title="View IR Details">
                            <span class="fas fa-eye"></span>
                        </button>

                        @role('Developer')
                            <form action="{{ route('employees.staff.history.destroy', [$employee->id, $first->id]) }}"
                                method="POST" class="confirm-delete">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-sm btn-falcon-danger" title="Delete IR">
                                    <span class="fas fa-trash"></span>
                                </button>
                            </form>
                        @endrole
                    </div>
                </div>

                <hr class="my-3">

                <div class="d-flex flex-wrap align-items-center gap-3 fs--1 text-600">
                    <span>
                        <span class="far fa-clock me-1"></span>
                        {{ $first->created_at->format('M d, Y') }}
                        ·
                        {{ $first->created_at->format('h:i A') }}
                    </span>

                    @if ($hasSda)
                        <span>
                            <span class="fas fa-peso-sign text-warning me-1"></span>
                            ₱{{ number_format($first->sda_amount ?? 0, 2) }}
                        </span>
                    @endif

                    @if ($hasSus)
                        <span>
                            <span class="fas fa-calendar-times text-danger me-1"></span>
                            {{ $first->suspension_start_date?->format('M d') ?? '—' }}
                            →
                            {{ $first->suspension_end_date?->format('M d, Y') ?? 'Ongoing' }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- VIEW MODAL --}}
            <div class="modal fade" id="{{ $modalId }}" tabindex="-1"
                aria-labelledby="{{ $modalId }}Label">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-body-tertiary">
                            <h5 class="modal-title text-900" id="{{ $modalId }}Label">
                                <span class="fas fa-file-alt text-primary me-2"></span>
                                IR #{{ $group['ir_number'] }}
                            </h5>

                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="alert alert-subtle-primary d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fs--2 text-uppercase fw-bold text-600">Total Violations</div>
                                    <h4 class="mb-0 text-primary">{{ $group['count'] }}</h4>
                                </div>

                                <div class="d-flex flex-wrap gap-2">
                                    @if ($hasSda)
                                        <span class="badge badge-subtle-warning">
                                            <span class="fas fa-file-invoice-dollar me-1"></span>
                                            SDA
                                        </span>
                                    @endif

                                    @if ($hasSus)
                                        <span class="badge badge-subtle-danger">
                                            <span class="fas fa-ban me-1"></span>
                                            Suspension
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <p class="fs--2 text-uppercase fw-bold text-600 mb-2">
                                <span class="fas fa-list-ul me-1"></span>
                                Violations Breakdown
                            </p>

                            @foreach ($group['records'] as $record)
                                <div class="card border mb-2">
                                    <div class="card-body py-3">
                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                            <span class="badge badge-subtle-primary">
                                                Section {{ $record->offense?->section }}
                                            </span>

                                            @if ($record->offense?->type)
                                                <span class="badge badge-subtle-secondary">
                                                    Type {{ $record->offense?->type }}
                                                </span>
                                            @endif
                                        </div>

                                        <p class="mb-0 fs--1 text-800">
                                            {{ $record->description ?: $record->offense?->description ?? 'No description provided.' }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach

                            @if ($hasSda)
                                <div class="card border border-warning mt-3">
                                    <div class="card-header bg-warning-subtle">
                                        <h6 class="mb-0 text-warning-emphasis">
                                            <span class="fas fa-file-invoice-dollar me-1"></span>
                                            Salary Deduction Authorization
                                        </h6>
                                    </div>

                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="fs--2 text-600">Total Amount</div>
                                                <div class="fw-semibold">
                                                    ₱{{ number_format($first->sda_amount ?? 0, 2) }}</div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="fs--2 text-600">Per Cutoff</div>
                                                <div class="fw-semibold">
                                                    ₱{{ number_format($first->sda_terms ?? 0, 2) }}</div>
                                            </div>

                                            @if ($first->sda_start_date)
                                                <div class="col-md-6">
                                                    <div class="fs--2 text-600">Start Date</div>
                                                    <div class="fw-semibold">
                                                        {{ $first->sda_start_date->format('M d, Y') }}</div>
                                                </div>
                                            @endif

                                            @if ($first->sda_end_date)
                                                <div class="col-md-6">
                                                    <div class="fs--2 text-600">End Date</div>
                                                    <div class="fw-semibold">
                                                        {{ $first->sda_end_date->format('M d, Y') }}</div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($hasSus)
                                <div class="card border border-danger mt-3">
                                    <div class="card-header bg-danger-subtle">
                                        <h6 class="mb-0 text-danger-emphasis">
                                            <span class="fas fa-ban me-1"></span>
                                            Suspension Details
                                        </h6>
                                    </div>

                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="fs--2 text-600">Start Date</div>
                                                <div class="fw-semibold">
                                                    {{ $first->suspension_start_date?->format('M d, Y') ?? '—' }}
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="fs--2 text-600">End Date</div>
                                                <div class="fw-semibold text-danger">
                                                    {{ $first->suspension_end_date?->format('M d, Y') ?? 'Ongoing' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="modal-footer bg-body-tertiary">
                            <span class="me-auto fs--2 text-500">
                                <span class="far fa-calendar-alt me-1"></span>
                                Recorded {{ $first->created_at->format('M d, Y h:i A') }}
                            </span>

                            <button type="button" class="btn btn-sm btn-falcon-default" data-bs-dismiss="modal">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <div class="avatar avatar-4xl mx-auto mb-3">
                    <div class="avatar-name rounded-circle bg-success-subtle text-success">
                        <span class="fas fa-clipboard-check"></span>
                    </div>
                </div>

                <h6 class="text-700 mb-1">No violation records found.</h6>
                <p class="fs--1 text-500 mb-0">This employee has a clean record.</p>
            </div>
        @endforelse
    </div>
</div>
