   <nav class="navbar px-3 d-flex align-items-center">

        <!-- LOGO -->
        <h5 class="mb-0">Palmer Cruz</h5>

        <!-- TOGGLE BUTTON -->
        <button id="toggleSidebar" class="btn btn-light btn-sm ms-3 hover-bg-primary">
          <i class="fas fa-align-justify"></i>
        </button>

        <!-- RIGHT SIDE -->
        <div class="ms-auto d-flex align-items-center gap-3">
            <!-- USER -->
            <div class="dropdown">
                <i class="far fa-circle-user dropdown-toggle" data-mdb-toggle="dropdown" aria-expanded="false" role="button"></i>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="dropdown-item-text">Welcome, {{ session('user')->name ?? 'Admin' }} 👋</li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item text-danger" href="{{ url('/logout') }}">
                            <i class="fas fa-right-from-bracket me-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>

            <!-- SETTINGS -->
            <button id="openSettings" class="btn-outline-custom btn-outline-primary-gradient btn-floating-sm">
                <i class="fas fa-gear"></i>
            </button>

        </div>

    </nav>