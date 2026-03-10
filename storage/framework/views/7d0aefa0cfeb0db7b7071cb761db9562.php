
<script>
function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

function showToast(icon, title, text = '') {
    return Swal.fire({
        icon, title, text,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
    });
}

function showError(message) {
    return Swal.fire({ icon: 'error', title: 'Error', text: message });
}

function setButtonLoading(btn, loading) {
    if (!btn) return;
    if (loading) {
        btn.dataset.originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="ri-loader-4-line ri-spin"></i>';
        btn.disabled = true;
    } else {
        btn.innerHTML = btn.dataset.originalHtml ?? btn.innerHTML;
        btn.disabled = false;
    }
}

// Collect all vote inputs for a given table into {candidateId: quantity}
function collectVotes(tableId) {
    const votes = {};
    document.querySelectorAll(`#table-${tableId} .vote-input`).forEach(input => {
        votes[input.dataset.candidate] = parseInt(input.value) || 0;
    });
    return votes;
}

// Collect blank/null inputs per category  {categoryCode: qty}
function collectSpecialVotes(tableId, type) {  // type = 'blank' | 'null'
    const result = {};
    document.querySelectorAll(`#table-${tableId} .${type}-votes-input`).forEach(input => {
        result[input.dataset.category] = parseInt(input.value) || 0;
    });
    return result;
}

// Re-render the totals row after a save
function refreshTableTotals(tableId, categoryTotals) {
    Object.entries(categoryTotals).forEach(([code, total]) => {
        const el = document.getElementById(`total-${code}-${tableId}`);
        if (el) el.textContent = total;
    });
    const totals = Object.values(categoryTotals);
    const grand  = totals.length > 0 ? totals[0] : 0;
    const totalEl = document.getElementById(`total-${tableId}`);
    if (totalEl) totalEl.textContent = grand;
}

// Update the left-border status class on a table card
function setTableStatusClass(tableId, newStatus) {
    const card = document.getElementById(`table-${tableId}`);
    if (!card) return;
    card.className = card.className.replace(/\bstatus-\S+/g, '');
    card.classList.add(`status-${newStatus}`);
}

// ─── SAVE VOTES ──────────────────────────────────────────────────────────────

async function saveTable(tableId, closeAfter = false) {
    const btn = document.querySelector(`[data-table-id="${tableId}"].save-table`);
    setButtonLoading(btn, true);

    try {
        const votes      = collectVotes(tableId);
        const blankVotes = collectSpecialVotes(tableId, 'blank');
        const nullVotes  = collectSpecialVotes(tableId, 'null');
        const body = {
            voting_table_id:  tableId,
            election_type_id: electionTypeId,
            votes,
            blank_votes: Object.keys(blankVotes).length ? blankVotes : undefined,
            null_votes:  Object.keys(nullVotes).length  ? nullVotes  : undefined,
            close: closeAfter,
        };

        const response = await fetch('/voting-table-votes/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json',
            },
            body: JSON.stringify(body),
        });

        const data = await response.json();

        if (data.success) {
            refreshTableTotals(tableId, data.category_totals ?? {});
            setTableStatusClass(tableId, data.table_status);
            showToast('success', data.message);
        } else {
            showError(data.message ?? 'Error al guardar votos');
        }
    } catch (err) {
        console.error('saveTable error', err);
        showError('Error de conexión al guardar votos');
    } finally {
        setButtonLoading(btn, false);
    }
}

// ─── REVIEW TABLE ─────────────────────────────────────────────────────────────
// Opens a modal/dialog letting the reviewer select which votes to observe
// or confirm that everything is correct.

