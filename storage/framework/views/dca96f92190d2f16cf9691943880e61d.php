
<?php
    $isDisabled = in_array($table->current_status, ['cerrada', 'escrutada', 'transmitida', 'anulada']) ||
                  !($permissions['can_register'] ?? false);

    // Colores para las categorías (cíclico)
    $categoryColors = ['primary', 'success', 'warning', 'info', 'danger', 'secondary', 'dark'];
    $categoryColorMap = [];
    $index = 0;

    $categoryCodes = array_keys($candidatesByCategory ?? []);
    foreach ($categoryCodes as $code) {
        $categoryColorMap[$code] = $categoryColors[$index % count($categoryColors)];
        $index++;
    }

    // Verificar inconsistencias
    $hasInconsistencies = false;
    if (isset($table->results_by_category)) {
        foreach ($table->results_by_category as $result) {
            if (!$result['is_consistent']) {
                $hasInconsistencies = true;
                break;
            }
        }
    }
?>

<div class="card mb-3 table-card status-<?php echo e($table->current_status); ?>"
     id="table-<?php echo e($table->id); ?>"
     data-table-id="<?php echo e($table->id); ?>"
     data-expected-voters="<?php echo e($table->expected_voters); ?>">

    
    <div class="card-header bg-light position-relative">
        <?php if($hasInconsistencies): ?>
            <span class="badge bg-danger role-badge" title="Tiene inconsistencias">
                <i class="ri-alert-line me-1"></i>Inconsistente
            </span>
        <?php elseif($table->current_status === 'observada'): ?>
            <span class="badge bg-danger role-badge" title="Tiene observaciones">
                <i class="ri-chat-1-line me-1"></i>Observada
            </span>
        <?php elseif($table->current_status === 'escrutada'): ?>
            <span class="badge bg-success role-badge">
                <i class="ri-check-line me-1"></i>Escrutada
            </span>
        <?php elseif($table->current_status === 'transmitida'): ?>
            <span class="badge bg-primary role-badge">
                <i class="ri-check-double-line me-1"></i>Transmitida
            </span>
        <?php elseif($table->current_status === 'cerrada'): ?>
            <span class="badge bg-secondary role-badge">
                <i class="ri-lock-line me-1"></i>Cerrada
            </span>
        <?php endif; ?>

        <div class="row align-items-center">
            <div class="col-md-3">
                <h5 class="mb-0">
                    <i class="ri-table-line me-1"></i>
                    Mesa <?php echo e($table->number); ?> - <?php echo e($table->internal_code ?? $table->oep_code); ?>

                </h5>
                <small class="text-muted"><?php echo e($table->institution->name ?? 'N/A'); ?></small>
            </div>
            <div class="col-md-2">
                <?php
                    $statusClasses = [
                        'configurada' => 'secondary',
                        'en_espera' => 'info',
                        'votacion' => 'primary',
                        'cerrada' => 'secondary',
                        'en_escrutinio' => 'warning',
                        'escrutada' => 'success',
                        'observada' => 'danger',
                        'transmitida' => 'success',
                        'anulada' => 'dark',
                        'sin_configurar' => 'light'
                    ];
                ?>
                <span class="badge bg-<?php echo e($statusClasses[$table->current_status] ?? 'secondary'); ?>">
                    <?php echo e($statusLabels[$table->current_status] ?? $table->current_status); ?>

                </span>
            </div>
            <div class="col-md-2">
                <span class="text-muted">
                    <i class="ri-group-line me-1"></i>
                    <?php echo e(number_format($table->expected_voters ?? 0)); ?>

                </span>
            </div>
            <div class="col-md-2">
                <span class="text-muted">
                    <i class="ri-bar-chart-line me-1"></i>
                    Votos: <span class="total-votes fw-bold" id="total-<?php echo e($table->id); ?>"><?php echo e($table->total_voters); ?></span>
                </span>
            </div>
            <div class="col-md-3 text-end">
                <?php echo $__env->make('voting-table-votes.partials.table-actions', ['table' => $table], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>

        
        <div class="row mt-2">
            <div class="col-12">
                <div class="d-flex gap-3 flex-wrap align-items-center">
                    <?php $__empty_1 = true; $__currentLoopData = $candidatesByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoryCode => $candidates): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $categoryTotal = $table->results_by_category[$categoryCode]['total_votes'] ?? 0;
                            $isConsistent = $table->results_by_category[$categoryCode]['is_consistent'] ?? true;
                        ?>
                        <span class="badge bg-<?php echo e($categoryColorMap[$categoryCode] ?? 'secondary'); ?> category-badge">
                            <?php echo e($categoryCode); ?>: <span id="total-<?php echo e($categoryCode); ?>-<?php echo e($table->id); ?>"><?php echo e($categoryTotal); ?></span>
                            <?php if(!$isConsistent): ?>
                                <i class="ri-alert-line text-warning ms-1" title="Inconsistente"></i>
                            <?php endif; ?>
                        </span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <span class="text-muted">No hay categorías disponibles</span>
                    <?php endif; ?>

                    <?php if($hasInconsistencies): ?>
                        <span class="inconsistency-warning">
                            <i class="ri-alert-line me-1"></i>Inconsistencias detectadas
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card-body p-0">
        <?php if(empty($candidatesByCategory)): ?>
            <div class="text-center py-5">
                <p class="text-muted">No hay candidatos disponibles para esta elección</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 3%;">#</th>
                            <th style="width: 10%;">Partido</th>
                            <?php $__currentLoopData = $candidatesByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoryCode => $candidates): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th colspan="3" class="text-center table-<?php echo e($categoryColorMap[$categoryCode] ?? 'secondary'); ?>">
                                    <?php echo e($categoryCode); ?>

                                </th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                        <tr>
                            <th></th>
                            <th></th>
                            <?php $__currentLoopData = $candidatesByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoryCode => $candidates): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th class="table-<?php echo e($categoryColorMap[$categoryCode] ?? 'secondary'); ?>">Candidato</th>
                                <th class="table-<?php echo e($categoryColorMap[$categoryCode] ?? 'secondary'); ?> text-center">Votos</th>
                                <th class="table-<?php echo e($categoryColorMap[$categoryCode] ?? 'secondary'); ?> text-center">Obs</th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php echo $__env->make('voting-table-votes.partials.table-rows', [
                            'table' => $table,
                            'candidatesByCategory' => $candidatesByCategory,
                            'permissions' => $permissions,
                            'isDisabled' => $isDisabled,
                            'categoryColorMap' => $categoryColorMap
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        
        <?php if(($permissions['can_observe'] ?? false) && !$isDisabled && !empty($candidatesByCategory)): ?>
        <div class="p-2 bg-light border-top">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="text-muted" id="selected-count-<?php echo e($table->id); ?>">0</span> votos seleccionados para observar
                    <?php $__currentLoopData = $candidatesByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoryCode => $candidates): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="badge bg-<?php echo e($categoryColorMap[$categoryCode] ?? 'secondary'); ?> ms-2"
                              id="selected-<?php echo e($categoryCode); ?>-<?php echo e($table->id); ?>">0 <?php echo e($categoryCode); ?></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-sm btn-warning create-observation-btn"
                            data-table-id="<?php echo e($table->id); ?>">
                        <i class="ri-chat-1-line me-1"></i>
                        Crear Observación
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        
        <div class="row g-0 bg-light p-2 border-top small">
            <div class="col-md-3">
                <span class="text-muted">Votos Válidos:</span>
                <span class="fw-bold ms-1"><?php echo e(array_sum(array_column($table->results_by_category ?? [], 'valid_votes'))); ?></span>
            </div>
            <div class="col-md-3">
                <span class="text-muted">Votos en Blanco:</span>
                <span class="fw-bold ms-1"><?php echo e(array_sum(array_column($table->results_by_category ?? [], 'blank_votes'))); ?></span>
            </div>
            <div class="col-md-3">
                <span class="text-muted">Votos Nulos:</span>
                <span class="fw-bold ms-1"><?php echo e(array_sum(array_column($table->results_by_category ?? [], 'null_votes'))); ?></span>
            </div>
            <div class="col-md-3">
                <span class="text-muted">Papeletas Sobrantes:</span>
                <span class="fw-bold ms-1"><?php echo e($table->ballots_leftover ?? 0); ?></span>
            </div>
        </div>

        
        <?php if($hasInconsistencies && isset($table->results_by_category)): ?>
            <div class="p-2 border-top bg-warning bg-opacity-10">
                <?php $__currentLoopData = $table->results_by_category; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoryCode => $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(!$result['is_consistent']): ?>
                        <small class="text-warning d-block">
                            <i class="ri-information-line me-1"></i>
                            <?php echo e($categoryCode); ?>: <?php echo e($result['valid_votes']); ?> válidos +
                            <?php echo e($result['blank_votes']); ?> blancos + <?php echo e($result['null_votes']); ?> nulos =
                            <?php echo e($result['valid_votes'] + $result['blank_votes'] + $result['null_votes']); ?>

                            (total: <?php echo e($result['total_votes']); ?>)
                        </small>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH D:\_Mine\sistema_electoral\resources\views/voting-table-votes/partials/table.blade.php ENDPATH**/ ?>