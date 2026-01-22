<div class="table-wrapper" style="overflow-x: auto; position: relative;">
    <table class="table table-sm table-bordered table-planning mb-0">
        <thead>
            <tr>
                <th rowspan="2" class="freeze-col freeze-col-1" style="vertical-align: middle;">Cutting Center</th>
                <th rowspan="2" class="freeze-col freeze-col-2" style="vertical-align: middle;">Code</th>
                @foreach($dates as $d)
                    <th class="{{ in_array($d['weekday'], ['Sat', 'Sun']) ? 'weekend-col' : '' }}">
                        {{ $d['label'] }}<br>
                        <small>{{ $d['weekday'] }}</small>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($groups as $cc => $codes)
                @foreach($codes as $codeObj)
                    <tr>
                        @if($loop->first)
                            <th rowspan="{{ $codes->count() }}" class="freeze-col freeze-col-1">{{ $cc }}</th>
                        @endif
                        <td class="freeze-col freeze-col-2">{{ $codeObj->code }}</td>
                        @foreach($dates as $d)
                            @php
                                $val = $qtyMap[$cc][$codeObj->code][$d['date']] ?? 0;
                            @endphp
                            <td class="{{ in_array($d['weekday'], ['Sat', 'Sun']) ? 'weekend-col' : '' }}" style="text-align: center;">
                                <input type="number"
                                       class="qty-input"
                                       value="{{ $val }}"
                                       data-location="{{ $location_code }}"
                                       data-cutting="{{ $cc }}"
                                       data-code="{{ $codeObj->code }}"
                                       data-date="{{ $d['date'] }}"
                                       data-type="{{ $type }}"
                                       min="0">
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>

<style>
/* ==========================================
   FREEZE COLUMNS (Cutting Center & Code)
   ========================================== */
.table-wrapper {
    overflow-x: auto;
    position: relative;
    max-width: 100%;
}

.table-planning {
    border-collapse: separate;
    border-spacing: 0;
    min-width: max-content;
}

/* Freeze kolom 1 (Cutting Center) */
.freeze-col-1 {
    position: sticky;
    left: 0;
    z-index: 10;
    background-color: #fff;
    border-right: 2px solid #dee2e6 !important;
}

/* Freeze kolom 2 (Code) */
.freeze-col-2 {
    position: sticky;
    left: 120px; /* Sesuaikan dengan lebar kolom Cutting Center */
    z-index: 10;
    background-color: #fff;
    border-right: 2px solid #dee2e6 !important;
}

/* Header freeze harus lebih tinggi z-index */
thead .freeze-col {
    z-index: 11;
    background-color: #f8f9fa;
}

/* Weekend styling */
.weekend-col {
    background-color: #fff3cd !important;
}

/* Input styling */
.qty-input {
    width: 60px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    padding: 4px 8px;
    text-align: center;
}

.qty-input:focus {
    outline: none;
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

/* Hover effect untuk frozen columns */
.freeze-col-1:hover,
.freeze-col-2:hover {
    background-color: #f8f9fa;
}
</style>
