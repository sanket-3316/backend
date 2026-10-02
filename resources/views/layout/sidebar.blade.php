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
            <a class="hover-bg-primary rounded-1" href="{{ url('/prompts') }}">
                <i class="fa-solid fa-comment-dots me-2"></i> Prompts
            </a>
            <a class="hover-bg-primary rounded-1" href="{{ url('/report/recycle-bin') }}">
                <i class="fa-solid fa-trash-can me-2"></i> Recycle Bin
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
    <a href="{{ url('leads') }}" class="hover-bg-primary rounded-1 d-flex align-items-center">
        <i class="fa-solid fa-user-tag"></i>
        <span>Leads</span>
    </a>

    <!-- SIMPLE MENU -->
    <a href="{{ url('contact-messages') }}" class="hover-bg-primary rounded-1 d-flex align-items-center">
        <i class="fa-solid fa-address-card"></i>
        <span>Contact Us</span>
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
            <a class="hover-bg-primary rounded-1" href="{{ url('/profile') }}">
                <i class="fa-solid fa-user me-2"></i> Profile
            </a>
            <a class="hover-bg-primary rounded-1" href="{{ url('/report-price') }}">
                <i class="fa-solid fa-tags me-2"></i> Report Price
            </a>
            @if(in_array(session('user')->role ?? 0, [1, 2]))
            <a class="hover-bg-primary rounded-1" href="{{ url('/settings/api-key') }}">
                <i class="fa-solid fa-key me-2"></i> API Key
            </a>
            <a class="hover-bg-primary rounded-1" href="{{ url('/settings/contact-details') }}">
                <i class="fa-solid fa-address-book me-2"></i> Contact Details
            </a>
            @endif
        </div>

        <div class="submenu-popup"></div>

    </div>

    <!-- USER DASHBOARD / LOGIN LOGS — admin only (role 1: Super Admin, role 2: Admin) -->
    @if(in_array(session('user')->role ?? 0, [1, 2]))
    <a href="{{ url('/users/dashboard') }}" class="hover-bg-primary rounded-1 d-flex align-items-center">
        <i class="fa-solid fa-users-gear"></i>
        <span>User Dashboard</span>
    </a>
    <a href="{{ url('/logs') }}" class="hover-bg-primary rounded-1 d-flex align-items-center">
        <i class="fa-solid fa-clock-rotate-left"></i>
        <span>Login Logs</span>
    </a>
    @endif

</div>

<div id="overlay"></div>
