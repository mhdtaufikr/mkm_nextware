@extends('layouts.master')

@section('content')
@include('home._css')
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
            <!-- ✅ Stock Strength Dashboard (Full Width) - Height dikurangi -->
            @include('home._stock_strength')

            <!-- OTDP Inbound & Outbound (Side by Side) - Height dikurangi -->
            @include('home._inbound')
            @include('home._outbound')
        </div>

        @endif
    </div>

    @include('home._modaldetail')
</main>

@endsection
