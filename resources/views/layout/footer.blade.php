<script src="{{ url('asset/js/mdb.min.js') }}"></script>
<script src="{{ url('asset/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ url('asset/js/sweetalert2@11.js') }}"></script>

<!-- TinyMCE FIRST -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js"></script>

<!-- Then your custom -->
<script src="{{ url('asset/js/custom.js') }}"></script>
<script>
    $(function() {

        /* ================================
           LOAD SAVED SETTINGS
        ================================= */
        let isMini = localStorage.getItem("mini") === "true";
        let isDark = localStorage.getItem("dark") === "true";

        if (isDark) {
            $("html")
                .attr("data-bs-theme", "dark")
                .attr("data-mdb-theme", "dark");

            $("body").addClass("dark");
            $("#darkMode").prop("checked", true);
        } else {
            $("html")
                .attr("data-bs-theme", "light")
                .attr("data-mdb-theme", "light");
        }
        /* ================================
           APPLY MINI SIDEBAR ON LOAD
        ================================= */
        if (isMini) {
            $("#sidebar").addClass("collapsed");
            $(".sidebar span").hide();
            $("#miniSidebar").prop("checked", true);
        } else {
            $("#sidebar").removeClass("collapsed");
            $(".sidebar span").show();
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

            if (enabled) {
                $("html")
                    .attr("data-bs-theme", "dark")
                    .attr("data-mdb-theme", "dark");

                $("body").addClass("dark");
            } else {
                $("html")
                    .attr("data-bs-theme", "light")
                    .attr("data-mdb-theme", "light");

                $("body").removeClass("dark");
            }

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
