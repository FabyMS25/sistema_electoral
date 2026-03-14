
<?php
    $importErrors  = session('import_errors', []);
    $importSuccess = session('success');
    $importFail    = session('error');
    $errorRows     = array_values(array_filter($importErrors, fn($e) => !str_starts_with($e, '⏭️')));
    $skippedRows   = array_values(array_filter($importErrors, fn($e) =>  str_starts_with($e, '⏭️')));
?>

<?php if(count($importErrors) > 0): ?>
<div class="modal fade" id="importErrorsModal" tabindex="-1"
     aria-labelledby="importErrorsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header text-white <?php echo e(count($errorRows) > 0 ? 'bg-danger' : 'bg-warning'); ?>">
                <h5 class="modal-title" id="importErrorsModalLabel">
                    <i class="ri-file-warning-line me-1"></i>
                    Resultado de la Importación
                </h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">

                
                <?php if($importSuccess): ?>
                <div class="alert alert-success py-2 d-flex align-items-center gap-2 mb-3">
                    <i class="ri-check-double-line fs-5 flex-shrink-0"></i>
                    <span><?php echo e($importSuccess); ?></span>
                </div>
                <?php elseif($importFail): ?>
                <div class="alert alert-danger py-2 d-flex align-items-center gap-2 mb-3">
                    <i class="ri-close-circle-line fs-5 flex-shrink-0"></i>
                    <span><?php echo e($importFail); ?></span>
                </div>
                <?php endif; ?>

                
                <div class="row g-2 text-center mb-3">
                    <?php if(count($skippedRows) > 0): ?>
                    <div class="col">
                        <div class="border rounded p-2 bg-warning-subtle">
                            <div class="fs-3 fw-bold text-warning"><?php echo e(count($skippedRows)); ?></div>
                            <small class="text-muted">Omitidas (duplicados)</small>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if(count($errorRows) > 0): ?>
                    <div class="col">
                        <div class="border rounded p-2 bg-danger-subtle">
                            <div class="fs-3 fw-bold text-danger"><?php echo e(count($errorRows)); ?></div>
                            <small class="text-muted">Con errores</small>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                
                <?php if(count($skippedRows) > 0 && count($errorRows) > 0): ?>
                <ul class="nav nav-tabs mb-2" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-errors" type="button">
                            <i class="ri-error-warning-line me-1 text-danger"></i>
                            Errores <span class="badge bg-danger ms-1"><?php echo e(count($errorRows)); ?></span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-skipped" type="button">
                            <i class="ri-skip-forward-line me-1 text-warning"></i>
                            Omitidas <span class="badge bg-warning text-dark ms-1"><?php echo e(count($skippedRows)); ?></span>
                        </button>
                    </li>
                </ul>
                <?php endif; ?>

                <div class="tab-content">

                    
                    <div class="tab-pane fade show active" id="tab-errors">
                        <?php if(count($errorRows) > 0): ?>
                        <div class="alert alert-danger py-2 mb-2">
                            <i class="ri-information-line me-1"></i>
                            Estas filas <strong>no fueron importadas</strong>.
                            Corrija los errores y vuelva a importar solo esas filas.
                        </div>
                        <div class="table-responsive" style="max-height:300px;overflow-y:auto;">
                            <table class="table table-sm table-bordered table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width:36px;">#</th>
                                        <th>Error</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $errorRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="text-center text-muted small"><?php echo e($i + 1); ?></td>
                                        <td class="small text-danger">
                                            <?php echo preg_replace(
                                                ['/(Fila \d+)/i', '/❌ \[ERROR\]\s*/'],
                                                ['<strong>$1</strong>', ''],
                                                e($msg)
                                            ); ?>

                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-4 text-success">
                            <i class="ri-checkbox-circle-line fs-1"></i>
                            <p class="mb-0 mt-2">Sin errores de validación.</p>
                        </div>
                        <?php endif; ?>
                    </div>

                    
                    <div class="tab-pane fade" id="tab-skipped">
                        <?php if(count($skippedRows) > 0): ?>
                        <div class="alert alert-warning py-2 mb-2">
                            <i class="ri-information-line me-1"></i>
                            Omitidas automáticamente: el candidato ya existe en la base de datos.
                            <strong>No es necesario corregir nada.</strong>
                        </div>
                        <div class="table-responsive" style="max-height:300px;overflow-y:auto;">
                            <table class="table table-sm table-bordered table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width:36px;">#</th>
                                        <th>Detalle</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $skippedRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="text-center text-muted small"><?php echo e($i + 1); ?></td>
                                        <td class="small text-warning-emphasis">
                                            <?php echo preg_replace(
                                                ['/(Fila \d+)/i', '/⏭️ \[OMITIDA\]\s*/'],
                                                ['<strong>$1</strong>', ''],
                                                e($msg)
                                            ); ?>

                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

            <div class="modal-footer gap-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i> Cerrar
                </button>
                <?php if(count($errorRows) > 0): ?>
                <a href="#" class="btn btn-warning"
                   data-bs-dismiss="modal"
                   data-bs-toggle="modal"
                   data-bs-target="#importModal">
                    <i class="ri-upload-2-line me-1"></i> Corregir y volver a importar
                </a>
                <?php endif; ?>
                <?php if($importSuccess): ?>
                <a href="<?php echo e(route('candidates.index')); ?>" class="btn btn-primary">
                    <i class="ri-refresh-line me-1"></i> Ver candidatos
                </a>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('importErrorsModal');
    if (el) new bootstrap.Modal(el).show();
});
</script>
<?php endif; ?>
<?php /**PATH D:\_Mine\sistema_electoral\resources\views/candidates/partials/modal-import-errors.blade.php ENDPATH**/ ?>