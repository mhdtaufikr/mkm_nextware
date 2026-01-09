<nav class="topnav navbar navbar-expand shadow justify-content-between justify-content-sm-start navbar-light bg-white"
     id="sidenavAccordion">

  <!-- Toggle -->
  <button class="btn btn-icon btn-transparent-dark order-1 order-lg-0 me-2 ms-lg-2 me-lg-0"
          id="sidebarToggle">
    <i data-feather="menu"></i>
  </button>

  <!-- Brand -->
  <a class="navbar-brand d-flex align-items-center gap-2 ms-2" href="{{ url('/') }}">
    <img src="{{ asset('assets/img/topbar.png') }}" alt="Logo" class="topbar-logo">
    <span class="topbar-title">NEXTWARE</span>
  </a>

  <!-- Right -->
  <ul class="navbar-nav align-items-center ms-auto">
    <li class="nav-item dropdown no-caret dropdown-user me-3 me-lg-4">
      <a class="btn btn-icon btn-transparent-dark dropdown-toggle"
         id="navbarDropdownUserImage"
         href="javascript:void(0);"
         role="button"
         data-bs-toggle="dropdown"
         aria-haspopup="true"
         aria-expanded="false">

        <img class="img-fluid rounded-circle"
             src="{{ Auth::user()->img ? asset(Auth::user()->img) : asset('assets/img/illustrations/profiles/profile-1.png') }}"
             alt="User" />
      </a>

      <div class="dropdown-menu dropdown-menu-end border-0 shadow animated--fade-in-up"
           aria-labelledby="navbarDropdownUserImage">

        <h6 class="dropdown-header d-flex align-items-center">
          <img class="dropdown-user-img"
               src="{{ Auth::user()->img ? asset(Auth::user()->img) : asset('assets/img/illustrations/profiles/profile-1.png') }}"
               alt="User" />
          <div class="dropdown-user-details">
            <div class="dropdown-user-details-name">{{ auth()->user()->name }}</div>
            <div class="dropdown-user-details-email">{{ auth()->user()->email }}</div>
          </div>
        </h6>

        <div class="dropdown-divider"></div>

        <a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
          <div class="dropdown-item-icon"><i data-feather="key"></i></div>
          Change Password
        </a>

        <a class="dropdown-item" href="{{ url('/logout') }}">
          <div class="dropdown-item-icon"><i data-feather="log-out"></i></div>
          Logout
        </a>
      </div>
    </li>
  </ul>
</nav>
