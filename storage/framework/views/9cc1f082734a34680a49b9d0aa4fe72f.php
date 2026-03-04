
<div class="row mb-2">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row g-3">
                    <div class="col-md-8 d-flex gap-3">
                        <h5 class="card-title mb-0 mt-2">Tipo de Elección: </h5>
                        <form method="GET" action="<?php echo e(url()->current()); ?>">
                            <div class="row">
                                <div class="col-md-8">
                                    <select name="election_type" class="form-select" onchange="this.form.submit()">
                                        <?php $__currentLoopData = $electionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $electionType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($electionType->id); ?>"
                                                <?php echo e($selectedElectionType && $selectedElectionType->id == $electionType->id ? 'selected' : ''); ?>>
                                                <?php echo e($electionType->name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex justify-content-end align-items-center">
                            <button class="btn btn-sm btn-outline-primary" onclick="refreshDashboard()">
                                <i class="ri-refresh-line"></i> Actualizar
                            </button>
                            <div class="ms-2">
                                <small class="text-muted" id="last-update-time">
                                    <?php echo e(now()->format('H:i:s')); ?>

                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="<?php echo e(url()->current()); ?>" class="row g-3" id="locationFilterForm">
                    <input type="hidden" name="election_type" value="<?php echo e($selectedElectionType ? $selectedElectionType->id : ''); ?>">

                    <div class="col-md-3">
                        <label for="department" class="form-label">Departamento</label>
                        <select name="department" id="department" class="form-select" onchange="updateProvinces()">
                            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($department->id); ?>"
                                    <?php echo e($selectedDepartment == $department->id ? 'selected' : ''); ?>>
                                    <?php echo e($department->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="province" class="form-label">Provincia</label>
                        <select name="province" id="province" class="form-select" onchange="updateMunicipalities()">
                            <?php $__currentLoopData = $provinces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $province): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($province->id); ?>"
                                    <?php echo e($selectedProvince == $province->id ? 'selected' : ''); ?>>
                                    <?php echo e($province->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="municipality" class="form-label">Municipio</label>
                        <select name="municipality" id="municipality" class="form-select" onchange="this.form.submit()">
                            <?php $__currentLoopData = $municipalities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $municipality): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($municipality->id); ?>"
                                    <?php echo e($selectedMunicipality == $municipality->id ? 'selected' : ''); ?>>
                                    <?php echo e($municipality->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Filtrar Resultados</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function updateProvinces() {
    const departmentId = document.getElementById('department').value;
    const provinceSelect = document.getElementById('province');
    const municipalitySelect = document.getElementById('municipality');

    fetch(`/api/provinces/${departmentId}`)
        .then(response => response.json())
        .then(data => {
            provinceSelect.innerHTML = '<option value="">Seleccione...</option>';
            data.forEach(province => {
                provinceSelect.innerHTML += `<option value="${province.id}">${province.name}</option>`;
            });
            municipalitySelect.innerHTML = '<option value="">Seleccione...</option>';
        });
}

function updateMunicipalities() {
    const provinceId = document.getElementById('province').value;
    const municipalitySelect = document.getElementById('municipality');

    fetch(`/api/municipalities/${provinceId}`)
        .then(response => response.json())
        .then(data => {
            municipalitySelect.innerHTML = '<option value="">Seleccione...</option>';
            data.forEach(municipality => {
                municipalitySelect.innerHTML += `<option value="${municipality.id}">${municipality.name}</option>`;
            });
        });
}
</script>
<?php /**PATH D:\_Mine\corporate\resources\views/partials/dashboard-filters.blade.php ENDPATH**/ ?>