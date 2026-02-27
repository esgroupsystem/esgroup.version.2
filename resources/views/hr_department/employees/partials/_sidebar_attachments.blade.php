<div class="card mb-3 shadow-sm">
    <div class="card-header bg-body-tertiary d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">
            <i class="fas fa-paperclip mono-icon me-2"></i> Attachments
        </h6>
        <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#uploadAttachmentModal">
            <i class="fas fa-upload me-1"></i> Upload
        </button>
    </div>

    <div class="card-body">
        @forelse($employee->attachments as $att)
            <div class="d-flex mb-3 attachment-row">
                <div class="flex-grow-1 me-2 text-truncate">
                    <i class="fas fa-file mono-icon me-2"></i>

                    <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank"
                        class="text-truncate d-inline-block" style="max-width: 180px;">
                        {{ $att->file_name }}
                    </a>

                    <div class="small text-muted">
                        {{ strtoupper($att->mime_type) }} • {{ round($att->size / 1024, 1) }} KB
                    </div>
                </div>

                <div class="flex-shrink-0">
                    <form action="{{ route('employees.staff.attachments.destroy', [$employee->id, $att->id]) }}"
                        method="POST" class="confirm-delete">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-muted">No attachments.</p>
        @endforelse
    </div>
</div>
