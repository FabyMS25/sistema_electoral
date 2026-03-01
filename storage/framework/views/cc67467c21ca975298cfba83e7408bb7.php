
<?php
    $totalTables = $votingTables->total();
    $totalExpectedVoters = $votingTables->sum('expected_voters');
    $totalActualVoters = $votingTables->sum('total_voters');
    $pendingTables = $votingTables->whereIn('status', ['configurada', 'en_espera', 'votacion'])->count();
    $computedTables = $votingTables->whereIn('status', ['escrutada', 'transmitida'])->count();
    $observedTables = $votingTables->where('status', 'observada')->count();
    $annulledTables = $votingTables->where('status', 'anulada')->count();
    $totalActas = $votingTables->filter(function($table) {
        return $table->total_voters > 0 || 
               $table->total_voters_second > 0 || 
               $table->valid_votes > 0 || 
               $table->valid_votes_second > 0;
    })->count();
    $participationPercentage = $totalExpectedVoters > 0 ? round(($totalActualVoters/$totalExpectedVoters)*100, 1) : 0;
    $pendingPercentage = $totalTables > 0 ? round(($pendingTables/$totalTables)*100, 1) : 0;
    $computedPercentage = $totalTables > 0 ? round(($computedTables/$totalTables)*100, 1) : 0;
?>

<div class="row">
    <div class="col-xl-2 col-md-4 mb-1">
        <div class="card stats-card bg-primary text-white">
            <div class="card-body">
                <h6 class="text-white-50 mb-2">Total Mesas</h6>
                <h3 class="mb-0 text-white"><?php echo e(number_format($totalTables)); ?></h3>
                <small class="text-white-50">Registradas</small>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-4 mb-1">
        <div class="card stats-card bg-info text-white">
            <div class="card-body">
                <h6 class="text-white-50 mb-2">Electores</h6>
                <h3 class="mb-0 text-white"><?php echo e(number_format($totalExpectedVoters)); ?></h3>
                <small class="text-white-50">Habilitados</small>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-4 mb-1">
        <div class="card stats-card bg-success text-white">
            <div class="card-body">
                <h6 class="text-white-50 mb-2">Votaron</h6>
                <h3 class="mb-0 text-white"><?php echo e(number_format($totalActualVoters)); ?></h3>
                <small class="text-white-50"><?php echo e($participationPercentage); ?>% participación</small>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-4 mb-1">
        <div class="card stats-card bg-warning text-white">
            <div class="card-body">
                <h6 class="text-white-50 mb-2">Pendientes</h6>
                <h3 class="mb-0 text-white"><?php echo e(number_format($pendingTables)); ?></h3>
                <small class="text-white-50"><?php echo e($pendingPercentage); ?>% del total</small>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-4 mb-1">
        <div class="card stats-card bg-success text-white">
            <div class="card-body">
                <h6 class="text-white-50 mb-2">Escrutadas</h6>
                <h3 class="mb-0 text-white"><?php echo e(number_format($computedTables)); ?></h3>
                <small class="text-white-50"><?php echo e($computedPercentage); ?>% procesado</small>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-4 mb-1">
        <div class="card stats-card bg-danger text-white">
            <div class="card-body">
                <h6 class="text-white-50 mb-2">Actas</h6>
                <h3 class="mb-0 text-white"><?php echo e(number_format($totalActas)); ?></h3>
                <small class="text-white-50">Con votos registrados</small>
            </div>
        </div>
    </div>
</div>

<?php if($observedTables > 0 || $annulledTables > 0): ?>
<div class="row mt-2">
    <div class="col-12">
        <div class="d-flex gap-2 justify-content-end">
            <?php if($observedTables > 0): ?>
            <span class="badge bg-danger-subtle text-danger">
                <i class="ri-error-warning-line me-1"></i>
                <?php echo e($observedTables); ?> mesas observadas
            </span>
            <?php endif; ?>
            <?php if($annulledTables > 0): ?>
            <span class="badge bg-dark-subtle text-dark">
                <i class="ri-forbid-line me-1"></i>
                <?php echo e($annulledTables); ?> mesas anuladas
            </span>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?><?php /**PATH D:\_Mine\corporate\resources\views/voting-tables/partials/stats-cards.blade.php ENDPATH**/ ?>