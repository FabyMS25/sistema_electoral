{{-- resources/views/voting-table-votes/partials/quick-actions.blade.php --}}
<div class="quick-actions">
    <div class="card border-0 shadow">
        <div class="card-body py-2 px-3">
            <div class="row align-items-center g-2">
                <div class="col-md-5 col-lg-6">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <span class="text-muted small">
                            <i class="ri-table-line me-1"></i>
                            <strong id="qa-visible-count">{{ $votingTables->count() }}</strong>
                            mesa{{ $votingTables->count() !== 1 ? 's' : '' }} visibles
                        </span>
                        <span class="text-muted small" id="qa-pending-indicator" style="display:none;">
                            <i class="ri-pencil-line me-1 text-warning"></i>
                            <strong id="qa-pending-count" class="text-warning">0</strong>
                            con cambios
                        </span>
                        @if($totals['expected'] > 0)
                        <span class="text-muted small">
                            <i class="ri-user-line me-1"></i>
                            {{ number_format($totals['total']) }} /
                            {{ number_format($totals['expected']) }}
                            habilitados
                            <span class="badge bg-{{ $totals['participation'] >= 75 ? 'success' : ($totals['participation'] >= 50 ? 'warning text-dark' : 'secondary') }} ms-1">
                                {{ $totals['participation'] }}%
                            </span>
                        </span>
                        @endif
                    </div>
                </div>
                <div class="col-md-7 col-lg-6 d-flex justify-content-end align-items-center gap-2 flex-wrap">

                    {{-- Guardar todo --}}
                    @if($permissions['can_register'] ?? false)
                    <button class="btn btn-success btn-sm" id="saveAllBtn"
                            title="Guardar todas las mesas visibles (Ctrl+S)">
                        <i class="ri-save-line me-1"></i>
                        <span class="d-none d-md-inline">Guardar todo</span>
                    </button>
                    @endif

                    {{-- Validar todo (votacion → en_escrutinio) --}}
                    @if($permissions['can_validate'] ?? false)
                    <button class="btn btn-info text-white btn-sm" id="validateAllBtn"
                            title="Validar todas las mesas en votación">
                        <i class="ri-checkbox-circle-line me-1"></i>
                        <span class="d-none d-md-inline">Validar todo</span>
                    </button>
                    @endif

                    {{-- Escrutar todo (en_escrutinio → escrutada) --}}
                    @if($permissions['can_validate'] ?? false)
                    <button class="btn btn-success btn-sm" id="escrutarAllBtn"
                            title="Escrutar todas las mesas en escrutinio">
                        <i class="ri-check-double-line me-1"></i>
                        <span class="d-none d-md-inline">Escrutar todo</span>
                    </button>
                    @endif

                    <button class="btn btn-outline-secondary btn-sm" id="qaRefreshBtn"
                            title="Recargar página (F5)">
                        <i class="ri-refresh-line"></i>
                    </button>

                    <button class="btn btn-outline-secondary btn-sm" type="button"
                            data-bs-toggle="popover" data-bs-trigger="focus"
                            data-bs-placement="top" data-bs-html="true"
                            data-bs-title="Atajos de teclado"
                            data-bs-content="
                                <table class='table table-sm table-borderless mb-0 small'>
                                  <tr><td><kbd>Ctrl+S</kbd></td><td>Guardar todo</td></tr>
                                  <tr><td><kbd>Ctrl+V</kbd></td><td>Validar todo</td></tr>
                                  <tr><td><kbd>Ctrl+Enter</kbd></td><td>Guardar mesa en foco</td></tr>
                                  <tr><td><kbd>F5</kbd></td><td>Actualizar</td></tr>
                                  <tr><td><kbd>Esc</kbd></td><td>Deseleccionar campo</td></tr>
                                </table>">
                        <i class="ri-keyboard-line"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.quick-actions {
    position: sticky;
    bottom: 16px;
    z-index: 900;
}
.quick-actions .card {
    backdrop-filter: blur(8px);
    background-color: rgba(255,255,255,0.96) !important;
    border-top: 2px solid #e2e8f0 !important;
}
@media (max-width: 576px) {
    .quick-actions { bottom: 8px; }
    .quick-actions .btn { font-size: 0.78rem; }
}
</style>

