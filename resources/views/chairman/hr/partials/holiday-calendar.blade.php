<div class="card border-0 shadow-sm h-100">
    <div class="card-header bg-white border-0">
        <h5 class="fw-bold mb-1">
            Holiday Calendar
        </h5>
        <p class="text-muted small mb-0">
            Active holidays for {{ $year }}
        </p>
    </div>

    <div class="card-body">
        @forelse ($holidays as $month => $monthHolidays)
            <div class="holiday-month mb-3">
                <h6 class="fw-bold mb-2">
                    {{ $month }}
                </h6>

                <div class="d-grid gap-2">
                    @foreach ($monthHolidays as $holiday)
                        <div class="holiday-item">
                            <div class="d-flex justify-content-between gap-2">
                                <div>
                                    <div class="fw-semibold">
                                        {{ $holiday->name }}
                                    </div>
                                    <div class="text-muted small">
                                        {{ $holiday->observed_date->format('M d, Y') }}

                                        @if ($holiday->is_moved)
                                            <span class="ms-1">
                                                moved from {{ $holiday->actual_date?->format('M d, Y') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <span class="{{ $holiday->type_badge_class }}">
                                    {{ ucfirst($holiday->holiday_type) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-4">
                No active holidays found for {{ $year }}.
            </div>
        @endforelse
    </div>
</div>
