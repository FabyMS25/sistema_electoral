{{-- resources/views/users/show.blade.php --}}
@extends('layouts.master')

@section('title')
    Detalle de Usuario
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .info-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.25rem;
        }
        .info-value {
            background-color: #f8f9fa;
            padding: 0.5rem;
            border-radius: 0.25rem;
            margin-bottom: 1rem;
        }
        .badge-assignment {
            font-size: 0.8rem;
            padding: 0.4rem 0.6rem;
        }
        .timeline {
            position: relative;
            padding-left: 1.5rem;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9e9ef;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -1.5rem;
            top: 0.25rem;
            width: 0.75rem;
            height: 0.75rem;
            border-radius: 50%;
            background: #0ab39c;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px #e9e9ef;
        }
        .delegate-type-badge {
            background-color: #e7f1ff;
            color: #0a5dc2;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
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
            {{ $user->name }} {{ $user->last_name }}
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0 d-flex justify-content-between align-items-center">
                        <span>Información del Usuario</span>
                        <div class="btn-group" role="group">
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-warning btn-sm">
                                <i class="ri-pencil-line align-middle me-1"></i> Editar
                            </a>
                            @if($user->is_active)
                                <button type="button" class="btn btn-danger btn-sm"
                                        onclick="confirmDeactivate('{{ $user->id }}', '{{ $user->name }}')">
                                    <i class="ri-user-unfollow-line align-middle me-1"></i> Desactivar
                                </button>
                            @else
                                <a href="{{ route('users.activate', $user) }}" class="btn btn-success btn-sm">
                                    <i class="ri-user-follow-line align-middle me-1"></i> Activar
                                </a>
                            @endif
                        </div>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-4">
                            <div class="text-center mb-4">
                                <div class="mb-3">
                                    <img src="{{ $user->avatar ? URL::asset('build/images/users/'.$user->avatar) : URL::asset('build/images/users/avatar-1.jpg') }}"
                                         alt="avatar" class="avatar-xl rounded-circle img-thumbnail">
                                </div>
                                <h5 class="mb-1">{{ $user->name }} {{ $user->last_name }}</h5>
                                <p class="text-muted mb-2">ID: #{{ $user->id }}</p>
                                <div class="mb-2">
                                    @if($user->is_active)
                                        <span class="badge bg-success-subtle text-success fs-6">Activo</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger fs-6">Inactivo</span>
                                    @endif
                                </div>
                                <div class="mt-4">
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
                                </div>
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

    <!-- Botones de Acciones Rápidas -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Acciones Rápidas</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @can('assign_roles')
                        <a href="{{ route('users.assign-roles.form', $user) }}" class="btn btn-soft-primary">
                            <i class="ri-shield-user-line align-middle me-1"></i> Asignar Roles
                        </a>
                        @endcan

                        @can('assign_delegates')
                        <a href="{{ route('users.assign-institution.form', $user) }}" class="btn btn-soft-success">
                            <i class="ri-building-line align-middle me-1"></i> Asignar Recinto
                        </a>
                        <a href="{{ route('users.assign-table.form', $user) }}" class="btn btn-soft-info">
                            <i class="ri-table-line align-middle me-1"></i> Asignar Mesa
                        </a>
                        @endcan

                        @can('assign_permissions')
                        <a href="{{ route('users.permissions.form', $user) }}" class="btn btn-soft-warning">
                            <i class="ri-key-line align-middle me-1"></i> Permisos Directos
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Roles Asignados -->
    <div class="row">
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
                                            <span class="fw-bold">{{ $role->display_name }}</span>
                                            <br>
                                            <small class="text-muted">{{ $role->description }}</small>
                                        </td>
                                        <td>
                                            @switch($role->pivot->scope)
                                                @case('global')
                                                    <span class="badge bg-primary">Global</span>
                                                    @break
                                                @case('institution')
                                                    <span class="badge bg-success">Institución</span>
                                                    @break
                                                @case('voting_table')
                                                    <span class="badge bg-info">Mesa</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $role->pivot->scope }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            @if($role->pivot->institution_id)
                                                <small>Inst: {{ optional($role->pivot->institution)->name ?? 'N/A' }}</small>
                                            @endif
                                            @if($role->pivot->voting_table_id)
                                                <small>Mesa: {{ optional($role->pivot->votingTable)->number ?? 'N/A' }}</small>
                                            @endif
                                            @if($role->pivot->election_type_id)
                                                <br>
                                                <small>Elección: {{ optional($role->pivot->electionType)->name ?? 'N/A' }}</small>
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
                            @can('assign_roles')
                            <a href="{{ route('users.assign-roles.form', $user) }}" class="btn btn-sm btn-primary mt-2">
                                Asignar Roles
                            </a>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Asignaciones de Delegaciones -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Delegaciones Activas</h4>
                </div>
                <div class="card-body">
                    @php
                        $activeAssignments = $user->assignments()->where('status', 'activo')->get();
                    @endphp

                    @if($activeAssignments->count() > 0)
                        <div class="timeline">
                            @foreach($activeAssignments as $assignment)
                            <div class="timeline-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            @if($assignment->institution_id)
                                                <i class="ri-building-line text-success me-1"></i>
                                                Recinto: {{ $assignment->institution->name ?? 'N/A' }}
                                            @elseif($assignment->voting_table_id)
                                                <i class="ri-table-line text-info me-1"></i>
                                                Mesa: {{ $assignment->votingTable->number ?? 'N/A' }}
                                                ({{ $assignment->votingTable->institution->name ?? 'N/A' }})
                                            @endif
                                        </h6>
                                        <p class="mb-1">
                                            <span class="delegate-type-badge">
                                                {{ $assignment->delegate_type_label }}
                                            </span>
                                        </p>
                                        <p class="text-muted small mb-1">
                                            <i class="ri-calendar-line align-middle"></i>
                                            Desde: {{ $assignment->assignment_date ? $assignment->assignment_date->format('d/m/Y') : 'N/A' }}
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
                                    @can('assign_delegates')
                                    <button type="button" class="btn btn-sm btn-soft-danger"
                                            onclick="removeAssignment('{{ $assignment->id }}', '{{ $assignment->delegate_type_label }}')">
                                        <i class="ri-close-line"></i>
                                    </button>
                                    @endcan
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
                            @can('assign_delegates')
                            <a href="{{ route('users.assign-institution.form', $user) }}" class="btn btn-sm btn-success mt-2">
                                Asignar Recinto
                            </a>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de Actividad -->
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
                                    <th>Realizado por</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(\App\Models\AuditLog::where('user_id', $user->id)->orWhere(function($q) use ($user) {
                                    $q->where('model_type', 'App\\Models\\User')->where('model_id', $user->id);
                                })->latest()->take(10)->get() as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $log->action_color ?? 'secondary' }}">
                                            {{ $log->action }}
                                        </span>
                                    </td>
                                    <td>{{ $log->description }}</td>
                                    <td>{{ optional($log->user)->name ?? 'Sistema' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No hay actividad registrada</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="remove-assignment-form" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@section('script')
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
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
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ url('users') }}/${userId}`;
                form.innerHTML = `
                    @csrf
                    @method('DELETE')
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function removeAssignment(assignmentId, type) {
        Swal.fire({
            title: '¿Remover asignación?',
            html: `¿Estás seguro de remover la asignación de <strong>${type}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, remover',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('remove-assignment-form');
                form.action = `{{ url('users') }}/${assignmentId}/assignment/${assignmentId}/remove`;
                form.submit();
            }
        });
    }
</script>
@endsection
