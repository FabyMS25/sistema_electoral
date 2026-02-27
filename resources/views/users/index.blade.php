{{-- resources/views/users/index.blade.php --}}
@extends('layouts.master')

@section('title')
    @lang('translation.list-users')
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
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

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Administración de Usuarios del Sistema</h4>
                </div>
                
                <div class="card-body">
                    @include('components.alerts')

                    <div class="row g-4 mb-3">
                        <div class="col-sm-auto">
                            <div>
                                @can('create_users')
                                <a href="{{ route('users.create') }}" class="btn btn-success add-btn">
                                    <i class="ri-add-line align-bottom me-1"></i> Agregar Usuario
                                </a>
                                @endcan
                            </div>
                        </div>
                        <div class="col-sm">
                            <form method="GET" action="{{ route('users.index') }}" class="d-flex justify-content-sm-end">
                                <div class="search-box ms-2">
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="Buscar usuario..." value="{{ request('search') }}">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                                <button type="submit" class="btn btn-soft-primary ms-2">
                                    <i class="ri-search-line"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive table-card mt-3 mb-1">
                        <table class="table align-middle table-nowrap">
                            <thead class="table-light">
                                <tr>
                                    <th>Usuario</th>
                                    <th>CI</th>
                                    <th>Contacto</th>
                                    <th>Roles</th>
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
                                                <img src="{{ $user->avatar ? URL::asset('build/images/users/'.$user->avatar) : URL::asset('build/images/users/avatar-1.jpg') }}" alt="avatar" class="avatar-xs rounded-circle" />
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
                                    <td colspan="7" class="text-center py-4">
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
                        <div class="pagination-wrap hstack gap-2">
                            {{ $users->links() }}
                        </div>
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