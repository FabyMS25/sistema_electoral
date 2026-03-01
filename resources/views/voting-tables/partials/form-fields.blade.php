{{-- resources/views/voting-tables/partials/form-fields.blade.php --}}
@props(['votingTable' => null, 'institutions' => [], 'electionTypes' => [], 'statusOptions' => []])

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

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="ri-check-line me-1"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- ===== ETAPA 1: CONFIGURACIÓN PRE-ELECTORAL (OBLIGATORIO) ===== -->
<div class="card border-primary mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0">
            <i class="ri-settings-4-line me-1"></i>
            ETAPA 1: CONFIGURACIÓN DE LA MESA
            <small class="text-white-50 ms-2">(Datos obligatorios - Completar ANTES de la elección)</small>
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <div class="mb-3">
                    <label for="institution_id-field" class="form-label fw-bold">
                        Institución/Recinto <span class="text-danger">*</span>
                        <i class="ri-question-line text-muted" data-bs-toggle="tooltip" title="Recinto electoral donde se encuentra la mesa"></i>
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
                        <small class="text-muted">Seleccione el recinto donde funcionará la mesa</small>
                    @enderror
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="election_type_id-field" class="form-label fw-bold">
                        Tipo de Elección <span class="text-danger">*</span>
                        <i class="ri-question-line text-muted" data-bs-toggle="tooltip" title="Tipo de proceso electoral"></i>
                    </label>
                    <select class="form-select @error('election_type_id') is-invalid @enderror" 
                            name="election_type_id" id="election_type_id-field" required>
                        <option value="">-- Seleccione tipo --</option>
                        @foreach($electionTypes as $type)
                            <option value="{{ $type->id }}" 
                                {{ old('election_type_id', $votingTable->election_type_id ?? '') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('election_type_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <small class="text-muted">Seleccione el tipo de elección</small>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="number-field" class="form-label fw-bold">
                        Número de Mesa <span class="text-danger">*</span>
                        <i class="ri-question-line text-muted" data-bs-toggle="tooltip" title="Número único de la mesa dentro del recinto"></i>
                    </label>
                    <input type="number" id="number-field" name="number" 
                        class="form-control @error('number') is-invalid @enderror" 
                        placeholder="Ej: 1, 2, 3..." 
                        value="{{ old('number', $votingTable->number ?? '') }}" 
                        min="1" required />
                    @error('number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <small class="text-muted">Número que identifica la mesa en el recinto</small>
                    @enderror
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="letter-field" class="form-label">
                        Letra
                        <i class="ri-question-line text-muted" data-bs-toggle="tooltip" title="Letra adicional para mesas con subdivisión (A, B, C...)"></i>
                    </label>
                    <input type="text" id="letter-field" name="letter" 
                        class="form-control @error('letter') is-invalid @enderror" 
                        placeholder="Ej: A, B, C..." 
                        value="{{ old('letter', $votingTable->letter ?? '') }}" 
                        maxlength="1" />
                    @error('letter')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <small class="text-muted">Opcional - Solo para mesas con subdivisión</small>
                    @enderror
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="type-field" class="form-label">
                        Tipo de Mesa
                        <i class="ri-question-line text-muted" data-bs-toggle="tooltip" title="Tipo de mesa según el género de los votantes"></i>
                    </label>
                    <select class="form-select @error('type') is-invalid @enderror" name="type" id="type-field">
                        <option value="mixta" {{ old('type', $votingTable->type ?? 'mixta') == 'mixta' ? 'selected' : '' }}>Mixta (Hombres y Mujeres)</option>
                        <option value="masculina" {{ old('type', $votingTable->type ?? '') == 'masculina' ? 'selected' : '' }}>Masculina</option>
                        <option value="femenina" {{ old('type', $votingTable->type ?? '') == 'femenina' ? 'selected' : '' }}>Femenina</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <small class="text-muted">Por defecto: Mixta</small>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="oep_code-field" class="form-label">
                        Código OEP
                        <i class="ri-question-line text-muted" data-bs-toggle="tooltip" title="Código oficial del Órgano Electoral Plurinacional"></i>
                    </label>
                    <input type="text" id="oep_code-field" name="oep_code" 
                        class="form-control @error('oep_code') is-invalid @enderror" 
                        placeholder="Ej: 303182-1" 
                        value="{{ old('oep_code', $votingTable->oep_code ?? '') }}" />
                    @error('oep_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <small class="text-muted">
                            <i class="ri-information-line"></i>
                            Código que aparece en el acta electoral
                        </small>
                    @enderror
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="internal_code-field" class="form-label">
                        Código Interno
                        <i class="ri-question-line text-muted" data-bs-toggle="tooltip" title="Código interno del sistema. Se genera automáticamente"></i>
                    </label>
                    <input type="text" id="internal_code-field" name="internal_code" 
                        class="form-control @error('internal_code') is-invalid @enderror" 
                        placeholder="Se genera automáticamente" 
                        value="{{ old('internal_code', $votingTable->internal_code ?? '') }}" />
                    @error('internal_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <small class="text-muted">
                            <i class="ri-information-line"></i>
                            Dejar vacío para generar automáticamente (Ej: REC-001-M01)
                        </small>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Estado de la Mesa -->
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="status-field" class="form-label fw-bold">
                        Estado de la Mesa <span class="text-danger">*</span>
                        <i class="ri-question-line text-muted" data-bs-toggle="tooltip" title="Estado actual de la mesa. Por defecto: Configurada"></i>
                    </label>
                    <select class="form-select @error('status') is-invalid @enderror" name="status" id="status-field" required>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('status', $votingTable->status ?? 'configurada') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== ETAPA 2: INFORMACIÓN DEL PADRÓN (OPCIONAL - PRE-ELECTORAL) ===== -->
<div class="card border-info mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="card-title mb-0">
            <i class="ri-group-line me-1"></i>
            ETAPA 2: INFORMACIÓN DEL PADRÓN ELECTORAL
            <small class="text-white-50 ms-2">(Opcional - Datos del padrón para esta mesa)</small>
        </h5>
    </div>
    <div class="card-body">
        <!-- Rango de Votantes (del padrón) -->
        <div class="row">
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="voter_range_start_name-field" class="form-label">Desde (Apellido)</label>
                    <input type="text" id="voter_range_start_name-field" name="voter_range_start_name" 
                        class="form-control" placeholder="Ej: ACOSTA" 
                        value="{{ old('voter_range_start_name', $votingTable->voter_range_start_name ?? '') }}" />
                    <small class="text-muted">Apellido inicial del rango</small>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="voter_range_end_name-field" class="form-label">Hasta (Apellido)</label>
                    <input type="text" id="voter_range_end_name-field" name="voter_range_end_name" 
                        class="form-control" placeholder="Ej: ZEBALLOS" 
                        value="{{ old('voter_range_end_name', $votingTable->voter_range_end_name ?? '') }}" />
                    <small class="text-muted">Apellido final del rango</small>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="voter_range_start_id-field" class="form-label">Desde (N° Carnet)</label>
                    <input type="number" id="voter_range_start_id-field" name="voter_range_start_id" 
                        class="form-control" placeholder="Ej: 1000000" 
                        value="{{ old('voter_range_start_id', $votingTable->voter_range_start_id ?? '') }}" />
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="voter_range_end_id-field" class="form-label">Hasta (N° Carnet)</label>
                    <input type="number" id="voter_range_end_id-field" name="voter_range_end_id" 
                        class="form-control" placeholder="Ej: 1999999" 
                        value="{{ old('voter_range_end_id', $votingTable->voter_range_end_id ?? '') }}" />
                </div>
            </div>
        </div>

        <!-- Votantes Esperados (Padrón) -->
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="expected_voters-field" class="form-label">Votantes Esperados (Padrón)</label>
                    <input type="number" id="expected_voters-field" name="expected_voters" 
                        class="form-control" value="{{ old('expected_voters', $votingTable->expected_voters ?? 0) }}" min="0" />
                    <small class="text-muted">Total de ciudadanos habilitados según padrón</small>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="ballots_received-field" class="form-label">Papeletas Recibidas</label>
                    <input type="number" id="ballots_received-field" name="ballots_received" 
                        class="form-control" value="{{ old('ballots_received', $votingTable->ballots_received ?? 0) }}" min="0" />
                    <small class="text-muted">Papeletas que realmente recibió la mesa</small>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="ballots_spoiled-field" class="form-label">Papeletas Deterioradas</label>
                    <input type="number" id="ballots_spoiled-field" name="ballots_spoiled" 
                        class="form-control" value="{{ old('ballots_spoiled', $votingTable->ballots_spoiled ?? 0) }}" min="0" />
                    <small class="text-muted">Papeletas dañadas antes de usar</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== ETAPA 3: DURANTE LA VOTACIÓN ===== -->
<div class="card border-warning mb-4">
    <div class="card-header bg-warning text-dark">
        <h5 class="card-title mb-0">
            <i class="ri-time-line me-1"></i>
            ETAPA 3: DURANTE LA VOTACIÓN
            <small class="text-dark-50 ms-2">(Opcional - Se llena el día de la elección)</small>
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="opening_time" class="form-label">Hora de Apertura</label>
                    <input type="time" class="form-control @error('opening_time') is-invalid @enderror" 
                           id="opening_time" name="opening_time" 
                           value="{{ old('opening_time', $votingTable ? \Carbon\Carbon::parse($votingTable->opening_time)->format('H:i') : '') }}">
                    @error('opening_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="closing_time" class="form-label">Hora de Cierre</label>
                    <input type="time" class="form-control @error('closing_time') is-invalid @enderror" 
                           id="closing_time" name="closing_time" 
                           value="{{ old('closing_time', $votingTable ? \Carbon\Carbon::parse($votingTable->closing_time)->format('H:i') : '') }}">
                    @error('closing_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="election_date" class="form-label">Fecha de Elección</label>
                    <input type="date" class="form-control @error('election_date') is-invalid @enderror" 
                           id="election_date" name="election_date" 
                           value="{{ old('election_date', $votingTable ? \Carbon\Carbon::parse($votingTable->election_date)->format('Y-m-d') : '') }}">
                    @error('election_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== ETAPA 4: ACTA Y OBSERVACIONES ===== -->
<div class="card border-secondary mb-4">
    <div class="card-header bg-secondary text-white">
        <h5 class="card-title mb-0">
            <i class="ri-file-copy-line me-1"></i>
            ETAPA 4: ACTA Y OBSERVACIONES
            <small class="text-white-50 ms-2">(Se llena DESPUÉS de la elección)</small>
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="acta_number" class="form-label">Número de Acta</label>
                    <input type="text" class="form-control @error('acta_number') is-invalid @enderror" 
                           id="acta_number" name="acta_number" 
                           placeholder="Ej: ACT-001" 
                           value="{{ old('acta_number', $votingTable->acta_number ?? '') }}">
                    @error('acta_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Número del acta de escrutinio</small>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label for="observations" class="form-label">Observaciones</label>
            <textarea class="form-control @error('observations') is-invalid @enderror" 
                      id="observations" name="observations" 
                      rows="3" placeholder="Observaciones adicionales...">{{ old('observations', $votingTable->observations ?? '') }}</textarea>
            @error('observations')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<!-- Nota informativa -->
<div class="alert alert-info mt-3">
    <i class="ri-information-line me-1"></i>
    <strong>Nota:</strong> Los campos de la Etapa 3 y 4 pueden dejarse vacíos al crear la mesa. 
    Se llenarán durante el proceso electoral y el registro de votos.
</div>