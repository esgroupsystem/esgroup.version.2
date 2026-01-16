@extends('layouts.app')
@section('title', 'Tickets Job Order')

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

            {{-- ===================== STAT CARDS ===================== --}}
            <div class="row g-3 mb-4">

                <div class="col-md-3">
                    <div class="card shadow-sm p-3">
                        <div class="d-flex justify-content-between">
                            <span class="fs-4 text-orange"><i class="fas fa-ticket-alt"></i></span>
                            <span class="badge bg-light text-dark">+19.01%</span>
                        </div>
                        <h6 class="mt-2">New Tickets Today</h6>
                        <h3 class="fw-bold">{{ $stats['new'] ?? 0 }}</h3>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm p-3">
                        <div class="d-flex justify-content-between">
                            <span class="fs-4 text-warning"><i class="fas fa-clock"></i></span>
                            <span class="badge bg-light text-dark">+19.01%</span>
                        </div>
                        <h6 class="mt-2">Pending Tickets</h6>
                        <h3 class="fw-bold">{{ $stats['pending'] ?? 0 }}</h3>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm p-3">
                        <div class="d-flex justify-content-between">
                            <span class="fs-4 text-purple"><i class="fas fa-spinner fa-spin"></i></span>
                            <span class="badge bg-light text-dark">+19.01%</span>
                        </div>
                        <h6 class="mt-2">In Progress</h6>
                        <h3 class="fw-bold">{{ $stats['progress'] ?? 0 }}</h3>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card shadow-sm p-3">
                        <div class="d-flex justify-content-between">
                            <span class="fs-4 text-success"><i class="fas fa-check-circle"></i></span>
                            <span class="badge bg-light text-dark">+19.01%</span>
                        </div>
                        <h6 class="mt-2">Completed</h6>
                        <h3 class="fw-bold">{{ $stats['completed'] ?? 0 }}</h3>
                    </div>
                </div>

            </div>

            {{-- MAIN CONTENT --}}
            <div class="row gy-4">

                {{-- LEFT SIDE --}}
                <div class="col-lg-9">

                    <div class="card mb-4">
                        <div class="bg-holder d-none d-lg-block bg-card"
                            style="background-image:url(/assets/img/icons/spot-illustrations/corner-4.png);"></div>

                        <div class="card-body position-relative">
                            <div class="row">
                                <div class="col-lg-8">
                                    <h3 class="mb-2">Tickets Job Order</h3>
                                    <p class="text-muted">Manage internal IT Job Order requests.</p>
                                </div>

                                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0 d-flex flex-column align-items-end gap-2">
                                    <a href="{{ route('tickets.createjoborder.index') }}" class="btn btn-primary w-auto">
                                        <i class="fas fa-plus me-1"></i> Create Ticket
                                    </a>

                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary dropdown-toggle w-auto" type="button"
                                            data-bs-toggle="dropdown">
                                            <i class="fas fa-file-export me-1"></i> Export
                                        </button>

                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('tickets.export', 'pdf') }}">
                                                    <i class="fas fa-file-pdf text-danger me-2"></i> PDF
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('tickets.export', 'excel') }}">
                                                    <i class="fas fa-file-excel text-success me-2"></i> Excel
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- TABLE WITH TABS --}}
                    <div class="card mb-4">
                        <div class="card-header pb-0">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <button class="nav-link active" data-bs-target="#pendingTable" data-tab="pending"
                                        data-bs-toggle="tab">
                                        Pending <span class="badge bg-secondary">{{ $stats['pending'] }}</span>
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-target="#progressTable" data-tab="progress"
                                        data-bs-toggle="tab">
                                        In Progress <span class="badge bg-info text-dark">{{ $stats['progress'] }}</span>
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-target="#completedTable" data-tab="completed"
                                        data-bs-toggle="tab">
                                        Completed <span class="badge bg-success">{{ $stats['completed'] }}</span>
                                    </button>
                                </li>
                            </ul>
                        </div>

                        {{-- SEARCH --}}
                        <div class="p-3">
                            <input class="form-control form-control-sm search" placeholder="Search Ticket...">
                        </div>

                        {{-- TABLES --}}
                        <div class="card-body p-0 tab-content">

                            @php
                                $isApprover = in_array(auth()->user()->role, ['IT Head', 'Developer']);
                            @endphp

                            <div id="pendingTable" class="tab-pane fade show active">
                                @include('tickets.partials.table', ['list' => $pending])
                            </div>

                            <div id="progressTable" class="tab-pane fade">
                                @include('tickets.partials.table', ['list' => $progress])
                            </div>

                            <div id="completedTable" class="tab-pane fade">
                                @include('tickets.partials.table', ['list' => $completed])
                            </div>

                        </div>

                    </div>

                </div>

                <div class="col-lg-3">

                    {{-- TICKET CATEGORIES --}}
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Ticket Categories</h6>
                        </div>
                        <div class="card-body p-2">
                            <ul class="list-group list-group-flush">
                                @foreach ($categories as $cat)
                                    <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                        <span class="text-muted" style="font-size: 13px; font-weight: 500;">
                                            {{ $cat['name'] }}
                                        </span>

                                        <span class="badge bg-primary" style="font-size: 11px; padding: 4px 8px;">
                                            {{ $cat['total'] }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    {{-- SUPPORT AGENTS --}}
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">IT Active Members</h6>
                        </div>
                        <div class="card-body p-2">
                            <ul class="list-group list-group-flush">
                                @foreach ($agents as $agent)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $agent->full_name }}
                                        <span class="badge bg-dark">{{ $agent->job_orders_assigned_count }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                </div>


            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            let timer = null;

            const search = document.querySelector(".search");

            search.addEventListener("keyup", function() {
                let value = this.value;
                let activePane = document.querySelector(".tab-pane.active");
                let tab = activePane.id.replace("Table", "");

                clearTimeout(timer);

                timer = setTimeout(() => {
                    fetch(`?search=${value}&tab=${tab}`, {
                            headers: {
                                "X-Requested-With": "XMLHttpRequest"
                            }
                        })
                        .then(res => res.text())
                        .then(html => {
                            activePane.innerHTML = html;
                        });
                }, 300);
            });

        });
    </script>
@endpush
@push('styles')
    <style>
        .pagination {
            font-size: 14px !important;
        }

        .pagination .page-link {
            padding: 4px 10px !important;
            font-size: 14px !important;
            border-radius: 4px !important;
            color: #4a4a4a !important;
            border: 1px solid #d0d5dd !important;
            background: #f8f9fa !important;
        }

        .pagination .page-item.active .page-link {
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
            color: #fff !important;
            font-weight: 600 !important;
        }

        .pagination .page-link:hover {
            background: #e2e6ea !important;
            border-color: #c4c9cf !important;
        }

        .pagination .page-item.disabled .page-link {
            opacity: .5 !important;
        }

        .pagination .page-item {
            margin: 0 2px !important;
        }
    </style>
@endpush
