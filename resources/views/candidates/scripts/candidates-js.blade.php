<script>
// ══════════════════════════════════════════════════
//  CHOICES.JS HELPERS
// ══════════════════════════════════════════════════
function makeChoices(selector, placeholder) {
    const el = document.querySelector(selector);
    if (!el) return null;
    return new Choices(el, {
        searchEnabled: true,
        shouldSort: false,
        placeholder: true,
        placeholderValue: placeholder,
        itemSelectText: '',
        allowHTML: false,
    });
}

function destroyChoices(instance) {
    try { if (instance) instance.destroy(); } catch (_) {}
    return null;
}

// ══════════════════════════════════════════════════
//  INIT — called on DOMContentLoaded
// ══════════════════════════════════════════════════
function initializeForms() {
    window.choicesETC  = makeChoices('#election_type_category_id-field', 'Seleccione categoría');
    setupGeographicSelectsModal();
    setupColorPicker();
    setupImagePreviews();
    setupCreateButton();
    setupEditButton();
    setupViewButton();
    setupDeleteButton();
    setupCheckAll();
}

// ══════════════════════════════════════════════════
//  COLOR PICKER
// ══════════════════════════════════════════════════
function setupColorPicker() {
    const picker = document.getElementById('color-field');
    const hex    = document.getElementById('color-hex');
    if (!picker || !hex) return;

    picker.addEventListener('input', () => hex.value = picker.value);
    hex.addEventListener('input', function () {
        if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) picker.value = this.value;
    });
}

// ══════════════════════════════════════════════════
//  GEOGRAPHIC SELECTS (inside the modal form)
// ══════════════════════════════════════════════════
function setupGeographicSelectsModal() {
    const deptSel = document.getElementById('department_id-field');
    const provSel = document.getElementById('province_id-field');
    const munSel  = document.getElementById('municipality_id-field');
    if (!deptSel || !provSel || !munSel) return;

    window.choicesDept = destroyChoices(window.choicesDept);
    window.choicesProv = destroyChoices(window.choicesProv);
    window.choicesMun  = destroyChoices(window.choicesMun);

    window.choicesDept = makeChoices('#department_id-field', 'Seleccione departamento');
    window.choicesProv = makeChoices('#province_id-field',  'Primero seleccione departamento');
    window.choicesMun  = makeChoices('#municipality_id-field', 'Primero seleccione provincia');

    deptSel.addEventListener('change', function () {
        loadProvinces(this.value);
    });

    provSel.addEventListener('change', function () {
        loadMunicipalities(this.value);
    });
}

function loadProvinces(departmentId) {
    const provSel = document.getElementById('province_id-field');
    const munSel  = document.getElementById('municipality_id-field');

    window.choicesProv = destroyChoices(window.choicesProv);
    window.choicesMun  = destroyChoices(window.choicesMun);

    // Reset municipality first
    munSel.innerHTML = '<option value="">Primero seleccione provincia</option>';
    munSel.disabled  = true;
    window.choicesMun = makeChoices('#municipality_id-field', 'Primero seleccione provincia');

    if (!departmentId) {
        provSel.innerHTML = '<option value="">Primero seleccione departamento</option>';
        provSel.disabled  = true;
        window.choicesProv = makeChoices('#province_id-field', 'Primero seleccione departamento');
        return;
    }

    fetch(`/candidates/provinces/${departmentId}`)
        .then(r => { if (!r.ok) throw new Error('Network error'); return r.json(); })
        .then(provinces => {
            provSel.innerHTML = '<option value="">Seleccione una provincia</option>';
            provinces.forEach(p => {
                provSel.insertAdjacentHTML('beforeend', `<option value="${p.id}">${p.name}</option>`);
            });
            provSel.disabled = false;
            window.choicesProv = makeChoices('#province_id-field', 'Seleccione una provincia');

            // Restore saved province (for edit)
            if (window._pendingProvinceId) {
                setTimeout(() => {
                    window.choicesProv?.setChoiceByValue(String(window._pendingProvinceId));
                    provSel.dispatchEvent(new Event('change', { bubbles: true }));
                    window._pendingProvinceId = null;
                }, 150);
            }
        })
        .catch(() => {
            provSel.innerHTML = '<option value="">Error al cargar provincias</option>';
            provSel.disabled  = false;
            window.choicesProv = makeChoices('#province_id-field', 'Error al cargar');
        });
}

