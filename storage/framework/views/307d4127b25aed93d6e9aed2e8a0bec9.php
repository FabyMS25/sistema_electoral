
<div class="card mb-3 shadow-sm border-0">
    <div class="card-body py-3">
        <div class="row g-3 align-items-end">

            
            <div class="col-md-3">
                <label class="form-label form-label-sm fw-semibold mb-1">
                    <i class="ri-vote-line me-1 text-muted"></i>Tipo de Elección
                </label>
                <form method="GET" action="<?php echo e(url()->current()); ?>" id="electionTypeForm">
                    
                    <input type="hidden" name="department"   value="<?php echo e($selectedDepartment); ?>">
                    <input type="hidden" name="province"     value="<?php echo e($selectedProvince); ?>">
                    <input type="hidden" name="municipality" value="<?php echo e($selectedMunicipality); ?>">
                    <select name="election_type" class="form-select form-select-sm"
                            onchange="document.getElementById('electionTypeForm').submit()">
                        <?php $__currentLoopData = $electionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $et): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($et->id); ?>"
                                <?php echo e($selectedElectionType?->id == $et->id ? 'selected' : ''); ?>>
                                <?php echo e($et->name); ?>

                                (<?php echo e(\Carbon\Carbon::parse($et->election_date)->format('d/m/Y')); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </form>
            </div>

            
            <div class="col-md-7">
                <form method="GET" action="<?php echo e(url()->current()); ?>" id="locationFilterForm"
                      class="row g-2 align-items-end">
                    <input type="hidden" name="election_type" value="<?php echo e($selectedElectionType?->id ?? ''); ?>">

                    <div class="col-4">
                        <label class="form-label form-label-sm fw-semibold mb-1">
                            <i class="ri-map-2-line me-1 text-muted"></i>Departamento
                        </label>
                        <select name="department" id="dept-select" class="form-select form-select-sm">
                            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($dept->id); ?>"
                                    <?php echo e($selectedDepartment == $dept->id ? 'selected' : ''); ?>>
                                    <?php echo e($dept->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="col-4">
                        <label class="form-label form-label-sm fw-semibold mb-1">
                            <i class="ri-map-pin-2-line me-1 text-muted"></i>Provincia
                        </label>
                        <select name="province" id="prov-select" class="form-select form-select-sm">
                            <?php $__currentLoopData = $provinces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($prov->id); ?>"
                                    <?php echo e($selectedProvince == $prov->id ? 'selected' : ''); ?>>
                                    <?php echo e($prov->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="col-4">
                        <label class="form-label form-label-sm fw-semibold mb-1">
                            <i class="ri-community-line me-1 text-muted"></i>Municipio
                        </label>
                        <select name="municipality" id="muni-select" class="form-select form-select-sm"
                                onchange="document.getElementById('locationFilterForm').submit()">
                            <?php $__currentLoopData = $municipalities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $muni): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($muni->id); ?>"
                                    <?php echo e($selectedMunicipality == $muni->id ? 'selected' : ''); ?>>
                                    <?php echo e($muni->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </form>
            </div>

            
            <div class="col-md-2 text-end">
                <button class="btn btn-sm btn-outline-primary" onclick="ElectionDashboard?.refresh()">
                    <i class="ri-refresh-line me-1"></i>Actualizar
                </button>
                <div class="small text-muted mt-1" id="ds-filter-time"><?php echo e(now()->format('H:i')); ?></div>
            </div>

        </div>
    </div>
</div>

<script>
// Cascade: dept → province → municipality
document.getElementById('dept-select')?.addEventListener('change', function () {
    fetch(`/api/provinces/${this.value}`)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('prov-select');
            sel.innerHTML = data.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
            sel.dispatchEvent(new Event('change'));
        });
});

document.getElementById('prov-select')?.addEventListener('change', function () {
    fetch(`/api/municipalities/${this.value}`)
        .then(r => r.json())
        .then(data => {
            const sel = document.getElementById('muni-select');
            sel.innerHTML = data.map(m => `<option value="${m.id}">${m.name}</option>`).join('');
        });
});
</script>
<?php /**PATH D:\_Mine\sistema_electoral\resources\views/partials/dashboard-filters.blade.php ENDPATH**/ ?>