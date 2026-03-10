
<script>
window.initViewToggle = function() {
    document.querySelectorAll('.view-toggle').forEach(btn => {
        btn.addEventListener('click', function() {
            const tableId = this.dataset.table;
            const view    = this.dataset.view;
            document.querySelectorAll(`.view-toggle[data-table="${tableId}"]`)
                .forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const tableEl = document.querySelector(`#table-${tableId} table`);
            if (!tableEl) return;
            tableEl.classList.remove('hide-primary', 'hide-secondary');
            switch (view) {
                case 'primary':
                    tableEl.classList.add('hide-secondary');
                    break;
                case 'secondary':
                    tableEl.classList.add('hide-primary');
                    break;
            }
        });
    });
};
</script>

<style>
table.hide-primary  .col-primary  { display: none; }
table.hide-secondary .col-secondary { display: none; }
</style>
<?php /**PATH D:\_Mine\sistema_electoral\resources\views/voting-table-votes/scripts/view-toggle-js.blade.php ENDPATH**/ ?>