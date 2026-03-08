{{-- resources/views/candidates/partials/modal-delete.blade.php --}}
<div class="modal fade zoomIn" id="deleteRecordModal" tabindex="-1"
     aria-labelledby="deleteRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
            </div>

            <div class="modal-body text-center py-4">
                <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop"
                    colors="primary:#f7b84b,secondary:#f06548"
                    style="width:80px;height:80px">
                </lord-icon>
                <h4 class="mt-3">¿Está seguro?</h4>
                <p id="deleteMessage" class="text-muted mb-0">
                    ¿Desea eliminar este candidato?
                </p>
                <small class="text-muted">Esta acción desactivará al candidato (no se eliminan los datos).</small>
            </div>

            <div class="modal-footer justify-content-center gap-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i> Cancelar
                </button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="ri-delete-bin-line me-1"></i> Sí, eliminar
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
