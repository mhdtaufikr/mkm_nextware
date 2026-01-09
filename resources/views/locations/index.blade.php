@extends('layouts.master')

@section('content')
<main>
  <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
    <div class="container-fluid px-4">
      <div class="page-header-content pt-4">
        <div class="row align-items-center justify-content-between">
          <div class="col-auto mt-4">
            <h1 class="page-header-title">
              <div class="page-header-icon"><i data-feather="map-pin"></i></div>
              Location
            </h1>
            <div class="page-header-subtitle">Sync Location from Mile API</div>
          </div>

          <div class="col-auto mt-4">
            <form action="{{ route('location.sync') }}" method="POST" class="d-inline">
              @csrf
              <button type="submit" class="btn btn-light">
                <i data-feather="refresh-cw" class="me-1"></i>
                Hit Endpoint (Sync)
              </button>
            </form>
          </div>

        </div>
      </div>
    </div>
  </header>

  <div class="container-fluid px-4 mt-n10">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title mb-0">List Location</h3>
      </div>

      @include('partials.alert')

      <div class="card-body">
        <div class="table-responsive">
          <table id="tableLocation" class="table table-striped table-bordered w-100">
            <thead>
              <tr>
                <th style="width:70px;">ID</th>
                <th>Code</th>
                <th>Display Name</th>
                <th>Type</th>
                <th>Address</th>
                <th>Status</th>
                <th style="width:160px;">External ID</th>
              </tr>
            </thead>
            <tbody>
              @foreach($locations as $loc)
                <tr>
                  <td>{{ $loc->id }}</td>
                  <td>{{ $loc->location_code }}</td>
                  <td>{{ $loc->display_name }}</td>
                  <td>{{ $loc->location_type }}</td>
                  <td class="text-break">{{ $loc->address }}</td>
                  <td>
                    @if((int)$loc->status === 1)
                      <span class="badge bg-success">Active</span>
                    @else
                      <span class="badge bg-secondary">Inactive</span>
                    @endif
                  </td>
                  <td><small class="text-muted">{{ $loc->external_id }}</small></td>
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
    $(document).ready(function() {
      $("#tableLocation").DataTable({
        responsive: true,
        lengthChange: false,
        autoWidth: false
      });
    });
  </script>
@endsection
