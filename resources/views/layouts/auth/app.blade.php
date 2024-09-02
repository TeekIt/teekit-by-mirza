<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('layouts.header-links')
</head>

<body class="hold-transition main-pages-bg-img">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white">
            <div class="container">
                <div class="cstm-container-sm">
                    <a class="navbar-brand" target="_blank" href="https://teekit.co.uk/">
                        <img style="max-height: 50px;" src="{{ asset('images/icons/logo.webp') }}" alt="TeekIt Logo">
                    </a>
                    <!-- Toggle Button For Mobiles - Begins -->
                    <button class="navbar-toggler mobile-login-btn" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="Toggle navigation">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Login
                    </button>
                </div>
                <!-- Toggle Button For Mobiles - Ends -->
                <div class="collapse navbar-collapse" id="navbarSupportedContent" aria-current="true" role="navigation">
                    <form class="my-2 my-lg-0 ml-auto w-lg-50" style="min-width: 45vw;" method="POST"
                        action="{{ route('login') }}">
                        <div class="row">
                            <div class="col-md-5 mt-3">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <input class="form-control mr-sm-2" type="email" required autocomplete="off"
                                        name="email" placeholder="Email" aria-label="email"
                                        value="{{ old('email') }}">
                                    <label for="checkauto">
                                        <input name="remember" id="checkauto" type="checkbox"> Keep me logged in
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-5 mt-3">
                                <div class="form-group">
                                    <input class="form-control mr-sm-2" type="password" autocomplete="off"
                                        placeholder="Password" name="password" required>
                                    <p>
                                        <a class="text-dark" href="{{ route('password.request') }}">
                                            Forgot Password?
                                        </a>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <button class="btn btn-outline-primary my-2 my-sm-0 login-btn" type="submit">
                                        Login
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </nav>
        <!-- /Navbar -->
        <div class="container">
            @include('flash::message')
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
            <div class="row mtd" style="margin-top: 20vh">
                <div class="col-md-6 col-lg-8">
                    <img src="{{ asset('images/backgrounds/bike.webp') }}" class="bg-img">
                </div>
                <div class="col-md-6 col-lg-4">
                    @yield('content')
                </div>
            </div>
        </div>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
    @include('layouts.scripts')
</body>

<style>
    .border {
        border: 1px solid red;
    }

    .bg-img {
        max-height: 540px;
        margin: 0 auto;
        display: block;
        max-width: 500px;
        height: 100%;
        width: 100%;
        object-fit: contain;
    }

    @media screen and (max-width: 769px) {
        .cstm-container-sm {
            width: 100%;
            display: flex;
            justify-content: space-between;
            padding: 0px 20px;
        }

        .mobile-login-btn {
            border: none !important;
            color: #3663ae !important;
        }
    }
</style>

</html>
