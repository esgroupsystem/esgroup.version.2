<?php $__env->startSection('title', 'Receiving Records'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container" data-layout="container">

        <script>
            var isFluid = JSON.parse(localStorage.getItem('isFluid'));
            if (isFluid) {
                var container = document.querySelector('[data-layout]');
                container.classList.remove('container');
                container.classList.add('container-fluid');
            }
        </script>

        <div class="content">

            <?php if(session('success')): ?>
                <div class="alert alert-success border-0 shadow-sm d-flex align-items-center" role="alert">
                    <span class="fas fa-check-circle me-2"></span>
                    <div><?php echo e(session('success')); ?></div>
                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center" role="alert">
                    <span class="fas fa-exclamation-circle me-2"></span>
                    <div><?php echo e(session('error')); ?></div>
                </div>
            <?php endif; ?>

            
            <div class="card border-0 shadow-sm mb-3 overflow-hidden">
                <div class="card-body bg-body-tertiary">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <h6 class="text-primary mb-1">Maintenance</h6>
                            <h4 class="mb-1 fw-bold">
                                <span class="fas fa-truck-loading text-primary me-2"></span>
                                Receiving Records
                            </h4>
                            <p class="text-muted mb-0 fs-10">
                                View delivered items and stock receiving transactions.
                            </p>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="<?php echo e(route('items.dashboard')); ?>" class="btn btn-falcon-default btn-sm">
                                <span class="fas fa-chart-bar me-1"></span> Stock Dashboard
                            </a>
                            <a href="<?php echo e(route('receivings.create')); ?>" class="btn btn-primary btn-sm">
                                <span class="fas fa-plus me-1"></span> New Receiving
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white border-bottom">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-8 col-lg-6">
                            <label class="form-label mb-1">Search Receiving Records</label>

                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-body-tertiary border-end-0">
                                    <span class="fas fa-search text-500"></span>
                                </span>

                                <input id="liveSearch" class="form-control border-start-0"
                                    placeholder="Search receiving number, delivered by, remarks..."
                                    value="<?php echo e(request('search')); ?>">
                            </div>

                            <div class="form-text fs-11">
                                Search by receiving number, delivered by, or remarks.
                            </div>
                        </div>

                        <div class="col-md-4 col-lg-6 text-md-end">
                            <?php if(isset($receivings)): ?>
                                <span class="badge badge-subtle-primary px-3 py-2 fs-10">
                                    Total Records: <?php echo e($receivings->total()); ?>

                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div id="receivingTable">
                    <?php echo $__env->make('maintenance.receive.table', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>

            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            let timer = null;
            const searchBox = document.getElementById("liveSearch");
            const tableWrapper = document.getElementById("receivingTable");

            function loadReceivings(url = null) {
                const value = searchBox.value || '';
                const fetchUrl = url || `?search=${encodeURIComponent(value)}`;

                fetch(fetchUrl, {
                        headers: {
                            "X-Requested-With": "XMLHttpRequest"
                        }
                    })
                    .then(res => res.text())
                    .then(html => {
                        tableWrapper.innerHTML = html;
                    })
                    .catch(err => console.error('Error loading receivings:', err));
            }

            searchBox.addEventListener("keyup", function() {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    loadReceivings();
                }, 300);
            });

            document.addEventListener("click", function(e) {
                const link = e.target.closest(".pagination a");
                if (link) {
                    e.preventDefault();
                    loadReceivings(link.getAttribute("href"));
                }
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/maintenance/receive/index.blade.php ENDPATH**/ ?>