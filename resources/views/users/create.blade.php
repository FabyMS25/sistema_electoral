{{-- resources/views/users/create.blade.php --}}
@extends('layouts.master')

@section('title')
    Crear Usuario
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .permission-group {
            border: 1px solid #e9e9ef;
            border-radius: 0.25rem;
            margin-bottom: 1rem;
        }
        .permission-group-header {
            background-color: #f3f6f9;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e9e9ef;
            font-weight: 600;
        }
        .permission-group-body {
            padding: 1rem;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 0.5rem;
        }
        .role-card {
            border: 1px solid #e9e9ef;
            border-radius: 0.25rem;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .role-card:hover {
            background-color: #f3f6f9;
        }
        .role-card.selected {
            background-color: #e7f1ff;
            border-color: #0ab39c;
        }
        .role-card input {
            margin-right: 0.5rem;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Usuarios
        @endslot
        @slot('li_2')
            <a href="{{ route('users.index') }}">Lista de Usuarios</a>
        @endslot
        @slot('title')
            Crear Nuevo Usuario
        @endslot
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
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3">Datos Personales</h5>
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nombres <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="last_name" class="form-label">Apellidos</label>
                                    <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                           id="last_name" name="last_name" value="{{ old('last_name') }}">
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="id_card" class="form-label">Carnet de Identidad</label>
                                    <input type="text" class="form-control @error('id_card') is-invalid @enderror" 
                                           id="id_card" name="id_card" value="{{ old('id_card') }}">
                                    @error('id_card')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Teléfono</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone') }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Dirección</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" name="address" rows="2">{{ old('address') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5 class="mb-3">Credenciales de Acceso</h5>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password" required>
                                    <small class="text-muted">Mínimo 8 caracteres</small>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" 
                                           id="password_confirmation" name="password_confirmation" required>
                                </div>

                                <div class="mb-3 form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" 
                                           id="is_active" name="is_active" value="1" checked>
                                    <label class="form-check-label" for="is_active">
                                        <strong>Usuario activo</strong>
                                        <small class="d-block text-muted">Desactive si el usuario no debe acceder al sistema</small>
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
                                                @foreach($roles as $role)
                                                <div class="role-card" data-role-id="{{ $role->id }}" 
                                                     data-permissions='@json($role->permissions->pluck('id'))'>
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input role-checkbox" 
                                                               id="role_{{ $role->id }}" name="roles[]" value="{{ $role->id }}"
                                                               {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="role_{{ $role->id }}">
                                                            <strong>{{ $role->display_name }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $role->description }}</small>
                                                        </label>
                                                    </div>
                                                </div>
                                                @endforeach
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
                                                @foreach($permissions as $group => $groupPermissions)
                                                <div class="permission-group">
                                                    <div class="permission-group-header">
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input group-checkbox" 
                                                                   id="group_{{ Str::slug($group) }}" 
                                                                   data-group="{{ $group }}">
                                                            <label class="form-check-label fw-bold" for="group_{{ Str::slug($group) }}">
                                                                {{ $group }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="permission-group-body">
                                                        @foreach($groupPermissions as $permission)
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input permission-checkbox" 
                                                                   id="perm_{{ $permission->id }}" name="permissions[]" 
                                                                   value="{{ $permission->id }}"
                                                                   data-group="{{ $group }}"
                                                                   {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                                {{ $permission->display_name }}
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
                                <a href="{{ route('users.index') }}" class="btn btn-soft-secondary me-2">
                                    Cancelar
                                </a>
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
<script src="{{ URL::asset('build/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // ===== AUTOASIGNACIÓN DE PERMISOS POR ROL =====
    const roleCheckboxes = document.querySelectorAll('.role-checkbox');
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
    const groupCheckboxes = document.querySelectorAll('.group-checkbox');
    
    // Objeto para almacenar permisos por rol
    const rolePermissions = {};
    
    @foreach($roles as $role)
        rolePermissions[{{ $role->id }}] = @json($role->permissions->pluck('id'));
    @endforeach
    
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
    const form = document.getElementById('createUserForm');
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
    
    // Inicializar grupos al cargar la página
    updateGroupCheckboxes();
});
</script>
@endsection