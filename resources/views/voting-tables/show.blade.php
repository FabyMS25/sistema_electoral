{{-- resources/views/voting-tables/show.blade.php --}}
@extends('layouts.master')

@section('title')
    Mesa {{ $votingTable->code }}
@endsection

@section('css')
    <link href="{{ URL::asset('build/libs/apexcharts/apexcharts.min.css') }}" rel="stylesheet" />
    <style>
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
        .status-badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }
        .progress {
            height: 10px;
            border-radius: 5px;
        }
        .delegate-card {
            transition: transform 0.2s;
            border: 1px solid #e9e9ef;
            border-radius: 0.5rem;
            padding: 1rem;
            text-align: center;
        }
        .delegate-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .delegate-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('voting-tables.index') }}">Mesas</a>
        @endslot
        @slot('title')
            Mesa {{ $votingTable->code }}
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="ri-table-line me-1"></i>
                        Detalles de la Mesa de Votación
                    </h4>
                    <div>
                        @can('edit_mesas')
                        <a href="{{ route('voting-tables.edit', $votingTable->id) }}" class="btn btn-warning btn-sm">
                            <i class="ri-pencil-line me-1"></i>Editar
                        </a>
                        @endcan
                        <a href="{{ route('voting-tables.index') }}" class="btn btn-secondary btn-sm">
                            <i class="ri-arrow-left-line me-1"></i>Volver
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Estado y Progreso -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="info-box">
                                <h5 class="mb-3">Estado de la Mesa</h5>
                                @php
                                    $statusColors = [
                                        'pendiente' => 'warning',
                                        'en_proceso' => 'info',
                                        'cerrado' => 'secondary',
                                        'en_computo' => 'primary',
                                        'computado' => 'success',
                                        'observado' => 'danger',
                                        'anulado' => 'dark'
                                    ];
                                    $statusLabels = [
                                        'pendiente' => 'Pendiente',
                                        'en_proceso' => 'En Proceso',
                                        'cerrado' => 'Cerrado',
                                        'en_computo' => 'En Cómputo',
                                        'computado' => 'Computado',
                                        'observado' => 'Observado',
                                        'anulado' => 'Anulado'
                                    ];
                                @endphp
                                <div class="text-center">
                                    <span class="badge bg-{{ $statusColors[$votingTable->status] }} status-badge">
                                        {{ $statusLabels[$votingTable->status] ?? $votingTable->status }}
                                    </span>
                                </div>
                                @if($votingTable->status == 'observado')
                                    <div class="alert alert-danger mt-3 mb-0">
                                        <i class="ri-alert-line me-1"></i>
                                        Esta mesa tiene observaciones pendientes
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="info-box">
                                <h5 class="mb-3">Progreso de Votación</h5>
                                @php
                                    $progress = $votingTable->registered_citizens > 0 
                                        ? round(($votingTable->voted_citizens / $votingTable->registered_citizens) * 100, 1) 
                                        : 0;
                                @endphp
                                <h2 class="text-center mb-3">{{ $progress }}%</h2>
                                <div class="progress mb-3">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: {{ $progress }}%;" 
                                         aria-valuenow="{{ $progress }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100"></div>
                                </div>
                                <div class="row text-center">
                                    <div class="col-6">
                                        <strong>{{ number_format($votingTable->voted_citizens) }}</strong>
                                        <small class="d-block text-muted">Votaron</small>
                                    </div>
                                    <div class="col-6">
                                        <strong>{{ number_format($votingTable->registered_citizens - $votingTable->voted_citizens) }}</strong>
                                        <small class="d-block text-muted">Faltan</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="info-box">
                                <h5 class="mb-3">Cómputo de Votos</h5>
                                <div class="row text-center">
                                    <div class="col-6">
                                        <h4 class="text-primary">{{ number_format($votingTable->computed_records) }}</h4>
                                        <small class="text-muted">Computados</small>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="text-danger">{{ number_format($votingTable->annulled_records) }}</h4>
                                        <small class="text-muted">Anulados</small>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <h4 class="text-success">{{ number_format($votingTable->enabled_records) }}</h4>
                                        <small class="text-muted">Habilitados</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Información Básica -->
                        <div class="col-md-6">
                            <div class="info-box">
                                <h5 class="mb-3">
                                    <i class="ri-information-line me-1"></i>
                                    Información Básica
                                </h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="info-label">Código:</td>
                                        <td class="info-value">
                                            <span class="badge bg-info-subtle text-info fs-6">{{ $votingTable->code }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Código INE/OEP:</td>
                                        <td class="info-value">{{ $votingTable->code_ine ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Número de Mesa:</td>
                                        <td class="info-value">
                                            <strong>{{ $votingTable->number }}</strong>
                                            @if($votingTable->letter)
                                                <span class="badge bg-secondary ms-1">Letra {{ $votingTable->letter }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Tipo:</td>
                                        <td class="info-value">
                                            @php
                                                $typeLabels = [
                                                    'mixta' => 'Mixta',
                                                    'masculina' => 'Masculina',
                                                    'femenina' => 'Femenina'
                                                ];
                                            @endphp
                                            {{ $typeLabels[$votingTable->type] ?? $votingTable->type }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Recinto:</td>
                                        <td class="info-value">
                                            <strong>{{ $votingTable->institution->name ?? 'N/A' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $votingTable->institution->code ?? '' }}</small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Tipo de Elección:</td>
                                        <td class="info-value">{{ $votingTable->electionType->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Fecha Elección:</td>
                                        <td class="info-value">
                                            {{ $votingTable->election_date ? \Carbon\Carbon::parse($votingTable->election_date)->format('d/m/Y') : 'No definida' }}
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Horarios -->
                            <div class="info-box mt-3">
                                <h5 class="mb-3">
                                    <i class="ri-time-line me-1"></i>
                                    Horarios
                                </h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="info-label">Hora de Apertura:</td>
                                        <td class="info-value">{{ $votingTable->opening_time ? \Carbon\Carbon::parse($votingTable->opening_time)->format('H:i') : 'No registrada' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Hora de Cierre:</td>
                                        <td class="info-value">{{ $votingTable->closing_time ? \Carbon\Carbon::parse($votingTable->closing_time)->format('H:i') : 'No registrada' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Rango de Votantes -->
                        <div class="col-md-6">
                            <div class="info-box">
                                <h5 class="mb-3">
                                    <i class="ri-group-line me-1"></i>
                                    Rango de Votantes
                                </h5>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="card bg-primary-subtle">
                                            <div class="card-body text-center">
                                                <h6 class="text-muted">Desde</h6>
                                                <h5>{{ $votingTable->from_name ?? 'N/A' }}</h5>
                                                <small>C.I. {{ $votingTable->from_number ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card bg-info-subtle">
                                            <div class="card-body text-center">
                                                <h6 class="text-muted">Hasta</h6>
                                                <h5>{{ $votingTable->to_name ?? 'N/A' }}</h5>
                                                <small>C.I. {{ $votingTable->to_number ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center mt-3">
                                    <strong>Total Habilitados: {{ number_format($votingTable->registered_citizens) }}</strong>
                                </div>
                            </div>

                            <!-- Acta Electoral -->
                            <div class="info-box mt-3">
                                <h5 class="mb-3">
                                    <i class="ri-file-copy-line me-1"></i>
                                    Acta Electoral
                                </h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="info-label">Número de Acta:</td>
                                        <td class="info-value">
                                            @if($votingTable->acta_number)
                                                <span class="badge bg-success">{{ $votingTable->acta_number }}</span>
                                            @else
                                                <span class="text-muted">No registrada</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Fecha de Subida:</td>
                                        <td class="info-value">
                                            @if($votingTable->acta_uploaded_at)
                                                {{ \Carbon\Carbon::parse($votingTable->acta_uploaded_at)->format('d/m/Y H:i') }}
                                            @else
                                                <span class="text-muted">No subida</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                @if($votingTable->acta_photo || $votingTable->acta_pdf)
                                <div class="mt-2">
                                    @if($votingTable->acta_photo)
                                    <a href="{{ Storage::url($votingTable->acta_photo) }}" target="_blank" class="btn btn-sm btn-info me-1">
                                        <i class="ri-image-line me-1"></i>Ver Foto
                                    </a>
                                    @endif
                                    @if($votingTable->acta_pdf)
                                    <a href="{{ Storage::url($votingTable->acta_pdf) }}" target="_blank" class="btn btn-sm btn-danger">
                                        <i class="ri-file-pdf-line me-1"></i>Ver PDF
                                    </a>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Delegados de Mesa -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="info-box">
                                <h5 class="mb-3">
                                    <i class="ri-user-star-line me-1"></i>
                                    Delegados de Mesa
                                </h5>
                                <div class="row">
                                    @php
                                        $delegates = [
                                            'president_id' => ['label' => 'Presidente', 'color' => 'primary'],
                                            'secretary_id' => ['label' => 'Secretario', 'color' => 'success'],
                                            'vocal1_id' => ['label' => 'Vocal 1', 'color' => 'info'],
                                            'vocal2_id' => ['label' => 'Vocal 2', 'color' => 'warning'],
                                            'vocal3_id' => ['label' => 'Vocal 3', 'color' => 'secondary'],
                                            'vocal4_id' => ['label' => 'Vocal 4 (Suplente)', 'color' => 'dark'],
                                        ];
                                    @endphp

                                    @foreach($delegates as $field => $info)
                                        @php
                                            $delegate = $votingTable->$field ? App\Models\User::find($votingTable->$field) : null;
                                        @endphp
                                        <div class="col-md-4 col-lg-2 mb-3">
                                            <div class="delegate-card">
                                                @if($delegate)
                                                    <div class="delegate-avatar bg-{{ $info['color'] }} bg-soft">
                                                        {{ strtoupper(substr($delegate->name, 0, 1)) }}{{ strtoupper(substr($delegate->last_name ?? '', 0, 1)) }}
                                                    </div>
                                                    <h6 class="mb-1">{{ $delegate->name }} {{ $delegate->last_name }}</h6>
                                                    <small class="text-muted d-block">{{ $info['label'] }}</small>
                                                    <small class="text-muted">{{ $delegate->email }}</small>
                                                @else
                                                    <div class="delegate-avatar bg-light text-muted">
                                                        <i class="ri-user-line fs-2"></i>
                                                    </div>
                                                    <h6 class="mb-1 text-muted">No asignado</h6>
                                                    <small class="text-muted d-block">{{ $info['label'] }}</small>
                                                    <small class="text-muted">Disponible</small>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @can('assign_table_delegates')
                                <div class="text-end mt-3">
                                    <a href="{{ route('users.assign-table.form', ['user' => 0]) }}" class="btn btn-sm btn-primary">
                                        <i class="ri-user-add-line me-1"></i>Asignar Delegados
                                    </a>
                                </div>
                                @endcan
                            </div>
                        </div>
                    </div>

                    <!-- Resultados Detallados -->
                    @if($votingTable->votes->count() > 0)
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="info-box">
                                <h5 class="mb-3">
                                    <i class="ri-bar-chart-2-line me-1"></i>
                                    Resultados por Candidato
                                </h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Candidato</th>
                                                <th>Partido</th>
                                                <th class="text-center">Votos</th>
                                                <th class="text-center">Porcentaje</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($votingTable->votes as $vote)
                                            <tr>
                                                <td>{{ $vote->candidate->name ?? 'N/A' }}</td>
                                                <td>{{ $vote->candidate->party ?? 'N/A' }}</td>
                                                <td class="text-center">{{ number_format($vote->quantity) }}</td>
                                                <td class="text-center">{{ $vote->percentage ?? 0 }}%</td>
                                            </tr>
                                            @endforeach
                                            <tr class="table-info">
                                                <td colspan="2"><strong>Votos Válidos</strong></td>
                                                <td class="text-center"><strong>{{ number_format($votingTable->computed_records - $votingTable->blank_votes - $votingTable->null_votes) }}</strong></td>
                                                <td class="text-center"><strong>{{ $votingTable->computed_records > 0 ? round((($votingTable->computed_records - $votingTable->blank_votes - $votingTable->null_votes) / $votingTable->computed_records) * 100, 1) : 0 }}%</strong></td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">Votos en Blanco</td>
                                                <td class="text-center">{{ number_format($votingTable->blank_votes) }}</td>
                                                <td class="text-center">{{ $votingTable->computed_records > 0 ? round(($votingTable->blank_votes / $votingTable->computed_records) * 100, 1) : 0 }}%</td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">Votos Nulos</td>
                                                <td class="text-center">{{ number_format($votingTable->null_votes) }}</td>
                                                <td class="text-center">{{ $votingTable->computed_records > 0 ? round(($votingTable->null_votes / $votingTable->computed_records) * 100, 1) : 0 }}%</td>
                                            </tr>
                                            <tr class="table-secondary">
                                                <td colspan="2"><strong>Total Votos Computados</strong></td>
                                                <td class="text-center"><strong>{{ number_format($votingTable->computed_records) }}</strong></td>
                                                <td class="text-center"><strong>100%</strong></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Observaciones -->
                    @if($votingTable->observations)
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="info-box">
                                <h5 class="mb-3">
                                    <i class="ri-chat-1-line me-1"></i>
                                    Observaciones
                                </h5>
                                <p class="mb-0">{{ $votingTable->observations }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Auditoría -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="info-box bg-light">
                                <h5 class="mb-3">
                                    <i class="ri-history-line me-1"></i>
                                    Información de Auditoría
                                </h5>
                                <div class="row">
                                    <div class="col-md-3">
                                        <small class="text-muted d-block">Creado por:</small>
                                        <strong>{{ $votingTable->createdBy->name ?? 'Sistema' }}</strong>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted d-block">Fecha creación:</small>
                                        <strong>{{ $votingTable->created_at->format('d/m/Y H:i') }}</strong>
                                    </div>
                                    @if($votingTable->updatedBy)
                                    <div class="col-md-3">
                                        <small class="text-muted d-block">Actualizado por:</small>
                                        <strong>{{ $votingTable->updatedBy->name }}</strong>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted d-block">Fecha actualización:</small>
                                        <strong>{{ $votingTable->updated_at->format('d/m/Y H:i') }}</strong>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gráfico de resultados si hay votos
            @if($votingTable->votes->count() > 0)
                var options = {
                    series: [{
                        data: [
                            @foreach($votingTable->votes as $vote)
                                {{ $vote->quantity }},
                            @endforeach
                        ]
                    }],
                    chart: {
                        type: 'bar',
                        height: 350
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            horizontal: true,
                        }
                    },
                    xaxis: {
                        categories: [
                            @foreach($votingTable->votes as $vote)
                                '{{ $vote->candidate->name ?? "N/A" }}',
                            @endforeach
                        ],
                    },
                    colors: ['#0ab39c'],
                    title: {
                        text: 'Resultados por Candidato',
                        align: 'center',
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold',
                        }
                    }
                };

                var chart = new ApexCharts(document.querySelector("#results-chart"), options);
                chart.render();
            @endif
        });
    </script>
@endsection