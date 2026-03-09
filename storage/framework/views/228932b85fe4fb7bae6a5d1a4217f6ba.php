<?php $__env->startSection('title', 'Crear Mesa'); ?>

<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?> Mesas <?php $__env->endSlot(); ?>
        <?php $__env->slot('li_2'); ?> <a href="<?php echo e(route('voting-tables.index')); ?>">Lista de Mesas</a> <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?> Crear Nueva Mesa <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="ri-add-line me-1"></i>
                        Nueva Mesa de Votación
                    </h4>
                </div>
                <div class="card-body">
                    
                    <div class="alert alert-info py-2">
                        <i class="ri-information-line me-1"></i>
                        Los campos marcados con <span class="text-danger">*</span> son obligatorios.
                        El <strong>estado electoral</strong> (papeletas, votos, horarios) se gestiona desde
                        <strong>Configuración Electoral</strong> en la vista de detalle, una vez creada la mesa.
                    </div>

                    <form action="<?php echo e(route('voting-tables.store')); ?>" method="POST" id="votingTableForm">
                        <?php echo csrf_field(); ?>

                        <?php echo $__env->make('voting-tables.partials.form-fields', [
                            'votingTable'  => null,
                            'institutions' => $institutions,
                            'users'        => $users,
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Init tooltips
            [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
              .forEach(el => new bootstrap.Tooltip(el));
        });
    </script>
    <?php echo $__env->make('voting-tables.scripts.voting-table-js', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\_Mine\sistema_electoral\resources\views/voting-tables/create.blade.php ENDPATH**/ ?>