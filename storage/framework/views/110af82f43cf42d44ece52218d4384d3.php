<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="<?php echo e(route('institutions.import')); ?>" enctype="multipart/form-data" id="import-form">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">
                        <i class="ri-file-upload-line text-info me-1"></i>
                        Importar Recintos
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="import-file" class="form-label">Seleccionar archivo Excel</label>
                        <input class="form-control" type="file" id="import-file"
                               name="file" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">
                            <i class="ri-information-line me-1"></i>
                            Archivos permitidos: .xlsx, .xls, .csv (máx. 5MB)
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="ri-information-line me-1"></i>
                        <strong>Importante:</strong> Asegúrese de que el archivo cumpla con la estructura de la plantilla.
                        <br>
                        <a href="<?php echo e(route('institutions.template')); ?>" class="alert-link mt-2 d-inline-block">
                            <i class="ri-download-line me-1"></i>Descargar Plantilla
                        </a>
                    </div>

                    <div class="alert alert-warning">
                        <i class="ri-error-warning-line me-1"></i>
                        <strong>Requisitos del archivo:</strong>
                        <ul class="mb-0 mt-2">
                            <li>La primera fila debe contener los encabezados</li>
                            <li>El nombre del recinto es obligatorio</li>
                            <li>Departamento, Provincia, Municipio y Localidad deben existir en el sistema</li>
                            <li>El código debe ser único si se proporciona</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="import-submit-btn">
                        <i class="ri-upload-line me-1"></i>Importar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const importForm = document.getElementById('import-form');
    const importBtn = document.getElementById('import-submit-btn');
    const fileInput = document.getElementById('import-file');

    if (importForm) {
        importForm.addEventListener('submit', function(e) {
            if (!fileInput.files.length) {
                e.preventDefault();
                Swal.fire({
                    title: 'Error',
                    text: 'Por favor seleccione un archivo para importar.',
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
                return;
            }
            importBtn.disabled = true;
            importBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i>Importando...';
            Swal.fire({
                title: 'Importando...',
                text: 'Por favor espere mientras se procesa el archivo.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        });
    }
});
</script>
<?php /**PATH D:\_Mine\sistema_electoral\resources\views/institutions/partials/modal-import.blade.php ENDPATH**/ ?>