<?php $__env->startSection('title'); ?> Asignaciones – <?php echo e($user->name); ?> <?php echo e($user->last_name); ?> <?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
<link href="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.css')); ?>" rel="stylesheet" />
<style>
    /* Role + permission cards — same as create */
    .permission-group { border:1px solid #e9e9ef; border-radius:.25rem; margin-bottom:1rem; }
    .permission-group-header { background:#f3f6f9; padding:.75rem 1rem; border-bottom:1px solid #e9e9ef; font-weight:600; }
    .permission-group-body { padding:1rem; display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:.5rem; }
    .role-card { border:1px solid #e9e9ef; border-radius:.25rem; padding:.75rem; margin-bottom:.5rem; cursor:pointer; transition:all .15s; }
    .role-card:hover { background:#f3f6f9; }
    .role-card.selected { background:#e7f1ff; border-color:#0ab39c; }

    /* Delegation panels */
    .delegation-card { transition:opacity .2s; }
    .delegation-card.locked { opacity:.35; pointer-events:none; }

    /* Sidebar pills */
    .a-pill { border-left:3px solid #0ab39c; background:#f0faf8; padding:.55rem .875rem; border-radius:0 .35rem .35rem 0; margin-bottom:.4rem; }
    .a-pill.mesa { border-left-color:#299cdb; background:#f0f7ff; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<?php $__env->startComponent('components.breadcrumb'); ?>
    <?php $__env->slot('li_1'); ?> Usuarios <?php $__env->endSlot(); ?>
    <?php $__env->slot('li_2'); ?> <a href="<?php echo e(route('users.index')); ?>">Lista</a> <?php $__env->endSlot(); ?>
    <?php $__env->slot('title'); ?> Asignaciones: <?php echo e($user->name); ?> <?php echo e($user->last_name); ?> <?php $__env->endSlot(); ?>
<?php echo $__env->renderComponent(); ?>


<div class="card mb-3">
    <div class="card-body py-3">
        <div class="d-flex align-items-center gap-3">
            <img src="<?php echo e($user->avatar ? URL::asset('build/images/users/'.$user->avatar) : URL::asset('build/images/users/avatar-1.jpg')); ?>"
                 alt="" class="rounded-circle" style="width:46px;height:46px;object-fit:cover;">
            <div>
                <h5 class="mb-0"><?php echo e($user->name); ?> <?php echo e($user->last_name); ?></h5>
                <p class="text-muted small mb-0"><?php echo e($user->email); ?><?php if($user->id_card): ?> &bull; CI: <?php echo e($user->id_card); ?><?php endif; ?></p>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="<?php echo e(route('users.show', $user)); ?>" class="btn btn-soft-secondary btn-sm">
                    <i class="ri-arrow-left-line align-middle"></i> Ver Perfil
                </a>
                <a href="<?php echo e(route('users.edit', $user)); ?>" class="btn btn-soft-warning btn-sm">
                    <i class="ri-pencil-line align-middle"></i> Editar
                </a>
            </div>
        </div>
    </div>
</div>

<?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible">
        <i class="ri-checkbox-circle-line align-middle me-1"></i> <?php echo e(session('success')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if(session('error')): ?>
    <div class="alert alert-danger alert-dismissible">
        <i class="ri-error-warning-line align-middle me-1"></i> <?php echo e(session('error')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">


<div class="col-lg-8 d-flex flex-column gap-4">

    
    <?php if(auth()->user()->allPermissions->contains('name', 'assign_roles')): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-1">Roles y Permisos del Sistema</h5>
            <p class="text-muted small mb-0">
                Selecciona los roles. Los permisos se marcan automáticamente y pueden ajustarse.
                La sección de delegación se activa según el ámbito del rol elegido.
            </p>
        </div>
        <div class="card-body">
            <form action="<?php echo e(route('users.assign-roles', $user)); ?>" method="POST" id="rolesForm">
                <?php echo csrf_field(); ?>

                <?php $currentMap = $currentRoles->keyBy('id'); ?>

                
                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <input type="hidden" class="h-role-id"    name="roles[<?php echo e($index); ?>][role_id]"           value="<?php echo e($role->id); ?>"                                                                        disabled>
                <input type="hidden" class="h-role-scope" name="roles[<?php echo e($index); ?>][scope]"             value="<?php echo e($currentMap->get($role->id)?->pivot?->scope ?? $role->default_scope ?? 'global'); ?>" id="hScope_<?php echo e($role->id); ?>" disabled>
                <input type="hidden" class="h-role-et"    name="roles[<?php echo e($index); ?>][election_type_id]"  value="<?php echo e($currentMap->get($role->id)?->pivot?->election_type_id ?? ''); ?>"                    id="hEt_<?php echo e($role->id); ?>"    disabled>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <div class="row">
                    
                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-header bg-soft-primary">
                                <h6 class="card-title mb-0">Roles disponibles</h6>
                            </div>
                            <div class="card-body" style="max-height:420px;overflow-y:auto;">
                                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $isChecked = (bool) $currentMap->get($role->id); ?>
                                <div class="role-card <?php echo e($isChecked ? 'selected' : ''); ?>"
                                     data-role-id="<?php echo e($role->id); ?>"
                                     data-default-scope="<?php echo e($role->default_scope ?? 'global'); ?>"
                                     data-role-name="<?php echo e(strtolower($role->name)); ?>">
                                    <div class="form-check mb-0">
                                        <input type="checkbox"
                                               class="form-check-input role-cb"
                                               id="role_<?php echo e($role->id); ?>"
                                               data-role-id="<?php echo e($role->id); ?>"
                                               <?php echo e($isChecked ? 'checked' : ''); ?>>
                                        <label class="form-check-label d-flex align-items-start gap-1" for="role_<?php echo e($role->id); ?>">
                                            <div>
                                                <strong><?php echo e($role->display_name ?? $role->name); ?></strong>
                                                <span class="badge ms-1
                                                    <?php if(($role->default_scope ?? 'global') === 'global'): ?>  bg-primary-subtle text-primary  <?php endif; ?>
                                                    <?php if(($role->default_scope ?? 'global') === 'recinto'): ?> bg-success-subtle text-success <?php endif; ?>
                                                    <?php if(($role->default_scope ?? 'global') === 'mesa'): ?>    bg-info-subtle    text-info    <?php endif; ?>
                                                "><?php echo e(ucfirst($role->default_scope ?? 'global')); ?></span>
                                                <?php if($role->description): ?>
                                                <br><small class="text-muted"><?php echo e($role->description); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>

                    </div>

                    
                    <div class="col-md-7">
                        <div class="card">
                            <div class="card-header bg-soft-info d-flex justify-content-between align-items-center">
                                <h6 class="card-title mb-0">Permisos</h6>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-soft-success" id="selAllPerms">Todos</button>
                                    <button type="button" class="btn btn-sm btn-soft-danger"  id="deselAllPerms">Ninguno</button>
                                </div>
                            </div>
                            <div class="card-body" style="max-height:500px;overflow-y:auto;">
                                <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group => $groupPermissions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="permission-group">
                                    <div class="permission-group-header">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input grp-cb"
                                                   id="grp_<?php echo e(Str::slug($group)); ?>" data-group="<?php echo e($group); ?>">
                                            <label class="form-check-label fw-bold" for="grp_<?php echo e(Str::slug($group)); ?>"><?php echo e($group); ?></label>
                                        </div>
                                    </div>
                                    <div class="permission-group-body">
                                        <?php $__currentLoopData = $groupPermissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $perm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input perm-cb"
                                                   id="perm_<?php echo e($perm->id); ?>"
                                                   name="permissions[]"
                                                   value="<?php echo e($perm->id); ?>"
                                                   data-group="<?php echo e($group); ?>"
                                                   <?php echo e(in_array($perm->id, $userPermissions) ? 'checked' : ''); ?>>
                                            <label class="form-check-label" for="perm_<?php echo e($perm->id); ?>">
                                                <?php echo e($perm->display_name ?? $perm->name); ?>

                                            </label>
                                        </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">
                            Tipo de Elección
                            <small class="text-muted fw-normal">(para los roles — opcional)</small>
                        </label>
                        <select class="form-select" id="sharedEt">
                            <option value="">Todas las elecciones</option>
                            <?php $__currentLoopData = $electionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $et): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($et->id); ?>"><?php echo e($et->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <small class="text-muted">
                            Deja en "Todas" si el usuario trabaja ambas elecciones del día.
                        </small>
                    </div>
                </div>

                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line align-middle me-1"></i> Guardar Roles y Permisos
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    
    
    
    <?php if(auth()->user()->allPermissions->contains('name', 'assign_delegates')): ?>

    <div class="card delegation-card locked" id="cardRecinto" style="display:none">
        <div class="card-header">
            <h5 class="card-title mb-1">
                <i class="ri-building-line align-middle me-2 text-success"></i>
                Delegación en Recinto
            </h5>
            <p class="text-muted small mb-0" id="recintoHint">
                Activa al seleccionar un rol de ámbito <strong>Recinto</strong>.
            </p>
        </div>
        <div class="card-body">
            <form action="<?php echo e(route('users.assign-institution', $user)); ?>" method="POST" id="formRecinto">
                <?php echo csrf_field(); ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Recinto <span class="text-danger">*</span></label>
                        <select class="form-select <?php $__errorArgs = ['institution_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                name="institution_id" id="recintoSelect" required>
                            <option value="">Seleccione recinto…</option>
                            <?php $__currentLoopData = $institutions->groupBy(fn($i) => $i->municipality?->name ?? 'Sin municipio'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $municipio => $instList): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <optgroup label="<?php echo e($municipio); ?>">
                                <?php $__currentLoopData = $instList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inst): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($inst->id); ?>" <?php echo e(old('institution_id') == $inst->id ? 'selected' : ''); ?>>
                                    <?php echo e($inst->name); ?> (<?php echo e($inst->code); ?>)
                                </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </optgroup>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['institution_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Función <span class="text-danger">*</span></label>
                        <select class="form-select <?php $__errorArgs = ['delegate_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                name="delegate_type" required>
                            <option value="">Seleccione…</option>
                            <option value="delegado_general" <?php echo e(old('delegate_type') == 'delegado_general' ? 'selected' : ''); ?>>Delegado General</option>
                            <option value="tecnico"          <?php echo e(old('delegate_type') == 'tecnico'          ? 'selected' : ''); ?>>Técnico / Soporte</option>
                            <option value="observador"       <?php echo e(old('delegate_type') == 'observador'       ? 'selected' : ''); ?>>Observador</option>
                        </select>
                        <?php $__errorArgs = ['delegate_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>


                    <div class="col-md-4">
                        <label class="form-label">Fecha Asignación</label>
                        <input type="date" class="form-control" name="assignment_date"
                               value="<?php echo e(old('assignment_date', now()->format('Y-m-d'))); ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Expiración <small class="text-muted">(opcional)</small></label>
                        <input type="date" class="form-control" name="expiration_date"
                               id="recintoExpDate" value="<?php echo e(old('expiration_date')); ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">N° Credencial <small class="text-muted">(opcional)</small></label>
                        <input type="text" class="form-control" name="credential_number" value="<?php echo e(old('credential_number')); ?>">
                    </div>
                </div>
                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="ri-building-line align-middle me-1"></i> Agregar Delegación en Recinto
                    </button>
                </div>
            </form>
        </div>
    </div>

    
    

    <div class="card delegation-card locked" id="cardMesa" style="display:none">
        <div class="card-header">
            <h5 class="card-title mb-1">
                <i class="ri-table-line align-middle me-2 text-info"></i>
                Delegación en Mesa de Votación
            </h5>
            <p class="text-muted small mb-0">
                Activa al seleccionar un rol de ámbito <strong>Mesa</strong>.
                Primero elige el recinto y luego la mesa dentro de ese recinto.
            </p>
        </div>
        <div class="card-body">
            <form action="<?php echo e(route('users.assign-table', $user)); ?>" method="POST" id="formMesa">
                <?php echo csrf_field(); ?>
                
                <input type="hidden" name="institution_id" id="mesaInstId" value="">

                <div class="row g-3">

                    
                    <div class="col-md-6">
                        <label class="form-label">
                            <span class="badge bg-secondary me-1">1</span>
                            Recinto <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="mesaRecintoFilter" required>
                            <option value="">Seleccione recinto…</option>
                            <?php $__currentLoopData = $institutions->groupBy(fn($i) => $i->municipality?->name ?? 'Sin municipio'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $municipio => $instList): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <optgroup label="<?php echo e($municipio); ?>">
                                <?php $__currentLoopData = $instList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inst): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($inst->id); ?>">
                                    <?php echo e($inst->name); ?> (<?php echo e($inst->code); ?>)
                                </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </optgroup>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    
                    <div class="col-md-6">
                        <label class="form-label">
                            <span class="badge bg-secondary me-1">2</span>
                            Mesa <span class="text-danger">*</span>
                        </label>
                        <select class="form-select <?php $__errorArgs = ['voting_table_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                name="voting_table_id" id="mesaSelect" required disabled>
                            <option value="">Primero seleccione recinto…</option>
                            
                            <?php $__currentLoopData = $votingTables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($table->id); ?>"
                                    data-inst-id="<?php echo e($table->institution_id); ?>"
                                    <?php echo e(old('voting_table_id') == $table->id ? 'selected' : ''); ?>>
                                Mesa <?php echo e($table->number); ?><?php echo e($table->letter ? ' ('.$table->letter.')' : ''); ?>

                                — <?php echo e(ucfirst($table->type)); ?>

                                (<?php echo e($table->oep_code ?? $table->internal_code); ?>)
                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['voting_table_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Función en la Mesa <span class="text-danger">*</span></label>
                        <select class="form-select <?php $__errorArgs = ['delegate_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                name="delegate_type" required>
                            <option value="">Seleccione…</option>
                            <option value="presidente"    <?php echo e(old('delegate_type') == 'presidente'    ? 'selected' : ''); ?>>Presidente de Mesa</option>
                            <option value="secretario"    <?php echo e(old('delegate_type') == 'secretario'    ? 'selected' : ''); ?>>Secretario</option>
                            <option value="vocal"         <?php echo e(old('delegate_type') == 'vocal'         ? 'selected' : ''); ?>>Vocal</option>
                            <option value="delegado_mesa" <?php echo e(old('delegate_type') == 'delegado_mesa' ? 'selected' : ''); ?>>Delegado de Mesa</option>
                        </select>
                        <?php $__errorArgs = ['delegate_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>


                    <div class="col-md-4">
                        <label class="form-label">Fecha Asignación</label>
                        <input type="date" class="form-control" name="assignment_date"
                               value="<?php echo e(old('assignment_date', now()->format('Y-m-d'))); ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Expiración <small class="text-muted">(opcional)</small></label>
                        <input type="date" class="form-control" name="expiration_date"
                               id="mesaExpDate" value="<?php echo e(old('expiration_date')); ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">N° Credencial <small class="text-muted">(opcional)</small></label>
                        <input type="text" class="form-control" name="credential_number" value="<?php echo e(old('credential_number')); ?>">
                    </div>
                </div>
                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-info text-white btn-sm">
                        <i class="ri-table-line align-middle me-1"></i> Agregar Delegación en Mesa
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php endif; ?> 
</div>


<div class="col-lg-4">

    <div class="card mb-3">
        <div class="card-header py-2">
            <h6 class="mb-0 small text-uppercase fw-semibold text-muted">Roles Actuales</h6>
        </div>
        <div class="card-body p-0">
            <?php $__empty_1 = true; $__currentLoopData = $currentRoles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="px-3 py-2 border-bottom">
                <span class="fw-semibold"><?php echo e($role->display_name ?? $role->name); ?></span>
                <div class="d-flex gap-1 mt-1 flex-wrap">
                    <?php switch($role->pivot->scope):
                        case ('global'): ?>  <span class="badge bg-primary-subtle text-primary">Global</span>  <?php break; ?>
                        <?php case ('recinto'): ?> <span class="badge bg-success-subtle text-success">Recinto</span> <?php break; ?>
                        <?php case ('mesa'): ?>    <span class="badge bg-info-subtle text-info">Mesa</span>          <?php break; ?>
                    <?php endswitch; ?>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-muted small text-center py-3 mb-0">Sin roles asignados</p>
            <?php endif; ?>
        </div>
    </div>

    <?php if(auth()->user()->allPermissions->contains('name', 'assign_delegates')): ?>

    <div class="card mb-3">
        <div class="card-header py-2">
            <h6 class="mb-0 small text-uppercase fw-semibold text-muted">Delegaciones en Recinto</h6>
        </div>
        <div class="card-body py-2 px-2">
            <?php $ras = $user->assignments->where('status','activo')->whereNull('voting_table_id'); ?>
            <?php $__empty_1 = true; $__currentLoopData = $ras; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="a-pill">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-semibold small"><?php echo e($a->institution?->name ?? '—'); ?></div>
                        <div class="d-flex gap-1 mt-1">
                            <span class="badge bg-success-subtle text-success"><?php echo e($a->delegate_type_label); ?></span>
                        </div>
                        <div class="text-muted mt-1" style="font-size:.7rem">Desde <?php echo e($a->assignment_date?->format('d/m/Y') ?? '—'); ?></div>
                    </div>
                    <form action="<?php echo e(route('users.remove-assignment', [$user, $a])); ?>" method="POST"
                          onsubmit="return confirm('¿Remover delegación?')">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-xs btn-soft-danger"><i class="ri-close-line"></i></button>
                    </form>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-muted small text-center py-2 mb-0">Sin delegaciones de recinto</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header py-2">
            <h6 class="mb-0 small text-uppercase fw-semibold text-muted">Delegaciones en Mesa</h6>
        </div>
        <div class="card-body py-2 px-2">
            <?php $mas = $user->assignments->where('status','activo')->whereNotNull('voting_table_id'); ?>
            <?php $__empty_1 = true; $__currentLoopData = $mas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="a-pill mesa">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-semibold small">
                            Mesa <?php echo e($a->votingTable?->number ?? '—'); ?>

                            <span class="fw-normal text-muted">· <?php echo e($a->votingTable?->institution?->name ?? '—'); ?></span>
                        </div>
                        <div class="d-flex gap-1 mt-1">
                            <span class="badge bg-info-subtle text-info"><?php echo e($a->delegate_type_label); ?></span>
                        </div>
                        <div class="text-muted mt-1" style="font-size:.7rem">Desde <?php echo e($a->assignment_date?->format('d/m/Y') ?? '—'); ?></div>
                    </div>
                    <form action="<?php echo e(route('users.remove-assignment', [$user, $a])); ?>" method="POST"
                          onsubmit="return confirm('¿Remover delegación?')">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-xs btn-soft-danger"><i class="ri-close-line"></i></button>
                    </form>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-muted small text-center py-2 mb-0">Sin delegaciones de mesa</p>
            <?php endif; ?>
        </div>
    </div>

    <?php endif; ?>
</div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script src="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.js')); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Data from PHP ─────────────────────────────────────────────────────────
    const ROLE_PERMS  = <?php echo json_encode($roles->mapWithKeys(fn($r) => [$r->id => $r->permissions->pluck('id')]), 15, 512) ?>;
    const ROLE_SCOPE  = <?php echo json_encode($roles->mapWithKeys(fn($r) => [$r->id => $r->default_scope ?? 'global']), 15, 512) ?>;
    const SCOPE_RANK  = { global: 0, recinto: 1, mesa: 2 };

    // ── Scope state ───────────────────────────────────────────────────────────
    function highestScope() {
        let best = 'global';
        document.querySelectorAll('.role-cb:checked').forEach(cb => {
            const s = ROLE_SCOPE[cb.dataset.roleId] ?? 'global';
            if (SCOPE_RANK[s] > SCOPE_RANK[best]) best = s;
        });
        return best;
    }

    // ── Show/hide delegation cards based on scope ─────────────────────────────
    //
    //   global  → both cards hidden
    //   recinto → cardRecinto shown+unlocked,  cardMesa hidden
    //   mesa    → cardRecinto hidden,           cardMesa shown+unlocked
    //             (recinto is read-only text derived from the selected mesa)
    //
    function applyScope() {
        const scope = highestScope();
        const cardR = document.getElementById('cardRecinto');
        const cardM = document.getElementById('cardMesa');

        if (scope === 'global') {
            cardR.style.display = 'none';
            cardM.style.display = 'none';
        } else if (scope === 'recinto') {
            cardR.style.display = '';
            cardR.classList.remove('locked');
            cardM.style.display = 'none';
        } else { // mesa
            cardR.style.display = 'none';   // ← hides the recinto picker entirely
            cardM.style.display = '';
            cardM.classList.remove('locked');
        }

        // Sync hidden role inputs
        document.querySelectorAll('.role-cb').forEach(cb => {
            const id = cb.dataset.roleId;
            const on = cb.checked;
            document.getElementById('hScope_' + id).disabled = !on;
            document.getElementById('hEt_'    + id).disabled = !on;
            document.querySelector(`.h-role-id[value="${id}"]`).disabled = !on;
            if (on) document.getElementById('hScope_' + id).value = ROLE_SCOPE[id] ?? 'global';
        });

        syncElectionType();
    }

    // ── Roles → permissions ───────────────────────────────────────────────────
    function updatePermsFromRoles() {
        const enabled = new Set();
        document.querySelectorAll('.role-cb:checked').forEach(cb => {
            (ROLE_PERMS[cb.dataset.roleId] ?? []).forEach(id => enabled.add(Number(id)));
        });
        document.querySelectorAll('.perm-cb').forEach(cb => {
            if (enabled.has(Number(cb.value))) cb.checked = true;
        });
        syncGroups();
    }

    function syncGroups() {
        document.querySelectorAll('.grp-cb').forEach(gc => {
            const all = [...document.querySelectorAll(`.perm-cb[data-group="${gc.dataset.group}"]`)];
            const chk = all.filter(c => c.checked);
            gc.checked       = all.length > 0 && chk.length === all.length;
            gc.indeterminate = chk.length > 0 && chk.length < all.length;
        });
    }



    // ── Election type → push to all active role hidden et inputs ─────────────
    function syncElectionType() {
        const val = document.getElementById('sharedEt')?.value ?? '';
        document.querySelectorAll('.h-role-et:not([disabled])').forEach(inp => inp.value = val);
    }
    document.getElementById('sharedEt')?.addEventListener('change', syncElectionType);

    // ── Role checkboxes ───────────────────────────────────────────────────────
    document.querySelectorAll('.role-cb').forEach(cb => {
        cb.addEventListener('change', function () {
            this.closest('.role-card').classList.toggle('selected', this.checked);
            updatePermsFromRoles();
            applyScope();
        });
    });

    // ── Permission group toggles ──────────────────────────────────────────────
    document.querySelectorAll('.grp-cb').forEach(gc => {
        gc.addEventListener('change', function () {
            document.querySelectorAll(`.perm-cb[data-group="${this.dataset.group}"]`)
                    .forEach(cb => cb.checked = this.checked);
        });
    });
    document.querySelectorAll('.perm-cb').forEach(cb => cb.addEventListener('change', syncGroups));
    document.getElementById('selAllPerms')?.addEventListener('click', () => {
        document.querySelectorAll('.perm-cb').forEach(cb => cb.checked = true); syncGroups();
    });
    document.getElementById('deselAllPerms')?.addEventListener('click', () => {
        document.querySelectorAll('.perm-cb').forEach(cb => cb.checked = false); syncGroups();
    });

    // ── Mesa section: recinto picker → filters mesa dropdown ────────────────
    const mesaRecintoFilter = document.getElementById('mesaRecintoFilter');
    const mesaSelect        = document.getElementById('mesaSelect');
    const mesaInstId        = document.getElementById('mesaInstId');

    function filterMesasByRecinto() {
        const instId = mesaRecintoFilter?.value ?? '';

        // Set hidden institution_id from recinto picker
        if (mesaInstId) mesaInstId.value = instId;

        if (!mesaSelect) return;

        // Reset mesa dropdown
        mesaSelect.value    = '';
        mesaSelect.disabled = !instId;
        mesaSelect.options[0].text = instId ? 'Seleccione mesa…' : 'Primero seleccione recinto…';

        // Show only options matching the chosen recinto; hide the rest
        let visibleCount = 0;
        Array.from(mesaSelect.options).forEach(opt => {
            if (!opt.value) return; // skip placeholder
            const match = opt.dataset.instId === instId;
            opt.style.display = match ? '' : 'none';
            if (match) visibleCount++;
        });

        if (visibleCount === 0 && instId) {
            mesaSelect.options[0].text = 'Sin mesas disponibles en este recinto';
        }
    }

    mesaRecintoFilter?.addEventListener('change', filterMesasByRecinto);

    document.getElementById('formMesa')?.addEventListener('submit', function (e) {
        if (!mesaInstId?.value) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Selecciona un recinto',
                        text: 'Debes elegir un recinto antes de seleccionar la mesa.' });
            return;
        }
        if (!mesaSelect?.value) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Selecciona una mesa',
                        text: 'Debes elegir una mesa de votación.' });
        }
    });

    // ── Expiration guards ─────────────────────────────────────────────────────
    ['recintoExpDate', 'mesaExpDate'].forEach(expId => {
        document.getElementById(expId)?.addEventListener('change', function () {
            const assignVal = this.closest('form')?.querySelector('[name="assignment_date"]')?.value;
            if (assignVal && this.value && this.value < assignVal) {
                Swal.fire({ icon: 'error', title: 'Fecha inválida',
                            text: 'La expiración debe ser posterior a la fecha de asignación.' });
                this.value = '';
            }
        });
    });

    // ── Init ─────────────────────────────────────────────────────────────────
    syncGroups();
    applyScope();
    filterMesasByRecinto();
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\_Mine\sistema_electoral\resources\views/users/assign-roles.blade.php ENDPATH**/ ?>