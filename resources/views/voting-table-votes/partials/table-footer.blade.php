{{--
    resources/views/voting-table-votes/partials/table-footer.blade.php

    Variables expected:
      $table            – VotingTable (with results_by_category and ballots_leftover)
      $permissions      – global permissions array
      $candidatesByCategory – [categoryCode => Collection]
      $categoryColorMap – [categoryCode => Bootstrap colour string]
      $isDisabled       – bool: inputs are read-only
--}}

{{-- ── Selection bar for observe-by-vote (only shown to users who can observe) ── --}}
@if(($permissions['can_observe'] ?? false) && !$isDisabled)
<div class="p-2 bg-light border-top">
    <div class="row align-items-center">
        <div class="col-md-8 small">
            <span class="text-muted">
                <span id="selected-count-{{ $table->id }}">0</span> votos seleccionados para observar
            </span>
            @foreach($candidatesByCategory as $categoryCode => $_)
                <span class="badge bg-{{ $categoryColorMap[$categoryCode] ?? 'secondary' }} ms-2"
                      id="selected-{{ $categoryCode }}-{{ $table->id }}">
                    0 {{ $categoryCode }}
                </span>
            @endforeach
        </div>
        <div class="col-md-4 text-end">
            <button type="button"
                    class="btn btn-sm btn-warning create-observation-btn"
                    data-table-id="{{ $table->id }}">
                <i class="ri-chat-1-line me-1"></i>Crear Observación
            </button>
        </div>
    </div>
</div>
@endif

{{-- ── Vote summary row ── --}}
<div class="row g-0 bg-light p-2 border-top small">
    @php
        $totalValid  = array_sum(array_column($table->results_by_category ?? [], 'valid_votes'));
        $totalBlank  = array_sum(array_column($table->results_by_category ?? [], 'blank_votes'));
        $totalNull   = array_sum(array_column($table->results_by_category ?? [], 'null_votes'));
    @endphp

    <div class="col-6 col-md-3">
        <span class="text-muted">Votos Válidos:</span>
        <span class="fw-bold ms-1" id="footer-valid-{{ $table->id }}">{{ $totalValid }}</span>
    </div>
    <div class="col-6 col-md-3">
        <span class="text-muted">Votos en Blanco:</span>
        <span class="fw-bold ms-1" id="footer-blank-{{ $table->id }}">{{ $totalBlank }}</span>
    </div>
    <div class="col-6 col-md-3">
        <span class="text-muted">Votos Nulos:</span>
        <span class="fw-bold ms-1" id="footer-null-{{ $table->id }}">{{ $totalNull }}</span>
    </div>
    <div class="col-6 col-md-3">
        <span class="text-muted">Papeletas Sobrantes:</span>
        <span class="fw-bold ms-1">{{ $table->ballots_leftover ?? 0 }}</span>
    </div>
</div>

{{-- ── Inconsistency details row ── --}}
@php
    $hasInconsistencies = collect($table->results_by_category ?? [])
        ->contains(fn($r) => !($r['is_consistent'] ?? true));
@endphp

@if($hasInconsistencies)
<div class="p-2 border-top bg-danger bg-opacity-10 small">
    <strong class="text-danger">
        <i class="ri-alert-line me-1"></i>Inconsistencias detectadas:
    </strong>
    @foreach($table->results_by_category ?? [] as $code => $result)
        @if(!($result['is_consistent'] ?? true))
            <div class="ms-3 text-danger">
                {{ $code }}: {{ $result['valid_votes'] }} válidos
                + {{ $result['blank_votes'] }} blancos
                + {{ $result['null_votes'] }} nulos
                = {{ $result['valid_votes'] + $result['blank_votes'] + $result['null_votes'] }}
                @if($result['total_votes'] !== ($result['valid_votes'] + $result['blank_votes'] + $result['null_votes']))
                    (guardado: {{ $result['total_votes'] }})
                @endif
            </div>
        @endif
    @endforeach
</div>
@endif
