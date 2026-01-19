@extends('layouts.master')

@section('content')
<style>
    /* ✅ Freeze Table Header */
    .table-wrapper {
        max-height: 600px;
        overflow-y: auto;
        overflow-x: auto;
        position: relative;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
    }

    .table-planning {
        margin-bottom: 0;
    }

    .table-planning thead th {
        position: sticky;
        top: 0;
        background-color: #5a9fd4;
        color: white;
        z-index: 10;
        box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
    }

    /* ✅ Freeze First Column (Cutting Center) */
    .table-planning tbody th {
        position: sticky;
        left: 0;
        background-color: #f8f9fa;
        z-index: 5;
        box-shadow: 2px 0 2px -1px rgba(0, 0, 0, 0.1);
        font-weight: 600;
    }

    /* ✅ Freeze Second Column (Code) */
    .table-planning tbody td:first-child {
        position: sticky;
        left: 0;
        background-color: #ffffff;
        z-index: 4;
        box-shadow: 2px 0 2px -1px rgba(0, 0, 0, 0.1);
        font-weight: 500;
    }

    /* ✅ Input Styling */
    .qty-input {
        width: 70px;
        text-align: center;
        padding: 4px 8px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        transition: all 0.2s;
        background-color: transparent;
    }

    .qty-input:focus {
        border-color: #5a9fd4;
        box-shadow: 0 0 0 0.2rem rgba(90, 159, 212, 0.15);
        outline: none;
    }

    .qty-input.is-dirty {
        border-color: #ffc107;
        background-color: #fff8e1;
    }

    .qty-input.is-valid {
        border-color: #28a745;
        background-color: #e8f5e9;
    }

    .qty-input.is-invalid {
        border-color: #dc3545;
        background-color: #ffebee;
    }

    /* ✅ Hover Effect */
    .table-planning tbody tr:hover {
        background-color: #f5f7fa;
    }

    /* ✅ Weekend Column Highlight - Abu-abu gelap */
    .weekend-col {
        background-color: #d6d8db !important;
    }

    .weekend-col .qty-input {
        background-color: #e9ecef;
    }

    /* ✅ Weekday Column - Putih bersih */
    .weekday-col {
        background-color: #ffffff;
    }

    /* ✅ Header Cell Styling */
    .table-planning thead th {
        font-size: 12px;
        padding: 10px 8px;
        white-space: nowrap;
        text-align: center;
    }

    .table-planning tbody td,
    .table-planning tbody th {
        font-size: 13px;
        padding: 8px;
        vertical-align: middle;
    }

    /* ✅ Scrollbar Styling */
    .table-wrapper::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }

    .table-wrapper::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 5px;
    }

    .table-wrapper::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 5px;
    }

    .table-wrapper::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* ✅ Header weekend styling */
    .table-planning thead th.weekend-col {
        background-color: #7f8c8d;
        color: white;
    }

    /* ✅ Row striping untuk readability */
    .table-planning tbody tr:nth-child(even) {
        background-color: #fafbfc;
    }

    .table-planning tbody tr:nth-child(even) .weekend-col {
        background-color: #c8cbce !important;
    }
</style>


<main>
    <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
        <div class="container-fluid px-4">
            <div class="page-header-content pt-4">
                <div class="row align-items-center">
                    <div class="col-auto mt-4">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="calendar"></i></div>
                            Planning Input
                        </h1>
                        <div class="page-header-subtitle">
                            Planning per Code (group by Cutting Center)
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container-fluid px-4 mt-n10">
        <div class="card">
            <div class="card-header">
                <div class="card-title"><strong>Filter</strong></div>
            </div>

            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Location Code</label>
                        <select id="location_code" class="form-select">
                            <option value="">-- Select Location --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Type</label>
                        <select id="type" class="form-select">
                            <option value="">-- Select Type --</option>
                            <option value="inbound">Inbound</option>
                            <option value="outbound">Outbound</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Month</label>
                        <input id="month" type="month" class="form-control"
                               value="{{ now()->format('Y-m') }}">
                    </div>
                </div>

                <hr class="my-4">

                <div id="tableWrapper">
                    <div class="text-muted">
                        Pilih Location dan Type untuk menampilkan planning.
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
(function () {

    const $location = $('#location_code');
    const $month = $('#month');
    const $type = $('#type');
    const $wrap = $('#tableWrapper');

    function toast(type, msg) {
        if (window.Swal) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type,
                title: msg,
                showConfirmButton: false,
                timer: 1800
            });
        } else {
            alert(msg);
        }
    }

    function loadMeta() {
        $.get("{{ route('planning.meta') }}")
            .done(res => {
                $location.empty().append('<option value="">-- Select Location --</option>');
                (res.locations || []).forEach(lc => {
                    $location.append(`<option value="${lc}">${lc}</option>`);
                });
            })
            .fail(() => toast('error', 'Gagal load master location'));
    }

    function loadTable() {
        const location_code = $location.val();
        const month = $month.val();
        const type = $type.val();

        if (!location_code || !type) {
            $wrap.html(`
                <div class="text-muted">
                    Pilih Location dan Type (Inbound / Outbound) untuk menampilkan tabel.
                </div>
            `);
            return;
        }

        $wrap.html(`<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>`);

        $.get("{{ route('planning.table') }}", {
            location_code,
            month,
            type
        })
        .done(res => {
            $wrap.html(res.html || '<div class="text-muted">No content.</div>');
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        })
        .fail(() => {
            $wrap.html(`
                <div class="alert alert-danger mb-0">
                    Gagal load table. Silakan coba lagi.
                </div>
            `);
        });
    }

    $location.on('change', loadTable);
    $month.on('change', loadTable);
    $type.on('change', loadTable);

    $(document).on('input', '.qty-input', function () {
        $(this).addClass('is-dirty');
    });

    $(document).on('blur', '.qty-input', function () {
        const $el = $(this);
        if (!$el.hasClass('is-dirty')) return;

        const payload = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            location_code: $el.data('location'),
            cutting_center: $el.data('cutting'),
            code: $el.data('code'),
            plan_date: $el.data('date'),
            type: $el.data('type'),
            qty: parseInt($el.val() || '0', 10)
        };

        $el.prop('disabled', true);

        $.post("{{ route('planning.upsert') }}", payload)
            .done(() => {
                $el.removeClass('is-dirty').addClass('is-valid');
                setTimeout(() => $el.removeClass('is-valid'), 800);
            })
            .fail(xhr => {
                $el.addClass('is-invalid');
                toast('error', xhr.responseJSON?.message || 'Gagal simpan');
                setTimeout(() => $el.removeClass('is-invalid'), 1500);
            })
            .always(() => $el.prop('disabled', false));
    });

    loadMeta();

})();
</script>
@endsection
