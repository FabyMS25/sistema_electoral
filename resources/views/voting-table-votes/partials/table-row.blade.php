{{-- resources/views/voting-table-votes/partials/table-row.blade.php --}}
@php
    // Filtrar candidatos regulares (no NULO ni BLANCO)
    $alcaldesRegulares = $candidatesByCategory['alcalde']->filter(function($c) {
        return !in_array($c->type, ['null_votes', 'blank_votes']);
    })->values();

    $concejalesRegulares = $candidatesByCategory['concejal']->filter(function($c) {
        return !in_array($c->type, ['null_votes', 'blank_votes']);
    })->values();

    // Obtener NULO y BLANCO específicamente
    $nuloAlcalde = $candidatesByCategory['alcalde']->firstWhere('type', 'null_votes');
    $blancoAlcalde = $candidatesByCategory['alcalde']->firstWhere('type', 'blank_votes');
    $nuloConcejal = $candidatesByCategory['concejal']->firstWhere('type', 'null_votes');
    $blancoConcejal = $candidatesByCategory['concejal']->firstWhere('type', 'blank_votes');

    $maxRows = max($alcaldesRegulares->count(), $concejalesRegulares->count());
    $totalAlcalde = 0;
    $totalConcejal = 0;
@endphp

{{-- Filas de candidatos regulares --}}
@for($i = 0; $i < $maxRows; $i++)
    @php
        $alcalde = $alcaldesRegulares[$i] ?? null;
        $concejal = $concejalesRegulares[$i] ?? null;

        if ($alcalde) {
            $voteAlcalde = $table->votes->firstWhere('candidate_id', $alcalde->id);
            $quantityAlcalde = $voteAlcalde ? $voteAlcalde->quantity : 0;
            $totalAlcalde += $quantityAlcalde;
            $isAlcaldeObserved = $voteAlcalde && $voteAlcalde->vote_status === 'observed';
        } else {
            $quantityAlcalde = 0;
            $isAlcaldeObserved = false;
        }

        if ($concejal) {
            $voteConcejal = $table->votes->firstWhere('candidate_id', $concejal->id);
            $quantityConcejal = $voteConcejal ? $voteConcejal->quantity : 0;
            $totalConcejal += $quantityConcejal;
            $isConcejalObserved = $voteConcejal && $voteConcejal->vote_status === 'observed';
        } else {
            $quantityConcejal = 0;
            $isConcejalObserved = false;
        }
    @endphp
    <tr class="{{ $isAlcaldeObserved || $isConcejalObserved ? 'table-warning' : '' }}">
        <td class="text-center fw-bold">{{ $i + 1 }}</td>

        {{-- Partido --}}
        <td>
            @if($alcalde || $concejal)
                @php $party = $alcalde->party ?? $concejal->party; @endphp
                <div class="d-flex align-items-center">
                    @php
                        $logo = $alcalde->party_logo ?? $concejal->party_logo ?? null;
                        $color = $alcalde->color ?? $concejal->color ?? '#0ab39c';
                    @endphp
                    @if($logo)
                        <img src="{{ asset('storage/' . $logo) }}"
                             width="20" height="20"
                             class="me-1 rounded"
                             style="object-fit: contain;">
                    @else
                        <span class="candidate-color" style="background-color: {{ $color }}; width: 16px; height: 16px; border-radius: 4px; display: inline-block; margin-right: 4px;"></span>
                    @endif
                    <span class="small">{{ $party }}</span>
                </div>
            @endif
        </td>

        {{-- Alcalde Candidato --}}
        <td class="table-primary">
            @if($alcalde)
                <div class="d-flex align-items-center">
                    @if($alcalde->photo)
                        <img src="{{ asset('storage/' . $alcalde->photo) }}"
                             class="rounded-circle me-1"
                             width="20" height="20"
                             style="object-fit: cover;">
                    @endif
                    <span class="small">{{ Str::limit($alcalde->name, 25) }}</span>
                    @if($isAlcaldeObserved)
                        <i class="ri-alert-line text-danger ms-1" title="Observado"></i>
                    @endif
                </div>
            @else
                <span class="text-muted fst-italic small">---</span>
            @endif
        </td>

        {{-- Alcalde Votos --}}
        <td class="table-primary text-center">
            @if($alcalde)
                <input type="number"
                       class="form-control form-control-sm vote-input text-center"
                       data-table="{{ $table->id }}"
                       data-candidate="{{ $alcalde->id }}"
                       data-category="alcalde"
                       value="{{ $quantityAlcalde }}"
                       min="0"
                       max="{{ $table->expected_voters ?? 9999 }}"
                       step="1"
                       {{ $isDisabled ? 'disabled' : '' }}
                       style="width: 70px; margin: 0 auto; {{ $isAlcaldeObserved ? 'border-color: #f06548;' : '' }}">
            @endif
        </td>

        {{-- Alcalde Checkbox Observación --}}
        <td class="table-primary text-center">
            @if($alcalde && $userCan['observe'] && !$isDisabled)
                <input type="checkbox"
                       class="form-check-input observe-checkbox"
                       data-table="{{ $table->id }}"
                       data-candidate="{{ $alcalde->id }}"
                       data-category="alcalde"
                       data-candidate-name="{{ $alcalde->name }}"
                       {{ $isAlcaldeObserved ? 'checked' : '' }}
                       {{ $isAlcaldeObserved ? 'disabled' : '' }}
                       title="Marcar como observado">
            @elseif($isAlcaldeObserved)
                <i class="ri-checkbox-circle-fill text-warning" title="Observado"></i>
            @endif
        </td>

        {{-- Concejal Candidato --}}
        <td class="table-success">
            @if($concejal)
                <div class="d-flex align-items-center">
                    @if($concejal->photo)
                        <img src="{{ asset('storage/' . $concejal->photo) }}"
                             class="rounded-circle me-1"
                             width="20" height="20"
                             style="object-fit: cover;">
                    @endif
                    <span class="small">{{ Str::limit($concejal->name, 25) }}</span>
                    @if($isConcejalObserved)
                        <i class="ri-alert-line text-danger ms-1" title="Observado"></i>
                    @endif
                </div>
            @else
                <span class="text-muted fst-italic small">---</span>
            @endif
        </td>

        {{-- Concejal Votos --}}
        <td class="table-success text-center">
            @if($concejal)
                <input type="number"
                       class="form-control form-control-sm vote-input text-center"
                       data-table="{{ $table->id }}"
                       data-candidate="{{ $concejal->id }}"
                       data-category="concejal"
                       value="{{ $quantityConcejal }}"
                       min="0"
                       max="{{ $table->expected_voters ?? 9999 }}"
                       step="1"
                       {{ $isDisabled ? 'disabled' : '' }}
                       style="width: 70px; margin: 0 auto; {{ $isConcejalObserved ? 'border-color: #f06548;' : '' }}">
            @endif
        </td>

        {{-- Concejal Checkbox Observación --}}
        <td class="table-success text-center">
            @if($concejal && $userCan['observe'] && !$isDisabled)
                <input type="checkbox"
                       class="form-check-input observe-checkbox"
                       data-table="{{ $table->id }}"
                       data-candidate="{{ $concejal->id }}"
                       data-category="concejal"
                       data-candidate-name="{{ $concejal->name }}"
                       {{ $isConcejalObserved ? 'checked' : '' }}
                       {{ $isConcejalObserved ? 'disabled' : '' }}
                       title="Marcar como observado">
            @elseif($isConcejalObserved)
                <i class="ri-checkbox-circle-fill text-warning" title="Observado"></i>
            @endif
        </td>
    </tr>
