  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary" style="overflow: initial;">
      <!-- Brand Logo -->
      <a class="nav-link nav-sidebar-arrow" onclick="jQuery('.navbar-nav>.nav-item>.nav-link').click();">
          <img src="{{ asset('images/icons/arrow.png') }}">
      </a>
      <a class="brand-link" href="/" style="display:block; opacity: 1">
          <img alt="{{ config('app.name') }} - Logo" class="brand-image" src="{{ asset('images/logo.png') }}"
              style="display: block; opacity: 1">
      </a>

      <!-- Sidebar -->
      <div class="sidebar">
          <!-- Sidebar Menu -->
          <nav class="mt-5">
              <ul class="nav nav-pills nav-sidebar flex-column" data-accordion="false" data-widget="treeview"
                  role="menu">
                  <!-- Add icons to the links using the .nav-icon class
                 with font-awesome or any other icon font library -->
                  <li class="nav-item">
                      <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="/">
                          <i class="nav-icon fas fa-tachometer-alt"></i>
                          <p class="ml-2">
                              Dashboard
                          </p>
                      </a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link {{ request()->is('admin/notifications/home') ? 'active' : '' }}"
                          href="{{ route('admin.notifications.home') }}">
                          <i class="nav-icon fas fa-bell"></i>
                          <p class="ml-2">
                              Notifications
                          </p>
                      </a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link {{ request()->is('admin/vans') ? 'active' : '' }}"
                          href="{{ route('admin.vans') }}">
                          <i class="nav-icon fas fa-shuttle-van"></i>
                          <p class="ml-2">
                              Vans
                          </p>
                      </a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link {{ request()->is('admin/promocodes/home') ? 'active' : '' }}"
                          href="{{ route('admin.promocodes.home') }}">
                          <i class="nav-icon fas fa-qrcode"></i>
                          <p class="ml-2">
                              Promo Codes
                          </p>
                      </a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link {{ request()->is('admin/referralcodes') ? 'active' : '' }}"
                          href="{{ route('admin.referralcodes') }}">
                          <i class="nav-icon fas fa-share-alt-square"></i>
                          <p class="ml-2">
                              Referrals
                          </p>
                      </a>
                  </li>
                  <li class="nav-item has-treeview">
                      <a href="#" class="nav-link ">
                          <i class="nav-icon fas fa-store-alt"></i>
                          <p class="ml-2">
                              Sellers
                              <i class="fas fa-angle-left right"></i>
                          </p>
                      </a>
                      <ul class="nav nav-treeview">
                          <li class="nav-item">
                              <a href="{{ route('admin.sellers.parent') }}"
                                  class="nav-link @if (request()->is('admin/sellers/parent')) active @endif">
                                  <i class="fas fa-gears nav-icon"></i>
                                  <p>Parent</p>
                              </a>
                          </li>
                          {{-- <li class="nav-item">
                              <a href="{{ route('admin.sellers.parent') }}"
                                  class="nav-link @if (request()->is('admin/sellers/parent')) active @endif">
                                  <i class="fas fa-gears nav-icon"></i>
                                  <p>Parent</p>
                              </a>
                          </li> --}}
                          <li class="nav-item">
                              <a href="{{ route('admin.sellers.child') }}"
                                  class="nav-link @if (request()->is('admin/sellers/child')) active @endif">
                                  <i class="fas fa-gears nav-icon"></i>
                                  <p>Child</p>
                              </a>
                          </li>
                      </ul>
                  </li>
                  <li class="nav-item">
                      <a href="{{ route('admin.customers') }}"
                          class="nav-link  @if (request()->is('admin/customers')) active @endif">
                          <i class="nav-icon fas fa-users-cog"></i>
                          <p class="ml-2">
                              Customers
                          </p>
                      </a>
                  </li>
                  <li class="nav-item has-treeview">
                      <a href="#" class="nav-link ">
                          <i class="nav-icon fas fa-luggage-cart"></i>
                          <p>
                              Orders
                              <i class="fas fa-angle-left right"></i>
                          </p>
                      </a>
                      <ul class="nav nav-treeview">
                          <li class="nav-item">
                              <a href="{{ route('admin.orders') }}"
                                  class="nav-link {{ request()->routeIs('admin.orders') ? 'active' : '' }}">
                                  <i class="fas fa-gears nav-icon"></i>
                                  <p>All</p>
                              </a>
                          </li>
                          <li class="nav-item">
                              <a href="{{ route('admin.orders.verified') }}"
                                  class="nav-link {{ request()->routeIs('admin.orders.verified') ? 'active' : '' }}">
                                  <i class="fas fa-gears nav-icon"></i>
                                  <p>Verified</p>
                              </a>
                          </li>
                          <li class="nav-item">
                              <a href="{{ route('admin.orders.unverified') }}"
                                  class="nav-link {{ request()->routeIs('admin.orders.unverified') ? 'active' : '' }}">
                                  <i class="fas fa-gears nav-icon"></i>
                                  <p>Unverified</p>
                              </a>
                          </li>
                          <li class="nav-item">
                              <a href="{{ route('admin.orders.complete') }}"
                                  class="nav-link {{ request()->routeIs('admin.orders.complete') ? 'active' : '' }}">
                                  <i class="fas fa-money nav-icon"></i>
                                  <p>Completed</p>
                              </a>
                          </li>
                      </ul>
                  </li>
                  <li
                      class="nav-item has-treeview {{ request()->is('withdrawals-drivers') || request()->is('withdrawals') ? 'active' : '' }}">
                      <a href="#" class="nav-link">
                          <i class="nav-icon fas fa-money-bill-wave"></i>
                          <p>
                              Withdrawals
                              <i class="fas fa-angle-left right"></i>
                          </p>
                      </a>
                      <ul class="nav nav-treeview">
                          <li class="nav-item">
                              <a href="/withdrawals" class="nav-link">
                                  <i class="fas fa-gears nav-icon"></i>
                                  <p>Sellers</p>
                              </a>
                          </li>
                      </ul>
                  </li>
                  <li class="nav-item">
                      <a href="{{ route('admin.categories') }}"
                          class="nav-link {{ request()->routeIs('admin.categories') ? 'active' : '' }}">
                          <i class="nav-icon fas fa-clipboard-list"></i>
                          <p class="ml-2">
                              Categories
                              <span class="badge badge-warning text-light">new</span>
                          </p>
                      </a>
                  </li>
                  <li class="nav-item">
                      <a href="{{ route('admin.settings') }}"
                          class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                          <i class="nav-icon fa fa-cog"></i>
                          <p class="ml-2">
                              Settings
                          </p>
                      </a>
                  </li>
              </ul>
          </nav>
          <!-- /.sidebar-menu -->
      </div>
      <!-- /.sidebar -->
  </aside>
