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

                        {{-- Default Dashboard --}}
                        @can('dashboard.view')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}"
                                    href="{{ route('dashboard.index') }}">
                                    Default
                                </a>
                            </li>
                        @endcan

                        {{-- HR Dashboard --}}
                        @can('hr-dashboard.view')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('dashboard/hr') ? 'active' : '' }}"
                                    href="{{ route('hr.dashboard') }}">
                                    Employees
                                </a>
                            </li>
                        @endcan

                        {{-- IT Dashboard --}}
                        @can('dashboard.it')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('dashboard/it-department') ? 'active' : '' }}"
                                    href="{{ route('dashboard.itindex') }}">
                                    IT Department
                                </a>
                            </li>
                        @endcan

                        {{-- Maintenance Dashboard --}}
                        @can('items.view')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('maintenance/items/dashboard') ? 'active' : '' }}"
                                    href="{{ route('items.dashboard') }}">
                                    Maintenance Stock
                                </a>
                            </li>
                        @endcan

                        {{-- Odometer --}}
                        @can('odometer.view')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->is('odometer-index*') ? 'active' : '' }}"
                                    href="{{ route('odometer.index') }}">
                                    Odometer Monitoring
                                </a>
                            </li>
                        @endcan

                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('biometrics/logs') ? 'active' : '' }}"
                                href="{{ route('biometrics.logs') }}">
                                Biometrics Logs
                            </a>
                        </li>

                    </ul>
                </li>


                {{-- ========================================================= --}}
                {{-- ================= IT Department ================= --}}
                {{-- ========================================================= --}}

                @canany(['tickets.view', 'cctv.view', 'it-inventory.view'])

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

                    {{-- Bus Dashboard --}}
                    @can('cctv.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('concern.bus-status') ? 'active' : '' }}"
                                href="{{ route('concern.bus-status') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="fas fa-bus"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">Bus Dashboard</span>
                                </div>
                            </a>
                        </li>
                    @endcan

                    @can('tickets.view')
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
                    @endcan

                    @can('cctv.view')
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
                    @endcan

                    @can('it-inventory.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('it-inventory.*') ? 'active' : '' }}"
                                href="{{ route('it-inventory.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <i class="fas fa-laptop-house"></i>
                                    </span>
                                    <span class="nav-link-text ps-1">IT Inventory</span>
                                </div>
                            </a>
                        </li>
                    @endcan

                @endcanany


                {{-- =============================================== --}}
                {{-- ================= EMPLOYEES ================= --}}
                {{-- =============================================== --}}

                @canany(['employees.view', 'departments.view', 'violations.view'])

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

                            @can('employees.view')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('employees') ? 'active' : '' }}"
                                        href="{{ route('employees.staff.index') }}">
                                        <span class="nav-link-text">
                                            Employee List
                                        </span>
                                    </a>
                                </li>
                            @endcan

                            @can('departments.view')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('employees/departments*') || request()->is('employees/positions*') ? 'active' : '' }}"
                                        href="{{ route('employees.departments.index') }}">
                                        <span class="nav-link-text">
                                            Department & Position
                                        </span>
                                    </a>
                                </li>
                            @endcan

                            @can('violations.view')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->is('employees/offenses*') ? 'active' : '' }}"
                                        href="{{ route('violation.offenses.index') }}">
                                        <span class="nav-link-text">
                                            HR Offenses
                                        </span>
                                    </a>
                                </li>
                            @endcan

                        </ul>
                    </li>

                @endcanany
                {{-- ============================================== --}}
                {{-- ================= BENEFITS ================= --}}
                {{-- ============================================== --}}

                @can('claims.view')
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
                                    <span class="nav-link-text">
                                        SSS / Maternity / Paternity
                                    </span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link disabled text-muted" href="#">
                                    <span class="nav-link-text">
                                        Cash Advance (Coming Soon)
                                    </span>
                                </a>
                            </li>

                        </ul>
                    </li>
                @endcan


                {{-- ======================================================= --}}
                {{-- ================= Attendance Parent ================= --}}
                {{-- ======================================================= --}}

                @canany(['employee-leave.view', 'driver-leave.view', 'conductor-leave.view'])

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
                                    href="#leaveMenu" role="button" data-bs-toggle="collapse">

                                    <span class="nav-link-text">
                                        Leaves
                                    </span>
                                </a>

                                <ul class="nav collapse {{ request()->is('leave*') ? 'show' : '' }}" id="leaveMenu">

                                    @can('employee-leave.view')
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('employee-leave.employee.index') }}">
                                                <span class="nav-link-text">Employee</span>
                                            </a>
                                        </li>
                                    @endcan

                                    @can('driver-leave.view')
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('driver-leave.driver.index') }}">
                                                <span class="nav-link-text">Driver</span>
                                            </a>
                                        </li>
                                    @endcan

                                    @can('conductor-leave.view')
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('conductor-leave.conductor.index') }}">
                                                <span class="nav-link-text">Conductor</span>
                                            </a>
                                        </li>
                                    @endcan

                                </ul>
                            </li>

                        </ul>
                    </li>

                @endcanany


                {{-- ================= BIOMETRIC ================= --}}

                @can('mirasol-logs.view')
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
                                <span class="nav-link-text ps-1">
                                    Mirasol Biometrics
                                </span>
                            </div>
                        </a>
                    </li>
                @endcan


                @can('manual-biometrics.view')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('manual-biometrics.*') ? 'active' : '' }}"
                            href="{{ route('manual-biometrics.index') }}">

                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-keyboard"></span>
                                </span>
                                <span class="nav-link-text ps-1">
                                    Manual Biometrics
                                </span>
                            </div>
                        </a>
                    </li>
                @endcan


                {{-- Employee Schedule / Rates --}}

                @canany(['payroll-plotting.view', 'employee-salaries.view'])
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

                    @can('payroll-plotting.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('payroll-plotting*') ? 'active' : '' }}"
                                href="{{ route('payroll-plotting.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="fas fa-calendar-alt"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">
                                        Work Schedule
                                    </span>
                                </div>
                            </a>
                        </li>
                    @endcan

                    @can('employee-salaries.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('payroll-employee-salaries.*') ? 'active' : '' }}"
                                href="{{ route('payroll-employee-salaries.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="fas fa-money-bill-wave"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">
                                        Employee Rates
                                    </span>
                                </div>
                            </a>
                        </li>
                    @endcan
                @endcanany


                @can('holidays.view')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('holidays.*') ? 'active' : '' }}"
                            href="{{ route('holidays.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </span>
                                <span class="nav-link-text ms-2">
                                    Holiday Calendar
                                </span>
                            </div>
                        </a>
                    </li>
                @endcan


                {{-- Payroll Process --}}

                @canany(['payroll-attendance-adjustments.view', 'attendance-summary.view', 'payroll.view'])
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

                    @can('payroll-attendance-adjustments.view')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('payroll-attendance-adjustments.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="fas fa-edit"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">
                                        Adjustment
                                    </span>
                                </div>
                            </a>
                        </li>
                    @endcan

                    @can('attendance-summary.view')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('attendance-summary.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="fas fa-clipboard-list"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">
                                        Attendance Summary
                                    </span>
                                </div>
                            </a>
                        </li>
                    @endcan

                    @can('payroll.view')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('payroll.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="fas fa-money-check-alt"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">
                                        Payroll
                                    </span>
                                </div>
                            </a>
                        </li>
                    @endcan
                @endcanany


                {{-- ======================================================= --}}
                {{-- ================= MAINTENANCE ================= --}}
                {{-- ======================================================= --}}

                @can('parts-out.view')
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

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('parts-out*') ? 'active' : '' }}"
                            href="{{ route('parts-out.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-tools"></span>
                                </span>
                                <span class="nav-link-text ps-1">
                                    Parts Issuance
                                </span>
                            </div>
                        </a>
                    </li>
                @endcan


                @can('buses.view')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('buses*') ? 'active' : '' }}"
                            href="{{ route('buses.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-bus"></span>
                                </span>
                                <span class="nav-link-text ps-1">
                                    Vehicle History
                                </span>
                            </div>
                        </a>
                    </li>
                @endcan


                @can('allbus.view')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('allbus*') ? 'active' : '' }}"
                            href="{{ route('allbus.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-bus"></span>
                                </span>
                                <span class="nav-link-text ps-1">
                                    Bus List
                                </span>
                            </div>
                        </a>
                    </li>
                @endcan



                @canany(['receivings.view', 'stock-transfers.view'])
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

                    @can('receivings.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('receivings*') ? 'active' : '' }}"
                                href="{{ route('receivings.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="fas fa-truck-loading"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">
                                        Receiving Area
                                    </span>
                                </div>
                            </a>
                        </li>
                    @endcan


                    @can('stock-transfers.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('stock-transfers*') ? 'active' : '' }}"
                                href="{{ route('stock-transfers.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="fas fa-exchange-alt"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">
                                        Stock Transfer
                                    </span>
                                </div>
                            </a>
                        </li>
                    @endcan
                @endcanany



                {{-- ===================================================== --}}
                {{-- ================= Products ================= --}}
                {{-- ===================================================== --}}

                @canany(['category.view', 'items.view'])
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


                    @can('category.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('category*') ? 'active' : '' }}"
                                href="{{ route('category.index') }}">

                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="far fa-list-alt"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">
                                        Categories
                                    </span>
                                </div>
                            </a>
                        </li>
                    @endcan


                    @can('items.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('items*') ? 'active' : '' }}"
                                href="{{ route('items.index') }}">

                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="fas fa-toolbox"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">
                                        Products
                                    </span>
                                </div>
                            </a>
                        </li>
                    @endcan
                @endcanany



                {{-- ========================================================== --}}
                {{-- ================= Authentication ================= --}}
                {{-- ========================================================== --}}

                @canany(['users.view', 'roles.view'])
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


                    @can('users.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('authentication/users*') ? 'active' : '' }}"
                                href="{{ route('authentication.users.index') }}">

                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="fas fa-user-shield"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">
                                        Users
                                    </span>
                                </div>
                            </a>
                        </li>
                    @endcan


                    @can('roles.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('authentication/roles*') ? 'active' : '' }}"
                                href="{{ route('roles.index') }}">

                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="fas fa-user-secret"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">
                                        Roles
                                    </span>
                                </div>
                            </a>
                        </li>
                    @endcan
                @endcanany
            </ul>
        </div>
    </div>

</nav>
