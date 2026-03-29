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


                
                
                
                <li class="nav-item">
                    <a class="nav-link dropdown-indicator d-flex justify-content-between align-items-center"
                        href="#dashboardMenu" data-bs-toggle="collapse"
                        aria-expanded="<?php echo e(request()->is('dashboard*') ? 'true' : 'false'); ?>"
                        aria-controls="dashboardMenu">

                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-chart-pie"></span>
                            </span>
                            <span class="nav-link-text ps-1">Dashboard</span>
                        </div>
                    </a>

                    <ul class="nav collapse <?php echo e(request()->is('dashboard*') ? 'show' : ''); ?>" id="dashboardMenu">

                        <li class="nav-item">
                            <a class="nav-link <?php echo e(request()->is('dashboard') ? 'active' : ''); ?>"
                                href="<?php echo e(route('dashboard.index')); ?>">
                                Default
                            </a>
                        </li>
                        <?php if (\Illuminate\Support\Facades\Blade::check('role', 'Developer', 'IT Head', 'Operation Manager')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->is('dashboard/hr') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('hr.dashboard')); ?>">
                                    Employees
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if (\Illuminate\Support\Facades\Blade::check('role', 'Developer', 'IT Head')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->is('dashboard/it-department') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('dashboard.itindex')); ?>">
                                    IT Department
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if (\Illuminate\Support\Facades\Blade::check('role', 'Developer', 'IT Head', 'Operation Manager', 'Maintenance Head', 'Maintenance Engineer')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->is('maintenance/items/dashboard') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('items.dashboard')); ?>">
                                    Maintenance Stock
                                </a>
                            </li>
                        <?php endif; ?>

                    </ul>
                </li>


                
                
                

                <?php if (\Illuminate\Support\Facades\Blade::check('role', 'Developer', 'IT Head', 'IT Officer', 'Operation Manager')): ?>
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
                        <a class="nav-link <?php echo e(request()->is('tickets/job-order') ? 'active' : ''); ?>"
                            href="<?php echo e(route('tickets.joborder.index')); ?>">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-ticket-alt"></span>
                                </span>
                                <span class="nav-link-text ps-1">Tickets Job Order</span>
                            </div>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->is('tickets/cctv') ? 'active' : ''); ?>"
                            href="<?php echo e(route('concern.cctv.index')); ?>">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-video"></span>
                                </span>
                                <span class="nav-link-text ps-1">CCTV Concern</span>
                            </div>
                        </a>
                    </li>
                <?php endif; ?>


                
                
                

                <?php if (\Illuminate\Support\Facades\Blade::check('role', 'Developer', 'IT Head', 'HR Head', 'HR Officer')): ?>
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
                        <a class="nav-link dropdown-indicator <?php echo e(request()->is('employees*') ? 'active' : ''); ?>"
                            href="#employeesMenu" role="button" data-bs-toggle="collapse"
                            aria-expanded="<?php echo e(request()->is('employees*') ? 'true' : 'false'); ?>">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-users"></span>
                                </span>
                                <span class="nav-link-text ps-1">Employees</span>
                            </div>
                        </a>

                        <ul class="nav collapse <?php echo e(request()->is('employees*') ? 'show' : ''); ?>" id="employeesMenu">

                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->is('employees') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('employees.staff.index')); ?>">
                                    <span class="nav-link-text">Employee List</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->is('employees/departments*') || request()->is('employees/positions*') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('employees.departments.index')); ?>">
                                    <span class="nav-link-text">Department & Position</span>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->is('employees/offenses*') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('violation.offenses.index')); ?>">
                                    <span class="nav-link-text">HR Offenses</span>
                                </a>
                            </li>

                        </ul>
                    </li>
                    
                    
                    

                    <li class="nav-item">
                        <a class="nav-link dropdown-indicator <?php echo e(request()->is('claims*') ? 'active' : ''); ?>"
                            href="#benefitsMenu" role="button" data-bs-toggle="collapse"
                            aria-expanded="<?php echo e(request()->is('claims*') ? 'true' : 'false'); ?>">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-hand-holding-heart"></span>
                                </span>
                                <span class="nav-link-text ps-1">Benefits</span>
                            </div>
                        </a>

                        <ul class="nav collapse <?php echo e(request()->is('claims*') ? 'show' : ''); ?>" id="benefitsMenu">

                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->is('claims*') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('claims.index')); ?>">
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
                <?php endif; ?>


                
                
                

                <?php if (\Illuminate\Support\Facades\Blade::check('role', 'Developer', 'IT Head', 'IT Officer', 'HR Head', 'HR Officer')): ?>
                    <li class="nav-item">
                        <a class="nav-link dropdown-indicator <?php echo e(request()->is('attendance*') || request()->is('leave*') ? 'active' : ''); ?>"
                            href="#attendanceMenu" role="button" data-bs-toggle="collapse"
                            aria-expanded="<?php echo e(request()->is('attendance*') || request()->is('leave*') ? 'true' : 'false'); ?>">

                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-calendar-check"></span>
                                </span>
                                <span class="nav-link-text ps-1">Attendance</span>
                            </div>
                        </a>

                        <ul class="nav collapse <?php echo e(request()->is('attendance*') || request()->is('leave*') ? 'show' : ''); ?>"
                            id="attendanceMenu">
                            <li class="nav-item">
                                <a class="nav-link dropdown-indicator <?php echo e(request()->is('leave*') ? 'active' : ''); ?>"
                                    href="#leaveMenu" role="button" data-bs-toggle="collapse"
                                    aria-expanded="<?php echo e(request()->is('leave*') ? 'true' : 'false'); ?>">
                                    <span class="nav-link-text">Leaves</span>
                                </a>

                                <ul class="nav collapse <?php echo e(request()->is('leave*') ? 'show' : ''); ?>" id="leaveMenu">

                                    <li class="nav-item">
                                        <a class="nav-link <?php echo e(request()->is('leave/employee') ? 'active' : ''); ?>"
                                            href="<?php echo e(route('employee-leave.employee.index')); ?>">
                                            <span class="nav-link-text">Employee</span>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a class="nav-link <?php echo e(request()->is('leave/driver') ? 'active' : ''); ?>"
                                            href="<?php echo e(route('driver-leave.driver.index')); ?>">
                                            <span class="nav-link-text">Driver</span>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a class="nav-link <?php echo e(request()->is('leave/conductor') ? 'active' : ''); ?>"
                                            href="<?php echo e(route('conductor-leave.conductor.index')); ?>">
                                            <span class="nav-link-text">Conductor</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (\Illuminate\Support\Facades\Blade::check('role', 'Developer', 'IT Head')): ?>
                    
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
                        <a class="nav-link <?php echo e(request()->is('payroll-plotting*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('payroll-plotting.index')); ?>">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-calendar-alt"></span>
                                </span>
                                <span class="nav-link-text ps-1">Work Schedule</span>
                            </div>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->routeIs('payroll-employee-salaries.*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('payroll-employee-salaries.index')); ?>">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-money-bill-wave"></span>
                                </span>
                                <span class="nav-link-text ps-1">Employee Rates</span>
                            </div>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->routeIs('holidays.*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('holidays.index')); ?>">

                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </span>
                                <span class="nav-link-text ms-2">Holiday Calendar</span>
                            </div>
                        </a>
                    </li>

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
                        <a class="nav-link <?php echo e(request()->routeIs('mirasol-logs.*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('mirasol-logs.index')); ?>">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-fingerprint"></span>
                                </span>
                                <span class="nav-link-text ps-1">Mirasol Biometrics</span>
                            </div>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->routeIs('manual-biometrics.*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('manual-biometrics.index')); ?>">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-keyboard"></span>
                                </span>
                                <span class="nav-link-text ps-1">Manual Biometrics</span>
                            </div>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->routeIs('payroll-attendance-adjustments.*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('payroll-attendance-adjustments.index')); ?>">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-edit"></span>
                                </span>
                                <span class="nav-link-text ps-1">Adjustment</span>
                            </div>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->routeIs('attendance-summary.*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('attendance-summary.index')); ?>">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-clipboard-list"></span>
                                </span>
                                <span class="nav-link-text ps-1">Attendance Summary</span>
                            </div>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->routeIs('payroll.*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('payroll.index')); ?>">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-money-check-alt"></span>
                                </span>
                                <span class="nav-link-text ps-1">Payroll</span>
                            </div>
                        </a>
                    </li>
                <?php endif; ?>


                
                
                
                <?php if (\Illuminate\Support\Facades\Blade::check('role', 'Developer', 'IT Head', 'Maintenance Head', 'Maintenance Engineer')): ?>
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
                        <a class="nav-link <?php echo e(request()->is('parts-out*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('parts-out.index')); ?>">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-tools"></span>
                                </span>
                                <span class="nav-link-text ps-1">Parts Issuance</span>
                            </div>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->is('buses*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('buses.index')); ?>">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-bus"></span>
                                </span>
                                <span class="nav-link-text ps-1">Vehicle History</span>
                            </div>
                        </a>
                    </li>
                    
                    
                    

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

                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->is('receivings*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('receivings.index')); ?>">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-truck-loading"></span>
                                </span>
                                <span class="nav-link-text ps-1">Receiving Area</span>
                            </div>
                        </a>
                    </li>

                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->is('stock-transfers*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('stock-transfers.index')); ?>">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-exchange-alt"></span>
                                </span>
                                <span class="nav-link-text ps-1">Stock Transfer</span>
                            </div>
                        </a>
                    </li>
                <?php endif; ?>

                
                
                

                <?php if (\Illuminate\Support\Facades\Blade::check('role', 'Developer', 'IT Head', 'Maintenance Head', 'Maintenance Engineer')): ?>
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

                    

                    
                    
                    

                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->is('category*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('category.index')); ?>">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="far fa-list-alt"></span>
                                </span>
                                <span class="nav-link-text ps-1">Categories</span>
                            </div>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->is('items*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('items.index')); ?>">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-toolbox"></span>
                                </span>
                                <span class="nav-link-text ps-1">Products</span>
                            </div>
                        </a>
                    </li>
                    
                    
                    

                    
                <?php endif; ?>


                
                
                

                <?php if (\Illuminate\Support\Facades\Blade::check('role', 'Developer', 'IT Head')): ?>
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
                        <a class="nav-link <?php echo e(request()->is('authentication/users*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('authentication.users.index')); ?>">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-user-shield"></span>
                                </span>
                                <span class="nav-link-text ps-1">Users</span>
                            </div>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->is('authentication/roles*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('roles.index')); ?>">
                            <div class="d-flex align-items-center">
                                <span class="nav-link-icon">
                                    <span class="fas fa-user-secret"></span>
                                </span>
                                <span class="nav-link-text ps-1">Roles</span>
                            </div>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

</nav>
<?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/layouts/sidebar.blade.php ENDPATH**/ ?>