


<?php $__env->startSection('title'); ?> Crear Usuario <?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>
    <link href="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.css')); ?>" rel="stylesheet" type="text/css" />
    <style>
        .permission-group { border: 1px solid #e9e9ef; border-radius: 0.25rem; margin-bottom: 1rem; }
        .permission-group-header { background-color: #f3f6f9; padding: 0.75rem 1rem; border-bottom: 1px solid #e9e9ef; font-weight: 600; }
        .permission-group-body { padding: 1rem; display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.5rem; }
        .role-card { border: 1px solid #e9e9ef; border-radius: 0.25rem; padding: 0.75rem; margin-bottom: 0.5rem; cursor: pointer; transition: all 0.2s; }
        .role-card:hover { background-color: #f3f6f9; }

        .avatar-preview-wrap {
            text-align: center;
            padding: 1.25rem 1rem;
            background: #f8f9fa;
            border-radius: 0.5rem;
            border: 2px dashed #dee2e6;
        }
        .avatar-preview-wrap img {
            width: 88px; height: 88px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,.12);
            transition: transform .2s;
        }
        .avatar-preview-wrap img:hover { transform: scale(1.05); }
        .gender-toggle .btn-check:checked + .btn {
            background-color: #0ab39c; border-color: #0ab39c; color: #fff;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?> Usuarios <?php $__env->endSlot(); ?>
        <?php $__env->slot('li_2'); ?> <a href="<?php echo e(route('users.index')); ?>">Lista de Usuarios</a> <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?> Crear Nuevo Usuario <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Información del Usuario</h4>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('users.store')); ?>" method="POST" id="createUserForm">
                        <?php echo csrf_field(); ?>

                        
                        <div class="row">

                            
                            <div class="col-md-2">
                                <h5 class="mb-3">Avatar</h5>
                                <div class="avatar-preview-wrap">
                                    <img id="avatarPreview"
                                         src="<?php echo e(URL::asset('build/images/users/avatar-op-m.png')); ?>"
                                         alt="Vista previa del avatar">
                                    <p class="text-muted small mt-2 mb-0" id="avatarRoleHint">
                                        Operador (M)
                                    </p>
                                </div>

                                <div class="mt-3">
                                    <label class="form-label d-block">Género</label>
                                    <div class="btn-group gender-toggle w-100" role="group">
                                        <input type="radio" class="btn-check" name="gender"
                                               id="genderM" value="m"
                                               <?php echo e(old('gender', 'm') === 'm' ? 'checked' : ''); ?>>
                                        <label class="btn btn-outline-secondary btn-sm" for="genderM">
                                            <i class="ri-men-line me-1"></i>M
                                        </label>
                                        <input type="radio" class="btn-check" name="gender"
                                               id="genderW" value="w"
                                               <?php echo e(old('gender') === 'w' ? 'checked' : ''); ?>>
                                        <label class="btn btn-outline-secondary btn-sm" for="genderW">
                                            <i class="ri-women-line me-1"></i>F
                                        </label>
                                    </div>
                                </div>

                                <small class="text-muted d-block mt-2">
                                    <i class="ri-information-line align-middle me-1"></i>
                                    Se asigna automáticamente según el rol seleccionado.
                                </small>
                            </div>

                            
                            <div class="col-md-5">
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
                                           id="name" name="name" value="<?php echo e(old('name')); ?>" required>
                                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
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
                                           id="last_name" name="last_name" value="<?php echo e(old('last_name')); ?>">
                                    <?php $__errorArgs = ['last_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
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
                                           id="id_card" name="id_card" value="<?php echo e(old('id_card')); ?>">
                                    <?php $__errorArgs = ['id_card'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
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
                                           id="phone" name="phone" value="<?php echo e(old('phone')); ?>">
                                    <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
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
                                              id="address" name="address" rows="2"><?php echo e(old('address')); ?></textarea>
                                    <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>

                            
                            <div class="col-md-5">
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
                                           id="email" name="email" value="<?php echo e(old('email')); ?>" required>
                                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           id="password" name="password" required>
                                    <small class="text-muted">Mínimo 8 caracteres</small>
                                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>

                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">
                                        Confirmar Contraseña <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" class="form-control"
                                           id="password_confirmation" name="password_confirmation" required>
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
                                            <div class="card-body" style="max-height:400px;overflow-y:auto;">
                                                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="role-card"
                                                     data-role-id="<?php echo e($role->id); ?>"
                                                     data-role-name="<?php echo e(strtolower($role->name)); ?>">
                                                    <div class="form-check">
                                                        <input type="checkbox"
                                                               class="form-check-input role-checkbox"
                                                               id="role_<?php echo e($role->id); ?>"
                                                               name="roles[]"
                                                               value="<?php echo e($role->id); ?>"
                                                               <?php echo e(in_array($role->id, old('roles', [])) ? 'checked' : ''); ?>>
                                                        <label class="form-check-label" for="role_<?php echo e($role->id); ?>">
                                                            <strong><?php echo e($role->display_name ?? $role->name); ?></strong>
                                                            <?php if($role->description): ?>
                                                            <br><small class="text-muted"><?php echo e($role->description); ?></small>
                                                            <?php endif; ?>
                                                        </label>
                                                    </div>
                                                </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        </div>
                                        <div class="alert alert-info mt-2">
                                            <i class="ri-information-line"></i>
                                            <small>
                                                Al seleccionar un rol, sus permisos se marcarán automáticamente
                                                y el avatar se actualizará en tiempo real.
                                            </small>
                                        </div>
                                    </div>

                                    <div class="col-md-7">
                                        <div class="card">
                                            <div class="card-header bg-soft-info d-flex justify-content-between align-items-center">
                                                <h6 class="card-title mb-0">Permisos</h6>
                                                <div>
                                                    <button type="button" class="btn btn-sm btn-soft-success"
                                                            id="select-all-permissions">
                                                        Seleccionar Todos
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-soft-danger"
                                                            id="deselect-all-permissions">
                                                        Deseleccionar Todos
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-body" style="max-height:500px;overflow-y:auto;">
                                                <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group => $groupPermissions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="permission-group">
                                                    <div class="permission-group-header">
                                                        <div class="form-check">
                                                            <input type="checkbox"
                                                                   class="form-check-input group-checkbox"
                                                                   id="group_<?php echo e(Str::slug($group)); ?>"
                                                                   data-group="<?php echo e($group); ?>">
                                                            <label class="form-check-label fw-bold"
                                                                   for="group_<?php echo e(Str::slug($group)); ?>">
                                                                <?php echo e($group); ?>

                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="permission-group-body">
                                                        <?php $__currentLoopData = $groupPermissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <div class="form-check">
                                                            <input type="checkbox"
                                                                   class="form-check-input permission-checkbox"
                                                                   id="perm_<?php echo e($permission->id); ?>"
                                                                   name="permissions[]"
                                                                   value="<?php echo e($permission->id); ?>"
                                                                   data-group="<?php echo e($group); ?>"
                                                                   <?php echo e(in_array($permission->id, old('permissions', [])) ? 'checked' : ''); ?>>
                                                            <label class="form-check-label"
                                                                   for="perm_<?php echo e($permission->id); ?>">
                                                                <?php echo e($permission->display_name ?? $permission->name); ?>

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
                                <a href="<?php echo e(route('users.index')); ?>" class="btn btn-soft-secondary me-2">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-line align-middle me-1"></i> Crear Usuario
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
<script src="<?php echo e(URL::asset('build/libs/sweetalert2/sweetalert2.min.js')); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ─────────────────────────────────────────────────────────────────────────
    //  AVATAR LOGIC
    //  Must mirror UserController::resolveDefaultAvatar() exactly.
    //
    //  Available files in public/build/images/users/:
    //    avatar-admin-m.png   / avatar-admin-w.png
    //    avatar-manager-m.png / avatar-manager-w.png   ← also used for 'delegado' tier
    //    avatar-op-m.png      / avatar-op-w.png
    // ─────────────────────────────────────────────────────────────────────────

    const AVATAR_BASE = '<?php echo e(URL::asset("build/images/users")); ?>';

    const TIER_ORDER = { admin: 3, manager: 2, delegado: 1, op: 0 };

    // Maps internal tier → actual image file prefix
    const TIER_TO_FILE  = { admin: 'admin', manager: 'manager', delegado: 'manager', op: 'op' };

    const TIER_LABELS = {
        admin:    'Administrador',
        manager:  'Coordinador / Fiscal',
        delegado: 'Delegado / Mesa',
        op:       'Operador',
    };

    function classifyRole(roleName) {
        const n = roleName.toLowerCase();
        if (n.includes('admin') || n.includes('superadmin'))                   return 'admin';
        if (n.includes('coordinador') || n.includes('manager') ||
            n.includes('fiscal')      || n.includes('notario'))                return 'manager';
        if (n.includes('delegado')    || n.includes('presidente') ||
            n.includes('secretario')  || n.includes('vocal'))                  return 'delegado';
        return 'op';
    }

    function updateAvatarPreview() {
        const gender      = document.querySelector('input[name="gender"]:checked')?.value ?? 'm';
        const genderLabel = gender === 'w' ? 'F' : 'M';

        let topTier = 'op';
        document.querySelectorAll('.role-checkbox:checked').forEach(cb => {
            const roleName = cb.closest('.role-card')?.dataset?.roleName ?? '';
            const tier     = classifyRole(roleName);
            if (TIER_ORDER[tier] > TIER_ORDER[topTier]) topTier = tier;
        });

        const fileName = `avatar-${TIER_TO_FILE[topTier]}-${gender}.png`;

        document.getElementById('avatarPreview').src = `${AVATAR_BASE}/${fileName}`;
        document.getElementById('avatarRoleHint').textContent =
            `${TIER_LABELS[topTier]} (${genderLabel})`;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  ROLES → PERMISSIONS AUTO-CHECK
    // ─────────────────────────────────────────────────────────────────────────

    const rolePermissions = <?php echo json_encode(
        $roles->mapWithKeys(fn($r) => [$r->id => $r->permissions->pluck('id')])
    , 15, 512) ?>;

    const roleCheckboxes       = document.querySelectorAll('.role-checkbox');
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
    const groupCheckboxes      = document.querySelectorAll('.group-checkbox');

    function updatePermissionsFromRoles() {
        const enabled = new Set();
        roleCheckboxes.forEach(cb => {
            if (cb.checked && rolePermissions[cb.value]) {
                rolePermissions[cb.value].forEach(id => enabled.add(Number(id)));
            }
        });
        permissionCheckboxes.forEach(cb => {
            if (enabled.has(Number(cb.value))) cb.checked = true;
        });
        updateGroupCheckboxes();
    }

    function updateGroupCheckboxes() {
        groupCheckboxes.forEach(groupCb => {
            const group   = groupCb.dataset.group;
            const all     = document.querySelectorAll(`.permission-checkbox[data-group="${group}"]`);
            const checked = document.querySelectorAll(`.permission-checkbox[data-group="${group}"]:checked`);
            if (!all.length) return;
            if (checked.length === all.length) {
                groupCb.checked = true;  groupCb.indeterminate = false;
            } else if (checked.length > 0) {
                groupCb.checked = false; groupCb.indeterminate = true;
            } else {
                groupCb.checked = false; groupCb.indeterminate = false;
            }
        });
    }

    // Wire events
    roleCheckboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            updatePermissionsFromRoles();
            updateAvatarPreview();
        });
    });

    document.querySelectorAll('input[name="gender"]').forEach(r =>
        r.addEventListener('change', updateAvatarPreview)
    );

    groupCheckboxes.forEach(groupCb => {
        groupCb.addEventListener('change', function () {
            document.querySelectorAll(`.permission-checkbox[data-group="${this.dataset.group}"]`)
                    .forEach(cb => cb.checked = this.checked);
        });
    });

    permissionCheckboxes.forEach(cb => cb.addEventListener('change', updateGroupCheckboxes));

    document.getElementById('select-all-permissions').addEventListener('click', () => {
        permissionCheckboxes.forEach(cb => cb.checked = true);
        updateGroupCheckboxes();
    });

    document.getElementById('deselect-all-permissions').addEventListener('click', () => {
        permissionCheckboxes.forEach(cb => cb.checked = false);
        updateGroupCheckboxes();
    });

    // ─────────────────────────────────────────────────────────────────────────
    //  FORM VALIDATION
    // ─────────────────────────────────────────────────────────────────────────

    document.getElementById('createUserForm').addEventListener('submit', function (e) {
        const pw  = document.getElementById('password').value;
        const pwc = document.getElementById('password_confirmation').value;
        if (pw !== pwc) {
            e.preventDefault();
            Swal.fire({ icon: 'error', title: 'Error', text: 'Las contraseñas no coinciden' });
        }
    });

    document.getElementById('email').addEventListener('blur', function () {
        const email = this.value.trim();
        if (!email) return;
        fetch(`<?php echo e(route('users.check-email')); ?>?email=${encodeURIComponent(email)}`)
            .then(r => r.json())
            .then(data => {
                if (data.exists) {
                    Swal.fire({
                        icon: 'warning', title: 'Email ya registrado',
                        text: 'Este correo electrónico ya está en uso',
                        timer: 3000, showConfirmButton: false,
                    });
                }
            })
            .catch(() => {});
    });

    // ─────────────────────────────────────────────────────────────────────────
    //  INIT
    // ─────────────────────────────────────────────────────────────────────────
    updateGroupCheckboxes();
    updateAvatarPreview();
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\_Mine\sistema_electoral\resources\views/users/create.blade.php ENDPATH**/ ?>