async function reviewTable(tableId) {
    // First load the current votes for this table
    const votesResp = await fetch(
        `/voting-table-votes/${tableId}/votes?election_type_id=${electionTypeId}`,
        { headers: { 'Accept': 'application/json' } }
    );
    const votes = await votesResp.json();

    const voteRows = votes.map(v => `
        <div class="form-check mb-1">
            <input class="form-check-input review-observe-cb"
                   type="checkbox"
                   value="${v.id}"
                   id="rev_${v.id}"
                   ${v.vote_status === 'observed' ? 'checked disabled' : ''}>
            <label class="form-check-label d-flex justify-content-between" for="rev_${v.id}">
                <span>
                    <strong>${escHtml(v.candidate_name)}</strong>
                    <small class="text-muted ms-1">${escHtml(v.candidate_party)}</small>
                </span>
                <span class="badge bg-secondary">${v.quantity} votos</span>
            </label>
        </div>
    `).join('');

    const { value: formValues } = await Swal.fire({
        title: `Revisión — Mesa ${tableId}`,
        width: 600,
        html: `
            <p class="text-start text-muted small mb-2">
                Marque los candidatos cuyos votos desea observar.
                Si todo está correcto, deje todo sin marcar y confirme.
            </p>
            <div class="text-start border rounded p-2 mb-3"
                 style="max-height:300px;overflow-y:auto;">
                ${voteRows || '<p class="text-muted">No hay votos registrados</p>'}
            </div>
            <div class="text-start mb-2">
                <label class="form-label fw-bold">Notas de la revisión (opcional):</label>
                <textarea id="review-notes" class="form-control" rows="2"
                          placeholder="Describa lo observado…"></textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Confirmar revisión',
        cancelButtonText:  'Cancelar',
        preConfirm: () => {
            const selected = Array.from(
                document.querySelectorAll('.review-observe-cb:checked:not(:disabled)')
            ).map(cb => parseInt(cb.value));
            return {
                observed_vote_ids: selected,
                observation_notes: document.getElementById('review-notes').value.trim(),
            };
        },
    });

    if (!formValues) return;

    const btn = document.querySelector(`[data-table-id="${tableId}"].review-table`);
    setButtonLoading(btn, true);

    try {
        const resp = await fetch(`/voting-table-votes/${tableId}/review`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                election_type_id:  electionTypeId,
                observed_vote_ids: formValues.observed_vote_ids,
                observation_notes: formValues.observation_notes || null,
            }),
        });

        const data = await resp.json();

        if (data.success) {
            setTableStatusClass(tableId, data.table_status);
            showToast(data.has_observations ? 'warning' : 'success', data.message);
            setTimeout(() => location.reload(), 1800);
        } else {
            showError(data.message);
        }
    } finally {
        setButtonLoading(btn, false);
    }
}

// ─── CORRECT TABLE ───────────────────────────────────────────────────────────
// Opens a modal pre-filled with observed votes so the corrector can edit quantities.

async function correctTable(tableId) {
    const votesResp = await fetch(
        `/voting-table-votes/${tableId}/votes?election_type_id=${electionTypeId}`,
        { headers: { 'Accept': 'application/json' } }
    );
    const votes = await votesResp.json();

    const observed = votes.filter(v => v.vote_status === 'observed');
    const all      = votes;

    // Group votes by category
    const byCategory = {};
    all.forEach(v => {
        const cat = v.category_code ?? String(v.category_id ?? 'General');
        if (!byCategory[cat]) byCategory[cat] = [];
        byCategory[cat].push(v);
    });

    // Build blank/null rows from the DOM (current live values)
    const blankInputs = document.querySelectorAll(`#table-${tableId} .blank-votes-input`);
    const nullInputs  = document.querySelectorAll(`#table-${tableId} .null-votes-input`);

    const blankByCategory = {};
    blankInputs.forEach(inp => { blankByCategory[inp.dataset.category] = parseInt(inp.value) || 0; });

    const nullByCategory  = {};
    nullInputs.forEach(inp => { nullByCategory[inp.dataset.category] = parseInt(inp.value) || 0; });

    let rows = '';

    // Candidate vote rows grouped by category
    Object.entries(byCategory).forEach(([cat, catVotes]) => {
        rows += `<div class="mb-2">
            <div class="fw-bold small text-muted border-bottom pb-1 mb-1">${escHtml(cat)}</div>`;
        catVotes.forEach(v => {
            rows += `
            <div class="row align-items-center mb-1 ${v.vote_status === 'observed' ? 'bg-warning bg-opacity-10 rounded px-1' : ''}">
                <div class="col-7 small">
                    ${v.vote_status === 'observed' ? '<i class="ri-alert-line text-warning me-1"></i>' : ''}
                    <strong>${escHtml(v.candidate_name)}</strong>
                    <div class="text-muted">${escHtml(v.candidate_party)}</div>
                </div>
                <div class="col-5">
                    <input type="number"
                           class="form-control form-control-sm correction-input"
                           data-vote-id="${v.id}"
                           data-category="${escHtml(cat)}"
                           value="${v.quantity}"
                           min="0">
                </div>
            </div>`;
        });

        // Blank and null rows for this category
        const blank = blankByCategory[cat] ?? 0;
        const nul   = nullByCategory[cat]  ?? 0;
        rows += `
            <div class="row align-items-center mb-1">
                <div class="col-7 small text-muted"><i class="ri-subtract-line me-1"></i>Votos en Blanco — ${escHtml(cat)}</div>
                <div class="col-5">
                    <input type="number" class="form-control form-control-sm blank-correction-input"
                           data-category="${escHtml(cat)}" value="${blank}" min="0">
                </div>
            </div>
            <div class="row align-items-center mb-2">
                <div class="col-7 small text-muted"><i class="ri-close-line me-1"></i>Votos Nulos — ${escHtml(cat)}</div>
                <div class="col-5">
                    <input type="number" class="form-control form-control-sm null-correction-input"
                           data-category="${escHtml(cat)}" value="${nul}" min="0">
                </div>
            </div>`;

        rows += '</div>';
    });

    const { value: formValues } = await Swal.fire({
        title: `Corrección — Mesa ${tableId}`,
        width: 620,
        html: `
            <div class="text-start border rounded p-2 mb-3"
                 style="max-height:320px;overflow-y:auto;">
                ${rows || '<p class="text-muted">No hay votos registrados</p>'}
            </div>
            <div class="text-start">
                <label class="fw-bold form-label">Motivo de la corrección <span class="text-danger">*</span></label>
                <textarea id="correction-notes" class="form-control" rows="2"
                          placeholder="Describa el motivo…"></textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Aplicar correcciones',
        cancelButtonText:  'Cancelar',
        preConfirm: () => {
            const notes = document.getElementById('correction-notes').value.trim();
            if (!notes) {
                Swal.showValidationMessage('El motivo de la corrección es obligatorio');
                return false;
            }
            const corrections = {};
            document.querySelectorAll('.correction-input').forEach(input => {
                corrections[input.dataset.voteId] = parseInt(input.value) || 0;
            });
            const blank_votes = {};
            document.querySelectorAll('.blank-correction-input').forEach(inp => {
                blank_votes[inp.dataset.category] = parseInt(inp.value) || 0;
            });
            const null_votes = {};
            document.querySelectorAll('.null-correction-input').forEach(inp => {
                null_votes[inp.dataset.category] = parseInt(inp.value) || 0;
            });
            return { corrections, notes, blank_votes, null_votes };
        },
    });

    if (!formValues) return;

    const btn = document.querySelector(`[data-table-id="${tableId}"].correct-table`);
    setButtonLoading(btn, true);

    try {
        const resp = await fetch(`/voting-table-votes/${tableId}/correct`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                election_type_id: electionTypeId,
                corrections:      formValues.corrections,
                blank_votes:      formValues.blank_votes,
                null_votes:       formValues.null_votes,
                notes:            formValues.notes,
            }),
        });

        const data = await resp.json();

        if (data.success) {
            showToast('success', data.message);
            setTimeout(() => location.reload(), 1800);
        } else {
            showError(data.message);
        }
    } finally {
        setButtonLoading(btn, false);
    }
}

// ─── VALIDATE TABLE ───────────────────────────────────────────────────────────

async function validateTable(tableId, action) {
    const actionLabels = {
        validate:        { label: 'Validar votos',       color: 'info',    icon: 'ri-check-line',        confirmText: '¿Validar los votos de esta mesa? Quedará En Escrutinio.' },
        close_validated: { label: 'Validar y Escrutar', color: 'success', icon: 'ri-check-double-line',  confirmText: '¿Validar y escrutar esta mesa? Esta acción es final.' },
        reject:          { label: 'Rechazar',            color: 'danger',  icon: 'ri-close-circle-line', confirmText: '¿Rechazar esta mesa? Se marcará como Observada.' },
        reject:   { label: 'rechazar', color: 'danger', icon: 'ri-close-circle-line' },
    };
    const meta = actionLabels[action] ?? actionLabels.validate;

    const { value: notes } = await Swal.fire({
        title: `¿${meta.label.charAt(0).toUpperCase() + meta.label.slice(1)} la mesa?`,
        input: 'textarea',
        inputLabel: 'Notas (opcional)',
        inputPlaceholder: 'Agregue notas si es necesario…',
        showCancelButton: true,
        confirmButtonText: `Sí, ${meta.label}`,
        cancelButtonText: 'Cancelar',
    });

    if (notes === undefined) return;   // cancelled

    const btn = document.querySelector(`[data-table-id="${tableId}"].validate-table[data-action="${action}"]`);
    setButtonLoading(btn, true);

    try {
        const resp = await fetch(`/voting-table-votes/${tableId}/validate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                election_type_id: electionTypeId,
                action,
                notes: notes || null,
            }),
        });

        const data = await resp.json();

        if (data.success) {
            setTableStatusClass(tableId, data.table_status);
            showToast('success', data.message);
            setTimeout(() => location.reload(), 1800);
        } else {
            showError(data.message);
        }
    } finally {
        setButtonLoading(btn, false);
    }
}

