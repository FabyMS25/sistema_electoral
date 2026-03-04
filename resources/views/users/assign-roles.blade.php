{{-- resources/views/users/assign-roles.blade.php --}}
@extends('layouts.master')

@section('title', 'Asignar Roles - ' . $user->name)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Asignar Roles a: {{ $user->name }} {{ $user->last_name }}</h4>
                <p class="text-muted mb-0">Email: {{ $user->email }} | CI: {{ $user->id_card ?? 'N/A' }}</p>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('users.assign-roles', $user) }}">
                    @csrf

                    <div class="row" id="roles-container">
                        @foreach($roles as $role)
                        <div class="col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-header bg-light">
                                    <div class="form-check">
                                        <input class="form-check-input role-checkbox" type="checkbox"
                                               name="roles[{{ $loop->index }}][role_id]"
                                               value="{{ $role->id }}"
                                               id="role_{{ $role->id }}"
                                               data-role-id="{{ $role->id }}"
                                               {{ isset($currentRoles[$role->id]) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="role_{{ $role->id }}">
                                            {{ $role->display_name }}
                                        </label>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small">{{ $role->description }}</p>

                                    <div class="role-config" id="config_{{ $role->id }}"
                                         style="{{ isset($currentRoles[$role->id]) ? '' : 'display: none;' }}">

                                        <div class="mb-2">
                                            <label class="form-label">Ámbito:</label>
                                            <select name="roles[{{ $loop->index }}][scope]"
                                                    class="form-select form-select-sm scope-select"
                                                    data-role-id="{{ $role->id }}">
                                                <option value="global" {{ (isset($currentRoles[$role->id]) && $currentRoles[$role->id]['scope'] == 'global') ? 'selected' : '' }}>
                                                    Global (sin restricciones)
                                                </option>
                                                <option value="institution" {{ (isset($currentRoles[$role->id]) && $currentRoles[$role->id]['scope'] == 'institution') ? 'selected' : '' }}>
                                                    Institución/Recinto
                                                </option>
                                                <option value="voting_table" {{ (isset($currentRoles[$role->id]) && $currentRoles[$role->id]['scope'] == 'voting_table') ? 'selected' : '' }}>
                                                    Mesa de Votación
                                                </option>
                                            </select>
                                        </div>

                                        <div class="institution-select" id="inst_{{ $role->id }}"
                                             style="{{ (isset($currentRoles[$role->id]) && $currentRoles[$role->id]['scope'] == 'institution') ? '' : 'display: none;' }}">
                                            <label class="form-label">Seleccionar Institución:</label>
                                            <select name="roles[{{ $loop->index }}][institution_id]"
                                                    class="form-select form-select-sm">
                                                <option value="">Seleccione...</option>
                                                @foreach($institutions as $institution)
                                                    <option value="{{ $institution->id }}"
                                                        {{ (isset($currentRoles[$role->id]) && $currentRoles[$role->id]['institution_id'] == $institution->id) ? 'selected' : '' }}>
                                                        {{ $institution->name }} ({{ $institution->code }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="table-select" id="table_{{ $role->id }}"
                                             style="{{ (isset($currentRoles[$role->id]) && $currentRoles[$role->id]['scope'] == 'voting_table') ? '' : 'display: none;' }}">
                                            <label class="form-label">Seleccionar Mesa:</label>
                                            <select name="roles[{{ $loop->index }}][voting_table_id]"
                                                    class="form-select form-select-sm">
                                                <option value="">Seleccione...</option>
                                                @foreach($votingTables as $table)
                                                    <option value="{{ $table->id }}"
                                                        {{ (isset($currentRoles[$role->id]) && $currentRoles[$role->id]['voting_table_id'] == $table->id) ? 'selected' : '' }}>
                                                        {{ $table->institution->name }} - Mesa {{ $table->number }} ({{ $table->oep_code }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mt-2">
                                            <label class="form-label">Tipo de Elección:</label>
                                            <select name="roles[{{ $loop->index }}][election_type_id]"
                                                    class="form-select form-select-sm">
                                                <option value="">Todas</option>
                                                @foreach($electionTypes as $type)
                                                    <option value="{{ $type->id }}"
                                                        {{ (isset($currentRoles[$role->id]) && $currentRoles[$role->id]['election_type_id'] == $type->id) ? 'selected' : '' }}>
                                                        {{ $type->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Guardar Asignaciones</button>
                        <a href="{{ route('users.show', $user) }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    // Mostrar/ocultar configuración al marcar rol
    $('.role-checkbox').change(function() {
        var roleId = $(this).data('role-id');
        var configDiv = $('#config_' + roleId);

        if ($(this).is(':checked')) {
            configDiv.slideDown();
        } else {
            configDiv.slideUp();
        }
    });

    // Mostrar/ocultar selectores según ámbito
    $('.scope-select').change(function() {
        var roleId = $(this).data('role-id');
        var scope = $(this).val();

        $('#inst_' + roleId).slideUp();
        $('#table_' + roleId).slideUp();

        if (scope === 'institution') {
            $('#inst_' + roleId).slideDown();
        } else if (scope === 'voting_table') {
            $('#table_' + roleId).slideDown();
        }
    });
});
</script>
@endsection
