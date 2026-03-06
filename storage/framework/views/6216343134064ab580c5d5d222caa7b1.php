
<div class="modal fade" id="validationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="ri-check-line me-1"></i>
                    Validar Mesa
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="validationForm">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" id="validationTableId">

                    <div class="mb-3">
                        <label class="form-label">Acción</label>
                        <select class="form-select" id="validationAction" required>
                            <option value="validate">Validar</option>
                            <option value="approve">Aprobar</option>
                            <option value="reject">Rechazar</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notas</label>
                        <textarea class="form-control" id="validationNotes" rows="3"></textarea>
                    </div>

                    <div class="alert alert-warning" id="validationWarning" style="display: none;">
                        <i class="ri-alert-line me-1"></i>
                        <span id="validationWarningText"></span>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="confirmValidationBtn">
                    <i class="ri-check-line me-1"></i>
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</div>
<?php /**PATH D:\_Mine\sistema_electoral\resources\views/voting-table-votes/partials/modals/validation-modal.blade.php ENDPATH**/ ?>