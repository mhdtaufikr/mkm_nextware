<div class="col-sm-12">

    {{-- SUCCESS --}}
    @if (session('status') || session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i data-feather="check-circle" class="me-1"></i>
            <strong>{{ session('status') ?? session('success') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif


    {{-- FAILED / ERROR (custom) --}}
    @if (session('failed'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i data-feather="x-circle" class="me-1"></i>
            <strong>Process Failed</strong>
            <ul class="mb-0 mt-2 ps-3">
                @if (is_array(session('failed')))
                    @foreach (session('failed') as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                @else
                    <li>{{ session('failed') }}</li>
                @endif
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif


    {{-- ERROR LOGS (bulk process / import) --}}
    @if (session('errorLogs'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i data-feather="alert-triangle" class="me-1"></i>
            <strong>Data Process Failed</strong>
            <ul class="mb-0 mt-2 ps-3">
                @foreach (session('errorLogs') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif


    {{-- VALIDATION ERRORS --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i data-feather="alert-octagon" class="me-1"></i>
            <strong>Validation Error</strong>
            <ul class="mb-0 mt-2 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

</div>
