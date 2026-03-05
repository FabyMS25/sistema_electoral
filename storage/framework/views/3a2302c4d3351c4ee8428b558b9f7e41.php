
<script>
window.initViewToggle = function() {
    console.log('Inicializando toggle de vistas...');

    document.querySelectorAll('.view-toggle').forEach(btn => {
        btn.addEventListener('click', function() {
            const tableId = this.dataset.table;
            const view = this.dataset.view;

            // Remover clase active de todos los botones de esta mesa
            document.querySelectorAll(`.view-toggle[data-table="${tableId}"]`).forEach(b => {
                b.classList.remove('active');
            });

            // Agregar clase active al botón clickeado
            this.classList.add('active');

            // Ocultar todas las vistas de esta mesa
            document.querySelector(`.view-both-${tableId}`).style.display = 'none';

            // En lugar de primary/secondary fijos, manejamos vistas por categoría
            // Como ahora es completamente dinámico, podemos tener múltiples vistas
            // Por ahora, mantenemos both como la vista principal
            if (view === 'both') {
                document.querySelector(`.view-both-${tableId}`).style.display = 'block';
            }
        });
    });
};
</script>
<?php /**PATH D:\_Mine\corporate\resources\views/voting-table-votes/scripts/view-toggle-js.blade.php ENDPATH**/ ?>