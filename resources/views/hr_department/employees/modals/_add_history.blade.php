<div class="modal fade" id="addHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('employees.staff.history.store', $employee->id) }}" method="POST" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">Add Employment History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Type of Movement</label>
                    <select name="title" id="historyTitle" class="form-select" required>
                        <option value="">-- Select Type --</option>
                        <option value="Lateral Transfer">Lateral Transfer</option>
                        <option value="Change Position">Change Position</option>
                        <option value="Promotion">Promotion</option>
                        <option value="Assignment">Assignment</option>
                        <option value="Training">Training</option>
                        <option value="Violations">Violations</option>
                    </select>
                </div>

                {{-- ✅ Only show when Violations --}}
                <div id="violationFields" class="d-none">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Offense (Section)</label>
                        <select name="offense_id" id="offenseSelect" class="form-select">
                            <option value="">-- Select Offense Section --</option>
                            @foreach ($offenses ?? [] as $o)
                                <option value="{{ $o->id }}" data-description="{{ e($o->offense_description) }}">
                                    {{ $o->section }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Selecting a section will auto-fill the description.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Action (Optional)</label>

                        <div class="form-check">
                            <input class="form-check-input action-checkbox" type="checkbox" name="disciplinary_action[]"
                                value="Salary Deduction Authorization" id="actionSDA">
                            <label class="form-check-label" for="actionSDA">
                                Salary Deduction Authorization (SDA)
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input action-checkbox" type="checkbox" name="disciplinary_action[]"
                                value="Suspension" id="actionSuspension">
                            <label class="form-check-label" for="actionSuspension">
                                Suspension
                            </label>
                        </div>

                        <small class="text-muted">You may select multiple actions.</small>
                    </div>

                    {{-- ✅ SDA Fields Wrapper --}}
                    <div id="sdaFieldsWrapper" class="d-none">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">SDA Total Amount</label>
                            <input type="number" step="0.01" min="0" name="sda_amount" class="form-control"
                                placeholder="Enter total deduction amount">
                        </div>

                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Deduction Terms</label>
                                <input type="number" min="1" step="1" name="sda_terms"
                                    class="form-control" placeholder="e.g. 3">
                                <small class="text-muted">Number of installments/terms</small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Deduction Start Date</label>
                                <input type="date" name="sda_start_date" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    Deduction End Date <span class="text-muted">(Optional)</span>
                                </label>
                                <input type="date" name="sda_end_date" class="form-control">
                                <small class="text-muted">Leave blank if ongoing</small>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ✅ Description (will become readonly when Violations + section selected) --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" id="historyDescription" class="form-control" rows="3"
                        placeholder="Describe what happened..."></textarea>
                    <small class="text-muted" id="descLockHint" style="display:none;">
                        Description is locked when an Offense section is selected.
                    </small>
                </div>

                {{-- ✅ General Start/End Date (disabled when Suspension checked) --}}
                <div class="row g-2" id="generalDatesWrapper">
                    <div class="col">
                        <label class="form-label fw-semibold">Start Date</label>
                        <input type="date" name="start_date" class="form-control" id="generalStartDate">
                    </div>

                    <div class="col">
                        <label class="form-label fw-semibold">End Date</label>
                        <input type="date" name="end_date" class="form-control" id="generalEndDate">
                        <small class="text-muted">Leave blank if current</small>
                    </div>
                </div>

                {{-- ✅ Suspension Dates (show only when Suspension checked) --}}
                <div class="row g-2 d-none mt-2" id="suspensionDatesWrapper">
                    <div class="col">
                        <label class="form-label fw-semibold">Suspension Start Date</label>
                        <input type="date" name="suspension_start_date" class="form-control"
                            id="suspensionStartDate">
                    </div>

                    <div class="col">
                        <label class="form-label fw-semibold">Suspension End Date</label>
                        <input type="date" name="suspension_end_date" class="form-control"
                            id="suspensionEndDate">
                        <small class="text-muted">Leave blank if still suspended</small>
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