@endfor

{{-- Fila para NULO --}}
@if($nuloAlcalde || $nuloConcejal)
<tr class="table-secondary">
    <td class="text-center">{{ $maxRows + 1 }}</td>
    <td>-</td>

    {{-- NULO Alcalde --}}
    <td class="table-primary fw-bold">NULO</td>
    <td class="table-primary text-center">
        @if($nuloAlcalde)
            @php
                $vote = $table->votes->firstWhere('candidate_id', $nuloAlcalde->id);
                $quantity = $vote ? $vote->quantity : 0;
                $totalAlcalde += $quantity;
                $isObserved = $vote && $vote->vote_status === 'observed';
            @endphp
            <input type="number"
                   class="form-control form-control-sm vote-input text-center"
                   data-table="{{ $table->id }}"
                   data-candidate="{{ $nuloAlcalde->id }}"
                   data-category="alcalde"
                   value="{{ $quantity }}"
                   min="0"
                   {{ $isDisabled ? 'disabled' : '' }}
                   style="width: 70px; margin: 0 auto; {{ $isObserved ? 'border-color: #f06548;' : '' }}">
        @endif
    </td>
    <td class="table-primary text-center">
        @if($nuloAlcalde && $userCan['observe'] && !$isDisabled)
            <input type="checkbox"
                   class="form-check-input observe-checkbox"
                   data-table="{{ $table->id }}"
                   data-candidate="{{ $nuloAlcalde->id }}"
                   data-category="alcalde"
                   data-candidate-name="NULO"
                   {{ $isObserved ? 'checked' : '' }}
                   {{ $isObserved ? 'disabled' : '' }}>
        @elseif($isObserved)
            <i class="ri-checkbox-circle-fill text-warning"></i>
        @endif
    </td>

    {{-- NULO Concejal --}}
    <td class="table-success fw-bold">NULO</td>
    <td class="table-success text-center">
        @if($nuloConcejal)
            @php
                $vote = $table->votes->firstWhere('candidate_id', $nuloConcejal->id);
                $quantity = $vote ? $vote->quantity : 0;
                $totalConcejal += $quantity;
                $isObserved = $vote && $vote->vote_status === 'observed';
            @endphp
            <input type="number"
                   class="form-control form-control-sm vote-input text-center"
                   data-table="{{ $table->id }}"
                   data-candidate="{{ $nuloConcejal->id }}"
                   data-category="concejal"
                   value="{{ $quantity }}"
                   min="0"
                   {{ $isDisabled ? 'disabled' : '' }}
                   style="width: 70px; margin: 0 auto; {{ $isObserved ? 'border-color: #f06548;' : '' }}">
        @endif
    </td>
    <td class="table-success text-center">
        @if($nuloConcejal && $userCan['observe'] && !$isDisabled)
            <input type="checkbox"
                   class="form-check-input observe-checkbox"
                   data-table="{{ $table->id }}"
                   data-candidate="{{ $nuloConcejal->id }}"
                   data-category="concejal"
                   data-candidate-name="NULO"
                   {{ $isObserved ? 'checked' : '' }}
                   {{ $isObserved ? 'disabled' : '' }}>
        @elseif($isObserved)
            <i class="ri-checkbox-circle-fill text-warning"></i>
        @endif
    </td>
