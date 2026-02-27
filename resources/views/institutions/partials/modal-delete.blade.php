{{-- resources/views/institutions/partials/modal-delete.blade.php --}}
<div class="modal fade zoomIn" id="deleteRecordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="ri-delete-bin-line text-danger me-1"></i>
                    Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mt-2 text-center">
                    <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop"
                        colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon>
                    <div class="mt-4 pt-2 fs-15 mx-4 mx-sm-5">
                        <h4>¿Está seguro?</h4>
                        <p class="text-muted mx-4 mb-0">
                            ¿Está seguro de que desea eliminar el recinto 
                            <strong class="text-danger" id="delete-institution-name"></strong>?
                        </p>
                        <p class="text-warning mt-2 mb-0">
                            <small>
                                <i class="ri-information-line me-1"></i>
                                Esta acción no se puede deshacer y solo es posible si no tiene mesas asociadas.
                            </small>
                        </p>
                    </div>
                </div>
                <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                    <button type="button" class="btn w-sm btn-light" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancelar
                    </button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn w-sm btn-danger">
                            <i class="ri-delete-bin-line me-1"></i>Sí, eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>