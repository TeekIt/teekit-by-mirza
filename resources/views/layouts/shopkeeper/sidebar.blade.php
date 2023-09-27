<aside class="main-sidebar sidebar-dark-primary" style=" overflow: initial; ">
    <!-- Brand Logo -->
    <a class="nav-link nav-sidebar-arrow" onclick="jQuery('.navbar-nav>.nav-item>.nav-link').click();"> <img
            alt="" src="{{ asset('res/res/img/arrow.png') }}"></a>
    <a class="brand-link" href="/" style="display:block;opacity: 1">
        <img alt="AdminLTE Logo" class="brand-image" src="{{ asset('res/res/img/logo.png') }}"
            style="display: block; opacity: 1">
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->

        <!-- Sidebar Menu -->
        <nav class="mt-5">
            <ul class="nav nav-pills nav-sidebar flex-column" data-accordion="false" data-widget="treeview"
                role="menu">
                <!-- Add icons to the links using the .nav-icon class
                 with font-awesome or any other icon font library -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('/') ? 'active' : '' }} " href="/">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p class="ml-2">
                            Dashboard
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if (request()->is('inventory')) active @endif" href="{{route('inventory')}}">
                        <i class="nav-icon fa fa-truck"></i>
                        <p class="ml-2">
                            Inventory
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('orders') ? 'active' : '' }}" href="/orders">
                        <i class="nav-icon fas fa-clipboard-list"></i>
                        <p class="ml-2">
                            Orders
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('seller/orders') ? 'active' : '' }}" href="{{route('seller.orders')}}">
                        <i class="nav-icon fas fa-clipboard-list"></i>
                        <p class="ml-2">
                            Orders Test
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('withdrawals') ? 'active' : '' }}" href="/withdrawals">
                        <i class="nav-icon fas fa-clipboard-list"></i>
                        <p class="ml-2">
                            Withdrawals
                        </p>
                    </a>
                </li>
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link ">
                        <i class="nav-icon fas fa-user-secret"></i>
                        <p>
                            Settings
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{route('usergeneral')}}" class="nav-link">
                                <i class="fas fa-gears nav-icon"></i>
                                <p>Seller General Settings</p>
                            </a>
                        </li>
                        {{-- <li class="nav-item">
                            <a href="/settings/general" class="nav-link">
                                <i class="fas fa-gears nav-icon"></i>
                                <p>General Settings</p>
                            </a>
                        </li> --}}
                        <li class="nav-item">
                            <a href="/settings/payment" class="nav-link">
                                <i class="fas fa-money nav-icon"></i>
                                <p>Payment Settings</p>
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