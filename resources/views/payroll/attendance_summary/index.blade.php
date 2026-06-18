@extends('layouts.app')
@section('title', 'Attendance Summary')

@section('content')
    <div class="container-fluid" data-layout="container">
        <script>
            (function() {
                try {
                    var isFluid = JSON.parse(localStorage.getItem('isFluid'));

                    if (isFluid) {
                        var container = document.querySelector('[data-layout]');

                        if (container) {
                            container.classList.remove('container');
                            container.classList.add('container-fluid');
                        }
                    }
                } catch (e) {}
            })();
        </script>

        @php
            $totalRecords = (int) ($stats['total'] ?? 0);
            $presentCount = (int) ($stats['present'] ?? 0);
            $payableRecords = (int) ($stats['payable_records'] ?? 0);
            $needsReview = (int) ($stats['needs_review'] ?? 0);
            $halfDayCount = (int) ($stats['half_day'] ?? 0);
            $lateUndertimeRecords = (int) ($stats['late_undertime_records'] ?? 0);
            $lateCount = (int) ($stats['late'] ?? 0);
            $undertimeCount = (int) ($stats['undertime'] ?? 0);
            $absentCount = (int) ($stats['absent'] ?? 0);
            $incompleteCount = (int) ($stats['incomplete'] ?? 0);
            $noScheduleCount = (int) ($stats['no_schedule'] ?? 0);
            $holidayCount = (int) ($stats['holiday'] ?? 0);
            $holidayPaidCount = (int) ($stats['holiday_paid'] ?? 0);
            $holidayUnpaidCount = (int) ($stats['holiday_unpaid'] ?? 0);
            $holidayWorkedCount = (int) ($stats['holiday_worked'] ?? 0);
            $restDayCount = (int) ($stats['rest_day'] ?? 0);
            $restDayPaidCount = (int) ($stats['rest_day_paid'] ?? 0);
            $leaveCount = (int) ($stats['leave'] ?? 0);
            $adjustmentCount = (int) ($stats['adjustment'] ?? 0);
            $regularShiftCount = (int) ($stats['regular_shift'] ?? 0);
            $flexibleShiftCount = (int) ($stats['flexible_shift'] ?? 0);

            $totalLateMinutes = (float) ($stats['total_late_minutes'] ?? 0);
            $totalUndertimeMinutes = (float) ($stats['total_undertime_minutes'] ?? 0);
            $totalWorkedMinutes = (float) ($stats['total_worked_minutes'] ?? 0);
            $totalPayableDays = (float) ($stats['total_payable_days'] ?? 0);
            $totalPayableHours = (float) ($stats['total_payable_hours'] ?? 0);

            $reviewPercent = $totalRecords > 0 ? round(($needsReview / $totalRecords) * 100, 1) : 0;
        @endphp

        <style>
            .attendance-summary-page .hero-card {
                background: linear-gradient(135deg, rgba(44, 123, 229, .10), rgba(0, 210, 122, .08));
            }

            .attendance-summary-page .summary-card {
                transition: transform .18s ease, box-shadow .18s ease;
                border-left: 4px solid transparent !important;
            }

            .attendance-summary-page .summary-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .08) !important;
            }

            .attendance-summary-page .summary-card-primary {
                border-left-color: var(--falcon-primary, #2c7be5) !important;
            }

            .attendance-summary-page .summary-card-success {
                border-left-color: var(--falcon-success, #00d27a) !important;
            }

            .attendance-summary-page .summary-card-warning {
                border-left-color: var(--falcon-warning, #f5803e) !important;
            }

            .attendance-summary-page .summary-card-danger {
                border-left-color: var(--falcon-danger, #e63757) !important;
            }

            .attendance-summary-page .summary-card-info {
                border-left-color: var(--falcon-info, #27bcfd) !important;
            }

            .attendance-summary-page .summary-card-secondary {
                border-left-color: var(--falcon-secondary, #748194) !important;
            }

            .attendance-summary-page .summary-value {
                font-size: 1.55rem;
                font-weight: 800;
                line-height: 1;
            }

            .attendance-summary-page .summary-label {
                font-size: .72rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .04em;
            }

            .attendance-summary-page .rule-item {
                border: 1px solid var(--falcon-border-color, #d8e2ef);
                border-radius: .65rem;
                padding: .75rem;
                height: 100%;
                background: var(--falcon-body-bg, #fff);
            }

            .attendance-summary-page .rule-icon {
                width: 34px;
                height: 34px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
            }
        </style>

        <div class="content attendance-summary-page">
            @if (session('success'))
                <div class="alert alert-success border-200 bg-soft-success d-flex align-items-center gap-2">
                    <span class="fas fa-check-circle"></span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger border-200 bg-soft-danger d-flex align-items-center gap-2">
                    <span class="fas fa-exclamation-circle"></span>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <div class="card border-0 shadow-sm mb-3 overflow-hidden hero-card">
                <div class="card-body">
                    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-start gap-3">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="avatar avatar-xl">
                                    <span class="avatar-name rounded-circle bg-primary text-white">
                                        <span class="fas fa-file-invoice-dollar"></span>
                                    </span>
                                </span>

                                <div>
                                    <h3 class="mb-0 text-900">Payroll Attendance Summary</h3>
                                    <div class="text-600">
                                        Final checking page before payroll computation.
                                    </div>
                                </div>
                            </div>

                            <p class="text-700 mb-3">
                                This summary combines plotted schedule, biometrics, attendance adjustments,
                                holidays, rest days, leaves, late, undertime, and payable payroll units.
                                Review all warnings before exporting payroll.
                            </p>

                            <div class="d-flex flex-wrap gap-2">
                                <span
                                    class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
                                    <span class="fas fa-calendar-alt me-1"></span>
                                    {{ $cutoffLabel }}
                                </span>

                                <span class="badge rounded-pill bg-light text-dark border px-3 py-2">
                                    <span class="fas fa-database me-1"></span>
                                    {{ number_format($totalRecords) }} record(s)
                                </span>

                                <span
                                    class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                    <span class="fas fa-coins me-1"></span>
                                    {{ number_format($totalPayableDays, 2) }} payroll pay unit(s)
                                </span>

                                @if ($needsReview > 0)
                                    <span
                                        class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle px-3 py-2">
                                        <span class="fas fa-triangle-exclamation me-1"></span>
                                        {{ number_format($needsReview) }} need review
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-2 min-w-240">
                            <form method="POST" action="{{ route('attendance-summary.rebuild') }}">
                                @csrf

                                <input type="hidden" name="cutoff_month" value="{{ $cutoffMonth }}">
                                <input type="hidden" name="cutoff_year" value="{{ $cutoffYear }}">
                                <input type="hidden" name="cutoff_type" value="{{ $cutoffType }}">
                                <input type="hidden" name="search" value="{{ $search }}">
                                <input type="hidden" name="status" value="{{ $status }}">
                                <input type="hidden" name="day_type" value="{{ $dayType }}">

                                <button type="submit" class="btn btn-success w-100">
                                    <span class="fas fa-sync-alt me-1"></span>
                                    Rebuild Current Cutoff
                                </button>
                            </form>

                            <a href="{{ route('attendance-summary.export-payroll', request()->query()) }}" target="_blank"
                                class="btn btn-primary w-100">
                                <span class="fas fa-print me-1"></span>
                                Export Payroll Print
                            </a>

                            <small class="text-600 text-center">
                                Rebuild after changing schedule, biometrics, adjustments, holidays, or leaves.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            @if ($needsReview > 0)
                <div class="alert alert-warning border-200 bg-soft-warning">
                    <div class="d-flex align-items-start gap-2">
                        <span class="fas fa-exclamation-triangle mt-1"></span>
                        <div>
                            <div class="fw-bold">Payroll checking required before release.</div>
                            <div class="fs-10">
                                Found {{ number_format($needsReview) }} record(s) needing review:
                                {{ number_format($absentCount) }} absent,
                                {{ number_format($halfDayCount) }} half day,
                                {{ number_format($incompleteCount) }} incomplete log,
                                {{ number_format($noScheduleCount) }} no plotted schedule,
                                and {{ number_format($holidayUnpaidCount) }} unpaid holiday.
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-body-tertiary border-bottom border-200 py-3">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-2">
                        <div>
                            <h5 class="mb-1">
                                <span class="fas fa-shield-alt text-primary me-2"></span>
                                Payroll Rules Applied
                            </h5>
                            <p class="mb-0 text-600 fs-10">
                                These are the critical rules used by the summary builder.
                            </p>
                        </div>

                        <span class="badge badge-phoenix badge-phoenix-primary align-self-lg-center">
                            Schedule-first payroll validation
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6 col-xl-3">
                            <div class="rule-item">
                                <div class="d-flex gap-2">
                                    <span class="rule-icon bg-primary-subtle text-primary">
                                        <span class="fas fa-fingerprint"></span>
                                    </span>
                                    <div>
                                        <div class="fw-bold text-900">Biometrics</div>
                                        <div class="text-600 fs-10">
                                            Earliest log is time in and latest log is time out. Missing or invalid out
                                            becomes review/half day.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="rule-item">
                                <div class="d-flex gap-2">
                                    <span class="rule-icon bg-info-subtle text-info">
                                        <span class="fas fa-edit"></span>
                                    </span>
                                    <div>
                                        <div class="fw-bold text-900">Adjustments</div>
                                        <div class="text-600 fs-10">
                                            Approved/manual adjustment time, paid leave, offset, or paid adjustment can
                                            qualify attendance.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="rule-item">
                                <div class="d-flex gap-2">
                                    <span class="rule-icon bg-warning-subtle text-warning">
                                        <span class="fas fa-star"></span>
                                    </span>
                                    <div>
                                        <div class="fw-bold text-900">Holiday Gate</div>
                                        <div class="text-600 fs-10">
                                            Holiday without work is paid only when before and after dates are qualified by
                                            logs, leave, adjustment, or rest day.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="rule-item">
                                <div class="d-flex gap-2">
                                    <span class="rule-icon bg-success-subtle text-success">
                                        <span class="fas fa-bed"></span>
                                    </span>
                                    <div>
                                        <div class="fw-bold text-900">Rest Day</div>
                                        <div class="text-600 fs-10">
                                            Plotted rest day/day off is always 100% paid and still shown for payroll
                                            checking.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="rule-item">
                                <div class="d-flex gap-2">
                                    <span class="rule-icon bg-danger-subtle text-danger">
                                        <span class="fas fa-calendar-times"></span>
                                    </span>
                                    <div>
                                        <div class="fw-bold text-900">No Schedule</div>
                                        <div class="text-600 fs-10">
                                            Biometrics with no plotted schedule is not silently paid. It appears as No
                                            Schedule.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="rule-item">
                                <div class="d-flex gap-2">
                                    <span class="rule-icon bg-primary-subtle text-primary">
                                        <span class="fas fa-clock"></span>
                                    </span>
                                    <div>
                                        <div class="fw-bold text-900">Regular Shift</div>
                                        <div class="text-600 fs-10">
                                            Late and undertime are based on plotted in/out and grace minutes.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="rule-item">
                                <div class="d-flex gap-2">
                                    <span class="rule-icon bg-info-subtle text-info">
                                        <span class="fas fa-stopwatch"></span>
                                    </span>
                                    <div>
                                        <div class="fw-bold text-900">Flexible Shift</div>
                                        <div class="text-600 fs-10">
                                            Flexible shift requires 9 worked hours for full payable day.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-3">
                            <div class="rule-item">
                                <div class="d-flex gap-2">
                                    <span class="rule-icon bg-warning-subtle text-warning">
                                        <span class="fas fa-percent"></span>
                                    </span>
                                    <div>
                                        <div class="fw-bold text-900">Holiday Pay Units</div>
                                        <div class="text-600 fs-10">
                                            Regular holiday worked = 2.00 units. Special/non-regular worked = 1.30 units.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-body-tertiary border-bottom border-200">
                    <div>
                        <h5 class="mb-1">
                            <span class="fas fa-filter text-primary me-2"></span>
                            Filter Attendance Summary
                        </h5>
                        <small class="text-muted">
                            Choose cutoff, status, day type, or search employee details.
                        </small>
                    </div>
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('attendance-summary.index') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3 col-xl-2">
                                <label class="form-label fw-semibold">Month</label>
                                <select name="cutoff_month" class="form-select">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}"
                                            {{ (int) $cutoffMonth === $m ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-3 col-xl-2">
                                <label class="form-label fw-semibold">Year</label>
                                <select name="cutoff_year" class="form-select">
                                    @for ($y = now('Asia/Manila')->year + 1; $y >= now('Asia/Manila')->year - 3; $y--)
                                        <option value="{{ $y }}"
                                            {{ (int) $cutoffYear === $y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-3 col-xl-2">
                                <label class="form-label fw-semibold">Cutoff</label>
                                <select name="cutoff_type" class="form-select">
                                    <option value="first" {{ $cutoffType === 'first' ? 'selected' : '' }}>
                                        1st Cutoff (11-25)
                                    </option>
                                    <option value="second" {{ $cutoffType === 'second' ? 'selected' : '' }}>
                                        2nd Cutoff (26-10)
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-3 col-xl-2">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="status" class="form-select">
                                    @foreach ($statusOptions as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ (string) $status === (string) $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 col-xl-2">
                                <label class="form-label fw-semibold">Day / Audit Type</label>
                                <select name="day_type" class="form-select">
                                    @foreach ($dayTypeOptions as $value => $label)
                                        <option value="{{ $value }}"
                                            {{ (string) $dayType === (string) $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 col-xl-2">
                                <label class="form-label fw-semibold">Search</label>
                                <input type="text" class="form-control" name="search" value="{{ $search }}"
                                    placeholder="Name, emp no, bio id, status">
                            </div>

                            <div class="col-md-3 col-xl-2 d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <span class="fas fa-search me-1"></span>
                                    Apply Filter
                                </button>
                            </div>

                            <div class="col-md-3 col-xl-2 d-grid">
                                <a href="{{ route('attendance-summary.index') }}" class="btn btn-outline-secondary">
                                    <span class="fas fa-undo me-1"></span>
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100 summary-card summary-card-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="summary-label text-success">Payable Units</div>
                                    <div class="summary-value text-900">{{ number_format($totalPayableDays, 2) }}</div>
                                    <div class="text-600 fs-10">{{ number_format($totalPayableHours, 2) }} payable hour(s)
                                    </div>
                                </div>
                                <span class="fas fa-coins text-success fs-4"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div
                        class="card border-0 shadow-sm h-100 summary-card {{ $needsReview > 0 ? 'summary-card-danger' : 'summary-card-success' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="summary-label {{ $needsReview > 0 ? 'text-danger' : 'text-success' }}">
                                        Needs Review</div>
                                    <div class="summary-value text-900">{{ number_format($needsReview) }}</div>
                                    <div class="text-600 fs-10">{{ number_format($reviewPercent, 1) }}% of current records
                                    </div>
                                </div>
                                <span
                                    class="fas fa-clipboard-check {{ $needsReview > 0 ? 'text-danger' : 'text-success' }} fs-4"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100 summary-card summary-card-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="summary-label text-primary">Present / Adjusted</div>
                                    <div class="summary-value text-900">{{ number_format($presentCount) }}</div>
                                    <div class="text-600 fs-10">{{ number_format($adjustmentCount) }} with adjustment
                                    </div>
                                </div>
                                <span class="fas fa-user-check text-primary fs-4"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100 summary-card summary-card-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="summary-label text-warning">Late / Undertime</div>
                                    <div class="summary-value text-900">{{ number_format($lateUndertimeRecords) }}</div>
                                    <div class="text-600 fs-10">
                                        L: {{ number_format($totalLateMinutes, 0) }} min |
                                        UT: {{ number_format($totalUndertimeMinutes, 0) }} min
                                    </div>
                                </div>
                                <span class="fas fa-clock text-warning fs-4"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6 col-xl-3">
                    <div
                        class="card border-0 shadow-sm h-100 summary-card {{ $holidayUnpaidCount > 0 ? 'summary-card-danger' : 'summary-card-info' }}">
                        <div class="card-body">
                            <div class="summary-label {{ $holidayUnpaidCount > 0 ? 'text-danger' : 'text-info' }}">Holiday
                                Audit</div>
                            <div class="d-flex justify-content-between mt-2">
                                <div>
                                    <div class="fw-bold text-900">Paid: {{ number_format($holidayPaidCount) }}</div>
                                    <div class="text-danger fw-semibold">Unpaid: {{ number_format($holidayUnpaidCount) }}
                                    </div>
                                </div>
                                <div class="text-end text-600 fs-10">
                                    <div>Total: {{ number_format($holidayCount) }}</div>
                                    <div>Worked: {{ number_format($holidayWorkedCount) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100 summary-card summary-card-success">
                        <div class="card-body">
                            <div class="summary-label text-success">Rest Day Paid</div>
                            <div class="summary-value text-900">{{ number_format($restDayPaidCount) }}</div>
                            <div class="text-600 fs-10">{{ number_format($restDayCount) }} plotted rest day record(s)
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100 summary-card summary-card-primary">
                        <div class="card-body">
                            <div class="summary-label text-primary">Schedule Types</div>
                            <div class="d-flex justify-content-between mt-2">
                                <div>
                                    <div class="fw-bold text-900">Regular: {{ number_format($regularShiftCount) }}</div>
                                    <div class="fw-bold text-info">Flexible: {{ number_format($flexibleShiftCount) }}
                                    </div>
                                </div>
                                <span class="fas fa-calendar-check text-primary fs-4"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div
                        class="card border-0 shadow-sm h-100 summary-card {{ $noScheduleCount > 0 ? 'summary-card-danger' : 'summary-card-secondary' }}">
                        <div class="card-body">
                            <div class="summary-label {{ $noScheduleCount > 0 ? 'text-danger' : 'text-secondary' }}">
                                Missing / Special</div>
                            <div class="d-flex justify-content-between mt-2">
                                <div class="text-600 fs-10">
                                    <div>Absent: <strong>{{ number_format($absentCount) }}</strong></div>
                                    <div>Half Day: <strong>{{ number_format($halfDayCount) }}</strong></div>
                                    <div>Incomplete: <strong>{{ number_format($incompleteCount) }}</strong></div>
                                    <div>No Schedule: <strong>{{ number_format($noScheduleCount) }}</strong></div>
                                </div>
                                <span
                                    class="fas fa-triangle-exclamation {{ $noScheduleCount > 0 ? 'text-danger' : 'text-secondary' }} fs-4"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @include('payroll.attendance_summary.table')
        </div>
    </div>
@endsection
