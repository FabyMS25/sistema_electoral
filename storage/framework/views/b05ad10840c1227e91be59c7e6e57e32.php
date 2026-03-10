
<?php
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
?>

<?php for($i = 0; $i < $maxRows; $i++): ?>
<tr>
    <td class="text-center fw-bold small"><?php echo e($i + 1); ?></td>
    <td>
        <?php $firstCandidate = null; ?>
        <?php $__currentLoopData = $candidatesByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoryCode => $_): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $c = $regularCandidates[$categoryCode][$i] ?? null; ?>
            <?php if($c && !$firstCandidate): ?> <?php $firstCandidate = $c; ?> <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php if($firstCandidate): ?>
            <div class="d-flex align-items-center gap-1">
                <?php if($firstCandidate->party_logo): ?>
                    <img src="<?php echo e($firstCandidate->party_logo_url); ?>"
                         width="20" height="20" class="rounded" style="object-fit:contain;">
                <?php else: ?>
                    <span style="background:<?php echo e($firstCandidate->color ?? '#0ab39c'); ?>;
                                 width:14px;height:14px;border-radius:3px;display:inline-block;flex-shrink:0;"></span>
                <?php endif; ?>
                <span class="small"><?php echo e($firstCandidate->party); ?></span>
            </div>
        <?php endif; ?>
    </td>
    <?php $__currentLoopData = $candidatesByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoryCode => $_): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $candidate  = $regularCandidates[$categoryCode][$i] ?? null;
            $vote       = $candidate ? ($voteMap[$candidate->id] ?? null) : null;
            $quantity   = $vote?->quantity ?? 0;
            $isObserved = $vote && $vote->vote_status === \App\Models\Vote::VOTE_STATUS_OBSERVED;
            $colClass   = 'table-' . ($categoryColorMap[$categoryCode] ?? 'secondary');
        ?>
        <td class="<?php echo e($colClass); ?> col-<?php echo e(Str::slug($categoryCode)); ?>">
            <?php if($candidate): ?>
                <div class="d-flex align-items-center gap-1">
                    <?php if($candidate->photo): ?>
                        <img src="<?php echo e($candidate->photo_url); ?>"
                             class="rounded-circle" width="20" height="20" style="object-fit:cover;">
                    <?php endif; ?>
                    <span class="small"><?php echo e(Str::limit($candidate->name, 25)); ?></span>
                    <?php if($isObserved): ?>
                        <i class="ri-alert-line text-danger" title="Observado"></i>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <span class="text-muted fst-italic small">---</span>
            <?php endif; ?>
        </td>
        <td class="<?php echo e($colClass); ?> col-<?php echo e(Str::slug($categoryCode)); ?> text-center">
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

                       style="width:70px;margin:0 auto;<?php echo e($isObserved ? 'border-color:#f06548;' : ''); ?>">
            <?php endif; ?>
        </td>
        <td class="<?php echo e($colClass); ?> col-<?php echo e(Str::slug($categoryCode)); ?> text-center">
            <?php if($candidate): ?>
                <?php if($canObserve): ?>
                    <input type="checkbox"
                           class="form-check-input observe-checkbox"
                           data-table="<?php echo e($table->id); ?>"
                           data-vote-id="<?php echo e($vote?->id ?? ''); ?>"
                           data-candidate="<?php echo e($candidate->id); ?>"
                           data-category="<?php echo e($categoryCode); ?>"
                           data-candidate-name="<?php echo e($candidate->name); ?>"
                           <?php echo e($isObserved ? 'checked disabled' : ''); ?>

                           title="<?php echo e($isObserved ? 'Ya observado' : 'Marcar como observado'); ?>">
                <?php elseif($isObserved): ?>
                    <i class="ri-checkbox-circle-fill text-warning" title="Observado"></i>
                <?php endif; ?>
            <?php endif; ?>
        </td>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tr>
<?php endfor; ?>
<tr class="table-light">
    <td class="text-center text-muted" style="font-size:0.7rem;">
        <i class="ri-subtract-line"></i>
    </td>
    <td class="text-end small fw-semibold text-muted pe-2" style="white-space:nowrap; font-size:0.78rem;">
        En Blanco
    </td>
    <?php $__currentLoopData = $candidatesByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoryCode => $_): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $blankQty = $table->results_by_category[$categoryCode]['blank_votes'] ?? 0;
            $colClass  = 'table-' . ($categoryColorMap[$categoryCode] ?? 'secondary');
        ?>
        <td class="<?php echo e($colClass); ?>"></td>
        <td class="<?php echo e($colClass); ?> text-center">
            <?php if(!$isDisabled && ($permissions['can_register'] ?? false)): ?>
                <input type="number"
                       class="form-control form-control-sm blank-votes-input text-center fw-bold"
                       data-table="<?php echo e($table->id); ?>"
                       data-category="<?php echo e($categoryCode); ?>"
                       value="<?php echo e($blankQty); ?>"
                       min="0" step="1"
                       style="width:70px; margin:0 auto;"
                       title="Votos en blanco — <?php echo e($categoryCode); ?>">
            <?php else: ?>
                <span class="fw-bold"><?php echo e($blankQty); ?></span>
            <?php endif; ?>
        </td>
        <td class="<?php echo e($colClass); ?>"></td>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tr>
<tr class="table-light">
    <td class="text-center text-muted" style="font-size:0.7rem;">
        <i class="ri-close-line"></i>
    </td>
    <td class="text-end small fw-semibold text-muted pe-2" style="white-space:nowrap; font-size:0.78rem;">
        Nulos
    </td>
    <?php $__currentLoopData = $candidatesByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoryCode => $_): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $nullQty  = $table->results_by_category[$categoryCode]['null_votes'] ?? 0;
            $colClass = 'table-' . ($categoryColorMap[$categoryCode] ?? 'secondary');
        ?>
        <td class="<?php echo e($colClass); ?>"></td>
        <td class="<?php echo e($colClass); ?> text-center">
            <?php if(!$isDisabled && ($permissions['can_register'] ?? false)): ?>
                <input type="number"
                       class="form-control form-control-sm null-votes-input text-center fw-bold"
                       data-table="<?php echo e($table->id); ?>"
                       data-category="<?php echo e($categoryCode); ?>"
                       value="<?php echo e($nullQty); ?>"
                       min="0" step="1"
                       style="width:70px; margin:0 auto;"
                       title="Votos nulos — <?php echo e($categoryCode); ?>">
            <?php else: ?>
                <span class="fw-bold"><?php echo e($nullQty); ?></span>
            <?php endif; ?>
        </td>
        <td class="<?php echo e($colClass); ?>"></td>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tr>
<tr class="table-info fw-bold">
    <td colspan="2" class="text-end small">TOTALES</td>
    <?php $__currentLoopData = $candidatesByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoryCode => $_): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $catTotal = $table->results_by_category[$categoryCode]['total_votes'] ?? 0;
            $colClass = 'table-' . ($categoryColorMap[$categoryCode] ?? 'secondary');
        ?>
        <td class="<?php echo e($colClass); ?> text-center" colspan="2">
            <span id="total-<?php echo e($categoryCode); ?>-<?php echo e($table->id); ?>"><?php echo e($catTotal); ?></span>
        </td>
        <td class="<?php echo e($colClass); ?>"></td>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tr>


<?php /**PATH D:\_Mine\sistema_electoral\resources\views/voting-table-votes/partials/table-rows.blade.php ENDPATH**/ ?>