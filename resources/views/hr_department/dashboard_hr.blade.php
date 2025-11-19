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

            <div class="row g-3 mb-3">
                <div class="col-xxl-8 col-lg-12">
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
                                    <div class="d-flex position-relative">
                                        <a href="{{ route('employees.staff.index') }}"
                                            class="d-flex align-items-center stretched-link text-decoration-none">
                                            <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2"><span
                                                    class="fas fa-users text-primary"></span></div>
                                            <div class="flex-1">
                                                <h6 class="mb-0 text-800">Employees</h6>
                                                <p class="mb-0 fs-11 text-500">Manage employee records</p>
                                            </div>
                                        </a>
                                    </div>
                                </div>

                                <div class="col-sm-6 col-md-4">
                                    <div class="d-flex position-relative">
                                        <a href="{{ route('driver-leave.driver.index') }}"
                                            class="d-flex align-items-center stretched-link text-decoration-none">
                                            <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2"><span
                                                    class="fas fa-plane-departure text-warning"></span></div>
                                            <div class="flex-1">
                                                <h6 class="mb-0 text-800">Leave Requests</h6>
                                                <p class="mb-0 fs-11 text-500">Approve or review leaves</p>
                                            </div>
                                        </a>
                                    </div>
                                </div>

                                <div class="col-sm-6 col-md-4">
                                    <div class="d-flex position-relative">
                                        <a href="{{ route('hr.dashboard') }}"
                                            class="d-flex align-items-center stretched-link text-decoration-none">
                                            <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2"><span
                                                    class="fas fa-gavel text-danger"></span></div>
                                            <div class="flex-1">
                                                <h6 class="mb-0 text-800">Offences</h6>
                                                <p class="mb-0 fs-11 text-500">View offences & actions</p>
                                            </div>
                                        </a>
                                    </div>
                                </div>

                                <div class="col-sm-6 col-md-4">
                                    <div class="d-flex position-relative">
                                        <a href="{{ route('employees.departments.index') }}"
                                            class="d-flex align-items-center stretched-link text-decoration-none">
                                            <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2"><span
                                                    class="fas fa-file-alt text-info"></span></div>
                                            <div class="flex-1">
                                                <h6 class="mb-0 text-800">Document Compliance</h6>
                                                <p class="mb-0 fs-11 text-500">Review missing documents</p>
                                            </div>
                                        </a>
                                    </div>
                                </div>

                                <div class="col-sm-6 col-md-4">
                                    <div class="d-flex position-relative">
                                        <a href="{{ route('hr.dashboard') }}"
                                            class="d-flex align-items-center stretched-link text-decoration-none">
                                            <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2"><span
                                                    class="fas fa-user-slash text-secondary"></span></div>
                                            <div class="flex-1">
                                                <h6 class="mb-0 text-800">Terminations</h6>
                                                <p class="mb-0 fs-11 text-500">Pending termination actions</p>
                                            </div>
                                        </a>
                                    </div>
                                </div>

                                <div class="col-sm-6 col-md-4">
                                    <div class="d-flex position-relative">
                                        <a href="{{ route('employees.departments.index') }}"
                                            class="d-flex align-items-center stretched-link text-decoration-none">
                                            <div class="icon-item icon-item-sm border rounded-3 shadow-none me-2"><span
                                                    class="fas fa-building text-primary"></span></div>
                                            <div class="flex-1">
                                                <h6 class="mb-0 text-800">Departments</h6>
                                                <p class="mb-0 fs-11 text-500">Overview by department</p>
                                            </div>
                                        </a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-4 col-md-6">
                    <div class="card h-100">
                        <div class="card-header d-flex flex-between-center">
                            <h5 class="mb-0">Team Progress</h5>
                            <a class="btn btn-link btn-sm px-0" href="#!">Report<span
                                    class="fas fa-chevron-right ms-1 fs-11"></span></a>
                        </div>
                        <div class="card-body">
                            <p class="fs-10 text-600">Overview of HR tasks and progress</p>
                            <div class="progress mb-3 rounded-pill" style="height: 6px;">
                                <div class="progress-bar bg-progress-gradient rounded-pill" style="width: 72%"></div>
                            </div>
                            <p class="mb-0 text-primary">72% completed</p>
                            <p class="mb-0 fs-11 text-500">This month</p>
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

            <div class="row g-3">

                <div class="col-lg-7">

                    <div class="card shadow-sm mb-3">
                        <div class="card-header d-flex align-items-center gap-2">
                            <span class="header-icon bg-primary bg-opacity-10 text-primary rounded-2 p-2"><i
                                    class="fas fa-chart-pie"></i></span>
                            <span class="fw-semibold">Employee Status Distribution</span>
                        </div>
                        <div class="card-body pt-3">
                            <div id="statusPieChart" class="chart-area"></div>

                            <div class="row mt-3 g-2 small">
                                <div class="col-6 d-flex align-items-center gap-2"><span
                                        class="legend-dot bg-success"></span> Active: <strong
                                        class="ms-1">{{ $statusCounts['Active'] ?? 0 }}</strong></div>
                                <div class="col-6 d-flex align-items-center gap-2"><span
                                        class="legend-dot bg-warning"></span> On Leave: <strong
                                        class="ms-1">{{ $statusCounts['On Leave'] ?? 0 }}</strong></div>
                                <div class="col-6 d-flex align-items-center gap-2"><span
                                        class="legend-dot bg-danger"></span> Terminated: <strong
                                        class="ms-1">{{ $statusCounts['Terminated'] ?? 0 }}</strong></div>
                                <div class="col-6 d-flex align-items-center gap-2"><span
                                        class="legend-dot bg-secondary"></span> Inactive: <strong
                                        class="ms-1">{{ $statusCounts['Inactive'] ?? 0 }}</strong></div>
                                <div class="col-6 d-flex align-items-center gap-2"><span
                                        class="legend-dot bg-info"></span> Pending Docs: <strong
                                        class="ms-1">{{ $statusCounts['Pending Documents'] ?? 0 }}</strong></div>
                            </div>

                        </div>
                    </div>

                    <div class="card shadow-sm mb-3">
                        <div class="card-header fw-semibold">Employees by Department</div>
                        <div class="card-body" style="height:270px;padding:1.2rem;">
                            <div id="deptBarChart"></div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 mb-3">
                        <div class="card-header fw-semibold">Leave Summary</div>
                        <div class="card-body" style="height: 270px; overflow-y:auto;">

                            <ul class="list-group list-group-flush small">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Active Leave</span> <strong>{{ $leaveSummary['active'] }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Not Started</span> <strong>{{ $leaveSummary['not_started'] }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Ongoing</span> <strong>{{ $leaveSummary['ongoing'] }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Ended Today</span> <strong>{{ $leaveSummary['expired_today'] }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Cancelled</span> <strong>{{ $leaveSummary['cancelled'] }}</strong>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>Completed</span> <strong>{{ $leaveSummary['completed'] }}</strong>
                                </li>
                            </ul>

                        </div>
                    </div>


                </div>

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
                        <div class="card-header fw-semibold">Document Compliance</div>
                        <div class="card-body" style="height:270px; overflow-y:auto;">

                            <ul class="list-group list-group-flush small">
                                @foreach ($documentCompliance as $doc => $count)
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>{{ ucfirst(str_replace('_', ' ', $doc)) }}</span>
                                        <strong>{{ $count }}</strong>
                                    </li>
                                @endforeach
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

            {{-- Bottom area --}}
            <div class="row g-3 mt-3">

                <div class="col-lg-4">
                    <div class="card p-3 shadow-sm border-0 mb-3">
                        <h6 class="fw-semibold">Attendance Summary</h6>

                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Present</span><strong>{{ $attendance['present'] }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Absent</span><strong>{{ $attendance['absent'] }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>On Leave</span><strong>{{ $attendance['on_leave'] }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Official Business</span><strong>{{ $attendance['ob'] }}</strong>
                            </li>
                        </ul>
                    </div>


                    <div class="card p-3 shadow-sm border-0">
                        <h6 class="fw-semibold mb-2">Security Logs</h6>

                        @foreach ($securityLogs as $log)
                            <div class="mb-2 small">
                                <strong>{{ $log['event'] }}</strong><br>
                                <span class="text-muted">{{ $log['time'] }}</span>
                            </div>
                        @endforeach
                    </div>

                </div>

                <div class="col-lg-4">
                    <div class="card p-3 shadow-sm border-0 mb-3">
                        <h6 class="fw-semibold">Salary & Benefits</h6>
                        <p class="text-muted small">Module coming soon.</p>
                    </div>


                    <div class="card p-3 shadow-sm border-0">
                        <h6 class="fw-semibold">HR Notes</h6>
                        <p class="text-muted small">No notes added yet.</p>
                    </div>

                </div>

                <div class="col-lg-4">
                    <div class="card p-3 shadow-sm border-0 mb-3">
                        <h6 class="fw-semibold">Top Performers</h6>
                        <p class="text-muted small">Performance module coming soon.</p>
                    </div>


                    <div class="card p-3 shadow-sm border-0">
                        <h6 class="fw-semibold">Reminders</h6>

                        @foreach ($reminders as $r)
                            <div class="small mb-1">• {{ $r }}</div>
                        @endforeach
                    </div>

                </div>

            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Apex donut for status
            new ApexCharts(document.querySelector("#statusPieChart"), {
                chart: {
                    type: 'donut',
                    height: 260,
                    toolbar: {
                        show: false
                    }
                },
                series: {!! json_encode(array_values($statusCounts)) !!},
                labels: {!! json_encode(array_keys($statusCounts)) !!},
                colors: ['#28a745', '#f6c23e', '#dc3545', '#6c757d', '#36b9cc'],
                stroke: {
                    width: 0
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                name: {
                                    show: false
                                },
                                value: {
                                    show: true,
                                    fontSize: '22px',
                                    fontWeight: 700
                                },
                                total: {
                                    show: true,
                                    label: 'Total',
                                    fontSize: '14px',
                                    fontWeight: 600,
                                    formatter: () => "{{ $totalEmployees }}"
                                }
                            }
                        }
                    }
                },
                legend: {
                    show: false
                }
            }).render();

            // Dept bar chart using Apex (simple)
            new ApexCharts(document.querySelector('#deptBarChart'), {
                chart: {
                    type: 'bar',
                    height: 240,
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                    name: 'Employees',
                    data: {!! json_encode($deptData) !!}
                }],
                xaxis: {
                    categories: {!! json_encode($deptLabels) !!},
                    labels: {
                        rotate: -20
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        columnWidth: '45%'
                    }
                },
                colors: ['#3f80ea']
            }).render();

        });
    </script>
@endpush

@push('styles')
    <style>
        .card {
            border-radius: 12px !important;
        }

        .header-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 15px
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block
        }

        .chart-area {
            height: 240px
        }

        .display-6 {
            font-size: 1.6rem
        }

        /* small tweaks to match Falcon look */
        .bg-progress-gradient {
            background: linear-gradient(90deg, #3f80ea, #36b9cc)
        }
    </style>
@endpush
