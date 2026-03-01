{{-- resources/views/voting-table-votes/partials/quick-actions.blade.php --}}
<div class="quick-actions mt-4">
    <div class="card bg-light">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h6 class="mb-0">
                        <i class="ri-flashlight-line me-1 text-warning"></i>
                        Acciones rápidas
                    </h6>
                    <small class="text-muted">
                        {{ $votingTables->count() }} mesas visibles | 
                        <span id="pendingCount">0</span> con cambios pendientes
                    </small>
                </div>
                <div class="col-md-6 text-end">
                    <div class="btn-group" role="group">
                        <button class="btn btn-success" id="quickSaveAllBtn" title="Guardar todas las mesas">
                            <i class="ri-save-line me-1"></i>
                            Guardar todo
                        </button>
                        <button class="btn btn-warning" id="quickCloseAllBtn" title="Cerrar todas las mesas">
                            <i class="ri-lock-line me-1"></i>
                            Cerrar todo
                        </button>
                        <button class="btn btn-info" id="quickRefreshBtn" title="Actualizar vista">
                            <i class="ri-refresh-line me-1"></i>
                            Actualizar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Atajos de teclado -->
            <div class="row mt-2">
                <div class="col-12">
                    <small class="text-muted">
                        <i class="ri-keyboard-line me-1"></i>
                        Atajos: 
                        <span class="badge bg-light text-dark me-2">Ctrl + S</span> Guardar todo
                        <span class="badge bg-light text-dark me-2 ms-2">Ctrl + Enter</span> Guardar mesa actual
                        <span class="badge bg-light text-dark me-2 ms-2">Esc</span> Cancelar
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.quick-actions {
    position: sticky;
    bottom: 20px;
    z-index: 1000;
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        transform: translateY(100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.quick-actions .card {
    box-shadow: 0 -4px 12px rgba(0,0,0,0.1);
    border: none;
}

.quick-actions .btn-group .btn {
    padding: 0.5rem 1rem;
}

@media (max-width: 768px) {
    .quick-actions .btn-group {
        display: flex;
        width: 100%;
    }
    .quick-actions .btn-group .btn {
        flex: 1;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Actualizar contador de pendientes
    function updatePendingCount() {
        const pendingCount = document.getElementById('pendingCount');
        if (pendingCount && window.pendingTables) {
            pendingCount.textContent = window.pendingTables.size;
        }
    }

    // Escuchar cambios en mesas pendientes
    setInterval(updatePendingCount, 500);

    // Atajos de teclado
    document.addEventListener('keydown', function(e) {
        // Ctrl + S: Guardar todo
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            document.getElementById('quickSaveAllBtn')?.click();
        }
        
        // Ctrl + Enter: Guardar mesa actual (la que está en foco)
        if (e.ctrlKey && e.key === 'Enter') {
            e.preventDefault();
            const activeInput = document.activeElement;
            if (activeInput && activeInput.classList.contains('candidate-vote')) {
                const tableId = activeInput.dataset.table;
                const saveBtn = document.querySelector(`.save-table[data-table-id="${tableId}"]`);
                if (saveBtn) saveBtn.click();
            }
        }
    });

    // Refresh button
    document.getElementById('quickRefreshBtn')?.addEventListener('click', function() {
        location.reload();
    });

    // Quick save all
    document.getElementById('quickSaveAllBtn')?.addEventListener('click', function() {
        document.getElementById('saveAllBtn')?.click();
    });

    // Quick close all
    document.getElementById('quickCloseAllBtn')?.addEventListener('click', function() {
        document.getElementById('closeAllBtn')?.click();
    });
});
</script>