<script>
(function () {
    window.pendingTables = window.pendingTables ?? new Set();

    function updatePendingDisplay() {
        const count     = window.pendingTables.size;
        const indicator = document.getElementById('qa-pending-indicator');
        const badge     = document.getElementById('qa-pending-count');
        if (!indicator || !badge) return;
        badge.textContent        = count;
        indicator.style.display  = count > 0 ? '' : 'none';
    }

    // Track unsaved changes
    document.querySelectorAll('.vote-input, .blank-votes-input, .null-votes-input').forEach(input => {
        input.addEventListener('input', function () {
            const tableId = this.dataset.table;
            if (tableId) {
                window.pendingTables.add(tableId);
                updatePendingDisplay();
            }
        });
    });

    document.addEventListener('tableSaved', function (e) {
        if (e.detail?.tableId) {
            window.pendingTables.delete(String(e.detail.tableId));
            updatePendingDisplay();
        }
    });

    // ── Guardar todo ────────────────────────────────────────────────────────
    document.getElementById('saveAllBtn')?.addEventListener('click', function () {
        const buttons = document.querySelectorAll('.save-table');
        if (buttons.length === 0) {
            Swal.fire({ icon: 'info', title: 'Sin mesas editables',
                        text: 'No hay mesas en estado de votación en esta vista.' });
            return;
        }
        Swal.fire({
            title:             `¿Guardar ${buttons.length} mesa${buttons.length !== 1 ? 's' : ''}?`,
            text:              'Se guardarán todas las mesas visibles con sus votos actuales.',
            icon:              'question',
            showCancelButton:  true,
            confirmButtonText: 'Sí, guardar todo',
            cancelButtonText:  'Cancelar',
            confirmButtonColor: '#0ab39c',
        }).then(result => {
            if (!result.isConfirmed) return;
            buttons.forEach(btn => btn.click());
        });
    });

    // ── Validar todo (votacion → en_escrutinio) ──────────────────────────
    document.getElementById('validateAllBtn')?.addEventListener('click', function () {
        const buttons = document.querySelectorAll('.validate-table[data-action="validate"]');
        if (buttons.length === 0) {
            Swal.fire({ icon: 'info', title: 'Sin mesas para validar',
                        text: 'No hay mesas en estado Votación listas para validar.' });
            return;
        }
        Swal.fire({
            title:             `¿Validar ${buttons.length} mesa${buttons.length !== 1 ? 's' : ''}?`,
            text:              'Las mesas pasarán a En Escrutinio. Solo se validarán las que no tengan observaciones pendientes.',
            icon:              'question',
            showCancelButton:  true,
            confirmButtonText: 'Sí, validar todo',
            cancelButtonText:  'Cancelar',
            confirmButtonColor: '#17a2b8',
        }).then(result => {
            if (!result.isConfirmed) return;
            // Fire sequentially with a small delay to avoid overwhelming the server
            buttons.forEach((btn, i) => {
                setTimeout(() => btn.click(), i * 300);
            });
        });
    });

    // ── Escrutar todo (en_escrutinio → escrutada) ────────────────────────
    document.getElementById('escrutarAllBtn')?.addEventListener('click', function () {
        const buttons = document.querySelectorAll('.validate-table[data-action="escrutar"]');
        if (buttons.length === 0) {
            Swal.fire({ icon: 'info', title: 'Sin mesas para escrutar',
                        text: 'No hay mesas en estado En Escrutinio en esta vista.' });
            return;
        }
        Swal.fire({
            title:             `¿Escrutar ${buttons.length} mesa${buttons.length !== 1 ? 's' : ''}?`,
            text:              'Las mesas quedarán como Escrutadas. Esta acción es definitiva.',
            icon:              'warning',
            showCancelButton:  true,
            confirmButtonText: 'Sí, escrutar todo',
            cancelButtonText:  'Cancelar',
            confirmButtonColor: '#0ab39c',
        }).then(result => {
            if (!result.isConfirmed) return;
            buttons.forEach((btn, i) => {
                setTimeout(() => btn.click(), i * 300);
            });
        });
    });

    // ── Refresh ──────────────────────────────────────────────────────────
    document.getElementById('qaRefreshBtn')?.addEventListener('click', function () {
        location.reload();
    });

    // ── Keyboard shortcuts ───────────────────────────────────────────────
    document.addEventListener('keydown', function (e) {
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            document.getElementById('saveAllBtn')?.click();
        }
        if (e.ctrlKey && e.key === 'v') {
            e.preventDefault();
            document.getElementById('validateAllBtn')?.click();
        }
        if (e.ctrlKey && e.key === 'Enter') {
            e.preventDefault();
            const el = document.activeElement;
            if (el?.dataset?.table) {
                document.querySelector(`.save-table[data-table-id="${el.dataset.table}"]`)?.click();
            }
        }
        if (e.key === 'F5')     { e.preventDefault(); location.reload(); }
        if (e.key === 'Escape') { document.activeElement?.blur(); }
    });
})();
</script>
