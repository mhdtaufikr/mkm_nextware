@extends('layouts.master')

@section('content')
<main>
    <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
        <div class="container-fluid px-4">
            <div class="page-header-content pt-4">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto mt-4">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="calendar"></i></div>
                            Planning Input
                        </h1>
                        <div class="page-header-subtitle">Input qty per tanggal (autosave)</div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container-fluid px-4 mt-n10">
        @include('partials.alert')

        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">Filter</h3>
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
                        <label class="form-label">Month</label>
                        <input id="month" type="month" class="form-control" value="{{ now()->format('Y-m') }}">
                    </div>
                </div>


                <hr class="my-4">

                <div id="tableWrapper">
                    <div class="text-muted">Pilih Location, Cutting Center, dan Month untuk menampilkan tabel.</div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    (function () {
        const $location = $('#location_code');
        const $month = $('#month');
        const $wrap = $('#tableWrapper');

        function toast(type, msg) {
            if (window.Swal) {
                Swal.fire({ toast:true, position:'top-end', icon:type, title:msg, showConfirmButton:false, timer:1800 });
            } else {
                alert(msg);
            }
        }

        function loadMeta() {
            $.get("{{ route('planning.meta') }}")
                .done(res => {
                    if (!$location.data('loaded')) {
                        $location.empty().append(`<option value="">-- Select Location --</option>`);
                        (res.locations || []).forEach(lc => {
                            $location.append(`<option value="${lc}">${lc}</option>`);
                        });
                        $location.data('loaded', true);
                    }
                })
                .fail(() => toast('error', 'Gagal load master data.'));
        }

        function loadTable() {
            const location_code = $location.val();
            const month = $month.val();

            if (!location_code || !month) {
                $wrap.html(`<div class="text-muted">Pilih Location dan Month untuk menampilkan tabel.</div>`);
                return;
            }

            $wrap.html(`<div class="text-muted">Loading...</div>`);

            $.get("{{ route('planning.table') }}", { location_code, month })
              .done(res => {
                  $wrap.html(res.html || '<div class="text-muted">No content.</div>');
              })
              .fail(xhr => {
                  const msg =
                      xhr.responseJSON?.message ||
                      (xhr.responseJSON?.errors ? JSON.stringify(xhr.responseJSON.errors) : '') ||
                      xhr.responseText ||
                      'Gagal load table';

                  $wrap.html(`
                    <div class="alert alert-danger mb-0">
                      <div class="fw-bold">Gagal load table</div>
                      <pre class="mb-0" style="white-space:pre-wrap;">${msg}</pre>
                    </div>
                  `);
              });
        }

        // autosave qty
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
                plan_date: $el.data('date'),
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
                    toast('error', xhr.responseJSON?.message || 'Gagal simpan.');
                    setTimeout(() => $el.removeClass('is-invalid'), 1500);
                })
                .always(() => $el.prop('disabled', false));
        });

        // events
        $location.on('change', loadTable);
        $month.on('change', loadTable);

        // init
        loadMeta();
    })();
    </script>

@endsection
