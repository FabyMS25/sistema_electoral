{{-- resources/views/voting-tables/partials/form-fields.blade.php --}}
@props(['votingTable' => null, 'institutions' => [], 'users' => []])

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <div class="d-flex">
            <i class="ri-error-warning-line fs-18 me-2"></i>
            <div>
                <strong>Por favor corrige los siguientes errores:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- SECCIÓN 1 — IDENTIFICACIÓN --}}
<div class="card border-primary mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0">
            <i class="ri-settings-4-line me-1"></i>
            Identificación de la Mesa
        </h5>
    </div>
    <div class="card-body">

        {{-- Recinto --}}
        <div class="mb-3">
            <label for="institution_id-field" class="form-label fw-bold">
                Recinto Electoral <span class="text-danger">*</span>
            </label>
            <select class="form-select @error('institution_id') is-invalid @enderror"
                    name="institution_id" id="institution_id-field" required>
                <option value="">-- Seleccione un recinto --</option>
                @foreach($institutions as $institution)
                    <option value="{{ $institution->id }}"
                            data-code="{{ $institution->code }}"
                            {{ old('institution_id', $votingTable->institution_id ?? '') == $institution->id ? 'selected' : '' }}>
                        {{ $institution->name }} ({{ $institution->code }})
                    </option>
                @endforeach
            </select>
            @error('institution_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <small class="text-muted">
                    La mesa estará disponible para todos los tipos de elección activos.
                </small>
            @enderror
        </div>

        <div class="row">
            {{-- Número --}}
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="number-field" class="form-label fw-bold">
                        N° Mesa <span class="text-danger">*</span>
                    </label>
                    <input type="number" id="number-field" name="number"
                        class="form-control @error('number') is-invalid @enderror"
                        placeholder="Ej: 1"
                        value="{{ old('number', $votingTable->number ?? '') }}"
                        min="1" required />
                    @error('number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Letra --}}
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="letter-field" class="form-label">Letra</label>
                    <input type="text" id="letter-field" name="letter"
                        class="form-control @error('letter') is-invalid @enderror"
                        placeholder="A, B…"
                        value="{{ old('letter', $votingTable->letter ?? '') }}"
                        maxlength="1" />
                    @error('letter')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <small class="text-muted">Opcional</small>
                    @enderror
                </div>
            </div>

            {{-- Tipo --}}
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="type-field" class="form-label">Tipo de Mesa</label>
                    <select class="form-select @error('type') is-invalid @enderror"
                            name="type" id="type-field">
                        <option value="mixta"     {{ old('type', $votingTable->type ?? 'mixta') == 'mixta'     ? 'selected' : '' }}>Mixta (H y M)</option>
                        <option value="masculina" {{ old('type', $votingTable->type ?? '')       == 'masculina' ? 'selected' : '' }}>Masculina</option>
                        <option value="femenina"  {{ old('type', $votingTable->type ?? '')       == 'femenina'  ? 'selected' : '' }}>Femenina</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Votantes esperados --}}
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="expected_voters-field" class="form-label">
                        Votantes Esperados (Padrón)
                    </label>
                    <input type="number" id="expected_voters-field" name="expected_voters"
                        class="form-control @error('expected_voters') is-invalid @enderror"
                        value="{{ old('expected_voters', $votingTable->expected_voters ?? 0) }}"
                        min="0" />
                    @error('expected_voters')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <small class="text-muted">Ciudadanos habilitados según padrón</small>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Códigos --}}
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="oep_code-field" class="form-label">Código OEP</label>
                    <input type="text" id="oep_code-field" name="oep_code"
                        class="form-control @error('oep_code') is-invalid @enderror"
                        placeholder="Se genera automáticamente"
                        value="{{ old('oep_code', $votingTable->oep_code ?? '') }}" />
                    @error('oep_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <small class="text-muted">
                            <i class="ri-information-line"></i>
                            Dejar vacío para generar (Ej: REC-001-1)
                        </small>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="internal_code-field" class="form-label">Código Interno</label>
                    <input type="text" id="internal_code-field" name="internal_code"
                        class="form-control @error('internal_code') is-invalid @enderror"
                        placeholder="Se genera automáticamente"
                        value="{{ old('internal_code', $votingTable->internal_code ?? '') }}" />
                    @error('internal_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <small class="text-muted">
                            <i class="ri-information-line"></i>
                            Dejar vacío para generar (Ej: REC-001-M01)
                        </small>
                    @enderror
                </div>
            </div>
        </div>

    </div>
</div>

{{-- SECCIÓN 2 — RANGO DE VOTANTES --}}
<div class="card border-info mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="card-title mb-0">
            <i class="ri-group-line me-1"></i>
            Rango de Votantes
            <small class="text-white-50 ms-2">(Opcional)</small>
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="voter_range_start_name-field" class="form-label">
                        Primer Apellido del Rango
                    </label>
                    <input type="text" id="voter_range_start_name-field"
                        name="voter_range_start_name"
                        class="form-control"
                        placeholder="Ej: ACOSTA"
                        value="{{ old('voter_range_start_name', $votingTable->voter_range_start_name ?? '') }}" />
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="voter_range_end_name-field" class="form-label">
                        Último Apellido del Rango
                    </label>
                    <input type="text" id="voter_range_end_name-field"
                        name="voter_range_end_name"
                        class="form-control"
                        placeholder="Ej: ZEBALLOS"
                        value="{{ old('voter_range_end_name', $votingTable->voter_range_end_name ?? '') }}" />
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SECCIÓN 3 — DELEGADOS DE MESA --}}
<div class="card border-secondary mb-4">
    <div class="card-header bg-secondary text-white">
        <h5 class="card-title mb-0">
            <i class="ri-user-star-line me-1"></i>
            Delegados de Mesa
            <small class="text-white-50 ms-2">(Opcional — también se pueden asignar después)</small>
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            @php
                $delegates = [
                    'president_id' => ['label' => 'Presidente',  'icon' => 'ri-user-star-line'],
                    'secretary_id' => ['label' => 'Secretario',  'icon' => 'ri-user-line'],
                    'vocal1_id'    => ['label' => 'Vocal 1',     'icon' => 'ri-user-line'],
                    'vocal2_id'    => ['label' => 'Vocal 2',     'icon' => 'ri-user-line'],
                    'vocal3_id'    => ['label' => 'Vocal 3',     'icon' => 'ri-user-line'],
                    'vocal4_id'    => ['label' => 'Vocal 4',     'icon' => 'ri-user-line'],
                ];
            @endphp

            @foreach($delegates as $field => $info)
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="{{ $field }}-field" class="form-label">
                            <i class="{{ $info['icon'] }} me-1"></i>
                            {{ $info['label'] }}
                        </label>
                        <select class="form-select @error($field) is-invalid @enderror"
                                name="{{ $field }}" id="{{ $field }}-field">
                            <option value="">-- No asignado --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ old($field, $votingTable?->$field ?? '') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} {{ $user->last_name ?? '' }}
                                </option>
                            @endforeach
                        </select>
                        @error($field)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- SECCIÓN 4 — OBSERVACIONES --}}
<div class="card border-light mb-4">
    <div class="card-body">
        <div class="mb-0">
            <label for="observations" class="form-label">Observaciones</label>
            <textarea class="form-control @error('observations') is-invalid @enderror"
                      id="observations" name="observations"
                      rows="3"
                      placeholder="Observaciones adicionales…">{{ old('observations', $votingTable->observations ?? '') }}</textarea>
            @error('observations')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

{{-- Info note --}}
<div class="alert alert-info">
    <i class="ri-information-line me-1"></i>
    <strong>Nota:</strong> Los datos electorales (papeletas, estado, horarios) se configuran
    por separado en <strong>Configuración de Elección</strong> después de crear la mesa,
    una vez por cada tipo de elección activo.
</div>
