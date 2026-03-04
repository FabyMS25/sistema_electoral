
<script>
    window.saveTable = function(tableId, close = false) {
        console.log('🔵 Guardando mesa:', tableId, 'Cerrar:', close);

        const tableCard = document.getElementById(`table-${tableId}`);
        if (!tableCard) {
            console.error('❌ Mesa no encontrada:', tableId);
            return;
        }

        // Obtener votantes habilitados
        const expectedVoters = parseInt(tableCard.dataset.expectedVoters) || 0;

        // Recolectar votos - CORREGIDO: Asegurar que tomamos los valores actuales
        const votes = {};
        let totalVotosAlcalde = 0;
        let totalVotosConcejal = 0;

        // IMPORTANTE: Usar querySelectorAll con el ID correcto de la mesa
        document.querySelectorAll(`#table-${tableId} .vote-input`).forEach(input => {
            if (input.dataset.candidate) {
                const candidateId = input.dataset.candidate;
                // 🔴 CORRECCIÓN CRÍTICA: Asegurar que tomamos el valor actual del input
                const value = parseInt(input.value) || 0;

                // Solo incluir votos con valor > 0
                if (value > 0) {
                    votes[candidateId] = value;
                }

                if (input.dataset.category === 'alcalde') {
                    totalVotosAlcalde += value;
                } else if (input.dataset.category === 'concejal') {
                    totalVotosConcejal += value;
                }
            }
        });

        console.log('📊 Votos recolectados (con valores > 0):', votes);
        console.log('📊 Total Alcaldes (votantes):', totalVotosAlcalde);
        console.log('📊 Total Concejales (votantes):', totalVotosConcejal);
        console.log('📊 Votantes habilitados:', expectedVoters);

        // Validar que hay votos para registrar
        if (Object.keys(votes).length === 0) {
            Swal.fire({
                icon: 'warning',
                title: '⚠️ Sin votos',
                text: 'No hay votos para registrar. Ingrese al menos un valor.',
                confirmButtonColor: '#f7b84b'
            });
            return;
        }

        if (totalVotosAlcalde === 0 && totalVotosConcejal === 0) {
            Swal.fire({
                icon: 'warning',
                title: '⚠️ Sin votos',
                text: 'No hay votos registrados en esta mesa',
                confirmButtonColor: '#f7b84b'
            });
            return;
        }

        // Validación: Los votantes deben ser iguales en ambas categorías
        if (totalVotosAlcalde !== totalVotosConcejal) {
            Swal.fire({
                icon: 'error',
                title: '❌ Error de consistencia',
                html: `El número de votantes debe ser el mismo en ambas categorías:<br><br>
                       <strong>Alcaldes:</strong> ${totalVotosAlcalde} votantes<br>
                       <strong>Concejales:</strong> ${totalVotosConcejal} votantes<br><br>
                       <span class="text-muted">Cada votante emite un voto para Alcalde y un voto para Concejal.</span>`,
                confirmButtonColor: '#f06548'
            });
            return;
        }

        // Validar contra votantes habilitados
        if (expectedVoters > 0 && totalVotosAlcalde > expectedVoters) {
            Swal.fire({
                icon: 'error',
                title: '❌ Error de consistencia',
                html: `Los votos registrados (${totalVotosAlcalde}) exceden<br>
                       los votantes habilitados (${expectedVoters})`,
                confirmButtonColor: '#f06548'
            });
            return;
        }

        // Deshabilitar botones
        const saveBtn = tableCard.querySelector('.save-table');
        const closeBtn = tableCard.querySelector('.close-table');

        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> Guardando...';
        }
        if (closeBtn) closeBtn.disabled = true;

        Swal.fire({
            title: 'Guardando votos...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Preparar datos para enviar
        const requestData = {
            voting_table_id: parseInt(tableId),
            election_type_id: window.electionTypeId,
            votes: votes,  // Solo votos con valor > 0
            close: close
        };

        console.log('📤 Enviando datos:', requestData);

        fetch('<?php echo e(route("voting-table-votes.register")); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Accept': 'application/json'
            },
            body: JSON.stringify(requestData)
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw new Error(err.message || 'Error en la respuesta'); });
            }
            return response.json();
        })
        .then(data => {
            Swal.close();

            if (data.success) {
                // Actualizar los totales mostrados
                if (data.totals) {
                    const totalAlcaldeEl = document.getElementById(`total-alcalde-${tableId}`);
                    const totalConcejalEl = document.getElementById(`total-concejal-${tableId}`);
                    const totalEl = document.getElementById(`total-${tableId}`);

                    if (totalAlcaldeEl) totalAlcaldeEl.textContent = data.totals.alcalde || 0;
                    if (totalConcejalEl) totalConcejalEl.textContent = data.totals.concejal || 0;
                    if (totalEl) totalEl.textContent = data.totals.total || 0;
                }

                Swal.fire({
                    icon: 'success',
                    title: '✅ ¡Éxito!',
                    text: data.message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });

                if (data.table_status === 'cerrada') {
                    setTimeout(() => location.reload(), 1500);
                }
            } else {
                throw new Error(data.message || 'Error al guardar los votos');
            }
        })
        .catch(error => {
            Swal.close();
            console.error('❌ Error:', error);

            Swal.fire({
                icon: 'error',
                title: '❌ Error',
                text: error.message,
                confirmButtonColor: '#f06548'
            });
        })
        .finally(() => {
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="ri-save-line"></i>';
            }
            if (closeBtn) closeBtn.disabled = false;
        });
    };

    // También corregir saveAllTables
    window.saveAllTables = function() {
        const tables = document.querySelectorAll('.table-card');
        if (tables.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'Sin mesas',
                text: 'No hay mesas para guardar.'
            });
            return;
        }

        const tablesData = {};
        tables.forEach(table => {
            const tableId = table.dataset.tableId;
            const votes = {};

            document.querySelectorAll(`#table-${tableId} .vote-input`).forEach(input => {
                if (input.dataset.candidate) {
                    const value = parseInt(input.value) || 0;
                    if (value > 0) {  // Solo incluir votos con valor > 0
                        votes[input.dataset.candidate] = value;
                    }
                }
            });

            if (Object.keys(votes).length > 0) {
                tablesData[tableId] = votes;
            }
        });

        if (Object.keys(tablesData).length === 0) {
            Swal.fire({
                icon: 'warning',
                title: '⚠️ Sin datos',
                text: 'No hay votos para guardar en ninguna mesa.'
            });
            return;
        }

        Swal.fire({
            title: 'Guardar todas las mesas',
            text: `¿Guardar cambios en ${Object.keys(tablesData).length} mesas de la página actual?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, guardar todas',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0ab39c'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Guardando...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('<?php echo e(route("voting-table-votes.register-all")); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                    },
                    body: JSON.stringify({
                        election_type_id: window.electionTypeId,
                        tables: tablesData,
                        close_all: false
                    })
                })
                .then(response => response.json())
                .then(data => {
                    Swal.close();

                    let icon = data.success ? 'success' : 'warning';
                    let title = data.success ? '✅ Proceso completado' : '⚠️ Proceso con advertencias';

                    Swal.fire({
                        icon: icon,
                        title: title,
                        html: data.message.replace(/\n/g, '<br>'),
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#0ab39c'
                    }).then(() => {
                        if (data.success) {
                            location.reload();
                        }
                    });
                })
                .catch(error => {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: '❌ Error',
                        text: 'Error al procesar las mesas: ' + error.message
                    });
                });
            }
        });
    };

    window.initVoteListeners = function() {
        console.log('Inicializando listeners de votos...');

        // Listener para cambios en inputs
        document.querySelectorAll('.vote-input').forEach(input => {
            input.addEventListener('input', function() {
                const tableId = this.dataset.table;
                if (window.updateTableTotals) {
                    window.updateTableTotals(tableId);
                }
            });

            input.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    const inputs = document.querySelectorAll(`.vote-input[data-table="${this.dataset.table}"]`);
                    const index = Array.from(inputs).indexOf(this);
                    if (inputs[index + 1]) {
                        inputs[index + 1].focus();
                    }
                }
            });
        });

        // Listener para botones guardar
        document.querySelectorAll('.save-table').forEach(btn => {
            btn.addEventListener('click', function() {
                const tableId = this.dataset.tableId;
                console.log('🟢 Botón guardar clickeado para mesa:', tableId);
                window.saveTable(tableId, false);
            });
        });

        // Listener para botones cerrar
        document.querySelectorAll('.close-table').forEach(btn => {
            btn.addEventListener('click', function() {
                const tableId = this.dataset.tableId;
                Swal.fire({
                    title: '¿Cerrar mesa?',
                    text: 'Una vez cerrada, no se podrán modificar los votos.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f06548',
                    confirmButtonText: 'Sí, cerrar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.saveTable(tableId, true);
                    }
                });
            });
        });

        const saveAllBtn = document.getElementById('saveAllBtn');
        if (saveAllBtn) {
            saveAllBtn.addEventListener('click', window.saveAllTables);
        }

        const closeAllBtn = document.getElementById('closeAllBtn');
        if (closeAllBtn) {
            closeAllBtn.addEventListener('click', window.closeAllTables);
        }
    };
</script>
<?php /**PATH D:\_Mine\corporate\resources\views/voting-table-votes/scripts/votes-table-js.blade.php ENDPATH**/ ?>