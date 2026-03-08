{{--
    $stats is passed from CandidateController::index()
    Keys: byCategory, byDepartment, byElectionType, geo
--}}
@php
    $totalCandidates   = $candidates->total();
    $byCategory        = $stats['byCategory'];
    $byDepartment      = $stats['byDepartment'];
    $byElectionType    = $stats['byElectionType'];
    $geo               = $stats['geo'];
@endphp

{{-- ── Top summary cards ── --}}
<div class="row g-3">
    @php
        $summaryCards = [
            ['label' => 'Total Candidatos',   'value' => $totalCandidates,          'icon' => 'ri-user-star-line',   'color' => 'primary'],
            ['label' => 'Categorías Activas', 'value' => $byCategory->count(),       'icon' => 'ri-stack-line',       'color' => 'success'],
            ['label' => 'Departamentos',       'value' => $byDepartment->count(),    'icon' => 'ri-map-pin-line',     'color' => 'info'],
            ['label' => 'Tipos de Elección',   'value' => $byElectionType->count(), 'icon' => 'ri-government-line', 'color' => 'warning'],
        ];
    @endphp

    @foreach($summaryCards as $card)
    <div class="col-xl-3 col-md-6">
        <div class="card mb-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3 flex-shrink-0">
                        <span class="avatar-title bg-{{ $card['color'] }}-subtle text-{{ $card['color'] }} rounded fs-3">
                            <i class="{{ $card['icon'] }}"></i>
                        </span>
                    </div>
                    <div>
                        <p class="text-muted mb-1 small">{{ $card['label'] }}</p>
                        <h4 class="mb-0">{{ $card['value'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ── By election type + category ── --}}
@if($byCategory->isNotEmpty())
<div class="row mt-3">
    <div class="col-12">
        <div class="card mb-0">
            <div class="card-header py-2">
                <h6 class="card-title mb-0">Candidatos por Tipo de Elección y Categoría</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tipo de Elección</th>
                                <th>Categoría</th>
                                <th>Código</th>
                                <th class="text-center">Franja</th>
                                <th class="text-center">Votos/Persona</th>
                                <th class="text-center">Candidatos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($byCategory->sortByDesc('total') as $item)
                            <tr>
                                <td>{{ $item->electionTypeCategory?->electionType?->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">
                                        {{ $item->electionTypeCategory?->electionCategory?->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td><code>{{ $item->electionTypeCategory?->electionCategory?->code ?? '–' }}</code></td>
                                <td class="text-center">{{ $item->electionTypeCategory?->ballot_order ?? '–' }}</td>
                                <td class="text-center">{{ $item->electionTypeCategory?->votes_per_person ?? 1 }}</td>
                                <td class="text-center"><span class="badge bg-info">{{ $item->total }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ── By department  +  geographic scope ── --}}
@if($byDepartment->isNotEmpty())
<div class="row mt-3">
    <div class="col-md-6">
        <div class="card mb-0">
            <div class="card-header py-2">
                <h6 class="card-title mb-0">Por Departamento</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr><th>Departamento</th><th class="text-center">Candidatos</th></tr>
                        </thead>
                        <tbody>
                            @foreach($byDepartment->sortByDesc('total') as $item)
                            <tr>
                                <td>{{ $item->department?->name ?? 'Sin departamento' }}</td>
                                <td class="text-center"><span class="badge bg-success">{{ $item->total }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-0">
            <div class="card-header py-2">
                <h6 class="card-title mb-0">Por Ámbito Geográfico</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr><th>Ámbito</th><th class="text-center">Candidatos</th></tr>
                        </thead>
                        <tbody>
                            @foreach(['nacional' => ['label'=>'Nacional','color'=>'primary'], 'departamental' => ['label'=>'Departamental','color'=>'info'], 'provincial' => ['label'=>'Provincial','color'=>'warning'], 'municipal' => ['label'=>'Municipal','color'=>'success']] as $key => $cfg)
                            <tr>
                                <td>{{ $cfg['label'] }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $cfg['color'] }}">{{ $geo[$key] }}</span>
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
@endif
