{{-- resources/views/users/assign-table.blade.php --}}
@extends('layouts.master')

@section('title')
    Asignar Mesa
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet" type="text/css" />
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
            Asignar Mesa
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Asignar Mesa a {{ $user->name }} {{ $user->last_name }}</h4>
                </div>
                <div class="card-body">
                    @if($currentAssignments->count() > 0)
                    <div class="alert alert-info mb-4">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="ri-information-line fs-16"></i>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <strong>Asignaciones actuales:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach($currentAssignments as $assignment)
                                    <li>
                                        Mesa {{ $assignment->votingTable->number }} - 
                                        {{ $assignment->votingTable->institution->name }}
                                        (Rol: {{ ucfirst($assignment->role) }})
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endif

                    <form action="{{ route('users.assign-table', $user) }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="voting_table_id" class="form-label">Seleccionar Mesa <span class="text-danger">*</span></label>
                                    <select class="form-select @error('voting_table_id') is-invalid @enderror" 
                                            id="voting_table_id" name="voting_table_id" 
                                            data-choices data-choices-search-false required>
                                        <option value="">Seleccione una mesa...</option>
                                        @foreach($mesas as $mesa)
                                        <option value="{{ $mesa->id }}" 
                                            data-info='{"institution":"{{ $mesa->institution->name }}","number":"{{ $mesa->number }}"}'
                                            {{ old('voting_table_id') == $mesa->id ? 'selected' : '' }}>
                                            Mesa {{ $mesa->number }} - {{ $mesa->institution->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('voting_table_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="role" class="form-label">Rol en la Mesa <span class="text-danger">*</span></label>
                                    <select class="form-select @error('role') is-invalid @enderror" 
                                            id="role" name="role" data-choices data-choices-search-false required>
                                        <option value="">Seleccione...</option>
                                        <option value="presidente" {{ old('role') == 'presidente' ? 'selected' : '' }}>Presidente</option>
                                        <option value="secretario" {{ old('role') == 'secretario' ? 'selected' : '' }}>Secretario</option>
                                        <option value="vocal" {{ old('role') == 'vocal' ? 'selected' : '' }}>Vocal</option>
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="assigned_until" class="form-label">Fecha de Fin (opcional)</label>
                                    <input type="date" class="form-control @error('assigned_until') is-invalid @enderror" 
                                           id="assigned_until" name="assigned_until" 
                                           value="{{ old('assigned_until') }}"
                                           min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                    @error('assigned_until')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="ri-information-line align-middle me-1"></i>
                            <strong>Notas importantes:</strong>
                            <ul class="mb-0 mt-1">
                                <li>Un usuario puede ser delegado de múltiples mesas</li>
                                <li>Cada mesa solo puede tener un delegado activo por rol</li>
                                <li>Las asignaciones con fecha de fin se desactivarán automáticamente</li>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('users.show', $user) }}" class="btn btn-soft-secondary">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="ri-save-line align-middle me-1"></i> Guardar Asignación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    document.getElementById('voting_table_id').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const infoDiv = document.getElementById('mesaInfo');
        
        if (!this.value) {
            if(infoDiv) infoDiv.innerHTML = '<p class="text-muted">Seleccione una mesa para ver su información</p>';
            return;
        }

        const data = selected.dataset.info ? JSON.parse(selected.dataset.info) : {};
        
        if(infoDiv) {
            infoDiv.innerHTML = `
                <h6>Mesa ${data.number}</h6>
                <p><strong>Recinto:</strong> ${data.institution}</p>
                <p><strong>Estado:</strong> <span class="badge bg-success">Activa</span></p>
            `;
        }
    });
</script>
@endsection