<div class="table-responsive table-card mt-2 mb-1">
    <table class="table align-middle table-nowrap" id="customerTable">
        <thead class="table-light">
            <tr>
                <th scope="col" style="width: 50px;">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="checkAll" value="option">
                    </div>
                </th>
                <th>
                    <a href="<?php echo e(route('institutions.index', array_merge(request()->query(), ['sort' => 'code', 'direction' => request('sort') == 'code' && request('direction') == 'asc' ? 'desc' : 'asc']))); ?>"
                       class="text-dark text-decoration-none">
                        Código
                        <?php if(request('sort') == 'code'): ?>
                            <i class="ri-arrow-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?>-line"></i>
                        <?php endif; ?>
                    </a>
                </th>
                <th>
                    <a href="<?php echo e(route('institutions.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc']))); ?>"
                       class="text-dark text-decoration-none">
                        Recinto
                        <?php if(request('sort') == 'name'): ?>
                            <i class="ri-arrow-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?>-line"></i>
                        <?php endif; ?>
                    </a>
                </th>
                <th>Ubicación</th>
                <th>
                    <a href="<?php echo e(route('institutions.index', array_merge(request()->query(), ['sort' => 'registered_citizens', 'direction' => request('sort') == 'registered_citizens' && request('direction') == 'asc' ? 'desc' : 'asc']))); ?>"
                       class="text-dark text-decoration-none">
                        Ciudadanos
                        <?php if(request('sort') == 'registered_citizens'): ?>
                            <i class="ri-arrow-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?>-line"></i>
                        <?php endif; ?>
                    </a>
                </th>
                <th>Mesas</th>
                <th>Actas</th>
                <th>
                    <a href="<?php echo e(route('institutions.index', array_merge(request()->query(), ['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc']))); ?>"
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
            <?php $__empty_1 = true; $__currentLoopData = $institutions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $institution): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <th scope="row">
                        <div class="form-check">
                            <input class="form-check-input child-checkbox" type="checkbox" name="selected_ids[]" value="<?php echo e($institution->id); ?>">
                        </div>
                    </th>
                    <td>
                        <span class="badge bg-info-subtle text-info"><?php echo e($institution->code); ?></span>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <strong><?php echo e($institution->name); ?></strong>
                            <?php if($institution->short_name): ?>
                                <small class="text-muted"><?php echo e($institution->short_name); ?></small>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div>
                            <strong><?php echo e($institution->locality->municipality->name ?? 'N/A'); ?></strong>
                            <br>
                            <small class="text-muted">
                                <?php echo e($institution->locality->name ?? ''); ?>

                                <?php if($institution->district): ?>
                                    <br>Distrito: <?php echo e($institution->district->name); ?>

                                <?php endif; ?>
                                <?php if($institution->zone): ?>
                                    <br>Zona: <?php echo e($institution->zone->name); ?>

                                <?php endif; ?>
                            </small>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold"><?php echo e(number_format($institution->registered_citizens ?? 0)); ?></span>
                            <small class="text-muted">Habilitados</small>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-primary"><?php echo e($institution->voting_tables_count ?? 0); ?></span>
                    </td>
                    <td>
                        <div class="d-flex flex-column gap-1">
                            <small class="text-primary">
                                <i class="ri-checkbox-circle-line"></i> C: <?php echo e($institution->total_computed_records ?? 0); ?>

                            </small>
                            <small class="text-danger">
                                <i class="ri-close-circle-line"></i> A: <?php echo e($institution->total_annulled_records ?? 0); ?>

                            </small>
                        </div>
                    </td>
                    <td>
                        <?php
                            $statusColors = [
                                'activo' => 'success',
                                'inactivo' => 'danger',
                                'en_mantenimiento' => 'warning'
                            ];
                            $statusLabels = [
                                'activo' => 'Activo',
                                'inactivo' => 'Inactivo',
                                'en_mantenimiento' => 'Mantenimiento'
                            ];
                        ?>
                        <span class="badge bg-<?php echo e($statusColors[$institution->status] ?? 'secondary'); ?>-subtle text-<?php echo e($statusColors[$institution->status] ?? 'secondary'); ?>">
                            <?php echo e($statusLabels[$institution->status] ?? $institution->status); ?>

                        </span>
                        <?php if(!$institution->is_operative): ?>
                            <br><small class="text-warning">No Operativo</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_recintos')): ?>
                            <a href="<?php echo e(route('institutions.show', $institution->id)); ?>"
                               class="btn btn-sm btn-info" title="Ver detalles">
                                <i class="ri-eye-line"></i>
                            </a>
                            <?php endif; ?>

                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_recintos')): ?>
                            <a href="<?php echo e(route('institutions.edit', $institution->id)); ?>"
                               class="btn btn-sm btn-warning" title="Editar">
                                <i class="ri-pencil-line"></i>
                            </a>
                            <?php endif; ?>

                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete_recintos')): ?>
                            <button class="btn btn-sm btn-danger remove-item-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteRecordModal"
                                data-id="<?php echo e($institution->id); ?>"
                                data-name="<?php echo e($institution->name); ?>"
                                data-delete-url="<?php echo e(route('institutions.destroy', $institution->id)); ?>"
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
                                <p class="text-muted mb-0">No hay recintos que coincidan con los filtros.</p>
                                <a href="<?php echo e(route('institutions.index')); ?>" class="btn btn-primary mt-3">
                                    <i class="ri-refresh-line me-1"></i>Limpiar filtros
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php /**PATH D:\_Mine\corporate\resources\views/institutions/partials/table.blade.php ENDPATH**/ ?>