function loadMunicipalities(provinceId) {
    const munSel = document.getElementById('municipality_id-field');
    window.choicesMun = destroyChoices(window.choicesMun);

    if (!provinceId) {
        munSel.innerHTML = '<option value="">Primero seleccione provincia</option>';
        munSel.disabled  = true;
        window.choicesMun = makeChoices('#municipality_id-field', 'Primero seleccione provincia');
        return;
    }

    fetch(`/candidates/municipalities/${provinceId}`)
        .then(r => { if (!r.ok) throw new Error('Network error'); return r.json(); })
        .then(municipalities => {
            munSel.innerHTML = '<option value="">Seleccione un municipio</option>';
            municipalities.forEach(m => {
                munSel.insertAdjacentHTML('beforeend', `<option value="${m.id}">${m.name}</option>`);
            });
            munSel.disabled = false;
            window.choicesMun = makeChoices('#municipality_id-field', 'Seleccione un municipio');

            if (window._pendingMunicipalityId) {
                setTimeout(() => {
                    window.choicesMun?.setChoiceByValue(String(window._pendingMunicipalityId));
                    window._pendingMunicipalityId = null;
                }, 150);
            }
        })
        .catch(() => {
            munSel.innerHTML = '<option value="">Error al cargar municipios</option>';
            munSel.disabled  = false;
            window.choicesMun = makeChoices('#municipality_id-field', 'Error al cargar');
        });
}

// ══════════════════════════════════════════════════
//  IMAGE PREVIEWS
// ══════════════════════════════════════════════════
function setupImagePreviews() {
    bindPreview('photo-field',      'photo-preview');
    bindPreview('party_logo-field', 'party-logo-preview');
}

function bindPreview(inputId, previewId) {
    const input   = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    if (!input || !preview) return;
    input.addEventListener('change', function () {
        if (!this.files?.[0]) return;
        const reader = new FileReader();
        reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(this.files[0]);
    });
}

// ══════════════════════════════════════════════════
//  CREATE BUTTON
// ══════════════════════════════════════════════════
function setupCreateButton() {
    document.getElementById('create-btn')?.addEventListener('click', function () {
        const form = document.getElementById('candidateForm');
        if (form) form.reset();

        document.getElementById('modalTitleText').textContent     = 'Agregar Candidato';
        document.getElementById('form-method').value              = 'POST';
        document.getElementById('candidate_id').value             = '';
        if (form) form.action                                      = '/candidates';

        // Default colour
        document.getElementById('color-field').value = '#1b8af8';
        document.getElementById('color-hex').value   = '#1b8af8';

        // Hide previews
        ['photo-preview','party-logo-preview'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.display = 'none';
        });

        // Hide active-status row (only shown on edit)
        const activeRow = document.getElementById('active-status-row');
        if (activeRow) activeRow.style.display = 'none';

        // Reset Choices instances
        window.choicesETC?.setChoiceByValue('');
        resetGeographicModalSelects();
    });
}

function resetGeographicModalSelects() {
    const deptSel = document.getElementById('department_id-field');
    const provSel = document.getElementById('province_id-field');
    const munSel  = document.getElementById('municipality_id-field');

    if (deptSel) { window.choicesDept?.setChoiceByValue(''); }
    if (provSel) {
        window.choicesProv = destroyChoices(window.choicesProv);
        provSel.innerHTML = '<option value="">Primero seleccione departamento</option>';
        provSel.disabled  = true;
        window.choicesProv = makeChoices('#province_id-field', 'Primero seleccione departamento');
    }
    if (munSel) {
        window.choicesMun = destroyChoices(window.choicesMun);
        munSel.innerHTML = '<option value="">Primero seleccione provincia</option>';
        munSel.disabled  = true;
        window.choicesMun = makeChoices('#municipality_id-field', 'Primero seleccione provincia');
    }
}

