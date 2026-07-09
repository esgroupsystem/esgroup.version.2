{{-- resources/views/layouts/partials/sidebar.blade.php --}}

@php
    use Illuminate\Support\Facades\Route;

    $safeRoute = static function (string $name): string {
        return Route::has($name) ? route($name) : '#';
    };

    $isDashboardActive =
        request()->routeIs([
            'dashboard.*',
            'dashboard',
            'chairman.*',
            'items.dashboard',
            'odometer.*',
            'fleet.buses.*',
        ]) ||
        request()->is(['dashboard*', 'chairman/hr-data*', 'maintenance/items/dashboard*', 'odometer*', 'fleet/buses*']);

    $isEmployeesActive = request()->routeIs(['employees.*', 'violation.*']) || request()->is(['employees*']);

    $isBenefitsActive = request()->routeIs('claims.*') || request()->is('claims*');

    $isLeavesActive = request()->routeIs(['employee-leave.*', 'driver-leave.*', 'conductor-leave.*']);

    $isPayrollProcessActive = request()->routeIs([
        'payroll-attendance-adjustments.*',
        'attendance-summary.*',
        'payroll.*',
    ]);
@endphp

<nav class="navbar navbar-light navbar-vertical navbar-expand-xl px-4 px-lg-1">
    <div class="d-flex align-items-center">
        <div class="toggle-icon-wrapper">
            <button class="btn navbar-toggler-humburger-icon navbar-vertical-toggle" type="button"
                data-bs-toggle="tooltip" data-bs-placement="left" title="Toggle Navigation">
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
                {{-- ================= DASHBOARD =================== --}}
                {{-- =============================================== --}}

                <li class="nav-item">
                    <a class="nav-link dropdown-indicator {{ $isDashboardActive ? 'active' : '' }}"
                        href="#dashboardMenu" role="button" data-bs-toggle="collapse"
                        aria-expanded="{{ $isDashboardActive ? 'true' : 'false' }}" aria-controls="dashboardMenu">

                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-chart-pie"></span>
                            </span>
                            <span class="nav-link-text ps-1">Dashboard</span>
                        </div>
                    </a>

                    <ul class="nav collapse {{ $isDashboardActive ? 'show' : '' }}" id="dashboardMenu">
                        @can('dashboard.view')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('dashboard.index') || request()->is('dashboard') ? 'active' : '' }}"
                                    href="{{ $safeRoute('dashboard.index') }}">
                                    Default
                                </a>
                            </li>
                        @endcan

                        @can('chairman.view')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('chairman.hr-data.index') || request()->is('chairman/hr-data*') ? 'active' : '' }}"
                                    href="{{ $safeRoute('chairman.hr-data.index') }}">
                                    All Data
                                </a>
                            </li>
                        @endcan

                        @can('hr-dashboard.view')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('hr.dashboard') || request()->is('dashboard/hr') ? 'active' : '' }}"
                                    href="{{ $safeRoute('hr.dashboard') }}">
                                    Employees
                                </a>
                            </li>
                        @endcan

                        @can('dashboard.it')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('dashboard.itindex') || request()->is('dashboard/it-department') ? 'active' : '' }}"
                                    href="{{ $safeRoute('dashboard.itindex') }}">
                                    IT Department
                                </a>
                            </li>
                        @endcan

                        @can('items.view')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('items.dashboard') || request()->is('maintenance/items/dashboard') ? 'active' : '' }}"
                                    href="{{ $safeRoute('items.dashboard') }}">
                                    Maintenance Stock
                                </a>
                            </li>
                        @endcan

                        @can('odometer.view')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('odometer.*') || request()->is('odometer*') ? 'active' : '' }}"
                                    href="{{ $safeRoute('odometer.index') }}">
                                    Odometer Monitoring
                                </a>
                            </li>
                        @endcan

                        @can('fleet.view')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('fleet.buses.*') || request()->is('fleet/buses*') ? 'active' : '' }}"
                                    href="{{ $safeRoute('fleet.buses.index') }}">
                                    Bus Analytics
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>

                {{-- =============================================== --}}
                {{-- =============== IT DEPARTMENT ================= --}}
                {{-- =============================================== --}}

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

                    @can('cctv.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('concern.bus-status') ? 'active' : '' }}"
                                href="{{ $safeRoute('concern.bus-status') }}">
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
                            <a class="nav-link {{ request()->routeIs('tickets.joborder.*') || request()->is('tickets/job-order*') ? 'active' : '' }}"
                                href="{{ $safeRoute('tickets.joborder.index') }}">
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
                            <a class="nav-link {{ request()->routeIs('concern.cctv.*') || request()->is('tickets/cctv*') ? 'active' : '' }}"
                                href="{{ $safeRoute('concern.cctv.index') }}">
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
                                href="{{ $safeRoute('it-inventory.index') }}">
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
                {{-- ================= HR DEPARTMENT =============== --}}
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
                        <a class="nav-link dropdown-indicator {{ $isEmployeesActive ? 'active' : '' }}"
                            href="#employeesMenu" role="button" data-bs-toggle="collapse"
                            aria-expanded="{{ $isEmployeesActive ? 'true' : 'false' }}" aria-controls="employeesMenu">

                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-users"></span>
                                </span>
                                <span class="nav-link-text ps-1">Employees</span>
                            </div>
                        </a>

                        <ul class="nav collapse {{ $isEmployeesActive ? 'show' : '' }}" id="employeesMenu">
                            @can('employees.view')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('employees.staff.*') || request()->is('employees') ? 'active' : '' }}"
                                        href="{{ $safeRoute('employees.staff.index') }}">
                                        <span class="nav-link-text">Employee List</span>
                                    </a>
                                </li>
                            @endcan

                            @can('departments.view')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('employees.departments.*') || request()->is('employees/departments*', 'employees/positions*') ? 'active' : '' }}"
                                        href="{{ $safeRoute('employees.departments.index') }}">
                                        <span class="nav-link-text">Department & Position</span>
                                    </a>
                                </li>
                            @endcan

                            @can('violations.view')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('violation.offenses.*') || request()->is('employees/offenses*') ? 'active' : '' }}"
                                        href="{{ $safeRoute('violation.offenses.index') }}">
                                        <span class="nav-link-text">HR Offenses</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany

                {{-- =============================================== --}}
                {{-- ================= BENEFITS ==================== --}}
                {{-- =============================================== --}}

                @can('claims.view')
                    <li class="nav-item">
                        <a class="nav-link dropdown-indicator {{ $isBenefitsActive ? 'active' : '' }}"
                            href="#benefitsMenu" role="button" data-bs-toggle="collapse"
                            aria-expanded="{{ $isBenefitsActive ? 'true' : 'false' }}" aria-controls="benefitsMenu">

                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-hand-holding-heart"></span>
                                </span>
                                <span class="nav-link-text ps-1">Benefits</span>
                            </div>
                        </a>

                        <ul class="nav collapse {{ $isBenefitsActive ? 'show' : '' }}" id="benefitsMenu">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('claims.*') || request()->is('claims*') ? 'active' : '' }}"
                                    href="{{ $safeRoute('claims.index') }}">
                                    <span class="nav-link-text">SSS / Maternity / Paternity</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link disabled text-muted" href="#" tabindex="-1"
                                    aria-disabled="true">
                                    <span class="nav-link-text">Cash Advance (Coming Soon)</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endcan

                {{-- =============================================== --}}
                {{-- ================= LEAVES ====================== --}}
                {{-- =============================================== --}}

                @canany(['employee-leave.view', 'driver-leave.view', 'conductor-leave.view'])
                    <li class="nav-item">
                        <a class="nav-link dropdown-indicator {{ $isLeavesActive ? 'active' : '' }}" href="#leavesMenu"
                            role="button" data-bs-toggle="collapse"
                            aria-expanded="{{ $isLeavesActive ? 'true' : 'false' }}" aria-controls="leavesMenu">

                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-calendar-alt"></span>
                                </span>
                                <span class="nav-link-text ps-1">Leaves</span>
                            </div>
                        </a>

                        <ul class="nav collapse {{ $isLeavesActive ? 'show' : '' }}" id="leavesMenu">
                            @can('employee-leave.view')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('employee-leave.*') ? 'active' : '' }}"
                                        href="{{ $safeRoute('employee-leave.employee.index') }}">
                                        <span class="nav-link-text ps-1">Admin</span>
                                    </a>
                                </li>
                            @endcan

                            @can('driver-leave.view')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('driver-leave.*') ? 'active' : '' }}"
                                        href="{{ $safeRoute('driver-leave.driver.index') }}">
                                        <span class="nav-link-text ps-1">Driver</span>
                                    </a>
                                </li>
                            @endcan

                            @can('conductor-leave.view')
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('conductor-leave.*') ? 'active' : '' }}"
                                        href="{{ $safeRoute('conductor-leave.conductor.index') }}">
                                        <span class="nav-link-text ps-1">Conductor</span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany

                {{-- =============================================== --}}
                {{-- ============ BIOMETRIC MANAGEMENT ============= --}}
                {{-- =============================================== --}}

                @canany(['mirasol-logs.view', 'manual-biometrics.view'])
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

                    @can('mirasol-logs.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('mirasol-logs.*') ? 'active' : '' }}"
                                href="{{ $safeRoute('mirasol-logs.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="fas fa-fingerprint"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">Mirasol Biometrics</span>
                                </div>
                            </a>
                        </li>
                    @endcan

                    @can('manual-biometrics.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('manual-biometrics.*') ? 'active' : '' }}"
                                href="{{ $safeRoute('manual-biometrics.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="fas fa-keyboard"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">Manual Biometrics</span>
                                </div>
                            </a>
                        </li>
                    @endcan
                @endcanany

                {{-- =============================================== --}}
                {{-- ========== EMPLOYEE SCHEDULE / RATES ========== --}}
                {{-- =============================================== --}}

                @canany(['biometrics.view', 'payroll-plotting.view', 'employee-salaries.view'])
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

                    @can('biometrics.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('biometrics.employees.*') ? 'active' : '' }}"
                                href="{{ $safeRoute('biometrics.employees.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="fas fa-fingerprint"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">Employees</span>
                                </div>
                            </a>
                        </li>
                    @endcan

                    @can('payroll-plotting.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('payroll-plotting.*') || request()->is('payroll-plotting*') ? 'active' : '' }}"
                                href="{{ $safeRoute('payroll-plotting.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="fas fa-calendar-alt"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">Work Schedule</span>
                                </div>
                            </a>
                        </li>
                    @endcan

                    @can('employee-salaries.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('payroll-employee-salaries.*') ? 'active' : '' }}"
                                href="{{ $safeRoute('payroll-employee-salaries.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="fas fa-money-bill-wave"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">Employee Rates</span>
                                </div>
                            </a>
                        </li>
                    @endcan

                    @can('holidays.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('holidays.*') ? 'active' : '' }}"
                                href="{{ $safeRoute('holidays.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </span>
                                    <span class="nav-link-text ps-1">Holiday Calendar</span>
                                </div>
                            </a>
                        </li>
                    @endcan
                @endcanany

                {{-- =============================================== --}}
                {{-- =============== PAYROLL PROCESS =============== --}}
                {{-- =============================================== --}}

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
                            <a class="nav-link {{ request()->routeIs('payroll-attendance-adjustments.*') ? 'active' : '' }}"
                                href="{{ $safeRoute('payroll-attendance-adjustments.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="fas fa-edit"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">Adjustment</span>
                                </div>
                            </a>
                        </li>
                    @endcan

                    @can('attendance-summary.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('attendance-summary.*') ? 'active' : '' }}"
                                href="{{ $safeRoute('attendance-summary.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="fas fa-clipboard-list"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">Summary</span>
                                </div>
                            </a>
                        </li>
                    @endcan

                    @can('payroll.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('payroll.*') ? 'active' : '' }}"
                                href="{{ $safeRoute('payroll.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="fas fa-money-check-alt"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">Payroll</span>
                                </div>
                            </a>
                        </li>
                    @endcan
                @endcanany

                {{-- =============================================== --}}
                {{-- ================ STOCK MOVEMENTS ============== --}}
                {{-- =============================================== --}}

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
                        <a class="nav-link {{ request()->routeIs('parts-out.*') || request()->is('parts-out*') ? 'active' : '' }}"
                            href="{{ $safeRoute('parts-out.index') }}">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-tools"></span>
                                </span>
                                <span class="nav-link-text ps-1">Parts Issuance</span>
                            </div>
                        </a>
                    </li>
                @endcan

                {{-- =============================================== --}}
                {{-- ================ MAINTENANCE ================== --}}
                {{-- =============================================== --}}

                @canany(['buses.view', 'allbus.view'])
                    @can('buses.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('maintenance.job-orders.*') ? 'active' : '' }}"
                                href="{{ $safeRoute('maintenance.job-orders.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="fas fa-clipboard-list"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">Maintenance Job Orders</span>
                                </div>
                            </a>
                        </li>
                    @endcan

                    @can('buses.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('buses.*') || request()->is('buses*') ? 'active' : '' }}"
                                href="{{ $safeRoute('buses.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="fas fa-bus"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">Vehicle History</span>
                                </div>
                            </a>
                        </li>
                    @endcan

                    @can('allbus.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('allbus.*') || request()->is('allbus*') ? 'active' : '' }}"
                                href="{{ $safeRoute('allbus.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="fas fa-bus"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">Bus List</span>
                                </div>
                            </a>
                        </li>
                    @endcan
                @endcanany

                {{-- =============================================== --}}
                {{-- ============= INVENTORY MANAGEMENT ============ --}}
                {{-- =============================================== --}}

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
                            <a class="nav-link {{ request()->routeIs('receivings.*') || request()->is('receivings*') ? 'active' : '' }}"
                                href="{{ $safeRoute('receivings.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="fas fa-truck-loading"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">Receiving Area</span>
                                </div>
                            </a>
                        </li>
                    @endcan

                    @can('stock-transfers.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('stock-transfers.*') || request()->is('stock-transfers*') ? 'active' : '' }}"
                                href="{{ $safeRoute('stock-transfers.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="fas fa-exchange-alt"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">Stock Transfer</span>
                                </div>
                            </a>
                        </li>
                    @endcan
                @endcanany

                {{-- =============================================== --}}
                {{-- ============= PRODUCTS MANAGEMENT ============= --}}
                {{-- =============================================== --}}

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
                            <a class="nav-link {{ request()->routeIs('category.*') || request()->is('category*') ? 'active' : '' }}"
                                href="{{ $safeRoute('category.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="far fa-list-alt"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">Categories</span>
                                </div>
                            </a>
                        </li>
                    @endcan

                    @can('items.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('items.*') || request()->is('items*') ? 'active' : '' }}"
                                href="{{ $safeRoute('items.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="fas fa-toolbox"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">Products</span>
                                </div>
                            </a>
                        </li>
                    @endcan
                @endcanany

                {{-- =============================================== --}}
                {{-- ================ AUTHENTICATION =============== --}}
                {{-- =============================================== --}}

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
                            <a class="nav-link {{ request()->routeIs('authentication.users.*') || request()->is('authentication/users*') ? 'active' : '' }}"
                                href="{{ $safeRoute('authentication.users.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="fas fa-user-shield"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">Users</span>
                                </div>
                            </a>
                        </li>
                    @endcan

                    @can('roles.view')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('roles.*') || request()->is('authentication/roles*') ? 'active' : '' }}"
                                href="{{ $safeRoute('roles.index') }}">
                                <div class="d-flex align-items-center">
                                    <span class="nav-link-icon">
                                        <span class="fas fa-user-secret"></span>
                                    </span>
                                    <span class="nav-link-text ps-1">Roles</span>
                                </div>
                            </a>
                        </li>
                    @endcan
                @endcanany

            </ul>
        </div>
    </div>
</nav>
