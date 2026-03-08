<?php $__env->startSection('title'); ?> Asignar Recinto - <?php echo e($user->name); ?> <?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <link href="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.css')); ?>" rel="stylesheet" type="text/css" />
    <style>
        .current-assignment { border-left: 3px solid #0ab39c; background-color: #f0f9f7; }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?> Usuarios <?php $__env->endSlot(); ?>
        <?php $__env->slot('li_2'); ?> <a href="<?php echo e(route('users.show', $user)); ?>"><?php echo e($user->name); ?> <?php echo e($user->last_name); ?></a> <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?> Asignar Recinto <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row">
        
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Nueva Asignación de Recinto</h4>
                    <p class="text-muted mb-0">
                        Usuario: <?php echo e($user->name); ?> <?php echo e($user->last_name); ?> (<?php echo e($user->email); ?>)
                    </p>
                </div>
                <div class="card-body">

                    <?php if(session('success')): ?>
                        <div class="alert alert-success alert-dismissible">
                            <?php echo e(session('success')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <?php if(session('error')): ?>
                        <div class="alert alert-danger alert-dismissible">
                            <?php echo e(session('error')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo e(route('users.assign-institution', $user)); ?>" method="POST"
                          id="assignInstitutionForm">
                        <?php echo csrf_field(); ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="election_type_id" class="form-label">
                                        Tipo de Elección <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select <?php $__errorArgs = ['election_type_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                            id="election_type_id" name="election_type_id" required>
                                        <option value="">Seleccione…</option>
                                        <?php $__currentLoopData = $electionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($type->id); ?>"
                                                <?php echo e(old('election_type_id') == $type->id ? 'selected' : ''); ?>>
                                                <?php echo e($type->name); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php $__errorArgs = ['election_type_id'];
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
                                    <label for="delegate_type" class="form-label">
                                        Tipo de Delegado <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select <?php $__errorArgs = ['delegate_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                            id="delegate_type" name="delegate_type" required>
                                        <option value="">Seleccione…</option>
                                        <option value="delegado_general"
                                            <?php echo e(old('delegate_type') == 'delegado_general' ? 'selected' : ''); ?>>
                                            Delegado General
                                        </option>
                                        <option value="tecnico"
                                            <?php echo e(old('delegate_type') == 'tecnico' ? 'selected' : ''); ?>>
                                            Técnico / Soporte
                                        </option>
                                        <option value="observador"
                                            <?php echo e(old('delegate_type') == 'observador' ? 'selected' : ''); ?>>
                                            Observador
                                        </option>
                                    </select>
                                    <?php $__errorArgs = ['delegate_type'];
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

                        <div class="mb-3">
                            <label for="institution_id" class="form-label">
                                Recinto <span class="text-danger">*</span>
                            </label>
                            
                            <select class="form-select <?php $__errorArgs = ['institution_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    id="institution_id" name="institution_id" required>
                                <option value="">Seleccione recinto…</option>
                                <?php $__currentLoopData = $institutions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $municipalityName => $instList): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <optgroup label="<?php echo e($municipalityName); ?>">
                                        <?php $__currentLoopData = $instList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $institution): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($institution->id); ?>"
                                                <?php echo e(old('institution_id') == $institution->id ? 'selected' : ''); ?>>
                                                <?php echo e($institution->name); ?> (<?php echo e($institution->code); ?>)
                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </optgroup>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['institution_id'];
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

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="assignment_date" class="form-label">Fecha de Asignación</label>
                                    <input type="date"
                                           class="form-control <?php $__errorArgs = ['assignment_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="assignment_date" name="assignment_date"
                                           value="<?php echo e(old('assignment_date', now()->format('Y-m-d'))); ?>">
                                    <?php $__errorArgs = ['assignment_date'];
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
                                    <label for="expiration_date" class="form-label">Fecha de Expiración</label>
                                    <input type="date"
                                           class="form-control <?php $__errorArgs = ['expiration_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="expiration_date" name="expiration_date"
                                           value="<?php echo e(old('expiration_date')); ?>">
                                    <small class="text-muted">Opcional — dejar vacío si no expira</small>
                                    <?php $__errorArgs = ['expiration_date'];
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

                        <div class="mb-3">
                            <label for="credential_number" class="form-label">Número de Credencial</label>
                            <input type="text"
                                   class="form-control <?php $__errorArgs = ['credential_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   id="credential_number" name="credential_number"
                                   value="<?php echo e(old('credential_number')); ?>">
                            <?php $__errorArgs = ['credential_number'];
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

                        <div class="mb-3">
                            <label for="observations" class="form-label">Observaciones</label>
                            <textarea class="form-control <?php $__errorArgs = ['observations'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                      id="observations" name="observations" rows="3"><?php echo e(old('observations')); ?></textarea>
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

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line align-middle me-1"></i> Guardar Asignación
                            </button>
                            <a href="<?php echo e(route('users.show', $user)); ?>" class="btn btn-soft-secondary">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Asignaciones de Recinto Activas</h5>
                </div>
                <div class="card-body">
                    <?php if($currentAssignments->count() > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php $__currentLoopData = $currentAssignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="list-group-item current-assignment mb-2 rounded">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <?php echo e($assignment->institution?->name ?? '—'); ?>

                                        </h6>
                                        <p class="mb-1">
                                            <span class="badge bg-success-subtle text-success">
                                                <?php echo e($assignment->delegate_type_label); ?>

                                            </span>
                                            <?php if($assignment->electionType): ?>
                                            <small class="text-muted ms-1">
                                                <?php echo e($assignment->electionType->short_name ?? $assignment->electionType->name); ?>

                                            </small>
                                            <?php endif; ?>
                                        </p>
                                        <p class="text-muted small mb-0">
                                            <i class="ri-calendar-line align-middle"></i>
                                            Desde: <?php echo e($assignment->assignment_date?->format('d/m/Y') ?? 'N/A'); ?>

                                            <?php if($assignment->expiration_date): ?>
                                                <br>Hasta: <?php echo e($assignment->expiration_date->format('d/m/Y')); ?>

                                            <?php endif; ?>
                                        </p>
                                        <?php if($assignment->credential_number): ?>
                                        <p class="text-muted small mb-0">
                                            <i class="ri-id-card-line align-middle"></i>
                                            Cred: <?php echo e($assignment->credential_number); ?>

                                        </p>
                                        <?php endif; ?>
                                    </div>
                                    <form action="<?php echo e(route('users.remove-assignment', [$user, $assignment])); ?>"
                                          method="POST" class="d-inline ms-2"
                                          onsubmit="return confirm('¿Remover esta asignación?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-soft-danger">
                                            <i class="ri-close-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                colors="primary:#121331,secondary:#08a88a" style="width:50px;height:50px">
                            </lord-icon>
                            <p class="text-muted mb-0">No tiene asignaciones de recinto activas</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Roles en Recinto</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <i class="ri-information-line align-middle me-1"></i>
                        <p class="mb-1 small"><strong>Delegado General:</strong> Acceso completo al recinto.</p>
                        <p class="mb-1 small"><strong>Técnico:</strong> Soporte técnico sin permisos de voto.</p>
                        <p class="mb-0 small"><strong>Observador:</strong> Solo lectura.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script src="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.js')); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('expiration_date').addEventListener('change', function () {
        const assignDate = document.getElementById('assignment_date').value;
        if (assignDate && this.value && this.value < assignDate) {
            Swal.fire({
                icon: 'error',
                title: 'Fecha inválida',
                text: 'La fecha de expiración debe ser posterior a la fecha de asignación.'
            });
            this.value = '';
        }
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\_Mine\sistema_electoral\resources\views/users/assign-institution.blade.php ENDPATH**/ ?>