</tr>
@endif

{{-- Fila para BLANCO --}}
@if($blancoAlcalde || $blancoConcejal)
<tr class="table-secondary">
    <td class="text-center">{{ $maxRows + 2 }}</td>
    <td>-</td>

    {{-- BLANCO Alcalde --}}
    <td class="table-primary fw-bold">BLANCO</td>
    <td class="table-primary text-center">
        @if($blancoAlcalde)
            @php
                $vote = $table->votes->firstWhere('candidate_id', $blancoAlcalde->id);
                $quantity = $vote ? $vote->quantity : 0;
                $totalAlcalde += $quantity;
                $isObserved = $vote && $vote->vote_status === 'observed';
            @endphp
            <input type="number"
                   class="form-control form-control-sm vote-input text-center"
                   data-table="{{ $table->id }}"
                   data-candidate="{{ $blancoAlcalde->id }}"
                   data-category="alcalde"
                   value="{{ $quantity }}"
                   min="0"
                   {{ $isDisabled ? 'disabled' : '' }}
                   style="width: 70px; margin: 0 auto; {{ $isObserved ? 'border-color: #f06548;' : '' }}">
        @endif
    </td>
    <td class="table-primary text-center">
        @if($blancoAlcalde && $userCan['observe'] && !$isDisabled)
            <input type="checkbox"
                   class="form-check-input observe-checkbox"
                   data-table="{{ $table->id }}"
                   data-candidate="{{ $blancoAlcalde->id }}"
                   data-category="alcalde"
                   data-candidate-name="BLANCO"
                   {{ $isObserved ? 'checked' : '' }}
                   {{ $isObserved ? 'disabled' : '' }}>
        @elseif($isObserved)
            <i class="ri-checkbox-circle-fill text-warning"></i>
        @endif
    </td>

    {{-- BLANCO Concejal --}}
    <td class="table-success fw-bold">BLANCO</td>
    <td class="table-success text-center">
        @if($blancoConcejal)
            @php
                $vote = $table->votes->firstWhere('candidate_id', $blancoConcejal->id);
                $quantity = $vote ? $vote->quantity : 0;
                $totalConcejal += $quantity;
                $isObserved = $vote && $vote->vote_status === 'observed';
            @endphp
            <input type="number"
                   class="form-control form-control-sm vote-input text-center"
                   data-table="{{ $table->id }}"
                   data-candidate="{{ $blancoConcejal->id }}"
                   data-category="concejal"
                   value="{{ $quantity }}"
                   min="0"
                   {{ $isDisabled ? 'disabled' : '' }}
                   style="width: 70px; margin: 0 auto; {{ $isObserved ? 'border-color: #f06548;' : '' }}">
        @endif
    </td>
    <td class="table-success text-center">
        @if($blancoConcejal && $userCan['observe'] && !$isDisabled)
            <input type="checkbox"
                   class="form-check-input observe-checkbox"
                   data-table="{{ $table->id }}"
                   data-candidate="{{ $blancoConcejal->id }}"
                   data-category="concejal"
                   data-candidate-name="BLANCO"
                   {{ $isObserved ? 'checked' : '' }}
                   {{ $isObserved ? 'disabled' : '' }}>
        @elseif($isObserved)
            <i class="ri-checkbox-circle-fill text-warning"></i>
        @endif
    </td>
</tr>
@endif

{{-- Fila de totales --}}
<tr class="table-info fw-bold">
    <td colspan="2" class="text-end">TOTALES:</td>
    <td class="table-primary text-center" colspan="2">
        <span id="total-alcalde-{{ $table->id }}">{{ $totalAlcalde }}</span>
    </td>
    <td class="table-primary"></td>
    <td class="table-success text-center" colspan="2">
        <span id="total-concejal-{{ $table->id }}">{{ $totalConcejal }}</span>
    </td>
    <td class="table-success"></td>
</tr>
