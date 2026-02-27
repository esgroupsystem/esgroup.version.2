<div class="modal fade" id="addHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('employees.staff.history.store', $employee->id) }}" method="POST" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">Add Employment History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Type of Movement</label>
                    <select name="title" class="form-select" required>
                        <option value="">-- Select Type --</option>
                        <option value="Lateral Transfer">Lateral Transfer</option>
                        <option value="Change Position">Change Position</option>
                        <option value="Promotion">Promotion</option>
                        <option value="Assignment">Assignment</option>
                        <option value="Training">Training</option>
                        <option value="Violations">Violations</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Describe what happened..."></textarea>
                </div>

                <div class="row g-2">
                    <div class="col">
                        <label class="form-label fw-semibold">Start Date</label>
                        <input type="date" name="start_date" class="form-control">
                    </div>

                    <div class="col">
                        <label class="form-label fw-semibold">End Date</label>
                        <input type="date" name="end_date" class="form-control">
                        <small class="text-muted">Leave blank if current</small>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save History
                </button>
            </div>
        </form>
    </div>
</div>
