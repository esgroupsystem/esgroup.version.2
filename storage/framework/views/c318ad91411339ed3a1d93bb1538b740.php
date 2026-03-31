<?php $__env->startPush('scripts'); ?>
    <script>
        window.cctvInventoryOptions = `
        <option value="">-- Select Inventory Item --</option>
        <?php $__currentLoopData = $inventoryItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($item->id); ?>">
                <?php echo e($item->item_name); ?> | Stock: <?php echo e($item->stock_qty); ?> <?php echo e($item->unit); ?><?php echo e($item->brand ? ' | ' . $item->brand : ''); ?>

            </option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    `;

        window.cctvBuildItemRow = function(index) {
            return `
            <div class="row g-2 item-row item-row-modern mb-2">
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-1">Inventory Item</label>
                    <select class="form-select form-select-modern" name="items[${index}][it_inventory_item_id]">
                        ${window.cctvInventoryOptions}
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Qty</label>
                    <input type="number" min="1" class="form-control form-control-modern"
                        name="items[${index}][qty_used]" placeholder="Qty">
                </div>

                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Remarks</label>
                    <input type="text" class="form-control form-control-modern"
                        name="items[${index}][remarks]" placeholder="Remarks">
                </div>

                <div class="col-md-1 d-grid">
                    <label class="form-label small invisible mb-1">Remove</label>
                    <button type="button" class="btn btn-outline-danger rounded-pill"
                        onclick="cctvRemoveItemRow(this)">
                        <span class="fas fa-times"></span>
                    </button>
                </div>
            </div>
        `;
        };

        window.cctvAddItemRow = function(wrapperId) {
            const wrapper = document.getElementById(wrapperId);

            if (!wrapper) {
                alert('Wrapper not found: ' + wrapperId);
                console.error('Wrapper not found:', wrapperId);
                return;
            }

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
            } else {
                row.querySelectorAll('input').forEach(input => input.value = '');
                row.querySelectorAll('select').forEach(select => select.value = '');
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                window.jQuery('.bus-select').select2({
                    placeholder: "Select Bus",
                    allowClear: true,
                    width: '100%',
                    theme: 'bootstrap-5',
                    dropdownParent: window.jQuery('#createModal')
                });
            }
        });
    </script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/it_department/concern/partials/scripts.blade.php ENDPATH**/ ?>