// ─── CLOSE TABLE ─────────────────────────────────────────────────────────────

async function closeTable(tableId) {
    const confirm = await Swal.fire({
        title: '¿Cerrar mesa?',
        text: 'Se cerrará el período de registro de votos.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, cerrar',
        cancelButtonText: 'Cancelar',
    });

    if (!confirm.isConfirmed) return;

    const btn = document.querySelector(`[data-table-id="${tableId}"].close-table`);
    setButtonLoading(btn, true);

    try {
        const resp = await fetch(`/voting-table-votes/${tableId}/close`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json',
            },
            body: JSON.stringify({ election_type_id: electionTypeId }),
        });

        const data = await resp.json();

        if (data.success) {
            setTableStatusClass(tableId, 'cerrada');
            showToast('success', data.message);
            setTimeout(() => location.reload(), 1800);
        } else {
            showError(data.message);
        }
    } finally {
        setButtonLoading(btn, false);
    }
}

// ─── REOPEN TABLE ────────────────────────────────────────────────────────────

async function reopenTable(tableId) {
    const confirm = await Swal.fire({
        title: '¿Reabrir mesa?',
        text: 'La mesa volverá al estado Votación y podrá editarse.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, reabrir',
        cancelButtonText: 'Cancelar',
    });

    if (!confirm.isConfirmed) return;

    const btn = document.querySelector(`[data-table-id="${tableId}"].reopen-table`);
    setButtonLoading(btn, true);

    try {
        const resp = await fetch(`/voting-table-votes/${tableId}/reopen`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
                'Accept': 'application/json',
            },
            body: JSON.stringify({ election_type_id: electionTypeId }),
        });

        const data = await resp.json();

        if (data.success) {
            setTableStatusClass(tableId, 'votacion');
            showToast('success', data.message);
            setTimeout(() => location.reload(), 1800);
        } else {
            showError(data.message);
        }
    } finally {
        setButtonLoading(btn, false);
    }
}

