{{-- resources/views/voting-table-votes/partials/table-rows.blade.php --}}
@php
    if (empty($candidatesByCategory)) {
        echo '<tr><td colspan="' . (2 + (count($candidatesByCategory) * 3)) . '" class="text-center text-muted py-3">No hay candidatos disponibles</td></tr>';
        return;
    }

    // Organizar candidatos por categoría
    $regularCandidates = [];
    $categoryTotals = [];

    foreach ($candidatesByCategory as $categoryCode => $categoryCandidates) {
        $regularCandidates[$categoryCode] = $categoryCandidates
            ->filter(function($c) {
                return true; // Ya no hay tipo en el modelo
            })
            ->values();

        $categoryTotals[$categoryCode] = 0;
    }

    $maxRows = !empty($regularCandidates) ? max(array_map(function($cats) {
        return $cats->count();
    }, $regularCandidates)) : 0;
@endphp

{{-- Filas de candidatos --}}
@for($i = 0; $i < $maxRows; $i++)
    <tr>
        <td class="text-center fw-bold">{{ $i + 1 }}</td>

        {{-- Partido (tomado del primer candidato disponible) --}}
        <td>
            @php $firstCandidate = null; @endphp
            @foreach($candidatesByCategory as $categoryCode => $categoryCandidates)
                @php
                    $candidate = $regularCandidates[$categoryCode][$i] ?? null;
                    if ($candidate && !$firstCandidate) $firstCandidate = $candidate;
                @endphp
            @endforeach

            @if($firstCandidate)
                <div class="d-flex align-items-center">
                    @if($firstCandidate->party_logo)
                        <img src="{{ $firstCandidate->party_logo_url }}"
                             width="20" height="20" class="me-1 rounded" style="object-fit: contain;">
                    @else
                        <span class="candidate-color"
                              style="background-color: {{ $firstCandidate->color ?? '#0ab39c' }};
                                     width: 16px; height: 16px; border-radius: 4px; display: inline-block; margin-right: 4px;"></span>
                    @endif
                    <span class="small">{{ $firstCandidate->party }}</span>
                </div>
            @endif
        </td>

        {{-- Celdas para cada categoría --}}
        @foreach($candidatesByCategory as $categoryCode => $categoryCandidates)
            @php
                $candidate = $regularCandidates[$categoryCode][$i] ?? null;
                if ($candidate) {
                    // Get vote from the votes collection
                    $vote = null;
                    if (isset($table->votes)) {
                        $vote = $table->votes->firstWhere('candidate_id', $candidate->id);
                    }
                    $quantity = $vote ? $vote->quantity : 0;
                    $categoryTotals[$categoryCode] += $quantity;
                    $isObserved = $vote && $vote->vote_status === 'observed';
                } else {
                    $quantity = 0;
                    $isObserved = false;
                }
            @endphp

            {{-- Nombre del candidato --}}
            <td class="table-{{ $categoryColorMap[$categoryCode] ?? 'secondary' }}">
                @if($candidate)
                    <div class="d-flex align-items-center">
                        @if($candidate->photo)
                            <img src="{{ $candidate->photo_url }}"
                                 class="rounded-circle me-1" width="20" height="20" style="object-fit: cover;">
                        @endif
                        <span class="small">{{ Str::limit($candidate->name, 25) }}</span>
                        @if($isObserved)
                            <i class="ri-alert-line text-danger ms-1" title="Observado"></i>
                        @endif
                    </div>
                @else
                    <span class="text-muted fst-italic small">---</span>
                @endif
            </td>

            {{-- Input de votos --}}
            <td class="table-{{ $categoryColorMap[$categoryCode] ?? 'secondary' }} text-center">
                @if($candidate)
                    <input type="number"
                           class="form-control form-control-sm vote-input text-center"
                           data-table="{{ $table->id }}"
                           data-candidate="{{ $candidate->id }}"
                           data-category="{{ $categoryCode }}"
                           value="{{ $quantity }}"
                           min="0"
                           max="{{ $table->expected_voters ?? 9999 }}"
                           step="1"
                           {{ $isDisabled ? 'disabled' : '' }}
                           style="width: 70px; margin: 0 auto; {{ $isObserved ? 'border-color: #f06548;' : '' }}">
                @endif
            </td>

            {{-- Checkbox de observación --}}
            <td class="table-{{ $categoryColorMap[$categoryCode] ?? 'secondary' }} text-center">
                @if($candidate && ($permissions['can_observe'] ?? false) && !$isDisabled)
                    <input type="checkbox"
                           class="form-check-input observe-checkbox"
                           data-table="{{ $table->id }}"
                           data-candidate="{{ $candidate->id }}"
                           data-category="{{ $categoryCode }}"
                           data-candidate-name="{{ $candidate->name }}"
                           {{ $isObserved ? 'checked' : '' }}
                           {{ $isObserved ? 'disabled' : '' }}
                           title="Marcar como observado">
                @elseif($isObserved)
                    <i class="ri-checkbox-circle-fill text-warning"></i>
                @endif
            </td>
        @endforeach
    </tr>
@endfor

{{-- Fila de totales --}}
<tr class="table-info fw-bold">
    <td colspan="2" class="text-end">TOTALES:</td>
    @foreach($candidatesByCategory as $categoryCode => $categoryCandidates)
        <td class="table-{{ $categoryColorMap[$categoryCode] ?? 'secondary' }} text-center" colspan="2">
            <span id="total-{{ $categoryCode }}-{{ $table->id }}">{{ $categoryTotals[$categoryCode] }}</span>
        </td>
        <td class="table-{{ $categoryColorMap[$categoryCode] ?? 'secondary' }}"></td>
    @endforeach
</tr>
