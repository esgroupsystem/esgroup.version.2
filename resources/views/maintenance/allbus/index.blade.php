@extends('layouts.app')
@section('title', 'All Bus')

@section('content')
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

            @if (session('success'))
                <div class="alert alert-success border-0 shadow-sm d-flex align-items-center" role="alert">
                    <span class="fas fa-check-circle me-2"></span>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            <div class="card border-0 shadow-sm mb-3 overflow-hidden">
                <div class="card-body bg-body-tertiary">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <h6 class="text-primary mb-1">Fleet Management</h6>
                            <h4 class="mb-1 fw-bold">
                                <span class="fas fa-bus text-primary me-2"></span>
                                All Bus
                            </h4>
                            <p class="text-muted mb-0 fs-10">
                                Manage bus details including garage, body number, and plate number.
                            </p>
                        </div>

                        <a href="{{ route('allbus.create') }}" class="btn btn-primary btn-sm">
                            <span class="fas fa-plus me-1"></span> Add Bus
                        </a>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-8 col-lg-6">
                            <label class="form-label mb-1">Search Bus Records</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-body-tertiary border-end-0">
                                    <span class="fas fa-search text-500"></span>
                                </span>
                                <input type="text" id="searchInput" class="form-control border-start-0"
                                    placeholder="Search garage, name, body number, plate number..."
                                    value="{{ request('search') }}">
                            </div>
                        </div>

                        <div class="col-md-4 col-lg-6 text-md-end">
                            <span class="badge badge-subtle-primary px-3 py-2 fs-10">
                                Total Records: {{ $buses->total() }}
                            </span>
                        </div>
                    </div>
                </div>

                <div id="busTable">
                    @include('maintenance.allbus.table', ['buses' => $buses])
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const tableWrapper = document.getElementById('busTable');
            let timeout = null;

            function loadTable(url = null) {
                const search = searchInput.value || '';
                const requestUrl = url || `{{ route('allbus.index') }}?search=${encodeURIComponent(search)}`;

                fetch(requestUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            tableWrapper.innerHTML = data.html;
                            bindPagination();
                        }
                    });
            }

            function bindPagination() {
                tableWrapper.querySelectorAll('.pagination a').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();

                        const url = new URL(this.href);
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
@endsection
