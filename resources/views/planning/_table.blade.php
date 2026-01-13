<div class="mb-2">
    <div class="small text-muted">
        Type: <b>{{ $type }}</b> |
        Location: <b>{{ $location_code }}</b> |
        Month: <b>{{ $month }}</b>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-sm align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th style="min-width:220px;">Cutting Center</th>

                @foreach($dates as $d)
                    <th class="text-center" style="min-width:90px;">
                        <div class="fw-semibold">{{ \Carbon\Carbon::parse($d['date'])->format('d') }}</div>
                        <div class="text-muted small">{{ \Carbon\Carbon::parse($d['date'])->format('D') }}</div>
                    </th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @forelse($cutting_centers as $cc)
                <tr>
                    <td class="fw-semibold text-nowrap bg-light">{{ $cc }}</td>

                    @foreach($dates as $d)
                        @php
                            $date = $d['date'];
                            $qty = (int) ($qtyMap[$cc][$date] ?? 0);
                        @endphp
                        <td class="p-1">
                            <input
                                type="number"
                                min="0"
                                class="form-control form-control-sm text-end qty-input"
                                value="{{ $qty }}"
                                data-location="{{ $location_code }}"
                                data-cutting="{{ $cc }}"
                                data-date="{{ $date }}"
                            />
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 1 + count($dates) }}" class="text-muted text-center py-3">
                        Tidak ada Cutting Center untuk location ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="text-muted small mt-2">
    * Baris = Cutting Center, kolom = tanggal. Autosave saat pindah cell.
</div>
