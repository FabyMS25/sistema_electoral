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

                {{-- ── Right: action buttons ── --}}
                <div class="col-md-7 col-lg-6 d-flex justify-content-end align-items-center gap-2 flex-wrap">

                    {{-- Save all (only if can register) --}}
                    @if($permissions['can_register'] ?? false)
                    <button class="btn btn-success btn-sm" id="saveAllBtn"
                            title="Guardar todas las mesas visibles (Ctrl+S)">
                        <i class="ri-save-line me-1"></i>
                        <span class="d-none d-md-inline">Guardar todo</span>
                    </button>
                    @endif

                    {{-- Close all (only if can close) --}}
                    @if($permissions['can_close'] ?? false)
                    <button class="btn btn-dark btn-sm" id="closeAllBtn"
                            title="Cerrar todas las mesas válidas">
                        <i class="ri-lock-line me-1"></i>
                        <span class="d-none d-md-inline">Cerrar todo</span>
                    </button>
                    @endif

                    {{-- Refresh --}}
                    <button class="btn btn-outline-secondary btn-sm" id="qaRefreshBtn"
                            title="Recargar página (F5)">
                        <i class="ri-refresh-line"></i>
                    </button>

                    {{-- Keyboard shortcuts help --}}
                    <button class="btn btn-outline-secondary btn-sm" type="button"
                            data-bs-toggle="popover" data-bs-trigger="focus"
                            data-bs-placement="top" data-bs-html="true"
                            data-bs-title="Atajos de teclado"
                            data-bs-content="
                                <table class='table table-sm table-borderless mb-0 small'>
                                  <tr><td><kbd>Ctrl+S</kbd></td><td>Guardar todo</td></tr>
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
    // ── Pending-changes counter ──────────────────────────────────────────
    window.pendingTables = window.pendingTables ?? new Set();

    function updatePendingDisplay() {
        const count = window.pendingTables.size;
        const indicator = document.getElementById('qa-pending-indicator');
        const badge     = document.getElementById('qa-pending-count');
        if (!indicator || !badge) return;
        badge.textContent = count;
        indicator.style.display = count > 0 ? '' : 'none';
    }

    // Watch for changes on any vote / blank / null input
    document.querySelectorAll('.vote-input, .blank-votes-input, .null-votes-input').forEach(input => {
        input.addEventListener('input', function () {
            const tableId = this.dataset.table;
            if (tableId) {
                window.pendingTables.add(tableId);
                updatePendingDisplay();
            }
        });
    });

    // Remove from pending after a successful save
    document.addEventListener('tableSaved', function (e) {
        if (e.detail?.tableId) {
            window.pendingTables.delete(String(e.detail.tableId));
            updatePendingDisplay();
        }
    });

    // ── Save all ─────────────────────────────────────────────────────────
    document.getElementById('saveAllBtn')?.addEventListener('click', function () {
        const buttons = document.querySelectorAll('.save-table');
        if (buttons.length === 0) return;

        Swal.fire({
            title: `¿Guardar ${buttons.length} mesa${buttons.length !== 1 ? 's' : ''}?`,
            text: 'Se guardarán todas las mesas visibles con sus votos actuales.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, guardar todo',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#0ab39c',
        }).then(result => {
            if (!result.isConfirmed) return;
            buttons.forEach(btn => btn.click());
        });
    });

    // ── Close all ─────────────────────────────────────────────────────────
    document.getElementById('closeAllBtn')?.addEventListener('click', function () {
        const buttons = document.querySelectorAll('.close-table');
        if (buttons.length === 0) {
            Swal.fire({ icon: 'info', title: 'Sin mesas disponibles',
                        text: 'No hay mesas abiertas para cerrar en esta vista.' });
            return;
        }

        Swal.fire({
            title: `¿Cerrar ${buttons.length} mesa${buttons.length !== 1 ? 's' : ''}?`,
            text: 'Solo se cerrarán las mesas que no tengan inconsistencias.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, cerrar todo',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#405189',
        }).then(result => {
            if (!result.isConfirmed) return;
            buttons.forEach(btn => btn.click());
        });
    });

    // ── Refresh ──────────────────────────────────────────────────────────
    document.getElementById('qaRefreshBtn')?.addEventListener('click', function () {
        location.reload();
    });

    // ── Keyboard shortcuts ────────────────────────────────────────────────
    document.addEventListener('keydown', function (e) {
        // Ctrl+S → save all
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            document.getElementById('saveAllBtn')?.click();
        }
        // Ctrl+Enter → save the table whose input is focused
        if (e.ctrlKey && e.key === 'Enter') {
            e.preventDefault();
            const el = document.activeElement;
            if (el?.dataset?.table) {
                document.querySelector(`.save-table[data-table-id="${el.dataset.table}"]`)?.click();
            }
        }
        // F5 → refresh
        if (e.key === 'F5') { e.preventDefault(); location.reload(); }
        // Esc → blur
        if (e.key === 'Escape') document.activeElement?.blur();
    });

    // ── Bootstrap popover init ────────────────────────────────────────────
    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
        new bootstrap.Popover(el, { sanitize: false });
    });
})();
</script>
