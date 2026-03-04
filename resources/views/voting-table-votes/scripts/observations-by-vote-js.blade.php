{{-- resources/views/voting-table-votes/scripts/observations-by-vote-js.blade.php --}}
<script>
// ===== FUNCIONES PARA OBSERVACIONES POR VOTO =====

function createObservationWithSelected(tableId) {
    // Obtener checkboxes seleccionados
    const checkboxes = document.querySelectorAll(`#table-${tableId} .observe-checkbox:checked`);

    if (checkboxes.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Sin selección',
            text: 'Debe seleccionar al menos un voto para observar'
        });
        return;
    }

    // Preparar lista de candidatos seleccionados
    const selected = Array.from(checkboxes).map(cb => ({
        candidateId: cb.dataset.candidate,
        candidateName: cb.dataset.candidateName,
        category: cb.dataset.category
    }));

    const candidatesList = selected.map(s => `• ${s.candidateName} (${s.category})`).join('<br>');

    Swal.fire({
        title: 'Crear Observación',
        html: `
            <div class="text-start mb-3">
                <label class="form-label fw-bold">Tipo de Observación</label>
                <select id="swal-observation-type" class="form-select">
                    <option value="votos_inconsistentes">Votos Inconsistentes</option>
                    <option value="error_datos">Error en Datos</option>
                    <option value="otro">Otro</option>
                </select>
            </div>
            <div class="text-start mb-3">
                <label class="form-label fw-bold">Descripción</label>
                <textarea id="swal-observation-desc" class="form-control" rows="3" placeholder="Describa la observación..."></textarea>
            </div>
            <div class="text-start mb-3">
                <label class="form-label fw-bold">Votos a observar:</label>
                <div class="border rounded p-2 bg-light" style="max-height: 150px; overflow-y: auto;">
                    ${candidatesList}
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Crear Observación',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const type = document.getElementById('swal-observation-type').value;
            const description = document.getElementById('swal-observation-desc').value;

            if (!description) {
                Swal.showValidationMessage('La descripción es requerida');
                return false;
            }
            return { type, description };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('voting_table_id', tableId);
            formData.append('type', result.value.type);
            formData.append('description', result.value.description);
            formData.append('severity', 'warning');

            selected.forEach(s => {
                formData.append('vote_ids[]', s.candidateId);
            });

            fetch('/observations', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '✅ Observación creada',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    setTimeout(() => location.reload(), 1500);
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: '❌ Error',
                    text: error.message
                });
            });
        }
    });
}

// Inicializar botones de observación con seleccionados
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.create-observation-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            createObservationWithSelected(this.dataset.tableId);
        });
    });
});
</script>
