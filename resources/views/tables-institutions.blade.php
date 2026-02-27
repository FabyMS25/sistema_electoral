@extends('layouts.master')
@section('title')
    @lang('translation.list-institutions')
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .required-field label:after {
            content: " *";
            color: red;
        }
        .info-tooltip {
            cursor: help;
            border-bottom: 1px dotted #ccc;
        }
    </style>
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Tables
        @endslot
        @slot('title')
            Recintos Electorales
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Administración de Recintos Electorales</h4>
                </div>
                
                <div class="card-body">
                    @include('components.alerts')

                    <div class="listjs-table" id="institutionList">
                        <div class="row g-4 mb-3">
                            <div class="col-sm-auto">
                                <div>
                                    <button type="button" class="btn btn-success add-btn" data-bs-toggle="modal"
                                        id="create-btn" data-bs-target="#showModal">
                                        <i class="ri-add-line align-bottom me-1"></i> Agregar Recinto
                                    </button>
                                    <div class="btn-group ms-2" role="group">
                                        <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ri-download-line align-bottom me-1"></i> Excel
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('institutions.export') }}">
                                                    <i class="ri-file-excel-line me-2"></i> Exportar Datos
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('institutions.template') }}">
                                                    <i class="ri-file-download-line me-2"></i> Descargar Plantilla
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#importModal">
                                                    <i class="ri-file-upload-line me-2"></i> Importar Datos
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <button class="btn btn-soft-danger" id="delete-multiple-btn" onclick="deleteMultiple()" style="display:none;">
                                        <i class="ri-delete-bin-2-line"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-sm">
                                <div class="d-flex justify-content-sm-end">
                                    <div class="search-box ms-2">
                                        <input type="text" class="form-control search" placeholder="Buscar recinto...">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive table-card mt-3 mb-1">
                            <table class="table align-middle table-nowrap" id="customerTable">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 50px;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="checkAll" value="option">
                                            </div>
                                        </th>
                                        <th class="sort" data-sort="institution_code">Código</th>
                                        <th class="sort" data-sort="institution_name">Recinto</th>
                                        <th class="sort" data-sort="location">Ubicación</th>
                                        <th class="sort" data-sort="citizens">Ciudadanos</th>
                                        <th class="sort" data-sort="mesas">Mesas</th>
                                        <th class="sort" data-sort="actas">Actas</th>
                                        <th class="sort" data-sort="status">Estado</th>
                                        <th class="sort" data-sort="action">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="list form-check-all">
                                    @foreach($institutions as $institution)
                                        <tr>
                                            <th scope="row">
                                                <div class="form-check">
                                                    <input class="form-check-input child-checkbox" type="checkbox" name="chk_child" value="{{ $institution->id }}">
                                                </div>
                                            </th>
                                            <td class="institution_code">
                                                <span class="badge bg-info-subtle text-info">{{ $institution->code }}</span>
                                            </td>
                                            <td class="institution_name">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h5 class="fs-14 mb-1">{{ $institution->name }}</h5>
                                                        <small class="text-muted">{{ $institution->short_name ?? '' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="location">
                                                <div>
                                                    <strong>{{ $institution->locality->municipality->name ?? 'N/A' }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        {{ $institution->locality->name ?? '' }}
                                                        @if($institution->district)
                                                            <br>Distrito: {{ $institution->district->name }}
                                                        @endif
                                                        @if($institution->zone)
                                                            <br>Zona: {{ $institution->zone->name }}
                                                        @endif
                                                    </small>
                                                </div>
                                            </td>
                                            <td class="citizens">
                                                <span class="fw-semibold">{{ number_format($institution->registered_citizens ?? 0) }}</span>
                                            </td>
                                            <td class="mesas">
                                                <span class="badge bg-primary">{{ $institution->voting_tables_count ?? 0 }}</span>
                                            </td>
                                            <td class="actas">
                                                <div class="d-flex flex-column gap-1">
                                                    <small class="text-primary">C: {{ $institution->total_computed_records ?? 0 }}</small>
                                                    <small class="text-danger">A: {{ $institution->total_annulled_records ?? 0 }}</small>
                                                    <small class="text-success">H: {{ $institution->total_enabled_records ?? 0 }}</small>
                                                </div>
                                            </td>
                                            <td class="status">
                                                @if($institution->status == 'activo')
                                                    <span class="badge bg-success-subtle text-success">Activo</span>
                                                @elseif($institution->status == 'inactivo')
                                                    <span class="badge bg-danger-subtle text-danger">Inactivo</span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning">Mantenimiento</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <div class="view">
                                                        <a href="{{ route('institutions.show', $institution->id) }}" class="btn btn-sm btn-info" title="Ver detalles">
                                                            <i class="ri-eye-line"></i>
                                                        </a>
                                                    </div>
                                                    <div class="edit">
                                                        <button class="btn btn-sm btn-success edit-item-btn"
                                                            data-bs-toggle="modal" data-bs-target="#showModal"
                                                            data-id="{{ $institution->id }}"
                                                            data-name="{{ $institution->name }}"
                                                            data-short-name="{{ $institution->short_name }}"
                                                            data-code="{{ $institution->code }}"
                                                            data-address="{{ $institution->address }}"
                                                            data-reference="{{ $institution->reference }}"
                                                            data-latitude="{{ $institution->latitude }}"
                                                            data-longitude="{{ $institution->longitude }}"
                                                            data-phone="{{ $institution->phone }}"
                                                            data-email="{{ $institution->email }}"
                                                            data-responsible="{{ $institution->responsible_name }}"
                                                            data-registered-citizens="{{ $institution->registered_citizens }}"
                                                            data-department-id="{{ $institution->locality->municipality->province->department->id ?? '' }}"
                                                            data-province-id="{{ $institution->locality->municipality->province->id ?? '' }}"
                                                            data-municipality-id="{{ $institution->locality->municipality->id ?? '' }}"
                                                            data-locality-id="{{ $institution->locality_id }}"
                                                            data-district-id="{{ $institution->district_id }}"
                                                            data-zone-id="{{ $institution->zone_id }}"
                                                            data-status="{{ $institution->status }}"
                                                            data-is-operative="{{ $institution->is_operative ? '1' : '0' }}"
                                                            data-observations="{{ $institution->observations }}"
                                                            data-update-url="{{ route('institutions.update', $institution->id) }}">
                                                            <i class="ri-pencil-line"></i>
                                                        </button>
                                                    </div>
                                                    <div class="remove">
                                                        <button class="btn btn-sm btn-danger remove-item-btn"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteRecordModal"
                                                            data-id="{{ $institution->id }}"
                                                            data-name="{{ $institution->name }}"
                                                            data-delete-url="{{ route('institutions.destroy', $institution->id) }}">
                                                            <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            
                            @if($institutions->isEmpty())
                                <div class="noresult">
                                    <div class="text-center">
                                        <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                            colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px">
                                        </lord-icon>
                                        <h5 class="mt-2">Lo sentimos! No se encontraron resultados</h5>
                                        <p class="text-muted mb-0">No hay recintos registrados en el sistema.</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-end">
                            <div class="pagination-wrap hstack gap-2">
                                {{ $institutions->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal fade" id="showModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-light p-3">
                    <h5 class="modal-title" id="exampleModalLabel">
                        <i class="ri-building-line me-1"></i>Agregar Recinto Electoral
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        id="close-modal"></button>
                </div>
                <form id="institutionForm" method="POST" action="{{ route('institutions.store') }}" class="tablelist-form" autocomplete="off">
                    @csrf
                    <input type="hidden" id="method_field" name="_method" value="">
                    <input type="hidden" id="institution_id" name="id">
                    
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="ri-information-line align-middle me-1"></i> 
                            Los campos marcados con <span class="text-danger">*</span> son obligatorios.
                        </div>

                        <!-- Datos Básicos -->
                        <h5 class="mb-3 text-primary">Datos Básicos del Recinto</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3 required-field">
                                    <label for="name-field" class="form-label">Nombre del Recinto</label>
                                    <input type="text" id="name-field" name="name" class="form-control @error('name') is-invalid @enderror" 
                                        placeholder="Ej: Unidad Educativa Simón Bolívar" value="{{ old('name') }}" required />
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="short-name-field" class="form-label">Nombre Corto</label>
                                    <input type="text" id="short-name-field" name="short_name" class="form-control @error('short_name') is-invalid @enderror" 
                                        placeholder="Ej: UE Simón Bolívar" value="{{ old('short_name') }}" />
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
                                        placeholder="Se genera automáticamente" value="{{ old('code') }}" />
                                    <small class="text-muted">Dejar vacío para generar automáticamente</small>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Ubicación Geográfica -->
                        <h5 class="mb-3 text-primary mt-3">Ubicación Geográfica</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3 required-field">
                                    <label for="department-field" class="form-label">Departamento</label>
                                    <select class="form-control @error('department_id') is-invalid @enderror" name="department_id" id="department-field"
                                        data-url="{{ url('institutions/provinces') }}" required>
                                        <option value="">Seleccione Departamento</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
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
                                    <select class="form-control @error('province_id') is-invalid @enderror" name="province_id" id="province-field"
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
                                    <select class="form-control @error('municipality_id') is-invalid @enderror" name="municipality_id" id="municipality-field"
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
                                    <select class="form-control @error('locality_id') is-invalid @enderror" name="locality_id" id="locality-field"
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
                                    <select class="form-control @error('district_id') is-invalid @enderror" name="district_id" id="district-field"
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
                                    <select class="form-control @error('zone_id') is-invalid @enderror" name="zone_id" id="zone-field" disabled>
                                        <option value="">Seleccione Zona (opcional)</option>
                                    </select>
                                    @error('zone_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Ubicación Física -->
                        <h5 class="mb-3 text-primary mt-3">Ubicación Física y Contacto</h5>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="address-field" class="form-label">Dirección</label>
                                    <textarea id="address-field" name="address" class="form-control @error('address') is-invalid @enderror" 
                                        placeholder="Dirección exacta del recinto" rows="2">{{ old('address') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="reference-field" class="form-label">Referencia</label>
                                    <input type="text" id="reference-field" name="reference" class="form-control @error('reference') is-invalid @enderror" 
                                        placeholder="Ej: Frente a la plaza" value="{{ old('reference') }}" />
                                    @error('reference')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="latitude-field" class="form-label">Latitud</label>
                                    <input type="text" id="latitude-field" name="latitude" class="form-control @error('latitude') is-invalid @enderror" 
                                        placeholder="-17.123456" value="{{ old('latitude') }}" />
                                    @error('latitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="longitude-field" class="form-label">Longitud</label>
                                    <input type="text" id="longitude-field" name="longitude" class="form-control @error('longitude') is-invalid @enderror" 
                                        placeholder="-65.123456" value="{{ old('longitude') }}" />
                                    @error('longitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="phone-field" class="form-label">Teléfono</label>
                                    <input type="text" id="phone-field" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                        placeholder="Ej: 4-1234567" value="{{ old('phone') }}" />
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
                                    <input type="email" id="email-field" name="email" class="form-control @error('email') is-invalid @enderror" 
                                        placeholder="recinto@ejemplo.com" value="{{ old('email') }}" />
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="responsible-field" class="form-label">Responsable</label>
                                    <input type="text" id="responsible-field" name="responsible_name" class="form-control @error('responsible_name') is-invalid @enderror" 
                                        placeholder="Nombre del encargado" value="{{ old('responsible_name') }}" />
                                    @error('responsible_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Datos Electorales -->
                        <h5 class="mb-3 text-primary mt-3">Datos Electorales</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="registered-citizens-field" class="form-label">
                                        <span class="info-tooltip" title="Ciudadanos habilitados para votar en este recinto">Ciudadanos Habilitados</span>
                                    </label>
                                    <input type="number" id="registered-citizens-field" name="registered_citizens" 
                                        class="form-control @error('registered_citizens') is-invalid @enderror" 
                                        placeholder="0" value="{{ old('registered_citizens') }}" min="0" />
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
                                        placeholder="0" value="{{ old('total_computed_records', 0) }}" min="0" />
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
                                        placeholder="0" value="{{ old('total_annulled_records', 0) }}" min="0" />
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
                                        placeholder="0" value="{{ old('total_enabled_records', 0) }}" min="0" />
                                    @error('total_enabled_records')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Estado y Observaciones -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status-field" class="form-label">Estado</label>
                                    <select class="form-control @error('status') is-invalid @enderror" name="status" id="status-field">
                                        <option value="activo">Activo</option>
                                        <option value="inactivo">Inactivo</option>
                                        <option value="en_mantenimiento">En Mantenimiento</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" role="switch" id="is-operative-field" name="is_operative" value="1" checked>
                                        <label class="form-check-label" for="is-operative-field">
                                            <strong>Operativo para Elecciones</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="observations-field" class="form-label">Observaciones</label>
                            <textarea id="observations-field" name="observations" class="form-control @error('observations') is-invalid @enderror" 
                                placeholder="Observaciones adicionales" rows="2">{{ old('observations') }}</textarea>
                            @error('observations')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <div class="hstack gap-2 justify-content-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                <i class="ri-close-line me-1"></i>Cancelar
                            </button>
                            <button type="submit" class="btn btn-success" id="save-btn">
                                <i class="ri-save-line me-1"></i>Guardar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade zoomIn" id="deleteRecordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mt-2 text-center">
                        <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop"
                            colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon>
                        <div class="mt-4 pt-2 fs-15 mx-4 mx-sm-5">
                            <h4>¿Está seguro?</h4>
                            <p class="text-muted mx-4 mb-0">¿Está seguro de que desea eliminar el recinto <strong id="delete-institution-name"></strong>?</p>
                            <p class="text-danger mt-2 mb-0"><small>Esta acción no se puede deshacer y solo es posible si no tiene mesas asociadas.</small></p>
                        </div>
                    </div>
                    <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                        <button type="button" class="btn w-sm btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <form id="deleteForm" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn w-sm btn-danger">
                                <i class="ri-delete-bin-line me-1"></i>Sí, eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('institutions.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">
                            <i class="ri-file-upload-line me-1"></i>Importar Recintos
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="import-file" class="form-label">Seleccionar archivo Excel</label>
                            <input class="form-control" type="file" id="import-file" name="file" accept=".xlsx,.xls,.csv" required>
                            <div class="form-text">Archivos permitidos: .xlsx, .xls, .csv (máx. 2MB)</div>
                        </div>
                        <div class="alert alert-info">
                            <i class="ri-information-line me-1"></i>
                            <strong>Importante:</strong> Asegúrese de que el archivo cumpla con la estructura de la plantilla.
                            <br>
                            <a href="{{ route('institutions.template') }}" class="alert-link">
                                <i class="ri-download-line me-1"></i>Descargar Plantilla
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-upload-line me-1"></i>Importar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Import Errors Modal -->
    @if(session('import_errors'))
    <div class="modal fade" id="importErrorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-warning">
                        <i class="ri-alert-line me-1"></i>Errores de Importación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Se encontraron errores durante la importación:</strong>
                        <br>Los siguientes registros no pudieron ser procesados correctamente.
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach(session('import_errors') as $error)
                        <div class="list-group-item">
                            <i class="ri-error-warning-line text-danger me-2"></i>{{ $error }}
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <a href="{{ route('institutions.template') }}" class="btn btn-info">
                        <i class="ri-download-line me-1"></i>Descargar Plantilla
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/prismjs/prism.js') }}"></script>
    <script src="{{ URL::asset('build/libs/list.js/list.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/list.pagination.js/list.pagination.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Inicializar Choices.js para selects
            if (typeof Choices !== 'undefined') {
                new Choices('#department-field', { searchEnabled: true, shouldSort: false });
                new Choices('#province-field', { searchEnabled: true, shouldSort: false });
                new Choices('#municipality-field', { searchEnabled: true, shouldSort: false });
                new Choices('#locality-field', { searchEnabled: true, shouldSort: false });
                new Choices('#district-field', { searchEnabled: true, shouldSort: false });
                new Choices('#zone-field', { searchEnabled: true, shouldSort: false });
                new Choices('#status-field', { searchEnabled: false, shouldSort: false });
            }
            
            if (document.querySelectorAll('#customerTable tbody tr').length > 0) {
                var options = {
                    valueNames: [
                        'institution_code',
                        'institution_name', 
                        { name: 'location', attr: 'data-location' },
                        { name: 'citizens', attr: 'data-citizens' },
                        { name: 'mesas', attr: 'data-mesas' },
                        { name: 'status', attr: 'data-status' }
                    ],
                    page: 10,
                    pagination: true
                };
                try {
                    var institutionList = new List('institutionList', options);
                } catch (e) {
                    console.warn('List.js no pudo inicializarse:', e);
                }
            }

            const checkAll = document.getElementById('checkAll');
            const childCheckboxes = document.querySelectorAll('.child-checkbox');
            const deleteMultipleBtn = document.getElementById('delete-multiple-btn');
            
            checkAll.addEventListener('change', function() {
                childCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                toggleDeleteButton();
            });
            
            childCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const checkedBoxes = document.querySelectorAll('.child-checkbox:checked');
                    checkAll.checked = checkedBoxes.length === childCheckboxes.length;
                    checkAll.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < childCheckboxes.length;
                    toggleDeleteButton();
                });
            });

            function toggleDeleteButton() {
                const checkedBoxes = document.querySelectorAll('.child-checkbox:checked');
                deleteMultipleBtn.style.display = checkedBoxes.length > 0 ? 'inline-block' : 'none';
            }

            // Carga dinámica de selects dependientes
            const departmentSelect = document.getElementById('department-field');
            const provinceSelect = document.getElementById('province-field');
            const municipalitySelect = document.getElementById('municipality-field');
            const localitySelect = document.getElementById('locality-field');
            const districtSelect = document.getElementById('district-field');
            const zoneSelect = document.getElementById('zone-field');

            departmentSelect.addEventListener('change', function () {
                const departmentId = this.value;
                resetDependentSelects([provinceSelect, municipalitySelect, localitySelect, districtSelect, zoneSelect]);                
                if (departmentId) {
                    const url = `${this.dataset.url}/${departmentId}`;
                    loadOptions(url, provinceSelect, 'Seleccione Provincia');
                }
            });

            provinceSelect.addEventListener('change', function () {
                const provinceId = this.value;
                resetDependentSelects([municipalitySelect, localitySelect, districtSelect, zoneSelect]);                
                if (provinceId) {
                    const url = `${this.dataset.url}/${provinceId}`;
                    loadOptions(url, municipalitySelect, 'Seleccione Municipio');
                }
            });

            municipalitySelect.addEventListener('change', function () {
                const municipalityId = this.value;
                resetDependentSelects([localitySelect, districtSelect, zoneSelect]);                
                if (municipalityId) {
                    const url = `${this.dataset.url}/${municipalityId}`;
                    loadOptions(url, localitySelect, 'Seleccione Localidad');
                }
            });

            localitySelect.addEventListener('change', function () {
                const localityId = this.value;
                resetDependentSelects([districtSelect, zoneSelect]);                
                if (localityId) {
                    const url = `${this.dataset.url}/${localityId}`;
                    loadOptions(url, districtSelect, 'Seleccione Distrito');
                }
            });

            districtSelect.addEventListener('change', function () {
                const districtId = this.value;
                resetDependentSelects([zoneSelect]);                
                if (districtId) {
                    const url = `${this.dataset.url}/${districtId}`;
                    loadOptions(url, zoneSelect, 'Seleccione Zona');
                }
            });

            function resetDependentSelects(selects) {
                selects.forEach(select => {
                    if (select) {
                        const firstOption = select.querySelector('option');
                        const originalPlaceholder = firstOption ? firstOption.textContent : 'Seleccionar';
                        select.innerHTML = `<option value="">${originalPlaceholder}</option>`;
                        select.disabled = true;
                        
                        // Actualizar Choices.js si está inicializado
                        if (select._choices) {
                            select._choices.destroy();
                        }
                    }
                });
            }

            async function loadOptions(url, target, placeholder) {
                target.innerHTML = `<option value="">Cargando...</option>`;
                target.disabled = true;                
                try {
                    const response = await fetch(url);                    
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    const data = await response.json();                    
                    if (data.error) {
                        throw new Error(data.error);
                    }                    
                    target.innerHTML = `<option value="">${placeholder}</option>`;                    
                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.id;
                            option.textContent = item.name;
                            target.appendChild(option);
                        });
                    } else {
                        const noDataOption = document.createElement('option');
                        noDataOption.value = "";
                        noDataOption.textContent = "No hay datos disponibles";
                        noDataOption.disabled = true;
                        target.appendChild(noDataOption);
                    }
                    
                    // Reinicializar Choices.js
                    if (typeof Choices !== 'undefined') {
                        target._choices = new Choices(target, { searchEnabled: true, shouldSort: false });
                    }
                    
                } catch (error) {
                    console.error('Error loading data from', url, ':', error);                    
                    target.innerHTML = `<option value="">${placeholder}</option>`;
                    const errorOption = document.createElement('option');
                    errorOption.value = "";
                    errorOption.textContent = "Error al cargar datos";
                    errorOption.disabled = true;
                    target.appendChild(errorOption);                    
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Error',
                            text: 'No se pudieron cargar los datos. Por favor, recarga la página.',
                            icon: 'error',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                } finally {
                    target.disabled = false;
                }
            }

            // Modal de creación
            document.getElementById('create-btn').addEventListener('click', function() {
                document.getElementById('exampleModalLabel').innerHTML = '<i class="ri-building-line me-1"></i>Agregar Recinto Electoral';
                document.getElementById('institutionForm').action = "{{ route('institutions.store') }}";
                document.getElementById('method_field').value = '';            
                document.getElementById('institutionForm').reset();
                document.getElementById('is-operative-field').checked = true;
                document.getElementById('institution_id').value = '';
                document.getElementById('save-btn').innerHTML = '<i class="ri-save-line me-1"></i>Guardar';
                clearValidationErrors();
                resetDependentSelects([provinceSelect, municipalitySelect, localitySelect, districtSelect, zoneSelect]);
                
                // Resetear Choices.js
                if (typeof Choices !== 'undefined') {
                    if (departmentSelect._choices) departmentSelect._choices.destroy();
                    departmentSelect._choices = new Choices(departmentSelect, { searchEnabled: true, shouldSort: false });
                }
            });

            // Modal de edición
            document.querySelectorAll(".edit-item-btn").forEach(btn => {
                btn.addEventListener("click", async function () {
                    document.getElementById('exampleModalLabel').innerHTML = '<i class="ri-edit-line me-1"></i>Editar Recinto Electoral';
                    document.getElementById('institutionForm').action = this.dataset.updateUrl;
                    document.getElementById('method_field').value = 'PUT';
                    document.getElementById('institution_id').value = this.dataset.id;
                    document.getElementById('save-btn').innerHTML = '<i class="ri-refresh-line me-1"></i>Actualizar';
                    
                    // Datos básicos
                    document.querySelector("#name-field").value = this.dataset.name || '';
                    document.querySelector("#short-name-field").value = this.dataset.shortName || '';
                    document.querySelector("#code-field").value = this.dataset.code || '';
                    
                    // Ubicación física
                    document.querySelector("#address-field").value = this.dataset.address || '';
                    document.querySelector("#reference-field").value = this.dataset.reference || '';
                    document.querySelector("#latitude-field").value = this.dataset.latitude || '';
                    document.querySelector("#longitude-field").value = this.dataset.longitude || '';
                    
                    // Contacto
                    document.querySelector("#phone-field").value = this.dataset.phone || '';
                    document.querySelector("#email-field").value = this.dataset.email || '';
                    document.querySelector("#responsible-field").value = this.dataset.responsible || '';
                    
                    // Datos electorales
                    document.querySelector("#registered-citizens-field").value = this.dataset.registeredCitizens || '';
                    document.querySelector("#total-computed-records-field").value = this.dataset.totalComputedRecords || 0;
                    document.querySelector("#total-annulled-records-field").value = this.dataset.totalAnnulledRecords || 0;
                    document.querySelector("#total-enabled-records-field").value = this.dataset.totalEnabledRecords || 0;
                    
                    // Estado
                    document.querySelector("#status-field").value = this.dataset.status || 'activo';
                    document.querySelector("#is-operative-field").checked = this.dataset.isOperative === '1';
                    document.querySelector("#observations-field").value = this.dataset.observations || '';
                    
                    // Ubicación geográfica
                    const deptSelect = document.querySelector("#department-field");
                    if (this.dataset.departmentId) {
                        deptSelect.value = this.dataset.departmentId;
                        if (deptSelect._choices) deptSelect._choices.destroy();
                        deptSelect._choices = new Choices(deptSelect, { searchEnabled: true, shouldSort: false });
                        deptSelect.dispatchEvent(new Event("change"));
                        
                        setTimeout(async () => {
                            if (this.dataset.provinceId) {
                                await selectAndTrigger("#province-field", this.dataset.provinceId);
                                await selectAndTrigger("#municipality-field", this.dataset.municipalityId);
                                await selectAndTrigger("#locality-field", this.dataset.localityId);
                                if (this.dataset.districtId) {
                                    await selectAndTrigger("#district-field", this.dataset.districtId);
                                }
                                if (this.dataset.zoneId) {
                                    const zoneSelect = document.querySelector("#zone-field");
                                    zoneSelect.value = this.dataset.zoneId;
                                    if (zoneSelect._choices) {
                                        zoneSelect._choices.destroy();
                                        zoneSelect._choices = new Choices(zoneSelect, { searchEnabled: true, shouldSort: false });
                                    }
                                }
                            }
                        }, 500);
                    }
                    
                    clearValidationErrors();
                });
            });

            // Eliminación individual
            document.querySelectorAll('.remove-item-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const deleteUrl = this.getAttribute('data-delete-url');
                    const name = this.getAttribute('data-name');
                    document.getElementById('deleteForm').action = deleteUrl;
                    document.getElementById('delete-institution-name').textContent = name;
                });
            });

            // Validación del formulario
            const form = document.getElementById('institutionForm');
            form.addEventListener('submit', function(event) {
                let isValid = true;
                const requiredFields = ['name-field', 'department-field', 'province-field', 'municipality-field', 'locality-field'];
                
                requiredFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field && !field.value) {
                        field.classList.add('is-invalid');
                        isValid = false;
                        
                        // Para selects con Choices.js
                        if (field._choices) {
                            field.closest('.choices').classList.add('is-invalid');
                        }
                    } else if (field) {
                        field.classList.remove('is-invalid');
                        if (field._choices) {
                            field.closest('.choices').classList.remove('is-invalid');
                        }
                    }
                });

                if (!isValid) {
                    event.preventDefault();
                    event.stopPropagation();
                    Swal.fire({
                        title: 'Campos requeridos',
                        text: 'Por favor complete todos los campos obligatorios.',
                        icon: 'warning',
                        confirmButtonText: 'Entendido'
                    });
                }
            });

            // Limpiar validaciones al cerrar modal
            document.getElementById('showModal').addEventListener('hidden.bs.modal', function () {
                clearValidationErrors();
            });

            async function selectAndTrigger(selector, value) {
                const select = document.querySelector(selector);
                if (value && select) {
                    select.value = value;
                    if (select._choices) {
                        select._choices.destroy();
                    }
                    select.dispatchEvent(new Event("change"));
                    
                    // Pequeña espera para que se carguen los datos
                    await new Promise(resolve => setTimeout(resolve, 500));
                    
                    if (select._choices) {
                        select._choices = new Choices(select, { searchEnabled: true, shouldSort: false });
                    }
                }
            }

            function clearValidationErrors() {
                document.querySelectorAll('.is-invalid').forEach(field => {
                    field.classList.remove('is-invalid');
                });
                document.querySelectorAll('.choices.is-invalid').forEach(field => {
                    field.classList.remove('is-invalid');
                });
            }

            // Auto-cerrar alertas
            setTimeout(function() {
                document.querySelectorAll('.alert-dismissible').forEach(function(alert) {
                    if (alert) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                });
            }, 5000);
        });

        // Mostrar modal de errores de importación
        @if(session('import_errors'))
            document.addEventListener('DOMContentLoaded', function() {
                const importErrorModal = new bootstrap.Modal(document.getElementById('importErrorModal'));
                importErrorModal.show();
            });
        @endif

        // Eliminación múltiple
        function deleteMultiple() {
            const checkedBoxes = document.querySelectorAll('.child-checkbox:checked');
            const ids = Array.from(checkedBoxes).map(cb => cb.value);            
            
            if (ids.length === 0) {
                Swal.fire({
                    title: 'Sin selección',
                    text: 'Por favor seleccione al menos un registro para eliminar.',
                    icon: 'info',
                    confirmButtonText: 'Entendido'
                });
                return;
            }            
            
            Swal.fire({
                title: '¿Está seguro?',
                text: `¿Desea eliminar ${ids.length} recintos seleccionados?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch('{{ route("institutions.deleteMultiple") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ ids: ids })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => { throw new Error(err.message || 'Error en la solicitud'); });
                        }
                        return response.json();
                    })
                    .catch(error => {
                        Swal.showValidationMessage(
                            `Error: ${error}`
                        );
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    if (result.value && result.value.success) {
                        Swal.fire({
                            title: '¡Eliminado!',
                            text: result.value.message || `Se eliminaron ${ids.length} registros correctamente.`,
                            icon: 'success',
                            confirmButtonText: 'Entendido'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: result.value?.message || 'Ocurrió un error al eliminar los registros.',
                            icon: 'error',
                            confirmButtonText: 'Entendido'
                        });
                    }
                }
            });
        }
    </script>
@endsection