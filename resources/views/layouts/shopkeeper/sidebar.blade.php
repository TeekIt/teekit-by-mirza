<aside class="main-sidebar sidebar-dark-primary" style="overflow: initial;">
    <!-- Brand Logo -->
    <a class="nav-link nav-sidebar-arrow" onclick="jQuery('.navbar-nav>.nav-item>.nav-link').click();"> 
        <img src="{{ asset('res/res/img/arrow.png') }}">
    </a>
    <a class="brand-link" href="/" style="display:block;opacity: 1">
        <img alt="AdminLTE Logo" class="brand-image" src="{{ asset('res/res/img/logo.png') }}" style="display: block; opacity: 1">
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-5">
            <ul class="nav nav-pills nav-sidebar flex-column" data-accordion="false" data-widget="treeview"
                role="menu">
                <!-- Add icons to the links using the .nav-icon class with font-awesome or any other icon font library -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('seller.dashboard') ? 'active' : '' }}" href="{{ route('seller.dashboard') }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p class="ml-2">
                            Dashboard
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('seller.inventory') ? 'active' : '' }}" href="{{ route('seller.inventory') }}">
                        <i class="nav-icon fa fa-truck"></i>
                        <p class="ml-2">
                            Inventory
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('seller.orders') ? 'active' : '' }}" href="{{ route('seller.orders') }}">
                        <i class="nav-icon fas fa-cart-arrow-down"></i>
                        <p class="ml-2">
                           My Orders
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('seller.orders.from.others') ? 'active' : '' }}" href="{{ route('seller.orders.from.others') }}">
                        <i class="nav-icon fas fa-luggage-cart"></i>
                        <p class="ml-2">
                            Orders From Others
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('seller.withdrawal') ? 'active' : '' }}" href="{{ route('seller.withdrawal') }}">
                        <i class="nav-icon fas fa-dollar-sign"></i>
                        <p class="ml-2">
                            Withdrawals
                        </p>
                    </a>
                </li>
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>
                            Settings
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{route('seller.settings.general')}}" class="nav-link ">
                                <i class="fas fa-gears nav-icon"></i>
                                <p>General</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{route('setting.payment')}}" class="nav-link ">
                                <i class="fas fa-money nav-icon"></i>
                                <p>Payment</p>
                            </a>
                        </li>
                    </ul>
                </li>

            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>