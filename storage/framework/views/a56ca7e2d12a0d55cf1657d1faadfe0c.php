<div class="row g-4 mb-2">
    
    <div class="col-sm-auto">
        <div class="d-flex flex-wrap gap-2 align-items-center">

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_candidatos')): ?>
            <button type="button" class="btn btn-success"
                    data-bs-toggle="modal" data-bs-target="#candidateModal" id="create-btn">
                <i class="ri-add-line me-1"></i> Agregar Candidato
            </button>
            <?php endif; ?>

            
            <div class="btn-group">
                <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="ri-download-line me-1"></i> Exportar
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item"
                           href="<?php echo e(route('candidates.export-all') . '?' . http_build_query(request()->except('selected_ids','page'))); ?>">
                            <i class="ri-file-excel-line me-2 text-success"></i>
                            Exportar Todo
                            <small class="text-muted d-block">
                                (<?php echo e($candidates->total()); ?> registros con filtros actuales)
                            </small>
                        </a>
                    </li>
                    <li>
                        <button class="dropdown-item" id="export-selected-btn"
                                onclick="exportSelected()" disabled>
                            <i class="ri-file-excel-line me-2 text-success"></i>
                            Exportar Seleccionados
                            <span id="selected-count-badge" class="badge bg-primary ms-1" style="display:none;">0</span>
                        </button>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="<?php echo e(route('candidates.template')); ?>">
                            <i class="ri-file-download-line me-2 text-secondary"></i> Descargar Plantilla CSV
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#"
                           data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="ri-file-upload-line me-2 text-secondary"></i> Importar Datos
                        </a>
                    </li>
                </ul>
            </div>

            
            <button class="btn btn-soft-danger d-none" id="delete-multiple-btn"
                    onclick="deleteMultiple()">
                <i class="ri-delete-bin-2-line me-1"></i> Eliminar Seleccionados
            </button>
        </div>
    </div>

    
    <div class="col-sm">
        <form method="GET" action="<?php echo e(route('candidates.index')); ?>" id="filter-form">

            <div class="row g-2 align-items-center">
                
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="ri-search-line text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0"
                               placeholder="Nombre, partido, lista…"
                               value="<?php echo e(request('search')); ?>">
                    </div>
                </div>

                
                <div class="col-md-3">
                    <select name="department_id" class="form-select" id="filter-department">
                        <option value="">Todos los departamentos</option>
                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($dept->id); ?>"
                                <?php echo e(request('department_id') == $dept->id ? 'selected' : ''); ?>>
                                <?php echo e($dept->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                
                <div class="col-md-4">
                    <select name="election_type_category_id" class="form-select" id="filter-category">
                        <option value="">Todas las categorías</option>
                        <?php $__currentLoopData = $electionTypeCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $etc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($etc->id); ?>"
                                <?php echo e(request('election_type_category_id') == $etc->id ? 'selected' : ''); ?>>
                                <?php echo e($etc->electionType->name); ?> – <?php echo e($etc->electionCategory->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>

            <div class="row g-2 mt-1">
                
                <div class="col-md-4">
                    <select name="province_id" class="form-select" id="filter-province"
                            <?php echo e(request('department_id') ? '' : 'disabled'); ?>>
                        <option value="">Todas las provincias</option>
                        <?php $__currentLoopData = $provinces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($prov->id); ?>"
                                <?php echo e(request('province_id') == $prov->id ? 'selected' : ''); ?>>
                                <?php echo e($prov->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                
                <div class="col-md-4">
                    <select name="municipality_id" class="form-select" id="filter-municipality"
                            <?php echo e(request('province_id') ? '' : 'disabled'); ?>>
                        <option value="">Todos los municipios</option>
                        <?php $__currentLoopData = $municipalities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mun): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($mun->id); ?>"
                                <?php echo e(request('municipality_id') == $mun->id ? 'selected' : ''); ?>>
                                <?php echo e($mun->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                
                <div class="col-md-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="ri-filter-3-line me-1"></i> Filtrar
                        </button>
                        <?php if(request()->hasAny(['search','election_type_category_id','department_id','province_id','municipality_id'])): ?>
                            <a href="<?php echo e(route('candidates.index')); ?>"
                               class="btn btn-outline-secondary" title="Limpiar filtros">
                                <i class="ri-close-line"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            
            <input type="hidden" name="sort"      value="<?php echo e(request('sort', 'name')); ?>">
            <input type="hidden" name="direction" value="<?php echo e(request('direction', 'asc')); ?>">
            <input type="hidden" name="per_page"  value="<?php echo e(request('per_page', 20)); ?>">
        </form>

        
        <?php if(request()->hasAny(['search','election_type_category_id','department_id','province_id','municipality_id'])): ?>
        <div class="mt-2 d-flex align-items-center gap-2 flex-wrap">
            <span class="text-muted small">Filtros:</span>

            <?php if(request('search')): ?>
                <span class="badge bg-primary d-inline-flex align-items-center gap-1">
                    <i class="ri-search-line"></i> "<?php echo e(request('search')); ?>"
                    <a href="<?php echo e(route('candidates.index', request()->except(['search','page']))); ?>" class="text-white"><i class="ri-close-line"></i></a>
                </span>
            <?php endif; ?>

            <?php if(request('election_type_category_id') && ($selCat = $electionTypeCategories->find(request('election_type_category_id')))): ?>
                <span class="badge bg-info d-inline-flex align-items-center gap-1">
                    <i class="ri-stack-line"></i> <?php echo e($selCat->electionType->name); ?> – <?php echo e($selCat->electionCategory->name); ?>

                    <a href="<?php echo e(route('candidates.index', request()->except(['election_type_category_id','page']))); ?>" class="text-white"><i class="ri-close-line"></i></a>
                </span>
            <?php endif; ?>

            <?php if(request('department_id') && ($selDept = $departments->find(request('department_id')))): ?>
                <span class="badge bg-success d-inline-flex align-items-center gap-1">
                    <i class="ri-map-pin-line"></i> <?php echo e($selDept->name); ?>

                    <a href="<?php echo e(route('candidates.index', request()->except(['department_id','province_id','municipality_id','page']))); ?>" class="text-white"><i class="ri-close-line"></i></a>
                </span>
            <?php endif; ?>

            <?php if(request('province_id') && ($selProv = $provinces->find(request('province_id')))): ?>
                <span class="badge bg-warning text-dark d-inline-flex align-items-center gap-1">
                    <i class="ri-map-pin-line"></i> <?php echo e($selProv->name); ?>

                    <a href="<?php echo e(route('candidates.index', request()->except(['province_id','municipality_id','page']))); ?>" class="text-dark"><i class="ri-close-line"></i></a>
                </span>
            <?php endif; ?>

            <?php if(request('municipality_id') && ($selMun = $municipalities->find(request('municipality_id')))): ?>
                <span class="badge bg-secondary d-inline-flex align-items-center gap-1">
                    <i class="ri-map-pin-line"></i> <?php echo e($selMun->name); ?>

                    <a href="<?php echo e(route('candidates.index', request()->except(['municipality_id','page']))); ?>" class="text-white"><i class="ri-close-line"></i></a>
                </span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>


<form id="export-selected-form"
      action="<?php echo e(route('candidates.export-selected')); ?>"
      method="POST" style="display:none;">
    <?php echo csrf_field(); ?>
    
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Department → Province cascade (filter bar) ───────────────
    const filterDept = document.getElementById('filter-department');
    if (filterDept) {
        filterDept.addEventListener('change', function () {
            const url = new URL(window.location.href);
            this.value
                ? url.searchParams.set('department_id', this.value)
                : url.searchParams.delete('department_id');
            url.searchParams.delete('province_id');
            url.searchParams.delete('municipality_id');
            url.searchParams.delete('page');
            window.location.href = url.toString();
        });
    }

    // ── Province → Municipality cascade (filter bar) ──────────────
    const filterProv = document.getElementById('filter-province');
    if (filterProv) {
        filterProv.addEventListener('change', function () {
            const url = new URL(window.location.href);
            this.value
                ? url.searchParams.set('province_id', this.value)
                : url.searchParams.delete('province_id');
            url.searchParams.delete('municipality_id');
            url.searchParams.delete('page');
            window.location.href = url.toString();
        });
    }

    // ── Category filter (auto-submit) ────────────────────────────
    const filterCat = document.getElementById('filter-category');
    if (filterCat) {
        filterCat.addEventListener('change', function () {
            const url = new URL(window.location.href);
            this.value
                ? url.searchParams.set('election_type_category_id', this.value)
                : url.searchParams.delete('election_type_category_id');
            url.searchParams.delete('page');
            window.location.href = url.toString();
        });
    }
});
</script>
<?php /**PATH D:\_Mine\sistema_electoral\resources\views/candidates/partials/actions-bar.blade.php ENDPATH**/ ?>