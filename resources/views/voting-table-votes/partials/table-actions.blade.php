@php
    $status       = $table->current_status ?? 'sin_configurar';
    $isFinal      = in_array($status, ['escrutada', 'transmitida', 'anulada']);
    $isEditable   = in_array($status, ['configurada', 'en_espera', 'votacion'])
                    && !$isFinal
                    && ($permissions['can_register'] ?? false);
    $isObservable = in_array($status, ['votacion', 'en_escrutinio', 'observada'])
                    && !$isFinal;
    $isCorrectable = $status === 'observada';
    $canValidate  = in_array($status, ['votacion', 'observada'])
                    && !$isFinal
                    && ($permissions['can_validate'] ?? false);
    $canEscrutar  = $status === 'en_escrutinio'
                    && ($permissions['can_validate'] ?? false);
    $canReject    = in_array($status, ['votacion', 'en_escrutinio', 'observada'])
                    && !$isFinal
                    && ($permissions['can_validate'] ?? false);
    $isReopenable = in_array($status, ['observada', 'en_escrutinio'])
                    && ($permissions['can_reopen'] ?? false);
@endphp
<div class="btn-group btn-group-sm flex-wrap" role="group">
    @if(($table->observations_count ?? 0) > 0)
        <button type="button" class="btn btn-warning view-observations"
                data-table-id="{{ $table->id }}"
                title="Ver {{ $table->observations_count }} observación(es) pendiente(s)">
            <i class="ri-alert-line"></i>
            <span class="badge bg-white text-warning ms-1">{{ $table->observations_count }}</span>
        </button>
    @endif
    @if($isEditable)
        <button type="button" class="btn btn-success save-table"
                data-table-id="{{ $table->id }}"
                data-election-type-id="{{ $electionTypeId }}"
                title="Guardar votos">
            <i class="ri-save-line"></i>
        </button>
    @endif
    @if($isObservable && ($permissions['can_observe'] ?? false))
        <button type="button" class="btn btn-warning observe-table-general"
                data-table-id="{{ $table->id }}"
                data-election-type-id="{{ $electionTypeId }}"
                title="Agregar observación">
            <i class="ri-chat-1-line"></i>
        </button>
    @endif
    @if($isCorrectable && ($permissions['can_correct'] ?? false))
        <button type="button" class="btn btn-primary correct-table"
                data-table-id="{{ $table->id }}"
                data-election-type-id="{{ $electionTypeId }}"
                title="Corregir votos observados">
            <i class="ri-edit-line"></i>
        </button>
    @endif
    @if($canValidate)
        <button type="button" class="btn btn-info text-white validate-table"
                data-table-id="{{ $table->id }}"
                data-election-type-id="{{ $electionTypeId }}"
                data-action="validate"
                title="Validar votos — pasa a En Escrutinio">
            <i class="ri-checkbox-circle-line me-1"></i>Validar
        </button>
    @endif
    @if($canEscrutar)
        <button type="button" class="btn btn-success validate-table"
                data-table-id="{{ $table->id }}"
                data-election-type-id="{{ $electionTypeId }}"
                data-action="escrutar"
                title="Escrutar — cierra el conteo definitivamente">
            <i class="ri-check-double-line me-1"></i>Escrutar
        </button>
    @endif
    @if($canReject)
        <button type="button" class="btn btn-danger validate-table"
                data-table-id="{{ $table->id }}"
                data-election-type-id="{{ $electionTypeId }}"
                data-action="reject"
                title="Rechazar — mesa vuelve a Observada">
            <i class="ri-close-circle-line"></i>
        </button>
    @endif
    @if(!$isFinal && ($permissions['can_upload_acta'] ?? false))
        <button type="button" class="btn btn-secondary upload-acta"
                data-table-id="{{ $table->id }}"
                data-election-type-id="{{ $electionTypeId }}"
                onclick="openActaModal({{ $table->id }}, {{ $electionTypeId ?? 'null' }})"
                title="Subir acta">
            <i class="ri-upload-line"></i>
        </button>
    @endif
    @if($isReopenable)
        <button type="button" class="btn btn-outline-secondary reopen-table"
                data-table-id="{{ $table->id }}"
                data-election-type-id="{{ $electionTypeId }}"
                title="Reabrir mesa — vuelve a Votación">
            <i class="ri-lock-unlock-line"></i>
        </button>
    @endif
</div>
