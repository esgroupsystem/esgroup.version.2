<?php $__currentLoopData = $jobOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
        $badgeClass = match ($jo->status) {
            'Open' => 'badge-subtle-warning',
            'In Progress' => 'badge-subtle-info',
            'Fixed' => 'badge-subtle-success',
            'Closed' => 'badge-subtle-secondary',
            default => 'badge-subtle-primary',
        };
    ?>

    <div class="modal fade" id="editModal<?php echo e($jo->id); ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content cctv-modal border-0 shadow-lg">
                <form method="POST" action="<?php echo e(route('concern.cctv.update', $jo->id)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>

                    <div class="modal-header cctv-modal-header border-0">
                        <div class="w-100">
                            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="modal-icon">
                                        <span class="fas fa-video"></span>
                                    </div>
                                    <div>
                                        <h4 class="modal-title mb-1">Update CCTV Job Order</h4>
                                        <div class="text-muted small">Review and update job order details.</div>
                                    </div>
                                </div>

                                <div class="text-lg-end">
                                    <div class="fw-bold fs-5 text-dark"><?php echo e($jo->jo_no); ?></div>
                                    <span class="badge rounded-pill <?php echo e($badgeClass); ?>"><?php echo e($jo->status); ?></span>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn-close ms-3" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body pt-0">
                        <div class="form-section">
                            <div class="section-title">
                                <span class="fas fa-info-circle me-2 text-primary"></span>
                                Basic Information
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Bus No</label>
                                    <select class="form-select form-select-modern" name="bus_no" required>
                                        <option value="">-- Select Bus --</option>
                                        <?php $__currentLoopData = $buses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bus): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $label = trim(
                                                    ($bus->body_number ?? '') . ' - ' .
                                                    ($bus->plate_number ?? '') . ' - ' .
                                                    ($bus->name ?? '')
                                                );
                                            ?>
                                            <option value="<?php echo e($bus->body_number); ?>" <?php if($jo->bus_no == $bus->body_number): echo 'selected'; endif; ?>>
                                                <?php echo e($label); ?> <?php echo e($bus->garage ? "({$bus->garage})" : ''); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Reported By</label>
                                    <input type="text" class="form-control form-control-modern"
                                        name="reported_by" value="<?php echo e($jo->reported_by); ?>">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <select class="form-select form-select-modern" name="status" required>
                                        <?php $__currentLoopData = ['Open', 'In Progress', 'Fixed', 'Closed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($st); ?>" <?php if($jo->status === $st): echo 'selected'; endif; ?>><?php echo e($st); ?></option>
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
                                            <option value="<?php echo e($it); ?>" <?php if($jo->issue_type === $it): echo 'selected'; endif; ?>><?php echo e($it); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Assign To</label>
                                    <select class="form-select form-select-modern" name="assigned_to">
                                        <option value="">— None —</option>
                                        <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($a->id); ?>" <?php if($jo->assigned_to == $a->id): echo 'selected'; endif; ?>>
                                                <?php echo e($a->full_name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <div class="form-hint mt-1">Optional. Choose assigned personnel.</div>
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
                                    onclick="cctvAddItemRow('edit-items-wrapper-<?php echo e($jo->id); ?>')">
                                    <span class="fas fa-plus me-1"></span> Add Item
                                </button>
                            </div>

                            <div class="items-card">
                                <div id="edit-items-wrapper-<?php echo e($jo->id); ?>">
                                    <?php $__empty_1 = true; $__currentLoopData = $jo->usedItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $used): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <div class="row g-2 item-row item-row-modern mb-2">
                                            <div class="col-md-6">
                                                <label class="form-label small text-muted mb-1">Inventory Item</label>
                                                <select class="form-select form-select-modern" name="items[<?php echo e($idx); ?>][it_inventory_item_id]">
                                                    <option value="">-- Select Inventory Item --</option>
                                                    <?php $__currentLoopData = $inventoryItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($item->id); ?>" <?php if($used->it_inventory_item_id == $item->id): echo 'selected'; endif; ?>>
                                                            <?php echo e($item->item_name); ?> | Stock: <?php echo e($item->stock_qty); ?>

                                                            <?php echo e($item->unit); ?><?php echo e($item->brand ? ' | ' . $item->brand : ''); ?>

                                                        </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label small text-muted mb-1">Qty</label>
                                                <input type="number" min="1" class="form-control form-control-modern"
                                                    name="items[<?php echo e($idx); ?>][qty_used]" value="<?php echo e($used->qty_used); ?>" placeholder="Qty">
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label small text-muted mb-1">Remarks</label>
                                                <input type="text" class="form-control form-control-modern"
                                                    name="items[<?php echo e($idx); ?>][remarks]" value="<?php echo e($used->remarks); ?>" placeholder="Remarks">
                                            </div>

                                            <div class="col-md-1 d-grid">
                                                <label class="form-label small invisible mb-1">Remove</label>
                                                <button type="button" class="btn btn-outline-danger rounded-pill"
                                                    onclick="cctvRemoveItemRow(this)">
                                                    <span class="fas fa-times"></span>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <div class="row g-2 item-row item-row-modern mb-2">
                                            <div class="col-md-6">
                                                <label class="form-label small text-muted mb-1">Inventory Item</label>
                                                <select class="form-select form-select-modern" name="items[0][it_inventory_item_id]">
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
                                                <button type="button" class="btn btn-outline-danger rounded-pill"
                                                    onclick="cctvRemoveItemRow(this)">
                                                    <span class="fas fa-times"></span>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endif; ?>
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
                                    <textarea class="form-control form-control-modern" name="problem_details" rows="4" required><?php echo e($jo->problem_details); ?></textarea>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Action Taken</label>
                                    <textarea class="form-control form-control-modern" name="action_taken" rows="4"><?php echo e($jo->action_taken); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                            Close
                        </button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                            <span class="fas fa-save me-1"></span> Update Job Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/it_department/concern/partials/edit-modals.blade.php ENDPATH**/ ?>