
<?php
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
?>


<?php for($i = 0; $i < $maxRows; $i++): ?>
    <?php
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
    ?>
    <tr class="<?php echo e($isAlcaldeObserved || $isConcejalObserved ? 'table-warning' : ''); ?>">
        <td class="text-center fw-bold"><?php echo e($i + 1); ?></td>

        
        <td>
            <?php if($alcalde || $concejal): ?>
                <?php $party = $alcalde->party ?? $concejal->party; ?>
                <div class="d-flex align-items-center">
                    <?php
                        $logo = $alcalde->party_logo ?? $concejal->party_logo ?? null;
                        $color = $alcalde->color ?? $concejal->color ?? '#0ab39c';
                    ?>
                    <?php if($logo): ?>
                        <img src="<?php echo e(asset('storage/' . $logo)); ?>"
                             width="20" height="20"
                             class="me-1 rounded"
                             style="object-fit: contain;">
                    <?php else: ?>
                        <span class="candidate-color" style="background-color: <?php echo e($color); ?>; width: 16px; height: 16px; border-radius: 4px; display: inline-block; margin-right: 4px;"></span>
                    <?php endif; ?>
                    <span class="small"><?php echo e($party); ?></span>
                </div>
            <?php endif; ?>
        </td>

        
        <td class="table-primary">
            <?php if($alcalde): ?>
                <div class="d-flex align-items-center">
                    <?php if($alcalde->photo): ?>
                        <img src="<?php echo e(asset('storage/' . $alcalde->photo)); ?>"
                             class="rounded-circle me-1"
                             width="20" height="20"
                             style="object-fit: cover;">
                    <?php endif; ?>
                    <span class="small"><?php echo e(Str::limit($alcalde->name, 25)); ?></span>
                    <?php if($isAlcaldeObserved): ?>
                        <i class="ri-alert-line text-danger ms-1" title="Observado"></i>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <span class="text-muted fst-italic small">---</span>
            <?php endif; ?>
        </td>

        
        <td class="table-primary text-center">
            <?php if($alcalde): ?>
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

                       style="width: 70px; margin: 0 auto; <?php echo e($isAlcaldeObserved ? 'border-color: #f06548;' : ''); ?>">
            <?php endif; ?>
        </td>

        
        <td class="table-primary text-center">
            <?php if($alcalde && $userCan['observe'] && !$isDisabled): ?>
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

        
        <td class="table-success">
            <?php if($concejal): ?>
                <div class="d-flex align-items-center">
                    <?php if($concejal->photo): ?>
                        <img src="<?php echo e(asset('storage/' . $concejal->photo)); ?>"
                             class="rounded-circle me-1"
                             width="20" height="20"
                             style="object-fit: cover;">
                    <?php endif; ?>
                    <span class="small"><?php echo e(Str::limit($concejal->name, 25)); ?></span>
                    <?php if($isConcejalObserved): ?>
                        <i class="ri-alert-line text-danger ms-1" title="Observado"></i>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <span class="text-muted fst-italic small">---</span>
            <?php endif; ?>
        </td>

        
        <td class="table-success text-center">
            <?php if($concejal): ?>
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

                       style="width: 70px; margin: 0 auto; <?php echo e($isConcejalObserved ? 'border-color: #f06548;' : ''); ?>">
            <?php endif; ?>
        </td>

        
        <td class="table-success text-center">
            <?php if($concejal && $userCan['observe'] && !$isDisabled): ?>
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
<?php endfor; ?>


<?php if($nuloAlcalde || $nuloConcejal): ?>
<tr class="table-secondary">
    <td class="text-center"><?php echo e($maxRows + 1); ?></td>
    <td>-</td>

    
    <td class="table-primary fw-bold">NULO</td>
    <td class="table-primary text-center">
        <?php if($nuloAlcalde): ?>
            <?php
                $vote = $table->votes->firstWhere('candidate_id', $nuloAlcalde->id);
                $quantity = $vote ? $vote->quantity : 0;
                $totalAlcalde += $quantity;
                $isObserved = $vote && $vote->vote_status === 'observed';
            ?>
            <input type="number"
                   class="form-control form-control-sm vote-input text-center"
                   data-table="<?php echo e($table->id); ?>"
                   data-candidate="<?php echo e($nuloAlcalde->id); ?>"
                   data-category="alcalde"
                   value="<?php echo e($quantity); ?>"
                   min="0"
                   <?php echo e($isDisabled ? 'disabled' : ''); ?>

                   style="width: 70px; margin: 0 auto; <?php echo e($isObserved ? 'border-color: #f06548;' : ''); ?>">
        <?php endif; ?>
    </td>
    <td class="table-primary text-center">
        <?php if($nuloAlcalde && $userCan['observe'] && !$isDisabled): ?>
            <input type="checkbox"
                   class="form-check-input observe-checkbox"
                   data-table="<?php echo e($table->id); ?>"
                   data-candidate="<?php echo e($nuloAlcalde->id); ?>"
                   data-category="alcalde"
                   data-candidate-name="NULO"
                   <?php echo e($isObserved ? 'checked' : ''); ?>

                   <?php echo e($isObserved ? 'disabled' : ''); ?>>
        <?php elseif($isObserved): ?>
            <i class="ri-checkbox-circle-fill text-warning"></i>
        <?php endif; ?>
    </td>

    
    <td class="table-success fw-bold">NULO</td>
    <td class="table-success text-center">
        <?php if($nuloConcejal): ?>
            <?php
                $vote = $table->votes->firstWhere('candidate_id', $nuloConcejal->id);
                $quantity = $vote ? $vote->quantity : 0;
                $totalConcejal += $quantity;
                $isObserved = $vote && $vote->vote_status === 'observed';
            ?>
            <input type="number"
                   class="form-control form-control-sm vote-input text-center"
                   data-table="<?php echo e($table->id); ?>"
                   data-candidate="<?php echo e($nuloConcejal->id); ?>"
                   data-category="concejal"
                   value="<?php echo e($quantity); ?>"
                   min="0"
                   <?php echo e($isDisabled ? 'disabled' : ''); ?>

                   style="width: 70px; margin: 0 auto; <?php echo e($isObserved ? 'border-color: #f06548;' : ''); ?>">
        <?php endif; ?>
    </td>
    <td class="table-success text-center">
        <?php if($nuloConcejal && $userCan['observe'] && !$isDisabled): ?>
            <input type="checkbox"
                   class="form-check-input observe-checkbox"
                   data-table="<?php echo e($table->id); ?>"
                   data-candidate="<?php echo e($nuloConcejal->id); ?>"
                   data-category="concejal"
                   data-candidate-name="NULO"
                   <?php echo e($isObserved ? 'checked' : ''); ?>

                   <?php echo e($isObserved ? 'disabled' : ''); ?>>
        <?php elseif($isObserved): ?>
            <i class="ri-checkbox-circle-fill text-warning"></i>
        <?php endif; ?>
    </td>
