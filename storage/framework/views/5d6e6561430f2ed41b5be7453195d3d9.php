
<div class="table-responsive table-card mt-2 mb-1">
    <table class="table align-middle table-nowrap">
        <thead class="table-light">
            <tr>
                <th scope="col" style="width: 50px;">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="checkAll" value="option">
                    </div>
                </th>
                <th>
                    <a href="<?php echo e(route('voting-tables.index', array_merge(request()->query(), ['sort' => 'institution_name', 'direction' => request('sort') == 'institution_name' && request('direction') == 'asc' ? 'desc' : 'asc']))); ?>" 
                       class="text-dark text-decoration-none">
                        Institución
                        <?php if(request('sort') == 'institution_name'): ?>
                            <i class="ri-arrow-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?>-line"></i>
                        <?php endif; ?>
                    </a>
                </th>
                <th>
                    <a href="<?php echo e(route('voting-tables.index', array_merge(request()->query(), ['sort' => 'oep_code', 'direction' => request('sort') == 'oep_code' && request('direction') == 'asc' ? 'desc' : 'asc']))); ?>" 
                       class="text-dark text-decoration-none">
                        Código OEP
                        <?php if(request('sort') == 'oep_code'): ?>
                            <i class="ri-arrow-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?>-line"></i>
                        <?php endif; ?>
                    </a>
                </th>
                <th>
                    <a href="<?php echo e(route('voting-tables.index', array_merge(request()->query(), ['sort' => 'internal_code', 'direction' => request('sort') == 'internal_code' && request('direction') == 'asc' ? 'desc' : 'asc']))); ?>" 
                       class="text-dark text-decoration-none">
                        Código Interno
                        <?php if(request('sort') == 'internal_code'): ?>
                            <i class="ri-arrow-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?>-line"></i>
                        <?php endif; ?>
                    </a>
                </th>
                <th>
                    <a href="<?php echo e(route('voting-tables.index', array_merge(request()->query(), ['sort' => 'number', 'direction' => request('sort') == 'number' && request('direction') == 'asc' ? 'desc' : 'asc']))); ?>" 
                       class="text-dark text-decoration-none">
                        N° Mesa
                        <?php if(request('sort') == 'number'): ?>
                            <i class="ri-arrow-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?>-line"></i>
                        <?php endif; ?>
                    </a>
                </th>
                <th>
                    <a href="<?php echo e(route('voting-tables.index', array_merge(request()->query(), ['sort' => 'expected_voters', 'direction' => request('sort') == 'expected_voters' && request('direction') == 'asc' ? 'desc' : 'asc']))); ?>" 
                       class="text-dark text-decoration-none">
                        Electores
                        <?php if(request('sort') == 'expected_voters'): ?>
                            <i class="ri-arrow-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?>-line"></i>
                        <?php endif; ?>
                    </a>
                </th>
                <th>
                    <a href="<?php echo e(route('voting-tables.index', array_merge(request()->query(), ['sort' => 'total_voters', 'direction' => request('sort') == 'total_voters' && request('direction') == 'asc' ? 'desc' : 'asc']))); ?>" 
                       class="text-dark text-decoration-none">
                        Votaron
                        <?php if(request('sort') == 'total_voters'): ?>
                            <i class="ri-arrow-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?>-line"></i>
                        <?php endif; ?>
                    </a>
                </th>
                <th>
                    <a href="<?php echo e(route('voting-tables.index', array_merge(request()->query(), ['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc']))); ?>" 
                       class="text-dark text-decoration-none">
                        Estado
                        <?php if(request('sort') == 'status'): ?>
                            <i class="ri-arrow-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?>-line"></i>
                        <?php endif; ?>
                    </a>
                </th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody class="list form-check-all">
            <?php $__empty_1 = true; $__currentLoopData = $votingTables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <th scope="row">
                        <div class="form-check">
                            <input class="form-check-input child-checkbox" type="checkbox" name="selected_ids[]" value="<?php echo e($table->id); ?>">
                        </div>
                    </th>
                    <td>
                        <div class="d-flex flex-column">
                            <strong><?php echo e($table->institution->name ?? 'N/A'); ?></strong>
                            <small class="text-muted"><?php echo e($table->institution->code ?? ''); ?></small>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-info-subtle text-info"><?php echo e($table->oep_code ?? 'N/A'); ?></span>
                    </td>
                    <td>
                        <span class="badge bg-primary-subtle text-primary"><?php echo e($table->internal_code ?? 'N/A'); ?></span>
                    </td>
                    <td>
                        <span class="fw-semibold"><?php echo e($table->number); ?></span>
                        <?php if($table->letter): ?>
                            <span class="badge bg-secondary"><?php echo e($table->letter); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold"><?php echo e(number_format($table->expected_voters ?? 0)); ?></span>
                            <small class="text-muted">Habilitados</small>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold"><?php echo e(number_format($table->total_voters ?? 0)); ?></span>
                            <small class="text-muted"><?php echo e($table->expected_voters > 0 ? round(($table->total_voters / $table->expected_voters) * 100, 1) : 0); ?>%</small>
                        </div>
                    </td>
                    <td>
                        <?php
                            $statusColors = [
                                'configurada' => 'secondary',
                                'en_espera' => 'info',
                                'votacion' => 'primary',
                                'cerrada' => 'warning',
                                'en_escrutinio' => 'dark',
                                'escrutada' => 'success',
                                'observada' => 'danger',
                                'transmitida' => 'success',
                                'anulada' => 'dark'
                            ];
                            
                            $statusLabels = [
                                'configurada' => 'Configurada',
                                'en_espera' => 'En Espera',
                                'votacion' => 'Votación',
                                'cerrada' => 'Cerrada',
                                'en_escrutinio' => 'En Escrutinio',
                                'escrutada' => 'Escrutada',
                                'observada' => 'Observada',
                                'transmitida' => 'Transmitida',
                                'anulada' => 'Anulada'
                            ];
                        ?>
                        <span class="badge bg-<?php echo e($statusColors[$table->status] ?? 'secondary'); ?>-subtle text-<?php echo e($statusColors[$table->status] ?? 'secondary'); ?>">
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
                                data-code="<?php echo e($table->oep_code ?? $table->internal_code); ?>"
                                data-oep="<?php echo e($table->oep_code); ?>"
                                data-internal="<?php echo e($table->internal_code); ?>"
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
                    <td colspan="9" class="text-center py-4">
                        <div class="noresult">
                            <div class="text-center">
                                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                    colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px">
                                </lord-icon>
                                <h5 class="mt-2">Lo sentimos! No se encontraron resultados</h5>
                                <p class="text-muted mb-0">No hay mesas de votación que coincidan con los filtros.</p>
                                <a href="<?php echo e(route('voting-tables.index')); ?>" class="btn btn-primary mt-3">
                                    <i class="ri-refresh-line me-1"></i>Limpiar filtros
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div><?php /**PATH D:\_Mine\corporate\resources\views/voting-tables/partials/table.blade.php ENDPATH**/ ?>