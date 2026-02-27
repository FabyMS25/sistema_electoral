{{-- resources/views/voting-table-votes/scripts/votes-js.blade.php --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let pendingTables = new Set();
    let saveTimeouts = {};

    // Actualizar total por mesa
    function updateTableTotal(tableId) {
        const inputs = document.querySelectorAll(`.candidate-vote[data-table="${tableId}"]`);
        let total = 0;
        inputs.forEach(input => {
            total += parseInt(input.value) || 0;
        });
        document.getElementById(`total-${tableId}`).textContent = total;
        return total;
    }

    // Marcar mesa como pendiente de guardar
    function markPending(tableId) {
        pendingTables.add(tableId);
        const tableCard = document.getElementById(`table-${tableId}`);
        if (tableCard) {
            tableCard.style.border = '2px solid #f7b84b';
        }
        
        // Limpiar timeout anterior
        if (saveTimeouts[tableId]) {
            clearTimeout(saveTimeouts[tableId]);
        }
        
        // Auto-guardar después de 3 segundos
        saveTimeouts[tableId] = setTimeout(() => {
            saveTable(tableId);
        }, 3000);
    }

    // Guardar mesa específica
    function saveTable(tableId, close = false) {
        const tableCard = document.getElementById(`table-${tableId}`);
        if (!tableCard) return;

        const inputs = document.querySelectorAll(`.candidate-vote[data-table="${tableId}"]`);
        const votes = {};
        
        inputs.forEach(input => {
            const candidateId = input.dataset.candidate;
            votes[candidateId] = parseInt(input.value) || 0;
        });

        // Mostrar loading
        const saveBtn = tableCard.querySelector('.save-table');
        const originalText = saveBtn ? saveBtn.innerHTML : '';
        if (saveBtn) {
            saveBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i>Guardando...';
            saveBtn.disabled = true;
        }

        // Enviar datos
        fetch('{{ route("voting-table-votes.register") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
                    title: '¡Éxito!',
                    text: data.message,
                    icon: 'success',
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
                title: 'Error',
                text: error.message,
                icon: 'error',
                confirmButtonText: 'Entendido'
            });
        })
        .finally(() => {
            if (saveBtn) {
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            }
        });
    }

    // Event listeners para inputs de votos
    document.querySelectorAll('.candidate-vote').forEach(input => {
        input.addEventListener('input', function() {
            const tableId = this.dataset.table;
            updateTableTotal(tableId);
            markPending(tableId);
        });
    });

    // Guardar mesa individual
    document.querySelectorAll('.save-table').forEach(btn => {
        btn.addEventListener('click', function() {
            const tableId = this.dataset.tableId;
            saveTable(tableId, false);
        });
    });

    // Cerrar mesa individual
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

    // Guardar todas las mesas
    document.getElementById('saveAllBtn')?.addEventListener('click', function() {
        const tables = Array.from(pendingTables);
        if (tables.length === 0) {
            Swal.fire({
                title: 'Sin cambios',
                text: 'No hay mesas con cambios pendientes.',
                icon: 'info',
                confirmButtonText: 'Entendido'
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

    // Cerrar todas las mesas
    document.getElementById('closeAllBtn')?.addEventListener('click', function() {
        const tables = document.querySelectorAll('.table-card:not(.cerrado)');
        
        if (tables.length === 0) {
            Swal.fire({
                title: 'Sin mesas',
                text: 'No hay mesas abiertas para cerrar.',
                icon: 'info',
                confirmButtonText: 'Entendido'
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

    // Filtros con auto-submit
    document.getElementById('institutionFilter')?.addEventListener('change', function() {
        this.form.submit();
    });
    
    document.getElementById('electionTypeFilter')?.addEventListener('change', function() {
        this.form.submit();
    });
});
</script>