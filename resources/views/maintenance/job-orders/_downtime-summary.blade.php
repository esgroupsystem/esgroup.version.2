@php
    $downtimeBreakdown = $jobOrder->downtime_breakdown;
@endphp

<div class="row g-3">
    @foreach (\App\Enums\JobOrderStatus::downtimeStatuses() as $downtimeStatus)
        @php
            $downtimeData = $downtimeBreakdown[$downtimeStatus->value];
        @endphp

        <div class="col-md-4">
            <div class="border rounded-3 p-3 bg-light h-100">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <span class="badge rounded-pill {{ $downtimeStatus->badgeClass() }}">
                        <span class="{{ $downtimeStatus->icon() }} me-1"></span>
                        {{ $downtimeStatus->label() }}
                    </span>

                    @if ($jobOrder->status === $downtimeStatus)
                        <span class="badge badge-subtle-primary text-primary">Counting</span>
                    @endif
                </div>

                <div class="fs-11 text-600 text-uppercase fw-semibold mb-1">Accumulated Time</div>
                <h5 class="fw-bold text-800 mb-0">{{ $downtimeData['label'] }}</h5>
            </div>
        </div>
    @endforeach
</div>

<div class="alert {{ $jobOrder->is_downtime_running ? 'alert-subtle-warning' : 'alert-subtle-success' }} mt-3 mb-0">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <div class="fw-bold">
                <span class="fas {{ $jobOrder->is_downtime_running ? 'fa-stopwatch' : 'fa-circle-check' }} me-1"></span>
                Total Downtime: {{ $jobOrder->total_downtime_label }}
            </div>

            <div class="fs-11 mt-1">
                @if ($jobOrder->is_downtime_running)
                    The counter is active because the current status is {{ $jobOrder->status_label }}.
                @else
                    The counter stopped when the job order became Operational.
                @endif
            </div>
        </div>

        <span class="badge rounded-pill {{ $jobOrder->is_downtime_running ? 'badge-subtle-warning text-warning' : 'badge-subtle-success text-success' }}">
            {{ $jobOrder->is_downtime_running ? 'Counter Running' : 'Counter Stopped' }}
        </span>
    </div>
</div>
