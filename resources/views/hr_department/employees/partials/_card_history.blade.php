@php
    $normalizeActions = function ($actions) {
        if (blank($actions)) {
            return [];
        }

        if (is_string($actions)) {
            $decoded = json_decode($actions, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $actions = $decoded;
            } else {
                $actions = [$actions];
            }
        }

        if (!is_array($actions)) {
            return [];
        }

        return collect($actions)->filter()->map(fn($action) => trim((string) $action))->unique()->values()->all();
    };

    $groupHasAction = function ($group, string $action) use ($normalizeActions) {
        $actions = $normalizeActions($group['actions'] ?? []);

        if (!empty($actions)) {
            return in_array($action, $actions, true);
        }

        return collect($group['records'] ?? [])
            ->flatMap(fn($record) => $normalizeActions($record->disciplinary_action ?? []))
            ->contains($action);
    };

    $totalViolations = $groupedIrHistories->sum('count');

    $suspensionCount = $groupedIrHistories->filter(fn($g) => $groupHasAction($g, 'Suspension'))->count();

    $sdaCount = $groupedIrHistories->filter(fn($g) => $groupHasAction($g, 'Salary Deduction Authorization'))->count();

    $finalWarningCount = $groupedIrHistories->filter(fn($g) => $groupHasAction($g, 'Final Warning'))->count();

    $remarksCount = $groupedIrHistories
        ->filter(function ($g) {
            return collect($g['records'] ?? [])
                ->filter(fn($r) => filled($r->remarks))
                ->isNotEmpty();
        })
        ->count();

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

    $formatDateText = function ($value, $fallback = '—') {
        if (blank($value)) {
            return $fallback;
        }

        try {
            return $value instanceof \Carbon\CarbonInterface
                ? $value->format('M d, Y')
                : \Illuminate\Support\Carbon::parse($value)->format('M d, Y');
        } catch (\Throwable $e) {
            return $fallback;
        }
    };

    $formatDateTimeText = function ($value, $fallback = '—') {
        if (blank($value)) {
            return $fallback;
        }

        try {
            return $value instanceof \Carbon\CarbonInterface
                ? $value->format('M d, Y h:i A')
                : \Illuminate\Support\Carbon::parse($value)->format('M d, Y h:i A');
        } catch (\Throwable $e) {
            return $fallback;
        }
    };
@endphp

