{{-- resources/views/voting-table-votes/partials/table-row.blade.php --}}
<div class="card mb-3 table-card {{ $table->status }}" id="table-{{ $table->id }}" data-table-id="{{ $table->id }}">
    <div class="card-header position-relative">
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
                    Mesa {{ $table->number }}
                    <small class="text-muted ms-2">{{ $table->internal_code ?? $table->oep_code }}</small>
                </h5>
                <small class="text-muted">{{ $table->institution->name ?? 'N/A' }}</small>
            </div>
            <div class="col-md-3">
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
                    $statusLabels = [
                        'configurada' => 'Configurada',
                        'en_espera' => 'En Espera',
                        'votacion' => 'Votación',
                        'cerrada' => 'Cerrada',
                        'en_escrutinio' => 'En Escrutinio',
                        'escrutada' => 'Escrutada',
                        'observada' => 'Observada',
                        'transmitida' => 'Transmitida',
                        'anulada' => 'Anulada'
                    ];
                @endphp
                <span class="badge bg-{{ $statusClasses[$table->status] ?? 'secondary' }} total-badge">
                    {{ $statusLabels[$table->status] ?? $table->status }}
                </span>
                <span class="ms-2 text-muted">
                    <i class="ri-group-line me-1"></i>
                    {{ number_format($table->expected_voters ?? 0) }}
                </span>
            </div>
            <div class="col-md-3">
                <span class="text-muted">
                    <i class="ri-bar-chart-line me-1"></i>
                    Votos: <span class="total-votes" id="total-{{ $table->id }}">{{ $table->total_voters ?? 0 }}</span>
                </span>
                @if(isset($table->observations_count) && $table->observations_count > 0)
                    <span class="badge bg-warning observation-badge ms-2"
                          onclick="showObservations({{ $table->id }})">
                        <i class="ri-chat-1-line me-1"></i>
                        {{ $table->observations_count }}
                    </span>
                @endif
                @if($table->acta_number)
                    <span class="badge bg-info ms-2">
                        <i class="ri-file-copy-line me-1"></i>
                        Acta
                    </span>
                @endif
            </div>
            <div class="col-md-3 text-end">
                <div class="action-buttons">
                    @if(!in_array($table->status, ['cerrada', 'escrutada', 'transmitida', 'anulada']))
                        @if($userCan['register'] && $table->validation_status !== 'validated')
                            <button class="btn btn-sm btn-success save-table" data-table-id="{{ $table->id }}" title="Guardar votos">
                                <i class="ri-save-line"></i>
                            </button>
                        @endif

                        @if($userCan['review'] && $table->validation_status === 'pending')
                            <button class="btn btn-sm btn-info review-table" data-table-id="{{ $table->id }}" title="Revisar">
                                <i class="ri-eye-line"></i>
                            </button>
                        @endif

                        @if($userCan['observe'])
                            <button class="btn btn-sm btn-warning observe-table" data-table-id="{{ $table->id }}" title="Observar">
                                <i class="ri-chat-1-line"></i>
                            </button>
                        @endif

                        @if($userCan['correct'] && $table->validation_status === 'observed')
                            <button class="btn btn-sm btn-warning correct-table" data-table-id="{{ $table->id }}" title="Corregir">
                                <i class="ri-refund-line"></i>
                            </button>
                        @endif

                        @if($userCan['validate'] && $table->validation_status === 'reviewed')
                            <button class="btn btn-sm btn-success validate-table" data-table-id="{{ $table->id }}" title="Validar">
                                <i class="ri-check-line"></i>
                            </button>
                        @endif

                        @if($userCan['upload_acta'])
                            <button class="btn btn-sm btn-info upload-acta" data-table-id="{{ $table->id }}" title="Subir acta">
                                <i class="ri-upload-line"></i>
                            </button>
                        @endif

                        @if($userCan['close'])
                            <button class="btn btn-sm btn-secondary close-table" data-table-id="{{ $table->id }}" title="Cerrar mesa">
                                <i class="ri-lock-line"></i>
                            </button>
                        @endif
                    @else
                        <span class="text-muted small">Cerrada</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card-body">
        <!-- Barra de estado de validación -->
        <div class="row mb-2">
            <div class="col-12">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <small class="text-muted">Estado:</small>

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
                        $validationLabels = [
                            'pending' => 'Pendiente',
                            'reviewed' => 'Revisado',
                            'observed' => 'Observado',
                            'corrected' => 'Corregido',
                            'validated' => 'Validado',
                            'approved' => 'Aprobado',
                            'rejected' => 'Rechazado'
                        ];
                    @endphp

                    <span class="badge bg-{{ $validationColors[$table->validation_status] ?? 'secondary' }}">
                        {{ $validationLabels[$table->validation_status] ?? $table->validation_status }}
                    </span>

                    @if($table->verified_by)
                        <small class="text-muted">
                            <i class="ri-user-line"></i> Revisado: {{ $table->verified_at ? \Carbon\Carbon::parse($table->verified_at)->format('d/m H:i') : '' }}
                        </small>
                    @endif

                    @if($table->validated_by)
                        <small class="text-muted">
                            <i class="ri-check-double-line"></i> Validado: {{ $table->validated_at ? \Carbon\Carbon::parse($table->validated_at)->format('d/m H:i') : '' }}
                        </small>
                    @endif

                    @if($table->ballots_received > 0)
                        <small class="text-muted">
                            <i class="ri-file-copy-line"></i> Papeletas: {{ $table->ballots_used }}/{{ $table->ballots_received }}
                        </small>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tabla de votos - ALCALDES -->
        <h6 class="text-primary mb-2">
            <i class="ri-user-star-line me-1"></i>
            Alcaldes
        </h6>
        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40%">Candidato</th>
                        <th style="width: 40%">Partido</th>
                        <th style="width: 20%">Votos</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $alcaldeCandidates = $candidatesByCategory['alcalde'] ?? collect();
                        $totalAlcalde = 0;
                    @endphp
                    @forelse($alcaldeCandidates as $candidate)
                        @php
                            $vote = $table->votes->firstWhere('candidate_id', $candidate->id);
                            $quantity = $vote ? $vote->quantity : 0;
                            $totalAlcalde += $quantity;
                            $isDisabled = in_array($table->status, ['cerrada', 'escrutada', 'transmitida', 'anulada']) ||
                                         ($table->validation_status === 'validated' && !$userCan['correct']) ||
                                         ($table->validation_status === 'approved');
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="candidate-color" style="background-color: {{ $candidate->color ?? '#0ab39c' }}; width: 20px; height: 20px; border-radius: 4px; display: inline-block; margin-right: 8px;"></span>
                                    <span class="ms-2">{{ $candidate->name }}</span>
                                </div>
                            </td>
                            <td>{{ $candidate->party }}</td>
                            <td>
                                <input type="number"
                                       class="form-control form-control-sm vote-input candidate-vote"
                                       data-table="{{ $table->id }}"
                                       data-candidate="{{ $candidate->id }}"
                                       data-category="alcalde"
                                       value="{{ $quantity }}"
                                       min="0"
                                       max="{{ $table->expected_voters ?? 9999 }}"
                                       {{ $isDisabled ? 'disabled' : '' }}>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">
                                No hay candidatos a Alcalde disponibles
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Tabla de votos - CONCEJALES -->
        <h6 class="text-success mb-2">
            <i class="ri-group-line me-1"></i>
            Concejales
        </h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40%">Candidato</th>
                        <th style="width: 40%">Partido</th>
                        <th style="width: 20%">Votos</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $concejalCandidates = $candidatesByCategory['concejal'] ?? collect();
                        $totalConcejal = 0;
                    @endphp
                    @forelse($concejalCandidates as $candidate)
                        @php
                            $vote = $table->votes->firstWhere('candidate_id', $candidate->id);
                            $quantity = $vote ? $vote->quantity : 0;
                            $totalConcejal += $quantity;
                            $isDisabled = in_array($table->status, ['cerrada', 'escrutada', 'transmitida', 'anulada']) ||
                                         ($table->validation_status === 'validated' && !$userCan['correct']) ||
                                         ($table->validation_status === 'approved');
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="candidate-color" style="background-color: {{ $candidate->color ?? '#0ab39c' }}; width: 20px; height: 20px; border-radius: 4px; display: inline-block; margin-right: 8px;"></span>
                                    <span class="ms-2">{{ $candidate->name }}</span>
                                </div>
                            </td>
                            <td>{{ $candidate->party }}</td>
                            <td>
                                <input type="number"
                                       class="form-control form-control-sm vote-input candidate-vote"
                                       data-table="{{ $table->id }}"
                                       data-candidate="{{ $candidate->id }}"
                                       data-category="concejal"
                                       value="{{ $quantity }}"
                                       min="0"
                                       max="{{ $table->expected_voters ?? 9999 }}"
                                       {{ $isDisabled ? 'disabled' : '' }}>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">
                                No hay candidatos a Concejal disponibles
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-info">
                    <tr>
                        <th colspan="2" class="text-end">Total Alcalde:</th>
                        <th>
                            <span class="fw-bold" id="total-alcalde-{{ $table->id }}">{{ $totalAlcalde }}</span>
                        </th>
                    </tr>
                    <tr>
                        <th colspan="2" class="text-end">Total Concejal:</th>
                        <th>
                            <span class="fw-bold" id="total-concejal-{{ $table->id }}">{{ $totalConcejal }}</span>
                        </th>
                    </tr>
                    <tr class="table-secondary">
                        <th colspan="2" class="text-end">Total General:</th>
                        <th>
                            <span class="fw-bold" id="total-{{ $table->id }}">{{ $totalAlcalde + $totalConcejal }}</span>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
