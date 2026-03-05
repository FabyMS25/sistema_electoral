
<script>
// ===== FUNCIONES PARA OBSERVACIONES =====

window.observeTable = function(tableId) {
    window.currentObservationTable = tableId;
    document.getElementById('observationTableId').value = tableId;
    document.getElementById('observationType').value = '';
    document.getElementById('observationDescription').value = '';
    document.getElementById('observationSeverity').value = 'warning';
    document.getElementById('observationEvidence').value = '';

    loadTableVotesForObservation(tableId);

    const modal = new bootstrap.Modal(document.getElementById('observationModal'));
    modal.show();
};

function loadTableVotesForObservation(tableId) {
    const container = document.getElementById('voteCheckboxes');
    if (!container) return;

    container.innerHTML = '<div class="text-center text-muted py-3"><i class="ri-loader-4-line ri-spin me-1"></i>Cargando votos...</div>';

    fetch(`/voting-table-votes/${tableId}/votes`)
        .then(response => response.json())
        .then(votes => {
            if (!votes || votes.length === 0) {
                container.innerHTML = '<p class="text-muted text-center py-3">No hay votos registrados</p>';
                return;
            }

            let html = '<div class="row">';
            votes.forEach(vote => {
                // 🔴 CORRECCIÓN: Usar 'observed' directamente o VOTE_STATUS_OBSERVED
                const isObserved = vote.vote_status === 'observed';
                const disabledAttr = isObserved ? 'disabled' : '';
                const checkedAttr = isObserved ? 'checked' : '';

                html += `
                    <div class="col-md-6 mb-2">
                        <div class="form-check ${isObserved ? 'text-warning' : ''}">
                            <input class="form-check-input" type="checkbox"
                                   name="vote_ids[]" value="${vote.candidate_id}"
                                   id="vote_${vote.candidate_id}"
                                   ${disabledAttr} ${checkedAttr}>
                            <label class="form-check-label" for="vote_${vote.candidate_id}">
                                <strong>${vote.candidate_name}</strong>
                                ${vote.candidate_type === 'null_votes' ? '<span class="badge bg-secondary ms-1">Nulo</span>' : ''}
                                ${vote.candidate_type === 'blank_votes' ? '<span class="badge bg-light text-dark ms-1">Blanco</span>' : ''}
                                <br>
                                <small>${vote.candidate_party} - ${vote.quantity} votos</small>
                                ${isObserved ? '<br><small class="text-warning"><i class="ri-alert-line"></i> Ya observado</small>' : ''}
                            </label>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            container.innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading votes:', error);
            container.innerHTML = '<p class="text-danger text-center py-3">Error al cargar votos</p>';
        });
}

// Botón guardar observación
document.getElementById('saveObservationBtn')?.addEventListener('click', function() {
    if (!window.currentObservationTable) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se ha seleccionado una mesa'
        });
        return;
    }

    const type = document.getElementById('observationType').value;
    const description = document.getElementById('observationDescription').value.trim();
    const severity = document.getElementById('observationSeverity').value;

    if (!type) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'El tipo de observación es requerido'
        });
        return;
    }

    if (!description) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'La descripción es requerida'
        });
        return;
    }

    const selectedVotes = [];
    document.querySelectorAll('#voteCheckboxes input[type="checkbox"]:checked').forEach(cb => {
        selectedVotes.push(cb.value);
    });

    submitObservation(window.currentObservationTable, type, description, severity, selectedVotes);
});

function submitObservation(tableId, type, description, severity, selectedVotes) {
    const saveBtn = document.getElementById('saveObservationBtn');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i>Guardando...';
    saveBtn.disabled = true;

    const formData = new FormData();
    formData.append('voting_table_id', tableId);
    formData.append('type', type);
    formData.append('description', description);
    formData.append('severity', severity);

    if (selectedVotes.length > 0) {
        selectedVotes.forEach(voteId => {
            formData.append('vote_ids[]', voteId);
        });
    }

    const evidence = document.getElementById('observationEvidence').files[0];
    if (evidence) {
        formData.append('evidence', evidence);
    }

    fetch('/observations', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('observationModal'));
            modal.hide();

            Swal.fire({
                icon: 'success',
                title: '✅ Observación creada',
                text: data.message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });

            setTimeout(() => location.reload(), 1500);
        } else {
            if (data.errors) {
                let errorMessages = Object.values(data.errors).flat().join('\n');
                throw new Error(errorMessages);
            } else {
                throw new Error(data.message || 'Error al crear la observación');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: '❌ Error',
            text: error.message
        });
    })
    .finally(() => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

window.showObservations = function(tableId) {
    Swal.fire({
        title: 'Cargando observaciones...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch(`/observations/table/${tableId}`)
        .then(response => response.json())
        .then(observations => {
            Swal.close();

            if (observations.error) {
                throw new Error(observations.error);
            }

            if (observations.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Sin observaciones',
                    text: 'Esta mesa no tiene observaciones'
                });
                return;
            }

            let html = '<div class="list-group" style="max-height: 400px; overflow-y: auto;">';
            observations.forEach(obs => {
                const severityClass = {
                    'info': 'info',
                    'warning': 'warning',
                    'error': 'danger',
                    'critical': 'dark'
                }[obs.severity] || 'secondary';

                const statusClass = {
                    'pending': 'warning',
                    'in_review': 'info',
                    'resolved': 'success',
                    'rejected': 'danger',
                    'escalated': 'primary'
                }[obs.status] || 'secondary';

                html += `
                    <div class="list-group-item ${obs.status === 'pending' ? 'list-group-item-warning' : ''}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">
                                    ${obs.code}
                                    <span class="badge bg-${severityClass} ms-1">${obs.severity}</span>
                                    <span class="badge bg-${statusClass} ms-1">${obs.status}</span>
                                </h6>
                                <p class="mb-1">${obs.description}</p>
                                <small class="text-muted">
                                    <i class="ri-user-line me-1"></i> ${obs.reviewer_name} (${obs.reviewer_role})
                                    <i class="ri-calendar-line ms-2 me-1"></i> ${obs.created_at}
                                </small>
                            </div>
                        </div>
                        ${obs.resolved_at ? `
                            <div class="mt-2 p-2 bg-light rounded">
                                <small>
                                    <strong>Resuelto por:</strong> ${obs.resolver_name || 'N/A'}<br>
                                    <strong>Notas:</strong> ${obs.resolution_notes || 'N/A'}
                                </small>
                            </div>
                        ` : ''}
                        ${obs.evidence_url ? `
                            <div class="mt-2">
                                <a href="${obs.evidence_url}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="ri-image-line me-1"></i>Ver evidencia
                                </a>
                            </div>
                        ` : ''}
                        ${obs.votes_count > 0 ? `
                            <div class="mt-1">
                                <small class="text-muted">
                                    <i class="ri-ball-pen-line me-1"></i>${obs.votes_count} voto(s) observado(s)
                                </small>
                            </div>
                        ` : ''}
                    </div>
                `;
            });
            html += '</div>';

            Swal.fire({
                title: 'Observaciones de la Mesa',
                html: html,
                icon: 'info',
                width: '700px',
                confirmButtonText: 'Cerrar',
                confirmButtonColor: '#0ab39c'
            });
        })
        .catch(error => {
            Swal.close();
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: '❌ Error',
                text: 'Error al cargar observaciones: ' + error.message
            });
        });
};

// Inicializar listeners de observaciones
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.observe-table-general').forEach(btn => {
        btn.addEventListener('click', function() {
            window.observeTable(this.dataset.tableId);
        });
    });

    document.querySelectorAll('.view-observations').forEach(btn => {
        btn.addEventListener('click', function() {
            window.showObservations(this.dataset.tableId);
        });
    });
});
</script>
<?php /**PATH D:\_Mine\corporate\resources\views/voting-table-votes/scripts/observations-js.blade.php ENDPATH**/ ?>