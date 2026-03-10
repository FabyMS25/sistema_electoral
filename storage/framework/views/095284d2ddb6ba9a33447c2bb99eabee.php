
<?php if(isset($tableStats) && ($tableStats['total'] ?? 0) > 0): ?>
<?php
    $total     = $tableStats['total'] ?? 0;
    $gPending  = ($tableStats['configurada'] ?? 0) + ($tableStats['en_espera'] ?? 0);
    $gVoting   = $tableStats['votacion']      ?? 0;
    $gCounting = $tableStats['en_escrutinio'] ?? 0;
    $gDone     = ($tableStats['escrutada'] ?? 0) + ($tableStats['transmitida'] ?? 0);
    $gObserved = $tableStats['observada']     ?? 0;
    $gAnnulled = $tableStats['anulada']       ?? 0;
    $pct = fn($n) => $total > 0 ? round(($n / $total) * 100, 1) : 0;
?>
<?php if($total > 0): ?>
<div class="row mb-2">
    <div class="col-12">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <?php if(($tableStats['configurada'] ?? 0) > 0): ?>
                <a href="<?php echo e(request()->fullUrlWithQuery(['status' => 'configurada'])); ?>"
                   class="badge bg-secondary text-decoration-none p-2 stat-badge"
                   title="Mesas configuradas (no iniciadas)">
                    <i class="ri-settings-4-line me-1"></i>
                    Configuradas: <strong><?php echo e($tableStats['configurada']); ?></strong>
                </a>
            <?php endif; ?>

            <?php if(($tableStats['en_espera'] ?? 0) > 0): ?>
                <a href="<?php echo e(request()->fullUrlWithQuery(['status' => 'en_espera'])); ?>"
                   class="badge bg-info text-white text-decoration-none p-2 stat-badge"
                   title="Mesas en espera de apertura">
                    <i class="ri-time-line me-1"></i>
                    En Espera: <strong><?php echo e($tableStats['en_espera']); ?></strong>
                </a>
            <?php endif; ?>

            <?php if(($tableStats['votacion'] ?? 0) > 0): ?>
                <a href="<?php echo e(request()->fullUrlWithQuery(['status' => 'votacion'])); ?>"
                   class="badge bg-primary text-decoration-none p-2 stat-badge"
                   title="Mesas actualmente en votación">
                    <i class="ri-vote-line me-1"></i>
                    Votación: <strong><?php echo e($tableStats['votacion']); ?></strong>
                </a>
            <?php endif; ?>

            <?php if(($tableStats['en_escrutinio'] ?? 0) > 0): ?>
                <a href="<?php echo e(request()->fullUrlWithQuery(['status' => 'en_escrutinio'])); ?>"
                   class="badge bg-warning text-dark text-decoration-none p-2 stat-badge"
                   title="Mesas en proceso de escrutinio">
                    <i class="ri-bar-chart-2-line me-1"></i>
                    Escrutinio: <strong><?php echo e($tableStats['en_escrutinio']); ?></strong>
                </a>
            <?php endif; ?>

            <?php if(($tableStats['observada'] ?? 0) > 0): ?>
                <a href="<?php echo e(request()->fullUrlWithQuery(['status' => 'observada'])); ?>"
                   class="badge bg-danger text-decoration-none p-2 stat-badge"
                   title="Mesas con observaciones pendientes">
                    <i class="ri-alert-line me-1"></i>
                    Observadas: <strong><?php echo e($tableStats['observada']); ?></strong>
                </a>
            <?php endif; ?>

            <?php if(($tableStats['escrutada'] ?? 0) > 0): ?>
                <a href="<?php echo e(request()->fullUrlWithQuery(['status' => 'escrutada'])); ?>"
                   class="badge bg-success text-decoration-none p-2 stat-badge"
                   title="Mesas con escrutinio completo">
                    <i class="ri-check-double-line me-1"></i>
                    Escrutadas: <strong><?php echo e($tableStats['escrutada']); ?></strong>
                </a>
            <?php endif; ?>

            <?php if(($tableStats['transmitida'] ?? 0) > 0): ?>
                <a href="<?php echo e(request()->fullUrlWithQuery(['status' => 'transmitida'])); ?>"
                   class="badge bg-success text-decoration-none p-2 stat-badge"
                   title="Mesas con resultados transmitidos">
                    <i class="ri-cloud-line me-1"></i>
                    Transmitidas: <strong><?php echo e($tableStats['transmitida']); ?></strong>
                </a>
            <?php endif; ?>

            <?php if(($tableStats['anulada'] ?? 0) > 0): ?>
                <a href="<?php echo e(request()->fullUrlWithQuery(['status' => 'anulada'])); ?>"
                   class="badge bg-dark text-decoration-none p-2 stat-badge"
                   title="Mesas anuladas">
                    <i class="ri-forbid-line me-1"></i>
                    Anuladas: <strong><?php echo e($tableStats['anulada']); ?></strong>
                </a>
            <?php endif; ?>
            <span class="ms-auto text-muted small">
                <strong><?php echo e($total); ?></strong> mesas en total
            </span>
        </div>
    </div>
