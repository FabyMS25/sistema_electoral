{{-- resources/views/voting-table-votes/partials/modals/upload-acta-modal.blade.php --}}
<div class="modal fade" id="uploadActaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="ri-upload-line me-1"></i>
                    Subir Acta de Mesa
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="uploadActaForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="actaTableId" name="voting_table_id">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Número de Acta <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="actaNumber" name="acta_number" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="hasPhysicalActa" name="has_physical" checked>
                                <label class="form-check-label" for="hasPhysicalActa">
                                    ¿Tiene acta física?
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Foto del Acta <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="actaPhoto" name="photo" accept="image/*" required>
                        <small class="text-muted">Máx. 10MB. Formatos: JPG, PNG. Tome una foto clara y legible.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">PDF del Acta (opcional)</label>
                        <input type="file" class="form-control" id="actaPdf" name="pdf" accept=".pdf">
                        <small class="text-muted">Máx. 20MB</small>
                    </div>

                    <div class="alert alert-warning">
                        <i class="ri-information-line me-1"></i>
                        <strong>Importante:</strong> La foto del acta debe ser legible y mostrar claramente todos los resultados.
                        El sistema verificará automáticamente la consistencia con los votos registrados.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-info" id="uploadActaBtn">
                    <i class="ri-upload-line me-1"></i>
                    Subir Acta
                </button>
            </div>
        </div>
    </div>
</div>
