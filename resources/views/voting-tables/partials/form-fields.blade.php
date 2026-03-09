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

{{-- SECCIÓN 1 — IDENTIFICACIÓN DE LA MESA --}}
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
                    Seleccione el recinto donde se encuentra esta mesa.
                </small>
            @enderror
        </div>

        <div class="row">
            {{-- Número de Mesa --}}
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

            {{-- Letra de Mesa --}}
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="letter-field" class="form-label">Letra</label>
                    <input type="text" id="letter-field" name="letter"
                        class="form-control @error('letter') is-invalid @enderror"
                        placeholder="A, B, C..."
                        value="{{ old('letter', $votingTable->letter ?? '') }}"
                        maxlength="1" />
                    @error('letter')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <small class="text-muted">Opcional (Ej: A, B, C)</small>
                    @enderror
                </div>
            </div>

            {{-- Tipo de Mesa --}}
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="type-field" class="form-label">Tipo de Mesa</label>
                    <select class="form-select @error('type') is-invalid @enderror"
                            name="type" id="type-field">
                        <option value="mixta"     {{ old('type', $votingTable->type ?? 'mixta') == 'mixta'     ? 'selected' : '' }}>Mixta (Hombres y Mujeres)</option>
                        <option value="masculina" {{ old('type', $votingTable->type ?? '')       == 'masculina' ? 'selected' : '' }}>Masculina</option>
                        <option value="femenina"  {{ old('type', $votingTable->type ?? '')       == 'femenina'  ? 'selected' : '' }}>Femenina</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SECCIÓN 2 — CÓDIGOS DE IDENTIFICACIÓN --}}
<div class="card border-info mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="card-title mb-0">
            <i class="ri-barcode-line me-1"></i>
            Códigos de Identificación
            <small class="text-white-50 ms-2">(Se generan automáticamente si se dejan vacíos)</small>
        </h5>
    </div>
    <div class="card-body">
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
                            Formato: [Código Recinto]-[N° Mesa][Letra] (Ej: REC001-1A)
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
                            Formato: [Código Recinto]-M[N° Mesa][Letra] (Ej: REC001-M01A)
                        </small>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SECCIÓN 3 — RANGO DE VOTANTES --}}
<div class="card border-success mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="card-title mb-0">
            <i class="ri-group-line me-1"></i>
            Rango de Votantes y Padrón
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            {{-- Votantes Esperados --}}
            <div class="col-md-12 mb-3">
                <label for="expected_voters-field" class="form-label fw-bold">
                    Votantes Esperados (Según Padrón)
                </label>
                <input type="number" id="expected_voters-field" name="expected_voters"
                    class="form-control @error('expected_voters') is-invalid @enderror"
                    value="{{ old('expected_voters', $votingTable->expected_voters ?? 0) }}"
                    min="0" />
                @error('expected_voters')
                    <div class="invalid-feedback">{{ $message }}</div>
                @else
                    <small class="text-muted">Número total de ciudadanos habilitados para votar en esta mesa</small>
                @enderror
            </div>

            {{-- Rango Desde --}}
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="voter_range_start_name-field" class="form-label">
                        Inicio del Rango (Apellido)
                    </label>
                    <input type="text" id="voter_range_start_name-field"
                        name="voter_range_start_name"
                        class="form-control"
                        placeholder="Ej: ACOSTA"
                        value="{{ old('voter_range_start_name', $votingTable->voter_range_start_name ?? '') }}" />
                    <small class="text-muted">Primer apellido del rango alfabético</small>
                </div>
            </div>

            {{-- Rango Hasta --}}
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="voter_range_end_name-field" class="form-label">
                        Fin del Rango (Apellido)
                    </label>
                    <input type="text" id="voter_range_end_name-field"
                        name="voter_range_end_name"
                        class="form-control"
                        placeholder="Ej: ZEBALLOS"
                        value="{{ old('voter_range_end_name', $votingTable->voter_range_end_name ?? '') }}" />
                    <small class="text-muted">Último apellido del rango alfabético</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SECCIÓN 4 — DELEGADOS DE MESA --}}
