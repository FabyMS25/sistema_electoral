<?php $__env->startSection('title', 'Centro de Monitoreo'); ?>
<?php $__env->startSection('css'); ?>
    <link href="<?php echo e(URL::asset('build/libs/swiper/swiper-bundle.min.css')); ?>" rel="stylesheet" type="text/css" />
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="layout-wrapper">
        <nav class="navbar navbar-expand-lg bg-dark" id="navbar">
                <div class="container">
                    <a class="navbar-brand" href="index">
                        <img src="<?php echo e(URL::asset('build/images/logo_elections_large.png')); ?>" class="card-logo card-logo-dark" alt="logo dark"
                            height="50">
                        <img src="<?php echo e(URL::asset('build/images/logo_elections_large.png')); ?>" class="card-logo card-logo-light" alt="logo light"
                            height="50">
                    </a>

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav mx-auto mt-2 mt-lg-0" id="navbar-example">
                            <li class="nav-item">
                                <a class="nav-link active" href="#hero">Inicio</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#reviews">Reviews</a>
                            </li>
                        </ul>
                        <div class="">
                            <?php if(auth()->guard()->check()): ?>
                                <a href="/" class="btn btn-primary">Admin Dashboard</a>
                            <?php else: ?>
                                <a href="<?php echo e(route('login')); ?>" class="btn btn-primary">Ingresar</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <button class="navbar-toggler py-0 fs-20 text-body" type="button" data-bs-toggle="collapse"
                        data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="Toggle navigation">
                        <i class="mdi mdi-menu"></i>
                    </button>

                </div>
        </nav>
        <div class="vertical-overlay" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent.show"></div>
        <div class="row justify-content-center mt-2">
            <div class="col-lg-11">
            <div class="card p-2 pb-0 mb-0">
                <div class='card-header'>
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
            </div>
        </div>
        <footer class="custom-footer bg-dark py-3 position-relative">
            <div class="container">
                <div class="row text-center text-sm-start align-items-center mt-2">
                    <div class="footer-inner">
                        <span>
                            © <?php echo e(date('Y')); ?> Sistema de Procesamiento Electoral
                        </span>
                        <span>
                            Plataforma de análisis y consolidación de datos
                        </span>
                    </div>
                </div>
            </div>
        </footer>
        
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(URL::asset('build/libs/swiper/swiper-bundle.min.js')); ?>"></script>
    
    <?php echo $__env->yieldContent('dashboard-scripts'); ?>

    <?php if(auth()->guard()->check()): ?>
    <script>
    document.getElementById('toggleDashboardBtn')?.addEventListener('click', function () {
        const btn          = this;
        const url          = btn.dataset.toggleUrl;
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
            btn.innerHTML = originalHtml;
            alert('Error: ' + err.message);
        })
        .finally(() => { btn.disabled = false; });
    });
    </script>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master-without-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\_Mine\sistema_electoral\resources\views/landing.blade.php ENDPATH**/ ?>