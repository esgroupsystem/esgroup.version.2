<!DOCTYPE html>
<html data-bs-theme="light" lang="en-US" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Jell Group of Company')</title>

    <!-- ===============================================-->
    <!--    Favicons -->
    <!-- ===============================================-->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/favicons/esgroup-logo180x180.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/favicons/esgroup-logo32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/favicons/esgroup-logo16x16.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/favicons/favicon.ico') }}">
    <link rel="manifest" href="{{ asset('assets/img/favicons/manifest.json') }}">
    <meta name="msapplication-TileImage" content="{{ asset('assets/img/favicons/esgroup-logo180x180.png') }}">
    <meta name="theme-color" content="#ffffff">

    <!-- ===============================================-->
    <!--    Falcon Config & Vendor Core -->
    <!-- ===============================================-->
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <script src="{{ asset('vendors/simplebar/simplebar.min.js') }}"></script>

    <!-- ===============================================-->
    <!--    Fonts -->
    <!-- ===============================================-->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700%7cPoppins:300,400,500,600,700,800,900&display=swap"
          rel="stylesheet">

    <!-- ===============================================-->
    <!--    Vendor CSS -->
    <!-- ===============================================-->
    <link href="{{ asset('vendors/simplebar/simplebar.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendors/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendors/leaflet/leaflet.css') }}" rel="stylesheet">
    <link href="{{ asset('vendors/leaflet.markercluster/MarkerCluster.css') }}" rel="stylesheet">
    <link href="{{ asset('vendors/leaflet.markercluster/MarkerCluster.Default.css') }}" rel="stylesheet">
    <link href="{{ asset('vendors/flatpickr/flatpickr.min.css') }}" rel="stylesheet">

    <!-- ===============================================-->
    <!--    Theme CSS -->
    <!-- ===============================================-->
    <link href="{{ asset('assets/css/theme-rtl.css') }}" rel="stylesheet" id="style-rtl">
    <link href="{{ asset('assets/css/theme.css') }}" rel="stylesheet" id="style-default">
    <link href="{{ asset('assets/css/user-rtl.css') }}" rel="stylesheet" id="user-style-rtl">
    <link href="{{ asset('assets/css/user.css') }}" rel="stylesheet" id="user-style-default">

    <!-- ===============================================-->
    <!--    RTL Setup -->
    <!-- ===============================================-->
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
    {{-- =============================================== --}}
    {{--                     MAIN CONTENT                --}}
    {{-- =============================================== --}}

    <!-- ✅ Toast Container (top-right) -->
    <div class="flash-toast-container position-fixed top-0 end-0 p-3" style="z-index: 99999;"></div>

    <!-- ✅ Flash message (Laracasts) -->
    @include('flash::message')

    <main class="main" id="top">
            
            @include('layouts.sidebar')

            <div class="content">

                @include('layouts.header')
                @yield('content')

            </div>
        </div>
    </main>

    {{-- =============================================== --}}
    {{--                  JS FILES & PLUGINS             --}}
    {{-- =============================================== --}}
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
    <script src="{{ asset('assets/js/theme.js') }}"></script>

    <!-- ===============================================-->
    <!--    Falcon Config -->
    <!-- ===============================================-->
    <script>
        window.CONFIG = {
            theme: 'light',
            navbarPosition: 'top',
            navbarStyle: 'transparent'
        };
    </script>

    <!-- ===============================================-->
    <!--    Falcon Theme Control -->
    <!-- ===============================================-->
    <script src="{{ asset('assets/js/theme-control.js') }}"></script>

    <!-- ===============================================-->
    <!--    Falcon Dashboard Scripts -->
    <!-- ===============================================-->
    <script src="{{ asset('assets/js/theme-dashboard-fixed.js') }}"></script>

    <!-- ===============================================-->
    <!--    SimpleBar Scroll Init -->
    <!-- ===============================================-->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.my-scrollbar').forEach(el => new SimpleBar(el));
        });
    </script>

    <!-- ✅ Convert Flash Messages into Falcon-style Toasts -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const flashMessages = document.querySelectorAll('.alert');

            flashMessages.forEach(msg => {
                let type = msg.classList.contains('alert-success') ? 'success' : 'error';
                let text = msg.innerText.trim();

                // ✅ Toast element
                const toast = document.createElement('div');
                toast.className = `toast show mb-2`;

                // ✅ Falcon clean UI style
                toast.style.minWidth = '320px';
                toast.style.background = '#f1f3f5'; // light clean gray
                toast.style.color = '#333'; // dark readable text
                toast.style.borderRadius = '8px';
                toast.style.padding = '12px 16px';
                toast.style.boxShadow = "0 4px 12px rgba(0,0,0,0.10)";
                toast.style.borderLeft = type === 'success' ?
                    '5px solid #28a745' // green
                    :
                    '5px solid #dc3545'; // red

                toast.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <div class="fw-semibold">${text}</div>
                    <button type="button" class="btn-close ms-3" data-bs-dismiss="toast"></button>
                </div>
            `;

                document.querySelector('.flash-toast-container').appendChild(toast);

                // ✅ Auto-hide in 3 seconds
                setTimeout(() => {
                    toast.classList.remove("show");
                    toast.classList.add("hide");
                }, 3000);
            });

            // ✅ Remove original flash alerts
            document.querySelectorAll('.alert').forEach(e => e.remove());
        });
    </script>
    

    @stack('scripts')
    
</body>
</html>