<div class="card border-warning mb-4">
    <div class="card-header bg-warning text-dark">
        <h5 class="card-title mb-0">
            <i class="ri-user-star-line me-1"></i>
            Delegados de Mesa
            <small class="text-dark-50 ms-2">(Usuarios registrados en el sistema)</small>
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            @php
                $delegates = [
                    'president_id' => ['label' => 'Presidente',  'icon' => 'ri-user-star-line', 'color' => 'primary'],
                    'secretary_id' => ['label' => 'Secretario',  'icon' => 'ri-user-settings-line', 'color' => 'success'],
                    'vocal1_id'    => ['label' => 'Vocal 1',     'icon' => 'ri-user-line', 'color' => 'info'],
                    'vocal2_id'    => ['label' => 'Vocal 2',     'icon' => 'ri-user-line', 'color' => 'secondary'],
                    'vocal3_id'    => ['label' => 'Vocal 3',     'icon' => 'ri-user-line', 'color' => 'dark'],
                    'vocal4_id'    => ['label' => 'Vocal 4',     'icon' => 'ri-user-line', 'color' => 'warning'],
                ];
            @endphp

            @foreach($delegates as $field => $info)
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="{{ $field }}-field" class="form-label">
                            <i class="{{ $info['icon'] }} me-1 text-{{ $info['color'] }}"></i>
                            {{ $info['label'] }}
                        </label>
                        <select class="form-select @error($field) is-invalid @enderror"
                                name="{{ $field }}" id="{{ $field }}-field">
                            <option value="">-- No asignado --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}"
                                    data-name="{{ $user->name }} {{ $user->last_name ?? '' }}"
                                    {{ old($field, $votingTable?->$field ?? '') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} {{ $user->last_name ?? '' }}
                                    @if($user->email) ({{ $user->email }}) @endif
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

        <div class="alert alert-light mt-2 mb-0">
            <i class="ri-information-line me-1"></i>
            <small>Los delegados deben estar registrados como usuarios en el sistema.</small>
        </div>
    </div>
</div>

{{-- SECCIÓN 5 — OBSERVACIONES --}}
<div class="card border-secondary mb-4">
    <div class="card-header bg-secondary text-white">
        <h5 class="card-title mb-0">
            <i class="ri-chat-1-line me-1"></i>
            Observaciones Generales
        </h5>
    </div>
    <div class="card-body">
        <div class="mb-0">
            <textarea class="form-control @error('observations') is-invalid @enderror"
                      id="observations" name="observations"
                      rows="3"
                      placeholder="Observaciones adicionales sobre la mesa (ubicación, accesibilidad, notas especiales, etc.)">{{ old('observations', $votingTable->observations ?? '') }}</textarea>
            @error('observations')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

{{-- NOTA INFORMATIVA SOBRE DATOS ELECTORALES --}}
<div class="alert alert-info">
    <div class="d-flex">
        <div class="me-3">
            <i class="ri-information-line fs-4"></i>
        </div>
        <div>
            <h6 class="alert-heading">Información importante sobre la estructura de datos:</h6>
            <p class="mb-2">
                <strong>Esta mesa NO está limitada a un solo tipo de elección.</strong>
                Los datos electorales específicos (papeletas recibidas, votantes, estado, horarios)
                se configuran por separado para <strong>cada tipo de elección</strong> en la sección
                "Configuración Electoral" después de crear la mesa.
            </p>
            <p class="mb-0">
                <i class="ri-checkbox-circle-line text-success me-1"></i>
                La mesa quedará automáticamente habilitada para <strong>TODOS los tipos de elección activos</strong>
                en el sistema.
            </p>
        </div>
    </div>
</div>

{{-- Script para auto-generar códigos basados en el recinto seleccionado --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const institutionSelect = document.getElementById('institution_id-field');
    const numberInput = document.getElementById('number-field');
    const letterInput = document.getElementById('letter-field');
    const oepCodeInput = document.getElementById('oep_code-field');
    const internalCodeInput = document.getElementById('internal_code-field');

    function generateCodes() {
        if (!institutionSelect || !numberInput) return;

        const selectedOption = institutionSelect.options[institutionSelect.selectedIndex];
        const institutionCode = selectedOption?.dataset?.code || '';
        const number = numberInput.value;
        const letter = letterInput?.value?.toUpperCase() || '';

        if (institutionCode && number) {
            // Solo generar si los campos están vacíos o es creación nueva
            if (!oepCodeInput.value || oepCodeInput.value === '') {
                oepCodeInput.value = institutionCode + '-' + number + (letter || '');
            }
            if (!internalCodeInput.value || internalCodeInput.value === '') {
                const paddedNumber = number.padStart(2, '0');
                internalCodeInput.value = institutionCode + '-M' + paddedNumber + (letter || '');
            }
        }
    }

    if (institutionSelect) {
        institutionSelect.addEventListener('change', generateCodes);
    }
    if (numberInput) {
        numberInput.addEventListener('input', generateCodes);
    }
    if (letterInput) {
        letterInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase().replace(/[^A-Z]/g, '');
            generateCodes();
        });
    }

    // Generar códigos iniciales si estamos en creación
    @if(!isset($votingTable) || !$votingTable->exists)
        generateCodes();
    @endif
});
</script>
@endpush
