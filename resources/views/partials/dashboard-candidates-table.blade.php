{{--
    resources/views/partials/dashboard-candidates-table.blade.php
--}}
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th style="width:48px;">#</th>
                <th>Candidato</th>
                <th>Partido</th>
                <th>Votos</th>
                <th>%</th>
            </tr>
        </thead>
        <tbody>
            @php $sorted = collect($stats)->sortByDesc('votes')->values(); @endphp
            @forelse($sorted as $i => $stat)
                @php
                    $colors = ['success', 'info', 'warning'];
                    $color  = $colors[$i] ?? 'secondary';
                    $width  = max(2, $stat['percentage']);
                @endphp
                <tr>
                    <td>
                        <span class="badge bg-{{ $color }} rounded-pill">#{{ $i + 1 }}</span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($stat['candidate']->photo)
                                <img src="{{ asset('storage/'.$stat['candidate']->photo) }}"
                                     class="rounded-circle"
                                     style="width:28px;height:28px;object-fit:cover;" alt="">
                            @endif
                            <div>
                                <h6 class="mb-0 small">{{ $stat['candidate']->name }}</h6>
                                <small class="text-muted">
                                    {{ $stat['candidate']->party_full_name ?? $stat['candidate']->party }}
                                </small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">{{ $stat['candidate']->party }}</span>
                    </td>
                    <td><strong>{{ number_format($stat['votes']) }}</strong></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-semibold" style="min-width:36px;">{{ $stat['percentage'] }}%</span>
                            <div class="progress flex-grow-1" style="height:6px;min-width:60px;">
                                <div class="progress-bar bg-{{ $color }}"
                                     role="progressbar" style="width:{{ $width }}%"></div>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">Sin votos registrados aún</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
