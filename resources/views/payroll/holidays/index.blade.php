@extends('layouts.app')

@section('title', 'Philippine Holiday Calendar')

@section('content')
    <div class="container-fluid" data-layout="container">
        <div class="content">

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-body-tertiary border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">Philippine Holiday Calendar</h4>
                        <small class="text-muted">Connected to Payroll and Attendance Summary</small>
                    </div>

                    <a href="{{ route('holidays.create') }}" class="btn btn-primary btn-sm">
                        Add Holiday
                    </a>
                </div>

                <div class="card-body">
                    <form method="GET" class="row g-2 mb-4">
                        <div class="col-md-2">
                            <input type="number" name="year" class="form-control" value="{{ $year }}"
                                placeholder="Year">
                        </div>
                        <div class="col-md-2">
                            <select name="month" class="form-select">
                                <option value="">All Months</option>
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ (int) $month === $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" value="{{ $search }}"
                                placeholder="Search holiday...">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-outline-primary w-100">Filter</button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('holidays.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                        </div>
                    </form>

                    <div class="row g-3 mb-4">
                        @php
                            $start = \Carbon\Carbon::create(
                                $year,
                                $month ?: now()->month,
                                1,
                                0,
                                0,
                                0,
                                'Asia/Manila',
                            )->startOfMonth();
                            $end = $start->copy()->endOfMonth();
                            $daysInMonth = $start->daysInMonth;
                            $firstDayOfWeek = $start->dayOfWeek; // 0 sunday
                        @endphp

                        <div class="col-12">
                            <div class="border rounded-3 p-3 bg-light-subtle">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">{{ $start->format('F Y') }}</h5>
                                    <div class="small text-muted">
                                        <span class="badge bg-danger-subtle text-danger me-1">Regular</span>
                                        <span class="badge bg-warning-subtle text-warning">Special</span>
                                    </div>
                                </div>

                                <div class="row text-center fw-bold small mb-2">
                                    <div class="col">Sun</div>
                                    <div class="col">Mon</div>
                                    <div class="col">Tue</div>
                                    <div class="col">Wed</div>
                                    <div class="col">Thu</div>
                                    <div class="col">Fri</div>
                                    <div class="col">Sat</div>
                                </div>

                                @php
                                    $day = 1;
                                    $printed = 0;
                                @endphp

                                @for ($week = 0; $week < 6; $week++)
                                    <div class="row g-2 mb-2">
                                        @for ($dow = 0; $dow < 7; $dow++)
                                            @php
                                                $currentDate = null;
                                                $holidayForDay = collect();

                                                if (
                                                    ($week === 0 && $dow >= $firstDayOfWeek) ||
                                                    ($week > 0 && $day <= $daysInMonth)
                                                ) {
                                                    $currentDate = $start->copy()->day($day)->format('Y-m-d');
                                                    $holidayForDay = $calendar->get($currentDate, collect());
                                                    $day++;
                                                    $printed++;
                                                }
                                            @endphp

                                            <div class="col">
                                                <div class="border rounded-3 bg-white p-2" style="min-height: 120px;">
                                                    @if ($currentDate)
                                                        <div class="fw-semibold mb-2">
                                                            {{ \Carbon\Carbon::parse($currentDate)->format('d') }}
                                                        </div>

                                                        @forelse ($holidayForDay as $holiday)
                                                            <div class="mb-1">
                                                                <span
                                                                    class="{{ $holiday->type_badge_class }} d-inline-block mb-1">
                                                                    {{ ucfirst($holiday->holiday_type) }}
                                                                </span>
                                                                <div class="small fw-semibold">
                                                                    {{ $holiday->name }}
                                                                </div>
                                                                @if ($holiday->is_moved)
                                                                    <div class="small text-muted">
                                                                        Actual: {{ $holiday->actual_date->format('M d') }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @empty
                                                            <div class="small text-muted">—</div>
                                                        @endforelse
                                                    @endif
                                                </div>
                                            </div>
                                        @endfor
                                    </div>

                                    @if ($day > $daysInMonth)
                                        @break
                                    @endif
                                @endfor
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Holiday</th>
                                    <th>Type</th>
                                    <th>Actual Date</th>
                                    <th>Observed Date</th>
                                    <th>Not Worked</th>
                                    <th>Worked</th>
                                    <th>Source</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($holidays as $holiday)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $holiday->name }}</div>
                                            @if ($holiday->is_moved)
                                                <small class="text-muted">Moved holiday observance</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="{{ $holiday->type_badge_class }}">
                                                {{ ucfirst($holiday->holiday_type) }}
                                            </span>
                                        </td>
                                        <td>{{ $holiday->actual_date->format('M d, Y') }}</td>
                                        <td>{{ $holiday->observed_date->format('M d, Y') }}</td>
                                        <td>{{ number_format((float) $holiday->not_worked_multiplier, 2) }}x</td>
                                        <td>{{ number_format((float) $holiday->worked_multiplier, 2) }}x</td>
                                        <td>{{ $holiday->source_proclamation ?: '—' }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('holidays.edit', $holiday) }}"
                                                class="btn btn-outline-primary btn-sm">
                                                Edit
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">No holidays found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $holidays->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
