<?php $__env->startSection('title', 'Crear Nueva Mesa de Votación'); ?>

<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?> <a href="<?php echo e(route('voting-tables.index')); ?>">Mesas</a> <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?> Crear Nueva Mesa <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title mb-0">
                        <i class="ri-add-line me-1"></i>
                        Nueva Mesa de Votación
                    </h4>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('voting-tables.store')); ?>" method="POST" id="votingTableForm">
                        <?php echo csrf_field(); ?>

                        <?php echo $__env->make('voting-tables.partials.form-fields', [
                            'votingTable' => null,
                            'institutions' => $institutions,
                            'users' => $users,
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?php echo e(route('voting-tables.index')); ?>" class="btn btn-secondary">
                                <i class="ri-close-line me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i>Crear Mesa
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <?php echo $__env->make('voting-tables.scripts.voting-table-js', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->yieldPushContent('scripts'); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\_Mine\sistema_electoral\resources\views/voting-tables/create.blade.php ENDPATH**/ ?>