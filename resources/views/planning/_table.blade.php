<div class="table-wrapper">
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
    .freeze-col {
    position: sticky;
    background-color: #fff;
    z-index: 10;
}

.freeze-col-1 {
    left: 0;
    border-right: 2px solid #dee2e6 !important;
}

.freeze-col-2 {
    left: 150px; /* Sesuaikan dengan lebar kolom Cutting Center */
    border-right: 2px solid #dee2e6 !important;
}

thead .freeze-col {
    z-index: 11;
    background-color: #f8f9fa;
}

</style>