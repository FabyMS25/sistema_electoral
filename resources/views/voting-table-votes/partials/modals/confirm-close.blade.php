{{-- resources/views/voting-table-votes/partials/modals/confirm-close.blade.php --}}
<div class="modal fade" id="confirmCloseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-white">
                    <i class="ri-alert-line me-1"></i>
                    Confirmar Cierre
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">¿Está seguro que desea cerrar esta mesa?</p>
                <p class="text-danger mt-2 mb-0">
                    <small>Una vez cerrada, no se podrán modificar los votos.</small>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmCloseBtn">Cerrar Mesa</button>
            </div>
        </div>
    </div>
</div>