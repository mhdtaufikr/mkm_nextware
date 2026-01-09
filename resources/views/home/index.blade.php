@extends('layouts.master')

@section('content')
<main>
    <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
        <div class="container-fluid px-4">
            <div class="page-header-content pt-4">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto mt-4">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="tool"></i></div>
                            Your View
                        </h1>
                        <div class="page-header-subtitle">Manage Your View</div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container-fluid px-4 mt-n10">
        <div class="content-wrapper">
            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">List of Your View</h3>
                                </div>

                                @include('partials.alert')

                                <div class="card-body">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

</main>




@endsection