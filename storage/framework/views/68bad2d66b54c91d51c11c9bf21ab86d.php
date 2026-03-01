
<?php if(isset($tableStats) && $tableStats['total'] > 0): ?>
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="fw-semibold">Resumen por estado:</span>

            <?php if($tableStats['configurada'] > 0): ?>
                <span class="badge bg-secondary p-2" title="Mesas configuradas">
                    <i class="ri-settings-4-line me-1"></i>
                    Configuradas: <?php echo e($tableStats['configurada']); ?>

                </span>
            <?php endif; ?>

            <?php if($tableStats['en_espera'] > 0): ?>
                <span class="badge bg-info p-2" title="Mesas en espera">
                    <i class="ri-time-line me-1"></i>
                    En Espera: <?php echo e($tableStats['en_espera']); ?>

                </span>
            <?php endif; ?>

            <?php if($tableStats['votacion'] > 0): ?>
                <span class="badge bg-primary p-2" title="Mesas en votación">
                    <i class="ri-vote-line me-1"></i>
                    Votación: <?php echo e($tableStats['votacion']); ?>

                </span>
            <?php endif; ?>

            <?php if($tableStats['en_escrutinio'] > 0): ?>
                <span class="badge bg-warning text-dark p-2" title="Mesas en escrutinio">
                    <i class="ri-bar-chart-2-line me-1"></i>
                    Escrutinio: <?php echo e($tableStats['en_escrutinio']); ?>

                </span>
            <?php endif; ?>

            <?php if($tableStats['cerrada'] > 0): ?>
                <span class="badge bg-secondary p-2" title="Mesas cerradas">
                    <i class="ri-lock-line me-1"></i>
                    Cerradas: <?php echo e($tableStats['cerrada']); ?>

                </span>
            <?php endif; ?>

            <?php if($tableStats['escrutada'] > 0): ?>
                <span class="badge bg-success p-2" title="Mesas escrutadas">
                    <i class="ri-check-double-line me-1"></i>
                    Escrutadas: <?php echo e($tableStats['escrutada']); ?>

                </span>
            <?php endif; ?>

            <?php if($tableStats['observada'] > 0): ?>
                <span class="badge bg-danger p-2" title="Mesas observadas">
                    <i class="ri-alert-line me-1"></i>
                    Observadas: <?php echo e($tableStats['observada']); ?>

                </span>
            <?php endif; ?>

            <?php if($tableStats['transmitida'] > 0): ?>
                <span class="badge bg-success p-2" title="Mesas transmitidas">
                    <i class="ri-cloud-line me-1"></i>
                    Transmitidas: <?php echo e($tableStats['transmitida']); ?>

                </span>
            <?php endif; ?>

            <?php if($tableStats['anulada'] > 0): ?>
                <span class="badge bg-dark p-2" title="Mesas anuladas">
                    <i class="ri-forbid-line me-1"></i>
                    Anuladas: <?php echo e($tableStats['anulada']); ?>

                </span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Barra de progreso de validación -->
<div class="row mb-3">
    <div class="col-12">
        <div class="progress" style="height: 25px;">
            <?php
                $total = $tableStats['total'];
                $pendientes = ($tableStats['configurada'] + $tableStats['en_espera']) ?? 0;
                $enProceso = ($tableStats['votacion'] + $tableStats['en_escrutinio']) ?? 0;
                $completadas = ($tableStats['escrutada'] + $tableStats['transmitida']) ?? 0;
                $observadas = $tableStats['observada'] ?? 0;
                $cerradas = $tableStats['cerrada'] ?? 0;

                $pendientesPercent = $total > 0 ? round(($pendientes / $total) * 100) : 0;
                $enProcesoPercent = $total > 0 ? round(($enProceso / $total) * 100) : 0;
                $completadasPercent = $total > 0 ? round(($completadas / $total) * 100) : 0;
                $observadasPercent = $total > 0 ? round(($observadas / $total) * 100) : 0;
                $cerradasPercent = $total > 0 ? round(($cerradas / $total) * 100) : 0;
            ?>

            <?php if($pendientes > 0): ?>
                <div class="progress-bar bg-secondary" role="progressbar"
                     style="width: <?php echo e($pendientesPercent); ?>%"
                     title="Pendientes: <?php echo e($pendientes); ?> mesas">
                    <?php echo e($pendientesPercent); ?>%
                </div>
            <?php endif; ?>

            <?php if($enProceso > 0): ?>
                <div class="progress-bar bg-primary" role="progressbar"
                     style="width: <?php echo e($enProcesoPercent); ?>%"
                     title="En Proceso: <?php echo e($enProceso); ?> mesas">
                    <?php echo e($enProcesoPercent); ?>%
                </div>
            <?php endif; ?>

            <?php if($completadas > 0): ?>
                <div class="progress-bar bg-success" role="progressbar"
                     style="width: <?php echo e($completadasPercent); ?>%"
                     title="Completadas: <?php echo e($completadas); ?> mesas">
                    <?php echo e($completadasPercent); ?>%
                </div>
            <?php endif; ?>

            <?php if($observadas > 0): ?>
                <div class="progress-bar bg-danger" role="progressbar"
                     style="width: <?php echo e($observadasPercent); ?>%"
                     title="Observadas: <?php echo e($observadas); ?> mesas">
                    <?php echo e($observadasPercent); ?>%
                </div>
            <?php endif; ?>

            <?php if($cerradas > 0): ?>
                <div class="progress-bar bg-secondary" role="progressbar"
                     style="width: <?php echo e($cerradasPercent); ?>%"
                     title="Cerradas: <?php echo e($cerradas); ?> mesas">
                    <?php echo e($cerradasPercent); ?>%
                </div>
            <?php endif; ?>
        </div>
        <div class="d-flex justify-content-between mt-1 small text-muted">
            <span>Pendientes: <?php echo e($pendientes); ?></span>
            <span>En Proceso: <?php echo e($enProceso); ?></span>
            <span>Completadas: <?php echo e($completadas); ?></span>
            <span>Observadas: <?php echo e($observadas); ?></span>
            <span>Cerradas: <?php echo e($cerradas); ?></span>
        </div>
    </div>
</div>
<?php endif; ?>
<?php /**PATH D:\_Mine\corporate\resources\views/voting-table-votes/partials/quick-stats.blade.php ENDPATH**/ ?>