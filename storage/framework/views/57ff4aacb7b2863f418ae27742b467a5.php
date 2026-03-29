
<?php $__env->startSection('title', 'Generate Payroll'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container-fluid" data-layout="container">
        <div class="content">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Generate Payroll</h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="<?php echo e(route('payroll.store')); ?>">
                        <?php echo csrf_field(); ?>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Cutoff Month</label>
                                <select name="cutoff_month" class="form-select" required>
                                    <?php for($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?php echo e($m); ?>"
                                            <?php echo e((int) old('cutoff_month', $defaultCutoffMonth) === $m ? 'selected' : ''); ?>>
                                            <?php echo e(\Carbon\Carbon::create(null, $m, 1)->format('F')); ?>

                                        </option>
                                    <?php endfor; ?>
                                </select>
                                <?php $__errorArgs = ['cutoff_month'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <small class="text-danger"><?php echo e($message); ?></small>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Cutoff Year</label>
                                <input type="number" name="cutoff_year" class="form-control"
                                    value="<?php echo e(old('cutoff_year', $defaultCutoffYear)); ?>" required>
                                <?php $__errorArgs = ['cutoff_year'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <small class="text-danger"><?php echo e($message); ?></small>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Cutoff Type</label>
                                <select name="cutoff_type" class="form-select" required>
                                    <option value="first"
                                        <?php echo e(old('cutoff_type', $defaultCutoffType) === 'first' ? 'selected' : ''); ?>>
                                        1st Cutoff (11–25)
                                    </option>
                                    <option value="second"
                                        <?php echo e(old('cutoff_type', $defaultCutoffType) === 'second' ? 'selected' : ''); ?>>
                                        2nd Cutoff (26–10 next month)
                                    </option>
                                </select>
                                <?php $__errorArgs = ['cutoff_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <small class="text-danger"><?php echo e($message); ?></small>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="rebuild_summary" value="1"
                                        id="rebuild_summary" <?php echo e(old('rebuild_summary', '1') ? 'checked' : ''); ?>>
                                    <label class="form-check-label" for="rebuild_summary">
                                        Rebuild daily attendance summary before generating payroll
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" rows="3" class="form-control"><?php echo e(old('remarks')); ?></textarea>
                                <?php $__errorArgs = ['remarks'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <small class="text-danger"><?php echo e($message); ?></small>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                Generate Payroll
                            </button>
                            <a href="<?php echo e(route('payroll.index')); ?>" class="btn btn-light">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\esgroup.version.2\resources\views/payroll/payrolls/create.blade.php ENDPATH**/ ?>