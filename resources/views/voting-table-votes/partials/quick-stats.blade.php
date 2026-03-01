{{-- resources/views/voting-table-votes/partials/quick-stats.blade.php --}}
@if(isset($tableStats) && $tableStats['total'] > 0)
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="fw-semibold">Resumen por estado:</span>

            @if($tableStats['configurada'] > 0)
                <span class="badge bg-secondary p-2" title="Mesas configuradas">
                    <i class="ri-settings-4-line me-1"></i>
                    Configuradas: {{ $tableStats['configurada'] }}
                </span>
            @endif

            @if($tableStats['en_espera'] > 0)
                <span class="badge bg-info p-2" title="Mesas en espera">
                    <i class="ri-time-line me-1"></i>
                    En Espera: {{ $tableStats['en_espera'] }}
                </span>
            @endif

            @if($tableStats['votacion'] > 0)
                <span class="badge bg-primary p-2" title="Mesas en votación">
                    <i class="ri-vote-line me-1"></i>
                    Votación: {{ $tableStats['votacion'] }}
                </span>
            @endif

            @if($tableStats['en_escrutinio'] > 0)
                <span class="badge bg-warning text-dark p-2" title="Mesas en escrutinio">
                    <i class="ri-bar-chart-2-line me-1"></i>
                    Escrutinio: {{ $tableStats['en_escrutinio'] }}
                </span>
            @endif

            @if($tableStats['cerrada'] > 0)
                <span class="badge bg-secondary p-2" title="Mesas cerradas">
                    <i class="ri-lock-line me-1"></i>
                    Cerradas: {{ $tableStats['cerrada'] }}
                </span>
            @endif

            @if($tableStats['escrutada'] > 0)
                <span class="badge bg-success p-2" title="Mesas escrutadas">
                    <i class="ri-check-double-line me-1"></i>
                    Escrutadas: {{ $tableStats['escrutada'] }}
                </span>
            @endif

            @if($tableStats['observada'] > 0)
                <span class="badge bg-danger p-2" title="Mesas observadas">
                    <i class="ri-alert-line me-1"></i>
                    Observadas: {{ $tableStats['observada'] }}
                </span>
            @endif

            @if($tableStats['transmitida'] > 0)
                <span class="badge bg-success p-2" title="Mesas transmitidas">
                    <i class="ri-cloud-line me-1"></i>
                    Transmitidas: {{ $tableStats['transmitida'] }}
                </span>
            @endif

            @if($tableStats['anulada'] > 0)
                <span class="badge bg-dark p-2" title="Mesas anuladas">
                    <i class="ri-forbid-line me-1"></i>
                    Anuladas: {{ $tableStats['anulada'] }}
                </span>
            @endif
        </div>
    </div>
</div>

<!-- Barra de progreso de validación -->
<div class="row mb-3">
    <div class="col-12">
        <div class="progress" style="height: 25px;">
            @php
                $total = $tableStats['total'];
                $pendientes = ($tableStats['configurada'] + $tableStats['en_espera']) ?? 0;
                $enProceso = ($tableStats['votacion'] + $tableStats['en_escrutinio']) ?? 0;
                $completadas = ($tableStats['escrutada'] + $tableStats['transmitida']) ?? 0;
                $observadas = $tableStats['observada'] ?? 0;
                $cerradas = $tableStats['cerrada'] ?? 0;

                $pendientesPercent = $total > 0 ? round(($pendientes / $total) * 100) : 0;
                $enProcesoPercent = $total > 0 ? round(($enProceso / $total) * 100) : 0;
                $completadasPercent = $total > 0 ? round(($completadas / $total) * 100) : 0;
                $observadasPercent = $total > 0 ? round(($observadas / $total) * 100) : 0;
                $cerradasPercent = $total > 0 ? round(($cerradas / $total) * 100) : 0;
            @endphp

            @if($pendientes > 0)
                <div class="progress-bar bg-secondary" role="progressbar"
                     style="width: {{ $pendientesPercent }}%"
                     title="Pendientes: {{ $pendientes }} mesas">
                    {{ $pendientesPercent }}%
                </div>
            @endif

            @if($enProceso > 0)
                <div class="progress-bar bg-primary" role="progressbar"
                     style="width: {{ $enProcesoPercent }}%"
                     title="En Proceso: {{ $enProceso }} mesas">
                    {{ $enProcesoPercent }}%
                </div>
            @endif

            @if($completadas > 0)
                <div class="progress-bar bg-success" role="progressbar"
                     style="width: {{ $completadasPercent }}%"
                     title="Completadas: {{ $completadas }} mesas">
                    {{ $completadasPercent }}%
                </div>
            @endif

            @if($observadas > 0)
                <div class="progress-bar bg-danger" role="progressbar"
                     style="width: {{ $observadasPercent }}%"
                     title="Observadas: {{ $observadas }} mesas">
                    {{ $observadasPercent }}%
                </div>
            @endif

            @if($cerradas > 0)
                <div class="progress-bar bg-secondary" role="progressbar"
                     style="width: {{ $cerradasPercent }}%"
                     title="Cerradas: {{ $cerradas }} mesas">
                    {{ $cerradasPercent }}%
                </div>
            @endif
        </div>
        <div class="d-flex justify-content-between mt-1 small text-muted">
            <span>Pendientes: {{ $pendientes }}</span>
            <span>En Proceso: {{ $enProceso }}</span>
            <span>Completadas: {{ $completadas }}</span>
            <span>Observadas: {{ $observadas }}</span>
            <span>Cerradas: {{ $cerradas }}</span>
        </div>
    </div>
</div>
@endif
