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
            <form action="<?php echo e(route('candidates.import')); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ri-information-line me-1"></i>
                        El archivo debe ser CSV con los siguientes encabezados:
                        <ul class="mt-2 mb-2">
                            <li><code>nombre</code> - Nombre del candidato</li>
                            <li><code>partido</code> - Sigla del partido</li>
                            <li><code>nombre_completo_partido</code> - Nombre completo del partido</li>
                            <li><code>color</code> - Color hexadecimal (ej: #1b8af8)</li>
                            <li><code>election_type_category_id</code> - ID de la combinación elección/categoría</li>
                            <li><code>orden_lista</code> - Orden en la lista</li>
                            <li><code>nombre_lista</code> - Nombre de la lista</li>
                            <li><code>department_id</code> - ID del departamento (opcional)</li>
                            <li><code>province_id</code> - ID de la provincia (opcional)</li>
                            <li><code>municipality_id</code> - ID del municipio (opcional)</li>
                        </ul>
                        <a href="<?php echo e(route('candidates.template')); ?>" class="btn btn-sm btn-outline-primary mt-2">
                            <i class="ri-file-download-line me-1"></i>
                            Descargar plantilla
                        </a>
                    </div>

                    <div class="alert alert-warning">
                        <i class="ri-information-line me-1"></i>
                        <strong>Nota:</strong> Los IDs de election_type_category_id, department_id, province_id y municipality_id deben existir en la base de datos.
                    </div>

                    <div class="mb-3">
                        <label for="import_file" class="form-label">
                            Archivo CSV <span class="text-danger">*</span>
                        </label>
                        <input type="file" class="form-control" id="import_file" name="import_file"
                               accept=".csv,.txt" required>
                        <small class="text-muted">Máximo 5MB. Usar codificación UTF-8 para caracteres especiales.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="ri-upload-2-line me-1"></i>
                        Importar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php /**PATH D:\_Mine\corporate\resources\views/candidates/partials/modal-import.blade.php ENDPATH**/ ?>