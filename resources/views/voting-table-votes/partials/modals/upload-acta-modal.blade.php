{{--
    resources/views/voting-table-votes/partials/modals/upload-acta-modal.blade.php
--}}
<div class="modal fade" id="uploadActaModal" tabindex="-1" aria-labelledby="uploadActaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="uploadActaModalLabel">
                    <i class="ri-upload-line me-1 text-info"></i>
                    Subir Acta
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="actaTableId"          value="">
                <input type="hidden" id="actaElectionTypeId"   value="">

                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="actaNumber" class="form-label fw-bold">
                            Número de Acta <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="actaNumber" class="form-control"
                               placeholder="Ej: ACTA-001">
                    </div>

                    <div class="col-md-6">
                        <label for="actaHasPhysical" class="form-label fw-bold">
                            ¿Tiene acta física?
                        </label>
                        <select id="actaHasPhysical" class="form-select">
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label for="actaPhoto" class="form-label fw-bold">
                            Foto del Acta <span class="text-danger">*</span>
                            <small class="text-muted fw-normal">Máx. 5 MB. JPG o PNG.</small>
                        </label>
                        <input type="file" id="actaPhoto" class="form-control"
                               accept="image/jpeg,image/png,image/jpg">
                        <div id="actaPhotoPreview" class="mt-2 d-none">
                            <img id="actaPhotoImg" src="" alt="Preview"
                                 class="img-thumbnail" style="max-height:200px;">
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="actaPdf" class="form-label fw-bold">
                            PDF del Acta
                            <small class="text-muted fw-normal">(opcional, máx. 10 MB)</small>
                        </label>
                        <input type="file" id="actaPdf" class="form-control" accept=".pdf">
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-info text-white" id="saveActaBtn">
                    <i class="ri-upload-line me-1"></i>Subir Acta
                </button>
            </div>

        </div>
    </div>
</div>

<script>
// ─── Photo preview ────────────────────────────────────────────────────────────
document.getElementById('actaPhoto')?.addEventListener('change', function() {
    const file = this.files[0];
    const preview = document.getElementById('actaPhotoPreview');
    const img     = document.getElementById('actaPhotoImg');
    if (file && preview && img) {
        img.src = URL.createObjectURL(file);
        preview.classList.remove('d-none');
    }
});

// ─── Open modal ───────────────────────────────────────────────────────────────
window.openActaModal = function(tableId, electionTypeId) {
    const resolvedEtId = (electionTypeId && electionTypeId !== 'null')
        ? electionTypeId
        : (window.electionTypeId ?? '');

    if (!resolvedEtId) {
        Swal.fire({ icon: 'error', title: 'Error',
                    text: 'No se pudo determinar el tipo de elección. Recargue la página.' });
        return;
    }

    document.getElementById('actaTableId').value        = tableId;
    document.getElementById('actaElectionTypeId').value = resolvedEtId;
    document.getElementById('actaNumber').value         = '';
    document.getElementById('actaPhoto').value          = '';
    document.getElementById('actaPdf').value            = '';
    document.getElementById('actaPhotoPreview').classList.add('d-none');

    new bootstrap.Modal(document.getElementById('uploadActaModal')).show();
};

// ─── Save acta ────────────────────────────────────────────────────────────────
document.getElementById('saveActaBtn')?.addEventListener('click', function() {
    const tableId      = document.getElementById('actaTableId').value;
    const etId         = document.getElementById('actaElectionTypeId').value;
    const actaNumber   = document.getElementById('actaNumber').value.trim();
    const photoFile    = document.getElementById('actaPhoto').files[0];
    const pdfFile      = document.getElementById('actaPdf').files[0];
    const hasPhysical  = document.getElementById('actaHasPhysical').value;

    if (!tableId || tableId === '0') {
        return Swal.fire({ icon: 'error', title: 'Error', text: 'Mesa no identificada' });
    }
    if (!etId || etId === '0' || etId === 'null') {
        return Swal.fire({ icon: 'error', title: 'Error', text: 'Tipo de elección no identificado' });
    }
    if (!actaNumber) {
        return Swal.fire({ icon: 'error', title: 'Error', text: 'Ingrese el número de acta' });
    }
    if (!photoFile) {
        return Swal.fire({ icon: 'error', title: 'Error', text: 'Seleccione la foto del acta' });
    }
    if (photoFile.size > 5 * 1024 * 1024) {
        return Swal.fire({ icon: 'error', title: 'Error', text: 'La foto no puede superar 5 MB' });
    }

    const formData = new FormData();
    formData.append('voting_table_id',  tableId);
    formData.append('election_type_id', etId);
    formData.append('acta_number',      actaNumber);
    formData.append('photo',            photoFile);
    formData.append('has_physical',     hasPhysical);
    if (pdfFile) formData.append('pdf', pdfFile);

    const btn      = this;
    const origHtml = btn.innerHTML;
    btn.innerHTML  = '<i class="ri-loader-4-line ri-spin me-1"></i>Subiendo...';
    btn.disabled   = true;

    fetch('/actas/upload', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: formData,
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('uploadActaModal'))?.hide();
            Swal.fire({
                icon: 'success', title: '✅ Acta subida',
                text: data.message,
                toast: true, position: 'top-end',
                showConfirmButton: false, timer: 2500,
            });
        } else {
            const msg = data.errors
                ? Object.values(data.errors).flat().join('\n')
                : (data.message ?? 'Error desconocido');
            Swal.fire({ icon: 'error', title: '❌ Error', text: msg });
        }
    })
    .catch(err => {
        Swal.fire({ icon: 'error', title: '❌ Error de red', text: err.message });
    })
    .finally(() => {
        btn.innerHTML = origHtml;
        btn.disabled  = false;
    });
});
</script>
