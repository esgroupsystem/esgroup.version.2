@extends('layouts.app')
@section('title', 'Create Purchase Order')

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
            <div class="card mb-3">
                <div class="bg-holder d-none d-lg-block bg-card"
                    style="background-image:url(/assets/img/icons/spot-illustrations/corner-4.png);opacity:0.7;">
                </div>

                <div class="card-body position-relative">
                    <h5>Purchase Order #: <span class="text-primary">{{ $po_number }}</span></h5>
                    <p class="fs-10">{{ now()->format('F d, Y h:i A') }}</p>

                    <div>
                        <strong class="me-2">Status:</strong>
                        <div class="badge rounded-pill badge-subtle-warning fs-11">
                            Pending <span class="fas fa-clock ms-1"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FORM CARD --}}
            <div class="card mb-3">
                <div class="card-body">

                    <form action="{{ route('request.store') }}" method="POST">
                        @csrf

                        {{-- REQUESTER INFO --}}
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <h5 class="mb-3 fs-9">Requester Information</h5>

                                <p class="mb-1 fs-10"><strong>Name:</strong> {{ auth()->user()->full_name }}</p>
                                <p class="mb-1 fs-10"><strong>Email:</strong> {{ auth()->user()->email }}</p>

                                <label class="form-label fs-9 mt-2">Garage Location</label>
                                <select class="form-select form-select-sm" name="garage" required>
                                    <option value="">Select</option>
                                    <option value="Mirasol">Mirasol</option>
                                    <option value="Balintawak">Balintawak</option>
                                </select>
                            </div>
                        </div>

                        <hr>

                        {{-- PRODUCT TABLE --}}
                        <div class="table-responsive fs-10">
                            <table class="table table-striped border-bottom" id="poTable">
                                <thead class="bg-200">
                                    <tr>
                                        <th class="border-0 text-900 text-center" style="width: 10%">Category</th>
                                        <th class="border-0 text-900 text-center" style="width: 20%">Product</th>
                                        <th class="border-0 text-900 text-center" style="width: 5%">Qty</th>
                                        <th class="border-0 text-center" style="width: 3%">Action</th>
                                    </tr>
                                </thead>

                                <tbody id="poItems">

                                    {{-- Default Row --}}
                                    <tr>
                                        {{-- CATEGORY --}}
                                        <td class="align-middle text-center">
                                            <select class="form-select form-select-sm category_select" name="category_id[]"
                                                required>
                                                <option value="">Select</option>
                                                @foreach ($categories as $cat)
                                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                                @endforeach
                                            </select>
                                        </td>

                                        {{-- PRODUCT --}}
                                        <td class="align-middle text-center">
                                            <select class="form-select form-select-sm product_select" name="product_id[]"
                                                required>
                                                <option value="">Select Product</option>
                                            </select>
                                        </td>

                                        {{-- QTY --}}
                                        <td class="align-middle text-center">
                                            <input type="number" min="1"
                                                class="form-control form-control-sm text-center" name="qty[]" required>
                                        </td>

                                        {{-- REMOVE --}}
                                        <td class="text-center align-middle">
                                            <button type="button" class="btn btn-sm btn-danger removeRow">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>

                        {{-- ADD ROW --}}
                        <button type="button" class="btn btn-falcon-default btn-sm mt-2" id="addRow">
                            <i class="fas fa-plus"></i> Add Item
                        </button>

                        {{-- SUBMIT --}}
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary btn-sm">Submit Purchase Order</button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection


@push('scripts')
    <script>
        let products = @json($products);

        // ADD ROW
        document.getElementById("addRow").addEventListener("click", function() {
            let row = `
        <tr>
            <td class="align-middle text-center">
                <select class="form-select form-select-sm category_select" name="category_id[]" required>
                    <option value="">Select</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </td>

            <td class="align-middle text-center">
                <select class="form-select form-select-sm product_select" name="product_id[]" required>
                    <option value="">Select Product</option>
                </select>
            </td>

            <td class="align-middle text-center">
                <input type="number" min="1" class="form-control form-control-sm text-center"
                       name="qty[]" required>
            </td>

            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-danger removeRow">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
        `;
            document.getElementById("poItems").insertAdjacentHTML("beforeend", row);
        });

        // REMOVE ROW
        document.addEventListener("click", function(e) {
            if (e.target.closest(".removeRow")) {
                e.target.closest("tr").remove();
            }
        });

        // DYNAMIC PRODUCT SELECTION
        document.addEventListener("change", function(e) {
            if (e.target.classList.contains("category_select")) {
                let category_id = e.target.value;
                let productSelect = e.target.closest("tr").querySelector(".product_select");

                let filtered = products.filter(p => p.category_id == category_id);

                productSelect.innerHTML = `<option value="">Select Product</option>`;

                filtered.forEach(p => {
                    productSelect.innerHTML += `<option value="${p.id}">${p.product_name}</option>`;
                });
            }
        });
    </script>
@endpush
