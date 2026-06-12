@include('layout.header')
@include('layout.navbar')
@include('layout.sidebar')
    <div id="content" class="content">
        @yield('content')
    </div>
@include('layout.setting')
@include('layout.footer')
@stack('scripts')
