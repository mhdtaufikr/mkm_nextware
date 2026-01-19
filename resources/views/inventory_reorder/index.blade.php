@extends('layouts.master')

@section('content')
<main>
    {{-- PAGE HEADER (DIPERKECIL) --}}
    <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-6">
        <div class="container-fluid px-4">
            <div class="page-header-content pt-3">
                <h1 class="page-header-title mb-1">
                    <i data-feather="tool" class="me-1"></i>
                    Inventory Re-Order Level
                </h1>
                <div class="page-header-subtitle">
                    Set reorder level per item (autosave)
                </div>
            </div>
        </div>
    </header>

    {{-- CONTENT --}}
    <div class="container-fluid px-4 mt-n6">
        <div class="content-wrapper">
            <section class="content">
                <div class="container-fluid px-0">

                    {{-- FILTER CARD --}}
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Filter</h6>
                        </div>
                        <div class="card-body py-2">
                            <form method="GET" class="row g-2 align-items-end">
                                <div class="col-md-6">
                                    <label class="form-label mb-1">Location</label>
                                    <select name="location_code"
                                            class="form-select form-select-sm"
                                            onchange="this.form.submit()">
                                        <option value="">-- Select Location --</option>
                                        @foreach($locations as $loc)
                                            <option value="{{ $loc->location_code }}">
                                                {{ $loc->location_code }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- ALERT --}}
                    @include('partials.alert')

                    {{-- DATA --}}
                    @if(!empty($locationCode) && $groups->isNotEmpty())

                        @foreach($groups as $cc => $items)

                            <div class="card mb-3">
                                <div class="card-header py-2">
                                    <h6 class="card-title mb-0">
                                        Cutting Center: {{ $cc }}
                                    </h6>
                                </div>

                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:220px;">Code</th>
                                                    <th style="width:140px;">Reorder Level</th>
                                                    <th style="width:140px;">Reorder Qty</th>
                                                    <th style="width:160px;">Unit Price</th>
                                                    <th style="width:160px;" class="text-end">Value</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($items as $inv)
                                                    @php
                                                        $rule = $inv->reorderLevel;
                                                    @endphp
                                                    <tr>
                                                        <td class="fw-semibold">
                                                            {{ $inv->code }}
                                                        </td>

                                                        <td class="p-1">
                                                            <input type="number"
                                                                   min="0"
                                                                   class="form-control form-control-sm autosave"
                                                                   value="{{ $rule->reorder_level ?? 0 }}"
                                                                   data-id="{{ $inv->id }}"
                                                                   data-field="reorder_level">
                                                        </td>

                                                        <td class="p-1">
                                                            <input type="number"
                                                                   min="0"
                                                                   class="form-control form-control-sm autosave"
                                                                   value="{{ $rule->reorder_qty ?? 0 }}"
                                                                   data-id="{{ $inv->id }}"
                                                                   data-field="reorder_qty">
                                                        </td>

                                                        <td class="p-1">
                                                            <input type="number"
                                                                   step="0.01"
                                                                   min="0"
                                                                   class="form-control form-control-sm autosave"
                                                                   value="{{ $rule->unit_price ?? 0 }}"
                                                                   data-id="{{ $inv->id }}"
                                                                   data-field="unit_price">
                                                        </td>

                                                        <td class="text-end pe-2">
                                                            {{ number_format($rule->reorder_value ?? 0, 2) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        @endforeach

                    @elseif(!empty($locationCode))
                        <div class="alert alert-warning">
                            No inventory found for selected location.
                        </div>
                    @endif

                </div>
            </section>
        </div>
    </div>
</main>

{{-- AUTOSAVE --}}
<script>
$(document).on('blur', '.autosave', function () {

    const el = $(this);

    const payload = {
        _token: '{{ csrf_token() }}',
        inventory_id: el.data('id')
    };

    payload[el.data('field')] = el.val();

    el.prop('disabled', true);

    $.post('{{ route('inventory-reorder.autosave') }}', payload)
        .done(() => {
            el.addClass('is-valid');
            setTimeout(() => el.removeClass('is-valid'), 500);
        })
        .fail(() => {
            el.addClass('is-invalid');
            setTimeout(() => el.removeClass('is-invalid'), 1200);
        })
        .always(() => el.prop('disabled', false));
});
</script>
@endsection
