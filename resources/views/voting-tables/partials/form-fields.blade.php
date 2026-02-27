{{-- resources/views/voting-tables/partials/form-fields.blade.php --}}
@props(['votingTable' => null, 'institutions' => [], 'electionTypes' => []])

<!-- Datos Básicos -->
<h5 class="mb-3 text-primary">
    <i class="ri-information-line me-1"></i>
    Datos Básicos de la Mesa
</h5>

<div class="row">
    <div class="col-md-4">
        <div class="mb-3 required-field">
            <label for="number-field" class="form-label">Número de Mesa</label>
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
    
    <div class="col-md-4">
        <div class="mb-3">
            <label for="letter-field" class="form-label">Letra (opcional)</label>
            <input type="text" id="letter-field" name="letter" 
                class="form-control @error('letter') is-invalid @enderror" 
                placeholder="Ej: A" 
                value="{{ old('letter', $votingTable->letter ?? '') }}" 
                maxlength="1" />
            @error('letter')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="mb-3">
            <label for="type-field" class="form-label">Tipo de Mesa</label>
            <select class="form-control @error('type') is-invalid @enderror" 
                    name="type" id="type-field">
                <option value="mixta" {{ old('type', $votingTable->type ?? 'mixta') == 'mixta' ? 'selected' : '' }}>Mixta</option>
                <option value="masculina" {{ old('type', $votingTable->type ?? '') == 'masculina' ? 'selected' : '' }}>Masculina</option>
                <option value="femenina" {{ old('type', $votingTable->type ?? '') == 'femenina' ? 'selected' : '' }}>Femenina</option>
            </select>
            @error('type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="code-field" class="form-label">Código de Mesa</label>
            <input type="text" id="code-field" name="code" 
                class="form-control @error('code') is-invalid @enderror" 
                placeholder="Se genera automáticamente" 
                value="{{ old('code', $votingTable->code ?? '') }}" />
            <small class="text-muted">Dejar vacío para generar automáticamente</small>
            @error('code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="mb-3">
            <label for="code_ine-field" class="form-label">Código INE/OEP</label>
            <input type="text" id="code_ine-field" name="code_ine" 
                class="form-control @error('code_ine') is-invalid @enderror" 
                placeholder="Código del INE" 
                value="{{ old('code_ine', $votingTable->code_ine ?? '') }}" />
            @error('code_ine')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<!-- Rango de Votantes -->
<h5 class="mb-3 text-primary mt-4">
    <i class="ri-group-line me-1"></i>
    Rango de Votantes
</h5>

<div class="row">
    <div class="col-md-3">
        <div class="mb-3">
            <label for="from_name-field" class="form-label">Desde (Apellido)</label>
            <input type="text" id="from_name-field" name="from_name" 
                class="form-control @error('from_name') is-invalid @enderror" 
                placeholder="Ej: ACOSTA" 
                value="{{ old('from_name', $votingTable->from_name ?? '') }}" />
            @error('from_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="mb-3">
            <label for="to_name-field" class="form-label">Hasta (Apellido)</label>
            <input type="text" id="to_name-field" name="to_name" 
                class="form-control @error('to_name') is-invalid @enderror" 
                placeholder="Ej: ZEBALLOS" 
                value="{{ old('to_name', $votingTable->to_name ?? '') }}" />
            @error('to_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="mb-3">
            <label for="from_number-field" class="form-label">Desde (N° Carnet)</label>
            <input type="number" id="from_number-field" name="from_number" 
                class="form-control @error('from_number') is-invalid @enderror" 
                placeholder="Ej: 1000000" 
                value="{{ old('from_number', $votingTable->from_number ?? '') }}" />
            @error('from_number')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="mb-3">
            <label for="to_number-field" class="form-label">Hasta (N° Carnet)</label>
            <input type="number" id="to_number-field" name="to_number" 
                class="form-control @error('to_number') is-invalid @enderror" 
                placeholder="Ej: 1999999" 
                value="{{ old('to_number', $votingTable->to_number ?? '') }}" />
            @error('to_number')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<!-- Ubicación -->
<h5 class="mb-3 text-primary mt-4">
    <i class="ri-map-pin-line me-1"></i>
    Ubicación
</h5>

<div class="row">
    <div class="col-md-8">
        <div class="mb-3 required-field">
            <label for="institution_id-field" class="form-label">Institución/Recinto</label>
            <select class="form-control @error('institution_id') is-invalid @enderror" 
                    name="institution_id" id="institution_id-field" required>
                <option value="">Seleccione Recinto</option>
                @foreach($institutions as $institution)
                    <option value="{{ $institution->id }}" 
                        {{ old('institution_id', $votingTable->institution_id ?? '') == $institution->id ? 'selected' : '' }}>
                        {{ $institution->name }} ({{ $institution->code }})
                    </option>
                @endforeach
            </select>
            @error('institution_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="mb-3 required-field">
            <label for="election_type_id-field" class="form-label">Tipo de Elección</label>
            <select class="form-control @error('election_type_id') is-invalid @enderror" 
                    name="election_type_id" id="election_type_id-field" required>
                <option value="">Seleccione Tipo</option>
                @foreach($electionTypes as $type)
                    <option value="{{ $type->id }}" 
                        {{ old('election_type_id', $votingTable->election_type_id ?? '') == $type->id ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>
            @error('election_type_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<!-- Datos Electorales -->
<h5 class="mb-3 text-primary mt-4">
    <i class="ri-bar-chart-line me-1"></i>
    Datos Electorales
</h5>

<div class="row">
    <div class="col-md-3">
        <div class="mb-3">
            <label for="registered_citizens-field" class="form-label">Ciudadanos Habilitados</label>
            <input type="number" id="registered_citizens-field" name="registered_citizens" 
                class="form-control @error('registered_citizens') is-invalid @enderror" 
                placeholder="0" value="{{ old('registered_citizens', $votingTable->registered_citizens ?? 0) }}" min="0" />
            @error('registered_citizens')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="mb-3">
            <label for="voted_citizens-field" class="form-label">Ciudadanos que Votaron</label>
            <input type="number" id="voted_citizens-field" name="voted_citizens" 
                class="form-control @error('voted_citizens') is-invalid @enderror" 
                placeholder="0" value="{{ old('voted_citizens', $votingTable->voted_citizens ?? 0) }}" min="0" />
            @error('voted_citizens')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="mb-3">
            <label for="blank_votes-field" class="form-label">Votos en Blanco</label>
            <input type="number" id="blank_votes-field" name="blank_votes" 
                class="form-control @error('blank_votes') is-invalid @enderror" 
                placeholder="0" value="{{ old('blank_votes', $votingTable->blank_votes ?? 0) }}" min="0" />
            @error('blank_votes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="mb-3">
            <label for="null_votes-field" class="form-label">Votos Nulos</label>
            <input type="number" id="null_votes-field" name="null_votes" 
                class="form-control @error('null_votes') is-invalid @enderror" 
                placeholder="0" value="{{ old('null_votes', $votingTable->null_votes ?? 0) }}" min="0" />
            @error('null_votes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="mb-3">
            <label for="computed_records-field" class="form-label">Papeletas Computadas</label>
            <input type="number" id="computed_records-field" name="computed_records" 
                class="form-control @error('computed_records') is-invalid @enderror" 
                placeholder="0" value="{{ old('computed_records', $votingTable->computed_records ?? 0) }}" min="0" />
            @error('computed_records')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="mb-3">
            <label for="annulled_records-field" class="form-label">Papeletas Anuladas</label>
            <input type="number" id="annulled_records-field" name="annulled_records" 
                class="form-control @error('annulled_records') is-invalid @enderror" 
                placeholder="0" value="{{ old('annulled_records', $votingTable->annulled_records ?? 0) }}" min="0" />
            @error('annulled_records')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="mb-3">
            <label for="enabled_records-field" class="form-label">Papeletas Habilitadas</label>
            <input type="number" id="enabled_records-field" name="enabled_records" 
                class="form-control @error('enabled_records') is-invalid @enderror" 
                placeholder="0" value="{{ old('enabled_records', $votingTable->enabled_records ?? 0) }}" min="0" />
            @error('enabled_records')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="mb-3">
            <label for="status-field" class="form-label">Estado</label>
            <select class="form-control @error('status') is-invalid @enderror" 
                    name="status" id="status-field">
                <option value="pendiente" {{ old('status', $votingTable->status ?? 'pendiente') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                <option value="en_proceso" {{ old('status', $votingTable->status ?? '') == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                <option value="cerrado" {{ old('status', $votingTable->status ?? '') == 'cerrado' ? 'selected' : '' }}>Cerrado</option>
                <option value="en_computo" {{ old('status', $votingTable->status ?? '') == 'en_computo' ? 'selected' : '' }}>En Cómputo</option>
                <option value="computado" {{ old('status', $votingTable->status ?? '') == 'computado' ? 'selected' : '' }}>Computado</option>
                <option value="observado" {{ old('status', $votingTable->status ?? '') == 'observado' ? 'selected' : '' }}>Observado</option>
                <option value="anulado" {{ old('status', $votingTable->status ?? '') == 'anulado' ? 'selected' : '' }}>Anulado</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<!-- Horarios -->
<h5 class="mb-3 text-primary mt-4">
    <i class="ri-time-line me-1"></i>
    Horarios
</h5>

<div class="row">
    <div class="col-md-4">
        <div class="mb-3">
            <label for="opening_time-field" class="form-label">Hora de Apertura</label>
            <input type="time" id="opening_time-field" name="opening_time" 
                class="form-control @error('opening_time') is-invalid @enderror" 
                value="{{ old('opening_time', $votingTable->opening_time ?? '') }}" />
            @error('opening_time')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="mb-3">
            <label for="closing_time-field" class="form-label">Hora de Cierre</label>
            <input type="time" id="closing_time-field" name="closing_time" 
                class="form-control @error('closing_time') is-invalid @enderror" 
                value="{{ old('closing_time', $votingTable->closing_time ?? '') }}" />
            @error('closing_time')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="mb-3">
            <label for="election_date-field" class="form-label">Fecha de Elección</label>
            <input type="date" id="election_date-field" name="election_date" 
                class="form-control @error('election_date') is-invalid @enderror" 
                value="{{ old('election_date', $votingTable->election_date ?? '') }}" />
            @error('election_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<!-- Acta -->
<h5 class="mb-3 text-primary mt-4">
    <i class="ri-file-copy-line me-1"></i>
    Acta Electoral
</h5>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="acta_number-field" class="form-label">Número de Acta</label>
            <input type="text" id="acta_number-field" name="acta_number" 
                class="form-control @error('acta_number') is-invalid @enderror" 
                placeholder="Ej: ACT-001" 
                value="{{ old('acta_number', $votingTable->acta_number ?? '') }}" />
            @error('acta_number')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="mb-3">
            <label for="acta_uploaded_at-field" class="form-label">Fecha de Subida</label>
            <input type="datetime-local" id="acta_uploaded_at-field" name="acta_uploaded_at" 
                class="form-control @error('acta_uploaded_at') is-invalid @enderror" 
                value="{{ old('acta_uploaded_at', $votingTable->acta_uploaded_at ?? '') }}" />
            @error('acta_uploaded_at')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<!-- Observaciones -->
<div class="mb-3">
    <label for="observations-field" class="form-label">Observaciones</label>
    <textarea id="observations-field" name="observations" 
        class="form-control @error('observations') is-invalid @enderror" 
        placeholder="Observaciones adicionales sobre la mesa" 
        rows="2">{{ old('observations', $votingTable->observations ?? '') }}</textarea>
    @error('observations')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>