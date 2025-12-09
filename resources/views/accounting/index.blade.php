@extends('layouts.app')
@section('title', 'Accounting - Approved POs')

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
                            <h5 class="fs-9 mb-0">Approved Purchase Orders</h5>
                        </div>

                        <div class="col-auto text-end">
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
                    @include('accounting.table')
                </div>
            </div>

        </div>
    </div>

    {{-- Include Accounting Modals --}}
    @foreach ($orders as $order)
        @include('accounting.modal', ['order' => $order])
    @endforeach

@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            // LIVE SEARCH
            let timer = null;
            document.getElementById("liveSearch").addEventListener("keyup", function() {
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

            // AJAX PAGINATION
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
