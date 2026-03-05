
<div class="btn-group btn-group-sm">
    <?php if(($table->actas_count ?? 0) > 0): ?>
        <button class="btn btn-info view-actas" data-table-id="<?php echo e($table->id); ?>" title="Ver actas">
            <i class="ri-file-copy-line"></i>
            <span class="badge bg-white text-info ms-1"><?php echo e($table->actas_count); ?></span>
        </button>
    <?php endif; ?>

    <?php if(($table->observations_count ?? 0) > 0): ?>
        <button class="btn btn-warning view-observations" data-table-id="<?php echo e($table->id); ?>" title="Ver observaciones">
            <i class="ri-chat-1-line"></i>
            <span class="badge bg-white text-warning ms-1"><?php echo e($table->observations_count); ?></span>
        </button>
    <?php endif; ?>

    <?php if(!in_array($table->status, ['cerrada', 'escrutada', 'transmitida', 'anulada'])): ?>
        <?php if($permissions['can_register'] ?? false): ?>
            <button class="btn btn-success save-table" data-table-id="<?php echo e($table->id); ?>" title="Guardar (Ctrl+Enter)">
                <i class="ri-save-line"></i>
            </button>
        <?php endif; ?>

        <?php if($permissions['can_observe'] ?? false): ?>
            <button class="btn btn-warning observe-table-general" data-table-id="<?php echo e($table->id); ?>" title="Observación general">
                <i class="ri-chat-1-line"></i>
            </button>
        <?php endif; ?>

        <?php if($permissions['can_upload_acta'] ?? false): ?>  
            <button class="btn btn-info upload-acta" data-table-id="<?php echo e($table->id); ?>" title="Subir acta">
                <i class="ri-upload-line"></i>
            </button>
        <?php endif; ?>

        <?php if($permissions['can_close'] ?? false): ?>
            <button class="btn btn-secondary close-table" data-table-id="<?php echo e($table->id); ?>" title="Cerrar mesa">
                <i class="ri-lock-line"></i>
            </button>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php /**PATH D:\_Mine\corporate\resources\views/voting-table-votes/partials/table-actions.blade.php ENDPATH**/ ?>