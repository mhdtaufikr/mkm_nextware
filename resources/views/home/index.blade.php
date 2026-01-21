@extends('layouts.master')

@section('content')
<main>
    <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
        <div class="container-fluid px-4">
            <div class="page-header-content pt-4">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto mt-4">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="home"></i></div>
                            Dashboard
                        </h1>
                        <div class="page-header-subtitle">
                            OTDP Inbound & Outbound per Cutting Center
                        </div>
                    </div>

                    <div class="col-auto mt-4">
                        <form method="GET" action="{{ route('home') }}"
                              class="d-flex gap-2 align-items-center">
                            <label class="text-white-50 small mb-0">Location</label>
                            <select name="location_id"
                                    class="form-select form-select-sm"
                                    onchange="this.form.submit()">
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ $selected && $selected->id == $loc->id ? 'selected' : '' }}>
                                        {{ $loc->display_name ?? $loc->location_code }}
                                        @if((int)($loc->is_default ?? 0) === 1) (default) @endif
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container-fluid px-4 mt-n15">
        @include('partials.alert')

        @if(!$selected)
            <div class="card">
                <div class="card-body text-muted">
                    Tidak ada location active.
                </div>
            </div>
        @else

        <div class="row">
            <!-- ✅ Stock Strength Dashboard (Full Width) -->
            @include('home._stock_strength')

            <!-- OTDP Inbound & Outbound (Side by Side) -->
            @include('home._inbound')
            @include('home._outbound')
        </div>

        @endif
    </div>

    @include('home._modaldetail')
</main>

@endsection
