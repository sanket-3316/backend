<!DOCTYPE html>
<html>

<head>
    <title>Admin Panel</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* =====================================
        THEME VARIABLES
        ===================================== */
        :root {
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #1e293b;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        /* 🌙 DARK MODE */
        body.dark {
            --bg: #0f172a;
            --card: #020617;
            --text: #e2e8f0;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.6);
        }

        /* =====================================
        GLOBAL
        ===================================== */
        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: "Segoe UI", sans-serif;
            transition: 0.3s;
        }

        /* =====================================
        NAVBAR
        ===================================== */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: var(--card);
            box-shadow: var(--shadow);
            z-index: 1100;
        }

        /* =====================================
        SIDEBAR
        ===================================== */
        .sidebar {
            position: fixed;
            top: 60px;
            left: 0;
            width: 240px;
            height: 100%;
            background: var(--card);
            box-shadow: var(--shadow);
            transition: 0.3s;
            overflow-y: auto;
        }

        /* MINI SIDEBAR */
        .sidebar.collapsed {
            width: 80px;
        }

        /* HIDDEN (DESKTOP) */
        .sidebar.hidden {
            left: -240px;
        }

        /* =====================================
        MENU
        ===================================== */
        .menu-item {
            position: relative;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            color: var(--text);
            text-decoration: none;
            transition: 0.2s;
        }

        .sidebar a:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        body.dark .sidebar a:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        /* SUBMENU NORMAL */
        .submenu {
            display: none;
            padding-left: 40px;
        }

        .submenu a {
            padding: 8px 10px;
            display: block;
        }

        /* =====================================
        MINI SIDEBAR POPUP
        ===================================== */
        .submenu-popup {
            position: fixed;
            left: 80px;
            background: var(--card);
            min-width: 200px;
            padding: 10px;
            box-shadow: var(--shadow);
            border-radius: 10px;
            display: none;
            z-index: 2000;
        }

        .sidebar.collapsed .menu-item:hover .submenu-popup {
            display: block;
        }

        /* =====================================
            CONTENT
            ===================================== */
        .content {
            margin-left: 240px;
            margin-top: 60px;
            padding: 20px;
            transition: 0.3s;
        }

        /* MINI SIDEBAR FIX */
        .sidebar.collapsed~.content {
            margin-left: 80px;
        }

        /* SIDEBAR HIDDEN */
        .sidebar.hidden~.content {
            margin-left: 0;
        }

        /* =====================================
            SETTINGS PANEL
            ===================================== */
        .settings {
            position: fixed;
            right: -300px;
            top: 0;
            width: 300px;
            height: 100%;
            background: var(--card);
            padding: 20px;
            box-shadow: var(--shadow);
            transition: 0.3s;
            z-index: 1200;
        }

        .settings.open {
            right: 0;
        }

        /* =====================================
        OVERLAY (MOBILE)
        ===================================== */
        #overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 1040;
        }

        #overlay.show {
            display: block;
        }

        /* =====================================
        MOBILE
        ===================================== */
        @media (max-width: 991px) {

            #openSettings{
                display: none;
            }
            .sidebar {
                left: -240px;
            }

            .sidebar.show {
                left: 0;
                z-index: 9999;
            }

            .content {
                margin-left: 0 !important;
            }

            .sidebar.collapsed {
                width: 240px;
                /* full on mobile */
            }
        }
    </style>
</head>

