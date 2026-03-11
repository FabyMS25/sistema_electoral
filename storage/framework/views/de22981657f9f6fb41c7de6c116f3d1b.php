
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['institution' => null, 'departments' => [], 'statusOptions' => []]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['institution' => null, 'departments' => [], 'statusOptions' => []]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if($errors->any()): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <div class="d-flex">
            <i class="ri-error-warning-line fs-18 me-2"></i>
            <div>
                <strong>Por favor corrige los siguientes errores:</strong>
                <ul class="mb-0 mt-2">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>


<div class="card border-primary mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0">
            <i class="ri-building-line me-1"></i>
            ETAPA 1: DATOS BÁSICOS DEL RECINTO
            <small class="text-white-50 ms-2">(Datos obligatorios)</small>
        </h5>
    </div>
    <div class="card-body">

        
        <div class="row">
            <div class="col-md-8">
                <div class="mb-3">
                    <label for="name-field" class="form-label fw-bold">
                        Nombre del Recinto <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="name-field" name="name"
                        class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        placeholder="Ej: Unidad Educativa Simón Bolívar"
                        value="<?php echo e(old('name', $institution->name ?? '')); ?>" required />
                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php else: ?>
                        <small class="text-muted">Nombre completo del recinto electoral</small>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="code-field" class="form-label fw-bold">Código del Recinto</label>
                    <input type="text" id="code-field" name="code"
                        class="form-control <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        placeholder="Se genera automáticamente"
                        value="<?php echo e(old('code', $institution->code ?? '')); ?>" />
                    <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php else: ?>
                        <small class="text-muted">
                            <i class="ri-information-line"></i> Dejar vacío para generar automáticamente
                        </small>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>

        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="short_name-field" class="form-label">Nombre Corto</label>
                    <input type="text" id="short_name-field" name="short_name"
                        class="form-control <?php $__errorArgs = ['short_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        placeholder="Ej: UE Simón Bolívar"
                        value="<?php echo e(old('short_name', $institution->short_name ?? '')); ?>" />
                    <?php $__errorArgs = ['short_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php else: ?>
                        <small class="text-muted">Opcional – Nombre abreviado para reportes y listas</small>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>

        
        <div class="alert alert-light border mb-3 px-3 py-2">
            <div class="row align-items-start g-3">

                
                <div class="col-md-5">
                    <label for="status-field" class="form-label fw-bold">
                        Estado del Edificio <span class="text-danger">*</span>
                    </label>
                    <select class="form-select <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            name="status" id="status-field" required>
                        <option value="activo"
                            <?php echo e(old('status', $institution->status ?? 'activo') === 'activo' ? 'selected' : ''); ?>>
                            🟢 Activo – El edificio está abierto y en condiciones normales
                        </option>
                        <option value="en_mantenimiento"
                            <?php echo e(old('status', $institution->status ?? '') === 'en_mantenimiento' ? 'selected' : ''); ?>>
                            🟡 En Mantenimiento – Obras o reparaciones temporales en curso
                        </option>
                        <option value="inactivo"
                            <?php echo e(old('status', $institution->status ?? '') === 'inactivo' ? 'selected' : ''); ?>>
                            🔴 Inactivo – Cerrado permanentemente o fuera de servicio
                        </option>
                    </select>
                    <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <small class="text-muted">
                        <i class="ri-building-2-line me-1"></i>
                        Estado físico/administrativo del local.
                    </small>
                </div>

                
                <div class="col-md-1 text-center d-none d-md-flex align-items-center justify-content-center">
                    <div class="vr" style="height: 80px;"></div>
                </div>

                
                <div class="col-md-6">
                    <label class="form-label fw-bold d-block">
                        Habilitado para Elecciones
                    </label>

                    <div class="form-check form-switch mb-1">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="is_operative-field" name="is_operative" value="1"
                               <?php echo e(old('is_operative', $institution->is_operative ?? true) ? 'checked' : ''); ?>>
                        <label class="form-check-label fw-semibold" for="is_operative-field" id="operative-label">
                            
                        </label>
                    </div>

                    <small class="text-muted" id="operative-hint">
                        <i class="ri-vote-line me-1"></i>
                        Activa si este recinto participará en la jornada electoral vigente.
                        Puede deshabilitarse sin cambiar el estado del edificio
                        (p. ej. cuando el cupo ya está completo o el recinto fue reasignado).
                    </small>

                    <div id="operative-warning" class="alert alert-warning py-1 px-2 mt-2 mb-0 small" style="display:none;">
                        <i class="ri-error-warning-line me-1"></i>
                        Un recinto que <strong>no está Activo</strong> no puede ser habilitado para elecciones.
                        Se deshabilitará automáticamente.
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>


<div class="card border-info mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="card-title mb-0">
            <i class="ri-map-pin-line me-1"></i>
            ETAPA 2: UBICACIÓN GEOGRÁFICA
            <small class="text-white-50 ms-2">(Todos los campos son obligatorios)</small>
        </h5>
    </div>
    <div class="card-body">
        <div class="row">

            
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="department-field" class="form-label fw-bold">
                        Departamento <span class="text-danger">*</span>
                    </label>
                    <select class="form-select <?php $__errorArgs = ['department_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            name="department_id"
                            id="department-field"
                            data-cascade-url="<?php echo e(url('institutions/provinces')); ?>"
                            data-cascade-target="#province-field"
                            required>
                        <option value="">-- Seleccione Departamento --</option>
                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($dept->id); ?>"
                                <?php echo e(old('department_id',
                                       $institution?->locality?->municipality?->province?->department_id
                                   ) == $dept->id ? 'selected' : ''); ?>>
                                <?php echo e($dept->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['department_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="province-field" class="form-label fw-bold">
                        Provincia <span class="text-danger">*</span>
                    </label>
                    <select class="form-select <?php $__errorArgs = ['province_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            name="province_id"
                            id="province-field"
                            data-cascade-url="<?php echo e(url('institutions/municipalities')); ?>"
                            data-cascade-target="#municipality-field"
                            data-restore="<?php echo e(old('province_id', $institution?->locality?->municipality?->province_id)); ?>"
                            required disabled>
                        <option value="">-- Seleccione Provincia --</option>
                    </select>
                    <?php $__errorArgs = ['province_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="municipality-field" class="form-label fw-bold">
                        Municipio <span class="text-danger">*</span>
                    </label>
                    <select class="form-select <?php $__errorArgs = ['municipality_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            name="municipality_id"
                            id="municipality-field"
                            data-cascade-url="<?php echo e(url('institutions/localities')); ?>"
                            data-cascade-target="#locality-field"
                            data-restore="<?php echo e(old('municipality_id', $institution?->locality?->municipality_id)); ?>"
                            required disabled>
                        <option value="">-- Seleccione Municipio --</option>
                    </select>
                    <?php $__errorArgs = ['municipality_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="locality-field" class="form-label fw-bold">
                        Localidad <span class="text-danger">*</span>
                    </label>
                    <select class="form-select <?php $__errorArgs = ['locality_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            name="locality_id"
                            id="locality-field"
                            data-cascade-url="<?php echo e(url('institutions/districts')); ?>"
                            data-cascade-target="#district-field"
                            data-restore="<?php echo e(old('locality_id', $institution?->locality_id)); ?>"
                            required disabled>
                        <option value="">-- Seleccione Localidad --</option>
                    </select>
                    <?php $__errorArgs = ['locality_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>

        <div class="row">
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="district-field" class="form-label">
                        Distrito <small class="text-muted">(opcional)</small>
                    </label>
                    <select class="form-select <?php $__errorArgs = ['district_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            name="district_id"
                            id="district-field"
                            data-cascade-url="<?php echo e(url('institutions/zones')); ?>"
                            data-cascade-target="#zone-field"
                            data-restore="<?php echo e(old('district_id', $institution?->district_id)); ?>"
                            disabled>
                        <option value="">-- Seleccione Distrito (opcional) --</option>
                    </select>
                    <?php $__errorArgs = ['district_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="zone-field" class="form-label">
                        Zona <small class="text-muted">(opcional)</small>
                    </label>
                    <select class="form-select <?php $__errorArgs = ['zone_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            name="zone_id"
                            id="zone-field"
                            data-restore="<?php echo e(old('zone_id', $institution?->zone_id)); ?>"
                            disabled>
                        <option value="">-- Seleccione Zona (opcional) --</option>
                    </select>
                    <?php $__errorArgs = ['zone_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="card border-warning mb-4">
    <div class="card-header bg-warning text-dark">
        <h5 class="card-title mb-0">
            <i class="ri-map-pin-line me-1"></i>
            ETAPA 3: DIRECCIÓN Y CONTACTO
            <small class="ms-2">(Información de contacto – opcional)</small>
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <div class="mb-3">
                    <label for="address-field" class="form-label">Dirección</label>
                    <input type="text" id="address-field" name="address"
                        class="form-control <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        placeholder="Dirección exacta del recinto"
                        value="<?php echo e(old('address', $institution->address ?? '')); ?>" />
                    <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="reference-field" class="form-label">Referencia</label>
                    <input type="text" id="reference-field" name="reference"
                        class="form-control <?php $__errorArgs = ['reference'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        placeholder="Ej: Frente a la plaza"
                        value="<?php echo e(old('reference', $institution->reference ?? '')); ?>" />
                    <?php $__errorArgs = ['reference'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="latitude-field" class="form-label">Latitud</label>
                    <input type="text" id="latitude-field" name="latitude"
                        class="form-control <?php $__errorArgs = ['latitude'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        placeholder="-17.123456"
                        value="<?php echo e(old('latitude', $institution->latitude ?? '')); ?>" />
                    <?php $__errorArgs = ['latitude'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="longitude-field" class="form-label">Longitud</label>
                    <input type="text" id="longitude-field" name="longitude"
                        class="form-control <?php $__errorArgs = ['longitude'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        placeholder="-65.123456"
                        value="<?php echo e(old('longitude', $institution->longitude ?? '')); ?>" />
                    <?php $__errorArgs = ['longitude'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="phone-field" class="form-label">Teléfono</label>
                    <input type="text" id="phone-field" name="phone"
                        class="form-control <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        placeholder="Ej: 4-1234567"
                        value="<?php echo e(old('phone', $institution->phone ?? '')); ?>" />
                    <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="email-field" class="form-label">Email</label>
                    <input type="email" id="email-field" name="email"
                        class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        placeholder="recinto@ejemplo.com"
                        value="<?php echo e(old('email', $institution->email ?? '')); ?>" />
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="responsible-field" class="form-label">Responsable</label>
                    <input type="text" id="responsible-field" name="responsible_name"
                        class="form-control <?php $__errorArgs = ['responsible_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        placeholder="Nombre del encargado"
                        value="<?php echo e(old('responsible_name', $institution->responsible_name ?? '')); ?>" />
                    <?php $__errorArgs = ['responsible_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="card border-secondary mb-4">
    <div class="card-header bg-secondary text-white">
        <h5 class="card-title mb-0">
            <i class="ri-bar-chart-2-line me-1"></i>
            ETAPA 4: DATOS ELECTORALES
            <small class="text-white-50 ms-2">(Se actualizan automáticamente con las mesas)</small>
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="registered_citizens-field" class="form-label">Ciudadanos Habilitados</label>
                    <input type="number" id="registered_citizens-field" name="registered_citizens"
                        class="form-control <?php $__errorArgs = ['registered_citizens'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        value="<?php echo e(old('registered_citizens', $institution->registered_citizens ?? 0)); ?>" min="0" />
                    <?php $__errorArgs = ['registered_citizens'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <small class="text-muted">Total del padrón</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="total_computed_records-field" class="form-label">Actas Computadas</label>
                    <input type="number" id="total_computed_records-field" name="total_computed_records"
                        class="form-control <?php $__errorArgs = ['total_computed_records'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        value="<?php echo e(old('total_computed_records', $institution->total_computed_records ?? 0)); ?>" min="0" />
                    <?php $__errorArgs = ['total_computed_records'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="total_annulled_records-field" class="form-label">Actas Anuladas</label>
                    <input type="number" id="total_annulled_records-field" name="total_annulled_records"
                        class="form-control <?php $__errorArgs = ['total_annulled_records'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        value="<?php echo e(old('total_annulled_records', $institution->total_annulled_records ?? 0)); ?>" min="0" />
                    <?php $__errorArgs = ['total_annulled_records'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="total_enabled_records-field" class="form-label">Actas Habilitadas</label>
                    <input type="number" id="total_enabled_records-field" name="total_enabled_records"
                        class="form-control <?php $__errorArgs = ['total_enabled_records'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        value="<?php echo e(old('total_enabled_records', $institution->total_enabled_records ?? 0)); ?>" min="0" />
                    <?php $__errorArgs = ['total_enabled_records'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="mb-3">
    <label for="observations-field" class="form-label">Observaciones</label>
    <textarea id="observations-field" name="observations"
        class="form-control <?php $__errorArgs = ['observations'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
        placeholder="Observaciones adicionales sobre el recinto"
        rows="2"><?php echo e(old('observations', $institution->observations ?? '')); ?></textarea>
    <?php $__errorArgs = ['observations'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
</div>

<div class="alert alert-info mt-3">
    <i class="ri-information-line me-1"></i>
    <strong>Nota:</strong> Los contadores de la Etapa 4 se actualizan automáticamente cuando se registran votos en las mesas de este recinto.
</div>
<?php /**PATH D:\_Mine\sistema_electoral\resources\views/institutions/partials/form-fields.blade.php ENDPATH**/ ?>