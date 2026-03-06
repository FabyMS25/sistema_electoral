

<?php
    $status   = $table->current_status ?? 'sin_configurar';
    $isFinal  = in_array($status, ['escrutada', 'transmitida', 'anulada']);
    $isClosed = $status === 'cerrada';

    // States where new votes can still be entered
    $isEditable = in_array($status, ['configurada', 'en_espera', 'votacion', 'en_escrutinio'])
                  && !$isFinal;

    // States where review makes sense (votes have been entered)
    $isReviewable = in_array($status, ['cerrada', 'votacion', 'en_escrutinio', 'observada', 'corregida']);

    // Validate is relevant once we are in escrutinio or after correction
    $isValidatable = in_array($status, ['en_escrutinio', 'observada', 'cerrada']);
?>

<div class="btn-group btn-group-sm" role="group">

    
    <?php if(($table->observations_count ?? 0) > 0): ?>
        <button type="button"
                class="btn btn-warning view-observations"
                data-table-id="<?php echo e($table->id); ?>"
                title="Ver <?php echo e($table->observations_count); ?> observación(es) pendiente(s)">
            <i class="ri-alert-line"></i>
            <span class="badge bg-white text-warning ms-1"><?php echo e($table->observations_count); ?></span>
        </button>
    <?php endif; ?>

    
    <?php if($isEditable && ($permissions['can_register'] ?? false)): ?>
        <button type="button"
                class="btn btn-success save-table"
                data-table-id="<?php echo e($table->id); ?>"
                data-election-type-id="<?php echo e($electionTypeId); ?>"
                title="Guardar votos (Ctrl+Enter)">
            <i class="ri-save-line"></i>
        </button>
    <?php endif; ?>

    
    <?php if($isReviewable && ($permissions['can_review'] ?? false)): ?>
        <button type="button"
                class="btn btn-info review-table"
                data-table-id="<?php echo e($table->id); ?>"
                data-election-type-id="<?php echo e($electionTypeId); ?>"
                title="Revisar votos">
            <i class="ri-eye-line"></i>
        </button>
    <?php endif; ?>

    
    <?php if($status === 'observada' && ($permissions['can_correct'] ?? false)): ?>
        <button type="button"
                class="btn btn-primary correct-table"
                data-table-id="<?php echo e($table->id); ?>"
                data-election-type-id="<?php echo e($electionTypeId); ?>"
                title="Corregir votos observados">
            <i class="ri-edit-line"></i>
        </button>
    <?php endif; ?>

    
    <?php if($isEditable && ($permissions['can_observe'] ?? false)): ?>
        <button type="button"
                class="btn btn-warning observe-table-general"
                data-table-id="<?php echo e($table->id); ?>"
                data-election-type-id="<?php echo e($electionTypeId); ?>"
                title="Agregar observación general">
            <i class="ri-chat-1-line"></i>
        </button>
    <?php endif; ?>

    
    <?php if(!$isFinal && ($permissions['can_upload_acta'] ?? false)): ?>
        <button type="button"
                class="btn btn-secondary upload-acta"
                data-table-id="<?php echo e($table->id); ?>"
                data-election-type-id="<?php echo e($electionTypeId); ?>"
                onclick="openActaModal(<?php echo e($table->id); ?>, <?php echo e($electionTypeId ?? 'null'); ?>)"
                title="Subir acta">
            <i class="ri-upload-line"></i>
        </button>
    <?php endif; ?>

    
    <?php if($isEditable && ($permissions['can_close'] ?? false)): ?>
        <button type="button"
                class="btn btn-dark close-table"
                data-table-id="<?php echo e($table->id); ?>"
                data-election-type-id="<?php echo e($electionTypeId); ?>"
                title="Cerrar mesa">
            <i class="ri-lock-line"></i>
        </button>
    <?php endif; ?>

    
    <?php if(($isClosed || $status === 'observada') && ($permissions['can_reopen'] ?? false)): ?>
        <button type="button"
                class="btn btn-outline-secondary reopen-table"
                data-table-id="<?php echo e($table->id); ?>"
                data-election-type-id="<?php echo e($electionTypeId); ?>"
                title="Reabrir mesa">
            <i class="ri-lock-unlock-line"></i>
        </button>
    <?php endif; ?>

    
    <?php if($isValidatable && ($permissions['can_validate'] ?? false)): ?>
        <div class="btn-group btn-group-sm" role="group">
            
            <button type="button"
                    class="btn btn-info text-white validate-table"
                    data-table-id="<?php echo e($table->id); ?>"
                    data-election-type-id="<?php echo e($electionTypeId); ?>"
                    data-action="validate"
                    title="Validar votos">
                <i class="ri-checkbox-circle-line"></i> Validar
            </button>
            
            <button type="button"
                    class="btn btn-success validate-table"
                    data-table-id="<?php echo e($table->id); ?>"
                    data-election-type-id="<?php echo e($electionTypeId); ?>"
                    data-action="close_validated"
                    title="Validar y Escrutar mesa (final)">
                <i class="ri-check-double-line"></i>
            </button>
            
            <button type="button"
                    class="btn btn-danger validate-table"
                    data-table-id="<?php echo e($table->id); ?>"
                    data-election-type-id="<?php echo e($electionTypeId); ?>"
                    data-action="reject"
                    title="Rechazar mesa">
                <i class="ri-close-circle-line"></i>
            </button>
        </div>
    <?php endif; ?>

</div>
<?php /**PATH D:\_Mine\sistema_electoral\resources\views/voting-table-votes/partials/table-actions.blade.php ENDPATH**/ ?>