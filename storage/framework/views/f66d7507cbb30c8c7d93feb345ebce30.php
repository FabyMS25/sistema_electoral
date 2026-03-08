<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('translation.list-candidates'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <link href="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.css')); ?>" rel="stylesheet" />
    <link href="<?php echo e(URL::asset('build/libs/choices.js/public/assets/styles/choices.min.css')); ?>" rel="stylesheet" />
    <style>
        .color-preview  { width:30px; height:30px; border-radius:4px; border:1px solid #e9e9ef; display:inline-block; }
        .stats-toggle   { cursor:pointer; user-select:none; }
        .stats-toggle i { transition:transform .3s; }
        .stats-toggle.collapsed i { transform:rotate(-90deg); }
        #statsContainer { transition:all .3s; overflow:hidden; }
        .pagination-info { font-size:.875rem; color:#6c757d; }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?> Tables <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?> Candidatos <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">

                
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="ri-user-star-line me-1"></i> Administración de Candidatos
                    </h4>
                    <div class="stats-toggle" onclick="toggleStats()" id="statsToggle">
                        <span class="badge bg-light text-dark p-1">
                            <i class="ri-arrow-down-s-line me-1"></i> Estadísticas
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <?php echo $__env->make('components.alerts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                    
                    <div id="statsContainer" class="mb-2">
                        <?php echo $__env->make('candidates.partials.stats-cards', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </div>

                    
                    <?php echo $__env->make('candidates.partials.actions-bar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                    
                    <div id="candidateList">
                        <?php echo $__env->make('candidates.partials.table', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                        
                        <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">

                            
                            <select class="form-select form-select-sm" style="width:auto;"
                                    onchange="window.location.href=this.value">
                                <?php $__currentLoopData = [20, 50, 100, 200]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e(route('candidates.index', ['per_page' => $pp] + request()->except('per_page','page'))); ?>"
                                        <?php echo e(request('per_page', 20) == $pp ? 'selected' : ''); ?>>
                                        <?php echo e($pp); ?> por página
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>

                            <div class="pagination-info">
                                <?php if($candidates instanceof \Illuminate\Pagination\LengthAwarePaginator && $candidates->total() > 0): ?>
                                    Mostrando <?php echo e($candidates->firstItem()); ?>–<?php echo e($candidates->lastItem()); ?>

                                    de <?php echo e($candidates->total()); ?> resultados
                                <?php else: ?>
                                    Sin resultados
                                <?php endif; ?>
                            </div>

                            <div class="pagination-wrap">
                                <?php echo e($candidates->onEachSide(1)->appends(request()->query())->links('pagination::bootstrap-5')); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <?php echo $__env->make('candidates.partials.modal-view', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('candidates.partials.modal-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('candidates.partials.modal-delete', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('candidates.partials.modal-import', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php if(session('import_errors')): ?>
        <?php echo $__env->make('candidates.partials.modal-import-errors', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('build/libs/choices.js/public/assets/scripts/choices.min.js')); ?>"></script>
    <?php echo $__env->make('candidates.scripts.candidates-js', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <script>
    // ── Stats toggle (persisted in localStorage) ──────────────────
    function toggleStats() {
        const container = document.getElementById('statsContainer');
        const btn       = document.getElementById('statsToggle');
        const icon      = btn.querySelector('i');
        const hide      = !container.classList.contains('d-none');

        container.classList.toggle('d-none', hide);
        btn.classList.toggle('collapsed', hide);
        icon.className = hide ? 'ri-arrow-right-s-line me-1' : 'ri-arrow-down-s-line me-1';
        localStorage.setItem('candidateStatsVisible', String(!hide));
    }

    document.addEventListener('DOMContentLoaded', function () {
        const visible = localStorage.getItem('candidateStatsVisible');
        if (visible === 'false') {
            const container = document.getElementById('statsContainer');
            const btn       = document.getElementById('statsToggle');
            if (container && btn) {
                container.classList.add('d-none');
                btn.classList.add('collapsed');
                btn.querySelector('i').className = 'ri-arrow-right-s-line me-1';
            }
        }
    });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\_Mine\sistema_electoral\resources\views/candidates/index.blade.php ENDPATH**/ ?>