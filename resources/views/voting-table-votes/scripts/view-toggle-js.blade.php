<script>
//view-toggle-js.blade.php
function initViewToggle() {
    document.querySelectorAll('.view-toggle').forEach(btn => {
        btn.addEventListener('click', function() {
            const tableId = this.dataset.table;
            const view = this.dataset.view;
            document.querySelectorAll(`.view-toggle[data-table="${tableId}"]`).forEach(b => {
                b.classList.remove('active');
            });
            this.classList.add('active');
            document.querySelector(`.view-both-${tableId}`).style.display = 'none';
            document.querySelector(`.view-alcaldes-${tableId}`).style.display = 'none';
            document.querySelector(`.view-concejales-${tableId}`).style.display = 'none';
            if (view === 'both') {
                document.querySelector(`.view-both-${tableId}`).style.display = 'block';
            } else if (view === 'alcaldes') {
                document.querySelector(`.view-alcaldes-${tableId}`).style.display = 'block';
            } else if (view === 'concejales') {
                document.querySelector(`.view-concejales-${tableId}`).style.display = 'block';
            }
            localStorage.setItem(`table-view-${tableId}`, view);
        });
    });
}

function loadSavedView(tableId) {
    const savedView = localStorage.getItem(`table-view-${tableId}`);
    if (savedView) {
        const btn = document.querySelector(`.view-toggle[data-table="${tableId}"][data-view="${savedView}"]`);
        if (btn) {
            btn.click();
        }
    }
}
function updateSelectedCount(tableId) {
    const checkboxes = document.querySelectorAll(`#table-${tableId} .observe-checkbox:checked`);
    const count = checkboxes.length;
    let alcaldesCount = 0;
    let concejalesCount = 0;
    checkboxes.forEach(cb => {
        if (cb.dataset.category === 'alcalde') {
            alcaldesCount++;
        } else {
            concejalesCount++;
        }
    });
    const countElement = document.getElementById(`selected-count-${tableId}`);
    if (countElement) {
        countElement.textContent = count;
        countElement.className = count > 0 ? 'fw-bold text-warning' : 'text-muted';
    }
    const alcaldesElement = document.getElementById(`selected-alcaldes-${tableId}`);
    if (alcaldesElement) {
        alcaldesElement.textContent = alcaldesCount + ' Alcaldes';
        alcaldesElement.className = alcaldesCount > 0 ? 'badge bg-primary' : 'badge bg-secondary';
    }
    const concejalesElement = document.getElementById(`selected-concejales-${tableId}`);
    if (concejalesElement) {
        concejalesElement.textContent = concejalesCount + ' Concejales';
        concejalesElement.className = concejalesCount > 0 ? 'badge bg-success' : 'badge bg-secondary';
    }
    const createBtn = document.getElementById(`create-observation-${tableId}`);
    if (createBtn) {
        if (count > 0) {
            createBtn.disabled = false;
            createBtn.classList.remove('btn-secondary');
            createBtn.classList.add('btn-warning');
        } else {
            createBtn.disabled = true;
            createBtn.classList.remove('btn-warning');
            createBtn.classList.add('btn-secondary');
        }
    }
    if (!window.selectedObservations) window.selectedObservations = {};
    window.selectedObservations[tableId] = Array.from(checkboxes).map(cb => ({
        candidateId: cb.dataset.candidate,
        candidateName: cb.dataset.candidateName,
        category: cb.dataset.category
    }));
}

function initObservationCheckboxes() {
    document.querySelectorAll('.observe-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const tableId = this.dataset.table;
            updateSelectedCount(tableId);
        });
    });
    document.querySelectorAll('.table-card').forEach(card => {
        const tableId = card.dataset.tableId;
        updateSelectedCount(tableId);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    initViewToggle();
    initObservationCheckboxes();
    document.querySelectorAll('.table-card').forEach(card => {
        loadSavedView(card.dataset.tableId);
    });
});
</script>
