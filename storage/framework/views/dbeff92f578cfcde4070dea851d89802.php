
<script>
document.addEventListener('DOMContentLoaded', function () {

    // =========================================================================
    // STATUS ↔ IS_OPERATIVE SYNC
    // =========================================================================
    // Business rule (mirrors VotingTable::scopeForElections):
    //   A building can only be "habilitado para elecciones" when its status is 'activo'.
    //   If status → en_mantenimiento or inactivo, force is_operative OFF and lock it.
    // =========================================================================

    const statusEl    = document.getElementById('status-field');
    const operativeEl = document.getElementById('is_operative-field');
    const opLabel     = document.getElementById('operative-label');
    const opWarning   = document.getElementById('operative-warning');

    function syncOperative() {
        if (!statusEl || !operativeEl) return;

        const isActive = statusEl.value === 'activo';

        if (!isActive) {
            operativeEl.checked  = false;
            operativeEl.disabled = true;
            if (opWarning) opWarning.style.display = 'block';
        } else {
            operativeEl.disabled = false;
            if (opWarning) opWarning.style.display = 'none';
        }

        updateOperativeLabel();
    }

    function updateOperativeLabel() {
        if (!operativeEl || !opLabel) return;

        if (operativeEl.disabled) {
            opLabel.textContent = 'No disponible (edificio no activo)';
            opLabel.className   = 'form-check-label text-muted fst-italic';
        } else if (operativeEl.checked) {
            opLabel.textContent = 'Sí – incluido en la jornada electoral vigente';
            opLabel.className   = 'form-check-label fw-semibold text-success';
        } else {
            opLabel.textContent = 'No – excluido de la jornada electoral vigente';
            opLabel.className   = 'form-check-label fw-semibold text-secondary';
        }
    }

    if (statusEl) {
        statusEl.addEventListener('change', syncOperative);
        syncOperative(); // run once on load
    }

    if (operativeEl) {
        operativeEl.addEventListener('change', updateOperativeLabel);
        updateOperativeLabel(); // run once on load
    }

    // =========================================================================
    // CASCADE SELECTS
    // =========================================================================
    // Pattern: each <select> has data-cascade-url + data-cascade-target.
    // fetchCascade() returns a Promise and recurses when data-restore is set.
    // =========================================================================

    function fetchCascade(parentEl) {
        const baseUrl   = parentEl.dataset.cascadeUrl;
        const targetSel = parentEl.dataset.cascadeTarget;
        const parentVal = parentEl.value;

        if (!baseUrl || !targetSel) return Promise.resolve();

        const targetEl = document.querySelector(targetSel);
        if (!targetEl) return Promise.resolve();

        clearDescendants(targetEl);

        if (!parentVal) return Promise.resolve();

        const url = baseUrl.replace(/\/$/, '') + '/' + parentVal;

        return fetch(url)
            .then(r => {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(data => {
                const restoreVal = targetEl.dataset.restore || '';

                const labelMap = {
                    '#province-field':     'Provincia',
                    '#municipality-field': 'Municipio',
                    '#locality-field':     'Localidad',
                    '#district-field':     'Distrito (opcional)',
                    '#zone-field':         'Zona (opcional)',
                };
                const label = labelMap[targetSel] || 'opción';

                targetEl.innerHTML = `<option value="">-- Seleccione ${label} --</option>`;

                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(item => {
                        const opt       = document.createElement('option');
                        opt.value       = item.id;
                        opt.textContent = item.name;
                        if (String(item.id) === String(restoreVal)) opt.selected = true;
                        targetEl.appendChild(opt);
                    });
                    targetEl.disabled = false;
                } else {
                    targetEl.disabled = true;
                }

                if (restoreVal && targetEl.value && targetEl.dataset.cascadeUrl) {
                    return fetchCascade(targetEl);
                }
            })
            .catch(err => {
                console.error('Cascade error (' + url + '):', err);
                targetEl.innerHTML = '<option value="">-- Error al cargar --</option>';
                targetEl.disabled  = true;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error', title: 'Error de carga',
                        text: 'No se pudieron cargar las opciones.',
                        toast: true, position: 'top-end',
                        showConfirmButton: false, timer: 3000,
                    });
                }
            });
    }

    function clearDescendants(el) {
        if (!el) return;
        const labelMap = {
            'province-field':     'Provincia',
            'municipality-field': 'Municipio',
            'locality-field':     'Localidad',
            'district-field':     'Distrito (opcional)',
            'zone-field':         'Zona (opcional)',
        };
        const placeholder = labelMap[el.id] || 'opción';
        el.innerHTML = `<option value="">-- Seleccione ${placeholder} --</option>`;
        el.disabled  = true;

        if (el.dataset.cascadeTarget) {
            clearDescendants(document.querySelector(el.dataset.cascadeTarget));
        }
    }

    // Wire cascade listeners
    document.querySelectorAll('[data-cascade-url][data-cascade-target]').forEach(parentEl => {
        parentEl.addEventListener('change', () => fetchCascade(parentEl));
    });

    // Edit-form restore: kick off chain from department
    const deptEl = document.getElementById('department-field');
    if (deptEl && deptEl.value) {
        fetchCascade(deptEl);
    }

    // =========================================================================
    // BULK ACTION BUTTONS (index page)
    // =========================================================================

    const checkAll          = document.getElementById('checkAll');
    const deleteMultipleBtn = document.getElementById('delete-multiple-btn');
    const exportSelectedBtn = document.getElementById('export-selected-btn');
    const selectedBadge     = document.getElementById('selected-count-badge');

    function updateBulkButtons() {
        const count = document.querySelectorAll('.child-checkbox:checked').length;

        if (deleteMultipleBtn) {
            deleteMultipleBtn.style.display = count > 0 ? 'inline-block' : 'none';
            deleteMultipleBtn.innerHTML =
                `<i class="ri-delete-bin-2-line me-1"></i>Eliminar Seleccionados (${count})`;
        }
        if (exportSelectedBtn) {
            exportSelectedBtn.disabled = count === 0;
        }
        if (selectedBadge) {
            selectedBadge.textContent   = count;
            selectedBadge.style.display = count > 0 ? 'inline-block' : 'none';
        }
    }

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            document.querySelectorAll('.child-checkbox').forEach(cb => {
                cb.checked = this.checked;
            });
            updateBulkButtons();
        });
    }

    document.querySelectorAll('.child-checkbox').forEach(cb => {
        cb.addEventListener('change', function () {
            const all     = document.querySelectorAll('.child-checkbox');
            const checked = document.querySelectorAll('.child-checkbox:checked');
            if (checkAll) {
                checkAll.checked       = checked.length === all.length && all.length > 0;
                checkAll.indeterminate = checked.length > 0 && checked.length < all.length;
            }
            updateBulkButtons();
        });
    });

    // Auto-submit filter form on select change
    const filterForm = document.getElementById('filter-form');
    if (filterForm) {
        filterForm.querySelectorAll('select').forEach(sel => {
            sel.addEventListener('change', () => filterForm.submit());
        });
    }

    // ── Delete single (modal) ─────────────────────────────────────────────────
    const deleteModal = document.getElementById('deleteRecordModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            const btn  = event.relatedTarget;
            const nameEl = document.getElementById('deleteInstitutionName');
            const form   = document.getElementById('deleteForm');
            if (nameEl) nameEl.textContent = btn.dataset.name;
            if (form)   form.action        = btn.dataset.deleteUrl;
        });
    }
});

