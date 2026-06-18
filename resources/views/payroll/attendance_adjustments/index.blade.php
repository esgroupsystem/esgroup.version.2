@extends('layouts.app')
@section('title', 'Payroll Attendance Adjustments')

@section('content')
    <div class="container-fluid" data-layout="container">
        <div class="content">

            @if (session('success'))
                <div class="alert alert-success border-0 shadow-sm">
                    <span class="fas fa-check-circle me-1"></span>
                    {{ session('success') }}
                </div>
            @endif

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                        <div>
                            <h4 class="mb-1 text-900">
                                <span class="fas fa-user-clock text-primary me-2"></span>
                                Payroll Attendance Adjustments
                            </h4>
                            <p class="mb-0 text-600">
                                Manage Sick Leave, Medical Leave, Change Schedule, Offset, and Official Business
                                adjustments.
                            </p>
                        </div>

                        <a href="{{ route('payroll-attendance-adjustments.create') }}" class="btn btn-primary">
                            <span class="fas fa-plus me-1"></span>
                            New Adjustment
                        </a>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-xl me-3">
                                    <div class="avatar-name rounded-circle bg-primary-subtle text-primary">
                                        <span class="fas fa-list"></span>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
                                    <small class="text-600">Total Adjustments</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-xl me-3">
                                    <div class="avatar-name rounded-circle bg-success-subtle text-success">
                                        <span class="fas fa-notes-medical"></span>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="mb-0">{{ number_format($stats['leaves']) }}</h4>
                                    <small class="text-600">Leave Adjustments</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-xl me-3">
                                    <div class="avatar-name rounded-circle bg-warning-subtle text-warning">
                                        <span class="fas fa-exchange-alt"></span>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="mb-0">{{ number_format($stats['offsets']) }}</h4>
                                    <small class="text-600">Offset Requests</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-xl me-3">
                                    <div class="avatar-name rounded-circle bg-info-subtle text-info">
                                        <span class="fas fa-clock"></span>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="mb-0">{{ number_format($stats['manual_time']) }}</h4>
                                    <small class="text-600">Manual Time Entries</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body-tertiary border-bottom">
                    <form method="GET" action="{{ route('payroll-attendance-adjustments.index') }}"
                        class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fs-10 fw-semibold text-700">Search</label>
                            <input type="text" name="search" class="form-control"
                                placeholder="Employee name, employee no, type, reason..." value="{{ $search }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fs-10 fw-semibold text-700">Adjustment Type</label>
                            <select name="type" class="form-select">
                                <option value="">All Types</option>
                                @foreach ($types as $value => $label)
                                    <option value="{{ $value }}" @selected($type === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fs-10 fw-semibold text-700">From</label>
                            <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fs-10 fw-semibold text-700">To</label>
                            <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                        </div>

                        <div class="col-md-1 d-grid">
                            <button class="btn btn-secondary">
                                <span class="fas fa-search"></span>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive scrollbar">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-body-tertiary">
                                <tr>
                                    <th>Employee</th>
                                    <th>Type</th>
                                    <th>Period / Date</th>
                                    <th>Adjusted Time</th>
                                    <th>Offset Proof</th>
                                    <th>Payroll Effect</th>
                                    <th>Encoded</th>
                                    <th class="text-end" width="160">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($adjustments as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-900">{{ $item->employee_name }}</div>
                                            <div class="fs-10 text-600">
                                                Emp No: {{ $item->employee_no ?: 'N/A' }}
                                                |
                                                Bio ID: {{ $item->biometric_employee_id ?: 'N/A' }}
                                            </div>
                                        </td>

                                        <td>
                                            @php
                                                $badgeClass = match ($item->adjustment_type) {
                                                    'sick_leave', 'medical_leave' => 'success',
                                                    'change_schedule' => 'info',
                                                    'offset' => 'warning',
                                                    'official_business' => 'primary',
                                                    default => 'secondary',
                                                };
                                            @endphp

                                            <span class="badge bg-{{ $badgeClass }}-subtle text-{{ $badgeClass }}">
                                                {{ $item->type_label }}
                                            </span>
                                        </td>

                                        <td>
                                            <div class="fw-semibold">{{ $item->period_label }}</div>
                                            <small class="text-600">
                                                {{ $item->adjusted_day_type ?: '—' }}
                                            </small>
                                        </td>

                                        <td>
                                            <span class="text-800">{{ $item->adjusted_time_label }}</span>
                                        </td>

                                        <td>
                                            @if ($item->adjustment_type === 'offset')
                                                <div class="fw-semibold text-900">
                                                    {{ $item->offset_proof_label }}
                                                </div>
                                                <small class="text-success">
                                                    <span class="fas fa-check-circle me-1"></span>
                                                    Biometrics verified
                                                </small>
                                            @else
                                                <span class="text-500">—</span>
                                            @endif
                                        </td>

                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @if ($item->is_paid)
                                                    <span class="badge bg-success">Paid</span>
                                                @else
                                                    <span class="badge bg-secondary">Unpaid</span>
                                                @endif

                                                @if ($item->ignore_late)
                                                    <span class="badge bg-info">Ignore Late</span>
                                                @endif

                                                @if ($item->ignore_undertime)
                                                    <span class="badge bg-warning text-dark">Ignore UT</span>
                                                @endif
                                            </div>
                                        </td>

                                        <td>
                                            <div class="text-900">{{ $item->encoder->name ?? 'N/A' }}</div>
                                            <small class="text-600">
                                                {{ $item->encoded_at?->format('M d, Y h:i A') ?? '—' }}
                                            </small>
                                        </td>

                                        <td class="text-end">
                                            <div class="btn-group">
                                                <a href="{{ route('payroll-attendance-adjustments.edit', $item) }}"
                                                    class="btn btn-falcon-warning btn-sm">
                                                    <span class="fas fa-edit"></span>
                                                </a>

                                                <form method="POST"
                                                    action="{{ route('payroll-attendance-adjustments.destroy', $item) }}"
                                                    onsubmit="return confirm('Delete this payroll adjustment?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-falcon-danger btn-sm">
                                                        <span class="fas fa-trash"></span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="text-500">
                                                <span class="fas fa-folder-open fa-2x mb-2"></span>
                                                <div>No payroll attendance adjustments found.</div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($adjustments->hasPages())
                    <div class="card-footer bg-body-tertiary">
                        {{ $adjustments->links('pagination.custom') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
