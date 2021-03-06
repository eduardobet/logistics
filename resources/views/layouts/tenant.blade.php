<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Meta -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ config('app.description', 'Seal Control Panel.') }}">
    <meta name="author" content="Josu&eacute; Artaud and Eduardobet">

    <title>@yield('title', config('app.name', ''))</title>

    <link href="{{ asset(mix('css/tenant.css')) }}" rel="stylesheet">
    <style>
         @media (min-width: 392px) {
    .search-box {
      display: flex;
      width: 80%;
      margin-left: 5%;
    } }
    </style>
    @yield('xtra_styles')
</head>
<body class="dasbhoard-3">

     <div class="noty-container"></div>   
    
        @yield('content')

    <!-- Scripts -->
    @if (!isset($noJs))
    <script src="{{ mix('js/app.js') }}"></script>
    @endif
    <script>
        
        @if ($success = session()->get('flash_success'))
        new Noty({
            type: 'success',
            layout: 'top',
            text: '{{ $success }}',
            container: '.noty-container',
            killer: true,
            timeout: 2000,
        }).show();
        @endif

        @if ($error = session()->get('flash_error'))
        new Noty({
            type: 'error',
            layout: 'top',
            text: '{{ $error }}',
            container: '.noty-container',
            killer: true,
            timeout: 2000,
        }).show();
        @endif

        @if ($error = session()->get('flash_lock_error'))
        new Noty({
            type: 'error',
            layout: 'top',
            text: '{{ $error }}',
            container: '.noty-container',
            killer: true,
            timeout: 2000,
        }).show();
        @endif

        @if ($info = session()->get('flash_info'))
        new Noty({
            type: 'info',
            layout: 'top',
            text: '{{ $info }}',
            container: '.noty-container',
            killer: true,
            timeout: 2000,
        }).show();
        @endif

    </script>

    @if (!isset($noSearch))
    <script>
        $("#q").on('keyup', function (e) {
            if ( e.keyCode == 13 ) {
                doGlobalSearch()
            }
        });
        
        $("#btn-search").click(function() {
            doGlobalSearch()
        });
        
        function doGlobalSearch() {
            var q = $.trim($("#q").val());
            @if (auth()->check() && !auth()->user()->isClient())
                if (q) 
                    window.location = `{{ route('tenant.get.search', $tenant->domain) }}?q=${q}&cbranch_id={{ isset($branch) ? $branch->id : '' }}`;
            @else
                if (q) 
                    window.location = `{{ route('tenant.get.client-search', $tenant->domain) }}?q=${q}&cbranch_id={{ isset($branch) ? $branch->id : '' }}`;
            @endif
        }
    </script>
    @endif
    @yield('xtra_scripts')
</body>
</html>
