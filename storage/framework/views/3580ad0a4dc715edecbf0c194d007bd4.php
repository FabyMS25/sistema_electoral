
<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-white-50 mb-2">Total Mesas</h6>
                        <h3 class="mb-0 text-white"><?php echo e(number_format($tableStats['total'] ?? 0)); ?></h3>
                        <small class="text-white-50">Visibles en filtros</small>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="ri-table-line display-6 text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stats-card bg-info text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-white-50 mb-2">Votantes Esperados</h6>
                        <h3 class="mb-0 text-white"><?php echo e(number_format($totals['expected'] ?? 0)); ?></h3>
                        <small class="text-white-50">Según padrón</small>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="ri-group-line display-6 text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stats-card bg-success text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-white-50 mb-2">Votos Totales</h6>
                        <h3 class="mb-0 text-white"><?php echo e(number_format($totals['total'] ?? 0)); ?></h3>
                        <small class="text-white-50">Alcalde + Concejal</small>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="ri-bar-chart-line display-6 text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stats-card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-white-50 mb-2">Participación</h6>
                        <h3 class="mb-0 text-white">
                            <?php echo e(isset($totals['expected']) && $totals['expected'] > 0 ? round(($totals['total'] / $totals['expected']) * 100, 1) : 0); ?>%
                        </h3>
                        <small class="text-white-50">Promedio general</small>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="ri-percent-line display-6 text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tarjetas de totales por categoría -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white py-2">
                <h6 class="mb-0">
                    <i class="ri-user-star-line me-1"></i>
                    Alcalde
                </h6>
            </div>
            <div class="card-body py-3">
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-primary mb-0"><?php echo e(number_format($totals['alcalde'] ?? 0)); ?></h4>
                        <small class="text-muted">Votos Válidos</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-primary mb-0"><?php echo e(isset($totals['alcalde']) && $totals['total'] > 0 ? round(($totals['alcalde'] / $totals['total']) * 100, 1) : 0); ?>%</h4>
                        <small class="text-muted">del total</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-success">
            <div class="card-header bg-success text-white py-2">
                <h6 class="mb-0">
                    <i class="ri-group-line me-1"></i>
                    Concejal
                </h6>
            </div>
            <div class="card-body py-3">
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-success mb-0"><?php echo e(number_format($totals['concejal'] ?? 0)); ?></h4>
                        <small class="text-muted">Votos Válidos</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success mb-0"><?php echo e(isset($totals['concejal']) && $totals['total'] > 0 ? round(($totals['concejal'] / $totals['total']) * 100, 1) : 0); ?>%</h4>
                        <small class="text-muted">del total</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Totales por candidato -->
<?php if(isset($candidates) && $candidates->isNotEmpty() && isset($totals['by_candidate']) && count(array_filter($totals['by_candidate'])) > 0): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Totales por Candidato</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php
                        $alcaldeCategory = \App\Models\ElectionCategory::where('code', 'ALC')->first();
                        $concejalCategory = \App\Models\ElectionCategory::where('code', 'CON')->first();
                    ?>

                    <?php $__currentLoopData = $candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $votes = $totals['by_candidate'][$candidate->id] ?? 0;
                            $categoryTotal = $candidate->election_category_id == $alcaldeCategory?->id ? ($totals['alcalde'] ?? 0) : ($totals['concejal'] ?? 0);
                            $percentage = $categoryTotal > 0 ? round(($votes / $categoryTotal) * 100, 1) : 0;
                            $categoryClass = $candidate->election_category_id == $alcaldeCategory?->id ? 'primary' : 'success';
                        ?>
                        <?php if($votes > 0): ?>
                        <div class="col-md-4 col-lg-3 mb-3">
                            <div class="card border-<?php echo e($categoryClass); ?> h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <span class="candidate-color" style="background-color: <?php echo e($candidate->color ?? '#0ab39c'); ?>; width: 20px; height: 20px; border-radius: 4px; display: inline-block; margin-right: 8px;"></span>
                                        <div class="flex-grow-1 ms-2">
                                            <h6 class="mb-1"><?php echo e($candidate->name); ?></h6>
                                            <small class="text-muted"><?php echo e($candidate->party); ?></small>
                                        </div>
                                        <div class="text-end">
                                            <strong class="d-block"><?php echo e(number_format($votes)); ?></strong>
                                            <small class="text-<?php echo e($categoryClass); ?>"><?php echo e($percentage); ?>%</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php /**PATH D:\_Mine\corporate\resources\views/voting-table-votes/partials/summary-cards.blade.php ENDPATH**/ ?>