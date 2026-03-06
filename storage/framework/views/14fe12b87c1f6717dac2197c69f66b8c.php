
<div class="row mb-3">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body py-3">
                <form method="GET" action="<?php echo e(route('voting-table-votes.index')); ?>" id="filterForm">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label form-label-sm fw-semibold mb-1">
                                <i class="ri-building-2-line me-1 text-muted"></i>Recinto
                            </label>
                            <select name="institution_id" class="form-select form-select-sm select2"
                                    id="institutionFilter">
                                <option value="">Todos los recintos</option>
                                <?php $__currentLoopData = $institutions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inst): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($inst->id); ?>"
                                        <?php echo e(request('institution_id') == $inst->id ? 'selected' : ''); ?>>
                                        <?php echo e($inst->name); ?> (<?php echo e($inst->code); ?>)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label form-label-sm fw-semibold mb-1">
                                <i class="ri-vote-line me-1 text-muted"></i>Tipo de Elección
                            </label>
                            <select name="election_type_id" class="form-select form-select-sm select2"
                                    id="electionTypeFilter">
                                <?php $__currentLoopData = $electionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($type->id); ?>"
                                        <?php echo e(($electionTypeId ?? '') == $type->id ? 'selected' : ''); ?>>
                                        <?php echo e($type->name); ?>

                                        (<?php echo e(\Carbon\Carbon::parse($type->election_date)->format('d/m/Y')); ?>)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        
                        <div class="col-md-2">
                            <label class="form-label form-label-sm fw-semibold mb-1">
                                <i class="ri-flag-line me-1 text-muted"></i>Estado
                            </label>
                            <select name="status" class="form-select form-select-sm" id="statusFilter">
                                <option value="">Todos</option>
                                <?php
                                    $statusOptions = [
                                        'configurada'   => ['label' => 'Configurada',    'icon' => '⚙️'],
                                        'en_espera'     => ['label' => 'En Espera',       'icon' => '⏳'],
                                        'votacion'      => ['label' => 'En Votación',     'icon' => '🗳️'],
                                        'cerrada'       => ['label' => 'Cerrada',         'icon' => '🔒'],
                                        'en_escrutinio' => ['label' => 'En Escrutinio',   'icon' => '📊'],
                                        'escrutada'     => ['label' => 'Escrutada',       'icon' => '✅'],
                                        'observada'     => ['label' => 'Observada',       'icon' => '⚠️'],
                                        'transmitida'   => ['label' => 'Transmitida',     'icon' => '📡'],
                                        'anulada'       => ['label' => 'Anulada',         'icon' => '❌'],
                                    ];
                                ?>
                                <?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($val); ?>"
                                        <?php echo e(request('status') === $val ? 'selected' : ''); ?>>
                                        <?php echo e($opt['icon']); ?> <?php echo e($opt['label']); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        
                        <div class="col-md-2">
                            <label class="form-label form-label-sm fw-semibold mb-1">
                                <i class="ri-hashtag me-1 text-muted"></i>N° Mesa
                            </label>
                            <input type="number" name="table_number" class="form-control form-control-sm"
                                   placeholder="Ej: 1, 2…" min="1"
                                   value="<?php echo e(request('table_number')); ?>">
                        </div>

                        
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                                <i class="ri-search-line me-1"></i>Buscar
                            </button>
                            <a href="<?php echo e(route('voting-table-votes.index', ['election_type_id' => $electionTypeId ?? ''])); ?>"
                               class="btn btn-outline-secondary btn-sm" title="Limpiar filtros">
                                <i class="ri-refresh-line"></i>
                            </a>
                        </div>
                    </div>

                    
                    <?php
                        $activeCount = collect([
                            'institution_id', 'status', 'table_number', 'table_code',
                            'from_name', 'to_name', 'table_type', 'min_votes',
                            'max_votes', 'has_observations', 'participation',
                        ])->filter(fn($k) => request($k))->count();
                    ?>

                    <?php if($activeCount > 0): ?>
                    <div class="mt-2 d-flex flex-wrap gap-1 align-items-center">
                        <small class="text-muted me-1">Activos:</small>
                        <?php if(request('institution_id')): ?>
                            <?php $inst = $institutions->find(request('institution_id')); ?>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle filter-badge"
                                  data-param="institution_id">
                                🏫 <?php echo e($inst?->name ?? 'Recinto'); ?> <i class="ri-close-line ms-1"></i>
                            </span>
                        <?php endif; ?>
                        <?php if(request('status')): ?>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle filter-badge"
                                  data-param="status">
                                <?php echo e($statusOptions[request('status')]['icon'] ?? '🏷️'); ?>

                                <?php echo e($statusOptions[request('status')]['label'] ?? request('status')); ?>

                                <i class="ri-close-line ms-1"></i>
                            </span>
                        <?php endif; ?>
                        <?php if(request('table_number')): ?>
                            <span class="badge bg-info-subtle text-info border border-info-subtle filter-badge"
                                  data-param="table_number">
                                # Mesa <?php echo e(request('table_number')); ?> <i class="ri-close-line ms-1"></i>
                            </span>
                        <?php endif; ?>
                        <?php if(request('table_code')): ?>
                            <span class="badge bg-info-subtle text-info border border-info-subtle filter-badge"
                                  data-param="table_code">
                                📋 <?php echo e(request('table_code')); ?> <i class="ri-close-line ms-1"></i>
                            </span>
                        <?php endif; ?>
                        <?php if(request('table_type')): ?>
                            <span class="badge bg-secondary-subtle text-secondary border filter-badge"
                                  data-param="table_type">
                                👥 <?php echo e(ucfirst(request('table_type'))); ?> <i class="ri-close-line ms-1"></i>
                            </span>
                        <?php endif; ?>
                        <?php if(request('has_observations') !== null && request('has_observations') !== ''): ?>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle filter-badge"
                                  data-param="has_observations">
                                ⚠️ <?php echo e(request('has_observations') == '1' ? 'Con obs.' : 'Sin obs.'); ?>

                                <i class="ri-close-line ms-1"></i>
                            </span>
                        <?php endif; ?>
                        <?php if(request('min_votes') || request('max_votes')): ?>
                            <span class="badge bg-secondary-subtle text-secondary border filter-badge"
                                  data-param="min_votes">
                                🗳️ <?php echo e(request('min_votes') ?? '0'); ?>–<?php echo e(request('max_votes') ?? '∞'); ?> votos
                                <i class="ri-close-line ms-1"></i>
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    
                    <div class="mt-2">
                        <a class="text-muted small text-decoration-none" data-bs-toggle="collapse"
                           href="#advancedFilters" role="button" aria-expanded="false">
                            <i class="ri-equalizer-line me-1"></i>
                            Filtros avanzados
                            <?php if(collect(['table_code','from_name','to_name','table_type','min_votes','max_votes','has_observations','participation','sort_by'])->filter(fn($k)=>request($k))->count() > 0): ?>
                                <span class="badge bg-primary rounded-pill ms-1" style="font-size:10px;">
                                    <?php echo e(collect(['table_code','from_name','to_name','table_type','min_votes','max_votes','has_observations','participation','sort_by'])->filter(fn($k)=>request($k))->count()); ?>

                                </span>
                            <?php endif; ?>
                        </a>
                    </div>

                    <div class="collapse <?php echo e(collect(['table_code','from_name','to_name','table_type','min_votes','max_votes','has_observations','participation','sort_by'])->filter(fn($k)=>request($k))->count() > 0 ? 'show' : ''); ?>"
                         id="advancedFilters">
                        <div class="border rounded p-3 mt-2 bg-light">
                            <div class="row g-2">

                                <div class="col-md-2">
                                    <label class="form-label form-label-sm fw-semibold mb-1">Código Mesa</label>
                                    <input type="text" name="table_code" class="form-control form-control-sm"
                                           placeholder="OEP o interno" value="<?php echo e(request('table_code')); ?>">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label form-label-sm fw-semibold mb-1">Tipo de Mesa</label>
                                    <select name="table_type" class="form-select form-select-sm">
                                        <option value="">Todos</option>
                                        <option value="mixta"     <?php echo e(request('table_type') == 'mixta'     ? 'selected' : ''); ?>>Mixta</option>
                                        <option value="masculina" <?php echo e(request('table_type') == 'masculina' ? 'selected' : ''); ?>>Masculina</option>
                                        <option value="femenina"  <?php echo e(request('table_type') == 'femenina'  ? 'selected' : ''); ?>>Femenina</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label form-label-sm fw-semibold mb-1">Apellido desde</label>
                                    <input type="text" name="from_name" class="form-control form-control-sm"
                                           placeholder="Apellido inicial" value="<?php echo e(request('from_name')); ?>">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label form-label-sm fw-semibold mb-1">Apellido hasta</label>
                                    <input type="text" name="to_name" class="form-control form-control-sm"
                                           placeholder="Apellido final" value="<?php echo e(request('to_name')); ?>">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label form-label-sm fw-semibold mb-1">Observaciones</label>
                                    <select name="has_observations" class="form-select form-select-sm">
                                        <option value="">Todas</option>
                                        <option value="1" <?php echo e(request('has_observations') == '1' ? 'selected' : ''); ?>>Con observaciones</option>
                                        <option value="0" <?php echo e(request('has_observations') == '0' ? 'selected' : ''); ?>>Sin observaciones</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label form-label-sm fw-semibold mb-1">Participación</label>
                                    <select name="participation" class="form-select form-select-sm">
                                        <option value="">Cualquiera</option>
                                        <option value="alta"  <?php echo e(request('participation') == 'alta'  ? 'selected' : ''); ?>>Alta (&gt;75%)</option>
                                        <option value="media" <?php echo e(request('participation') == 'media' ? 'selected' : ''); ?>>Media (50-75%)</option>
                                        <option value="baja"  <?php echo e(request('participation') == 'baja'  ? 'selected' : ''); ?>>Baja (&lt;50%)</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label form-label-sm fw-semibold mb-1">Votos mín.</label>
                                    <input type="number" name="min_votes" class="form-control form-control-sm"
                                           placeholder="0" min="0" value="<?php echo e(request('min_votes')); ?>">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label form-label-sm fw-semibold mb-1">Votos máx.</label>
                                    <input type="number" name="max_votes" class="form-control form-control-sm"
                                           placeholder="∞" min="0" value="<?php echo e(request('max_votes')); ?>">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label form-label-sm fw-semibold mb-1">Ordenar por</label>
                                    <select name="sort_by" class="form-select form-select-sm">
                                        <option value="number"          <?php echo e(request('sort_by','number') == 'number'          ? 'selected' : ''); ?>>N° Mesa</option>
                                        <option value="expected_voters" <?php echo e(request('sort_by') == 'expected_voters'          ? 'selected' : ''); ?>>Habilitados</option>
                                        <option value="institution"     <?php echo e(request('sort_by') == 'institution'              ? 'selected' : ''); ?>>Recinto</option>
                                        <option value="status"          <?php echo e(request('sort_by') == 'status'                   ? 'selected' : ''); ?>>Estado</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label form-label-sm fw-semibold mb-1">Dirección</label>
                                    <select name="sort_direction" class="form-select form-select-sm">
                                        <option value="asc"  <?php echo e(request('sort_direction','asc') == 'asc'  ? 'selected' : ''); ?>>↑ Asc</option>
                                        <option value="desc" <?php echo e(request('sort_direction') == 'desc'        ? 'selected' : ''); ?>>↓ Desc</option>
                                    </select>
                                </div>

                                <div class="col-md-4 d-flex align-items-end justify-content-end gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="ri-search-line me-1"></i>Aplicar filtros
                                    </button>
                                    <a href="<?php echo e(route('voting-table-votes.index', ['election_type_id' => $electionTypeId ?? ''])); ?>"
                                       class="btn btn-outline-secondary btn-sm">
                                        <i class="ri-delete-bin-line me-1"></i>Limpiar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet"/>
<style>
.filter-badge {
    cursor: pointer;
    user-select: none;
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 20px;
    transition: opacity 0.15s;
}
.filter-badge:hover { opacity: 0.75; }
.select2-container--bootstrap-5 .select2-selection { min-height: 31px; font-size: 0.875rem; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Select2
    $('.select2').select2({ theme: 'bootstrap-5', width: '100%', allowClear: true, placeholder: 'Seleccionar…' });

    // Auto-submit on main filter change
    $('#institutionFilter, #electionTypeFilter, #statusFilter').on('change', function () {
        document.getElementById('filterForm').submit();
    });

    // Click badge → remove that param
    document.querySelectorAll('.filter-badge').forEach(badge => {
        badge.addEventListener('click', function () {
            const param = this.dataset.param;
            const url   = new URL(window.location.href);
            // Remove related params (e.g. min_votes badge also removes max_votes)
            if (param === 'min_votes') url.searchParams.delete('max_votes');
            url.searchParams.delete(param);
            window.location.href = url.toString();
        });
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH D:\_Mine\sistema_electoral\resources\views/voting-table-votes/partials/filters.blade.php ENDPATH**/ ?>