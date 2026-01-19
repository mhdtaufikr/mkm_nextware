@extends('layouts.master')

@section('content')
<main>
    {{-- HEADER --}}
    <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
        <div class="container-fluid px-4">
            <div class="page-header-content pt-4">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="tool"></i></div>
                            Order Items
                        </h1>
                        <div class="page-header-subtitle">
                            Inbound & Outbound Orders
                        </div>
                    </div>

                    {{-- FILTER + SYNC --}}
                    <div class="col-auto">
                        <form method="GET" class="row gx-2 align-items-center">
                            <div class="col-auto">
                                <select name="location_external_id" class="form-control form-control-sm">
                                    <option value="">Location</option>
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc->external_id }}">
                                            {{ $loc->location_code }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-auto">
                                <input type="date" name="start_date"
                                    class="form-control form-control-sm"
                                    value="{{ request('start_date') }}">
                            </div>

                            <div class="col-auto">
                                <input type="date" name="end_date"
                                    class="form-control form-control-sm"
                                    value="{{ request('end_date') }}">
                            </div>

                            <div class="col-auto">
                                <select name="type" class="form-control form-control-sm">
                                    <option value="">Type</option>
                                    <option value="inbound" @selected(request('type')=='inbound')>Inbound</option>
                                    <option value="outbound" @selected(request('type')=='outbound')>Outbound</option>
                                </select>
                            </div>

                            <div class="col-auto">
                                <button class="btn btn-sm btn-light">Filter</button>
                            </div>

                            <div class="col-auto">
                                <button
                                    formaction="{{ route('order-items.sync') }}"
                                    formmethod="POST"
                                    class="btn btn-sm btn-success"
                                    onclick="return confirm('Sync orders from API?')"
                                >
                                    @csrf
                                    Sync
                                </button>
                            </div>
                        </form>
                    </div>
                    {{-- END FILTER --}}
                </div>
            </div>
        </div>
    </header>

    {{-- CONTENT --}}
    <div class="container-fluid px-4 mt-n10">
        @include('partials.alert')

        <div class="card">
            <div class="card-body">
                <table id="orderTable" class="table table-bordered table-striped table-sm w-100">
                    <thead>
                        <tr>
                            <th style="width:80px">Action</th>
                            <th>Date</th>
                            <th>Ref</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Total Item</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr data-id="{{ $order->id }}">
                                <td class="text-center details-control"
                                    style="cursor:pointer; color:#0d6efd;">
                                    Detail
                                </td>
                                <td>{{ optional($order->external_created_at)->format('Y-m-d H:i') }}</td>
                                <td>{{ $order->ref_number }}</td>
                                <td>{{ $order->customer_name }}</td>
                                <td>
                                    <span class="badge bg-{{ $order->type == 'outbound' ? 'danger' : 'success' }}">
                                        {{ strtoupper($order->type) }}
                                    </span>
                                </td>
                                <td>{{ $order->status }}</td>
                                <td class="text-end">{{ $order->total_item }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

{{-- SINGLE DATATABLE SCRIPT --}}
<script>
$(document).ready(function () {

    const tableId = '#orderTable';
    const orderDetailUrl = "{{ route('order-items.detail', ':id') }}";

    // safety: destroy if already initialized
    if ($.fn.DataTable.isDataTable(tableId)) {
        $(tableId).DataTable().destroy();
    }

    let table = $(tableId).DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        order: [[1, 'desc']]
    });

    function format(details) {
        let rows = '';

        details.forEach(item => {
            rows += `
                <tr>
                    <td>${item.code}</td>
                    <td>${item.serial_number ?? '-'}</td>
                    <td class="text-end">${item.qty}</td>
                    <td class="text-end">${item.qty_process}</td>
                    <td>${item.rack ?? '-'}</td>
                    <td>${item.status}</td>
                </tr>
            `;
        });

        return `
            <table class="table table-sm table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Serial</th>
                        <th>Qty</th>
                        <th>Processed</th>
                        <th>Rack</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        `;
    }

    $('#orderTable tbody').on('click', 'td.details-control', function () {
        let tr = $(this).closest('tr');
        let row = table.row(tr);
        let orderId = tr.data('id');
        let btn = $(this);

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            btn.text('Detail');
            return;
        }

        btn.text('Loading...');

        $.get(orderDetailUrl.replace(':id', orderId))
            .done(res => {
                row.child(format(res.data)).show();
                tr.addClass('shown');
                btn.text('Hide');
            })
            .fail(() => {
                btn.text('Error');
                alert('Failed to load order detail');
            });
    });

});
</script>
@endsection
