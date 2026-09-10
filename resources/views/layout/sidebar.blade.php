<div id="sidebar" class="sidebar">

    <!-- SIMPLE MENU -->
    <a href="{{ url('/dashboard') }}" class="hover-bg-primary rounded-1 d-flex align-items-center">
        <i class="fa-solid fa-gauge-high"></i>
        <span>Dashboard</span>
    </a>

    <!-- PARENT MENU -->
    <div class="menu-item">

        <a href="#" class="menu-toggle hover-bg-primary rounded-1 d-flex align-items-center">
            <i class="fa-solid fa-chart-column"></i>
            <span>Reports</span>
            <i class="fa-solid fa-chevron-down ms-auto"></i>
        </a>

        <div class="submenu">
            <a class="hover-bg-primary rounded-1" href="{{ url('/report/') }}">
                <i class="fa-solid fa-chart-line me-2"></i> Reports
            </a>
            <a class="hover-bg-primary rounded-1" href="{{ url('/redirect') }}">
                <i class="fa-solid fa-rotate-right me-2"></i> Redirect
            </a>
            <a class="hover-bg-primary rounded-1" href="{{ url('/keywords') }}">
                <i class="fa-solid fa-robot me-2"></i> Keywords
            </a>
        </div>

        <div class="submenu-popup"></div>

    </div>

    <!-- SIMPLE MENU -->
    <a href="{{ url('category') }}" class="hover-bg-primary rounded-1 d-flex align-items-center">
        <i class="fa-solid fa-gauge-high"></i>
        <span>Category</span>
    </a>

    <!-- SIMPLE MENU -->
    <a href="{{ url('career') }}" class="hover-bg-primary rounded-1 d-flex align-items-center">
        <i class="fa-solid fa-briefcase"></i>
        <span>Careers</span>
    </a>

    <!-- SETTINGS -->
    <div class="menu-item">

        <a href="#" class="menu-toggle hover-bg-primary rounded-1 d-flex align-items-center">
            <i class="fa-solid fa-gear"></i>
            <span>Settings</span>
            <i class="fa-solid fa-chevron-down ms-auto"></i>
        </a>

        <div class="submenu">
            <a class="hover-bg-primary rounded-1" href="#">
                <i class="fa-solid fa-user me-2"></i> Profile
            </a>
            <a class="hover-bg-primary rounded-1" href="{{ url('/report-price') }}">
                <i class="fa-solid fa-tags me-2"></i> Report Price
            </a>
        </div>

        <div class="submenu-popup"></div>

    </div>

    <!-- USER DASHBOARD — admin only (role 1: Super Admin, role 2: Admin) -->
    @if(in_array(session('user')->role ?? 0, [1, 2]))
    <a href="{{ url('/users/dashboard') }}" class="hover-bg-primary rounded-1 d-flex align-items-center">
        <i class="fa-solid fa-users-gear"></i>
        <span>User Dashboard</span>
    </a>
    @endif

</div>

<div id="overlay"></div>
