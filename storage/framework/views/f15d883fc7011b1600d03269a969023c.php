


<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('translation.list-voting-tables'); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <link href="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.css')); ?>" rel="stylesheet" />
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
        .pagination {
            display: flex;
            padding-left: 0;
            list-style: none;
            gap: 5px;
        }
        .page-link {
            position: relative;
            display: block;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            color: #0ab39c;
            background-color: #fff;
            border: 1px solid #e9e9ef;
            border-radius: 0.25rem;
            text-decoration: none;
        }
        .page-item.active .page-link {
            background-color: #0ab39c;
            border-color: #0ab39c;
            color: #fff;
        }
        .badge-count {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?>
            Mesas
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?>
            Gestión de Mesas Electorales
        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="ri-table-line me-1"></i>
                        Administración de Mesas de Votación
                    </h4>
                </div>
                
                <div class="card-body">
                    <?php echo $__env->make('components.alerts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    
                    <!-- Stats Cards -->
                    <?php echo $__env->make('voting-tables.partials.stats-cards', ['votingTables' => $votingTables], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                    <div id="votingTableList">
                        <!-- Barra de acciones -->
                        <?php echo $__env->make('voting-tables.partials.actions-bar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                        <!-- Tabla de mesas -->
                        <?php echo $__env->make('voting-tables.partials.table', ['votingTables' => $votingTables], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        
                        <!-- Paginación -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Mostrando <?php echo e($votingTables->firstItem()); ?> a <?php echo e($votingTables->lastItem()); ?> de <?php echo e($votingTables->total()); ?> resultados
                            </div>
                            <div class="pagination-wrap">
                                <?php echo e($votingTables->onEachSide(1)->links('pagination::bootstrap-5')); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales -->
    <?php echo $__env->make('voting-tables.partials.modal-delete', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('voting-tables.partials.modal-import', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if(session('import_errors')): ?>
        <?php echo $__env->make('voting-tables.partials.modal-import-errors', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <script src="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.js')); ?>"></script>
    <?php echo $__env->make('voting-tables.scripts.voting-table-js', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\_Mine\corporate\resources\views/voting-tables/index.blade.php ENDPATH**/ ?>