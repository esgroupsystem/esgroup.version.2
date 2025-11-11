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

            {{-- TOP CARD --}}
            <div class="card mb-4">
                <div class="bg-holder d-none d-lg-block bg-card"
                    style="background-image:url(/assets/img/icons/spot-illustrations/corner-4.png);">
                </div>

                <div class="card-body position-relative">
                    <div class="row">
                        <div class="col-lg-8">
                            <h3 class="mb-2">Tickets Job Order</h3>
                            <p class="text-muted">
                                Manage internal IT Job Order requests with sorting, search, filtering and pagination.
                            </p>
                        </div>
                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <a href="{{ route('tickets.createjoborder.index') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Create Ticket
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TABLE CARD WITH TABS --}}
            <div class="card mb-4">
                <div class="card-header pb-0">

                    {{-- TABS --}}
                    <ul class="nav nav-tabs" id="ticketTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending"
                                type="button" role="tab">
                                Pending
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="progress-tab" data-bs-toggle="tab" data-bs-target="#progress"
                                type="button" role="tab">
                                In Progress
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed"
                                type="button" role="tab">
                                Completed
                            </button>
                        </li>
                    </ul>
                </div>

                {{-- SEARCH + FILTER --}}
                <div class="p-3">
                    <div class="row g-3 align-items-center">

                        <div class="col-md-4">
                            <input class="form-control form-control-sm search" placeholder="Search Ticket...">
                        </div>

                        <div class="col-md-3">
                            <select class="form-select form-select-sm" data-list-filter="status">
                                <option value="">Filter Status</option>
                                <option value="Pending">Pending</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>

                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="tab-content">

                        {{-- ✅ PENDING TAB --}}
                        <div class="tab-pane fade show active" id="pending" role="tabpanel">
                            @include('tickets.partials.tab-table', [
                                'id' => 'pendingTable',
                                'list' => $pending,
                            ])
                        </div>

                        {{-- ✅ IN PROGRESS TAB --}}
                        <div class="tab-pane fade" id="progress" role="tabpanel">
                            @include('tickets.partials.tab-table', [
                                'id' => 'progressTable',
                                'list' => $progress,
                            ])
                        </div>

                        {{-- ✅ COMPLETED TAB --}}
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
                const active = document.querySelector(".tab-pane.active").id;
                return tables[active];
            }

            const searchInput = document.querySelector(".search");
            const filterSelect = document.querySelector('[data-list-filter="status"]');

            searchInput.addEventListener("keyup", () => {
                getActiveTable().search(searchInput.value);
            });

            filterSelect.addEventListener("change", () => {
                applyFilter();
            });

            function applyFilter() {
                const selected = filterSelect.value.toLowerCase();
                const t = getActiveTable();

                t.filter(item => {
                    if (!selected) return true;

                    const row = item.elm;
                    const statusCell = row.querySelector(".status");
                    const statusText = statusCell ? statusCell.innerText.trim().toLowerCase() : "";

                    return statusText.includes(selected);
                });
            }
            document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
                tab.addEventListener("shown.bs.tab", () => {
                    const table = getActiveTable();
                    table.search(searchInput.value);
                    applyFilter();
                });
            });

        });
    </script>
@endpush
