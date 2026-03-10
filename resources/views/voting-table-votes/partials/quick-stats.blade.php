{{-- resources/views/voting-table-votes/partials/quick-stats.blade.php --}}
@if(isset($tableStats) && ($tableStats['total'] ?? 0) > 0)
@php
    $total     = $tableStats['total'] ?? 0;
    $gPending  = ($tableStats['configurada'] ?? 0) + ($tableStats['en_espera'] ?? 0);
    $gVoting   = $tableStats['votacion']      ?? 0;
    $gCounting = $tableStats['en_escrutinio'] ?? 0;
    $gDone     = ($tableStats['escrutada'] ?? 0) + ($tableStats['transmitida'] ?? 0);
    $gObserved = $tableStats['observada']     ?? 0;
    $gAnnulled = $tableStats['anulada']       ?? 0;
    $pct = fn($n) => $total > 0 ? round(($n / $total) * 100, 1) : 0;
@endphp
@if($total > 0)
<div class="row mb-2">
    <div class="col-12">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            @if(($tableStats['configurada'] ?? 0) > 0)
                <a href="{{ request()->fullUrlWithQuery(['status' => 'configurada']) }}"
                   class="badge bg-secondary text-decoration-none p-2 stat-badge"
                   title="Mesas configuradas (no iniciadas)">
                    <i class="ri-settings-4-line me-1"></i>
                    Configuradas: <strong>{{ $tableStats['configurada'] }}</strong>
                </a>
            @endif

            @if(($tableStats['en_espera'] ?? 0) > 0)
                <a href="{{ request()->fullUrlWithQuery(['status' => 'en_espera']) }}"
                   class="badge bg-info text-white text-decoration-none p-2 stat-badge"
                   title="Mesas en espera de apertura">
                    <i class="ri-time-line me-1"></i>
                    En Espera: <strong>{{ $tableStats['en_espera'] }}</strong>
                </a>
            @endif

            @if(($tableStats['votacion'] ?? 0) > 0)
                <a href="{{ request()->fullUrlWithQuery(['status' => 'votacion']) }}"
                   class="badge bg-primary text-decoration-none p-2 stat-badge"
                   title="Mesas actualmente en votación">
                    <i class="ri-vote-line me-1"></i>
                    Votación: <strong>{{ $tableStats['votacion'] }}</strong>
                </a>
            @endif

            @if(($tableStats['en_escrutinio'] ?? 0) > 0)
                <a href="{{ request()->fullUrlWithQuery(['status' => 'en_escrutinio']) }}"
                   class="badge bg-warning text-dark text-decoration-none p-2 stat-badge"
                   title="Mesas en proceso de escrutinio">
                    <i class="ri-bar-chart-2-line me-1"></i>
                    Escrutinio: <strong>{{ $tableStats['en_escrutinio'] }}</strong>
                </a>
            @endif

            @if(($tableStats['observada'] ?? 0) > 0)
                <a href="{{ request()->fullUrlWithQuery(['status' => 'observada']) }}"
                   class="badge bg-danger text-decoration-none p-2 stat-badge"
                   title="Mesas con observaciones pendientes">
                    <i class="ri-alert-line me-1"></i>
                    Observadas: <strong>{{ $tableStats['observada'] }}</strong>
                </a>
            @endif

            @if(($tableStats['escrutada'] ?? 0) > 0)
                <a href="{{ request()->fullUrlWithQuery(['status' => 'escrutada']) }}"
                   class="badge bg-success text-decoration-none p-2 stat-badge"
                   title="Mesas con escrutinio completo">
                    <i class="ri-check-double-line me-1"></i>
                    Escrutadas: <strong>{{ $tableStats['escrutada'] }}</strong>
                </a>
            @endif

            @if(($tableStats['transmitida'] ?? 0) > 0)
                <a href="{{ request()->fullUrlWithQuery(['status' => 'transmitida']) }}"
                   class="badge bg-success text-decoration-none p-2 stat-badge"
                   title="Mesas con resultados transmitidos">
                    <i class="ri-cloud-line me-1"></i>
                    Transmitidas: <strong>{{ $tableStats['transmitida'] }}</strong>
                </a>
            @endif

            @if(($tableStats['anulada'] ?? 0) > 0)
                <a href="{{ request()->fullUrlWithQuery(['status' => 'anulada']) }}"
                   class="badge bg-dark text-decoration-none p-2 stat-badge"
                   title="Mesas anuladas">
                    <i class="ri-forbid-line me-1"></i>
                    Anuladas: <strong>{{ $tableStats['anulada'] }}</strong>
                </a>
            @endif
            <span class="ms-auto text-muted small">
                <strong>{{ $total }}</strong> mesas en total
            </span>
        </div>
    </div>
