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

                {{-- New Tickets --}}
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

                {{-- Pending --}}
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

                {{-- In Progress --}}
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

                {{-- Completed --}}
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

            {{-- =================== CONTENT ROW (LEFT + RIGHT SIDEBAR) =================== --}}
            <div class="row gy-4">

                {{-- LEFT SIDE: TABLE AREA --}}
                <div class="col-lg-9">

                    {{-- TOP CARD --}}
                    <div class="card mb-4">
                        <div class="bg-holder d-none d-lg-block bg-card"
                            style="background-image:url(/assets/img/icons/spot-illustrations/corner-4.png);"></div>

                        <div class="card-body position-relative">
                            <div class="row">
                                <div class="col-lg-8">
                                    <h3 class="mb-2">Tickets Job Order</h3>
                                    <p class="text-muted">
                                        Manage internal IT Job Order requests with sorting, search, and pagination.
                                    </p>
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

                    {{-- TABLE CARD WITH TABS --}}
                    <div class="card mb-4">
                        <div class="card-header pb-0">

                            {{-- TABS --}}
                            <ul class="nav nav-tabs" id="ticketTabs" role="tablist">

                                {{-- Pending --}}
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active d-flex align-items-center gap-2" id="pending-tab"
                                        data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">

                                        Pending
                                        <span class="badge rounded-pill bg-secondary">
                                            {{ $stats['pending'] }}
                                        </span>
                                    </button>
                                </li>

                                {{-- In Progress --}}
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link d-flex align-items-center gap-2" id="progress-tab"
                                        data-bs-toggle="tab" data-bs-target="#progress" type="button" role="tab">

                                        In Progress
                                        <span class="badge rounded-pill bg-info text-dark">
                                            {{ $stats['progress'] }}
                                        </span>
                                    </button>
                                </li>

                                {{-- Completed --}}
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link d-flex align-items-center gap-2" id="completed-tab"
                                        data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">

                                        Completed
                                        <span class="badge rounded-pill bg-success">
                                            {{ $stats['completed'] }}
                                        </span>
                                    </button>
                                </li>

                            </ul>

                        </div>

                        {{-- SEARCH BAR --}}
                        <div class="p-3">
                            <input class="form-control form-control-sm search" placeholder="Search Ticket...">
                        </div>

                        {{-- TABLE CONTENT --}}
                        <div class="card-body p-0">
                            <div class="tab-content">

                                {{-- Pending --}}
                                <div class="tab-pane fade show active" id="pending" role="tabpanel">
                                    @include('tickets.partials.tab-table', [
                                        'id' => 'pendingTable',
                                        'list' => $pending,
                                    ])
                                </div>

                                {{-- Progress --}}
                                <div class="tab-pane fade" id="progress" role="tabpanel">
                                    @include('tickets.partials.tab-table', [
                                        'id' => 'progressTable',
                                        'list' => $progress,
                                    ])
                                </div>

                                {{-- Completed --}}
                                <div class="tab-pane fade" id="completed" role="tabpanel">
                                    @include('tickets.partials.tab-table', [
                                        'id' => 'completedTable',
                                        'list' => $completed,
                                    ])
                                </div>

                            </div>
                        </div>

                    </div>

                </div>

                {{-- RIGHT SIDE: SIDEBAR --}}
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

            const tables = {
                pending: new List("pendingTable", {
                    valueNames: ["ticket_id", "requester", "job_type", "status", "date"],
                    page: 10,
                    pagination: true
                }),
                progress: new List("progressTable", {
                    valueNames: ["ticket_id", "requester", "job_type", "status", "date"],
                    page: 10,
                    pagination: true
                }),
                completed: new List("completedTable", {
                    valueNames: ["ticket_id", "requester", "job_type", "status", "date"],
                    page: 10,
                    pagination: true
                }),
            };

            function getActiveTable() {
                return tables[document.querySelector(".tab-pane.active").id];
            }

            const searchInput = document.querySelector(".search");
            searchInput.addEventListener("keyup", () => {
                getActiveTable().search(searchInput.value);
            });

            document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
                tab.addEventListener("shown.bs.tab", () => {
                    getActiveTable().search(searchInput.value);
                });
            });

        });
    </script>
@endpush
