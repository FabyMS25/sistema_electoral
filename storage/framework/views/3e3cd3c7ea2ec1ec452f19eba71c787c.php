
<div class="modal fade" id="uploadActaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title text-white">
                    <i class="ri-upload-line me-1"></i>
                    Subir Acta Digital
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="actaTableId">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Número de Acta <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="actaNumber" 
                           placeholder="Ej: ACT-001-2025" required>
                    <small class="text-muted">Número según el registro del OEP</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Foto del Acta <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="actaPhoto" accept="image/*" required>
                    <small class="text-muted">Tome una foto clara del acta original (máx. 5MB)</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">PDF (opcional)</label>
                    <input type="file" class="form-control" id="actaPdf" accept=".pdf">
                    <small class="text-muted">Versión escaneada en PDF (máx. 10MB)</small>
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="hasPhysicalActa" checked value="1">
                    <label class="form-check-label" for="hasPhysicalActa">
                        <strong>Tengo el acta física</strong>
                        <br>
                        <small class="text-muted">Confirmo que poseo el acta física en mi poder</small>
                    </label>
                </div>

                <div class="alert alert-info mt-2">
                    <i class="ri-information-line me-1"></i>
                    <small>El acta será almacenada de forma segura y estará disponible para verificación.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info" id="uploadActaBtn">
                    <i class="ri-upload-line me-1"></i>
                    Subir Acta
                </button>
            </div>
        </div>
    </div>
</div><?php /**PATH D:\_Mine\corporate\resources\views/voting-table-votes/partials/modals/upload-acta-modal.blade.php ENDPATH**/ ?>