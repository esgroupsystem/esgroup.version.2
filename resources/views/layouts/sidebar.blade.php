<nav class="navbar navbar-light navbar-vertical navbar-expand-xl">

    <script>
        var navbarStyle = localStorage.getItem("navbarStyle");
        if (navbarStyle && navbarStyle !== 'transparent') {
            document.querySelector('.navbar-vertical').classList.add(`navbar-${navbarStyle}`);
        }
    </script>

    <div class="d-flex align-items-center ms-3 ms-sm-4">
        <div class="toggle-icon-wrapper">
            <button class="btn navbar-toggler-humburger-icon navbar-vertical-toggle"
                data-bs-toggle="tooltip" data-bs-placement="left" title="Toggle Navigation">
                <span class="navbar-toggle-icon">
                    <span class="toggle-line"></span>
                </span>
            </button>
        </div>

        <a class="navbar-brand" href="#">
            <div class="d-flex align-items-center py-3">
                <img class="me-2" src="../assets/img/icons/spot-illustrations/falcon.png" width="40">
                <span class="font-sans-serif text-primary">falcon</span>
            </div>
        </a>
    </div>

    <div class="collapse navbar-collapse" id="navbarVerticalCollapse">
        <div class="navbar-vertical-content scrollbar">
            <ul class="navbar-nav flex-column mb-3" id="navbarVerticalNav">


                <!-- ✅ DASHBOARD -->
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

                    <ul class="nav collapse {{ request()->is('dashboard*') ? 'show' : '' }}" id="dashboardMenu">

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
                            <a class="nav-link {{ request()->is('dashboard/support-desk') ? 'active' : '' }}"
                                href="#">
                                Support Desk
                                <span class="badge rounded-pill ms-2 badge-subtle-success">New</span>
                            </a>
                        </li>

                    </ul>
                </li>


                <!-- ✅ LABEL -->
                <li class="nav-item">
                    <div class="row navbar-vertical-label-wrapper mt-3 mb-2">
                        <div class="col-auto navbar-vertical-label">IT Department</div>
                        <div class="col ps-0">
                            <hr class="mb-0 navbar-vertical-divider">
                        </div>
                    </div>
                </li>


                <!-- ✅ IT MANAGEMENT -->
                <li class="nav-item">
                    <a class="nav-link dropdown-indicator d-flex justify-content-between align-items-center"
                    href="#itMenu"
                    data-bs-toggle="collapse"
                    aria-expanded="{{ request()->is('tickets/*') ? 'true' : 'false' }}"
                    aria-controls="itMenu">

                        <div class="d-flex align-items-center">
                            <span class="nav-link-icon">
                                <span class="fas fa-tools"></span>
                            </span>
                            <span class="nav-link-text ps-1">IT Management</span>
                        </div>

                    </a>

                    <ul class="nav collapse {{ request()->is('tickets/*') ? 'show' : '' }}" id="itMenu">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('tickets/job-order') ? 'active' : '' }}"
                               href="{{ route('tickets.joborder.index') }}">
                                Tickets Job Order
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('tickets/cctv') ? 'active' : '' }}"
                               href="{{ route('tickets.cctv.index') }}">
                                CCTV Concern
                            </a>
                        </li>
                    </ul>

                </li>

            </ul>
        </div>
    </div>
</nav>
