<!DOCTYPE html>
<html data-mdb-theme="light">

<head>
    <title>Admin Panel</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Bootstrap -->
    <link href="{{ url('asset/css/custom.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="{{ url('asset/css/jquery.dataTables.min.css') }}">

    <script>
        // GLOBAL 12-HOUR DATE/TIME FORMATTER — a plain synchronous script
        // (not part of the Vite-bundled app.js above, which loads as a
        // deferred ES module and can run AFTER a page's own inline
        // DataTable render callbacks fire on first load) so it's guaranteed
        // to exist before any dashboard page's own script runs.
        //
        // Site runs on Asia/Kolkata (see config/app.php) — stored timestamps
        // are already IST wall-clock, so this only needs to force 12-hour
        // AM/PM display instead of relying on browser locale defaults.
        window.formatDateTime = function(value) {
            if (!value) return '—';

            let str = String(value).includes('T') ? value : value.replace(' ', 'T');
            let d = new Date(str);

            if (isNaN(d)) return value;

            return d.toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true,
            });
        };

        window.formatDate = function(value) {
            if (!value) return '—';

            let str = String(value).includes('T') ? value : value.replace(' ', 'T');
            let d = new Date(str);

            if (isNaN(d)) return value;

            return d.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            });
        };
    </script>

</head>

<body>
    <div id="globalLoader">
        <div class="loader-spinner"></div>
    </div>

    <div class="toast-container position-fixed top-0 end-0 p-3">

        <div id="globalToast" class="toast align-items-center text-white border-0">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage">
                    Message
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-mdb-dismiss="toast"></button>
            </div>
        </div>

    </div>
