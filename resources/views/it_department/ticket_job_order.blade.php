@extends('layouts.app')
@section('title', 'Tickets Job Order')

@section('content')

    <div class="people-page">

        {{-- TOP HEADER CARD --}}
        <div class="page-title-card">
            <div class="d-flex align-items-center gap-3">
                <div class="page-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>

                <div>
                    <h4 class="people-title mb-1">Tickets Job Order</h4>
                    <p class="people-subtitle mb-0">
                        Manage, monitor, and collaborate on internal IT job order requests.
                    </p>
                </div>
            </div>

            <a href="{{ route('tickets.createjoborder.index') }}" class="btn-add-member">
                <i class="fas fa-plus me-1"></i> Create Ticket
            </a>
        </div>

        {{-- MAIN TABLE CARD --}}
        <div class="table-panel">

            {{-- CARD HEADER --}}
            <div class="table-panel-header">
                <div>
                    <h6 class="policy-title mb-1">Ticket Monitoring</h6>
                    <p class="policy-subtitle mb-0">
                        Track all job orders, assigned personnel, priority, status, and completion progress.
                    </p>
                </div>

                <div class="toolbar-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" class="search" placeholder="Search ticket...">
                    </div>

                    <div class="dropdown">
                        <button class="tool-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            Export
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

            {{-- TABS --}}
            <div class="table-panel-tabs">
                <ul class="people-tabs nav" role="tablist">
                    <li class="nav-item">
                        <button class="people-tab active" data-bs-target="#pendingTable" data-tab="pending"
                            data-bs-toggle="tab" type="button">
                            Pending
                            <span>{{ $stats['pending'] ?? 0 }}</span>
                        </button>
                    </li>

                    <li class="nav-item">
                        <button class="people-tab" data-bs-target="#progressTable" data-tab="progress" data-bs-toggle="tab"
                            type="button">
                            In Progress
                            <span>{{ $stats['progress'] ?? 0 }}</span>
                        </button>
                    </li>

                    <li class="nav-item">
                        <button class="people-tab" data-bs-target="#completedTable" data-tab="completed"
                            data-bs-toggle="tab" type="button">
                            Completed
                            <span>{{ $stats['completed'] ?? 0 }}</span>
                        </button>
                    </li>
                </ul>
            </div>

            {{-- TABLE BODY --}}
            <div class="table-panel-body">
                <div class="tab-content">

                    <div id="pendingTable" class="tab-pane fade show active">
                        @include('it_department.people-table', ['list' => $pending])
                    </div>

                    <div id="progressTable" class="tab-pane fade">
                        @include('it_department.people-table', ['list' => $progress])
                    </div>

                    <div id="completedTable" class="tab-pane fade">
                        @include('it_department.people-table', ['list' => $completed])
                    </div>

                </div>
            </div>

        </div>

    </div>

@endsection

@push('styles')
    @include('it_department.css.index_style')
@endpush

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            let timer = null;
            const search = document.querySelector(".search");

            if (!search) return;

            search.addEventListener("keyup", function() {
                const value = this.value;
                const activePane = document.querySelector(".tab-pane.active");

                if (!activePane) return;

                const tab = activePane.id.replace("Table", "");

                clearTimeout(timer);

                timer = setTimeout(() => {
                    fetch(`?search=${encodeURIComponent(value)}&tab=${tab}`, {
                            headers: {
                                "X-Requested-With": "XMLHttpRequest"
                            }
                        })
                        .then(response => response.text())
                        .then(html => {
                            activePane.innerHTML = html;
                        });
                }, 300);
            });
        });

        document.addEventListener('DOMContentLoaded', function() {

            const activeTab = localStorage.getItem('ticket_active_tab');
            if (activeTab) {
                const tabButton = document.querySelector(
                    `.people-tab[data-tab="${activeTab}"]`
                );
                if (tabButton) {
                    bootstrap.Tab.getOrCreateInstance(tabButton).show();
                }
            }

            document.querySelectorAll('.people-tab').forEach(tab => {

                tab.addEventListener('shown.bs.tab', function() {

                    localStorage.setItem(
                        'ticket_active_tab',
                        this.dataset.tab
                    );
                });
            });
        });
    </script>
@endpush
