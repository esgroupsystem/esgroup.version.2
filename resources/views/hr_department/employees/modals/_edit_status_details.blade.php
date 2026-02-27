<div class="modal fade" id="editStatusDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('employees.status-details.update', $employee->id) }}"
              method="POST" class="modal-content">
            @csrf
            @method('PUT')

            <div class="modal-header">
                <h5 class="modal-title">Edit Employee Status Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Date Resigned</label>
                        <input type="date" name="date_resigned" class="form-control"
                            value="{{ optional($employee->date_resigned)->format('Y-m-d') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Last Duty</label>
                        <input type="date" name="last_duty" class="form-control"
                            value="{{ optional($employee->last_duty)->format('Y-m-d') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Clearance Date</label>
                        <input type="date" name="clearance_date" class="form-control"
                            value="{{ optional($employee->clearance_date)->format('Y-m-d') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Last Pay Status</label>
                        <select name="last_pay_status" class="form-control">
                            <option value="">— Select —</option>
                            <option value="Not released"
                                @selected($employee->last_pay_status === 'Not released')>
                                Not released
                            </option>
                            <option value="Released"
                                @selected($employee->last_pay_status === 'Released')>
                                Released
                            </option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Last Pay Date</label>
                        <input type="date" name="last_pay_date" class="form-control"
                            value="{{ optional($employee->last_pay_date)->format('Y-m-d') }}">
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>