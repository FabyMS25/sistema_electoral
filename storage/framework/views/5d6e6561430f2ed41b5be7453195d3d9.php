
<div class="table-responsive table-card mt-3 mb-1">
    <table class="table align-middle table-nowrap">
        <thead class="table-light">
            <tr>
                <th scope="col" style="width: 50px;">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="checkAll">
                    </div>
                </th>
                <th>Institución</th>
                <th>Código</th>
                <th>N° Mesa</th>
                <th>Electores</th>
                <th>Votaron</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody class="list form-check-all">
            <?php $__empty_1 = true; $__currentLoopData = $votingTables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <th scope="row">
                        <div class="form-check">
                            <input class="form-check-input child-checkbox" type="checkbox" name="chk_child" value="<?php echo e($table->id); ?>">
                        </div>
                    </th>
                    <td>
                        <div class="d-flex flex-column">
                            <strong><?php echo e($table->institution->name ?? 'N/A'); ?></strong>
                            <small class="text-muted"><?php echo e($table->institution->code ?? ''); ?></small>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-info-subtle text-info"><?php echo e($table->code); ?></span>
                    </td>
                    <td>
                        <span class="fw-semibold"><?php echo e($table->number); ?></span>
                        <?php if($table->letter): ?>
                            <span class="badge bg-secondary"><?php echo e($table->letter); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold"><?php echo e(number_format($table->registered_citizens)); ?></span>
                            <small class="text-muted">Habilitados</small>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold"><?php echo e(number_format($table->voted_citizens)); ?></span>
                            <small class="text-muted"><?php echo e($table->progress_percentage); ?>%</small>
                        </div>
                    </td>
                    <td>
                        <?php
                            $statusColors = [
                                'pendiente' => 'warning',
                                'en_proceso' => 'info',
                                'cerrado' => 'secondary',
                                'en_computo' => 'primary',
                                'computado' => 'success',
                                'observado' => 'danger',
                                'anulado' => 'dark'
                            ];
                            $statusLabels = [
                                'pendiente' => 'Pendiente',
                                'en_proceso' => 'En Proceso',
                                'cerrado' => 'Cerrado',
                                'en_computo' => 'En Cómputo',
                                'computado' => 'Computado',
                                'observado' => 'Observado',
                                'anulado' => 'Anulado'
                            ];
                        ?>
                        <span class="badge bg-<?php echo e($statusColors[$table->status]); ?>-subtle text-<?php echo e($statusColors[$table->status]); ?>">
                            <?php echo e($statusLabels[$table->status] ?? $table->status); ?>

                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_mesas')): ?>
                            <a href="<?php echo e(route('voting-tables.show', $table->id)); ?>" 
                               class="btn btn-sm btn-info" title="Ver detalles">
                                <i class="ri-eye-line"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_mesas')): ?>
                            <a href="<?php echo e(route('voting-tables.edit', $table->id)); ?>" 
                               class="btn btn-sm btn-warning" title="Editar">
                                <i class="ri-pencil-line"></i>
                            </a>
                            <?php endif; ?>
                            
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete_mesas')): ?>
                            <button class="btn btn-sm btn-danger remove-item-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteRecordModal"
                                data-id="<?php echo e($table->id); ?>"
                                data-code="<?php echo e($table->code); ?>"
                                data-delete-url="<?php echo e(route('voting-tables.destroy', $table->id)); ?>"
                                title="Eliminar">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <div class="noresult">
                            <div class="text-center">
                                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                    colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px">
                                </lord-icon>
                                <h5 class="mt-2">Lo sentimos! No se encontraron resultados</h5>
                                <p class="text-muted mb-0">No hay mesas de votación registradas en el sistema.</p>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_mesas')): ?>
                                <a href="<?php echo e(route('voting-tables.create')); ?>" class="btn btn-primary mt-3">
                                    <i class="ri-add-line me-1"></i>Crear Primera Mesa
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div><?php /**PATH D:\_Mine\corporate\resources\views/voting-tables/partials/table.blade.php ENDPATH**/ ?>