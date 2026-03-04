{{-- resources/views/partials/dashboard-content.blade.php --}}
@include('partials.dashboard-filters')
<div id="loading-indicator" style="display: none; position: fixed; top: 20px; right: 20px; z-index: 9999; background: #fff; padding: 10px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.2);">
    <div class="d-flex align-items-center">
        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <span>Actualizando datos...</span>
    </div>
</div>
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Votos Totales</p>
                    </div>
                    <div class="flex-shrink-0">
                        <h5 class="text-success fs-14 mb-0">
                            <i class="ri-arrow-right-up-line fs-13 align-middle"></i> En vivo
                        </h5>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="flex-grow-1">
                            <span class="counter-value" data-target="{{ $totalVotes }}">0</span>
                        </h4>
                        <p class="text-muted text-truncate">Total de votos emitidos</p>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-primary-subtle text-primary rounded-2 fs-2">
                            <i data-feather="archive" class="text-primary"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Mesas Reportadas</p>
                    </div>
                    <div class="flex-shrink-0">
                        <h5 class="text-info fs-14 mb-0">{{ $progressPercentage }}%</h5>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="flex-grow-1">
                            <span class="counter-value" data-target="{{ $reportedTables }}">0</span>/
                            <span class="text-muted fs-14">{{ $totalTables }}</span>
                        </h4>
                        <div class="progress progress-sm mb-2 mt-2" style="height: 5px;">
                            <div class="progress-bar bg-info" role="progressbar"
                                             style="width: {{ $progressPercentage }}%"></div>
                        </div>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-info-subtle text-info rounded-2 fs-2">
                            <i class="bx bx-table text-info"></i>
                        </span>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Tipo: {{ $selectedElectionType ? $selectedElectionType->name : 'N/A' }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Candidato Líder</p>
                    </div>
                    <div class="flex-shrink-0">
                        <h5 class="text-success fs-14 mb-0">
                            <i class="ri-arrow-right-up-line fs-13 align-middle"></i> #1
                        </h5>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div class="leading-candidate">
                    @if(count($candidateStats) > 0)
                        @php
                            $leadingCandidate = collect($candidateStats)->sortByDesc('votes')->first();
                        @endphp
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-2">
                            @if($leadingCandidate['candidate']->photo)
                                <img src="{{ asset('storage/' . $leadingCandidate['candidate']->photo) }}"
                                    alt="{{ $leadingCandidate['candidate']->name }}"
                                    class="rounded-circle border border-3 border-white shadow-sm"
                                    style="width:55px;height:55px;object-fit:cover;">
                            @else
                                <div class="avatar-lg">
                                    <span class="avatar-title bg-primary rounded-circle text-white fs-4">
                                        {{ substr($leadingCandidate['candidate']->name, 0, 1) }}
                                    </span>
                                </div>
                            @endif
                            </div>
                            <div>
                                <h4 class="fs-16 fw-semibold ff-secondary mb-1">
                                    {{ $leadingCandidate['candidate']->name ?? 'N/A' }}
                                </h4>
                                <p class="text-muted total-voted mb-0">
                                    {{ number_format($leadingCandidate['votes']) }} votos
                                    ({{ $leadingCandidate['percentage'] }}%)
                                </p>
                            </div>
                        </div>
                    @else
                        <h4 class="fs-20 fw-semibold ff-secondary mb-4">Sin votos aún</h4>
                    @endif
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-success-subtle rounded fs-3">
                            <i class="bx bx-trophy text-success"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Votos por mesa</p>
                    </div>
                    <div class="flex-shrink-0">
                        <h5 class="text-warning fs-14 mb-0">
                            {{ $reportedTables > 0 ? round($totalVotes / $reportedTables, 1) : 0 }}
                        </h5>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="flex-grow-1">
                            <span class="counter-value" data-target="{{ $reportedTables > 0 ? number_format($totalVotes / $reportedTables, 1) : 0 }}">0</span>
                        </h4>
                        <p class="text-muted text-truncate">Votos por mesa</p>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-warning-subtle rounded fs-3">
                            <i class="bx bx-stats text-warning"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Resultados por Candidato - {{ $selectedElectionType ? $selectedElectionType->name : '' }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Posición</th>
                                <th>Candidato</th>
                                <th>Partido</th>
                                <th>Votos</th>
                                <th>Porcentaje</th>
                                <th>Progreso</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $sortedStats = collect($candidateStats)->sortByDesc('votes')->values(); @endphp
                            @foreach($sortedStats as $index => $stats)
                            @php $candidate = $stats['candidate']; @endphp
                                <tr>
                                    <td><span class="badge bg-{{ $index == 0 ? 'success' : ($index == 1 ? 'info' : ($index == 2 ? 'warning' : 'primary')) }}">#{{ $index + 1 }}</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($candidate->photo)
                                                <img src="{{ asset('storage/' . $candidate->photo) }}"
                                                     alt="{{ $candidate->name }}"
                                                     class="rounded-circle avatar-xs me-2">
                                            @else
                                                <div class="avatar-xs me-2">
                                                    <span class="avatar-title rounded-circle bg-{{ $index == 0 ? 'success' : ($index == 1 ? 'info' : ($index == 2 ? 'warning' : 'primary')) }}">
                                                        {{ substr($candidate->name, 0, 1) }}
                                                    </span>
                                                </div>
                                            @endif
                                            <span>{{ $candidate->name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $candidate->party }}</td>
                                    <td>{{ number_format($stats['votes']) }}</td>
                                    <td>{{ $stats['percentage'] }}%</td>
                                    <td style="width: 150px;">
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar bg-{{ $index == 0 ? 'success' : ($index == 1 ? 'info' : ($index == 2 ? 'warning' : 'primary')) }}"
                                                 role="progressbar"
                                                 style="width: {{ $stats['percentage'] }}%;"
                                                 aria-valuenow="{{ $stats['percentage'] }}"
                                                 aria-valuemin="0"
                                                 aria-valuemax="100"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Estado de Mesas</h5>
            </div>
            <div class="card-body table-status">
                <div class="d-flex justify-content-around text-center">
                    <div class="total-tables">
                        <h4 class="text-primary total-tables-count">{{ $totalTables }}</h4>
                        <p class="text-muted mb-0">Total de Mesas</p>
                    </div>
                    <div class="reported-tables">
                        <h4 class="text-success reported-tables-count">{{ $reportedTables }}</h4>
                        <p class="text-muted mb-0">Mesas Reportadas</p>
                    </div>
                    <div class="pending-tables">
                        <h4 class="text-warning pending-tables-count">{{ $totalTables - $reportedTables }}</h4>
                        <p class="text-muted mb-0">Mesas Pendientes</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Progreso General</h5>
            </div>
            <div class="card-body progress-general">
                <div class="progress mb-3" style="height: 20px;">
                    <div class="progress-bar bg-success general-progress-bar" role="progressbar"
                         style="width: {{ $progressPercentage }}%"
                         aria-valuenow="{{ $progressPercentage }}"
                         aria-valuemin="0"
                         aria-valuemax="100">
                        {{ $progressPercentage }}%
                    </div>
                </div>
                <p class="text-muted mb-0 text-center progress-text">
                    {{ $reportedTables }} de {{ $totalTables }} mesas reportadas
                </p>
            </div>
        </div>
    </div>
</div>
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h5 class="card-title mb-0 flex-grow-1">Resultados Detallados por Localidad</h5>
                <div class="flex-shrink-0">
                    <button class="btn btn-sm btn-primary" id="exportLocalityTable">
                        <i class="ri-download-line align-middle"></i> Exportar Reporte
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="locality-table">
                        <thead class="table-light">
                            <tr>
                                <th>Localidad</th>
                                <th>Municipio</th>
                                <th>Mesas</th>
                                <th>Reportadas</th>
                                <th>Avance</th>
                                <th>Votos Totales</th>
                                @foreach($candidates as $candidate)
                                <th>{{ $candidate->name }} ({{ $candidate->party }})</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($localityStats as $locality)
                            @php
                                $localityData = $localityResults[$locality->id] ?? null;
                                $progress = $locality->total_tables > 0
                                    ? round(($locality->reported_tables / $locality->total_tables) * 100, 1)
                                    : 0;
                            @endphp
                            <tr>
                                <td><strong>{{ $locality->name }}</strong></td>
                                <td>{{ $locality->municipality_name }}</td>
                                <td>{{ $locality->total_tables }}</td>
                                <td>{{ $locality->reported_tables }}</td>
                                <td>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar" role="progressbar"
                                            style="width: {{ $progress }}%;"
                                            aria-valuenow="{{ $progress }}"
                                            aria-valuemin="0"
                                            aria-valuemax="100"></div>
                                    </div>
                                    <small>{{ $progress }}%</small>
                                </td>
                                <td><strong>{{ $localityData['total_votes'] ?? 0 }}</strong></td>
                                @foreach($candidates as $candidate)
                                @php
                                    $candidateVotes = 0;
                                    $candidatePercentage = 0;
                                    if ($localityData && isset($localityData['candidates'])) {
                                        foreach ($localityData['candidates'] as $cand) {
                                            if ($cand['id'] == $candidate->id) {
                                                $candidateVotes = $cand['votes'];
                                                $candidatePercentage = $cand['percentage'];
                                                break;
                                            }
                                        }
                                    }
                                @endphp
                                <td>
                                    {{ number_format($candidateVotes) }}<br>
                                    <small class="text-muted">{{ $candidatePercentage }}%</small>
                                </td>
                                @endforeach
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ 6 + count($candidates) }}" class="text-center text-muted py-4">
                                    No hay datos disponibles
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row mt-4">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header border-0 align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Resultados por Candidato</h4>
                <div>
                    <button type="button" class="btn btn-soft-secondary btn-sm" onclick="window.location.reload()">
                        Actualizar
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="candidates_chart"></div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card card-height-100">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Distribución por Partido</h4>
                <div class="flex-shrink-0">
                    <button type="button" class="btn btn-soft-primary btn-sm" id="exportPartyData">
                        Exportar
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="party_distribution_chart" style="height: 300px;"></div>
            </div>
        </div>
    </div>
