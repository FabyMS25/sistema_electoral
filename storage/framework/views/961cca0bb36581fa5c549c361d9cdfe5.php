
<div class="modal fade" id="validationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white">
                    <i class="ri-check-line me-1"></i>
                    Validar Resultados
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="validationTableId">
                <div class="alert alert-info">
                    <i class="ri-information-line me-1"></i>
                    Está a punto de validar los resultados de esta mesa.
                </div>
                <div class="mb-3">
                    <label class="form-label">Acción</label>
                    <select class="form-select" id="validationAction">
                        <option value="validate">Validar Resultados</option>
                        <option value="approve">Aprobar Resultados</option>
                        <option value="reject">Rechazar Resultados</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Notas de Validación</label>
                    <textarea class="form-control" id="validationNotes" rows="3" 
                              placeholder="Agregue notas sobre la validación..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="confirmValidationBtn">Confirmar</button>
            </div>
        </div>
    </div>
</div><?php /**PATH D:\_Mine\corporate\resources\views/voting-table-votes/partials/modals/validation-modal.blade.php ENDPATH**/ ?>