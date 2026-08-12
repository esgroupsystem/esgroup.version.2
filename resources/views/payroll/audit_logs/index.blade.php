@extends('layouts.app')
@section('title', 'Payroll Transaction Logs')

@section('content')
    <div class="container-fluid" data-layout="container">
        <div class="content">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-body-tertiary border-bottom py-3">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-2 align-items-lg-center">
                        <div>
                            <h4 class="mb-1">
                                <i class="fas fa-history text-primary me-2"></i>Payroll Transaction Logs
                            </h4>
                            <p class="text-muted small mb-0">
                                Complete payroll audit trail for payroll runs, adjustments, benefits, biometric activity, employee rates/deductions, schedules, and related payroll changes.
                            </p>
                        </div>
                        <span class="badge bg-primary-subtle text-primary fs-10 px-3 py-2">
                            {{ number_format($logs->total()) }} transaction(s)
                        </span>
                    </div>
                </div>

                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('payroll-audit-logs.index') }}" class="row g-2 align-items-end">
                        <div class="col-12 col-xl-3">
                            <label class="form-label small fw-semibold">Search</label>
                            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                                class="form-control" placeholder="Employee, payroll no., user, request ID...">
                        </div>
                        <div class="col-6 col-md-3 col-xl-2">
                            <label class="form-label small fw-semibold">Module</label>
                            <select name="module" class="form-select">
                                <option value="">All modules</option>
                                @foreach ($modules as $module)
                                    <option value="{{ $module }}" @selected(($filters['module'] ?? '') === $module)>
                                        {{ ucwords(str_replace('_', ' ', $module)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3 col-xl-2">
                            <label class="form-label small fw-semibold">Action</label>
                            <select name="action" class="form-select">
                                <option value="">All actions</option>
                                @foreach ($actions as $action)
                                    <option value="{{ $action }}" @selected(($filters['action'] ?? '') === $action)>
                                        {{ ucwords(str_replace('_', ' ', $action)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6 col-xl-2">
                            <label class="form-label small fw-semibold">User</label>
                            <select name="user_id" class="form-select">
                                <option value="">All users</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $user->id)>
                                        {{ $user->full_name ?: $user->username }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3 col-xl-1">
                            <label class="form-label small fw-semibold">From</label>
                            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control">
                        </div>
                        <div class="col-6 col-md-3 col-xl-1">
                            <label class="form-label small fw-semibold">To</label>
                            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control">
                        </div>
                        <div class="col-12 col-xl-1 d-grid">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-body-tertiary">
                            <tr>
                                <th class="ps-3 text-nowrap">Date / Time</th>
                                <th>User</th>
                                <th>Module</th>
                                <th>Action</th>
                                <th>Payroll / Employee Context</th>
                                <th>Description</th>
                                <th class="text-center">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                @php
                                    $badgeClass = match ($log->action) {
                                        'created' => 'success',
                                        'deleted' => 'danger',
                                        default => 'primary',
                                    };
                                    $detailId = 'audit-detail-' . $log->id;
                                @endphp
                                <tr>
                                    <td class="ps-3 text-nowrap">
                                        <div class="fw-semibold">{{ optional($log->created_at)->format('M d, Y') }}</div>
                                        <div class="small text-muted">{{ optional($log->created_at)->format('h:i:s A') }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $log->user?->full_name ?: ($log->user?->username ?: 'System / Console') }}</div>
                                        <div class="small text-muted">{{ $log->user?->email }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info">
                                            {{ ucwords(str_replace('_', ' ', $log->module)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $badgeClass }}-subtle text-{{ $badgeClass }}">
                                            {{ ucwords(str_replace('_', ' ', $log->action)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($log->payroll)
                                            <div class="fw-semibold">{{ $log->payroll->payroll_number }}</div>
                                        @endif
                                        @if ($log->employeeBiometric)
                                            <div class="fw-semibold">
                                                {{ $log->employeeBiometric->payroll_display_name ?? 'Employee' }}
                                            </div>
                                            <div class="small text-muted">
                                                {{ $log->employeeBiometric->effective_employee_no ?? $log->employeeBiometric->display_employee_no ?? $log->employeeBiometric->source_employee_no ?? ('Bio ID: '.$log->employee_biometric_id) }}
                                            </div>
                                        @elseif ($log->employee_biometric_id)
                                            <div class="small text-muted">Bio ID: {{ $log->employee_biometric_id }}</div>
                                        @endif
                                        <div class="small text-muted">Group: {{ $log->garage_group ?: 'N/A' }}</div>
                                    </td>
                                    <td style="min-width: 260px;">
                                        {{ $log->description ?: 'Payroll-related change' }}
                                        <div class="small text-muted mt-1">Request: {{ $log->request_id }}</div>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-secondary" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#{{ $detailId }}">
                                            <i class="fas fa-code"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr class="collapse" id="{{ $detailId }}">
                                    <td colspan="7" class="bg-body-tertiary px-3 py-3">
                                        <div class="row g-2 mb-3 small">
                                            <div class="col-md-4"><strong>Request ID:</strong> {{ $log->request_id }}</div>
                                            <div class="col-md-4"><strong>IP:</strong> {{ $log->ip_address ?: 'System / Console' }}</div>
                                            <div class="col-md-4 text-truncate" title="{{ $log->user_agent }}"><strong>User agent:</strong> {{ $log->user_agent ?: 'N/A' }}</div>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-lg-4">
                                                <div class="small fw-semibold mb-1">Before</div>
                                                <pre class="bg-white border rounded p-2 mb-0 small" style="max-height: 260px; overflow:auto;">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="small fw-semibold mb-1">After</div>
                                                <pre class="bg-white border rounded p-2 mb-0 small" style="max-height: 260px; overflow:auto;">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="small fw-semibold mb-1">Context</div>
                                                <pre class="bg-white border rounded p-2 mb-0 small" style="max-height: 260px; overflow:auto;">{{ json_encode($log->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-history fa-2x mb-3 d-block"></i>
                                        No payroll audit transactions found for the selected filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($logs->hasPages())
                    <div class="card-footer bg-white">
                        {{ $logs->links('pagination.custom') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
