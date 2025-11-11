<nav class="navbar navbar-light navbar-glass navbar-top navbar-expand">

    {{-- Sidebar Toggle --}}
    <button class="btn navbar-toggler-humburger-icon navbar-toggler me-1 me-sm-3" type="button" data-bs-toggle="collapse"
        data-bs-target="#navbarVerticalCollapse" aria-controls="navbarVerticalCollapse" aria-expanded="false"
        aria-label="Toggle Navigation">
        <span class="navbar-toggle-icon">
            <span class="toggle-line"></span>
        </span>
    </button>

    {{-- BRAND --}}
    <a class="navbar-brand me-1 me-sm-3" href="{{ route('dashboard.index') }}">
        <div class="d-flex align-items-center">
            <span class="font-sans-serif text-primary">Jell Group</span>
        </div>
    </a>

    {{-- SEARCH BAR --}}
    <ul class="navbar-nav align-items-center d-none d-lg-block">
        <li class="nav-item">
            <div class="search-box" data-list='{"valueNames":["title"]}'>

                <form class="position-relative" data-bs-toggle="search" data-bs-display="static">
                    <input class="form-control search-input fuzzy-search" type="search" placeholder="Search..."
                        aria-label="Search">
                    <span class="fas fa-search search-box-icon"></span>
                </form>

                <div class="btn-close-falcon-container position-absolute end-0 top-50 translate-middle shadow-none"
                    data-bs-dismiss="search">
                    <button class="btn btn-link btn-close-falcon p-0" aria-label="Close"></button>
                </div>

                {{-- Search Results Container --}}
                <div class="dropdown-menu border font-base start-0 mt-2 py-0 overflow-hidden w-100">
                    <div class="scrollbar list py-3" style="max-height: 24rem;">
                        <h6 class="dropdown-header fw-medium text-uppercase px-x1 fs-11 pt-0 pb-2">No Search Data</h6>
                        <p class="px-3 mb-0 text-muted fs-10">You can integrate your own items here.</p>
                    </div>
                </div>

            </div>
        </li>
    </ul>

    <ul class="navbar-nav navbar-nav-icons ms-auto flex-row align-items-center">

        {{-- THEME SWITCH --}}
        <li class="nav-item ps-2 pe-0">
            <div class="dropdown theme-control-dropdown">
                <a class="nav-link d-flex align-items-center dropdown-toggle fs-9 pe-1 py-0" href="#"
                    id="themeSwitchDropdown" data-bs-toggle="dropdown">
                    <span class="fas fa-sun fs-7" data-theme-dropdown-toggle-icon="light"></span>
                    <span class="fas fa-moon fs-7" data-theme-dropdown-toggle-icon="dark"></span>
                    <span class="fas fa-adjust fs-7" data-theme-dropdown-toggle-icon="auto"></span>
                </a>

                <div class="dropdown-menu dropdown-menu-end dropdown-caret py-0 mt-3">
                    <div class="bg-white rounded-2 py-2">
                        <button class="dropdown-item d-flex align-items-center gap-2" type="button" value="light"
                            data-theme-control="theme">
                            <span class="fas fa-sun"></span> Light
                        </button>

                        <button class="dropdown-item d-flex align-items-center gap-2" type="button" value="dark"
                            data-theme-control="theme">
                            <span class="fas fa-moon"></span> Dark
                        </button>

                        <button class="dropdown-item d-flex align-items-center gap-2" type="button" value="auto"
                            data-theme-control="theme">
                            <span class="fas fa-adjust"></span> Auto
                        </button>
                    </div>
                </div>
            </div>
        </li>

        {{-- NOTIFICATION BELL --}}
        <li class="nav-item dropdown px-2">
            <a class="nav-link notification-indicator px-0" id="navbarDropdownNotification" data-bs-toggle="dropdown">

                <span class="fas fa-bell fs-7"></span>
            </a>

            <div class="dropdown-menu dropdown-menu-end dropdown-menu-card shadow">
                <div class="card card-notification">

                    <div class="card-header">
                        <h6 class="mb-0">Notifications</h6>
                    </div>
                </div>
            </div>
        </li>

        {{-- USER MENU --}}
        <li class="nav-item ps-2 pe-0">
            <div class="dropdown">
                <a class="nav-link d-flex align-items-center dropdown-toggle fs-9 pe-1 py-0" href="#"
                    id="userDropdownMenu" data-bs-toggle="dropdown">

                    <span class="fas fa-user-circle fs-7"></span>

                </a>

                <div class="dropdown-menu dropdown-menu-end dropdown-caret py-0 mt-3"
                    aria-labelledby="userDropdownMenu">

                    <div class="bg-white rounded-2 py-2">
                        <a class="dropdown-item d-flex align-items-center gap-2" href="#">
                            <span class="fas fa-user"></span> Profile
                        </a>

                        <a class="dropdown-item d-flex align-items-center gap-2" href="#"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <span class="fas fa-sign-out-alt"></span> Logout
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>

                </div>
            </div>
        </li>
    </ul>

</nav>
