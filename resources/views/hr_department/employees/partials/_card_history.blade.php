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

                $actions = $group['actions'] ?? [];

                $hasSus = in_array('Suspension', $actions, true);
                $hasSda = in_array('Salary Deduction Authorization', $actions, true);

                $safeIrKey = md5((string) ($group['ir_number'] ?? $first->id));

                $viewModalId = 'irModal_' . $safeIrKey;
                $editModalId = 'editModal_' . $safeIrKey;
                $violationFieldsId = 'violationFields_' . $safeIrKey;

                $formatDateInput = function ($value) {
                    if (blank($value)) {
                        return '';
                    }

                    if ($value instanceof \Carbon\CarbonInterface) {
                        return $value->format('Y-m-d');
                    }

                    try {
                        return \Illuminate\Support\Carbon::parse($value)->format('Y-m-d');
                    } catch (\Throwable $e) {
                        return '';
                    }
                };
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
                        <!-- View IR Details Modal Trigger -->
                        <button type="button" class="btn btn-sm btn-falcon-default" data-bs-toggle="modal"
                            data-bs-target="#{{ $viewModalId }}" title="View IR Details">
                            <span class="fas fa-eye"></span>
                        </button>

                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                            data-bs-target="#{{ $editModalId }}" title="Edit Violation">
                            <i class="fas fa-edit"></i>
                        </button>

                        <!-- Delete Form -->
                        <form action="{{ route('employees.staff.history.destroy', [$employee->id, $first->id]) }}"
                            method="POST" class="confirm-delete d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-falcon-danger" title="Delete IR">
                                <span class="fas fa-trash"></span>
                            </button>
                        </form>
                    </div>
                </div>

                {{-- UPDATING MODAL FOR VIOLATION --}}
                {{-- FULL EDIT VIOLATION MODAL --}}
                {{-- FULL EDIT VIOLATION MODAL --}}
                <div class="modal fade violation-edit-modal" id="{{ $editModalId }}" tabindex="-1"
                    aria-labelledby="{{ $editModalId }}Label" aria-hidden="true">

                    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                        <form action="{{ route('employees.staff.history.update', [$employee->id, $first->id]) }}"
                            method="POST" class="modal-content border-0 shadow-lg">

                            @csrf
                            @method('PUT')

                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="{{ $editModalId }}Label">
                                    <i class="fas fa-edit me-2"></i>
                                    Edit Violation
                                </h5>

                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                    aria-label="Close">
                                </button>
                            </div>

                            <div class="modal-body">
                                <input type="hidden" name="title" value="Violations">

                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">IR Number</label>
                                        <input type="text" name="ir_number" class="form-control"
                                            value="{{ $group['ir_number'] }}" required>
                                    </div>
                                </div>

                                <div class="border rounded-3 p-3 mb-3 bg-body-tertiary">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0 text-900">
                                            <i class="fas fa-list-ul text-primary me-2"></i>
                                            Violation Details
                                        </h6>

                                        <button type="button" class="btn btn-sm btn-outline-primary addViolationBtn"
                                            data-target="#{{ $violationFieldsId }}">
                                            <i class="fas fa-plus me-1"></i>
                                            Add Another Violation
                                        </button>
                                    </div>

                                    <div id="{{ $violationFieldsId }}" class="violation-fields">
                                        @foreach ($group['records'] as $index => $record)
                                            <div class="violation-row border rounded-3 p-3 mb-3 bg-white">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h6 class="mb-0 violation-number">Violation #{{ $index + 1 }}
                                                    </h6>
                                                    <button type="button"
                                                        class="btn btn-sm btn-falcon-danger removeViolation {{ $loop->first ? 'd-none' : '' }}">
                                                        <i class="fas fa-trash me-1"></i> Remove
                                                    </button>
                                                </div>

                                                <div class="mb-3">
                                                    <label>Offense Section</label>
                                                    <select name="offense_id[]" class="form-select offenseSelect">
                                                        <option value="">-- Select --</option>
                                                        @foreach ($offenses as $o)
                                                            <option value="{{ $o->id }}"
                                                                data-description="{{ $o->offense_description }}"
                                                                @selected($record->offense_id == $o->id)>
                                                                {{ $o->section }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div>
                                                    <label>Description</label>
                                                    <textarea name="description[]" readonly class="form-control offenseDescription">{{ $record->description }}</textarea>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-outline-primary addViolationBtn"
                                        data-target="#{{ $violationFieldsId }}">
                                        <i class="fas fa-plus"></i> Add Another Violation
                                    </button>
                                </div>

                                <div class="border rounded-3 p-3 mb-3">
                                    <label class="form-label fw-semibold mb-2">Disciplinary Action</label>

                                    <div class="form-check">
                                        <input class="form-check-input action-checkbox js-action-sda" type="checkbox"
                                            name="disciplinary_action[]" value="Salary Deduction Authorization"
                                            id="actionSDA_{{ $safeIrKey }}" @checked($hasSda)>

                                        <label class="form-check-label" for="actionSDA_{{ $safeIrKey }}">
                                            Salary Deduction Authorization (SDA)
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input action-checkbox js-action-suspension"
                                            type="checkbox" name="disciplinary_action[]" value="Suspension"
                                            id="actionSuspension_{{ $safeIrKey }}" @checked($hasSus)>

                                        <label class="form-check-label" for="actionSuspension_{{ $safeIrKey }}">
                                            Suspension
                                        </label>
                                    </div>
                                </div>

                                <div
                                    class="sda-fields-wrapper border rounded-3 p-3 mb-3 {{ $hasSda ? '' : 'd-none' }}">
                                    <h6 class="text-warning-emphasis mb-3">
                                        <i class="fas fa-file-invoice-dollar me-2"></i>
                                        Salary Deduction Authorization
                                    </h6>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">SDA Total Amount</label>
                                        <input type="number" step="0.01" min="0" name="sda_amount"
                                            class="form-control"
                                            value="{{ $group['sda_amount'] ?? ($first->sda_amount ?? '') }}" required>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Deduction Terms</label>
                                            <input type="number" min="1" step="1" name="sda_terms"
                                                class="form-control"
                                                value="{{ $group['sda_terms'] ?? ($first->sda_terms ?? '') }}" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Deduction Start Date</label>
                                            <input type="date" name="sda_start_date" class="form-control"
                                                value="{{ $formatDateInput($group['sda_start_date'] ?? ($first->sda_start_date ?? null)) }}" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">
                                                Deduction End Date
                                                <span class="text-muted">(Optional)</span>
                                            </label>
                                            <input type="date" name="sda_end_date" class="form-control"
                                                value="{{ $formatDateInput($group['sda_end_date'] ?? ($first->sda_end_date ?? null)) }}">
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="suspension-dates-wrapper border rounded-3 p-3 {{ $hasSus ? '' : 'd-none' }}">
                                    <h6 class="text-danger-emphasis mb-3">
                                        <i class="fas fa-ban me-2"></i>
                                        Suspension Details
                                    </h6>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Suspension Start Date</label>
                                            <input type="date" name="suspension_start_date" class="form-control"
                                                value="{{ $formatDateInput($group['suspension_start_date'] ?? ($first->suspension_start_date ?? null)) }}" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Suspension End Date</label>
                                            <input type="date" name="suspension_end_date" class="form-control"
                                                value="{{ $formatDateInput($group['suspension_end_date'] ?? ($first->suspension_end_date ?? null)) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer bg-body-tertiary">
                                <button type="button" class="btn btn-falcon-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i>
                                    Cancel
                                </button>

                                <button type="submit" class="btn btn-falcon-primary">
                                    <i class="fas fa-save me-1"></i>
                                    Update Violation
                                </button>
                            </div>
                        </form>
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
            <div class="modal fade" id="{{ $viewModalId }}" tabindex="-1"
                aria-labelledby="{{ $viewModalId }}Label">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-body-tertiary">
                            <h5 class="modal-title text-900" id="{{ $viewModalId }}Label">
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
