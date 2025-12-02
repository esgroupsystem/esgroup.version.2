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

            {{-- HEADER CARD --}}
            <div class="card mb-4">
                <div class="bg-holder d-none d-lg-block bg-card"
                    style="background-image:url(/assets/img/icons/spot-illustrations/corner-4.png);">
                </div>

                <div class="card-body position-relative">
                    <div class="row">
                        <div class="col-lg-8">
                            <h3 class="mb-2">Employees Directory</h3>
                            <p class="text-muted">Manage employee records and search quickly.</p>
                        </div>

                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
                                <i class="fas fa-plus me-1"></i> Add Employee
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- EMPLOYEE LIST CARD --}}
            <div class="card mb-4">

                <div class="card-header pb-0">
                    <h5 class="mb-0">Employee List</h5>
                </div>

                {{-- SEARCH BAR --}}
                <div class="p-3">
                    <input id="liveSearch" class="form-control form-control-sm" placeholder="Search employee..."
                        value="{{ request('search') }}">
                </div>

                {{-- AJAX TABLE RELOAD AREA --}}
                <div id="employeeTable">
                    @include('hr_department.employees.table')
                </div>

            </div>
        </div>
    </div>

    {{-- ADD EMPLOYEE MODAL --}}
    @include('hr_department.employees.modal_add')
@endsection


@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            /* ------------------------------
               LIVE SEARCH (AJAX)
            --------------------------------*/
            let timer = null;

            document.getElementById("liveSearch").addEventListener("keyup", function() {
                let search = this.value;

                clearTimeout(timer);

                timer = setTimeout(() => {
                    fetch(`?search=${search}`, {
                            headers: {
                                "X-Requested-With": "XMLHttpRequest"
                            }
                        })
                        .then(res => res.text())
                        .then(html => {
                            document.getElementById("employeeTable").innerHTML = html;
                        });
                }, 300);
            });


            /* ------------------------------
               POSITION DROPDOWN (MODAL)
            --------------------------------*/
            const departments = @json($departments);
            const departmentSelect = document.getElementById("departmentSelect");
            const positionSelect = document.getElementById("positionSelect");

            if (departmentSelect) {
                departmentSelect.addEventListener("change", function() {
                    const deptId = this.value;
                    positionSelect.innerHTML = '<option value="">-- Select Position --</option>';

                    const selected = departments.find(d => d.id == deptId);

                    if (selected?.positions) {
                        selected.positions.forEach(pos => {
                            const opt = document.createElement("option");
                            opt.value = pos.id;
                            opt.textContent = pos.title;
                            positionSelect.appendChild(opt);
                        });
                    }
                });
            }
        });
    </script>
@endpush


@push('styles')
    <style>
        .pagination {
            font-size: 14px !important;
        }

        .pagination .page-link {
            padding: 4px 10px !important;
            font-size: 14px !important;
            border-radius: 4px !important;
            color: #4a4a4a !important;
            border: 1px solid #d0d5dd !important;
            background: #f8f9fa !important;
        }

        .pagination .page-item.active .page-link {
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
            color: #fff !important;
            font-weight: 600 !important;
        }

        .pagination .page-link:hover {
            background: #e2e6ea !important;
            border-color: #c4c9cf !important;
        }

        .pagination .page-item.disabled .page-link {
            opacity: .5 !important;
        }

        .pagination .page-item {
            margin: 0 2px !important;
        }
    </style>
@endpush
