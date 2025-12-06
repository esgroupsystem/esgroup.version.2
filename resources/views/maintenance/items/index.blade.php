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

            {{-- 🧩 TOP CARD --}}
            <div class="card mb-4">
                <div class="bg-holder d-none d-lg-block bg-card"
                    style="background-image:url(/assets/img/icons/spot-illustrations/corner-4.png);">
                </div>

                <div class="card-body position-relative">
                    <div class="row">
                        <div class="col-lg-8">
                            <h3 class="mb-2">Items Management</h3>
                            <p class="text-muted">
                                Manage all items and assign them under categories.
                            </p>
                        </div>

                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#itemModal"
                                onclick="openCreateItem()">
                                <i class="fas fa-plus me-1"></i> Add Item
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 🧭 TABLE CARD --}}
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
                    <div id="itemTable"
                        data-list='{"valueNames":["item_name","item_category"],"page":10,"pagination":true}'>
                        <div class="table-responsive scrollbar">
                            <table class="table table-hover table-striped fs-10 mb-0">
                                <thead class="bg-200 text-900">
                                    <tr>
                                        <th class="sort" data-sort="item_name">Item Name</th>
                                        <th class="sort" data-sort="item_category">Category</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="list">
                                    <tr class="d-none">
                                        <td class="item_name"></td>
                                        <td class="item_category"></td>
                                        <td></td>
                                    </tr>

                                    @forelse ($products as $item)
                                        <tr class="align-middle">

                                            {{-- ITEM DESIGN (2 LINES) --}}
                                            <td class="item_name">
                                                <div class="fw-semibold text-110">
                                                    {{ $item->product_name }}
                                                    @if ($item->unit)
                                                        ({{ $item->unit }})
                                                    @endif
                                                </div>

                                                <div class="text-500
                                                 fs-12">
                                                    {{ $item->details ? $item->details : 'N/A' }}
                                                </div>
                                            </td>

                                            {{-- CATEGORY --}}
                                            <td class="item_category">
                                                <div class="fw-semibold text-110">{{ $item->category->name }}</div>
                                                @if ($item->part_number)
                                                    <div class="text-600 fs-9">#{{ $item->part_number }}</div>
                                                @endif
                                            </td>

                                            {{-- ACTIONS --}}
                                            <td class="text-center">
                                                <div class="dropdown font-sans-serif position-static">
                                                    <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                        type="button" data-bs-toggle="dropdown">
                                                        <span class="fas fa-ellipsis-h fs-10"></span>
                                                    </button>

                                                    <div class="dropdown-menu dropdown-menu-end border py-0 shadow-sm">
                                                        <div class="py-2">
                                                            <button class="dropdown-item"
                                                                onclick="openEditItem({{ $item->id }},
                                                                    '{{ $item->category_id }}',
                                                                    '{{ $item->product_name }}',
                                                                    '{{ $item->unit }}',
                                                                    '{{ $item->part_number }}',
                                                                    `{{ $item->details }}`)">
                                                                <i class="fas fa-edit me-2"></i> Edit
                                                            </button>

                                                            <form action="{{ route('items.destroy', $item->id) }}"
                                                                method="POST" class="d-inline confirm-delete">
                                                                @csrf @method('DELETE')
                                                                <button class="dropdown-item text-danger">
                                                                    <i class="fas fa-trash me-2"></i> Delete
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="item_name">No items found</td>
                                            <td class="item_category">—</td>
                                            <td class="text-end">—</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- PAGINATION --}}
                        <div class="d-flex justify-content-center my-3">
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
    </div>

    {{-- 🧾 ITEM MODAL --}}
    <div class="modal fade" id="itemModal" tabindex="-1">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">

                <form id="itemForm" method="POST" action="{{ route('items.store') }}">
                    @csrf

                    <div class="modal-header bg-light">
                        <h5 class="modal-title" id="itemModalTitle">Add Item</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <input type="hidden" id="itemId" name="id">

                        <div class="mb-3">
                            <label class="form-label">Category<span class="text-danger">*</span></label>
                            <select name="category_id" id="itemCategory" class="form-select" required>
                                <option value="">Select Category</option>
                                @foreach ($categories as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Product Name<span class="text-danger">*</span></label>
                            <input type="text" id="itemName" name="product_name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Unit<span class="text-danger">*</span></label>
                            <input type="text" id="itemUnit" name="unit" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Part #</label>
                            <input type="text" id="itemPartNumber" name="part_number" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Details</label>
                            <textarea id="itemDetails" name="details" class="form-control" rows="3"></textarea>
                        </div>

                    </div>

                    <div class="modal-footer bg-light">
                        <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        <button class="btn btn-primary btn-sm" type="submit">Save</button>
                    </div>

                </form>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const listjs = new List("itemTable", {
                valueNames: ["item_name", "item_category"],
                page: 10,
                pagination: true
            });

            document.querySelector(".search").addEventListener("keyup", e => {
                listjs.search(e.target.value);
            });
        });

        function openCreateItem() {
            document.getElementById("itemModalTitle").innerText = "Add Item";
            document.getElementById("itemForm").action = "{{ route('items.store') }}";

            document.getElementById("itemCategory").value = "";
            document.getElementById("itemName").value = "";
            document.getElementById("itemUnit").value = "";
            document.getElementById("itemPartNumber").value = "";
            document.getElementById("itemDetails").value = "";

            let method = document.querySelector('#itemForm input[name="_method"]');
            if (method) method.remove();
        }

        function openEditItem(id, category_id, name, unit, part_number, details) {
            document.getElementById("itemModalTitle").innerText = "Edit Item";
            document.getElementById("itemForm").action = "/maintenance/items/" + id;

            if (!document.querySelector('#itemForm input[name="_method"]')) {
                let m = document.createElement("input");
                m.type = "hidden";
                m.name = "_method";
                m.value = "PUT";
                document.getElementById("itemForm").appendChild(m);
            }

            document.getElementById("itemCategory").value = category_id;
            document.getElementById("itemName").value = name;
            document.getElementById("itemUnit").value = unit;
            document.getElementById("itemPartNumber").value = part_number;
            document.getElementById("itemDetails").value = details;

            new bootstrap.Modal(document.getElementById('itemModal')).show();
        }

        document.addEventListener("DOMContentLoaded", function() {
            const itemNameInput = document.getElementById("itemName");

            itemNameInput.addEventListener("input", function() {
                let words = this.value.split(" ");
                words = words.map(w => w.charAt(0).toUpperCase() + w.slice(1).toLowerCase());
                this.value = words.join(" ");
            });
        });
    </script>
@endpush
