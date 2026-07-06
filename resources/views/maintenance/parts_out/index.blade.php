@extends('layouts.app')

@section('title', 'Parts Out')

@push('styles')
    <style>
        .parts-out-hero {
            background:
                linear-gradient(135deg, rgba(var(--falcon-primary-rgb), .13), rgba(var(--falcon-warning-rgb), .06)),
                var(--falcon-card-bg);
        }

        .metric-card {
            transition: all .18s ease-in-out;
        }

        .metric-card:hover {
            transform: translateY(-1px);
            box-shadow: var(--falcon-box-shadow-sm);
        }

        .metric-icon {
            width: 42px;
            height: 42px;
            min-width: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            line-height: 1;
        }

        .metric-label {
            font-size: .72rem;
            color: var(--falcon-600);
            margin-bottom: .15rem;
        }

        .metric-value {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--falcon-900);
            line-height: 1.15;
        }

        .ui-icon {
            width: 30px;
            height: 30px;
            min-width: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            line-height: 1;
        }

        .ui-icon-sm {
            width: 24px;
            height: 24px;
            min-width: 24px;
            font-size: .75rem;
        }

        .parts-out-search-box .input-group-text,
        .parts-out-search-box .form-control,
        .parts-out-search-box .btn {
            min-height: 42px;
        }

        .parts-out-table th {
            white-space: nowrap;
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .045em;
            color: var(--falcon-700);
        }

        .parts-out-table td {
            vertical-align: middle;
            padding-top: .85rem;
            padding-bottom: .85rem;
        }

        .parts-out-number-chip {
            border-left: 3px solid var(--falcon-primary);
            min-width: 190px;
        }

        .parts-out-empty-state {
            min-height: 280px;
        }

        .table-loading-overlay {
            position: absolute;
            inset: 0;
            z-index: 5;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(var(--falcon-body-bg-rgb), .72);
            backdrop-filter: blur(1px);
        }

        .table-loading-overlay.show {
            display: flex;
        }

        @media (max-width: 767.98px) {
            .parts-out-actions {
                width: 100%;
            }

            .parts-out-actions .btn {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container" data-layout="container">
        <script>
            const isFluid = JSON.parse(localStorage.getItem('isFluid'));

            if (isFluid) {
                const container = document.querySelector('[data-layout]');
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

            {{-- HERO --}}
            <div class="card border-0 shadow-sm mb-3 overflow-hidden parts-out-hero">
                <div class="card-body p-4">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg">
                            <div class="d-flex align-items-start gap-3">
                                <div class="metric-icon bg-primary-subtle text-primary"
                                    style="width: 54px; height: 54px; font-size: 1.25rem;">
                                    <span class="fas fa-tools"></span>
                                </div>

                                <div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <h6 class="mb-0 text-primary fw-semibold">
                                            Maintenance Inventory
                                        </h6>

                                        <span class="badge badge-subtle-warning rounded-pill">
                                            Parts Out Module
                                        </span>
                                    </div>

                                    <h3 class="mb-1 fw-bold text-900">
                                        Parts Out Records
                                    </h3>

                                    <p class="mb-0 text-600">
                                        Monitor issued parts, vehicle usage, mechanics, job orders, stock deduction, and
                                        rollback status.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-auto">
                            <div class="d-flex flex-column flex-sm-row gap-2 parts-out-actions">
                                <a href="{{ route('items.dashboard') }}" class="btn btn-falcon-default">
                                    <span class="fas fa-chart-pie me-1"></span>
                                    Stock Dashboard
                                </a>

                                <a href="{{ route('parts-out.create') }}" class="btn btn-primary">
                                    <span class="fas fa-plus me-1"></span>
                                    New Parts Out
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SUMMARY CARDS --}}
            <div class="row g-3 mb-3">
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100 metric-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <div class="metric-icon bg-primary-subtle text-primary">
                                    <span class="fas fa-clipboard-list"></span>
                                </div>

                                <div>
                                    <div class="metric-label">Total Records</div>
                                    <div class="metric-value">
                                        {{ isset($partsOuts) ? number_format($partsOuts->total()) : 0 }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100 metric-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <div class="metric-icon bg-success-subtle text-success">
                                    <span class="fas fa-box-open"></span>
                                </div>

                                <div>
                                    <div class="metric-label">Visible Page Items</div>
                                    <div class="metric-value">
                                        {{ isset($partsOuts) ? number_format($partsOuts->sum(fn($row) => $row->items_count ?? $row->items->count())) : 0 }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100 metric-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <div class="metric-icon bg-info-subtle text-info">
                                    <span class="fas fa-bus"></span>
                                </div>

                                <div>
                                    <div class="metric-label">Vehicle Based</div>
                                    <div class="metric-value">Yes</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100 metric-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <div class="metric-icon bg-warning-subtle text-warning">
                                    <span class="fas fa-undo"></span>
                                </div>

                                <div>
                                    <div class="metric-label">Rollback</div>
                                    <div class="metric-value">Controlled</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TABLE CARD --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-body-tertiary border-bottom">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-7 col-xl-6">
                            <label for="searchInput" class="form-label fw-semibold mb-1">
                                Search Parts Out Records
                            </label>

                            <div class="input-group parts-out-search-box">
                                <span class="input-group-text bg-white border-end-0">
                                    <span class="fas fa-search text-500"></span>
                                </span>

                                <input type="search" id="searchInput" class="form-control border-start-0"
                                    placeholder="Search parts out no., mechanic, requester, JO no., vehicle..."
                                    value="{{ request('search') }}" autocomplete="off">

                                <button type="button" id="clearSearchBtn" class="btn btn-falcon-default">
                                    <span class="fas fa-times me-1"></span>
                                    Clear
                                </button>
                            </div>

                            <div class="form-text fs-10">
                                Result updates automatically after typing.
                            </div>
                        </div>

                        <div class="col-lg-5 col-xl-6">
                            <div class="d-flex justify-content-lg-end align-items-center gap-2 flex-wrap">
                                <span class="badge badge-subtle-primary rounded-pill px-3 py-2">
                                    <span class="fas fa-database me-1"></span>
                                    {{ isset($partsOuts) ? number_format($partsOuts->total()) : 0 }} record(s)
                                </span>

                                <span class="badge badge-subtle-secondary rounded-pill px-3 py-2">
                                    <span class="fas fa-clock me-1"></span>
                                    Updated {{ now()->format('M d, Y h:i A') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="position-relative">
                    <div id="tableLoadingOverlay" class="table-loading-overlay">
                        <div class="text-center">
                            <div class="spinner-border text-primary mb-2" role="status"></div>
                            <div class="fs-10 text-700 fw-semibold">Loading parts out records...</div>
                        </div>
                    </div>

                    <div id="partsOutTable" aria-live="polite">
                        @include('maintenance.parts_out.table', ['partsOuts' => $partsOuts])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const tableWrapper = document.getElementById('partsOutTable');
            const clearSearchBtn = document.getElementById('clearSearchBtn');
            const loadingOverlay = document.getElementById('tableLoadingOverlay');

            let timer = null;
            let controller = null;

            function setLoading(isLoading) {
                loadingOverlay.classList.toggle('show', isLoading);
            }

            function buildSearchUrl() {
                const url = new URL(window.location.href);
                const search = searchInput.value.trim();

                url.searchParams.delete('page');

                if (search) {
                    url.searchParams.set('search', search);
                } else {
                    url.searchParams.delete('search');
                }

                return url;
            }

            function updateBrowserUrl(url) {
                window.history.replaceState({}, '', url.toString());
            }

            async function loadTable(url = null) {
                if (controller) {
                    controller.abort();
                }

                controller = new AbortController();

                const fetchUrl = url ? new URL(url, window.location.origin) : buildSearchUrl();

                setLoading(true);

                try {
                    const response = await fetch(fetchUrl.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        },
                        signal: controller.signal
                    });

                    if (!response.ok) {
                        throw new Error(`Request failed: ${response.status}`);
                    }

                    const html = await response.text();

                    tableWrapper.innerHTML = html;
                    updateBrowserUrl(fetchUrl);
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        console.error(error);

                        tableWrapper.innerHTML = `
                            <div class="card-body">
                                <div class="alert alert-danger mb-0">
                                    <div class="fw-semibold mb-1">
                                        <span class="fas fa-exclamation-triangle me-1"></span>
                                        Unable to load parts out records.
                                    </div>
                                    <div class="fs-10">
                                        Refresh the page or check your connection.
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                } finally {
                    setLoading(false);
                }
            }

            searchInput.addEventListener('input', function() {
                clearTimeout(timer);

                timer = setTimeout(function() {
                    loadTable();
                }, 350);
            });

            clearSearchBtn.addEventListener('click', function() {
                if (!searchInput.value) {
                    return;
                }

                searchInput.value = '';
                loadTable();
                searchInput.focus();
            });

            document.addEventListener('click', function(event) {
                const paginationLink = event.target.closest('#partsOutTable .pagination a');

                if (!paginationLink) {
                    return;
                }

                event.preventDefault();
                loadTable(paginationLink.href);
            });
        });
    </script>
@endpush
