{{-- resources/views/partials/dashboard-candidates-table.blade.php --}}
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>Posición</th>
                <th>Candidato</th>
                <th>Partido</th>
                <th>Votos</th>
                <th>Porcentaje</th>
            </tr>
        </thead>
        <tbody>
            @php
                $sortedStats = collect($stats)->sortByDesc('votes')->values();
                $totalVotes = $sortedStats->sum('votes');
            @endphp
            @foreach($sortedStats as $index => $stat)
                <tr>
                    <td>
                        <span class="badge bg-{{ $index == 0 ? 'success' : ($index == 1 ? 'info' : ($index == 2 ? 'warning' : 'secondary')) }} rounded-pill">
                            #{{ $index + 1 }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            @if($stat['candidate']->photo)
                                <img src="{{ asset('storage/' . $stat['candidate']->photo) }}"
                                     alt="{{ $stat['candidate']->name }}"
                                     class="rounded-circle avatar-sm me-2"
                                     style="width: 32px; height: 32px; object-fit: cover;">
                            @endif
                            <div>
                                <h6 class="mb-0">{{ $stat['candidate']->name }}</h6>
                                <small class="text-muted">{{ $stat['candidate']->party_full_name ?? $stat['candidate']->party }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark">{{ $stat['candidate']->party }}</span>
                    </td>
                    <td>
                        <h6 class="mb-0">{{ number_format($stat['votes']) }}</h6>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-semibold">{{ $stat['percentage'] }}%</span>
                            <div class="progress" style="width: 80px; height: 6px;">
                                <div class="progress-bar bg-{{ $index == 0 ? 'success' : ($index == 1 ? 'info' : ($index == 2 ? 'warning' : 'secondary')) }}"
                                     role="progressbar"
                                     style="width: {{ $stat['percentage'] }}%"></div>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
