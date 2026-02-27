<div class="card mb-4 shadow-sm profile-header-card">
    <div class="d-flex align-items-center p-3 header-flex">
        {{-- LEFT --}}
        <div class="d-flex align-items-center flex-grow-1 profile-left">
            <div class="me-3">
                <img class="rounded-circle" src="{{ $profilePath }}" alt="Profile">
            </div>

            <div>
                <h3 class="mb-1 fw-bold">
                    <i class="fas fa-user mono-icon me-2"></i> {{ $employee->full_name }}
                </h3>

                <div class="small-muted">
                    <i class="fas fa-id-badge mono-icon me-1"></i>
                    {{ $employee->position->title ?? 'No Position' }}
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    <i class="fas fa-building mono-icon me-1"></i>
                    {{ $employee->department->name ?? 'No Department' }}
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    <i class="fas fa-clipboard-check mono-icon me-1"></i>
                    Employment Status: <strong>{{ $employee->status ?? 'Active' }}</strong>
                </div>

                <div class="mt-3">
                    <a href="{{ session('employees_back_url', route('employees.staff.index')) }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>

                    <button class="btn btn-primary btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        <i class="fas fa-edit me-1"></i> Edit Profile
                    </button>

                    <a class="btn btn-outline-dark btn-sm ms-2"
                       href="{{ route('employees.staff.print', $employee->id) }}" target="_blank">
                        <i class="fas fa-print me-1"></i> Print 201 (PDF)
                    </a>
                </div>
            </div>
        </div>

        {{-- RIGHT: QR --}}
        <div class="qr-card ms-4">
            @if (!empty($employee->employee_id_permanent))
                <div class="qr-box">
                    {!! QrCode::size(82)->style('round')->margin(0)->backgroundColor(255, 255, 255)->generate((string) $employee->employee_id_permanent) !!}
                </div>
                <div class="qr-id-label">Permanent ID</div>
                <div class="qr-id-value">{{ $employee->employee_id_permanent }}</div>
            @else
                <div class="qr-empty">
                    <i class="fas fa-qrcode me-1"></i> No Permanent ID
                </div>
            @endif
        </div>

    </div>
</div>