{{-- resources/views/users/index.blade.php --}}
@extends('layouts.master')

@section('title')
    @lang('translation.list-users')
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .filter-card {
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        .delegate-badge {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
            background-color: #e7f1ff;
            color: #0a5dc2;
            border-radius: 0.25rem;
            margin-right: 0.25rem;
            display: inline-block;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Usuarios
        @endslot
        @slot('title')
            Gestión de Usuarios
        @endslot
    @endcomponent

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Total Usuarios</p>
                            <h4 class="mb-2">{{ $stats['total'] }}</h4>
                            <p class="text-muted mb-0">
                                <span class="text-success fw-bold font-size-12 me-2">
                                    <i class="ri-arrow-up-line"></i> {{ $stats['active'] }} activos
                                </span>
                            </p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-primary rounded-3">
                                <i class="ri-user-line font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Activos Hoy</p>
                            <h4 class="mb-2">{{ $stats['online_today'] }}</h4>
                            <p class="text-muted mb-0">
                                <span class="text-primary fw-bold font-size-12 me-2">
                                    <i class="ri-user-star-line"></i> último acceso
                                </span>
                            </p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-success rounded-3">
                                <i class="ri-user-smile-line font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Delegados</p>
                            <h4 class="mb-2">{{ $stats['delegates'] }}</h4>
                            <p class="text-muted mb-0">
                                <span class="text-warning fw-bold font-size-12 me-2">
                                    <i class="ri-user-settings-line"></i> asignaciones
                                </span>
                            </p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-warning rounded-3">
                                <i class="ri-user-settings-line font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Inactivos</p>
                            <h4 class="mb-2">{{ $stats['inactive'] }}</h4>
                            <p class="text-muted mb-0">
                                <span class="text-danger fw-bold font-size-12 me-2">
                                    <i class="ri-user-forbid-line"></i> sin acceso
                                </span>
                            </p>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-danger rounded-3">
                                <i class="ri-user-forbid-line font-size-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros Avanzados -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('users.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Búsqueda</label>
                                <div class="search-box">
                                    <input type="text" name="search" class="form-control"
                                           placeholder="Nombre, email, CI..." value="{{ request('search') }}">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Rol</label>
                                <select name="role" class="form-select">
                                    <option value="">Todos</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                                            {{ $role->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Tipo Delegado</label>
                                <select name="delegate_type" class="form-select">
                                    <option value="">Todos</option>
                                    @foreach($delegateTypes as $value => $label)
                                        <option value="{{ $value }}" {{ request('delegate_type') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Estado</label>
                                <select name="status" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activos</option>
                                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivos</option>
                                </select>
                            </div>

                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="ri-filter-3-line align-middle me-1"></i> Filtrar
                                </button>
                                <a href="{{ route('users.index') }}" class="btn btn-soft-secondary">
                                    <i class="ri-refresh-line align-middle me-1"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Listado de Usuarios -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h4 class="card-title mb-0">Administración de Usuarios del Sistema</h4>
                    @can('create_users')
                    <a href="{{ route('users.create') }}" class="btn btn-success">
                        <i class="ri-add-line align-bottom me-1"></i> Nuevo Usuario
                    </a>
                    @endcan
                </div>

                <div class="card-body">
                    @include('components.alerts')

                    <div class="table-responsive table-card mt-3 mb-1">
                        <table class="table align-middle table-nowrap">
                            <thead class="table-light">
                                <tr>
                                    <th>Usuario</th>
                                    <th>CI</th>
                                    <th>Contacto</th>
                                    <th>Roles</th>
                                    <th>Delegaciones</th>
                                    <th>Estado</th>
                                    <th>Último Acceso</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <img src="{{ $user->avatar ? URL::asset('build/images/users/'.$user->avatar) : URL::asset('build/images/users/avatar-1.jpg') }}"
                                                     alt="avatar" class="avatar-xs rounded-circle" />
                                            </div>
                                            <div class="flex-grow-1 ms-2">
                                                <h5 class="fs-14 mb-1">{{ $user->name }} {{ $user->last_name }}</h5>
                                                <p class="text-muted mb-0">ID: #{{ $user->id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $user->id_card ?? 'N/A' }}</td>
                                    <td>
                                        <p class="mb-0">{{ $user->email }}</p>
                                        <small class="text-muted">{{ $user->phone ?? 'Sin teléfono' }}</small>
                                    </td>
                                    <td>
                                        @foreach($user->roles as $role)
                                            <span class="badge bg-info-subtle text-info">{{ $role->display_name }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach($user->assignments->take(2) as $assignment)
                                            @if($assignment->institution_id)
                                                <span class="delegate-badge" title="Recinto">
                                                    <i class="ri-building-line"></i> {{ $assignment->institution->code ?? '' }}
                                                </span>
                                            @elseif($assignment->voting_table_id)
                                                <span class="delegate-badge" title="Mesa {{ $assignment->delegate_type_label }}">
                                                    <i class="ri-table-line"></i> Mesa {{ $assignment->votingTable->number ?? '' }}
                                                </span>
                                            @endif
                                        @endforeach
                                        @if($user->assignments->count() > 2)
                                            <span class="delegate-badge">+{{ $user->assignments->count() - 2 }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->is_active)
                                            <span class="badge bg-success-subtle text-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->last_login_at)
                                            {{ $user->last_login_at->diffForHumans() }}
                                        @else
                                            <span class="text-muted">Nunca</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            @can('view_users')
                                            <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-info" title="Ver">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            @endcan

                                            @can('assign_roles')
                                            <a href="{{ route('users.assign-roles.form', $user) }}" class="btn btn-sm btn-primary" title="Asignar Roles">
                                                <i class="ri-shield-user-line"></i>
                                            </a>
                                            @endcan

                                            @can('edit_users')
                                            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning" title="Editar">
                                                <i class="ri-pencil-line"></i>
                                            </a>
                                            @endcan

                                            @if($user->is_active)
                                                @can('delete_users')
                                                <button class="btn btn-sm btn-danger"
                                                        onclick="confirmDeactivate('{{ $user->id }}', '{{ $user->name }}')"
                                                        title="Desactivar">
                                                    <i class="ri-user-unfollow-line"></i>
                                                </button>
                                                @endcan
                                            @else
                                                @can('edit_users')
                                                <a href="{{ route('users.activate', $user) }}" class="btn btn-sm btn-success" title="Activar">
                                                    <i class="ri-user-follow-line"></i>
                                                </a>
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="noresult">
                                            <div class="text-center">
                                                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                                    colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px">
                                                </lord-icon>
                                                <h5 class="mt-2">Lo sentimos! No se encontraron resultados</h5>
                                                <p class="text-muted mb-0">No hay usuarios registrados en el sistema.</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end">
                        {{ $users->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
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
                    form.action = `/users/${userId}`;
                    form.innerHTML = `
                        @csrf
                        @method('DELETE')
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Auto-cerrar alertas después de 5 segundos
        setTimeout(function() {
            document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);
    </script>
@endsection
