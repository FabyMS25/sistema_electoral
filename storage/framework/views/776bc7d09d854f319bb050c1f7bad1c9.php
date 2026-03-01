
<script>
// ===== VARIABLES GLOBALES =====
let pendingTables = new Set();
let saveTimeouts = {};
let currentObservationTable = null;  // <-- SOLO UNA VEZ
let currentActaTable = null;         // <-- SOLO UNA VEZ
let currentValidationTable = null;   // <-- SOLO UNA VEZ

// ===== FUNCIONES DE UTILIDAD =====

function updateTableTotal(tableId) {
    const inputs = document.querySelectorAll(`.candidate-vote[data-table="${tableId}"]`);
    let total = 0;
    inputs.forEach(input => {
        total += parseInt(input.value) || 0;
    });
    document.getElementById(`total-${tableId}`).textContent = total;
    return total;
}

function markPending(tableId) {
    if (!window.userPermissions?.register) return;

    pendingTables.add(tableId);
    const tableCard = document.getElementById(`table-${tableId}`);
    if (tableCard) {
        tableCard.style.border = '2px solid #f7b84b';
    }

    if (saveTimeouts[tableId]) {
        clearTimeout(saveTimeouts[tableId]);
    }

    saveTimeouts[tableId] = setTimeout(() => {
        saveTable(tableId);
    }, 3000);
}

// ===== REGISTRO DE VOTOS =====

function saveTable(tableId, close = false) {
    const tableCard = document.getElementById(`table-${tableId}`);
    if (!tableCard) return;

    const inputs = document.querySelectorAll(`.candidate-vote[data-table="${tableId}"]`);
    const votes = {};

    inputs.forEach(input => {
        const candidateId = input.dataset.candidate;
        votes[candidateId] = parseInt(input.value) || 0;
    });

    const saveBtn = tableCard.querySelector('.save-table');
    const originalText = saveBtn ? saveBtn.innerHTML : '';
    if (saveBtn) {
        saveBtn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i>';
        saveBtn.disabled = true;
    }

    fetch('<?php echo e(route("voting-table-votes.register")); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        },
        body: JSON.stringify({
            voting_table_id: tableId,
            election_type_id: window.electionTypeId,
            votes: votes,
            close: close
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            pendingTables.delete(tableId);
            if (tableCard) tableCard.style.border = '';

            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: data.message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });

            if (data.table_status === 'cerrado') {
                location.reload();
            }
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message
        });
    })
    .finally(() => {
        if (saveBtn) {
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        }
    });
}

// ===== OBSERVACIONES =====

document.querySelectorAll('.observe-table').forEach(btn => {
    btn.addEventListener('click', function() {
        currentObservationTable = this.dataset.tableId;
        document.getElementById('observationTableId').value = currentObservationTable;

        // Limpiar campos del modal
        document.getElementById('observationType').value = '';
        document.getElementById('observationDescription').value = '';
        document.getElementById('observationSeverity').value = 'warning';
        document.getElementById('observationEvidence').value = '';

        const modal = new bootstrap.Modal(document.getElementById('observationModal'));
        modal.show();
    });
});

document.getElementById('saveObservationBtn')?.addEventListener('click', function() {
    if (!currentObservationTable) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se ha seleccionado una mesa'
        });
        return;
    }

    // Validar campos requeridos
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

    // Mostrar indicador de carga
    const saveBtn = this;
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i>Guardando...';
    saveBtn.disabled = true;

    const formData = new FormData();
    formData.append('voting_table_id', currentObservationTable);
    formData.append('type', type);
    formData.append('description', description);
    formData.append('severity', severity);

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
                title: 'Éxito',
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
            title: 'Error',
            text: error.message
        });
    })
    .finally(() => {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
});

// ===== ACTAS =====

document.querySelectorAll('.upload-acta').forEach(btn => {
    btn.addEventListener('click', function() {
        currentActaTable = this.dataset.tableId;
        document.getElementById('actaTableId').value = currentActaTable;

        // Limpiar campos del modal
        document.getElementById('actaNumber').value = '';
        document.getElementById('actaPhoto').value = '';
        document.getElementById('actaPdf').value = '';
        document.getElementById('hasPhysicalActa').checked = true;

        const modal = new bootstrap.Modal(document.getElementById('uploadActaModal'));
        modal.show();
    });
});

