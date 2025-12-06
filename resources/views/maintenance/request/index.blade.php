@extends('layouts.app')
@section('title', 'Purchase Orders')

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
            <div class="card mb-3" id="ordersTable"
                data-list='{"valueNames":["order","date","ship","status"],"page":10,"pagination":true}'>

                <div class="card-header">
                    <div class="row flex-between-center">
                        <div class="col-auto">
                            <h5 class="fs-9 mb-0">Status Orders</h5>
                        </div>

                        <div class="col-auto text-end">
                            <a href="{{ route('request.create') }}" class="btn btn-falcon-default btn-sm">
                                <span class="fas fa-plus"></span>
                                <span class="ms-1 d-none d-sm-inline-block">New</span>
                            </a>
                            <button class="btn btn-falcon-default btn-sm mx-2">
                                <span class="fas fa-filter"></span>
                                <span class="ms-1 d-none d-sm-inline-block">Filter</span>
                            </button>
                            <button class="btn btn-falcon-default btn-sm">
                                <span class="fas fa-external-link-alt"></span>
                                <span class="ms-1 d-none d-sm-inline-block">Export</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive scrollbar">
                        <table class="table table-sm align-middle fs-10 mb-0">
                            <thead class="bg-200">
                                <tr>
                                    <th class="text-900 align-middle">Order</th>
                                    <th class="text-900 align-middle text-center">Date</th>
                                    <th class="text-900 align-middle text-center">Ship To</th>
                                    <th class="text-900 align-middle text-center">Status</th>
                                    <th style="width:40px;"></th>
                                </tr>
                            </thead>

                            <tbody class="list" id="table-orders-body">
                                @foreach ($orders as $order)
                                    <tr class="btn-reveal-trigger">

                                        <td class="order py-3 align-middle white-space-nowrap">
                                            <a href="#" data-bs-toggle="modal"
                                                data-bs-target="#poModal{{ $order->id }}">
                                                <strong>{{ $order->po_number }}</strong>
                                            </a>
                                            by <strong>{{ $order->requester->full_name }}</strong><br>
                                            <a>{{ $order->requester->email }}</a>
                                        </td>

                                        <td class="date py-3 align-middle text-center">
                                            {{ $order->created_at->format('d/m/Y') }}
                                        </td>

                                        <td class="ship py-3 align-middle text-center">
                                            {{ $order->garage }}<br>
                                            <span class="text-500">Purchase Order</span>
                                        </td>

                                        <td class="status py-3 align-middle text-center">
                                            @if ($order->status == 'Approved')
                                                <span class="badge badge rounded-pill badge-subtle-success">
                                                    Approved <span class="fas fa-check ms-1"></span>
                                                </span>
                                            @else
                                                <span class="badge badge rounded-pill badge-subtle-warning">
                                                    Pending <span class="fas fa-stream ms-1"></span>
                                                </span>
                                            @endif
                                        </td>

                                        <td class="py-3 align-middle text-end">
                                            <div class="dropdown">
                                                <button class="btn btn-link btn-sm text-600 dropdown-toggle btn-reveal"
                                                    type="button" id="dropdown-{{ $order->id }}"
                                                    data-bs-toggle="dropdown">
                                                    <span class="fas fa-ellipsis-h fs-10"></span>
                                                </button>

                                                <div class="dropdown-menu dropdown-menu-end py-0">
                                                    <div class="py-2">
                                                        <form action="{{ route('request.update', $order->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('PUT')

                                                            <button type="submit" name="status" value="Approved"
                                                                class="dropdown-item">Approved</button>

                                                            <button type="submit" name="status" value="Pending"
                                                                class="dropdown-item">Pending</button>

                                                            <div class="dropdown-divider"></div>

                                                            <a class="dropdown-item text-danger" href="#">Delete</a>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-center">
                        <button class="btn btn-sm btn-falcon-default me-1" data-list-pagination="prev">
                            <span class="fas fa-chevron-left"></span>
                        </button>

                        <ul class="pagination mb-0"></ul>

                        <button class="btn btn-sm btn-falcon-default ms-1" data-list-pagination="next">
                            <span class="fas fa-chevron-right"></span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @foreach ($orders as $order)
        <div class="modal fade" id="poModal{{ $order->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title">
                            <span class="fas fa-file-invoice text-primary me-2"></span>
                            Purchase Order <strong>{{ $order->po_number }}</strong>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="card shadow-none border-0 mb-3 bg-100">
                            <div class="card-body p-3">
                                <h6 class="fw-bold text-primary mb-3">
                                    <span class="fas fa-user me-1"></span> Request Information
                                </h6>
                                <div class="row g-3 fs-10">
                                    <div class="col-md-6"><strong>Requested
                                            By:</strong><br>{{ $order->requester->full_name }}</div>
                                    <div class="col-md-6"><strong>Email:</strong><br>{{ $order->requester->email }}</div>
                                    <div class="col-md-6"><strong>Date
                                            Created:</strong><br>{{ $order->created_at->format('d/m/Y') }}</div>
                                    <div class="col-md-6"><strong>Garage:</strong><br>{{ $order->garage }}</div>
                                </div>
                            </div>
                        </div>
                        <hr class="my-3">
                        <h6 class="fw-bold text-primary mb-2"><span class="fas fa-box me-1"></span> Item List </h6>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0 table-hover">
                                <thead class="bg-200">
                                    <tr>
                                        <th class="ps-2">Category</th>
                                        <th class="ps-2">Product Name</th>
                                        <th class="text-center">Unit</th>
                                        <th class="text-center">Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($order->items as $item)
                                        <tr>
                                            <td class="ps-2">
                                                {{ $item->product->category->name ?? '—' }}
                                            </td>
                                            <td class="ps-2">
                                                {{ $item->product->product_name }}
                                            </td>
                                            <td class="text-center">
                                                {{ $item->product->unit ?? 'pc' }}
                                            </td>
                                            <td class="text-center fw-bold">
                                                {{ $item->qty }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>

                    {{-- FOOTER --}}
                    <div class="modal-footer bg-light">
                        <button class="btn btn-falcon-default" data-bs-dismiss="modal">
                            <span class="fas fa-times me-1"></span> Close
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endforeach

@endsection
