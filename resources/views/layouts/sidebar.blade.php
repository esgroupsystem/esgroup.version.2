<nav class="navbar navbar-light navbar-vertical navbar-expand-xl px-4 px-lg-7">
    <div class="d-flex align-items-center">
        <div class="toggle-icon-wrapper">
            <button class="btn navbar-toggler-humburger-icon navbar-vertical-toggle"
                    data-bs-toggle="tooltip"
                    data-bs-placement="left"
                    title="Toggle Navigation">
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
                       href="#dashboardMenu"
                       data-bs-toggle="collapse"
                       aria-expanded="{{ request()->is('dashboard*') ? 'true' : 'false' }}"
                       aria-controls="dashboardMenu">

                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-chart-pie"></span>
                            </span>
                            <span class="nav-link-text ps-1">Dashboard</span>
                        </div>
                    </a>

                    <ul class="nav collapse {{ request()->is('dashboard*') ? 'show' : '' }}"
                        id="dashboardMenu">

                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}"
                               href="{{ route('dashboard.index') }}">
                                Default
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('dashboard/analytics') ? 'active' : '' }}"
                               href="{{ route('dashboard.analytics') }}">
                                Analytics
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('dashboard/crm') ? 'active' : '' }}"
                               href="{{ route('dashboard.crm') }}">
                                CRM
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


                {{-- LABEL --}}
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
                    <a class="nav-link {{ request()->is('tickets/cctv') ? 'active' : '' }}"
                       href="{{ route('tickets.cctv.index') }}">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-video"></span>
                            </span>
                            <span class="nav-link-text ps-1">CCTV Concern</span>
                        </div>
                    </a>
                </li>

                {{-- LABEL --}}
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

                {{-- EMPLOYEES LIST --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('employees') ? 'active' : '' }}"
                       href="{{ route('employees.staff.index') }}">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-users"></span>
                            </span>
                            <span class="nav-link-text ps-1">Employee List</span>
                        </div>
                    </a>
                </li>

                {{-- DEPARTMENTS & POSITIONS --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('employees/departments') ? 'active' : '' }}"
                       href="{{ route('employees.departments.index') }}">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-users-cog"></span>
                            </span>
                            <span class="nav-link-text ps-1">Departments & Positions</span>
                        </div>
                    </a>
                </li>

            </ul>
        </div>
    </div>

</nav>
