{{--
    resources/views/voting-table-votes/partials/table-actions.blade.php

    Renders the action button group for a single voting table row.

    Variables expected (passed from table.blade.php):
      $table        – VotingTable model (enriched with current_status, observations_count)
      $permissions  – array from resolvePermissions() — GLOBAL flags (can_register etc.)
      $electionTypeId – int

    NOTE: The global $permissions flags tell us what buttons to render at all.
    The actual server-side permission check (with scope) happens in the controller.
    So a user who has register_votes only for Mesa 5 will still see the Save button
    on Mesa 5 — and the controller rejects the call for any other mesa.
--}}

@php
    $status   = $table->current_status ?? 'sin_configurar';
    $isFinal  = in_array($status, ['escrutada', 'transmitida', 'anulada']);
    $isClosed = $status === 'cerrada';

    // States where new votes can still be entered
    $isEditable = in_array($status, ['configurada', 'en_espera', 'votacion', 'en_escrutinio'])
                  && !$isFinal;

    // States where review makes sense (votes have been entered)
    $isReviewable = in_array($status, ['cerrada', 'votacion', 'en_escrutinio', 'observada', 'corregida']);

    // Validate is relevant once we are in escrutinio or after correction
    $isValidatable = in_array($status, ['en_escrutinio', 'observada', 'cerrada']);
@endphp

<div class="btn-group btn-group-sm" role="group">

    {{-- ── Observations badge ── --}}
    @if(($table->observations_count ?? 0) > 0)
        <button type="button"
                class="btn btn-warning view-observations"
                data-table-id="{{ $table->id }}"
                title="Ver {{ $table->observations_count }} observación(es) pendiente(s)">
            <i class="ri-alert-line"></i>
            <span class="badge bg-white text-warning ms-1">{{ $table->observations_count }}</span>
        </button>
    @endif

    {{-- ── Register / Save  (only when mesa is editable and not final) ── --}}
    @if($isEditable && ($permissions['can_register'] ?? false))
        <button type="button"
                class="btn btn-success save-table"
                data-table-id="{{ $table->id }}"
                data-election-type-id="{{ $electionTypeId }}"
                title="Guardar votos (Ctrl+Enter)">
            <i class="ri-save-line"></i>
        </button>
    @endif

    {{-- ── Review (revisor checks the entered votes) ── --}}
    @if($isReviewable && ($permissions['can_review'] ?? false))
        <button type="button"
                class="btn btn-info review-table"
                data-table-id="{{ $table->id }}"
                data-election-type-id="{{ $electionTypeId }}"
                title="Revisar votos">
            <i class="ri-eye-line"></i>
        </button>
    @endif

    {{-- ── Correct (fix observed votes) ── --}}
    @if($status === 'observada' && ($permissions['can_correct'] ?? false))
        <button type="button"
                class="btn btn-primary correct-table"
                data-table-id="{{ $table->id }}"
                data-election-type-id="{{ $electionTypeId }}"
                title="Corregir votos observados">
            <i class="ri-edit-line"></i>
        </button>
    @endif

    {{-- ── General observation ── --}}
    @if($isEditable && ($permissions['can_observe'] ?? false))
        <button type="button"
                class="btn btn-warning observe-table-general"
                data-table-id="{{ $table->id }}"
                data-election-type-id="{{ $electionTypeId }}"
                title="Agregar observación general">
            <i class="ri-chat-1-line"></i>
        </button>
    @endif

    {{-- ── Upload acta ── --}}
    @if(!$isFinal && ($permissions['can_upload_acta'] ?? false))
        <button type="button"
                class="btn btn-secondary upload-acta"
                data-table-id="{{ $table->id }}"
                data-election-type-id="{{ $electionTypeId }}"
                onclick="openActaModal({{ $table->id }}, {{ $electionTypeId ?? 'null' }})"
                title="Subir acta">
            <i class="ri-upload-line"></i>
        </button>
    @endif

    {{-- ── Close ── --}}
    @if($isEditable && ($permissions['can_close'] ?? false))
        <button type="button"
                class="btn btn-dark close-table"
                data-table-id="{{ $table->id }}"
                data-election-type-id="{{ $electionTypeId }}"
                title="Cerrar mesa">
            <i class="ri-lock-line"></i>
        </button>
    @endif

    {{-- ── Reopen ── --}}
    @if(($isClosed || $status === 'observada') && ($permissions['can_reopen'] ?? false))
        <button type="button"
                class="btn btn-outline-secondary reopen-table"
                data-table-id="{{ $table->id }}"
                data-election-type-id="{{ $electionTypeId }}"
                title="Reabrir mesa">
            <i class="ri-lock-unlock-line"></i>
        </button>
    @endif

    {{-- ── Validate (visible from en_escrutinio / cerrada / observada) ── --}}
    @if($isValidatable && ($permissions['can_validate'] ?? false))
        <div class="btn-group btn-group-sm" role="group">
            {{-- Validate → mesa stays en_escrutinio, votes → validated --}}
            <button type="button"
                    class="btn btn-info text-white validate-table"
                    data-table-id="{{ $table->id }}"
                    data-election-type-id="{{ $electionTypeId }}"
                    data-action="validate"
                    title="Validar votos">
                <i class="ri-checkbox-circle-line"></i> Validar
            </button>
            {{-- Close validated → mesa → escrutada (final) --}}
            <button type="button"
                    class="btn btn-success validate-table"
                    data-table-id="{{ $table->id }}"
                    data-election-type-id="{{ $electionTypeId }}"
                    data-action="close_validated"
                    title="Validar y Escrutar mesa (final)">
                <i class="ri-check-double-line"></i>
            </button>
            {{-- Reject → mesa → observada --}}
            <button type="button"
                    class="btn btn-danger validate-table"
                    data-table-id="{{ $table->id }}"
                    data-election-type-id="{{ $electionTypeId }}"
                    data-action="reject"
                    title="Rechazar mesa">
                <i class="ri-close-circle-line"></i>
            </button>
        </div>
    @endif

</div>