document.getElementById('uploadActaBtn')?.addEventListener('click', function() {
    if (!currentActaTable) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se ha seleccionado una mesa'
        });
        return;
    }

    // Validar campos requeridos
    const actaNumber = document.getElementById('actaNumber').value.trim();
    const actaPhoto = document.getElementById('actaPhoto').files[0];

    if (!actaNumber) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'El número de acta es requerido'
        });
        return;
    }

    if (!actaPhoto) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Debe seleccionar una foto del acta'
        });
        return;
    }

    const uploadBtn = this;
    const originalText = uploadBtn.innerHTML;
    uploadBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i>Subiendo...';
    uploadBtn.disabled = true;

    const formData = new FormData();
    formData.append('voting_table_id', currentActaTable);
    formData.append('acta_number', actaNumber);
    formData.append('photo', actaPhoto);

    const pdfFile = document.getElementById('actaPdf').files[0];
    if (pdfFile) {
        formData.append('pdf', pdfFile);
    }

    const hasPhysical = document.getElementById('hasPhysicalActa').checked;
    formData.append('has_physical', hasPhysical ? 'on' : 'off');

    fetch('/actas/upload', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('uploadActaModal'));
            modal.hide();

            Swal.fire({
                icon: 'success',
                title: 'Éxito',
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
                throw new Error(data.message || 'Error al subir el acta');
            }
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message
        });
    })
    .finally(() => {
        uploadBtn.innerHTML = originalText;
        uploadBtn.disabled = false;
    });
});

// ===== VALIDACIÓN =====

document.querySelectorAll('.validate-table, .review-table').forEach(btn => {
    btn.addEventListener('click', function() {
        currentValidationTable = this.dataset.tableId;
        document.getElementById('validationTableId').value = currentValidationTable;

        const modal = new bootstrap.Modal(document.getElementById('validationModal'));
        modal.show();
    });
});

document.getElementById('confirmValidationBtn')?.addEventListener('click', function() {
    if (!currentValidationTable) return;

    const action = document.getElementById('validationAction').value;
    const notes = document.getElementById('validationNotes').value;

    fetch(`/api/tables/${currentValidationTable}/validate`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        },
        body: JSON.stringify({
            action: action,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('validationModal'));
            modal.hide();

            Swal.fire({
                icon: 'success',
                title: 'Validación completada',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(data.message);
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message
        });
    });
});

// ===== EVENTOS DE INPUT =====

document.querySelectorAll('.candidate-vote').forEach(input => {
    input.addEventListener('input', function() {
        const tableId = this.dataset.table;
        updateTableTotal(tableId);
        markPending(tableId);
    });
});

// ===== BOTONES DE ACCIÓN =====

document.querySelectorAll('.save-table').forEach(btn => {
    btn.addEventListener('click', function() {
        const tableId = this.dataset.tableId;
        saveTable(tableId, false);
    });
});

document.querySelectorAll('.close-table').forEach(btn => {
    btn.addEventListener('click', function() {
        const tableId = this.dataset.tableId;

        Swal.fire({
            title: '¿Cerrar mesa?',
            text: 'Una vez cerrada, no se podrán modificar los votos.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, cerrar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                saveTable(tableId, true);
            }
        });
    });
});

document.getElementById('saveAllBtn')?.addEventListener('click', function() {
    const tables = Array.from(pendingTables);
    if (tables.length === 0) {
        Swal.fire({
            icon: 'info',
            title: 'Sin cambios',
            text: 'No hay mesas con cambios pendientes.'
        });
        return;
    }

    Swal.fire({
        title: 'Guardar todas',
        text: `¿Guardar cambios en ${tables.length} mesas?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            tables.forEach(tableId => saveTable(tableId, false));
        }
    });
});

document.getElementById('closeAllBtn')?.addEventListener('click', function() {
    const tables = document.querySelectorAll('.table-card:not(.cerrado)');

    if (tables.length === 0) {
        Swal.fire({
            icon: 'info',
            title: 'Sin mesas',
            text: 'No hay mesas abiertas para cerrar.'
        });
        return;
    }

    Swal.fire({
        title: 'Cerrar todas las mesas',
        text: `¿Cerrar ${tables.length} mesas? Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, cerrar todas',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            tables.forEach(table => {
                const tableId = table.id.replace('table-', '');
                saveTable(tableId, true);
            });
        }
    });
});

// ===== FILTROS =====

document.getElementById('institutionFilter')?.addEventListener('change', function() {
    this.form.submit();
});

document.getElementById('electionTypeFilter')?.addEventListener('change', function() {
    this.form.submit();
});

// ===== VER OBSERVACIONES =====

window.showObservations = function(tableId) {
    fetch(`/api/tables/${tableId}/observations`)
        .then(response => response.json())
        .then(observations => {
            let html = '<ul class="list-group">';
            observations.forEach(obs => {
                html += `
                    <li class="list-group-item">
                        <strong>${obs.type}:</strong> ${obs.description}
                        <br>
                        <small class="text-muted">${obs.created_at} - ${obs.reviewer_name}</small>
                    </li>
                `;
            });
            html += '</ul>';

            Swal.fire({
                title: 'Observaciones',
                html: html,
                icon: 'info',
                width: '600px'
            });
        });
};

</script>
<?php /**PATH D:\_Mine\corporate\resources\views/voting-table-votes/scripts/votes-js.blade.php ENDPATH**/ ?>