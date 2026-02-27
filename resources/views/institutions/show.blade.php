@extends('layouts.master')

@section('title')
    {{ $institution->name }}
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/leaflet/leaflet.css') }}" rel="stylesheet" />
    <style>
        #map {
            height: 300px;
            border-radius: 0.25rem;
        }
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #0ab39c;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.25rem;
        }
        .info-label {
            font-weight: 600;
            color: #495057;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-value {
            font-size: 1.1rem;
            color: #212529;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('institutions.index') }}">Recintos</a>
        @endslot
        @slot('title')
            {{ $institution->name }}
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="ri-building-line me-1"></i>
                        Detalles del Recinto
                    </h4>
                    <div>
                        @can('edit_recintos')
                        <button class="btn btn-warning btn-sm" onclick="window.location.href='{{ route('institutions.edit', $institution->id) }}'">
                            <i class="ri-pencil-line me-1"></i>Editar
                        </button>
                        @endcan
                        <a href="{{ route('institutions.index') }}" class="btn btn-secondary btn-sm">
                            <i class="ri-arrow-left-line me-1"></i>Volver
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Información Básica -->
                        <div class="col-md-6">
                            <div class="info-box">
                                <h5 class="mb-3">Información Básica</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="info-label">Código:</td>
                                        <td class="info-value">
                                            <span class="badge bg-info-subtle text-info fs-6">{{ $institution->code }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Nombre:</td>
                                        <td class="info-value">{{ $institution->name }}</td>
                                    </tr>
                                    @if($institution->short_name)
                                    <tr>
                                        <td class="info-label">Nombre Corto:</td>
                                        <td class="info-value">{{ $institution->short_name }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td class="info-label">Dirección:</td>
                                        <td class="info-value">{{ $institution->address ?? 'No especificada' }}</td>
                                    </tr>
                                    @if($institution->reference)
                                    <tr>
                                        <td class="info-label">Referencia:</td>
                                        <td class="info-value">{{ $institution->reference }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td class="info-label">Estado:</td>
                                        <td class="info-value">
                                            @if($institution->status == 'activo')
                                                <span class="badge bg-success">Activo</span>
                                            @elseif($institution->status == 'inactivo')
                                                <span class="badge bg-danger">Inactivo</span>
                                            @else
                                                <span class="badge bg-warning">Mantenimiento</span>
                                            @endif
                                            
                                            @if($institution->is_operative)
                                                <span class="badge bg-info ms-1">Operativo</span>
                                            @else
                                                <span class="badge bg-secondary ms-1">No Operativo</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div class="info-box">
                                <h5 class="mb-3">Contacto</h5>
                                <table class="table table-borderless">
                                    @if($institution->phone)
                                    <tr>
                                        <td class="info-label">Teléfono:</td>
                                        <td class="info-value">{{ $institution->phone }}</td>
                                    </tr>
                                    @endif
                                    @if($institution->email)
                                    <tr>
                                        <td class="info-label">Email:</td>
                                        <td class="info-value">{{ $institution->email }}</td>
                                    </tr>
                                    @endif
                                    @if($institution->responsible_name)
                                    <tr>
                                        <td class="info-label">Responsable:</td>
                                        <td class="info-value">{{ $institution->responsible_name }}</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                        </div>

                        <!-- Ubicación Geográfica -->
                        <div class="col-md-6">
                            <div class="info-box">
                                <h5 class="mb-3">Ubicación Geográfica</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="info-label">Departamento:</td>
                                        <td class="info-value">{{ $institution->locality->municipality->province->department->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Provincia:</td>
                                        <td class="info-value">{{ $institution->locality->municipality->province->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Municipio:</td>
                                        <td class="info-value">{{ $institution->locality->municipality->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Localidad:</td>
                                        <td class="info-value">{{ $institution->locality->name }}</td>
                                    </tr>
                                    @if($institution->district)
                                    <tr>
                                        <td class="info-label">Distrito:</td>
                                        <td class="info-value">{{ $institution->district->name }}</td>
                                    </tr>
                                    @endif
                                    @if($institution->zone)
                                    <tr>
                                        <td class="info-label">Zona:</td>
                                        <td class="info-value">{{ $institution->zone->name }}</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>

                            @if($institution->latitude && $institution->longitude)
                            <div class="info-box">
                                <h5 class="mb-3">Ubicación en Mapa</h5>
                                <div id="map"></div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Datos Electorales -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="info-box">
                                <h5 class="mb-3">Datos Electorales</h5>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="card bg-primary-subtle">
                                            <div class="card-body text-center">
                                                <h6>Ciudadanos Habilitados</h6>
                                                <h3>{{ number_format($institution->registered_citizens ?? 0) }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-success-subtle">
                                            <div class="card-body text-center">
                                                <h6>Actas Computadas</h6>
                                                <h3>{{ number_format($institution->total_computed_records ?? 0) }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-danger-subtle">
                                            <div class="card-body text-center">
                                                <h6>Actas Anuladas</h6>
                                                <h3>{{ number_format($institution->total_annulled_records ?? 0) }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="card bg-warning-subtle">
                                            <div class="card-body text-center">
                                                <h6>Actas Habilitadas</h6>
                                                <h3>{{ number_format($institution->total_enabled_records ?? 0) }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mesas de Votación -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="info-box">
                                <h5 class="mb-3">Mesas de Votación ({{ $institution->votingTables->count() }})</h5>
                                @if($institution->votingTables->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>N° Mesa</th>
                                                    <th>Código</th>
                                                    <th>Ciudadanos</th>
                                                    <th>Estado</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($institution->votingTables as $table)
                                                <tr>
                                                    <td>{{ $table->number }}</td>
                                                    <td>{{ $table->code }}</td>
                                                    <td>{{ $table->registered_citizens ?? 'N/A' }}</td>
                                                    <td>
                                                        @if($table->status == 'activo')
                                                            <span class="badge bg-success">Activo</span>
                                                        @elseif($table->status == 'cerrado')
                                                            <span class="badge bg-secondary">Cerrado</span>
                                                        @else
                                                            <span class="badge bg-warning">Pendiente</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('voting-tables.show', $table->id) }}" class="btn btn-sm btn-info">
                                                            <i class="ri-eye-line"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted">No hay mesas de votación registradas en este recinto.</p>
                                    @can('create_mesas')
                                    <a href="{{ route('voting-tables.create', ['institution_id' => $institution->id]) }}" class="btn btn-sm btn-primary">
                                        <i class="ri-add-line me-1"></i>Agregar Mesa
                                    </a>
                                    @endcan
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($institution->observations)
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="info-box">
                                <h5 class="mb-3">Observaciones</h5>
                                <p>{{ $institution->observations }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @if($institution->latitude && $institution->longitude)
    <script src="{{ URL::asset('build/libs/leaflet/leaflet.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var map = L.map('map').setView([{{ $institution->latitude }}, {{ $institution->longitude }}], 15);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            
            L.marker([{{ $institution->latitude }}, {{ $institution->longitude }}])
                .addTo(map)
                .bindPopup('<b>{{ $institution->name }}</b><br>{{ $institution->address }}');
        });
    </script>
    @endif
@endsection