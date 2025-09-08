
<div class="row mb-2">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row g-3">
                    <div class="col-md-8 d-flex gap-3">
                        <h5 class="card-title mb-0 mt-2">Tipo de Elección: </h5>
                        <form method="GET" action="{{ url()->current() }}">
                            <div class="row">
                            <div class="col-md-8">
                                <select name="election_type" class="form-select" onchange="this.form.submit()">
                                    @foreach($electionTypes as $electionType)
                                        <option value="{{ $electionType->id }}" 
                                            {{ $selectedElectionType && $selectedElectionType->id == $electionType->id ? 'selected' : '' }}>
                                            {{ $electionType->name }} ({{ $electionType->type }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">Filtrar Resultados</button>
                            </div>
                            </div>
                        </form>
                    </div>             
                    <div class="col-md-4">
                        <div class="d-flex justify-content-end align-items-center">
                            <button class="btn btn-sm btn-outline-primary" onclick="refreshDashboard()">
                                <i class="ri-refresh-line"></i> Actualizar ahora
                            </button>
                            <div class="ms-2">
                                <small class="text-muted" id="last-update-time">
                                    Última actualización: {{ now()->format('H:i:s') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ url()->current() }}" class="row g-3 p-1" id="locationFilterForm">
                    <input type="hidden" name="election_type" value="{{ $selectedElectionType ? $selectedElectionType->id : '' }}">
                    <div class="col-md-3">
                        <label for="department" class="form-label mb-0">Departamento</label>
                        <select name="department" id="department" class="form-select" onchange="updateProvinces()">
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" 
                                    {{ $selectedDepartment == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>                    
                    <div class="col-md-3">
                        <label for="province" class="form-label mb-0">Provincia</label>
                        <select name="province" id="province" class="form-select" onchange="updateMunicipalities()">
                            @foreach($provinces as $province)
                                <option value="{{ $province->id }}" 
                                    {{ $selectedProvince == $province->id ? 'selected' : '' }}>
                                    {{ $province->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>                    
                    <div class="col-md-3">
                        <label for="municipality" class="form-label mb-0">Municipio</label>
                        <select name="municipality" id="municipality" class="form-select" onchange="this.form.submit()">
                            @foreach($municipalities as $municipality)
                                <option value="{{ $municipality->id }}" 
                                    {{ $selectedMunicipality == $municipality->id ? 'selected' : '' }}>
                                    {{ $municipality->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>                    
                    <div class="col-md-3 mt-1">
                        <label class="form-label d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Filtrar Resultados</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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
                            <span class="counter-value" data-target="{{ $totalVotes }}">
                                0</span>
                        </h4>
                        <p class="text-muted text-truncate">Total de votos emitidos</sppan>
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
                        <h5 class="text-info fs-14 mb-0">
                            {{ $progressPercentage}}%
                        </h5>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="flex-grow-1">
                            <span class="counter-value" data-target="{{ $reportedTables }}">
                                0</span>/
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
                            $leadingCandidate = reset($candidateStats);
                        @endphp
                        <div class=d-flex>
                            <div class="flex-grow-1">
                            @if($leadingCandidate['candidate']->photo)
                                <img src="{{ asset('storage/' . $leadingCandidate['candidate']->photo) }}" 
                                    alt="{{ $leadingCandidate['candidate']->name }}" 
                                    class="img-fluid rounded-circle border border-3 border-white shadow-sm"
                                    style="width:55px;height:55px;object-fit:cover;">
                            @else
                                <lord-icon src="https://cdn.lordicon.com/dxjqoygy.json" trigger="loop"
                                    colors="primary:#405189,secondary:#0ab39c" style="width:55px;height:5555px"> </lord-icon>
                            @endif
                            </div>
                            <h4 class="fs-16 fw-semibold ff-secondary mb-2">
                                {{ $leadingCandidate['candidate']->name ?? 'N/A' }}
                            </h4>
                        </div>
                        <p class="text-muted total-voted mb-0">
                            {{ number_format($leadingCandidate['votes']) }} votos
                            ({{ $leadingCandidate['percentage'] }}%)
                        </p>
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
                        <p class="text-muted text-truncate">Votos por mesa</sppan>
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
<!-- Tabla de Resultados por Candidato -->
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
                            @foreach($candidateStats as $candidateId => $stats)
                            @php $candidate = $stats['candidate']; @endphp
                                <tr>
                                    <td><span class="badge bg-primary">{{ $stats['rank'] }}</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($candidate->photo)
                                                <img src="{{ asset('storage/' . $candidate->photo) }}" 
                                                     alt="{{ $candidate->name }}" 
                                                     class="rounded-circle avatar-xs me-2">
                                            @else
                                                <div class="avatar-xs me-2">
                                                    <span class="avatar-title rounded-circle bg-primary">
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
                                    <td>
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar" role="progressbar" 
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
<!-- Información Adicional -->
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
                                    if ($localityData) {
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
                                    {{ $candidateVotes }}<br>
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

<div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header border-0 align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Resultados por Candidato</h4>
                    <div>
                        <button type="button" class="btn btn-soft-secondary btn-sm">
                            TODOS
                        </button>
                        <button type="button" class="btn btn-soft-primary btn-sm">
                            PRINCIPALES
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
                        <button type="button" class="btn btn-soft-primary btn-sm">
                            Exportar
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="party_distribution_chart"  class="e-charts" style="height: 300px;"></div>
                </div>
            </div>
        </div>        
</div>

<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header border-0 align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Resultados por Localidad</h4>
                <div>
                    <button type="button" class="btn btn-soft-secondary btn-sm">Todos</button>
                    @foreach($localityStats as $locality)
                    <button type="button" class="btn btn-soft-secondary btn-sm" 
                            data-locality="{{ $locality->id }}">
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
                <div>
                    <div id="projects-overview-chart" dir="ltr" ></div>
                </div>
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
                        data-colors='["--vz-light", "--vz-success", "--vz-primary"]'
                        style="height: 100%; width: 100%;" 
                        dir="ltr"></div>
                </div>
                <div class="px-2 py-2 mt-1 locality-progress-container">
                    @foreach($localityStats as $locality)
                    @php
                        $localityData = $localityResults[$locality->id] ?? null;
                        $progress = $locality->total_tables > 0 
                        ? round(($locality->reported_tables / $locality->total_tables) * 100, 1)
                        : 0;
                    @endphp
                    <div class="locality-progress-item" data-locality-id="{{ $locality->id }}">
                        <p class="mb-1">{{ $locality->name }} ({{ $locality->municipality_name }})
                        <span class="float-end">{{ $progress }}%</span></p>                                    
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar progress-bar-striped bg-primary" role="progressbar" 
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
    <div class="mt-1">
        <small class="text-muted">Auto: 2 min</small>
    </div>
    <div id="refresh-status" class="mt-1">
        <small class="text-success">● Activo</small>
    </div>
</div>
@section('dashboard-scripts')
    <script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/jsvectormap/jsvectormap.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/jsvectormap/maps/quillacollo-merc.js') }}"></script>
    <script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script>      
        document.addEventListener('DOMContentLoaded', function() { 
            let refreshInterval = 120000;
            let refreshTimer = null;
            let isRefreshing = false;      
            let charts = {}; 
            initializeCharts();
            startAutoRefresh();               
            document.getElementById('exportLocalityTable').addEventListener('click', function() {
                const table = document.getElementById('locality-table');
                let csvContent = "data:text/csv;charset=utf-8,";
                const headers = [];
                table.querySelectorAll('thead th').forEach(header => {
                    headers.push(header.textContent.trim());
                });
                csvContent += headers.join(',') + '\r\n';
                table.querySelectorAll('tbody tr').forEach(row => {
                    const rowData = [];
                    row.querySelectorAll('td').forEach(cell => {
                        let text = cell.textContent.trim();
                        if (text.includes('\n')) {
                            text = text.split('\n').join('; ');
                        }
                        rowData.push('"' + text + '"');
                    });
                    csvContent += rowData.join(',') + '\r\n';
                });
                const encodedUri = encodeURI(csvContent);
                const link = document.createElement('a');
                link.setAttribute('href', encodedUri);
                link.setAttribute('download', 'locality_results.csv');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
    
            function initializeCharts(){
                const sortedStats = Object.values(@json($candidateStats)).sort((a, b) => b.votes - a.votes);
                const candidateNames = sortedStats.map(stat => stat.candidate.name);
                const candidateColors = sortedStats.map(stat => stat.candidate.color || '#3b5de7');
                const candidateVotes = sortedStats.map(stat => stat.votes);
                const candidatePercentages = @json($candidates->map(function($candidate) use ($candidateStats) {
                    return $candidateStats[$candidate->id]['percentage'] ?? 0;
                }));            
                const candidateParties = @json($candidates->pluck('party'));
                const candidatePhotos = @json($candidates->map(function($candidate) {
                    return $candidate->photo ? asset('storage/' . $candidate->photo) : '';
                }));
                var candidateOptions = {
                    series: [{
                        name: 'Votos',
                        data: candidateVotes
                    }],
                    chart: {
                        type: 'bar',
                        height: 350,
                        toolbar: {
                            show: true
                        }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            horizontal: false,
                            distributed: true,
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    xaxis: {
                        categories: candidateNames,
                        labels: {
                            style: {
                                fontSize: '12px'
                            }
                        }
                    },
                    colors: candidateColors,
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return val.toLocaleString() + " votos";
                            }
                        }
                    }
                };
                charts.candidateChart = new ApexCharts(document.querySelector("#candidates_chart"), candidateOptions);
                charts.candidateChart.render();                
                var partyOptions = {
                    tooltip: {
                        trigger: 'item'
                    },
                    series: candidateVotes,
                    labels: candidateNames,
                    colors:candidateColors,
                    chart: {
                        type: 'donut',
                        height: 300,
                    },
                    legend: {
                        position: 'bottom',
                        horizontalAlign: 'center',
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '50%',
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        label: 'Total Votos',
                                        formatter: function (w) {
                                            return w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString();
                                        }
                                    }
                                }
                            }
                        }
                    },
                    dataLabels: {
                        enabled: false,
                        formatter: function (val, opts) {
                            return opts.w.globals.labels[opts.seriesIndex] + ': ' + val.toFixed(1) + '%';
                        },
                        dropShadow: {
                            enabled: true
                        }
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 200
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
                };
                charts.partyChart = new ApexCharts(document.querySelector("#party_distribution_chart"), partyOptions);
                charts.partyChart.render();
                const localityResults = @json($localityResults);
                const localities = Object.values(localityResults).map(l => l.name);
                const series = candidateNames.map(name => {
                    return {
                        name: name,
                        type: 'bar',
                        data: Object.values(localityResults).map(l => {
                            const candidate = (l.candidates || []).find(c => c.name === name);
                            return candidate ? candidate.votes : 0;
                        })
                    };
                });
                var options = {
                        series: series,
                        chart: {
                            type: 'bar',
                            stacked: false 
                        },
                        stroke: {
                            curve: 'smooth',
                        },
                        // fill: {
                        //     opacity: [1, 0.1, 1]
                        // },
                        markers: {
                            size: [0, 4, 0],
                            strokeWidth: 2,
                            hover: {
                                size: 4,
                            }
                        },
                        xaxis: {
                            categories: localities,
                            axisTicks: {
                                show: false
                            },
                            axisBorder: {
                                show: false
                            }
                        },
                        grid: {
                            show: true,
                            xaxis: {
                                lines: {
                                    show: true,
                                }
                            },
                            yaxis: {
                                lines: {
                                    show: false,
                                }
                            },
                            padding: {
                                top: 0,
                                right: -2,
                                bottom: 15,
                                left: 10
                            },
                        },
                        legend: {
                            show: true,
                            horizontalAlign: 'center',
                            offsetX: 0,
                            offsetY: -5,
                            markers: {
                                width: 9,
                                height: 9,
                                radius: 6,
                            },
                            itemMargin: {
                                horizontal: 10,
                                vertical: 0
                            },
                        },
                        plotOptions: {
                            bar: {
                                columnWidth: '90%',
                                barHeight: '50%'
                            }
                        },
                        colors: candidateColors,
                        tooltip: {
                            shared: true,
                            intersect: false,
                            y: {
                                formatter: function (val) {
                                return val + " votos";
                                }
                            }
                        }
                };
                charts.localityChart = new ApexCharts(document.querySelector("#projects-overview-chart"), options);
                charts.localityChart.render();
                const markers = Object.values(localityResults).map(l => {
                    return {
                        name: l.name + " (" + l.total_votes + " votos)",
                        coords: [l.latitude, l.longitude],
                        votes: l.total_votes,
                        candidates: l.candidates
                    };
                });
                var vectorMapColors = getChartColorsArray("votes-by-locations");
                if(vectorMapColors){
                    document.getElementById("votes-by-locations").innerHTML = "";
                    charts.boliviaMap = new jsVectorMap({
                        map: "quillacollo_merc",
                        selector: "#votes-by-locations",
                        zoomOnScroll: true,
                        zoomButtons: true,

                        selectedMarkers: [0, 5],
                        regionStyle: {
                            initial: {
                                stroke: "#9599ad",
                                strokeWidth: 0.25,
                                fill: vectorMapColors[0],
                                fillOpacity: 1,
                            },
                        },
                        markersSelectable: true,

                        markers: markers,
                        markerStyle: {
                            initial: {
                                fill: vectorMapColors[1],
                            },
                            hover: {
                                fill: vectorMapColors[2],
                            },
                            selected: {
                                fill: vectorMapColors[2],
                            },
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
                            const popup = document.createElement('div');
                            popup.className = 'custom-map-popup';
                            popup.innerHTML = `
                                <div class="popup-header">
                                    <h5>${m.name}</h5>
                                    <button type="button" class="btn-close" aria-label="Close"></button>
                                </div>
                                <div class="popup-body">
                                    <p><strong>Total votes:</strong> ${m.votes.toLocaleString()}</p>
                                    <h6>Candidate Results:</h6>
                                    <ul class="list-group">
                                        ${m.candidates.map(c => `
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                ${c.name} (${c.party})
                                                <span class="badge bg-primary rounded-pill">
                                                    ${c.votes.toLocaleString()} (${c.percentage}%)
                                                </span>
                                            </li>
                                        `).join('')}
                                    </ul>
                                </div>
                            `;
                            if (!document.querySelector('#map-popup-styles')) {
                                const styles = document.createElement('style');
                                styles.id = 'map-popup-styles';
                                styles.textContent = `
                                    .custom-map-popup {
                                        position: absolute;
                                        top: 50%;
                                        left: 50%;
                                        transform: translate(-50%, -50%);
                                        background: white;
                                        border-radius: 8px;
                                        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                                        z-index: 1000;
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
                            popup.addEventListener('click', function(e) {
                                if (e.target === this) {
                                    document.body.removeChild(popup);
                                }
                            });
                        }
                    });
                }
            }
            function getChartColorsArray(chartId) {
                if (document.getElementById(chartId) !== null) {
                    const colorAttr = "data-colors" + ("-" + document.documentElement.getAttribute("data-theme") ?? "");
                    var colors = document.getElementById(chartId).getAttribute(colorAttr) ?? document.getElementById(chartId).getAttribute("data-colors");
                    if (colors) {
                        colors = JSON.parse(colors);
                        return colors.map(function (value) {
                            var newValue = value.replace(" ", "");
                            if (newValue.indexOf(",") === -1) {
                                var color = getComputedStyle(document.documentElement).getPropertyValue(newValue);
                                if (color) return color;
                                else return newValue;;
                            } else {
                                var val = value.split(',');
                                if (val.length == 2) {
                                    var rgbaColor = getComputedStyle(document.documentElement).getPropertyValue(val[0]);
                                    rgbaColor = "rgba(" + rgbaColor + "," + val[1] + ")";
                                    return rgbaColor;
                                } else {
                                    return newValue;
                                }
                            }
                        });
                    } else {
                        console.warn('data-colors attributes not found on', chartId);
                    }
                }
            }

            function startAutoRefresh() {
                if (refreshTimer) clearInterval(refreshTimer);
                refreshTimer = setInterval(refreshDashboard, refreshInterval);
            }
            function stopAutoRefresh() {
                if (refreshTimer) {
                    clearInterval(refreshTimer);
                    refreshTimer = null;
                }
            }    
            function refreshDashboard() {
                if (isRefreshing) return;                
                isRefreshing = true;
                showLoadingIndicator();
                const electionType = document.querySelector('select[name="election_type"]').value;
                const department = document.querySelector('select[name="department"]').value;
                const province = document.querySelector('select[name="province"]').value;
                const municipality = document.querySelector('select[name="municipality"]').value;
                fetch(`/refresh-dashboard?election_type=${electionType}&department=${department}&province=${province}&municipality=${municipality}`)
                    .then(response => {
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            updateDashboard(data.data);
                            updateLastUpdateTime();
                        } else {
                            console.error('Failed to fetch updated data');
                        }
                        isRefreshing = false;
                        hideLoadingIndicator();
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                        isRefreshing = false;
                        hideLoadingIndicator();
                    });
            }
            function updateDashboard(data) {
                updateCounter('.row > .col-xl-3:first-child .counter-value', data.totalVotes);
                updateCounter('.row > .col-xl-2 .counter-value', data.reportedTables);
                document.querySelector('.progress-bar.bg-info').style.width = data.progressPercentage + '%';
                document.querySelector('.text-info.fs-14').textContent = data.progressPercentage.toFixed(2) + '%';
                if (Object.keys(data.candidateStats).length > 0) {
                    const sortedCandidates = Object.values(data.candidateStats).sort((a, b) => b.votes - a.votes);
                    const leadingCandidate = sortedCandidates[0];
                    const leaderContainer = document.querySelector('.leading-candidate');        
                    if (leaderContainer) {
                        const nameElement = leaderContainer.querySelector('.fs-16.fw-semibold.ff-secondary');
                        if (nameElement) {
                            nameElement.textContent = leadingCandidate.candidate.name;
                        }
                        const votesElement = leaderContainer.querySelector('.total-voted');
                        if (votesElement) {
                            votesElement.innerHTML = `${leadingCandidate.votes.toLocaleString()} votos (${leadingCandidate.percentage.toFixed(1)}%)`;
                        }
                        const imgElement = leaderContainer.querySelector('img');
                        if (imgElement && leadingCandidate.candidate.photo) {
                            imgElement.src = `/storage/${leadingCandidate.candidate.photo}`;
                            imgElement.alt = leadingCandidate.candidate.name;
                        }
                        const lordIcon = leaderContainer.querySelector('lord-icon');
                        if (lordIcon) {
                            if (leadingCandidate.candidate.photo) {
                                lordIcon.style.display = 'none';
                                if (imgElement) imgElement.style.display = 'block';
                            } else {
                                lordIcon.style.display = 'block';
                                if (imgElement) imgElement.style.display = 'none';
                            }
                        }
                    }
                }    
                const votesPerTable = data.reportedTables > 0 ? (data.totalVotes / data.reportedTables).toFixed(1) : 0;
                updateCounter('.row > .col-xl-3:last-child .counter-value', votesPerTable);
                const votesPerTableLabel = document.querySelector('.row > .col-xl-3:last-child .text-warning.fs-14');
                if (votesPerTableLabel) {
                    votesPerTableLabel.textContent = votesPerTable ;
                }    
                updateTableStatus(data);
                updateProgressGeneral(data);
                
                updateLocalidadStats(data); 
                updateVotosPorLocalidades(data); 
                
                updateCandidateTable(data.candidateStats, data.candidates);
                updateLocalityTable(data.localityStats, data.localityResults, data.candidates);
                
                updateCharts(data);
                updateMap(data);
            }
            function updateCounter(selector, value) {
                const elements = document.querySelectorAll(selector);
                elements.forEach(element => {
                    element.textContent = typeof value === 'number' ? value.toLocaleString() : value;
                });
            }
            function updateProgressGeneral(data) {
                const progressBar = document.querySelector('.general-progress-bar');
                if (progressBar) {
                    progressBar.style.width = data.progressPercentage + '%';
                    progressBar.setAttribute('aria-valuenow', data.progressPercentage);
                    progressBar.textContent = data.progressPercentage.toFixed(2) + '%';
                }
                const progressText = document.querySelector('.progress-text');
                if (progressText) {
                    progressText.textContent = `${data.reportedTables} de ${data.totalTables} mesas reportadas`;
                }
            }
            function updateTableStatus(data) {
                const totalTablesElement = document.querySelector('.total-tables-count');
                if (totalTablesElement) {
                    totalTablesElement.textContent = data.totalTables;
                }
                const reportedTablesElement = document.querySelector('.reported-tables-count');
                if (reportedTablesElement) {
                    reportedTablesElement.textContent = data.reportedTables;
                }
                const pendingTablesElement = document.querySelector('.pending-tables-count');
                if (pendingTablesElement) {
                    pendingTablesElement.textContent = data.totalTables - data.reportedTables;
                }
            }
            function updateCandidateTable(candidateStats, candidates) {
                const tbody = document.querySelector('.table tbody');
                tbody.innerHTML = '';
                const sortedStats = Object.values(candidateStats).sort((a, b) => b.votes - a.votes);    
                sortedStats.forEach(stats => {
                    const candidate = stats.candidate;
                    const row = document.createElement('tr');        
                    row.innerHTML = `
                        <td><span class="badge bg-primary">${stats.rank}</span></td>
                        <td>
                            <div class="d-flex align-items-center">
                                ${candidate.photo ? 
                                    `<img src="/storage/${candidate.photo}" alt="${candidate.name}" class="rounded-circle avatar-xs me-2">` : 
                                    `<div class="avatar-xs me-2">
                                        <span class="avatar-title rounded-circle bg-primary">${candidate.name.charAt(0)}</span>
                                    </div>`
                                }
                                <span>${candidate.name}</span>
                            </div>
                        </td>
                        <td>${candidate.party}</td>
                        <td>${stats.votes.toLocaleString()}</td>
                        <td>${stats.percentage}%</td>
                        <td>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar" role="progressbar" 
                                    style="width: ${stats.percentage}%;"
                                    aria-valuenow="${stats.percentage}" 
                                    aria-valuemin="0" 
                                    aria-valuemax="100"></div>
                            </div>
                        </td>
                    `;        
                    tbody.appendChild(row);
                });
            }

            function updateLocalidadStats(data) {
                updateCounter('.total-votes-counter', data.totalVotes);
                updateCounter('.total-tables-counter', data.totalTables);
                updateCounter('.reported-tables-counter', data.reportedTables);
                updateCounter('.progress-counter', data.progressPercentage.toFixed(2));
            }
            function updateVotosPorLocalidades(data) {
                // Update the progress bars in the "Votos por Localidades" section
                const container = document.querySelector('.locality-progress-container');
                if (!container) return;
                
                // Clear and rebuild the locality progress items
                container.innerHTML = '';
                
                data.localityStats.forEach(locality => {
                    const localityData = data.localityResults[locality.id] || {total_votes: 0};
                    const progress = locality.total_tables > 0 
                        ? Math.round((locality.reported_tables / locality.total_tables) * 100) 
                        : 0;
                    
                    const item = document.createElement('div');
                    item.className = 'locality-progress-item';
                    item.setAttribute('data-locality-id', locality.id);
                    item.innerHTML = `
                        <p class="mb-1">${locality.name} (${locality.municipality_name})
                            <span class="float-end">${progress}%</span>
                        </p>                                    
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar progress-bar-striped bg-primary" role="progressbar" 
                                style="width: ${progress}%;" aria-valuenow="${progress}" 
                                aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    `;
                    
                    container.appendChild(item);
                });
            }
            function updateLocalityTable(localityStats, localityResults, candidates) {
                const tbody = document.querySelector('#locality-table tbody');
                tbody.innerHTML = '';
                
                localityStats.forEach(locality => {
                    const localityData = localityResults[locality.id] || {candidates: [], total_votes: 0};
                    const progress = locality.total_tables > 0 
                        ? Math.round((locality.reported_tables / locality.total_tables) * 100) 
                        : 0;
                        
                    const row = document.createElement('tr');
                    
                    // Create main cells
                    row.innerHTML = `
                        <td><strong>${locality.name}</strong></td>
                        <td>${locality.municipality_name}</td>
                        <td>${locality.total_tables}</td>
                        <td>${locality.reported_tables}</td>
                        <td>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" role="progressbar" 
                                    style="width: ${progress}%;"
                                    aria-valuenow="${progress}" 
                                    aria-valuemin="0" 
                                    aria-valuemax="100"></div>
                            </div>
                            <small>${progress}%</small>
                        </td>
                        <td><strong>${localityData.total_votes.toLocaleString()}</strong></td>
                    `;
                    
                    // Add candidate cells
                    candidates.forEach(candidate => {
                        let candidateVotes = 0;
                        let candidatePercentage = 0;
                        
                        if (localityData) {
                            const candidateData = localityData.candidates.find(c => c.id === candidate.id);
                            if (candidateData) {
                                candidateVotes = candidateData.votes;
                                candidatePercentage = candidateData.percentage;
                            }
                        }
                        
                        const cell = document.createElement('td');
                        cell.innerHTML = `
                            ${candidateVotes.toLocaleString()}<br>
                            <small class="text-muted">${candidatePercentage}%</small>
                        `;
                        row.appendChild(cell);
                    });
                    
                    tbody.appendChild(row);
                });
            }

function updateCharts(data) {
    if (!charts.candidateChart || !charts.partyChart || !charts.localityChart) return;
    const sortedStats = Object.values(data.candidateStats).sort((a, b) => b.votes - a.votes);
    const candidateNames = sortedStats.map(stat => stat.candidate.name);
    const candidateColors = sortedStats.map(stat => stat.candidate.color || '#3b5de7');
    const candidateVotes = sortedStats.map(stat => stat.votes);
    charts.candidateChart.updateOptions({
        xaxis: {
            categories: candidateNames
        },
        colors: candidateColors
    });
    
    charts.candidateChart.updateSeries([{
        name: 'Votos',
        data: candidateVotes
    }]);
    charts.partyChart.updateOptions({
        labels: candidateNames,
        colors: candidateColors
    });
    
    charts.partyChart.updateSeries(candidateVotes);
    const localityResults = data.localityResults;
    const localities = Object.values(localityResults).map(l => l.name);
    const localitySeries = sortedStats.map(stat => {
        return {
            name: stat.candidate.name,
            type: 'bar',
            data: Object.values(localityResults).map(locality => {
                const candidateData = locality.candidates.find(c => c.id === stat.candidate.id);
                return candidateData ? candidateData.votes : 0;
            })
        };
    });
    
    charts.localityChart.updateOptions({
        xaxis: {
            categories: localities
        },
        colors: candidateColors
    });
    
    charts.localityChart.updateSeries(localitySeries);
}
function updateMap(data) {
    if (!document.getElementById("votes-by-locations")) return;
    
    const localityResults = data.localityResults;
    const markers = Object.values(localityResults).map(l => {
        return {
            name: l.name + " (" + l.total_votes + " votos)",
            coords: [l.latitude, l.longitude],
            votes: l.total_votes,
            candidates: l.candidates
        };
    });
    
    // Clear the existing map container
    const mapContainer = document.getElementById("votes-by-locations");
    mapContainer.innerHTML = "";
    
    // Recreate the map with new data
    var vectorMapColors = getChartColorsArray("votes-by-locations");
    if(vectorMapColors){
        charts.boliviaMap = new jsVectorMap({
            map: "quillacollo_merc",
            selector: "#votes-by-locations",
            zoomOnScroll: true,
            zoomButtons: true,
            selectedMarkers: [0, 5],
            regionStyle: {
                initial: {
                    stroke: "#9599ad",
                    strokeWidth: 0.25,
                    fill: vectorMapColors[0],
                    fillOpacity: 1,
                },
            },
            markersSelectable: true,
            markers: markers,
            markerStyle: {
                initial: {
                    fill: vectorMapColors[1],
                },
                hover: {
                    fill: vectorMapColors[2],
                },
                selected: {
                    fill: vectorMapColors[2],
                },
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
                const popup = document.createElement('div');
                popup.className = 'custom-map-popup';
                popup.innerHTML = `
                    <div class="popup-header">
                        <h5>${m.name}</h5>
                        <button type="button" class="btn-close" aria-label="Close"></button>
                    </div>
                    <div class="popup-body">
                        <p><strong>Total votes:</strong> ${m.votes.toLocaleString()}</p>
                        <h6>Candidate Results:</h6>
                        <ul class="list-group">
                            ${m.candidates.map(c => `
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    ${c.name} (${c.party})
                                    <span class="badge bg-primary rounded-pill">
                                        ${c.votes.toLocaleString()} (${c.percentage}%)
                                    </span>
                                </li>
                            `).join('')}
                        </ul>
                    </div>
                `;
                
                if (!document.querySelector('#map-popup-styles')) {
                    const styles = document.createElement('style');
                    styles.id = 'map-popup-styles';
                    styles.textContent = `
                        .custom-map-popup {
                            position: absolute;
                            top: 50%;
                            left: 50%;
                            transform: translate(-50%, -50%);
                            background: white;
                            border-radius: 8px;
                            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                            z-index: 1000;
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
                
                popup.addEventListener('click', function(e) {
                    if (e.target === this) {
                        document.body.removeChild(popup);
                    }
                });
            }
        });
    }
} 
            function showLoadingIndicator() {
                document.getElementById('loading-indicator').style.display = 'block';
            }
            function hideLoadingIndicator() {
                document.getElementById('loading-indicator').style.display = 'none';
            }
            function updateLastUpdateTime() {
                const now = new Date();
                const timeString = now.toLocaleTimeString();
                document.getElementById('last-update-time').textContent = `Última actualización: ${timeString}`;
            }    
            window.refreshDashboard = refreshDashboard;
            window.startAutoRefresh = startAutoRefresh;
            window.stopAutoRefresh = stopAutoRefresh;
        });
    </script>
@endsection