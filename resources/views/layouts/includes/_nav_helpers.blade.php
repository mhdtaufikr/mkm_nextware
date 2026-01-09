@php
    // active for exact route prefix/path
    function nav_active($patterns) {
        foreach ((array)$patterns as $p) {
            if (Request::is($p)) return 'active';
        }
        return '';
    }

    function nav_show($patterns) {
        foreach ((array)$patterns as $p) {
            if (Request::is($p)) return 'show';
        }
        return '';
    }
@endphp