</div>
<div class="row mt-4">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header border-0 align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Resultados por Localidad</h4>
                <div>
                    <button type="button" class="btn btn-soft-secondary btn-sm" onclick="filterLocality('all')">Todos</button>
                    @foreach($localityStats->take(5) as $locality)
                    <button type="button" class="btn btn-soft-secondary btn-sm"
                            onclick="filterLocality('{{ $locality->id }}')">
                        {{ $locality->name }}
                    </button>
                    @endforeach
                </div>
            </div>
            <div class="card-header p-0 border-0 bg-light-subtle">
                <div class="row g-0 text-center">
                    <div class="col-6 col-sm-3">
                        <div class="p-3 border border-dashed border-start-0">
                            <h5 class="mb-1"><span class="counter-value total-votes-counter" data-target="{{ $totalVotes }}">{{ $totalVotes }}</span></h5>
                            <p class="text-muted mb-0">Total de Votos</p>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="p-3 border border-dashed border-start-0">
                            <h5 class="mb-1"><span class="counter-value total-tables-counter" data-target="{{ $totalTables }}">{{ $totalTables }}</span></h5>
                            <p class="text-muted mb-0">Mesas Totales</p>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="p-3 border border-dashed border-start-0">
                            <h5 class="mb-1"><span class="counter-value reported-tables-counter" data-target="{{ $reportedTables }}">{{ $reportedTables }}</span></h5>
                            <p class="text-muted mb-0">Mesas Reportadas</p>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="p-3 border border-dashed border-start-0 border-end-0">
                            <h5 class="mb-1 text-success"><span class="counter-value progress-counter" data-target="{{ $progressPercentage }}">{{ $progressPercentage }}</span>%</h5>
                            <p class="text-muted mb-0">Avance</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0 pb-2">
                <div id="projects-overview-chart" style="height: 350px;"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card card-height-100">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Votos por Localidades</h4>
                <div class="flex-shrink-0">
                    <button type="button" class="btn btn-soft-primary btn-sm" id="exportMapData">
                        Exportar
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div style="height: 269px; position: relative;">
                    <div id="votes-by-locations"
                        data-colors='["#e9e9ef", "#0ab39c", "#f06548"]'
                        style="height: 100%; width: 100%;"></div>
                </div>
                <div class="px-2 py-2 mt-1 locality-progress-container" style="max-height: 200px; overflow-y: auto;">
                    @foreach($localityStats as $locality)
                    @php
                        $progress = $locality->total_tables > 0
                            ? round(($locality->reported_tables / $locality->total_tables) * 100, 1)
                            : 0;
                    @endphp
                    <div class="locality-progress-item mb-2" data-locality-id="{{ $locality->id }}">
                        <div class="d-flex justify-content-between">
                            <p class="mb-1 small">{{ $locality->name }} ({{ $locality->municipality_name }})</p>
                            <span class="small fw-bold">{{ $progress }}%</span>
                        </div>
                        <div class="progress" style="height: 4px;">
                            <div class="progress-bar bg-primary" role="progressbar"
                                style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}"
                                aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
