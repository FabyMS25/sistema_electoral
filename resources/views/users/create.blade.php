{{-- resources/views/users/create.blade.php --}}
@extends('layouts.master')

@section('title') Crear Usuario @endsection

@section('css')
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
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
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Usuarios @endslot
        @slot('li_2') <a href="{{ route('users.index') }}">Lista de Usuarios</a> @endslot
        @slot('title') Crear Nuevo Usuario @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Información del Usuario</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.store') }}" method="POST" id="createUserForm">
                        @csrf

                        {{-- ── Row 1: Avatar | Personal | Credentials ───── --}}
                        <div class="row">

                            {{-- Avatar panel --}}
                            <div class="col-md-2">
                                <h5 class="mb-3">Avatar</h5>
                                <div class="avatar-preview-wrap">
                                    <img id="avatarPreview"
                                         src="{{ URL::asset('build/images/users/avatar-op-m.png') }}"
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
                                               {{ old('gender', 'm') === 'm' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-secondary btn-sm" for="genderM">
                                            <i class="ri-men-line me-1"></i>M
                                        </label>
                                        <input type="radio" class="btn-check" name="gender"
                                               id="genderW" value="w"
                                               {{ old('gender') === 'w' ? 'checked' : '' }}>
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

                            {{-- Personal data --}}
                            <div class="col-md-5">
                                <h5 class="mb-3">Datos Personales</h5>

                                <div class="mb-3">
                                    <label for="name" class="form-label">Nombres <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="last_name" class="form-label">Apellidos</label>
                                    <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                           id="last_name" name="last_name" value="{{ old('last_name') }}">
                                    @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="id_card" class="form-label">Carnet de Identidad</label>
                                    <input type="text" class="form-control @error('id_card') is-invalid @enderror"
                                           id="id_card" name="id_card" value="{{ old('id_card') }}">
                                    @error('id_card') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Teléfono</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                           id="phone" name="phone" value="{{ old('phone') }}">
                                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Dirección</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror"
                                              id="address" name="address" rows="2">{{ old('address') }}</textarea>
                                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            {{-- Credentials --}}
                            <div class="col-md-5">
                                <h5 class="mb-3">Credenciales de Acceso</h5>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                           id="password" name="password" required>
                                    <small class="text-muted">Mínimo 8 caracteres</small>
                                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
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

                        {{-- ── Row 2: Roles + Permissions ───────────────── --}}
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
                                                @foreach($roles as $role)
                                                <div class="role-card"
                                                     data-role-id="{{ $role->id }}"
                                                     data-role-name="{{ strtolower($role->name) }}">
                                                    <div class="form-check">
                                                        <input type="checkbox"
                                                               class="form-check-input role-checkbox"
                                                               id="role_{{ $role->id }}"
                                                               name="roles[]"
                                                               value="{{ $role->id }}"
                                                               {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="role_{{ $role->id }}">
                                                            <strong>{{ $role->display_name ?? $role->name }}</strong>
                                                            @if($role->description)
                                                            <br><small class="text-muted">{{ $role->description }}</small>
                                                            @endif
                                                        </label>
                                                    </div>
                                                </div>
                                                @endforeach
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
                                                @foreach($permissions as $group => $groupPermissions)
                                                <div class="permission-group">
                                                    <div class="permission-group-header">
                                                        <div class="form-check">
                                                            <input type="checkbox"
                                                                   class="form-check-input group-checkbox"
                                                                   id="group_{{ Str::slug($group) }}"
                                                                   data-group="{{ $group }}">
                                                            <label class="form-check-label fw-bold"
                                                                   for="group_{{ Str::slug($group) }}">
                                                                {{ $group }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="permission-group-body">
                                                        @foreach($groupPermissions as $permission)
                                                        <div class="form-check">
                                                            <input type="checkbox"
                                                                   class="form-check-input permission-checkbox"
                                                                   id="perm_{{ $permission->id }}"
                                                                   name="permissions[]"
                                                                   value="{{ $permission->id }}"
                                                                   data-group="{{ $group }}"
                                                                   {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                   for="perm_{{ $permission->id }}">
                                                                {{ $permission->display_name ?? $permission->name }}
                                                            </label>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-end">
                                <a href="{{ route('users.index') }}" class="btn btn-soft-secondary me-2">Cancelar</a>
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
@endsection

@section('script')
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
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

    const AVATAR_BASE = '{{ URL::asset("build/images/users") }}';

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

    const rolePermissions = @json(
        $roles->mapWithKeys(fn($r) => [$r->id => $r->permissions->pluck('id')])
    );

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
        fetch(`{{ route('users.check-email') }}?email=${encodeURIComponent(email)}`)
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
@endsection
