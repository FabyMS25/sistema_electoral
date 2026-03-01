<div class="modal fade" id="observationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-white">
                    <i class="ri-chat-1-line me-1"></i>
                    Crear Observación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="observationTableId">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Tipo de Observación <span class="text-danger">*</span></label>
                    <select class="form-select" id="observationType" required>
                        <option value="">Seleccione un tipo</option>
                        <option value="inconsistencia_acta">Inconsistencia en Acta</option>
                        <option value="error_datos">Error en Datos</option>
                        <option value="falta_firma">Falta Firma</option>
                        <option value="acta_ilegible">Acta Ilegible</option>
                        <option value="votos_inconsistentes">Votos Inconsistentes</option>
                        <option value="mesa_anulada">Mesa Anulada</option>
                        <option value="reclamo_partido">Reclamo de Partido</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Descripción <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="observationDescription" rows="3" 
                              placeholder="Describa la observación en detalle..." required></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Severidad <span class="text-danger">*</span></label>
                    <select class="form-select" id="observationSeverity" required>
                        <option value="info">Informativo</option>
                        <option value="warning" selected>Advertencia</option>
                        <option value="error">Error</option>
                        <option value="critical">Crítico</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Evidencia (opcional)</label>
                    <input type="file" class="form-control" id="observationEvidence" accept="image/*,.pdf">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="saveObservationBtn">Crear Observación</button>
            </div>
        </div>
    </div>
</div>