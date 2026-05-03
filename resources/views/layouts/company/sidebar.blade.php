<aside class="main-sidebar sidebar-dark-primary main-sidebar-width" style="overflow: initial;">
    <!-- Brand Logo -->
    <a class="nav-link nav-sidebar-arrow" onclick="jQuery('.navbar-nav>.nav-item>.nav-link').click();">
        <img src="{{ asset('images/icons/arrow.png') }}">
    </a>
    <a class="brand-link" href="/" style="display:block;opacity: 1">
        <img alt="{{ config('app.name') }} - Logo" class="brand-image" src="{{ asset('images/logo.png') }}"
            style="display: block; opacity: 1">
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-5">
            <ul class="nav nav-pills nav-sidebar flex-column" data-accordion="false" data-widget="treeview"
                role="menu">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('vans.company.dashboard') ? 'active' : '' }}"
                        href="{{ route('vans.company.dashboard') }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p class="ml-2">Dashboard</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('vans') ? 'active' : '' }}" href="{{ route('vans') }}">
                        <i class="nav-icon fas fa-shuttle-van"></i>
                        <p class="ml-2">Vans</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('vans.inventories') ? 'active' : '' }}" href="{{ route('vans.inventories') }}">
                        <i class="nav-icon fas fa-truck-loading"></i>
                        <p class="ml-2">Inventory</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('vans.company.orders') ? 'active' : '' }}"
                        href="{{ route('vans.company.orders') }}">
                        <i class="nav-icon fas fa-cart-arrow-down"></i>
                        <p class="ml-2">Orders</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('vans.company.settings') ? 'active' : '' }}"
                        href="{{ route('vans.company.settings') }}">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p class="ml-2">Settings</p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