// ══════════════════════════════════════════════════
//  EDIT BUTTON
// ══════════════════════════════════════════════════
function setupEditButton() {
    document.querySelectorAll('.edit-item-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const d = this.dataset;

            document.getElementById('modalTitleText').textContent = 'Editar Candidato';
            document.getElementById('form-method').value          = 'PUT';
            document.getElementById('candidate_id').value         = d.id;
            document.getElementById('candidateForm').action       = d.updateUrl;

            // Text fields
            document.getElementById('name-field').value             = d.name            ?? '';
            document.getElementById('party-field').value            = d.party           ?? '';
            document.getElementById('party_full_name-field').value  = d.party_full_name ?? '';
            document.getElementById('list_order-field').value       = d.list_order      ?? '';
            document.getElementById('list_name-field').value        = d.list_name       ?? '';

            // Colour
            const colour = d.color || '#1b8af8';
            document.getElementById('color-field').value = colour;
            document.getElementById('color-hex').value   = colour;

            // Election type/category
            window.choicesETC?.setChoiceByValue(d.election_type_category_id ?? '');

            // Active status
            const activeRow = document.getElementById('active-status-row');
            if (activeRow) {
                activeRow.style.display = 'block';
                const activeField = document.getElementById('active-field');
                if (activeField) activeField.checked = (d.active === '1');
            }

            // Image previews
            setPreviewUrl('photo-preview',      d.photoUrl);
            setPreviewUrl('party-logo-preview', d.partyLogoUrl);

            // Geographic cascade
            if (d.department_id) {
                window._pendingProvinceId    = d.province_id    || null;
                window._pendingMunicipalityId = d.municipality_id || null;
                window.choicesDept?.setChoiceByValue(d.department_id);
                setTimeout(() => {
                    document.getElementById('department_id-field')
                        ?.dispatchEvent(new Event('change', { bubbles: true }));
                }, 100);
            } else {
                resetGeographicModalSelects();
            }
        });
    });
}

function setPreviewUrl(previewId, url) {
    const el = document.getElementById(previewId);
    if (!el) return;
    if (url && url !== 'null' && url !== 'undefined') {
        el.src           = url;
        el.style.display = 'block';
    } else {
        el.style.display = 'none';
    }
}

// ══════════════════════════════════════════════════
//  VIEW BUTTON
// ══════════════════════════════════════════════════
function setupViewButton() {
    document.querySelectorAll('.view-item-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const d = this.dataset;

            setText('view-name',           d.name);
            setText('view-party',          d.party);
            setText('view-party-full-name',d.party_full_name);
            setText('view-election-type',  d.election_type);
            setText('view-election-category', d.election_category);
            setText('view-election-code',  d.election_category_code);
            setText('view-ballot-order',   d.ballot_order);
            setText('view-votes-per-person', d.votes_per_person || '1');

            // List
            const listParts = [];
            if (d.list_name)  listParts.push(d.list_name);
            if (d.list_order) listParts.push(`Orden: ${d.list_order}`);
            setText('view-list', listParts.join(' — ') || 'N/A');

            // Location
            const loc = [d.department_name, d.province_name, d.municipality_name].filter(Boolean).join(' / ');
            setText('view-location', loc || 'N/A');

            // Active badge
            const activeEl = document.getElementById('view-active');
            if (activeEl) {
                activeEl.innerHTML = d.active === '1'
                    ? '<span class="badge bg-success">Activo</span>'
                    : '<span class="badge bg-danger">Inactivo</span>';
            }

            // Photo
            const photo = document.getElementById('view-photo');
            if (photo) {
                photo.src           = (d.photoUrl && d.photoUrl !== 'null') ? d.photoUrl : '/build/images/default-candidate.jpg';
                photo.style.display = 'block';
            }

            // Party logo
            const logo = document.getElementById('view-party-logo');
            if (logo) {
                if (d.partyLogoUrl && d.partyLogoUrl !== 'null') {
                    logo.src           = d.partyLogoUrl;
                    logo.style.display = 'inline-block';
                } else {
                    logo.style.display = 'none';
                }
            }

            // Colour swatch
            const swatch = document.getElementById('view-color-preview');
            if (swatch) {
                if (d.color) {
                    swatch.style.backgroundColor = d.color;
                    swatch.style.display         = 'inline-block';
                    swatch.title                 = d.color;
                } else {
                    swatch.style.display = 'none';
                }
            }
        });
    });
}

function setText(id, value) {
    const el = document.getElementById(id);
    if (el) el.textContent = value || 'N/A';
}

