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

    <link href="{{ asset('css/tenant.css') }}" rel="stylesheet">
</head>
<body class="dasbhoard-3">

     <div class="noty-container"></div>   
    
        @yield('content')

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
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
    @yield('xtra_scripts')
</body>
</html>
