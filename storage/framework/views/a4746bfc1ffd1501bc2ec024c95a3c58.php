
<?php
    $isDisabled = in_array($table->status, ['cerrada', 'escrutada', 'transmitida', 'anulada']) ||
                  ($table->validation_status === 'validated' && !($userCan['correct'] ?? false)) ||
                  ($table->validation_status === 'approved');
?>

<div class="card mb-3 table-card status-<?php echo e($table->status); ?>"
     id="table-<?php echo e($table->id); ?>"
     data-table-id="<?php echo e($table->id); ?>"
     data-expected-voters="<?php echo e($table->expected_voters); ?>">
    <div class="card-header bg-light position-relative">
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
                    Mesa <?php echo e($table->number); ?> - <?php echo e($table->internal_code ?? $table->oep_code); ?>

                </h5>
                <small class="text-muted"><?php echo e($table->institution->name ?? 'N/A'); ?></small>
            </div>
            <div class="col-md-2">
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
                ?>
                <span class="badge bg-<?php echo e($statusClasses[$table->status] ?? 'secondary'); ?>">
                    <?php echo e($statusLabels[$table->status] ?? $table->status); ?>

                </span>
            </div>
            <div class="col-md-2">
                <span class="text-muted">
                    <i class="ri-group-line me-1"></i>
                    <?php echo e(number_format($table->expected_voters ?? 0)); ?>

                </span>
            </div>
            <div class="col-md-2">
                <span class="text-muted">
                    <i class="ri-bar-chart-line me-1"></i>
                    Votos: <span class="total-votes fw-bold" id="total-<?php echo e($table->id); ?>"><?php echo e($table->votes->sum('quantity')); ?></span>
                </span>
            </div>
            <div class="col-md-3 text-end">
                <div class="btn-group btn-group-sm">
                    <?php if($table->actas_count > 0): ?>
                        <button class="btn btn-info view-actas" data-table-id="<?php echo e($table->id); ?>" title="Ver actas">
                            <i class="ri-file-copy-line"></i>
                            <span class="badge bg-white text-info ms-1"><?php echo e($table->actas_count); ?></span>
                        </button>
                    <?php endif; ?>

                    <?php if($table->observations_count > 0): ?>
                        <button class="btn btn-warning view-observations" data-table-id="<?php echo e($table->id); ?>" title="Ver observaciones">
                            <i class="ri-chat-1-line"></i>
                            <span class="badge bg-white text-warning ms-1"><?php echo e($table->observations_count); ?></span>
                        </button>
                    <?php endif; ?>

                    <?php if(!in_array($table->status, ['cerrada', 'escrutada', 'transmitida', 'anulada'])): ?>
                        <?php if($userCan['register'] && $table->validation_status !== 'validated'): ?>
                            <button class="btn btn-success save-table" data-table-id="<?php echo e($table->id); ?>" title="Guardar (Ctrl+Enter)">
                                <i class="ri-save-line"></i>
                            </button>
                        <?php endif; ?>

                        <?php if($userCan['observe']): ?>
                            <button class="btn btn-warning observe-table-general" data-table-id="<?php echo e($table->id); ?>" title="Observación general">
                                <i class="ri-chat-1-line"></i>
                            </button>
                        <?php endif; ?>

                        <?php if($userCan['upload_acta']): ?>
                            <button class="btn btn-info upload-acta" data-table-id="<?php echo e($table->id); ?>" title="Subir acta">
                                <i class="ri-upload-line"></i>
                            </button>
                        <?php endif; ?>

                        <?php if($userCan['close']): ?>
                            <button class="btn btn-secondary close-table" data-table-id="<?php echo e($table->id); ?>" title="Cerrar mesa">
                                <i class="ri-lock-line"></i>
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-3 flex-wrap small">
                    <span class="text-muted">Validación:</span>
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
                    ?>
                    <span class="badge bg-<?php echo e($validationColors[$table->validation_status] ?? 'secondary'); ?>">
                        <?php echo e($validationLabels[$table->validation_status] ?? $table->validation_status); ?>

                    </span>

                    <?php if($table->verified_by): ?>
                        <span class="text-muted">
                            <i class="ri-user-line"></i> Revisado: <?php echo e($table->verified_at ? \Carbon\Carbon::parse($table->verified_at)->format('d/m H:i') : ''); ?>

                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary view-toggle active" data-view="both" data-table="<?php echo e($table->id); ?>">
                        <i class="ri-layout-column-line"></i> Ambos
                    </button>
                    <button type="button" class="btn btn-outline-primary view-toggle" data-view="alcaldes" data-table="<?php echo e($table->id); ?>">
                        <i class="ri-user-star-line"></i> Solo Alcaldes
                    </button>
                    <button type="button" class="btn btn-outline-primary view-toggle" data-view="concejales" data-table="<?php echo e($table->id); ?>">
                        <i class="ri-group-line"></i> Solo Concejales
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive view-both-<?php echo e($table->id); ?>" style="display: block;">
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
                    <?php echo $__env->make('voting-table-votes.partials.table-row', [
                        'table' => $table,
                        'candidatesByCategory' => $candidatesByCategory,
                        'userCan' => $userCan,
                        'isDisabled' => $isDisabled,
                        'showBoth' => true
                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </tbody>
            </table>
        </div>
        <div class="table-responsive view-alcaldes-<?php echo e($table->id); ?>" style="display: none;">
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
                    <?php
                        $alcaldes = $candidatesByCategory['alcalde'] ?? collect();
                        $totalAlcalde = 0;
                    ?>

                    <?php $__currentLoopData = $alcaldes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $alcalde): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $voteAlcalde = $table->votes->firstWhere('candidate_id', $alcalde->id);
                            $quantityAlcalde = $voteAlcalde ? $voteAlcalde->quantity : 0;
                            $totalAlcalde += $quantityAlcalde;
                            $isAlcaldeObserved = $voteAlcalde && $voteAlcalde->vote_status === 'observed';
                        ?>
                        <tr class="<?php echo e($isAlcaldeObserved ? 'table-warning' : ''); ?>">
                            <td class="text-center"><?php echo e($index + 1); ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php
                                        $logo = $alcalde->party_logo ?? null;
                                        $color = $alcalde->color ?? '#0ab39c';
                                    ?>
                                    <?php if($logo): ?>
                                        <img src="<?php echo e(asset('storage/' . $logo)); ?>" width="20" height="20" class="me-1 rounded" style="object-fit: contain;">
                                    <?php else: ?>
                                        <span class="candidate-color" style="background-color: <?php echo e($color); ?>; width: 16px; height: 16px; border-radius: 4px; display: inline-block; margin-right: 4px;"></span>
                                    <?php endif; ?>
                                    <span class="small"><?php echo e($alcalde->party); ?></span>
                                </div>
                            </td>
                            <td class="table-primary">
                                <div class="d-flex align-items-center">
                                    <?php if($alcalde->photo): ?>
                                        <img src="<?php echo e(asset('storage/' . $alcalde->photo)); ?>" class="rounded-circle me-1" width="20" height="20" style="object-fit: cover;">
                                    <?php endif; ?>
                                    <span class="small"><?php echo e($alcalde->name); ?></span>
                                    <?php if($isAlcaldeObserved): ?>
                                        <i class="ri-alert-line text-danger ms-1" title="Observado"></i>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="table-primary text-center">
                                <input type="number"
                                       class="form-control form-control-sm vote-input text-center"
                                       data-table="<?php echo e($table->id); ?>"
                                       data-candidate="<?php echo e($alcalde->id); ?>"
                                       data-category="alcalde"
                                       value="<?php echo e($quantityAlcalde); ?>"
                                       min="0"
                                       max="<?php echo e($table->expected_voters ?? 9999); ?>"
                                       step="1"
                                       <?php echo e($isDisabled ? 'disabled' : ''); ?>

                                       style="width: 80px; margin: 0 auto; <?php echo e($isAlcaldeObserved ? 'border-color: #f06548;' : ''); ?>">
                            </td>
                            <td class="table-primary text-center">
                                <?php if($userCan['observe'] && !$isDisabled): ?>
                                    <input type="checkbox"
                                           class="form-check-input observe-checkbox"
                                           data-table="<?php echo e($table->id); ?>"
                                           data-candidate="<?php echo e($alcalde->id); ?>"
                                           data-category="alcalde"
                                           data-candidate-name="<?php echo e($alcalde->name); ?>"
                                           <?php echo e($isAlcaldeObserved ? 'checked' : ''); ?>

                                           <?php echo e($isAlcaldeObserved ? 'disabled' : ''); ?>

                                           title="Marcar como observado">
                                <?php elseif($isAlcaldeObserved): ?>
                                    <i class="ri-checkbox-circle-fill text-warning" title="Observado"></i>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    
                    <?php $__currentLoopData = ['nulo', 'blanco']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $candidate = $candidatesByCategory['alcalde']->firstWhere('type', $tipo . '_votes');
                            if ($candidate) {
                                $vote = $table->votes->firstWhere('candidate_id', $candidate->id);
                                $quantity = $vote ? $vote->quantity : 0;
                                $totalAlcalde += $quantity;
                                $isObserved = $vote && $vote->vote_status === 'observed';
                            }
                        ?>
                        <?php if($candidate): ?>
                        <tr class="table-secondary <?php echo e($isObserved ? 'table-warning' : ''); ?>">
                            <td class="text-center"><?php echo e($loop->index + $alcaldes->count() + 1); ?></td>
                            <td>-</td>
                            <td class="table-primary fw-bold"><?php echo e(ucfirst($tipo)); ?></td>
                            <td class="table-primary text-center">
                                <input type="number"
                                       class="form-control form-control-sm vote-input text-center"
                                       data-table="<?php echo e($table->id); ?>"
                                       data-candidate="<?php echo e($candidate->id); ?>"
                                       data-category="alcalde"
                                       value="<?php echo e($quantity); ?>"
                                       min="0"
                                       <?php echo e($isDisabled ? 'disabled' : ''); ?>

                                       style="width: 80px; margin: 0 auto; <?php echo e($isObserved ? 'border-color: #f06548;' : ''); ?>">
                            </td>
                            <td class="table-primary text-center">
                                <?php if($userCan['observe'] && !$isDisabled): ?>
                                    <input type="checkbox"
                                           class="form-check-input observe-checkbox"
                                           data-table="<?php echo e($table->id); ?>"
                                           data-candidate="<?php echo e($candidate->id); ?>"
                                           data-category="alcalde"
                                           data-candidate-name="<?php echo e(ucfirst($tipo)); ?>"
                                           <?php echo e($isObserved ? 'checked' : ''); ?>

                                           <?php echo e($isObserved ? 'disabled' : ''); ?>>
                                <?php elseif($isObserved): ?>
                                    <i class="ri-checkbox-circle-fill text-warning"></i>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <tr class="table-info fw-bold">
                        <td colspan="3" class="text-end">TOTAL ALCALDES:</td>
                        <td class="table-primary text-center">
                            <span id="total-alcalde-<?php echo e($table->id); ?>"><?php echo e($totalAlcalde); ?></span>
                        </td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="table-responsive view-concejales-<?php echo e($table->id); ?>" style="display: none;">
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
                    <?php
                        $concejales = $candidatesByCategory['concejal'] ?? collect();
                        $totalConcejal = 0;
                    ?>

                    <?php $__currentLoopData = $concejales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $concejal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $voteConcejal = $table->votes->firstWhere('candidate_id', $concejal->id);
                            $quantityConcejal = $voteConcejal ? $voteConcejal->quantity : 0;
                            $totalConcejal += $quantityConcejal;
                            $isConcejalObserved = $voteConcejal && $voteConcejal->vote_status === 'observed';
                        ?>
                        <tr class="<?php echo e($isConcejalObserved ? 'table-warning' : ''); ?>">
                            <td class="text-center"><?php echo e($index + 1); ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php
                                        $logo = $concejal->party_logo ?? null;
                                        $color = $concejal->color ?? '#0ab39c';
                                    ?>
                                    <?php if($logo): ?>
                                        <img src="<?php echo e(asset('storage/' . $logo)); ?>" width="20" height="20" class="me-1 rounded" style="object-fit: contain;">
                                    <?php else: ?>
                                        <span class="candidate-color" style="background-color: <?php echo e($color); ?>; width: 16px; height: 16px; border-radius: 4px; display: inline-block; margin-right: 4px;"></span>
                                    <?php endif; ?>
                                    <span class="small"><?php echo e($concejal->party); ?></span>
                                </div>
                            </td>
                            <td class="table-success">
                                <div class="d-flex align-items-center">
                                    <?php if($concejal->photo): ?>
                                        <img src="<?php echo e(asset('storage/' . $concejal->photo)); ?>" class="rounded-circle me-1" width="20" height="20" style="object-fit: cover;">
                                    <?php endif; ?>
                                    <span class="small"><?php echo e($concejal->name); ?></span>
                                    <?php if($isConcejalObserved): ?>
                                        <i class="ri-alert-line text-danger ms-1" title="Observado"></i>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="table-success text-center">
                                <input type="number"
                                       class="form-control form-control-sm vote-input text-center"
                                       data-table="<?php echo e($table->id); ?>"
                                       data-candidate="<?php echo e($concejal->id); ?>"
                                       data-category="concejal"
                                       value="<?php echo e($quantityConcejal); ?>"
                                       min="0"
                                       max="<?php echo e($table->expected_voters ?? 9999); ?>"
                                       step="1"
                                       <?php echo e($isDisabled ? 'disabled' : ''); ?>

                                       style="width: 80px; margin: 0 auto; <?php echo e($isConcejalObserved ? 'border-color: #f06548;' : ''); ?>">
                            </td>
                            <td class="table-success text-center">
                                <?php if($userCan['observe'] && !$isDisabled): ?>
                                    <input type="checkbox"
                                           class="form-check-input observe-checkbox"
                                           data-table="<?php echo e($table->id); ?>"
                                           data-candidate="<?php echo e($concejal->id); ?>"
                                           data-category="concejal"
                                           data-candidate-name="<?php echo e($concejal->name); ?>"
                                           <?php echo e($isConcejalObserved ? 'checked' : ''); ?>

                                           <?php echo e($isConcejalObserved ? 'disabled' : ''); ?>

                                           title="Marcar como observado">
                                <?php elseif($isConcejalObserved): ?>
                                    <i class="ri-checkbox-circle-fill text-warning" title="Observado"></i>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    
                    <?php $__currentLoopData = ['nulo', 'blanco']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $candidate = $candidatesByCategory['concejal']->firstWhere('type', $tipo . '_votes');
                            if ($candidate) {
                                $vote = $table->votes->firstWhere('candidate_id', $candidate->id);
                                $quantity = $vote ? $vote->quantity : 0;
                                $totalConcejal += $quantity;
                                $isObserved = $vote && $vote->vote_status === 'observed';
                            }
                        ?>
                        <?php if($candidate): ?>
                        <tr class="table-secondary <?php echo e($isObserved ? 'table-warning' : ''); ?>">
                            <td class="text-center"><?php echo e($loop->index + $concejales->count() + 1); ?></td>
                            <td>-</td>
                            <td class="table-success fw-bold"><?php echo e(ucfirst($tipo)); ?></td>
                            <td class="table-success text-center">
                                <input type="number"
                                       class="form-control form-control-sm vote-input text-center"
                                       data-table="<?php echo e($table->id); ?>"
                                       data-candidate="<?php echo e($candidate->id); ?>"
                                       data-category="concejal"
                                       value="<?php echo e($quantity); ?>"
                                       min="0"
                                       <?php echo e($isDisabled ? 'disabled' : ''); ?>

                                       style="width: 80px; margin: 0 auto; <?php echo e($isObserved ? 'border-color: #f06548;' : ''); ?>">
                            </td>
                            <td class="table-success text-center">
                                <?php if($userCan['observe'] && !$isDisabled): ?>
                                    <input type="checkbox"
                                           class="form-check-input observe-checkbox"
                                           data-table="<?php echo e($table->id); ?>"
                                           data-candidate="<?php echo e($candidate->id); ?>"
                                           data-category="concejal"
                                           data-candidate-name="<?php echo e(ucfirst($tipo)); ?>"
                                           <?php echo e($isObserved ? 'checked' : ''); ?>

                                           <?php echo e($isObserved ? 'disabled' : ''); ?>>
                                <?php elseif($isObserved): ?>
                                    <i class="ri-checkbox-circle-fill text-warning"></i>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <tr class="table-info fw-bold">
                        <td colspan="3" class="text-end">TOTAL CONCEJALES:</td>
                        <td class="table-success text-center">
                            <span id="total-concejal-<?php echo e($table->id); ?>"><?php echo e($totalConcejal); ?></span>
                        </td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php if($userCan['observe'] && !$isDisabled): ?>
        <div class="p-2 bg-light border-top">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="text-muted" id="selected-count-<?php echo e($table->id); ?>">0</span> votos seleccionados para observar
                    <span class="badge bg-primary ms-2" id="selected-alcaldes-<?php echo e($table->id); ?>">0 Alcaldes</span>
                    <span class="badge bg-success ms-1" id="selected-concejales-<?php echo e($table->id); ?>">0 Concejales</span>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-sm btn-warning create-observation-btn"
                            data-table-id="<?php echo e($table->id); ?>"
                            id="create-observation-<?php echo e($table->id); ?>">
                        <i class="ri-chat-1-line me-1"></i>
                        Crear Observación con Seleccionados
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <div class="row g-0 bg-light p-2 border-top small">
            <div class="col-md-3">
                <span class="text-muted">Votos Válidos:</span>
                <span class="fw-bold ms-1"><?php echo e($table->valid_votes ?? 0); ?></span>
            </div>
            <div class="col-md-3">
                <span class="text-muted">Votos en Blanco:</span>
                <span class="fw-bold ms-1"><?php echo e($table->blank_votes ?? 0); ?></span>
            </div>
            <div class="col-md-3">
                <span class="text-muted">Votos Nulos:</span>
                <span class="fw-bold ms-1"><?php echo e($table->null_votes ?? 0); ?></span>
            </div>
            <div class="col-md-3">
                <span class="text-muted">Papeletas Sobrantes:</span>
                <span class="fw-bold ms-1"><?php echo e($table->ballots_leftover ?? 0); ?></span>
            </div>
        </div>
    </div>
</div>
<?php /**PATH D:\_Mine\corporate\resources\views/voting-table-votes/partials/table.blade.php ENDPATH**/ ?>