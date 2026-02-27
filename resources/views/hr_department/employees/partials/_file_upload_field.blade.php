<div class="col-md-4">
    <label class="fw-bold">{{ $label }}</label>

    <div class="d-flex align-items-center gap-2 flex-wrap">

        {{-- View existing file --}}
        @if (!empty($value))
            <a href="{{ asset('storage/' . $value) }}"
               target="_blank"
               class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-eye me-1"></i> View
            </a>
        @else
            <span class="text-muted">No file</span>
        @endif

        {{-- File input --}}
        <input
            type="file"
            name="{{ $name }}"
            class="form-control form-control-sm file-input"
            data-target="#{{ $name }}Filename"
        >
    </div>

    {{-- File name preview --}}
    <div id="{{ $name }}Filename"
         class="avatar-file-label mt-1 text-muted small">
        @if (!empty($value))
            {{ basename($value) }}
        @endif
    </div>
</div>