@php
    $nonce = app()->bound('csp_nonce') ? app('csp_nonce') : '';
@endphp
<!DOCTYPE html>
<html data-bs-theme="light" lang="en-US" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jell Group of Company</title>
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/favicons/esgroup-logo180x180.png') }}">
    <link rel="icon" type="image/png" sizes="32x32"
        href="{{ asset('assets/img/favicons/esgroup-logo32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16"
        href="{{ asset('assets/img/favicons/esgroup-logo16x16.png') }}">
    <link rel="shortcut icon" href="{{ asset('assets/img/favicons/favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700|Poppins:300,400,500,600,700,800,900"
        rel="stylesheet">

    @vite('resources/css/app.css')

    <link href="{{ asset('assets/css/theme.css') }}" rel="stylesheet" id="style-default">
    <link href="{{ asset('assets/css/user.css') }}" rel="stylesheet" id="user-style-default">

    <!-- RTL -->
    <script nonce="{{ $nonce }}">
        const isRTL = JSON.parse(localStorage.getItem('isRTL'));
        if (isRTL) {
            document.querySelector('html').setAttribute('dir', 'rtl');
        }
    </script>

    @stack('styles')
</head>

<body>

    <!-- Toast -->
    <div class="flash-toast-container position-fixed top-0 end-0 p-3" style="z-index: 99999;"></div>

    @include('flash::message')

    @yield('content')

    <script nonce="{{ $nonce }}" src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

    <!-- REQUIRED GLOBAL LIBS -->
    <script nonce="{{ $nonce }}" src="{{ asset('vendors/lodash/lodash.min.js') }}"></script>
    <script nonce="{{ $nonce }}" src="{{ asset('vendors/anchorjs/anchor.min.js') }}"></script>
    <script nonce="{{ $nonce }}" src="{{ asset('vendors/is/is.min.js') }}"></script>

    <!-- ✅ ADD THIS (IMPORTANT) -->
    <script nonce="{{ $nonce }}" src="{{ asset('vendors/bootstrap/bootstrap.min.js') }}"></script>

    <!-- VITE -->
    @vite('resources/js/app.js')

    <!-- FALCON -->
    <script nonce="{{ $nonce }}" src="{{ asset('assets/js/config.js') }}"></script>
    <script nonce="{{ $nonce }}" src="{{ asset('assets/js/theme.js') }}"></script>
    <script nonce="{{ $nonce }}" src="{{ asset('assets/js/theme-control.js') }}"></script>

    <!-- Toast script -->
    <script nonce="{{ $nonce }}">
        document.addEventListener("DOMContentLoaded", function() {
            const flashMessages = document.querySelectorAll('.alert');
            flashMessages.forEach(msg => {
                const text = msg.innerText.trim();
                const toast = document.createElement('div');
                toast.className = "toast show mb-2";
                toast.innerHTML = `
                    <div class="bg-light p-3 rounded shadow">
                        ${text}
                    </div>`;
                document.querySelector(".flash-toast-container").appendChild(toast);
                setTimeout(() => toast.remove(), 3000);
            });
            flashMessages.forEach(e => e.remove());
        });
    </script>

    @stack('scripts')

</body>

</html>
