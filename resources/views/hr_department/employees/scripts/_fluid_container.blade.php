<script>
    (function () {
        try {
            var isFluid = JSON.parse(localStorage.getItem('isFluid'));
            if (isFluid) {
                var container = document.querySelector('[data-layout]');
                if (!container) return;
                container.classList.remove('container');
                container.classList.add('container-fluid');
            }
        } catch (e) {}
    })();
</script>