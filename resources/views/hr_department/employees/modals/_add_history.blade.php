<div class="modal fade" id="addHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('employees.staff.history.store', $employee->id) }}" method="POST" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">Add Violation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="title" value="Violations">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">IR Number</label>
                    <input type="text" name="ir_number" class="form-control" placeholder="IR-2026-0001" required>
                </div>
                {{-- ✅ Only show when Violations --}}
                <div id="violationFields">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Offense (Section)</label>
                        <div id="violationsContainer">

                            <div class="violation-row border rounded p-3 mb-3">

                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Violation #1</h6>

                                    <button type="button" class="btn btn-sm btn-danger removeViolation d-none">
                                        Remove
                                    </button>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Offense Section</label>

                                    <select name="offense_id[]" class="form-select offenseSelect">
                                        <option value="">-- Select Offense Section --</option>

                                        @foreach ($offenses ?? [] as $o)
                                            <option value="{{ $o->id }}"
                                                data-description="{{ e($o->offense_description) }}">
                                                {{ $o->section }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Description</label>

                                    <textarea name="description[]" class="form-control offenseDescription" rows="3"></textarea>
                                </div>

                            </div>

                        </div>

                        <button type="button" id="addViolationBtn" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-plus"></i> Add Another Violation
                        </button>
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

                <div class="row g-2 d-none mt-2" id="suspensionDatesWrapper">
                    <div class="col">
                        <label class="form-label fw-semibold">Suspension Start Date</label>
                        <input type="date" name="suspension_start_date" class="form-control"
                            id="suspensionStartDate" required>
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
                    <i class="fas fa-save me-1"></i> Save Violation
                </button>
            </div>
        </form>
    </div>
</div>
