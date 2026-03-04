{{-- resources/views/partials/dashboard-tables-stats.blade.php --}}
<div class="row">
    <div class="col-md-4">
        <div class="card border shadow-none">
            <div class="card-body text-center">
                <div class="avatar-md mx-auto mb-3">
                    <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-1">
                        <i class="ri-table-line"></i>
                    </span>
                </div>
                <h4 class="mb-1">{{ number_format($totalTables) }}</h4>
                <p class="text-muted mb-0">Total de Mesas</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border shadow-none">
            <div class="card-body text-center">
                <div class="avatar-md mx-auto mb-3">
                    <span class="avatar-title bg-success-subtle text-success rounded-circle fs-1">
                        <i class="ri-checkbox-circle-line"></i>
                    </span>
                </div>
                <h4 class="mb-1">{{ number_format($reportedTables) }}</h4>
                <p class="text-muted mb-0">Mesas Reportadas</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border shadow-none">
            <div class="card-body text-center">
                <div class="avatar-md mx-auto mb-3">
                    <span class="avatar-title bg-warning-subtle text-warning rounded-circle fs-1">
                        <i class="ri-time-line"></i>
                    </span>
                </div>
                <h4 class="mb-1">{{ number_format($totalTables - $reportedTables) }}</h4>
                <p class="text-muted mb-0">Mesas Pendientes</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Progreso de Escrutinio</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div>
                        <div class="fs-1 fw-bold text-primary">{{ $progressPercentage }}%</div>
                        <span class="text-muted">Completado</span>
                    </div>
                    <div class="flex-grow-1">
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                 role="progressbar"
                                 style="width: {{ $progressPercentage }}%">
                                {{ $progressPercentage }}%
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row text-center">
                    <div class="col-4">
                        <div class="p-3">
                            <h5 class="mb-1">{{ $reportedTables }}</h5>
                            <small class="text-muted">Mesas Computadas</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 border-start border-end">
                            <h5 class="mb-1">{{ $totalTables - $reportedTables }}</h5>
                            <small class="text-muted">Mesas Pendientes</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3">
                            <h5 class="mb-1">{{ $totalTables }}</h5>
                            <small class="text-muted">Total Habilitadas</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