</div>
<div class="row mb-2">
    <div class="col-12">
        <div class="progress" style="height: 22px; border-radius: 6px;" title="<?php echo e($total); ?> mesas">

            <?php if($gPending > 0): ?>
            <div class="progress-bar bg-secondary" role="progressbar"
                 style="width:<?php echo e($pct($gPending)); ?>%; font-size:11px;"
                 title="Sin iniciar: <?php echo e($gPending); ?>">
                <?php if($pct($gPending) >= 8): ?> <?php echo e($pct($gPending)); ?>% <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if($gVoting > 0): ?>
            <div class="progress-bar bg-primary" role="progressbar"
                 style="width:<?php echo e($pct($gVoting)); ?>%; font-size:11px;"
                 title="Votación: <?php echo e($gVoting); ?>">
                <?php if($pct($gVoting) >= 8): ?> <?php echo e($pct($gVoting)); ?>% <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if($gCounting > 0): ?>
            <div class="progress-bar bg-warning text-dark" role="progressbar"
                 style="width:<?php echo e($pct($gCounting)); ?>%; font-size:11px;"
                 title="Escrutinio: <?php echo e($gCounting); ?>">
                <?php if($pct($gCounting) >= 8): ?> <?php echo e($pct($gCounting)); ?>% <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if($gObserved > 0): ?>
            <div class="progress-bar bg-danger" role="progressbar"
                 style="width:<?php echo e($pct($gObserved)); ?>%; font-size:11px;"
                 title="Observadas: <?php echo e($gObserved); ?>">
                <?php if($pct($gObserved) >= 8): ?> <?php echo e($pct($gObserved)); ?>% <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if($gDone > 0): ?>
            <div class="progress-bar bg-success" role="progressbar"
                 style="width:<?php echo e($pct($gDone)); ?>%; font-size:11px;"
                 title="Completadas: <?php echo e($gDone); ?>">
                <?php if($pct($gDone) >= 8): ?> <?php echo e($pct($gDone)); ?>% <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if($gAnnulled > 0): ?>
            <div class="progress-bar bg-dark" role="progressbar"
                 style="width:<?php echo e($pct($gAnnulled)); ?>%; font-size:11px;"
                 title="Anuladas: <?php echo e($gAnnulled); ?>">
                <?php if($pct($gAnnulled) >= 8): ?> <?php echo e($pct($gAnnulled)); ?>% <?php endif; ?>
            </div>
            <?php endif; ?>

        </div>
        <div class="d-flex flex-wrap gap-3 mt-1 small text-muted">
            <span><span class="badge bg-secondary">&nbsp;</span> Sin iniciar <?php echo e($gPending); ?></span>
            <span><span class="badge bg-primary">&nbsp;</span> Votación <?php echo e($gVoting); ?></span>
            <span><span class="badge bg-warning text-dark">&nbsp;</span> Escrutinio <?php echo e($gCounting); ?></span>
            <span><span class="badge bg-danger">&nbsp;</span> Observadas <?php echo e($gObserved); ?></span>
            <span><span class="badge bg-success">&nbsp;</span> Completadas <?php echo e($gDone); ?></span>
            <?php if($gAnnulled > 0): ?>
            <span><span class="badge bg-dark">&nbsp;</span> Anuladas <?php echo e($gAnnulled); ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>
<style>
.stat-badge { transition: transform .15s, opacity .15s; }
.stat-badge:hover { transform: translateY(-1px); opacity: .85; }
</style>
<?php endif; ?>
<?php /**PATH D:\_Mine\sistema_electoral\resources\views/voting-table-votes/partials/quick-stats.blade.php ENDPATH**/ ?>