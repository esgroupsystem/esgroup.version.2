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


                {{-- =============================================== --}}
                {{-- ================= DASHBOARD ================= --}}
                {{-- =============================================== --}}
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
                        @role('Developer', 'IT Head', 'Operation Manager')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('dashboard/hr') ? 'active' : '' }}"
                                    href="{{ route('hr.dashboard') }}">
                                    Employees
                                </a>
                            </li>
                        @endrole

                        @role('Developer', 'IT Head')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('dashboard/it-department') ? 'active' : '' }}"
                                    href="{{ route('dashboard.itindex') }}">
                                    IT Department
                                </a>
                            </li>
                        @endrole

                        @role('Developer', 'IT Head', 'Operation Manager', 'Maintenance Head', 'Maintenance Engineer')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('maintenance/items/dashboard') ? 'active' : '' }}"
                                    href="{{ route('items.dashboard') }}">
                                    Maintenance Stock
                                </a>
                            </li>
                        @endrole

                    </ul>
                </li>


                {{-- ========================================================= --}}
                {{-- ================= IT Department ================= --}}
                {{-- ========================================================= --}}

                @role('Developer', 'IT Head', 'IT Officer', 'Operation Manager')
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

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('tickets/cctv') ? 'active' : '' }}"
                            href="{{ route('concern.cctv.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-video"></span>
                                </span>
                                <span class="nav-link-text ps-1">CCTV Concern</span>
                            </div>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('it-inventory.*') ? 'active' : '' }}"
                            href="{{ route('it-inventory.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon"><i class="fas fa-laptop-house"></i></span>
                                <span class="nav-link-text ps-1">IT Inventory</span>
                            </div>
                        </a>
                    </li>
                @endrole


                {{-- =============================================== --}}
                {{-- ================= EMPLOYEES ================= --}}
                {{-- =============================================== --}}

                @role('Developer', 'IT Head', 'HR Head', 'HR Officer')
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
                                <a class="nav-link {{ request()->is('employees/departments*') || request()->is('employees/positions*') ? 'active' : '' }}"
                                    href="{{ route('employees.departments.index') }}">
                                    <span class="nav-link-text">Department & Position</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('employees/offenses*') ? 'active' : '' }}"
                                    href="{{ route('violation.offenses.index') }}">
                                    <span class="nav-link-text">HR Offenses</span>
                                </a>
                            </li>

                        </ul>
                    </li>
                    {{-- ============================================== --}}
                    {{-- ================= BENEFITS ================= --}}
                    {{-- ============================================== --}}

                    <li class="nav-item">
                        <a class="nav-link dropdown-indicator {{ request()->is('claims*') ? 'active' : '' }}"
                            href="#benefitsMenu" role="button" data-bs-toggle="collapse"
                            aria-expanded="{{ request()->is('claims*') ? 'true' : 'false' }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-hand-holding-heart"></span>
                                </span>
                                <span class="nav-link-text ps-1">Benefits</span>
                            </div>
                        </a>

                        <ul class="nav collapse {{ request()->is('claims*') ? 'show' : '' }}" id="benefitsMenu">

                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('claims*') ? 'active' : '' }}"
                                    href="{{ route('claims.index') }}">
                                    <span class="nav-link-text">SSS / Maternity / Paternity</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link disabled text-muted" href="#">
                                    <span class="nav-link-text">Cash Advance (Coming Soon)</span>
                                </a>
                            </li>

                        </ul>
                    </li>
                @endrole


                {{-- ======================================================= --}}
                {{-- ================= Attendance Parent ================= --}}
                {{-- ======================================================= --}}

                @role('Developer', 'IT Head', 'IT Officer', 'HR Head', 'HR Officer')
                    <li class="nav-item">
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
                            <li class="nav-item">
                                <a class="nav-link dropdown-indicator {{ request()->is('leave*') ? 'active' : '' }}"
                                    href="#leaveMenu" role="button" data-bs-toggle="collapse"
                                    aria-expanded="{{ request()->is('leave*') ? 'true' : 'false' }}">
                                    <span class="nav-link-text">Leaves</span>
                                </a>

                                <ul class="nav collapse {{ request()->is('leave*') ? 'show' : '' }}" id="leaveMenu">

                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->is('leave/employee') ? 'active' : '' }}"
                                            href="{{ route('employee-leave.employee.index') }}">
                                            <span class="nav-link-text">Employee</span>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->is('leave/driver') ? 'active' : '' }}"
                                            href="{{ route('driver-leave.driver.index') }}">
                                            <span class="nav-link-text">Driver</span>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->is('leave/conductor') ? 'active' : '' }}"
                                            href="{{ route('conductor-leave.conductor.index') }}">
                                            <span class="nav-link-text">Conductor</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                @endrole

                @role('Developer', 'IT Head', 'HR Head', 'HR Officer')
                    <li class="nav-item">
                        <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                            <div class="col-auto navbar-vertical-label">
                                Biometric Management
                            </div>
                            <div class="col ps-0">
                                <hr class="mb-0 navbar-vertical-divider">
                            </div>
                        </div>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('mirasol-logs.*') ? 'active' : '' }}"
                            href="{{ route('mirasol-logs.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-fingerprint"></span>
                                </span>
                                <span class="nav-link-text ps-1">Mirasol Biometrics</span>
                            </div>
                        </a>
                    </li>
                @endrole

                @role('Developer', 'IT Head', 'HR Head', 'HR Officer')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('manual-biometrics.*') ? 'active' : '' }}"
                            href="{{ route('manual-biometrics.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-keyboard"></span>
                                </span>
                                <span class="nav-link-text ps-1">Manual Biometrics</span>
                            </div>
                        </a>
                    </li>
                @endrole
                @role('Developer', 'IT Head')
                {{-- Employee Schedule --}}
                <li class="nav-item">
                    <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                        <div class="col-auto navbar-vertical-label">
                            Employee Schedule / Rates
                        </div>
                        <div class="col ps-0">
                            <hr class="mb-0 navbar-vertical-divider">
                        </div>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->is('payroll-plotting*') ? 'active' : '' }}"
                        href="{{ route('payroll-plotting.index') }}">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-calendar-alt"></span>
                            </span>
                            <span class="nav-link-text ps-1">Work Schedule</span>
                        </div>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('payroll-employee-salaries.*') ? 'active' : '' }}"
                        href="{{ route('payroll-employee-salaries.index') }}">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-money-bill-wave"></span>
                            </span>
                            <span class="nav-link-text ps-1">Employee Rates</span>
                        </div>
                    </a>
                </li>
                @endrole

                @role('Developer', 'IT Head', 'HR Head', 'HR Officer')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('holidays.*') ? 'active' : '' }}"
                        href="{{ route('holidays.index') }}">

                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </span>
                            <span class="nav-link-text ms-2">Holiday Calendar</span>
                        </div>
                    </a>
                </li>
                @endrole
                
                @role('Developer', 'IT Head')
                <li class="nav-item">
                    <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                        <div class="col-auto navbar-vertical-label">
                            Payroll Process
                        </div>
                        <div class="col ps-0">
                            <hr class="mb-0 navbar-vertical-divider">
                        </div>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('payroll-attendance-adjustments.*') ? 'active' : '' }}"
                        href="{{ route('payroll-attendance-adjustments.index') }}">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-edit"></span>
                            </span>
                            <span class="nav-link-text ps-1">Adjustment</span>
                        </div>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('attendance-summary.*') ? 'active' : '' }}"
                        href="{{ route('attendance-summary.index') }}">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-clipboard-list"></span>
                            </span>
                            <span class="nav-link-text ps-1">Attendance Summary</span>
                        </div>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('payroll.*') ? 'active' : '' }}"
                        href="{{ route('payroll.index') }}">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-money-check-alt"></span>
                            </span>
                            <span class="nav-link-text ps-1">Payroll</span>
                        </div>
                    </a>
                </li>
            @endrole


            {{-- ======================================================= --}}
            {{-- ================= LABEL Maintenance ================= --}}
            {{-- ======================================================= --}}
            @role('Developer', 'IT Head', 'Maintenance Head', 'Maintenance Engineer')
                <li class="nav-item">
                    <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                        <div class="col-auto navbar-vertical-label">
                            Stock Movements
                        </div>
                        <div class="col ps-0">
                            <hr class="mb-0 navbar-vertical-divider">
                        </div>
                    </div>
                </li>

                {{-- Parts Out --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('parts-out*') ? 'active' : '' }}"
                        href="{{ route('parts-out.index') }}">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-tools"></span>
                            </span>
                            <span class="nav-link-text ps-1">Parts Issuance</span>
                        </div>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->is('buses*') ? 'active' : '' }}"
                        href="{{ route('buses.index') }}">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-bus"></span>
                            </span>
                            <span class="nav-link-text ps-1">Vehicle History</span>
                        </div>
                    </a>
                </li>
                {{-- ===================================================== --}}
                {{-- ================= LABEL Inventory ================= --}}
                {{-- ===================================================== --}}

                <li class="nav-item">
                    <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                        <div class="col-auto navbar-vertical-label">
                            Inventory Management
                        </div>
                        <div class="col ps-0">
                            <hr class="mb-0 navbar-vertical-divider">
                        </div>
                    </div>
                </li>

                {{-- Receiving --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('receivings*') ? 'active' : '' }}"
                        href="{{ route('receivings.index') }}">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-truck-loading"></span>
                            </span>
                            <span class="nav-link-text ps-1">Receiving Area</span>
                        </div>
                    </a>
                </li>

                {{-- Stock Transfer --}}
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('stock-transfers*') ? 'active' : '' }}"
                        href="{{ route('stock-transfers.index') }}">
                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-exchange-alt"></span>
                            </span>
                            <span class="nav-link-text ps-1">Stock Transfer</span>
                        </div>
                    </a>
                </li>
            @endrole

            {{-- ===================================================== --}}
            {{-- ================= LABEL Inventory ================= --}}
            {{-- ===================================================== --}}

            @role('Developer', 'IT Head', 'Maintenance Head', 'Maintenance Engineer')
                <li class="nav-item">
                    <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                        <div class="col-auto navbar-vertical-label">
                            Products Management
                        </div>
                        <div class="col ps-0">
                            <hr class="mb-0 navbar-vertical-divider">
                        </div>
                    </div>
                </li>

                {{-- <li class="nav-item">
                        <a class="nav-link {{ request()->is('request*') ? 'active' : '' }}"
                            href="{{ route('request.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-inbox"></span>
                                </span>
                                <span class="nav-link-text ps-1">Request Items</span>
                            </div>
                        </a>
                    </li> --}}

                {{-- ===================================================== --}}
                {{-- ================= LABEL Inventory ================= --}}
                {{-- ===================================================== --}}

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
                {{-- ===================================================== --}}
                {{-- ================= LABEL Purchaser ================= --}}
                {{-- ===================================================== --}}

                {{-- <li class="nav-item">
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
                    </li> --}}
            @endrole


            {{-- ========================================================== --}}
            {{-- ================= LABEL Authentication ================= --}}
            {{-- ========================================================== --}}

            @role('Developer', 'IT Head')
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
            @endrole
        </ul>
    </div>
</div>

</nav>
