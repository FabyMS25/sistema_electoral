<div class="table-responsive table-card mt-3 mb-1">
    <table class="table align-middle table-nowrap" id="customerTable">
        <thead class="table-light">
            <tr>
                <th scope="col" style="width: 50px;">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="checkAll" value="option">
                    </div>
                </th>
                <th>
                    <a href="<?php echo e(route('candidates.index', array_merge(request()->query(), ['sort' => 'photo', 'direction' => request('sort') == 'photo' && request('direction') == 'asc' ? 'desc' : 'asc']))); ?>"
                       class="text-dark text-decoration-none">
                        Foto
                    </a>
                </th>
                <th>
                    <a href="<?php echo e(route('candidates.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc']))); ?>"
                       class="text-dark text-decoration-none">
                        Nombre
                        <?php if(request('sort') == 'name'): ?>
                            <i class="ri-arrow-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?>-line"></i>
                        <?php endif; ?>
                    </a>
                </th>
                <th>
                    <a href="<?php echo e(route('candidates.index', array_merge(request()->query(), ['sort' => 'party', 'direction' => request('sort') == 'party' && request('direction') == 'asc' ? 'desc' : 'asc']))); ?>"
                       class="text-dark text-decoration-none">
                        Partido
                        <?php if(request('sort') == 'party'): ?>
                            <i class="ri-arrow-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?>-line"></i>
                        <?php endif; ?>
                    </a>
                </th>
                <th>Color</th>
                <th>
                    <a href="<?php echo e(route('candidates.index', array_merge(request()->query(), ['sort' => 'election_type', 'direction' => request('sort') == 'election_type' && request('direction') == 'asc' ? 'desc' : 'asc']))); ?>"
                       class="text-dark text-decoration-none">
                        Tipo Elección
                        <?php if(request('sort') == 'election_type'): ?>
                            <i class="ri-arrow-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?>-line"></i>
                        <?php endif; ?>
                    </a>
                </th>
                <th>
                    <a href="<?php echo e(route('candidates.index', array_merge(request()->query(), ['sort' => 'election_category', 'direction' => request('sort') == 'election_category' && request('direction') == 'asc' ? 'desc' : 'asc']))); ?>"
                       class="text-dark text-decoration-none">
                        Categoría
                        <?php if(request('sort') == 'election_category'): ?>
                            <i class="ri-arrow-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?>-line"></i>
                        <?php endif; ?>
                    </a>
                </th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody class="list form-check-all">
            <?php $__empty_1 = true; $__currentLoopData = $candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <th scope="row">
                        <div class="form-check">
                            <input class="form-check-input child-checkbox" type="checkbox" name="selected_ids[]" value="<?php echo e($candidate->id); ?>">
                        </div>
                    </th>
                    <td class="photo">
                        <?php if($candidate->photo): ?>
                            <img src="<?php echo e($candidate->photo_url); ?>" alt="<?php echo e($candidate->name); ?>" class="avatar-xs rounded-circle">
                        <?php else: ?>
                            <div class="avatar-xs bg-light rounded-circle d-flex align-items-center justify-content-center">
                                <i class="ri-user-line text-muted"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="name">
                        <div class="fw-semibold"><?php echo e($candidate->name); ?></div>
                        <?php if($candidate->list_name): ?>
                            <small class="text-muted"><?php echo e($candidate->list_name); ?> (Orden: <?php echo e($candidate->list_order); ?>)</small>
                        <?php endif; ?>
                    </td>
                    <td class="party">
                        <div class="d-flex gap-2 align-items-center">
                            <?php if($candidate->party_logo): ?>
                                <img src="<?php echo e($candidate->party_logo_url); ?>" alt="<?php echo e($candidate->party); ?>" class="avatar-xs rounded-circle">
                            <?php endif; ?>
                            <div>
                                <span class="fw-semibold"><?php echo e($candidate->party); ?></span>
                                <?php if($candidate->party_full_name): ?>
                                    <br><small class="text-muted"><?php echo e($candidate->party_full_name); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="color">
                        <?php if($candidate->color): ?>
                            <div class="color-preview" style="background-color: <?php echo e($candidate->color); ?>"
                                 title="<?php echo e($candidate->color); ?>"></div>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="election_type">
                        <?php if($candidate->electionTypeCategory): ?>
                            <span class="fw-semibold">
                                <?php echo e($candidate->electionTypeCategory->electionType->name ?? 'N/A'); ?>

                            </span>
                        <?php else: ?>
                            <span class="text-muted">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td class="election_category">
                        <?php if($candidate->electionTypeCategory): ?>
                            <span class="badge bg-primary-subtle text-primary">
                                <?php echo e($candidate->electionTypeCategory->electionCategory->name ?? 'N/A'); ?>

                                (<?php echo e($candidate->electionTypeCategory->electionCategory->code ?? ''); ?>)
                            </span>
                            <br>
                            <small class="text-muted">
                                Franja <?php echo e($candidate->electionTypeCategory->ballot_order ?? ''); ?>

                            </small>
                        <?php else: ?>
                            <span class="text-muted">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('view_candidatos')): ?>
                            <button type="button" class="btn btn-sm btn-info view-item-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#viewCandidateModal"
                                data-id="<?php echo e($candidate->id); ?>"
                                data-name="<?php echo e($candidate->name); ?>"
                                data-party="<?php echo e($candidate->party); ?>"
                                data-party_full_name="<?php echo e($candidate->party_full_name); ?>"
                                data-color="<?php echo e($candidate->color); ?>"
                                data-election_type_category_id="<?php echo e($candidate->election_type_category_id); ?>"
                                data-election_type="<?php echo e($candidate->electionTypeCategory->electionType->name ?? 'N/A'); ?>"
                                data-election_category="<?php echo e($candidate->electionTypeCategory->electionCategory->name ?? 'N/A'); ?>"
                                data-election_category_code="<?php echo e($candidate->electionTypeCategory->electionCategory->code ?? ''); ?>"
                                data-ballot_order="<?php echo e($candidate->electionTypeCategory->ballot_order ?? ''); ?>"
                                data-votes_per_person="<?php echo e($candidate->electionTypeCategory->votes_per_person ?? 1); ?>"
                                data-list_order="<?php echo e($candidate->list_order); ?>"
                                data-list_name="<?php echo e($candidate->list_name); ?>"
                                data-department_id="<?php echo e($candidate->department_id); ?>"
                                data-province_id="<?php echo e($candidate->province_id); ?>"
                                data-municipality_id="<?php echo e($candidate->municipality_id); ?>"
                                data-department_name="<?php echo e($candidate->department->name ?? ''); ?>"
                                data-province_name="<?php echo e($candidate->province->name ?? ''); ?>"
                                data-municipality_name="<?php echo e($candidate->municipality->name ?? ''); ?>"
                                data-photo-url="<?php echo e($candidate->photo_url); ?>"
                                data-party-logo-url="<?php echo e($candidate->party_logo_url); ?>"
                                data-active="<?php echo e($candidate->active ? '1' : '0'); ?>"
                                title="Ver detalles">
                                <i class="ri-eye-line"></i>
                            </button>
                            <?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('edit_candidatos')): ?>
                            <button type="button" class="btn btn-sm btn-warning edit-item-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#candidateModal"
                                data-id="<?php echo e($candidate->id); ?>"
                                data-update-url="<?php echo e(route('candidates.update', $candidate->id)); ?>"
                                data-name="<?php echo e($candidate->name); ?>"
                                data-party="<?php echo e($candidate->party); ?>"
                                data-party_full_name="<?php echo e($candidate->party_full_name); ?>"
                                data-color="<?php echo e($candidate->color); ?>"
                                data-election_type_category_id="<?php echo e($candidate->election_type_category_id); ?>"
                                data-list_order="<?php echo e($candidate->list_order); ?>"
                                data-list_name="<?php echo e($candidate->list_name); ?>"
                                data-department_id="<?php echo e($candidate->department_id); ?>"
                                data-province_id="<?php echo e($candidate->province_id); ?>"
                                data-municipality_id="<?php echo e($candidate->municipality_id); ?>"
                                data-photo-url="<?php echo e($candidate->photo_url); ?>"
                                data-party-logo-url="<?php echo e($candidate->party_logo_url); ?>"
                                data-active="<?php echo e($candidate->active ? '1' : '0'); ?>"
                                title="Editar">
                                <i class="ri-pencil-line"></i>
                            </button>
                            <?php endif; ?>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete_candidatos')): ?>
                            <button class="btn btn-sm btn-danger remove-item-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteRecordModal"
                                data-id="<?php echo e($candidate->id); ?>"
                                data-name="<?php echo e($candidate->name); ?>"
                                data-delete-url="<?php echo e(route('candidates.destroy', $candidate->id)); ?>"
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
                                <p class="text-muted mb-0">No hay candidatos que coincidan con los filtros.</p>
                                <a href="<?php echo e(route('candidates.index')); ?>" class="btn btn-primary mt-3">
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
<?php /**PATH D:\_Mine\corporate\resources\views/candidates/partials/table.blade.php ENDPATH**/ ?>