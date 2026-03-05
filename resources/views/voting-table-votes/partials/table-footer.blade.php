{{-- resources/views/voting-table-votes/partials/table-footer.blade.php --}}
@if($userCan['observe'] && !$isDisabled)
<div class="p-2 bg-light border-top">
    <div class="row align-items-center">
        <div class="col-md-8">
            <span class="text-muted" id="selected-count-{{ $table->id }}">0</span> votos seleccionados para observar
            <span class="badge bg-primary ms-2" id="selected-primary-{{ $table->id }}">0 {{ $primaryCategory ?? 'Primarios' }}</span>
            <span class="badge bg-success ms-1" id="selected-secondary-{{ $table->id }}">0 {{ $secondaryCategory ?? 'Secundarios' }}</span>
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
