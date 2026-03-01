
<div class="card mb-3 table-card <?php echo e($table->status); ?>" id="table-<?php echo e($table->id); ?>" data-table-id="<?php echo e($table->id); ?>">
    <div class="card-header position-relative">
        <?php if($table->validation_status === 'observed' || $table->status === 'observada'): ?>
            <span class="badge bg-danger role-badge" title="Tiene observaciones">
                <i class="ri-alert-line me-1"></i>Observada
            </span>
        <?php elseif($table->validation_status === 'validated'): ?>
            <span class="badge bg-success role-badge">
                <i class="ri-check-line me-1"></i>Validada
            </span>
        <?php elseif($table->validation_status === 'approved'): ?>
            <span class="badge bg-primary role-badge">
                <i class="ri-check-double-line me-1"></i>Aprobada
            </span>
        <?php elseif($table->status === 'cerrada'): ?>
            <span class="badge bg-secondary role-badge">
                <i class="ri-lock-line me-1"></i>Cerrada
            </span>
        <?php endif; ?>

        <div class="row align-items-center">
            <div class="col-md-3">
                <h5 class="mb-0">
                    <i class="ri-table-line me-1"></i>
                    Mesa <?php echo e($table->number); ?>

                    <small class="text-muted ms-2"><?php echo e($table->internal_code ?? $table->oep_code); ?></small>
                </h5>
                <small class="text-muted"><?php echo e($table->institution->name ?? 'N/A'); ?></small>
            </div>
            <div class="col-md-3">
                <?php
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
                ?>
                <span class="badge bg-<?php echo e($statusClasses[$table->status] ?? 'secondary'); ?> total-badge">
                    <?php echo e($statusLabels[$table->status] ?? $table->status); ?>

                </span>
                <span class="ms-2 text-muted">
                    <i class="ri-group-line me-1"></i>
                    <?php echo e(number_format($table->expected_voters ?? 0)); ?>

                </span>
            </div>
            <div class="col-md-3">
                <span class="text-muted">
                    <i class="ri-bar-chart-line me-1"></i>
                    Votos: <span class="total-votes" id="total-<?php echo e($table->id); ?>"><?php echo e($table->total_voters ?? 0); ?></span>
                </span>
                <?php if(isset($table->observations_count) && $table->observations_count > 0): ?>
                    <span class="badge bg-warning observation-badge ms-2"
                          onclick="showObservations(<?php echo e($table->id); ?>)">
                        <i class="ri-chat-1-line me-1"></i>
                        <?php echo e($table->observations_count); ?>

                    </span>
                <?php endif; ?>
                <?php if($table->acta_number): ?>
                    <span class="badge bg-info ms-2">
                        <i class="ri-file-copy-line me-1"></i>
                        Acta
                    </span>
                <?php endif; ?>
            </div>
            <div class="col-md-3 text-end">
                <div class="action-buttons">
                    <?php if(!in_array($table->status, ['cerrada', 'escrutada', 'transmitida', 'anulada'])): ?>
                        <?php if($userCan['register'] && $table->validation_status !== 'validated'): ?>
                            <button class="btn btn-sm btn-success save-table" data-table-id="<?php echo e($table->id); ?>" title="Guardar votos">
                                <i class="ri-save-line"></i>
                            </button>
                        <?php endif; ?>

                        <?php if($userCan['review'] && $table->validation_status === 'pending'): ?>
                            <button class="btn btn-sm btn-info review-table" data-table-id="<?php echo e($table->id); ?>" title="Revisar">
                                <i class="ri-eye-line"></i>
                            </button>
                        <?php endif; ?>

                        <?php if($userCan['observe']): ?>
                            <button class="btn btn-sm btn-warning observe-table" data-table-id="<?php echo e($table->id); ?>" title="Observar">
                                <i class="ri-chat-1-line"></i>
                            </button>
                        <?php endif; ?>

                        <?php if($userCan['correct'] && $table->validation_status === 'observed'): ?>
                            <button class="btn btn-sm btn-warning correct-table" data-table-id="<?php echo e($table->id); ?>" title="Corregir">
                                <i class="ri-refund-line"></i>
                            </button>
                        <?php endif; ?>

                        <?php if($userCan['validate'] && $table->validation_status === 'reviewed'): ?>
                            <button class="btn btn-sm btn-success validate-table" data-table-id="<?php echo e($table->id); ?>" title="Validar">
                                <i class="ri-check-line"></i>
                            </button>
                        <?php endif; ?>

                        <?php if($userCan['upload_acta']): ?>
                            <button class="btn btn-sm btn-info upload-acta" data-table-id="<?php echo e($table->id); ?>" title="Subir acta">
                                <i class="ri-upload-line"></i>
                            </button>
                        <?php endif; ?>

                        <?php if($userCan['close']): ?>
                            <button class="btn btn-sm btn-secondary close-table" data-table-id="<?php echo e($table->id); ?>" title="Cerrar mesa">
                                <i class="ri-lock-line"></i>
                            </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="text-muted small">Cerrada</span>
                    <?php endif; ?>
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

                    <?php
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
                    ?>

                    <span class="badge bg-<?php echo e($validationColors[$table->validation_status] ?? 'secondary'); ?>">
                        <?php echo e($validationLabels[$table->validation_status] ?? $table->validation_status); ?>

                    </span>

                    <?php if($table->verified_by): ?>
                        <small class="text-muted">
                            <i class="ri-user-line"></i> Revisado: <?php echo e($table->verified_at ? \Carbon\Carbon::parse($table->verified_at)->format('d/m H:i') : ''); ?>

                        </small>
                    <?php endif; ?>

                    <?php if($table->validated_by): ?>
                        <small class="text-muted">
                            <i class="ri-check-double-line"></i> Validado: <?php echo e($table->validated_at ? \Carbon\Carbon::parse($table->validated_at)->format('d/m H:i') : ''); ?>

                        </small>
                    <?php endif; ?>

                    <?php if($table->ballots_received > 0): ?>
                        <small class="text-muted">
                            <i class="ri-file-copy-line"></i> Papeletas: <?php echo e($table->ballots_used); ?>/<?php echo e($table->ballots_received); ?>

                        </small>
                    <?php endif; ?>
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
                    <?php
                        $alcaldeCandidates = $candidatesByCategory['alcalde'] ?? collect();
                        $totalAlcalde = 0;
                    ?>
                    <?php $__empty_1 = true; $__currentLoopData = $alcaldeCandidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $vote = $table->votes->firstWhere('candidate_id', $candidate->id);
                            $quantity = $vote ? $vote->quantity : 0;
                            $totalAlcalde += $quantity;
                            $isDisabled = in_array($table->status, ['cerrada', 'escrutada', 'transmitida', 'anulada']) ||
                                         ($table->validation_status === 'validated' && !$userCan['correct']) ||
                                         ($table->validation_status === 'approved');
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="candidate-color" style="background-color: <?php echo e($candidate->color ?? '#0ab39c'); ?>; width: 20px; height: 20px; border-radius: 4px; display: inline-block; margin-right: 8px;"></span>
                                    <span class="ms-2"><?php echo e($candidate->name); ?></span>
                                </div>
                            </td>
                            <td><?php echo e($candidate->party); ?></td>
                            <td>
                                <input type="number"
                                       class="form-control form-control-sm vote-input candidate-vote"
                                       data-table="<?php echo e($table->id); ?>"
                                       data-candidate="<?php echo e($candidate->id); ?>"
                                       data-category="alcalde"
                                       value="<?php echo e($quantity); ?>"
                                       min="0"
                                       max="<?php echo e($table->expected_voters ?? 9999); ?>"
                                       <?php echo e($isDisabled ? 'disabled' : ''); ?>>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted">
                                No hay candidatos a Alcalde disponibles
                            </td>
                        </tr>
                    <?php endif; ?>
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
                    <?php
                        $concejalCandidates = $candidatesByCategory['concejal'] ?? collect();
                        $totalConcejal = 0;
                    ?>
                    <?php $__empty_1 = true; $__currentLoopData = $concejalCandidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $vote = $table->votes->firstWhere('candidate_id', $candidate->id);
                            $quantity = $vote ? $vote->quantity : 0;
                            $totalConcejal += $quantity;
                            $isDisabled = in_array($table->status, ['cerrada', 'escrutada', 'transmitida', 'anulada']) ||
                                         ($table->validation_status === 'validated' && !$userCan['correct']) ||
                                         ($table->validation_status === 'approved');
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="candidate-color" style="background-color: <?php echo e($candidate->color ?? '#0ab39c'); ?>; width: 20px; height: 20px; border-radius: 4px; display: inline-block; margin-right: 8px;"></span>
                                    <span class="ms-2"><?php echo e($candidate->name); ?></span>
                                </div>
                            </td>
                            <td><?php echo e($candidate->party); ?></td>
                            <td>
                                <input type="number"
                                       class="form-control form-control-sm vote-input candidate-vote"
                                       data-table="<?php echo e($table->id); ?>"
                                       data-candidate="<?php echo e($candidate->id); ?>"
                                       data-category="concejal"
                                       value="<?php echo e($quantity); ?>"
                                       min="0"
                                       max="<?php echo e($table->expected_voters ?? 9999); ?>"
                                       <?php echo e($isDisabled ? 'disabled' : ''); ?>>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted">
                                No hay candidatos a Concejal disponibles
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-info">
                    <tr>
                        <th colspan="2" class="text-end">Total Alcalde:</th>
                        <th>
                            <span class="fw-bold" id="total-alcalde-<?php echo e($table->id); ?>"><?php echo e($totalAlcalde); ?></span>
                        </th>
                    </tr>
                    <tr>
                        <th colspan="2" class="text-end">Total Concejal:</th>
                        <th>
                            <span class="fw-bold" id="total-concejal-<?php echo e($table->id); ?>"><?php echo e($totalConcejal); ?></span>
                        </th>
                    </tr>
                    <tr class="table-secondary">
                        <th colspan="2" class="text-end">Total General:</th>
                        <th>
                            <span class="fw-bold" id="total-<?php echo e($table->id); ?>"><?php echo e($totalAlcalde + $totalConcejal); ?></span>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<?php /**PATH D:\_Mine\corporate\resources\views/voting-table-votes/partials/table-row.blade.php ENDPATH**/ ?>