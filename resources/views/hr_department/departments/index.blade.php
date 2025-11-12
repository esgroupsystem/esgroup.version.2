@extends('layouts.app')
@section('title', 'Departments & Positions')

@section('content')
    <div class="container" data-layout="container">
        <div class="content">

            {{-- 🧩 TOP CARD --}}
            <div class="card mb-4">
                <div class="bg-holder d-none d-lg-block bg-card"
                    style="background-image:url(/assets/img/icons/spot-illustrations/corner-4.png);">
                </div>

                <div class="card-body position-relative">
                    <div class="row">
                        <div class="col-lg-8">
                            <h3 class="mb-2">Departments & Positions</h3>
                            <p class="text-muted">
                                Manage departments and their respective job titles across the organization.
                            </p>
                        </div>
                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                                <i class="fas fa-plus me-1"></i> Add Department
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 🧭 TABLE --}}
            <div class="card mb-4">
                <div class="p-3">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <input class="form-control form-control-sm search" placeholder="Search department...">
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div id="departmentTable"
                        data-list='{"valueNames":["department_name","positions"],"page":10,"pagination":true}'>

                        <div class="table-responsive scrollbar">
                            <table class="table table-hover table-striped fs-10 mb-0">
                                <thead class="bg-200 text-900">
                                    <tr>
                                        <th class="sort" data-sort="department_name">Department</th>
                                        <th class="sort" data-sort="positions">Positions</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="list">
                                    @forelse ($departments as $dept)
                                        <tr>
                                            <td class="department_name">{{ $dept->name }}</td>
                                            <td class="positions">
                                                @forelse ($dept->positions as $pos)
                                                    <span class="badge bg-light text-dark me-1">
                                                        {{ $pos->title }}
                                                        <form action="{{ route('employees.positions.destroy', $pos->id) }}" method="POST"
                                                            class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-link text-danger p-0 ms-1"
                                                                style="font-size:10px;">x</button>
                                                        </form>
                                                    </span>
                                                @empty
                                                    <span class="text-muted">No positions</span>
                                                @endforelse

                                                {{-- Add new position inline --}}
                                                <form action="{{ route('employees.departments.position.store') }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="department_id" value="{{ $dept->id }}">
                                                    <input type="text" name="title"
                                                        class="form-control form-control-sm d-inline-block mt-2"
                                                        style="width: 200px;" placeholder="Add new position...">
                                                </form>
                                            </td>
                                            <td class="text-center">
                                                <form action="{{ route('employees.departments.destroy', $dept->id) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Delete this department?')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">No departments found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
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

    {{-- ➕ Add Department Modal --}}
    <div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('employees.departments.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addDepartmentModalLabel">Add Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Department Name</label>
                    <input type="text" name="name" class="form-control" required>
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
            const departmentTable = new List("departmentTable", {
                valueNames: ["department_name", "positions"],
                page: 10,
                pagination: true
            });

            const searchInput = document.querySelector(".search");
            searchInput.addEventListener("keyup", () => {
                departmentTable.search(searchInput.value);
            });
        });
    </script>
@endpush