// ══════════════════════════════════════════════════
//  DELETE BUTTON
// ══════════════════════════════════════════════════
function setupDeleteButton() {
    document.querySelectorAll('.remove-item-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const form    = document.getElementById('deleteForm');
            const msg     = document.getElementById('deleteMessage');
            if (form) form.action = this.dataset.deleteUrl;
            if (msg)  msg.textContent = `¿Eliminar al candidato "${this.dataset.name}"?`;
        });
    });
}

// ══════════════════════════════════════════════════
//  CHECKBOXES + BULK ACTIONS
// ══════════════════════════════════════════════════
function setupCheckAll() {
    const checkAll  = document.getElementById('checkAll');
    const children  = () => document.querySelectorAll('.child-checkbox');
    const delBtn    = document.getElementById('delete-multiple-btn');
    const expBtn    = document.getElementById('export-selected-btn');
    const badge     = document.getElementById('selected-count-badge');

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            children().forEach(cb => cb.checked = this.checked);
            updateBulkButtons();
        });
    }

    document.addEventListener('change', function (e) {
        if (!e.target.classList.contains('child-checkbox')) return;
        const all  = children();
        const checked = Array.from(all).filter(cb => cb.checked);
        if (checkAll) {
            checkAll.checked       = checked.length === all.length && all.length > 0;
            checkAll.indeterminate = checked.length > 0 && checked.length < all.length;
        }
        updateBulkButtons();
    });

    function updateBulkButtons() {
        const n = Array.from(children()).filter(cb => cb.checked).length;
        if (delBtn) {
            delBtn.classList.toggle('d-none', n === 0);
            delBtn.innerHTML = `<i class="ri-delete-bin-2-line me-1"></i>Eliminar Seleccionados (${n})`;
        }
        if (expBtn) expBtn.disabled = n === 0;
        if (badge)  { badge.textContent = n; badge.style.display = n > 0 ? 'inline-block' : 'none'; }
    }
}

// ══════════════════════════════════════════════════
//  EXPORT SELECTED
//  Builds multiple <input name="selected_ids[]"> so
//  Laravel's array validation works correctly.
// ══════════════════════════════════════════════════
window.exportSelected = function () {
    const ids = Array.from(document.querySelectorAll('.child-checkbox:checked')).map(cb => cb.value);
    if (!ids.length) {
        return Swal.fire({ icon: 'warning', title: 'Sin selección',
            text: 'Seleccione al menos un candidato para exportar.',
            confirmButtonColor: '#1b8af8' });
    }

    const form = document.getElementById('export-selected-form');
    // Remove any previously appended inputs
    form.querySelectorAll('input[name="selected_ids[]"]').forEach(i => i.remove());

    ids.forEach(id => {
        const input = Object.assign(document.createElement('input'), {
            type: 'hidden', name: 'selected_ids[]', value: id,
        });
        form.appendChild(input);
    });

    form.submit();
};

// ══════════════════════════════════════════════════
//  DELETE MULTIPLE
// ══════════════════════════════════════════════════
window.deleteMultiple = function () {
    const ids = Array.from(document.querySelectorAll('.child-checkbox:checked')).map(cb => cb.value);
    if (!ids.length) {
        return Swal.fire({ icon: 'warning', title: 'Sin selección',
            text: 'Seleccione al menos un candidato para eliminar.',
            confirmButtonColor: '#1b8af8' });
    }

    Swal.fire({
        title: '¿Está seguro?',
        text: `Se eliminarán ${ids.length} candidato(s). Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton:    true,
        confirmButtonColor:  '#f06548',
        cancelButtonColor:   '#8590a5',
        confirmButtonText:   'Sí, eliminar',
        cancelButtonText:    'Cancelar',
    }).then(result => {
        if (!result.isConfirmed) return;

        const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/candidates/multiple-delete';
        form.innerHTML = `
            <input type="hidden" name="_token"  value="${csrf}">
            <input type="hidden" name="_method" value="DELETE">
        `;
        ids.forEach(id => {
            const inp = document.createElement('input');
            inp.type  = 'hidden';
            inp.name  = 'ids[]';
            inp.value = id;
            form.appendChild(inp);
        });
        document.body.appendChild(form);
        form.submit();
    });
};

// ══════════════════════════════════════════════════
//  BOOT
// ══════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', initializeForms);
</script>
