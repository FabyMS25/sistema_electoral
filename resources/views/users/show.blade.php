{{-- resources/views/users/show.blade.php --}}
@extends('layouts.master')

@section('title')
    Detalle de Usuario
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .info-label { font-weight: 600; color: #495057; margin-bottom: 0.25rem; }
        .info-value { background-color: #f8f9fa; padding: 0.5rem; border-radius: 0.25rem; margin-bottom: 1rem; }
        .timeline { position: relative; padding-left: 1.5rem; }
        .timeline::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 2px; background: #e9e9ef; }
        .timeline-item { position: relative; padding-bottom: 1.5rem; }
        .timeline-item::before { content: ''; position: absolute; left: -1.5rem; top: 0.25rem; width: 0.75rem; height: 0.75rem; border-radius: 50%; background: #0ab39c; border: 2px solid #fff; box-shadow: 0 0 0 2px #e9e9ef; }
        .delegate-type-badge { background-color: #e7f1ff; color: #0a5dc2; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; font-weight: 500; }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1') Usuarios @endslot
        @slot('li_2') <a href="{{ route('users.index') }}">Lista de Usuarios</a> @endslot
        @slot('title') {{ $user->name }} {{ $user->last_name }} @endslot
    @endcomponent

    {{-- ── Header card ─────────────────────────────────────────────────────── --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0 d-flex justify-content-between align-items-center">
                        <span>Información del Usuario</span>
                        <div class="btn-group" role="group">
                            @if(auth()->user()->allPermissions->contains('name', 'edit_users'))
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-warning btn-sm">
                                <i class="ri-pencil-line align-middle me-1"></i> Editar
                            </a>
                            @endif
                            @if($user->id !== auth()->id())
                                @if($user->is_active)
                                    @if(auth()->user()->allPermissions->contains('name', 'delete_users'))
                                    <button type="button" class="btn btn-danger btn-sm"
                                            onclick="confirmDeactivate('{{ $user->id }}', '{{ addslashes($user->name) }}')">
                                        <i class="ri-user-unfollow-line align-middle me-1"></i> Desactivar
                                    </button>
                                    @endif
                                @else
                                    @if(auth()->user()->allPermissions->contains('name', 'activate_users'))
                                    <form method="POST" action="{{ route('users.activate', $user) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="ri-user-follow-line align-middle me-1"></i> Activar
                                        </button>
                                    </form>
                                    @endif
                                @endif
                            @endif
                        </div>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-4 text-center mb-4">
                            <img src="{{ $user->avatar ? URL::asset('build/images/users/'.$user->avatar) : URL::asset('build/images/users/avatar-1.jpg') }}"
                                 alt="avatar" class="avatar-xl rounded-circle img-thumbnail mb-3">
                            <h5 class="mb-1">{{ $user->name }} {{ $user->last_name }}</h5>
                            <p class="text-muted mb-2">ID: #{{ $user->id }}</p>
                            @if($user->is_active)
                                <span class="badge bg-success-subtle text-success fs-6">Activo</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger fs-6">Inactivo</span>
                            @endif
                            <div class="mt-3">
                                <p class="text-muted mb-1">
                                    <i class="ri-time-line align-middle me-1"></i>
                                    Registrado: {{ $user->created_at->format('d/m/Y H:i') }}
                                </p>
                                @if($user->last_login_at)
                                <p class="text-muted mb-1">
                                    <i class="ri-login-circle-line align-middle me-1"></i>
                                    Último acceso: {{ $user->last_login_at->diffForHumans() }}
                                </p>
                                @endif
                                @if($user->createdBy)
                                <p class="text-muted mb-0">
                                    <i class="ri-user-add-line align-middle me-1"></i>
                                    Creado por: {{ $user->createdBy->name }}
                                </p>
                                @endif
                            </div>
                        </div>
                        <div class="col-xl-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-label">Nombre Completo</div>
                                    <div class="info-value">{{ $user->name }} {{ $user->last_name }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-label">Carnet de Identidad</div>
                                    <div class="info-value">{{ $user->id_card ?? 'No registrado' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-label">Correo Electrónico</div>
                                    <div class="info-value">{{ $user->email }}</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-label">Teléfono</div>
                                    <div class="info-value">{{ $user->phone ?? 'No registrado' }}</div>
                                </div>
                                <div class="col-12">
                                    <div class="info-label">Dirección</div>
                                    <div class="info-value">{{ $user->address ?? 'No registrada' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Acciones rápidas ─────────────────────────────────────────────────── --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Acciones Rápidas</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @if(auth()->user()->allPermissions->contains('name', 'assign_roles'))
                        <a href="{{ route('users.assign-roles.form', $user) }}" class="btn btn-soft-primary">
                            <i class="ri-shield-user-line align-middle me-1"></i> Asignar Roles
                        </a>
                        @endif
                        @if(auth()->user()->allPermissions->contains('name', 'assign_delegates')
                            || auth()->user()->allPermissions->contains('name', 'assign_roles'))
                        <a href="{{ route('users.assign-roles.form', $user) }}" class="btn btn-soft-success">
                            <i class="ri-links-line align-middle me-1"></i> Asignaciones
                        </a>
                        @endif
                        @if(auth()->user()->allPermissions->contains('name', 'assign_permissions'))
                        <a href="{{ route('users.permissions.form', $user) }}" class="btn btn-soft-warning">
                            <i class="ri-key-line align-middle me-1"></i> Permisos Directos
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Roles + Delegaciones ─────────────────────────────────────────────── --}}
    <div class="row">
        {{-- Roles --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Roles Asignados</h4>
                </div>
                <div class="card-body">
                    @if($user->roles->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-nowrap">
                                <thead>
                                    <tr>
                                        <th>Rol</th>
                                        <th>Ámbito</th>
                                        <th>Detalle</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->roles as $role)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">{{ $role->display_name ?? $role->name }}</span>
                                            @if($role->description)
                                            <br><small class="text-muted">{{ $role->description }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{--
                                                DB enum: global | recinto | mesa
                                                Old view used 'institution'/'voting_table' — fixed below
                                            --}}
                                            @switch($role->pivot->scope)
                                                @case('global')
                                                    <span class="badge bg-primary">Global</span>
                                                    @break
                                                @case('recinto')
                                                    <span class="badge bg-success">Recinto</span>
                                                    @break
                                                @case('mesa')
                                                    <span class="badge bg-info">Mesa</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $role->pivot->scope }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            {{--
                                                pivot->institution / votingTable / electionType do NOT exist as
                                                auto-loaded relations on the pivot object. We load those IDs via
                                                withPivot() and look them up from already-loaded relations.
                                                The user's assignments already carry these, but for role pivot
                                                we just show the IDs to avoid extra queries.
                                            --}}
                                            @if($role->pivot->institution_id)
                                                @php
                                                    $inst = $user->assignments
                                                        ->where('institution_id', $role->pivot->institution_id)
                                                        ->first()?->institution;
                                                @endphp
                                                <small>
                                                    Recinto: {{ $inst?->name ?? '#'.$role->pivot->institution_id }}
                                                </small>
                                            @endif
                                            @if($role->pivot->voting_table_id)
                                                @php
                                                    $vt = $user->assignments
                                                        ->where('voting_table_id', $role->pivot->voting_table_id)
                                                        ->first()?->votingTable;
                                                @endphp
                                                <small>
                                                    Mesa: {{ $vt ? 'N° '.$vt->number : '#'.$role->pivot->voting_table_id }}
                                                </small>
                                            @endif
                                            @if($role->pivot->election_type_id)
                                                @php
                                                    $et = $user->assignments
                                                        ->where('election_type_id', $role->pivot->election_type_id)
                                                        ->first()?->electionType;
                                                @endphp
                                                <br><small>
                                                    Elección: {{ $et?->short_name ?? $et?->name ?? '#'.$role->pivot->election_type_id }}
                                                </small>
                                            @endif
                                            @if(!$role->pivot->institution_id && !$role->pivot->voting_table_id)
                                                <small class="text-muted">—</small>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                colors="primary:#121331,secondary:#08a88a" style="width:50px;height:50px">
                            </lord-icon>
                            <p class="text-muted mb-0">No tiene roles asignados</p>
                            @if(auth()->user()->allPermissions->contains('name', 'assign_roles'))
                            <a href="{{ route('users.assign-roles.form', $user) }}" class="btn btn-sm btn-primary mt-2">
                                Asignar Roles
                            </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Delegaciones activas --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Delegaciones Activas</h4>
                </div>
                <div class="card-body">
                    @php
                        $activeAssignments = $user->assignments->where('status', 'activo');
                    @endphp
                    @if($activeAssignments->count() > 0)
                        <div class="timeline">
                            @foreach($activeAssignments as $assignment)
                            <div class="timeline-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            @if($assignment->voting_table_id)
                                                <i class="ri-table-line text-info me-1"></i>
                                                Mesa {{ $assignment->votingTable?->number ?? '—' }}
                                                ({{ $assignment->votingTable?->institution?->name ?? $assignment->institution?->name ?? '—' }})
                                            @else
                                                <i class="ri-building-line text-success me-1"></i>
                                                {{ $assignment->institution?->name ?? '—' }}
                                            @endif
                                        </h6>
                                        <p class="mb-1">
                                            <span class="delegate-type-badge">
                                                {{ $assignment->delegate_type_label }}
                                            </span>
                                        </p>
                                        <p class="text-muted small mb-1">
                                            <i class="ri-calendar-line align-middle"></i>
                                            Desde: {{ $assignment->assignment_date?->format('d/m/Y') ?? 'N/A' }}
                                            @if($assignment->expiration_date)
                                                | Hasta: {{ $assignment->expiration_date->format('d/m/Y') }}
                                            @endif
                                        </p>
                                        @if($assignment->credential_number)
                                        <p class="text-muted small mb-0">
                                            <i class="ri-id-card-line align-middle"></i>
                                            Credencial: {{ $assignment->credential_number }}
                                        </p>
                                        @endif
                                    </div>
                                    @if(auth()->user()->allPermissions->contains('name', 'assign_delegates'))
                                    <form method="POST"
                                          action="{{ route('users.remove-assignment', [$user, $assignment]) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('¿Remover esta asignación?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-soft-danger">
                                            <i class="ri-close-line"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                colors="primary:#121331,secondary:#08a88a" style="width:50px;height:50px">
                            </lord-icon>
                            <p class="text-muted mb-0">No tiene delegaciones activas</p>
                            @if(auth()->user()->allPermissions->contains('name', 'assign_delegates'))
                            <a href="{{ route('users.assign-roles.form', $user) }}"
                               class="btn btn-sm btn-success mt-2">
                                Ir a Asignaciones
                            </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── Historial de actividad ──────────────────────────────────────────── --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Historial de Actividad</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Acción</th>
                                    <th>Descripción</th>
                                    <th>IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $logs = \App\Models\AuditLog::where(function($q) use ($user) {
                                        $q->where('user_id', $user->id)
                                          ->orWhere(function($q2) use ($user) {
                                              $q2->where('model_type', \App\Models\User::class)
                                                 ->where('model_id', $user->id);
                                          });
                                    })->with('user')->latest()->take(10)->get();
                                @endphp
                                @forelse($logs as $log)
                                <tr>
                                    <td class="text-nowrap">
                                        {{ ($log->performed_at ?? $log->created_at)?->format('d/m/Y H:i') }}
                                    </td>
                                    <td>
                                        {{-- AuditLog has no action_color field — derive from action name --}}
                                        @php
                                            $badgeColor = match($log->action) {
                                                'created'  => 'success',
                                                'updated'  => 'primary',
                                                'deleted'  => 'danger',
                                                'restored' => 'info',
                                                'reviewed', 'validated' => 'info',
                                                'observed', 'rejected'  => 'warning',
                                                default    => 'secondary',
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $badgeColor }}">{{ $log->action }}</span>
                                    </td>
                                    <td>{{ $log->description }}</td>
                                    <td class="text-muted small">{{ $log->ip_address ?? '—' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No hay actividad registrada</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    const CSRF_TOKEN = '{{ csrf_token() }}';

    function confirmDeactivate(userId, userName) {
        Swal.fire({
            title: '¿Desactivar usuario?',
            html: `¿Estás seguro de desactivar a <strong>${userName}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, desactivar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/users/${userId}`;

                const csrf = document.createElement('input');
                csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = CSRF_TOKEN;

                const method = document.createElement('input');
                method.type = 'hidden'; method.name = '_method'; method.value = 'DELETE';

                form.appendChild(csrf);
                form.appendChild(method);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>
@endsection
