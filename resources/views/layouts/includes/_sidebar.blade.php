@include('layouts.includes._nav_helpers')

<div id="layoutSidenav_nav">
  <nav class="sidenav shadow-right sidenav-light">
    <div class="sidenav-menu">
      <div class="nav accordion" id="accordionSidenav">

        @php
          $user = auth()->user();
          $isAdmin = auth()->check() && $user->role === 'admin';
        @endphp

        @if ($isAdmin)

          <!-- ================= MASTER DATA ================= -->
          <div class="sidenav-menu-heading">Master Data</div>

          <a class="nav-link collapsed"
             href="javascript:void(0);"
             data-bs-toggle="collapse"
             data-bs-target="#collapseMasterData"
             aria-expanded="{{ nav_show(['location*','inventory*','inventory-item*']) ? 'true' : 'false' }}"
             aria-controls="collapseMasterData">

            <div class="nav-link-icon">
              <i data-feather="database"></i>
            </div>

            Master Data
            <div class="sidenav-collapse-arrow">
              <i class="fas fa-angle-down"></i>
            </div>
          </a>

          <div class="collapse {{ nav_show(['location*','inventory*','inventory-item*']) }}"
               id="collapseMasterData"
               data-bs-parent="#accordionSidenav">

            <nav class="sidenav-menu-nested nav">

              <a class="nav-link {{ nav_active('location*') }}"
                 href="{{ url('/location') }}">
                <i data-feather="map-pin" class="me-2"></i>
                Location
              </a>

              <a class="nav-link {{ nav_active('inventory*') }}"
                 href="{{ url('/inventory') }}">
                <i data-feather="archive" class="me-2"></i>
                Inventory
              </a>

              <a class="nav-link {{ nav_active('inventory-item*') }}"
                 href="{{ url('/inventory-item') }}">
                <i data-feather="layers" class="me-2"></i>
                Inventory Item
              </a>

            </nav>
          </div>


          <!-- ================= CONFIGURATION ================= -->
          <div class="sidenav-menu-heading">Configuration</div>

          <a class="nav-link collapsed"
             href="javascript:void(0);"
             data-bs-toggle="collapse"
             data-bs-target="#collapseConfig"
             aria-expanded="{{ nav_show(['dropdown*','rule*','user*']) ? 'true' : 'false' }}"
             aria-controls="collapseConfig">

            <div class="nav-link-icon">
              <i data-feather="tool"></i>
            </div>

            Configuration
            <div class="sidenav-collapse-arrow">
              <i class="fas fa-angle-down"></i>
            </div>
          </a>

          <div class="collapse {{ nav_show(['dropdown*','rule*','user*']) }}"
               id="collapseConfig"
               data-bs-parent="#accordionSidenav">

            <nav class="sidenav-menu-nested nav">

              <a class="nav-link {{ nav_active('dropdown*') }}"
                 href="{{ url('/dropdown') }}">
                <i data-feather="chevron-down" class="me-2"></i>
                Dropdown
              </a>

              <a class="nav-link {{ nav_active('rule*') }}"
                 href="{{ url('/rule') }}">
                <i data-feather="sliders" class="me-2"></i>
                Rules
              </a>

              <a class="nav-link {{ nav_active('user*') }}"
                 href="{{ url('/user') }}">
                <i data-feather="users" class="me-2"></i>
                User
              </a>

            </nav>
          </div>

        @endif

      </div>
    </div>

    <!-- ================= FOOTER ================= -->
    <div class="sidenav-footer">
      <div class="sidenav-footer-content">
        <div class="sidenav-footer-subtitle">Logged in as:</div>
        <div class="sidenav-footer-title">
          {{ auth()->check() ? auth()->user()->name : 'Guest' }}
        </div>
      </div>
    </div>

  </nav>
</div>
