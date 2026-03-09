


<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('translation.list-institutions'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <link href="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.css')); ?>" rel="stylesheet" />
    <link href="<?php echo e(URL::asset('build/libs/choices.js/public/assets/styles/choices.min.css')); ?>" rel="stylesheet" />
    <style>
        .stats-card {
            transition: transform 0.2s;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .stats-toggle {
            cursor: pointer;
            user-select: none;
        }
        .stats-toggle i {
            transition: transform 0.3s ease;
        }
        .stats-toggle.collapsed i {
            transform: rotate(-90deg);
        }
        #statsContainer {
            transition: all 0.3s ease;
            overflow: hidden;
        }
        #statsContainer.collapsed {
            display: none;
        }
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        .pagination-info {
            font-size: 0.875rem;
            color: #6c757d;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?>
            Recintos
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?>
            Gestión de Recintos Electorales
        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="ri-building-line me-1"></i>
                        Listas de Recintos de Votación
                    </h4>
                    <div class="stats-toggle" onclick="toggleStats()" id="statsToggle">
                        <span class="badge bg-light text-dark p-1">
                            <i class="ri-arrow-down-s-line me-1"></i>
                            Estadísticas
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <?php echo $__env->make('components.alerts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <div id="statsContainer" class="mb-2">
                        <?php echo $__env->make('institutions.partials.stats-cards', ['institutions' => $institutions], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>
                    <?php echo $__env->make('institutions.partials.actions-bar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <div id="institutionList">
                        <?php echo $__env->make('institutions.partials.table', ['institutions' => $institutions], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <div class="d-flex justify-content-between align-items-center">
                            <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href=this.value">
                                <option value="<?php echo e(route('institutions.index', ['per_page' => 20] + request()->except('per_page', 'page'))); ?>" <?php echo e(request('per_page', 20) == 20 ? 'selected' : ''); ?>>20</option>
                                <option value="<?php echo e(route('institutions.index', ['per_page' => 50] + request()->except('per_page', 'page'))); ?>" <?php echo e(request('per_page') == 50 ? 'selected' : ''); ?>>50</option>
                                <option value="<?php echo e(route('institutions.index', ['per_page' => 100] + request()->except('per_page', 'page'))); ?>" <?php echo e(request('per_page') == 100 ? 'selected' : ''); ?>>100</option>
                                <option value="<?php echo e(route('institutions.index', ['per_page' => 200] + request()->except('per_page', 'page'))); ?>" <?php echo e(request('per_page') == 200 ? 'selected' : ''); ?>>200</option>
                            </select>
                            <div class="pagination-info">
                                Mostrando <?php echo e($institutions->firstItem()); ?> a <?php echo e($institutions->lastItem()); ?> de <?php echo e($institutions->total()); ?> resultados
                            </div>
                            <div class="pagination-wrap">
                                <?php echo e($institutions->onEachSide(1)->appends(request()->query())->links('pagination::bootstrap-5')); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales -->
    <?php echo $__env->make('institutions.partials.modal-delete', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('institutions.partials.modal-import', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php if(session('import_errors')): ?>
        <?php echo $__env->make('institutions.partials.modal-import-errors', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.js')); ?>"></script>
    <?php echo $__env->make('institutions.scripts.institution-js', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <script>
        function toggleStats() {
            const statsContainer = document.getElementById('statsContainer');
            const toggleBtn = document.getElementById('statsToggle');
            const icon = toggleBtn.querySelector('i');
            statsContainer.classList.toggle('collapsed');
            toggleBtn.classList.toggle('collapsed');
            if (statsContainer.classList.contains('collapsed')) {
                icon.classList.remove('ri-arrow-down-s-line');
                icon.classList.add('ri-arrow-right-s-line');
            } else {
                icon.classList.remove('ri-arrow-right-s-line');
                icon.classList.add('ri-arrow-down-s-line');
            }
            localStorage.setItem('showStats', !statsContainer.classList.contains('collapsed'));
        }
        document.addEventListener('DOMContentLoaded', function() {
            const showStats = localStorage.getItem('showStats');
            const statsContainer = document.getElementById('statsContainer');
            const toggleBtn = document.getElementById('statsToggle');

            if (statsContainer && toggleBtn && showStats === 'false') {
                const icon = toggleBtn.querySelector('i');
                statsContainer.classList.add('collapsed');
                toggleBtn.classList.add('collapsed');
                icon.classList.remove('ri-arrow-down-s-line');
                icon.classList.add('ri-arrow-right-s-line');
            }
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\_Mine\sistema_electoral\resources\views/institutions/index.blade.php ENDPATH**/ ?>