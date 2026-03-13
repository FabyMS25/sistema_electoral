
<?php
    $te              = $table->elections->first();
    $ballotsInUrn    = $te?->total_voters    ?? 0;
    $ballotsLeftover = $te?->ballots_leftover ?? 0;
    $ballotsSpoiled  = $te?->ballots_spoiled  ?? 0;
    $ballotsReceived = $te?->ballots_received ?? 0;
    $expectedVoters  = $table->expected_voters ?? 0;
    $participation = $expectedVoters > 0
        ? round(($ballotsInUrn / $expectedVoters) * 100, 1)
        : 0;
    $balanceOk = ($ballotsReceived === 0)
        || ($ballotsInUrn + $ballotsLeftover + $ballotsSpoiled === $ballotsReceived);
    $canEdit = ($permissions['can_register'] ?? false) && ! $isDisabled;
?>

<div class="border rounded bg-light px-3 py-2 mb-2 ballot-data-section"
     id="ballot-data-<?php echo e($table->id); ?>"
     data-table-id="<?php echo e($table->id); ?>">
    <div class="row align-items-center g-2">
        <div class="col-6 col-md-2">
            <label class="form-label form-label-sm text-muted mb-1">
                <i class="ri-group-line me-1"></i>Habilitados
            </label>
            <div class="fw-bold fs-6 text-info"><?php echo e(number_format($expectedVoters)); ?></div>
            <small class="text-muted">del padrón</small>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label form-label-sm text-muted mb-1">
                <i class="ri-inbox-line me-1"></i>En ánfora
                <span class="badge bg-secondary ms-1" style="font-size:0.65rem;" title="Calculado automáticamente">auto</span>
            </label>
            <div class="fw-bold fs-6 text-primary" id="urn-count-<?php echo e($table->id); ?>">
                <?php echo e(number_format($ballotsInUrn)); ?>

            </div>
            <small class="text-muted">válidos+blancos+nulos</small>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label form-label-sm text-muted mb-1" for="leftover-<?php echo e($table->id); ?>">
                <i class="ri-file-list-3-line me-1"></i>No utilizadas
            </label>
            <?php if($canEdit): ?>
                <input type="number"
                       id="leftover-<?php echo e($table->id); ?>"
                       class="form-control form-control-sm ballot-leftover-input"
                       data-table="<?php echo e($table->id); ?>"
                       value="<?php echo e($ballotsLeftover); ?>"
                       min="0"
                       style="max-width:90px;"
                       placeholder="0"
                       title="Papeletas no utilizadas (sobrantes)">
            <?php else: ?>
                <div class="fw-bold fs-6"><?php echo e(number_format($ballotsLeftover)); ?></div>
            <?php endif; ?>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label form-label-sm text-muted mb-1" for="spoiled-<?php echo e($table->id); ?>">
                <i class="ri-delete-bin-line me-1"></i>Deterioradas
            </label>
            <?php if($canEdit): ?>
                <input type="number"
                       id="spoiled-<?php echo e($table->id); ?>"
                       class="form-control form-control-sm ballot-spoiled-input"
                       data-table="<?php echo e($table->id); ?>"
                       value="<?php echo e($ballotsSpoiled); ?>"
                       min="0"
                       style="max-width:90px;"
                       placeholder="0"
                       title="Papeletas deterioradas / inutilizadas">
            <?php else: ?>
                <div class="fw-bold fs-6"><?php echo e(number_format($ballotsSpoiled)); ?></div>
            <?php endif; ?>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label form-label-sm text-muted mb-1" for="received-<?php echo e($table->id); ?>">
                <i class="ri-mail-download-line me-1"></i>Recibidas
            </label>
            <?php if($canEdit): ?>
                <input type="number"
                       id="received-<?php echo e($table->id); ?>"
                       class="form-control form-control-sm ballot-received-input"
                       data-table="<?php echo e($table->id); ?>"
                       value="<?php echo e($ballotsReceived > 0 ? $ballotsReceived : ''); ?>"
                       min="0"
                       max="<?php echo e($expectedVoters); ?>"
                       style="max-width:90px;"
                       placeholder="<?php echo e($expectedVoters); ?>"
                       title="Total papeletas recibidas (en ánfora + no utilizadas + deterioradas)">
            <?php else: ?>
                <div class="fw-bold fs-6"><?php echo e(number_format($ballotsReceived)); ?></div>
            <?php endif; ?>
            <small class="text-muted">máx <?php echo e(number_format($expectedVoters)); ?></small>
        </div>
        <div class="col-6 col-md-2 text-end">
            <label class="form-label form-label-sm text-muted mb-1">
                <i class="ri-percent-line me-1"></i>Participación
            </label>
            <div class="fw-bold fs-6 <?php echo e($participation >= 75 ? 'text-success' : ($participation >= 50 ? 'text-warning' : 'text-secondary')); ?>"
                 id="participation-<?php echo e($table->id); ?>">
                <?php echo e($participation); ?>%
            </div>
            <div id="ballot-balance-<?php echo e($table->id); ?>" class="mt-1">
                <?php if($ballotsReceived > 0): ?>
                    <?php if($balanceOk): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:0.65rem;">
                            <i class="ri-checkbox-circle-line me-1"></i>Papeletas cuadran
                        </span>
                    <?php else: ?>
                        <?php
                            $diff = $ballotsInUrn + $ballotsLeftover + $ballotsSpoiled - $ballotsReceived;
                        ?>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size:0.65rem;"
                              title="Diferencia: <?php echo e($diff > 0 ? '+' : ''); ?><?php echo e($diff); ?>">
                            <i class="ri-alert-line me-1"></i>No cuadran (<?php echo e($diff > 0 ? '+' : ''); ?><?php echo e($diff); ?>)
                        </span>
                    <?php endif; ?>
                <?php else: ?>
                    <small class="text-muted" style="font-size:0.65rem;">
                        <i class="ri-information-line"></i> Ingrese papeletas recibidas para verificar
                    </small>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="mt-1 text-muted" style="font-size:0.72rem;">
        <i class="ri-equation-line me-1"></i>
        Ánfora + No utilizadas + Deterioradas = Recibidas
        &nbsp;|&nbsp;
        Ánfora = Válidos + Blancos + Nulos
    </div>

</div>

<style>
.ballot-data-section .form-control-sm { font-size: 0.82rem; }
.ballot-data-section label { font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; }
</style>
<?php /**PATH D:\_Mine\sistema_electoral\resources\views/voting-table-votes/partials/ballot-inputs.blade.php ENDPATH**/ ?>