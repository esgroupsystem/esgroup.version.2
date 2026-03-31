<div class="card filter-card border-0 shadow-sm">
    <div class="card-header bg-body-tertiary border-bottom border-200 py-3 px-4">
        <h6 class="mb-0">Filter Panel</h6>
    </div>
    <div class="card-body p-4">
        <form method="GET" action="{{ route('concern.cctv.index') }}">
            <div class="mb-3">
                <label class="form-label mb-1">Status</label>
                <select class="form-select form-select-sm" name="status">
                    <option value="">All</option>
                    @foreach (['Open', 'In Progress', 'Fixed', 'Closed'] as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <input type="hidden" name="q" value="{{ request('q') }}">

            <button class="btn btn-primary w-100" type="submit">
                <span class="fas fa-check me-1"></span> Apply Filter
            </button>
        </form>
    </div>
</div>
