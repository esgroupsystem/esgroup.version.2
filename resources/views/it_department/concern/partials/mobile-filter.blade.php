<div class="offcanvas offcanvas-end" tabindex="-1" id="filterCanvas" aria-labelledby="filterCanvasLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="filterCanvasLabel">Filter</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <form method="GET" action="{{ route('concern.cctv.index') }}">
            <div class="mb-3">
                <label class="form-label mb-1">Status</label>
                <select class="form-select" name="status">
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
