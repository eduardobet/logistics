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
    
        @yield('content')

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>
