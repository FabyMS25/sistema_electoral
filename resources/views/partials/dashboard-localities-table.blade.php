{{-- resources/views/partials/dashboard-localities-table.blade.php --}}
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>Localidad</th>
                <th>Municipio</th>
                <th>Mesas</th>
                <th>Reportadas</th>
                <th>Avance</th>
                <th>Votos Alcalde</th>
                <th>Votos Concejal</th>
                <th>Ganador Alcalde</th>
                <th>Ganador Concejal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($localityStats as $locality)
                @php
                    $localityData = $localityResults[$locality->id] ?? [
                        'total_votes_alcalde' => 0,
                        'total_votes_concejal' => 0,
                        'alcalde' => [],
                        'concejal' => []
                    ];

                    $progress = $locality->total_tables > 0
                        ? round(($locality->reported_tables / $locality->total_tables) * 100)
                        : 0;

                    // Ganador Alcalde
                    $winningAlcalde = null;
                    if (!empty($localityData['alcalde'])) {
                        usort($localityData['alcalde'], function($a, $b) {
                            return $b['votes'] - $a['votes'];
                        });
                        $winningAlcalde = $localityData['alcalde'][0] ?? null;
                    }

                    // Ganador Concejal
                    $winningConcejal = null;
                    if (!empty($localityData['concejal'])) {
                        usort($localityData['concejal'], function($a, $b) {
                            return $b['votes'] - $a['votes'];
                        });
                        $winningConcejal = $localityData['concejal'][0] ?? null;
                    }
                @endphp
                <tr>
                    <td>
                        <strong>{{ $locality->name }}</strong>
                    </td>
                    <td>{{ $locality->municipality_name ?? 'N/A' }}</td>
                    <td class="text-center">{{ number_format($locality->total_tables ?? 0) }}</td>
                    <td class="text-center">
                        <span class="badge bg-{{ $locality->reported_tables == $locality->total_tables ? 'success' : 'warning' }}">
                            {{ number_format($locality->reported_tables ?? 0) }}
                        </span>
                    </td>
                    <td style="width: 150px;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-semibold">{{ $progress }}%</span>
                            <div class="progress flex-grow-1" style="height: 6px;">
                                <div class="progress-bar bg-info" role="progressbar"
                                     style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-primary">
                            {{ number_format($localityData['total_votes_alcalde'] ?? 0) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-info">
                            {{ number_format($localityData['total_votes_concejal'] ?? 0) }}
                        </span>
                    </td>
                    <td>
                        @if($winningAlcalde)
                            <div class="d-flex align-items-center">
                                @if($winningAlcalde['party_logo'] ?? false)
                                    <img src="{{ asset('storage/' . $winningAlcalde['party_logo']) }}"
                                         width="16" height="16" class="me-1">
                                @endif
                                <span class="small">{{ Str::limit($winningAlcalde['candidate_name'], 20) }}</span>
                                <span class="badge bg-success ms-1">{{ $winningAlcalde['percentage'] }}%</span>
                            </div>
                        @else
                            <span class="badge bg-secondary">Sin datos</span>
                        @endif
                    </td>
                    <td>
                        @if($winningConcejal)
                            <div class="d-flex align-items-center">
                                @if($winningConcejal['party_logo'] ?? false)
                                    <img src="{{ asset('storage/' . $winningConcejal['party_logo']) }}"
                                         width="16" height="16" class="me-1">
                                @endif
                                <span class="small">{{ Str::limit($winningConcejal['candidate_name'], 20) }}</span>
                                <span class="badge bg-info ms-1">{{ $winningConcejal['percentage'] }}%</span>
                            </div>
                        @else
                            <span class="badge bg-secondary">Sin datos</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-4">
                        <div class="text-center">
                            <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px">
                            </lord-icon>
                            <h5 class="mt-3">No hay localidades disponibles</h5>
                            <p class="text-muted mb-0">No se encontraron localidades para mostrar.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
