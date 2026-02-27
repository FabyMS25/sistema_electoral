


<?php $__env->startSection('title'); ?>
    Registro de Votos
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <link href="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.css')); ?>" rel="stylesheet" />
    <style>
        .candidate-card {
            transition: all 0.2s;
            border: 1px solid #e9e9ef;
            border-radius: 0.5rem;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
        }
        .candidate-card:hover {
            background-color: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .candidate-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            display: inline-block;
            margin-right: 8px;
        }
        .vote-input {
            width: 100px;
            text-align: center;
            font-weight: bold;
            border: 2px solid #e9e9ef;
            border-radius: 0.5rem;
            padding: 0.5rem;
        }
        .vote-input:focus {
            border-color: #0ab39c;
            outline: none;
        }
        .table-card {
            transition: all 0.2s;
            border-left: 4px solid transparent;
        }
        .table-card.pendiente {
            border-left-color: #f7b84b;
        }
        .table-card.en_proceso {
            border-left-color: #0ab39c;
        }
        .table-card.activo {
            border-left-color: #0ab39c;
        }
        .table-card.cerrado {
            border-left-color: #f06548;
            opacity: 0.8;
            background-color: #f8f9fa;
        }
        .total-badge {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }
        .quick-actions {
            position: sticky;
            bottom: 20px;
            z-index: 100;
            background: white;
            padding: 1rem;
            border-radius: 1rem;
            box-shadow: 0 -4px 12px rgba(0,0,0,0.1);
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

    <!-- Filtros -->
    <?php echo $__env->make('voting-table-votes.partials.filters', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Resumen General -->
    <?php echo $__env->make('voting-table-votes.partials.summary-cards', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Lista de Mesas -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-table-line me-1"></i>
                        Mesas de Votación
                    </h5>
                </div>
                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $votingTables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php echo $__env->make('voting-table-votes.partials.table-row', ['table' => $table], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center py-5">
                            <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px">
                            </lord-icon>
                            <h5 class="mt-3">No hay mesas disponibles</h5>
                            <p class="text-muted">No se encontraron mesas para los filtros seleccionados.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <?php if($votingTables->isNotEmpty()): ?>
        <div class="quick-actions">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <span class="text-muted">
                            <i class="ri-information-line me-1"></i>
                            <?php echo e($votingTables->count()); ?> mesas visibles
                        </span>
                    </div>
                    <div class="col-md-6 text-end">
                        <button class="btn btn-success me-2" id="saveAllBtn">
                            <i class="ri-save-line me-1"></i>
                            Guardar Todos
                        </button>
                        <button class="btn btn-warning" id="closeAllBtn">
                            <i class="ri-lock-line me-1"></i>
                            Cerrar Todos
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Modales -->
    <?php echo $__env->make('voting-table-votes.partials.modals.confirm-close', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('voting-table-votes.partials.modals.bulk-update', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.js')); ?>"></script>
    <?php echo $__env->make('voting-table-votes.scripts.votes-js', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar con datos de candidatos
            window.candidates = <?php echo json_encode($candidates, 15, 512) ?>;
            window.electionTypeId = <?php echo e($electionTypeId ?? 'null'); ?>;
            window.institutionId = <?php echo e($institutionId ?? 'null'); ?>;
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\_Mine\corporate\resources\views/voting-table-votes/index.blade.php ENDPATH**/ ?>