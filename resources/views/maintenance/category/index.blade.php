@extends('layouts.app')
@section('title', 'Category Management')

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
                            <h3 class="mb-2">Category Management</h3>
                            <p class="text-muted">
                                Manage item categories used across the system.
                            </p>
                        </div>

                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal"
                                onclick="openCreateCategory()">
                                <i class="fas fa-plus me-1"></i> Add Category
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 🧭 TABLE CARD --}}
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Category List</h6>
                </div>

                {{-- SEARCH --}}
                <div class="p-3">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <input class="form-control form-control-sm search" placeholder="Search category...">
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div id="categoryTable" data-list='{"valueNames":["category_name"],"page":10,"pagination":true}'>

                        <div class="table-responsive scrollbar">
                            <table class="table table-hover table-striped fs-10 mb-0">
                                <thead class="bg-200 text-900">
                                    <tr>
                                        <th class="sort" data-sort="category_name">Category Name</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>

                                <tbody class="list">
                                    @foreach ($categories as $category)
                                        <tr class="align-middle">

                                            <td class="category_name fw-semibold">{{ $category->name }}</td>

                                            <td class="text-center">
                                                <div class="dropdown font-sans-serif position-static">
                                                    <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                                        type="button" data-bs-toggle="dropdown">
                                                        <span class="fas fa-ellipsis-h fs-10"></span>
                                                    </button>

                                                    <div class="dropdown-menu dropdown-menu-end border py-0 shadow-sm">
                                                        <div class="py-2">

                                                            {{-- EDIT --}}
                                                            <button class="dropdown-item"
                                                                onclick="openEditCategory({{ $category->id }}, '{{ $category->name }}')">
                                                                <i class="fas fa-edit me-2"></i> Edit
                                                            </button>

                                                            {{-- DELETE --}}
                                                            <form action="{{ route('category.destroy', $category->id) }}"
                                                                method="POST" class="d-inline">
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
                                    @endforeach
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

    {{-- 🧾 CATEGORY MODAL --}}
    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">

                <form id="categoryForm" method="POST" action="{{ route('category.store') }}">
                    @csrf

                    <div class="modal-header bg-light">
                        <h5 class="modal-title" id="modalTitle">Add Category</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <input type="hidden" id="categoryId" name="category_id">

                        <div class="mb-3">
                            <label class="form-label">Category Name</label>
                            <input type="text" id="categoryName" name="name" class="form-control" required>
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
            const listjs = new List("categoryTable", {
                valueNames: ["category_name"],
                page: 10,
                pagination: true
            });

            document.querySelector(".search").addEventListener("keyup", e => {
                listjs.search(e.target.value);
            });
        });

        function openCreateCategory() {
            document.getElementById("modalTitle").innerText = "Add Category";
            document.getElementById("categoryForm").action = "{{ route('category.store') }}";
            document.getElementById("categoryName").value = "";

            let method = document.querySelector('#categoryForm input[name="_method"]');
            if (method) method.remove();
        }

        function openEditCategory(id, name) {
            document.getElementById("modalTitle").innerText = "Edit Category";
            document.getElementById("categoryForm").action = "/category/" + id;

            if (!document.querySelector('#categoryForm input[name="_method"]')) {
                let m = document.createElement("input");
                m.type = "hidden";
                m.name = "_method";
                m.value = "PUT";
                document.getElementById("categoryForm").appendChild(m);
            }

            document.getElementById("categoryName").value = name;

            new bootstrap.Modal(document.getElementById('categoryModal')).show();
        }
    </script>
@endpush
