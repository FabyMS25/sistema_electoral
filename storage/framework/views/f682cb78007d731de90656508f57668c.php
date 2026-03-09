
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['votingTable' => null, 'institutions' => [], 'users' => []]));

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

foreach (array_filter((['votingTable' => null, 'institutions' => [], 'users' => []]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
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
            <i class="ri-settings-4-line me-1"></i>
            Identificación de la Mesa
        </h5>
    </div>
    <div class="card-body">

        
        <div class="mb-3">
            <label for="institution_id-field" class="form-label fw-bold">
                Recinto Electoral <span class="text-danger">*</span>
            </label>
            <select class="form-select <?php $__errorArgs = ['institution_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    name="institution_id" id="institution_id-field" required>
                <option value="">-- Seleccione un recinto --</option>
                <?php $__currentLoopData = $institutions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $institution): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($institution->id); ?>"
                            data-code="<?php echo e($institution->code); ?>"
                            <?php echo e(old('institution_id', $votingTable->institution_id ?? '') == $institution->id ? 'selected' : ''); ?>>
                        <?php echo e($institution->name); ?> (<?php echo e($institution->code); ?>)
                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php $__errorArgs = ['institution_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="invalid-feedback"><?php echo e($message); ?></div>
            <?php else: ?>
                <small class="text-muted">
                    Seleccione el recinto donde se encuentra esta mesa.
                </small>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="row">
            
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="number-field" class="form-label fw-bold">
                        N° Mesa <span class="text-danger">*</span>
                    </label>
                    <input type="number" id="number-field" name="number"
                        class="form-control <?php $__errorArgs = ['number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        placeholder="Ej: 1"
                        value="<?php echo e(old('number', $votingTable->number ?? '')); ?>"
                        min="1" required />
                    <?php $__errorArgs = ['number'];
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

            
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="letter-field" class="form-label">Letra</label>
                    <input type="text" id="letter-field" name="letter"
                        class="form-control <?php $__errorArgs = ['letter'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        placeholder="A, B, C..."
                        value="<?php echo e(old('letter', $votingTable->letter ?? '')); ?>"
                        maxlength="1" />
                    <?php $__errorArgs = ['letter'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php else: ?>
                        <small class="text-muted">Opcional (Ej: A, B, C)</small>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>

            
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="type-field" class="form-label">Tipo de Mesa</label>
                    <select class="form-select <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            name="type" id="type-field">
                        <option value="mixta"     <?php echo e(old('type', $votingTable->type ?? 'mixta') == 'mixta'     ? 'selected' : ''); ?>>Mixta (Hombres y Mujeres)</option>
                        <option value="masculina" <?php echo e(old('type', $votingTable->type ?? '')       == 'masculina' ? 'selected' : ''); ?>>Masculina</option>
                        <option value="femenina"  <?php echo e(old('type', $votingTable->type ?? '')       == 'femenina'  ? 'selected' : ''); ?>>Femenina</option>
                    </select>
                    <?php $__errorArgs = ['type'];
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


<div class="card border-info mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="card-title mb-0">
            <i class="ri-barcode-line me-1"></i>
            Códigos de Identificación
            <small class="text-white-50 ms-2">(Se generan automáticamente si se dejan vacíos)</small>
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="oep_code-field" class="form-label">Código OEP</label>
                    <input type="text" id="oep_code-field" name="oep_code"
                        class="form-control <?php $__errorArgs = ['oep_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        placeholder="Se genera automáticamente"
                        value="<?php echo e(old('oep_code', $votingTable->oep_code ?? '')); ?>" />
                    <?php $__errorArgs = ['oep_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php else: ?>
                        <small class="text-muted">
                            <i class="ri-information-line"></i>
                            Formato: [Código Recinto]-[N° Mesa][Letra] (Ej: REC001-1A)
                        </small>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="internal_code-field" class="form-label">Código Interno</label>
                    <input type="text" id="internal_code-field" name="internal_code"
                        class="form-control <?php $__errorArgs = ['internal_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        placeholder="Se genera automáticamente"
                        value="<?php echo e(old('internal_code', $votingTable->internal_code ?? '')); ?>" />
                    <?php $__errorArgs = ['internal_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php else: ?>
                        <small class="text-muted">
                            <i class="ri-information-line"></i>
                            Formato: [Código Recinto]-M[N° Mesa][Letra] (Ej: REC001-M01A)
                        </small>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="card border-success mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="card-title mb-0">
            <i class="ri-group-line me-1"></i>
            Rango de Votantes y Padrón
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            
            <div class="col-md-12 mb-3">
                <label for="expected_voters-field" class="form-label fw-bold">
                    Votantes Esperados (Según Padrón)
                </label>
                <input type="number" id="expected_voters-field" name="expected_voters"
                    class="form-control <?php $__errorArgs = ['expected_voters'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    value="<?php echo e(old('expected_voters', $votingTable->expected_voters ?? 0)); ?>"
                    min="0" />
                <?php $__errorArgs = ['expected_voters'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                <?php else: ?>
                    <small class="text-muted">Número total de ciudadanos habilitados para votar en esta mesa</small>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="voter_range_start_name-field" class="form-label">
                        Inicio del Rango (Apellido)
                    </label>
                    <input type="text" id="voter_range_start_name-field"
                        name="voter_range_start_name"
                        class="form-control"
                        placeholder="Ej: ACOSTA"
                        value="<?php echo e(old('voter_range_start_name', $votingTable->voter_range_start_name ?? '')); ?>" />
                    <small class="text-muted">Primer apellido del rango alfabético</small>
                </div>
            </div>

            
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="voter_range_end_name-field" class="form-label">
                        Fin del Rango (Apellido)
                    </label>
                    <input type="text" id="voter_range_end_name-field"
                        name="voter_range_end_name"
                        class="form-control"
                        placeholder="Ej: ZEBALLOS"
                        value="<?php echo e(old('voter_range_end_name', $votingTable->voter_range_end_name ?? '')); ?>" />
                    <small class="text-muted">Último apellido del rango alfabético</small>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="card border-warning mb-4">
    <div class="card-header bg-warning text-dark">
        <h5 class="card-title mb-0">
            <i class="ri-user-star-line me-1"></i>
            Delegados de Mesa
            <small class="text-dark-50 ms-2">(Usuarios registrados en el sistema)</small>
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <?php
                $delegates = [
                    'president_id' => ['label' => 'Presidente',  'icon' => 'ri-user-star-line', 'color' => 'primary'],
                    'secretary_id' => ['label' => 'Secretario',  'icon' => 'ri-user-settings-line', 'color' => 'success'],
                    'vocal1_id'    => ['label' => 'Vocal 1',     'icon' => 'ri-user-line', 'color' => 'info'],
                    'vocal2_id'    => ['label' => 'Vocal 2',     'icon' => 'ri-user-line', 'color' => 'secondary'],
                    'vocal3_id'    => ['label' => 'Vocal 3',     'icon' => 'ri-user-line', 'color' => 'dark'],
                    'vocal4_id'    => ['label' => 'Vocal 4',     'icon' => 'ri-user-line', 'color' => 'warning'],
                ];
            ?>

            <?php $__currentLoopData = $delegates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="<?php echo e($field); ?>-field" class="form-label">
                            <i class="<?php echo e($info['icon']); ?> me-1 text-<?php echo e($info['color']); ?>"></i>
                            <?php echo e($info['label']); ?>

                        </label>
                        <select class="form-select <?php $__errorArgs = [$field];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                name="<?php echo e($field); ?>" id="<?php echo e($field); ?>-field">
                            <option value="">-- No asignado --</option>
                            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($user->id); ?>"
                                    data-name="<?php echo e($user->name); ?> <?php echo e($user->last_name ?? ''); ?>"
                                    <?php echo e(old($field, $votingTable?->$field ?? '') == $user->id ? 'selected' : ''); ?>>
                                    <?php echo e($user->name); ?> <?php echo e($user->last_name ?? ''); ?>

                                    <?php if($user->email): ?> (<?php echo e($user->email); ?>) <?php endif; ?>
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = [$field];
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
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="alert alert-light mt-2 mb-0">
            <i class="ri-information-line me-1"></i>
            <small>Los delegados deben estar registrados como usuarios en el sistema.</small>
        </div>
    </div>
</div>


<div class="card border-secondary mb-4">
    <div class="card-header bg-secondary text-white">
        <h5 class="card-title mb-0">
            <i class="ri-chat-1-line me-1"></i>
            Observaciones Generales
        </h5>
    </div>
    <div class="card-body">
        <div class="mb-0">
            <textarea class="form-control <?php $__errorArgs = ['observations'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                      id="observations" name="observations"
                      rows="3"
                      placeholder="Observaciones adicionales sobre la mesa (ubicación, accesibilidad, notas especiales, etc.)"><?php echo e(old('observations', $votingTable->observations ?? '')); ?></textarea>
            <?php $__errorArgs = ['observations'];
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


<div class="alert alert-info">
    <div class="d-flex">
        <div class="me-3">
            <i class="ri-information-line fs-4"></i>
        </div>
        <div>
            <h6 class="alert-heading">Información importante sobre la estructura de datos:</h6>
            <p class="mb-2">
                <strong>Esta mesa NO está limitada a un solo tipo de elección.</strong>
                Los datos electorales específicos (papeletas recibidas, votantes, estado, horarios)
                se configuran por separado para <strong>cada tipo de elección</strong> en la sección
                "Configuración Electoral" después de crear la mesa.
            </p>
            <p class="mb-0">
                <i class="ri-checkbox-circle-line text-success me-1"></i>
                La mesa quedará automáticamente habilitada para <strong>TODOS los tipos de elección activos</strong>
                en el sistema.
            </p>
        </div>
    </div>
</div>


<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const institutionSelect = document.getElementById('institution_id-field');
    const numberInput = document.getElementById('number-field');
    const letterInput = document.getElementById('letter-field');
    const oepCodeInput = document.getElementById('oep_code-field');
    const internalCodeInput = document.getElementById('internal_code-field');

    function generateCodes() {
        if (!institutionSelect || !numberInput) return;

        const selectedOption = institutionSelect.options[institutionSelect.selectedIndex];
        const institutionCode = selectedOption?.dataset?.code || '';
        const number = numberInput.value;
        const letter = letterInput?.value?.toUpperCase() || '';

        if (institutionCode && number) {
            // Solo generar si los campos están vacíos o es creación nueva
            if (!oepCodeInput.value || oepCodeInput.value === '') {
                oepCodeInput.value = institutionCode + '-' + number + (letter || '');
            }
            if (!internalCodeInput.value || internalCodeInput.value === '') {
                const paddedNumber = number.padStart(2, '0');
                internalCodeInput.value = institutionCode + '-M' + paddedNumber + (letter || '');
            }
        }
    }

    if (institutionSelect) {
        institutionSelect.addEventListener('change', generateCodes);
    }
    if (numberInput) {
        numberInput.addEventListener('input', generateCodes);
    }
    if (letterInput) {
        letterInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase().replace(/[^A-Z]/g, '');
            generateCodes();
        });
    }

    // Generar códigos iniciales si estamos en creación
    <?php if(!isset($votingTable) || !$votingTable->exists): ?>
        generateCodes();
    <?php endif; ?>
});
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH D:\_Mine\sistema_electoral\resources\views/voting-tables/partials/form-fields.blade.php ENDPATH**/ ?>