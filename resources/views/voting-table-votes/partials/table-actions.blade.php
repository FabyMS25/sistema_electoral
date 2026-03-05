{{-- resources/views/voting-table-votes/partials/table-actions.blade.php --}}
<div class="btn-group btn-group-sm">
    @if(($table->actas_count ?? 0) > 0)
        <button class="btn btn-info view-actas" data-table-id="{{ $table->id }}" title="Ver actas">
            <i class="ri-file-copy-line"></i>
            <span class="badge bg-white text-info ms-1">{{ $table->actas_count }}</span>
        </button>
    @endif

    @if(($table->observations_count ?? 0) > 0)
        <button class="btn btn-warning view-observations" data-table-id="{{ $table->id }}" title="Ver observaciones">
            <i class="ri-chat-1-line"></i>
            <span class="badge bg-white text-warning ms-1">{{ $table->observations_count }}</span>
        </button>
    @endif

    @if(!in_array($table->status, ['cerrada', 'escrutada', 'transmitida', 'anulada']))
        @if($permissions['can_register'] ?? false)
            <button class="btn btn-success save-table" data-table-id="{{ $table->id }}" title="Guardar (Ctrl+Enter)">
                <i class="ri-save-line"></i>
            </button>
        @endif

        @if($permissions['can_observe'] ?? false)
            <button class="btn btn-warning observe-table-general" data-table-id="{{ $table->id }}" title="Observación general">
                <i class="ri-chat-1-line"></i>
            </button>
        @endif

        @if($permissions['can_upload_acta'] ?? false)  {{-- ← NOMBRE CORREGIDO --}}
            <button class="btn btn-info upload-acta" data-table-id="{{ $table->id }}" title="Subir acta">
                <i class="ri-upload-line"></i>
            </button>
        @endif

        @if($permissions['can_close'] ?? false)
            <button class="btn btn-secondary close-table" data-table-id="{{ $table->id }}" title="Cerrar mesa">
                <i class="ri-lock-line"></i>
            </button>
        @endif
    @endif
</div>
