{{-- resources/views/voting-table-votes/scripts/votes-table-js.blade.php --}}
<script>
    window.saveTable = function(tableId, close = false) {
        console.log('🔵 Guardando mesa:', tableId, 'Cerrar:', close);

        const tableCard = document.getElementById(`table-${tableId}`);
        if (!tableCard) {
            console.error('❌ Mesa no encontrada:', tableId);
            return;
        }

        const expectedVoters = parseInt(tableCard.dataset.expectedVoters) || 0;
        const votes = {};
        const categoryTotals = {};

        // Recolectar votos por categoría
        document.querySelectorAll(`#table-${tableId} .vote-input`).forEach(input => {
            const candidateId = input.dataset.candidate;
            const value = parseInt(input.value) || 0;
            const category = input.dataset.category;

            if (value > 0) {
                votes[candidateId] = value;
            }

            if (!categoryTotals[category]) {
                categoryTotals[category] = 0;
            }
            categoryTotals[category] += value;
        });

        console.log('📊 Votos recolectados:', votes);
        console.log('📊 Totales por categoría:', categoryTotals);

        // Validar que hay votos
        if (Object.keys(votes).length === 0) {
            Swal.fire({
                icon: 'warning',
                title: '⚠️ Sin votos',
                text: 'No hay votos para registrar. Ingrese al menos un valor.',
                confirmButtonColor: '#f7b84b'
            });
            return;
        }

        // Validación DINÁMICA: Todas las categorías deben tener el mismo total
        const totals = Object.values(categoryTotals);
        if (totals.length > 1 && new Set(totals).size > 1) {
            let errorHtml = 'El número de votantes debe ser el mismo en todas las categorías:<br><br>';
            Object.entries(categoryTotals).forEach(([cat, val]) => {
                errorHtml += `<strong>${cat}:</strong> ${val} votantes<br>`;
            });

            Swal.fire({
                icon: 'error',
                title: '❌ Error de consistencia',
                html: errorHtml,
                confirmButtonColor: '#f06548'
            });
            return;
        }

        const totalVoters = totals[0] || 0;

        // Validar contra votantes habilitados
        if (expectedVoters > 0 && totalVoters > expectedVoters) {
            Swal.fire({
                icon: 'error',
                title: '❌ Error de consistencia',
                html: `Los votos registrados (${totalVoters}) exceden<br>
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

        const requestData = {
            voting_table_id: parseInt(tableId),
            election_type_id: window.electionTypeId,
            votes: votes,
            close: close
        };

        console.log('📤 Enviando datos:', requestData);

        fetch('{{ route("voting-table-votes.register") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(requestData)
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || 'Error en la respuesta');
                });
            }
            return response.json();
        })
        .then(data => {
            Swal.close();

            if (data.success) {
                // Actualizar totales por categoría si vienen del servidor
                if (data.category_totals) {
                    Object.entries(data.category_totals).forEach(([category, total]) => {
                        const el = document.getElementById(`total-${category}-${tableId}`);
                        if (el) {
                            el.textContent = total;
                        }
                    });
                }

                // Actualizar total general
                if (data.total_voters) {
                    const totalEl = document.getElementById(`total-${tableId}`);
                    if (totalEl) totalEl.textContent = data.total_voters;
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
                const value = parseInt(input.value) || 0;
                if (value > 0) {
                    votes[input.dataset.candidate] = value;
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

                fetch('{{ route("voting-table-votes.register-all") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
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

    window.closeAllTables = function() {
        const tables = document.querySelectorAll('.table-card:not([data-status="cerrada"])');

        if (tables.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'Sin mesas',
                text: 'No hay mesas abiertas para cerrar.'
            });
            return;
        }

        const tablesData = {};
        tables.forEach(table => {
            const tableId = table.dataset.tableId;
            const votes = {};

            document.querySelectorAll(`#table-${tableId} .vote-input`).forEach(input => {
                const value = parseInt(input.value) || 0;
                if (value > 0) {
                    votes[input.dataset.candidate] = value;
                }
            });

            tablesData[tableId] = votes;
        });

        Swal.fire({
            title: '⚠️ Cerrar todas las mesas',
            html: `¿Cerrar <strong>${tables.length}</strong> mesas?<br><br>Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f06548',
            confirmButtonText: 'Sí, cerrar todas',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Cerrando mesas...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch('{{ route("voting-table-votes.register-all") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        election_type_id: window.electionTypeId,
                        tables: tablesData,
                        close_all: true
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
</script>
