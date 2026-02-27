<div class="col-md-6">
    <label class="fw-bold">{{ $label }}</label>
    <div class="d-flex justify-content-between">
        <span class="text-muted">{{ $value ?? '—' }}</span>
        <small class="text-muted">{{ $date?->format('M d, Y') ?? '' }}</small>
    </div>
</div>