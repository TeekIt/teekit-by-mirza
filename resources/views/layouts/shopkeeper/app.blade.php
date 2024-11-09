<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('layouts.header-links')
    @yield('styles')
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        @include('layouts.common.navbar')
        @include('layouts.shopkeeper.sidebar')

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <div class="row">
                <div class="col-md-12">
                    @include('flash::message')
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                </div>
            </div>
            @yield('content')
        </div>
    </div>
    <!-- content-wrapper -->

    <style>
        table tr:first-of-type td {
            border-top: 0;
        }

        .brand-link {
            background-color: white;
        }

        [class*=sidebar-dark-] {
            background-color: #3a4b83;
        }

        [class*=sidebar-dark-] * {
            color: #fff;
        }

        .text-primary {
            color: #3a4b83 !important;
        }

        nav.main-header {
            box-shadow: 0 0px 1px rgba(0, 0, 0, .25), 0 4px 15px rgba(0, 0, 0, .22) !important;
        }

        .brand-link .brand-image {
            max-height: 90px;
        }

        nav.main-header {
            min-height: 120px;
        }

        .navbar-light .navbar-nav .nav-link,
        nav.main-header a {
            color: #3a4b83;
            font-weight: 600;
        }

        .brand-link {
            min-height: 120px;
        }

        .brand-link .brand-image {
            float: unset;
            margin: 0 auto;
            display: block;
        }

        [class*=sidebar-dark] .brand-link {
            margin: 0;
            padding: 0;
        }

        .brand-link .brand-image {
            padding-top: 25px;
        }

        .sidebar-mini.sidebar-collapse .main-sidebar.sidebar-focused .brand-link,
        .sidebar-mini.sidebar-collapse .main-sidebar:hover .brand-link,
        .sidebar-mini.sidebar-collapse .main-sidebar:hover {
            width: 4.6rem;
        }

        .checked {
            color: orange;
        }

        .nav-sidebar-arrow {
            position: absolute;
            top: 55%;
            right: -17px;
            cursor: pointer;
            background: #ffcf42;
            border-radius: 100% 100%;
            padding: 0;
        }

        .nav-sidebar-arrow img {
            max-width: inherit;
            max-height: 40px;
        }

        .sidebar-collapse .nav-sidebar-arrow img {
            transform: rotate(178deg);
        }

        .content-header h1 {
            font-size: 3.5rem;
        }

        .text-site-primary {
            color: #3a4b83;
        }

        .card-body {
            padding: 5px 5px;
        }

        .img-container {
            background: #f4f6f9;
            display: block;
            margin-left: 30px;
            margin-right: 30px;
            padding-top: 25px;
            padding-bottom: 25px;
            border-radius: 20px;
        }

        .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link.active,
        .sidebar-light-primary .nav-sidebar>.nav-item>.nav-link.active {
            box-shadow: unset;
            color: #ffcf42;
            background-color: unset;
        }

        .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link.active *,
        .sidebar-light-primary .nav-sidebar>.nav-item>.nav-link.active * {
            color: #ffcf42;
        }

        .img-container {
            background: #f4f6f9;
            display: block;
            margin-left: 30px;
            margin-right: 30px;
            padding-top: 25px;
            padding-bottom: 25px;
            border-radius: 20px;
            width: 100%;
            height: auto;
            margin: 0;
            padding: 10px;
            border-radius: 7px;
        }

        .img-container img {
            width: auto;
            height: auto;
            max-width: 100%;
            height: auto;
        }

        .pt-30 {
            padding-top: 30px;
        }

        .pb-30 {
            padding-bottom: 30px;
        }

        .card-body {
            padding: 5px 15px;
        }

        .color-circle {
            width: 15px;
            height: 15px;
            display: inline-block;
            border-radius: 100vw;
        }

        .color-red {
            background: red;
        }

        .ui-timepicker-standard {
            margin-top: -242px !important;
            z-index: 1100 !important;
        }

        input.form-control,
        select.form-control {
            border: 0;
            border-bottom: 1px solid;
            border-radius: 0;
            color: #8aa7d7;
            border-color: #4a7ed6;
            padding-left: 3px;
            background: transparent;
            background-color: transparent !important;
        }

        .form-control:focus {
            color: #495057;
            background-color: transparent;
            border-color: #80bdff;
            color: #8aa7d7 !important;
        }
    </style>

    @include('layouts.scripts')
    @yield('scripts')
</body>

</html>