// ── Export selected ───────────────────────────────────────────────────────────
function exportSelected() {
    const ids = Array.from(document.querySelectorAll('.child-checkbox:checked')).map(cb => cb.value);
    if (!ids.length) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'warning', title: 'Sin selección',
                text: 'Seleccione al menos un recinto para exportar.',
                confirmButtonText: 'Entendido' });
        }
        return;
    }
    document.getElementById('selected-ids-input').value = JSON.stringify(ids);
    document.getElementById('export-selected-form').submit();
}

// ── Delete multiple ───────────────────────────────────────────────────────────
function deleteMultiple() {
    const ids = Array.from(document.querySelectorAll('.child-checkbox:checked')).map(cb => cb.value);
    if (!ids.length) return;

    Swal.fire({
        title: '¿Estás seguro?',
        text:  `¿Deseas eliminar ${ids.length} recinto(s)? Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton:   true,
        confirmButtonColor: '#d33',
        cancelButtonColor:  '#3085d6',
        confirmButtonText:  'Sí, eliminar',
        cancelButtonText:   'Cancelar',
    }).then(result => {
        if (!result.isConfirmed) return;

        Swal.fire({ title: 'Eliminando...', text: 'Por favor espere',
                    allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        fetch('<?php echo e(route("institutions.deleteMultiple")); ?>', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json',
                       'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
            body: JSON.stringify({ ids }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Eliminados',
                            text: data.message, confirmButtonText: 'OK' })
                    .then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Error',
                            text: data.message, confirmButtonText: 'OK' });
            }
        })
        .catch(() => {
            Swal.fire({ icon: 'error', title: 'Error',
                        text: 'Ocurrió un error inesperado al eliminar los recintos.',
                        confirmButtonText: 'OK' });
        });
    });
}
</script>
<?php /**PATH D:\_Mine\sistema_electoral\resources\views/institutions/scripts/institution-js.blade.php ENDPATH**/ ?>