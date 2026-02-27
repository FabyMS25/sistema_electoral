{{-- resources/views/institutions/partials/form-fields.blade.php --}}
@props(['institution' => null, 'departments' => []])

<!-- Datos Básicos -->
<h5 class="mb-3 text-primary">
    <i class="ri-information-line me-1"></i>
    Datos Básicos del Recinto
</h5>
<div class="row">
    <div class="col-md-6">
        <div class="mb-3 required-field">
            <label for="name-field" class="form-label">Nombre del Recinto</label>
            <input type="text" id="name-field" name="name" 
                class="form-control @error('name') is-invalid @enderror" 
                placeholder="Ej: Unidad Educativa Simón Bolívar" 
                value="{{ old('name', $institution->name ?? '') }}" required />
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="mb-3">
            <label for="short-name-field" class="form-label">Nombre Corto</label>
            <input type="text" id="short-name-field" name="short_name" 
                class="form-control @error('short_name') is-invalid @enderror" 
                placeholder="Ej: UE Simón Bolívar" 
                value="{{ old('short_name', $institution->short_name ?? '') }}" />
            @error('short_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="mb-3">
            <label for="code-field" class="form-label">Código</label>
            <input type="text" id="code-field" name="code" 
                class="form-control @error('code') is-invalid @enderror" 
                placeholder="Se genera automáticamente" 
                value="{{ old('code', $institution->code ?? '') }}" />
            <small class="text-muted">Dejar vacío para generar automáticamente</small>
            @error('code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<!-- Ubicación Geográfica -->
<h5 class="mb-3 text-primary mt-4">
    <i class="ri-map-pin-line me-1"></i>
    Ubicación Geográfica
</h5>
<div class="row">
    <div class="col-md-3">
        <div class="mb-3 required-field">
            <label for="department-field" class="form-label">Departamento</label>
            <select class="form-control @error('department_id') is-invalid @enderror" 
                    name="department_id" id="department-field"
                    data-url="{{ url('institutions/provinces') }}" required>
                <option value="">Seleccione Departamento</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" 
                        {{ old('department_id', $institution->locality->municipality->province->department->id ?? '') == $department->id ? 'selected' : '' }}>
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>
            @error('department_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="mb-3 required-field">
            <label for="province-field" class="form-label">Provincia</label>
            <select class="form-control @error('province_id') is-invalid @enderror" 
                    name="province_id" id="province-field"
                    data-url="{{ url('institutions/municipalities') }}" required disabled>
                <option value="">Seleccione Provincia</option>
            </select>
            @error('province_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="mb-3 required-field">
            <label for="municipality-field" class="form-label">Municipio</label>
            <select class="form-control @error('municipality_id') is-invalid @enderror" 
                    name="municipality_id" id="municipality-field"
                    data-url="{{ url('institutions/localities') }}" required disabled>
                <option value="">Seleccione Municipio</option>
            </select>
            @error('municipality_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="mb-3 required-field">
            <label for="locality-field" class="form-label">Localidad</label>
            <select class="form-control @error('locality_id') is-invalid @enderror" 
                    name="locality_id" id="locality-field"
                    data-url="{{ url('institutions/districts') }}" required disabled>
                <option value="">Seleccione Localidad</option>
            </select>
            @error('locality_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="district-field" class="form-label">Distrito</label>
            <select class="form-control @error('district_id') is-invalid @enderror" 
                    name="district_id" id="district-field"
                    data-url="{{ url('institutions/zones') }}" disabled>
                <option value="">Seleccione Distrito (opcional)</option>
            </select>
            @error('district_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="zone-field" class="form-label">Zona</label>
            <select class="form-control @error('zone_id') is-invalid @enderror" 
                    name="zone_id" id="zone-field" disabled>
                <option value="">Seleccione Zona (opcional)</option>
            </select>
            @error('zone_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<!-- Ubicación Física -->
<h5 class="mb-3 text-primary mt-4">
    <i class="ri-road-map-line me-1"></i>
    Ubicación Física y Contacto
</h5>
<div class="row">
    <div class="col-md-8">
        <div class="mb-3">
            <label for="address-field" class="form-label">Dirección</label>
            <textarea id="address-field" name="address" 
                class="form-control @error('address') is-invalid @enderror" 
                placeholder="Dirección exacta del recinto" rows="2">{{ old('address', $institution->address ?? '') }}</textarea>
            @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="mb-3">
            <label for="reference-field" class="form-label">Referencia</label>
            <input type="text" id="reference-field" name="reference" 
                class="form-control @error('reference') is-invalid @enderror" 
                placeholder="Ej: Frente a la plaza" 
                value="{{ old('reference', $institution->reference ?? '') }}" />
            @error('reference')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="mb-3">
            <label for="latitude-field" class="form-label">
                <span class="info-tooltip" title="Coordenada de latitud (ej: -17.123456)">Latitud</span>
            </label>
            <input type="text" id="latitude-field" name="latitude" 
                class="form-control @error('latitude') is-invalid @enderror" 
                placeholder="-17.123456" 
                value="{{ old('latitude', $institution->latitude ?? '') }}" />
            @error('latitude')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="mb-3">
            <label for="longitude-field" class="form-label">
                <span class="info-tooltip" title="Coordenada de longitud (ej: -65.123456)">Longitud</span>
            </label>
            <input type="text" id="longitude-field" name="longitude" 
                class="form-control @error('longitude') is-invalid @enderror" 
                placeholder="-65.123456" 
                value="{{ old('longitude', $institution->longitude ?? '') }}" />
            @error('longitude')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="mb-3">
            <label for="phone-field" class="form-label">Teléfono</label>
            <input type="text" id="phone-field" name="phone" 
                class="form-control @error('phone') is-invalid @enderror" 
                placeholder="Ej: 4-1234567" 
                value="{{ old('phone', $institution->phone ?? '') }}" />
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="email-field" class="form-label">Email</label>
            <input type="email" id="email-field" name="email" 
                class="form-control @error('email') is-invalid @enderror" 
                placeholder="recinto@ejemplo.com" 
                value="{{ old('email', $institution->email ?? '') }}" />
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="mb-3">
            <label for="responsible-field" class="form-label">Responsable</label>
            <input type="text" id="responsible-field" name="responsible_name" 
                class="form-control @error('responsible_name') is-invalid @enderror" 
                placeholder="Nombre del encargado" 
                value="{{ old('responsible_name', $institution->responsible_name ?? '') }}" />
            @error('responsible_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<!-- Datos Electorales -->
<h5 class="mb-3 text-primary mt-4">
    <i class="ri-vote-line me-1"></i>
    Datos Electorales
</h5>
<div class="row">
    <div class="col-md-3">
        <div class="mb-3">
            <label for="registered-citizens-field" class="form-label">
                <span class="info-tooltip" title="Ciudadanos habilitados para votar en este recinto">Ciudadanos Habilitados</span>
            </label>
            <input type="number" id="registered-citizens-field" name="registered_citizens" 
                class="form-control @error('registered_citizens') is-invalid @enderror" 
                placeholder="0" value="{{ old('registered_citizens', $institution->registered_citizens ?? 0) }}" min="0" />
            @error('registered_citizens')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="mb-3">
            <label for="total-computed-records-field" class="form-label">
                <span class="info-tooltip" title="Total de actas computadas">Actas Computadas</span>
            </label>
            <input type="number" id="total-computed-records-field" name="total_computed_records" 
                class="form-control @error('total_computed_records') is-invalid @enderror" 
                placeholder="0" value="{{ old('total_computed_records', $institution->total_computed_records ?? 0) }}" min="0" />
            @error('total_computed_records')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="mb-3">
            <label for="total-annulled-records-field" class="form-label">
                <span class="info-tooltip" title="Total de actas anuladas">Actas Anuladas</span>
            </label>
            <input type="number" id="total-annulled-records-field" name="total_annulled_records" 
                class="form-control @error('total_annulled_records') is-invalid @enderror" 
                placeholder="0" value="{{ old('total_annulled_records', $institution->total_annulled_records ?? 0) }}" min="0" />
            @error('total_annulled_records')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="mb-3">
            <label for="total-enabled-records-field" class="form-label">
                <span class="info-tooltip" title="Total de actas habilitadas">Actas Habilitadas</span>
            </label>
            <input type="number" id="total-enabled-records-field" name="total_enabled_records" 
                class="form-control @error('total_enabled_records') is-invalid @enderror" 
                placeholder="0" value="{{ old('total_enabled_records', $institution->total_enabled_records ?? 0) }}" min="0" />
            @error('total_enabled_records')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<!-- Estado -->
<h5 class="mb-3 text-primary mt-4">
    <i class="ri-settings-line me-1"></i>
    Estado del Recinto
</h5>
<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="status-field" class="form-label">Estado</label>
            <select class="form-control @error('status') is-invalid @enderror" name="status" id="status-field">
                <option value="activo" {{ old('status', $institution->status ?? 'activo') == 'activo' ? 'selected' : '' }}>Activo</option>
                <option value="inactivo" {{ old('status', $institution->status ?? '') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                <option value="en_mantenimiento" {{ old('status', $institution->status ?? '') == 'en_mantenimiento' ? 'selected' : '' }}>En Mantenimiento</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="mb-3">
            <div class="form-check form-switch mt-3">
                <input class="form-check-input" type="checkbox" role="switch" 
                       id="is-operative-field" name="is_operative" value="1" 
                       {{ old('is_operative', $institution->is_operative ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is-operative-field">
                    <strong>Operativo para Elecciones</strong>
                    <br>
                    <small class="text-muted">Indica si el recinto estará habilitado durante las elecciones</small>
                </label>
            </div>
        </div>
    </div>
</div>

<div class="mb-3">
    <label for="observations-field" class="form-label">Observaciones</label>
    <textarea id="observations-field" name="observations" 
        class="form-control @error('observations') is-invalid @enderror" 
        placeholder="Observaciones adicionales sobre el recinto" 
        rows="2">{{ old('observations', $institution->observations ?? '') }}</textarea>
    @error('observations')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>