{{-- resources/views/users/assign-table.blade.php --}}
@extends('layouts.master')

@section('title')
    Asignar Mesa - {{ $user->name }}
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .table-group {
            margin-bottom: 1.5rem;
        }
        .table-group h6 {
            background-color: #f3f6f9;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            margin-bottom: 0.75rem;
        }
        .current-assignment {
            border-left: 3px solid #0ab39c;
            background-color: #f0f9f7;
        }
        .table-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            background-color: #e9e9ef;
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
            Asignar Mesa de Votación
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Nueva Asignación de Mesa</h4>
                    <p class="text-muted mb-0">Usuario: {{ $user->name }} {{ $user->last_name }} ({{ $user->email }})</p>
                </div>
                <div class="card-body">
                    <form action="{{ route('users.assign-table', $user) }}" method="POST">
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
                                    <label for="delegate_type" class="form-label">Rol en Mesa <span class="text-danger">*</span></label>
                                    <select class="form-select @error('delegate_type') is-invalid @enderror"
                                            id="delegate_type" name="delegate_type" required>
                                        <option value="">Seleccione...</option>
                                        <option value="presidente" {{ old('delegate_type') == 'presidente' ? 'selected' : '' }}>
                                            Presidente de Mesa
                                        </option>
                                        <option value="secretario" {{ old('delegate_type') == 'secretario' ? 'selected' : '' }}>
                                            Secretario
                                        </option>
                                        <option value="vocal" {{ old('delegate_type') == 'vocal' ? 'selected' : '' }}>
                                            Vocal
                                        </option>
                                        <option value="delegado_mesa" {{ old('delegate_type') == 'delegado_mesa' ? 'selected' : '' }}>
                                            Delegado de Mesa
                                        </option>
                                    </select>
                                    @error('delegate_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="voting_table_id" class="form-label">Mesa de Votación <span class="text-danger">*</span></label>
                            <select class="form-select @error('voting_table_id') is-invalid @enderror"
                                    id="voting_table_id" name="voting_table_id" required>
                                <option value="">Seleccione...</option>
                                @foreach($votingTables as $institution => $tables)
                                    <optgroup label="{{ $institution }}">
                                        @foreach($tables as $table)
                                            <option value="{{ $table->id }}"
                                                data-code="{{ $table->oep_code }}"
                                                data-institution="{{ $table->institution->name }}"
                                                {{ old('voting_table_id') == $table->id ? 'selected' : '' }}>
                                                Mesa {{ $table->number }} {{ $table->letter ? '- ' . $table->letter : '' }}
                                                ({{ $table->oep_code }}) - {{ $table->type }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <small class="text-muted">
                                Solo se muestran mesas sin delegado activo o asignadas a este usuario
                            </small>
                            @error('voting_table_id')
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
                    <h4 class="card-title mb-0">Mesas Asignadas</h4>
                </div>
                <div class="card-body">
                    @if($currentAssignments->count() > 0)
                        <div class="list-group">
                            @foreach($currentAssignments as $assignment)
                            <div class="list-group-item current-assignment">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">Mesa {{ $assignment->votingTable->number }}</h6>
                                        <p class="mb-1 small">
                                            {{ $assignment->votingTable->institution->name }}
                                            <br>
                                            <span class="badge bg-info-subtle text-info">
                                                {{ $assignment->delegate_type_label }}
                                            </span>
                                            <span class="table-badge ms-1">
                                                {{ $assignment->votingTable->oep_code }}
                                            </span>
                                        </p>
                                        <p class="text-muted small mb-0">
                                            <i class="ri-calendar-line"></i>
                                            {{ $assignment->assignment_date->format('d/m/Y') }}
                                            @if($assignment->expiration_date)
                                                - {{ $assignment->expiration_date->format('d/m/Y') }}
                                            @endif
                                        </p>
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
                            <p class="text-muted mb-0">No tiene mesas asignadas</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Información</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <i class="ri-information-line"></i>
                        <p class="mb-0 small">
                            Los delegados de mesa pueden:
                        </p>
                        <ul class="small mt-2 mb-0">
                            <li>Registrar votos en su mesa asignada</li>
                            <li>Subir actas de su mesa</li>
                            <li>Ver resultados de su mesa</li>
                        </ul>
                    </div>
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

        const tableSelect = new Choices('#voting_table_id', {
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

        // Validar fechas
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
