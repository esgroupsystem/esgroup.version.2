@php
    $lp = $employee->last_pay_status;
    $badge = $lp === 'Released' ? 'badge-subtle-success' : 'badge-subtle-warning';
@endphp

<div class="card mb-3 shadow-sm">
    <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">
            <i class="fas fa-clipboard-list mono-icon me-2"></i> Employee Status Details
        </h5>
        <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#editStatusDetailsModal">
            <i class="fas fa-edit me-1"></i> Edit
        </button>
    </div>

    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="small text-muted fw-bold">Last Pay Status</div>
                <span class="badge rounded-pill {{ $lp ? $badge : 'badge-subtle-secondary' }}">
                    {{ $lp ?? '—' }}
                </span>

                @if ($employee->last_pay_date)
                    <div class="small text-muted mt-1">
                        <i class="fas fa-calendar-alt me-1"></i>
                        {{ $employee->last_pay_date->format('M d, Y') }}
                    </div>
                @endif
            </div>

            <div class="col-md-6">
                <div class="small text-muted fw-bold">Date Resigned</div>
                <div>{{ $employee->date_resigned?->format('M d, Y') ?? '—' }}</div>
            </div>

            <div class="col-md-6">
                <div class="small text-muted fw-bold">Last Duty</div>
                <div>{{ $employee->last_duty?->format('M d, Y') ?? '—' }}</div>
            </div>

            <div class="col-md-6">
                <div class="small text-muted fw-bold">Clearance Date</div>
                <div>{{ $employee->clearance_date?->format('M d, Y') ?? '—' }}</div>
            </div>
        </div>
    </div>
</div>