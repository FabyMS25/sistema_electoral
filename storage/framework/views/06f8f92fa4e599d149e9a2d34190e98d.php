
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?php echo e(route('voting-table-votes.index')); ?>" class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Recinto</label>
                        <select name="institution_id" class="form-select" id="institutionFilter">
                            <option value="">Todos los recintos</option>
                            <?php $__currentLoopData = $institutions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $institution): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($institution->id); ?>" 
                                    <?php echo e(($institutionId ?? '') == $institution->id ? 'selected' : ''); ?>>
                                    <?php echo e($institution->name); ?> (<?php echo e($institution->code); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Tipo de Elección</label>
                        <select name="election_type_id" class="form-select" id="electionTypeFilter">
                            <?php $__currentLoopData = $electionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($type->id); ?>" 
                                    <?php echo e(($electionTypeId ?? '') == $type->id ? 'selected' : ''); ?>>
                                    <?php echo e($type->name); ?> - <?php echo e(\Carbon\Carbon::parse($type->election_date)->format('d/m/Y')); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ri-filter-line me-1"></i>
                            Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div><?php /**PATH D:\_Mine\corporate\resources\views/voting-table-votes/partials/filters.blade.php ENDPATH**/ ?>