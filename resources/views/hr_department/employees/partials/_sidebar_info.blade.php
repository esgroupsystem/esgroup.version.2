<div class="card mb-3 shadow-sm">
    <div class="card-body">
        <div class="fw-bold text-muted mb-2">Employment Info</div>
        <div><i class="fas fa-calendar-check me-2"></i>Hired:
            <strong>{{ $employee->date_hired?->format('M d, Y') ?? '—' }}</strong>
        </div>
        <div><i class="fas fa-hourglass-half me-2"></i>Tenure: <strong>{{ $tenure }}</strong></div>
        <div><i class="fas fa-user-clock me-2"></i>Age: <strong>{{ $age }}</strong></div>

        <div class="fw-bold text-muted mt-3 mb-2">Address</div>
        <div><i class="fas fa-map-marker-alt me-2"></i>{{ $employee->address_1 ?? '—' }}</div>
        <div><i class="fas fa-location-arrow me-2"></i>{{ $employee->address_2 ?? '—' }}</div>

        <div class="fw-bold text-muted mt-3 mb-2">Emergency Contact</div>
        <div><i class="fas fa-user-shield me-2"></i>{{ $employee->emergency_name ?? '—' }}</div>
        <div><i class="fas fa-phone-alt me-2"></i>{{ $employee->emergency_contact ?? '—' }}</div>

        <div class="fw-bold text-muted mt-3 mb-2">Contact</div>
        <div><i class="fas fa-envelope me-2"></i>{{ $employee->email ?? '—' }}</div>
        <div><i class="fas fa-phone me-2"></i>{{ $employee->phone_number ?? '—' }}</div>

        <div class="fw-bold text-muted mt-3 mb-2">Company</div>
        <div><i class="fas fa-building me-2"></i>{{ $employee->company ?? '—' }}</div>
        <div><i class="fas fa-warehouse me-2"></i>{{ $employee->garage ?? '—' }}</div>
    </div>
</div>
