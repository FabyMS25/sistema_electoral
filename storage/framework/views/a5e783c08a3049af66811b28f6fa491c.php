
<div class="card mb-3 table-card <?php echo e($table->status); ?>" id="table-<?php echo e($table->id); ?>">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-4">
                <h5 class="mb-0">
                    <i class="ri-table-line me-1"></i>
                    Mesa <?php echo e($table->number); ?>

                    <small class="text-muted ms-2"><?php echo e($table->code); ?></small>
                </h5>
                <small class="text-muted"><?php echo e($table->institution->name ?? 'N/A'); ?></small>
            </div>
            <div class="col-md-4">
                <?php
                    $statusClasses = [
                        'pendiente' => 'warning',
                        'en_proceso' => 'info',
                        'activo' => 'success',
                        'cerrado' => 'danger',
                        'en_computo' => 'primary',
                        'computado' => 'success',
                        'observado' => 'warning',
                        'anulado' => 'dark'
                    ];
                    $statusLabels = [
                        'pendiente' => 'Pendiente',
                        'en_proceso' => 'En Proceso',
                        'activo' => 'Activo',
                        'cerrado' => 'Cerrado',
                        'en_computo' => 'En Cómputo',
                        'computado' => 'Computado',
                        'observado' => 'Observado',
                        'anulado' => 'Anulado'
                    ];
                ?>
                <span class="badge bg-<?php echo e($statusClasses[$table->status] ?? 'secondary'); ?> total-badge">
                    <?php echo e($statusLabels[$table->status] ?? $table->status); ?>

                </span>
                <span class="ms-2 text-muted">
                    <i class="ri-group-line me-1"></i>
                    Habilitados: <?php echo e(number_format($table->registered_citizens ?? 0)); ?>

                </span>
            </div>
            <div class="col-md-4 text-end">
                <span class="text-muted me-3">
                    <i class="ri-bar-chart-line me-1"></i>
                    Votos: <span id="total-<?php echo e($table->id); ?>"><?php echo e($table->votes->sum('quantity')); ?></span>
                </span>
                <?php if($table->status !== 'cerrado'): ?>
                <button class="btn btn-sm btn-success save-table" data-table-id="<?php echo e($table->id); ?>">
                    <i class="ri-save-line me-1"></i>Guardar
                </button>
                <button class="btn btn-sm btn-warning close-table" data-table-id="<?php echo e($table->id); ?>">
                    <i class="ri-lock-line me-1"></i>Cerrar
                </button>
                <?php else: ?>
                <span class="badge bg-secondary">Cerrada</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40%">Candidato</th>
                        <th style="width: 40%">Partido</th>
                        <th style="width: 20%">Votos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $vote = $table->votes->firstWhere('candidate_id', $candidate->id);
                            $quantity = $vote ? $vote->quantity : 0;
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="candidate-color" style="background-color: <?php echo e($candidate->color ?? '#0ab39c'); ?>; width: 20px; height: 20px; border-radius: 4px; display: inline-block; margin-right: 8px;"></span>
                                    <span class="ms-2"><?php echo e($candidate->name); ?></span>
                                </div>
                            </td>
                            <td><?php echo e($candidate->party); ?></td>
                            <td>
                                <input type="number" 
                                       class="form-control vote-input candidate-vote" 
                                       data-table="<?php echo e($table->id); ?>"
                                       data-candidate="<?php echo e($candidate->id); ?>"
                                       value="<?php echo e($quantity); ?>"
                                       min="0"
                                       max="<?php echo e($table->registered_citizens ?? 9999); ?>"
                                       <?php echo e($table->status === 'cerrado' ? 'disabled' : ''); ?>>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted">
                                No hay candidatos disponibles para esta elección
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-info">
                    <tr>
                        <th colspan="2" class="text-end">Total Votos:</th>
                        <th>
                            <span class="fw-bold">
                                <?php echo e($table->votes->sum('quantity')); ?>

                            </span>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div><?php /**PATH D:\_Mine\corporate\resources\views/voting-table-votes/partials/table-row.blade.php ENDPATH**/ ?>