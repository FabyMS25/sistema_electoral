
<?php
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
?>


<?php for($i = 0; $i < $maxRows; $i++): ?>
    <tr>
        <td class="text-center fw-bold"><?php echo e($i + 1); ?></td>

        
        <td>
            <?php $firstCandidate = null; ?>
            <?php $__currentLoopData = $candidatesByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoryCode => $categoryCandidates): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $candidate = $regularCandidates[$categoryCode][$i] ?? null;
                    if ($candidate && !$firstCandidate) $firstCandidate = $candidate;
                ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if($firstCandidate): ?>
                <div class="d-flex align-items-center">
                    <?php if($firstCandidate->party_logo): ?>
                        <img src="<?php echo e($firstCandidate->party_logo_url); ?>"
                             width="20" height="20" class="me-1 rounded" style="object-fit: contain;">
                    <?php else: ?>
                        <span class="candidate-color"
                              style="background-color: <?php echo e($firstCandidate->color ?? '#0ab39c'); ?>;
                                     width: 16px; height: 16px; border-radius: 4px; display: inline-block; margin-right: 4px;"></span>
                    <?php endif; ?>
                    <span class="small"><?php echo e($firstCandidate->party); ?></span>
                </div>
            <?php endif; ?>
        </td>

        
        <?php $__currentLoopData = $candidatesByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoryCode => $categoryCandidates): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
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
            ?>

            
            <td class="table-<?php echo e($categoryColorMap[$categoryCode] ?? 'secondary'); ?>">
                <?php if($candidate): ?>
                    <div class="d-flex align-items-center">
                        <?php if($candidate->photo): ?>
                            <img src="<?php echo e($candidate->photo_url); ?>"
                                 class="rounded-circle me-1" width="20" height="20" style="object-fit: cover;">
                        <?php endif; ?>
                        <span class="small"><?php echo e(Str::limit($candidate->name, 25)); ?></span>
                        <?php if($isObserved): ?>
                            <i class="ri-alert-line text-danger ms-1" title="Observado"></i>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <span class="text-muted fst-italic small">---</span>
                <?php endif; ?>
            </td>

            
            <td class="table-<?php echo e($categoryColorMap[$categoryCode] ?? 'secondary'); ?> text-center">
                <?php if($candidate): ?>
                    <input type="number"
                           class="form-control form-control-sm vote-input text-center"
                           data-table="<?php echo e($table->id); ?>"
                           data-candidate="<?php echo e($candidate->id); ?>"
                           data-category="<?php echo e($categoryCode); ?>"
                           value="<?php echo e($quantity); ?>"
                           min="0"
                           max="<?php echo e($table->expected_voters ?? 9999); ?>"
                           step="1"
                           <?php echo e($isDisabled ? 'disabled' : ''); ?>

                           style="width: 70px; margin: 0 auto; <?php echo e($isObserved ? 'border-color: #f06548;' : ''); ?>">
                <?php endif; ?>
            </td>

            
            <td class="table-<?php echo e($categoryColorMap[$categoryCode] ?? 'secondary'); ?> text-center">
                <?php if($candidate && ($permissions['can_observe'] ?? false) && !$isDisabled): ?>
                    <input type="checkbox"
                           class="form-check-input observe-checkbox"
                           data-table="<?php echo e($table->id); ?>"
                           data-candidate="<?php echo e($candidate->id); ?>"
                           data-category="<?php echo e($categoryCode); ?>"
                           data-candidate-name="<?php echo e($candidate->name); ?>"
                           <?php echo e($isObserved ? 'checked' : ''); ?>

                           <?php echo e($isObserved ? 'disabled' : ''); ?>

                           title="Marcar como observado">
                <?php elseif($isObserved): ?>
                    <i class="ri-checkbox-circle-fill text-warning"></i>
                <?php endif; ?>
            </td>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tr>
<?php endfor; ?>


<tr class="table-info fw-bold">
    <td colspan="2" class="text-end">TOTALES:</td>
    <?php $__currentLoopData = $candidatesByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoryCode => $categoryCandidates): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <td class="table-<?php echo e($categoryColorMap[$categoryCode] ?? 'secondary'); ?> text-center" colspan="2">
            <span id="total-<?php echo e($categoryCode); ?>-<?php echo e($table->id); ?>"><?php echo e($categoryTotals[$categoryCode]); ?></span>
        </td>
        <td class="table-<?php echo e($categoryColorMap[$categoryCode] ?? 'secondary'); ?>"></td>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tr>
<?php /**PATH D:\_Mine\corporate\resources\views/voting-table-votes/partials/table-rows.blade.php ENDPATH**/ ?>