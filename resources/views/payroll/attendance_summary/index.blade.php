@extends('layouts.app')
@section('title', 'Attendance Summary')

@section('content')
    <div class="container-fluid" data-layout="container">
        <script>
            var isFluid = JSON.parse(localStorage.getItem('isFluid'));
            if (isFluid) {
                var container = document.querySelector('[data-layout]');
                container.classList.remove('container-fluid');
                container.classList.add('container-fluid');
            }
        </script>

        @php
            $presentCount = $summaries->whereIn('attendance_status', ['present', 'adjusted_present'])->count();
            $lateCount = $summaries->whereIn('attendance_status', ['late', 'late_undertime'])->count();
            $undertimeCount = $summaries->whereIn('attendance_status', ['undertime', 'late_undertime'])->count();
            $absentCount = $summaries->where('attendance_status', 'absent')->count();
            $incompleteCount = $summaries->where('attendance_status', 'incomplete_log')->count();
            $holidayCount = $summaries->whereIn('attendance_status', ['holiday', 'holiday_worked'])->count();
            $restDayCount = $summaries->whereIn('attendance_status', ['rest_day', 'rest_day_worked'])->count();
            $adjustmentCount = $summaries->where('has_adjustment', true)->count();
        @endphp

        <div class="content">
            @if (session('success'))
                <div class="alert alert-success border-200 bg-soft-success d-flex align-items-center gap-2">
                    <span class="fas fa-check-circle"></span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- Page Header --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex flex-column flex-xl-row gap-3 justify-content-between align-items-xl-center">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="fas fa-clipboard-list text-primary"></span>
                                <h4 class="mb-0">Attendance Summary</h4>
                            </div>
                            <p class="text-muted mb-2">
                                This page combines plotted schedule, biometrics logs, attendance adjustment,
                                and holidays into one daily attendance result used by payroll.
                            </p>

                            <div class="d-flex flex-wrap gap-2">
                                <span
                                    class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
                                    <span class="fas fa-calendar-alt me-1"></span>{{ $cutoffLabel }}
                                </span>

                                <span class="badge rounded-pill bg-light text-dark border px-3 py-2">
                                    <span class="fas fa-database me-1"></span>{{ $summaries->total() }} record(s)
                                </span>
                            </div>
                        </div>

                        <div class="text-xl-end">
                            <form method="POST" action="{{ route('attendance-summary.rebuild') }}">
                                @csrf
                                <input type="hidden" name="cutoff_month" value="{{ $cutoffMonth }}">
                                <input type="hidden" name="cutoff_year" value="{{ $cutoffYear }}">
                                <input type="hidden" name="cutoff_type" value="{{ $cutoffType }}">

                                <button type="submit" class="btn btn-success">
                                    <span class="fas fa-sync-alt me-1"></span>Rebuild Current Cutoff Summary
                                </button>
                            </form>
                            <small class="text-muted d-block mt-2">
                                Rebuild this cutoff if plotting, logs, adjustments, or holidays were updated.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter Card --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-body-tertiary border-bottom border-200">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-0">Filter Attendance Summary</h6>
                            <small class="text-muted">Choose cutoff and search employee attendance records.</small>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('attendance-summary.index') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3 col-lg-2">
                                <label class="form-label fw-semibold">Month</label>
                                <select name="cutoff_month" class="form-select">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $cutoffMonth == $m ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-3 col-lg-2">
                                <label class="form-label fw-semibold">Year</label>
                                <select name="cutoff_year" class="form-select">
                                    @for ($y = now('Asia/Manila')->year + 1; $y >= now('Asia/Manila')->year - 3; $y--)
                                        <option value="{{ $y }}" {{ $cutoffYear == $y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div class="col-md-3 col-lg-2">
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

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label fw-semibold">Search</label>
                                <input type="text" class="form-control" name="search" value="{{ $search }}"
                                    placeholder="Search employee name, employee no, biometric id, or status">
                            </div>

                            <div class="col-md-6 col-lg-2 d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <span class="fas fa-search me-1"></span>Apply Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Summary Cards --}}
            <div class="row g-3 mb-3">
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-success fs-9 fw-bold text-uppercase">Present</span>
                                <span class="fas fa-user-check text-success"></span>
                            </div>
                            <h3 class="mb-1">{{ $presentCount }}</h3>
                            <p class="text-muted mb-0 fs-10">Present and adjusted present records</p>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-warning fs-9 fw-bold text-uppercase">Late / Undertime</span>
                                <span class="fas fa-clock text-warning"></span>
                            </div>
                            <h3 class="mb-1">{{ $lateCount + $undertimeCount }}</h3>
                            <p class="text-muted mb-0 fs-10">Attendance with time deduction indicators</p>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-danger fs-9 fw-bold text-uppercase">Absent / Incomplete</span>
                                <span class="fas fa-exclamation-triangle text-danger"></span>
                            </div>
                            <h3 class="mb-1">{{ $absentCount + $incompleteCount }}</h3>
                            <p class="text-muted mb-0 fs-10">Needs checking or adjustment</p>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-info fs-9 fw-bold text-uppercase">Special Days</span>
                                <span class="fas fa-calendar-day text-info"></span>
                            </div>
                            <h3 class="mb-1">{{ $holidayCount + $restDayCount }}</h3>
                            <p class="text-muted mb-0 fs-10">Holiday and rest day related records</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Extra Summary Cards --}}
            <div class="row g-3 mb-3">
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-primary fs-9 fw-bold text-uppercase mb-2">With Adjustment</div>
                            <h4 class="mb-1">{{ $adjustmentCount }}</h4>
                            <small class="text-muted">Records affected by attendance adjustment</small>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-warning fs-9 fw-bold text-uppercase mb-2">Late Count</div>
                            <h4 class="mb-1">{{ $lateCount }}</h4>
                            <small class="text-muted">Records with late attendance result</small>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-warning fs-9 fw-bold text-uppercase mb-2">Undertime Count</div>
                            <h4 class="mb-1">{{ $undertimeCount }}</h4>
                            <small class="text-muted">Records with undertime result</small>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-danger fs-9 fw-bold text-uppercase mb-2">Absent Count</div>
                            <h4 class="mb-1">{{ $absentCount }}</h4>
                            <small class="text-muted">Records with no valid attendance</small>
                        </div>
                    </div>
                </div>
            </div>

            @include('payroll.attendance_summary.table')
        </div>
    </div>
@endsection