</tr>
<?php endif; ?>


<?php if($blancoAlcalde || $blancoConcejal): ?>
<tr class="table-secondary">
    <td class="text-center"><?php echo e($maxRows + 2); ?></td>
    <td>-</td>

    
    <td class="table-primary fw-bold">BLANCO</td>
    <td class="table-primary text-center">
        <?php if($blancoAlcalde): ?>
            <?php
                $vote = $table->votes->firstWhere('candidate_id', $blancoAlcalde->id);
                $quantity = $vote ? $vote->quantity : 0;
                $totalAlcalde += $quantity;
                $isObserved = $vote && $vote->vote_status === 'observed';
            ?>
            <input type="number"
                   class="form-control form-control-sm vote-input text-center"
                   data-table="<?php echo e($table->id); ?>"
                   data-candidate="<?php echo e($blancoAlcalde->id); ?>"
                   data-category="alcalde"
                   value="<?php echo e($quantity); ?>"
                   min="0"
                   <?php echo e($isDisabled ? 'disabled' : ''); ?>

                   style="width: 70px; margin: 0 auto; <?php echo e($isObserved ? 'border-color: #f06548;' : ''); ?>">
        <?php endif; ?>
    </td>
    <td class="table-primary text-center">
        <?php if($blancoAlcalde && $userCan['observe'] && !$isDisabled): ?>
            <input type="checkbox"
                   class="form-check-input observe-checkbox"
                   data-table="<?php echo e($table->id); ?>"
                   data-candidate="<?php echo e($blancoAlcalde->id); ?>"
                   data-category="alcalde"
                   data-candidate-name="BLANCO"
                   <?php echo e($isObserved ? 'checked' : ''); ?>

                   <?php echo e($isObserved ? 'disabled' : ''); ?>>
        <?php elseif($isObserved): ?>
            <i class="ri-checkbox-circle-fill text-warning"></i>
        <?php endif; ?>
    </td>

    
    <td class="table-success fw-bold">BLANCO</td>
    <td class="table-success text-center">
        <?php if($blancoConcejal): ?>
            <?php
                $vote = $table->votes->firstWhere('candidate_id', $blancoConcejal->id);
                $quantity = $vote ? $vote->quantity : 0;
                $totalConcejal += $quantity;
                $isObserved = $vote && $vote->vote_status === 'observed';
            ?>
            <input type="number"
                   class="form-control form-control-sm vote-input text-center"
                   data-table="<?php echo e($table->id); ?>"
                   data-candidate="<?php echo e($blancoConcejal->id); ?>"
                   data-category="concejal"
                   value="<?php echo e($quantity); ?>"
                   min="0"
                   <?php echo e($isDisabled ? 'disabled' : ''); ?>

                   style="width: 70px; margin: 0 auto; <?php echo e($isObserved ? 'border-color: #f06548;' : ''); ?>">
        <?php endif; ?>
    </td>
    <td class="table-success text-center">
        <?php if($blancoConcejal && $userCan['observe'] && !$isDisabled): ?>
            <input type="checkbox"
                   class="form-check-input observe-checkbox"
                   data-table="<?php echo e($table->id); ?>"
                   data-candidate="<?php echo e($blancoConcejal->id); ?>"
                   data-category="concejal"
                   data-candidate-name="BLANCO"
                   <?php echo e($isObserved ? 'checked' : ''); ?>

                   <?php echo e($isObserved ? 'disabled' : ''); ?>>
        <?php elseif($isObserved): ?>
            <i class="ri-checkbox-circle-fill text-warning"></i>
        <?php endif; ?>
    </td>
</tr>
<?php endif; ?>


<tr class="table-info fw-bold">
    <td colspan="2" class="text-end">TOTALES:</td>
    <td class="table-primary text-center" colspan="2">
        <span id="total-alcalde-<?php echo e($table->id); ?>"><?php echo e($totalAlcalde); ?></span>
    </td>
    <td class="table-primary"></td>
    <td class="table-success text-center" colspan="2">
        <span id="total-concejal-<?php echo e($table->id); ?>"><?php echo e($totalConcejal); ?></span>
    </td>
    <td class="table-success"></td>
</tr>
<?php /**PATH D:\_Mine\corporate\resources\views/voting-table-votes/partials/table-row.blade.php ENDPATH**/ ?>