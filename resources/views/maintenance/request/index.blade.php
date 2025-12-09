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
            <div class="card mb-3" id="ordersCard">
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

                <div class="p-3">
                    <input id="liveSearch" class="form-control form-control-sm" placeholder="Search order..."
                        value="{{ request('search') }}">
                </div>

                <div id="ordersTable">
                    @include('maintenance.request.table')
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

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            // ---------------------------
            // AJAX SEARCH
            // ---------------------------
            let timer = null;
            const searchBox = document.getElementById("liveSearch");

            searchBox.addEventListener("keyup", function() {
                let value = this.value;

                clearTimeout(timer);
                timer = setTimeout(() => {
                    fetch(`?search=${value}`, {
                            headers: {
                                "X-Requested-With": "XMLHttpRequest"
                            }
                        })
                        .then(res => res.text())
                        .then(html => {
                            document.getElementById("ordersTable").innerHTML = html;
                        });
                }, 300);
            });

            // ---------------------------
            // AJAX PAGINATION 
            // (Clicking pagination links)
            // ---------------------------
            document.addEventListener("click", function(e) {
                if (e.target.closest(".pagination a")) {
                    e.preventDefault();
                    let url = e.target.getAttribute("href");

                    fetch(url, {
                            headers: {
                                "X-Requested-With": "XMLHttpRequest"
                            }
                        })
                        .then(res => res.text())
                        .then(html => {
                            document.getElementById("ordersTable").innerHTML = html;
                        });
                }
            });

        });
    </script>
@endpush
