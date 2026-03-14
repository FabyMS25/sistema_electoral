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

{{-- ══════════════════════════════════════════════════════════════════════════
     ROW 1 · KPI cards
═══════════════════════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-md-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <p class="text-uppercase fw-semibold text-muted mb-1" style="font-size:.68rem;letter-spacing:.05em;">
                            Mesas Escrutadas
                        </p>
                        <h3 class="mb-0 fw-bold">
                            <span id="kpi-reported">{{ $reportedTables }}</span>
                            <small class="text-muted fw-normal fs-6">/ <span id="kpi-total">{{ $totalTables }}</span></small>
                        </h3>
                    </div>
                    <span class="avatar-title bg-primary-subtle text-primary rounded-3 fs-2"
                          style="width:48px;height:48px;display:inline-flex;align-items:center;justify-content:center;">
                        <i class="ri-table-line"></i>
                    </span>
                </div>
                <div class="mt-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">Avance</small>
                        <small class="fw-bold text-primary" id="kpi-pct">{{ $progressPercentage }}%</small>
                    </div>
                    <div class="progress" style="height:6px;border-radius:6px;">
                        <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated"
                             id="kpi-bar" style="width:{{ $progressPercentage }}%"></div>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">{{ $selectedElectionType?->name ?? 'N/A' }}</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Total papeletas en ánfora --}}
    <div class="col-xl-3 col-md-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <p class="text-uppercase fw-semibold text-muted mb-1" style="font-size:.68rem;letter-spacing:.05em;">
                            Papeletas en Ánfora
                        </p>
                        <h3 class="mb-0 fw-bold">
                            <span id="kpi-votes">{{ number_format($totalVotes) }}</span>
                        </h3>
                    </div>
                    <span class="avatar-title bg-success-subtle text-success rounded-3 fs-2"
                          style="width:48px;height:48px;display:inline-flex;align-items:center;justify-content:center;">
                        <i class="ri-inbox-line"></i>
                    </span>
                </div>
                <div class="mt-3 d-flex gap-3">
                    <div>
                        <small class="text-muted d-block">En Blanco</small>
                        <span class="fw-bold text-secondary" id="kpi-blank">{{ number_format($totalBlankVotes) }}</span>
                        <small class="text-muted ms-1">
                            ({{ $totalVotes > 0 ? round(($totalBlankVotes / $totalVotes) * 100, 1) : 0 }}%)
                        </small>
                    </div>
                    <div>
                        <small class="text-muted d-block">Nulos</small>
                        <span class="fw-bold text-danger" id="kpi-null">{{ number_format($totalNullVotes) }}</span>
                        <small class="text-muted ms-1">
                            ({{ $totalVotes > 0 ? round(($totalNullVotes / $totalVotes) * 100, 1) : 0 }}%)
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Candidato líder (active category) --}}
    <div class="col-xl-3 col-md-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <p class="text-uppercase fw-semibold text-muted mb-2" style="font-size:.68rem;letter-spacing:.05em;">
                    Candidato Líder · {{ $activeCategoryCode }}
                </p>
                @if(count($candidateStats) > 0)
                    @php $leader = collect($candidateStats)->sortByDesc('votes')->first(); @endphp
                    <div class="d-flex align-items-center gap-3">
                        @if($leader['candidate']->photo)
                            <img src="{{ asset('storage/'.$leader['candidate']->photo) }}"
                                 class="rounded-circle shadow-sm"
                                 style="width:52px;height:52px;object-fit:cover;
                                        border:3px solid {{ $leader['candidate']->color ?? '#0ab39c' }};">
                        @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                 style="width:52px;height:52px;flex-shrink:0;font-size:1.3rem;
                                        background:{{ $leader['candidate']->color ?? '#0ab39c' }};">
                                {{ strtoupper(substr($leader['candidate']->name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="min-w-0">
                            <h6 class="mb-0 fw-bold text-truncate">{{ $leader['candidate']->name }}</h6>
                            <small class="text-muted">{{ $leader['candidate']->party }}</small>
                            <div class="mt-1">
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    {{ number_format($leader['votes']) }} votos · {{ $leader['percentage'] }}%
                                </span>
                            </div>
                        </div>
                    </div>
                    {{-- Mini leader bar --}}
                    <div class="mt-3">
                        <div class="progress" style="height:5px;border-radius:5px;">
                            <div class="progress-bar bg-success"
                                 style="width:{{ $leader['percentage'] }}%;background:{{ $leader['candidate']->color ?? '#0ab39c' }} !important;"></div>
                        </div>
                    </div>
                @else
                    <div class="text-center text-muted py-3">
                        <i class="ri-bar-chart-line fs-1 d-block mb-1"></i>
                        Sin votos aún
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Votos por mesa (promedio) --}}
    <div class="col-xl-3 col-md-6">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <p class="text-uppercase fw-semibold text-muted mb-1" style="font-size:.68rem;letter-spacing:.05em;">
                    Promedio por Mesa
                </p>
                <h3 class="mb-0 fw-bold">
                    {{ $reportedTables > 0 ? number_format($totalVotes / $reportedTables, 1) : 0 }}
                </h3>
                <small class="text-muted">papeletas / mesa escrutada</small>

                <div class="mt-3 row g-0 text-center border-top pt-3">
                    <div class="col-6 border-end">
                        <div class="fw-bold text-warning" id="kpi-pending">
                            {{ $totalTables - $reportedTables }}
                        </div>
                        <small class="text-muted">Pendientes</small>
                    </div>
                    <div class="col-6">
                        @php
                            $validTotal = $categoryStats[$activeCategoryCode]['totalVotes'] ?? 0;
                            $ballotTotal = $categoryStats[$activeCategoryCode]['totalBallots'] ?? 0;
                            $validPct = $ballotTotal > 0 ? round(($validTotal / $ballotTotal) * 100, 1) : 0;
                        @endphp
                        <div class="fw-bold text-primary">{{ $validPct }}%</div>
                        <small class="text-muted">Votos válidos</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <div class="row">
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

{{-- ══════════════════════════════════════════════════════════════════════════
     ROW 2 · Category tabs + main results chart
═══════════════════════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom-0 pb-0 bg-transparent">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h5 class="card-title mb-0">Resultados por Candidato</h5>
                    {{-- Category pills --}}
                    <ul class="nav nav-pills nav-sm gap-1" id="categoryTabs" role="tablist">
                        @foreach($typeCategories as $tc)
                            @php $code = $tc->electionCategory?->code ?? 'UNK'; @endphp
                            <li class="nav-item" role="presentation">
                                <button class="nav-link py-1 px-3 {{ $code === $activeCategoryCode ? 'active' : '' }}"
                                        id="tab-{{ $code }}"
                                        data-bs-toggle="pill"
                                        data-bs-target="#panel-{{ $code }}"
                                        data-category="{{ $code }}"
                                        type="button" role="tab">
                                    {{ $tc->electionCategory?->name ?? $code }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="card-body pt-2">
                <div class="tab-content">
                    @foreach($typeCategories as $tc)
                        @php
                            $code  = $tc->electionCategory?->code ?? 'UNK';
                            $stats = $categoryStats[$code] ?? null;
                            $sortedStats = $stats
                                ? collect($stats['stats'])->sortByDesc('votes')->values()
                                : collect();
                            $catTotal   = $stats['totalBallots'] ?? 0;
                            $catValid   = $stats['totalVotes']   ?? 0;
                            $catBlank   = $stats['blankVotes']   ?? 0;
                            $catNull    = $stats['nullVotes']    ?? 0;
                        @endphp
                        <div class="tab-pane fade {{ $code === $activeCategoryCode ? 'show active' : '' }}"
                             id="panel-{{ $code }}" role="tabpanel">

                            {{-- Special votes row --}}
                            <div class="d-flex gap-3 mb-3 flex-wrap">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
                                    <i class="ri-inbox-line me-1"></i>
                                    Ánfora: <strong>{{ number_format($catTotal) }}</strong>
                                </span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                    <i class="ri-check-line me-1"></i>
                                    Válidos: <strong>{{ number_format($catValid) }}</strong>
                                    @if($catTotal > 0) ({{ round(($catValid / $catTotal) * 100, 1) }}%) @endif
                                </span>
                                <span class="badge bg-secondary-subtle text-secondary border px-3 py-2">
                                    <i class="ri-subtract-line me-1"></i>
                                    Blancos: <strong>{{ number_format($catBlank) }}</strong>
                                    @if($catTotal > 0) ({{ round(($catBlank / $catTotal) * 100, 1) }}%) @endif
                                </span>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2">
                                    <i class="ri-close-line me-1"></i>
                                    Nulos: <strong>{{ number_format($catNull) }}</strong>
                                    @if($catTotal > 0) ({{ round(($catNull / $catTotal) * 100, 1) }}%) @endif
                                </span>
                            </div>

                            {{-- Horizontal bar results --}}
                            @forelse($sortedStats as $rank => $s)
                                @php
                                    $cand  = $s['candidate'];
                                    $pct   = $s['percentage'];
                                    $color = $cand->color ?? '#3b5de7';
                                    $isLeader = $rank === 0;
                                @endphp
                                <div class="mb-3 {{ $isLeader ? 'p-2 rounded bg-light border' : '' }}">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        {{-- Rank badge --}}
                                        <span class="badge rounded-pill fw-bold"
                                              style="background:{{ $color }};min-width:26px;">
                                            {{ $rank + 1 }}
                                        </span>
                                        {{-- Photo / initial --}}
                                        @if($cand->photo)
                                            <img src="{{ asset('storage/'.$cand->photo) }}"
                                                 class="rounded-circle"
                                                 style="width:30px;height:30px;object-fit:cover;border:2px solid {{ $color }};">
                                        @else
                                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                                 style="width:30px;height:30px;flex-shrink:0;font-size:.8rem;background:{{ $color }};">
                                                {{ strtoupper(substr($cand->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        {{-- Party logo --}}
                                        @if($cand->party_logo)
                                            <img src="{{ asset('storage/'.$cand->party_logo) }}"
                                                 style="height:22px;width:auto;object-fit:contain;" alt="{{ $cand->party }}">
                                        @endif
                                        {{-- Name + party --}}
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="fw-bold text-truncate" style="font-size:.88rem;">
                                                {{ $cand->name }}
                                                @if($isLeader)
                                                    <i class="ri-trophy-line text-warning ms-1"></i>
                                                @endif
                                            </div>
                                            <small class="text-muted">{{ $cand->party }}</small>
                                        </div>
                                        {{-- Vote count + % --}}
                                        <div class="text-end" style="min-width:100px;">
                                            <div class="fw-bold" style="color:{{ $color }};">
                                                {{ number_format($s['votes']) }}
                                            </div>
                                            <small class="text-muted">{{ $pct }}%</small>
                                        </div>
                                    </div>
                                    {{-- Progress bar --}}
                                    <div class="progress ms-5" style="height:{{ $isLeader ? 10 : 6 }}px;border-radius:6px;">
                                        <div class="progress-bar"
                                             role="progressbar"
                                             style="width:{{ $pct }}%;background:{{ $color }};border-radius:6px;transition:width .6s ease;"
                                             aria-valuenow="{{ $pct }}"
                                             aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-4">
                                    <i class="ri-bar-chart-line fs-1 d-block mb-2"></i>
                                    Sin resultados para esta categoría
                                </div>
                            @endforelse
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
    <div class="row">
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
<div class="row">
    <div class="col-6">
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
    <div class="col-6">
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

<div class="row">
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
                                <th class="text-center">Blancos</th>
                                <th class="text-center">Nulos</th>
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
                            @if(isset($categoryStats[$activeCategoryCode]))
                            @php
                                $blankForCat = $categoryStats[$activeCategoryCode]['blankVotes'] ?? 0;
                                $nullForCat  = $categoryStats[$activeCategoryCode]['nullVotes']  ?? 0;
                                $totalForCat = $categoryStats[$activeCategoryCode]['totalVotes'] ?? 0;
                            @endphp
                            <tr class="table-light fw-semibold">
                                <td colspan="3" class="text-end text-muted small">Votos especiales:</td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary border">
                                        <i class="ri-subtract-line me-1"></i>Blancos: {{ number_format($blankForCat) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-danger-subtle text-danger border">
                                        <i class="ri-close-line me-1"></i>Nulos: {{ number_format($nullForCat) }}
                                    </span>
                                </td>
                                <td>
                                    @if($totalForCat > 0)
                                        <small class="text-muted">
                                            {{ round((($blankForCat + $nullForCat) / $totalForCat) * 100, 1) }}% del total
                                        </small>
                                    @endif
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
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





{{-- ══════════════════════════════════════════════════════════════════════════
     ROW 3 · Charts side by side
═══════════════════════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-3">
    {{-- Bar chart --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-bottom-0 bg-transparent d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0">
                    <i class="ri-bar-chart-2-line me-1 text-primary"></i>Votos por Candidato
                </h6>
                <div class="d-flex gap-1">
                    @foreach($typeCategories as $tc)
                        @php $code = $tc->electionCategory?->code ?? 'UNK'; @endphp
                        <button type="button"
                                class="btn btn-xs {{ $code === $activeCategoryCode ? 'btn-primary' : 'btn-outline-secondary' }}"
                                style="font-size:.72rem;padding:.2rem .6rem;"
                                onclick="switchChartCategory('{{ $code }}')">
                            {{ $code }}
                        </button>
                    @endforeach
                </div>
            </div>
            <div class="card-body pt-0">
                <div id="candidates_chart" style="min-height:300px;"></div>
            </div>
        </div>
    </div>

    {{-- Donut chart --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-bottom-0 bg-transparent">
                <h6 class="card-title mb-0">
                    <i class="ri-pie-chart-2-line me-1 text-success"></i>Distribución
                </h6>
            </div>
            <div class="card-body pt-0">
                <div id="party_distribution_chart" style="min-height:300px;"></div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     ROW 4 · Progress + locality chart
═══════════════════════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-bottom-0 bg-transparent d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0">
                    <i class="ri-map-pin-2-line me-1 text-warning"></i>Resultados por Localidad
                </h6>
                <div id="locality-filter-btns" class="d-flex gap-1 flex-wrap">
                    <button type="button" class="btn btn-outline-secondary active"
                            style="font-size:.72rem;padding:.2rem .6rem;"
                            onclick="filterLocality('all')">Todos</button>
                    @foreach($localityStats->take(6) as $ls)
                        <button type="button" class="btn btn-outline-secondary"
                                style="font-size:.72rem;padding:.2rem .6rem;"
                                onclick="filterLocality('{{ $ls->id }}')">
                            {{ $ls->name }}
                        </button>
                    @endforeach
                </div>
            </div>
            <div class="card-body pt-0 pb-2">
                <div id="projects-overview-chart" style="height:300px;"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-bottom-0 bg-transparent">
                <h6 class="card-title mb-0">
                    <i class="ri-checkbox-circle-line me-1 text-info"></i>Estado de Escrutinio
                </h6>
            </div>
            <div class="card-body">
                {{-- Big donut progress --}}
                <div id="progress-radial-chart" style="height:200px;"></div>

                <div class="row g-0 text-center mt-3 border-top pt-3">
                    <div class="col-4">
                        <div class="fw-bold fs-5 text-success" id="stat-reported">{{ $reportedTables }}</div>
                        <small class="text-muted">Escrutadas</small>
                    </div>
                    <div class="col-4 border-start border-end">
                        <div class="fw-bold fs-5 text-warning" id="stat-pending">{{ $totalTables - $reportedTables }}</div>
                        <small class="text-muted">Pendientes</small>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold fs-5 text-secondary" id="stat-total">{{ $totalTables }}</div>
                        <small class="text-muted">Total</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     ROW 5 · Locality detail table
═══════════════════════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom-0 bg-transparent d-flex align-items-center justify-content-between">
                <h6 class="card-title mb-0">
                    <i class="ri-list-check-2 me-1 text-primary"></i>Detalle por Localidad
                </h6>
                <button class="btn btn-sm btn-outline-primary" onclick="exportTableToCSV('ds-locality-table','resultados.csv')">
                    <i class="ri-download-line me-1"></i>CSV
                </button>
            </div>
            <div class="card-body p-0">
                @include('partials.dashboard-localities-table')
            </div>
        </div>
    </div>
</div>


<div class="auto-refresh-controls"
     style="position:fixed;bottom:20px;right:20px;z-index:1000;
            background:white;padding:10px;border-radius:8px;
            box-shadow:0 2px 10px rgba(0,0,0,.12);">
    <div class="btn-group btn-group-sm">
        <button class="btn btn-outline-primary"
                onclick="window.refreshDashboard && window.refreshDashboard()"
                title="Actualizar ahora">
            <i class="ri-refresh-line"></i>
        </button>
        <button class="btn btn-outline-success"
                onclick="window.startAutoRefresh && window.startAutoRefresh()"
                title="Iniciar auto-actualización">
            <i class="ri-play-line"></i>
        </button>
        <button class="btn btn-outline-secondary"
                onclick="window.stopAutoRefresh && window.stopAutoRefresh()"
                title="Pausar auto-actualización">
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
{{-- ══════════════════════════════════════════════════════════════════════════
     SCRIPTS
═══════════════════════════════════════════════════════════════════════════ --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.2/dist/apexcharts.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Data from server ───────────────────────────────────────────────────
    const ALL_STATS     = @json($categoryStats ?? []);
    const LOCALITY_DATA = @json($localityResults ?? []);
    const ACTIVE_CODE   = @json($activeCategoryCode ?? '');
    const TOTAL_TABLES  = {{ $totalTables ?? 0 }};

    let charts        = {};
    let activeCode    = ACTIVE_CODE;
    let refreshTimer  = null;
    let isRefreshing  = false;

    // ── Build arrays for a given category code ─────────────────────────────
    function buildArrays(code) {
        const stats  = ALL_STATS[code] ?? {};
        const sorted = Object.values(stats.stats ?? {}).sort((a, b) => b.votes - a.votes);
        return {
            names:  sorted.map(s => {
                const n = s.candidate?.name ?? 'N/A';
                return n.length > 22 ? n.substring(0, 20) + '…' : n;
            }),
            colors: sorted.map(s => s.candidate?.color ?? '#3b5de7'),
            votes:  sorted.map(s => s.votes ?? 0),
            pcts:   sorted.map(s => s.percentage ?? 0),
        };
    }

    // ── Init / update bar chart ────────────────────────────────────────────
    function renderBarChart(code) {
        const { names, colors, votes, pcts } = buildArrays(code);
        const el = document.querySelector('#candidates_chart');
        if (!el) return;

        const opts = {
            series: [{ name: 'Votos', data: votes }],
            chart:  { type: 'bar', height: 300, toolbar: { show: false }, id: 'candidateBar',
                      animations: { enabled: true, speed: 400 } },
            plotOptions: { bar: { distributed: true, borderRadius: 5, horizontal: false,
                columnWidth: votes.length > 8 ? '85%' : '60%' } },
            xaxis: {
                categories: names,
                labels: { rotate: -40, trim: true, style: { fontSize: '11px' } },
            },
            yaxis: { labels: { formatter: v => v.toLocaleString() } },
            colors,
            dataLabels: {
                enabled: true,
                formatter: (val, opts2) => pcts[opts2.dataPointIndex] + '%',
                style: { fontSize: '10px' },
                offsetY: -4,
            },
            tooltip: {
                y: { formatter: v => v.toLocaleString() + ' votos' },
                custom({ series, seriesIndex, dataPointIndex }) {
                    const name  = names[dataPointIndex] ?? '';
                    const votes = (series[seriesIndex][dataPointIndex] ?? 0).toLocaleString();
                    const pct   = pcts[dataPointIndex] ?? 0;
                    return `<div class="px-3 py-2 small">
                        <strong>${name}</strong><br>
                        ${votes} votos (${pct}%)
                    </div>`;
                },
            },
            legend: { show: false },
            grid:   { borderColor: '#f1f1f1' },
        };

        if (charts.bar) {
            charts.bar.updateOptions({ colors, xaxis: { categories: names } }, false, false);
            charts.bar.updateSeries([{ name: 'Votos', data: votes }]);
        } else {
            charts.bar = new ApexCharts(el, opts);
            charts.bar.render();
        }
    }

    // ── Init / update donut chart ──────────────────────────────────────────
    function renderDonut(code) {
        const { names, colors, votes } = buildArrays(code);
        const el = document.querySelector('#party_distribution_chart');
        if (!el || !votes.length) return;

        const opts = {
            series: votes,
            labels: names,
            colors,
            chart: { type: 'donut', height: 300, id: 'partyDonut',
                     animations: { enabled: true, speed: 400 } },
            legend: { position: 'bottom', fontSize: '11px', itemMargin: { horizontal: 8 } },
            plotOptions: {
                pie: { donut: { size: '65%', labels: { show: true,
                    value:  { fontSize: '16px', fontWeight: 700,
                              formatter: v => Number(v).toLocaleString() },
                    total:  { show: true, label: 'Total válidos',
                              fontSize: '11px',
                              formatter: w => w.globals.seriesTotals
                                .reduce((a, b) => a + b, 0).toLocaleString() }
                }}}
            },
            tooltip: { y: { formatter: v => v.toLocaleString() + ' votos' } },
            dataLabels: { enabled: false },
        };

        if (charts.donut) {
            charts.donut.updateOptions({ labels: names, colors }, false, false);
            charts.donut.updateSeries(votes);
        } else {
            charts.donut = new ApexCharts(el, opts);
            charts.donut.render();
        }
    }

    // ── Radial progress chart ──────────────────────────────────────────────
    function renderRadial(reported, total) {
        const el  = document.querySelector('#progress-radial-chart');
        if (!el) return;
        const pct = total > 0 ? Math.round((reported / total) * 100) : 0;

        const opts = {
            series: [pct],
            chart:  { type: 'radialBar', height: 200,
                      animations: { enabled: true, speed: 600 } },
            plotOptions: {
                radialBar: {
                    hollow: { size: '55%' },
                    dataLabels: {
                        name:  { show: true, fontSize: '13px', offsetY: -8, color: '#74788d',
                                 formatter: () => 'Escrutadas' },
                        value: { show: true, fontSize: '22px', fontWeight: 700, offsetY: 4,
                                 formatter: v => v + '%',
                                 color: pct >= 75 ? '#0ab39c' : pct >= 50 ? '#f7b84b' : '#f06548' },
                    },
                    track: { background: '#f1f1f1' },
                }
            },
            colors: [pct >= 75 ? '#0ab39c' : pct >= 50 ? '#f7b84b' : '#f06548'],
            stroke: { lineCap: 'round' },
        };

        if (charts.radial) {
            charts.radial.updateSeries([pct]);
            charts.radial.updateOptions({ colors: opts.colors });
        } else {
            charts.radial = new ApexCharts(el, opts);
            charts.radial.render();
        }
    }

    // ── Locality overview bar chart ────────────────────────────────────────
    function renderLocalityChart(localityData, code) {
        const el = document.querySelector('#projects-overview-chart');
        if (!el) return;

        const localities = Object.values(localityData);
        if (!localities.length) return;

        const localityNames = localities.map(l => l.name ?? '?');
        const { names, colors } = buildArrays(code);

        const series = names.map((name, idx) => ({
            name,
            data: localities.map(l => {
                const cat = Object.values(l.categories ?? {})
                    .find(c => (c.candidates ?? []).some(x => {
                        const short = (x.name ?? '').length > 22 ? x.name.substring(0, 20) + '…' : x.name;
                        return short === name || x.name === name;
                    }));
                if (!cat) return 0;
                const cand = cat.candidates.find(x => {
                    const short = (x.name ?? '').length > 22 ? x.name.substring(0, 20) + '…' : x.name;
                    return short === name || x.name === name;
                });
                return cand?.votes ?? 0;
            }),
        }));

        const opts = {
            series,
            chart: { type: 'bar', height: 300, stacked: false, toolbar: { show: false },
                     animations: { enabled: true, speed: 400 } },
            xaxis: { categories: localityNames,
                     labels: { rotate: -35, style: { fontSize: '11px' } } },
            yaxis: { labels: { formatter: v => v.toLocaleString() } },
            colors,
            plotOptions: { bar: { columnWidth: '75%', borderRadius: 3 } },
            legend: { position: 'bottom', fontSize: '11px' },
            tooltip: { shared: true, intersect: false,
                       y: { formatter: v => v.toLocaleString() + ' votos' } },
            grid: { borderColor: '#f1f1f1' },
            dataLabels: { enabled: false },
        };

        if (charts.locality) {
            charts.locality.updateOptions({ colors, xaxis: { categories: localityNames } }, false, false);
            charts.locality.updateSeries(series);
        } else {
            charts.locality = new ApexCharts(el, opts);
            charts.locality.render();
        }
    }

    // ── Switch category (pills + charts) ──────────────────────────────────
    window.switchChartCategory = function(code) {
        activeCode = code;
        renderBarChart(code);
        renderDonut(code);
        renderLocalityChart(LOCALITY_DATA, code);
        // Sync category pill buttons in chart header
        document.querySelectorAll('.auto-refresh-controls').forEach(() => {});
    };

    // ── Initial render ─────────────────────────────────────────────────────
    if (Object.keys(ALL_STATS).length) {
        renderBarChart(activeCode);
        renderDonut(activeCode);
        renderLocalityChart(LOCALITY_DATA, activeCode);
    }
    renderRadial({{ $reportedTables }}, TOTAL_TABLES);

    // ── Refresh dashboard ──────────────────────────────────────────────────
    function refreshDashboard() {
        if (isRefreshing) return;
        isRefreshing = true;

        const electionType = document.querySelector('select[name="election_type"]')?.value ?? '';
        const category     = document.getElementById('filter-category-input')?.value ?? '';
        const department   = document.getElementById('dept-select')?.value ?? '';
        const province     = document.getElementById('prov-select')?.value ?? '';
        const municipality = document.getElementById('muni-select')?.value ?? '';

        const params = new URLSearchParams({
            election_type: electionType, category,
            department, province, municipality,
        });

        fetch(`/refresh-dashboard?${params}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
        .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(data => {
            if (!data.success) throw new Error(data.message ?? 'Error');
            // Update KPI counters
            setText('#kpi-reported', data.reportedTables);
            setText('#kpi-total',    data.totalTables);
            setText('#kpi-pending',  (data.totalTables ?? 0) - (data.reportedTables ?? 0));
            setText('#kpi-votes',    Number(data.totalVotes).toLocaleString());
            setText('#kpi-blank',    Number(data.totalBlankVotes).toLocaleString());
            setText('#kpi-null',     Number(data.totalNullVotes).toLocaleString());
            setText('#kpi-pct',      data.progressPercentage + '%');
            setText('#stat-reported', data.reportedTables);
            setText('#stat-pending',  (data.totalTables ?? 0) - (data.reportedTables ?? 0));
            setText('#stat-total',    data.totalTables);

            const bar = document.getElementById('kpi-bar');
            if (bar) bar.style.width = data.progressPercentage + '%';

            renderRadial(data.reportedTables, data.totalTables);
        })
        .catch(err => {
            console.warn('Refresh error, reloading:', err.message);
            location.reload();
        })
        .finally(() => { isRefreshing = false; });
    }

    function setText(sel, val) {
        document.querySelectorAll(sel).forEach(el => { el.textContent = val ?? ''; });
    }

    function startAuto() {
        stopAuto();
        refreshTimer = setInterval(refreshDashboard, 120_000);
        document.getElementById('refresh-status').innerHTML =
            '<small class="text-success">● Activo</small>';
    }
    function stopAuto() {
        clearInterval(refreshTimer);
        refreshTimer = null;
        document.getElementById('refresh-status').innerHTML =
            '<small class="text-secondary">○ Pausado</small>';
    }

    // ── Locality filter ────────────────────────────────────────────────────
    window.filterLocality = function(id) {
        document.querySelectorAll('.locality-progress-item').forEach(el => {
            el.style.display = (id === 'all' || el.dataset.localityId == id) ? '' : 'none';
        });
        document.querySelectorAll('#locality-filter-btns button').forEach(btn => {
            btn.classList.toggle('active', btn.textContent.trim() === 'Todos' ? id === 'all' : false);
        });
    };

    // ── CSV export ─────────────────────────────────────────────────────────
    window.exportTableToCSV = function(tableId, filename) {
        const table = document.getElementById(tableId);
        if (!table) return;
        const rows = [...table.querySelectorAll('tr')].map(row =>
            [...row.querySelectorAll('td,th')]
                .map(c => '"' + c.innerText.replace(/\n/g, ' ').replace(/"/g, '""') + '"')
                .join(',')
        );
        const blob = new Blob([rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const a = Object.assign(document.createElement('a'), {
            href: URL.createObjectURL(blob), download: filename, style: 'display:none',
        });
        document.body.append(a);
        a.click();
        a.remove();
    };

    // ── Expose API ─────────────────────────────────────────────────────────
    window.ElectionDashboard = { refresh: refreshDashboard, startAuto, stopAuto };
    window.refreshDashboard  = refreshDashboard;
    window.startAutoRefresh  = startAuto;
    window.stopAutoRefresh   = stopAuto;

    startAuto();
});
</script>
@endpush
@section('dashboard-scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.2/dist/apexcharts.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/js/jsvectormap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/maps/world.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    let refreshTimer   = null;
    let isRefreshing   = false;
    let charts         = {};
    const REFRESH_MS   = 120_000; // 2 minutes
    const initialStats = @json($candidateStats ?? []);
    const localityData = @json($localityResults ?? []);
    initCharts(initialStats, localityData);
    startAutoRefresh();
    document.getElementById('exportLocalityTable')?.addEventListener('click', () =>
        exportTableToCSV('locality-table', 'resultados_localidades.csv')
    );
    window.refreshDashboard  = refreshDashboard;
    window.startAutoRefresh  = startAutoRefresh;
    window.stopAutoRefresh   = stopAutoRefresh;
    window.filterLocality    = filterLocality;
    function refreshDashboard() {
        if (isRefreshing) return;
        isRefreshing = true;
        setRefreshIcon(true);
        const electionType = document.querySelector('select[name="election_type"]')?.value ?? '';
        const category     = document.querySelector('#filter-category-input')?.value ?? '';
        const department   = document.querySelector('#dept-select')?.value ?? '';
        const province     = document.querySelector('#prov-select')?.value ?? '';
        const municipality = document.querySelector('#muni-select')?.value ?? '';
        const params = new URLSearchParams({ election_type: electionType, category, department, province, municipality });
        fetch(`/refresh-dashboard?${params}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => {
            if (!r.ok) throw new Error(`HTTP ${r.status}`);
            return r.json();
        })
        .then(data => {
            if (!data.success) throw new Error(data.message ?? 'Error del servidor');
            updateCounters(data);
            updateCharts(data.candidateStats ?? {});
            updateLastUpdated(data.last_updated);
        })
        .catch(err => {
            console.warn('Refresh failed, reloading page:', err.message);
            location.reload();
        })
        .finally(() => {
            isRefreshing = false;
            setRefreshIcon(false);
        });
    }

    function startAutoRefresh() {
        stopAutoRefresh();
        refreshTimer = setInterval(refreshDashboard, REFRESH_MS);
        setRefreshStatus(true);
    }

    function stopAutoRefresh() {
        if (refreshTimer) {
            clearInterval(refreshTimer);
            refreshTimer = null;
        }
        setRefreshStatus(false);
    }

    function setRefreshStatus(active) {
        const el = document.getElementById('refresh-status');
        if (!el) return;
        el.innerHTML = active
            ? '<small class="text-success">● Activo</small>'
            : '<small class="text-secondary">○ Pausado</small>';
    }

    function setRefreshIcon(loading) {
        const btn = document.querySelector('.auto-refresh-controls .btn[onclick*="refreshDashboard"]');
        if (!btn) return;
        btn.innerHTML = loading
            ? '<span class="spinner-border spinner-border-sm" role="status"></span>'
            : '<i class="ri-refresh-line"></i>';
        btn.disabled = loading;
    }

    function updateLastUpdated(ts) {
        const el = document.querySelector('.card-header p.text-muted');
        if (el && ts) el.textContent = 'Última actualización: ' + ts;
    }
    function updateCounters(data) {
        setCounter('.total-votes-counter',    data.totalVotes);
        setCounter('.reported-tables-counter', data.reportedTables);
        setCounter('.total-tables-counter',   data.totalTables);
        setCounter('.progress-counter',       data.progressPercentage);
        setCounter('.blank-votes-counter',     data.totalBlankVotes);
        setCounter('.null-votes-counter',      data.totalNullVotes);
        document.querySelectorAll('.general-progress-bar').forEach(bar => {
            bar.style.width = data.progressPercentage + '%';
            bar.textContent = data.progressPercentage + '%';
            bar.setAttribute('aria-valuenow', data.progressPercentage);
        });
        const pending = (data.totalTables ?? 0) - (data.reportedTables ?? 0);
        setText('.total-tables-count',    data.totalTables);
        setText('.reported-tables-count', data.reportedTables);
        setText('.pending-tables-count',  pending);
        setText('.progress-text',
            `${data.reportedTables} de ${data.totalTables} mesas reportadas`
        );
        const headerBar = document.querySelector('.progress-bar.bg-info[role="progressbar"]');
        if (headerBar) {
            headerBar.style.width = data.progressPercentage + '%';
        }
    }

    function setCounter(selector, value) {
        document.querySelectorAll(selector).forEach(el => {
            el.textContent = Number(value ?? 0).toLocaleString('es-BO');
        });
    }

    function setText(selector, value) {
        document.querySelectorAll(selector).forEach(el => {
            el.textContent = value ?? '';
        });
    }
    function buildChartArrays(candidateStats) {
        const sorted = Object.values(candidateStats).sort((a, b) => b.votes - a.votes);
        return {
            names:  sorted.map(s => {
                const n = s.candidate?.name ?? 'Sin nombre';
                return n.length > 20 ? n.substring(0, 18) + '…' : n;
            }),
            colors: sorted.map(s => s.candidate?.color ?? '#3b5de7'),
            votes:  sorted.map(s => s.votes ?? 0),
        };
    }

    function initCharts(candidateStats, localityResults) {
        if (!Object.keys(candidateStats).length) return;
        const { names, colors, votes } = buildChartArrays(candidateStats);
        const barEl = document.querySelector('#candidates_chart');
        if (barEl) {
            charts.bar = new ApexCharts(barEl, {
                series: [{ name: 'Votos', data: votes }],
                chart:  { type: 'bar', height: 350, toolbar: { show: true }, id: 'candidateBar' },
                plotOptions: { bar: { distributed: true, borderRadius: 4 } },
                xaxis: {
                    categories: names,
                    labels: { rotate: -45, trim: true, style: { fontSize: '11px' } }
                },
                colors,
                tooltip: { y: { formatter: v => v.toLocaleString() + ' votos' } },
                legend: { show: false },
            });
            charts.bar.render();
        }
        const donutEl = document.querySelector('#party_distribution_chart');
        if (donutEl && votes.length) {
            charts.donut = new ApexCharts(donutEl, {
                series:  votes,
                labels:  names,
                colors,
                chart:   { type: 'donut', height: 300, id: 'partyDonut' },
                legend:  { position: 'bottom', fontSize: '11px' },
                plotOptions: {
                    pie: { donut: { size: '60%', labels: { show: true,
                        total: { show: true, label: 'Total',
                            formatter: w => w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString()
                        }
                    }}}
                },
                tooltip: { y: { formatter: v => v.toLocaleString() + ' votos' } },
            });
            charts.donut.render();
        }
        const localityEl = document.querySelector('#projects-overview-chart');
        const localities  = Object.values(localityResults).map(l => l.name);
        if (localityEl && localities.length && names.length) {
            const series = names.map((name) => ({
                name,
                type: 'bar',
                data: Object.values(localityResults).map(l => {
                    let votes = 0;
                    Object.values(l.categories ?? {}).forEach(cat => {
                        const found = (cat.candidates ?? []).find(c => {
                            const short = (c.name ?? '').length > 20
                                ? c.name.substring(0, 18) + '…' : c.name;
                            return short === name || c.name === name;
                        });
                        if (found) votes = found.votes;
                    });
                    return votes;
                }),
            }));
            charts.locality = new ApexCharts(localityEl, {
                series,
                chart:   { type: 'bar', height: 350, stacked: false, toolbar: { show: true } },
                xaxis:   { categories: localities, labels: { rotate: -45, style: { fontSize: '11px' } } },
                colors,
                legend:  { position: 'bottom', horizontalAlign: 'center' },
                tooltip: { shared: true, intersect: false },
                plotOptions: { bar: { columnWidth: '70%' } },
            });
            charts.locality.render();
        }
        initMap(localityResults);
    }

    function updateCharts(candidateStats) {
        if (!Object.keys(candidateStats).length) return;
        const { names, colors, votes } = buildChartArrays(candidateStats);
        if (charts.bar) {
            charts.bar.updateOptions({ colors }, false, false);
            charts.bar.updateSeries([{ name: 'Votos', data: votes }]);
        }
        if (charts.donut) {
            charts.donut.updateOptions({ labels: names, colors }, false, false);
            charts.donut.updateSeries(votes);
        }
    }

    function initMap(localityResults) {
        const mapEl = document.getElementById('votes-by-locations');
        if (!mapEl || !Object.keys(localityResults).length) return;
        if (typeof jsVectorMap === 'undefined') return;
        const markers = Object.values(localityResults).map(l => ({
            name:   `${l.name} (${l.total_votes ?? 0} votos)`,
            coords: [l.latitude ?? -17.4, l.longitude ?? -66.2],
            votes:  l.total_votes ?? 0,
            categories: l.categories ?? {},
        }));
        mapEl.innerHTML = '';
        charts.map = new jsVectorMap({
            map:         'world',
            selector:    '#votes-by-locations',
            zoomOnScroll: true,
            zoomButtons:  true,
            markers,
            markerStyle: {
                initial:  { fill: '#0ab39c' },
                hover:    { fill: '#f06548' },
                selected: { fill: '#f06548' },
            },
            onMarkerClick(event, index) { showMarkerPopup(markers[index]); },
        });
    }

    function showMarkerPopup(marker) {
        document.querySelector('.custom-map-popup')?.remove();
        let rows = '';
        Object.entries(marker.categories).forEach(([code, cat]) => {
            rows += `<li class="list-group-item list-group-item-secondary small fw-bold">${cat.label ?? code}</li>`;
            (cat.candidates ?? []).forEach(c => {
                rows += `
                    <li class="list-group-item d-flex justify-content-between align-items-center py-1">
                        <span class="small">${c.name} <span class="text-muted">(${c.party})</span></span>
                        <span class="badge bg-primary rounded-pill">${(c.votes ?? 0).toLocaleString()} · ${c.percentage ?? 0}%</span>
                    </li>`;
            });
        });
        if (!rows) rows = '<li class="list-group-item small text-muted">Sin datos</li>';

        const popup = document.createElement('div');
        popup.className = 'custom-map-popup position-fixed top-50 start-50 translate-middle bg-white rounded shadow-lg';
        popup.style.cssText = 'z-index:10000;width:340px;max-width:92vw;';
        popup.innerHTML = `
            <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                <h6 class="mb-0 fw-bold">${marker.name}</h6>
                <button type="button" class="btn-close btn-close-sm"></button>
            </div>
            <div class="p-2" style="max-height:60vh;overflow-y:auto;">
                <ul class="list-group list-group-flush">${rows}</ul>
            </div>`;
        popup.querySelector('.btn-close').addEventListener('click', () => popup.remove());
        document.body.appendChild(popup);
    }
    function filterLocality(localityId) {
        document.querySelectorAll('.locality-progress-item').forEach(item => {
            item.style.display = (localityId === 'all' || item.dataset.localityId == localityId)
                ? '' : 'none';
        });
    }

    function exportTableToCSV(tableId, filename) {
        const table = document.getElementById(tableId);
        if (!table) return;
        const rows = [...table.querySelectorAll('tr')].map(row =>
            [...row.querySelectorAll('td,th')]
                .map(cell => '"' + cell.innerText.replace(/\n/g, ' ').replace(/"/g, '""') + '"')
                .join(',')
        );
        const blob = new Blob([rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const link = Object.assign(document.createElement('a'), {
            href:     URL.createObjectURL(blob),
            download: filename,
            style:    'display:none',
        });
        document.body.append(link);
        link.click();
        link.remove();
    }

});
</script>
@endsection
