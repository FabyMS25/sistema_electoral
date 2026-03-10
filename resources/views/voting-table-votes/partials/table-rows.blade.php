{{-- resources/views/voting-table-votes/partials/table-rows.blade.php--}}
@php
    if (empty($candidatesByCategory)) {
        return;
    }
    $regularCandidates = [];
    $voteMap           = [];
    foreach ($candidatesByCategory as $categoryCode => $categoryCandidates) {
        $regularCandidates[$categoryCode] = $categoryCandidates->values();
    }
    if (isset($table->votes)) {
        foreach ($table->votes as $vote) {
            $voteMap[$vote->candidate_id] = $vote;
        }
    }
    $maxRows = empty($regularCandidates)
        ? 0
        : max(array_map(fn($c) => $c->count(), $regularCandidates));

    $canObserve = ($permissions['can_observe'] ?? false) && !$isDisabled;
@endphp

@for($i = 0; $i < $maxRows; $i++)
<tr>
    <td class="text-center fw-bold small">{{ $i + 1 }}</td>
    <td>
        @php $firstCandidate = null; @endphp
        @foreach($candidatesByCategory as $categoryCode => $_)
            @php $c = $regularCandidates[$categoryCode][$i] ?? null; @endphp
            @if($c && !$firstCandidate) @php $firstCandidate = $c; @endphp @endif
        @endforeach

        @if($firstCandidate)
            <div class="d-flex align-items-center gap-1">
                @if($firstCandidate->party_logo)
                    <img src="{{ $firstCandidate->party_logo_url }}"
                         width="20" height="20" class="rounded" style="object-fit:contain;">
                @else
                    <span style="background:{{ $firstCandidate->color ?? '#0ab39c' }};
                                 width:14px;height:14px;border-radius:3px;display:inline-block;flex-shrink:0;"></span>
                @endif
                <span class="small">{{ $firstCandidate->party }}</span>
            </div>
        @endif
    </td>
    @foreach($candidatesByCategory as $categoryCode => $_)
        @php
            $candidate  = $regularCandidates[$categoryCode][$i] ?? null;
            $vote       = $candidate ? ($voteMap[$candidate->id] ?? null) : null;
            $quantity   = $vote?->quantity ?? 0;
            $isObserved = $vote && $vote->vote_status === \App\Models\Vote::VOTE_STATUS_OBSERVED;
            $colClass   = 'table-' . ($categoryColorMap[$categoryCode] ?? 'secondary');
        @endphp
        <td class="{{ $colClass }} col-{{ Str::slug($categoryCode) }}">
            @if($candidate)
                <div class="d-flex align-items-center gap-1">
                    @if($candidate->photo)
                        <img src="{{ $candidate->photo_url }}"
                             class="rounded-circle" width="20" height="20" style="object-fit:cover;">
                    @endif
                    <span class="small">{{ Str::limit($candidate->name, 25) }}</span>
                    @if($isObserved)
                        <i class="ri-alert-line text-danger" title="Observado"></i>
                    @endif
                </div>
            @else
                <span class="text-muted fst-italic small">---</span>
            @endif
        </td>
        <td class="{{ $colClass }} col-{{ Str::slug($categoryCode) }} text-center">
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
                       style="width:70px;margin:0 auto;{{ $isObserved ? 'border-color:#f06548;' : '' }}">
            @endif
        </td>
        <td class="{{ $colClass }} col-{{ Str::slug($categoryCode) }} text-center">
            @if($candidate)
                @if($canObserve)
                    <input type="checkbox"
                           class="form-check-input observe-checkbox"
                           data-table="{{ $table->id }}"
                           data-vote-id="{{ $vote?->id ?? '' }}"
                           data-candidate="{{ $candidate->id }}"
                           data-category="{{ $categoryCode }}"
                           data-candidate-name="{{ $candidate->name }}"
                           {{ $isObserved ? 'checked disabled' : '' }}
                           title="{{ $isObserved ? 'Ya observado' : 'Marcar como observado' }}">
                @elseif($isObserved)
                    <i class="ri-checkbox-circle-fill text-warning" title="Observado"></i>
                @endif
            @endif
        </td>
    @endforeach
</tr>
@endfor
<tr class="table-light">
    <td class="text-center text-muted" style="font-size:0.7rem;">
        <i class="ri-subtract-line"></i>
    </td>
    <td class="text-end small fw-semibold text-muted pe-2" style="white-space:nowrap; font-size:0.78rem;">
        En Blanco
    </td>
    @foreach($candidatesByCategory as $categoryCode => $_)
        @php
            $blankQty = $table->results_by_category[$categoryCode]['blank_votes'] ?? 0;
            $colClass  = 'table-' . ($categoryColorMap[$categoryCode] ?? 'secondary');
        @endphp
        <td class="{{ $colClass }}"></td>
        <td class="{{ $colClass }} text-center">
            @if(!$isDisabled && ($permissions['can_register'] ?? false))
                <input type="number"
                       class="form-control form-control-sm blank-votes-input text-center fw-bold"
                       data-table="{{ $table->id }}"
                       data-category="{{ $categoryCode }}"
                       value="{{ $blankQty }}"
                       min="0" step="1"
                       style="width:70px; margin:0 auto;"
                       title="Votos en blanco — {{ $categoryCode }}">
            @else
                <span class="fw-bold">{{ $blankQty }}</span>
            @endif
        </td>
        <td class="{{ $colClass }}"></td>
    @endforeach
</tr>
<tr class="table-light">
    <td class="text-center text-muted" style="font-size:0.7rem;">
        <i class="ri-close-line"></i>
    </td>
    <td class="text-end small fw-semibold text-muted pe-2" style="white-space:nowrap; font-size:0.78rem;">
        Nulos
    </td>
    @foreach($candidatesByCategory as $categoryCode => $_)
        @php
            $nullQty  = $table->results_by_category[$categoryCode]['null_votes'] ?? 0;
            $colClass = 'table-' . ($categoryColorMap[$categoryCode] ?? 'secondary');
        @endphp
        <td class="{{ $colClass }}"></td>
        <td class="{{ $colClass }} text-center">
            @if(!$isDisabled && ($permissions['can_register'] ?? false))
                <input type="number"
                       class="form-control form-control-sm null-votes-input text-center fw-bold"
                       data-table="{{ $table->id }}"
                       data-category="{{ $categoryCode }}"
                       value="{{ $nullQty }}"
                       min="0" step="1"
                       style="width:70px; margin:0 auto;"
                       title="Votos nulos — {{ $categoryCode }}">
            @else
                <span class="fw-bold">{{ $nullQty }}</span>
            @endif
        </td>
        <td class="{{ $colClass }}"></td>
    @endforeach
</tr>
<tr class="table-info fw-bold">
    <td colspan="2" class="text-end small">TOTALES</td>
    @foreach($candidatesByCategory as $categoryCode => $_)
        @php
            $catTotal = $table->results_by_category[$categoryCode]['total_votes'] ?? 0;
            $colClass = 'table-' . ($categoryColorMap[$categoryCode] ?? 'secondary');
        @endphp
        <td class="{{ $colClass }} text-center" colspan="2">
            <span id="total-{{ $categoryCode }}-{{ $table->id }}">{{ $catTotal }}</span>
        </td>
        <td class="{{ $colClass }}"></td>
    @endforeach
</tr>


