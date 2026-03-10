


<?php $__env->startSection('title', 'Editar Recinto'); ?>

<?php $__env->startSection('css'); ?>
    <link href="<?php echo e(URL::asset('build/libs/choices.js/public/assets/styles/choices.min.css')); ?>" rel="stylesheet" />
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?>
            <a href="<?php echo e(route('institutions.index')); ?>">Recintos</a>
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('li_2'); ?>
            <a href="<?php echo e(route('institutions.show', $institution->id)); ?>"><?php echo e($institution->name); ?></a>
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?>
            Editar Recinto
        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="ri-edit-line me-1"></i>
                        Editando: <?php echo e($institution->name); ?>

                    </h4>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('institutions.update', $institution->id)); ?>" method="POST" id="institutionForm">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        <div class="alert alert-info">
                            <i class="ri-information-line me-1"></i>
                            Los campos marcados con <span class="text-danger">*</span> son obligatorios.
                        </div>

                        <?php echo $__env->make('institutions.partials.form-fields', [
                            'institution'   => $institution,
                            'departments'   => $departments,
                            'statusOptions' => $statusOptions,
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                        <div class="row mt-4">
                            <div class="col-12 text-end">
                                <a href="<?php echo e(route('institutions.show', $institution->id)); ?>" class="btn btn-secondary">
                                    <i class="ri-close-line me-1"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line me-1"></i>Actualizar Recinto
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <?php echo $__env->make('institutions.scripts.institution-js', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\_Mine\sistema_electoral\resources\views/institutions/edit.blade.php ENDPATH**/ ?>