// ─── SAVE ALL TABLES ─────────────────────────────────────────────────────────

window.saveAllTables = async function() {
    const saveBtns = document.querySelectorAll('.save-table');
    if (saveBtns.length === 0) {
        showToast('info', 'No hay mesas editables en esta página');
        return;
    }

    const confirm = await Swal.fire({
        title: `¿Guardar ${saveBtns.length} mesa(s)?`,
        text: 'Se guardarán todos los votos ingresados en esta página.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Guardar todo',
        cancelButtonText: 'Cancelar',
    });

    if (!confirm.isConfirmed) return;

    let ok = 0, fail = 0;
    for (const btn of saveBtns) {
        try {
            await saveTable(parseInt(btn.dataset.tableId), false);
            ok++;
        } catch {
            fail++;
        }
    }

    showToast('success', `${ok} mesa(s) guardada(s)${fail > 0 ? `, ${fail} con error` : ''}`);
};

// ─── LIVE INPUT TOTAL UPDATE ─────────────────────────────────────────────────

window.updateTableTotals = function(tableId) {
    const categoryTotals = {};

    // Valid votes from candidate inputs
    document.querySelectorAll(`#table-${tableId} .vote-input`).forEach(input => {
        const cat = input.dataset.category;
        const val = parseInt(input.value) || 0;
        categoryTotals[cat] = (categoryTotals[cat] ?? 0) + val;
    });

    // Blank votes
    document.querySelectorAll(`#table-${tableId} .blank-votes-input`).forEach(input => {
        const cat = input.dataset.category;
        const val = parseInt(input.value) || 0;
        categoryTotals[cat] = (categoryTotals[cat] ?? 0) + val;
    });

    // Null votes
    document.querySelectorAll(`#table-${tableId} .null-votes-input`).forEach(input => {
        const cat = input.dataset.category;
        const val = parseInt(input.value) || 0;
        categoryTotals[cat] = (categoryTotals[cat] ?? 0) + val;
    });

    Object.entries(categoryTotals).forEach(([code, total]) => {
        const el = document.getElementById(`total-${code}-${tableId}`);
        if (el) el.textContent = total;
    });

    Object.entries(categoryTotals).forEach(([code, total]) => {
        const el = document.getElementById(`total-${code}-${tableId}`);
        if (el) el.textContent = total;
    });

    const counts = Object.values(categoryTotals);
    const grand  = counts.length > 0 ? counts[0] : 0;
    const totalEl = document.getElementById(`total-${tableId}`);
    if (totalEl) totalEl.textContent = grand;

    // ── Update footer summary row ──
    let footerValid = 0, footerBlank = 0, footerNull = 0;
    document.querySelectorAll(`#table-${tableId} .vote-input`).forEach(i => {
        footerValid += parseInt(i.value) || 0;
    });
    document.querySelectorAll(`#table-${tableId} .blank-votes-input`).forEach(i => {
        footerBlank += parseInt(i.value) || 0;
    });
    document.querySelectorAll(`#table-${tableId} .null-votes-input`).forEach(i => {
        footerNull  += parseInt(i.value) || 0;
    });
    const fv = document.getElementById(`footer-valid-${tableId}`);
    const fb = document.getElementById(`footer-blank-${tableId}`);
    const fn = document.getElementById(`footer-null-${tableId}`);
    if (fv) fv.textContent = footerValid;
    if (fb) fb.textContent = footerBlank;
    if (fn) fn.textContent = footerNull;
};

