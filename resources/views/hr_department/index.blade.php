@extends('layouts.app')
@section('title', 'Employees Management')

@section('content')
    <div class="container" data-layout="container">
        <script>
            var isFluid = JSON.parse(localStorage.getItem('isFluid'));
            if (isFluid) {
                var container = document.querySelector('[data-layout]');
                container.classList.remove('container');
                container.classList.add('container-fluid');
            }
        </script>

        <div class="content">

            {{-- 🧩 TOP CARD --}}
            <div class="card mb-4">
                <div class="bg-holder d-none d-lg-block bg-card"
                    style="background-image:url(/assets/img/icons/spot-illustrations/corner-4.png);">
                </div>

                <div class="card-body position-relative">
                    <div class="row">
                        <div class="col-lg-8">
                            <h3 class="mb-2">Employees Directory</h3>
                            <p class="text-muted">
                                Manage employee records, search by name or department, and maintain up-to-date staff info.
                            </p>
                        </div>
                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                                <i class="fas fa-plus me-1"></i> Add Employee
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 🧭 TABLE CARD --}}
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Employee List</h6>
                </div>

                {{-- SEARCH ONLY --}}
                <div class="p-3">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <input class="form-control form-control-sm search" placeholder="Search employee...">
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div id="employeeTable"
                        data-list='{"valueNames":["employee_id","name","department","position","email","phone","company"],"page":10,"pagination":true}'>

                        <div class="table-responsive scrollbar">
                            <table class="table table-hover table-striped fs-10 mb-0">
                                <thead class="bg-200 text-900">
                                    <tr>
                                        <th class="sort" data-sort="employee_id">Employee ID</th>
                                        <th class="sort" data-sort="name">Full Name</th>
                                        <th class="sort" data-sort="department">Department</th>
                                        <th class="sort" data-sort="position">Position</th>
                                        <th class="sort" data-sort="company">Company</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="list">
                                    @forelse ($employees as $employee)
                                        <tr>
                                            <td class="employee_id">{{ $employee->employee_id }}</td>
                                            <td class="name">{{ $employee->full_name }}</td>
                                            <td class="department">{{ $employee->department?->name ?? '-' }}</td>
                                            <td class="position">{{ $employee->position?->title ?? '-' }}</td>
                                            <td class="company">{{ $employee->company ?? '-' }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('employees.staff.show', $employee->id) }}"
                                                    class="btn btn-sm btn-info me-1">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                <form action="{{ route('employees.staff.destroy', $employee->id) }}"
                                                    method="POST" class="d-inline confirm-delete">

                                                    @csrf
                                                    @method('DELETE')

                                                    <button class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </td>

                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-3 text-muted">No employees found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- PAGINATION --}}
                        <div class="d-flex justify-content-center my-3">
                            <button class="btn btn-sm btn-falcon-default me-1" data-list-pagination="prev">
                                <span class="fas fa-chevron-left"></span>
                            </button>

                            <ul class="pagination mb-0"></ul>

                            <button class="btn btn-sm btn-falcon-default ms-1" data-list-pagination="next">
                                <span class="fas fa-chevron-right"></span>
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 🧾 Add Employee Modal --}}
    <div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('employees.staff.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addEmployeeModalLabel">Add Employee</h5>
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
                            <option value="MIRASOL">MIRASOL</option>
                            <option value="BALINTAWAK">BALINTAWAK</option>
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
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const employeeTable = new List("employeeTable", {
                valueNames: ["employee_id", "name", "department", "position", "company"],
                page: 10,
                pagination: true
            });

            const searchInput = document.querySelector(".search");
            searchInput.addEventListener("keyup", () => {
                employeeTable.search(searchInput.value);
            });

            // Dynamic positions per department
            const departments = @json($departments);

            const departmentSelect = document.getElementById("departmentSelect");
            const positionSelect = document.getElementById("positionSelect");

            departmentSelect.addEventListener("change", function() {
                const deptId = this.value;
                positionSelect.innerHTML = '<option value="">-- Select Position --</option>';

                if (deptId) {
                    const selected = departments.find(d => d.id == deptId);
                    if (selected && selected.positions) {
                        selected.positions.forEach(pos => {
                            const opt = document.createElement("option");
                            opt.value = pos.id;
                            opt.textContent = pos.title;
                            positionSelect.appendChild(opt);
                        });
                    }
                }
            });
        });
    </script>
@endpush
