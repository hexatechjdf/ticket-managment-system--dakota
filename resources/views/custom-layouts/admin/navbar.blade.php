<nav class="navbar top-navbar">
    <div class="container-fluid">
        <div class="d-flex align-items-center">
            <span class="nav-brand">
                <i class="bi bi-speedometer2"></i>
                <span>{{env('APP_NAME')}}</span>
            </span>
            <ul class="navbar-nav d-flex flex-row">
                <li class="nav-item me-2">
                    <a href="{{ route('agency.index') }}" class="nav-link @yield('select_agency')" data-section="dashboard">
                        <i class="bi bi-people"></i>
                        <span>Agencies</span>
                    </a>
                </li>
                <!-- <li class="nav-item me-2">
                        <a href="#" class="nav-link" data-section="tickets">
                            <i class="bi bi-ticket-perforated"></i>
                            <span>Tickets</span>
                        </a>
                    </li> -->
                <li class="nav-item me-2">
                    <a href="{{ route('setting.index') }}" class="nav-link @yield('select_setting')" style="visibility: hidden" data-section="settings">
                        <i class="bi bi-gear"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="user-info">
            <span class="text-white me-3 d-none d-md-inline">Welcome back, Admin!</span>

            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </button>
            </form>
        </div>
    </div>
</nav>
