{{-- resources/views/voting-table-votes/partials/table.blade.php --}}
@php
    $isDisabled = in_array($table->current_status, [
                  'en_escrutinio', 'escrutada', 'transmitida', 'anulada'
              ]) || !($permissions['can_register'] ?? false);
    $categoryColors = ['primary', 'success', 'warning', 'info', 'danger', 'secondary', 'dark'];
    $categoryColorMap = [];
    $index = 0;
    $categoryCodes = array_keys($candidatesByCategory ?? []);
    foreach ($categoryCodes as $code) {
        $categoryColorMap[$code] = $categoryColors[$index % count($categoryColors)];
        $index++;
    }
    $hasInconsistencies = false;
    if (isset($table->results_by_category)) {
        foreach ($table->results_by_category as $result) {
            if (!$result['is_consistent']) {
                $hasInconsistencies = true;
                break;
            }
        }
    }
@endphp

<div class="card mb-3 table-card status-{{ $table->current_status }}"
     id="table-{{ $table->id }}"
     data-table-id="{{ $table->id }}"
     data-expected-voters="{{ $table->expected_voters }}">
    <div class="card-header bg-light position-relative">
        @if($hasInconsistencies)
            <span class="badge bg-danger role-badge" title="Tiene inconsistencias">
                <i class="ri-alert-line me-1"></i>Inconsistente
            </span>
        @elseif($table->current_status === 'observada')
            <span class="badge bg-danger role-badge" title="Tiene observaciones">
                <i class="ri-chat-1-line me-1"></i>Observada
            </span>
        @elseif($table->current_status === 'escrutada')
            <span class="badge bg-success role-badge">
                <i class="ri-check-line me-1"></i>Escrutada
            </span>
        @elseif($table->current_status === 'transmitida')
            <span class="badge bg-primary role-badge">
                <i class="ri-check-double-line me-1"></i>Transmitida
            </span>
        @endif

        <div class="row align-items-center">
            <div class="col-md-3">
                <h5 class="mb-0">
                    <i class="ri-table-line me-1"></i>
                    Mesa {{ $table->number }} - {{ $table->internal_code ?? $table->oep_code }}
                </h5>
                <small class="text-muted">{{ $table->institution->name ?? 'N/A' }}</small>
            </div>
            <div class="col-md-2">
                @php
                    $statusClasses = [
                        'configurada' => 'secondary',
                        'en_espera' => 'info',
                        'votacion' => 'primary',
                        'en_escrutinio' => 'warning',
                        'escrutada' => 'success',
                        'observada' => 'danger',
                        'transmitida' => 'success',
                        'anulada' => 'dark',
                        'sin_configurar' => 'light'
                    ];
                @endphp
                <span class="badge bg-{{ $statusClasses[$table->current_status] ?? 'secondary' }}">
                    {{ $statusLabels[$table->current_status] ?? $table->current_status }}
                </span>
            </div>
            <div class="col-md-2">
                <span class="text-muted">
                    <i class="ri-group-line me-1"></i>
                    {{ number_format($table->expected_voters ?? 0) }}
                </span>
            </div>
            <div class="col-md-2">
                <span class="text-muted">
                    <i class="ri-bar-chart-line me-1"></i>
                    Votos: <span class="total-votes fw-bold" id="total-{{ $table->id }}">{{ $table->total_voters }}</span>
                </span>
            </div>
            <div class="col-md-3 text-end">
                @include('voting-table-votes.partials.table-actions', ['table' => $table])
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-12">
                <div class="d-flex gap-3 flex-wrap align-items-center">
                    @forelse($candidatesByCategory as $categoryCode => $candidates)
                        @php
                            $categoryTotal = $table->results_by_category[$categoryCode]['total_votes'] ?? 0;
                            $isConsistent = $table->results_by_category[$categoryCode]['is_consistent'] ?? true;
                        @endphp
                        <span class="badge bg-{{ $categoryColorMap[$categoryCode] ?? 'secondary' }} category-badge">
                            {{ $categoryCode }}: <span id="total-{{ $categoryCode }}-{{ $table->id }}">{{ $categoryTotal }}</span>
                            @if(!$isConsistent)
                                <i class="ri-alert-line text-warning ms-1" title="Inconsistente"></i>
                            @endif
                        </span>
                    @empty
                        <span class="text-muted">No hay categorías disponibles</span>
                    @endforelse

                    @if($hasInconsistencies)
                        <span class="inconsistency-warning">
                            <i class="ri-alert-line me-1"></i>Inconsistencias detectadas
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        @if(empty($candidatesByCategory))
            <div class="text-center py-5">
                <p class="text-muted">No hay candidatos disponibles para esta elección</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 3%;">#</th>
                            <th style="width: 10%;">Partido</th>
                            @foreach($candidatesByCategory as $categoryCode => $candidates)
                                <th colspan="3" class="text-center table-{{ $categoryColorMap[$categoryCode] ?? 'secondary' }}">
                                    {{ $categoryCode }}
                                </th>
                            @endforeach
                        </tr>
                        <tr>
                            <th></th>
                            <th></th>
                            @foreach($candidatesByCategory as $categoryCode => $candidates)
                                <th class="table-{{ $categoryColorMap[$categoryCode] ?? 'secondary' }}">Candidato</th>
                                <th class="table-{{ $categoryColorMap[$categoryCode] ?? 'secondary' }} text-center">Votos</th>
                                <th class="table-{{ $categoryColorMap[$categoryCode] ?? 'secondary' }} text-center">Obs</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @include('voting-table-votes.partials.table-rows', [
                            'table' => $table,
                            'candidatesByCategory' => $candidatesByCategory,
                            'permissions' => $permissions,
                            'isDisabled' => $isDisabled,
                            'categoryColorMap' => $categoryColorMap
                        ])
                    </tbody>
                </table>
            </div>
        @endif
        @if(($permissions['can_observe'] ?? false) && !$isDisabled && !empty($candidatesByCategory))
        <div class="p-2 bg-light border-top">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="text-muted" id="selected-count-{{ $table->id }}">0</span> votos seleccionados para observar
                    @foreach($candidatesByCategory as $categoryCode => $candidates)
                        <span class="badge bg-{{ $categoryColorMap[$categoryCode] ?? 'secondary' }} ms-2"
                              id="selected-{{ $categoryCode }}-{{ $table->id }}">0 {{ $categoryCode }}</span>
                    @endforeach
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-sm btn-warning create-observation-btn"
                            data-table-id="{{ $table->id }}">
                        <i class="ri-chat-1-line me-1"></i>
                        Crear Observación
                    </button>
                </div>
            </div>
        </div>
        @endif
        <div class="row g-0 bg-light p-2 border-top small">
            <div class="col-md-3">
                <span class="text-muted">Votos Válidos:</span>
                <span class="fw-bold ms-1">{{ array_sum(array_column($table->results_by_category ?? [], 'valid_votes')) }}</span>
            </div>
            <div class="col-md-3">
                <span class="text-muted">Votos en Blanco:</span>
                <span class="fw-bold ms-1">{{ array_sum(array_column($table->results_by_category ?? [], 'blank_votes')) }}</span>
            </div>
            <div class="col-md-3">
                <span class="text-muted">Votos Nulos:</span>
                <span class="fw-bold ms-1">{{ array_sum(array_column($table->results_by_category ?? [], 'null_votes')) }}</span>
            </div>
            <div class="col-md-3">
                <span class="text-muted">Papeletas Sobrantes:</span>
                <span class="fw-bold ms-1">{{ $table->ballots_leftover ?? 0 }}</span>
            </div>
        </div>
        @if($hasInconsistencies && isset($table->results_by_category))
            <div class="p-2 border-top bg-warning bg-opacity-10">
                @foreach($table->results_by_category as $categoryCode => $result)
                    @if(!$result['is_consistent'])
                        <small class="text-warning d-block">
                            <i class="ri-information-line me-1"></i>
                            {{ $categoryCode }}: {{ $result['valid_votes'] }} válidos +
                            {{ $result['blank_votes'] }} blancos + {{ $result['null_votes'] }} nulos =
                            {{ $result['valid_votes'] + $result['blank_votes'] + $result['null_votes'] }}
                            (total: {{ $result['total_votes'] }})
                        </small>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>
