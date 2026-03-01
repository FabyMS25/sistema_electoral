{{-- resources/views/voting-table-votes/partials/table-row.blade.php --}}
<div class="card mb-3 table-card {{ $table->status }}" id="table-{{ $table->id }}">
    <div class="card-header position-relative">
        @if($table->validation_status === 'observed')
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
        @endif
        
        <div class="row align-items-center">
            <div class="col-md-3">
                <h5 class="mb-0">
                    <i class="ri-table-line me-1"></i>
                    Mesa {{ $table->number }}
                    <small class="text-muted ms-2">{{ $table->code }}</small>
                </h5>
                <small class="text-muted">{{ $table->institution->name ?? 'N/A' }}</small>
            </div>
            <div class="col-md-3">
                @php
                    $statusClasses = [
                        'pendiente' => 'warning',
                        'en_proceso' => 'info',
                        'activo' => 'success',
                        'cerrado' => 'danger',
                        'en_computo' => 'primary',
                        'computado' => 'success',
                        'observado' => 'danger',
                        'anulado' => 'dark',
                        'validado' => 'success',
                        'aprobado' => 'primary'
                    ];
                    $statusLabels = [
                        'pendiente' => 'Pendiente',
                        'en_proceso' => 'En Proceso',
                        'activo' => 'Activo',
                        'cerrado' => 'Cerrado',
                        'en_computo' => 'En Cómputo',
                        'computado' => 'Computado',
                        'observado' => 'Observado',
                        'anulado' => 'Anulado',
                        'validado' => 'Validado',
                        'aprobado' => 'Aprobado'
                    ];
                @endphp
                <span class="badge bg-{{ $statusClasses[$table->status] ?? 'secondary' }} total-badge">
                    {{ $statusLabels[$table->status] ?? $table->status }}
                </span>
                <span class="ms-2 text-muted">
                    <i class="ri-group-line me-1"></i>
                    {{ number_format($table->registered_citizens ?? 0) }}
                </span>
            </div>
            <div class="col-md-3">
                <span class="text-muted">
                    <i class="ri-bar-chart-line me-1"></i>
                    Votos: <span id="total-{{ $table->id }}">{{ $table->votes->sum('quantity') }}</span>
                </span>
                @if($table->observations_count > 0)
                    <span class="badge bg-warning observation-badge ms-2" 
                          onclick="showObservations({{ $table->id }})">
                        <i class="ri-chat-1-line me-1"></i>
                        {{ $table->observations_count }}
                    </span>
                @endif
                @if($table->actas_count > 0)
                    <span class="badge bg-info ms-2">
                        <i class="ri-file-copy-line me-1"></i>
                        {{ $table->actas_count }}
                    </span>
                @endif
            </div>
            <div class="col-md-3 text-end">
                <div class="action-buttons">
                    @if($table->status !== 'cerrado')
                        @if($userCan['register'] && $table->validation_status !== 'validated')
                            <button class="btn btn-sm btn-success save-table" data-table-id="{{ $table->id }}">
                                <i class="ri-save-line"></i>
                            </button>
                        @endif
                        
                        @if($userCan['review'] && $table->validation_status === 'pending')
                            <button class="btn btn-sm btn-info review-table" data-table-id="{{ $table->id }}">
                                <i class="ri-eye-line"></i>
                            </button>
                        @endif
                        
                        @if($userCan['observe'])
                            <button class="btn btn-sm btn-warning observe-table" data-table-id="{{ $table->id }}">
                                <i class="ri-chat-1-line"></i>
                            </button>
                        @endif
                        
                        @if($userCan['correct'] && $table->validation_status === 'observed')
                            <button class="btn btn-sm btn-warning correct-table" data-table-id="{{ $table->id }}">
                                <i class="ri-refund-line"></i>
                            </button>
                        @endif
                        
                        @if($userCan['validate'] && $table->validation_status === 'reviewed')
                            <button class="btn btn-sm btn-success validate-table" data-table-id="{{ $table->id }}">
                                <i class="ri-check-line"></i>
                            </button>
                        @endif
                        
                        @if($userCan['upload_acta'])
                            <button class="btn btn-sm btn-info upload-acta" data-table-id="{{ $table->id }}">
                                <i class="ri-upload-line"></i>
                            </button>
                        @endif
                        
                        @if($userCan['close'] && auth()->user()->hasPermission('close_tables'))
                            <button class="btn btn-sm btn-secondary close-table" data-table-id="{{ $table->id }}">
                                <i class="ri-lock-line"></i>
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Barra de estado de validación -->
        <div class="row mb-2">
            <div class="col-12">
                <div class="d-flex align-items-center gap-2">
                    <small class="text-muted">Estado:</small>
                    <span class="validation-status {{ $table->validation_status === 'validated' ? 'bg-success text-white' : ($table->validation_status === 'observed' ? 'bg-danger text-white' : 'bg-light') }}">
                        {{ ucfirst($table->validation_status ?? 'pending') }}
                    </span>
                    @if($table->verified_by)
                        <small class="text-muted">
                            Revisado por: {{ $table->verifier->name ?? 'N/A' }}
                        </small>
                    @endif
                    @if($table->validated_by)
                        <small class="text-muted">
                            Validado por: {{ $table->validator->name ?? 'N/A' }}
                        </small>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tabla de votos -->
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
                    @forelse($candidates as $candidate)
                        @php
                            $vote = $table->votes->firstWhere('candidate_id', $candidate->id);
                            $quantity = $vote ? $vote->quantity : 0;
                            $isDisabled = $table->status === 'cerrado' || 
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
                                       class="form-control vote-input candidate-vote" 
                                       data-table="{{ $table->id }}"
                                       data-candidate="{{ $candidate->id }}"
                                       value="{{ $quantity }}"
                                       min="0"
                                       max="{{ $table->registered_citizens ?? 9999 }}"
                                       {{ $isDisabled ? 'disabled' : '' }}>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">
                                No hay candidatos disponibles para esta elección
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-info">
                    <tr>
                        <th colspan="2" class="text-end">Total Votos:</th>
                        <th>
                            <span class="fw-bold">
                                {{ $table->votes->sum('quantity') }}
                            </span>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>