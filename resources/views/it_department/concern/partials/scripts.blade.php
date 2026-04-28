@push('scripts')
    <script>
        window.cctvInventoryOptions = `
        <option value="">Select Inventory Item</option>
        @foreach ($inventoryItems as $item)
            <option value="{{ $item->id }}">
                {{ $item->item_name }} | Stock: {{ $item->stock_qty }} {{ $item->unit }}{{ $item->brand ? ' | ' . $item->brand : '' }}
            </option>
        @endforeach
    `;

        window.cctvBuildItemRow = function(index) {
            return `
            <div class="item-row mb-2">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-6">
                        <label class="form-label fs-11 fw-semibold text-muted">Inventory Item</label>
                        <select class="form-select form-select-sm" name="items[${index}][it_inventory_item_id]">
                            ${window.cctvInventoryOptions}
                        </select>
                    </div>

                    <div class="col-lg-2">
                        <label class="form-label fs-11 fw-semibold text-muted">Qty</label>
                        <input type="number" min="1"
                            class="form-control form-control-sm"
                            name="items[${index}][qty_used]"
                            placeholder="Qty">
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label fs-11 fw-semibold text-muted">Remarks</label>
                        <input type="text"
                            class="form-control form-control-sm"
                            name="items[${index}][remarks]"
                            placeholder="Remarks">
                    </div>

                    <div class="col-lg-1">
                        <button type="button"
                            class="btn btn-outline-danger btn-sm w-100"
                            onclick="cctvRemoveItemRow(this)">
                            <span class="fas fa-times"></span>
                        </button>
                    </div>
                </div>
            </div>
        `;
        };

        window.cctvReindexItemRows = function(wrapper) {
            wrapper.querySelectorAll('.item-row').forEach((row, index) => {
                row.querySelectorAll('[name]').forEach(field => {
                    field.name = field.name.replace(/items\[\d+\]/, `items[${index}]`);
                });
            });
        };

        window.cctvAddItemRow = function(wrapperId) {
            const wrapper = document.getElementById(wrapperId);
            if (!wrapper) return;

            const index = wrapper.querySelectorAll('.item-row').length;
            wrapper.insertAdjacentHTML('beforeend', window.cctvBuildItemRow(index));
        };

        window.cctvRemoveItemRow = function(button) {
            const row = button.closest('.item-row');
            if (!row) return;

            const wrapper = row.parentElement;
            const rows = wrapper.querySelectorAll('.item-row');

            if (rows.length > 1) {
                row.remove();
                window.cctvReindexItemRows(wrapper);
                return;
            }

            row.querySelectorAll('input').forEach(input => input.value = '');
            row.querySelectorAll('select').forEach(select => select.value = '');
        };

        document.addEventListener('DOMContentLoaded', function() {
            if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.select2) return;

            window.jQuery('.bus-select').each(function() {
                const $select = window.jQuery(this);
                const $modal = $select.closest('.modal');

                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }

                $select.select2({
                    placeholder: 'Search bus...',
                    allowClear: true,
                    width: '100%',
                    theme: 'bootstrap-5',
                    dropdownParent: $modal.length ? $modal : window.jQuery(document.body),
                    minimumResultsForSearch: 0
                });
            });
        });
    </script>
@endpush
