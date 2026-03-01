{{-- resources/views/voting-table-votes/partials/quick-stats.blade.php --}}
@if(isset($tableStats) && $tableStats['total'] > 0)
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex flex-wrap gap-2">
            <span class="badge bg-primary p-2">
                Total: {{ $tableStats['total'] }} mesas
            </span>
            @if($tableStats['pendiente'] > 0)
                <span class="badge bg-warning p-2">
                    Pendientes: {{ $tableStats['pendiente'] }}
                </span>
            @endif
            @if($tableStats['en_proceso'] > 0)
                <span class="badge bg-info p-2">
                    En Proceso: {{ $tableStats['en_proceso'] }}
                </span>
            @endif
            @if($tableStats['activo'] > 0)
                <span class="badge bg-success p-2">
                    Activas: {{ $tableStats['activo'] }}
                </span>
            @endif
            @if($tableStats['cerrado'] > 0)
                <span class="badge bg-secondary p-2">
                    Cerradas: {{ $tableStats['cerrado'] }}
                </span>
            @endif
            @if($tableStats['observado'] > 0)
                <span class="badge bg-danger p-2">
                    Observadas: {{ $tableStats['observado'] }}
                </span>
            @endif
        </div>
    </div>
</div>
@endif