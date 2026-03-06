
<script>
window.observeTable = function(tableId, electionTypeIdOverride) {
    const etId = electionTypeIdOverride ?? window.electionTypeId;
    document.getElementById('observationTableId').value          = tableId;
    document.getElementById('observationElectionTypeId').value   = etId ?? '';
    document.getElementById('observationType').value        = '';
    document.getElementById('observationSeverity').value    = 'warning';
    document.getElementById('observationDescription').value = '';
    const evInput = document.getElementById('observationEvidence');
    if (evInput) evInput.value = '';
    loadVotesForObservationModal(tableId, etId);
    const modal = new bootstrap.Modal(document.getElementById('observationModal'));
    modal.show();
};

function loadVotesForObservationModal(tableId, electionTypeId) {
    const container = document.getElementById('voteCheckboxes');
    if (!container) return;

    container.innerHTML = `
        <div class="text-center text-muted py-3">
            <i class="ri-loader-4-line ri-spin me-1"></i>Cargando votos...
        </div>`;

    const url = `/voting-table-votes/${tableId}/votes` +
                (electionTypeId ? `?election_type_id=${electionTypeId}` : '');

    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(votes => {
            if (!Array.isArray(votes) || votes.length === 0) {
                container.innerHTML =
                    '<p class="text-muted text-center py-3 mb-0">No hay votos registrados en esta mesa</p>';
                return;
            }

            // Group by category for readability
            const byCategory = {};
            votes.forEach(v => {
                const cat = v.vote_status === 'observed'
                    ? '⚠️ Ya observados'
                    : (v.category_id ?? 'General');
                if (!byCategory[cat]) byCategory[cat] = [];
                byCategory[cat].push(v);
            });

            let html = '';
            Object.entries(byCategory).forEach(([cat, catVotes]) => {
                html += `<div class="mb-2">
                    <small class="text-muted fw-bold d-block mb-1">${escHtml(String(cat))}</small>
                    <div class="row">`;

                catVotes.forEach(v => {
                    // FIX: compare against the string constant value directly
                    const isObserved = v.vote_status === 'observed';
                    html += `
                        <div class="col-md-6 mb-1">
                            <div class="form-check ${isObserved ? 'text-warning' : ''}">
                                <input class="form-check-input observation-vote-cb"
                                       type="checkbox"
                                       name="vote_ids[]"
                                       value="${v.id}"
                                       id="obsVote_${v.id}"
                                       data-candidate-name="${escAttr(v.candidate_name)}"
                                       ${isObserved ? 'checked disabled' : ''}>
                                <label class="form-check-label small" for="obsVote_${v.id}">
                                    <strong>${escHtml(v.candidate_name)}</strong><br>
                                    <span class="text-muted">${escHtml(v.candidate_party)}</span>
                                    — ${v.quantity} votos
                                    ${isObserved
                                        ? '<span class="badge bg-warning text-dark ms-1">Ya observado</span>'
                                        : ''}
                                </label>
                            </div>
                        </div>`;
                });

                html += `</div></div>`;
            });

            container.innerHTML = html;
        })
        .catch(err => {
            console.error('loadVotesForObservationModal error:', err);
            container.innerHTML =
                '<p class="text-danger text-center py-3 mb-0">Error al cargar votos</p>';
        });
}

// ─── Save observation ─────────────────────────────────────────────────────────

document.getElementById('saveObservationBtn')?.addEventListener('click', function() {
    const tableId      = document.getElementById('observationTableId').value;
    const etId         = document.getElementById('observationElectionTypeId').value;
    const type         = document.getElementById('observationType').value;
    const description  = document.getElementById('observationDescription').value.trim();
    const severity     = document.getElementById('observationSeverity').value;
    const evidenceFile = document.getElementById('observationEvidence')?.files[0];

    if (!tableId) {
        return Swal.fire({ icon: 'error', title: 'Error', text: 'No se ha identificado la mesa' });
    }
    if (!etId) {
        return Swal.fire({ icon: 'error', title: 'Error', text: 'No se ha identificado el tipo de elección' });
    }
    if (!type) {
        return Swal.fire({ icon: 'error', title: 'Error', text: 'Seleccione el tipo de observación' });
    }
    if (!description) {
        return Swal.fire({ icon: 'error', title: 'Error', text: 'La descripción es obligatoria' });
    }

    const selectedVoteIds = Array.from(
        document.querySelectorAll('#voteCheckboxes .observation-vote-cb:checked:not(:disabled)')
    ).map(cb => cb.value);

    const btn = this;
    const origHtml = btn.innerHTML;
    btn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i>Guardando...';
    btn.disabled  = true;

    const formData = new FormData();
    formData.append('voting_table_id',  tableId);
    formData.append('election_type_id', etId);
    formData.append('type',             type);
    formData.append('description',      description);
    formData.append('severity',         severity);
    selectedVoteIds.forEach(id => formData.append('vote_ids[]', id));
    if (evidenceFile) formData.append('evidence', evidenceFile);

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
            bootstrap.Modal.getInstance(document.getElementById('observationModal'))?.hide();
            Swal.fire({
                icon: 'success', title: '✅ Observación creada',
                text: data.message, timer: 2500,
                toast: true, position: 'top-end', showConfirmButton: false,
            });
            setTimeout(() => location.reload(), 1800);
        } else {
            let msg = data.message ?? 'Error desconocido';
            if (data.errors) msg = Object.values(data.errors).flat().join('\n');
            Swal.fire({ icon: 'error', title: '❌ Error', text: msg });
        }
    })
    .catch(err => {
        console.error('saveObservation error:', err);
        Swal.fire({ icon: 'error', title: '❌ Error de red', text: err.message });
    })
    .finally(() => {
        btn.innerHTML = origHtml;
        btn.disabled  = false;
    });
});

