{{-- resources/views/voting-table-votes/partials/table.blade.php --}}
@php
    $isDisabled = in_array($table->status, ['cerrada', 'escrutada', 'transmitida', 'anulada']) ||
                  ($table->validation_status === 'validated' && !($userCan['correct'] ?? false)) ||
                  ($table->validation_status === 'approved');
@endphp

<div class="card mb-3 table-card status-{{ $table->status }}"
     id="table-{{ $table->id }}"
     data-table-id="{{ $table->id }}"
     data-expected-voters="{{ $table->expected_voters }}">
    <div class="card-header bg-light position-relative">
        @if($table->validation_status === 'observed' || $table->status === 'observada')
            <span class="badge bg-danger role-badge" title="Tiene observaciones">
                <i class="ri-alert-line me-1"></i>Observada
            </span>
        @elseif($table->validation_status === 'validated')
            <span class="badge bg-success role-badge">
                <i class="ri-check-line me-1"></i>Validada
            </span>
        @elseif($table->validation_status === 'approved')
            <span class="badge bg-primary role-badge">
                <i class="ri-check-double-line me-1"></i>Aprobada
            </span>
        @elseif($table->status === 'cerrada')
            <span class="badge bg-secondary role-badge">
                <i class="ri-lock-line me-1"></i>Cerrada
            </span>
        @endif

        <div class="row align-items-center">
            <div class="col-md-3">
                <h5 class="mb-0">
                    <i class="ri-table-line me-1"></i>
                    Mesa {{ $table->number }} - {{ $table->internal_code ?? $table->oep_code }}
                </h5>
                <small class="text-muted">{{ $table->institution->name ?? 'N/A' }}</small>
            </div>
            <div class="col-md-2">
                @php
                    $statusClasses = [
                        'configurada' => 'secondary',
                        'en_espera' => 'info',
                        'votacion' => 'primary',
                        'cerrada' => 'danger',
                        'en_escrutinio' => 'warning',
                        'escrutada' => 'success',
                        'observada' => 'danger',
                        'transmitida' => 'success',
                        'anulada' => 'dark'
                    ];
                @endphp
                <span class="badge bg-{{ $statusClasses[$table->status] ?? 'secondary' }}">
                    {{ $statusLabels[$table->status] ?? $table->status }}
                </span>
            </div>
            <div class="col-md-2">
                <span class="text-muted">
                    <i class="ri-group-line me-1"></i>
                    {{ number_format($table->expected_voters ?? 0) }}
                </span>
            </div>
            <div class="col-md-2">
                <span class="text-muted">
                    <i class="ri-bar-chart-line me-1"></i>
                    Votos: <span class="total-votes fw-bold" id="total-{{ $table->id }}">{{ $table->votes->sum('quantity') }}</span>
                </span>
            </div>
            <div class="col-md-3 text-end">
                <div class="btn-group btn-group-sm">
                    @if($table->actas_count > 0)
                        <button class="btn btn-info view-actas" data-table-id="{{ $table->id }}" title="Ver actas">
                            <i class="ri-file-copy-line"></i>
                            <span class="badge bg-white text-info ms-1">{{ $table->actas_count }}</span>
                        </button>
                    @endif

                    @if($table->observations_count > 0)
                        <button class="btn btn-warning view-observations" data-table-id="{{ $table->id }}" title="Ver observaciones">
                            <i class="ri-chat-1-line"></i>
                            <span class="badge bg-white text-warning ms-1">{{ $table->observations_count }}</span>
                        </button>
                    @endif

                    @if(!in_array($table->status, ['cerrada', 'escrutada', 'transmitida', 'anulada']))
                        @if($userCan['register'] && $table->validation_status !== 'validated')
                            <button class="btn btn-success save-table" data-table-id="{{ $table->id }}" title="Guardar (Ctrl+Enter)">
                                <i class="ri-save-line"></i>
                            </button>
                        @endif

                        @if($userCan['observe'])
                            <button class="btn btn-warning observe-table-general" data-table-id="{{ $table->id }}" title="Observación general">
                                <i class="ri-chat-1-line"></i>
                            </button>
                        @endif

                        @if($userCan['upload_acta'])
                            <button class="btn btn-info upload-acta" data-table-id="{{ $table->id }}" title="Subir acta">
                                <i class="ri-upload-line"></i>
                            </button>
                        @endif

                        @if($userCan['close'])
                            <button class="btn btn-secondary close-table" data-table-id="{{ $table->id }}" title="Cerrar mesa">
                                <i class="ri-lock-line"></i>
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-3 flex-wrap small">
                    <span class="text-muted">Validación:</span>
                    @php
                        $validationColors = [
                            'pending' => 'warning',
                            'reviewed' => 'info',
                            'observed' => 'danger',
                            'corrected' => 'primary',
                            'validated' => 'success',
                            'approved' => 'success',
                            'rejected' => 'dark'
                        ];
                    @endphp
                    <span class="badge bg-{{ $validationColors[$table->validation_status] ?? 'secondary' }}">
                        {{ $validationLabels[$table->validation_status] ?? $table->validation_status }}
                    </span>

                    @if($table->verified_by)
                        <span class="text-muted">
                            <i class="ri-user-line"></i> Revisado: {{ $table->verified_at ? \Carbon\Carbon::parse($table->verified_at)->format('d/m H:i') : '' }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="col-md-6 text-end">
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary view-toggle active" data-view="both" data-table="{{ $table->id }}">
                        <i class="ri-layout-column-line"></i> Ambos
                    </button>
                    <button type="button" class="btn btn-outline-primary view-toggle" data-view="alcaldes" data-table="{{ $table->id }}">
                        <i class="ri-user-star-line"></i> Solo Alcaldes
                    </button>
                    <button type="button" class="btn btn-outline-primary view-toggle" data-view="concejales" data-table="{{ $table->id }}">
                        <i class="ri-group-line"></i> Solo Concejales
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive view-both-{{ $table->id }}" style="display: block;">
            <table class="table table-sm table-bordered table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th rowspan="2" style="width: 3%; vertical-align: middle;">#</th>
                        <th rowspan="2" style="width: 10%; vertical-align: middle;">Partido</th>
                        <th colspan="3" class="text-center table-primary border-end">ALCALDES</th>
                        <th colspan="3" class="text-center table-success">CONCEJALES</th>
                    </tr>
                    <tr>
                        <th class="table-primary" style="width: 18%;">Candidato</th>
                        <th class="table-primary text-center" style="width: 7%;">Votos</th>
                        <th class="table-primary text-center" style="width: 5%;">Obs</th>
                        <th class="table-success" style="width: 18%;">Candidato</th>
                        <th class="table-success text-center" style="width: 7%;">Votos</th>
                        <th class="table-success text-center" style="width: 5%;">Obs</th>
                    </tr>
                </thead>
                <tbody>
                    @include('voting-table-votes.partials.table-row', [
                        'table' => $table,
                        'candidatesByCategory' => $candidatesByCategory,
                        'userCan' => $userCan,
                        'isDisabled' => $isDisabled,
                        'showBoth' => true
                    ])
                </tbody>
            </table>
        </div>
        <div class="table-responsive view-alcaldes-{{ $table->id }}" style="display: none;">
            <table class="table table-sm table-bordered table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 15%;">Partido</th>
                        <th style="width: 50%;" class="table-primary">Candidato Alcalde</th>
                        <th style="width: 20%;" class="table-primary text-center">Votos</th>
                        <th style="width: 10%;" class="table-primary text-center">Obs</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $alcaldes = $candidatesByCategory['alcalde'] ?? collect();
                        $totalAlcalde = 0;
                    @endphp

                    @foreach($alcaldes as $index => $alcalde)
                        @php
                            $voteAlcalde = $table->votes->firstWhere('candidate_id', $alcalde->id);
                            $quantityAlcalde = $voteAlcalde ? $voteAlcalde->quantity : 0;
                            $totalAlcalde += $quantityAlcalde;
                            $isAlcaldeObserved = $voteAlcalde && $voteAlcalde->vote_status === 'observed';
                        @endphp
                        <tr class="{{ $isAlcaldeObserved ? 'table-warning' : '' }}">
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @php
                                        $logo = $alcalde->party_logo ?? null;
                                        $color = $alcalde->color ?? '#0ab39c';
                                    @endphp
                                    @if($logo)
                                        <img src="{{ asset('storage/' . $logo) }}" width="20" height="20" class="me-1 rounded" style="object-fit: contain;">
                                    @else
                                        <span class="candidate-color" style="background-color: {{ $color }}; width: 16px; height: 16px; border-radius: 4px; display: inline-block; margin-right: 4px;"></span>
                                    @endif
                                    <span class="small">{{ $alcalde->party }}</span>
                                </div>
                            </td>
                            <td class="table-primary">
                                <div class="d-flex align-items-center">
                                    @if($alcalde->photo)
                                        <img src="{{ asset('storage/' . $alcalde->photo) }}" class="rounded-circle me-1" width="20" height="20" style="object-fit: cover;">
                                    @endif
                                    <span class="small">{{ $alcalde->name }}</span>
                                    @if($isAlcaldeObserved)
                                        <i class="ri-alert-line text-danger ms-1" title="Observado"></i>
                                    @endif
                                </div>
                            </td>
                            <td class="table-primary text-center">
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
                                       style="width: 80px; margin: 0 auto; {{ $isAlcaldeObserved ? 'border-color: #f06548;' : '' }}">
                            </td>
                            <td class="table-primary text-center">
                                @if($userCan['observe'] && !$isDisabled)
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
                        </tr>
                    @endforeach

                    {{-- NULO y BLANCO para Alcaldes --}}
                    @foreach(['nulo', 'blanco'] as $tipo)
                        @php
                            $candidate = $candidatesByCategory['alcalde']->firstWhere('type', $tipo . '_votes');
                            if ($candidate) {
                                $vote = $table->votes->firstWhere('candidate_id', $candidate->id);
                                $quantity = $vote ? $vote->quantity : 0;
                                $totalAlcalde += $quantity;
                                $isObserved = $vote && $vote->vote_status === 'observed';
                            }
                        @endphp
                        @if($candidate)
                        <tr class="table-secondary {{ $isObserved ? 'table-warning' : '' }}">
                            <td class="text-center">{{ $loop->index + $alcaldes->count() + 1 }}</td>
                            <td>-</td>
                            <td class="table-primary fw-bold">{{ ucfirst($tipo) }}</td>
                            <td class="table-primary text-center">
                                <input type="number"
                                       class="form-control form-control-sm vote-input text-center"
                                       data-table="{{ $table->id }}"
                                       data-candidate="{{ $candidate->id }}"
                                       data-category="alcalde"
                                       value="{{ $quantity }}"
                                       min="0"
                                       {{ $isDisabled ? 'disabled' : '' }}
                                       style="width: 80px; margin: 0 auto; {{ $isObserved ? 'border-color: #f06548;' : '' }}">
                            </td>
                            <td class="table-primary text-center">
                                @if($userCan['observe'] && !$isDisabled)
                                    <input type="checkbox"
                                           class="form-check-input observe-checkbox"
                                           data-table="{{ $table->id }}"
                                           data-candidate="{{ $candidate->id }}"
                                           data-category="alcalde"
                                           data-candidate-name="{{ ucfirst($tipo) }}"
                                           {{ $isObserved ? 'checked' : '' }}
                                           {{ $isObserved ? 'disabled' : '' }}>
                                @elseif($isObserved)
                                    <i class="ri-checkbox-circle-fill text-warning"></i>
                                @endif
                            </td>
                        </tr>
                        @endif
                    @endforeach

                    <tr class="table-info fw-bold">
                        <td colspan="3" class="text-end">TOTAL ALCALDES:</td>
                        <td class="table-primary text-center">
                            <span id="total-alcalde-{{ $table->id }}">{{ $totalAlcalde }}</span>
                        </td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="table-responsive view-concejales-{{ $table->id }}" style="display: none;">
            <table class="table table-sm table-bordered table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 15%;">Partido</th>
                        <th style="width: 50%;" class="table-success">Candidato Concejal</th>
                        <th style="width: 20%;" class="table-success text-center">Votos</th>
                        <th style="width: 10%;" class="table-success text-center">Obs</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $concejales = $candidatesByCategory['concejal'] ?? collect();
                        $totalConcejal = 0;
                    @endphp

                    @foreach($concejales as $index => $concejal)
                        @php
                            $voteConcejal = $table->votes->firstWhere('candidate_id', $concejal->id);
                            $quantityConcejal = $voteConcejal ? $voteConcejal->quantity : 0;
                            $totalConcejal += $quantityConcejal;
                            $isConcejalObserved = $voteConcejal && $voteConcejal->vote_status === 'observed';
                        @endphp
                        <tr class="{{ $isConcejalObserved ? 'table-warning' : '' }}">
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @php
                                        $logo = $concejal->party_logo ?? null;
                                        $color = $concejal->color ?? '#0ab39c';
                                    @endphp
                                    @if($logo)
                                        <img src="{{ asset('storage/' . $logo) }}" width="20" height="20" class="me-1 rounded" style="object-fit: contain;">
                                    @else
                                        <span class="candidate-color" style="background-color: {{ $color }}; width: 16px; height: 16px; border-radius: 4px; display: inline-block; margin-right: 4px;"></span>
                                    @endif
                                    <span class="small">{{ $concejal->party }}</span>
                                </div>
                            </td>
                            <td class="table-success">
                                <div class="d-flex align-items-center">
                                    @if($concejal->photo)
                                        <img src="{{ asset('storage/' . $concejal->photo) }}" class="rounded-circle me-1" width="20" height="20" style="object-fit: cover;">
                                    @endif
                                    <span class="small">{{ $concejal->name }}</span>
                                    @if($isConcejalObserved)
                                        <i class="ri-alert-line text-danger ms-1" title="Observado"></i>
                                    @endif
                                </div>
                            </td>
                            <td class="table-success text-center">
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
                                       style="width: 80px; margin: 0 auto; {{ $isConcejalObserved ? 'border-color: #f06548;' : '' }}">
                            </td>
                            <td class="table-success text-center">
                                @if($userCan['observe'] && !$isDisabled)
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
                    @endforeach

                    {{-- NULO y BLANCO para Concejales --}}
                    @foreach(['nulo', 'blanco'] as $tipo)
                        @php
                            $candidate = $candidatesByCategory['concejal']->firstWhere('type', $tipo . '_votes');
                            if ($candidate) {
                                $vote = $table->votes->firstWhere('candidate_id', $candidate->id);
                                $quantity = $vote ? $vote->quantity : 0;
                                $totalConcejal += $quantity;
                                $isObserved = $vote && $vote->vote_status === 'observed';
                            }
                        @endphp
                        @if($candidate)
                        <tr class="table-secondary {{ $isObserved ? 'table-warning' : '' }}">
                            <td class="text-center">{{ $loop->index + $concejales->count() + 1 }}</td>
                            <td>-</td>
                            <td class="table-success fw-bold">{{ ucfirst($tipo) }}</td>
                            <td class="table-success text-center">
                                <input type="number"
                                       class="form-control form-control-sm vote-input text-center"
                                       data-table="{{ $table->id }}"
                                       data-candidate="{{ $candidate->id }}"
                                       data-category="concejal"
                                       value="{{ $quantity }}"
                                       min="0"
                                       {{ $isDisabled ? 'disabled' : '' }}
                                       style="width: 80px; margin: 0 auto; {{ $isObserved ? 'border-color: #f06548;' : '' }}">
                            </td>
                            <td class="table-success text-center">
                                @if($userCan['observe'] && !$isDisabled)
                                    <input type="checkbox"
                                           class="form-check-input observe-checkbox"
                                           data-table="{{ $table->id }}"
                                           data-candidate="{{ $candidate->id }}"
                                           data-category="concejal"
                                           data-candidate-name="{{ ucfirst($tipo) }}"
                                           {{ $isObserved ? 'checked' : '' }}
                                           {{ $isObserved ? 'disabled' : '' }}>
                                @elseif($isObserved)
                                    <i class="ri-checkbox-circle-fill text-warning"></i>
                                @endif
                            </td>
                        </tr>
                        @endif
                    @endforeach

                    <tr class="table-info fw-bold">
                        <td colspan="3" class="text-end">TOTAL CONCEJALES:</td>
                        <td class="table-success text-center">
                            <span id="total-concejal-{{ $table->id }}">{{ $totalConcejal }}</span>
                        </td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
        @if($userCan['observe'] && !$isDisabled)
        <div class="p-2 bg-light border-top">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="text-muted" id="selected-count-{{ $table->id }}">0</span> votos seleccionados para observar
                    <span class="badge bg-primary ms-2" id="selected-alcaldes-{{ $table->id }}">0 Alcaldes</span>
                    <span class="badge bg-success ms-1" id="selected-concejales-{{ $table->id }}">0 Concejales</span>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-sm btn-warning create-observation-btn"
                            data-table-id="{{ $table->id }}"
                            id="create-observation-{{ $table->id }}">
                        <i class="ri-chat-1-line me-1"></i>
                        Crear Observación con Seleccionados
                    </button>
                </div>
            </div>
        </div>
        @endif
        <div class="row g-0 bg-light p-2 border-top small">
            <div class="col-md-3">
                <span class="text-muted">Votos Válidos:</span>
                <span class="fw-bold ms-1">{{ $table->valid_votes ?? 0 }}</span>
            </div>
            <div class="col-md-3">
                <span class="text-muted">Votos en Blanco:</span>
                <span class="fw-bold ms-1">{{ $table->blank_votes ?? 0 }}</span>
            </div>
            <div class="col-md-3">
                <span class="text-muted">Votos Nulos:</span>
                <span class="fw-bold ms-1">{{ $table->null_votes ?? 0 }}</span>
            </div>
            <div class="col-md-3">
                <span class="text-muted">Papeletas Sobrantes:</span>
                <span class="fw-bold ms-1">{{ $table->ballots_leftover ?? 0 }}</span>
            </div>
        </div>
    </div>
</div>
