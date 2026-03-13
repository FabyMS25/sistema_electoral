{{-- resources/views/voting-table-votes/scripts/votes-table-js.blade.php --}}
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
        btn.disabled  = true;
    } else {
        btn.innerHTML = btn.dataset.originalHtml ?? btn.innerHTML;
        btn.disabled  = false;
    }
}

function escHtml(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function collectVotes(tableId) {
    const votes = {};
    document.querySelectorAll(`#table-${tableId} .vote-input`).forEach(input => {
        votes[input.dataset.candidate] = parseInt(input.value) || 0;
    });
    return votes;
}

function collectSpecialVotes(tableId, type) { // type = 'blank' | 'null'
    const result = {};
    document.querySelectorAll(`#table-${tableId} .${type}-votes-input`).forEach(input => {
        result[input.dataset.category] = parseInt(input.value) || 0;
    });
    return result;
}
function collectBallotData(tableId) {
    const received = document.getElementById(`received-${tableId}`);
    const leftover = document.getElementById(`leftover-${tableId}`);
    const spoiled  = document.getElementById(`spoiled-${tableId}`);

    const data = {};
    if (leftover) data.ballots_leftover = parseInt(leftover.value) || 0;
    if (spoiled)  data.ballots_spoiled  = parseInt(spoiled.value)  || 0;
    if (received && received.value.trim() !== '') {
        data.ballots_received = parseInt(received.value) || 0;
    }
    return data;
}
function updateBallotBalance(tableId, urnTotal) {
    const balanceEl = document.getElementById(`ballot-balance-${tableId}`);
    if (!balanceEl) return;

    const leftover = parseInt(document.getElementById(`leftover-${tableId}`)?.value) || 0;
    const spoiled  = parseInt(document.getElementById(`spoiled-${tableId}`)?.value)  || 0;
    const received = parseInt(document.getElementById(`received-${tableId}`)?.value) || 0;

    if (received === 0) {
        balanceEl.innerHTML = `<small class="text-muted" style="font-size:0.65rem;">
            <i class="ri-information-line"></i> Ingrese papeletas recibidas para verificar
        </small>`;
        return;
    }

    const accounted = urnTotal + leftover + spoiled;
    const diff      = accounted - received;

    if (diff === 0) {
        balanceEl.innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:0.65rem;">
            <i class="ri-checkbox-circle-line me-1"></i>Papeletas cuadran
        </span>`;
    } else {
        balanceEl.innerHTML = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size:0.65rem;"
            title="${accounted} contados vs ${received} recibidas">
            <i class="ri-alert-line me-1"></i>No cuadran (${diff > 0 ? '+' : ''}${diff})
        </span>`;
    }
}

function refreshTableTotals(tableId, categoryTotals) {
    Object.entries(categoryTotals).forEach(([code, total]) => {
        const el = document.getElementById(`total-${code}-${tableId}`);
        if (el) el.textContent = total;
    });
    const counts  = Object.values(categoryTotals);
    const grand   = counts.length > 0 ? counts[0] : 0;
    const totalEl = document.getElementById(`total-${tableId}`);
    if (totalEl) totalEl.textContent = grand;
}

function setTableStatusClass(tableId, newStatus) {
    const card = document.getElementById(`table-${tableId}`);
    if (!card) return;
    card.className = card.className.replace(/\bstatus-\S+/g, '');
    card.classList.add(`status-${newStatus}`);
}
window.updateTableTotals = function(tableId) {
    const catValid = {};
    const catBlank = {};
    const catNull  = {};

    document.querySelectorAll(`#table-${tableId} .vote-input`).forEach(input => {
        const cat = input.dataset.category;
        catValid[cat] = (catValid[cat] ?? 0) + (parseInt(input.value) || 0);
    });
    document.querySelectorAll(`#table-${tableId} .blank-votes-input`).forEach(input => {
        const cat = input.dataset.category;
        catBlank[cat] = (catBlank[cat] ?? 0) + (parseInt(input.value) || 0);
    });
    document.querySelectorAll(`#table-${tableId} .null-votes-input`).forEach(input => {
        const cat = input.dataset.category;
        catNull[cat] = (catNull[cat] ?? 0) + (parseInt(input.value) || 0);
    });

    const allCats = new Set([
        ...Object.keys(catValid),
        ...Object.keys(catBlank),
        ...Object.keys(catNull),
    ]);

    let firstCatTotal = 0;
    allCats.forEach(code => {
        const total = (catValid[code] ?? 0) + (catBlank[code] ?? 0) + (catNull[code] ?? 0);
        const el    = document.getElementById(`total-${code}-${tableId}`);
        if (el) el.textContent = total;
        if (firstCatTotal === 0) firstCatTotal = total;
    });

    const totalEl = document.getElementById(`total-${tableId}`);
    if (totalEl) totalEl.textContent = firstCatTotal;
    let footerValid = 0, footerBlank = 0, footerNull = 0;
    Object.values(catValid).forEach(v => { footerValid += v; });
    Object.values(catBlank).forEach(v => { footerBlank += v; });
    Object.values(catNull).forEach(v  => { footerNull  += v; });
    const fv = document.getElementById(`footer-valid-${tableId}`);
    const fb = document.getElementById(`footer-blank-${tableId}`);
    const fn = document.getElementById(`footer-null-${tableId}`);
    if (fv) fv.textContent = footerValid;
    if (fb) fb.textContent = footerBlank;
    if (fn) fn.textContent = footerNull;
    const urnEl = document.getElementById(`urn-count-${tableId}`);
    if (urnEl) urnEl.textContent = firstCatTotal.toLocaleString();
    const tableCard      = document.getElementById(`table-${tableId}`);
    const expectedVoters = tableCard
        ? parseInt(tableCard.dataset.expectedVoters || '0')
        : 0;
    const participEl = document.getElementById(`participation-${tableId}`);
    if (participEl && expectedVoters > 0) {
        const pct = Math.round((firstCatTotal / expectedVoters) * 1000) / 10;
        participEl.textContent = pct + '%';
        participEl.className = participEl.className
            .replace(/text-(success|warning|secondary|danger)/g, '');
        participEl.classList.add(
            pct >= 75 ? 'text-success' : pct >= 50 ? 'text-warning' : 'text-secondary'
        );
    }
    updateBallotBalance(tableId, firstCatTotal);
};
async function saveTable(tableId, closeAfter = false) {
    const btn = document.querySelector(`[data-table-id="${tableId}"].save-table`);
    setButtonLoading(btn, true);

    try {
        const votes      = collectVotes(tableId);
        const blankVotes = collectSpecialVotes(tableId, 'blank');
        const nullVotes  = collectSpecialVotes(tableId, 'null');
        const ballots    = collectBallotData(tableId);

        const body = {
            voting_table_id:  tableId,
            election_type_id: window.electionTypeId,
            votes,
            blank_votes: Object.keys(blankVotes).length ? blankVotes : undefined,
            null_votes:  Object.keys(nullVotes).length  ? nullVotes  : undefined,
            close:       closeAfter,
            ...ballots,
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
            const urnEl = document.getElementById(`urn-count-${tableId}`);
            if (urnEl && data.total_voters !== undefined) {
                urnEl.textContent = Number(data.total_voters).toLocaleString();
            }
            showToast('success', data.message);
            document.dispatchEvent(new CustomEvent('tableSaved', { detail: { tableId: String(tableId) } }));
        } else {
            const msg = data.errors
                ? Object.values(data.errors).flat().join('\n')
                : (data.message ?? 'Error al guardar votos');
            showError(msg);
        }
    } catch (err) {
        console.error('saveTable error', err);
        showError('Error de conexión al guardar votos');
    } finally {
        setButtonLoading(btn, false);
    }
}

async function reviewTable(tableId) {
    let votes = [];
    try {
        const r = await fetch(
            `/voting-table-votes/${tableId}/votes?election_type_id=${window.electionTypeId}`,
            { headers: { 'Accept': 'application/json' } }
        );
        votes = await r.json();
    } catch (e) {
        showError('No se pudieron cargar los votos de la mesa');
        return;
    }

    const byCategory = {};
    votes.forEach(v => {
        const cat = v.category_code || 'General';
        if (!byCategory[cat]) byCategory[cat] = [];
        byCategory[cat].push(v);
    });

    let rows = '';
    Object.entries(byCategory).forEach(([cat, catVotes]) => {
        rows += `<div class="mb-2">
            <div class="text-muted small fw-bold border-bottom pb-1 mb-1">${escHtml(cat)}</div>`;
        catVotes.forEach(v => {
            const isObs = v.vote_status === 'observed';
            rows += `
            <div class="form-check mb-1 ${isObs ? 'text-warning' : ''}">
                <input class="form-check-input review-observe-cb"
                       type="checkbox" value="${v.id}" id="rev_${v.id}"
                       ${isObs ? 'checked disabled' : ''}>
                <label class="form-check-label d-flex justify-content-between" for="rev_${v.id}">
                    <span>
                        <strong>${escHtml(v.candidate_name)}</strong>
                        <small class="text-muted ms-1">${escHtml(v.candidate_party)}</small>
                        ${isObs ? '<span class="badge bg-warning text-dark ms-1">Ya observado</span>' : ''}
                    </span>
                    <span class="badge bg-secondary">${v.quantity} votos</span>
                </label>
            </div>`;
        });
        rows += `</div>`;
    });

    const { value: formValues } = await Swal.fire({
        title: `Revisión — Mesa ${tableId}`,
        width: 620,
        html: `
            <p class="text-start text-muted small mb-2">
                Marque los candidatos cuyos votos desea observar.
                Si todo está correcto, deje sin marcar y confirme.
            </p>
            <div class="text-start border rounded p-2 mb-3" style="max-height:300px;overflow-y:auto;">
                ${rows || '<p class="text-muted text-center py-3">No hay votos registrados</p>'}
            </div>
            <div class="text-start">
                <label class="form-label fw-bold">Notas (opcional):</label>
                <textarea id="review-notes" class="form-control" rows="2"
                          placeholder="Describa lo observado…"></textarea>
            </div>`,
        showCancelButton: true,
        confirmButtonText: 'Confirmar revisión',
        cancelButtonText: 'Cancelar',
        preConfirm: () => ({
            observed_vote_ids: Array.from(
                document.querySelectorAll('.review-observe-cb:checked:not(:disabled)')
            ).map(cb => parseInt(cb.value)),
            observation_notes: document.getElementById('review-notes').value.trim(),
        }),
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
                election_type_id:  window.electionTypeId,
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

async function correctTable(tableId) {
    let votes = [];
    try {
        const r = await fetch(
            `/voting-table-votes/${tableId}/votes?election_type_id=${window.electionTypeId}`,
            { headers: { 'Accept': 'application/json' } }
        );
        votes = await r.json();
    } catch (e) {
        showError('No se pudieron cargar los votos de la mesa');
        return;
    }

    const byCategory = {};
    votes.forEach(v => {
        const cat = v.category_code || 'General';
        if (!byCategory[cat]) byCategory[cat] = [];
        byCategory[cat].push(v);
    });

    const blankByCategory = {};
    document.querySelectorAll(`#table-${tableId} .blank-votes-input`)
        .forEach(inp => { blankByCategory[inp.dataset.category] = parseInt(inp.value) || 0; });
    const nullByCategory = {};
    document.querySelectorAll(`#table-${tableId} .null-votes-input`)
        .forEach(inp => { nullByCategory[inp.dataset.category] = parseInt(inp.value) || 0; });

    let rows = '';
    Object.entries(byCategory).forEach(([cat, catVotes]) => {
        rows += `<div class="mb-3">
            <div class="fw-bold small text-muted border-bottom pb-1 mb-2">${escHtml(cat)}</div>`;
        catVotes.forEach(v => {
            const isObs = v.vote_status === 'observed';
            rows += `
            <div class="row align-items-center mb-1 ${isObs ? 'bg-warning bg-opacity-10 rounded px-1' : ''}">
                <div class="col-7 small">
                    ${isObs ? '<i class="ri-alert-line text-warning me-1"></i>' : ''}
                    <strong>${escHtml(v.candidate_name)}</strong>
                    <div class="text-muted">${escHtml(v.candidate_party)}</div>
                </div>
                <div class="col-5">
                    <input type="number" class="form-control form-control-sm correction-input"
                           data-vote-id="${v.id}" data-category="${escHtml(cat)}"
                           value="${v.quantity}" min="0">
                </div>
            </div>`;
        });
        rows += `
            <div class="row align-items-center mb-1 mt-2">
                <div class="col-7 small text-muted">
                    <i class="ri-subtract-line me-1"></i>Votos en Blanco
                </div>
                <div class="col-5">
                    <input type="number" class="form-control form-control-sm blank-correction-input"
                           data-category="${escHtml(cat)}"
                           value="${blankByCategory[cat] ?? 0}" min="0">
                </div>
            </div>
            <div class="row align-items-center mb-2">
                <div class="col-7 small text-muted">
                    <i class="ri-close-line me-1"></i>Votos Nulos
                </div>
                <div class="col-5">
                    <input type="number" class="form-control form-control-sm null-correction-input"
                           data-category="${escHtml(cat)}"
                           value="${nullByCategory[cat] ?? 0}" min="0">
                </div>
            </div>
        </div>`;
    });

    const { value: formValues } = await Swal.fire({
        title: `Corrección — Mesa ${tableId}`,
        width: 640,
        html: `
            <div class="text-start border rounded p-2 mb-3"
                 style="max-height:340px;overflow-y:auto;">
                ${rows || '<p class="text-muted text-center py-3">No hay votos registrados</p>'}
            </div>
            <div class="text-start">
                <label class="fw-bold form-label">
                    Motivo de la corrección <span class="text-danger">*</span>
                </label>
                <textarea id="correction-notes" class="form-control" rows="2"
                          placeholder="Describa el motivo…"></textarea>
            </div>`,
        showCancelButton: true,
        confirmButtonText: 'Aplicar correcciones',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const notes = document.getElementById('correction-notes').value.trim();
            if (!notes) {
                Swal.showValidationMessage('El motivo de la corrección es obligatorio');
                return false;
            }
            const corrections = {};
            document.querySelectorAll('.correction-input').forEach(inp => {
                corrections[inp.dataset.voteId] = parseInt(inp.value) || 0;
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
                election_type_id: window.electionTypeId,
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

async function validateTable(tableId, action) {
    const actionMeta = {
        validate: {
            icon:        'question',
            label:       'Validar votos',
            confirmText: 'Los votos quedarán validados y la mesa pasará a En Escrutinio.',
            btnColor:    '#17a2b8',
        },
        escrutar: {
            icon:        'warning',
            label:       'Escrutar mesa',
            confirmText: 'Se cerrará el conteo definitivamente. Esta acción no se puede revertir.',
            btnColor:    '#0ab39c',
        },
        reject: {
            icon:        'warning',
            label:       'Rechazar mesa',
            confirmText: 'La mesa volverá a estado Observada para corrección.',
            btnColor:    '#f06548',
        },
    };

    const meta = actionMeta[action] ?? actionMeta.validate;

    const { value: notes, isConfirmed } = await Swal.fire({
        title:              meta.label,
        text:               meta.confirmText,
        icon:               meta.icon,
        input:              'textarea',
        inputLabel:         action === 'reject' ? 'Motivo (obligatorio)' : 'Notas (opcional)',
        inputPlaceholder:   'Agregue notas…',
        showCancelButton:   true,
        confirmButtonText:  `Sí, ${meta.label.toLowerCase()}`,
        confirmButtonColor: meta.btnColor,
        cancelButtonText:   'Cancelar',
        preConfirm: (val) => {
            if (action === 'reject' && !val?.trim()) {
                Swal.showValidationMessage('El motivo es obligatorio para rechazar');
                return false;
            }
            return val;
        },
    });

    if (!isConfirmed) return;

    const btn = document.querySelector(
        `[data-table-id="${tableId}"].validate-table[data-action="${action}"]`
    );
    setButtonLoading(btn, true);

    try {
        const resp = await fetch(`/voting-table-votes/${tableId}/validate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN':  csrfToken(),
                'Accept':        'application/json',
            },
            body: JSON.stringify({
                election_type_id: window.electionTypeId,
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

async function reopenTable(tableId) {
    const result = await Swal.fire({
        title: '¿Reabrir mesa?',
        text: 'La mesa volverá al estado Votación y podrá editarse.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, reabrir',
        cancelButtonText: 'Cancelar',
    });

    if (!result.isConfirmed) return;

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
            body: JSON.stringify({ election_type_id: window.electionTypeId }),
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

window.saveAllTables = async function() {
    const saveBtns = document.querySelectorAll('.save-table');
    if (saveBtns.length === 0) {
        showToast('info', 'No hay mesas editables en esta página');
        return;
    }

    const result = await Swal.fire({
        title: `¿Guardar ${saveBtns.length} mesa(s)?`,
        text: 'Se guardarán todos los votos ingresados en esta página.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Guardar todo',
        cancelButtonText: 'Cancelar',
    });

    if (!result.isConfirmed) return;

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
function bindBallotInputs() {
    document.querySelectorAll(
        '.ballot-leftover-input, .ballot-spoiled-input, .ballot-received-input'
    ).forEach(input => {
        input.addEventListener('focus', function () { this.select(); });
        input.addEventListener('input', function () {
            const tableId = this.dataset.table;
            if (!tableId) return;
            const urnEl  = document.getElementById(`urn-count-${tableId}`);
            const urnVal = parseInt((urnEl?.textContent ?? '0').replace(/,/g, '')) || 0;
            updateBallotBalance(tableId, urnVal);
            if (window.pendingTables) window.pendingTables.add(String(tableId));
        });
    });
}
window.initVoteListeners = function() {
    document.querySelectorAll('.vote-input').forEach(input => {
        input.addEventListener('focus', function () { this.select(); });
        input.addEventListener('input', () => window.updateTableTotals(input.dataset.table));
        input.addEventListener('keydown', e => {
            if (e.key === 'Enter' || (e.key === 'Tab' && !e.shiftKey)) {
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

    document.querySelectorAll('.blank-votes-input, .null-votes-input').forEach(input => {
        input.addEventListener('focus', function () { this.select(); });
        input.addEventListener('input', () => window.updateTableTotals(input.dataset.table));
    });

    document.querySelectorAll('.save-table').forEach(btn =>
        btn.addEventListener('click', () => saveTable(parseInt(btn.dataset.tableId)))
    );
    document.querySelectorAll('.review-table').forEach(btn =>
        btn.addEventListener('click', () => reviewTable(parseInt(btn.dataset.tableId)))
    );
    document.querySelectorAll('.correct-table').forEach(btn =>
        btn.addEventListener('click', () => correctTable(parseInt(btn.dataset.tableId)))
    );
    document.querySelectorAll('.validate-table').forEach(btn =>
        btn.addEventListener('click', () =>
            validateTable(parseInt(btn.dataset.tableId), btn.dataset.action ?? 'validate')
        )
    );
    document.querySelectorAll('.reopen-table').forEach(btn =>
        btn.addEventListener('click', () => reopenTable(parseInt(btn.dataset.tableId)))
    );

    document.querySelectorAll('.observe-checkbox').forEach(cb => {
        cb.addEventListener('change', () => {
            if (cb.checked && !cb.dataset.voteId) {
                cb.checked = false;
                showError('Guarde los votos antes de marcarlos como observados');
                return;
            }
            const tableId    = cb.dataset.table;
            const allChecked = Array.from(
                document.querySelectorAll(`#table-${tableId} .observe-checkbox:checked`)
            ).filter(c => c.dataset.voteId);

            const countEl = document.getElementById(`selected-count-${tableId}`);
            if (countEl) countEl.textContent = allChecked.length;

            const cats = {};
            allChecked.forEach(c => {
                cats[c.dataset.category] = (cats[c.dataset.category] ?? 0) + 1;
            });
            document.querySelectorAll(`#table-${tableId} [id^="selected-"]`).forEach(el => {
                if (el.id !== `selected-count-${tableId}`) el.textContent = '';
            });
            Object.entries(cats).forEach(([code, n]) => {
                const el = document.getElementById(`selected-${code}-${tableId}`);
                if (el) el.textContent = `${n} ${code}`;
            });
        });
    });
    bindBallotInputs();
};
</script>
