@extends('layouts.app')
@section('title', 'Cutoff Plotting Schedule')

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

        <div class="content">

            @if (session('success'))
                <div class="alert alert-success border-0 shadow-sm">
                    <span class="fas fa-check-circle me-2"></span>{{ session('success') }}
                </div>
            @endif

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-light border-bottom">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg">
                            <h5 class="mb-1">
                                <span class="fas fa-calendar-alt text-primary me-2"></span>
                                Cutoff Plotting Schedule
                            </h5>
                            <p class="text-muted fs-10 mb-0">
                                Create and manage employee plotting based on payroll cutoff periods.
                            </p>
                        </div>

                        <div class="col-lg-auto">
                            <form method="GET" action="{{ route('payroll-plotting.index') }}"
                                class="row g-2 align-items-center" id="filterForm">
                                <div class="col-auto">
                                    <input type="text" name="search" list="employeeSuggestions"
                                        class="form-control form-control-sm" style="width: 280px;"
                                        placeholder="Search employee name / employee no / bio id..."
                                        value="{{ request('search') }}" required>

                                    <datalist id="employeeSuggestions">
                                        @foreach ($suggestions as $suggestion)
                                            <option value="{{ $suggestion->employee_name }}">
                                                {{ $suggestion->employee_name }}
                                                @if ($suggestion->employee_no)
                                                    - {{ $suggestion->employee_no }}
                                                @endif
                                                @if ($suggestion->biometric_employee_id)
                                                    - Bio ID: {{ $suggestion->biometric_employee_id }}
                                                @endif
                                            </option>

                                            @if ($suggestion->employee_no)
                                                <option value="{{ $suggestion->employee_no }}">
                                                    {{ $suggestion->employee_name }} - {{ $suggestion->employee_no }}
                                                </option>
                                            @endif

                                            @if ($suggestion->biometric_employee_id)
                                                <option value="{{ $suggestion->biometric_employee_id }}">
                                                    {{ $suggestion->employee_name }} - Bio ID:
                                                    {{ $suggestion->biometric_employee_id }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </datalist>
                                </div>

                                <div class="col-auto">
                                    <select name="cutoff_month" class="form-select form-select-sm">
                                        @for ($m = 1; $m <= 12; $m++)
                                            <option value="{{ $m }}"
                                                {{ (int) $cutoffMonth === $m ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <select name="cutoff_year" class="form-select form-select-sm">
                                        @for ($y = now()->year - 2; $y <= now()->year + 3; $y++)
                                            <option value="{{ $y }}"
                                                {{ (int) $cutoffYear === $y ? 'selected' : '' }}>
                                                {{ $y }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <select name="cutoff_type" class="form-select form-select-sm">
                                        <option value="11_25" {{ $cutoffType === '11_25' ? 'selected' : '' }}>
                                            11 - 25
                                        </option>
                                        <option value="26_10" {{ $cutoffType === '26_10' ? 'selected' : '' }}>
                                            26 - 10
                                        </option>
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <button class="btn btn-primary btn-sm">
                                        <span class="fas fa-filter me-1"></span> Load
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body bg-body-tertiary border-bottom">
                    <div class="row g-3">
                        <div class="col-lg-8">
                            <form method="POST" action="{{ route('payroll-plotting.quick-fill') }}"
                                class="row g-2 align-items-end">
                                @csrf
                                <input type="hidden" name="cutoff_month" value="{{ $cutoffMonth }}">
                                <input type="hidden" name="cutoff_year" value="{{ $cutoffYear }}">
                                <input type="hidden" name="cutoff_type" value="{{ $cutoffType }}">
                                <input type="hidden" name="search" value="{{ request('search') }}">
                                <input type="hidden" name="page" value="{{ $employees->currentPage() }}">

                                @foreach ($employees as $employee)
                                    @php
                                        $employeeKey = $employee->biometric_employee_id
                                            ? 'bio:' . $employee->biometric_employee_id
                                            : 'empno:' . $employee->employee_no;
                                    @endphp
                                    <input type="hidden" name="employee_keys[]" value="{{ $employeeKey }}">
                                @endforeach

                                <div class="col-md-3">
                                    <label class="form-label fs-10 mb-1">Shift Name</label>
                                    <input type="text" name="default_shift_name" class="form-control form-control-sm"
                                        value="Regular Shift">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fs-10 mb-1">Time In</label>
                                    <input type="time" name="default_time_in" class="form-control form-control-sm"
                                        value="08:00">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fs-10 mb-1">Time Out</label>
                                    <input type="time" name="default_time_out" class="form-control form-control-sm"
                                        value="17:00">
                                </div>

                                <div class="col-md-1">
                                    <label class="form-label fs-10 mb-1">Grace</label>
                                    <input type="number" name="default_grace_minutes" class="form-control form-control-sm"
                                        value="15" min="0">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fs-10 mb-1">Rest Day Rule</label>
                                    <select name="rest_day_mode" class="form-select form-select-sm">
                                        <option value="sunday">Sunday Only</option>
                                        <option value="sat_sun">Saturday & Sunday</option>
                                        <option value="none">No Rest Day</option>
                                    </select>
                                </div>

                                <div class="col-md-auto">
                                    <button type="submit" class="btn btn-warning btn-sm"
                                        {{ blank(request('search')) ? 'disabled' : '' }}>
                                        <span class="fas fa-magic me-1"></span> Generate Default Cutoff
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="col-lg-4">
                            <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                                <span class="badge bg-success-subtle text-success border">Scheduled</span>
                                <span class="badge bg-warning-subtle text-warning border">Rest Day</span>
                                <span class="badge bg-info-subtle text-info border">Leave</span>
                                <span class="badge bg-danger-subtle text-danger border">Holiday</span>
                            </div>
                            <div class="text-muted fs-10 mt-2 text-lg-end">
                                Current Coverage: <strong>{{ $cutoffLabel }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="plottingTableWrapper">
                    @include('payroll.plotting.table')
                </div>
            </div>
        </div>
    </div>

    <style>
        .plotting-table th,
        .plotting-table td {
            vertical-align: top;
        }

        .employee-col {
            min-width: 240px;
            width: 240px;
            z-index: 4;
        }

        .day-col {
            min-width: 155px;
            width: 155px;
        }

        .sticky-col {
            position: sticky;
            left: 0;
            z-index: 3;
            box-shadow: 2px 0 4px rgba(0, 0, 0, 0.04);
        }

        .sticky-head {
            top: 0;
            z-index: 5;
        }

        .plot-cell {
            padding: .4rem;
            transition: background-color .2s ease;
        }

        .plot-mini-card {
            min-width: 140px;
        }

        .plot-mini-card .form-control,
        .plot-mini-card .form-select {
            font-size: 11px;
            padding: .28rem .45rem;
        }

        .plot-scheduled {
            background-color: rgba(25, 135, 84, .08);
        }

        .plot-rest-day {
            background-color: rgba(255, 193, 7, .13);
        }

        .plot-leave {
            background-color: rgba(13, 202, 240, .10);
        }

        .plot-holiday {
            background-color: rgba(220, 53, 69, .08);
        }

        .bg-success-subtle {
            background-color: rgba(25, 135, 84, .10) !important;
        }

        .bg-warning-subtle {
            background-color: rgba(255, 193, 7, .15) !important;
        }

        .bg-info-subtle {
            background-color: rgba(13, 202, 240, .12) !important;
        }

        .bg-danger-subtle {
            background-color: rgba(220, 53, 69, .10) !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function bindPlottingEvents(scope = document) {
                const statusFields = scope.querySelectorAll('.plot-status');

                function applyCellColor(select) {
                    const td = select.closest('.plot-cell');
                    if (!td) return;

                    td.classList.remove('plot-scheduled', 'plot-rest-day', 'plot-leave', 'plot-holiday');

                    if (select.value === 'scheduled') td.classList.add('plot-scheduled');
                    if (select.value === 'rest_day') td.classList.add('plot-rest-day');
                    if (select.value === 'leave') td.classList.add('plot-leave');
                    if (select.value === 'holiday') td.classList.add('plot-holiday');
                }

                statusFields.forEach(function(select) {
                    applyCellColor(select);

                    select.addEventListener('change', function() {
                        applyCellColor(this);

                        const cell = this.closest('.plot-cell');
                        const timeInputs = cell.querySelectorAll('input[type="time"]');

                        if (this.value !== 'scheduled') {
                            timeInputs.forEach(function(input) {
                                input.value = '';
                            });
                        }
                    });
                });
            }

            bindPlottingEvents(document);

            document.addEventListener('click', function(e) {
                const link = e.target.closest('#plottingTableWrapper .pagination a');
                if (!link) return;

                e.preventDefault();

                fetch(link.href, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        document.getElementById('plottingTableWrapper').innerHTML = html;
                        bindPlottingEvents(document.getElementById('plottingTableWrapper'));
                    });
            });
        });
    </script>
@endsection
