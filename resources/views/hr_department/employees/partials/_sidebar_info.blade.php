<div class="card mb-3 shadow-sm border-0">
    <div class="card-body">

        {{-- Employment Info --}}
        <div class="fw-bold text-muted mb-2">Employment Info</div>
        <div class="mb-1">
            <i class="fas fa-calendar-check text-primary me-2"></i>
            Hired: <strong>{{ $employee->date_hired?->format('M d, Y') ?? '—' }}</strong>
        </div>
        <div class="mb-1">
            <i class="fas fa-hourglass-half text-warning me-2"></i>
            Tenure: <strong>{{ $tenure }}</strong>
        </div>
        <div class="mb-3">
            <i class="fas fa-user-clock text-info me-2"></i>
            Age: <strong>{{ $age }}</strong>
        </div>

        {{-- Address --}}
        <div class="fw-bold text-muted mt-3 mb-2">Address</div>
        <div class="mb-1">
            <i class="fas fa-map-marker-alt text-danger me-2"></i>
            {{ $employee->address_1 ?? '—' }}
        </div>
        <div class="mb-3">
            <i class="fas fa-location-arrow text-danger me-2"></i>
            {{ $employee->address_2 ?? '—' }}
        </div>

        {{-- Emergency Contact --}}
        <div class="fw-bold text-muted mt-3 mb-2">Emergency Contact</div>
        <div class="mb-1">
            <i class="fas fa-user-shield text-warning me-2"></i>
            {{ $employee->emergency_name ?? '—' }}
        </div>
        <div class="mb-3">
            <i class="fas fa-phone-alt text-warning me-2"></i>
            {{ $employee->emergency_contact ?? '—' }}
        </div>

        {{-- Contact --}}
        <div class="fw-bold text-muted mt-3 mb-2">Contact</div>
        <div class="mb-1">
            <i class="fas fa-envelope text-primary me-2"></i>
            {{ $employee->email ?? '—' }}
        </div>
        <div class="mb-3">
            <i class="fas fa-phone text-primary me-2"></i>
            {{ $employee->phone_number ?? '—' }}
        </div>

        {{-- Company --}}
        <div class="fw-bold text-muted mt-3 mb-2">Company</div>
        <div class="mb-1">
            <i class="fas fa-building text-info me-2"></i>
            {{ $employee->company ?? '—' }}
        </div>
        <div>
            <i class="fas fa-warehouse text-info me-2"></i>
            {{ $employee->garage ?? '—' }}
        </div>

    </div>
</div>
