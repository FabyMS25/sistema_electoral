<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('translation.dashboards'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="card mb-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-0">Panel de Resultados Electorales</h5>
                <p class="text-muted mb-0">Última actualización: <?php echo e(now()->format('d/m/Y H:i')); ?></p>
            </div>
            <?php if(auth()->guard()->check()): ?>
            <div class="dashboard-controls">
                <button id="toggleDashboardBtn"
                        class="btn <?php echo e($dashboard->is_public ? 'btn-warning' : 'btn-success'); ?>"
                        data-current-status="<?php echo e($dashboard->is_public ? 'true' : 'false'); ?>"
                        data-toggle-url="<?php echo e(route('dashboard.toggle')); ?>">
                    <i class="<?php echo e($dashboard->is_public ? 'ri-lock-unlock-fill' : 'ri-rotate-lock-fill'); ?>"
                       id="toggleIcon"></i>
                    <span id="toggleText">
                        <?php echo e($dashboard->is_public ? 'Deshabilitar Dashboard' : 'Habilitar Dashboard'); ?>

                    </span>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php echo $__env->make('partials.dashboard-content', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <?php echo $__env->yieldContent('dashboard-scripts'); ?>
    <?php if(auth()->guard()->check()): ?>
    <script>
    document.getElementById('toggleDashboardBtn')?.addEventListener('click', function () {
        const btn         = this;
        const url         = btn.dataset.toggleUrl;
        const originalHtml = btn.innerHTML;

        btn.disabled  = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Procesando...';

        fetch(url, {
            method:  'POST',
            headers: {
                'Content-Type':     'application/json',
                'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(data => {
            if (!data.success) throw new Error(data.message ?? 'Error desconocido');

            const nowPublic = data.is_public;
            btn.dataset.currentStatus = nowPublic ? 'true' : 'false';
            btn.className  = 'btn ' + (nowPublic ? 'btn-warning' : 'btn-success');
            btn.innerHTML  = `<i class="${nowPublic ? 'ri-lock-unlock-fill' : 'ri-rotate-lock-fill'}" id="toggleIcon"></i> `
                           + `<span id="toggleText">${nowPublic ? 'Deshabilitar Dashboard' : 'Habilitar Dashboard'}</span>`;
        })
        .catch(err => {
            console.error('Toggle error:', err);
            btn.innerHTML = originalHtml;
            alert('Error al cambiar el estado del dashboard: ' + err.message);
        })
        .finally(() => {
            btn.disabled = false;
        });
    });
    </script>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\_Mine\sistema_electoral\resources\views/index.blade.php ENDPATH**/ ?>