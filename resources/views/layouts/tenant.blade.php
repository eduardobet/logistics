<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Meta -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ config('app.description', 'Seal Control Panel.') }}">
    <meta name="author" content="Josu&eacute; Artaud and Eduardobet">

    <title>{{ config('app.name', 'Seal Logistics Control Panel') }}</title>

    <link href="{{ asset('css/slim.css') }}" rel="stylesheet">
</head>
<body>
    
        @yield('content')

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
</body>
</html>
