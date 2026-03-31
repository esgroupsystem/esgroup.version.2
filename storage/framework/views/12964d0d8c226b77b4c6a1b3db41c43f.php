<?php $__env->startSection('title', 'Parts Out'); ?>

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
                                <span class="fas fa-tools text-primary me-2"></span>
                                Parts Out
                            </h4>
                            <p class="text-muted mb-0 fs-10">
                                Monitor issued and installed parts used for buses, cars, and other vehicles.
                            </p>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="<?php echo e(route('items.dashboard')); ?>" class="btn btn-falcon-default btn-sm">
                                <span class="fas fa-chart-bar me-1"></span> Stock Dashboard
                            </a>
                            <a href="<?php echo e(route('parts-out.create')); ?>" class="btn btn-primary btn-sm">
                                <span class="fas fa-plus me-1"></span> New Parts Out
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-8 col-lg-6">
                            <label class="form-label mb-1">Search Parts Out Records</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-body-tertiary border-end-0">
                                    <span class="fas fa-search text-500"></span>
                                </span>
                                <input type="text" id="searchInput" class="form-control border-start-0"
                                    placeholder="Search parts out no., mechanic, requester, JO no., date..."
                                    value="<?php echo e(request('search')); ?>">
                            </div>
                            <div class="form-text fs-11">
                                Search by parts out number, mechanic, requester, job order number, or date.
                            </div>
                        </div>

                        <div class="col-md-4 col-lg-6 text-md-end">
                            <?php if(isset($partsOuts)): ?>
                                <span class="badge badge-subtle-primary px-3 py-2 fs-10">
                                    Total Records: <?php echo e($partsOuts->total()); ?>

                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div id="partsOutTable">
                    <?php echo $__env->make('maintenance.parts_out.table', ['partsOuts' => $partsOuts], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const tableWrapper = document.getElementById('partsOutTable');
            let timeout = null;

            function loadTable(url = null) {
                const search = searchInput.value || '';
                const requestUrl = url || `<?php echo e(route('parts-out.index')); ?>?search=${encodeURIComponent(search)}`;

                fetch(requestUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        tableWrapper.innerHTML = html;
                        bindPagination();
                    })
                    .catch(error => console.error('Error loading table:', error));
            }

            function bindPagination() {
                tableWrapper.querySelectorAll('.pagination a').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();

                        const url = new URL(this.href, window.location.origin);
                        url.searchParams.set('search', searchInput.value || '');

                        loadTable(url.toString());
                    });
                });
            }

            searchInput.addEventListener('keyup', function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    loadTable();
                }, 400);
            });

            bindPagination();
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/maintenance/parts_out/index.blade.php ENDPATH**/ ?>