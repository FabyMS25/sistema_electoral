{{--nresources/views/voting-table-votes/partials/modals/observation-modal.blade.php--}}
<div class="modal fade" id="observationModal" tabindex="-1" aria-labelledby="observationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="observationModalLabel">
                    <i class="ri-chat-1-line me-1 text-warning"></i>
                    Crear Observación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                {{-- Hidden fields --}}
                <input type="hidden" id="observationTableId"    value="">
                <input type="hidden" id="observationElectionTypeId" value="{{ $electionTypeId ?? '' }}">

                <div class="row g-3">

                    {{-- Tipo --}}
                    <div class="col-md-6">
                        <label for="observationType" class="form-label fw-bold">
                            Tipo de Observación <span class="text-danger">*</span>
                        </label>
                        <select id="observationType" class="form-select">
                            <option value="">-- Seleccionar --</option>
                            @foreach(\App\Models\Observation::getTypes() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Severidad --}}
                    <div class="col-md-6">
                        <label for="observationSeverity" class="form-label fw-bold">
                            Severidad <span class="text-danger">*</span>
                        </label>
                        <select id="observationSeverity" class="form-select">
                            <option value="info">Info</option>
                            <option value="warning" selected>Advertencia</option>
                            <option value="error">Error</option>
                            <option value="critical">Crítico</option>
                        </select>
                    </div>

                    {{-- Descripción --}}
                    <div class="col-12">
                        <label for="observationDescription" class="form-label fw-bold">
                            Descripción <span class="text-danger">*</span>
                        </label>
                        <textarea id="observationDescription"
                                  class="form-control"
                                  rows="3"
                                  placeholder="Describa detalladamente la observación..."></textarea>
                    </div>

                    {{-- Evidencia --}}
                    <div class="col-12">
                        <label for="observationEvidence" class="form-label fw-bold">
                            Evidencia (foto)
                            <small class="text-muted fw-normal">Máx. 5MB. Formatos: JPG, PNG</small>
                        </label>
                        <input type="file"
                               id="observationEvidence"
                               class="form-control"
                               accept="image/jpeg,image/png,image/jpg">
                    </div>

                    {{-- Votes list (loaded via JS) --}}
                    <div class="col-12">
                        <label class="form-label fw-bold">
                            Votos a observar
                            <small class="text-muted fw-normal">(opcional — marque los que tienen error)</small>
                        </label>
                        <div id="voteCheckboxes"
                             class="border rounded p-2"
                             style="max-height: 250px; overflow-y: auto; min-height: 60px;">
                            <p class="text-muted text-center py-2 mb-0">
                                <i class="ri-loader-4-line me-1"></i>Seleccione una mesa para cargar los votos
                            </p>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-warning" id="saveObservationBtn">
                    <i class="ri-save-line me-1"></i>Crear Observación
                </button>
            </div>

        </div>
    </div>
</div>
