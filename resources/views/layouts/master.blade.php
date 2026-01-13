<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>NEXTWARE</title>

  <link rel="icon" href="{{ asset('assets/img/mkm_logo.png') }}">

  <!-- SB Admin Pro base -->
  <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet" />

  <!-- Custom overrides (NO inline css) -->
  <link href="{{ asset('assets/css/custom-ui.css') }}" rel="stylesheet" />

  <!-- Vendor CSS (keep what you use) -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
  <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
  <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-lite.min.css" rel="stylesheet">

  @stack('styles')

  <!-- Icons -->
  <script defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.28.0/feather.min.js" crossorigin="anonymous"></script>

  <!-- jQuery (once) -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Vendor JS -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- DataTables -->
  <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
  <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
  <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
  <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
  <script src="{{ asset('plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
  <script src="{{ asset('plugins/jszip/jszip.min.js') }}"></script>
  <script src="{{ asset('plugins/pdfmake/pdfmake.min.js') }}"></script>
  <script src="{{ asset('plugins/pdfmake/vfs_fonts.js') }}"></script>
  <script src="{{ asset('plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
  <script src="{{ asset('plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
  <script src="{{ asset('plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>

  <!-- Select2 -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <!-- Summernote -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-lite.min.js"></script>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- amCharts 5 -->
    <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/percent.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>


</head>

<body class="nav-fixed">

  @include('layouts.includes._topbar')

  <div id="layoutSidenav">
    @include('layouts.includes._sidebar')

    <div id="layoutSidenav_content">

      @yield('content')

      <footer class="footer-admin footer-light">
        <div class="container-xl px-4">
          <div class="row">
            <div class="col-md-6 small"></div>
            <div class="col-md-6 text-md-end small">
              Copyright PT Mitsubishi Krama Yudha Motors and Manufacturing &copy; {{ now()->year }}
            </div>
          </div>
        </div>
      </footer>

    </div>
  </div>

  <!-- Loader (single) -->
  <div id="loader" class="app-loader" aria-live="polite" aria-busy="true">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
  </div>

  <!-- Bootstrap JS (samakan dengan versi CSS kamu; ini contoh 5.3.1) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

  <!-- SB Admin Pro scripts -->
  <script src="{{ asset('assets/js/scripts.js') }}"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function () {

      const loader = document.getElementById('loader');

      function showLoader() {
        loader.classList.add('show');
      }

      function hideLoader() {
        loader.classList.remove('show');
      }

      // AJAX
      $(document).ajaxStart(showLoader);
      $(document).ajaxStop(hideLoader);

      // Page navigation
      window.addEventListener("beforeunload", showLoader);

      window.addEventListener("pageshow", function (event) {
        if (event.persisted) hideLoader();
      });

      if (window.feather) feather.replace();
    });
  </script>

  @stack('scripts')

  <!-- Change Password Modal (tetap) -->
  @include('layouts.includes._change_password_modal')

</body>
</html>
