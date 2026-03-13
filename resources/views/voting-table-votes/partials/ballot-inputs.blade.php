{{-- resources/views/voting-table-votes/partials/ballot-inputs.blade.php--}}
@php
    $te              = $table->elections->first();
    $ballotsInUrn    = $te?->total_voters    ?? 0;
    $ballotsLeftover = $te?->ballots_leftover ?? 0;
    $ballotsSpoiled  = $te?->ballots_spoiled  ?? 0;
    $ballotsReceived = $te?->ballots_received ?? 0;
    $expectedVoters  = $table->expected_voters ?? 0;
    $participation = $expectedVoters > 0
        ? round(($ballotsInUrn / $expectedVoters) * 100, 1)
        : 0;
    $balanceOk = ($ballotsReceived === 0)
        || ($ballotsInUrn + $ballotsLeftover + $ballotsSpoiled === $ballotsReceived);
    $canEdit = ($permissions['can_register'] ?? false) && ! $isDisabled;
@endphp

<div class="border rounded bg-light px-3 py-2 mb-2 ballot-data-section"
     id="ballot-data-{{ $table->id }}"
     data-table-id="{{ $table->id }}">
    <div class="row align-items-center g-2">
        <div class="col-6 col-md-2">
            <label class="form-label form-label-sm text-muted mb-1">
                <i class="ri-group-line me-1"></i>Habilitados
            </label>
            <div class="fw-bold fs-6 text-info">{{ number_format($expectedVoters) }}</div>
            <small class="text-muted">del padrón</small>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label form-label-sm text-muted mb-1">
                <i class="ri-inbox-line me-1"></i>En ánfora
                <span class="badge bg-secondary ms-1" style="font-size:0.65rem;" title="Calculado automáticamente">auto</span>
            </label>
            <div class="fw-bold fs-6 text-primary" id="urn-count-{{ $table->id }}">
                {{ number_format($ballotsInUrn) }}
            </div>
            <small class="text-muted">válidos+blancos+nulos</small>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label form-label-sm text-muted mb-1" for="leftover-{{ $table->id }}">
                <i class="ri-file-list-3-line me-1"></i>No utilizadas
            </label>
            @if($canEdit)
                <input type="number"
                       id="leftover-{{ $table->id }}"
                       class="form-control form-control-sm ballot-leftover-input"
                       data-table="{{ $table->id }}"
                       value="{{ $ballotsLeftover }}"
                       min="0"
                       style="max-width:90px;"
                       placeholder="0"
                       title="Papeletas no utilizadas (sobrantes)">
            @else
                <div class="fw-bold fs-6">{{ number_format($ballotsLeftover) }}</div>
            @endif
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label form-label-sm text-muted mb-1" for="spoiled-{{ $table->id }}">
                <i class="ri-delete-bin-line me-1"></i>Deterioradas
            </label>
            @if($canEdit)
                <input type="number"
                       id="spoiled-{{ $table->id }}"
                       class="form-control form-control-sm ballot-spoiled-input"
                       data-table="{{ $table->id }}"
                       value="{{ $ballotsSpoiled }}"
                       min="0"
                       style="max-width:90px;"
                       placeholder="0"
                       title="Papeletas deterioradas / inutilizadas">
            @else
                <div class="fw-bold fs-6">{{ number_format($ballotsSpoiled) }}</div>
            @endif
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label form-label-sm text-muted mb-1" for="received-{{ $table->id }}">
                <i class="ri-mail-download-line me-1"></i>Recibidas
            </label>
            @if($canEdit)
                <input type="number"
                       id="received-{{ $table->id }}"
                       class="form-control form-control-sm ballot-received-input"
                       data-table="{{ $table->id }}"
                       value="{{ $ballotsReceived > 0 ? $ballotsReceived : '' }}"
                       min="0"
                       max="{{ $expectedVoters }}"
                       style="max-width:90px;"
                       placeholder="{{ $expectedVoters }}"
                       title="Total papeletas recibidas (en ánfora + no utilizadas + deterioradas)">
            @else
                <div class="fw-bold fs-6">{{ number_format($ballotsReceived) }}</div>
            @endif
            <small class="text-muted">máx {{ number_format($expectedVoters) }}</small>
        </div>
        <div class="col-6 col-md-2 text-end">
            <label class="form-label form-label-sm text-muted mb-1">
                <i class="ri-percent-line me-1"></i>Participación
            </label>
            <div class="fw-bold fs-6 {{ $participation >= 75 ? 'text-success' : ($participation >= 50 ? 'text-warning' : 'text-secondary') }}"
                 id="participation-{{ $table->id }}">
                {{ $participation }}%
            </div>
            <div id="ballot-balance-{{ $table->id }}" class="mt-1">
                @if($ballotsReceived > 0)
                    @if($balanceOk)
                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:0.65rem;">
                            <i class="ri-checkbox-circle-line me-1"></i>Papeletas cuadran
                        </span>
                    @else
                        @php
                            $diff = $ballotsInUrn + $ballotsLeftover + $ballotsSpoiled - $ballotsReceived;
                        @endphp
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size:0.65rem;"
                              title="Diferencia: {{ $diff > 0 ? '+' : '' }}{{ $diff }}">
                            <i class="ri-alert-line me-1"></i>No cuadran ({{ $diff > 0 ? '+' : '' }}{{ $diff }})
                        </span>
                    @endif
                @else
                    <small class="text-muted" style="font-size:0.65rem;">
                        <i class="ri-information-line"></i> Ingrese papeletas recibidas para verificar
                    </small>
                @endif
            </div>
        </div>
    </div>
    <div class="mt-1 text-muted" style="font-size:0.72rem;">
        <i class="ri-equation-line me-1"></i>
        Ánfora + No utilizadas + Deterioradas = Recibidas
        &nbsp;|&nbsp;
        Ánfora = Válidos + Blancos + Nulos
    </div>

</div>

<style>
.ballot-data-section .form-control-sm { font-size: 0.82rem; }
.ballot-data-section label { font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; }
</style>
