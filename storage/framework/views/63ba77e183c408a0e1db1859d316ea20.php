<?php $__env->startSection('title'); ?>
    Registro de Votos - <?php echo e($electionType->name ?? 'Elecciones'); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>
    <link href="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.css')); ?>" rel="stylesheet" />
    <style>
        .vote-input:focus {
            border-color: #0ab39c;
            box-shadow: 0 0 0 0.2rem rgba(10, 179, 156, 0.25);
        }
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .quick-actions {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        .quick-actions .btn-group-vertical {
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .pagination-info {
            padding: 8px 0;
            color: #6c757d;
        }
        .table-card.status-observada {
            border-left: 4px solid #f06548;
        }
        .table-card.status-cerrada {
            border-left: 4px solid #8590a5;
            opacity: 0.9;
        }
        .table-card.status-escrutada {
            border-left: 4px solid #0ab39c;
        }
        .table-card.status-transmitida {
            border-left: 4px solid #405189;
        }
        .category-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }
        .inconsistency-warning {
            background-color: #fff3cd;
            border: 1px solid #ffe69c;
            color: #664d03;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?> Votos <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?> Registro de Votos - <?php echo e($electionType->name ?? 'Elecciones'); ?> <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <?php echo $__env->make('voting-table-votes.partials.filters', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="row mb-2">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Mesas</h6>
                            <h3 class="mb-0"><?php echo e($votingTables->total()); ?></h3>
                        </div>
                        <i class="ri-table-line fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Votos</h6>
                            <h3 class="mb-0"><?php echo e(number_format($totals['total'] ?? 0)); ?></h3>
                        </div>
                        <i class="ri-bar-chart-line fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Habilitados</h6>
                            <h3 class="mb-0"><?php echo e(number_format($totals['expected'] ?? 0)); ?></h3>
                        </div>
                        <i class="ri-group-line fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Participación</h6>
                            <h3 class="mb-0"><?php echo e($totals['participation'] ?? 0); ?>%</h3>
                        </div>
                        <i class="ri-percent-line fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <?php $__empty_1 = true; $__currentLoopData = $votingTables->items(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php echo $__env->make('voting-table-votes.partials.table', [
                    'table' => $table,
                    'candidatesByCategory' => $candidatesByCategory,
                    'statusLabels' => $statusLabels,
                    'validationLabels' => $validationLabels,
                    'permissions' => $permissions,
                    'categoryColorMap' => $categoryColorMap ?? []
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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

    <?php if($votingTables->hasPages()): ?>
    <div class="pagination-wrapper mt-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="pagination-info">
                Mostrando <?php echo e($votingTables->firstItem() ?? 0); ?> - <?php echo e($votingTables->lastItem() ?? 0); ?> de <?php echo e($votingTables->total()); ?> mesas
            </div>
            <div><?php echo e($votingTables->links()); ?></div>
        </div>
    </div>
    <?php endif; ?>

    <?php if($votingTables->total() > 0 && ($permissions['can_register'] ?? false)): ?>
        <div class="quick-actions">
            <div class="btn-group-vertical">
                <button class="btn btn-success" id="saveAllBtn" title="Guardar todas (Ctrl+S)">
                    <i class="ri-save-line"></i> Guardar Todo
                </button>
                <button class="btn btn-warning" id="closeAllBtn" title="Cerrar todas (Ctrl+C)">
                    <i class="ri-lock-line"></i> Cerrar Todo
                </button>
            </div>
        </div>
    <?php endif; ?>

    <?php echo $__env->make('voting-table-votes.partials.modals.observation-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('voting-table-votes.partials.modals.upload-acta-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('voting-table-votes.partials.modals.validation-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.js')); ?>"></script>
    <script>
        var currentObservationTable = null;
        var currentActaTable = null;
        var currentValidationTable = null;
        var pendingTables = new Set();
        var saveTimeouts = {};
        var electionTypeId = <?php echo e($electionTypeId ?? 'null'); ?>;

        var userPermissions = {
            register: <?php echo e(($permissions['can_register'] ?? false) ? 'true' : 'false'); ?>,
            review: <?php echo e(($permissions['can_review'] ?? false) ? 'true' : 'false'); ?>,
            correct: <?php echo e(($permissions['can_correct'] ?? false) ? 'true' : 'false'); ?>,
            validate: <?php echo e(($permissions['can_validate'] ?? false) ? 'true' : 'true'); ?>,
            observe: <?php echo e(($permissions['can_observe'] ?? false) ? 'true' : 'false'); ?>,
            uploadActa: <?php echo e(($permissions['can_upload_acta'] ?? false) ? 'true' : 'false'); ?>,
            close: <?php echo e(($permissions['can_close'] ?? false) ? 'true' : 'false'); ?>

        };

        window.updateTableTotals = function(tableId) {
            console.log('🔄 Actualizando totales para mesa:', tableId);

            const inputs = document.querySelectorAll(`#table-${tableId} .vote-input`);
            const categoryTotals = {};

            inputs.forEach(input => {
                const value = parseInt(input.value) || 0;
                const category = input.dataset.category;

                if (!categoryTotals[category]) {
                    categoryTotals[category] = 0;
                }
                categoryTotals[category] += value;
            });

            console.log(`📊 Mesa ${tableId} - Totales por categoría:`, categoryTotals);

            // Actualizar totales por categoría
            Object.entries(categoryTotals).forEach(([category, total]) => {
                const el = document.getElementById(`total-${category}-${tableId}`);
                if (el) {
                    el.textContent = total;
                }
            });

            // Calcular total general (todas las categorías deberían tener el mismo total)
            const totals = Object.values(categoryTotals);
            const totalVotes = totals.length > 0 ? totals[0] : 0;

            const totalEl = document.getElementById(`total-${tableId}`);
            if (totalEl) {
                totalEl.textContent = totalVotes;
            }

            // Actualizar contadores de selección
            updateSelectedCounts(tableId);

            return categoryTotals;
        };

        function updateSelectedCounts(tableId) {
            const checkboxes = document.querySelectorAll(`#table-${tableId} .observe-checkbox:checked`);
            const categoryCounts = {};

            checkboxes.forEach(cb => {
                const category = cb.dataset.category;
                if (!categoryCounts[category]) categoryCounts[category] = 0;
                categoryCounts[category]++;
            });

            const totalSelected = checkboxes.length;
            const selectedCountEl = document.getElementById(`selected-count-${tableId}`);
            if (selectedCountEl) selectedCountEl.textContent = totalSelected;

            Object.entries(categoryCounts).forEach(([category, count]) => {
                const el = document.getElementById(`selected-${category}-${tableId}`);
                if (el) el.textContent = count;
            });
        }
    </script>

    <?php echo $__env->make('voting-table-votes.scripts.votes-table-js', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('voting-table-votes.scripts.observations-js', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('voting-table-votes.scripts.observations-by-vote-js', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('voting-table-votes.scripts.view-toggle-js', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 Inicializando variables...');
            console.log('✅ Variables inicializadas:', userPermissions);

            if (typeof window.initVoteListeners === 'function') {
                window.initVoteListeners();
                console.log('✅ Listeners de votos inicializados');
            }

            if (typeof window.initObservationListeners === 'function') {
                window.initObservationListeners();
            }

            if (typeof window.initViewToggle === 'function') {
                window.initViewToggle();
            }

            function initKeyboardShortcuts() {
                document.addEventListener('keydown', function(e) {
                    if (e.ctrlKey && e.key === 's') {
                        e.preventDefault();
                        if (typeof window.saveAllTables === 'function') window.saveAllTables();
                    }
                    if (e.ctrlKey && e.key === 'c') {
                        e.preventDefault();
                        if (typeof window.closeAllTables === 'function') window.closeAllTables();
                    }
                });
            }
            initKeyboardShortcuts();
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\_Mine\sistema_electoral\resources\views/voting-table-votes/index.blade.php ENDPATH**/ ?>