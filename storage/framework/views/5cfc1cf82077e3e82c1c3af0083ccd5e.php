

<div class="modal fade" id="deleteRecordModal" tabindex="-1"
     aria-labelledby="deleteRecordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="deleteRecordModalLabel">
                    <i class="ri-alert-line text-danger me-1"></i>
                    Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center py-4">
                <div class="avatar-lg mx-auto mb-3">
                    <div class="avatar-title bg-danger-subtle text-danger rounded-circle fs-36">
                        <i class="ri-delete-bin-line"></i>
                    </div>
                </div>

                <h5 class="mb-1">¿Eliminar esta mesa de votación?</h5>

                <div class="bg-light rounded p-3 my-3 text-start">
                    <div class="d-flex gap-2 mb-1">
                        <span class="text-muted" style="width:90px">Recinto:</span>
                        <strong id="del-institution">—</strong>
                    </div>
                    <div class="d-flex gap-2 mb-1">
                        <span class="text-muted" style="width:90px">N° Mesa:</span>
                        <strong id="del-number">—</strong>
                    </div>
                    <div class="d-flex gap-2 mb-1">
                        <span class="text-muted" style="width:90px">Código OEP:</span>
                        <code id="del-oep">—</code>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="text-muted" style="width:90px">Cód. Interno:</span>
                        <code id="del-internal">—</code>
                    </div>
                </div>

                <div class="alert alert-warning text-start py-2 mb-0" id="del-elections-warning" style="display:none">
                    <i class="ri-information-line me-1"></i>
                    También se eliminarán <strong id="del-elections-count">0</strong>
                    registro(s) de <code>voting_table_elections</code> asociados.
                </div>

                <p class="text-danger small mt-3 mb-0">
                    <i class="ri-error-warning-line me-1"></i>
                    Esta acción <strong>no se puede deshacer</strong>.
                    Las mesas con votos registrados no pueden eliminarse.
                </p>
            </div>

            <div class="modal-footer justify-content-center border-0 pt-0 gap-2">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancelar
                </button>
                <form id="deleteRecordForm" method="POST" style="display:inline">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="ri-delete-bin-line me-1"></i>Eliminar
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('deleteRecordModal');
    if (!modal) return;

    modal.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;

        document.getElementById('del-institution').textContent = btn.dataset.institution || '—';
        document.getElementById('del-number').textContent      = btn.dataset.number      || '—';
        document.getElementById('del-oep').textContent         = btn.dataset.oep         || '—';
        document.getElementById('del-internal').textContent    = btn.dataset.internal    || '—';

        const electionsCount = parseInt(btn.dataset.elections || '0', 10);
        document.getElementById('del-elections-count').textContent = electionsCount;
        document.getElementById('del-elections-warning').style.display =
            electionsCount > 0 ? 'block' : 'none';

        const deleteUrl = btn.dataset.deleteUrl;
        if (deleteUrl) document.getElementById('deleteRecordForm').action = deleteUrl;
    });
});
</script>
<?php /**PATH D:\_Mine\sistema_electoral\resources\views/voting-tables/partials/modal-delete.blade.php ENDPATH**/ ?>