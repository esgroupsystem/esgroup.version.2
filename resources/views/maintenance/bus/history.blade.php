<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h4 class="mb-1">{{ $bus->plate_number ?? 'N/A' }}</h4>
                <div class="text-muted">
                    Body No: {{ $bus->body_number ?? 'N/A' }} |
                    Name: {{ $bus->name ?? 'N/A' }} |
                    Garage: {{ $bus->garage ?? 'N/A' }}
                </div>
            </div>

            <a href="{{ route('buses.index') }}" class="btn btn-falcon-default btn-sm">
                <span class="fas fa-arrow-left me-1"></span> Back
            </a>
        </div>
    </div>
</div>
