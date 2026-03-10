{{--
    resources/views/partials/dashboard-localities-table.blade.php
--}}
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0" id="ds-locality-table">
        <thead class="table-light">
            <tr>
                <th>Localidad</th>
                <th>Municipio</th>
                <th class="text-center">Mesas</th>
                <th class="text-center">Reportadas</th>
                <th style="min-width:120px;">Avance</th>
                <th class="text-center">V. Alcalde</th>
                <th class="text-center">V. Concejal</th>
                <th class="text-center">En Blanco</th>
                <th class="text-center">Nulos</th>
                <th>Líder Alcalde</th>
                <th>Líder Concejal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($localityStats as $locality)
                @php
                    $data     = $localityResults[$locality->id] ?? null;
                    $progress = $locality->total_tables > 0
                        ? round(($locality->reported_tables / $locality->total_tables) * 100)
                        : 0;
                    $barColor = $progress >= 100 ? 'bg-success' : ($progress >= 50 ? 'bg-info' : 'bg-warning');

                    $alcaldeLider  = ($data['alcalde'][0]  ?? null);
                    $concejalLider = ($data['concejal'][0] ?? null);
                @endphp
                <tr>
                    <td><strong>{{ $locality->name }}</strong></td>
                    <td><small class="text-muted">{{ $locality->municipality_name }}</small></td>

                    <td class="text-center">{{ $locality->total_tables }}</td>
                    <td class="text-center">
                        <span class="badge bg-{{ $locality->reported_tables >= $locality->total_tables && $locality->total_tables > 0 ? 'success' : 'warning text-dark' }}">
                            {{ $locality->reported_tables }}
                        </span>
                    </td>

                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="small fw-semibold" style="min-width:30px;">{{ $progress }}%</span>
                            <div class="progress flex-grow-1" style="height:6px;">
                                <div class="progress-bar {{ $barColor }}" role="progressbar"
                                     style="width:{{ $progress }}%"></div>
                            </div>
                        </div>
                    </td>

                    <td class="text-center">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                            {{ number_format($data['total_votes_alcalde'] ?? 0) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-info-subtle text-info border border-info-subtle">
                            {{ number_format($data['total_votes_concejal'] ?? 0) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-secondary-subtle text-secondary border">
                            {{ number_format($data['blank_votes'] ?? 0) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                            {{ number_format($data['null_votes'] ?? 0) }}
                        </span>
                    </td>

                    <td>
                        @if($alcaldeLider)
                            <div class="d-flex align-items-center gap-1">
                                @if($alcaldeLider['party_logo'] ?? false)
                                    <img src="{{ asset('storage/'.$alcaldeLider['party_logo']) }}"
                                         style="width:16px;height:16px;object-fit:contain;" alt="">
                                @endif
                                <span class="small">{{ Str::limit($alcaldeLider['candidate_name'], 18) }}</span>
                                <span class="badge bg-success ms-1">{{ $alcaldeLider['percentage'] }}%</span>
                            </div>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary">Sin datos</span>
                        @endif
                    </td>

                    <td>
                        @if($concejalLider)
                            <div class="d-flex align-items-center gap-1">
                                @if($concejalLider['party_logo'] ?? false)
                                    <img src="{{ asset('storage/'.$concejalLider['party_logo']) }}"
                                         style="width:16px;height:16px;object-fit:contain;" alt="">
                                @endif
                                <span class="small">{{ Str::limit($concejalLider['candidate_name'], 18) }}</span>
                                <span class="badge bg-info ms-1">{{ $concejalLider['percentage'] }}%</span>
                            </div>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary">Sin datos</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center py-5">
                        <i class="ri-map-pin-line fs-1 text-muted d-block mb-2"></i>
                        <span class="text-muted">No hay localidades disponibles</span>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
