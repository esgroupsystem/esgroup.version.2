@extends('layouts.app')
@section('title', 'Stock Transfers')

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

            @if (session('error'))
                <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center" role="alert">
                    <span class="fas fa-exclamation-circle me-2"></span>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            {{-- HEADER CARD --}}
            <div class="card border-0 shadow-sm mb-3 overflow-hidden">
                <div class="card-body bg-body-tertiary">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div>
                            <h6 class="text-warning mb-1">Maintenance</h6>
                            <h4 class="mb-1 fw-bold">
                                <span class="fas fa-exchange-alt text-warning me-2"></span>
                                Stock Transfers
                            </h4>
                            <p class="text-muted mb-0 fs-10">
                                View and manage stock movement from one location to another.
                            </p>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('items.dashboard') }}" class="btn btn-falcon-default btn-sm">
                                <span class="fas fa-chart-bar me-1"></span> Stock Dashboard
                            </a>
                            <a href="{{ route('stock-transfers.create') }}" class="btn btn-primary btn-sm">
                                <span class="fas fa-plus me-1"></span> New Transfer
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- MAIN CARD --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-8 col-lg-6">
                            <label class="form-label mb-1">Search Transfer Records</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-body-tertiary border-end-0">
                                    <span class="fas fa-search text-500"></span>
                                </span>
                                <input type="text" id="liveSearch" class="form-control border-start-0"
                                    placeholder="Search transfer no., requester, receiver, remarks..."
                                    value="{{ $search ?? '' }}">
                            </div>
                            <div class="form-text fs-11">
                                Search by transfer number, requested by, received by, or remarks.
                            </div>
                        </div>

                        <div class="col-md-4 col-lg-6 text-md-end">
                            <span class="badge badge-subtle-warning px-3 py-2 fs-10">
                                Total Records: {{ $transfers->total() }}
                            </span>
                        </div>
                    </div>
                </div>

                <div id="transferTableWrapper">
                    @include('maintenance.stock_transfers.table', ['transfers' => $transfers])
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('liveSearch');
            const wrapper = document.getElementById('transferTableWrapper');
            let timeout = null;

            function loadTransfers(url = null) {
                const search = input.value || '';
                const requestUrl = url ||
                    `{{ route('stock-transfers.index') }}?search=${encodeURIComponent(search)}`;

                fetch(requestUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        wrapper.innerHTML = html;
                        bindPagination();
                    })
                    .catch(error => console.error('Error loading transfers:', error));
            }

            function bindPagination() {
                wrapper.querySelectorAll('.pagination a').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        loadTransfers(this.href);
                    });
                });
            }

            input.addEventListener('keyup', function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => loadTransfers(), 300);
            });

            bindPagination();
        });
    </script>
@endsection