// ─── VOTE INPUT LISTENERS ────────────────────────────────────────────────────

window.initVoteListeners = function() {
    // Numeric inputs — live total update
    document.querySelectorAll('.vote-input').forEach(input => {
        input.addEventListener('input', () => {
            window.updateTableTotals(input.dataset.table);
        });
        // Tab / Enter navigation within a table
        input.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === 'Tab') {
                const all = Array.from(
                    document.querySelectorAll(`#table-${input.dataset.table} .vote-input`)
                );
                const idx = all.indexOf(input);
                if (idx < all.length - 1) {
                    e.preventDefault();
                    all[idx + 1].focus();
                }
            }
        });
    });

    // ── Action buttons ──
    document.querySelectorAll('.save-table').forEach(btn => {
        btn.addEventListener('click', () => saveTable(parseInt(btn.dataset.tableId)));
    });

    document.querySelectorAll('.review-table').forEach(btn => {
        btn.addEventListener('click', () => reviewTable(parseInt(btn.dataset.tableId)));
    });

    document.querySelectorAll('.correct-table').forEach(btn => {
        btn.addEventListener('click', () => correctTable(parseInt(btn.dataset.tableId)));
    });

    document.querySelectorAll('.validate-table').forEach(btn => {
        btn.addEventListener('click', () =>
            validateTable(parseInt(btn.dataset.tableId), btn.dataset.action ?? 'validate')
        );
    });

    document.querySelectorAll('.close-table').forEach(btn => {
        btn.addEventListener('click', () => closeTable(parseInt(btn.dataset.tableId)));
    });

    document.querySelectorAll('.reopen-table').forEach(btn => {
        btn.addEventListener('click', () => reopenTable(parseInt(btn.dataset.tableId)));
    });

    // ── Blank / null vote inputs — live total update ──
    document.querySelectorAll('.blank-votes-input, .null-votes-input').forEach(input => {
        input.addEventListener('input', () => {
            window.updateTableTotals(input.dataset.table);
        });
    });

    // ── Observe-checkbox counter ──
    // Only allow checking rows that have a saved vote (data-vote-id != "")
    document.querySelectorAll('.observe-checkbox').forEach(cb => {
        cb.addEventListener('change', () => {
            // Guard: if this vote hasn't been saved yet, block the check
            if (cb.checked && !cb.dataset.voteId) {
                cb.checked = false;
                showError('Guarde los votos antes de marcarlos como observados');
                return;
            }

            const tableId    = cb.dataset.table;
            // Only count boxes with a real vote ID
            const allChecked = Array.from(
                document.querySelectorAll(`#table-${tableId} .observe-checkbox:checked`)
            ).filter(c => c.dataset.voteId);

            const countEl = document.getElementById(`selected-count-${tableId}`);
            if (countEl) countEl.textContent = allChecked.length;

            // Per-category counts
            const cats = {};
            allChecked.forEach(c => {
                cats[c.dataset.category] = (cats[c.dataset.category] ?? 0) + 1;
            });
            // Reset all category counters first
            document.querySelectorAll(`#table-${tableId} [id^="selected-"]`).forEach(el => {
                if (el.id !== `selected-count-${tableId}`) el.textContent = '';
            });
            Object.entries(cats).forEach(([code, n]) => {
                const el = document.getElementById(`selected-${code}-${tableId}`);
                if (el) el.textContent = `${n} ${code}`;
            });
        });
    });

    // Global Ctrl+S shortcut
    document.addEventListener('keydown', e => {
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            document.getElementById('saveAllBtn')?.click();
        }
    });
};

// ─── UTILITY ─────────────────────────────────────────────────────────────────

function escHtml(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
</script>
<?php /**PATH D:\_Mine\sistema_electoral\resources\views/voting-table-votes/scripts/votes-table-js.blade.php ENDPATH**/ ?>