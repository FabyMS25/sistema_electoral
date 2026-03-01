
<div class="row g-4 mb-2">
    <div class="col-sm-auto">
        <div class="d-flex flex-wrap gap-2">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create_mesas')): ?>
            <a href="<?php echo e(route('voting-tables.create')); ?>" class="btn btn-success">
                <i class="ri-add-line align-bottom me-1"></i> Agregar Mesa
            </a>
            <?php endif; ?>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-download-line align-bottom me-1"></i> Exportar
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="<?php echo e(route('voting-tables.export-all') . '?' . http_build_query(request()->except('selected_ids'))); ?>">
                            <i class="ri-file-excel-line me-2"></i> Exportar Todo
                            <small class="text-muted d-block">(<?php echo e($votingTables->total()); ?> registros encontrados)</small>
                        </a>
                    </li>
                    <li>
                        <button class="dropdown-item" id="export-selected-btn" onclick="exportSelected()" disabled>
                            <i class="ri-file-excel-line me-2"></i> Exportar Seleccionados
                            <span id="selected-count-badge" class="badge bg-primary ms-2" style="display: none;">0</span>
                        </button>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="<?php echo e(route('voting-tables.template')); ?>">
                            <i class="ri-file-download-line me-2"></i> Descargar Plantilla
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="ri-file-upload-line me-2"></i> Importar Datos
                        </a>
                    </li>
                </ul>
            </div>
            <button class="btn btn-soft-danger" id="delete-multiple-btn" onclick="deleteMultiple()" style="display:none;">
                <i class="ri-delete-bin-2-line me-1"></i>
                Eliminar
            </button>
        </div>
    </div>
    
    <div class="col-sm">
        <div class="filters-row">
            <form method="GET" action="<?php echo e(route('voting-tables.index')); ?>" id="filter-form">
                <div class="row g-2 align-items-center">
                    <!-- Search Input -->
                    <div class="col-md-3 filter-item">
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="ri-search-line"></i>
                            </span>
                            <input type="text" name="search" class="form-control" 
                                placeholder="Buscar código OEP, interno, N° mesa, institución..." 
                                value="<?php echo e(request('search')); ?>">
                        </div>
                    </div>
                    
                    <!-- Institution Filter -->
                    <div class="col-md-3 filter-item">
                        <select name="institution_id" class="form-select">
                            <option value="">Institución</option>
                            <?php $__currentLoopData = $institutions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $institution): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($institution->id); ?>" <?php echo e(request('institution_id') == $institution->id ? 'selected' : ''); ?>>
                                    <?php echo e($institution->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    
                    <!-- Status Filter -->
                    <div class="col-md-2 filter-item">
                        <select name="status" class="form-select">
                            <option value="">Estado</option>
                            <?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value); ?>" <?php echo e(request('status') == $value ? 'selected' : ''); ?>>
                                    <?php echo e($label); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    
                    <!-- Election Type Filter -->
                    <div class="col-md-2 filter-item">
                        <select name="election_type_id" class="form-select">
                            <option value="">Tipo Elección</option>
                            <?php $__currentLoopData = $electionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($type->id); ?>" <?php echo e(request('election_type_id') == $type->id ? 'selected' : ''); ?>>
                                    <?php echo e($type->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="col-md-2">
                        <div class="action-buttons">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="ri-filter-3-line"></i> Filtrar
                            </button>
                            
                            <?php if(request()->hasAny(['search', 'institution_id', 'status', 'election_type_id'])): ?>
                                <a href="<?php echo e(route('voting-tables.index')); ?>" class="btn btn-outline-secondary btn-sm">
                                    <i class="ri-close-line"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Hidden inputs for sorting -->
                <input type="hidden" name="sort" value="<?php echo e(request('sort', 'institution_id')); ?>">
                <input type="hidden" name="direction" value="<?php echo e(request('direction', 'asc')); ?>">
                <input type="hidden" name="per_page" value="<?php echo e(request('per_page', 20)); ?>">
            </form>
        </div>
        <?php if(request()->hasAny(['search', 'institution_id', 'status', 'election_type_id'])): ?>
            <div class="m-1">
                <div class="d-flex gap-2 flex-wrap">
                    <?php if(request('search')): ?>
                        <span class="badge bg-primary">
                            <i class="ri-search-line"></i> "<?php echo e(request('search')); ?>"
                            <a href="<?php echo e(route('voting-tables.index', request()->except(['search', 'page']))); ?>" class="text-white ms-1">
                                <i class="ri-close-line"></i>
                            </a>
                        </span>
                    <?php endif; ?>
                    
                    <?php if(request('institution_id') && $institutions->find(request('institution_id'))): ?>
                        <span class="badge bg-info">
                            <?php echo e($institutions->find(request('institution_id'))->name); ?>

                            <a href="<?php echo e(route('voting-tables.index', request()->except(['institution_id', 'page']))); ?>" class="text-white ms-1">
                                <i class="ri-close-line"></i>
                            </a>
                        </span>
                    <?php endif; ?>
                    
                    <?php if(request('status') && isset($statusOptions[request('status')])): ?>
                        <span class="badge bg-success">
                            <?php echo e($statusOptions[request('status')]); ?>

                            <a href="<?php echo e(route('voting-tables.index', request()->except(['status', 'page']))); ?>" class="text-white ms-1">
                                <i class="ri-close-line"></i>
                            </a>
                        </span>
                    <?php endif; ?>
                    
                    <?php if(request('election_type_id') && $electionTypes->find(request('election_type_id'))): ?>
                        <span class="badge bg-warning text-dark">
                            <?php echo e($electionTypes->find(request('election_type_id'))->name); ?>

                            <a href="<?php echo e(route('voting-tables.index', request()->except(['election_type_id', 'page']))); ?>" class="text-dark ms-1">
                                <i class="ri-close-line"></i>
                            </a>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Hidden form for exporting selected items -->
<form id="export-selected-form" action="<?php echo e(route('voting-tables.export-selected')); ?>" method="POST" style="display: none;">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="selected_ids" id="selected-ids-input" value="">
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterSelects = document.querySelectorAll('#filter-form select');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            document.getElementById('filter-form').submit();
        });
    });
});
</script><?php /**PATH D:\_Mine\corporate\resources\views/voting-tables/partials/actions-bar.blade.php ENDPATH**/ ?>