{{-- resources/views/voting-table-votes/partials/table.blade.php --}}
@php
    $isDisabled = in_array($table->status, ['cerrada', 'escrutada', 'transmitida', 'anulada']) ||
                  ($table->validation_status === 'validated' && !($userCan['correct'] ?? false)) ||
                  ($table->validation_status === 'approved');

    // Colores para las categorías (cíclico)
    $categoryColors = ['primary', 'success', 'warning', 'info', 'danger', 'secondary', 'dark'];
    $categoryColorMap = [];
    $index = 0;

    $categoryCodes = array_keys($candidatesByCategory ?? []);
    foreach ($categoryCodes as $code) {
        $categoryColorMap[$code] = $categoryColors[$index % count($categoryColors)];
        $index++;
    }
@endphp

<div class="card mb-3 table-card status-{{ $table->status }}"
     id="table-{{ $table->id }}"
     data-table-id="{{ $table->id }}"
     data-expected-voters="{{ $table->expected_voters }}">

    {{-- Header de la mesa --}}
    <div class="card-header bg-light position-relative">
        @if($table->validation_status === 'observed' || $table->status === 'observada')
            <span class="badge bg-danger role-badge" title="Tiene observaciones">
                <i class="ri-alert-line me-1"></i>Observada
            </span>
        @elseif($table->validation_status === 'validated')
            <span class="badge bg-success role-badge">
                <i class="ri-check-line me-1"></i>Validada
            </span>
        @elseif($table->validation_status === 'approved')
            <span class="badge bg-primary role-badge">
                <i class="ri-check-double-line me-1"></i>Aprobada
            </span>
        @elseif($table->status === 'cerrada')
            <span class="badge bg-secondary role-badge">
                <i class="ri-lock-line me-1"></i>Cerrada
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
                        'cerrada' => 'danger',
                        'en_escrutinio' => 'warning',
                        'escrutada' => 'success',
                        'observada' => 'danger',
                        'transmitida' => 'success',
                        'anulada' => 'dark'
                    ];
                @endphp
                <span class="badge bg-{{ $statusClasses[$table->status] ?? 'secondary' }}">
                    {{ $statusLabels[$table->status] ?? $table->status }}
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
                    Votos: <span class="total-votes fw-bold" id="total-{{ $table->id }}">{{ $table->votes->sum('quantity') }}</span>
                </span>
            </div>
            <div class="col-md-3 text-end">
                @include('voting-table-votes.partials.table-actions', ['table' => $table])
            </div>
        </div>

        {{-- Totales por categoría --}}
        <div class="row mt-2">
            <div class="col-12">
                <div class="d-flex gap-3 flex-wrap">
                    @forelse($candidatesByCategory as $categoryCode => $candidates)
                        @php
                            $categoryTotal = 0;
                            foreach ($table->votes as $vote) {
                                if ($vote->candidate && $vote->candidate->electionTypeCategory &&
                                    $vote->candidate->electionTypeCategory->electionCategory &&
                                    $vote->candidate->electionTypeCategory->electionCategory->code == $categoryCode) {
                                    $categoryTotal += $vote->quantity;
                                }
                            }
                        @endphp
                        <span class="badge bg-{{ $categoryColorMap[$categoryCode] ?? 'secondary' }}">
                            {{ $categoryCode }}: <span id="total-{{ $categoryCode }}-{{ $table->id }}">{{ $categoryTotal }}</span>
                        </span>
                    @empty
                        <span class="text-muted">No hay categorías disponibles</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de votos dinámica --}}
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
                            'userCan' => $userCan,
                            'isDisabled' => $isDisabled,
                            'categoryColorMap' => $categoryColorMap
                        ])
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Footer con selección de observaciones --}}
        @if(($userCan['observe'] ?? false) && !$isDisabled && !empty($candidatesByCategory))
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

        {{-- Resumen de votos --}}
        <div class="row g-0 bg-light p-2 border-top small">
            <div class="col-md-3">
                <span class="text-muted">Votos Válidos:</span>
                <span class="fw-bold ms-1">{{ $table->valid_votes ?? 0 }}</span>
            </div>
            <div class="col-md-3">
                <span class="text-muted">Votos en Blanco:</span>
                <span class="fw-bold ms-1">{{ $table->blank_votes ?? 0 }}</span>
            </div>
            <div class="col-md-3">
                <span class="text-muted">Votos Nulos:</span>
                <span class="fw-bold ms-1">{{ $table->null_votes ?? 0 }}</span>
            </div>
            <div class="col-md-3">
                <span class="text-muted">Papeletas Sobrantes:</span>
                <span class="fw-bold ms-1">{{ $table->ballots_leftover ?? 0 }}</span>
            </div>
        </div>
    </div>
</div>
