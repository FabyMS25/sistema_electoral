<?php $__env->startSection('title'); ?>
    Editar Mesa
<?php $__env->stopSection(); ?>

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
            <a href="<?php echo e(route('voting-tables.index')); ?>">Mesas</a>
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('li_2'); ?>
            <a href="<?php echo e(route('voting-tables.show', $votingTable->id)); ?>">Mesa <?php echo e($votingTable->oep_code ?? $votingTable->internal_code); ?></a>
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?>
            Editar Mesa
        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="ri-edit-line me-1"></i>
                        Editando Mesa: <?php echo e($votingTable->oep_code ?? $votingTable->internal_code); ?> - N° <?php echo e($votingTable->number); ?>

                    </h4>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('voting-tables.update', $votingTable->id)); ?>" method="POST" id="votingTableForm">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        <div class="alert alert-info">
                            <i class="ri-information-line me-1"></i>
                            Los campos marcados con <span class="text-danger">*</span> son obligatorios.
                            <br>
                            <small>Los datos electorales (papeletas, estado, horarios) se configuran por separado.</small>
                        </div>

                        <?php echo $__env->make('voting-tables.partials.form-fields', [
                            'votingTable' => $votingTable,
                            'institutions' => $institutions,
                            'users' => $users
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                        <div class="row mt-4">
                            <div class="col-12 text-end">
                                <a href="<?php echo e(route('voting-tables.show', $votingTable->id)); ?>" class="btn btn-secondary">
                                    <i class="ri-close-line me-1"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line me-1"></i>Actualizar Mesa
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
    <?php echo $__env->make('voting-tables.scripts.voting-table-js', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Choices !== 'undefined') {
                const institutionSelect = document.getElementById('institution_id-field');
                if (institutionSelect && !institutionSelect._choices) {
                    new Choices(institutionSelect, {
                        searchEnabled: true,
                        placeholder: true,
                        placeholderValue: '-- Seleccione un recinto --'
                    });
                }
            }
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\_Mine\sistema_electoral\resources\views/voting-tables/edit.blade.php ENDPATH**/ ?>