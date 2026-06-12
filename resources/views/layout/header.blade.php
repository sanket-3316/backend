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
