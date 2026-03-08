{{-- resources/views/users/permissions.blade.php --}}
@extends('layouts.master')

@section('title') Permisos Directos - {{ $user->name }} @endsection

@section('css')
    <style>
        .permission-group { border: 1px solid #e9e9ef; border-radius: 0.35rem; margin-bottom: 1rem; }
        .permission-group-header {
            background-color: #f3f6f9;
            padding: 0.65rem 1rem;
            border-bottom: 1px solid #e9e9ef;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .permission-group-body {
            padding: 0.75rem 1rem;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 0.4rem;
        }
        .perm-item { padding: 0.4rem 0.5rem; border-radius: 0.25rem; }
        .perm-item:hover { background: #f8f9fa; }
        .perm-item.is-direct { background: #e7f9f5; }
        .perm-scope-badge { font-size: 0.65rem; padding: 0.1rem 0.35rem; border-radius: 0.2rem; }
        .role-perms-info { font-size: 0.75rem; color: #74788d; }
        .group-count { font-size: 0.75rem; color: #74788d; font-weight: 400; }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Usuarios @endslot
        @slot('li_2') <a href="{{ route('users.show', $user) }}">{{ $user->name }} {{ $user->last_name }}</a> @endslot
        @slot('title') Permisos Directos @endslot
    @endcomponent

    <div class="row">
        {{-- ── Main form ───────────────────────────────────────────────────── --}}
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h4 class="card-title mb-0">
                        Permisos directos de: {{ $user->name }} {{ $user->last_name }}
                    </h4>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-soft-success" id="selectAllPerms">
                            <i class="ri-check-double-line me-1"></i>Todos
                        </button>
                        <button type="button" class="btn btn-sm btn-soft-danger" id="deselectAllPerms">
                            <i class="ri-close-line me-1"></i>Ninguno
                        </button>
                    </div>
                </div>
                <div class="card-body">

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <i class="ri-information-line align-middle me-1"></i>
                        Estos permisos son <strong>adicionales</strong> a los que el usuario ya tiene por sus roles.
                        Los permisos por rol aparecen sombreados y no se pueden desmarcar aquí — deben gestionarse
                        desde <a href="{{ route('users.assign-roles.form', $user) }}">Asignar Roles</a>.
                    </div>

                    <form method="POST" action="{{ route('users.permissions.update', $user) }}" id="permissionsForm">
                        @csrf
                        @method('PUT')

                        {{-- Build a set of permission IDs inherited via roles (read-only indicators) --}}
                        @php
                            $rolePermIds = $user->roles->flatMap(fn($r) => $r->permissions->pluck('id'))->unique()->toArray();
                            // currentPermissions = user's direct permissions with pivot
                            $directIds = $currentPermissions->pluck('id')->toArray();
                        @endphp

                        @foreach($permissions as $group => $groupPermissions)
                        @php
                            $groupDirectCount = collect($groupPermissions)->filter(fn($p) => in_array($p->id, $directIds))->count();
                            $groupTotal = count($groupPermissions);
                        @endphp
                        <div class="permission-group">
                            <div class="permission-group-header">
                                <div class="form-check mb-0">
                                    <input type="checkbox" class="form-check-input group-checkbox"
                                           id="grp_{{ Str::slug($group) }}"
                                           data-group="{{ Str::slug($group) }}">
                                    <label class="form-check-label fw-bold" for="grp_{{ Str::slug($group) }}">
                                        {{ $group }}
                                    </label>
                                </div>
                                <span class="group-count">
                                    {{ $groupDirectCount }}/{{ $groupTotal }} directos
                                </span>
                            </div>
                            <div class="permission-group-body">
                                @foreach($groupPermissions as $permission)
                                @php
                                    $isDirect   = in_array($permission->id, $directIds);
                                    $isFromRole = in_array($permission->id, $rolePermIds);
                                    // Get scope from pivot only if it's a direct permission
                                    $pivotScope = $currentPermissions->find($permission->id)?->pivot?->scope;
                                @endphp
                                <div class="perm-item {{ $isDirect ? 'is-direct' : '' }}">
                                    <div class="form-check d-flex align-items-start gap-1">
                                        <input type="checkbox"
                                               class="form-check-input perm-checkbox flex-shrink-0"
                                               id="perm_{{ $permission->id }}"
                                               name="permissions[]"
                                               value="{{ $permission->id }}"
                                               data-group="{{ Str::slug($group) }}"
                                               {{ $isDirect ? 'checked' : '' }}
                                               {{-- Don't disable: user can choose to add/remove even if role gives it --}}>
                                        <label class="form-check-label" for="perm_{{ $permission->id }}">
                                            <span>{{ $permission->display_name ?? $permission->name }}</span>
                                            @if($isFromRole && !$isDirect)
                                            <span class="perm-scope-badge bg-light text-muted ms-1"
                                                  title="Heredado de rol">
                                                <i class="ri-shield-line"></i>
                                            </span>
                                            @endif
                                            @if($isDirect && $pivotScope && $pivotScope !== 'global')
                                            <span class="perm-scope-badge bg-info-subtle text-info ms-1">
                                                {{ $pivotScope }}
                                            </span>
                                            @endif
                                            @if($permission->description)
                                            <br><small class="role-perms-info">{{ $permission->description }}</small>
                                            @endif
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach

                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <a href="{{ route('users.show', $user) }}" class="btn btn-soft-secondary">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line align-middle me-1"></i>
                                Guardar Permisos Directos
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── Sidebar: roles summary ──────────────────────────────────────── --}}
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Permisos heredados de roles</h6>
                </div>
                <div class="card-body">
                    @if($user->roles->count() > 0)
                        @foreach($user->roles as $role)
                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="fw-semibold">{{ $role->display_name ?? $role->name }}</span>
                                <span class="badge bg-primary-subtle text-primary">
                                    {{ $role->permissions->count() }}
                                </span>
                            </div>
                            @if($role->permissions->count() > 0)
                            <div class="ps-2">
                                @foreach($role->permissions->take(5) as $rp)
                                <small class="d-block text-muted">
                                    <i class="ri-checkbox-blank-circle-fill me-1" style="font-size:0.45rem;vertical-align:middle;"></i>
                                    {{ $rp->display_name ?? $rp->name }}
                                </small>
                                @endforeach
                                @if($role->permissions->count() > 5)
                                <small class="text-muted">
                                    + {{ $role->permissions->count() - 5 }} más...
                                </small>
                                @endif
                            </div>
                            @else
                            <small class="text-muted">Sin permisos asignados al rol</small>
                            @endif
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted small mb-0">El usuario no tiene roles asignados.</p>
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Resumen</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <small>Directos</small>
                        <span class="badge bg-success" id="directCount">{{ $currentPermissions->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <small>Por roles</small>
                        <span class="badge bg-primary">{{ count($rolePermIds) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small>Total acceso</small>
                        <span class="badge bg-dark">
                            {{ count(array_unique(array_merge($directIds, $rolePermIds))) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const permCheckboxes = document.querySelectorAll('.perm-checkbox');
    const groupCheckboxes = document.querySelectorAll('.group-checkbox');
    const directCountEl = document.getElementById('directCount');

    function updateGroupState(groupSlug) {
        const groupCb = document.querySelector(`.group-checkbox[data-group="${groupSlug}"]`);
        if (!groupCb) return;
        const all     = document.querySelectorAll(`.perm-checkbox[data-group="${groupSlug}"]`);
        const checked = document.querySelectorAll(`.perm-checkbox[data-group="${groupSlug}"]:checked`);
        if (checked.length === all.length) {
            groupCb.checked = true; groupCb.indeterminate = false;
        } else if (checked.length > 0) {
            groupCb.checked = false; groupCb.indeterminate = true;
        } else {
            groupCb.checked = false; groupCb.indeterminate = false;
        }
    }

    function updateDirectCount() {
        const n = document.querySelectorAll('.perm-checkbox:checked').length;
        if (directCountEl) directCountEl.textContent = n;
    }

    permCheckboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            updateGroupState(this.dataset.group);
            updateDirectCount();
        });
    });

    groupCheckboxes.forEach(gcb => {
        gcb.addEventListener('change', function () {
            document.querySelectorAll(`.perm-checkbox[data-group="${this.dataset.group}"]`)
                    .forEach(cb => cb.checked = this.checked);
            updateDirectCount();
        });
    });

    document.getElementById('selectAllPerms').addEventListener('click', () => {
        permCheckboxes.forEach(cb => cb.checked = true);
        groupCheckboxes.forEach(gcb => {
            gcb.checked = true; gcb.indeterminate = false;
        });
        updateDirectCount();
    });

    document.getElementById('deselectAllPerms').addEventListener('click', () => {
        permCheckboxes.forEach(cb => cb.checked = false);
        groupCheckboxes.forEach(gcb => {
            gcb.checked = false; gcb.indeterminate = false;
        });
        updateDirectCount();
    });

    // Init group states
    const groups = new Set([...permCheckboxes].map(cb => cb.dataset.group));
    groups.forEach(updateGroupState);
    updateDirectCount();
});
</script>
@endsection
