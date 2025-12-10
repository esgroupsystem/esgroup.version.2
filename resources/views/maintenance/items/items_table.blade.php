<div class="table-responsive scrollbar">
    <table class="table table-hover table-striped fs-10 mb-0">
        <thead class="bg-200 text-900">
            <tr>
                <th>Item Name</th>
                <th>Category</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($items as $item)
                <tr class="align-middle">

                    {{-- ITEM NAME --}}
                    <td>
                        <div class="fw-semibold text-110">
                            {{ $item->product_name }}
                            @if ($item->unit)
                                ({{ $item->unit }})
                            @endif
                        </div>
                        <div class="text-500 fs-12">{{ $item->details ?? 'N/A' }}</div>
                    </td>

                    {{-- CATEGORY --}}
                    <td>
                        <div class="fw-semibold text-110">{{ $item->category->name }}</div>
                        @if ($item->part_number)
                            <div class="text-600 fs-9">#{{ $item->part_number }}</div>
                        @endif
                    </td>

                    {{-- ACTIONS --}}
                    <td class="text-center">
                        <div class="dropdown font-sans-serif position-static">
                            <button class="btn btn-link text-600 btn-sm dropdown-toggle btn-reveal"
                                data-bs-toggle="dropdown">
                                <span class="fas fa-ellipsis-h fs-10"></span>
                            </button>

                            <div class="dropdown-menu dropdown-menu-end border py-0 shadow-sm">
                                <div class="py-2">

                                    {{-- EDIT --}}
                                    <button class="dropdown-item"
                                        onclick="openEditItem({{ $item->id }},
                                            '{{ $item->category_id }}',
                                            '{{ $item->product_name }}',
                                            '{{ $item->unit }}',
                                            '{{ $item->part_number }}',
                                            `{{ $item->details }}`)">
                                        <i class="fas fa-edit me-2"></i> Edit
                                    </button>

                                    {{-- DELETE --}}
                                    <form action="{{ route('items.destroy', $item->id) }}"
                                        method="POST" class="d-inline confirm-delete">
                                        @csrf
                                        @method('DELETE')
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
                    <td colspan="3" class="text-center text-muted py-3">No items found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- PAGINATION --}}
<div class="my-3 d-flex justify-content-end px-3">
    {{ $items->appends(request()->except('page'))->links('pagination.custom') }}
</div>
