@php
    $nonce = app()->bound('csp_nonce') ? app('csp_nonce') : '';
@endphp
<!DOCTYPE html>
<html lang="en-US">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Jell Group of Company')</title>
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/favicons/esgroup-logo180x180.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicons/esgroup-logo32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicons/esgroup-logo16x16.png') }}">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; }
        body { font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif; -webkit-font-smoothing: antialiased; }
        img { max-width: 100%; }
        [hidden] { display: none !important; }
    </style>
    @stack('styles')
</head>

<body class="@yield('body_class')">
    @yield('content')

    @include('layouts.partials.toasts')

    @stack('scripts')
</body>

</html>
