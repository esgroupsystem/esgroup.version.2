<nav class="navbar navbar-light navbar-vertical navbar-expand-xl px-4 px-lg-7">
    <div class="d-flex align-items-center">
        <div class="toggle-icon-wrapper">
            <button class="btn navbar-toggler-humburger-icon navbar-vertical-toggle" data-bs-toggle="tooltip"
                data-bs-placement="left" title="Toggle Navigation">
                <span class="navbar-toggle-icon">
                    <span class="toggle-line"></span>
                </span>
            </button>
        </div>

        <a class="navbar-brand" href="#">
            <div class="d-flex align-items-center py-3">
                <span class="font-sans-serif text-primary">Jell Group</span>
            </div>
        </a>
    </div>

    <div class="collapse navbar-collapse" id="navbarVerticalCollapse">
        <div class="navbar-vertical-content scrollbar">
            <ul class="navbar-nav flex-column mb-3" id="navbarVerticalNav">

                {{-- DASHBOARD --}}
                <li class="nav-item">
                    <a class="nav-link dropdown-indicator d-flex justify-content-between align-items-center"
                        href="#dashboardMenu" data-bs-toggle="collapse"
                        aria-expanded="{{ request()->is('dashboard*') ? 'true' : 'false' }}"
                        aria-controls="dashboardMenu">

                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-chart-pie"></span>
                            </span>
                            <span class="nav-link-text ps-1">Dashboard</span>
                        </div>
                    </a>

                    <ul class="nav collapse {{ request()->is('dashboard*') ? 'show' : '' }}" id="dashboardMenu">

                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}"
                                href="{{ route('dashboard.index') }}">
                                Default
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('dashboard/hr') ? 'active' : '' }}"
                                href="{{ route('hr.dashboard') }}">
                                Employees
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('dashboard/it-department') ? 'active' : '' }}"
                                href="{{ route('dashboard.itindex') }}">
                                IT Department
                            </a>
                        </li>

                    </ul>
                </li>

                {{-- LABEL IT Department --}}
                <li class="nav-item">
                    <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                        <div class="col-auto navbar-vertical-label">
                            IT Department
                        </div>
                        <div class="col ps-0">
                            <hr class="mb-0 navbar-vertical-divider">
                        </div>
                    </div>
                </li>

                {{-- TICKETS JOB ORDER --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('tickets/job-order') ? 'active' : '' }}"
                        href="{{ route('tickets.joborder.index') }}">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-ticket-alt"></span>
                            </span>
                            <span class="nav-link-text ps-1">Tickets Job Order</span>
                        </div>
                    </a>
                </li>

                {{-- CCTV CONCERN --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('tickets/cctv') ? 'active' : '' }}" href="#">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-video"></span>
                            </span>
                            <span class="nav-link-text ps-1">CCTV Concern</span>
                        </div>
                    </a>
                </li>

                {{-- LABEL HR Department --}}
                <li class="nav-item">
                    <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                        <div class="col-auto navbar-vertical-label">
                            HR Department
                        </div>
                        <div class="col ps-0">
                            <hr class="mb-0 navbar-vertical-divider">
                        </div>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link dropdown-indicator {{ request()->is('employees*') ? 'active' : '' }}"
                        href="#employeesMenu" role="button" data-bs-toggle="collapse"
                        aria-expanded="{{ request()->is('employees*') ? 'true' : 'false' }}">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-users"></span>
                            </span>
                            <span class="nav-link-text ps-1">Employees</span>
                        </div>
                    </a>
                    <ul class="nav collapse {{ request()->is('employees*') ? 'show' : '' }}" id="employeesMenu">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('employees') ? 'active' : '' }}"
                                href="{{ route('employees.staff.index') }}">
                                <span class="nav-link-text">Employee List</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('employees/departments') || request()->is('employees/positions') ? 'active' : '' }}"
                                href="{{ route('employees.departments.index') }}">
                                <span class="nav-link-text">Department & Position</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">

                    {{-- Attendance Parent --}}
                    <a class="nav-link dropdown-indicator {{ request()->is('attendance*') || request()->is('leave*') ? 'active' : '' }}"
                        href="#attendanceMenu" role="button" data-bs-toggle="collapse"
                        aria-expanded="{{ request()->is('attendance*') || request()->is('leave*') ? 'true' : 'false' }}">

                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-calendar-check"></span>
                            </span>
                            <span class="nav-link-text ps-1">Attendance</span>
                        </div>
                    </a>

                    <ul class="nav collapse {{ request()->is('attendance*') || request()->is('leave*') ? 'show' : '' }}"
                        id="attendanceMenu">

                        {{-- LEAVES (Second Level) --}}
                        <li class="nav-item">
                            <a class="nav-link dropdown-indicator {{ request()->is('leave*') ? 'active' : '' }}"
                                href="#leaveMenu" role="button" data-bs-toggle="collapse"
                                aria-expanded="{{ request()->is('leave*') ? 'true' : 'false' }}">
                                <span class="nav-link-text">Leaves</span>
                            </a>

                            {{-- THIRD LEVEL --}}
                            <ul class="nav collapse {{ request()->is('leave*') ? 'show' : '' }}" id="leaveMenu">

                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('leave/driver') ? 'active' : '' }}"
                                        href="{{ route('driver-leave.driver.index') }}">
                                        <span class="nav-link-text">Driver (Leave)</span>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('leave/conductor') ? 'active' : '' }}"
                                        href="{{ route('conductor-leave.conductor.index') }}">
                                        <span class="nav-link-text">Conductor (Leave)</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                {{-- LABEL Maintenance --}}
                <li class="nav-item">
                    <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                        <div class="col-auto navbar-vertical-label">
                            Maintenance
                        </div>
                        <div class="col ps-0">
                            <hr class="mb-0 navbar-vertical-divider">
                        </div>
                    </div>
                </li>

                {{-- Request Orders --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('request*') ? 'active' : '' }}"
                        href="{{ route('request.index') }}">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-inbox"></span>
                            </span>
                            <span class="nav-link-text ps-1">Request Items</span>
                        </div>
                    </a>
                </li>

                {{-- Category --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('category*') ? 'active' : '' }}"
                        href="{{ route('category.index') }}">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="far fa-list-alt"></span>
                            </span>
                            <span class="nav-link-text ps-1">Categories</span>
                        </div>
                    </a>
                </li>

                {{-- Product --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('items*') ? 'active' : '' }}"
                        href="{{ route('items.index') }}">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-toolbox"></span>
                            </span>
                            <span class="nav-link-text ps-1">Products</span>
                        </div>
                    </a>
                </li>

                {{-- LABEL Purchaser --}}
                <li class="nav-item">
                    <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                        <div class="col-auto navbar-vertical-label">
                            Accounting
                        </div>
                        <div class="col ps-0">
                            <hr class="mb-0 navbar-vertical-divider">
                        </div>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->is('purchase*') ? 'active' : '' }}"
                        href="{{ route('purchase.index') }}">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-dolly"></span>
                            </span>
                            <span class="nav-link-text ps-1">Purchase Order</span>
                        </div>
                    </a>
                </li>

                {{-- LABEL Authentication --}}
                <li class="nav-item">
                    <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                        <div class="col-auto navbar-vertical-label">
                            Authentication
                        </div>
                        <div class="col ps-0">
                            <hr class="mb-0 navbar-vertical-divider">
                        </div>
                    </div>
                </li>
                {{-- USERS --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('authentication/users*') ? 'active' : '' }}"
                        href="{{ route('authentication.users.index') }}">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-user-shield"></span>
                            </span>
                            <span class="nav-link-text ps-1">Users</span>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('authentication/roles*') ? 'active' : '' }}"
                        href="{{ route('roles.index') }}">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-user-secret"></span>
                            </span>
                            <span class="nav-link-text ps-1">Roles</span>
                        </div>
                    </a>
                </li>



            </ul>
        </div>
    </div>

</nav>
