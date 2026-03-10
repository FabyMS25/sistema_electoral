
<script>
function createObservationWithSelected(tableId) {
    // Only consider checkboxes that have a real saved vote ID
    const allChecked = document.querySelectorAll(
        `#table-${tableId} .observe-checkbox:checked:not(:disabled)`
    );
    const checkboxes = Array.from(allChecked).filter(cb => cb.dataset.voteId);

    if (checkboxes.length === 0) {
        const anyChecked = document.querySelectorAll(
            `#table-${tableId} .observe-checkbox:checked:not(:disabled)`
        ).length;
        Swal.fire({
            icon: 'warning',
            title: 'Sin selección válida',
            text: anyChecked > 0
                ? 'Los votos marcados aún no están guardados. Guarde primero con el botón Guardar.'
                : 'Marque al menos un voto (☑) para crear una observación.',
        });
        return;
    }

    // Build a readable list of selected votes
    const selected = Array.from(checkboxes).map(cb => ({
        // data-vote-id is set on the checkbox (see table-rows.blade.php)
        voteId:        cb.dataset.voteId ?? cb.value,
        candidateName: cb.dataset.candidateName ?? '—',
        category:      cb.dataset.category ?? '',
    }));

    const candidatesList = selected
        .map(s => `• ${escHtml(s.candidateName)} (${escHtml(s.category)})`)
        .join('<br>');

    Swal.fire({
        title: `Observar ${selected.length} voto(s)`,
        width: 600,
        html: `
            <div class="text-start mb-3">
                <label class="form-label fw-bold">Tipo de Observación <span class="text-danger">*</span></label>
                <select id="swal-obs-type" class="form-select">
                    <option value="votos_inconsistentes">Votos Inconsistentes</option>
                    <option value="error_datos">Error en Datos</option>
                    <option value="diferencia_papeletas">Diferencia de Papeletas</option>
                    <option value="otro">Otro</option>
                </select>
            </div>
            <div class="text-start mb-3">
                <label class="form-label fw-bold">Severidad</label>
                <select id="swal-obs-severity" class="form-select">
                    <option value="info">Info</option>
                    <option value="warning" selected>Advertencia</option>
                    <option value="error">Error</option>
                    <option value="critical">Crítico</option>
                </select>
            </div>
            <div class="text-start mb-3">
                <label class="form-label fw-bold">Descripción <span class="text-danger">*</span></label>
                <textarea id="swal-obs-desc" class="form-control" rows="3"
                          placeholder="Describa el problema observado..."></textarea>
            </div>
            <div class="text-start">
                <label class="form-label fw-bold">Votos seleccionados:</label>
                <div class="border rounded p-2 bg-light small" style="max-height:120px;overflow-y:auto;">
                    ${candidatesList}
                </div>
            </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Crear Observación',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const desc = document.getElementById('swal-obs-desc').value.trim();
            if (!desc) {
                Swal.showValidationMessage('La descripción es obligatoria');
                return false;
            }
            return {
                type:        document.getElementById('swal-obs-type').value,
                severity:    document.getElementById('swal-obs-severity').value,
                description: desc,
            };
        },
    }).then(result => {
        if (!result.isConfirmed) return;

        const formData = new FormData();
        formData.append('voting_table_id',  tableId);
        formData.append('election_type_id', window.electionTypeId ?? '');
        formData.append('type',             result.value.type);
        formData.append('severity',         result.value.severity);
        formData.append('description',      result.value.description);

        // Send vote IDs (not candidate IDs) — the controller expects vote_ids[]
        selected.forEach(s => formData.append('vote_ids[]', parseInt(s.voteId)));

        fetch('/observations', {
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
                // Visually lock the checkboxes
                checkboxes.forEach(cb => { cb.checked = true; cb.disabled = true; });

                Swal.fire({
                    icon: 'success', title: '✅ Observación creada',
                    text: data.message,
                    timer: 2000, showConfirmButton: false,
                    toast: true, position: 'top-end',
                });
                setTimeout(() => location.reload(), 1800);
            } else {
                const msg = data.errors
                    ? Object.values(data.errors).flat().join('\n')
                    : (data.message ?? 'Error desconocido');
                Swal.fire({ icon: 'error', title: '❌ Error', text: msg });
            }
        })
        .catch(err => {
            Swal.fire({ icon: 'error', title: '❌ Error de red', text: err.message });
        });
    });
}

// ─── Bind buttons ─────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.create-observation-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            createObservationWithSelected(this.dataset.tableId);
        });
    });
});

function escHtml(str) {
    return String(str ?? '')
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
<?php /**PATH D:\_Mine\sistema_electoral\resources\views/voting-table-votes/scripts/observations-by-vote-js.blade.php ENDPATH**/ ?>