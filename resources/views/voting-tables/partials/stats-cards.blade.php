{{-- resources/views/voting-tables/partials/stats-cards.blade.php --}}
@php
    $collection = $votingTables->getCollection();

    $totalTables         = $votingTables->total();
    $totalExpectedVoters = $collection->sum('expected_voters');
    $totalActualVoters   = $collection->sum('display_total_voters');

    $pendingTables  = $collection->whereIn('display_status', ['configurada', 'en_espera', 'votacion'])->count();
    $computedTables = $collection->whereIn('display_status', ['escrutada', 'transmitida'])->count();
    $observedTables = $collection->where('display_status', 'observada')->count();
    $annulledTables = $collection->where('display_status', 'anulada')->count();

    $participationPct = $totalExpectedVoters > 0
        ? round(($totalActualVoters / $totalExpectedVoters) * 100, 1) : 0;
    $pendingPct  = $totalTables > 0 ? round(($pendingTables  / $totalTables) * 100, 1) : 0;
    $computedPct = $totalTables > 0 ? round(($computedTables / $totalTables) * 100, 1) : 0;
@endphp

<div class="row">
    <div class="col-xl-2 col-md-4 mb-1">
        <div class="card stats-card bg-primary text-white">
            <div class="card-body">
                <h6 class="text-white-50 mb-2">Total Mesas</h6>
                <h3 class="mb-0 text-white">{{ number_format($totalTables) }}</h3>
                <small class="text-white-50">Registradas</small>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 mb-1">
        <div class="card stats-card bg-info text-white">
            <div class="card-body">
                <h6 class="text-white-50 mb-2">Electores</h6>
                <h3 class="mb-0 text-white">{{ number_format($totalExpectedVoters) }}</h3>
                <small class="text-white-50">Habilitados</small>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 mb-1">
        <div class="card stats-card bg-success text-white">
            <div class="card-body">
                <h6 class="text-white-50 mb-2">Votaron</h6>
                <h3 class="mb-0 text-white">{{ number_format($totalActualVoters) }}</h3>
                <small class="text-white-50">{{ $participationPct }}% participación</small>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 mb-1">
        <div class="card stats-card bg-warning text-white">
            <div class="card-body">
                <h6 class="text-white-50 mb-2">Pendientes</h6>
                <h3 class="mb-0 text-white">{{ number_format($pendingTables) }}</h3>
                <small class="text-white-50">{{ $pendingPct }}% del total</small>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 mb-1">
        <div class="card stats-card bg-success text-white">
            <div class="card-body">
                <h6 class="text-white-50 mb-2">Escrutadas</h6>
                <h3 class="mb-0 text-white">{{ number_format($computedTables) }}</h3>
                <small class="text-white-50">{{ $computedPct }}% procesado</small>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 mb-1">
        <div class="card stats-card bg-danger text-white">
            <div class="card-body">
                <h6 class="text-white-50 mb-2">Sin configurar</h6>
                <h3 class="mb-0 text-white">{{ number_format($totalTables - $pendingTables - $computedTables - $observedTables - $annulledTables) }}</h3>
                <small class="text-white-50">Sin elección asignada</small>
            </div>
        </div>
    </div>
</div>

@if($observedTables > 0 || $annulledTables > 0)
<div class="d-flex gap-2 justify-content-end mt-1 mb-2">
    @if($observedTables > 0)
        <span class="badge bg-danger-subtle text-danger">
            <i class="ri-error-warning-line me-1"></i>{{ $observedTables }} observadas
        </span>
    @endif
    @if($annulledTables > 0)
        <span class="badge bg-dark-subtle text-dark">
            <i class="ri-forbid-line me-1"></i>{{ $annulledTables }} anuladas
        </span>
    @endif
</div>
@endif
