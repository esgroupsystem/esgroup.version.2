@extends('layouts.app')
@section('title', 'Items Management')

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
                    style="background-image:url(/assets/img/icons/spot-illustrations/corner-4.png);"></div>

                <div class="card-body position-relative">
                    <div class="row">
                        <div class="col-lg-8">
                            <h3 class="mb-2">Items Management</h3>
                            <p class="text-muted">Manage all items and assign them under categories.</p>
                        </div>

                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#itemModal"
                                onclick="openCreateItem()">
                                <i class="fas fa-plus me-1"></i> Add Item
                            </button>

                            <button class="btn btn-info ms-2" data-bs-toggle="modal" data-bs-target="#stockModal">
                                <i class="fas fa-boxes me-1"></i> View Stock
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ITEMS TABLE --}}
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Items List</h6>
                </div>

                {{-- SEARCH --}}
                <div class="p-3">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <input class="form-control form-control-sm search" placeholder="Search item...">
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div id="itemTable">
                        @include('maintenance.items.items_table', ['items' => $items])
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ITEM CREATE / EDIT MODAL --}}
    <div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-sm">

                <div class="modal-header bg-light">
                    <h5 class="modal-title text-900 fs-8" id="itemModalTitle">Add Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form id="itemForm" method="POST">
                    @csrf

                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category_id" id="itemCategory" required>
                                <option value="" disabled selected>Select Category</option>
                                @foreach ($categories as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Item Name</label>
                            <input type="text" class="form-control" name="product_name" id="itemName" required>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Unit</label>
                                <input type="text" class="form-control" name="unit" id="itemUnit">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Part Number</label>
                                <input type="text" class="form-control" name="part_number" id="itemPartNumber">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Details</label>
                                <textarea class="form-control" name="details" id="itemDetails" rows="3"></textarea>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer bg-light">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-save me-1"></i> Save
                        </button>
                        <button type="button" class="btn btn-falcon-default btn-sm" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>


    {{-- STOCK MODAL --}}
    <div class="modal fade" id="stockModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-sm">

                <div class="modal-header bg-light">
                    <h5 class="modal-title text-900 fs-8">
                        <span class="fas fa-boxes text-primary me-2"></span> Inventory Dashboard
                    </h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body bg-100">
                    <div id="stockTableContainer">
                        @include('maintenance.items.stock_table', ['products' => $stock])

                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button class="btn btn-falcon-default btn-sm" data-bs-dismiss="modal">
                        <span class="fas fa-times me-1"></span> Close
                    </button>
                </div>

            </div>
        </div>
    </div>

@endsection


@push('scripts')
    <script>
        /* ------------------------------
               AJAX PAGINATION INSIDE MODAL
            ------------------------------ */
        document.addEventListener("DOMContentLoaded", function() {

            function loadStockTable(url) {
                fetch(url, {
                        headers: {
                            "X-Requested-With": "XMLHttpRequest"
                        }
                    })
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById("stockTableContainer").innerHTML = html;
                        bindPaginationLinks();
                    });
            }

            function bindPaginationLinks() {
                document.querySelectorAll("#stockTableContainer .pagination a")
                    .forEach(link => {
                        link.addEventListener("click", function(e) {
                            e.preventDefault(); // prevent modal from closing
                            loadStockTable(this.href);
                        });
                    });
            }

            bindPaginationLinks();
        });

        /* ------------------------------
           ITEM MODAL HELPERS
        ------------------------------ */
        function openCreateItem() {
            document.getElementById("itemModalTitle").innerText = "Add Item";
            document.getElementById("itemForm").action = "{{ route('items.store') }}";
        }

        function openEditItem(id, category_id, name, unit, part_number, details) {
            document.getElementById("itemModalTitle").innerText = "Edit Item";
            document.getElementById("itemForm").action = "/maintenance/items/" + id;

            document.getElementById("itemCategory").value = category_id;
            document.getElementById("itemName").value = name;
            document.getElementById("itemUnit").value = unit;
            document.getElementById("itemPartNumber").value = part_number;
            document.getElementById("itemDetails").value = details;

            new bootstrap.Modal(document.getElementById('itemModal')).show();
        }
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
