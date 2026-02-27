<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('employees.staff.store') }}" method="POST" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">Add Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Department</label>
                    <select name="department_id" id="departmentSelect" class="form-select" required>
                        <option value="">-- Select Department --</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Position</label>
                    <select name="position_id" id="positionSelect" class="form-select" required>
                        <option value="">-- Select Position --</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone_number" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Company</label>
                    <select name="company" class="form-select" required>
                        <option value="">-- Select Company --</option>
                        <option>Jell Transport</option>
                        <option>ES Transport</option>
                        <option>Kellen Transport</option>
                        <option>Earthstar Transport</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Garage</label>
                    <select name="garage" class="form-select" required>
                        <option value="">-- Select Garage --</option>
                        <option value="Mirasol">Mirasol</option>
                        <option value="Balintawak">Balintawak</option>
                    </select>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Save</button>
            </div>

        </form>
    </div>
</div>
