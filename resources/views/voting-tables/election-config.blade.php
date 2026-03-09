@extends('layouts.master')

@section('title')
    Configuración Electoral - Mesa {{ $votingTable->oep_code ?? $votingTable->internal_code }}
@endsection

@section('css')
    <style>
        .config-card {
            border: 1px solid #e9e9ef;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }
        .config-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }
    </style>
@endsection

@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            <a href="{{ route('voting-tables.index') }}">Mesas</a>
        @endslot
        @slot('li_2')
            <a href="{{ route('voting-tables.show', $votingTable->id) }}">
                Mesa {{ $votingTable->oep_code ?? $votingTable->internal_code }}
            </a>
        @endslot
        @slot('title')
            Configuración Electoral
        @endslot
    @endcomponent

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="ri-settings-4-line me-1"></i>
                        Configuración por Tipo de Elección
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="ri-information-line me-1"></i>
                        <strong>Mesa:</strong> {{ $votingTable->institution->name }} - N° {{ $votingTable->number }}
                        @if($votingTable->letter)
                            ({{ $votingTable->letter }})
                        @endif
                        <br>
                        <small>Configure los datos electorales para cada tipo de elección.</small>
                    </div>

                    <div class="row">
                        @forelse($electionTypes as $electionType)
                            @php
                                $config = $votingTable->elections->firstWhere('election_type_id', $electionType->id);
                                $statusColors = [
                                    'configurada' => 'secondary',
                                    'en_espera' => 'info',
                                    'votacion' => 'primary',
                                    'cerrada' => 'warning',
                                    'en_escrutinio' => 'dark',
                                    'escrutada' => 'success',
                                    'observada' => 'danger',
                                    'transmitida' => 'success',
                                    'anulada' => 'dark'
                                ];
                            @endphp
                            <div class="col-md-6 mb-4">
                                <div class="card config-card">
                                    <div class="card-header bg-light">
                                        <h5 class="card-title mb-0">
                                            {{ $electionType->name }}
                                            <small class="text-muted d-block">{{ $electionType->short_name ?? '' }}</small>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('voting-tables.election-config.update', $votingTable->id) }}"
                                              method="POST" class="election-config-form">
                                            @csrf
                                            <input type="hidden" name="election_type_id" value="{{ $electionType->id }}">

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Papeletas Recibidas</label>
                                                    <input type="number" name="ballots_received"
                                                           class="form-control"
                                                           value="{{ old('ballots_received', $config->ballots_received ?? 0) }}"
                                                           min="0" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Papeletas Usadas</label>
                                                    <input type="number" name="ballots_used"
                                                           class="form-control"
                                                           value="{{ old('ballots_used', $config->ballots_used ?? 0) }}"
                                                           min="0" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Papeletas Sobrantes</label>
                                                    <input type="number" name="ballots_leftover"
                                                           class="form-control"
                                                           value="{{ old('ballots_leftover', $config->ballots_leftover ?? 0) }}"
                                                           min="0" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Papeletas Deterioradas</label>
                                                    <input type="number" name="ballots_spoiled"
                                                           class="form-control"
                                                           value="{{ old('ballots_spoiled', $config->ballots_spoiled ?? 0) }}"
                                                           min="0" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Total Votantes</label>
                                                    <input type="number" name="total_voters"
                                                           class="form-control"
                                                           value="{{ old('total_voters', $config->total_voters ?? 0) }}"
                                                           min="0" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Estado</label>
                                                    <select name="status" class="form-select" required>
                                                        @foreach(\App\Models\VotingTable::getStatuses() as $value => $label)
                                                            <option value="{{ $value }}"
                                                                {{ (old('status', $config->status ?? 'configurada') == $value) ? 'selected' : '' }}>
                                                                {{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Hora Apertura</label>
                                                    <input type="time" name="opening_time"
                                                           class="form-control"
                                                           value="{{ old('opening_time', $config->opening_time ?? '') }}">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Hora Cierre</label>
                                                    <input type="time" name="closing_time"
                                                           class="form-control"
                                                           value="{{ old('closing_time', $config->closing_time ?? '') }}">
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label class="form-label">Fecha Elección</label>
                                                    <input type="date" name="election_date"
                                                           class="form-control"
                                                           value="{{ old('election_date', $config->election_date ?? $electionType->election_date ?? '') }}">
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label class="form-label">Observaciones</label>
                                                    <textarea name="observations" class="form-control" rows="2">{{ old('observations', $config->observations ?? '') }}</textarea>
                                                </div>
                                            </div>

                                            <div class="text-end">
                                                @if($config)
                                                    <span class="badge bg-{{ $statusColors[$config->status] ?? 'secondary' }} status-badge me-2">
                                                        Estado actual: {{ \App\Models\VotingTable::getStatuses()[$config->status] ?? $config->status }}
                                                    </span>
                                                @endif
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="ri-save-line me-1"></i>Guardar Configuración
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <i class="ri-alert-line me-1"></i>
                                    No hay tipos de elección activos en el sistema.
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6><i class="ri-information-line me-1"></i> Información importante:</h6>
                                <ul class="mb-0">
                                    <li>La suma de usadas + sobrantes + deterioradas debe igualar las recibidas.</li>
                                    <li>Las papeletas recibidas no pueden ser menores al total de votantes.</li>
                                    <li>Los cambios en el estado afectan el flujo de trabajo de la mesa.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 text-end">
                            <a href="{{ route('voting-tables.show', $votingTable->id) }}" class="btn btn-secondary">
                                <i class="ri-arrow-left-line me-1"></i>Volver a la Mesa
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-calculate ballot consistency
            const forms = document.querySelectorAll('.election-config-form');
            forms.forEach(form => {
                const received = form.querySelector('input[name="ballots_received"]');
                const used = form.querySelector('input[name="ballots_used"]');
                const leftover = form.querySelector('input[name="ballots_leftover"]');
                const spoiled = form.querySelector('input[name="ballots_spoiled"]');

                function validateBallots() {
                    const total = parseInt(used.value || 0) +
                                 parseInt(leftover.value || 0) +
                                 parseInt(spoiled.value || 0);

                    if (total !== parseInt(received.value || 0)) {
                        received.setCustomValidity('La suma de usadas + sobrantes + deterioradas debe igualar las recibidas');
                    } else {
                        received.setCustomValidity('');
                    }
                }

                [received, used, leftover, spoiled].forEach(input => {
                    input.addEventListener('input', validateBallots);
                });
            });
        });
    </script>
@endsection
