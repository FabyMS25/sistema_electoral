
<div class="modal fade" id="viewCandidateModal" tabindex="-1"
     aria-labelledby="viewCandidateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewCandidateModalLabel">
                    <i class="ri-information-line me-1"></i>
                    Detalles del Candidato
                </h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="row g-4">

                    
                    <div class="col-md-3 text-center d-flex flex-column align-items-center gap-3">

                        
                        <div>
                            <img id="view-photo"
                                 src="/build/images/default-candidate.jpg"
                                 alt="Foto del candidato"
                                 class="rounded-circle border border-3 border-info shadow-sm"
                                 style="width:120px; height:120px; object-fit:cover;">
                        </div>

                        
                        <div id="view-party-logo-wrap">
                            <img id="view-party-logo" src="" alt="Logo del partido"
                                 style="max-width:64px; max-height:64px; display:none;">
                        </div>

                        
                        <div>
                            <small class="text-muted d-block mb-1">Color</small>
                            <div id="view-color-preview"
                                 class="mx-auto border rounded"
                                 style="width:40px; height:40px; display:none;"></div>
                            <small id="view-color-hex" class="text-muted font-monospace"></small>
                        </div>

                        
                        <div id="view-active"></div>
                    </div>

                    
                    <div class="col-md-9">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <colgroup>
                                <col style="width:38%">
                                <col>
                            </colgroup>
                            <tbody>
                                <tr>
                                    <th class="table-light">Nombre</th>
                                    <td id="view-name" class="fw-semibold"></td>
                                </tr>
                                <tr>
                                    <th class="table-light">Partido (Sigla)</th>
                                    <td id="view-party"></td>
                                </tr>
                                <tr>
                                    <th class="table-light">Nombre Completo del Partido</th>
                                    <td id="view-party-full-name" class="text-muted fst-italic"></td>
                                </tr>
                                <tr>
                                    <th class="table-light">Tipo de Elección</th>
                                    <td id="view-election-type"></td>
                                </tr>
                                <tr>
                                    <th class="table-light">Categoría</th>
                                    <td id="view-election-category"></td>
                                </tr>
                                <tr>
                                    <th class="table-light">Código de Categoría</th>
                                    <td><code id="view-election-code"></code></td>
                                </tr>
                                <tr>
                                    <th class="table-light">Franja (orden en papeleta)</th>
                                    <td id="view-ballot-order"></td>
                                </tr>
                                <tr>
                                    <th class="table-light">Votos por Persona</th>
                                    <td id="view-votes-per-person"></td>
                                </tr>
                                <tr>
                                    <th class="table-light">Lista / Orden</th>
                                    <td id="view-list"></td>
                                </tr>
                                <tr>
                                    <th class="table-light">Ubicación Geográfica</th>
                                    <td id="view-location"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i> Cerrar
                </button>
            </div>

        </div>
    </div>
</div>
<?php /**PATH D:\_Mine\sistema_electoral\resources\views/candidates/partials/modal-view.blade.php ENDPATH**/ ?>