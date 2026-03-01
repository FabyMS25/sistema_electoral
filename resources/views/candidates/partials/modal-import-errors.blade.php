<div class="modal fade" id="importErrorsModal" tabindex="-1" aria-labelledby="importErrorsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="importErrorsModalLabel">
                    <i class="ri-error-warning-line me-1"></i>
                    Errores de Importación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="ri-information-line me-1"></i>
                    Se encontraron los siguientes errores durante la importación:
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Error</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(session('import_errors') as $index => $error)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="text-danger">{{ $error }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var importErrorsModal = new bootstrap.Modal(document.getElementById('importErrorsModal'));
    importErrorsModal.show();
});
</script>