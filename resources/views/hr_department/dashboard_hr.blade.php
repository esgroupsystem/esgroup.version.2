@extends('layouts.app')
@section('title', 'HR Dashboard - Falcon')

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
            {{-- Top row: Quick actions + KPI small cards --}}
            <div class="row g-3 mb-3">
                <div class="col-xxl-6 col-lg-12">
                    <div class="card h-100">
                        <div class="bg-holder bg-card"
                            style="background-image:url(/assets/img/icons/spot-illustrations/corner-3.png);opacity:.06">
                        </div>

                        <div class="card-header z-1">
                            <h5 class="text-primary">Human Resources</h5>
                            <h6 class="text-600">Quick actions & shortcuts</h6>
                        </div>

                        <div class="card-body z-1">
                            <div class="row g-2 h-100 align-items-end">
                                <div class="col-sm-6 col-md-4">
                                    <a href="{{ route('employees.staff.index') }}"
                                        class="d-flex align-items-center stretched-link text-decoration-none">
                                        <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2">
                                            <span class="fas fa-users text-primary"></span>
                                        </div>
                                        <div class="flex-1">
                                            <h6 class="mb-0 text-800">Employees</h6>
                                            <p class="mb-0 fs-11 text-500">Manage employee records</p>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-sm-6 col-md-4">
                                    <a href="{{ route('driver-leave.driver.index') }}"
                                        class="d-flex align-items-center stretched-link text-decoration-none">
                                        <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2">
                                            <span class="fas fa-plane-departure text-warning"></span>
                                        </div>
                                        <div class="flex-1">
                                            <h6 class="mb-0 text-800">Leave Requests</h6>
                                            <p class="mb-0 fs-11 text-500">Approve or review leaves</p>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-sm-6 col-md-4">
                                    <a href="#" class="d-flex align-items-center stretched-link text-decoration-none">
                                        <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2">
                                            <span class="fas fa-gavel text-danger"></span>
                                        </div>
                                        <div class="flex-1">
                                            <h6 class="mb-0 text-800">Offences</h6>
                                            <p class="mb-0 fs-11 text-500">View offences & actions</p>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-sm-6 col-md-4">
                                    <a href="#" class="d-flex align-items-center stretched-link text-decoration-none">
                                        <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2">
                                            <span class="fas fa-user-slash text-secondary"></span>
                                        </div>
                                        <div class="flex-1">
                                            <h6 class="mb-0 text-800">Terminations</h6>
                                            <p class="mb-0 fs-11 text-500">Pending termination actions</p>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-sm-6 col-md-4">
                                    <a href="{{ route('employees.departments.index') }}"
                                        class="d-flex align-items-center stretched-link text-decoration-none">
                                        <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2">
                                            <span class="fas fa-building text-primary"></span>
                                        </div>
                                        <div class="flex-1">
                                            <h6 class="mb-0 text-800">Departments</h6>
                                            <p class="mb-0 fs-11 text-500">Overview by department</p>
                                        </div>
                                    </a>
                                </div>

                                <div class="col-sm-6 col-md-4">
                                    <a href="{{ route('authentication.users.index') ?? '#' }}"
                                        class="d-flex align-items-center stretched-link text-decoration-none">
                                        <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2">
                                            <span class="fas fa-user-cog text-info"></span>
                                        </div>
                                        <div class="flex-1">
                                            <h6 class="mb-0 text-800">User Management</h6>
                                            <p class="mb-0 fs-11 text-500">Create and manage system users</p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Small KPI cards --}}
                <div class="col-xxl-3 col-md-6">
                    <div class="card h-100">
                        <div class="card-header d-flex flex-between-center">
                            <h5 class="mb-0">Team Progress</h5>
                            <a class="btn btn-link btn-sm px-0" href="#!">Report<span
                                    class="fas fa-chevron-right ms-1 fs-11"></span></a>
                        </div>
                        <div class="card-body">
                            <p class="fs-10 text-600 mb-2">HR tasks progress overview</p>
                            <div class="progress mb-3 rounded-pill" style="height: 6px" role="progressbar"
                                aria-valuenow="62" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar bg-progress-gradient rounded-pill" style="width: 62%"></div>
                            </div>
                            <p class="mb-0 text-primary">62% completed</p>
                            <p class="mb-0 fs-11 text-500">This quarter</p>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-3 col-md-6">
                    <div class="card h-100">
                        <div class="card-header pb-0">
                            <div class="row">
                                <div class="col">
                                    <p class="mb-1 fs-11 text-500">Next HR Meeting</p>
                                    <h5 class="text-primary fs-9">Monthly HR Sync</h5>
                                </div>
                                <div class="col-auto">
                                    <div class="bg-primary-subtle px-3 py-3 rounded-circle text-center"
                                        style="width: 60px; height: 60px">
                                        <h5 class="text-primary mb-0 d-flex flex-column mt-n1">
                                            <span>09</span><small class="text-primary fs-11 lh-1">MAR</small>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body d-flex align-items-end">
                            <div class="row g-3 justify-content-between w-100">
                                <div class="col-10">
                                    <p class="fs-10 text-600 mb-0">Plan and review HR initiatives</p>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-success w-100 fs-10" type="button"><span
                                            class="fas fa-video me-2"></span>Join</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            {{-- KPI Row --}}
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-md-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fs-12 text-600 text-muted">Total Employees</div>
                                <div class="display-6 fw-bold">{{ $totalEmployees }}</div>
                            </div>
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center"
                                style="width:56px;height:56px;">
                                <i class="fas fa-users fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-md-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fs-12 text-600 text-muted">Active</div>
                                <div class="display-6 fw-bold text-success">{{ $activeEmployees }}</div>
                                <small class="text-muted">{{ $activePct }}% of total</small>
                            </div>
                            <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center"
                                style="width:56px;height:56px;">
                                <i class="fas fa-check fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-md-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fs-12 text-600 text-muted">On Leave Today</div>
                                <div class="display-6 fw-bold text-warning">{{ $onLeaveEmployees }}</div>
                                <small class="text-muted">Driver + Staff</small>
                            </div>
                            <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center"
                                style="width:56px;height:56px;">
                                <i class="fas fa-plane fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-md-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fs-12 text-600 text-muted">For Action</div>
                                <div class="display-6 fw-bold text-danger">{{ $forActionCount }}</div>
                                <small class="text-muted">Offences & Issues</small>
                            </div>
                            <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center"
                                style="width:56px;height:56px;">
                                <i class="fas fa-exclamation-circle fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            {{-- Main row: Left = Employee List (replaces pie chart), Right = timeline + other cards --}}
            <div class="row g-3">
                <div class="col-lg-7">
                    {{-- Employee List (Name | Department | Position | Status) --}}
                    {{-- Employee Status List (Name | Department | Position | Status) --}}
                    <div class="card shadow-sm mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <span class="header-icon bg-primary bg-opacity-10 text-primary rounded-2 p-2">
                                    <i class="fas fa-list"></i>
                                </span>
                                <span class="fw-semibold ms-2">Employee Status</span>
                            </div>

                            <input id="employeeSearch" class="form-control form-control-sm"
                                placeholder="Search employee..." style="min-width:200px;" />
                        </div>

                        <div class="card-body p-0">
                            <div id="employeeStatusTable"
                                data-list='{"valueNames":["name","department","position","status"],"page":10,"pagination":true}'>

                                <div class="table-responsive scrollbar">
                                    <table class="table table-hover table-striped fs-10 mb-0 align-middle">
                                        <thead class="bg-200 text-900">
                                            <tr>
                                                <th class="sort" data-sort="name">Name</th>
                                                <th class="sort" data-sort="department">Department</th>
                                                <th class="sort" data-sort="position">Position</th>
                                                <th class="sort text-center" data-sort="status">Status</th>
                                            </tr>
                                        </thead>

                                        <tbody class="list">
                                            @foreach ($employees as $emp)
                                                <tr>
                                                    <td class="name">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar avatar-sm rounded-circle bg-soft-primary text-primary me-2"
                                                                style="width:36px;height:36px;">
                                                                <span class="fs-8 fw-semi-bold">
                                                                    {{ strtoupper(substr($emp->full_name, 0, 1)) }}
                                                                </span>
                                                            </div>
                                                            <div>
                                                                <div class="fw-semi-bold">{{ $emp->full_name }}</div>
                                                                <div class="text-500 fs-10">{{ $emp->email ?? '-' }}</div>
                                                            </div>
                                                        </div>
                                                    </td>

                                                    <td class="department text-capitalize">
                                                        {{ $emp->department?->name ?? '-' }}
                                                    </td>

                                                    <td class="position text-capitalize">
                                                        {{ $emp->position?->title ?? '-' }}
                                                    </td>

                                                    <td class="status text-center">
                                                        @if (strtolower($emp->status) === 'active')
                                                            <span
                                                                class="badge rounded-pill bg-soft-success text-success">Active</span>
                                                        @elseif (strtolower($emp->status) === 'on leave')
                                                            <span
                                                                class="badge rounded-pill bg-soft-warning text-warning">On
                                                                Leave</span>
                                                        @elseif (strtolower($emp->status) === 'terminated')
                                                            <span
                                                                class="badge rounded-pill bg-soft-danger text-danger">Terminated</span>
                                                        @else
                                                            <span
                                                                class="badge rounded-pill bg-soft-secondary text-secondary">
                                                                {{ $emp->status ?? '—' }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Falcon Pagination --}}
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

                {{-- Right column --}}
                <div class="col-lg-5">
                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-header fw-semibold">Recent Employee Actions</div>
                        <div class="card-body" style="height:270px; overflow-y:auto;">
                            @forelse ($timeline as $t)
                                <div class="d-flex mb-3">
                                    <div class="me-2 text-primary">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="flex-grow-1 small">
                                        <div><strong>{{ $t['actor'] }}</strong></div>
                                        <div>{{ $t['action'] }}</div>
                                        <div class="text-muted">{{ $t['time'] }}</div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted small">No recent activities.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-header fw-semibold">Leave Summary</div>
                        <div class="card-body" style="height: 270px; overflow-y:auto;">
                            <ul class="list-group list-group-flush small">
                                <li class="list-group-item d-flex justify-content-between"><span>Active Leave</span>
                                    <strong>{{ $leaveSummary['active'] ?? 0 }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between"><span>Not Started</span>
                                    <strong>{{ $leaveSummary['not_started'] ?? 0 }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between"><span>Ongoing</span>
                                    <strong>{{ $leaveSummary['ongoing'] ?? 0 }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between"><span>Ended Today</span>
                                    <strong>{{ $leaveSummary['expired_today'] ?? 0 }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between"><span>Cancelled</span>
                                    <strong>{{ $leaveSummary['cancelled'] ?? 0 }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between"><span>Completed</span>
                                    <strong>{{ $leaveSummary['completed'] ?? 0 }}</strong>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-header fw-semibold">Employee Offences</div>
                        <div class="card-body" style="height:270px; overflow-y:auto;">
                            @forelse ($offences as $o)
                                <div class="mb-2 small">
                                    <strong>{{ $o->employee->full_name ?? '—' }}</strong><br>
                                    Level: {{ $o->level_label }} • Status: {{ $o->status_label }}<br>
                                    <span class="text-muted">{{ $o->updated_at->diffForHumans() }}</span>
                                </div>
                            @empty
                                <p class="text-muted small">No offences recorded.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <footer class="footer mt-4">
                <div class="row g-0 justify-content-between fs-10 mt-4 mb-3">
                    <div class="col-12 col-sm-auto text-center">
                        <p class="mb-0 text-600">HR Dashboard • Falcon style</p>
                    </div>
                    <div class="col-12 col-sm-auto text-center">
                        <p class="mb-0 text-600">v1.0</p>
                    </div>
                </div>
            </footer>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const table = new List("employeeStatusTable", {
                valueNames: ["name", "department", "position", "status"],
                page: 20,
                pagination: true
            });

            const searchInput = document.getElementById("employeeSearch");
            searchInput.addEventListener("keyup", () => {
                table.search(searchInput.value);
            });
        });

        // simple client-side search that hides / shows rows
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('employeeSearch');
            const tbody = document.getElementById('employeeTableBody');

            input.addEventListener('input', function() {
                const q = this.value.trim().toLowerCase();
                tbody.querySelectorAll('.employee-row').forEach(row => {
                    const text = row.innerText.toLowerCase();
                    row.style.display = q === '' ? '' : (text.indexOf(q) !== -1 ? '' : 'none');
                });
            });
        });
    </script>
@endpush