// ─── View observations for a table ──────────────────────────────────────────

window.showObservations = function(tableId) {
    Swal.fire({ title: 'Cargando observaciones...', allowOutsideClick: false,
                didOpen: () => Swal.showLoading() });

    fetch(`/observations/table/${tableId}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(observations => {
            Swal.close();

            if (observations.error) throw new Error(observations.error);

            if (!observations.length) {
                return Swal.fire({ icon: 'info', title: 'Sin observaciones',
                                   text: 'Esta mesa no tiene observaciones registradas' });
            }

            const severityColor = { info: 'info', warning: 'warning', error: 'danger', critical: 'dark' };
            const statusColor   = { pending: 'warning', in_review: 'info', resolved: 'success',
                                    rejected: 'danger', escalated: 'primary' };

            let html = '<div class="list-group" style="max-height:420px;overflow-y:auto;">';
            observations.forEach(obs => {
                html += `
                <div class="list-group-item ${obs.status === 'pending' ? 'list-group-item-warning' : ''}">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">
                                <strong>${escHtml(obs.code)}</strong>
                                <span class="badge bg-${severityColor[obs.severity] ?? 'secondary'} ms-1">${obs.severity}</span>
                                <span class="badge bg-${statusColor[obs.status] ?? 'secondary'} ms-1">${obs.status}</span>
                            </h6>
                            <p class="mb-1 small">${escHtml(obs.description)}</p>
                            <small class="text-muted">
                                <i class="ri-user-line me-1"></i>${escHtml(obs.reviewer_name)}
                                (${escHtml(obs.reviewer_role)})
                                <i class="ri-calendar-line ms-2 me-1"></i>${obs.created_at}
                            </small>
                        </div>
                    </div>
                    ${obs.resolved_at ? `
                        <div class="mt-2 p-2 bg-light rounded small">
                            <strong>Resuelto por:</strong> ${escHtml(obs.resolver_name ?? 'N/A')}<br>
                            <strong>Notas:</strong> ${escHtml(obs.resolution_notes ?? 'N/A')}
                        </div>` : ''}
                    ${obs.evidence_url ? `
                        <div class="mt-2">
                            <a href="${obs.evidence_url}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="ri-image-line me-1"></i>Ver evidencia
                            </a>
                        </div>` : ''}
                    ${obs.votes_count > 0 ? `
                        <div class="mt-1">
                            <small class="text-muted">
                                <i class="ri-ball-pen-line me-1"></i>${obs.votes_count} voto(s) observado(s)
                            </small>
                        </div>` : ''}
                </div>`;
            });
            html += '</div>';

            Swal.fire({
                title: 'Observaciones de la Mesa', html, width: '700px',
                confirmButtonText: 'Cerrar', confirmButtonColor: '#0ab39c',
            });
        })
        .catch(err => {
            Swal.close();
            Swal.fire({ icon: 'error', title: '❌ Error',
                        text: 'No se pudieron cargar las observaciones: ' + err.message });
        });
};

// ─── Listeners ────────────────────────────────────────────────────────────────

window.initObservationListeners = function() {
    document.querySelectorAll('.observe-table-general').forEach(btn => {
        btn.addEventListener('click', function() {
            window.observeTable(
                this.dataset.tableId,
                this.dataset.electionTypeId ?? window.electionTypeId
            );
        });
    });

    document.querySelectorAll('.view-observations').forEach(btn => {
        btn.addEventListener('click', function() {
            window.showObservations(this.dataset.tableId);
        });
    });
};

// ─── Util ─────────────────────────────────────────────────────────────────────

function escHtml(str) {
    return String(str ?? '')
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escAttr(str) { return escHtml(str); }
</script>
<?php /**PATH D:\_Mine\sistema_electoral\resources\views/voting-table-votes/scripts/observations-js.blade.php ENDPATH**/ ?>