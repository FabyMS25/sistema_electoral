<?php $__env->startSection('title'); ?>
    Registro de Votos
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <link href="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.css')); ?>" rel="stylesheet" />
    <style>
        .role-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .observation-badge {
            cursor: pointer;
        }
        .validation-status {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            display: inline-block;
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?>
            Votos
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?>
            Registro de Votos por Mesa
        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <?php echo $__env->make('voting-table-votes.partials.filters', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('voting-table-votes.partials.quick-stats', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('voting-table-votes.partials.summary-cards', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if(request()->has('institution_id') || request()->has('status') || request()->has('table_number')): ?>
        <div class="alert alert-info">
            <i class="ri-information-line me-1"></i>
            Mostrando resultados para los filtros aplicados.
            <a href="<?php echo e(route('voting-table-votes.index', ['election_type_id' => $electionTypeId])); ?>" class="alert-link">
                Limpiar filtros
            </a>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="ri-table-line me-1"></i>
                        Mesas de Votación
                        <span class="badge bg-primary ms-2"><?php echo e($votingTables->count()); ?> encontradas</span>
                    </h5>
                    <div class="action-buttons">
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('review_votes')): ?>
                            <button class="btn btn-sm btn-info" id="reviewAllBtn" title="Revisar todas">
                                <i class="ri-eye-line me-1"></i>Revisar
                            </button>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('validate_votes')): ?>
                            <button class="btn btn-sm btn-success" id="validateAllBtn" title="Validar todas">
                                <i class="ri-check-line me-1"></i>Validar
                            </button>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('correct_votes')): ?>
                            <button class="btn btn-sm btn-warning" id="correctAllBtn" title="Corregir todas">
                                <i class="ri-refund-line me-1"></i>Corregir
                            </button>
                        <?php endif; ?>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('register_votes')): ?>
                            <button class="btn btn-sm btn-success" id="saveAllBtn">
                                <i class="ri-save-line me-1"></i>Guardar
                            </button>
                            <button class="btn btn-sm btn-warning" id="closeAllBtn">
                                <i class="ri-lock-line me-1"></i>Cerrar
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $votingTables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php echo $__env->make('voting-table-votes.partials.table-row', [
                            'table' => $table,
                            'userCan' => [
                                'register' => auth()->user()->hasPermission('register_votes'),
                                'review' => auth()->user()->hasPermission('review_votes'),
                                'correct' => auth()->user()->hasPermission('correct_votes'),
                                'validate' => auth()->user()->hasPermission('validate_votes'),
                                'observe' => auth()->user()->hasPermission('create_observations'),
                                'upload_acta' => auth()->user()->hasPermission('upload_actas'),
                                'close' => auth()->user()->hasPermission('close_tables')
                            ]
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center py-5">
                            <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px">
                            </lord-icon>
                            <h5 class="mt-3">No hay mesas disponibles</h5>
                            <p class="text-muted">
                                No se encontraron mesas para los filtros seleccionados.
                                <?php if(request()->has('institution_id') || request()->has('status') || request()->has('table_number')): ?>
                                    <br>
                                    <a href="<?php echo e(route('voting-table-votes.index', ['election_type_id' => $electionTypeId])); ?>" class="btn btn-link">
                                        Limpiar filtros
                                    </a>
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <?php if($votingTables->isNotEmpty()): ?>
        <?php echo $__env->make('voting-table-votes.partials.quick-actions', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>

    <!-- Modales -->
    <?php echo $__env->make('voting-table-votes.partials.modals.observation-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('voting-table-votes.partials.modals.upload-acta-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('voting-table-votes.partials.modals.validation-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('voting-table-votes.partials.modals.confirm-close', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('voting-table-votes.partials.modals.bulk-update', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.js')); ?>"></script>
    <?php echo $__env->make('voting-table-votes.scripts.votes-js', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.candidates = <?php echo json_encode($candidates, 15, 512) ?>;
            window.electionTypeId = <?php echo e($electionTypeId ?? 'null'); ?>;
            window.institutionId = <?php echo e($institutionId ?? 'null'); ?>;
            window.userPermissions = {
                register: <?php echo e(auth()->user()->hasPermission('register_votes') ? 'true' : 'false'); ?>,
                review: <?php echo e(auth()->user()->hasPermission('review_votes') ? 'true' : 'false'); ?>,
                correct: <?php echo e(auth()->user()->hasPermission('correct_votes') ? 'true' : 'false'); ?>,
                validate: <?php echo e(auth()->user()->hasPermission('validate_votes') ? 'true' : 'false'); ?>,
                observe: <?php echo e(auth()->user()->hasPermission('create_observations') ? 'true' : 'false'); ?>,
                uploadActa: <?php echo e(auth()->user()->hasPermission('upload_actas') ? 'true' : 'false'); ?>

            };
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\_Mine\corporate\resources\views/voting-table-votes/index.blade.php ENDPATH**/ ?>