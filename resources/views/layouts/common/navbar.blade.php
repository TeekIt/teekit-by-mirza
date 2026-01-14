<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <div class="container pb-3 px-3">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link d-sm-block d-md-block d-lg-none" data-widget="pushmenu" href="#" role="button">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item d-block">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="btn btn-outline-primary my-2 my-sm-0 login-btn" type="submit">
                        <i class="fas fa-power-off"></i>
                        Logout
                    </button>
                </form>
            </li>
        </ul>
    </div>
</nav>
