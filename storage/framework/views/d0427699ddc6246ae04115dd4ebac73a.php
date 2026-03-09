
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Checkbox handling ────────────────────────────────────────────────────
    const checkAll    = document.getElementById('checkAll');
    const checkboxes  = document.querySelectorAll('.child-checkbox');
    const deleteManyBtn   = document.getElementById('delete-multiple-btn');
    const exportSelBtn    = document.getElementById('export-selected-btn');
    const selectedBadge   = document.getElementById('selected-count-badge');

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateActionButtons();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            updateCheckAll();
            updateActionButtons();
        });
    });

    function updateCheckAll() {
        if (!checkboxes.length || !checkAll) return;
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
        checkAll.checked       = allChecked;
        checkAll.indeterminate = !allChecked && anyChecked;
    }

    function updateActionButtons() {
        const n = document.querySelectorAll('.child-checkbox:checked').length;

        if (deleteManyBtn) {
            deleteManyBtn.style.display = n > 0 ? 'inline-block' : 'none';
            if (n > 0) {
                deleteManyBtn.innerHTML = `<i class="ri-delete-bin-2-line me-1"></i>Eliminar Seleccionados (${n})`;
            }
        }

        if (exportSelBtn) {
            exportSelBtn.disabled = n === 0;
            if (selectedBadge) {
                selectedBadge.style.display = n > 0 ? 'inline-block' : 'none';
                selectedBadge.textContent   = n;
            }
        }
    }

    // ── Delete single modal ──────────────────────────────────────────────────
    const deleteModal = document.getElementById('deleteRecordModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            const btn  = event.relatedTarget;
            if (!btn) return;

            const oep      = btn.getAttribute('data-oep')      || btn.getAttribute('data-code') || '';
            const internal = btn.getAttribute('data-internal') || '';
            const deleteUrl = btn.getAttribute('data-delete-url') || '';

            // Populate info text
            const infoEl = document.getElementById('deleteTableInfo');
            if (infoEl) {
                infoEl.textContent = oep
                    ? `Código OEP: ${oep}${internal ? ' — Interno: ' + internal : ''}`
                    : `ID: ${btn.getAttribute('data-id')}`;
            }

            // Point the delete form to the correct URL
            const form = document.getElementById('deleteForm');
            if (form && deleteUrl) {
                form.action = deleteUrl;
            }
        });
    }

    // ── Filter-form selects auto-submit ──────────────────────────────────────
    document.querySelectorAll('#filter-form select').forEach(sel => {
        sel.addEventListener('change', () => document.getElementById('filter-form')?.submit());
    });
});

// ── Export selected (called from button onclick) ──────────────────────────────
function exportSelected() {
    const ids = Array.from(document.querySelectorAll('.child-checkbox:checked')).map(cb => cb.value);

    if (ids.length === 0) {
        Swal.fire({ title: 'Sin selección', text: 'Seleccione al menos una mesa.', icon: 'warning', confirmButtonText: 'OK' });
        return;
    }

    document.getElementById('selected-ids-input').value = JSON.stringify(ids);
    document.getElementById('export-selected-form').submit();
}

// ── Delete multiple (called from button onclick) ──────────────────────────────
function deleteMultiple() {
    const ids = Array.from(document.querySelectorAll('.child-checkbox:checked')).map(cb => cb.value);
    if (ids.length === 0) return;

    Swal.fire({
        title: '¿Estás seguro?',
        text: `¿Deseas eliminar ${ids.length} mesa(s)? Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
    }).then(result => {
        if (!result.isConfirmed) return;

        Swal.fire({ title: 'Eliminando…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        fetch('<?php echo e(route("voting-tables.deleteMultiple")); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
            body: JSON.stringify({ ids }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ title: 'Eliminados', text: data.message, icon: 'success', confirmButtonText: 'OK' })
                    .then(() => location.reload());
            } else {
                Swal.fire({ title: 'Error', text: data.message, icon: 'error', confirmButtonText: 'OK' });
            }
        })
        .catch(() => {
            Swal.fire({ title: 'Error', text: 'Ocurrió un error al eliminar las mesas.', icon: 'error', confirmButtonText: 'OK' });
        });
    });
}
</script>
<?php /**PATH D:\_Mine\sistema_electoral\resources\views/voting-tables/scripts/voting-table-js.blade.php ENDPATH**/ ?>