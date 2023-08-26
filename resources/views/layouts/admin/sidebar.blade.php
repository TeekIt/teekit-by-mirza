  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary" style="overflow: initial;">
      <!-- Brand Logo -->
      <a class="nav-link nav-sidebar-arrow" onclick="jQuery('.navbar-nav>.nav-item>.nav-link').click();">
          <img src="{{ asset('res/res/img/arrow.png') }}">
      </a>
      <a class="brand-link" href="/" style="display:block;opacity: 1">
          <img alt="Teek it - Logo" class="brand-image" src="{{ asset('res/res/img/logo.png') }}" style="display: block; opacity: 1">
      </a>

      <!-- Sidebar -->
      <div class="sidebar">
          <!-- Sidebar Menu -->
          <nav class="mt-5">
              <ul class="nav nav-pills nav-sidebar flex-column" data-accordion="false" data-widget="treeview" role="menu">
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
                      <a class="nav-link {{ request()->is('notification/home') ? 'active' : '' }}" href="/notification/home">
                          <i class="nav-icon fas fa-bell"></i>
                          <p class="ml-2">
                              Notifications
                          </p>
                      </a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link {{ request()->is('promocodes/home') ? 'active' : '' }}" href="/promocodes/home">
                          <i class="nav-icon fas fa-qrcode"></i>
                          <p class="ml-2">
                              Promo Codes
                          </p>
                      </a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link {{ request()->is('/admin/referralcodes') ? 'active' : '' }}" href="{{ route('admin.referralcodes') }}">
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
                              <a href="{{ route('admin.sellers.parent') }}" class="nav-link @if (request()->is('admin/sellers/parent')) active @endif">
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
                              <a href="{{ route('admin.sellers.child') }}" class="nav-link @if (request()->is('admin/sellers/child')) active @endif">
                                  <i class="fas fa-gears nav-icon"></i>
                                  <p>Child</p>
                              </a>
                          </li>
                      </ul>
                  </li>
                  <li class="nav-item">
                      <a href="{{ route('admin.customers') }}" class="nav-link  @if (request()->is('admin/customers')) active @endif">
                          <i class="nav-icon fas fa-users-cog"></i>
                          <p class="ml-2"> Customers </p>
                      </a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link {{ request()->is('drivers') ? 'active' : '' }}" href="/drivers">
                          <i class="nav-icon fas fa-biking"></i>
                          <p class="ml-2">
                              Drivers
                          </p>
                      </a>
                  </li>
                  <li class="nav-item">
                      <a href="{{ route('admin.test.drivers') }}" class="nav-link @if (request()->is('admin/drivers')) active @endif">
                          <i class="nav-icon fas fa-biking"></i>
                          <p class="ml-2">
                              Test Drivers
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
                              <a href="/aorders" class="nav-link @if (request()->is('aorders')) active @endif">
                                  <i class="fas fa-gears nav-icon"></i>
                                  <p>All</p>
                              </a>
                          </li>
                          <li class="nav-item">
                              <a href="/aorders/verified" class="nav-link @if (request()->is('aorders/verified')) active @endif">
                                  <i class="fas fa-gears nav-icon"></i>
                                  <p>Verified</p>
                              </a>
                          </li>
                          <li class="nav-item">
                              <a href="/aorders/unverified" class="nav-link @if (request()->is('aorders/unverified')) active @endif">
                                  <i class="fas fa-gears nav-icon"></i>
                                  <p>Unverified</p>
                              </a>
                          </li>
                          <li class="nav-item">
                              <a href="/complete-orders" class="nav-link @if (request()->is('complete-orders')) active @endif">
                                  <i class="fas fa-money nav-icon"></i>
                                  <p>Completed</p>
                              </a>
                          </li>
                      </ul>
                  </li>
                  <li class="nav-item has-treeview {{ request()->is('withdrawals-drivers') || request()->is('withdrawals') ? 'active' : '' }}">
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
                          <li class="nav-item">
                              <a href="/withdrawals-drivers" class="nav-link">
                                  <i class="fas fa-money nav-icon"></i>
                                  <p>Drivers</p>
                              </a>
                          </li>
                      </ul>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link {{ request()->is('acategories') ? 'active' : '' }}" href="/acategories">
                          <i class="nav-icon fas fa-clipboard-list"></i>
                          <p class="ml-2">
                              Categories
                          </p>
                      </a>
                  </li>
                  <li class="nav-item">
                      <a class="nav-link {{ request()->is('asetting') ? 'active' : '' }}" href="/asetting">
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
