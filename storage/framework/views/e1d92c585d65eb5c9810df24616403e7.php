
<!-- Modal para Ver Detalles del Candidato -->
<div class="modal fade" id="viewCandidateModal" tabindex="-1" aria-labelledby="viewCandidateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewCandidateModalLabel">
                    <i class="ri-information-line me-1"></i>
                    Detalles del Candidato
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="mb-3">
                            <img id="view-photo" src="" alt="Foto del candidato" class="img-fluid rounded-circle" style="max-width: 150px; max-height: 150px; border: 3px solid #0ab39c;">
                            <div class="mt-2">
                                <img id="view-party-logo" src="" alt="Logo del partido" style="max-width: 60px; max-height: 60px;">
                            </div>
                        </div>
                        <div id="view-color-preview" class="color-preview mx-auto" style="width: 40px; height: 40px; border-radius: 4px; border: 1px solid #ddd;"></div>
                    </div>

                    <div class="col-md-8">
                        <table class="table table-bordered table-sm">
                            <tr>
                                <th style="width: 40%; background-color: #f8f9fa;">Nombre:</th>
                                <td id="view-name" class="fw-semibold"></td>
                            </tr>
                            <tr>
                                <th style="background-color: #f8f9fa;">Partido (Sigla):</th>
                                <td id="view-party"></td>
                            </tr>
                            <tr>
                                <th style="background-color: #f8f9fa;">Nombre Completo del Partido:</th>
                                <td id="view-party-full-name"></td>
                            </tr>
                            <tr>
                                <th style="background-color: #f8f9fa;">Lista / Orden:</th>
                                <td id="view-list"></td>
                            </tr>
                            <tr>
                                <th style="background-color: #f8f9fa;">Tipo de Elección:</th>
                                <td id="view-election-type"></td>
                            </tr>
                            <tr>
                                <th style="background-color: #f8f9fa;">Categoría:</th>
                                <td id="view-election-category"></td>
                            </tr>
                            <tr>
                                <th style="background-color: #f8f9fa;">Código de Categoría:</th>
                                <td><code id="view-election-code"></code></td>
                            </tr>
                            <tr>
                                <th style="background-color: #f8f9fa;">Franja (Orden en Papeleta):</th>
                                <td id="view-ballot-order"></td>
                            </tr>
                            <tr>
                                <th style="background-color: #f8f9fa;">Votos por Persona:</th>
                                <td id="view-votes-per-person"></td>
                            </tr>
                            <tr>
                                <th style="background-color: #f8f9fa;">Ubicación:</th>
                                <td id="view-location"></td>
                            </tr>
                            <tr>
                                <th style="background-color: #f8f9fa;">Estado:</th>
                                <td id="view-active"></td>
                            </tr>
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
<?php /**PATH D:\_Mine\corporate\resources\views/candidates/partials/modal-view.blade.php ENDPATH**/ ?>