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

        {{-- TABLE CARD --}}
        <div class="card mb-4">
            <div class="card-header">
                <div class="row g-3 align-items-center">

                    <div class="col-md-4">
                        <input class="form-control form-control-sm search"
                               placeholder="Search Ticket...">
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
                <div id="ticketTable"
                     data-list='{
                         "valueNames":["ticket_id","requester","department","status","date"],
                         "page":10,
                         "pagination":true
                     }'>
                    <div class="table-responsive scrollbar">
                        <table class="table table-hover table-striped fs-10 mb-0">
                            <thead class="bg-200 text-900">
                                <tr>
                                    <th class="sort" data-sort="ticket_id">Ticket ID</th>
                                    <th class="sort" data-sort="requester">Requester</th>
                                    <th class="sort" data-sort="department">Department</th>
                                    <th class="sort" data-sort="status">Status</th>
                                    <th class="sort" data-sort="date">Date</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>

                            <tbody class="list">

                                {{-- SAMPLE ROW (Replace later with dynamic data) --}}
                                <tr>
                                    <td class="ticket_id">TCK-00123</td>
                                    <td class="requester">Juan Dela Cruz</td>
                                    <td class="department">Accounting</td>
                                    <td class="status">
                                        <span class="badge badge-subtle-warning">Pending</span>
                                    </td>
                                    <td class="date">2025-11-01</td>
                                    <td class="text-center">
                                        <a href="#" class="btn btn-sm btn-info text-white">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center my-3">
                        <button class="btn btn-sm btn-falcon-default me-1"
                                data-list-pagination="prev">
                            <span class="fas fa-chevron-left"></span>
                        </button>

                        <ul class="pagination mb-0"></ul>

                        <button class="btn btn-sm btn-falcon-default ms-1"
                                data-list-pagination="next">
                            <span class="fas fa-chevron-right"></span>
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
@endsection