<style>
    .violation-summary-card .card-body {
        padding: .65rem .8rem;
    }

    .violation-summary-card h5 {
        font-size: 1rem;
        line-height: 1.1;
    }

    .violation-summary-card p {
        font-size: .72rem;
    }

    .violation-ir-card {
        border: 1px solid var(--falcon-border-color, #d8e2ef);
        border-radius: .55rem;
        padding: .75rem .85rem;
        margin-bottom: .75rem;
        background: #fff;
    }

    .violation-ir-card:hover {
        background: var(--falcon-gray-100, #f9fafd);
    }

    .violation-ir-title {
        font-size: .85rem;
        font-weight: 700;
    }

    .violation-meta {
        font-size: .72rem;
    }

    .violation-desc-box {
        max-width: 100%;
        padding: .45rem .55rem;
        border: 1px solid var(--falcon-border-color, #d8e2ef);
        border-radius: .45rem;
        background: var(--falcon-gray-100, #f9fafd);
        font-size: .74rem;
    }

    .violation-action-group .btn {
        padding: .18rem .42rem;
        font-size: .72rem;
        line-height: 1.2;
    }

    .violation-compact-table td,
    .violation-compact-table th {
        padding: .45rem .55rem;
        vertical-align: middle;
        font-size: .78rem;
    }

    .violation-edit-modal .modal-body {
        padding: .9rem;
    }

    .violation-edit-modal .card-body {
        padding: .75rem;
    }

    .violation-edit-modal .form-label {
        font-size: .76rem;
        margin-bottom: .25rem;
    }

    .violation-edit-modal .form-control,
    .violation-edit-modal .form-select {
        font-size: .78rem;
    }

    .violation-edit-modal textarea {
        min-height: 65px;
    }

    .violation-row {
        padding: .7rem !important;
    }

    @media (max-width: 767.98px) {
        .violation-action-group {
            width: 100%;
            justify-content: flex-start;
        }

        .violation-action-group .btn {
            flex: 1;
        }
    }
</style>

<div class="card shadow-sm border-0 mb-3">
    <div class="card-header bg-body-tertiary border-bottom py-2">
        <div class="d-flex justify-content-between align-items-center gap-2">
            <div>
                <h6 class="mb-0 text-900">
                    <span class="fas fa-shield-alt text-primary me-2"></span>
                    Violation Histories
                </h6>
                <small class="text-600">
                    IR cases, offenses, actions, SDA, suspension, final warning, and remarks.
                </small>
            </div>

            <button type="button" class="btn btn-sm btn-falcon-primary" data-bs-toggle="modal"
                data-bs-target="#addHistoryModal">
                <span class="fas fa-plus me-1"></span>
                Add Record
            </button>
        </div>
    </div>

    @if ($groupedIrHistories->isNotEmpty())
        <div class="card-body border-bottom py-2">
            <div class="row g-2">
                <div class="col-6 col-md">
                    <div class="card violation-summary-card bg-primary-subtle border-0 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="mb-0 text-primary">{{ $groupedIrHistories->count() }}</h5>
                                    <p class="mb-0 text-700">IR Cases</p>
                                </div>
                                <span class="fas fa-folder-open text-primary opacity-50"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md">
                    <div class="card violation-summary-card bg-danger-subtle border-0 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="mb-0 text-danger">{{ $totalViolations }}</h5>
                                    <p class="mb-0 text-700">Violations</p>
                                </div>
                                <span class="fas fa-exclamation-triangle text-danger opacity-50"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md">
                    <div class="card violation-summary-card bg-warning-subtle border-0 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="mb-0 text-warning">{{ $sdaCount }}</h5>
                                    <p class="mb-0 text-700">With SDA</p>
                                </div>
                                <span class="fas fa-file-invoice-dollar text-warning opacity-50"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md">
                    <div class="card violation-summary-card bg-danger-subtle border-0 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="mb-0 text-danger">{{ $suspensionCount }}</h5>
                                    <p class="mb-0 text-700">Suspension</p>
                                </div>
                                <span class="fas fa-ban text-danger opacity-50"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md">
                    <div class="card violation-summary-card bg-secondary-subtle border-0 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="mb-0 text-secondary">{{ $finalWarningCount }}</h5>
                                    <p class="mb-0 text-700">Final Warning</p>
                                </div>
                                <span class="fas fa-exclamation-circle text-secondary opacity-50"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md">
                    <div class="card violation-summary-card bg-info-subtle border-0 h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="mb-0 text-info">{{ $remarksCount }}</h5>
                                    <p class="mb-0 text-700">Remarks</p>
                                </div>
                                <span class="fas fa-comment-dots text-info opacity-50"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="card-body py-2">
        @forelse($groupedIrHistories as $group)
            @php
                $first = $group['first_record'];
                $records = collect($group['records'] ?? []);

                $actions = $normalizeActions($group['actions'] ?? []);

                if (empty($actions)) {
                    $actions = $records
                        ->flatMap(fn($record) => $normalizeActions($record->disciplinary_action ?? []))
                        ->unique()
                        ->values()
                        ->all();
                }

                $hasSus = in_array('Suspension', $actions, true);
                $hasSda = in_array('Salary Deduction Authorization', $actions, true);
                $hasFinalWarning = in_array('Final Warning', $actions, true);

                $safeIrKey = md5((string) ($group['ir_number'] ?? $first->id));

                $viewModalId = 'irModal_' . $safeIrKey;
                $editModalId = 'editModal_' . $safeIrKey;
                $violationFieldsId = 'violationFields_' . $safeIrKey;

                $groupRemarks = $records->pluck('remarks')->filter(fn($value) => filled($value))->unique()->values();

                $remarksText = $groupRemarks->first();

                $firstRecord = $records->first();

                $firstDescription = $firstRecord?->description ?: $firstRecord?->offense?->offense_description ?: null;
            @endphp

            <div class="violation-ir-card">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-2">
                    <div class="flex-grow-1">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <span class="violation-ir-title text-900">
                                <span class="fas fa-hashtag text-primary me-1"></span>
                                {{ $group['ir_number'] ?: 'NO-IR' }}
                            </span>

                            <span class="badge badge-subtle-primary">
                                {{ $group['count'] }} {{ Str::plural('Violation', $group['count']) }}
                            </span>

                            @if ($hasSda)
                                <span class="badge badge-subtle-warning">SDA</span>
                            @endif

                            @if ($hasSus)
                                <span class="badge badge-subtle-danger">Suspension</span>
                            @endif

                            @if ($hasFinalWarning)
                                <span class="badge badge-subtle-secondary">Final Warning</span>
                            @endif

                            @if (filled($remarksText))
                                <span class="badge badge-subtle-info">With Remarks</span>
                            @endif
                        </div>

                        <div class="violation-meta text-600 mb-2">
                            <span class="far fa-clock me-1"></span>
                            Recorded {{ $formatDateTimeText($first->created_at) }}
                        </div>

                        @if (filled($firstDescription))
                            <div class="violation-desc-box mb-2">
                                <div class="text-uppercase fw-bold text-600 mb-1" style="font-size:.65rem;">
                                    First Offense Description
                                </div>
                                <div class="text-800">
                                    {{ Str::limit($firstDescription, 140) }}
                                </div>
                            </div>
                        @endif

                        @if (filled($remarksText))
                            <div class="violation-desc-box bg-info-subtle mb-2">
                                <div class="text-uppercase fw-bold text-info mb-1" style="font-size:.65rem;">
                                    Remarks
                                </div>
                                <div class="text-800">
                                    {{ Str::limit($remarksText, 140) }}
                                </div>
                            </div>
                        @endif

                        <div class="row g-2 violation-meta text-700">
                            <div class="col-md-4">
                                <span class="text-600">Offense Count:</span>
                                <strong>{{ $group['count'] }} {{ Str::plural('record', $group['count']) }}</strong>
                            </div>

                            <div class="col-md-4">
                                <span class="text-600">Action:</span>
                                <strong>{{ count($actions) ? implode(', ', $actions) : 'No action selected' }}</strong>
                            </div>

                            <div class="col-md-4">
                                <span class="text-600">Updated:</span>
                                <strong>{{ $formatDateTimeText($first->updated_at) }}</strong>
                            </div>

                            @if ($hasSda)
                                <div class="col-md-4">
                                    <span class="text-600">SDA Amount:</span>
                                    <strong class="text-warning">
                                        ₱{{ number_format((float) ($first->sda_amount ?? 0), 2) }}
                                    </strong>
                                </div>

                                <div class="col-md-4">
                                    <span class="text-600">Per Cutoff:</span>
                                    <strong class="text-warning">
                                        ₱{{ number_format((float) ($first->sda_terms ?? 0), 2) }}
                                    </strong>
                                </div>

                                <div class="col-md-4">
                                    <span class="text-600">SDA Coverage:</span>
                                    <strong>
                                        {{ $formatDateText($first->sda_start_date) }}
                                        →
                                        {{ $formatDateText($first->sda_end_date, 'Ongoing') }}
                                    </strong>
                                </div>
                            @endif

                            @if ($hasSus)
                                <div class="col-md-6">
                                    <span class="text-600">Suspension Start:</span>
                                    <strong class="text-danger">
                                        {{ $formatDateText($first->suspension_start_date) }}
                                    </strong>
                                </div>

                                <div class="col-md-6">
                                    <span class="text-600">Suspension End:</span>
                                    <strong class="text-danger">
                                        {{ $formatDateText($first->suspension_end_date, 'Ongoing') }}
                                    </strong>
                                </div>
                            @endif

                            @if ($hasFinalWarning)
                                <div class="col-md-4">
                                    <span class="text-600">Final Warning:</span>
                                    <strong class="text-secondary">Yes</strong>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="btn-group violation-action-group flex-shrink-0" role="group"
                        aria-label="IR Actions">
                        <button type="button" class="btn btn-sm btn-falcon-default" data-bs-toggle="modal"
                            data-bs-target="#{{ $viewModalId }}" title="View IR Details">
                            <span class="fas fa-eye"></span>
                        </button>

                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                            data-bs-target="#{{ $editModalId }}" title="Edit IR">
                            <span class="fas fa-edit"></span>
                        </button>

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
            </div>

            {{-- VIEW MODAL --}}
            <div class="modal fade" id="{{ $viewModalId }}" tabindex="-1"
                aria-labelledby="{{ $viewModalId }}Label" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-body-tertiary py-2">
                            <div>
                                <h6 class="modal-title text-900 mb-0" id="{{ $viewModalId }}Label">
                                    <span class="fas fa-file-alt text-primary me-2"></span>
                                    IR Details: {{ $group['ir_number'] ?: 'NO-IR' }}
                                </h6>
                                <small class="text-600">
                                    Complete violation, action, SDA, suspension, final warning, and remarks.
                                </small>
                            </div>

                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <div class="modal-body py-2">
                            <div class="table-responsive mb-3">
                                <table class="table table-bordered violation-compact-table mb-0">
                                    <tbody>
                                        <tr>
                                            <th class="bg-body-tertiary" width="25%">IR Number</th>
                                            <td>{{ $group['ir_number'] ?: 'NO-IR' }}</td>
                                            <th class="bg-body-tertiary" width="25%">Total Violations</th>
                                            <td>{{ $group['count'] }}</td>
                                        </tr>

                                        <tr>
                                            <th class="bg-body-tertiary">Recorded</th>
                                            <td>{{ $formatDateTimeText($first->created_at) }}</td>
                                            <th class="bg-body-tertiary">Updated</th>
                                            <td>{{ $formatDateTimeText($first->updated_at) }}</td>
                                        </tr>

                                        <tr>
                                            <th class="bg-body-tertiary">Action</th>
                                            <td colspan="3">
                                                @if (count($actions))
                                                    @foreach ($actions as $action)
                                                        <span class="badge badge-subtle-primary me-1">
                                                            {{ $action }}
                                                        </span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">No action selected</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            @if ($hasFinalWarning)
                                <div class="alert alert-secondary border-0 py-2 mb-3">
                                    <span class="fas fa-exclamation-circle me-1"></span>
                                    This IR case is marked as <strong>Final Warning</strong>.
                                </div>
                            @endif

                            <h6 class="text-900 mb-2">
                                <span class="fas fa-list-ul text-primary me-1"></span>
                                Violations
                            </h6>

                            <div class="table-responsive mb-3">
                                <table class="table table-sm table-bordered violation-compact-table mb-0">
                                    <thead class="bg-body-tertiary">
                                        <tr>
                                            <th width="8%">#</th>
                                            <th width="22%">Section</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($records as $index => $record)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <span class="badge badge-subtle-primary">
                                                        {{ $record->offense?->section ?? '—' }}
                                                    </span>

                                                    @if ($record->offense?->type)
                                                        <div class="text-600 mt-1">
                                                            Type {{ $record->offense?->type }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $record->description ?: $record->offense?->offense_description ?? 'No description provided.' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <h6 class="text-900 mb-2">
                                <span class="fas fa-comment-dots text-info me-1"></span>
                                Remarks / Other Description
                            </h6>

                            <div class="border rounded-2 p-2 bg-info-subtle mb-3">
                                @if ($groupRemarks->isNotEmpty())
                                    @foreach ($groupRemarks as $remark)
                                        <p class="mb-1 fs--1 text-800">
                                            {{ $remark }}
                                        </p>
                                    @endforeach
                                @else
                                    <p class="mb-0 fs--1 text-600">
                                        No remarks encoded.
                                    </p>
                                @endif
                            </div>

                            @if ($hasSda)
                                <h6 class="text-900 mb-2">
                                    <span class="fas fa-file-invoice-dollar text-warning me-1"></span>
                                    Salary Deduction Authorization
                                </h6>

                                <div class="table-responsive mb-3">
                                    <table class="table table-bordered violation-compact-table mb-0">
                                        <tbody>
                                            <tr>
                                                <th class="bg-warning-subtle">Total Amount</th>
                                                <td>₱{{ number_format((float) ($first->sda_amount ?? 0), 2) }}</td>
                                                <th class="bg-warning-subtle">Per Cutoff</th>
                                                <td>₱{{ number_format((float) ($first->sda_terms ?? 0), 2) }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-warning-subtle">Start Date</th>
                                                <td>{{ $formatDateText($first->sda_start_date) }}</td>
                                                <th class="bg-warning-subtle">End Date</th>
                                                <td>{{ $formatDateText($first->sda_end_date, 'Ongoing') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            @if ($hasSus)
                                <h6 class="text-900 mb-2">
                                    <span class="fas fa-ban text-danger me-1"></span>
                                    Suspension Details
                                </h6>

                                <div class="table-responsive">
                                    <table class="table table-bordered violation-compact-table mb-0">
                                        <tbody>
                                            <tr>
                                                <th class="bg-danger-subtle">Start Date</th>
                                                <td>{{ $formatDateText($first->suspension_start_date) }}</td>
                                                <th class="bg-danger-subtle">End Date</th>
                                                <td>{{ $formatDateText($first->suspension_end_date, 'Ongoing') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        <div class="modal-footer bg-body-tertiary py-2">
                            <button type="button" class="btn btn-sm btn-falcon-primary"
                                data-bs-target="#{{ $editModalId }}" data-bs-toggle="modal"
                                data-bs-dismiss="modal">
                                <span class="fas fa-edit me-1"></span>
                                Edit
                            </button>

                            <button type="button" class="btn btn-sm btn-falcon-default" data-bs-dismiss="modal">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- EDIT MODAL --}}
            <div class="modal fade violation-edit-modal" id="{{ $editModalId }}" tabindex="-1"
                aria-labelledby="{{ $editModalId }}Label" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <form action="{{ route('employees.staff.history.update', [$employee->id, $first->id]) }}"
                        method="POST"
                        class="modal-content border-0 shadow-lg violation-edit-form violation-action-form">
                        @csrf
                        @method('PUT')

                        <div class="modal-header bg-primary text-white py-2">
                            <div>
                                <h6 class="modal-title mb-0" id="{{ $editModalId }}Label">
                                    <span class="fas fa-edit me-2"></span>
                                    Edit IR Case
                                </h6>
                                <small class="text-white-50">
                                    Update violation details, action, remarks, SDA, suspension, and final warning.
                                </small>
                            </div>

                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <input type="hidden" name="title" value="Violations">

                            <div class="row g-2 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">IR Number</label>
                                    <input type="text" name="ir_number" class="form-control"
                                        value="{{ old('ir_number', $group['ir_number']) }}"
                                        placeholder="IR-2026-0001" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Violation Count</label>
                                    <input type="text" class="form-control"
                                        value="{{ $group['count'] }} {{ Str::plural('Violation', $group['count']) }}"
                                        disabled>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Recorded</label>
                                    <input type="text" class="form-control"
                                        value="{{ $formatDateTimeText($first->created_at) }}" disabled>
                                </div>
                            </div>

                            <div class="border rounded-3 p-2 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0 text-900">
                                        <span class="fas fa-list-ul text-primary me-1"></span>
                                        Violation Details
                                    </h6>

                                    <button type="button" class="btn btn-sm btn-outline-primary addViolationBtn"
                                        data-target="#{{ $violationFieldsId }}">
                                        <span class="fas fa-plus me-1"></span>
                                        Add
                                    </button>
                                </div>

                                <div id="{{ $violationFieldsId }}" class="violation-fields">
                                    @foreach ($records as $index => $record)
                                        <div class="violation-row border rounded-2 mb-2 bg-body-tertiary">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="mb-0 violation-number text-900">
                                                    Violation #{{ $index + 1 }}
                                                </h6>

                                                <button type="button"
                                                    class="btn btn-sm btn-falcon-danger removeViolation {{ $loop->first ? 'd-none' : '' }}">
                                                    <span class="fas fa-trash"></span>
                                                </button>
                                            </div>

                                            <div class="mb-2">
                                                <label class="form-label fw-semibold">Offense Section</label>
                                                <select name="offense_id[]" class="form-select offenseSelect"
                                                    required>
                                                    <option value="">-- Select Offense Section --</option>
                                                    @foreach ($offenses as $o)
                                                        <option value="{{ $o->id }}"
                                                            data-description="{{ e($o->offense_description) }}"
                                                            @selected($record->offense_id == $o->id)>
                                                            {{ $o->section }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div>
                                                <label class="form-label fw-semibold">Description</label>
                                                <textarea name="description[]" class="form-control offenseDescription" rows="2"
                                                    placeholder="Description will auto-fill after selecting section.">{{ old('description.' . $index, $record->description ?: $record->offense?->offense_description ?? '') }}</textarea>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="border rounded-3 p-2 mb-3 bg-info-subtle">
                                <label class="form-label fw-semibold">
                                    Remarks / Other Description
                                    <span class="text-muted">(Optional)</span>
                                </label>

                                <textarea name="remarks" class="form-control" rows="2"
                                    placeholder="Employee explanation, HR notes, or follow-up details...">{{ old('remarks', $remarksText) }}</textarea>
                            </div>

                            <div class="border rounded-3 p-2 mb-3">
                                <label class="form-label fw-semibold mb-2">Disciplinary Action</label>

                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input action-checkbox js-action-sda"
                                                type="checkbox" name="disciplinary_action[]"
                                                value="Salary Deduction Authorization"
                                                id="actionSDA_{{ $safeIrKey }}" @checked($hasSda)>

                                            <label class="form-check-label" for="actionSDA_{{ $safeIrKey }}">
                                                Salary Deduction Authorization
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input action-checkbox js-action-suspension"
                                                type="checkbox" name="disciplinary_action[]" value="Suspension"
                                                id="actionSuspension_{{ $safeIrKey }}"
                                                @checked($hasSus)>

                                            <label class="form-check-label"
                                                for="actionSuspension_{{ $safeIrKey }}">
                                                Suspension
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input action-checkbox" type="checkbox"
                                                name="disciplinary_action[]" value="Final Warning"
                                                id="actionFinalWarning_{{ $safeIrKey }}"
                                                @checked($hasFinalWarning)>

                                            <label class="form-check-label"
                                                for="actionFinalWarning_{{ $safeIrKey }}">
                                                Final Warning
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="sda-fields-wrapper border rounded-3 p-2 mb-3 {{ $hasSda ? '' : 'd-none' }}">
                                <h6 class="text-warning-emphasis mb-2">
                                    <span class="fas fa-file-invoice-dollar me-1"></span>
                                    SDA Details
                                </h6>

                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Total Amount</label>
                                        <input type="number" step="0.01" min="0" name="sda_amount"
                                            class="form-control js-sda-required"
                                            value="{{ old('sda_amount', $first->sda_amount) }}" placeholder="1500.00"
                                            @required($hasSda) @disabled(!$hasSda)>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Per Cutoff</label>
                                        <input type="number" min="0" step="0.01" name="sda_terms"
                                            class="form-control js-sda-required"
                                            value="{{ old('sda_terms', $first->sda_terms) }}" placeholder="500.00"
                                            @required($hasSda) @disabled(!$hasSda)>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Start Date</label>
                                        <input type="date" name="sda_start_date"
                                            class="form-control js-sda-required"
                                            value="{{ old('sda_start_date', $formatDateInput($first->sda_start_date)) }}"
                                            @required($hasSda) @disabled(!$hasSda)>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">End Date</label>
                                        <input type="date" name="sda_end_date"
                                            class="form-control js-sda-optional"
                                            value="{{ old('sda_end_date', $formatDateInput($first->sda_end_date)) }}"
                                            @disabled(!$hasSda)>
                                    </div>
                                </div>
                            </div>

                            <div class="suspension-dates-wrapper border rounded-3 p-2 {{ $hasSus ? '' : 'd-none' }}">
                                <h6 class="text-danger-emphasis mb-2">
                                    <span class="fas fa-ban me-1"></span>
                                    Suspension Details
                                </h6>

                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Start Date</label>
                                        <input type="date" name="suspension_start_date"
                                            class="form-control js-suspension-required"
                                            value="{{ old('suspension_start_date', $formatDateInput($first->suspension_start_date)) }}"
                                            @required($hasSus) @disabled(!$hasSus)>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">End Date</label>
                                        <input type="date" name="suspension_end_date"
                                            class="form-control js-suspension-optional"
                                            value="{{ old('suspension_end_date', $formatDateInput($first->suspension_end_date)) }}"
                                            @disabled(!$hasSus)>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-body-tertiary py-2">
                            <button type="button" class="btn btn-sm btn-falcon-secondary" data-bs-dismiss="modal">
                                Cancel
                            </button>

                            <button type="submit" class="btn btn-sm btn-falcon-primary">
                                <span class="fas fa-save me-1"></span>
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div class="text-center py-4">
                <div class="avatar avatar-4xl mx-auto mb-3">
                    <div class="avatar-name rounded-circle bg-success-subtle text-success">
                        <span class="fas fa-clipboard-check"></span>
                    </div>
                </div>

                <h6 class="text-700 mb-1">No violation records found.</h6>
                <p class="fs--1 text-500 mb-0">
                    This employee has no recorded IR case or violation history.
                </p>

                <button type="button" class="btn btn-sm btn-falcon-primary mt-3" data-bs-toggle="modal"
                    data-bs-target="#addHistoryModal">
                    <span class="fas fa-plus me-1"></span>
                    Add First Record
                </button>
            </div>
        @endforelse
    </div>
</div>

{{-- ADD MODAL --}}
<div class="modal fade" id="addHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form action="{{ route('employees.staff.history.store', $employee->id) }}" method="POST"
            class="modal-content border-0 shadow-lg violation-action-form">
            @csrf

            <div class="modal-header bg-primary text-white py-2">
                <div>
                    <h6 class="modal-title mb-0">
                        <span class="fas fa-plus me-2"></span>
                        Add Violation
                    </h6>
                    <small class="text-white-50">
                        Encode IR number, offense details, actions, SDA, suspension, and final warning.
                    </small>
                </div>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="title" value="Violations">

                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">IR Number</label>
                        <input type="text" name="ir_number" class="form-control" value="{{ old('ir_number') }}"
                            placeholder="IR-2026-0001" required>
                    </div>
                </div>

                <div id="violationFields">
                    <div class="border rounded-3 p-2 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 text-900">
                                <span class="fas fa-list-ul text-primary me-1"></span>
                                Violation Details
                            </h6>

                            <button type="button" id="addViolationBtn" class="btn btn-sm btn-outline-primary">
                                <span class="fas fa-plus me-1"></span>
                                Add
                            </button>
                        </div>

                        <div id="violationsContainer">
                            <div class="violation-row border rounded-2 mb-2 bg-body-tertiary">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0 violation-title text-900">Violation #1</h6>

                                    <button type="button"
                                        class="btn btn-sm btn-falcon-danger removeViolation d-none">
                                        <span class="fas fa-trash"></span>
                                    </button>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label fw-semibold">Offense Section</label>
                                    <select name="offense_id[]" class="form-select offenseSelect" required>
                                        <option value="">-- Select Offense Section --</option>

                                        @foreach ($offenses ?? [] as $o)
                                            <option value="{{ $o->id }}"
                                                data-description="{{ e($o->offense_description) }}">
                                                {{ $o->section }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="form-label fw-semibold">Description</label>
                                    <textarea name="description[]" class="form-control offenseDescription" rows="2"
                                        placeholder="Description will auto-fill after selecting section."></textarea>
                                </div>
                            </div>
                        </div>

                        <small class="text-muted">
                            Selecting a section will auto-fill the description.
                        </small>
                    </div>

                    <div class="border rounded-3 p-2 mb-3 bg-info-subtle">
                        <label class="form-label fw-semibold">
                            Remarks / Other Description
                            <span class="text-muted">(Optional)</span>
                        </label>

                        <textarea name="remarks" class="form-control" rows="2"
                            placeholder="Employee explanation, HR notes, or follow-up details...">{{ old('remarks') }}</textarea>
                    </div>

                    <div class="border rounded-3 p-2 mb-3">
                        <label class="form-label fw-semibold mb-2">Disciplinary Action</label>

                        <div class="row g-2">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input action-checkbox js-action-sda" type="checkbox"
                                        name="disciplinary_action[]" value="Salary Deduction Authorization"
                                        id="actionSDA" @checked(in_array('Salary Deduction Authorization', old('disciplinary_action', []), true))>

                                    <label class="form-check-label" for="actionSDA">
                                        Salary Deduction Authorization
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input action-checkbox js-action-suspension"
                                        type="checkbox" name="disciplinary_action[]" value="Suspension"
                                        id="actionSuspension" @checked(in_array('Suspension', old('disciplinary_action', []), true))>

                                    <label class="form-check-label" for="actionSuspension">
                                        Suspension
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input action-checkbox" type="checkbox"
                                        name="disciplinary_action[]" value="Final Warning" id="actionFinalWarning"
                                        @checked(in_array('Final Warning', old('disciplinary_action', []), true))>

                                    <label class="form-check-label" for="actionFinalWarning">
                                        Final Warning
                                    </label>
                                </div>
                            </div>
                        </div>

                        <small class="text-muted">You may select multiple actions.</small>
                    </div>

                    <div id="sdaFieldsWrapper" class="sda-fields-wrapper border rounded-3 p-2 mb-3 d-none">
                        <h6 class="text-warning-emphasis mb-2">
                            <span class="fas fa-file-invoice-dollar me-1"></span>
                            SDA Details
                        </h6>

                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">SDA Total Amount</label>
                                <input type="number" step="0.01" min="0" name="sda_amount"
                                    class="form-control js-sda-required" value="{{ old('sda_amount') }}"
                                    placeholder="Enter total deduction amount" disabled>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Deduction Terms / Per Cutoff</label>
                                <input type="number" min="0" step="0.01" name="sda_terms"
                                    class="form-control js-sda-required" value="{{ old('sda_terms') }}"
                                    placeholder="e.g. 500.00" disabled>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Deduction Start Date</label>
                                <input type="date" name="sda_start_date" class="form-control js-sda-required"
                                    value="{{ old('sda_start_date') }}" disabled>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    Deduction End Date <span class="text-muted">(Optional)</span>
                                </label>
                                <input type="date" name="sda_end_date" class="form-control js-sda-optional"
                                    value="{{ old('sda_end_date') }}" disabled>
                                <small class="text-muted">Leave blank if ongoing.</small>
                            </div>
                        </div>
                    </div>

                    <div class="suspension-dates-wrapper border rounded-3 p-2 mb-3 d-none">
                        <h6 class="text-danger-emphasis mb-2">
                            <span class="fas fa-ban me-1"></span>
                            Suspension Details
                        </h6>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Suspension Start Date</label>
                                <input type="date" name="suspension_start_date"
                                    class="form-control js-suspension-required"
                                    value="{{ old('suspension_start_date') }}" disabled>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Suspension End Date</label>
                                <input type="date" name="suspension_end_date"
                                    class="form-control js-suspension-optional"
                                    value="{{ old('suspension_end_date') }}" disabled>
                                <small class="text-muted">Leave blank if still suspended.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-body-tertiary py-2">
                <button type="button" class="btn btn-sm btn-falcon-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>

                <button type="submit" class="btn btn-sm btn-falcon-primary">
                    <span class="fas fa-save me-1"></span>
                    Save Violation
                </button>
            </div>
        </form>
    </div>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function updateViolationNumbers(container) {
                const rows = container.querySelectorAll('.violation-row');

                rows.forEach(function(row, index) {
                    const title = row.querySelector('.violation-number') || row.querySelector(
                        '.violation-title');
                    const removeBtn = row.querySelector('.removeViolation');

                    if (title) {
                        title.textContent = 'Violation #' + (index + 1);
                    }

                    if (removeBtn) {
                        if (rows.length === 1 || index === 0) {
                            removeBtn.classList.add('d-none');
                        } else {
                            removeBtn.classList.remove('d-none');
                        }
                    }
                });
            }

            function toggleDisciplinaryFields(form) {
                const sdaCheckbox = form.querySelector('.js-action-sda');
                const suspensionCheckbox = form.querySelector('.js-action-suspension');

                const sdaWrapper = form.querySelector('.sda-fields-wrapper');
                const suspensionWrapper = form.querySelector('.suspension-dates-wrapper');

                const sdaRequiredFields = form.querySelectorAll('.js-sda-required');
                const sdaOptionalFields = form.querySelectorAll('.js-sda-optional');

                const suspensionRequiredFields = form.querySelectorAll('.js-suspension-required');
                const suspensionOptionalFields = form.querySelectorAll('.js-suspension-optional');

                const hasSda = sdaCheckbox && sdaCheckbox.checked;
                const hasSuspension = suspensionCheckbox && suspensionCheckbox.checked;

                if (sdaWrapper) {
                    sdaWrapper.classList.toggle('d-none', !hasSda);
                }

                sdaRequiredFields.forEach(function(field) {
                    field.disabled = !hasSda;
                    field.required = hasSda;

                    if (!hasSda) {
                        field.value = '';
                    }
                });

                sdaOptionalFields.forEach(function(field) {
                    field.disabled = !hasSda;
                    field.required = false;

                    if (!hasSda) {
                        field.value = '';
                    }
                });

                if (suspensionWrapper) {
                    suspensionWrapper.classList.toggle('d-none', !hasSuspension);
                }

                suspensionRequiredFields.forEach(function(field) {
                    field.disabled = !hasSuspension;
                    field.required = hasSuspension;

                    if (!hasSuspension) {
                        field.value = '';
                    }
                });

                suspensionOptionalFields.forEach(function(field) {
                    field.disabled = !hasSuspension;
                    field.required = false;

                    if (!hasSuspension) {
                        field.value = '';
                    }
                });
            }

            document.querySelectorAll('.violation-action-form').forEach(function(form) {
                toggleDisciplinaryFields(form);
            });

            document.addEventListener('change', function(event) {
                if (event.target.classList.contains('offenseSelect')) {
                    const selectedOption = event.target.options[event.target.selectedIndex];
                    const description = selectedOption ? selectedOption.getAttribute('data-description') :
                        '';
                    const row = event.target.closest('.violation-row');
                    const descriptionBox = row ? row.querySelector('.offenseDescription') : null;

                    if (descriptionBox) {
                        descriptionBox.value = description || '';
                    }
                }

                if (
                    event.target.classList.contains('js-action-sda') ||
                    event.target.classList.contains('js-action-suspension')
                ) {
                    const form = event.target.closest('form');

                    if (form) {
                        toggleDisciplinaryFields(form);
                    }
                }
            });

            document.addEventListener('click', function(event) {
                const addViolationBtn = event.target.closest('#addViolationBtn');

                if (addViolationBtn) {
                    const container = document.querySelector('#violationsContainer');

                    if (!container) {
                        return;
                    }

                    const firstRow = container.querySelector('.violation-row');

                    if (!firstRow) {
                        return;
                    }

                    const newRow = firstRow.cloneNode(true);

                    newRow.querySelectorAll('select').forEach(function(select) {
                        select.selectedIndex = 0;
                    });

                    newRow.querySelectorAll('textarea').forEach(function(textarea) {
                        textarea.value = '';
                    });

                    newRow.querySelectorAll('input').forEach(function(input) {
                        if (input.type === 'checkbox' || input.type === 'radio') {
                            input.checked = false;
                        } else {
                            input.value = '';
                        }
                    });

                    const removeBtn = newRow.querySelector('.removeViolation');

                    if (removeBtn) {
                        removeBtn.classList.remove('d-none');
                    }

                    container.appendChild(newRow);
                    updateViolationNumbers(container);
                }

                const editAddBtn = event.target.closest('.addViolationBtn');

                if (editAddBtn) {
                    const targetSelector = editAddBtn.getAttribute('data-target');
                    const container = document.querySelector(targetSelector);

                    if (!container) {
                        return;
                    }

                    const firstRow = container.querySelector('.violation-row');

                    if (!firstRow) {
                        return;
                    }

                    const newRow = firstRow.cloneNode(true);

                    newRow.querySelectorAll('select').forEach(function(select) {
                        select.selectedIndex = 0;
                    });

                    newRow.querySelectorAll('textarea').forEach(function(textarea) {
                        textarea.value = '';
                    });

                    newRow.querySelectorAll('input').forEach(function(input) {
                        if (input.type === 'checkbox' || input.type === 'radio') {
                            input.checked = false;
                        } else {
                            input.value = '';
                        }
                    });

                    const removeBtn = newRow.querySelector('.removeViolation');

                    if (removeBtn) {
                        removeBtn.classList.remove('d-none');
                    }

                    container.appendChild(newRow);
                    updateViolationNumbers(container);
                }

                const removeBtn = event.target.closest('.removeViolation');

                if (removeBtn) {
                    const row = removeBtn.closest('.violation-row');
                    const container = removeBtn.closest('#violationsContainer') || removeBtn.closest(
                        '.violation-fields');

                    if (!row || !container) {
                        return;
                    }

                    const rows = container.querySelectorAll('.violation-row');

                    if (rows.length <= 1) {
                        return;
                    }

                    row.remove();
                    updateViolationNumbers(container);
                }
            });
        });
    </script>
@endonce
