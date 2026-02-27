
<?php
    $totalTables = $votingTables->total();
    $totalCitizens = $votingTables->sum('registered_citizens');
    $totalVoted = $votingTables->sum('voted_citizens');
    $totalComputed = $votingTables->sum('computed_records');
    $pendingTables = $votingTables->where('status', 'pendiente')->count();
    $computedTables = $votingTables->where('status', 'computado')->count();
?>

<div class="row mb-4">
    <div class="col-xl-2 col-md-4">
        <div class="card stats-card bg-primary text-white">
            <div class="card-body">
                <h6 class="text-white-50 mb-2">Total Mesas</h6>
                <h3 class="mb-0 text-white"><?php echo e(number_format($totalTables)); ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-4">
        <div class="card stats-card bg-info text-white">
            <div class="card-body">
                <h6 class="text-white-50 mb-2">Electores</h6>
                <h3 class="mb-0 text-white"><?php echo e(number_format($totalCitizens)); ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-4">
        <div class="card stats-card bg-success text-white">
            <div class="card-body">
                <h6 class="text-white-50 mb-2">Votaron</h6>
                <h3 class="mb-0 text-white"><?php echo e(number_format($totalVoted)); ?></h3>
                <small><?php echo e($totalCitizens > 0 ? round(($totalVoted/$totalCitizens)*100, 1) : 0); ?>%</small>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-4">
        <div class="card stats-card bg-warning text-white">
            <div class="card-body">
                <h6 class="text-white-50 mb-2">Pendientes</h6>
                <h3 class="mb-0 text-white"><?php echo e(number_format($pendingTables)); ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-4">
        <div class="card stats-card bg-success text-white">
            <div class="card-body">
                <h6 class="text-white-50 mb-2">Computadas</h6>
                <h3 class="mb-0 text-white"><?php echo e(number_format($computedTables)); ?></h3>
            </div>
        </div>
    </div>
    
    <div class="col-xl-2 col-md-4">
        <div class="card stats-card bg-danger text-white">
            <div class="card-body">
                <h6 class="text-white-50 mb-2">Actas</h6>
                <h3 class="mb-0 text-white"><?php echo e(number_format($totalComputed)); ?></h3>
            </div>
        </div>
    </div>
</div><?php /**PATH D:\_Mine\corporate\resources\views/voting-tables/partials/stats-cards.blade.php ENDPATH**/ ?>