<!DOCTYPE html>
<html data-bs-theme="light" lang="en-US" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Jell Group of Company')</title>

    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/favicons/esgroup-logo180x180.png') }}">
    <link rel="icon" type="image/png" sizes="32x32"
        href="{{ asset('assets/img/favicons/esgroup-logo32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16"
        href="{{ asset('assets/img/favicons/esgroup-logo16x16.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/favicons/favicon.ico') }}">
    <link rel="manifest" href="{{ asset('assets/img/favicons/manifest.json') }}">
    <meta name="msapplication-TileImage" content="{{ asset('assets/img/favicons/esgroup-logo180x180.png') }}">
    <meta name="theme-color" content="#ffffff">

    <script src="{{ asset('assets/js/config.js') }}"></script>
    <script src="{{ asset('vendors/simplebar/simplebar.min.js') }}"></script>

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700%7cPoppins:300,400,500,600,700,800,900&display=swap"
        rel="stylesheet">

    <link href="{{ asset('vendors/simplebar/simplebar.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendors/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendors/leaflet/leaflet.css') }}" rel="stylesheet">
    <link href="{{ asset('vendors/leaflet.markercluster/MarkerCluster.css') }}" rel="stylesheet">
    <link href="{{ asset('vendors/leaflet.markercluster/MarkerCluster.Default.css') }}" rel="stylesheet">
    <link href="{{ asset('vendors/flatpickr/flatpickr.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendors/dropzone/dropzone.css') }}" rel="stylesheet">
    <link href="{{ asset('vendors/choices/choices.min.css') }}" rel="stylesheet">

    <link href="{{ asset('assets/css/theme-rtl.css') }}" rel="stylesheet" id="style-rtl">
    <link href="{{ asset('assets/css/theme.css') }}" rel="stylesheet" id="style-default">
    <link href="{{ asset('assets/css/user-rtl.css') }}" rel="stylesheet" id="user-style-rtl">
    <link href="{{ asset('assets/css/user.css') }}" rel="stylesheet" id="user-style-default">

    <script>
        const isRTL = JSON.parse(localStorage.getItem('isRTL'));
        if (isRTL) {
            document.getElementById('style-default').disabled = true;
            document.getElementById('user-style-default').disabled = true;
            document.querySelector('html').setAttribute('dir', 'rtl');
        } else {
            document.getElementById('style-rtl').disabled = true;
            document.getElementById('user-style-rtl').disabled = true;
        }
    </script>

    @stack('styles')
</head>

<body>

    <main class="main" id="top">
        @include('layouts.sidebar')

        <div class="content">

            <div class="flash-toast-container position-fixed top-0 end-0 p-3" style="z-index:99999;"></div>

            @include('flash::message')

            <div class="container-fluid px-4 px-lg-7">
                @include('layouts.header')
                @yield('content')
            </div>
        </div>
    </main>

    {{-- Scripts --}}
    <script src="{{ asset('vendors/popper/popper.min.js') }}"></script>
    <script src="{{ asset('vendors/bootstrap/bootstrap.min.js') }}"></script>
    <script src="{{ asset('vendors/anchorjs/anchor.min.js') }}"></script>
    <script src="{{ asset('vendors/is/is.min.js') }}"></script>
    <script src="{{ asset('vendors/chart/chart.umd.js') }}"></script>
    <script src="{{ asset('vendors/leaflet/leaflet.js') }}"></script>
    <script src="{{ asset('vendors/leaflet.markercluster/leaflet.markercluster.js') }}"></script>
    <script src="{{ asset('vendors/leaflet.tilelayer.colorfilter/leaflet-tilelayer-colorfilter.min.js') }}"></script>
    <script src="{{ asset('vendors/countup/countUp.umd.js') }}"></script>
    <script src="{{ asset('vendors/echarts/echarts.min.js') }}"></script>
    <script src="{{ asset('assets/data/world.js') }}"></script>
    <script src="{{ asset('vendors/dayjs/dayjs.min.js') }}"></script>
    <script src="{{ asset('vendors/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('vendors/fontawesome/all.min.js') }}"></script>
    <script src="{{ asset('vendors/lodash/lodash.min.js') }}"></script>
    <script src="{{ asset('vendors/list.js/list.min.js') }}"></script>
    <script src="{{ asset('vendors/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('vendors/typed.js/typed.umd.js') }}"></script>
    <script src="{{ asset('vendors/dropzone/dropzone-min.js') }}"></script>
    <script src="{{ asset('vendors/choices/choices.min.js') }}"></script>

    <script>
        if (window.Dropzone) Dropzone.autoDiscover = false;
        window.__dropzone_initialized = false;
    </script>

    <script src="{{ asset('assets/js/theme.js') }}"></script>
    <script src="{{ asset('assets/js/theme-control.js') }}"></script>
    <script src="{{ asset('assets/js/theme-dashboard-fixed.js') }}"></script>

    <script>
        window.CONFIG = {
            theme: 'light',
            navbarPosition: 'top',
            navbarStyle: 'transparent'
        };
    </script>

    {{-- Flash messages as toast --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const flashMessages = document.querySelectorAll('.alert');
            flashMessages.forEach(msg => {
                const text = msg.innerText.trim();
                const toast = document.createElement('div');
                toast.className = "toast show mb-2";
                toast.style = `
                    min-width:320px;background:#f1f3f5;color:#333;
                    border-radius:8px;padding:12px 16px;
                    box-shadow:0 4px 12px rgba(0,0,0,0.1);
                    border-left:5px solid #0d6efd;
                `;
                toast.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="fw-semibold">${text}</div>
                        <button type="button" class="btn-close ms-3" data-bs-dismiss="toast"></button>
                    </div>`;
                document.querySelector(".flash-toast-container").appendChild(toast);
                setTimeout(() => toast.classList.remove("show"), 2000);
            });
            flashMessages.forEach(e => e.remove());
        });
    </script>

    {{-- Choices.js initialization --}}
    <script>
        window.choicesInit = function() {};
        (function() {
            if (!window.Choices) return;
            const Original = window.Choices;
            class Patched extends Original {
                constructor(element, options = {}) {
                    if (element && element.choices) return element.choices;
                    if (options.allowHTML === undefined) options.allowHTML = true;
                    const instance = super(element, options);
                    element.dataset.choicesInit = "1";
                    element.choices = instance;
                    return instance;
                }
            }
            Object.setPrototypeOf(Patched, Original);
            window.Choices = Patched;
        })();
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".js-choice").forEach(select => {
                if (select.dataset.choicesInit === "1" || select.choices) return;
                new Choices(select, {
                    searchEnabled: true,
                    placeholder: true,
                    allowHTML: true
                });
                select.dataset.choicesInit = "1";
            });
        });
    </script>

    {{-- SweetAlert2 confirmation --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll("form.confirm-delete").forEach(form => {
                form.addEventListener("submit", function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'This action cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete it!',
                        background: '#fff',
                    }).then((result) => {
                        if (result.isConfirmed) form.submit();
                    });
                });
            });
        });
    </script>

    @stack('scripts')
</body>

</html>