<div class="auto-refresh-controls" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000; background: white; padding: 10px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <div class="btn-group btn-group-sm">
        <button class="btn btn-outline-primary" onclick="refreshDashboard()" title="Actualizar ahora">
            <i class="ri-refresh-line"></i>
        </button>
        <button class="btn btn-outline-success" onclick="startAutoRefresh()" title="Iniciar auto-actualización">
            <i class="ri-play-line"></i>
        </button>
        <button class="btn btn-outline-secondary" onclick="stopAutoRefresh()" title="Pausar auto-actualización">
            <i class="ri-pause-line"></i>
        </button>
    </div>
    <div class="mt-1 text-center">
        <small class="text-muted">Auto: 2 min</small>
    </div>
    <div id="refresh-status" class="mt-1 text-center">
        <small class="text-success">● Activo</small>
    </div>
</div>
@section('dashboard-scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.2/dist/apexcharts.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/js/jsvectormap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/maps/world.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof ApexCharts === 'undefined') {
        console.error('❌ ApexCharts no está cargado');
        return;
    }
    let refreshInterval = 120000;
    let refreshTimer = null;
    let isRefreshing = false;
    let charts = {};
    initializeCharts();
    startAutoRefresh();
    document.getElementById('exportLocalityTable')?.addEventListener('click', function() {
        exportTableToCSV('locality-table', 'resultados_localidades.csv');
    });
    function initializeCharts() {
        try {
            const candidateStats = @json($candidateStats ?? []);
            if (Object.keys(candidateStats).length === 0) {
                console.warn('⚠️ No hay datos de candidatos para mostrar');
                return;
            }
            const sortedStats = Object.values(candidateStats).sort((a, b) => b.votes - a.votes);
            const candidateNames = sortedStats.map(stat => {
                const name = stat.candidate?.name || 'Sin nombre';
                return name.length > 20 ? name.substring(0, 18) + '...' : name;
            });
            const candidateColors = sortedStats.map(stat => stat.candidate?.color || '#3b5de7');
            const candidateVotes = sortedStats.map(stat => stat.votes || 0);
            const barContainer = document.querySelector("#candidates_chart");
            if (barContainer) {
                const barOptions = {
                    series: [{ name: 'Votos', data: candidateVotes }],
                    chart: { type: 'bar', height: 350, toolbar: { show: true } },
                    plotOptions: { bar: { distributed: true, borderRadius: 4 } },
                    xaxis: {
                        categories: candidateNames,
                        labels: { rotate: -45, trim: true, style: { fontSize: '11px' } }
                    },
                    colors: candidateColors,
                    tooltip: { y: { formatter: val => val.toLocaleString() + ' votos' } },
                    legend: { show: false }
                };
                charts.candidateChart = new ApexCharts(barContainer, barOptions);
                charts.candidateChart.render();
            }
            const donutContainer = document.querySelector("#party_distribution_chart");
            if (donutContainer && candidateVotes.length > 0) {
                const donutOptions = {
                    series: candidateVotes,
                    labels: candidateNames,
                    colors: candidateColors,
                    chart: { type: 'donut', height: 300 },
                    legend: { position: 'bottom', fontSize: '11px' },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '60%',
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        label: 'Total',
                                        formatter: (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString()
                                    }
                                }
                            }
                        }
                    },
                    tooltip: { y: { formatter: val => val.toLocaleString() + ' votos' } }
                };
                charts.partyChart = new ApexCharts(donutContainer, donutOptions);
                charts.partyChart.render();
            }
            const localityContainer = document.querySelector("#projects-overview-chart");
            const localityResults = @json($localityResults ?? []);
            const localities = Object.values(localityResults).map(l => l.name);

            if (localityContainer && localities.length > 0 && candidateNames.length > 0) {
                const series = candidateNames.map((name, index) => {
                    return {
                        name: name,
                        type: 'bar',
                        data: Object.values(localityResults).map(l => {
                            const candidate = (l.candidates || []).find(c => c.name === name);
                            return candidate ? candidate.votes : 0;
                        })
                    };
                });

                const localityOptions = {
                    series: series,
                    chart: { type: 'bar', height: 350, stacked: false, toolbar: { show: true } },
                    xaxis: {
                        categories: localities,
                        labels: { rotate: -45, trim: true, style: { fontSize: '11px' } }
                    },
                    colors: candidateColors,
                    legend: { position: 'bottom', horizontalAlign: 'center' },
                    tooltip: { shared: true, intersect: false },
                    plotOptions: { bar: { columnWidth: '70%' } }
                };
                charts.localityChart = new ApexCharts(localityContainer, localityOptions);
                charts.localityChart.render();
            }
            initializeMap(localityResults);
        } catch (error) {
            console.error('❌ Error creando gráficos:', error);
        }
    }
    function initializeMap(localityResults) {
        const mapContainer = document.getElementById("votes-by-locations");
        if (!mapContainer || !localityResults || Object.keys(localityResults).length === 0) return;
        try {
            const markers = Object.values(localityResults).map(l => {
                return {
                    name: l.name + " (" + (l.total_votes || 0) + " votos)",
                    coords: [l.latitude || -17.4, l.longitude || -66.2],
                    votes: l.total_votes || 0,
                    candidates: l.candidates || []
                };
            });
            if (typeof jsVectorMap !== 'undefined') {
                mapContainer.innerHTML = "";
                charts.boliviaMap = new jsVectorMap({
                    map: "world",
                    selector: "#votes-by-locations",
                    zoomOnScroll: true,
                    zoomButtons: true,
                    markers: markers,
                    markerStyle: {
                        initial: { fill: '#0ab39c' },
                        hover: { fill: '#f06548' },
                        selected: { fill: '#f06548' }
                    },
                    labels: {
                        markers: {
                            render: function(marker) {
                                return marker.name;
                            }
                        }
                    },
                    onMarkerClick: function(event, index) {
                        const m = markers[index];
                        showMarkerPopup(m);
                    }
                });
            }
        } catch (error) {
            console.error('❌ Error creando mapa:', error);
        }
    }
    function showMarkerPopup(marker) {
        const popup = document.createElement('div');
        popup.className = 'custom-map-popup';
        let candidatesHtml = '';
        if (marker.candidates && marker.candidates.length > 0) {
            candidatesHtml = marker.candidates.map(c => `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    ${c.name} (${c.party})
                    <span class="badge bg-primary rounded-pill">
                        ${c.votes?.toLocaleString() || 0} (${c.percentage || 0}%)
                    </span>
                </li>
            `).join('');
        } else {
            candidatesHtml = '<li class="list-group-item">No hay datos</li>';
        }
        popup.innerHTML = `
            <div class="popup-header">
                <h5>${marker.name}</h5>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="popup-body">
                <p><strong>Total votos:</strong> ${marker.votes?.toLocaleString() || 0}</p>
                <h6>Resultados:</h6>
                <ul class="list-group">
                    ${candidatesHtml}
                </ul>
            </div>
        `;
        if (!document.querySelector('#map-popup-styles')) {
            const styles = document.createElement('style');
            styles.id = 'map-popup-styles';
            styles.textContent = `
                .custom-map-popup {
                    position: fixed;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background: white;
                    border-radius: 8px;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                    z-index: 10000;
                    width: 320px;
                    max-width: 90vw;
                }
                .popup-header {
                    padding: 12px 16px;
                    border-bottom: 1px solid #dee2e6;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .popup-body {
                    padding: 16px;
                    max-height: 60vh;
                    overflow-y: auto;
                }
            `;
            document.head.appendChild(styles);
        }
        popup.querySelector('.btn-close').addEventListener('click', function() {
            document.body.removeChild(popup);
        });
        document.body.appendChild(popup);
    }
    function exportTableToCSV(tableId, filename) {
        const table = document.getElementById(tableId);
        if (!table) return;
        let csv = [];
        let rows = table.querySelectorAll('tr');

        for (let i = 0; i < rows.length; i++) {
            let row = [], cols = rows[i].querySelectorAll('td, th');
            for (let j = 0; j < cols.length; j++) {
                let data = cols[j].innerText.replace(/\n/g, ' ').replace(/"/g, '""');
                row.push('"' + data + '"');
            }
            csv.push(row.join(','));
        }
        let csvContent = csv.join('\n');
        let blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        let link = document.createElement('a');
        let url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    window.filterLocality = function(localityId) {
        if (!charts.localityChart) return;
        console.log('Filtrar por localidad:', localityId);
    };
    function refreshDashboard() {
        if (isRefreshing) return;
        isRefreshing = true;
        showLoadingIndicator();
        const electionType = document.querySelector('select[name="election_type"]')?.value || '';
        const url = `/refresh-dashboard?election_type=${electionType}`;
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
                isRefreshing = false;
                hideLoadingIndicator();
            })
            .catch(error => {
                console.error('Error:', error);
                isRefreshing = false;
                hideLoadingIndicator();
            });
    }
    function startAutoRefresh() {
        if (refreshTimer) clearInterval(refreshTimer);
        refreshTimer = setInterval(refreshDashboard, refreshInterval);
        document.getElementById('refresh-status').innerHTML = '<small class="text-success">● Activo</small>';
    }
    function stopAutoRefresh() {
        if (refreshTimer) {
            clearInterval(refreshTimer);
            refreshTimer = null;
        }
        document.getElementById('refresh-status').innerHTML = '<small class="text-secondary">○ Pausado</small>';
    }
    function showLoadingIndicator() {
        document.getElementById('loading-indicator').style.display = 'block';
    }
    function hideLoadingIndicator() {
        document.getElementById('loading-indicator').style.display = 'none';
    }
    window.refreshDashboard = refreshDashboard;
    window.startAutoRefresh = startAutoRefresh;
    window.stopAutoRefresh = stopAutoRefresh;
});
</script>
@endsection