<body>


    <!-- 🔥 NAVBAR -->
    <nav class="navbar px-3 d-flex align-items-center">

        <!-- LOGO -->
        <h5 class="mb-0">Palmer Cruz</h5>

        <!-- TOGGLE BUTTON -->
        <button id="toggleSidebar" class="btn btn-light btn-sm ms-3">
            <i class="bi bi-list"></i>
        </button>

        <!-- RIGHT SIDE -->
        <div class="ms-auto d-flex align-items-center gap-3">

            <!-- USER -->
            <div class="dropdown">
                <i class="bi bi-person-circle fs-5 dropdown-toggle" data-bs-toggle="dropdown"></i>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li class="dropdown-item">Welcome 👋</li>
                </ul>
            </div>

            <!-- SETTINGS -->
            <button id="openSettings" class="btn btn-light btn-sm">
                <i class="bi bi-gear"></i>
            </button>

        </div>

    </nav>

    <!-- 🔥 SIDEBAR -->
    <div id="sidebar" class="sidebar">

        <!-- SIMPLE MENU -->
        <a href="#">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>

        <!-- PARENT MENU -->
        <div class="menu-item">

            <a href="#" class="menu-toggle">
                <i class="bi bi-bar-chart"></i>
                <span>Reports</span>
                <i class="bi bi-chevron-down ms-auto"></i>
            </a>

            <div class="submenu">
                <a href="#">Reports</a>
                <a href="#">Redirect</a>
                <a href="#">Automation</a>
            </div>

            <div class="submenu-popup"></div>

        </div>

        <!-- ANOTHER MENU -->
        <div class="menu-item">

            <a href="#" class="menu-toggle">
                <i class="bi bi-gear"></i>
                <span>Settings</span>
                <i class="bi bi-chevron-down ms-auto"></i>
            </a>

            <div class="submenu">
                <a href="#">Profile</a>
                <a href="#">Security</a>
            </div>

            <div class="submenu-popup"></div>

        </div>

    </div>

    <!-- 🔥 OVERLAY -->
    <div id="overlay"></div>

    <!-- 🔥 CONTENT -->
    <div id="content" class="content">
        <h2>Dashboard Content</h2>
        <p>Your main content here...</p>
    </div>

    <!-- 🔥 SETTINGS PANEL -->
    <div id="settings" class="settings">

        <div class="d-flex justify-content-between">
            <h5>Settings</h5>
            <button id="closeSettings">✖</button>
        </div>

        <hr>

        <!-- DARK MODE -->
        <label>
            <input type="checkbox" id="darkMode"> Dark Mode
        </label>

        <br><br>

        <!-- MINI SIDEBAR -->
        <label>
            <input type="checkbox" id="miniSidebar"> Mini Sidebar
        </label>

        <br><br>

        <!-- RESET -->
        <button id="resetSettings" class="btn btn-danger btn-sm">
            Reset
        </button>

    </div>


    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(function() {

            /* ================================
               LOAD SAVED SETTINGS
            ================================= */
            let isMini = localStorage.getItem("mini") === "true";
            let isDark = localStorage.getItem("dark") === "true";

            if (isDark) {
                $("body").addClass("dark");
                $("#darkMode").prop("checked", true);
            }

            if (isMini) {
                $("#sidebar").addClass("collapsed");
                $(".sidebar span").hide();
                $("#miniSidebar").prop("checked", true);
            }

            /* ================================
               SIDEBAR TOGGLE (DESKTOP + MOBILE)
            ================================= */
            $("#toggleSidebar").click(function() {

                let isMobile = $(window).width() <= 991;

                if (isMobile) {
                    $("#sidebar").toggleClass("show");
                    $("#overlay").toggleClass("show");
                } else {
                    $("#sidebar").toggleClass("hidden");
                }

            });

            /* ================================
               OVERLAY CLICK (MOBILE)
            ================================= */
            $("#overlay").click(function() {
                $("#sidebar").removeClass("show");
                $(this).removeClass("show");
            });

            /* ================================
               SETTINGS PANEL
            ================================= */
            $("#openSettings").click(() => $("#settings").addClass("open"));
            $("#closeSettings").click(() => $("#settings").removeClass("open"));

            /* ================================
               DARK MODE
            ================================= */
            $("#darkMode").change(function() {
                let enabled = $(this).is(":checked");
                $("body").toggleClass("dark", enabled);
                localStorage.setItem("dark", enabled);
            });

            /* ================================
               MINI SIDEBAR
            ================================= */
            $("#miniSidebar").change(function() {

                let enabled = $(this).is(":checked");

                $("#sidebar").toggleClass("collapsed", enabled);

                if (enabled) {
                    $(".sidebar span").hide();
                } else {
                    $(".sidebar span").show();
                }

                localStorage.setItem("mini", enabled);
            });

            /* ================================
               SUBMENU CLICK (DESKTOP ONLY)
            ================================= */
            $(document).on("click", ".menu-toggle", function(e) {

                if ($("#sidebar").hasClass("collapsed")) return;

                e.preventDefault();

                let parent = $(this).closest(".menu-item");
                let submenu = parent.find(".submenu");

                $(".submenu").not(submenu).slideUp();
                submenu.slideToggle();
            });

            /* ================================
               MINI SIDEBAR POPUP (HOVER)
            ================================= */
            $(".menu-item").hover(function() {

                if (!$("#sidebar").hasClass("collapsed")) return;

                let submenu = $(this).find(".submenu");
                let popup = $(this).find(".submenu-popup");

                if (!submenu.length) return;

                popup.html(submenu.html());

                let rect = this.getBoundingClientRect();

                popup.css({
                    top: rect.top + "px",
                    left: "80px"
                });

                popup.show();

            }, function() {
                $(this).find(".submenu-popup").hide();
            });

            /* ================================
               RESET SETTINGS
            ================================= */
            $("#resetSettings").click(function() {
                localStorage.clear();
                location.reload();
            });

        });
    </script>

</body>

</html>
