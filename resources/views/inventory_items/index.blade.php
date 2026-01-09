@extends('layouts.master')

@section('content')
<main>
  <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
    <div class="container-fluid px-4">
      <div class="page-header-content pt-4">

        <div class="row align-items-center justify-content-between g-3">
          <div class="col-12 col-lg-auto">
            <h1 class="page-header-title mb-0">
              <div class="page-header-icon"><i data-feather="layers"></i></div>
              Inventory Item
            </h1>
            <div class="page-header-subtitle">Hit API Inventory Item by Location (detail per SN/Rack)</div>
          </div>

          <div class="col-12 col-lg-8">
            <form action="{{ route('inventory-item.sync') }}" method="POST" class="row g-2 justify-content-lg-end">
              @csrf

              <div class="col-12 col-md-5">
                <select name="location_external_id" class="form-select" required>
                  <option value="">-- Choose Location --</option>
                  @foreach($locations as $loc)
                    <option value="{{ $loc->external_id }}">
                      {{ $loc->display_name ?? $loc->name }} ({{ $loc->location_code }})
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="col-6 col-md-3">
                <input type="date" name="start_date" class="form-control" value="{{ now()->format('Y-m-d') }}">
              </div>

              <div class="col-6 col-md-3">
                <input type="date" name="end_date" class="form-control" value="{{ now()->format('Y-m-d') }}">
              </div>

              <div class="col-12 col-md-1 d-grid">
                <button type="submit" class="btn btn-light">
                  <i data-feather="refresh-cw"></i>
                </button>
              </div>
            </form>

            <small class="text-white-50 d-block mt-2">
              Default sync: today range (00:00–23:59) untuk aman (data bisa puluhan ribu).
            </small>
          </div>
        </div>

      </div>
    </div>
  </header>

  <div class="container-fluid px-4 mt-n10">
    <div class="card">
      <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
          <h3 class="card-title mb-0">Inventory Item List</h3>
          <small class="text-muted">Latest records (showing recent data from database).</small>
        </div>

        <div class="d-flex align-items-center gap-2">
          <label class="small text-muted mb-0">Filter Location</label>
          <select id="filterLocation" class="form-select form-select-sm" style="min-width:220px;">
            <option value="">All</option>
            @foreach($locations as $loc)
              <option value="{{ $loc->location_code }}">
                {{ $loc->display_name ?? $loc->name }} ({{ $loc->location_code }})
              </option>
            @endforeach
          </select>
        </div>
      </div>

      @include('partials.alert')

      <div class="card-body">
        <div class="table-responsive">
          <table id="tableInventoryItem" class="table table-striped table-bordered w-100">
            <thead>
              <tr>
                <th style="width:70px;">ID</th>
                <th>Code</th>
                <th>Product Name</th>
                <th>Serial Number</th>
                <th>Rack</th>
                <th>Rack Type</th>
                <th>Status</th>
                <th>Qty</th>
                <th>Location</th>
                <th style="width:160px;">External ID</th>
              </tr>
            </thead>
            <tbody>
              @foreach($items as $it)
                <tr>
                  <td>{{ $it->id }}</td>
                  <td>{{ $it->code }}</td>
                  <td>{{ $it->product_name }}</td>
                  <td>{{ $it->serial_number }}</td>
                  <td>{{ $it->rack }}</td>
                  <td>{{ $it->rack_type }}</td>
                  <td>{{ $it->status }}</td>
                  <td>{{ $it->qty }}</td>
                  <td>{{ $it->location_code }}</td>
                  <td><small class="text-muted">{{ $it->external_id }}</small></td>
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
      const table = $("#tableInventoryItem").DataTable({
        responsive: true,
        lengthChange: false,
        autoWidth: false,
      });

      // column index location = 8
      $("#filterLocation").on("change", function () {
        const val = $(this).val();
        table.column(8).search(val ? "^" + val + "$" : "", true, false).draw();
      });
    });
  </script>
@endsection
