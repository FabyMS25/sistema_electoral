

<?php $__env->startSection('title', 'Crear Recinto'); ?>

<?php $__env->startSection('css'); ?>
    <link href="<?php echo e(URL::asset('build/libs/choices.js/public/assets/styles/choices.min.css')); ?>" rel="stylesheet" />
    <style>
        .required-field label:after {
            content: " *";
            color: red;
        }
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #0ab39c;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.25rem;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?>
            <a href="<?php echo e(route('institutions.index')); ?>">Recintos</a>
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?>
            Crear Nuevo Recinto
        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="ri-add-line me-1"></i>
                        Nuevo Recinto Electoral
                    </h4>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('institutions.store')); ?>" method="POST" id="institutionForm">
                        <?php echo csrf_field(); ?>

                        <div class="alert alert-info">
                            <i class="ri-information-line me-1"></i>
                            Los campos marcados con <span class="text-danger">*</span> son obligatorios.
                        </div>

                        <?php echo $__env->make('institutions.partials.form-fields', [
                            'institution' => null,
                            'departments' => $departments,
                            'statusOptions' => $statusOptions
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                        <div class="row mt-4">
                            <div class="col-12 text-end">
                                <a href="<?php echo e(route('institutions.index')); ?>" class="btn btn-secondary">
                                    <i class="ri-close-line me-1"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line me-1"></i>Crear Recinto
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
    <script src="<?php echo e(URL::asset('build/libs/choices.js/public/assets/scripts/choices.min.js')); ?>"></script>
    <?php echo $__env->make('institutions.scripts.institution-js', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\_Mine\sistema_electoral\resources\views/institutions/create.blade.php ENDPATH**/ ?>