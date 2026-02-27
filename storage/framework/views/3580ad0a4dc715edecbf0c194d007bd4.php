
<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stats-card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-white-50 mb-2">Total Mesas</h6>
                        <h3 class="mb-0 text-white"><?php echo e($votingTables->count()); ?></h3>
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
                        <h6 class="text-white-50 mb-2">Ciudadanos Habilitados</h6>
                        <h3 class="mb-0 text-white"><?php echo e(number_format($totals['registered'] ?? 0)); ?></h3>
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
                        <h6 class="text-white-50 mb-2">Votos Computados</h6>
                        <h3 class="mb-0 text-white"><?php echo e(number_format($totals['computed'] ?? 0)); ?></h3>
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
                            <?php echo e(isset($totals['registered']) && $totals['registered'] > 0 ? round(($totals['computed'] / $totals['registered']) * 100, 1) : 0); ?>%
                        </h3>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="ri-percent-line display-6 text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if(isset($candidates) && $candidates->isNotEmpty()): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Totales por Candidato</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php $__currentLoopData = $candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $votes = $totals['by_candidate'][$candidate->id] ?? 0;
                            $percentage = ($totals['computed'] ?? 0) > 0 ? round(($votes / $totals['computed']) * 100, 1) : 0;
                        ?>
                        <div class="col-md-4 col-lg-3 mb-3">
                            <div class="candidate-card p-2 border rounded">
                                <div class="d-flex align-items-center">
                                    <span class="candidate-color" style="background-color: <?php echo e($candidate->color ?? '#0ab39c'); ?>; width: 20px; height: 20px; border-radius: 4px; display: inline-block; margin-right: 8px;"></span>
                                    <div class="flex-grow-1 ms-2">
                                        <h6 class="mb-1"><?php echo e($candidate->name); ?></h6>
                                        <small class="text-muted"><?php echo e($candidate->party); ?></small>
                                    </div>
                                    <div class="text-end">
                                        <strong class="d-block"><?php echo e(number_format($votes)); ?></strong>
                                        <small class="text-muted"><?php echo e($percentage); ?>%</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?><?php /**PATH D:\_Mine\corporate\resources\views/voting-table-votes/partials/summary-cards.blade.php ENDPATH**/ ?>