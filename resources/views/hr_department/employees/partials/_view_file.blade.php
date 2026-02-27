<div class="col-md-4">
    <label class="fw-bold">{{ $label }}</label>

    @if ($path)
        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ asset('storage/' . $path) }}" target="_blank" class="btn btn-sm btn-light border">
                <i class="fas fa-eye me-1"></i> View
            </a>
            <small class="text-muted">{{ $date?->format('M d, Y') ?? '' }}</small>
        </div>
    @else
        <span class="text-muted">—</span>
    @endif
</div>