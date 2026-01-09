@php
function nav_active($patterns) {
    foreach ((array)$patterns as $p) {
        if (\Illuminate\Support\Facades\Request::is($p)) return 'active';
    }
    return '';
}

function nav_show($patterns) {
    foreach ((array)$patterns as $p) {
        if (\Illuminate\Support\Facades\Request::is($p)) return 'show';
    }
    return '';
}
@endphp
