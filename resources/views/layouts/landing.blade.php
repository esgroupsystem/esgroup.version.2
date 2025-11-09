<!DOCTYPE html>
<html data-bs-theme="light" lang="en-US" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Jell Group of Company</title>

    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="assets/img/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicons/favicon-16x16.png">
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicons/favicon.ico">
    <link rel="manifest" href="assets/img/favicons/manifest.json">

    <meta name="msapplication-TileImage" content="assets/img/favicons/mstile-150x150.png">
    <meta name="theme-color" content="#ffffff">

    <script src="assets/js/config.js"></script>
    <script src="vendors/simplebar/simplebar.min.js"></script>

    <!-- Styles -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700%7cPoppins:300,400,500,600,700,800,900&display=swap"
        rel="stylesheet">

    <link href="vendors/simplebar/simplebar.min.css" rel="stylesheet">
    <link href="assets/css/theme-rtl.css" rel="stylesheet" id="style-rtl">
    <link href="assets/css/theme.css" rel="stylesheet" id="style-default">
    <link href="assets/css/user-rtl.css" rel="stylesheet" id="user-style-rtl">
    <link href="assets/css/user.css" rel="stylesheet" id="user-style-default">

    <script>
        var isRTL = JSON.parse(localStorage.getItem('isRTL'));
        if (isRTL) {
            document.getElementById('style-default').disabled = true;
            document.getElementById('user-style-default').disabled = true;
            document.querySelector('html').setAttribute('dir', 'rtl');
        } else {
            document.getElementById('style-rtl').disabled = true;
            document.getElementById('user-style-rtl').disabled = true;
        }
    </script>
</head>

<body>

    <!-- ✅ Toast Container (top-right) -->
    <div class="flash-toast-container position-fixed top-0 end-0 p-3" style="z-index: 99999;"></div>

    <!-- ✅ Flash message (Laracasts) -->
    @include('flash::message')

    @yield('content')

    <!-- Scripts -->
    <script src="vendors/popper/popper.min.js"></script>
    <script src="vendors/bootstrap/bootstrap.min.js"></script>
    <script src="vendors/anchorjs/anchor.min.js"></script>
    <script src="vendors/is/is.min.js"></script>
    <script src="vendors/echarts/echarts.min.js"></script>
    <script src="vendors/fontawesome/all.min.js"></script>
    <script src="vendors/lodash/lodash.min.js"></script>
    <script src="vendors/list.js/list.min.js"></script>
    <script src="vendors/swiper/swiper-bundle.min.js"></script>

    <script src="assets/js/theme.js"></script>

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

</body>

</html>
