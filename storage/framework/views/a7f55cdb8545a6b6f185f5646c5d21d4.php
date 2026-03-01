<?php
    $totalInstitutions = $institutions->total();
    $totalCitizens = $institutions->sum('registered_citizens');
    $totalComputed = $institutions->sum('total_computed_records');
    $totalActive = $institutions->where('status', 'activo')->count();
    $totalOperative = $institutions->where('is_operative', true)->count();
    $totalInactive = $institutions->where('status', 'inactivo')->count();
    $totalMaintenance = $institutions->where('status', 'en_mantenimiento')->count();
    $activePercentage = $totalInstitutions > 0 ? round(($totalActive / $totalInstitutions) * 100, 1) : 0;
?>

<div class="row">
    <div class="col-xl-2 col-md-3 mb-1">
        <div class="card stats-card bg-primary text-white">
            <div class="card-body">
                <h6 class="text-white-50 mb-1">Total Recintos</h6>
                <h3 class="mb-0 text-white"><?php echo e(number_format($totalInstitutions)); ?></h3>
                <small class="text-white-50">Registrados</small>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-3 mb-1">
        <div class="card stats-card bg-info text-white">
            <div class="card-body">
                <h6 class="text-white-50 mb-1">Ciudadanos</h6>
                <h3 class="mb-0 text-white"><?php echo e(number_format($totalCitizens)); ?></h3>
                <small class="text-white-50">Habilitados</small>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-3 mb-1">
        <div class="card stats-card bg-success text-white">
            <div class="card-body">
                <h6 class="text-white-50 mb-1">Recintos Activos</h6>
                <h3 class="mb-0 text-white"><?php echo e(number_format($totalActive)); ?></h3>
                <small class="text-white-50"><?php echo e($activePercentage); ?>% del total</small>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-3 mb-1">
        <div class="card stats-card bg-warning text-white">
            <div class="card-body">
                <h6 class="text-white-50 mb-1">Operativos</h6>
                <h3 class="mb-0 text-white"><?php echo e(number_format($totalOperative)); ?></h3>
                <small class="text-white-50">Habilitados para elecciones</small>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-3 mb-1">
        <div class="card stats-card bg-success text-white">
            <div class="card-body">
                <h6 class="text-white-50 mb-1">Actas Computadas</h6>
                <h3 class="mb-0 text-white"><?php echo e(number_format($totalComputed)); ?></h3>
                <small class="text-white-50">Registradas</small>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-3 mb-1">
        <div class="card stats-card bg-danger text-white">
            <div class="card-body">
                <h6 class="text-white-50 mb-1">No Operativos</h6>
                <h3 class="mb-0 text-white"><?php echo e(number_format($totalInactive + $totalMaintenance)); ?></h3>
                <small class="text-white-50">Inactivos o en mantenimiento</small>
            </div>
        </div>
    </div>
</div>

<?php if($totalInactive > 0 || $totalMaintenance > 0): ?>
<div class="row mt-2">
    <div class="col-12">
        <div class="d-flex gap-2 justify-content-end">
            <?php if($totalInactive > 0): ?>
            <span class="badge bg-danger-subtle text-danger">
                <i class="ri-close-circle-line me-1"></i>
                <?php echo e($totalInactive); ?> recintos inactivos
            </span>
            <?php endif; ?>
            <?php if($totalMaintenance > 0): ?>
            <span class="badge bg-warning-subtle text-warning">
                <i class="ri-tools-line me-1"></i>
                <?php echo e($totalMaintenance); ?> en mantenimiento
            </span>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>
<?php /**PATH D:\_Mine\corporate\resources\views/institutions/partials/stats-cards.blade.php ENDPATH**/ ?>