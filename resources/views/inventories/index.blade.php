@extends('layouts.master')

@section('content')
<main>
  <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
    <div class="container-fluid px-4">
      <div class="page-header-content pt-4">

        {{-- ====== ONE ROW: TITLE (LEFT) + HIT ENDPOINT (RIGHT) ====== --}}
        <div class="row align-items-center justify-content-between g-3">
          <div class="col-12 col-lg-auto">
            <h1 class="page-header-title mb-0">
              <div class="page-header-icon"><i data-feather="archive"></i></div>
              Inventory
            </h1>
            <div class="page-header-subtitle">Hit API Inventory by Location</div>
          </div>

          <div class="col-12 col-lg-7">
            <form action="{{ route('inventory.sync') }}" method="POST" class="row g-2 justify-content-lg-end">
              @csrf

              <div class="col-12 col-md-8">
                <select name="location_external_id" class="form-select" required>
                  <option value="">-- Choose Location --</option>
                  @foreach($locations as $loc)
                    <option value="{{ $loc->external_id }}">
                      {{ $loc->display_name ?? $loc->name }} ({{ $loc->location_code }})
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="col-12 col-md-4">
                <button type="submit" class="btn btn-light w-100">
                  <i data-feather="refresh-cw" class="me-1"></i>
                  Hit Endpoint
                </button>
              </div>
            </form>
          </div>
        </div>
        {{-- ====== END ROW ====== --}}

      </div>
    </div>
  </header>

  <div class="container-fluid px-4 mt-n10">
    <div class="card">
      <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
          <h3 class="card-title mb-0">Inventory List</h3>
          <small class="text-muted">Use filter to narrow results.</small>
        </div>

        {{-- ====== FILTER LOCATION (DATATABLES) ====== --}}
        <div class="d-flex align-items-center gap-2">
          <label class="small text-muted mb-0">Filter Location</label>
          <select id="filterLocation" class="form-select form-select-sm" style="min-width: 220px;">
            <option value="">All</option>
            @foreach($locations as $loc)
              @php $label = ($loc->display_name ?? $loc->name) . ' (' . ($loc->location_code ?? '-') . ')'; @endphp
              <option value="{{ $loc->location_code }}">{{ $label }}</option>
            @endforeach
          </select>
        </div>
        {{-- ====== END FILTER ====== --}}
      </div>

      @include('partials.alert')

      <div class="card-body">
        <div class="table-responsive">
          <table id="tableInventory" class="table table-striped table-bordered w-100">
            <thead>
              <tr>
                <th style="width:70px;">ID</th>
                <th>Item Code</th>
                <th>Name</th>
                <th>Location</th>
                <th>Qty</th>
                <th>Incoming</th>
                <th>Status</th>
                <th style="width:180px;">External ID</th>
              </tr>
            </thead>
            <tbody>
              @foreach($inventories as $inv)
                <tr>
                  <td>{{ $inv->id }}</td>
                  <td>{{ $inv->code }}</td>
                  <td>{{ $inv->name }}</td>
                  <td>{{ $inv->location_code }}</td>
                  <td>{{ $inv->qty }}</td>
                  <td>{{ $inv->qty_incoming }}</td>
                  <td>{{ $inv->stock_status }}</td>
                  <td><small class="text-muted">{{ $inv->external_id }}</small></td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</main>
<script>
    $(document).ready(function () {
      // DataTables init
      const table = $("#tableInventory").DataTable({
        responsive: true,
        lengthChange: false,
        autoWidth: false,
      });

      // Location filter (column index = 3 -> "Location")
      $("#filterLocation").on("change", function () {
        const val = $(this).val();
        table.column(3).search(val ? "^" + val + "$" : "", true, false).draw();
      });
    });
  </script>
@endsection
