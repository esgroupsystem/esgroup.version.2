<div class="table-responsive scrollbar rounded-3 border" style="max-height: 500px;">
    <table class="table table-sm table-hover align-middle fs-10 mb-0">
        <thead class="bg-200 text-900">
            <tr>
                <th>Category</th>
                <th>Product</th>
                <th class="text-center">Unit</th>
                <th class="text-center">Status</th>
                <th class="text-center">Stock</th>
                <th>Indicator</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($products  as $p)
                @php
                    $qty = $p->stock_qty ?? 0;
                    $percent = min(100, ($qty / 10) * 100);
                @endphp

                <tr>
                    <td>{{ $p->category->name ?? '—' }}</td>

                    <td>
                        <div class="fw-semibold">{{ $p->product_name }}</div>
                        <div class="text-500 fs-11">{{ $p->details }}</div>
                    </td>

                    <td class="text-center">
                        <span class="badge badge-subtle-secondary px-2">{{ $p->unit }}</span>
                    </td>

                    <td class="text-center">
                        @if ($qty <= 0)
                            <span class="badge badge-subtle-danger px-3">Out of Stock</span>
                        @elseif ($qty <= 5)
                            <span class="badge badge-subtle-warning px-3">Low</span>
                        @else
                            <span class="badge badge-subtle-success px-3">Available</span>
                        @endif
                    </td>

                    <td class="text-center fw-bold">{{ $qty }}</td>

                    <td>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar
                                @if ($qty <= 0) bg-danger
                                @elseif($qty <= 5) bg-warning
                                @else bg-success
                                @endif"
                                style="width: {{ $percent }}%">
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="my-3 d-flex justify-content-end px-3">
    {{ $products ->appends(request()->except('page'))->links('pagination.custom') }}
</div>
