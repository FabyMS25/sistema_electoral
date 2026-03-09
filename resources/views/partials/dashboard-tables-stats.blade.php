{{-- resources/views/partials/dashboard-tables-stats.blade.php --}}
<div class="row g-3 mt-1">
    <div class="col-md-4">
        <div class="card border h-100 shadow-none text-center">
            <div class="card-body">
                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-1 d-block mx-auto mb-3"
                      style="width:64px;height:64px;display:inline-flex;align-items:center;justify-content:center;">
                    <i class="ri-table-line"></i>
                </span>
                <h3 class="mb-1" id="ds-stat-total">{{ number_format($totalTables) }}</h3>
                <p class="text-muted mb-0">Total de Mesas</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border h-100 shadow-none text-center">
            <div class="card-body">
                <span class="avatar-title bg-success-subtle text-success rounded-circle fs-1 d-block mx-auto mb-3"
                      style="width:64px;height:64px;display:inline-flex;align-items:center;justify-content:center;">
                    <i class="ri-checkbox-circle-line"></i>
                </span>
                <h3 class="mb-1" id="ds-stat-reported">{{ number_format($reportedTables) }}</h3>
                <p class="text-muted mb-0">Mesas Reportadas</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border h-100 shadow-none text-center">
            <div class="card-body">
                <span class="avatar-title bg-warning-subtle text-warning rounded-circle fs-1 d-block mx-auto mb-3"
                      style="width:64px;height:64px;display:inline-flex;align-items:center;justify-content:center;">
                    <i class="ri-time-line"></i>
                </span>
                <h3 class="mb-1" id="ds-stat-pending">{{ number_format($totalTables - $reportedTables) }}</h3>
                <p class="text-muted mb-0">Mesas Pendientes</p>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Progreso de Escrutinio</h5>
    </div>
    <div class="card-body">
        <div class="d-flex align-items-center gap-4 mb-3">
            <div class="text-center" style="min-width:80px;">
                <div class="fs-1 fw-bold text-primary" id="ds-stat-pct">{{ $progressPercentage }}</div>
                <small class="text-muted">% Completado</small>
            </div>
            <div class="flex-grow-1">
                <div class="progress" style="height:22px; border-radius:6px;">
                    <div class="progress-bar bg-success progress-bar-striped progress-bar-animated"
                         id="ds-big-bar"
                         role="progressbar"
                         style="width:{{ $progressPercentage }}%; font-size:13px; font-weight:600;">
                        {{ $progressPercentage > 8 ? $progressPercentage.'%' : '' }}
                    </div>
                </div>
            </div>
        </div>
        <div class="row text-center border-top pt-3">
            <div class="col-4">
                <h5 class="mb-0" id="ds-stat-reported2">{{ $reportedTables }}</h5>
                <small class="text-muted">Computadas</small>
            </div>
            <div class="col-4 border-start border-end">
                <h5 class="mb-0" id="ds-stat-pending2">{{-- resources/views/partials/dashboard-tables-stats.blade.php --}}
{{-- Compact single card: progress bar + three counters in one row --}}
<div class="card mt-3 border-0 shadow-sm">
    <div class="card-body py-3 px-4">
        <div class="row align-items-center g-3">

            {{-- Label + % + bar --}}
            <div class="col-lg-7">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <span class="text-uppercase fw-semibold text-muted" style="font-size:.68rem;letter-spacing:.05em;white-space:nowrap;">
                        <i class="ri-bar-chart-grouped-line me-1"></i>Escrutinio de Mesas
                    </span>
                    <span class="ms-auto fw-bold text-success" style="font-size:1.1rem;" id="ds-stat-pct">{{ $progressPercentage }}</span>
                    <span class="text-muted small">%</span>
                </div>
                <div class="progress" style="height:8px;border-radius:8px;background:#e9ecef;">
                    <div class="progress-bar bg-success progress-bar-striped progress-bar-animated"
                         id="ds-big-bar"
                         role="progressbar"
                         style="width:{{ $progressPercentage }}%;border-radius:8px;transition:width .6s ease;">
                    </div>
                </div>
            </div>

            {{-- Three counters --}}
            <div class="col-lg-5">
                <div class="row g-0 text-center">
                    <div class="col-4 border-end">
                        <div class="fw-bold fs-5 text-success lh-1" id="ds-stat-reported">
                            {{ number_format($reportedTables) }}
                        </div>
                        <div class="text-muted mt-1" style="font-size:.7rem;">Computadas</div>
                    </div>
                    <div class="col-4 border-end">
                        <div class="fw-bold fs-5 text-warning lh-1" id="ds-stat-pending">
                            {{ number_format($totalTables - $reportedTables) }}
                        </div>
                        <div class="text-muted mt-1" style="font-size:.7rem;">Pendientes</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold fs-5 text-secondary lh-1" id="ds-stat-total">
                            {{ number_format($totalTables) }}
                        </div>
                        <div class="text-muted mt-1" style="font-size:.7rem;">Habilitadas</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>{{ $totalTables - $reportedTables }}</h5>
                <small class="text-muted">Pendientes</small>
            </div>
            <div class="col-4">
                <h5 class="mb-0" id="ds-stat-total2">{{ $totalTables }}</h5>
                <small class="text-muted">Habilitadas</small>
            </div>
        </div>
    </div>
</div>
