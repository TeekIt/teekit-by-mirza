<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('layouts.header-links')
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        @include('layouts.common.navbar')
        @include('layouts.admin.sidebar')

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            
            <x-session-messages />

            @yield('content')
        </div>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
    @include('layouts.scripts')
    @livewireScripts
</body>

</html>
