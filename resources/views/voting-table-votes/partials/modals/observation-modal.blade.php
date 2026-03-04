{{-- resources/views/voting-table-votes/partials/modals/observation-modal.blade.php --}}
<div class="modal fade" id="observationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="ri-chat-1-line me-1"></i>
                    Nueva Observación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="observationForm">
                    @csrf
                    <input type="hidden" id="observationTableId" name="voting_table_id">
                    <input type="hidden" id="selectedVotes" name="selected_votes">

                    <div class="alert alert-info" id="selectedVotesInfo">
                        <i class="ri-information-line me-1"></i>
                        <span id="selectedVotesCount">0</span> voto(s) seleccionados para observar
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de Observación <span class="text-danger">*</span></label>
                            <select class="form-select" id="observationType" name="type" required>
                                <option value="">Seleccionar...</option>
                                @php
                                    $observationController = new \App\Http\Controllers\ObservationController();
                                    $types = $observationController->getTypes();
                                @endphp
                                @foreach($types as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Severidad <span class="text-danger">*</span></label>
                            <select class="form-select" id="observationSeverity" name="severity" required>
                                <option value="info">Info</option>
                                <option value="warning" selected>Advertencia</option>
                                <option value="error">Error</option>
                                <option value="critical">Crítico</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="observationDescription" name="description" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Evidencia (foto)</label>
                        <input type="file" class="form-control" id="observationEvidence" name="evidence" accept="image/*">
                        <small class="text-muted">Máx. 5MB. Formatos: JPG, PNG</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="saveObservationBtn">
                    <i class="ri-save-line me-1"></i>
                    Crear Observación
                </button>
            </div>
        </div>
    </div>
</div>
