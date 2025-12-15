@extends('layouts.app')
@section('title', 'Purchase Orders Receiving')

@section('content')
    <div class="container" data-layout="container">

        {{-- Enable fluid layout if needed --}}
        <script>
            var isFluid = JSON.parse(localStorage.getItem('isFluid'));
            if (isFluid) {
                var container = document.querySelector('[data-layout]');
                container.classList.remove('container');
                container.classList.add('container-fluid');
            }
        </script>

        <div class="content">

            {{-- MAIN CARD --}}
            <div class="card mb-3" id="poReceiveCard">
                <div class="card-header">
                    <div class="row flex-between-center">
                        <div class="col-auto">
                            <h5 class="fs-9 mb-0">Receiving Purchase Orders</h5>
                        </div>

                        <div class="col-auto text-end">
                            <button class="btn btn-falcon-default btn-sm">
                                <span class="fas fa-filter"></span>
                                <span class="ms-1 d-none d-sm-inline-block">Filter</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- SEARCH --}}
                <div class="p-3">
                    <input id="liveSearch" class="form-control form-control-sm" placeholder="Search PO number..."
                        value="{{ request('search') }}">
                </div>

                {{-- TABLE --}}
                <div id="poReceiveTable">
                    @include('maintenance.receive.table')
                </div>

            </div>
        </div>

        {{-- =======================================
            GLOBAL MODAL FOR PO DETAILS (AJAX)
        ======================================== --}}
        <div class="modal fade" id="poDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header bg-light">
                        <h5 class="modal-title">
                            <span class="fas fa-file-invoice text-primary me-2"></span>
                            Purchase Order Details
                        </h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body" id="poDetailsContent">
                        <div class="text-center p-5">
                            <span class="spinner-border text-primary"></span>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button class="btn btn-falcon-default" data-bs-dismiss="modal">Close</button>
                    </div>

                </div>
            </div>
        </div>

        {{-- =======================================
            RECEIVE ITEM MODAL
        ======================================== --}}
        <div class="modal fade" id="receiveModal" tabindex="-1">
            <div class="modal-dialog">
                <form method="POST" id="receiveForm">
                    @csrf
                    <div class="modal-content">

                        <div class="modal-header bg-light">
                            <h5 class="modal-title">Receive Item</h5>
                            <button class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">

                            <div class="mb-3">
                                <label class="form-label">Product</label>
                                <input type="text" class="form-control" id="receiveItemName" disabled>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Remaining Qty</label>
                                <input type="number" class="form-control" id="remainingQty" disabled>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Received Qty Today</label>
                                <input type="number" name="received_qty" class="form-control" min="1" required>
                            </div>

                        </div>

                        <div class="modal-footer bg-light">
                            <button class="btn btn-primary">
                                <span class="fas fa-check me-1"></span> Confirm Receive
                            </button>

                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                <span class="fas fa-times me-1"></span> Cancel
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection


@push('scripts')
    <script>
        // OPEN GLOBAL DETAILS MODAL
        function openPOModal(id) {

            let modal = new bootstrap.Modal(document.getElementById('poDetailsModal'));
            modal.show();

            document.getElementById('poDetailsContent').innerHTML = `
        <div class='text-center p-5'>
            <span class='spinner-border text-primary'></span>
        </div>
    `;

            fetch("/received/po/receiving/" + id, {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                })
                .then(res => res.text())
                .then(html => {
                    document.getElementById('poDetailsContent').innerHTML = html;
                });
        }

        // SET RECEIVE ITEM DATA
        function setReceiveItem(id, name, remaining) {
            document.getElementById('receiveForm').action = "/received/po/item/" + id + "/receive";
            document.getElementById('receiveItemName').value = name;
            document.getElementById('remainingQty').value = remaining;
        }

        document.addEventListener("DOMContentLoaded", () => {

            let timer = null;
            const searchBox = document.getElementById("liveSearch");

            // AJAX SEARCH
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
                            document.getElementById("poReceiveTable").innerHTML = html;
                        });
                }, 300);
            });

            // AJAX PAGINATION
            document.addEventListener("click", function(e) {
                if (e.target.closest(".pagination a")) {
                    e.preventDefault();

                    fetch(e.target.getAttribute("href"), {
                            headers: {
                                "X-Requested-With": "XMLHttpRequest"
                            }
                        })
                        .then(res => res.text())
                        .then(html => {
                            document.getElementById("poReceiveTable").innerHTML = html;
                        });
                }
            });

        });
    </script>
@endpush
