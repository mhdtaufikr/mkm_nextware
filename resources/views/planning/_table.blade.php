<div class="table-wrapper">
    <table class="table table-sm table-bordered table-planning mb-0">
        <thead>
            <tr>
                <th rowspan="2" style="vertical-align: middle;">Cutting Center</th>
                <th rowspan="2" style="vertical-align: middle;">Code</th>
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
                            <th rowspan="{{ $codes->count() }}">{{ $cc }}</th>
                        @endif
                        <td>{{ $codeObj->code }}</td>
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
