{{-- resources/views/users/permissions.blade.php --}}
@extends('layouts.master')

@section('title')
    Permisos Directos - {{ $user->name }}
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
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
        .scope-badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
            background-color: #e7f1ff;
            color: #0a5dc2;
            border-radius: 0.25rem;
            margin-left: 0.25rem;
        }
        .current-permission {
            border-left: 3px solid #0ab39c;
            background-color: #f0f9f7;
            padding: 0.5rem;
            margin-bottom: 0.5rem;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Usuarios
        @endslot
        @slot('li_2')
            <a href="{{ route('users.show', $user) }}">{{ $user->name }} {{ $user->last_name }}</a>
        @endslot
        @slot('title')
            Permisos Directos
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Asignar Permisos Directos</h4>
                    <p class="text-muted mb-0">
                        Usuario: <strong>{{ $user->name }} {{ $user->last_name }}</strong> ({{ $user->email }})
                    </p>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.permissions.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="alert alert-info">
                            <i class="ri-information-line align-middle me-1"></i>
                            <strong>Nota:</strong> Los permisos directos sobrescriben los permisos de roles. Úselos para casos excepcionales.
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-soft-success" id="select-all-permissions">
                                        <i class="ri-checkbox-line me-1"></i> Seleccionar Todos
                                    </button>
                                    <button type="button" class="btn btn-sm btn-soft-danger" id="deselect-all-permissions">
                                        <i class="ri-checkbox-blank-line me-1"></i> Deseleccionar Todos
                                    </button>
                                    <button type="button" class="btn btn-sm btn-soft-info" id="show-selected">
                                        <i class="ri-eye-line me-1"></i> Ver Seleccionados
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            @foreach($permissions as $group => $groupPermissions)
                            <div class="col-md-12">
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
                                                   data-perm-scope="{{ $permission->scope }}"
                                                   {{ $currentPermissions->contains('id', $permission->id) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                {{ $permission->display_name }}
                                                @if($permission->scope != 'global')
                                                    <span class="scope-badge">{{ $permission->scope }}</span>
                                                @endif
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line align-middle me-1"></i> Guardar Permisos
                            </button>
                            <a href="{{ route('users.show', $user) }}" class="btn btn-soft-secondary">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Info de Roles Actuales -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Roles del Usuario</h5>
                </div>
                <div class="card-body">
                    @if($user->roles->count() > 0)
                        @foreach($user->roles as $role)
                        <div class="current-permission">
                            <h6 class="mb-1">
                                <i class="ri-shield-user-line text-primary me-1"></i>
                                {{ $role->display_name }}
                            </h6>
                            <p class="text-muted small mb-0">
                                @foreach($role->permissions->take(3) as $perm)
                                    <span class="badge bg-light text-dark me-1">{{ $perm->display_name }}</span>
                                @endforeach
                                @if($role->permissions->count() > 3)
                                    <span class="badge bg-light">+{{ $role->permissions->count() - 3 }}</span>
                                @endif
                            </p>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted mb-0">No tiene roles asignados</p>
                    @endif
                </div>
            </div>

            <!-- Permisos Actuales -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Permisos Directos Actuales</h5>
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    @if($currentPermissions->count() > 0)
                        @foreach($currentPermissions as $permission)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>
                                <i class="ri-key-line text-warning me-1"></i>
                                {{ $permission->display_name }}
                                @if($permission->pivot->scope != 'global')
                                    <span class="badge bg-info">{{ $permission->pivot->scope }}</span>
                                @endif
                            </span>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted mb-0 text-center py-3">
                            No tiene permisos directos asignados
                        </p>
                    @endif
                </div>
            </div>

            <!-- Ayuda -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Información</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning mb-0">
                        <i class="ri-information-line"></i>
                        <p class="mb-0 small">
                            <strong>Permisos directos:</strong> Se asignan individualmente y tienen prioridad sobre los permisos de roles.
                        </p>
                        <hr>
                        <p class="mb-0 small">
                            <strong>Ámbitos:</strong>
                            <br>🌐 Global - Sin restricciones
                            <br>🏛️ Recinto - Limitado a un recinto
                            <br>📊 Mesa - Limitado a una mesa
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');
        const groupCheckboxes = document.querySelectorAll('.group-checkbox');

        // ===== FUNCIONALIDAD DE GRUPOS =====

        groupCheckboxes.forEach(groupCb => {
            groupCb.addEventListener('change', function() {
                const group = this.dataset.group;
                const groupPermissions = document.querySelectorAll(`.permission-checkbox[data-group="${group}"]`);
                groupPermissions.forEach(cb => {
                    cb.checked = groupCb.checked;
                });
            });
        });

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

        permissionCheckboxes.forEach(cb => {
            cb.addEventListener('change', updateGroupCheckboxes);
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

        document.getElementById('show-selected').addEventListener('click', function() {
            const selected = [];
            permissionCheckboxes.forEach(cb => {
                if (cb.checked) {
                    const label = document.querySelector(`label[for="${cb.id}"]`).innerText.trim();
                    selected.push(label);
                }
            });

            Swal.fire({
                title: 'Permisos Seleccionados',
                html: selected.length > 0
                    ? selected.map(p => `<span class="badge bg-info m-1">${p}</span>`).join('')
                    : '<p class="text-muted">No hay permisos seleccionados</p>',
                confirmButtonText: 'Cerrar'
            });
        });

        updateGroupCheckboxes();
    });
</script>
@endsection
