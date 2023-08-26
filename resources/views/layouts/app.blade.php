<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    {{-- @include('layouts.admin.header-links') --}}
    @include('layouts.header-links')
    @livewireStyles
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        {{-- 1 == Super Admin --}}
        @if (Auth::user()->role_id === 1)
            @include('layouts.admin.navbar')
            @include('layouts.admin.sidebar')
            {{-- 2 == Parent Seller, 5 == Child Seller --}}
        @elseif(Auth::user()->role_id == 2 || Auth::user()->role_id == 5)
            @include('layouts.shopkeeper.navbar')
            @include('layouts.shopkeeper.sidebar')
        @endif
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Livewire component will render here by default -->
            {{ $slot }}
        </div>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
    @include('layouts.scripts')
    @livewireScripts
</body>

</html>
