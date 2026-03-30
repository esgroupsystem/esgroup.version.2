<!DOCTYPE html>
<html data-bs-theme="light" lang="en-US" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Jell Group of Company</title>

    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo e(asset('assets/img/favicons/esgroup-logo180x180.png')); ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo e(asset('assets/img/favicons/esgroup-logo32x32.png')); ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo e(asset('assets/img/favicons/esgroup-logo16x16.png')); ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo e(asset('assets/img/favicons/favicon.ico')); ?>">
    <link rel="manifest" href="<?php echo e(asset('assets/img/favicons/manifest.json')); ?>">

    <meta name="msapplication-TileImage" content="<?php echo e(asset('assets/img/favicons/esgroup-logo180x180.png')); ?>">
    <meta name="theme-color" content="#ffffff">

    <!-- JS Config -->
    <script src="<?php echo e(asset('assets/js/config.js')); ?>"></script>
    <script src="<?php echo e(asset('vendors/simplebar/simplebar.min.js')); ?>"></script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700%7cPoppins:300,400,500,600,700,800,900&display=swap"
        rel="stylesheet">

    <!-- Vendor CSS -->
    <link href="<?php echo e(asset('vendors/simplebar/simplebar.min.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('vendors/swiper/swiper-bundle.min.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('vendors/leaflet/leaflet.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('vendors/leaflet.markercluster/MarkerCluster.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('vendors/leaflet.markercluster/MarkerCluster.Default.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('vendors/flatpickr/flatpickr.min.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('vendors/dropzone/dropzone.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('vendors/choices/choices.min.css')); ?>" rel="stylesheet">

    <!-- Theme CSS -->
    <link href="<?php echo e(asset('assets/css/theme-rtl.css')); ?>" rel="stylesheet" id="style-rtl">
    <link href="<?php echo e(asset('assets/css/theme.css')); ?>" rel="stylesheet" id="style-default">
    <link href="<?php echo e(asset('assets/css/user-rtl.css')); ?>" rel="stylesheet" id="user-style-rtl">
    <link href="<?php echo e(asset('assets/css/user.css')); ?>" rel="stylesheet" id="user-style-default">

    <!-- RTL Handling -->
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

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>

<body>

    <!-- ✅ Toast Container -->
    <div class="flash-toast-container position-fixed top-0 end-0 p-3" style="z-index: 99999;"></div>

    <!-- ✅ Laravel Flash Message -->
    <?php echo $__env->make('flash::message', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- ✅ Page Content -->
    <?php echo $__env->yieldContent('content'); ?>

    <!-- Vendor JS -->
    <script src="<?php echo e(asset('vendors/popper/popper.min.js')); ?>"></script>
    <script src="<?php echo e(asset('vendors/bootstrap/bootstrap.min.js')); ?>"></script>
    <script src="<?php echo e(asset('vendors/anchorjs/anchor.min.js')); ?>"></script>
    <script src="<?php echo e(asset('vendors/is/is.min.js')); ?>"></script>
    <script src="<?php echo e(asset('vendors/chart/chart.umd.js')); ?>"></script>
    <script src="<?php echo e(asset('vendors/leaflet/leaflet.js')); ?>"></script>
    <script src="<?php echo e(asset('vendors/leaflet.markercluster/leaflet.markercluster.js')); ?>"></script>
    <script src="<?php echo e(asset('vendors/leaflet.tilelayer.colorfilter/leaflet-tilelayer-colorfilter.min.js')); ?>"></script>
    <script src="<?php echo e(asset('vendors/countup/countUp.umd.js')); ?>"></script>
    <script src="<?php echo e(asset('vendors/echarts/echarts.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/data/world.js')); ?>"></script>
    <script src="<?php echo e(asset('vendors/dayjs/dayjs.min.js')); ?>"></script>
    <script src="<?php echo e(asset('vendors/flatpickr/flatpickr.min.js')); ?>"></script>
    <script src="<?php echo e(asset('vendors/fontawesome/all.min.js')); ?>"></script>
    <script src="<?php echo e(asset('vendors/lodash/lodash.min.js')); ?>"></script>
    <script src="<?php echo e(asset('vendors/list.js/list.min.js')); ?>"></script>
    <script src="<?php echo e(asset('vendors/swiper/swiper-bundle.min.js')); ?>"></script>
    <script src="<?php echo e(asset('vendors/typed.js/typed.umd.js')); ?>"></script>
    <script src="<?php echo e(asset('vendors/dropzone/dropzone-min.js')); ?>"></script>
    <script src="<?php echo e(asset('vendors/choices/choices.min.js')); ?>"></script>

    <script>
        if (window.Dropzone) Dropzone.autoDiscover = false;
        window.__dropzone_initialized = false;
    </script>

    <!-- Theme Scripts -->
    <script src="<?php echo e(asset('assets/js/theme.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/theme-control.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/theme-dashboard-fixed.js')); ?>"></script>

    <!-- SimpleBar Init -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.my-scrollbar').forEach(el => new SimpleBar(el));
        });
    </script>

    <!-- ✅ Toast Messages -->
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
                setTimeout(() => toast.classList.remove("show"), 3000);
            });
            flashMessages.forEach(e => e.remove());
        });
    </script>

    <!-- ✅ Choices.js Initialization -->
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

    <?php echo $__env->yieldPushContent('scripts'); ?>

</body>

</html>
<?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/layouts/landing.blade.php ENDPATH**/ ?>