</div>
<div class="row mb-2">
    <div class="col-12">
        <div class="progress" style="height: 22px; border-radius: 6px;" title="{{ $total }} mesas">

            @if($gPending > 0)
            <div class="progress-bar bg-secondary" role="progressbar"
                 style="width:{{ $pct($gPending) }}%; font-size:11px;"
                 title="Sin iniciar: {{ $gPending }}">
                @if($pct($gPending) >= 8) {{ $pct($gPending) }}% @endif
            </div>
            @endif

            @if($gVoting > 0)
            <div class="progress-bar bg-primary" role="progressbar"
                 style="width:{{ $pct($gVoting) }}%; font-size:11px;"
                 title="Votación: {{ $gVoting }}">
                @if($pct($gVoting) >= 8) {{ $pct($gVoting) }}% @endif
            </div>
            @endif

            @if($gCounting > 0)
            <div class="progress-bar bg-warning text-dark" role="progressbar"
                 style="width:{{ $pct($gCounting) }}%; font-size:11px;"
                 title="Escrutinio: {{ $gCounting }}">
                @if($pct($gCounting) >= 8) {{ $pct($gCounting) }}% @endif
            </div>
            @endif

            @if($gObserved > 0)
            <div class="progress-bar bg-danger" role="progressbar"
                 style="width:{{ $pct($gObserved) }}%; font-size:11px;"
                 title="Observadas: {{ $gObserved }}">
                @if($pct($gObserved) >= 8) {{ $pct($gObserved) }}% @endif
            </div>
            @endif

            @if($gDone > 0)
            <div class="progress-bar bg-success" role="progressbar"
                 style="width:{{ $pct($gDone) }}%; font-size:11px;"
                 title="Completadas: {{ $gDone }}">
                @if($pct($gDone) >= 8) {{ $pct($gDone) }}% @endif
            </div>
            @endif

            @if($gAnnulled > 0)
            <div class="progress-bar bg-dark" role="progressbar"
                 style="width:{{ $pct($gAnnulled) }}%; font-size:11px;"
                 title="Anuladas: {{ $gAnnulled }}">
                @if($pct($gAnnulled) >= 8) {{ $pct($gAnnulled) }}% @endif
            </div>
            @endif

        </div>
        <div class="d-flex flex-wrap gap-3 mt-1 small text-muted">
            <span><span class="badge bg-secondary">&nbsp;</span> Sin iniciar {{ $gPending }}</span>
            <span><span class="badge bg-primary">&nbsp;</span> Votación {{ $gVoting }}</span>
            <span><span class="badge bg-warning text-dark">&nbsp;</span> Escrutinio {{ $gCounting }}</span>
            <span><span class="badge bg-danger">&nbsp;</span> Observadas {{ $gObserved }}</span>
            <span><span class="badge bg-success">&nbsp;</span> Completadas {{ $gDone }}</span>
            @if($gAnnulled > 0)
            <span><span class="badge bg-dark">&nbsp;</span> Anuladas {{ $gAnnulled }}</span>
            @endif
        </div>
    </div>
</div>
@endif
<style>
.stat-badge { transition: transform .15s, opacity .15s; }
.stat-badge:hover { transform: translateY(-1px); opacity: .85; }
</style>
@endif
