@extends('layouts.master')

@section('title')
    Detalle de Usuario
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
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
        <div class="col-xl-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        @if($user->avatar)
                            <img src="{{ $user->avatar ? URL::asset('build/images/users/'.$user->avatar) : URL::asset('build/images/users/avatar-1.jpg') }}" alt="user-img" class="img-thumbnail rounded-circle" />
                            {{-- <img src="{{ $user->avatar }}" alt="" class="avatar-xl rounded-circle"> --}}
                        @else
                            <div class="avatar-xl mx-auto">
                                <span class="avatar-title rounded-circle bg-soft-primary display-6" 
                                      style="width: 100px; height: 100px; font-size: 40px;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
                                </span>
                            </div>
                        @endif
                    </div>
                    
                    <h4 class="mb-1">{{ $user->name }} {{ $user->last_name }}</h4>
                    
                    <div class="mb-3">
                        @if($user->is_active)
                            <span class="badge bg-success px-3 py-2">Activo</span>
                        @else
                            <span class="badge bg-danger px-3 py-2">Inactivo</span>
                        @endif
                    </div>

                    <div class="d-flex justify-content-center gap-2 mb-4">
                        @can('edit_users')
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-soft-warning">
                            <i class="ri-edit-line"></i> Editar
                        </a>
                        @endcan
                        <a href="{{ route('users.index') }}" class="btn btn-soft-secondary">
                            <i class="ri-arrow-left-line"></i> Volver
                        </a>
                    </div>

                    <div class="text-start">
                        <h5 class="font-size-15 mb-3">Información Personal</h5>
                        
                        <div class="table-responsive">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <th scope="row" style="width: 40%;">CI:</th>
                                        <td>{{ $user->id_card ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Email:</th>
                                        <td>{{ $user->email }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Teléfono:</th>
                                        <td>{{ $user->phone ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Dirección:</th>
                                        <td>{{ $user->address ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Creado por:</th>
                                        <td>{{ $user->createdBy->name ?? 'Sistema' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Fecha creación:</th>
                                        <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Último acceso:</th>
                                        <td>{{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Nunca' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">IP último acceso:</th>
                                        <td>{{ $user->last_login_ip ?? 'N/A' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-soft-primary">
                    <h5 class="card-title mb-0">Roles y Permisos</h5>
                </div>
                <div class="card-body">
                    <h6 class="font-size-14">Roles:</h6>
                    <div class="mb-3">
                        @forelse($user->roles as $role)
                            <span class="badge bg-info font-size-12 mb-1">{{ $role->display_name }}</span>
                        @empty
                            <p class="text-muted small">Sin roles asignados</p>
                        @endforelse
                    </div>

                    <h6 class="font-size-14">Permisos Directos:</h6>
                    <div>
                        @forelse($user->permissions as $permission)
                            <span class="badge bg-secondary font-size-12 mb-1">{{ $permission->display_name }}</span>
                        @empty
                            <p class="text-muted small">Sin permisos directos</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            {{-- Delegado de Recinto --}}
            @can('assign_recinto_delegates')
            <div class="card">
                <div class="card-header bg-soft-success d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Delegado de Recinto</h5>
                    <a href="{{ route('users.assign-recinto.form', $user) }}" class="btn btn-sm btn-success">
                        <i class="ri-add-line"></i> Asignar Recinto
                    </a>
                </div>
                <div class="card-body">
                    @if($activeAssignments['recinto'])
                        <div class="alert alert-success mb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $activeAssignments['recinto']->institution->name }}</strong>
                                    <br>
                                    <small>Asignado: {{ $activeAssignments['recinto']->assigned_at->format('d/m/Y') }}</small>
                                    @if($activeAssignments['recinto']->assigned_until)
                                        <br>
                                        <small>Hasta: {{ $activeAssignments['recinto']->assigned_until->format('d/m/Y') }}</small>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-sm btn-danger" 
                                        onclick="removeAssignment('recinto', {{ $activeAssignments['recinto']->id }})">
                                    <i class="ri-delete-bin-line"></i> Remover
                                </button>
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-0">No tiene recinto asignado</p>
                    @endif
                </div>
            </div>
            @endcan

            {{-- Delegado de Mesa --}}
            @can('assign_table_delegates')
            <div class="card">
                <div class="card-header bg-soft-warning d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Delegado de Mesa</h5>
                    <a href="{{ route('users.assign-table.form', $user) }}" class="btn btn-sm btn-warning">
                        <i class="ri-add-line"></i> Asignar Mesa
                    </a>
                </div>
                <div class="card-body">
                    @if($activeAssignments['mesas']->count() > 0)
                        <div class="list-group">
                            @foreach($activeAssignments['mesas'] as $delegation)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Mesa {{ $delegation->votingTable->number }}</strong>
                                        <br>
                                        <small>{{ $delegation->votingTable->institution->name }}</small>
                                        <br>
                                        <span class="badge bg-info">Rol: {{ ucfirst($delegation->role) }}</span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="removeAssignment('mesa', {{ $delegation->id }})">
                                        <i class="ri-delete-bin-line"></i> Remover
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">No tiene mesas asignadas</p>
                    @endif
                </div>
            </div>
            @endcan

            {{-- Asignaciones como Revisor --}}
            @can('assign_recinto_delegates')
            <div class="card">
                <div class="card-header bg-soft-info">
                    <h5 class="card-title mb-0">Asignaciones como Revisor</h5>
                </div>
                <div class="card-body">
                    @if($activeAssignments['revisor']->count() > 0)
                        <div class="list-group">
                            @foreach($activeAssignments['revisor'] as $assignment)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        @if($assignment->assignable_type == 'App\\Models\\Institution')
                                            <strong>Recinto: {{ $assignment->assignable->name }}</strong>
                                        @else
                                            <strong>Mesa {{ $assignment->assignable->number }}</strong>
                                            <br>
                                            <small>{{ $assignment->assignable->institution->name }}</small>
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="removeAssignment('revisor', {{ $assignment->id }})">
                                        <i class="ri-delete-bin-line"></i> Remover
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">No tiene asignaciones como revisor</p>
                    @endif
                </div>
            </div>
            @endcan

            {{-- Asignaciones como Modificador --}}
            @can('assign_recinto_delegates')
            <div class="card">
                <div class="card-header bg-soft-purple">
                    <h5 class="card-title mb-0">Asignaciones como Modificador</h5>
                </div>
                <div class="card-body">
                    @if($activeAssignments['modificador']->count() > 0)
                        <div class="list-group">
                            @foreach($activeAssignments['modificador'] as $assignment)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        @if($assignment->assignable_type == 'App\\Models\\Institution')
                                            <strong>Recinto: {{ $assignment->assignable->name }}</strong>
                                        @else
                                            <strong>Mesa {{ $assignment->assignable->number }}</strong>
                                            <br>
                                            <small>{{ $assignment->assignable->institution->name }}</small>
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="removeAssignment('modificador', {{ $assignment->id }})">
                                        <i class="ri-delete-bin-line"></i> Remover
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">No tiene asignaciones como modificador</p>
                    @endif
                </div>
            </div>
            @endcan
        </div>
    </div>
@endsection

@section('script')
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    function removeAssignment(type, assignmentId) {
        Swal.fire({
            title: '¿Remover asignación?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, remover',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ url('users') }}/${type}/assignment/${assignmentId}/remove`;
                form.innerHTML = `
                    @csrf
                    @method('DELETE')
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>
@endsection