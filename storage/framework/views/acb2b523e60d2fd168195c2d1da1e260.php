<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('translation.dashboards'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <link href="<?php echo e(URL::asset('build/libs/jsvectormap/jsvectormap.min.css')); ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo e(URL::asset('build/libs/swiper/swiper-bundle.min.css')); ?>" rel="stylesheet" type="text/css" />
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title mb-0">Panel de Resultados Electorales</h5>
                <p class="text-muted mb-0">Última actualización: <?php echo e(now()->format('d/m/Y H:i')); ?></p>
            </div>            
            <?php if(Auth::check()): ?>
            <div class="dashboard-controls mb-3">
                <button id="toggleDashboardBtn" class="btn <?php echo e($dashboard->is_public ? 'btn-warning' : 'btn-success'); ?>" 
                        data-current-status="<?php echo e($dashboard->is_public ? 'true' : 'false'); ?>">
                    <i class="<?php echo e($dashboard->is_public ? 'ri-lock-unlock-fill' : 'ri-rotate-lock-fill'); ?>" id="toggleIcon"></i>
                    <span id="toggleText"><?php echo e($dashboard->is_public ? 'Deshabilitar Dashboard' : 'Habilitar Dashboard'); ?></span>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php echo $__env->make('partials.dashboard-content', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>        
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggleDashboardBtn');
    
    if (toggleBtn) {
        const toggleIcon = document.getElementById('toggleIcon');
        const toggleText = document.getElementById('toggleText');
        
        toggleBtn.addEventListener('click', function() {
            const currentStatus = this.dataset.currentStatus === 'true';
            toggleBtn.disabled = true;
            toggleBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

            fetch('<?php echo e(route("toggle-dashboard-visibility")); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const isPublic = data.is_public;
                    toggleBtn.dataset.currentStatus = isPublic ? 'true' : 'false';
                    toggleBtn.className = isPublic 
                        ? 'btn btn-warning' 
                        : 'btn btn-success';
                    
                    toggleBtn.innerHTML = `
                        <i class="fas ${isPublic ? 'fa-eye-slash' : 'fa-eye'}"></i>
                        <span>${isPublic ? 'Deshabilitar Dashboard' : 'Habilitar Dashboard'}</span>
                    `;
                    showAlert('success', data.message || 'Estado del dashboard actualizado correctamente');
                } else {
                    throw new Error(data.message || 'Error al actualizar el dashboard');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Error al actualizar el dashboard: ' + error.message);
                
                toggleBtn.disabled = false;
                toggleBtn.innerHTML = `
                    <i class="fas ${currentStatus ? 'fa-eye-slash' : 'fa-eye'}"></i>
                    <span>${currentStatus ? 'Deshabilitar Dashboard' : 'Habilitar Dashboard'}</span>
                `;
            })
            .finally(() => {
                toggleBtn.disabled = false;
            });
        });
    }
    
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;        
        const alertContainer = document.querySelector('.dashboard-controls') || document.body;
        alertContainer.insertAdjacentHTML('afterbegin', alertHtml);
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 3000);
    }
});
</script>    

<?php echo $__env->yieldContent('dashboard-scripts'); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\_Mine\corporate\resources\views/index.blade.php ENDPATH**/ ?>