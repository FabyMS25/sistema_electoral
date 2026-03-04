{{-- resources/views/users/assign-institution.blade.php --}}
@extends('layouts.master')

@section('title')
    Asignar Recinto - {{ $user->name }}
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .institution-group {
            margin-bottom: 1.5rem;
        }
        .institution-group h6 {
            background-color: #f3f6f9;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            margin-bottom: 0.75rem;
        }
        .current-assignment {
            border-left: 3px solid #0ab39c;
            background-color: #f0f9f7;
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
            Asignar Recinto
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Nueva Asignación de Recinto</h4>
                    <p class="text-muted mb-0">Usuario: {{ $user->name }} {{ $user->last_name }} ({{ $user->email }})</p>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.assign-institution', $user) }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="election_type_id" class="form-label">Tipo de Elección <span class="text-danger">*</span></label>
                                    <select class="form-select @error('election_type_id') is-invalid @enderror"
                                            id="election_type_id" name="election_type_id" required>
                                        <option value="">Seleccione...</option>
                                        @foreach($electionTypes as $type)
                                            <option value="{{ $type->id }}" {{ old('election_type_id') == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('election_type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="delegate_type" class="form-label">Tipo de Delegado <span class="text-danger">*</span></label>
                                    <select class="form-select @error('delegate_type') is-invalid @enderror"
                                            id="delegate_type" name="delegate_type" required>
                                        <option value="">Seleccione...</option>
                                        <option value="delegado_general" {{ old('delegate_type') == 'delegado_general' ? 'selected' : '' }}>
                                            Delegado General
                                        </option>
                                        <option value="tecnico" {{ old('delegate_type') == 'tecnico' ? 'selected' : '' }}>
                                            Técnico/Soporte
                                        </option>
                                        <option value="observador" {{ old('delegate_type') == 'observador' ? 'selected' : '' }}>
                                            Observador
                                        </option>
                                    </select>
                                    @error('delegate_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="institution_id" class="form-label">Recinto <span class="text-danger">*</span></label>
                            <select class="form-select @error('institution_id') is-invalid @enderror"
                                    id="institution_id" name="institution_id" required>
                                <option value="">Seleccione...</option>
                                @foreach($institutions as $municipality => $instList)
                                    <optgroup label="{{ $municipality }}">
                                        @foreach($instList as $institution)
                                            <option value="{{ $institution->id }}"
                                                data-code="{{ $institution->code }}"
                                                {{ old('institution_id') == $institution->id ? 'selected' : '' }}>
                                                {{ $institution->name }} ({{ $institution->code }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('institution_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="assignment_date" class="form-label">Fecha de Asignación</label>
                                    <input type="date" class="form-control @error('assignment_date') is-invalid @enderror"
                                           id="assignment_date" name="assignment_date"
                                           value="{{ old('assignment_date', now()->format('Y-m-d')) }}">
                                    @error('assignment_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="expiration_date" class="form-label">Fecha de Expiración</label>
                                    <input type="date" class="form-control @error('expiration_date') is-invalid @enderror"
                                           id="expiration_date" name="expiration_date" value="{{ old('expiration_date') }}">
                                    <small class="text-muted">Opcional, dejar vacío si no expira</small>
                                    @error('expiration_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="credential_number" class="form-label">Número de Credencial</label>
                            <input type="text" class="form-control @error('credential_number') is-invalid @enderror"
                                   id="credential_number" name="credential_number" value="{{ old('credential_number') }}">
                            @error('credential_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="observations" class="form-label">Observaciones</label>
                            <textarea class="form-control @error('observations') is-invalid @enderror"
                                      id="observations" name="observations" rows="3">{{ old('observations') }}</textarea>
                            @error('observations')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line align-middle me-1"></i> Guardar Asignación
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
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Asignaciones Activas</h4>
                </div>
                <div class="card-body">
                    @if($currentAssignments->count() > 0)
                        <div class="list-group">
                            @foreach($currentAssignments as $assignment)
                            <div class="list-group-item current-assignment">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $assignment->institution->name }}</h6>
                                        <p class="mb-1">
                                            <span class="badge bg-success-subtle text-success">
                                                {{ $assignment->delegate_type_label }}
                                            </span>
                                            <small class="text-muted ms-2">
                                                {{ $assignment->electionType->short_name ?? $assignment->electionType->name }}
                                            </small>
                                        </p>
                                        <p class="text-muted small mb-0">
                                            <i class="ri-calendar-line"></i>
                                            Desde: {{ $assignment->assignment_date->format('d/m/Y') }}
                                            @if($assignment->expiration_date)
                                                <br>Hasta: {{ $assignment->expiration_date->format('d/m/Y') }}
                                            @endif
                                        </p>
                                        @if($assignment->credential_number)
                                        <p class="text-muted small mb-0">
                                            <i class="ri-id-card-line"></i> Cred: {{ $assignment->credential_number }}
                                        </p>
                                        @endif
                                    </div>
                                    <form action="{{ route('users.remove-assignment', [$user, $assignment]) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Remover esta asignación?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-soft-danger">
                                            <i class="ri-close-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                colors="primary:#121331,secondary:#08a88a" style="width:50px;height:50px">
                            </lord-icon>
                            <p class="text-muted mb-0">No tiene asignaciones activas</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script src="{{ URL::asset('build/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar Choices.js para selects
        const electionTypeSelect = new Choices('#election_type_id', {
            searchEnabled: true,
            itemSelectText: '',
            placeholderValue: 'Seleccione...',
        });

        const institutionSelect = new Choices('#institution_id', {
            searchEnabled: true,
            itemSelectText: '',
            placeholderValue: 'Seleccione...',
            shouldSort: false,
        });

        const delegateTypeSelect = new Choices('#delegate_type', {
            searchEnabled: false,
            itemSelectText: '',
            placeholderValue: 'Seleccione...',
        });

        // Validar que fecha expiración sea posterior a asignación
        document.getElementById('expiration_date').addEventListener('change', function() {
            const assignmentDate = document.getElementById('assignment_date').value;
            const expirationDate = this.value;

            if (assignmentDate && expirationDate && expirationDate < assignmentDate) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'La fecha de expiración debe ser posterior a la fecha de asignación'
                });
                this.value = '';
            }
        });
    });
</script>
@endsection
