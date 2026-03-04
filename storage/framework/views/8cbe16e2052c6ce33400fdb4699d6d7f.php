


<?php $__env->startSection('title'); ?>
    Editar Usuario
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <link href="<?php echo e(URL::asset('build/libs/choices.js/public/assets/styles/choices.min.css')); ?>" rel="stylesheet" type="text/css" />
    <style>
        /* Mismos estilos que en create.blade.php */
        .permission-group { border: 1px solid #e9e9ef; border-radius: 0.25rem; margin-bottom: 1rem; }
        .permission-group-header { background-color: #f3f6f9; padding: 0.75rem 1rem; border-bottom: 1px solid #e9e9ef; font-weight: 600; }
        .permission-group-body { padding: 1rem; display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.5rem; }
        .role-card { border: 1px solid #e9e9ef; border-radius: 0.25rem; padding: 0.75rem; margin-bottom: 0.5rem; cursor: pointer; transition: all 0.2s; }
        .role-card:hover { background-color: #f3f6f9; }
        .role-card.selected { background-color: #e7f1ff; border-color: #0ab39c; }
        .role-card input { margin-right: 0.5rem; }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?>
            Usuarios
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('li_2'); ?>
            <a href="<?php echo e(route('users.index')); ?>">Lista de Usuarios</a>
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?>
            Editar: <?php echo e($user->name); ?> <?php echo e($user->last_name); ?>

        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Editar Información del Usuario</h4>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('users.update', $user)); ?>" method="POST" id="editUserForm">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3">Datos Personales</h5>
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nombres <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="name" name="name" value="<?php echo e(old('name', $user->name)); ?>" required>
                                    <?php $__errorArgs = ['name'];
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
                                    <label for="last_name" class="form-label">Apellidos</label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="last_name" name="last_name" value="<?php echo e(old('last_name', $user->last_name)); ?>">
                                    <?php $__errorArgs = ['last_name'];
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
                                    <label for="id_card" class="form-label">Carnet de Identidad</label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['id_card'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="id_card" name="id_card" value="<?php echo e(old('id_card', $user->id_card)); ?>">
                                    <?php $__errorArgs = ['id_card'];
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
                                    <label for="phone" class="form-label">Teléfono</label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="phone" name="phone" value="<?php echo e(old('phone', $user->phone)); ?>">
                                    <?php $__errorArgs = ['phone'];
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
                                    <label for="address" class="form-label">Dirección</label>
                                    <textarea class="form-control <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                              id="address" name="address" rows="2"><?php echo e(old('address', $user->address)); ?></textarea>
                                    <?php $__errorArgs = ['address'];
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
                                <h5 class="mb-3">Credenciales de Acceso</h5>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="email" name="email" value="<?php echo e(old('email', $user->email)); ?>" required>
                                    <?php $__errorArgs = ['email'];
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
                                    <label for="password" class="form-label">Nueva Contraseña</label>
                                    <input type="password" class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="password" name="password">
                                    <small class="text-muted">Dejar en blanco para mantener la contraseña actual</small>
                                    <?php $__errorArgs = ['password'];
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
                                    <label for="password_confirmation" class="form-label">Confirmar Nueva Contraseña</label>
                                    <input type="password" class="form-control" 
                                           id="password_confirmation" name="password_confirmation">
                                </div>

                                <div class="mb-3 form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" 
                                           id="is_active" name="is_active" value="1" <?php echo e($user->is_active ? 'checked' : ''); ?>>
                                    <label class="form-check-label" for="is_active">
                                        <strong>Usuario activo</strong>
                                        <small class="d-block text-muted">Desactive para bloquear el acceso al sistema</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <h5 class="mb-3">Roles y Permisos</h5>
                                
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="card">
                                            <div class="card-header bg-soft-primary">
                                                <h6 class="card-title mb-0">Roles (selecciona uno o varios)</h6>
                                            </div>
                                            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="role-card" data-role-id="<?php echo e($role->id); ?>" 
                                                     data-permissions='<?php echo json_encode($role->permissions->pluck('id'), 15, 512) ?>'>
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input role-checkbox" 
                                                               id="role_<?php echo e($role->id); ?>" name="roles[]" value="<?php echo e($role->id); ?>"
                                                               <?php echo e(in_array($role->id, $userRoles) ? 'checked' : ''); ?>>
                                                        <label class="form-check-label" for="role_<?php echo e($role->id); ?>">
                                                            <strong><?php echo e($role->display_name); ?></strong>
                                                            <br>
                                                            <small class="text-muted"><?php echo e($role->description); ?></small>
                                                        </label>
                                                    </div>
                                                </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        </div>
                                        <div class="alert alert-info">
                                            <i class="ri-information-line"></i>
                                            <small>Al seleccionar un rol, los permisos asociados se marcarán automáticamente. Puedes personalizar los permisos manualmente.</small>
                                        </div>
                                    </div>

                                    <div class="col-md-7">
                                        <div class="card">
                                            <div class="card-header bg-soft-info d-flex justify-content-between align-items-center">
                                                <h6 class="card-title mb-0">Permisos</h6>
                                                <div>
                                                    <button type="button" class="btn btn-sm btn-soft-success" id="select-all-permissions">
                                                        Seleccionar Todos
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-soft-danger" id="deselect-all-permissions">
                                                        Deseleccionar Todos
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                                                <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group => $groupPermissions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="permission-group">
                                                    <div class="permission-group-header">
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input group-checkbox" 
                                                                   id="group_<?php echo e(Str::slug($group)); ?>" 
                                                                   data-group="<?php echo e($group); ?>">
                                                            <label class="form-check-label fw-bold" for="group_<?php echo e(Str::slug($group)); ?>">
                                                                <?php echo e($group); ?>

                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="permission-group-body">
                                                        <?php $__currentLoopData = $groupPermissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input permission-checkbox" 
                                                                   id="perm_<?php echo e($permission->id); ?>" name="permissions[]" 
                                                                   value="<?php echo e($permission->id); ?>"
                                                                   data-group="<?php echo e($group); ?>"
                                                                   <?php echo e(in_array($permission->id, $userPermissions) ? 'checked' : ''); ?>>
                                                            <label class="form-check-label" for="perm_<?php echo e($permission->id); ?>">
                                                                <?php echo e($permission->display_name); ?>

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
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-end">
                                <a href="<?php echo e(route('users.show', $user)); ?>" class="btn btn-soft-secondary me-2">
                                    Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line align-middle me-1"></i> Actualizar Usuario
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script src="<?php echo e(URL::asset('build/libs/choices.js/public/assets/scripts/choices.min.js')); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // ===== AUTOASIGNACIÓN DE PERMISOS POR ROL =====
    const roleCheckboxes = document.querySelectorAll('.role-checkbox');
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
    const groupCheckboxes = document.querySelectorAll('.group-checkbox');
    
    // Objeto para almacenar permisos por rol
    const rolePermissions = {};
    
    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        rolePermissions[<?php echo e($role->id); ?>] = <?php echo json_encode($role->permissions->pluck('id'), 15, 512) ?>;
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    
    // Función para actualizar permisos basado en roles seleccionados
    function updatePermissionsFromRoles() {
        // Obtener todos los permisos de los roles seleccionados
        const selectedRoles = [];
        roleCheckboxes.forEach(cb => {
            if (cb.checked) {
                selectedRoles.push(parseInt(cb.value));
            }
        });
        
        // Si no hay roles seleccionados, no hacer nada
        if (selectedRoles.length === 0) return;
        
        // Recolectar todos los permisos de los roles seleccionados
        const permissionsToEnable = new Set();
        selectedRoles.forEach(roleId => {
            if (rolePermissions[roleId]) {
                rolePermissions[roleId].forEach(permId => {
                    permissionsToEnable.add(parseInt(permId));
                });
            }
        });
        
        // Marcar los permisos (sin desmarcar los que ya estaban marcados manualmente)
        permissionCheckboxes.forEach(cb => {
            const permId = parseInt(cb.value);
            if (permissionsToEnable.has(permId)) {
                cb.checked = true;
            }
            // NOTA: No desmarcamos permisos existentes para respetar selecciones manuales
        });
        
        // Actualizar checkboxes de grupo
        updateGroupCheckboxes();
    }
    
    // Event listener para cambios en roles
    roleCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            updatePermissionsFromRoles();
        });
    });
    
    // ===== FUNCIONALIDAD DE GRUPOS =====
    
    // Seleccionar/deseleccionar todos los permisos de un grupo
    groupCheckboxes.forEach(groupCb => {
        groupCb.addEventListener('change', function() {
            const group = this.dataset.group;
            const groupPermissions = document.querySelectorAll(`.permission-checkbox[data-group="${group}"]`);
            groupPermissions.forEach(cb => {
                cb.checked = groupCb.checked;
            });
        });
    });
    
    // Actualizar estado del checkbox de grupo basado en permisos individuales
    function updateGroupCheckboxes() {
        groupCheckboxes.forEach(groupCb => {
            const group = groupCb.dataset.group;
            const groupPermissions = document.querySelectorAll(`.permission-checkbox[data-group="${group}"]`);
            const checkedPermissions = document.querySelectorAll(`.permission-checkbox[data-group="${group}"]:checked`);
            
            if (groupPermissions.length === 0) return;
            
            if (checkedPermissions.length === groupPermissions.length) {
                groupCb.checked = true;
                groupCb.indeterminate = false;
            } else if (checkedPermissions.length > 0) {
                groupCb.checked = false;
                groupCb.indeterminate = true;
            } else {
                groupCb.checked = false;
                groupCb.indeterminate = false;
            }
        });
    }
    
    // Actualizar grupos cuando cambia un permiso individual
    permissionCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            updateGroupCheckboxes();
        });
    });
    
    // ===== BOTONES DE SELECCIÓN MASIVA =====
    
    document.getElementById('select-all-permissions').addEventListener('click', function() {
        permissionCheckboxes.forEach(cb => cb.checked = true);
        updateGroupCheckboxes();
    });
    
    document.getElementById('deselect-all-permissions').addEventListener('click', function() {
        permissionCheckboxes.forEach(cb => cb.checked = false);
        updateGroupCheckboxes();
    });
    
    // ===== VALIDACIÓN DE CONTRASEÑA =====
    const form = document.getElementById('editUserForm');
    form.addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirm = document.getElementById('password_confirmation').value;
        
        if (password !== confirm) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Las contraseñas no coinciden'
            });
        }
    });
    
    updateGroupCheckboxes();
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\_Mine\corporate\resources\views/users/edit.blade.php ENDPATH**/ ?>