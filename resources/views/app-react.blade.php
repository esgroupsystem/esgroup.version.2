<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Apply the saved theme before first paint to avoid a light/dark flash. --}}
    <script>
        (function() {
            try {
                var theme = localStorage.getItem('theme') || 'light';
                var dark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', dark);
            } catch (e) {}
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicons/esgroup-logo32x32.png') }}">

    @viteReactRefresh
    @vite(['resources/css/react.css', 'resources/js/react/app.tsx', "resources/js/react/pages/{$page['component']}.tsx"])
    @inertiaHead
</head>

<body class="font-sans">
    @inertia
</body>

</html>
