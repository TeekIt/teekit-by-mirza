  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <div class="container">
          <!-- Left navbar links -->
          <ul class="navbar-nav">
              <li class="nav-item">
                  <a class="nav-link d-sm-block d-md-block d-lg-none " data-widget="pushmenu" href="#"
                      role="button"><i class="fas fa-bars"></i></a>
              </li>
          </ul>
          <!-- Right navbar links -->
          <ul class="navbar-nav ml-sm-5">
              <li class="nav-item d-block">
                  <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                      @csrf
                  </form>
                  <a href="{{ route('logout') }}"
                      onclick="event.preventDefault(); 
                      document.getElementById('logout-form').submit();">
                      Logout
                  </a>
              </li>
          </ul>
      </div>
  </nav>
  <!-- /.navbar -->
