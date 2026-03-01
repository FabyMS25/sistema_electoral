<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="importModalLabel">
                    <i class="ri-file-upload-line me-1"></i>
                    Importar Candidatos
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('candidates.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ri-information-line me-1"></i>
                        El archivo debe ser CSV con los siguientes encabezados:
                        <ul class="mt-2 mb-0">
                            <li>nombre, partido, nombre_completo_partido, color, categoria_eleccion_id, tipo_eleccion_id, tipo, orden_lista, nombre_lista</li>
                        </ul>
                        <a href="{{ route('candidates.template') }}" class="btn btn-sm btn-outline-primary mt-2">
                            <i class="ri-file-download-line me-1"></i>
                            Descargar plantilla
                        </a>
                    </div>
                    
                    <div class="mb-3">
                        <label for="import_file" class="form-label">Archivo CSV <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="import_file" name="import_file" accept=".csv,.txt" required>
                        <small class="text-muted">Máximo 5MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="ri-upload-2-line me-1"></i>
                        Importar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>