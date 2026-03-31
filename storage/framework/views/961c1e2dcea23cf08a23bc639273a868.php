<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content cctv-modal border-0 shadow-lg">
            <form method="POST" action="<?php echo e(route('concern.cctv.store')); ?>">
                <?php echo csrf_field(); ?>

                <div class="modal-header cctv-modal-header border-0">
                    <div>
                        <h5 class="modal-title mb-1">Create CCTV Job Order</h5>
                        <div class="form-hint">Fill in all required details and save.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body pt-0">
                    <div class="form-section">
                        <div class="section-title">
                            <span class="fas fa-info-circle me-2 text-primary"></span>
                            Basic Information
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Bus</label>
                                <select class="form-select form-select-modern bus-select" name="bus_no" required>
                                    <option value="">-- Select Bus --</option>
                                    <?php $__currentLoopData = $buses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bus): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($bus->body_number); ?>">
                                            <?php echo e($bus->body_number); ?> - <?php echo e($bus->plate_number ?? 'No Plate'); ?> -
                                            <?php echo e($bus->name ?? 'No Name'); ?>

                                            <?php echo e($bus->garage ? "({$bus->garage})" : ''); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Reported By</label>
                                <input type="text" class="form-control form-control-modern"
                                    value="<?php echo e(auth()->user()->full_name); ?>" readonly>
                                <input type="hidden" name="reported_by" value="<?php echo e(auth()->user()->full_name); ?>">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select class="form-select form-select-modern" name="status" required>
                                    <?php $__currentLoopData = ['Open', 'In Progress', 'Fixed', 'Closed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($st); ?>" <?php if($st === 'Open'): echo 'selected'; endif; ?>>
                                            <?php echo e($st); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-title">
                            <span class="fas fa-tools me-2 text-primary"></span>
                            Issue & Assignment
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Issue Type</label>
                                <select class="form-select form-select-modern" name="issue_type" required>
                                    <?php $__currentLoopData = ['Camera', 'Monitor', 'DVR', 'Wiring', 'Power', 'Other']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($it); ?>"><?php echo e($it); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Assign To</label>
                                <select class="form-select form-select-modern" name="assigned_to">
                                    <option value="">— None —</option>
                                    <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($a->id); ?>"><?php echo e($a->full_name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <div class="form-hint mt-1">Optional.</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <div class="section-title mb-0">
                                <span class="fas fa-box-open me-2 text-primary"></span>
                                Items Used
                            </div>

                            <button type="button" class="btn btn-sm btn-primary"
                                onclick="cctvAddItemRow('create-items-wrapper')">
                                <span class="fas fa-plus me-1"></span> Add Item
                            </button>
                        </div>

                        <div class="items-card">
                            <div id="create-items-wrapper">
                                <div class="row g-2 item-row item-row-modern mb-2">
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted mb-1">Inventory Item</label>
                                        <select class="form-select form-select-modern"
                                            name="items[0][it_inventory_item_id]">
                                            <option value="">-- Select Inventory Item --</option>
                                            <?php $__currentLoopData = $inventoryItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($item->id); ?>">
                                                    <?php echo e($item->item_name); ?> | Stock: <?php echo e($item->stock_qty); ?>

                                                    <?php echo e($item->unit); ?><?php echo e($item->brand ? ' | ' . $item->brand : ''); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label small text-muted mb-1">Qty</label>
                                        <input type="number" min="1" class="form-control form-control-modern"
                                            name="items[0][qty_used]" placeholder="Qty">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label small text-muted mb-1">Remarks</label>
                                        <input type="text" class="form-control form-control-modern"
                                            name="items[0][remarks]" placeholder="Remarks">
                                    </div>

                                    <div class="col-md-1 d-grid">
                                        <label class="form-label small invisible mb-1">Remove</label>
                                        <button type="button"
                                            class="btn btn-outline-danger rounded-pill remove-item-row">
                                            <span class="fas fa-times"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-title">
                            <span class="fas fa-align-left me-2 text-primary"></span>
                            Work Details
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Problem Details</label>
                                <textarea class="form-control form-control-modern" name="problem_details" rows="4" required></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Action Taken</label>
                                <textarea class="form-control form-control-modern" name="action_taken" rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <span class="fas fa-save me-1"></span> Save Job Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/it_department/concern/partials/create-modal.blade.php ENDPATH**/ ?>